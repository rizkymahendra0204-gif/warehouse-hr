<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../includes/db.php';

if (!isset($conn) && isset($pdo)) {
    $conn = $pdo;
}

$error_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if (!empty($username) && !empty($password)) {
        try {
            $stmt = $conn->prepare("SELECT * FROM users WHERE username = :username LIMIT 1");
            $stmt->execute([':username' => $username]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($user) {
                // Fungsi pembantu verifikasi password
                $is_password_correct = password_verify($password, $user['password']) 
                                    || ($password === $user['password']) 
                                    || (md5($password) === $user['password']);

                if ($is_password_correct) {
                    $is_first = isset($user['is_first_login']) ? (int)$user['is_first_login'] : 0;

                    if ($is_first === 1) {
                        $_SESSION['temp_user_id']   = $user['user_id'] ?? $user['id'] ?? $user['username'];
                        $_SESSION['temp_user_name'] = $user['nama_lengkap'] ?? $user['username'];
                        
                        session_write_close();
                        header("Location: change_password");
                        exit();
                    } else {
                        // 1. Data Sesi Utama User
                        $_SESSION['user_id']      = $user['user_id'] ?? $user['id'] ?? $user['username'];
                        $_SESSION['username']     = $user['username'];
                        $_SESSION['nama_lengkap'] = $user['nama_lengkap'] ?? $user['username'];
                        $_SESSION['role']         = strtolower($user['role'] ?? 'user');
                        $_SESSION['foto_profil']  = $user['foto_profil'] ?? 'default.png';

                        // 2. Load Preferensi Bahasa dari DB (Default 'en')
                        $_SESSION['lang'] = !empty($user['lang']) ? $user['lang'] : 'en';

                        // 3. Load Pengaturan Notifikasi dari DB
                        $_SESSION['notif_settings'] = [
                            'request' => (int)($user['notif_request'] ?? 1),
                            'return'  => (int)($user['notif_return']  ?? 1),
                            'stock'   => (int)($user['notif_stock']   ?? 1)
                        ];

                        session_write_close();
                        header("Location: index");
                        exit();
                    }
                } else {
                    $error_message = "Username atau password salah!";
                }
            } else {
                $error_message = "Username tidak ditemukan!";
            }
        } catch (PDOException $e) {
            $error_message = "Error Database: " . $e->getMessage();
        }
    } else {
        $error_message = "Harap isi username dan password.";
    }
}
?>