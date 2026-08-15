<?php
require_once __DIR__ . '/includes/auth_check.php';
include 'includes/db.php';

// Pastikan koneksi PDO tersedia
if (!isset($conn) && isset($pdo)) {
    $conn = $pdo;
}

// 1. VALIDASI LOGIN
if (!isset($_SESSION['username'])) {
    header('Location: login.php');
    exit;
}

$is_admin = isset($_SESSION['role']) && strtolower($_SESSION['role']) === 'admin';

// Atur tab aktif (User biasa langsung ke tab notifikasi)
$active_tab = $_GET['tab'] ?? ($is_admin ? 'users' : 'notif');

// 2. PENANGANAN FORM NOTIFIKASI (POST HANDLER)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_notifications'])) {
    $notif_request = isset($_POST['notif_request']) ? 1 : 0;
    $notif_return  = isset($_POST['notif_return'])  ? 1 : 0;
    $notif_stock   = isset($_POST['notif_stock'])   ? 1 : 0;
    $username_curr = $_SESSION['username'] ?? '';

    try {
        $stmt_notif = $conn->prepare("UPDATE users SET 
            notif_request = :notif_request, 
            notif_return  = :notif_return, 
            notif_stock   = :notif_stock 
            WHERE username = :uname");

        $stmt_notif->execute([
            ':notif_request' => $notif_request,
            ':notif_return'  => $notif_return,
            ':notif_stock'   => $notif_stock,
            ':uname'         => $username_curr
        ]);

        $_SESSION['alert_message'] = $lang['alert_notif_success'] ?? "Pengaturan notifikasi berhasil diperbarui!";
        $_SESSION['alert_type']    = "success";
    } catch (PDOException $e) {
        $_SESSION['notif_settings'] = [
            'request' => $notif_request,
            'return'  => $notif_return,
            'stock'   => $notif_stock
        ];
        $_SESSION['alert_message'] = $lang['alert_notif_session'] ?? "Pengaturan notifikasi disimpan (Session Mode)!";
        $_SESSION['alert_type']    = "success";
    }

    header("Location: setting.php?tab=notif");
    exit;
}

// 3. FETCH DAFTAR USER (KHUSUS ADMIN)
$list_users = [];
if ($is_admin) {
    try {
        $stmt = $conn->query("SELECT * FROM users ORDER BY username ASC");
        $list_users = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        $_SESSION['alert_message'] = "Error mengambil data user: " . $e->getMessage();
        $_SESSION['alert_type'] = "danger";
    }
}

// 4. FETCH DATA USER AKTIF
$current_username = $_SESSION['username'] ?? '';
$stmt_curr = $conn->prepare("SELECT * FROM users WHERE username = :uname LIMIT 1");
$stmt_curr->execute([':uname' => $current_username]);
$data_user = $stmt_curr->fetch(PDO::FETCH_ASSOC) ?: [];
?>
<!DOCTYPE html>
<html lang="<?php echo $_SESSION['lang'] ?? 'id'; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $lang['setting_title'] ?? 'Pengaturan Sistem' ?> - HR Warehouse</title>

    <link rel="icon" type="image/png" href="assets/img/favicon-icon.png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="bg-light">

