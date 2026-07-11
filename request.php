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
            <div class="page-title">Request</div>
            
            <div class="container-fluid px-0">
                
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