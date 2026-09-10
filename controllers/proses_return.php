<?php
require_once __DIR__ . '/../includes/auth_check.php';
require_once __DIR__ . '/../services/inventory.php';
wh_require_post();
try {
    $count = wh_return($pdo, wh_input());
    wh_success($count . ' barang berhasil diretur.', 'return');
} catch (DomainException $error) { wh_http_error(422, $error->getMessage()); }