<div class="app-container">
    <?php include 'includes/sidebar.php'; ?>
    
    <div class="main-wrapper">
        <?php include 'includes/topbar.php'; ?>
        
        <main class="content-area p-4">
            
            <!-- Header Halaman -->
            <div class="mb-4">
                <h4 class="fw-bold mb-1"><?= $lang['setting_title'] ?? 'Pengaturan Sistem' ?></h4>
                <p class="text-secondary m-0" style="font-size: 14px;"><?= $lang['setting_subtitle'] ?? 'Kelola akun pengguna operasional dan konfigurasi notifikasi sistem.' ?></p>
            </div>

            <!-- Notifikasi Alert -->
            <?php if (isset($_SESSION['alert_message'])): ?>
                <div class="alert alert-<?= $_SESSION['alert_type']; ?> alert-dismissible fade show shadow-sm mb-4" role="alert">
                    <i class="bi bi-info-circle-fill me-2"></i> <?= $_SESSION['alert_message']; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
                <?php unset($_SESSION['alert_message'], $_SESSION['alert_type']); ?>
            <?php endif; ?>

            <div class="row g-4">
                
                <!-- KOLOM KIRI: Profile Ringkasan -->
                <div class="col-lg-4">
                    <div class="card border-0 shadow-sm text-center p-4 rounded-3 bg-white">
                        <div class="card-body p-0">
                            <div class="profile-avatar-wrapper mb-3">
                                <?php 
                                    $foto_user = $data_user['foto_profil'] ?? 'default.png';
                                    $has_photo = !empty($foto_user) && $foto_user !== 'default.png' && file_exists('assets/img/profile/' . $foto_user);
                                    $nama_display = $data_user['nama_lengkap'] ?? $data_user['nama'] ?? $_SESSION['username'] ?? 'User';
                                    $initial = strtoupper(substr(trim($nama_display), 0, 1));
                                ?>

                                <?php if ($has_photo): ?>
                                    <img src="assets/img/profile/<?= $foto_user ?>" alt="Foto Profil" class="profile-avatar rounded-circle img-thumbnail" style="width: 110px; height: 110px; object-fit: cover;">
                                <?php else: ?>
                                    <div class="avatar bg-primary text-white rounded-circle d-flex align-items-center justify-content-center fw-bold shadow-sm mx-auto" style="width: 110px; height: 110px; font-size: 44px; background-color: #556ee6 !important;">
                                        <?= $initial; ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                            
                            <h5 class="fw-bold mb-1"><?= htmlspecialchars($nama_display) ?></h5>
                            <p class="text-muted small mb-2">@<?= htmlspecialchars($data_user['username'] ?? $_SESSION['username'] ?? 'user') ?></p>
                            
                            <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-3 py-2 rounded-pill fw-semibold">
                                <i class="bi bi-shield-check me-1"></i><?= htmlspecialchars(strtoupper($data_user['role'] ?? $_SESSION['role'] ?? 'USER')) ?>
                            </span>

                            <hr class="my-4 text-muted opacity-25">

                            <div class="text-start small text-muted">
                                <div class="mb-2">
                                    <i class="bi bi-gear-wide-connected me-2"></i>Status: <span class="text-success fw-bold"><?= $lang['lbl_status_active'] ?? 'Aktif' ?></span>
                                </div>
                                <div class="mb-2">
                                    <i class="bi bi-shield-lock me-2"></i>Akses: <?= $is_admin ? ($lang['access_full'] ?? 'Penuh (Administrator)') : ($lang['access_restricted'] ?? 'Terbatas (Staff Operasional)'); ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- KOLOM KANAN: Form Tabbed (Manajemen User & Notifikasi) -->
                <div class="col-lg-8">
                    <div class="card border-0 shadow-sm rounded-3 bg-white">
                        <div class="card-header bg-white border-0 pt-3 px-4 pb-0">
                            <ul class="nav nav-tabs card-header-tabs" id="settingTab" role="tablist">
                                <?php if ($is_admin): ?>
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link <?= $active_tab !== 'notif' ? 'active' : '' ?> py-3" id="users-tab" data-bs-toggle="tab" data-bs-target="#user-management" type="button" role="tab">
                                        <i class="bi bi-people-fill me-2"></i><?= $lang['tab_user_management'] ?? 'Manajemen Pengguna' ?>
                                    </button>
                                </li>
                                <?php endif; ?>

                                <li class="nav-item" role="presentation">
                                    <button class="nav-link <?= ($active_tab === 'notif' || !$is_admin) ? 'active' : '' ?> py-3" id="notif-tab" data-bs-toggle="tab" data-bs-target="#notifications" type="button" role="tab">
                                        <i class="bi bi-bell-fill me-2"></i><?= $lang['tab_notifications'] ?? 'Pengaturan Notifikasi' ?>
                                    </button>
                                </li>
                            </ul>
                        </div>

                        <div class="card-body p-4">
                            <div class="tab-content" id="settingTabContent">
                                
                                <?php if ($is_admin): ?>
                                <!-- TAB 1: MANAJEMEN PENGGUNA (KHUSUS ADMIN) -->
                                <div class="tab-pane fade <?= $active_tab !== 'notif' ? 'show active' : '' ?>" id="user-management" role="tabpanel">
                                    <div class="d-flex justify-content-between align-items-center mb-3">
                                        <h6 class="fw-bold m-0 text-dark"><?= $lang['title_user_list'] ?? 'Daftar Akun Pengguna' ?></h6>
                                        <button type="button" class="btn btn-biru-solid btn-sm fw-bold shadow-sm px-3 py-2" onclick="openModalTambahUser()">
                                            <i class="bi bi-person-plus-fill me-1"></i> <?= $lang['btn_add_user'] ?? 'Tambah User Baru' ?>
                                        </button>
                                    </div>

                                    <div class="table-responsive">
                                        <table class="table table-hover align-middle m-0" style="font-size: 13px;">
                                            <thead class="table-light text-secondary small border-bottom">
                                                <tr>
                                                    <th class="text-center py-2" style="width: 50px;"><?= $lang['table_no'] ?? 'NO' ?></th>
                                                    <th class="py-2">USERNAME</th>
                                                    <th class="py-2">NAMA LENGKAP</th>
                                                    <th class="text-center py-2">ROLE</th>
                                                    <th class="text-center py-2" style="width: 100px;"><?= $lang['table_action'] ?? 'AKSI' ?></th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php if (!empty($list_users)): ?>
                                                    <?php $no = 1; foreach ($list_users as $u): ?>
                                                        <?php 
                                                            $username = $u['username'] ?? '-';
                                                            $nama     = $u['nama_lengkap'] ?? $u['nama'] ?? $u['name'] ?? '-';
                                                            $role     = strtolower($u['role'] ?? 'user');
                                                            $is_current_user = ($username === ($_SESSION['username'] ?? ''));
                                                        ?>
                                                        <tr>
                                                            <td class="text-center fw-bold"><?= $no++ ?></td>
                                                            <td class="fw-bold text-primary"><?= htmlspecialchars($username) ?></td>
                                                            <td><?= htmlspecialchars($nama) ?></td>
                                                            <td class="text-center">
                                                                <?php if ($role === 'admin'): ?>
                                                                    <span class="badge bg-primary px-2 py-1"><i class="bi bi-shield-lock-fill me-1"></i> <?= $lang['badge_admin'] ?? 'Administrator' ?></span>
                                                                <?php else: ?>
                                                                    <span class="badge bg-secondary px-2 py-1"><i class="bi bi-person-fill me-1"></i> <?= $lang['badge_user'] ?? 'User Biasa' ?></span>
                                                                <?php endif; ?>
                                                            </td>
                                                            <td class="text-center">
                                                                <?php if ($is_current_user): ?>
                                                                    <span class="badge bg-light text-muted border px-2 py-1 small"><?= $lang['badge_you'] ?? 'Akun Anda' ?></span>
                                                                <?php else: ?>
                                                                    <button type="button" class="btn btn-outline-danger btn-sm fw-bold px-2 py-1" onclick="konfirmasiHapus('<?= htmlspecialchars($username) ?>')">
                                                                        <i class="bi bi-trash3-fill"></i>
                                                                    </button>
                                                                <?php endif; ?>
                                                            </td>
                                                        </tr>
                                                    <?php endforeach; ?>
                                                <?php else: ?>
                                                    <tr>
                                                        <td colspan="5" class="text-center py-4 text-muted"><?= $lang['no_user_data'] ?? 'Belum ada data pengguna.' ?></td>
                                                    </tr>
                                                <?php endif; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                                <?php endif; ?>

                                <!-- TAB 2: PENGATURAN NOTIFIKASI -->
                                <div class="tab-pane fade <?= ($active_tab === 'notif' || !$is_admin) ? 'show active' : '' ?>" id="notifications" role="tabpanel">
                                    <form action="setting.php" method="POST">
                                        <div class="row g-3">
                                            <div class="col-12">
                                                <p class="text-muted small mb-3"><?= $lang['notif_desc'] ?? 'Atur pemberitahuan sistem yang ingin Anda aktifkan untuk akun operasional ini.' ?></p>
                                            </div>

                                            <?php 
                                                $req_checked   = ($data_user['notif_request'] ?? $_SESSION['notif_settings']['request'] ?? 1) == 1 ? 'checked' : '';
                                                $ret_checked   = ($data_user['notif_return']  ?? $_SESSION['notif_settings']['return']  ?? 1) == 1 ? 'checked' : '';
                                                $stk_checked   = ($data_user['notif_stock']   ?? $_SESSION['notif_settings']['stock']   ?? 0) == 1 ? 'checked' : '';
                                            ?>

                                            <div class="col-12">
                                                <div class="card p-3 border rounded-3 mb-3 bg-light">
                                                    <div class="form-check form-switch">
                                                        <input class="form-check-input" type="checkbox" id="notifRequest" name="notif_request" <?= $req_checked ?>>
                                                        <label class="form-check-label fw-bold" for="notifRequest"><?= $lang['notif_req_title'] ?? 'Notifikasi Permintaan (Request) Baru' ?></label>
                                                        <div class="form-text small"><?= $lang['notif_req_desc'] ?? 'Menampilkan titik merah & badge pada bell topbar saat ada pengajuan barang baru.' ?></div>
                                                    </div>
                                                </div>

                                                <div class="card p-3 border rounded-3 mb-3 bg-light">
                                                    <div class="form-check form-switch">
                                                        <input class="form-check-input" type="checkbox" id="notifReturn" name="notif_return" <?= $ret_checked ?>>
                                                        <label class="form-check-label fw-bold" for="notifReturn"><?= $lang['notif_ret_title'] ?? 'Notifikasi Pengembalian (Return)' ?></label>
                                                        <div class="form-text small"><?= $lang['notif_ret_desc'] ?? 'Pemberitahuan otomatis ketika transaksi barang retur diterima oleh sistem.' ?></div>
                                                    </div>
                                                </div>

                                                <div class="card p-3 border rounded-3 mb-3 bg-light">
                                                    <div class="form-check form-switch">
                                                        <input class="form-check-input" type="checkbox" id="notifStock" name="notif_stock" <?= $stk_checked ?>>
                                                        <label class="form-check-label fw-bold" for="notifStock"><?= $lang['notif_stk_title'] ?? 'Peringatan Stok Menipis (Low Stock Alert)' ?></label>
                                                        <div class="form-text small"><?= $lang['notif_stk_desc'] ?? 'Memberikan alert sistem jika jumlah stok master item berada di bawah 5 pcs.' ?></div>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="col-12 mt-4 text-end">
                                                <button type="submit" name="save_notifications" class="btn btn-biru-solid fw-bold px-4 py-2">
                                                    <i class="bi bi-check-circle me-1"></i> <?= $lang['btn_save_notif'] ?? 'Simpan Pengaturan Notifikasi' ?>
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
        </main>
    </div>
