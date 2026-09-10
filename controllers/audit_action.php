<?php
// Included before any audit summary query/rendering.
require_once __DIR__ . '/../includes/auth_check.php';
require_once __DIR__ . '/../includes/activity.php';
if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
    try {
        $input = wh_input();
        $action = wh_text($input, 'action_type', 30);
        if ($action === 'toggle_audit') { wh_require_role(['admin']); }
        elseif ($action !== 'update_status') { throw new DomainException('Tindakan tidak valid.'); }
        $pdo->beginTransaction();
        $stmt = $pdo->query("SELECT setting_value FROM settings WHERE setting_key = 'audit_active' FOR UPDATE");
        $active = $stmt->fetchColumn();
        if ($active === false) { throw new RuntimeException('Run database migration before using audit management.'); }
        if ($action === 'toggle_audit') {
            $new = wh_text($input, 'audit_status', 1);
            if (!in_array($new, ['0','1'], true)) { throw new DomainException('Status periode tidak valid.'); }
            $pdo->prepare("UPDATE settings SET setting_value = ? WHERE setting_key = 'audit_active'")->execute([$new]);
            wh_activity($pdo, 'Ubah Periode Management', $new === '1' ? 'Periode dibuka' : 'Periode ditutup', 'Warehouse Management');
        } else {
            if ($active !== '1') { throw new DomainException('Periode Warehouse Management sedang ditutup.'); }
            $code = wh_text($input, 'barcode', 50);
            $tx = wh_text($input, 'status_transaksi', 20);
            $state = wh_text($input, 'status_barang', 20);
            if ($tx !== 'Available' || !in_array($state, ['Active','Inactive'], true)) { throw new DomainException('Evaluasi hanya mengubah kondisi barang Available. Barang keluar harus melalui transaksi.'); }
            $stmt = $pdo->prepare('SELECT status_transaksi, status_barang FROM master_item WHERE barcode = ? FOR UPDATE');
            $stmt->execute([$code]); $item = $stmt->fetch();
            if (!$item || strtolower(trim($item['status_transaksi'])) !== 'available' || strtolower(trim($item['status_barang'])) !== 'inactive') { throw new DomainException('Hanya barang Available/Inactive yang dapat dievaluasi.'); }
            $pdo->prepare('UPDATE master_item SET status_barang = ? WHERE barcode = ?')->execute([$state, $code]);
            wh_activity($pdo, 'Evaluasi Stok', 'Barcode ' . $code . ': Available/' . $state, 'Warehouse Management');
        }
        $pdo->commit();
        wh_success('Perubahan Warehouse Management berhasil disimpan.', 'warehouse_management');
    } catch (Throwable $error) {
        if ($pdo->inTransaction()) { $pdo->rollBack(); }
        if ($error instanceof DomainException) { wh_http_error(422, $error->getMessage()); }
        throw $error;
    }
}
