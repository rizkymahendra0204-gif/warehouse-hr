<?php 
$current_page = basename($_SERVER['PHP_SELF']); 

// Otomatis deteksi lokasi file yang membuka sidebar (Root vs Subfolder)
$prefix = file_exists('includes/sidebar.php') ? '' : '../';

$is_collapsed = ($_COOKIE['sidebar_collapsed'] ?? 'false') === '';
$sidebar_class = $is_collapsed ? 'collapsed' : 'false';
?>

<aside class="sidebar <?= isset($sidebar_class) ? $sidebar_class : '' ?> ">
    <div class="sidebar-header d-flex align-items-center justify-content-center">
    <a href="<?= $prefix ?>index.php" class="sidebar-brand text-decoration-none">
        <!-- Logo Panjang (Tampil saat sidebar terbuka) -->
        <img src="<?= $prefix ?>assets/img/Logo.png" alt="Logo" class="logo-full">
        
        <!-- Logo Ikon / Favicon (Tampil saat sidebar dikecilkan) -->
        <img src="<?= $prefix ?>assets/img/favicon-icon.png" alt="Icon" class="logo-icon">
    </a>
</div>
    
    <div class="sidebar-menu">
        <div class="menu-category">MAIN</div>
        
        <!-- 1. Dashboard -->
        <a href="<?= $prefix ?>index.php" class="nav-link <?= ($current_page == 'index.php' || $current_page == '') ? 'active' : '' ?>">
            <i class="bi bi-bar-chart-line-fill"></i>
            <span><?= $lang['menu_dashboard'] ?? 'Dashboard' ?></span>
        </a>
        
        <!-- 2. Pending -->
        <a href="<?= $prefix ?>pending.php" class="nav-link <?= ($current_page == 'pending.php') ? 'active' : '' ?>">
            <i class="bi bi-cart3"></i>
            <span><?= $lang['menu_pending'] ?? 'Pending' ?></span>
        </a>
        
        <!-- 3. Return -->
        <a href="<?= $prefix ?>return.php" class="nav-link <?= ($current_page == 'return.php') ? 'active' : '' ?>">
            <i class="bi bi-arrow-counterclockwise"></i>
            <span><?= $lang['menu_return'] ?? 'Return' ?></span>
        </a>

        <!-- 4. Laporan -->
        <a href="<?= $prefix ?>laporan.php" class="nav-link <?= ($current_page == 'laporan.php' || $current_page == 'reports.php') ? 'active' : '' ?>">
            <i class="bi bi-journal-check"></i>
            <span><?= $lang['menu_laporan'] ?? 'Laporan' ?></span>
        </a> 
        
        <!-- 5. Transaksi -->
        <a href="<?= $prefix ?>transaksi.php" class="nav-link <?= ($current_page == 'transaksi.php') ? 'active' : '' ?>">
            <i class="bi bi-file-earmark-text"></i>
            <span><?= $lang['menu_transaksi'] ?? 'Transaksi' ?></span>
        </a>

        <div class="menu-category">WAREHOUSE & INVENTORY</div>
        
        <!-- 6. Generate Barcode -->
        <a href="<?= $prefix ?>generate_barcode.php" class="nav-link <?= ($current_page == 'generate_barcode.php') ? 'active' : '' ?>">
            <i class="bi bi-upc-scan"></i>
            <span><?= $lang['menu_generate_barcode'] ?? 'Generate Barcode' ?></span>
        </a>
        
        <!-- 7. Warehouse Management (Pengganti Internal Audit) -->
        <a href="<?= $prefix ?>warehouse_management.php" class="nav-link <?= ($current_page == 'warehouse_management.php' || $current_page == 'audit_item.php') ? 'active' : '' ?>">
            <i class="bi bi-boxes"></i>
            <span><?= $lang['whm_title'] ?? 'Manajemen Gudang' ?></span>
        </a>

        <!-- 8. Inventory (Stok Barang) -->
        <a href="<?= $prefix ?>stok_barang.php" class="nav-link <?= ($current_page == 'stok_barang.php') ? 'active' : '' ?>">
            <i class="bi bi-box-seam"></i>
            <span><?= $lang['menu_inventory'] ?? 'Inventory' ?></span>
        </a>

        <div class="menu-category">OTHERS</div>
        
        <!-- 9. Activity Log -->
        <a href="<?= $prefix ?>log_activity.php" class="nav-link <?= ($current_page == 'log_activity.php') ? 'active' : '' ?>">
            <i class="bi bi-ui-checks"></i>
            <span><?= $lang['menu_activity_log'] ?? 'Activity Log' ?></span>
        </a>
    </div>
</aside>