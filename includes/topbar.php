<?php 
$current_page = basename($_SERVER['PHP_SELF']); 
?>

<header class="topbar">
    
    <!-- Tombol Toggle Sidebar -->
    <button id="sidebarToggle" class="topbar-btn">
        <i class="bi bi-list fs-5 text-secondary"></i>
    </button>

    <div class="profile-section">
                <!-- 1. Notification Dropdown -->
                <div class="dropdown">
                    <div class="notification" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="bi bi-bell-fill"></i>
                        <div class="notification-dot"></div>
                    </div>
                    
                    <ul class="dropdown-menu dropdown-menu-end notification-dropdown">
                        <li class="notification-header">Pending Requests</li>
                        
                        <!-- Contoh Data Dummy Request -->
                        <li>
                            <a class="dropdown-item notification-item" href="pending.php">
                                <span class="notif-title">PT.CENTRAL JAYA</span>
                                <span class="notif-desc">#FR-110726 - Seragam SA Pria - Size L</span>
                            </a>
                        </li>
                        
                        <li><hr class="dropdown-divider m-0"></li>
                        <li><a class="dropdown-item text-center py-3" href="pending.php" style="color: #556ee6; font-weight: 600;">Lihat Semua Request</a></li>
                    </ul>
                </div>

    <!-- Bagian Profil & Dropdown -->
    <div class="profile-section">
        <div class="dropdown">
            <div class="user-info" data-bs-toggle="dropdown" aria-expanded="false" style="cursor: pointer;">
                <div class="avatar">🍔</div>
                <span class="user-name">Delicious Burger <i class="bi bi-chevron-down ms-1" style="font-size: 10px;"></i></span>
            </div>
            
            <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0 mt-2">
                <li><a class="dropdown-item py-2" href="#"><i class="bi bi-person me-2"></i> Profil Saya</a></li>
                <li><a class="dropdown-item py-2" href="#"><i class="bi bi-gear me-2"></i> Pengaturan</a></li>
                <li><hr class="dropdown-divider"></li>
                <li><a class="dropdown-item text-danger py-2" href="logout.php"><i class="bi bi-box-arrow-right me-2"></i> Logout</a></li>
            </ul>
        </div>
    </div>
</header>