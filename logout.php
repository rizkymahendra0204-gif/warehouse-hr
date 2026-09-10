<?php
require_once __DIR__ . '/includes/bootstrap.php';
wh_require_post();
wh_verify_csrf();
$_SESSION = [];
setcookie(session_name(), '', ['expires' => time() - 3600, 'path' => wh_url(), 'secure' => wh_https(), 'httponly' => true, 'samesite' => 'Lax']);
session_destroy();
wh_redirect('login');
