<?php
const WH_JSON = true;
require_once __DIR__ . '/../includes/auth_check.php';
require_once __DIR__ . '/../services/inventory.php';
wh_require_post();
try {
    $barcode = wh_text(wh_input(), 'barcode', 9); wh_parse_barcode($barcode);
    $stmt = $pdo->prepare('SELECT gender, tipe, size, status_transaksi, status_barang FROM master_item WHERE barcode = ?');
    $stmt->execute([$barcode]); $item = $stmt->fetch();
    if (!$item) { wh_http_error(422, 'Barcode tidak terdaftar.'); }
    if (strtolower(trim($item['status_transaksi'])) !== 'available' || strtolower(trim($item['status_barang'])) !== 'active') { wh_http_error(422, 'Barang sudah keluar atau tidak aktif.'); }
    wh_json(['success' => true, 'message' => $item['tipe'] . ' ' . $item['gender'] . ' (' . $item['size'] . ')', 'data' => $item]);
} catch (DomainException $error) { wh_http_error(422, $error->getMessage()); }
