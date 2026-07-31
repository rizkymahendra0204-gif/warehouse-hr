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
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    if (!empty($username) && !empty($password)) {
        try {
            $stmt = $conn->prepare("SELECT * FROM users WHERE username = :username LIMIT 1");
            $stmt->execute([':username' => $username]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($user) {
                // 1. Jika ini login pertama kali
                if ($user['is_first_login'] == 1) {
                    if ($password === $user['password']) { 
                        $_SESSION['temp_user_id']   = $user['user_id'];
                        $_SESSION['temp_user_name'] = $user['nama_lengkap'];
                        
                        session_write_close();
                        header("Location: change_password.php");
                        exit();
                    } else {
                        $error_message = "Username atau password default salah!";
                    }
                } 
                // 2. Jika login normal (bukan login pertama)
                else {
                    if (password_verify($password, $user['password'])) {
                        // DIPERBAIKI: Menggunakan $user['user_id'] sesuai nama kolom di database
                        $_SESSION['user_id']      = $user['user_id'];
                        $_SESSION['username']     = $user['username'];
                        $_SESSION['nama_lengkap'] = $user['nama_lengkap'];
                        $_SESSION['role']         = $user['role'];

                        session_write_close();
                        header("Location: index.php");
                        exit();
                    } else {
                        $error_message = "Username atau password salah!";
                    }
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