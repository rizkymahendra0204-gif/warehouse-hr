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
                    <div class="page-title fw-bold fs-4">Pengajuan Return Barang</div>
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
                            <tbody>
                            <?php
                            // Query tanpa GROUP BY agar setiap barcode pada TRX yang sama tampil di baris terpisah
                            $sql = "SELECT 
                                        t.transaction_id,
                                        t.request_id,
                                        t.id_sales,
                                        t.tgl_transaksi,
                                        TRIM(t.barcode) AS barcode_item,
                                        rf.perusahaan,
                                        rf.nama_sa,
                                        mi.tipe,
                                        mi.gender,
                                        mi.size,
                                        mi.status_transaksi,
                                        mi.status_barang
                                    FROM transaksi t
                                    INNER JOIN request_form rf ON t.request_id = rf.request_id
                                    INNER JOIN master_item mi ON TRIM(t.barcode) = TRIM(mi.barcode)
                                    ORDER BY t.transaction_id DESC, t.barcode ASC";

                            $query = mysqli_query($conn, $sql);

                            if ($query && mysqli_num_rows($query) > 0) {
                                while ($row = mysqli_fetch_assoc($query)) {
                                    $no_trx       = htmlspecialchars($row['transaction_id']);
                                    $barcode_item = htmlspecialchars($row['barcode_item']);
                                    $detail_item  = htmlspecialchars($row['tipe'] . " " . $row['gender'] . " - Size " . $row['size']);
                                    $tgl_trx      = !empty($row['tgl_transaksi']) ? date('d M Y', strtotime($row['tgl_transaksi'])) : '-';

                                    // LOGIKA STATUS
                                    $status_tx  = $row['status_transaksi'] ?? ''; 
                                    $status_brg = $row['status_barang'] ?? '';

                                    if (!empty($status_brg) && !empty($status_tx)) {
                                        $status_display = $status_brg . " (" . $status_tx . ")";
                                    } else {
                                        $status_display = $status_brg ?: ($status_tx ?: '-');
                                    }

                                    // LOGIKA WARNA & IKON
                                    if (strtolower($status_tx) === 'soldout' && strtolower($status_brg) === 'active') {
                                        $text_color = '#16a34a';
                                        $icon_class = 'bi-check-circle-fill';
                                    } else {
                                        $text_color = '#334155';
                                        $icon_class = 'bi-x-circle-fill';
                                    }

                                    // Membungkus single barcode ke JSON safe untuk dikirim ke openProcessPage
                                    $single_barcode_json = htmlspecialchars(json_encode([$barcode_item]), ENT_QUOTES, 'UTF-8');
                                    $detail_with_barcode = htmlspecialchars($detail_item . " (" . $barcode_item . ")", ENT_QUOTES, 'UTF-8');
                            ?>
                                    <tr class="border-bottom">
                                        <!-- No. Transaksi -->
                                        <td class="text-center"><span class="badge-trx">#<?php echo $no_trx; ?></span></td>
                                        
                                        <!-- Detail Item Transaksi -->
                                        <td>
                                            <div class="fw-bold"><?php echo htmlspecialchars($row['perusahaan']); ?> (<?php echo htmlspecialchars($row['nama_sa']); ?>)</div>
                                            <div class="text-muted small">
                                                <i class="bi bi-box-seam me-1"></i> <b>[<?php echo $barcode_item; ?>]</b> <?php echo $detail_item; ?> &nbsp;|&nbsp; 
                                                <i class="bi bi-calendar3 me-1"></i> <?php echo $tgl_trx; ?>
                                            </div>
                                        </td>
                                        
                                        <!-- Status dengan Ikon -->
                                        <td>
                                            <div class="d-flex align-items-center justify-content-center gap-2 fw-bold" style="color: <?php echo $text_color; ?>; font-size: 0.75rem; white-space: nowrap;">
                                                <i class="bi <?php echo $icon_class; ?>" style="font-size: 0.95rem;"></i>
                                                <span><?php echo htmlspecialchars($status_display); ?></span>
                                            </div>
                                        </td>
                                        
                                        <!-- Tombol Aksi -->
                                        <td class="text-center">
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
                
                <!-- Header Halaman -->
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div class="page-title fs-4 fw-bold">Return Item Transaksi</div>
                </div>

                <!-- Alert Container Flash Message -->
                <div id="alertContainer"></div>

                <form action="controllers/proses_return.php" method="POST" id="formReturn">
                    
                    <!-- SECTION 1: Informasi Tiket Return -->
                    <div class="bg-white border rounded-3 p-4 mb-4 shadow-sm">
                        <h6 class="fw-bold mb-4" style="color: #4b5563;">
                            <i class="bi bi-arrow-counterclockwise me-2"></i>Informasi Tiket Return
                        </h6>
                        <div class="row g-4">
                            <div class="col-md-6 col-lg-3">
                                <label class="form-label fw-semibold text-secondary" style="font-size: 13px;">ID Transaksi</label>
                                <input type="text" class="form-control <?php echo $bg_class; ?>" id="input_no_return" name="no_return" value="<?php echo $auto_id_transaksi; ?>" placeholder="Contoh: TRX-000001" readonly>
                            </div>
                            <div class="col-md-6 col-lg-3">
                                <label class="form-label fw-semibold text-secondary" style="font-size: 13px;">ID Sales</label>
                                <input type="text" class="form-control <?php echo $bg_class; ?>" id="input_id_sales" name="id_request_awal" value="<?php echo $auto_sales_id; ?>" placeholder="ID Sales" readonly>
                            </div>
                            <div class="col-md-6 col-lg-3">
                                <label class="form-label fw-semibold text-secondary" style="font-size: 13px;">Nama SA</label>
                                <input type="text" class="form-control <?php echo $bg_class; ?>" id="input_nama" name="nama" value="<?php echo $auto_nama; ?>" placeholder="Nama SA" readonly>
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

                    <!-- SECTION 3: Daftar Item Barang yang Di-Return (Otomatis Terisi) -->
                    <div class="bg-white border rounded-3 p-4 mb-4 shadow-sm">
                        <h6 class="fw-bold mb-3" style="color: #b91c1c;">
                            <i class="bi bi-box-seam me-2"></i>Daftar Item Barang dalam Transaksi Ini
                        </h6>
                        <p class="text-muted small mb-3">Tentukan kondisi barang untuk setiap item yang dikembalikan di bawah ini:</p>

                        <!-- CONTAINER ITEM DINAMIS -->
                        <div id="dynamic-item-container">
                            <!-- Barcode items akan dirender otomatis di sini lewat JavaScript -->
                        </div>
                    </div>

                    <!-- SECTION 4: Tombol Aksi Bawah -->
                    <div class="d-flex justify-content-end gap-3 mt-4 mb-5">
                        <button type="button" class="btn btn-light border fw-bold px-4 text-secondary" style="border-radius: 6px;" onclick="cancelProcess()">Batal</button>
                        <button type="submit" class="btn fw-bold text-white px-5" style="background-color: #b91c1c; border-radius: 6px;">Proses Return</button>
                    </div>
                </form>

            </div>

        </main>
        
    </div>
</div>

<!-- Scripts -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="assets/js/scripts.js"></script>

<!-- ENGINE JAVASCRIPT LOGIC -->
<script>
    // Mapping barcode schema 9 digit
    const GENDER_MAP = { '1': 'Pria', '2': 'Wanita' };
    const TYPE_MAP   = { '01': 'Baju', '02': 'Celana' };
    const SIZE_MAP   = { '01': 'S', '02': 'M', '03': 'L', '04': 'XL' };

    let itemCount = 0;

    $(document).ready(function() {
        <?php if ($is_auto): ?>
            // Auto load jika dipanggil via GET parameter URL
            const initialBarcodes = <?php echo $auto_barcodes_json; ?>;
            loadTransactionItems(initialBarcodes);
        <?php endif; ?>
    });

    // --- PARSER BARCODE 9 DIGIT ---
    function parseBarcode(rawCode) {
        const clean = String(rawCode).replace(/\*/g, '').trim();
        if (clean.length !== 9 || isNaN(clean)) {
            return { raw: clean, text: `(Barcode: ${clean})` };
        }

        const gender = GENDER_MAP[clean.substring(0, 1)] || 'Unknown';
        const type   = TYPE_MAP[clean.substring(1, 3)]   || 'Item';
        const size   = SIZE_MAP[clean.substring(3, 5)]   || 'All Size';
        const num    = clean.substring(5, 9);

        return {
            raw: clean,
            text: `(${type} ${gender} - Ukuran ${size} - #${num})`
        };
    }

    // --- SWITCH VIEW & LOAD DATA OTOMATIS ---
    function openProcessPage(trxId, salesId, saName, detailInfo, barcodesArray) {
        document.getElementById('view-return-list').style.display = 'none';
        document.getElementById('view-return-process').style.display = 'block';

        document.getElementById('input_no_return').value = trxId;
        document.getElementById('input_id_sales').value = salesId;
        document.getElementById('input_nama').value = saName;

        document.getElementById('rincian-item-list').innerHTML = `
            <tr style="border-bottom: 1px solid #f1f5f9;">
                <td class="fw-bold py-3 ps-0 text-secondary" width="15%">Info Order</td>
                <td class="py-3 text-dark">: ${detailInfo}</td>
            </tr>
        `;

        document.getElementById('alertContainer').innerHTML = '';

        // Tampilkan otomatis semua barcode transaksi tersebut
        loadTransactionItems(barcodesArray);
    }

    function loadTransactionItems(barcodesArray) {
        const container = document.getElementById('dynamic-item-container');
        container.innerHTML = '';
        itemCount = 0;

        if (!barcodesArray || barcodesArray.length === 0) {
            container.innerHTML = '<div class="alert alert-warning">Tidak ada barcode terdeteksi pada transaksi ini.</div>';
            return;
        }

        barcodesArray.forEach((barcode) => {
            addItemRow(barcode);
        });
    }

    // --- TAMBAH BARIS ITEM OTOMATIS ---
    function addItemRow(barcodeVal) {
        itemCount++;
        const container = document.getElementById('dynamic-item-container');
        const id = itemCount;
        const parsed = parseBarcode(barcodeVal);

        const rowHtml = `
            <div class="item-row bg-white border rounded-3 p-3 mb-3 shadow-sm" id="item-row-${id}">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <span class="fw-bold item-number" style="color: #b91c1c; font-size: 14px;">
                        <i class="bi bi-box-seam me-2"></i>Barang #${id}
                        <small class="text-dark fw-normal ms-2">${parsed.text}</small>
                    </span>
                    <button type="button" class="btn btn-sm text-danger fw-bold" 
                            style="background-color: #fee2e2; border-radius: 4px; padding: 2px 8px;" 
                            onclick="removeItemRow(${id})">
                        <i class="bi bi-trash3 me-1"></i>Hapus
                    </button>
                </div>
                
                <div class="row g-4">
                    <div class="col-md-6">
                        <label class="form-label fw-semibold text-secondary" style="font-size: 13px;">Barcode Barang</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light text-danger"><i class="bi bi-upc-scan"></i></span>
                            <input type="text" 
                                   class="form-control bg-light fw-bold" 
                                   name="barcode_return[]" 
                                   value="${parsed.raw}" 
                                   readonly>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold text-secondary" style="font-size: 13px;">Kondisi / Alasan Return</label>
                        <select class="form-select" name="kondisi_return[]" required>
                            <option value="Layak">Layak (Kembali ke Stok Warehouse)</option>
                            <option value="Kebesaran">Tukar: Ukuran Kebesaran</option>
                            <option value="Kekecilan">Tukar: Ukuran Kekecilan</option>
                            <option value="Cacat Produksi">Rusak: Cacat Produksi / Baju Rusak</option>
                        </select>
                    </div>
                </div>
            </div>
        `;

        container.insertAdjacentHTML('beforeend', rowHtml);
    }

    function removeItemRow(id) {
        const row = document.getElementById(`item-row-${id}`);
        if (row) row.remove();
    }

    function cancelProcess() {
        <?php if ($is_auto): ?>
            window.location.href = 'return.php';
        <?php else: ?>
            document.getElementById('view-return-process').style.display = 'none';
            document.getElementById('view-return-list').style.display = 'block';
        <?php endif; ?>
    }

    function filterTable() {
        const query = document.getElementById("searchTrx").value.toUpperCase();
        const rows = document.querySelectorAll("#tableTrx tbody tr");
        rows.forEach(row => {
            row.style.display = row.innerText.toUpperCase().includes(query) ? "" : "none";
        });
    }
</script>

</body>
</html>