<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pending Request - HR Warehouse</title>
    
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
            <button id="sidebarToggle" class="btn btn-light d-flex align-items-center justify-content-center" style="width: 36px; height: 36px; border-radius: 8px; border: 1px solid #e5e7eb;">
                <i class="bi bi-list fs-5 text-secondary"></i>
            </button>

            <div class="profile-section">
                <div class="dropdown">
                    <div class="user-info" data-bs-toggle="dropdown" aria-expanded="false" style="cursor: pointer;">
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
        <!-- MAIN CONTENT AREA -->
        <main class="content-area p-4">
            <h4 class="fw-bold mb-4" style="color: #4b5563;">Requester</h4>
            
            <!-- Table Container -->
            <div class="p-4" style="background-color: #ffffff; border-radius: 8px; min-height: 400px; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
                
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th scope="col" class="py-3 px-3" style="font-weight: 700; color: #4b5563; width: 15%;">No. Request</th>
                                <th scope="col" class="py-3 px-3" style="font-weight: 700; color: #4b5563; width: 50%;">Detail</th>
                                <th scope="col" class="py-3 px-3" style="font-weight: 700; color: #4b5563; width: 15%;">Status</th>
                                <th scope="col" class="py-3 px-3 text-center" style="font-weight: 700; color: #4b5563; width: 20%;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>

                            <!-- Baris Data 1 -->
                            <tr>
                                <td class="p-3">
                                    <div class="text-dark fw-bold">
                                        #FR-110726
                                    </div>
                                </td>
                                <td class="p-3">
                                    <div class="text-dark fw-bold">
                                        <span class="fw-bold me-1">CV. ABADI JAYA</span> | Cecep Surecep | Ukuran M |15 Nov 2025
                                    </div>
                                </td>
                                <td class="p-3">
                                    <div class="text-dark fw-bold">
                                        Pending
                                    </div>
                                </td>
                                <td class="p-3">
                                    <a href="transaksi.php" class="btn w-100 text-dark fw-bold d-flex align-items-center justify-content-center btn-proses">
                                        Proses <i class="bi bi-arrow-right-circle ms-2"></i>
                                    </a>
                                </td>
                            </tr>

                        </tbody>
                    </table>
                </div>
                
            </div>
        </main>

            </div>
        </main>
        
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="assets/js/scripts.js"></script>
</body>
</html>