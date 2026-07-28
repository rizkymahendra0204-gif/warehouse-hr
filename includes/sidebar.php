<?php 
$current_page = basename($_SERVER['PHP_SELF']); 
?>

<aside class="sidebar">
    <div class="sidebar-header">
        <div class="logo-icon">H</div>
        <span class="logo-text">HR WAREHOUSE</span>
    </div>
    
    <div class="sidebar-menu">
        <div class="menu-category">MAIN</div>
        <a href="index.php" class="nav-link <?= ($current_page == 'index.php' || $current_page == '') ? 'active' : '' ?>">
            <i class="bi bi-bar-chart-line-fill"></i>
            <span>Dashboard</span>
        </a>
        <a href="pending.php" class="nav-link <?= ($current_page == 'pending.php') ? 'active' : '' ?>">
            <i class="bi bi-cart3"></i>
            <span>Pending</span>
        </a>
        <a href="transaksi.php" class="nav-link <?= ($current_page == 'transaksi.php') ? 'active' : '' ?>">
            <i class="bi bi-file-earmark-text"></i>
            <span>Transaksi</span>
        </a>
        <a href="return.php" class="nav-link <?= ($current_page == 'return.php') ? 'active' : '' ?>">
            <i class="bi bi-chat-left-dots"></i>
            <span>Return</span>
        </a>

        <div class="menu-category">INVENTORY</div>
        <a href="stok_barang.php" class="nav-link <?= ($current_page == 'stok_barang.php') ? 'active' : '' ?>">
            <i class="bi bi-gear"></i>
            <span>Stok Barang</span>
        </a>
        <a href="generate_barcode.php" class="nav-link <?= ($current_page == 'generate_barcode.php') ? 'active' : '' ?>">
            <i class="bi bi-upc-scan"></i>
            <span>Generate Barcode</span>
        </a>

        <!-- <div class="menu-category">OTHERS</div>
        <a href="laporan.php" class="nav-link <?= ($current_page == 'laporan.php') ? 'active' : '' ?>">
            <i class="bi bi-journal-check"></i>
            <span>Laporan</span>
        </a>
        <a href="log_activity.php" class="nav-link <?= ($current_page == 'log_activity.php') ? 'active' : '' ?>">
            <i class="bi bi-ui-checks"></i>
            <span>Log Activity</span>
        </a>
        <a href="audit_item.php" class="nav-link <?= ($current_page == 'audit_item.php') ? 'active' : '' ?>">
            <i class="bi bi-patch-exclamation-fill"></i>
            <span>Change Status</span>
        </a> -->
    </div>
</aside>