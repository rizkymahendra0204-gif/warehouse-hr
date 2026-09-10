<?php
require_once __DIR__ . '/includes/auth_check.php';
include 'controllers/query_profile.php';
?>

<!DOCTYPE html>
<html lang="<?php echo $_SESSION['lang'] ?? 'id'; ?>">
<head>
    <meta name="csrf-token" content="<?= wh_escape(wh_csrf_token()) ?>">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $lang['profile_title'] ?? 'Profil Saya' ?> - HR Warehouse</title>

    <link rel="icon" type="image/png" href="assets/img/favicon-icon.png">

    <!-- Bootstrap 5 CSS & Icons -->
    <link href="assets/vendor-ui/bootstrap/bootstrap.min.css" rel="stylesheet">
    <link href="assets/vendor-ui/bootstrap-icons/bootstrap-icons.css" rel="stylesheet">
    
    <link rel="stylesheet" href="assets/css/style.css"> 
</head>
<body class="bg-light">

<div class="d-flex" id="wrapper">

    <!-- 1. PEMANGGILAN SIDEBAR -->
    <?php 
    if (file_exists('includes/sidebar.php')) {
        include 'includes/sidebar.php';
    } elseif (file_exists('sidebar.php')) {
        include 'sidebar.php';
    }
    ?>

    <!-- WRAPPER HALAMAN UTAMA -->
    <div id="page-content-wrapper" class="w-100 flex-grow-1">

        <!-- 2. PEMANGGILAN TOPBAR / HEADER -->
        <?php 
        if (file_exists('includes/topbar.php')) {
            include 'includes/topbar.php';
        } elseif (file_exists('includes/header.php')) {
            include 'includes/header.php';
        } elseif (file_exists('topbar.php')) {
            include 'topbar.php';
        }
        ?>

        <!-- 3. KONTEN UTAMA PROFIL -->
        <div class="container-fluid py-4 px-4">
            
            <!-- Header Halaman -->
            <div class="mb-4">
                <h3 class="fw-bold text-dark"><?= $lang['profile_title'] ?? 'Profil Saya' ?></h3>
                <p class="text-muted"><?= $lang['profile_subtitle'] ?? 'Kelola informasi data diri, foto profil, dan keamanan akun Anda.' ?></p>
            </div>

            <!-- Alert Status -->
            <?php if (!empty($pesan_sukses)): ?>
                <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm mb-4" role="alert">
                    <i class="bi bi-check-circle-fill me-2"></i><?= htmlspecialchars($pesan_sukses) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>

            <?php if (!empty($pesan_error)): ?>
                <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm mb-4" role="alert">
                    <i class="bi bi-exclamation-triangle-fill me-2"></i><?= htmlspecialchars($pesan_error) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>

            <div class="row g-4">
                
                <!-- KOLOM KIRI: Profile Ringkasan -->
                <div class="col-lg-4">
                    <div class="card border-0 shadow-sm text-center p-4">
                        <div class="card-body">
                            <div class="profile-avatar-wrapper mb-3">
                                <?php 
                                    $foto_path = !empty($data_user['foto_profil']) && file_exists('assets/img/profile/' . $data_user['foto_profil']) 
                                                ? 'assets/img/profile/' . $data_user['foto_profil'] 
                                                : 'assets/img/profile/default.png';
                                    
                                    $has_photo = !empty($data_user['foto_profil']) && $data_user['foto_profil'] !== 'default.png' && file_exists('assets/img/profile/' . $data_user['foto_profil']);
                                    $initial = strtoupper(substr(trim($data_user['nama_lengkap'] ?? $data_user['username'] ?? 'U'), 0, 1));
                                ?>

                                <?php if ($has_photo): ?>
                                    <img src="<?= $foto_path ?>" alt="Foto Profil" class="profile-avatar rounded-circle img-thumbnail" style="width: 120px; height: 120px; object-fit: cover;">
                                <?php else: ?>
                                    <div class="avatar bg-primary text-white rounded-circle d-flex align-items-center justify-content-center fw-bold shadow-sm mx-auto" style="width: 120px; height: 120px; font-size: 48px; background-color: #556ee6 !important;">
                                        <?= $initial; ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                            
                            <h5 class="fw-bold mb-1"><?= htmlspecialchars($data_user['nama_lengkap'] ?? 'User HR') ?></h5>
                            <p class="text-muted small mb-2">@<?= htmlspecialchars($data_user['username'] ?? 'username') ?></p>
                            
                            <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-3 py-2 rounded-pill fw-semibold">
                                <i class="bi bi-shield-check me-1"></i><?= htmlspecialchars(strtoupper($data_user['role'] ?? 'Staff')) ?>
                            </span>

                            <hr class="my-4 text-muted">

                            <div class="text-start small text-muted">
                                <div class="mb-2">
                                    <i class="bi bi-calendar3 me-2"></i><?= $lang['lbl_registered_since'] ?? 'Terdaftar sejak:' ?> <?= htmlspecialchars(date('d M Y', strtotime($data_user['created_at'] ?? 'now'))) ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- KOLOM KANAN: Form Tabbed (Data Diri & Ganti Password) -->
                <div class="col-lg-8">
                    <div class="card border-0 shadow-sm">
                        <div class="card-header bg-white border-0 pt-4 px-4 pb-0">
                            <!-- Nav Tabs -->
                            <ul class="nav nav-tabs card-header-tabs" id="profileTab" role="tablist">
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link active py-3" id="edit-tab" data-bs-toggle="tab" data-bs-target="#edit-profile" type="button" role="tab">
                                        <i class="bi bi-person-gear me-2"></i><?= $lang['tab_edit_profile'] ?? 'Edit Data Diri' ?>
                                    </button>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link py-3" id="security-tab" data-bs-toggle="tab" data-bs-target="#change-password" type="button" role="tab">
                                        <i class="bi bi-lock me-2"></i><?= $lang['tab_change_password'] ?? 'Ubah Password' ?>
                                    </button>
                                </li>
                            </ul>
                        </div>

                        <div class="card-body p-4">
                            <div class="tab-content" id="profileTabContent">
                                
                                <!-- TAB 1: FORM EDIT DATA DIRI -->
                                <div class="tab-pane fade show active" id="edit-profile" role="tabpanel">
                                    <form action="" method="POST" enctype="multipart/form-data">
