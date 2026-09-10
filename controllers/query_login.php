<?php
require_once __DIR__ . '/../includes/db.php';
wh_verify_csrf();
$error_message = '';
if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
    $input = wh_input();
    $username = is_string($input['username'] ?? null) ? trim($input['username']) : '';
    $password = is_string($input['password'] ?? null) ? $input['password'] : '';
    if ($username === '' || strlen($username) > 50 || $password === '' || strlen($password) > 72) {
        $error_message = 'Username atau password salah!';
    } else {
        $lookup = $pdo->prepare('SELECT user_id FROM users WHERE username = ? LIMIT 1');
        $lookup->execute([$username]);
        $accountId = $lookup->fetchColumn();
        $accountBucket = hash('sha256', $accountId !== false ? 'user:' . $accountId : 'unknown:' . mb_strtolower($username, 'UTF-8'));
        // Database-backed limits survive new sessions and concurrent requests.
        $keys = [$accountBucket => 5,
                 hash('sha256', 'ip:' . ($_SERVER['REMOTE_ADDR'] ?? 'unknown')) => 100];
        ksort($keys);
        $pdo->beginTransaction();
        try {
            $now = time();
            foreach ($keys as $key => $limit) {
                $stmt = $pdo->prepare('INSERT INTO login_throttle (bucket, attempts, window_start) VALUES (?, 0, ?) ON DUPLICATE KEY UPDATE bucket = VALUES(bucket)');
                $stmt->execute([$key, $now]);
                $stmt = $pdo->prepare('SELECT attempts, window_start FROM login_throttle WHERE bucket = ? FOR UPDATE');
                $stmt->execute([$key]);
                $bucket = $stmt->fetch();
                if ($now - (int)$bucket['window_start'] >= 900) {
                    $pdo->prepare('UPDATE login_throttle SET attempts = 0, window_start = ? WHERE bucket = ?')->execute([$now, $key]);
                } elseif ((int)$bucket['attempts'] >= $limit) {
                    $pdo->rollBack();
                    header('Retry-After: ' . max(1, 900 - ($now - (int)$bucket['window_start'])));
                    wh_http_error(429, 'Terlalu banyak percobaan login. Coba kembali setelah 15 menit.');
                }
            }
            $stmt = $pdo->prepare('SELECT * FROM users WHERE username = ? LIMIT 1');
            $stmt->execute([$username]);
            $account = $stmt->fetch();
            $stored = $account['password'] ?? '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2uheWG/igi.';
            $valid = password_verify($password, $stored);
            if ($account && $valid && in_array($account['role'], ['admin', 'staff'], true)) {
                if (password_needs_rehash($stored, PASSWORD_BCRYPT, ['cost' => 12])) {
                    $pdo->prepare('UPDATE users SET password = ? WHERE user_id = ?')->execute([password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]), $account['user_id']]);
                }
                $pdo->prepare('DELETE FROM login_throttle WHERE bucket = ?')->execute([$accountBucket]);
                $pdo->commit();
                if ((int)$account['is_first_login'] === 1) {
                    $_SESSION = [];
                    session_regenerate_id(true);
                    $_SESSION['temp_user_id'] = (int)$account['user_id'];
                    $_SESSION['temp_user_name'] = $account['nama_lengkap'];
                    $_SESSION['temp_auth_version'] = (int)$account['auth_version'];
                    $_SESSION['temp_login_at'] = time();
                    wh_csrf_token();
                    wh_redirect('change_password');
                }
                wh_set_login($account);
                wh_redirect('index');
            }
            foreach ($keys as $key => $limit) {
                $pdo->prepare('UPDATE login_throttle SET attempts = attempts + 1 WHERE bucket = ?')->execute([$key]);
            }
            $pdo->commit();
            $error_message = 'Username atau password salah!';
        } catch (Throwable $error) {
            if ($pdo->inTransaction()) { $pdo->rollBack(); }
            throw $error;
        }
    }
    http_response_code(401);
}
