<?php
// Run from CLI, on a restored staging copy first. DDL commits implicitly in MySQL/MariaDB.
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
require_once __DIR__ . '/../includes/db.php';
$pdo->exec("SET SESSION sql_mode = CONCAT(@@SESSION.sql_mode, ',NO_AUTO_VALUE_ON_ZERO')");
$apply = in_array('--apply', $argv, true);
if ($apply && !in_array('--backup-confirmed', $argv, true)) {
    fwrite(STDERR, "Backup database dan hentikan seluruh writer, lalu gunakan --apply --backup-confirmed.\n"); exit(1);
}
function columnInfo(PDO $pdo, string $table, string $column) {
    $q = $pdo->prepare('SELECT * FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND COLUMN_NAME = ?');
    $q->execute([$table, $column]); return $q->fetch();
}
function hasIndex(PDO $pdo, string $table, string $index): bool {
    $q = $pdo->prepare('SELECT COUNT(*) FROM information_schema.STATISTICS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND INDEX_NAME = ?');
    $q->execute([$table, $index]); return (int)$q->fetchColumn() > 0;
}
function hasConstraint(PDO $pdo, string $name): bool {
    $q = $pdo->prepare('SELECT COUNT(*) FROM information_schema.TABLE_CONSTRAINTS WHERE CONSTRAINT_SCHEMA = DATABASE() AND CONSTRAINT_NAME = ?');
    $q->execute([$name]); return (int)$q->fetchColumn() > 0;
}
$lock = 'warehouse_migration_' . substr(hash('sha256', (string)wh_config('DB_NAME', 'db_warehouse')), 0, 20);
$q = $pdo->prepare('SELECT GET_LOCK(?, 0)'); $q->execute([$lock]);
if ((int)$q->fetchColumn() !== 1) { fwrite(STDERR, "Migrasi lain sedang berjalan.\n"); exit(1); }
try {
    $checks = [
        'ID pengguna negatif/duplikat' => 'SELECT user_id FROM users GROUP BY user_id HAVING user_id < 0 OR COUNT(*) > 1',
        'Username kosong/duplikat' => "SELECT username FROM users GROUP BY username HAVING TRIM(username) = '' OR COUNT(*) > 1",
        'Role pengguna tidak valid' => "SELECT user_id FROM users WHERE role NOT IN ('admin','staff','','user')",
        'Setting duplikat' => 'SELECT setting_key FROM settings GROUP BY setting_key HAVING COUNT(*) > 1',
        'Request memiliki lebih dari satu transaksi' => 'SELECT request_id FROM transaksi WHERE request_id IS NOT NULL GROUP BY request_id HAVING COUNT(*) > 1',
        'Detail transaksi duplikat' => 'SELECT transaction_id, barcode FROM transaksi_detail GROUP BY transaction_id, barcode HAVING COUNT(*) > 1',
        'Retur duplikat' => 'SELECT transaction_id, barcode FROM return_items GROUP BY transaction_id, barcode HAVING COUNT(*) > 1',
        'Retur tanpa detail transaksi asal' => 'SELECT r.return_id FROM return_items r LEFT JOIN transaksi_detail d ON d.transaction_id=r.transaction_id AND d.barcode=r.barcode WHERE d.id_detail IS NULL',
    ];
    foreach ($checks as $label => $sql) {
        if ($pdo->query($sql)->fetch()) { throw new RuntimeException($label . '. Rekonsiliasi manual diperlukan; tidak ada baris yang dihapus otomatis.'); }
    }
    $changes = [];
    if (!hasIndex($pdo, 'users', 'PRIMARY')) { $changes[] = 'ALTER TABLE users ADD PRIMARY KEY (user_id)'; }
    if (stripos(columnInfo($pdo, 'users', 'user_id')['EXTRA'], 'auto_increment') === false) { $changes[] = 'ALTER TABLE users MODIFY user_id INT NOT NULL AUTO_INCREMENT'; }
    if (!hasIndex($pdo, 'users', 'uq_users_username')) { $changes[] = 'ALTER TABLE users ADD UNIQUE KEY uq_users_username (username)'; }
    if (!columnInfo($pdo, 'users', 'legacy_role')) { $changes[] = 'ALTER TABLE users ADD legacy_role VARCHAR(20) DEFAULT NULL'; }
    $changes[] = "UPDATE users SET legacy_role = CAST(role AS CHAR), role = 'staff' WHERE role IN ('', 'user')";
    if (!columnInfo($pdo, 'users', 'auth_version')) { $changes[] = 'ALTER TABLE users ADD auth_version INT UNSIGNED NOT NULL DEFAULT 1'; }
    if (!hasIndex($pdo, 'log_activity', 'PRIMARY')) {
        $refs = $pdo->query("SELECT COUNT(*) FROM information_schema.KEY_COLUMN_USAGE WHERE REFERENCED_TABLE_SCHEMA = DATABASE() AND REFERENCED_TABLE_NAME = 'log_activity'")->fetchColumn();
        if ((int)$refs > 0 || columnInfo($pdo, 'log_activity', 'legacy_id')) { throw new RuntimeException('Struktur log berbeda; tinjau dependensi log sebelum migrasi.'); }
        // Preserve every original ID (including repeated zeroes) as legacy_id.
        $changes[] = 'ALTER TABLE log_activity CHANGE COLUMN id legacy_id INT NOT NULL DEFAULT 0, ADD COLUMN id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY FIRST';
    } elseif (stripos(columnInfo($pdo, 'log_activity', 'id')['EXTRA'], 'auto_increment') === false) {
        $changes[] = 'ALTER TABLE log_activity MODIFY id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT';
    }
    if (!hasIndex($pdo, 'settings', 'PRIMARY')) { $changes[] = 'ALTER TABLE settings ADD PRIMARY KEY (setting_key)'; }
    foreach ([['transaksi','uq_transaksi_request','request_id'], ['transaksi_detail','uq_detail_transaction_barcode','transaction_id, barcode'], ['return_items','uq_return_transaction_barcode','transaction_id, barcode']] as [$table,$index,$columns]) {
        if (!hasIndex($pdo, $table, $index)) { $changes[] = "ALTER TABLE {$table} ADD UNIQUE KEY {$index} ({$columns})"; }
    }
    if (!hasConstraint($pdo, 'fk_return_original_detail')) {
        $changes[] = 'ALTER TABLE return_items ADD CONSTRAINT fk_return_original_detail FOREIGN KEY (transaction_id, barcode) REFERENCES transaksi_detail (transaction_id, barcode) ON DELETE RESTRICT ON UPDATE CASCADE';
    }
    foreach ([['log_activity','idx_log_created_at','created_at'], ['master_item','idx_stock_status','status_transaksi, status_barang'], ['request_form','idx_request_status_date','status, tgl_request']] as [$table,$index,$columns]) {
        if (!hasIndex($pdo, $table, $index)) { $changes[] = "ALTER TABLE {$table} ADD INDEX {$index} ({$columns})"; }
    }
    echo 'Database: ' . wh_config('DB_NAME', 'db_warehouse') . "\n";
    echo 'Preflight OK. Perubahan struktur: ' . count($changes) . "; tabel pendukung akan dibuat jika belum ada.\n";
    foreach ($changes as $sql) { echo $sql . ";\n"; if ($apply) { $pdo->exec($sql); } }
    if ($apply) {
        $support = file_get_contents(__DIR__ . '/migrations/001_support_tables.sql');
        foreach (explode(';', $support) as $sql) { if (trim($sql) !== '') { $pdo->exec($sql); } }
        $pdo->exec("INSERT INTO transaction_sequences (year, last_value) SELECT CAST(SUBSTRING(transaction_id,5,4) AS UNSIGNED), MAX(CAST(SUBSTRING(transaction_id,10) AS UNSIGNED)) FROM transaksi WHERE transaction_id REGEXP '^TRX-[0-9]{4}-[0-9]+$' GROUP BY SUBSTRING(transaction_id,5,4) ON DUPLICATE KEY UPDATE last_value = GREATEST(last_value, VALUES(last_value))");
        $pdo->exec("INSERT INTO settings (setting_key, setting_value) VALUES ('audit_active','0') ON DUPLICATE KEY UPDATE setting_key = VALUES(setting_key)");
        $pdo->exec("INSERT INTO schema_migrations (version) VALUES ('20260909_production_fixes_v1') ON DUPLICATE KEY UPDATE version = VALUES(version)");
        echo "Migrasi selesai. Seluruh data lama dipertahankan.\n";
    } else { echo "Hanya pemeriksaan. Tidak ada struktur/data yang diubah.\n"; }
} catch (Throwable $error) {
    fwrite(STDERR, 'MIGRASI DIHENTIKAN: ' . $error->getMessage() . "\nDDL yang sudah berhasil tidak otomatis rollback; periksa hasil atau pulihkan backup sebelum melanjutkan.\n");
    exit(1);
} finally {
    $pdo->prepare('SELECT RELEASE_LOCK(?)')->execute([$lock]);
}
