<?php
require_once __DIR__ . '/includes/auth_check.php';
include 'controllers/query_pending.php';
include 'includes/log.php';

// Fallback variabel bertipe Array dari PDO
if (!isset($requests_pending)) {
    if (isset($result_pending) && is_object($result_pending)) {
        $requests_pending = $result_pending->fetch_all(MYSQLI_ASSOC);
    } else {
        $requests_pending = $result_pending ?? [];
    }
}

if (!isset($requests_done)) {
    if (isset($result_done) && is_object($result_done)) {
        $requests_done = $result_done->fetch_all(MYSQLI_ASSOC);
    } else {
        $requests_done = $result_done ?? [];
    }
}
?>

<!DOCTYPE html>
<html lang="<?= $_SESSION['lang'] ?? 'id'; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $lang['pending_title'] ?? 'Manajemen Request Seragam' ?> - HR Warehouse</title>

    <link rel="icon" type="image/png" href="assets/img/favicon-icon.png">
    
    <!-- Bootstrap & Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    
    <!-- CSS Utama -->
    <link rel="stylesheet" href="assets/css/style.css">
    
    <!-- CSS Kustom untuk Tab Navigasi Bergaya Underline -->
    <style>
        .nav-tabs-custom {
            border-bottom: 1px solid #e2e8f0;
        }
        .nav-tabs-custom .nav-link {
            color: #64748b;
            font-weight: 600;
            font-size: 14px;
            border: none;
            border-bottom: 2px solid transparent;
            padding: 10px 16px;
            transition: all 0.2s ease-in-out;
            background: transparent !important;
        }
        .nav-tabs-custom .nav-link:hover {
            color: #0d6efd;
            border-color: transparent;
        }
        .nav-tabs-custom .nav-link.active {
            color: #0d6efd !important;
            border-bottom: 2px solid #0d6efd !important;
            font-weight: 700;
        }
    </style>
</head>
<body>

