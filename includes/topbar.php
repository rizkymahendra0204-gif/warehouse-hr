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
$current_page = pathinfo(basename($_SERVER['PHP_SELF']), PATHINFO_FILENAME); 

// 3. Pastikan koneksi database PDO tersedia
if (!isset($pdo)) {
    if (file_exists(__DIR__ . '/db.php')) {
        include_once __DIR__ . '/db.php';
    }
}

// 4. LOGIKA PENTING: Jaga parameter URL aktif saat ganti bahasa
$queryParams = $_GET;

$queryParams['lang'] = 'id';
$url_lang_id = '?' . http_build_query($queryParams);

$queryParams['lang'] = 'en';
$url_lang_en = '?' . http_build_query($queryParams);

// 5. AMBIL PREFERENSI NOTIFIKASI USER (INTEGRASI SETTING)
$user_notif_req   = 1;
$user_notif_ret   = 1;
$user_notif_stock = 0;

if (isset($pdo) && !empty($_SESSION['username'])) {
    try {
        $stmt_u = $pdo->prepare("SELECT notif_request, notif_return, notif_stock FROM users WHERE username = ?");
        $stmt_u->execute([$_SESSION['username']]);
        if ($u_setting = $stmt_u->fetch(PDO::FETCH_ASSOC)) {
            $user_notif_req   = (int)($u_setting['notif_request'] ?? 1);
            $user_notif_ret   = (int)($u_setting['notif_return'] ?? 1);
            $user_notif_stock = (int)($u_setting['notif_stock'] ?? 0);
        }
    } catch (PDOException $e) {
    error_log('[Warehouse HR] ' . $e);}
}

// Override dari session mode jika ada
if (isset($_SESSION['notif_settings'])) {
    $user_notif_req   = $_SESSION['notif_settings']['request'] ?? $user_notif_req;
    $user_notif_ret   = $_SESSION['notif_settings']['return']  ?? $user_notif_ret;
    $user_notif_stock = $_SESSION['notif_settings']['stock']   ?? $user_notif_stock;
}

// 6. QUERY DATA NOTIFIKASI SESUAI STATUS SWITCH ACTIVE
$notif_items = [];
$total_notif = 0;

