<?php
include 'controllers/query_laporan.php';
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan & Analitik - HR Warehouse</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
    
</head>
<body>

<div class="app-container">
    <?php include 'includes/sidebar.php'; ?>
    
    <div class="main-wrapper">
        <?php include 'includes/topbar.php'; ?>
        
        <main class="content-area p-4">
            <!-- Header -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h4 class="fw-bold mb-1">Laporan & Analitik</h4>
                    <p class="text-muted small mb-0">Ringkasan pergerakan stok dan transaksi</p>
                </div>
                <div class="d-flex gap-2">
                    <a href="controllers/export_excel.php?start_date=<?php echo $start_date; ?>&end_date=<?php echo $end_date; ?>" class="btn btn-success fw-bold">
                        <i class="bi bi-file-earmark-excel me-2"></i>Export ke Excel
                    </a>
                </div>
            </div>

            <!-- Filter Panel -->
            <div class="bg-white border rounded-3 p-3 mb-4 shadow-sm">
                <form method="GET" action="laporan.php" class="row align-items-end g-3">
                    <div class="col-md-4">
                        <label class="form-label small fw-bold text-secondary">Mulai Tanggal</label>
                        <input type="date" class="form-control" name="start_date" value="<?php echo $start_date; ?>">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small fw-bold text-secondary">Sampai Tanggal</label>
                        <input type="date" class="form-control" name="end_date" value="<?php echo $end_date; ?>">
                    </div>
                    <div class="col-md-4">
                        <button type="submit" class="btn btn-primary w-100 fw-bold" style="background-color: #556ee6;">
                            <i class="bi bi-funnel me-2"></i>Tampilkan Data
                        </button>
                    </div>
                </form>
            </div>

            <!-- Cards -->
            <div class="row g-4 mb-4">
                <div class="col-md-4">
                    <div class="report-card shadow-sm">
                        <div class="icon-box icon-masuk"><i class="bi bi-download"></i></div>
                        <div>
                            <div class="text-muted small fw-bold mb-1">BARANG MASUK</div>
                            <h3 class="fw-bold m-0"><?php echo number_format($total_masuk); ?> <span class="fs-6 text-muted fw-normal">Pcs</span></h3>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="report-card shadow-sm">
                        <div class="icon-box icon-keluar"><i class="bi bi-upload"></i></div>
                        <div>
                            <div class="text-muted small fw-bold mb-1">TOTAL TRANSAKSI</div>
                            <h3 class="fw-bold m-0"><?php echo number_format($total_trx); ?> <span class="fs-6 text-muted fw-normal">Trx</span></h3>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="report-card shadow-sm">
                        <div class="icon-box icon-return"><i class="bi bi-arrow-counterclockwise"></i></div>
                        <div>
                            <div class="text-muted small fw-bold mb-1">TOTAL RETURN</div>
                            <h3 class="fw-bold m-0"><?php echo number_format($total_return); ?> <span class="fs-6 text-muted fw-normal">Pcs</span></h3>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tabel Data -->
            <div class="bg-white border rounded-3 p-4 shadow-sm">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h6 class="fw-bold mb-0">Rincian Transaksi Keluar</h6>
                    <span class="badge bg-light text-secondary border">Periode: <?php echo date('d/m/Y', strtotime($start_date)) . " - " . date('d/m/Y', strtotime($end_date)); ?></span>
                </div>
                
                <div class="table-responsive">
                    <table class="table table-borderless align-middle m-0" style="font-size: 14px;">
                        <thead class="text-secondary small border-bottom">
                            <tr>
                                <th class="py-3">TANGGAL</th>
                                <th class="py-3">ID TRX</th>
                                <th class="py-3">PERUSAHAAN</th>
                                <th class="py-3">ITEM DIBERIKAN</th>
                                <th class="py-3 text-center">TOTAL (PCS)</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($list_transaksi)): ?>
                                <?php foreach ($list_transaksi as $row): ?>
                                    <?php
                                    $tgl = date('d/m/Y', strtotime($row['tgl_transaksi']));
                                    
                                    // Grouping nama item ("Kemeja Pria (2)")
                                    $raw_items_array = explode(',', $row['all_items']);
                                    $item_counts = array_count_values($raw_items_array);
                                    
                                    $formatted_items = [];
                                    foreach ($item_counts as $nama_item => $jumlah) {
                                        $formatted_items[] = htmlspecialchars(trim($nama_item)) . " ($jumlah)";
                                    }
                                    $string_item_diberikan = implode(', ', $formatted_items);
                                    ?>
                                    <tr class="border-bottom">
                                        <td class="fw-bold py-3"><?php echo $tgl; ?></td>
                                        <td class="text-muted">#<?php echo htmlspecialchars($row['transaction_id']); ?></td>
                                        <td class="text-muted"><?php echo htmlspecialchars($row['perusahaan']); ?></td>
                                        <td class="text-muted"><?php echo $string_item_diberikan; ?></td>
                                        <td class="text-center fw-bold text-primary"><?php echo $row['qty_total']; ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="5" class="text-center py-5 text-muted">Tidak ada transaksi pada periode tanggal ini.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

        </main>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="assets/js/scripts.js"></script>
</body>
</html>