</div>

<?php if ($is_admin): ?>
<!-- Modal 1: Tambah User (Admin Only) -->
<div class="modal fade" id="modalTambahUser" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <form action="controllers/user_controller.php" method="POST">
                <input type="hidden" name="action" value="tambah_user">
                
                <div class="modal-header">
                    <h5 class="modal-title fw-bold"><i class="bi bi-person-plus me-2"></i><?= $lang['modal_user_title'] ?? 'Buat Akun Pengguna' ?></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-bold small text-secondary"><?= $lang['lbl_fullname'] ?? 'Nama Lengkap' ?></label>
                        <input type="text" name="nama_lengkap" class="form-control" placeholder="<?= htmlspecialchars($lang['plc_fullname'] ?? 'Masukkan nama lengkap') ?>" required autocomplete="off">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold small text-secondary"><?= $lang['lbl_username'] ?? 'Username' ?></label>
                        <input type="text" name="username" class="form-control" placeholder="<?= htmlspecialchars($lang['plc_username'] ?? 'Masukkan username') ?>" required autocomplete="off">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold small text-secondary"><?= $lang['lbl_password'] ?? 'Password' ?></label>
                        <input type="password" name="password" class="form-control" placeholder="<?= htmlspecialchars($lang['plc_password'] ?? 'Masukkan password') ?>" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold small text-secondary"><?= $lang['lbl_role'] ?? 'Role / Hak Akses' ?></label>
                        <select name="role" class="form-select" required>
                            <option value="user" selected><?= $lang['opt_role_user'] ?? 'User Biasa (Staff Operasional)' ?></option>
                            <option value="admin"><?= $lang['opt_role_admin'] ?? 'Administrator' ?></option>
                        </select>
                    </div>
                </div>
                
                <div class="modal-footer">
                    <button type="button" class="btn btn-light border fw-bold" data-bs-dismiss="modal"><?= $lang['btn_cancel'] ?? 'Batal' ?></button>
                    <button type="submit" class="btn btn-biru-solid fw-bold px-4"><?= $lang['btn_save_user'] ?? 'Simpan User' ?></button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal 2: Konfirmasi Hapus User (Admin Only) -->
