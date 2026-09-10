<?php
require_once __DIR__ . '/../includes/auth_check.php';
wh_require_post();
$selected = $_POST['lang'] ?? '';
if (!in_array($selected, ['id', 'en'], true)) { wh_http_error(422, 'Bahasa tidak valid.'); }
$pdo->prepare('UPDATE users SET lang = ? WHERE user_id = ?')->execute([$selected, $_SESSION['user_id']]);
$_SESSION['lang'] = $selected;
wh_redirect('setting?tab=language');
