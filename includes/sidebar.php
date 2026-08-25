<?php 
// Mengambil nama file tanpa ekstensi .php untuk penentuan status active
$current_page = pathinfo(basename($_SERVER['PHP_SELF']), PATHINFO_FILENAME); 

// Otomatis deteksi lokasi file yang membuka sidebar (Root vs Subfolder)
$prefix = file_exists('includes/sidebar.php') ? '' : '../';
?>

<aside class="sidebar bg-white border-end" id="sidebar">
    <!-- Skrip Inline Pencegah Flicker saat Halaman Di-refresh -->
    <script>
        if (localStorage.getItem("sidebar_collapsed") === "true") {
            document.getElementById("sidebar").classList.add("collapsed");
        }
    </script>

    <div class="sidebar-header d-flex align-items-center justify-content-center">
        <a href="<?= $prefix ?>index" class="sidebar-brand text-decoration-none">
            <!-- Logo Panjang (Tampil saat sidebar terbuka) -->
            <img src="<?= $prefix ?>assets/img/Logo.png" alt="Logo" class="logo-full">
            
            <!-- Logo Ikon / Favicon (Tampil saat sidebar dikecilkan) -->
            <img src="<?= $prefix ?>assets/img/favicon-icon.png" alt="Icon" class="logo-icon">
        </a>
    </div>
    
    <div class="sidebar-menu">
        <div class="menu-category">MAIN</div>
        
        <!-- 1. Dashboard -->
        <a href="<?= $prefix ?>index" class="nav-link <?= ($current_page == 'index' || $current_page == '') ? 'active' : '' ?>">
            <i class="bi bi-bar-chart-line-fill"></i>
            <span><?= $lang['menu_dashboard'] ?? 'Dashboard' ?></span>
        </a>
        
        <!-- 2. Pending -->
        <a href="<?= $prefix ?>pending" class="nav-link <?= ($current_page == 'pending') ? 'active' : '' ?>">
            <i class="bi bi-cart3"></i>
            <span><?= $lang['menu_pending'] ?? 'Pending' ?></span>
        </a>
        
        <!-- 3. Return -->
        <a href="<?= $prefix ?>return" class="nav-link <?= ($current_page == 'return') ? 'active' : '' ?>">
            <i class="bi bi-arrow-counterclockwise"></i>
            <span><?= $lang['menu_return'] ?? 'Return' ?></span>
        </a>

        <!-- 4. Laporan -->
        <a href="<?= $prefix ?>laporan" class="nav-link <?= ($current_page == 'laporan' || $current_page == 'reports') ? 'active' : '' ?>">
            <i class="bi bi-journal-check"></i>
            <span><?= $lang['menu_laporan'] ?? 'Laporan' ?></span>
        </a> 
        
        <!-- 5. Transaksi -->
        <a href="<?= $prefix ?>transaksi" class="nav-link <?= ($current_page == 'transaksi') ? 'active' : '' ?>">
            <i class="bi bi-file-earmark-text"></i>
            <span><?= $lang['menu_transaksi'] ?? 'Transaksi' ?></span>
        </a>

        <div class="menu-category">WAREHOUSE & INVENTORY</div>
        
        <!-- 6. Generate Barcode -->
        <a href="<?= $prefix ?>generate_barcode" class="nav-link <?= ($current_page == 'generate_barcode') ? 'active' : '' ?>">
            <i class="bi bi-upc-scan"></i>
            <span><?= $lang['menu_generate_barcode'] ?? 'Generate Barcode' ?></span>
        </a>
        
        <!-- 7. Warehouse Management / Manajemen Gudang -->
        <a href="<?= $prefix ?>warehouse_management" class="nav-link <?= ($current_page == 'warehouse_management' || $current_page == 'audit_item') ? 'active' : '' ?>">
            <i class="bi bi-boxes"></i>
            <span><?= $lang['menu_closing'] ?? 'Manajemen Gudang' ?></span>
        </a>

        <!-- 8. Inventory (Stok Barang) -->
        <a href="<?= $prefix ?>stok_barang" class="nav-link <?= ($current_page == 'stok_barang') ? 'active' : '' ?>">
            <i class="bi bi-box-seam"></i>
            <span><?= $lang['menu_inventory'] ?? 'Inventory' ?></span>
        </a>

        <div class="menu-category">OTHERS</div>
        
        <!-- 9. Activity Log -->
        <a href="<?= $prefix ?>log_activity" class="nav-link <?= ($current_page == 'log_activity') ? 'active' : '' ?>">
            <i class="bi bi-ui-checks"></i>
            <span><?= $lang['menu_activity_log'] ?? 'Activity Log' ?></span>
        </a>

    </div>
</aside>