if (isset($pdo) && $pdo instanceof PDO) {
    try {
        // A. Notifikasi Request Baru (Jika Switch Request ON)
        if ($user_notif_req == 1) {
            $sql_notif = "SELECT rf.request_id, rf.perusahaan, rf.nama_sa 
                          FROM request_form rf 
                          LEFT JOIN transaksi t ON rf.request_id = t.request_id 
                          WHERE t.request_id IS NULL 
                          ORDER BY rf.request_id DESC LIMIT 5";
            $stmt_notif = $pdo->query($sql_notif);
            if ($stmt_notif) {
                while ($row = $stmt_notif->fetch(PDO::FETCH_ASSOC)) {
                    $notif_items[] = [
                        'type'     => 'request',
                        'title'    => htmlspecialchars($row['perusahaan']),
                        'desc'     => '#' . htmlspecialchars($row['request_id']) . ' — SA: ' . htmlspecialchars($row['nama_sa']),
                        'link'     => $prefix . 'pending?req=' . urlencode($row['request_id']),
                        'badge'    => 'Request',
                        'badge_bg' => 'bg-danger'
                    ];
                }
            }

            $sql_count = "SELECT COUNT(DISTINCT rf.request_id) as total 
                          FROM request_form rf 
                          LEFT JOIN transaksi t ON rf.request_id = t.request_id 
                          WHERE t.request_id IS NULL";
            $stmt_count = $pdo->query($sql_count);
            if ($stmt_count && $row_c = $stmt_count->fetch(PDO::FETCH_ASSOC)) {
                $total_notif += (int)($row_c['total'] ?? 0);
            }
        }

        // B. Notifikasi Return (Jika Switch Return ON)
        if ($user_notif_ret == 1) {
            $sql_ret = "SELECT transaction_id, tgl_return FROM return_items ORDER BY tgl_return DESC LIMIT 3";
            $stmt_ret = $pdo->query($sql_ret);
            if ($stmt_ret) {
                while ($row = $stmt_ret->fetch(PDO::FETCH_ASSOC)) {
                    $notif_items[] = [
                        'type'     => 'return',
                        'title'    => 'Barang Retur Diterima',
                        'desc'     => 'Transaksi #' . htmlspecialchars($row['transaction_id']),
                        'link'     => $prefix . 'return',
                        'badge'    => 'Return',
                        'badge_bg' => 'bg-warning text-dark'
                    ];
                }
            }
        }

        // C. Alert Stok Menipis (Jika Switch Low Stock ON)
        if ($user_notif_stock == 1) {
            $sql_stk = "SELECT tipe, gender, size, COUNT(*) as sisa 
                        FROM master_item 
                        WHERE status_transaksi = 'Available' 
                        GROUP BY tipe, gender, size 
                        HAVING sisa < 5 LIMIT 3";
            $stmt_stk = $pdo->query($sql_stk);
            if ($stmt_stk) {
                while ($row = $stmt_stk->fetch(PDO::FETCH_ASSOC)) {
                    $notif_items[] = [
                        'type'     => 'stock',
                        'title'    => 'Stok Menipis (' . $row['sisa'] . ' Pcs)',
                        'desc'     => wh_escape($row['tipe'] . ' ' . $row['gender'] . ' Size ' . $row['size']),
                        'link'     => $prefix . 'stok_barang',
                        'badge'    => 'Stok',
                        'badge_bg' => 'bg-danger'
                    ];
                    $total_notif++;
                }
            }
        }

    } catch (PDOException $e) {
    error_log('[Warehouse HR] ' . $e);
        $notif_items = [];
        $total_notif = 0;
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
                <?php if ($total_notif > 0): ?>
                    <div class="notification-dot"></div>
                <?php endif; ?>
            </div>
            
            <ul class="dropdown-menu dropdown-menu-end notification-dropdown shadow-sm border-0 mt-2" style="min-width: 280px;">
                <li class="notification-header d-flex justify-content-between align-items-center px-3 py-2 fw-bold text-secondary border-bottom" style="font-size: 13px;">
                    <span>Pemberitahuan</span>
                    <?php if ($total_notif > 0): ?>
                        <span class="badge bg-danger rounded-pill" style="font-size: 10px;"><?php echo $total_notif; ?> Baru</span>
                    <?php endif; ?>
                </li>
                
                <!-- RENDER DATA DINAMIS NOTIFIKASI -->
                <?php if (!empty($notif_items)): ?>
                    <?php foreach ($notif_items as $notif): ?>
                        <li>
                            <a class="dropdown-item notification-item py-2 border-bottom" href="<?= $notif['link']; ?>">
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <span class="notif-title fw-bold text-dark" style="font-size: 13px;">
                                        <?php echo $notif['title']; ?>
                                    </span>
                                    <span class="badge <?php echo $notif['badge_bg']; ?>" style="font-size: 9px;"><?php echo $notif['badge']; ?></span>
                                </div>
                                <span class="notif-desc text-muted small d-block" style="font-size: 12px;">
                                    <?php echo $notif['desc']; ?>
                                </span>
                            </a>
                        </li>
                    <?php endforeach; ?>
                <?php else: ?>
                    <li>
                        <div class="dropdown-item text-center text-muted py-3 small">
                            <i class="bi bi-check-circle me-1 text-success"></i> Tidak ada pemberitahuan
                        </div>
                    </li>
                <?php endif; ?>
                
                <li>
                    <a class="dropdown-item text-center py-2" href="<?= $prefix ?>pending" style="color: #556ee6; font-weight: 600; font-size: 13px;">
                        Lihat Semua Request (<?php echo $total_notif; ?>)
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
                        <?php echo wh_escape($initial_user); ?>
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
                <li><a class="dropdown-item py-2 mt-1" href="<?= $prefix ?>profile"><i class="bi bi-person me-2"></i> Profil Saya</a></li>
                <li><a class="dropdown-item py-2 mt-1" href="<?= $prefix ?>setting"><i class="bi bi-gear me-2"></i> Pengaturan</a></li>
                <li><hr class="dropdown-divider my-1"></li>
                <li>
                    <form method="POST" action="<?= wh_escape(wh_url('logout')) ?>"><?= wh_csrf_field() ?><button type="submit" class="dropdown-item text-danger py-2">
                        <i class="bi bi-box-arrow-right me-2"></i> Logout
                    </button></form>
                </li>
            </ul>
        </div>

    <script>
        // Passing PHP language array to Javascript Global Object
        window.I18N = <?php echo json_encode($lang ?? []); ?>;

        // Helper Function untuk Translate di JS + mengganti placeholder dinamis {var}
        function t(key, params = {}) {
            let text = (window.I18N && window.I18N[key]) ? window.I18N[key] : key;
            for (let [paramKey, value] of Object.entries(params)) {
                text = text.replace(new RegExp(`{${paramKey}}`, 'g'), value);
            }
            return text;
        }
    </script>

    </div>
</header>