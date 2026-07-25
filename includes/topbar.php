<?php
// Pastikan session sudah aktif
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$nama_user   = $_SESSION['nama_lengkap'] ?? $_SESSION['username'] ?? 'User';
$role_user   = $_SESSION['role'] ?? 'Staff';
$initial_user = strtoupper(substr(trim($nama_user), 0, 1));
?>

<?php 
$current_page = basename($_SERVER['PHP_SELF']); 

// 1. Pastikan koneksi database tersedia
if (!isset($conn)) {
    if (file_exists(__DIR__ . '/db.php')) {
        include_once __DIR__ . '/db.php';
    } elseif (file_exists(__DIR__ . '/../includes/db.php')) {
        include_once __DIR__ . '/../includes/db.php';
    }
    
    // Inisialisasi koneksi MySQLi jika belum ada
    if (isset($host, $user, $pass, $db) && !isset($conn)) {
        $conn = @new mysqli($host, $user, $pass, $db);
    }
}

// 2. Query Ambil Data Pending Request (Maksimal 5 data terbaru untuk dropdown)
$notif_items = [];
$total_pending = 0;

if (isset($conn) && $conn instanceof mysqli && !$conn->connect_error) {
    // Ambil request_form yang belum ada di tabel transaksi (Pending)
    $sql_notif = "SELECT rf.request_id, rf.perusahaan, rf.nama_sa 
                  FROM request_form rf 
                  LEFT JOIN transaksi t ON rf.request_id = t.request_id 
                  WHERE t.request_id IS NULL 
                  ORDER BY rf.request_id DESC LIMIT 5";
    
    $res_notif = mysqli_query($conn, $sql_notif);
    if ($res_notif) {
        while ($row_notif = mysqli_fetch_assoc($res_notif)) {
            $notif_items[] = $row_notif;
        }
    }

    // Hitung total seluruh request pending untuk indikator titik/badge
    $sql_count = "SELECT COUNT(DISTINCT rf.request_id) as total 
                  FROM request_form rf 
                  LEFT JOIN transaksi t ON rf.request_id = t.request_id 
                  WHERE t.request_id IS NULL";
    $res_count = mysqli_query($conn, $sql_count);
    if ($res_count && $row_c = mysqli_fetch_assoc($res_count)) {
        $total_pending = (int)$row_c['total'];
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
                <!-- Titik Merah Notifikasi (Hanya muncul jika ada pending request) -->
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
                            <a class="dropdown-item notification-item py-2 border-bottom" href="pending.php?req=<?php echo urlencode($notif['request_id']); ?>">
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
                    <a class="dropdown-item text-center py-2" href="pending.php" style="color: #556ee6; font-weight: 600; font-size: 13px;">
                        Lihat Semua Request (<?php echo $total_pending; ?>)
                    </a>
                </li>
            </ul>
        </div>

        <?php
        // Ambil data user dari Session (dengan fallback aman)
        $nama_user   = $_SESSION['nama_lengkap'] ?? $_SESSION['username'] ?? 'User';
        $role_user   = $_SESSION['role'] ?? 'Staff';

        // Ambil inisial huruf pertama nama untuk avatar
        $initial_user = strtoupper(substr(trim($nama_user), 0, 1));
        ?>

        <!-- 2. Bagian Profil User Dropdown -->
        <div class="dropdown">
            <div class="user-info d-flex align-items-center gap-2" data-bs-toggle="dropdown" aria-expanded="false" style="cursor: pointer;">
                <!-- Avatar Inisial Nama -->
                <div class="avatar bg-primary text-white rounded-circle d-flex align-items-center justify-content-center fw-bold shadow-sm" style="width: 36px; height: 36px; font-size: 14px; background-color: #556ee6 !important;">
                    <?php echo $initial_user; ?>
                </div>
                
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
                <li><a class="dropdown-item py-2 mt-1" href="#"><i class="bi bi-person me-2"></i> Profil Saya</a></li>
                <li><a class="dropdown-item py-2" href="#"><i class="bi bi-gear me-2"></i> Pengaturan</a></li>
                <li><hr class="dropdown-divider my-1"></li>
                <li>
                    <a class="dropdown-item text-danger py-2" href="logout.php">
                        <i class="bi bi-box-arrow-right me-2"></i> Logout
                    </a>
                </li>
            </ul>
        </div>

    </div>
</header>