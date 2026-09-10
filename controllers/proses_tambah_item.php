<?php
require_once __DIR__ . '/../includes/auth_check.php';
require_once __DIR__ . '/../services/inventory.php';
wh_require_post();
try {
    $code = wh_text(wh_input(), 'barcode', 9);
    // Product attributes are derived from the validated barcode, never trusted from the form.
    $result = wh_add_stock($pdo, [$code]);
    if (!$result['inserted']) { wh_http_error(409, 'Barcode sudah terdaftar.'); }
    wh_success('Barang berhasil ditambahkan.', 'stok_barang');
} catch (DomainException $error) { wh_http_error(422, $error->getMessage()); }