<div class="app-container">

    <!-- Memanggil Sidebar & Topbar -->
    <?php include 'includes/sidebar.php'; ?>

    <div class="main-wrapper">
        <?php include 'includes/topbar.php'; ?>

        <!-- MAIN CONTENT AREA -->
        <main class="content-area p-4">
            
            <!-- Header Halaman (Jarak bawah dibuat presisi mb-3) -->
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div class="page-title">
                    <h4 class="fw-bold m-0"><?= $lang['pending_title'] ?? 'Manajemen Request Seragam' ?></h4>
                    <p class="text-secondary m-0 mt-1" style="font-size: 14px;"><?= $lang['pending_subtitle'] ?? 'Pengolahan dan riwayat status pengajuan seragam SA' ?></p>
                </div>
            </div>

            <!-- AREA FILTER TAB (UNDERLINE STYLE) & SEARCH BAR SEJAJAR -->
            <div class="d-flex justify-content-between align-items-center mb-3 border-bottom">
                <ul class="nav nav-tabs nav-tabs-custom border-0" id="requestTabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active fw-bold" id="pending-tab" data-bs-toggle="tab" data-bs-target="#pending-panel" type="button" role="tab">
                            <?= $lang['tab_pending_req'] ?? 'Permintaan Tertunda' ?> 
                            <span class="badge bg-warning text-dark ms-2" id="badge-pending-count"><?= count($requests_pending) ?></span>
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link fw-bold" id="done-tab" data-bs-toggle="tab" data-bs-target="#done-panel" type="button" role="tab">
                            <?= $lang['tab_req_done'] ?? 'Permintaan Selesai' ?>
                            <span class="badge bg-success ms-2" id="badge-done-count"><?= count($requests_done) ?></span>
                        </button>
                    </li>
                </ul>
                
                <div class="input-group mb-2" style="width: 250px;">
                    <input type="text" id="searchInput" class="form-control ps-3" placeholder="<?= $lang['search_placeholder'] ?? 'Cari ...' ?>" style="font-size: 13px;">
                    <span class="input-group-text bg-white"><i class="bi bi-search text-secondary"></i></span>
                </div>
            </div>

            <div class="tab-content" id="requestTabsContent">
                
                <!-- ================= TAB 1: TABEL PENDING ================= -->
                <div class="tab-pane fade show active" id="pending-panel" role="tabpanel">
                    <div id="pending-tab-wrapper">
                        <?php $popupsPending = ""; ?>
                        <div class="table-card shadow-sm border-0 rounded-3 mb-4">
                            <div class="table-responsive">
                                <table class="table align-middle mb-0">
                                    <thead class="table-light text-secondary small border-bottom">
                                        <tr>
                                            <th scope="col" width="15%"><?= $lang['th_no_request'] ?? 'NO. REQUEST' ?></th>
                                            <th scope="col" width="45%"><?= $lang['th_detail_karyawan'] ?? 'DETAIL KARYAWAN & ITEM' ?></th>
                                            <th scope="col" width="15%" class="text-center"><?= $lang['table_status'] ?? 'STATUS' ?></th>
                                            <th scope="col" width="25%" class="text-center"><?= $lang['table_action'] ?? 'AKSI' ?></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (!empty($requests_pending)): ?>
                                            <?php foreach ($requests_pending as $row): 
                                                $req_id     = $row['request_id'];
                                                $pt         = htmlspecialchars($row['perusahaan']);
                                                $gender_txt = ($row['gender'] === 'male') ? 'SA Pria' : 'SA Wanita';
                                                $tgl        = date('d M Y', strtotime($row['tgl_request']));
                                                $total_qty  = $row['qty_top'] + $row['qty_bottoms'];
                                                $file_path  = $row['file_path'];
                                                $file_name  = $row['file_name'];
                                                $pembayaran = $row['pembayaran_text'];
                                            ?>
                                                <tr class="border-bottom">
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
                                                        <span class="badge bg-warning text-dark px-3 py-2 rounded-pill">
                                                            <i class="bi bi-hourglass-split me-1"></i> <?= $lang['badge_pending'] ?? 'Tertunda' ?>
                                                        </span>
                                                    </td>
                                                    <td class="text-center">
                                                        <button type="button" class="btn btn-proses w-100 fw-bold" data-bs-toggle="modal" data-bs-target="#reviewPopup_<?= htmlspecialchars($req_id) ?>">
                                                            <?= $lang['btn_process'] ?? 'Proses' ?> <i class="bi bi-arrow-right-circle ms-2"></i>
                                                        </button>
                                                    </td>
                                                </tr>

                                                <?php 
                                                ob_start(); 
                                                ?>
                                                <div class="modal fade" id="reviewPopup_<?= htmlspecialchars($req_id) ?>" tabindex="-1" aria-hidden="true">
                                                    <div class="modal-dialog modal-xl modal-dialog-centered">
                                                        <div class="modal-content">
                                                            <div class="modal-header border-0 pb-0 pt-4 px-4">
                                                                <h5 class="modal-title fw-bold" style="color: #4b5563;"><?= $lang['modal_detail_request'] ?? 'Detail Permintaan' ?> #<?= htmlspecialchars($req_id) ?></h5>
                                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                            </div>
                                                            <div class="modal-body p-4">
                                                                <div class="row g-4">
                                                                    <div class="col-lg-7">
                                                                        <div class="panel-card p-3 border rounded-3">
                                                                            <h6 class="fw-bold text-danger mb-3"><?= $lang['trx_sect_pesanan'] ?? 'RINGKASAN PESANAN' ?></h6>
                                                                            <table class="table table-borderless summary-table m-0">
                                                                                <tr><th>Perusahaan</th><td>: <?= $pt ?></td></tr>
                                                                                <tr><th>Nama SA</th><td>: <?= htmlspecialchars($row['nama_sa']) ?></td></tr>
                                                                                <?php if ($row['qty_top'] > 0): ?>
                                                                                    <tr><th>Item Atasan</th><td>: Baju <?= $gender_txt ?> - <?= htmlspecialchars($row['qty_top']) ?> Pcs</td></tr>
                                                                                <?php endif; ?>
                                                                                <?php if ($row['qty_bottoms'] > 0): ?>
                                                                                    <tr><th>Item Bawahan</th><td>: Celana <?= $gender_txt ?> - <?= htmlspecialchars($row['qty_bottoms']) ?> Pcs</td></tr>
                                                                                <?php endif; ?>
                                                                                <tr><th>Total Jumlah</th><td class="fw-bold">: <?= $total_qty ?> Pcs</td></tr>
                                                                                <tr><th>Total Harga</th><td class="fw-bold text-danger">: Rp<?= number_format($row['total_harga'], 0, ',', '.') ?></td></tr>
                                                                            </table>
                                                                        </div>
                                                                    </div>
                                                                    <div class="col-lg-5">
                                                                        <div class="panel-card p-3 border rounded-3 d-flex flex-column h-100">
                                                                            <?php if (strpos($req_id, 'RT-') === 0): ?>
                                                                                <!-- TAMPILAN KHUSUS JIKA TIKET RETUR -->
                                                                                <h6 class="fw-bold text-secondary mb-3">KONFIRMASI RETUR</h6>
                                                                                <div class="bukti-box p-3 text-center border rounded-3 bg-light mb-3 d-flex flex-column justify-content-center" style="flex-grow: 1;">
                                                                                    <i class="bi bi-arrow-return-left text-secondary mb-2 d-block" style="font-size: 40px;"></i>
                                                                                    <span class="text-secondary fw-bold" style="font-size: 14px;">Transaksi Retur Seragam</span>
                                                                                    <span class="text-muted mt-1" style="font-size: 12px;">(Tidak memerlukan bukti pembayaran)</span>
                                                                                </div>
                                                                            <?php else: ?>
                                                                                <!-- TAMPILAN JIKA TIKET REQUEST BARU (FR) -->
                                                                                <h6 class="fw-bold text-danger mb-3">KONFIRMASI PEMBAYARAN</h6>
                                                                                <p class="fw-bold mb-3" style="font-size: 14px; color: #1f2937;">
                                                                                    <?= $lang['rep_th_metode_pembayaran'] ?? 'Metode Pembayaran' ?>: 
                                                                                    <span class="text-danger"><?= htmlspecialchars($pembayaran) ?></span>
                                                                                </p>

                                                                                <div class="bukti-box p-3 text-center border rounded-3 bg-light mb-3">
                                                                                    <?php if ($file_path !== '#'): ?>
                                                                                        <?php 
                                                                                            $ext = strtolower(pathinfo($file_path, PATHINFO_EXTENSION));
                                                                                            $is_image = in_array($ext, ['jpg', 'jpeg', 'png', 'webp', 'gif']);
                                                                                        ?>
                                                                                        <?php if ($is_image): ?>
                                                                                            <div class="mb-2">
                                                                                                <img src="<?= htmlspecialchars($file_path) ?>" alt="Bukti Transfer" style="width: 100%; max-height: 200px; object-fit: contain; border-radius: 8px; background: #fff; border: 1px solid #e5e7eb;">
                                                                                            </div>
                                                                                        <?php else: ?>
                                                                                            <div style="background: #f8fafc; padding: 20px; border-radius: 8px; margin-bottom: 12px; border: 1px solid #e2e8f0;">
                                                                                                <i class="bi bi-file-earmark-pdf-fill text-danger d-block mb-2" style="font-size: 40px;"></i>
                                                                                                <span class="fw-bold text-secondary" style="font-size: 13px;">Dokumen <?= strtoupper($ext) ?></span>
                                                                                            </div>
                                                                                        <?php endif; ?>

                                                                                        <p class="text-secondary mb-3 small text-truncate" title="<?= htmlspecialchars($file_name) ?>" style="font-size: 12px;">
                                                                                            <?= htmlspecialchars($file_name) ?>
                                                                                        </p>

                                                                                        <a href="<?= htmlspecialchars($file_path) ?>" target="_blank" class="btn btn-sm text-white fw-bold w-100 py-2 shadow-sm" style="background-color: #b91c1c; border-radius: 6px;">
                                                                                            <i class="bi bi-box-arrow-up-right me-1"></i> Buka File
                                                                                        </a>
                                                                                    <?php else: ?>
                                                                                        <div class="py-3">
                                                                                            <i class="bi bi-file-earmark-x mb-2 d-block text-muted" style="font-size: 40px;"></i>
                                                                                            <span class="text-danger fw-bold" style="font-size: 13px;">Bukti transfer belum diunggah / tidak ditemukan</span>
                                                                                        </div>
                                                                                    <?php endif; ?>
                                                                                </div>
                                                                            <?php endif; ?>

                                                                            <!-- TOMBOL APPROVE -->
                                                                            <div class="mt-auto pt-2">
                                                                                <a href="transaksi.php?id=<?= urlencode($req_id) ?>&pt=<?= urlencode($row['perusahaan']) ?>" class="btn btn-aprove-custom fw-bold w-100 py-2 shadow-sm">
                                                                                    <i class="bi bi-check-circle me-1"></i> <?= $lang['modal_approve_trx'] ?? 'Approve Request' ?>
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
                                                $popupsPending .= ob_get_clean();
                                            endforeach; 
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

                        <!-- CETAK POPUP MODAL PENDING -->
                        <div id="pending-popups-container">
                            <?= $popupsPending ?>
                        </div>
                    </div>
                </div>

                <!-- ================= TAB 2: TABEL DONE ================= -->
                <div class="tab-pane fade" id="done-panel" role="tabpanel">
                    <?php $popupsDone = ""; ?>
                    <!-- KODE BARU: -->
                    <div class="table-card shadow-sm border-0 rounded-3 mb-4">
                        <div class="table-responsive">
                            <table class="table align-middle mb-0">
                                <thead class="table-light text-secondary small border-bottom">
                                    <tr>
                                        <th scope="col" width="15%"><?= $lang['th_no_request'] ?? 'NO. REQUEST' ?></th>
                                        <th scope="col" width="45%"><?= $lang['th_detail_karyawan'] ?? 'DETAIL KARYAWAN & ITEM' ?></th>
                                        <th scope="col" width="15%" class="text-center"><?= $lang['table_status'] ?? 'STATUS' ?></th>
                                        <th scope="col" width="25%" class="text-center"><?= $lang['table_action'] ?? 'AKSI' ?></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (!empty($requests_done)): ?>
                                        <?php foreach ($requests_done as $row_done): 
                                            $req_id     = $row_done['request_id'];
                                            $pt         = htmlspecialchars($row_done['perusahaan']);
                                            $gender_txt = ($row_done['gender'] === 'male') ? 'SA Pria' : 'SA Wanita';
                                            $tgl_done   = !empty($row_done['tgl_transaksi']) ? date('d M Y', strtotime($row_done['tgl_transaksi'])) : '-';
                                            $total_qty  = $row_done['qty_top'] + $row_done['qty_bottoms'];
                                            $file_path  = $row_done['file_path'];
                                            $file_name  = $row_done['file_name'];
                                        ?>
                                            <tr class="border-bottom">
                                                <td><span class="req-badge">#<?= htmlspecialchars($req_id) ?></span></td>
                                                <td>
                                                    <div class="fw-bold text-dark" style="font-size: 15px;"><?= $pt ?></div>
                                                    <div class="text-secondary mt-1" style="font-size: 13px;">
                                                        <i class="bi bi-box-seam me-1"></i> Request Seragam <?= $gender_txt ?>
                                                        <span class="mx-2 text-muted">|</span> 
                                                        <i class="bi bi-check2-square me-1 text-success"></i> <?= $lang['badge_done'] ?? 'Selesai' ?>: <?= $tgl_done ?>
                                                    </div>
                                                </td>
                                                <td class="text-center">
                                                    <span class="badge bg-success px-3 py-2 rounded-pill">
                                                        <i class="bi bi-check-all me-1"></i> <?= $lang['badge_done'] ?? 'Selesai' ?>
                                                    </span>
                                                </td>
                                                <td class="text-center">
                                                    <button type="button" class="btn btn-cetak-custom w-100 fw-semibold" data-bs-toggle="modal" data-bs-target="#reviewPopup_<?= htmlspecialchars($req_id) ?>">
                                                        <i class="bi bi-eye me-1"></i> <?= $lang['btn_view_details'] ?? 'Lihat Detail' ?>
                                                    </button>
                                                </td>
                                            </tr>

                                            <?php 
                                            ob_start(); 
                                            ?>
                                            <div class="modal fade" id="reviewPopup_<?= htmlspecialchars($req_id) ?>" tabindex="-1" aria-hidden="true">
                                                <div class="modal-dialog modal-lg modal-dialog-centered">
                                                    <div class="modal-content">
                                                        <div class="modal-header border-0 pb-0 pt-4 px-4">
                                                            <h5 class="modal-title fw-bold text-success"><?= $lang['modal_detail_request'] ?? 'Detail Permintaan' ?> #<?= htmlspecialchars($req_id) ?> (<?= $lang['badge_done'] ?? 'Selesai' ?>)</h5>
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
                                                                        <i class="bi bi-check-circle-fill me-1"></i> <?= $lang['modal_trx_selesai'] ?? 'Transaksi Selesai' ?>
                                                                    </span>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <?php 
                                            $popupsDone .= ob_get_clean();
                                        endforeach; 
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
                    <!-- CETAK POPUP MODAL DONE -->
                    <?= $popupsDone ?>
                </div>

            </div>

        </main>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="assets/js/scripts.js?v=<?= time() ?>"></script>
</body>
</html>
<?php 
if (isset($pdo)) {
    $pdo = null;
}
?>