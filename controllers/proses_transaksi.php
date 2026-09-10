<?php
require_once __DIR__ . '/../includes/auth_check.php';
require_once __DIR__ . '/../services/inventory.php';
wh_require_post();
try {
    $id = wh_transaction($pdo, wh_input());
    wh_success('Transaksi berhasil disimpan: ' . $id, 'pending', ['transaction_id' => $id]);
} catch (DomainException $error) { wh_http_error(422, $error->getMessage()); }
