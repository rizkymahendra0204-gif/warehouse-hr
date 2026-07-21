<?php
include 'controllers/query_pending.php';
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pending Request - HR Warehouse</title>
    
    <!-- Bootstrap & Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    
    <!-- file CSS  -->
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>

<div class="app-container">

    <!-- Memanggil file Sidebar -->
    <?php include 'includes/sidebar.php'; ?>

    <div class="main-wrapper">
        
        <!-- Memanggil file Topbar -->
        <?php include 'includes/topbar.php'; ?>

        <!-- MAIN CONTENT AREA -->
        <main class="content-area p-4">
            
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div class="page-title">Pending Request</div>
                
                <div class="input-group" style="width: 250px;">
                    <span class="input-group-text bg-white border-end-0"><i class="bi bi-search text-secondary"></i></span>
                    <input type="text" id="searchInput" class="form-control border-start-0 ps-0" placeholder="Cari No. Request...">
                </div>
            </div>
            
            <!-- Table Container -->
            <div class="table-card">
                <div class="table-responsive">
                    <table class="table align-middle mb-0">
                        <thead>
                            <tr>
                                <th scope="col" width="15%">No. Request</th>
                                <th scope="col" width="40%">Detail Karyawan & Item</th>
                                <th scope="col" width="15%" class="text-center">Status</th>
                                <th scope="col" width="20%" class="text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            
                            <?php 
                            // Variabel untuk menampung elemen Popup agar dicetak di luar tabel
                            $popupsHTML = ""; 

                            if ($result && $result->num_rows > 0): 
                                while ($row = $result->fetch_assoc()):
                                    // Ekstrak data
                                    $req_id     = $row['request_id'];
                                    $pt         = htmlspecialchars($row['perusahaan']);
                                    $gender_txt = ($row['gender'] === 'male') ? 'SA Pria' : 'SA Wanita';
                                    $tgl        = date('d M Y', strtotime($row['tgl_request']));
                                    $total_qty  = $row['qty_top'] + $row['qty_bottoms'];
                                    
                                    // Ambil nama file asli
                                    $file_name = !empty($row['upload']) ? basename($row['upload']) : 'Tidak ada file';

                                    // Buat path URL yang mengarah ke folder upload milik form
                                    if (!empty($row['upload'])) {
             
                                        $file_path = '/Request.Form.2/upload/' . basename($row['upload']);
                                    } else {
                                        $file_path = '#';
                                    }
                            ?>
                                    <!-- Baris Data Dinamis -->
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
                                        <td class="text-center"><span class="status-badge">Pending</span></td>
                                        <td class="text-center">
                                            <!-- Tombol Pemicu Popup -->
                                            <button type="button" class="btn btn-proses w-100" data-bs-toggle="modal" data-bs-target="#reviewPopup_<?= htmlspecialchars($req_id) ?>">
                                                Proses <i class="bi bi-arrow-right-circle ms-2"></i>
                                            </button>
                                        </td>
                                    </tr>

                            <?php 
                                    /* === MERAKIT POPUP KHUSUS UNTUK BARIS INI === */
                                    ob_start(); // Mulai menampung output HTML popup
                            ?>
                                    <div class="modal fade" id="reviewPopup_<?= htmlspecialchars($req_id) ?>" tabindex="-1" aria-labelledby="reviewPopupLabel_<?= htmlspecialchars($req_id) ?>" aria-hidden="true">
                                        <div class="modal-dialog modal-xl modal-dialog-centered">
                                            <div class="modal-content">
                                                
                                                <!-- Popup Header -->
                                                <div class="modal-header border-0 pb-0 pt-4 px-4 align-items-center">
                                                    <h4 class="modal-title fw-bold m-0" id="reviewPopupLabel_<?= htmlspecialchars($req_id) ?>" style="color: #4b5563;">Detail Transaksi #<?= htmlspecialchars($req_id) ?></h4>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                </div>
                                                
                                                <!-- Popup Body -->
                                                <div class="modal-body p-4">
                                                    <div class="row g-4">
                                                        
                                                        <!-- Kolom Kiri: Ringkasan (SECTION A) -->
                                                        <div class="col-lg-7">
                                                            <div class="panel-card">
                                                                <div class="d-flex align-items-center mb-4">
                                                                    <div class="icon-box-square">A</div>
                                                                    <h6 class="fw-bold m-0" style="color: #b91c1c; letter-spacing: 0.5px;">RINGKASAN PESANAN</h6>
                                                                </div>
                                                                
                                                                <table class="table table-borderless summary-table m-0">
                                                                    <tr>
                                                                        <th class="ps-0">Perusahaan</th>
                                                                        <td>: <?= $pt ?></td>
                                                                    </tr>
                                                                    <tr>
                                                                        <th class="ps-0">Nama SA</th>
                                                                        <td>: <?= htmlspecialchars($row['nama_sa']) ?></td>
                                                                    </tr>
                                                                    <tr>
                                                                        <th class="ps-0">Alamat</th>
                                                                        <td>: <?= nl2br(htmlspecialchars($row['alamat'])) ?></td>
                                                                    </tr>
                                                                    
                                                                    <?php if ($row['qty_top'] > 0): ?>
                                                                    <tr>
                                                                        <th class="ps-0">Item Atasan</th>
                                                                        <td>: Baju <?= $gender_txt ?> (Size <?= htmlspecialchars($row['size_top']) ?>) - <?= htmlspecialchars($row['qty_top']) ?> Pcs</td>
                                                                    </tr>
                                                                    <?php endif; ?>

                                                                    <?php if ($row['qty_bottoms'] > 0): ?>
                                                                    <tr>
                                                                        <th class="ps-0">Item Bawahan</th>
                                                                        <td>: Celana <?= $gender_txt ?> (Size <?= htmlspecialchars($row['size_bottoms']) ?>) - <?= htmlspecialchars($row['qty_bottoms']) ?> Pcs</td>
                                                                    </tr>
                                                                    <?php endif; ?>

                                                                    <tr>
                                                                        <th class="ps-0 border-0 pb-0">Total Jumlah</th>
                                                                        <td class="border-0 pb-0 fw-bold">: <?= $total_qty ?> Pcs</td>
                                                                    </tr>
                                                                    <tr>
                                                                        <th class="ps-0 border-0 pb-0">Total Harga</th>
                                                                        <td class="border-0 pb-0 fw-bold text-danger">: Rp<?= number_format($row['total_harga'], 0, ',', '.') ?></td>
                                                                    </tr>
                                                                </table>
                                                            </div>
                                                        </div>

                                                        <!-- Kolom Kanan: Konfirmasi (SECTION B) -->
                                                        <div class="col-lg-5">
                                                            <div class="panel-card d-flex flex-column">
                                                                <div class="d-flex align-items-center mb-4">
                                                                    <div class="icon-box-square">B</div>
                                                                    <h6 class="fw-bold m-0" style="color: #b91c1c; letter-spacing: 0.5px;">KONFIRMASI</h6>
                                                                </div>
                                                                
                                                                <div class="mb-4">
                                                                    <p class="fw-bold mb-2" style="font-size: 14px; color: #1f2937;">Metode Pembayaran: <span class="text-danger"><?= strtoupper(str_replace('_', ' ', $row['pembayaran'])) ?></span></p>
                                                                    
                                                                    <?php if ($row['pembayaran'] === 'transfer'): ?>
                                                                    <div class="bukti-box p-3 text-center">
                                                                        <?php 
                                                                        if ($file_path !== '#'): 
                                                                            // Deteksi Ekstensi File
                                                                            $ext = strtolower(pathinfo($file_path, PATHINFO_EXTENSION));
                                                                            $is_image = in_array($ext, ['jpg', 'jpeg', 'png', 'gif']);
                                                                        ?>
                                                                            
                                                                            <?php if ($is_image): ?>
                                                                                <img src="<?= htmlspecialchars($file_path) ?>" alt="Bukti Transfer" style="width: 100%; max-height: 200px; object-fit: contain; border-radius: 8px; margin-bottom: 12px; background: #fff; border: 1px solid #e5e7eb;">
                                                                            <?php else: ?>
                                                                                <div style="background: #f8fafc; padding: 24px; border-radius: 8px; margin-bottom: 12px; border: 1px solid #e2e8f0;">
                                                                                    <i class="bi bi-file-earmark-pdf-fill text-danger d-block mb-2" style="font-size: 48px;"></i>
                                                                                    <span class="fw-bold text-secondary" style="font-size: 14px;">Dokumen <?= strtoupper($ext) ?></span>
                                                                                </div>
                                                                            <?php endif; ?>

                                                                            <p class="text-secondary mb-3" style="font-size: 12px; word-break: break-all;"><?= htmlspecialchars($file_name) ?></p>
                                                                            
                                                                            <a href="<?= htmlspecialchars($file_path) ?>" target="_blank" class="btn btn-sm text-white fw-bold w-100 py-2" style="background-color: #b91c1c; border-radius: 6px;">
                                                                                <i class="bi bi-box-arrow-up-right me-1"></i> Buka File
                                                                            </a>
                                                                        <?php else: ?>
                                                                            <i class="bi bi-file-earmark-image mb-2 d-block" style="font-size: 48px; color: #9ca3af;"></i>
                                                                            <span class="text-danger fw-bold" style="font-size: 12px;">File tidak ditemukan</span>
                                                                        <?php endif; ?>
                                                                    </div>
                                                                    <?php else: ?>
                                                                    <div class="bukti-box p-4 text-center">
                                                                        <i class="bi bi-wallet2 mb-2 d-block" style="font-size: 48px; color: #9ca3af;"></i>
                                                                        <p class="text-secondary m-0" style="font-size: 13px;">Pembayaran Tidak memerlukan bukti transfer.</p>
                                                                    </div>
                                                                    <?php endif; ?>
                                                                </div>

                                                                <div class="d-grid gap-2 mt-auto">
                                                                    <a href="transaksi.php?id=<?= urlencode($req_id) ?>&pt=<?= urlencode($row['perusahaan']) ?>" class="btn text-white fw-bold py-2" style="background-color: #15803d; border-radius: 6px;">
                                                                        Approve Request
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
                                    // Simpan popup yang dirakit ke dalam variabel
                                    $popupsHTML .= ob_get_clean();
                                endwhile; 
                            else: 
                            ?>
                                <tr>
                                    <td colspan="4" class="text-center text-secondary py-5">
                                        <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                                        Belum ada request seragam yang pending saat ini.
                                    </td>
                                </tr>
                            <?php endif; ?>
                            
                        </tbody>
                    </table>
                </div>
            </div>
            
        </main>
    </div>
</div>

<!-- ================= CETAK SEMUA POPUP REVIEW REQUEST DI SINI ================= -->
<?= $popupsHTML ?>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="assets/js/scripts.js"></script>
</body>
</html>
<?php 
// Tutup koneksi database
if(isset($conn)){
    $conn->close();
}
?>