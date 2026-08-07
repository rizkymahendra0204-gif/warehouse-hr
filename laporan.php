<?php
require_once __DIR__ . '/includes/auth_check.php';

// Tangkap tipe laporan dari URL (default: internal)
$report_type = $_GET['type'] ?? 'internal';

// Include controller query
include __DIR__ . '/controllers/query_laporan.php';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <link rel="icon" type="image/png" href="assets/img/favicon.png">

    <title>Laporan - HR Warehouse</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        .nav-tabs-custom .nav-link {
            border: none;
            border-bottom: 3px solid transparent;
            color: #6c757d;
            font-weight: 600;
            padding: 0.75rem 1.25rem;
        }
        .nav-tabs-custom .nav-link.active {
            border-bottom-color: #0d6efd;
            color: #0d6efd;
            background: transparent;
        }
    </style>
</head>
<body>

<div class="app-container">
    <?php include __DIR__ . '/includes/sidebar.php'; ?>
    <div class="main-wrapper">
        <?php include __DIR__ . '/includes/topbar.php'; ?>
        
        <main class="content-area p-4">
            <!-- Header Title -->
            <div class="mb-3">
                <h4 class="fw-bold mb-1">
                    <?= $report_type === 'finance' ? 'Laporan Keuangan' : 'Laporan Pergerakan Stok' ?>
                </h4>
                <p class="text-muted small mb-0">
                    <?= $report_type === 'finance' ? 'Rekapitulasi biaya pembayaran seragam' : 'Rekapitulasi distribusi & retur seragam' ?>
                </p>
            </div>

            <!-- TAB NAVIGASI -->
            <ul class="nav nav-tabs nav-tabs-custom mb-4 border-bottom">
                <li class="nav-item">
                    <a class="nav-link <?= $report_type === 'internal' ? 'active' : '' ?>" 
                       href="laporan.php?type=internal&start_date=<?= urlencode($start_date) ?>&end_date=<?= urlencode($end_date) ?>">
                        <i class="bi bi-box-seam me-2"></i>Laporan Stok
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= $report_type === 'finance' ? 'active' : '' ?>" 
                       href="laporan.php?type=finance&start_date=<?= urlencode($start_date) ?>&end_date=<?= urlencode($end_date) ?>">
                        <i class="bi bi-wallet2 me-2"></i>Laporan Keuangan
                    </a>
                </li>
            </ul>

            <!-- ========================================== -->
            <!-- 🎛️ AREA FILTER & KARTU RINGKASAN          -->
            <!-- ========================================== -->
            <form method="GET" action="" class="mb-4">
                <input type="hidden" name="type" value="<?= htmlspecialchars($report_type) ?>">
                
                <!-- CONTAINER 1: Card Metric Utama + Filter Tanggal -->
                <div class="bg-white border rounded-3 p-3 mb-3 shadow-sm sticky-detail-pesanan">
                    <div class="row g-2 align-items-stretch">
                        <!-- Card 1: Utama (Master Stok / Total Akumulasi Biaya) -->
                        <div class="col-md-4">
                            <div class="border rounded-3 p-3 h-100 d-flex align-items-center gap-2 bg-white">
                                <div class="icon-box text-primary fs-4">
                                    <i class="bi <?= $report_type === 'finance' ? 'bi-cash-stack' : 'bi-boxes' ?>"></i>
                                </div>
                                <div>
                                    <div class="text-muted fw-bold text-uppercase" style="font-size: 13px; letter-spacing: 0.3px;">
                                        <?= $report_type === 'finance' ? 'TOTAL AKUMULASI BIAYA' : 'TOTAL MASTER STOK' ?>
                                    </div>
                                    <h4 class="fw-bold m-0 fs-5">
                                        <?= $report_type === 'finance' ? 'Rp ' . number_format($grand_total_all_time ?? 0, 0, ',', '.') : number_format($total_master_stok ?? 0) ?> 
                                        <?php if ($report_type !== 'finance'): ?>
                                            <span class="text-muted fw-normal" style="font-size: 13px;">Item</span>
                                        <?php endif; ?>
                                    </h4>
                                </div>
                            </div>
                        </div>

                        <!-- Box Filter Tanggal & Tombol Aksi -->
                        <div class="col-md-8">
                            <div class="border rounded-3 p-3 h-100 bg-white">
                                <div class="row g-2 align-items-center h-100">
                                    <div class="col-sm-7">
                                        <div class="row align-items-center mb-1">
                                            <label class="col-sm-5 col-form-label py-0 fw-bold text-secondary text-nowrap" style="font-size: 12px;">Tanggal Mulai</label>
                                            <div class="col-sm-7">
                                                <input type="date" class="form-control form-control-sm py-1" name="start_date" value="<?= htmlspecialchars($start_date) ?>">
                                            </div>
                                        </div>
                                        <div class="row align-items-center">
                                            <label class="col-sm-5 col-form-label py-0 fw-bold text-secondary text-nowrap" style="font-size: 12px;">Tanggal Selesai</label>
                                            <div class="col-sm-7">
                                                <input type="date" class="form-control form-control-sm py-1" name="end_date" value="<?= htmlspecialchars($end_date) ?>">
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-sm-5 d-flex flex-column gap-1">
                                        <a href="controllers/export_excel.php?type=<?= $report_type ?>&start_date=<?= urlencode($start_date) ?>&end_date=<?= urlencode($end_date) ?>" 
                                           class="btn btn-cetak-custom btn-sm fw-bold w-100 text-center py-2" style="font-size: 12px;">
                                            Export To Excel
                                        </a>
                                        <button type="submit" class="btn btn-proses-custom btn-sm fw-bold w-100 py-2" style="font-size: 12px;">
                                            Tampilkan Laporan
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- CONTAINER 2: Card Ringkasan Detail -->
                <div class="bg-white border rounded-3 p-3 shadow-sm">
                    <div class="row g-3 align-items-stretch">
                        <?php if ($report_type === 'finance'): ?>
                            <!-- 💰 KEUANGAN: 3 CARD (Total Request, Grand Total Periode, Total Pcs Distribusi) -->
                            <div class="col-md-4">
                                <div class="border rounded-3 p-3 h-100 d-flex align-items-center gap-2 bg-white">
                                    <div class="icon-box text-info fs-4"><i class="bi bi-file-earmark-text"></i></div>
                                    <div>
                                        <div class="text-muted fw-bold text-uppercase" style="font-size: 13px; letter-spacing: 0.3px;">TOTAL REQUEST</div>
                                        <h4 class="fw-bold m-0 fs-5"><?= number_format($total_req ?? 0) ?> <span class="text-muted fw-normal" style="font-size: 13px;">Form</span></h4>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="border rounded-3 p-3 h-100 d-flex align-items-center gap-2 bg-white">
                                    <div class="icon-box text-warning fs-4"><i class="bi bi-box-seam"></i></div>
                                    <div>
                                        <div class="text-muted fw-bold text-uppercase" style="font-size: 13px; letter-spacing: 0.3px;">TOTAL PCS DISTRIBUSI</div>
                                        <h4 class="fw-bold m-0 fs-5"><?= number_format($total_pcs_fin ?? 0) ?> <span class="text-muted fw-normal" style="font-size: 13px;">Pcs</span></h4>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="border rounded-3 p-3 h-100 d-flex align-items-center gap-2 bg-white">
                                    <div class="icon-box text-success fs-4"><i class="bi bi-wallet2"></i></div>
                                    <div>
                                        <div class="text-muted fw-bold text-uppercase" style="font-size: 13px; letter-spacing: 0.3px;">GRAND TOTAL</div>
                                        <h4 class="fw-bold m-0 text-success fs-5">Rp <?= number_format($grand_total_biaya ?? 0, 0, ',', '.') ?></h4>
                                    </div>
                                </div>
                            </div>
                            

                        <?php else: ?>
                            <!-- 📦 STOK: 3 CARD (Total Transaksi, Total Retur, Net Terpakai) -->
                            <div class="col-md-4">
                                <div class="border rounded-3 p-3 h-100 d-flex align-items-center gap-2 bg-white">
                                    <div class="icon-box text-info fs-4"><i class="bi bi-cart-check"></i></div>
                                    <div>
                                        <div class="text-muted fw-bold text-uppercase" style="font-size: 13px; letter-spacing: 0.3px;">TOTAL TRANSAKSI</div>
                                        <h4 class="fw-bold m-0 fs-5"><?= number_format($total_keluar ?? 0) ?> <span class="text-muted fw-normal" style="font-size: 13px;">Transaksi</span></h4>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="border rounded-3 p-3 h-100 d-flex align-items-center gap-2 bg-white">
                                    <div class="icon-box text-warning fs-4"><i class="bi bi-arrow-counterclockwise"></i></div>
                                    <div>
                                        <div class="text-muted fw-bold text-uppercase" style="font-size: 13px; letter-spacing: 0.3px;">TOTAL RETUR</div>
                                        <h4 class="fw-bold m-0 fs-5"><?= number_format($total_retur ?? 0) ?> <span class="text-muted fw-normal" style="font-size: 13px;">Pcs</span></h4>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="border rounded-3 p-3 h-100 d-flex align-items-center gap-2 bg-white">
                                    <div class="icon-box text-success fs-4"><i class="bi bi-box-seam"></i></div>
                                    <div>
                                        <div class="text-muted fw-bold text-uppercase" style="font-size: 13px; letter-spacing: 0.3px;">TOTAL STOK</div>
                                        <h4 class="fw-bold m-0 fs-5"><?= number_format($net_terpakai ?? 0) ?> <span class="text-muted fw-normal" style="font-size: 13px;">Pcs</span></h4>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </form>

            <!-- ========================================== -->
            <!-- 📋 CONTAINER 3: TABEL DATA LAPORAN         -->
            <!-- ========================================== -->
            <div class="bg-white border rounded-3 p-4 shadow-sm">
                <?php if ($report_type === 'internal'): ?>
                    <!-- TABEL INTERNAL (STOK) -->
                    <h6 class="fw-bold mb-3">Rincian Pergerakan Stok per Transaksi</h6>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle m-0" style="font-size: 14px;">
                            <thead class="table-light text-secondary small">
                                <tr>
                                    <th width="5%">NO</th>
                                    <th width="5%">TANGGAL</th>
                                    <th width="25%">DETAIL PESANAN</th>
                                    <th width="15%">PERUSAHAAN / BRAND</th>
                                    <th width="5%">NAMA SA</th>
                                    <th width="20%">ITEM DIBERIKAN</th>
                                    <th width="20%">ITEM RETURN</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($list_data)): $no = 1; foreach ($list_data as $row): ?>
                                    <?php 
                                        $raw_items = array_count_values(explode(', ', $row['raw_items'] ?? ''));
                                        $formatted_items = [];
                                        foreach ($raw_items as $name => $qty) { if($name) $formatted_items[] = trim($name) . " ($qty)"; }
                                        
                                        $str_returns = '-';
                                        if (!empty($row['raw_returns'])) {
                                            $raw_returns = array_count_values(explode(', ', $row['raw_returns']));
                                            $formatted_returns = [];
                                            foreach ($raw_returns as $name => $qty) { if($name) $formatted_returns[] = trim($name) . " ($qty)"; }
                                            $str_returns = implode(', ', $formatted_returns);
                                        }

                                        $barcode_item = htmlspecialchars($row['barcode'] ?? '-');
                                        $detail_item  = htmlspecialchars($row['raw_items'] ?? '-');
                                        $raw_gender = strtolower(trim($row['gender'] ?? ''));
                                        $gender_txt = ($raw_gender === 'male' || $raw_gender === 'pria' || $raw_gender === '1') 
                                            ? 'SA Pria' 
                                            : 'SA Wanita';
                                    ?>
                                    <tr>
                                        <td><?= $no++ ?></td>
                                        <td><?= date('d/m/Y', strtotime($row['tgl_transaksi'])) ?></td>
                                        <td>
                                            <div class="fw-bold text-primary" style="font-size: 15px;">[<?= $barcode_item ?>]</div>
                                            <div class="text-secondary mt-1" style="font-size: 13px;">
                                                <?= $gender_txt ?> | <?= htmlspecialchars($row['raw_items'] ?? '-') ?>
                                            </div>
                                        </td>
                                        <td><?= htmlspecialchars($row['perusahaan']) ?> (<?= htmlspecialchars($row['brand'] ?? '-') ?>)</td>
                                        <td><?= htmlspecialchars($row['nama_sa']) ?></td>
                                        <td><small><?= implode(', ', $formatted_items) ?></small></td>
                                        <td><small class="text-danger"><?= $str_returns ?></small></td>
                                    </tr>
                                <?php endforeach; else: ?>
                                    <tr><td colspan="7" class="text-center py-4 text-muted">Tidak ada pergerakan stok pada periode ini.</td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <!-- TABEL FINANCE (KEUANGAN) -->
                    <h6 class="fw-bold mb-3">Rincian Transaksi & Tagihan Keuangan</h6>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle m-0" style="font-size: 14px;">
                            <thead class="table-light text-secondary small">
                                <tr>
                                    <th>TANGGAL</th>
                                    <th>NO REQUEST</th>
                                    <th>PERUSAHAAN / BRAND</th>
                                    <th>NAMA SA</th>
                                    <th class="text-center">METODE PEMBAYARAN</th>
                                    <th class="text-center">TOTAL PCS</th>
                                    <th class="text-end">TOTAL TAGIHAN (RP)</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($list_data)): foreach ($list_data as $row): ?>
                                    <tr>
                                        <td class="fw-bold"><?= date('d/m/Y', strtotime($row['tgl_transaksi'])) ?></td>
                                        <td class="text-primary fw-bold">#<?= htmlspecialchars($row['request_id']) ?></td>
                                        <td><?= htmlspecialchars($row['perusahaan']) ?> (<?= htmlspecialchars($row['brand'] ?? '-') ?>)</td>
                                        <td><?= htmlspecialchars($row['nama_sa']) ?></td>
                                        <td class="text-center">
                                            <span class="badge bg-light text-dark border"><?= htmlspecialchars($row['pembayaran'] ?? 'Transfer') ?></span>
                                        </td>
                                        <td class="text-center fw-bold"><?= $row['total_pcs'] ?> Pcs</td>
                                        <td class="text-end fw-bold text-dark">Rp <?= number_format($row['total_harga'], 0, ',', '.') ?></td>
                                    </tr>
                                <?php endforeach; else: ?>
                                    <tr><td colspan="7" class="text-center py-4 text-muted">Tidak ada data keuangan pada periode ini.</td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </main>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="assets/js/scripts.js"></script>
</body>
</html>