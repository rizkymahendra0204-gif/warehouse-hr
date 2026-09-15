<?php
// Copy to local.php on the server. Keep real credentials out of this example.
return [
    'APP_ENV' => 'production',
    'APP_BASE_PATH' => '/warehouse-hr', // '' for domain root; match the deployed folder.
    'APP_REQUIRE_HTTPS' => false,
    'APP_TRUSTED_PROXY_IPS' => '', // Exact HTTPS reverse proxy IPs, comma separated, if used.
    'APP_TIMEZONE' => 'Asia/Jakarta',
    'DB_HOST' => '127.0.0.1',
    'DB_PORT' => '3306',
    'DB_NAME' => 'db_warehouse',
    'DB_USER' => 'warehouse_app',
    'DB_PASSWORD' => '', // REQUIRED: dedicated database account password.
    'REQUEST_UPLOAD_DIR' => '', // Absolute request-proof directory if Excel embeds images.
    'REQUEST_UPLOAD_BASE_URL' => '/Request.Form.2/',
];
