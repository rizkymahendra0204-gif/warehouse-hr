<?php
// 1. Panggil koneksi database di baris pertama
include 'includes/db.php';

$conn = new mysqli($host, $user, $pass, $db);

// Catch Parameter URL (jika dipanggil via GET)
$auto_id_transaksi = isset($_GET['req']) ? $_GET['req'] : '';
$auto_sales_id     = isset($_GET['sales']) ? $_GET['sales'] : '';
$auto_nama         = isset($_GET['nama']) ? $_GET['nama'] : '';
$auto_detail       = isset($_GET['detail']) ? $_GET['detail'] : '';

$is_auto       = !empty($auto_id_transaksi); 
$readonly_attr = $is_auto ? 'readonly' : '';
$bg_class      = $is_auto ? 'bg-light' : '';

// Jika diakses via URL GET, ambil daftar barcode transaksi tersebut dari DB
$auto_barcodes_json = '[]';
if ($is_auto) {
    $stmt_get_bc = $conn->prepare("SELECT barcode FROM transaksi WHERE transaction_id = ?");
    $stmt_get_bc->bind_param("s", $auto_id_transaksi);
    $stmt_get_bc->execute();
    $res_bc = $stmt_get_bc->get_result();
    $bc_list = [];
    while ($row_bc = $res_bc->fetch_assoc()) {
        $bc_list[] = $row_bc['barcode'];
    }
    $auto_barcodes_json = json_encode($bc_list);
    $stmt_get_bc->close();
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Proses Return - HR Warehouse</title>
    
    <!-- Bootstrap & Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    
    <!-- CSS File -->
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>

<div class="app-container">

    <!-- Sidebar -->
    <?php include 'includes/sidebar.php'; ?>

    <!-- MAIN CONTENT -->
    <div class="main-wrapper">
        
        <?php include 'includes/topbar.php'; ?>

        <!-- MAIN CONTENT AREA -->
        <main class="content-area p-4">

            <!-- ======================================================= -->
            <!-- VIEW 1: DAFTAR REQUEST PENDING RETURN                  -->
            <!-- ======================================================= -->

            <div id="view-return-list" style="<?php echo $is_auto ? 'display: none;' : 'display: block;'; ?>">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div class="page-title">Pengajuan Return
                        <p class="text-secondary m-0 mt-1" style="font-size: 14px;">Manajemen untuk pengajuan return</p>
                    </div>
                    <div style="width: 280px;">
                        <div class="input-group">
                            <span class="input-group-text bg-white border-end-0"><i class="bi bi-search text-muted"></i></span>
                            <input type="text" id="searchTrx" class="form-control border-start-0 ps-0" placeholder="Cari No. Transaksi / Perusahaan..." onkeyup="filterTable()">
                        </div>
                    </div>
                </div>

                <div class="bg-white border rounded-3 p-3 mb-4 shadow-sm">
                    <div class="table-responsive">
                        <table class="table table-borderless align-middle m-0" id="tableTrx">
                            <thead class="text-secondary small fw-bold border-bottom">
                                <tr>
                                    <th style="width: 15%; text-align: center;">NO. TRANSAKSI</th>
                                    <th style="width: 45%;">DETAIL ITEM TRANSAKSI</th>
                                    <th style="width: 20%; text-align: center;">STATUS</th>
                                    <th style="width: 20%; text-align: center;">AKSI</th>
                                </tr>
                            </thead>
                            <tbody>
                            <?php
                            $sql = "SELECT 
                                        t.transaction_id,
                                        t.request_id,
                                        t.id_sales,
                                        t.tgl_transaksi,
                                        TRIM(td.barcode) AS barcode_item,
                                        rf.perusahaan,
                                        rf.nama_sa,
                                        mi.tipe,
                                        mi.gender,
                                        mi.size,
                                        mi.status_transaksi,
                                        mi.status_barang
                                    FROM transaksi t
                                    INNER JOIN transaksi_detail td ON t.transaction_id = td.transaction_id
                                    INNER JOIN request_form rf ON t.request_id = rf.request_id
                                    INNER JOIN master_item mi ON TRIM(td.barcode) = TRIM(mi.barcode)
                                    ORDER BY t.transaction_id DESC, td.barcode ASC";

                            $query = mysqli_query($conn, $sql);

                            if ($query && mysqli_num_rows($query) > 0) {
                                while ($row = mysqli_fetch_assoc($query)) {
                                    $no_trx       = htmlspecialchars($row['transaction_id']);
                                    $barcode_item = htmlspecialchars($row['barcode_item']);
                                    $detail_item  = htmlspecialchars($row['tipe'] . " " . $row['gender'] . " - Size " . $row['size']);
                                    $tgl_trx      = !empty($row['tgl_transaksi']) ? date('d M Y', strtotime($row['tgl_transaksi'])) : '-';

                                    // LOGIKA STATUS
                                    $raw_st_transaksi = trim($row['status_transaksi'] ?? '');
                                    $raw_st_barang    = trim($row['status_barang'] ?? '');

                                    $st_transaksi_lower = strtolower($raw_st_transaksi);
                                    $st_barang_lower    = strtolower($raw_st_barang);

                                    // Tampilan teks status
                                    if (!empty($raw_st_transaksi) && !empty($raw_st_barang)) {
                                        $status_display = $raw_st_transaksi . " (" . $raw_st_barang . ")";
                                    } else {
                                        $status_display = $raw_st_transaksi ?: ($raw_st_barang ?: '-');
                                    }

                                    // PENENTUAN KELAYAKAN RETURN (JIKA AVAILABLE / INACTIVE = SUDAH DIRETURN):
                                    $already_returned = ($st_transaksi_lower === 'available' || $st_barang_lower === 'inactive');
                                    $can_return       = !$already_returned;

                                    // Logika penentuan warna & ikon
                                    if ($can_return) {
                                        $text_color = '#16a34a'; // Hijau jika masih Sold Out (Active)
                                        $icon_class = 'bi-check-circle-fill';
                                    } else {
                                        $text_color = '#dc2626'; // Merah jika sudah Available (Inactive)
                                        $icon_class = 'bi-arrow-counterclockwise';
                                    }

                                    // Membungkus single barcode ke JSON safe
                                    $single_barcode_json = htmlspecialchars(json_encode([$barcode_item]), ENT_QUOTES, 'UTF-8');
                                    $detail_with_barcode = htmlspecialchars($detail_item . " (" . $barcode_item . ")", ENT_QUOTES, 'UTF-8');
                            ?>
                                    <tr class="border-bottom">
                                        <td class="text-center"><span class="badge-trx">#<?php echo $no_trx; ?></span></td>
                                        
                                        <td>
                                            <div class="fw-bold"><?php echo htmlspecialchars($row['perusahaan']); ?> (<?php echo htmlspecialchars($row['nama_sa']); ?>)</div>
                                            <div class="text-muted small">
                                                <i class="bi bi-box-seam me-1"></i> <b>[<?php echo $barcode_item; ?>]</b> <?php echo $detail_item; ?> &nbsp;|&nbsp; 
                                                <i class="bi bi-calendar3 me-1"></i> <?php echo $tgl_trx; ?>
                                            </div>
                                        </td>
                                        
                                        <td>
                                            <div class="d-flex align-items-center justify-content-center gap-2 fw-bold" style="color: <?php echo $text_color; ?>; font-size: 0.75rem; white-space: nowrap;">
                                                <i class="bi <?php echo $icon_class; ?>" style="font-size: 0.95rem;"></i>
                                                <span><?php echo htmlspecialchars($status_display); ?></span>
                                            </div>
                                        </td>
                                        
                                        <td class="text-center">
                                            <?php if ($can_return): ?>
                                                <!-- Tombol Aktif jika barang belum direturn -->
                                                <button type="button" class="btn btn-proses-custom" 
                                                        onclick="openProcessPage(
                                                            '<?php echo addslashes($no_trx); ?>', 
                                                            '<?php echo addslashes($row['id_sales']); ?>', 
                                                            '<?php echo addslashes($row['nama_sa']); ?>', 
                                                            '<?php echo addslashes($detail_with_barcode); ?>',
                                                            <?php echo $single_barcode_json; ?>
                                                        )">
                                                    Proses Return <i class="bi bi-arrow-right-circle ms-1"></i>
                                                </button>
                                            <?php else: ?>
                                                <!-- Tombol Disabled jika barang sudah direturn -->
                                                <button type="button" class="btn btn-secondary btn-sm fw-bold px-3 opacity-75" disabled title="Barang ini sudah pernah direturn">
                                                    <i class="bi bi-check2-all me-1"></i> Sudah Direturn
                                                </button>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                            <?php 
                                }
                            } else {
                                echo '<tr><td colspan="4" class="text-center py-4 text-muted"><i class="bi bi-inbox me-1"></i> Tidak ada data transaksi ditemukan.</td></tr>';
                            }
                            ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- ======================================================= -->
            <!-- VIEW 2: FORM PROSES RETURN                              -->
            <!-- ======================================================= -->

            <div id="view-return-process" style="<?php echo $is_auto ? 'display: block;' : 'display: none;'; ?>">
                
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div class="page-title fs-4 fw-bold">Return Item Transaksi</div>
                    <a href="return.php" class="btn btn-outline-secondary fw-bold px-3 py-2" style="border-radius: 6px;">
                        <i class="bi bi-arrow-left me-1"></i> Kembali ke Return
                    </a>
                </div>

                <form action="controllers/proses_return.php" method="POST" id="formReturn">
                    
                    <!-- SECTION 1: Informasi Tiket Return -->
                    <div class="bg-white border rounded-3 p-4 mb-4 shadow-sm">
                        <h6 class="fw-bold mb-4" style="color: #4b5563;">
                            <i class="bi bi-arrow-counterclockwise me-2"></i>Informasi Tiket Return
                        </h6>
                        <div class="row g-4">
                            <div class="col-md-6 col-lg-3">
                                <label class="form-label fw-semibold text-secondary" style="font-size: 13px;">ID Transaksi</label>
                                <input type="text" class="form-control <?php echo $bg_class; ?>" id="input_no_return" name="no_return" value="<?php echo htmlspecialchars($auto_id_transaksi); ?>" placeholder="Contoh: TRX-000001" readonly>
                            </div>
                            <div class="col-md-6 col-lg-3">
                                <label class="form-label fw-semibold text-secondary" style="font-size: 13px;">ID Sales</label>
                                <input type="text" class="form-control <?php echo $bg_class; ?>" id="input_id_sales" name="id_request_awal" value="<?php echo htmlspecialchars($auto_sales_id); ?>" placeholder="ID Sales" readonly>
                            </div>
                            <div class="col-md-6 col-lg-3">
                                <label class="form-label fw-semibold text-secondary" style="font-size: 13px;">Nama SA</label>
                                <input type="text" class="form-control <?php echo $bg_class; ?>" id="input_nama" name="nama" value="<?php echo htmlspecialchars($auto_nama); ?>" placeholder="Nama SA" readonly>
                            </div>
                            <div class="col-md-6 col-lg-3">
                                <label class="form-label fw-semibold text-secondary" style="font-size: 13px;">Tanggal Return</label>
                                <input type="date" class="form-control bg-light" name="tgl_return" value="<?php echo date('Y-m-d'); ?>" readonly>
                            </div>
                        </div>
                    </div>

                    <!-- SECTION 2: Detail Pesanan -->
                    <div class="bg-white border rounded-3 p-4 mb-4 shadow-sm">
                        <h6 class="fw-bold mb-4" style="color: #4b5563;">
                            <i class="bi bi-cart-check me-2"></i>Detail Pesanan
                        </h6>
                        <div class="border-top pt-2">
                            <table class="table table-borderless m-0" style="font-size: 14px;">
                                <tbody id="rincian-item-list">
                                    <?php if ($is_auto): ?>
                                        <tr style="border-bottom: 1px solid #f1f5f9;">
                                            <td class="fw-bold py-3 ps-0 text-secondary" width="15%">Info Order</td>
                                            <td class="py-3 text-dark">: <?php echo !empty($auto_detail) ? htmlspecialchars($auto_detail) : 'Detail Request Transaksi #' . htmlspecialchars($auto_id_transaksi); ?></td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- SECTION 3: Daftar Item Barang -->
                    <div class="bg-white border rounded-3 p-4 mb-4 shadow-sm">
                        <h6 class="fw-bold mb-3" style="color: #b91c1c;">
                            <i class="bi bi-box-seam me-2"></i>Daftar Item Barang dalam Transaksi Ini
                        </h6>
                        <p class="text-muted small mb-3">Tentukan kondisi barang untuk setiap item yang dikembalikan di bawah ini:</p>

                        <!-- CONTAINER ITEM DINAMIS -->
                        <div id="dynamic-item-container">
                            <!-- Barcode items dirender otomatis via JS -->
                        </div>
                    </div>

                    <!-- SECTION 4: Tombol Aksi -->
                    <div class="d-flex justify-content-end gap-3 mt-4 mb-5">
                        <button type="button" class="btn btn-light border fw-bold px-4 text-secondary" style="border-radius: 6px;" onclick="cancelProcess()">Batal</button>
                        <button type="submit" class="btn fw-bold text-white px-5" style="background-color: #b91c1c; border-radius: 6px;">Proses Return</button>
                    </div>
                </form>

            </div>

        </main>
        
    </div>
</div>

<!-- Scripts Utama (jQuery & Bootstrap) -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<!-- Jembatan PHP ke JS (Wajib di atas scripts.js) -->
<script>
    // Passing data PHP ke global window JavaScript
    window.IS_AUTO_RETURN = <?php echo json_encode($is_auto); ?>;
    window.AUTO_BARCODES  = <?php echo $auto_barcodes_json; ?>;
</script>

<!-- Panggil File Eksternal JS -->
<script src="assets/js/scripts.js"></script>

</body>
</html>