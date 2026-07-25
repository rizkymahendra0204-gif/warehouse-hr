<?php
session_start();
require_once __DIR__ . '/includes/db.php';

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
                                    WHERE id = :id");
            $stmt->execute([
                ':pass' => $hashed_password,
                ':id'   => $user_id
            ]);

            // Ambil data user lengkap untuk otomatis menyelesaikan proses login
            $stmt_user = $conn->prepare("SELECT * FROM users WHERE id = :id LIMIT 1");
            $stmt_user->execute([':id' => $user_id]);
            $user = $stmt_user->fetch(PDO::FETCH_ASSOC);

            // Set Session Login Utama
            $_SESSION['user_id']      = $user['id'];
            $_SESSION['username']     = $user['username'];
            $_SESSION['nama_lengkap'] = $user['nama_lengkap'];
            $_SESSION['role']         = $user['role'];

            // Hapus session sementara
            unset($_SESSION['temp_user_id']);
            unset($_SESSION['temp_user_name']);

            $_SESSION['alert_message'] = "Password berhasil diperbarui! Selamat datang di sistem.";
            $_SESSION['alert_type']    = "success";

            header("Location: index.php");
            exit;

        } catch (PDOException $e) {
            $error_message = "Gagal memperbarui password: " . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Aktivasi Akun - Buat Password Baru</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body { background-color: #f4f6f9; }
        .setup-card { max-width: 450px; border-radius: 12px; }
    </style>
</head>
<body>
<div class="container d-flex justify-content-center align-items-center vh-100">
    <div class="card setup-card shadow-lg p-4 w-100 border-0">
        <div class="text-center mb-4">
            <h4 class="fw-bold text-primary mb-1"><i class="bi bi-shield-lock-fill me-2"></i>Aktivasi Akun</h4>
            <p class="text-muted small">Halo <b><?php echo htmlspecialchars($_SESSION['temp_user_name']); ?></b>, ini login pertama Anda. Silakan buat password baru untuk melanjutkan.</p>
        </div>

        <?php if (!empty($error_message)): ?>
            <div class="alert alert-danger alert-dismissible fade show small mb-3" role="alert">
                <i class="bi bi-exclamation-triangle-fill me-1"></i> <?php echo $error_message; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <form method="POST" action="">
            <div class="mb-3">
                <label class="form-label fw-semibold small">Password Baru</label>
                <input type="password" name="new_password" class="form-control" placeholder="Minimal 6 karakter" required autofocus>
            </div>

            <div class="mb-4">
                <label class="form-label fw-semibold small">Konfirmasi Password Baru</label>
                <input type="password" name="confirm_password" class="form-control" placeholder="Ulangi password baru" required>
            </div>

            <button type="submit" class="btn btn-primary w-100 fw-bold py-2" style="background-color: #556ee6;">
                <i class="bi bi-check-circle me-1"></i> Simpan Password & Masuk
            </button>
        </form>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>