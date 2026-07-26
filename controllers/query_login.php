<?php
session_start();
require_once __DIR__ . '/../includes/db.php';

if (!isset($conn) && isset($pdo)) {
    $conn = $pdo;
}

if (isset($_SESSION['user_id'])) {
    header("Location: audit_item.php");
    exit;
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
                // A. JIKA MASIH LOGIN PERTAMA KALI (Password belum di-hash)
                if ($user['is_first_login'] == 1) {
                    if ($password === $user['password']) { // Cek plain text
                        // Simpan ID sementara untuk proses ganti password
                        $_SESSION['temp_user_id'] = $user['id'];
                        $_SESSION['temp_user_name'] = $user['nama_lengkap'];
                        
                        header("Location: change_password.php");
                        exit;
                    } else {
                        $error_message = "Username atau password default salah!";
                    }
                } 
                // B. JIKA SUDAH PERNAH GANTI PASSWORD (Password sudah di-hash)
                else {
                    if (password_verify($password, $user['password'])) {
                        $_SESSION['user_id']      = $user['id'];
                        $_SESSION['username']     = $user['username'];
                        $_SESSION['nama_lengkap'] = $user['nama_lengkap'];
                        $_SESSION['role']         = $user['role'];

                        header("Location: index.php");
                        exit;
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