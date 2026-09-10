<?php
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
require_once __DIR__ . '/../includes/db.php';
$failed = 0;
function reportCheck(string $name, bool $ok): void {
    global $failed;
    echo ($ok ? 'OK   ' : 'FAIL ') . $name . PHP_EOL;
    if (!$ok) { $failed++; }
}
reportCheck('PHP 8.3 atau lebih baru', version_compare(PHP_VERSION, '8.3.0', '>='));
foreach (['pdo_mysql','mysqli','mbstring','gd','fileinfo','dom','xml','xmlreader','xmlwriter','simplexml','zip'] as $extension) {
    reportCheck('Extension ' . $extension, extension_loaded($extension));
}
reportCheck('Bukan akun root', strtolower((string)wh_config('DB_USER')) !== 'root');
reportCheck('Mode production', wh_config('APP_ENV', 'production') === 'production');
echo 'INFO HTTPS: ' . (wh_bool('APP_REQUIRE_HTTPS', false) ? 'wajib pada production' : 'opsional; HTTP diizinkan') . PHP_EOL;
try {
    $version = $pdo->query("SELECT COUNT(*) FROM schema_migrations WHERE version = '20260909_production_fixes_v1'")->fetchColumn();
    reportCheck('Migrasi database lengkap', (int)$version === 1);
    reportCheck('ID log unik', (int)$pdo->query('SELECT COUNT(*) - COUNT(DISTINCT id) FROM log_activity')->fetchColumn() === 0);
    reportCheck('Akun admin tersedia', (int)$pdo->query("SELECT COUNT(*) FROM users WHERE role = 'admin'")->fetchColumn() > 0);
    $profileDir = __DIR__ . '/../assets/img/profile';
    reportCheck('Folder foto dapat ditulis oleh pengguna CLI ini', is_dir($profileDir) && is_writable($profileDir));
} catch (PDOException $error) { reportCheck('Skema database siap', false); }
echo "Pemeriksaan ini read-only. TLS, akun web server, backup/restore dan akses HTTP tetap perlu diuji di server.\n";
exit($failed ? 1 : 0);
