<?php
session_start();
require_once __DIR__ . '/includes/db.php';

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
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - WAREHOUSE-HR</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body { background-color: #f4f6f9; }
        .login-card { max-width: 400px; border-radius: 12px; }
    </style>
</head>
<div class="container d-flex justify-content-center align-items-center vh-100">
    <div class="card login-card shadow-lg p-4 w-100 border-0">
        <div class="text-center mb-4">
            <h3 class="fw-bold text-primary mb-1"><i class="bi bi-box-seam me-2"></i>WAREHOUSE-HR</h3>
            <p class="text-muted small">Silakan login untuk mengakses sistem</p>
        </div>

        <?php if (!empty($error_message)): ?>
            <div class="alert alert-danger alert-dismissible fade show small mb-3" role="alert">
                <i class="bi bi-exclamation-triangle-fill me-1"></i> <?php echo $error_message; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <?php if (isset($_SESSION['alert_message'])): ?>
            <div class="alert alert-<?php echo $_SESSION['alert_type']; ?> alert-dismissible fade show small mb-3" role="alert">
                <?php 
                    echo $_SESSION['alert_message']; 
                    unset($_SESSION['alert_message']);
                    unset($_SESSION['alert_type']);
                ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <form method="POST" action="">
            <div class="mb-3">
                <label class="form-label fw-semibold small">Username</label>
                <div class="input-group">
                    <span class="input-group-text bg-light"><i class="bi bi-person"></i></span>
                    <input type="text" name="username" class="form-control" placeholder="Masukkan username" required autofocus>
                </div>
            </div>

            <div class="mb-4">
                <label class="form-label fw-semibold small">Password</label>
                <div class="input-group">
                    <span class="input-group-text bg-light"><i class="bi bi-key"></i></span>
                    <input type="password" name="password" class="form-control" placeholder="Masukkan password" required>
                </div>
            </div>

            <button type="submit" class="btn btn-primary w-100 fw-bold py-2" style="background-color: #556ee6;">
                <i class="bi bi-box-arrow-in-right me-1"></i> Masuk Sistem
            </button>
        </form>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>