<?php
include 'controllers/query_pending.php';
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manajemen Request - HR Warehouse</title>
    
    <!-- Bootstrap & Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    
    <!-- CSS Utama -->
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>

<div class="app-container">

    <!-- Memanggil Sidebar -->
    <?php include 'includes/sidebar.php'; ?>

    <div class="main-wrapper">
        
        <!-- Memanggil Topbar -->
        <?php include 'includes/topbar.php'; ?>

        <!-- MAIN CONTENT AREA -->
        <main class="content-area p-4">
            
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div class="page-title">
                    <h4 class="fw-bold m-0">Manajemen Request Seragam</h4>
                    <p class="text-secondary m-0 mt-1" style="font-size: 14px;">Pengolahan dan riwayat status pengajuan seragam SA</p>
                </div>
                
                <div class="input-group" style="width: 250px;">
                    <span class="input-group-text bg-white border-end-0"><i class="bi bi-search text-secondary"></i></span>
                    <input type="text" id="searchInput" class="form-control border-start-0 ps-0" placeholder="Cari No. Request / PT...">
                </div>
            </div>

            <!-- NAV TABS UNTUK SWAP TABEL -->
            <ul class="nav nav-tabs custom-tabs mb-3" id="requestTabs" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active fw-bold" id="pending-tab" data-bs-toggle="tab" data-bs-target="#pending-panel" type="button" role="tab">
                        <i class="bi bi-clock-history me-2 text-warning"></i>Pending Request 
                        <span class="badge bg-warning text-dark ms-2"><?= ($result_pending) ? $result_pending->num_rows : 0 ?></span>
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link fw-bold" id="done-tab" data-bs-toggle="tab" data-bs-target="#done-panel" type="button" role="tab">
                        <i class="bi bi-check-circle-fill me-2 text-success"></i>Request Selesai (Done)
                        <span class="badge bg-success ms-2"><?= ($result_done) ? $result_done->num_rows : 0 ?></span>
                    </button>
                </li>
            </ul>

            <?php $popupsHTML = ""; // Penampung Popup Modal ?>

            <div class="tab-content" id="requestTabsContent">
                
                <!-- ================= TAB 1: TABEL PENDING ================= -->
                <div class="tab-pane fade show active" id="pending-panel" role="tabpanel">
                    <div class="table-card shadow-sm border-0 rounded-3">
                        <div class="table-responsive">
                            <table class="table align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th scope="col" width="15%">No. Request</th>
                                        <th scope="col" width="45%">Detail Karyawan & Item</th>
                                        <th scope="col" width="15%" class="text-center">Status</th>
                                        <th scope="col" width="25%" class="text-center">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if ($result_pending && $result_pending->num_rows > 0): ?>
                                        <?php while ($row = $result_pending->fetch_assoc()): 
                                            $req_id     = $row['request_id'];
                                            $pt         = htmlspecialchars($row['perusahaan']);
                                            $gender_txt = ($row['gender'] === 'male') ? 'SA Pria' : 'SA Wanita';
                                            $tgl        = date('d M Y', strtotime($row['tgl_request']));
                                            $total_qty  = $row['qty_top'] + $row['qty_bottoms'];
                                            $file_path  = !empty($row['upload']) ? '/Request.Form.2/upload/' . basename($row['upload']) : '#';
                                            $file_name  = !empty($row['upload']) ? basename($row['upload']) : 'Tidak ada file';
                                        ?>
                                            <tr>
                                                <td><span class="req-badge">#<?= htmlspecialchars($req_id) ?></span></td>
                                                <td>
                                                    <div class="fw-bold text-dark" style="font-size: 15px;"><?= $pt ?></div>
                                                    <div class="text-secondary mt-1" style="font-size: 13px;">
                                                        <i class="bi bi-box-seam me-1"></i> Request Seragam <?= $gender_txt ?>
                                                        <span class="mx-2 text-muted">|</span> 
                                                        <i class="bi bi-calendar-event me-1"></i> <?= $tgl ?>
                                                    </div>
                                                </td>
                                                <td class="text-center">
                                                    <span class="badge bg-warning text-dark px-3 py-2 rounded-pill"><i class="bi bi-hourglass-split me-1"></i> Pending</span>
                                                </td>
                                                <td class="text-center">
                                                    <button type="button" class="btn btn-danger w-100 fw-bold" data-bs-toggle="modal" data-bs-target="#reviewPopup_<?= htmlspecialchars($req_id) ?>">
                                                        Proses <i class="bi bi-arrow-right-circle ms-2"></i>
                                                    </button>
                                                </td>
                                            </tr>

                                            <?php 
                                            // --- Bikin Modal Popup Pending ---
                                            ob_start(); 
                                            ?>
                                            <div class="modal fade" id="reviewPopup_<?= htmlspecialchars($req_id) ?>" tabindex="-1" aria-hidden="true">
                                                <div class="modal-dialog modal-xl modal-dialog-centered">
                                                    <div class="modal-content">
                                                        <div class="modal-header border-0 pb-0 pt-4 px-4">
                                                            <h5 class="modal-title fw-bold" style="color: #4b5563;">Detail Request #<?= htmlspecialchars($req_id) ?></h5>
                                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                        </div>
                                                        <div class="modal-body p-4">
                                                            <div class="row g-4">
                                                                <div class="col-lg-7">
                                                                    <div class="panel-card p-3 border rounded-3">
                                                                        <h6 class="fw-bold text-danger mb-3">RINGKASAN PESANAN</h6>
                                                                        <table class="table table-borderless summary-table m-0">
                                                                            <tr><th>Perusahaan</th><td>: <?= $pt ?></td></tr>
                                                                            <tr><th>Nama SA</th><td>: <?= htmlspecialchars($row['nama_sa']) ?></td></tr>
                                                                            <tr><th>Alamat</th><td>: <?= nl2br(htmlspecialchars($row['alamat'])) ?></td></tr>
                                                                            <?php if ($row['qty_top'] > 0): ?>
                                                                                <tr><th>Item Atasan</th><td>: Baju <?= $gender_txt ?> (Size <?= htmlspecialchars($row['size_top']) ?>) - <?= htmlspecialchars($row['qty_top']) ?> Pcs</td></tr>
                                                                            <?php endif; ?>
                                                                            <?php if ($row['qty_bottoms'] > 0): ?>
                                                                                <tr><th>Item Bawahan</th><td>: Celana <?= $gender_txt ?> (Size <?= htmlspecialchars($row['size_bottoms']) ?>) - <?= htmlspecialchars($row['qty_bottoms']) ?> Pcs</td></tr>
                                                                            <?php endif; ?>
                                                                            <tr><th>Total Jumlah</th><td class="fw-bold">: <?= $total_qty ?> Pcs</td></tr>
                                                                            <tr><th>Total Harga</th><td class="fw-bold text-danger">: Rp<?= number_format($row['total_harga'], 0, ',', '.') ?></td></tr>
                                                                        </table>
                                                                    </div>
                                                                </div>
                                                                <div class="col-lg-5">
                                                                    <div class="panel-card p-3 border rounded-3 d-flex flex-column h-100">
                                                                        <h6 class="fw-bold text-danger mb-3">KONFIRMASI</h6>
                                                                        <p class="fw-bold mb-2">Metode Pembayaran: <span class="text-danger"><?= strtoupper(str_replace('_', ' ', $row['pembayaran'])) ?></span></p>
                                                                        
                                                                        <?php if ($row['pembayaran'] === 'transfer' && $file_path !== '#'): ?>
                                                                            <a href="<?= htmlspecialchars($file_path) ?>" target="_blank" class="btn btn-sm btn-outline-danger w-100 mb-3">
                                                                                <i class="bi bi-file-earmark-pdf me-1"></i> Lihat Bukti Transfer
                                                                            </a>
                                                                        <?php endif; ?>

                                                                        <div class="mt-auto">
                                                                            <a href="transaksi.php?id=<?= urlencode($req_id) ?>&pt=<?= urlencode($row['perusahaan']) ?>" class="btn btn-success fw-bold w-100 py-2">
                                                                                <i class="bi bi-check-circle me-1"></i> Approve Request
                                                                            </a>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <?php 
                                            $popupsHTML .= ob_get_clean();
                                        endwhile; 
                                    else: ?>
                                        <tr>
                                            <td colspan="4" class="text-center text-secondary py-5">
                                                <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                                                Tidak ada request seragam yang pending saat ini.
                                            </td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- ================= TAB 2: TABEL DONE ================= -->
                <div class="tab-pane fade" id="done-panel" role="tabpanel">
                    <div class="table-card shadow-sm border-0 rounded-3">
                        <div class="table-responsive">
                            <table class="table align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th scope="col" width="15%">No. Request</th>
                                        <th scope="col" width="45%">Detail Karyawan & Item</th>
                                        <th scope="col" width="15%" class="text-center">Status</th>
                                        <th scope="col" width="25%" class="text-center">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if ($result_done && $result_done->num_rows > 0): ?>
                                        <?php while ($row_done = $result_done->fetch_assoc()): 
                                            $req_id     = $row_done['request_id'];
                                            $pt         = htmlspecialchars($row_done['perusahaan']);
                                            $gender_txt = ($row_done['gender'] === 'male') ? 'SA Pria' : 'SA Wanita';
                                            $tgl_done   = !empty($row_done['tgl_transaksi']) ? date('d M Y', strtotime($row_done['tgl_transaksi'])) : '-';
                                            $total_qty  = $row_done['qty_top'] + $row_done['qty_bottoms'];
                                        ?>
                                            <tr>
                                                <td><span class="req-badge">#<?= htmlspecialchars($req_id) ?></span></td>
                                                <td>
                                                    <div class="fw-bold text-dark" style="font-size: 15px;"><?= $pt ?></div>
                                                    <div class="text-secondary mt-1" style="font-size: 13px;">
                                                        <i class="bi bi-box-seam me-1"></i> Request Seragam <?= $gender_txt ?>
                                                        <span class="mx-2 text-muted">|</span> 
                                                        <i class="bi bi-check2-square me-1 text-success"></i> Selesai: <?= $tgl_done ?>
                                                    </div>
                                                </td>
                                                <td class="text-center">
                                                    <span class="badge bg-success px-3 py-2 rounded-pill"><i class="bi bi-check-all me-1"></i> Done</span>
                                                </td>
                                                <td class="text-center">
                                                    <button type="button" class="btn btn-outline-secondary w-100 fw-semibold" data-bs-toggle="modal" data-bs-target="#reviewPopup_<?= htmlspecialchars($req_id) ?>">
                                                        <i class="bi bi-eye me-1"></i> Lihat Detail
                                                    </button>
                                                </td>
                                            </tr>

                                            <?php 
                                            // --- Bikin Modal Popup Done (Read Only / Cetak) ---
                                            ob_start(); 
                                            ?>
                                            <div class="modal fade" id="reviewPopup_<?= htmlspecialchars($req_id) ?>" tabindex="-1" aria-hidden="true">
                                                <div class="modal-dialog modal-lg modal-dialog-centered">
                                                    <div class="modal-content">
                                                        <div class="modal-header border-0 pb-0 pt-4 px-4">
                                                            <h5 class="modal-title fw-bold text-success">Detail Request #<?= htmlspecialchars($req_id) ?> (Selesai)</h5>
                                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                        </div>
                                                        <div class="modal-body p-4">
                                                            <div class="panel-card p-3 border rounded-3">
                                                                <table class="table table-borderless summary-table m-0">
                                                                    <tr><th>Perusahaan</th><td>: <?= $pt ?></td></tr>
                                                                    <tr><th>Nama SA</th><td>: <?= htmlspecialchars($row_done['nama_sa']) ?></td></tr>
                                                                    <tr><th>Alamat</th><td>: <?= nl2br(htmlspecialchars($row_done['alamat'])) ?></td></tr>
                                                                    <tr><th>Total Jumlah</th><td class="fw-bold">: <?= $total_qty ?> Pcs</td></tr>
                                                                    <tr><th>Total Harga</th><td class="fw-bold text-success">: Rp<?= number_format($row_done['total_harga'], 0, ',', '.') ?></td></tr>
                                                                </table>
                                                                <hr>
                                                                <div class="text-end">
                                                                    <span class="badge bg-success-subtle text-success border border-success px-3 py-2">
                                                                        <i class="bi bi-check-circle-fill me-1"></i> Transaksi Telah Diterbitkan
                                                                    </span>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <?php 
                                            $popupsHTML .= ob_get_clean();
                                        endwhile; 
                                    else: ?>
                                        <tr>
                                            <td colspan="4" class="text-center text-secondary py-5">
                                                <i class="bi bi-check2-circle fs-1 d-block mb-2"></i>
                                                Belum ada riwayat request yang selesai.
                                            </td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

            </div>

        </main>
    </div>
</div>

<!-- CETAK SEMUA POPUP DI SINI -->
<?= $popupsHTML ?>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="assets/js/scripts.js"></script>
</body>
</html>
<?php 
if(isset($conn)){
    $conn->close();
}
?>