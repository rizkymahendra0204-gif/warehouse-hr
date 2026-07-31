<?php
session_start();
require_once __DIR__ . '/../includes/db.php';   

if (!isset($conn) && isset($pdo)) {
    $conn = $pdo;
}

// Keamanan: Cegah akses langsung tanpa melalui alur login pertama
if (!isset($_SESSION['temp_user_id'])) {
    header("Location: login.php");
    exit;
}

$error_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $new_password     = trim($_POST['new_password']);
    $confirm_password = trim($_POST['confirm_password']);

    if (empty($new_password) || empty($confirm_password)) {
        $error_message = "Semua kolom password wajib diisi.";
    } elseif (strlen($new_password) < 6) {
        $error_message = "Password minimal harus 6 karakter.";
    } elseif ($new_password !== $confirm_password) {
        $error_message = "Konfirmasi password tidak cocok!";
    } else {
        try {
            // Hash password baru
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
            $user_id = $_SESSION['temp_user_id'];

            // Update password & ubah status is_first_login menjadi 0
            $stmt = $conn->prepare("UPDATE users 
                                    SET password = :pass, is_first_login = 0 
                                    WHERE user_id = :id");
            $stmt->execute([
                ':pass' => $hashed_password,
                ':id'   => $user_id
            ]);

            // Ambil data user lengkap untuk otomatis menyelesaikan proses login
            $stmt_user = $conn->prepare("SELECT * FROM users WHERE user_id = :id LIMIT 1");
            $stmt_user->execute([':id' => $user_id]);
            $user = $stmt_user->fetch(PDO::FETCH_ASSOC);

            // Set Session Login Utama
            $_SESSION['user_id']      = $user['id_user'];
            $_SESSION['username']     = $user['username'];
            $_SESSION['nama_lengkap'] = $user['nama_lengkap'];
            $_SESSION['role']         = $user['role'];

            // Hapus session sementara
            unset($_SESSION['temp_user_id']);
            unset($_SESSION['temp_user_name']);

            $_SESSION['alert_message'] = "Password berhasil diperbarui! Selamat datang di sistem.";
            $_SESSION['alert_type']    = "success";

            // Pastikan session tersimpan penuh sebelum redirect
            session_write_close();

            header("Location: index.php");
            exit;

        } catch (PDOException $e) {
            $error_message = "Gagal memperbarui password: " . $e->getMessage();
        }
    }
}
?>