<?php
// Pastikan session sudah aktif
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 1. Deteksi otomatis lokasi pemanggil (Root vs Subfolder)
$prefix = file_exists('includes/topbar.php') ? '' : '../';

$nama_user   = $_SESSION['nama_lengkap'] ?? $_SESSION['username'] ?? 'User';
$role_user   = $_SESSION['role'] ?? 'Staff';
$foto_user   = $_SESSION['foto_profil'] ?? 'default.png';

// 2. Cek kelayakan dan keberadaan foto profil menggunakan absolute path
$server_foto_path = __DIR__ . '/../assets/img/profile/' . $foto_user;
$path_foto        = $prefix . 'assets/img/profile/' . $foto_user;
$has_foto         = !empty($foto_user) && $foto_user !== 'default.png' && file_exists($server_foto_path);

$initial_user = strtoupper(substr(trim($nama_user), 0, 1));
$current_page = basename($_SERVER['PHP_SELF']); 

// 3. Pastikan koneksi database PDO tersedia
if (!isset($pdo)) {
    if (file_exists(__DIR__ . '/db.php')) {
        include_once __DIR__ . '/db.php';
    }
}

// 4. Query Ambil Data Pending Request
$notif_items = [];
$total_pending = 0;

if (isset($pdo) && $pdo instanceof PDO) {
    try {
        $sql_notif = "SELECT rf.request_id, rf.perusahaan, rf.nama_sa 
                      FROM request_form rf 
                      LEFT JOIN transaksi t ON rf.request_id = t.request_id 
                      WHERE t.request_id IS NULL 
                      ORDER BY rf.request_id DESC LIMIT 5";
        
        $stmt_notif = $pdo->query($sql_notif);
        if ($stmt_notif) {
            $notif_items = $stmt_notif->fetchAll(PDO::FETCH_ASSOC);
        }

        $sql_count = "SELECT COUNT(DISTINCT rf.request_id) as total 
                      FROM request_form rf 
                      LEFT JOIN transaksi t ON rf.request_id = t.request_id 
                      WHERE t.request_id IS NULL";
        
        $stmt_count = $pdo->query($sql_count);
        if ($stmt_count && $row_c = $stmt_count->fetch(PDO::FETCH_ASSOC)) {
            $total_pending = (int)($row_c['total'] ?? 0);
        }
    } catch (PDOException $e) {
        $notif_items = [];
        $total_pending = 0;
    }
}
?>

<header class="topbar">
    
    <!-- Tombol Toggle Sidebar -->
    <button id="sidebarToggle" class="topbar-btn">
        <i class="bi bi-list fs-5 text-secondary"></i>
    </button>

    <div class="profile-section d-flex align-items-center gap-3">
        
        <!-- 1. Notification Dropdown -->
        <div class="dropdown">
            <div class="notification" data-bs-toggle="dropdown" aria-expanded="false" style="cursor: pointer;">
                <i class="bi bi-bell-fill"></i>
                <?php if ($total_pending > 0): ?>
                    <div class="notification-dot"></div>
                <?php endif; ?>
            </div>
            
            <ul class="dropdown-menu dropdown-menu-end notification-dropdown shadow-sm border-0 mt-2">
                <li class="notification-header d-flex justify-content-between align-items-center px-3 py-2 fw-bold text-secondary border-bottom" style="font-size: 13px;">
                    <span>Pending Requests</span>
                    <?php if ($total_pending > 0): ?>
                        <span class="badge bg-danger rounded-pill" style="font-size: 10px;"><?php echo $total_pending; ?> Baru</span>
                    <?php endif; ?>
                </li>
                
                <!-- RENDER DATA DINAMIS PENDING REQUEST -->
                <?php if (!empty($notif_items)): ?>
                    <?php foreach ($notif_items as $notif): ?>
                        <li>
                            <!-- FIX PATH LINK PENDING -->
                            <a class="dropdown-item notification-item py-2 border-bottom" href="<?= $prefix ?>pending.php?req=<?php echo urlencode($notif['request_id']); ?>">
                                <span class="notif-title fw-bold d-block text-dark" style="font-size: 13px;">
                                    <?php echo htmlspecialchars($notif['perusahaan']); ?>
                                </span>
                                <span class="notif-desc text-muted small" style="font-size: 12px;">
                                    #<?php echo htmlspecialchars($notif['request_id']); ?> — SA: <?php echo htmlspecialchars($notif['nama_sa']); ?>
                                </span>
                            </a>
                        </li>
                    <?php endforeach; ?>
                <?php else: ?>
                    <li>
                        <div class="dropdown-item text-center text-muted py-3 small">
                            <i class="bi bi-check-circle me-1 text-success"></i> Tidak ada request pending
                        </div>
                    </li>
                <?php endif; ?>
                
                <li>
                    <!-- FIX PATH LINK LIHAT SEMUA -->
                    <a class="dropdown-item text-center py-2" href="<?= $prefix ?>pending.php" style="color: #556ee6; font-weight: 600; font-size: 13px;">
                        Lihat Semua Request (<?php echo $total_pending; ?>)
                    </a>
                </li>
            </ul>
        </div>

        <!-- 2. Bagian Profil User Dropdown -->
        <div class="dropdown">
            <div class="user-info d-flex align-items-center gap-2" data-bs-toggle="dropdown" aria-expanded="false" style="cursor: pointer;">
                
                <!-- KONDISI TAMPILAN AVATAR / FOTO PROFIL -->
                <?php if ($has_foto): ?>
                    <img src="<?php echo htmlspecialchars($path_foto); ?>" 
                         alt="Foto Profil" 
                         class="rounded-circle shadow-sm border" 
                         style="width: 36px; height: 36px; object-fit: cover;">
                <?php else: ?>
                    <div class="avatar bg-primary text-white rounded-circle d-flex align-items-center justify-content-center fw-bold shadow-sm" style="width: 36px; height: 36px; font-size: 14px; background-color: #556ee6 !important;">
                        <?php echo $initial_user; ?>
                    </div>
                <?php endif; ?>
                
                <!-- Nama & Role User -->
                <div class="d-flex flex-column text-start">
                    <span class="user-name fw-semibold" style="font-size: 14px; line-height: 1.2;">
                        <?php echo htmlspecialchars($nama_user); ?> 
                        <i class="bi bi-chevron-down ms-1" style="font-size: 10px;"></i>
                    </span>
                    <small class="text-muted text-capitalize" style="font-size: 11px;">
                        <?php echo htmlspecialchars($role_user); ?>
                    </small>
                </div>
            </div>
            
            <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0 mt-2">
                <li class="px-3 py-2 border-bottom bg-light">
                    <div class="fw-bold small"><?php echo htmlspecialchars($nama_user); ?></div>
                    <span class="badge bg-soft-primary text-primary text-capitalize" style="font-size: 10px;">
                        <?php echo htmlspecialchars($role_user); ?> Account
                    </span>
                </li>
                <!-- FIX PATH LINK PROFIL & LOGOUT -->
                <li><a class="dropdown-item py-2 mt-1" href="<?= $prefix ?>profile.php"><i class="bi bi-person me-2"></i> Profil Saya</a></li>
                <li><a class="dropdown-item py-2" href="#"><i class="bi bi-gear me-2"></i> Pengaturan</a></li>
                <li><hr class="dropdown-divider my-1"></li>
                <li>
                    <a class="dropdown-item text-danger py-2" href="<?= $prefix ?>logout.php">
                        <i class="bi bi-box-arrow-right me-2"></i> Logout
                    </a>
                </li>
            </ul>
        </div>

    </div>
</header>