<div class="modal fade" id="modalHapusUser" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content border-0 shadow">
            <form action="controllers/user_controller.php" method="POST">
                <input type="hidden" name="action" value="hapus_user">
                <input type="hidden" name="username" id="hapus_username_input">

                <div class="modal-body text-center p-4">
                    <i class="bi bi-exclamation-triangle-fill text-danger fs-1 mb-2 d-block"></i>
                    <h6 class="fw-bold mb-2"><?= $lang['modal_del_user_title'] ?? 'Hapus Pengguna?' ?></h6>
                    <p class="text-muted small mb-0"><?= $lang['modal_del_user_desc'] ?? 'Apakah Anda yakin ingin menghapus user' ?> <strong id="hapus_username_label" class="text-dark"></strong>? <?= $lang['modal_del_user_warn'] ?? 'Aksi ini tidak dapat dibatalkan.' ?></p>
                </div>
                <div class="modal-footer justify-content-center border-0 pt-0 pb-4">
                    <button type="button" class="btn btn-light border px-3" data-bs-dismiss="modal"><?= $lang['btn_cancel'] ?? 'Batal' ?></button>
                    <button type="submit" class="btn btn-danger fw-bold px-3"><?= $lang['btn_confirm_delete'] ?? 'Ya, Hapus' ?></button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- SCRIPTS -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="assets/js/scripts.js?v=<?= time(); ?>"></script>

</body>
</html>