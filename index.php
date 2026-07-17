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
        
        <?php include 'includes/topbar.php'; ?>

        <!-- MAIN CONTENT AREA -->
        <main class="content-area p-4">
            
            <!-- Header Halaman -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div class="page-title">Dashboard</div>
                <div class="d-flex gap-2">
                    <button class="btn fw-bold text-white shadow-sm" style="background-color: #556ee6; border-radius: 8px; font-size: 14px;">
                        <i class="bi bi-arrow-clockwise me-2"></i>Refresh
                    </button>
                </div>
            </div>
            
            <div class="container-fluid px-0">
                
                <!-- ROW 1: KPI CARDS -->
                <div class="row g-4 mb-4">
                    <!-- KPI Pending -->
                    <div class="col-md-4">
                        <div class="kpi-card">
                            <div class="kpi-icon kpi-pending"><i class="bi bi-hourglass-split"></i></div>
                            <div>
                                <div class="text-secondary fw-bold mb-1" style="font-size: 13px; text-transform: uppercase; letter-spacing: 0.5px;">Pending Request</div>
                                <h2 class="fw-bold m-0 text-dark">15 <span style="font-size: 13px; font-weight: 500; color: #854d0e;" class="ms-1"></span></h2>
                            </div>
                        </div>
                    </div>  
                    <!-- KPI Transaksi -->  
                    <div class="col-md-4">
                        <div class="kpi-card">
                            <div class="kpi-icon kpi-success"><i class="bi bi-check2-all"></i></div>
                            <div>
                                <div class="text-secondary fw-bold mb-1" style="font-size: 13px; text-transform: uppercase; letter-spacing: 0.5px;">Total Transaksi</div>
                                <h2 class="fw-bold m-0 text-dark">124 <span style="font-size: 13px; font-weight: 500; color: #64748b;" class="ms-1"></span></h2>
                            </div>
                        </div>
                    </div>
                    <!-- KPI Return -->
                    <div class="col-md-4">
                        <div class="kpi-card">
                            <div class="kpi-icon kpi-danger"><i class="bi bi-arrow-counterclockwise"></i></div>
                            <div>
                                <div class="text-secondary fw-bold mb-1" style="font-size: 13px; text-transform: uppercase; letter-spacing: 0.5px;">Total Return</div>
                                <h2 class="fw-bold m-0 text-dark">8 <span style="font-size: 13px; font-weight: 500; color: #b91c1c;" class="ms-1"></span></h2>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- ROW 2: CHARTS -->
                <div class="row g-4">
                    
                    <!-- Chart Kiri: Stok Active -->
                    <div class="col-md-6">
                        <div class="chart-container">
                            <div class="d-flex justify-content-between align-items-center mb-4">
                                <h6 class="fw-bold m-0" style="color: #1e293b;"><i class="me-2 text-primary"></i>Stok Warehouse Active</h6>
                                <span class="badge bg-light text-success border">Active</span>
                            </div>
                            
                            <!-- Placeholder Grafik Active -->
                            <div class="chart-placeholder">
                                <div class="bar-active" style="height: 60%;" title="Kemeja Pria: 60%"></div>
                                <div class="bar-active" style="height: 85%;" title="Celana Pria: 85%"></div>
                                <div class="bar-active" style="height: 40%;" title="Kemeja Wanita: 40%"></div>
                                <div class="bar-active" style="height: 70%;" title="Rok Wanita: 70%"></div>
                            </div>
                            <div class="d-flex justify-content-around mt-3 text-secondary" style="font-size: 12px; font-weight: 600;">
                                <span>Kmj. Pria</span>
                                <span>Cln. Pria</span>
                                <span>Kmj. Wnt</span>
                                <span>Rok Wnt</span>
                            </div>
                        </div>
                    </div>

                    <!-- Chart Kanan: Stok Inactive -->
                    <div class="col-md-6">
                        <div class="chart-container">
                            <div class="d-flex justify-content-between align-items-center mb-4">
                                <h6 class="fw-bold m-0" style="color: #1e293b;"><i class="me-2 text-secondary"></i>Stok Warehouse Inactive</h6>
                                <span class="badge bg-light text-danger border">Inactive</span>
                            </div>
                            
                            <!-- Placeholder Grafik Inactive -->
                            <div class="chart-placeholder">
                                <div class="bar-inactive" style="height: 20%;" title="Kemeja Pria: 20%"></div>
                                <div class="bar-inactive" style="height: 10%;" title="Celana Pria: 10%"></div>
                                <div class="bar-inactive" style="height: 35%; background-color: #ef4444;" title="Kemeja Wanita: 35% (Tertinggi)"></div>
                                <div class="bar-inactive" style="height: 15%;" title="Rok Wanita: 15%"></div>
                            </div>
                            <div class="d-flex justify-content-around mt-3 text-secondary" style="font-size: 12px; font-weight: 600;">
                                <span>Kmj. Pria</span>
                                <span>Cln. Pria</span>
                                <span>Kmj. Wnt</span>
                                <span>Rok Wnt</span>
                            </div>
                        </div>
                    </div>

                </div>
                
            </div>
        </main>

        <!-- END CONTENT AREA -->
        
    </div>
</div>

<!-- Scripts dari Bootstrap dan JS -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="assets/js/scripts.js"></script>
</body>
</html>