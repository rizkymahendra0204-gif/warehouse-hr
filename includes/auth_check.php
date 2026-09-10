<?php
require_once __DIR__ . '/bootstrap.php';
function wh_auth_denied(): void {
    $_SESSION = [];
    if (wh_json_request() || !in_array($_SERVER['REQUEST_METHOD'] ?? 'GET', ['GET', 'HEAD'], true)) { wh_http_error(401, 'Silakan login kembali.'); }
    wh_redirect('login');
}
if (($_SESSION['auth_schema'] ?? null) !== 2 || !isset($_SESSION['user_id'])
    || time() - (int)($_SESSION['last_seen'] ?? 0) > 1800
    || time() - (int)($_SESSION['login_at'] ?? 0) > 28800) { wh_auth_denied(); }
require_once __DIR__ . '/db.php';
$authStmt = $pdo->prepare('SELECT user_id, username, nama_lengkap, role, auth_version, is_first_login FROM users WHERE user_id = ?');
$authStmt->execute([$_SESSION['user_id']]);
$authAccount = $authStmt->fetch();
if (!$authAccount || !in_array($authAccount['role'], ['admin', 'staff'], true)
    || (int)$authAccount['is_first_login'] !== 0
    || (int)$authAccount['auth_version'] !== (int)($_SESSION['auth_version'] ?? 0)) { wh_auth_denied(); }
foreach (['role', 'username', 'nama_lengkap'] as $key) { $_SESSION[$key] = $authAccount[$key]; }
$_SESSION['last_seen'] = time();
wh_verify_csrf();
require_once __DIR__ . '/language.php';
