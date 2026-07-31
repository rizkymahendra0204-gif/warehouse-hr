<?php
include 'controllers/query_changepassword.php';
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