<?php
require_once __DIR__ . '/includes/auth_check.php';
include 'controllers/query_dashboard.php';
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>WC | Dashboard</title>
    
    <link rel="icon" type="image/png" href="assets/img/favicon-icon.png">
    
    <!-- Bootstrap & Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    
    <!-- Memanggil file CSS Anda -->
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>

<div class="app-container">

    <?php include 'includes/sidebar.php'; ?>

    <!-- MAIN CONTENT -->
    <div class="main-wrapper">
        
        <?php include 'includes/topbar.php'; ?>

        <!-- MAIN CONTENT AREA -->
        <main class="content-area p-4">
            
            <!-- Header Halaman -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div class="page-title">
                    <h4 class="fw-bold m-0"><?= $lang['dashboard_title'] ?? 'Halaman Statistik' ?></h4>
                    <p class="text-secondary m-0 mt-1" style="font-size: 14px;"><?= $lang['dashboard_subtitle'] ?? 'Melihat data statistik transaksi item' ?></p>
                </div>
                <div class="d-flex gap-2">
                    <!-- Refresh Button (Optional) -->
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
                                <div class="text-secondary fw-bold mb-1" style="font-size: 13px; text-transform: uppercase; letter-spacing: 0.5px;"><?= $lang['kpi_pending'] ?? 'PENDING REQUEST' ?></div>
                                <h2 class="fw-bold m-0 text-dark"><?php echo $kpi_pending; ?> <span style="font-size: 13px; font-weight: 500; color: #64748b;" class="ms-1"><?= $lang['unit_trx'] ?? 'Trx' ?></span></h2>
                            </div>
                        </div>
                    </div>  
                    <!-- KPI Transaksi -->  
                    <div class="col-md-4">
                        <div class="kpi-card">
                            <div class="kpi-icon kpi-success"><i class="bi bi-check2-all"></i></div>
                            <div>
                                <div class="text-secondary fw-bold mb-1" style="font-size: 13px; text-transform: uppercase; letter-spacing: 0.5px;"><?= $lang['kpi_transaksi'] ?? 'TOTAL TRANSAKSI' ?></div>
                                <h2 class="fw-bold m-0 text-dark"><?php echo $kpi_transaksi; ?> <span style="font-size: 13px; font-weight: 500; color: #64748b;" class="ms-1"><?= $lang['unit_trx'] ?? 'Trx' ?></span></h2>
                            </div>
                        </div>
                    </div>
                    <!-- KPI Return -->
                    <div class="col-md-4">
                        <div class="kpi-card">
                            <div class="kpi-icon kpi-danger"><i class="bi bi-arrow-counterclockwise"></i></div>
                            <div>
                                <div class="text-secondary fw-bold mb-1" style="font-size: 13px; text-transform: uppercase; letter-spacing: 0.5px;"><?= $lang['kpi_return'] ?? 'TOTAL RETURN' ?></div>
                                <h2 class="fw-bold m-0 text-dark"><?php echo $kpi_return; ?> <span style="font-size: 13px; font-weight: 500; color: #64748b;" class="ms-1"><?= $lang['unit_trx'] ?? 'Trx' ?></span></h2>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- AREA CONTAINER STOK STATISTIK -->
                <div class="row g-4 mb-4">
                    
                    <!-- 1. REMAINING STOK (CURRENT / ACTIVE) -->
                    <div class="col-md-6">
                        <div class="bg-white border rounded-3 p-4 h-100 shadow-sm">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <div>
                                    <h6 class="fw-bold mb-1 text-dark"><?= $lang['remaining_stock_title'] ?? 'Remaining Stok (Current)' ?></h6>
                                    <small class="text-muted"><?= $lang['remaining_stock_desc'] ?? 'Total barang aktif yang siap digunakan' ?></small>
                                </div>
                                <span class="badge bg-success-subtle text-success border border-success-subtle px-3 py-2 rounded-pill fw-bold" style="font-size: 12px;">
                                    <?= $lang['badge_active'] ?? 'Available (Active)' ?>
                                </span>
                            </div>

                            <!-- Total Active Banner -->
                            <div class="p-3 bg-primary-subtle rounded-3 mb-3 border border-primary-subtle d-flex justify-content-center align-items-center text-center">
                                <h3 class="fw-bold text-primary m-0">
                                    <?= number_format($active_stock['total'] ?? 0) ?> 
                                    <small class="fs-6 fw-normal text-secondary"><?= $lang['unit_pcs'] ?? 'Pcs' ?></small>
                                </h3>
                            </div>

                            <!-- Grid 2x2 Active -->
                            <div class="row g-2">
                                <div class="col-6">
                                    <div class="border rounded-3 p-2 bg-light d-flex justify-content-between align-items-center">
                                        <div>
                                            <small class="text-secondary d-block fw-semibold" style="font-size: 12px;"><?= $lang['item_baju_pria'] ?? 'Baju Pria' ?></small>
                                            <span class="fw-bold text-dark fs-6"><?= number_format($active_stock['baju_pria'] ?? 0) ?> <?= $lang['unit_pcs'] ?? 'Pcs' ?></span>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="border rounded-3 p-2 bg-light d-flex justify-content-between align-items-center">
                                        <div>
                                            <small class="text-secondary d-block fw-semibold" style="font-size: 12px;"><?= $lang['item_celana_pria'] ?? 'Celana Pria' ?></small>
                                            <span class="fw-bold text-dark fs-6"><?= number_format($active_stock['celana_pria'] ?? 0) ?> <?= $lang['unit_pcs'] ?? 'Pcs' ?></span>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="border rounded-3 p-2 bg-light d-flex justify-content-between align-items-center">
                                        <div>
                                            <small class="text-secondary d-block fw-semibold" style="font-size: 12px;"><?= $lang['item_baju_wanita'] ?? 'Baju Wanita' ?></small>
                                            <span class="fw-bold text-dark fs-6"><?= number_format($active_stock['baju_wanita'] ?? 0) ?> <?= $lang['unit_pcs'] ?? 'Pcs' ?></span>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="border rounded-3 p-2 bg-light d-flex justify-content-between align-items-center">
                                        <div>
                                            <small class="text-secondary d-block fw-semibold" style="font-size: 12px;"><?= $lang['item_celana_wanita'] ?? 'Celana Wanita' ?></small>
                                            <span class="fw-bold text-dark fs-6"><?= number_format($active_stock['celana_wanita'] ?? 0) ?> <?= $lang['unit_pcs'] ?? 'Pcs' ?></span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- 2. RETURNED STOK (INACTIVE) -->
                    <div class="col-md-6">
                        <div class="bg-white border rounded-3 p-4 h-100 shadow-sm">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <div>
                                    <h6 class="fw-bold mb-1 text-dark"><?= $lang['returned_stock_title'] ?? 'Returned Stok' ?></h6>
                                    <small class="text-muted"><?= $lang['returned_stock_desc'] ?? 'Total barang dikembalikan/tidak aktif' ?></small>
                                </div>
                                <span class="badge bg-danger-subtle text-danger border border-danger-subtle px-3 py-2 rounded-pill fw-bold" style="font-size: 12px;">
                                    <?= $lang['badge_inactive'] ?? 'Available (Inactive)' ?>
                                </span>
                            </div>

                            <!-- Total Inactive Banner -->
                            <div class="p-3 rounded-3 mb-3 border d-flex justify-content-center align-items-center text-center" style="<?= $card_style ?>">
                                <h3 class="fw-bold <?= $text_class ?> m-0">
                                    <?= number_format($total_return) ?> 
                                    <small class="fs-6 fw-normal <?= $unit_class ?>"><?= $lang['unit_pcs'] ?? 'Pcs' ?></small>
                                </h3>
                            </div>

                            <!-- Grid 2x2 Inactive -->
                            <div class="row g-2">
                                <div class="col-6">
                                    <div class="border rounded-3 p-2 bg-light d-flex justify-content-between align-items-center">
                                        <div>
                                            <small class="text-secondary d-block fw-semibold" style="font-size: 12px;"><?= $lang['item_baju_pria'] ?? 'Baju Pria' ?></small>
                                            <span class="fw-bold text-dark fs-6"><?= number_format($inactive_stock['baju_pria'] ?? 0) ?> <?= $lang['unit_pcs'] ?? 'Pcs' ?></span>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="border rounded-3 p-2 bg-light d-flex justify-content-between align-items-center">
                                        <div>
                                            <small class="text-secondary d-block fw-semibold" style="font-size: 12px;"><?= $lang['item_celana_pria'] ?? 'Celana Pria' ?></small>
                                            <span class="fw-bold text-dark fs-6"><?= number_format($inactive_stock['celana_pria'] ?? 0) ?> <?= $lang['unit_pcs'] ?? 'Pcs' ?></span>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="border rounded-3 p-2 bg-light d-flex justify-content-between align-items-center">
                                        <div>
                                            <small class="text-secondary d-block fw-semibold" style="font-size: 12px;"><?= $lang['item_baju_wanita'] ?? 'Baju Wanita' ?></small>
                                            <span class="fw-bold text-dark fs-6"><?= number_format($inactive_stock['baju_wanita'] ?? 0) ?> <?= $lang['unit_pcs'] ?? 'Pcs' ?></span>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="border rounded-3 p-2 bg-light d-flex justify-content-between align-items-center">
                                        <div>
                                            <small class="text-secondary d-block fw-semibold" style="font-size: 12px;"><?= $lang['item_celana_wanita'] ?? 'Celana Wanita' ?></small>
                                            <span class="fw-bold text-dark fs-6"><?= number_format($inactive_stock['celana_wanita'] ?? 0) ?> <?= $lang['unit_pcs'] ?? 'Pcs' ?></span>
                                        </div>
                                    </div>
                                </div>
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
// Penutupan koneksi PDO (opsional)
if (isset($pdo)) {
    $pdo = null;
}
?>