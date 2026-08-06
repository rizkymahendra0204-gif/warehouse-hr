<?php 
$current_page = basename($_SERVER['PHP_SELF']); 

// Otomatis deteksi lokasi file yang membuka sidebar (Root vs Subfolder)
$prefix = file_exists('includes/sidebar.php') ? '' : '../';

$is_collapsed = ($_COOKIE['sidebar_collapsed'] ?? 'true') === 'true';
$sidebar_class = $is_collapsed ? 'collapsed' : '';
?>

<aside class="sidebar <?= isset($sidebar_class) ? $sidebar_class : '' ?> ">
    <div class="sidebar-header d-flex align-items-center gap-2">
        <!-- FIX PATH LOGO -->
        <img src="<?= $prefix ?>assets/img/Logo.png" alt="Logo" style="width: 200px; height: 32px; object-fit: contain;">
    </div>
    
    <div class="sidebar-menu">
        <div class="menu-category">MAIN</div>
        <a href="<?= $prefix ?>index.php" class="nav-link <?= ($current_page == 'index.php' || $current_page == '') ? 'active' : '' ?>">
            <i class="bi bi-bar-chart-line-fill"></i>
            <span>Dashboard</span>
        </a>
        <a href="<?= $prefix ?>pending.php" class="nav-link <?= ($current_page == 'pending.php') ? 'active' : '' ?>">
            <i class="bi bi-cart3"></i>
            <span>Pending</span>
        </a>
        <a href="<?= $prefix ?>transaksi.php" class="nav-link <?= ($current_page == 'transaksi.php') ? 'active' : '' ?>">
            <i class="bi bi-file-earmark-text"></i>
            <span>Transaksi</span>
        </a>
        <a href="<?= $prefix ?>return.php" class="nav-link <?= ($current_page == 'return.php') ? 'active' : '' ?>">
            <i class="bi bi-chat-left-dots"></i>
            <span>Return</span>
        </a>

        <div class="menu-category">INVENTORY</div>
        <a href="<?= $prefix ?>stok_barang.php" class="nav-link <?= ($current_page == 'stok_barang.php') ? 'active' : '' ?>">
            <i class="bi bi-gear"></i>
            <span>Stok Barang</span>
        </a>
        <a href="<?= $prefix ?>generate_barcode.php" class="nav-link <?= ($current_page == 'generate_barcode.php') ? 'active' : '' ?>">
            <i class="bi bi-upc-scan"></i>
            <span>Generate Barcode</span>
        </a>

        <div class="menu-category">OTHERS</div>
        <!-- FIX PATH LAPORAN & ACTIVE CHECK -->
        <a href="<?= $prefix ?>laporan.php" class="nav-link <?= ($current_page == 'laporan.php') ? 'active' : '' ?>">
            <i class="bi bi-journal-check"></i>
            <span>Laporan</span>
        </a> 
        <a href="<?= $prefix ?>log_activity.php" class="nav-link <?= ($current_page == 'log_activity.php') ? 'active' : '' ?>">
            <i class="bi bi-ui-checks"></i>
            <span>Log Activity</span>
        </a>
        <a href="<?= $prefix ?>audit_item.php" class="nav-link <?= ($current_page == 'audit_item.php') ? 'active' : '' ?>">
            <i class="bi bi-patch-exclamation-fill"></i>
            <span>Closing</span>
        </a>
    </div>
</aside>