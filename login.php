<?php
include 'controllers/query_login.php';
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - WAREHOUSE-HR</title>

    <link rel="icon" type="image/png" href="assets/img/favicon-icon.png">
    
    <!-- Bootstrap & Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    
    <!-- CSS Utama -->
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="login-page">

<div class="login-card">
    <!-- Header Logo / Judul Sistem -->
    <div class="text-center mb-9">
            <img src="assets/img/Logo_Login.png" alt="Logo" class="img-fluid">
        <!-- <h4 class="fw-bold text-dark m-0">WAREHOUSE-HR</h4>
        <p class="text-secondary small mt-1">Silakan login untuk mengakses sistem</p> -->
    </div>

    <!-- Alert error jika login gagal -->
    <?php if (!empty($error_message)): ?>
        <div class="alert alert-danger alert-dismissible fade show py-2 px-3 small rounded-3 mb-3" role="alert">
            <i class="bi bi-exclamation-triangle-fill me-1"></i> <?php echo $error_message; ?>
            <button type="button" class="btn-close py-2" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <!-- Alert Notifikasi Session -->
    <?php if (isset($_SESSION['alert_message'])): ?>
        <div class="alert alert-<?php echo $_SESSION['alert_type']; ?> alert-dismissible fade show py-2 px-3 small rounded-3 mb-3" role="alert">
            <?php 
                echo $_SESSION['alert_message']; 
                unset($_SESSION['alert_message']);
                unset($_SESSION['alert_type']);
            ?>
            <button type="button" class="btn-close py-2" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <!-- Form Login -->
    <form method="POST" action="">
        <div class="mb-3">
            <label class="form-label small fw-semibold text-secondary">Username</label>
            <div class="input-group">
                <span class="input-group-text bg-light border-end-0 text-secondary"><i class="bi bi-person"></i></span>
                <input type="text" name="username" class="form-control border-start-0 bg-light" placeholder="Masukkan username" required autofocus>
            </div>
        </div>

        <div class="mb-4">
            <label class="form-label small fw-semibold text-secondary">Password</label>
            <div class="input-group">
                <span class="input-group-text bg-light border-end-0 text-secondary"><i class="bi bi-key"></i></span>
                <input type="password" name="password" class="form-control border-start-0 bg-light" placeholder="Masukkan password" required>
            </div>
        </div>

        <button type="submit" class="btn btn-login btn-primary w-100 shadow-sm">
            <i class="bi bi-box-arrow-in-right me-1"></i> Masuk Sistem
        </button>
    </form>
</div>

<!-- Scripts -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>