<?php
require_once __DIR__ . '/../includes/db.php';
if (!isset($_SESSION['temp_user_id']) || time() - (int)($_SESSION['temp_login_at'] ?? 0) > 600) { wh_redirect('login'); }
wh_verify_csrf();
$error_message = '';
if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    if (!wh_password_valid($new_password)) {
        $error_message = 'Password baru harus 12–72 byte (minimal 12 karakter untuk huruf/angka).';
    } elseif ($new_password !== $confirm_password) {
        $error_message = 'Konfirmasi password tidak cocok!';
    } else {
        $pdo->beginTransaction();
        try {
            $stmt = $pdo->prepare('SELECT * FROM users WHERE user_id = ? FOR UPDATE');
            $stmt->execute([$_SESSION['temp_user_id']]);
            $account = $stmt->fetch();
            if (!$account || (int)$account['is_first_login'] !== 1 || (int)$account['auth_version'] !== (int)$_SESSION['temp_auth_version']) {
                $pdo->rollBack(); $_SESSION = []; wh_redirect('login');
            }
            if (password_verify($new_password, $account['password'])) {
                $pdo->rollBack(); $error_message = 'Gunakan password baru yang berbeda dari password sementara.';
            } else {
                $pdo->prepare('UPDATE users SET password = ?, is_first_login = 0, auth_version = auth_version + 1 WHERE user_id = ?')->execute([password_hash($new_password, PASSWORD_BCRYPT, ['cost' => 12]), $account['user_id']]);
                $pdo->commit();
                $account['auth_version']++;
                wh_set_login($account);
                wh_redirect('index');
            }
        } catch (Throwable $error) {
            if ($pdo->inTransaction()) { $pdo->rollBack(); }
            throw $error;
        }
    }
}
