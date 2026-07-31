<?php
session_start();
include 'controllers/query_dashboard.php';
?>

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
                <div class="page-title">Dashboard
                    <p class="text-secondary m-0 mt-1" style="font-size: 14px;">Melihat data statistik transaksi item</p>
                </div>
                <div class="d-flex gap-2">
                    <button onclick="window.location.reload();" class="btn fw-bold text-white shadow-sm" style="background-color: #556ee6; border-radius: 8px; font-size: 14px;">
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
                                <h2 class="fw-bold m-0 text-dark"><?php echo $kpi_pending; ?></h2>
                            </div>
                        </div>
                    </div>  
                    <!-- KPI Transaksi -->  
                    <div class="col-md-4">
                        <div class="kpi-card">
                            <div class="kpi-icon kpi-success"><i class="bi bi-check2-all"></i></div>
                            <div>
                                <div class="text-secondary fw-bold mb-1" style="font-size: 13px; text-transform: uppercase; letter-spacing: 0.5px;">Total Transaksi</div>
                                <h2 class="fw-bold m-0 text-dark"><?php echo $kpi_transaksi; ?> <span style="font-size: 13px; font-weight: 500; color: #64748b;" class="ms-1">Pcs</span></h2>
                            </div>
                        </div>
                    </div>
                    <!-- KPI Return -->
                    <div class="col-md-4">
                        <div class="kpi-card">
                            <div class="kpi-icon kpi-danger"><i class="bi bi-arrow-counterclockwise"></i></div>
                            <div>
                                <div class="text-secondary fw-bold mb-1" style="font-size: 13px; text-transform: uppercase; letter-spacing: 0.5px;">Total Return</div>
                                <h2 class="fw-bold m-0 text-dark"><?php echo $kpi_return; ?></h2>
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
                                <span class="badge bg-light text-success border">Active (Tersedia)</span>
                            </div>
                            
                            <!-- Placeholder Grafik Active Dinamis -->
                            <div class="chart-placeholder">
                                <div class="bar-active" style="height: <?php echo $h_active_baju_pria; ?>%;" title="Kemeja Pria: <?php echo $active_stock['baju_pria']; ?> Pcs"></div>
                                <div class="bar-active" style="height: <?php echo $h_active_cln_pria; ?>%;" title="Celana Pria: <?php echo $active_stock['cln_pria']; ?> Pcs"></div>
                                <div class="bar-active" style="height: <?php echo $h_active_baju_wnt; ?>%;" title="Kemeja Wanita: <?php echo $active_stock['baju_wnt']; ?> Pcs"></div>
                                <div class="bar-active" style="height: <?php echo $h_active_cln_wnt; ?>%;" title="Bawahan Wanita: <?php echo $active_stock['cln_wnt']; ?> Pcs"></div>
                            </div>
                            <div class="d-flex justify-content-around mt-3 text-secondary" style="font-size: 12px; font-weight: 600;">
                                <span>Bj. Pria (<?php echo $active_stock['baju_pria']; ?>)</span>
                                <span>Cln. Pria (<?php echo $active_stock['cln_pria']; ?>)</span>
                                <span>Bj. Wnt (<?php echo $active_stock['baju_wnt']; ?>)</span>
                                <span>Cln. Wnt (<?php echo $active_stock['cln_wnt']; ?>)</span>
                            </div>
                        </div>
                    </div>

                    <!-- Chart Kanan: Stok Inactive -->
                    <div class="col-md-6">
                        <div class="chart-container">
                            <div class="d-flex justify-content-between align-items-center mb-4">
                                <h6 class="fw-bold m-0" style="color: #1e293b;"><i class="me-2 text-secondary"></i>Stok Warehouse Inactive</h6>
                                <span class="badge bg-light text-danger border">Inactive (Nonaktif)</span>
                            </div>
                            
                            <!-- Placeholder Grafik Inactive Dinamis -->
                            <div class="chart-placeholder">
                                <div class="bar-inactive" style="height: <?php echo $h_inactive_baju_pria; ?>%;" title="Kemeja Pria: <?php echo $inactive_stock['baju_pria']; ?> Pcs"></div>
                                <div class="bar-inactive" style="height: <?php echo $h_inactive_cln_pria; ?>%;" title="Celana Pria: <?php echo $inactive_stock['cln_pria']; ?> Pcs"></div>
                                <div class="bar-inactive" style="height: <?php echo $h_inactive_baju_wnt; ?>%;" title="Kemeja Wanita: <?php echo $inactive_stock['baju_wnt']; ?> Pcs"></div>
                                <div class="bar-inactive" style="height: <?php echo $h_inactive_cln_wnt; ?>%;" title="Bawahan Wanita: <?php echo $inactive_stock['cln_wnt']; ?> Pcs"></div>
                            </div>
                            <div class="d-flex justify-content-around mt-3 text-secondary" style="font-size: 12px; font-weight: 600;">
                                <span>Bj. Pria (<?php echo $inactive_stock['baju_pria']; ?>)</span>
                                <span>Cln. Pria (<?php echo $inactive_stock['cln_pria']; ?>)</span>
                                <span>Bj. Wnt (<?php echo $inactive_stock['baju_wnt']; ?>)</span>
                                <span>Cln. Wnt (<?php echo $inactive_stock['cln_wnt']; ?>)</span>
                            </div>
                        </div>
                    </div>

                </div>
                
            </div>
        </main>
        
    </div>
</div>

<!-- Scripts -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="assets/js/scripts.js"></script>
</body>
</html>

<?php 
if (isset($conn)) {
    $conn->close();
}
?>