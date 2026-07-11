<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - HR Warehouse</title>
    
    <!-- Bootstrap & Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    
    <!-- Memanggil file CSS Anda -->
    <link rel="stylesheet" href="assets/css/style.css">
    
</head>
<body>

<div class="app-container">

<!-- Memanggil file Sidebar -->
    <?php include 'includes/sidebar.php'; ?>

    <!-- MAIN CONTENT -->
    <div class="main-wrapper">
        

        <!-- TOPBAR -->
        <header class="topbar" style="justify-content: space-between;">
            <!-- Tombol Tutup/Buka Sidebar -->
            <button id="sidebarToggle" class="btn btn-light d-flex align-items-center justify-content-center" style="width: 36px; height: 36px; border-radius: 8px; border: 1px solid #e5e7eb;">
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
                            <a class="dropdown-item notification-item" href="request.php">
                                <span class="notif-title">Andi (IT Dept)</span>
                                <span class="notif-desc">Meminta: Seragam Batik - Size L</span>
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item notification-item" href="request.php">
                                <span class="notif-title">Budi (Marketing)</span>
                                <span class="notif-desc">Meminta: Polo Shirt - Size M</span>
                            </a>
                        </li>
                        
                        <li><hr class="dropdown-divider m-0"></li>
                        <li><a class="dropdown-item text-center py-3" href="request.php" style="color: #556ee6; font-weight: 600;">Lihat Semua Request</a></li>
                    </ul>
                </div>

                <!-- 2. User Profile Dropdown -->
                <div class="dropdown">
                    <div class="user-info" data-bs-toggle="dropdown" aria-expanded="false">
                        <div class="avatar">🍔</div>
                        <span class="user-name">Delicious Burger <i class="bi bi-chevron-down ms-1" style="font-size: 10px;"></i></span>
                    </div>
                    
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li><a class="dropdown-item" href="#"><i class="bi bi-person me-2"></i> Profil Saya</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item text-danger" href="logout.php"><i class="bi bi-box-arrow-right me-2"></i> Logout</a></li>
                    </ul>
                </div>
            </div>
        </header>
    

        <!-- MAIN CONTENT AREA -->

        <main class="content-area">
            <div class="page-title">Dashboard</div>
            
            <div class="container-fluid px-0">
                <div class="row row-gap">
                <div class="col-md-4">
                    <div class="card p-3 shadow-sm border-0">
                        <div class="text-secondary fw-bold" style="font-size: 12px;">PENDING</div>
                        <h2 class="mt-2 text-warning">15</h2>
                    </div>
                </div>    
                <div class="col-md-4">
                    <div class="card p-3 shadow-sm border-0">
                        <div class="text-secondary fw-bold" style="font-size: 12px;">TOTAL TRANSAKSI</div>
                        <h2 class="mt-2">124</h2>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card p-3 shadow-sm border-0">
                        <div class="text-secondary fw-bold" style="font-size: 12px;">TOTAL RETURN</div>
                        <h2 class="mt-2 text-danger">8</h2>
                    </div>
                </div>
            </div>

            <div class="row mt-4">
                <!-- Kolom Kiri -->
                <div class="col-md-6">
                    <div class="card p-4 shadow-sm border-0" style="min-height: 300px;">
                        <div class="fw-bold mb-3">Stok Warehouse Active</div>
                        <div class="mockup-placeholder" style="height: 250px; background-color: #f3f4f6;">
                            <!-- Chart Kiri -->
                        </div>
                    </div>
                </div>

                <!-- Kolom Kanan -->
                <div class="col-md-6">
                    <div class="card p-4 shadow-sm border-0" style="min-height: 300px;">
                        <div class="fw-bold mb-3">Stok Warehouse Inactive</div>
                        <div class="mockup-placeholder" style="height: 250px; background-color: #f3f4f6;">
                            <!-- Chart Kanan -->
                        </div>
                    </div>
                </div>
            </div>
                
            </div>
        </main>

        <!-- END CONTENT AREA -->
        
    </div>
</div>

<!-- Scripts dari Bootstrap dan file JS Anda -->
<!-- 1. JQuery (Wajib paling atas di antara script lainnya) -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<!-- 2. Bootstrap Bundle (Untuk Dropdown) -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<script src="assets/js/scripts.js"></script>
</body>
</html>