<?= wh_csrf_field() ?>
                                        <div class="row g-3">
                                            <div class="col-md-6">
                                                <label class="form-label fw-semibold"><?= $lang['lbl_username'] ?? 'Username' ?></label>
                                                <input type="text" class="form-control bg-light" value="<?= htmlspecialchars($data_user['username'] ?? '') ?>" readonly disabled>
                                                <div class="form-text"><?= $lang['help_username_readonly'] ?? 'Username tidak dapat diubah.' ?></div>
                                            </div>

                                            <div class="col-md-6">
                                                <label class="form-label fw-semibold"><?= $lang['lbl_role'] ?? 'Role' ?></label>
                                                <input type="text" class="form-control bg-light" value="<?= htmlspecialchars(strtoupper($data_user['role'] ?? '')) ?>" readonly disabled>
                                            </div>

                                            <div class="col-md-12">
                                                <label class="form-label fw-semibold"><?= $lang['lbl_fullname'] ?? 'Nama Lengkap' ?></label>
                                                <input type="text" name="nama_lengkap" class="form-control" value="<?= htmlspecialchars($data_user['nama_lengkap'] ?? '') ?>" required>
                                            </div>

                                            <div class="col-md-12">
                                                <label class="form-label fw-semibold"><?= $lang['lbl_photo'] ?? 'Foto Profil' ?></label>
                                                <input type="file" name="foto" class="form-control" accept="image/*">
                                                <div class="form-text"><?= $lang['help_photo_format'] ?? 'Format yang didukung: JPG, PNG, WEBP (Maksimal 2MB).' ?></div>
                                            </div>

                                            <div class="col-12 mt-4 text-end">
                                                <button type="submit" name="update_profile" class="btn btn-proses-custom px-4">
                                                    <i class="bi bi-save me-1"></i><?= $lang['btn_save_changes'] ?? 'Simpan Perubahan' ?>
                                                </button>
                                            </div>
                                        </div>
                                    </form>
                                </div>

                                <!-- TAB 2: FORM UBAH PASSWORD -->
                                <div class="tab-pane fade" id="change-password" role="tabpanel">
                                    <form action="" method="POST">
<?= wh_csrf_field() ?>
                                        <div class="row g-3">
                                            <div class="col-12">
                                                <label class="form-label fw-semibold"><?= $lang['lbl_current_pass'] ?? 'Password Saat Ini' ?></label>
                                                <input type="password" name="pass_lama" class="form-control" placeholder="<?= htmlspecialchars($lang['plc_old_pass'] ?? 'Masukkan password lama') ?>" required>
                                            </div>

                                            <div class="col-md-6">
                                                <label class="form-label fw-semibold"><?= $lang['lbl_new_pass'] ?? 'Password Baru' ?></label>
                                                <input type="password" name="pass_baru" minlength="12" maxlength="72" class="form-control" placeholder="<?= htmlspecialchars($lang['plc_new_pass'] ?? 'Minimal 12 karakter') ?>" required>
                                            </div>

                                            <div class="col-md-6">
                                                <label class="form-label fw-semibold"><?= $lang['lbl_confirm_pass'] ?? 'Konfirmasi Password Baru' ?></label>
                                                <input type="password" name="konfirmasi_pass" class="form-control" placeholder="<?= htmlspecialchars($lang['plc_confirm_pass'] ?? 'Ulangi password baru') ?>" required>
                                            </div>

                                            <div class="col-12 mt-4 text-end">
                                                <button type="submit" name="update_password" class="btn btn-proses-custom text-white px-4">
                                                    <i class="bi bi-key me-1"></i><?= $lang['btn_update_password'] ?? 'Perbarui Password' ?>
                                                </button>
                                            </div>
                                        </div>
                                    </form>
                                </div>

                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>

    </div>
</div>

<!-- JS Bootstrap Bundle -->
<script src="assets/vendor-ui/jquery/jquery-3.6.0.min.js"></script>
<script src="assets/vendor-ui/bootstrap/bootstrap.bundle.min.js"></script>
<script src="assets/js/scripts.js"></script>

</body>
</html>