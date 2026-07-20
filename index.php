<?php
// dashboard.php
require_once 'includes/db.php';
$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    die("Koneksi Database Gagal: " . $conn->connect_error);
}

// ==========================================
// 1. QUERY MENGHITUNG DATA KPI CARDS
// ==========================================

// KPI 1: Pending Request (Menghitung baris di tabel request_form)
$kpi_pending = 0;
$res_pending = $conn->query("SELECT COUNT(*) as total FROM request_form");
if ($res_pending) {
    $row = $res_pending->fetch_assoc();
    $kpi_pending = (int)$row['total'];
}

// KPI 2: Total Transaksi (Menghitung jumlah total item/barcode unik yang sudah didistribusikan)
$kpi_transaksi = 0;
$res_transaksi = $conn->query("SELECT COUNT(*) as total FROM transaksi");
if ($res_transaksi) {
    $row = $res_transaksi->fetch_assoc();
    $kpi_transaksi = (int)$row['total'];
}

// KPI 3: Total Return (Silakan sesuaikan nama tabel 'transaksi_return' jika berbeda di database Anda)
$kpi_return = 0;
$res_return = $conn->query("SELECT COUNT(*) as total FROM transaksi_return");
if ($res_return) {
    $row = $res_return->fetch_assoc();
    $kpi_return = (int)$row['total'];
}


// ==========================================
// 2. QUERY GRAFIK STOK WAREHOUSE ACTIVE (Status: Tersedia)
// ==========================================
$active_stock = ['baju_pria' => 0, 'cln_pria' => 0, 'baju_wnt' => 0, 'cln_wnt' => 0];
$sql_active = "SELECT 
    SUM(CASE WHEN tipe = 'Baju' AND gender = 'Pria' THEN 1 ELSE 0 END) as baju_pria,
    SUM(CASE WHEN tipe = 'Celana' AND gender = 'Pria' THEN 1 ELSE 0 END) as cln_pria,
    SUM(CASE WHEN tipe = 'Baju' AND gender = 'Wanita' THEN 1 ELSE 0 END) as baju_wnt,
    SUM(CASE WHEN tipe = 'Celana' AND gender = 'Wanita' THEN 1 ELSE 0 END) as cln_wnt
    FROM master_item WHERE status_barang = 'active'";

$res_active = $conn->query($sql_active);
if ($res_active) {
    $row = $res_active->fetch_assoc();
    $active_stock['baju_pria'] = (int)($row['baju_pria'] ?? 0);
    $active_stock['cln_pria'] = (int)($row['cln_pria'] ?? 0);
    $active_stock['baju_wnt'] = (int)($row['baju_wnt'] ?? 0);
    $active_stock['cln_wnt'] = (int)($row['cln_wnt'] ?? 0);
}

// Hitung persentase tinggi grafik Active secara proporsional
$max_active = max(1, max($active_stock));
$h_active_baju_pria = ($active_stock['baju_pria'] / $max_active) * 100;
$h_active_cln_pria = ($active_stock['cln_pria'] / $max_active) * 100;
$h_active_baju_wnt  = ($active_stock['baju_wnt'] / $max_active) * 100;
$h_active_cln_wnt  = ($active_stock['cln_wnt'] / $max_active) * 100;


// ==========================================
// 3. QUERY GRAFIK STOK WAREHOUSE INACTIVE (Status: Nonaktif)
// ==========================================
$inactive_stock = ['baju_pria' => 0, 'cln_pria' => 0, 'baju_wnt' => 0, 'cln_wnt' => 0];
$sql_inactive = "SELECT 
    SUM(CASE WHEN tipe = 'Baju' AND gender = 'Pria' THEN 1 ELSE 0 END) as baju_pria,
    SUM(CASE WHEN tipe = 'Celana' AND gender = 'Pria' THEN 1 ELSE 0 END) as cln_pria,
    SUM(CASE WHEN tipe = 'Baju' AND gender = 'Wanita' THEN 1 ELSE 0 END) as baju_wnt,
    SUM(CASE WHEN (tipe = 'Celana' OR tipe = 'Rok') AND gender = 'Wanita' THEN 1 ELSE 0 END) as cln_wnt
    FROM master_item WHERE status = 'Nonaktif'";

$res_inactive = $conn->query($sql_inactive);
if ($res_inactive) {
    $row = $res_inactive->fetch_assoc();
    $inactive_stock['baju_pria'] = (int)($row['baju_pria'] ?? 0);
    $inactive_stock['cln_pria'] = (int)($row['cln_pria'] ?? 0);
    $inactive_stock['baju_wnt'] = (int)($row['baju_wnt'] ?? 0);
    $inactive_stock['cln_wnt'] = (int)($row['cln_wnt'] ?? 0);
}

// Hitung persentase tinggi grafik Inactive secara proporsional
$max_inactive = max(1, max($inactive_stock));
$h_inactive_baju_pria = ($inactive_stock['baju_pria'] / $max_inactive) * 100;
$h_inactive_cln_pria = ($inactive_stock['cln_pria'] / $max_inactive) * 100;
$h_inactive_baju_wnt  = ($inactive_stock['baju_wnt'] / $max_inactive) * 100;
$h_inactive_cln_wnt  = ($inactive_stock['cln_wnt'] / $max_inactive) * 100;
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
                <div class="page-title">Dashboard</div>
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
                                <span>Kmj. Pria (<?php echo $active_stock['baju_pria']; ?>)</span>
                                <span>Cln. Pria (<?php echo $active_stock['cln_pria']; ?>)</span>
                                <span>Kmj. Wnt (<?php echo $active_stock['baju_wnt']; ?>)</span>
                                <span>Bwn. Wnt (<?php echo $active_stock['cln_wnt']; ?>)</span>
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
                                <span>Kmj. Pria (<?php echo $inactive_stock['baju_pria']; ?>)</span>
                                <span>Cln. Pria (<?php echo $inactive_stock['cln_pria']; ?>)</span>
                                <span>Kmj. Wnt (<?php echo $inactive_stock['baju_wnt']; ?>)</span>
                                <span>Bwn. Wnt (<?php echo $inactive_stock['cln_wnt']; ?>)</span>
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