<?php
// 1. Panggil koneksi database di baris pertama
include 'include/db.php';

$conn = new mysqli($host, $user, $pass, $db);

// Catch Parameter URL (jika dipanggil via GET)
$auto_id_transaksi = isset($_GET['req']) ? $_GET['req'] : '';
$auto_sales_id     = isset($_GET['sales']) ? $_GET['sales'] : '';
$auto_nama         = isset($_GET['nama']) ? $_GET['nama'] : '';
$auto_detail       = isset($_GET['detail']) ? $_GET['detail'] : '';

$is_auto       = !empty($auto_id_transaksi); 
$readonly_attr = $is_auto ? 'readonly' : '';
$bg_class      = $is_auto ? 'bg-light' : '';
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
    
    <!-- Memanggil file CSS Anda -->
    <link rel="stylesheet" href="assets/css/style.css">
    
    <style>
        .badge-trx { background-color: #f1dd66; color: #333; font-weight: 700; font-size: 0.85rem; padding: 6px 12px; border-radius: 6px; }
        .btn-action-process { background-color: #b91c1c; color: #fff; border: none; font-weight: 500; border-radius: 6px; font-size: 0.9rem; }
        .btn-action-process:hover { background-color: #991b1b; color: #fff; }
        .scan-input-main:focus { border-color: #b91c1c; box-shadow: 0 0 0 0.25rem rgba(185, 28, 28, 0.25); }
    </style>
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

            <!-- ======================================================= -->
            <!-- VIEW 1: DAFTAR REQUEST PENDING RETURN                   -->
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
                                    <th style="width: 20%;">NO. TRANSAKSI</th>
                                    <th style="width: 45%;">DETAIL ITEM TRANSAKSI</th>
                                    <th style="width: 15%;">STATUS</th>
                                    <th style="width: 20%; text-align: center;">AKSI</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                // Query Multi-JOIN untuk mengambil data asli dari Database
                                $sql = "SELECT 
                                            t.transaction_id,
                                            t.request_id,
                                            t.id_sales,
                                            t.tgl_transaksi,
                                            t.barcode,
                                            rf.perusahaan,
                                            rf.nama_sa,
                                            rf.brand,
                                            mi.tipe,
                                            mi.gender,
                                            mi.size,
                                            mi.status_transaksi,
                                            mi.status_barang
                                        FROM transaksi t
                                        INNER JOIN request_form rf ON t.request_id = rf.request_id
                                        INNER JOIN master_item mi ON t.barcode = mi.barcode
                                        ORDER BY t.transaction_id DESC";

                                $query = mysqli_query($conn, $sql);

                                if ($query && mysqli_num_rows($query) > 0) {
                                    while ($row = mysqli_fetch_assoc($query)) {
                                        // Format No. Transaksi
                                        $no_trx = "TRX-" . str_pad($row['transaction_id'], 6, '0', STR_PAD_LEFT);
                                        
                                        // Detail item transaksi dari barcode
                                        $detail_item = "Request Seragam " . $row['brand'] . " (" . $row['tipe'] . " " . $row['gender'] . " - Size " . $row['size'] . ")";
                                        
                                        // Tgl Transaksi
                                        $tgl_trx = !empty($row['tgl_transaksi']) ? date('d M Y', strtotime($row['tgl_transaksi'])) : '-';
                                ?>
                                        <tr class="border-bottom">
                                            <!-- No. Transaksi dari transaction_id -->
                                            <td><span class="badge-trx">#<?php echo $no_trx; ?></span></td>
                                            
                                            <!-- Detail Item Transaksi -->
                                            <td>
                                                <div class="fw-bold"><?php echo htmlspecialchars($row['perusahaan']); ?></div>
                                                <div class="text-muted small">
                                                    <i class="bi bi-box-seam me-1"></i> <?php echo htmlspecialchars($detail_item); ?> &nbsp;|&nbsp; 
                                                    <i class="bi bi-calendar3 me-1"></i> <?php echo $tgl_trx; ?>
                                                </div>
                                            </td>
                                            
                                            <!-- Status dari status_transaksi & status_barang master_item -->
                                            <td>
                                                <span class="badge bg-primary bg-opacity-10 text-primary px-3 py-2 rounded-pill">
                                                    <?php echo !empty($row['status_barang']) ? htmlspecialchars($row['status_barang']) : htmlspecialchars($row['status_transaksi']); ?>
                                                </span>
                                            </td>
                                            
                                            <!-- Tombol Akses Form Return -->
                                            <td class="text-center">
                                                <button class="btn btn-action-process px-3 py-2" 
                                                        onclick="openProcessPage(
                                                            '<?php echo $no_trx; ?>', 
                                                            '<?php echo htmlspecialchars($row['id_sales']); ?>', 
                                                            '<?php echo addslashes($row['nama_sa']); ?>', 
                                                            '<?php echo addslashes($detail_item); ?>'
                                                        )">
                                                    Proses Return <i class="bi bi-arrow-right-short ms-1"></i>
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
                    <div class="page-title fs-4 fw-bold">Return</div>
                </div>

                <!-- Alert Container Flash Message -->
                <div id="alertContainer"></div>

                <form action="proses_return.php" method="POST" id="formReturn">
                    
                    <!-- SECTION 1: Informasi Tiket Return -->
                    <div class="bg-white border rounded-3 p-4 mb-4 shadow-sm">
                        <h6 class="fw-bold mb-4" style="color: #4b5563;">
                            <i class="bi bi-arrow-counterclockwise me-2"></i>Informasi Tiket Return
                        </h6>
                        <div class="row g-4">
                            <div class="col-md-6 col-lg-3">
                                <label class="form-label fw-semibold text-secondary" style="font-size: 13px;">ID Transaksi</label>
                                <input type="text" class="form-control <?php echo $bg_class; ?>" id="input_no_return" name="no_return" value="<?php echo $auto_id_transaksi; ?>" placeholder="Contoh: TRX-000001" <?php echo $readonly_attr; ?>>
                            </div>
                            <div class="col-md-6 col-lg-3">
                                <label class="form-label fw-semibold text-secondary" style="font-size: 13px;">ID Sales</label>
                                <input type="text" class="form-control <?php echo $bg_class; ?>" id="input_id_sales" name="id_request_awal" value="<?php echo $auto_sales_id; ?>" placeholder="Masukkan ID Sales" <?php echo $readonly_attr; ?>>
                            </div>
                            <div class="col-md-6 col-lg-3">
                                <label class="form-label fw-semibold text-secondary" style="font-size: 13px;">Nama SA</label>
                                <input type="text" class="form-control <?php echo $bg_class; ?>" id="input_nama" name="nama" value="<?php echo $auto_nama; ?>" placeholder="Nama SA" <?php echo $readonly_attr; ?>>
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
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="2" class="text-center text-muted py-3" style="font-size: 13px; font-style: italic;">
                                                <i class="bi bi-info-circle me-1"></i> Rincian item request akan muncul secara otomatis.
                                            </td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- SECTION 3: Pemindaian Item Return Dinamis & Auto-Scan -->
                    <div class="bg-white border rounded-3 p-4 mb-4 shadow-sm">
                        <h6 class="fw-bold mb-3" style="color: #b91c1c;">
                            <i class="bi bi-upc-scan me-2"></i>Pemindaian Item Return
                        </h6>

                        <!-- Fast Auto-Scan Input Barcode Scanner -->
                        <div class="bg-light p-3 rounded-3 mb-4 border">
                            <label class="form-label small fw-bold text-danger mb-1">
                                <i class="bi bi-lightning-charge-fill me-1"></i> Mode Cepat Auto-Scan:
                            </label>
                            <div class="input-group">
                                <span class="input-group-text bg-white text-danger"><i class="bi bi-upc-scan"></i></span>
                                <input type="text" id="mainBarcodeInput" class="form-control scan-input-main" placeholder="Scan barcode 9 digit di sini (Auto-Add)..." autocomplete="off" autofocus>
                            </div>
                        </div>

                        <!-- CONTAINER ITEM DINAMIS -->
                        <div id="dynamic-item-container">
                            <!-- Item row akan dirender dinamis lewat JS -->
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

<!-- Scripts dari Bootstrap, jQuery, dan JS Custom -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="assets/js/scripts.js"></script>

<!-- ENGINE JAVASCRIPT AUTO-SCAN & DOM MANIPULATION -->
<script>
    // Mapping barcode schema 9 digit
    const GENDER_MAP = { '1': 'Pria', '2': 'Wanita' };
    const TYPE_MAP   = { '01': 'Baju', '02': 'Celana' };
    const SIZE_MAP   = { '01': 'S', '02': 'M', '03': 'L', '04': 'XL' };

    let itemCount = 0;
    let scannedBarcodes = new Set();

    $(document).ready(function() {
        addItemRow();
    });

    // --- SWITCH VIEW (LIST ke PROCESS) ---
    function openProcessPage(trxId, salesId, saName, detailInfo) {
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

        // Reset Scan Container
        itemCount = 0;
        scannedBarcodes.clear();
        document.getElementById('dynamic-item-container').innerHTML = '';
        document.getElementById('alertContainer').innerHTML = '';

        addItemRow();
        
        setTimeout(() => {
            document.getElementById('mainBarcodeInput').focus();
        }, 100);
    }

    function cancelProcess() {
        <?php if ($is_auto): ?>
            window.location.href = 'return.php';
        <?php else: ?>
            document.getElementById('view-return-process').style.display = 'none';
            document.getElementById('view-return-list').style.display = 'block';
        <?php endif; ?>
    }

    // --- PARSER BARCODE 9 DIGIT ---
    function parseBarcode(rawCode) {
        const clean = rawCode.replace(/\*/g, '').trim();
        if (clean.length !== 9 || isNaN(clean)) return null;

        const gender = GENDER_MAP[clean.substring(0, 1)] || 'Unknown';
        const type   = TYPE_MAP[clean.substring(1, 3)]   || 'Item';
        const size   = SIZE_MAP[clean.substring(3, 5)]   || 'All Size';
        const num    = clean.substring(5, 9);

        return {
            raw: clean,
            text: `(${type} ${gender} - Ukuran ${size} - #${num})`
        };
    }

    // --- MAIN AUTO SCAN EVENT ---
    document.getElementById('mainBarcodeInput').addEventListener('keypress', function (e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            const val = this.value.trim();
            if (!val) return;

            processBarcode(val);
            this.value = '';
            this.focus();
        }
    });

    function processBarcode(barcodeVal) {
        const parsed = parseBarcode(barcodeVal);

        if (!parsed) {
            showAlert('Format Barcode tidak valid! Harus berisi 9 digit angka.', 'danger');
            return;
        }

        if (scannedBarcodes.has(parsed.raw)) {
            showAlert(`Barcode <strong>${parsed.raw}</strong> sudah ada dalam daftar return!`, 'warning');
            return;
        }

        const emptyInput = Array.from(document.querySelectorAll('.barcode-row-input')).find(input => !input.value.trim());

        if (emptyInput) {
            const rowId = emptyInput.dataset.id;
            fillRowData(rowId, parsed);
        } else {
            addItemRow(parsed);
        }

        scannedBarcodes.add(parsed.raw);
        showAlert(`Berhasil memindai <strong>${parsed.raw} ${parsed.text}</strong>`, 'success');
    }

    // --- TAMBAH BARIS ITEM DINAMIS ---
    function addItemRow(prefilledData = null) {
        itemCount++;
        const container = document.getElementById('dynamic-item-container');
        const id = itemCount;

        const rowHtml = `
            <div class="item-row bg-white border rounded-3 p-3 mb-3" id="item-row-${id}" style="box-shadow: 0 2px 4px rgba(0,0,0,0.02);">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <span class="fw-bold item-number" style="color: #b91c1c; font-size: 14px;">
                        <i class="bi bi-box-seam me-2"></i>Barang #${id}
                        <small id="item-desc-${id}" class="text-dark fw-normal ms-2">${prefilledData ? prefilledData.text : ''}</small>
                    </span>
                    <button type="button" class="btn btn-sm text-danger btn-remove-item fw-bold" 
                            style="background-color: #fee2e2; border-radius: 4px; padding: 2px 8px;" 
                            onclick="removeItemRow(${id})">
                        <i class="bi bi-trash3 me-1"></i>Hapus
                    </button>
                </div>
                
                <div class="row g-4">
                    <div class="col-md-6">
                        <label class="form-label fw-semibold text-secondary" style="font-size: 13px;">Barcode Fisik</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light text-danger"><i class="bi bi-upc-scan"></i></span>
                            <input type="text" 
                                   class="form-control barcode-row-input ${prefilledData ? 'bg-light' : ''}" 
                                   id="barcode-input-${id}"
                                   data-id="${id}"
                                   name="barcode_return[]" 
                                   value="${prefilledData ? prefilledData.raw : ''}"
                                   placeholder="Scan/Ketik Barcode"
                                   ${prefilledData ? 'readonly' : ''}
                                   onkeypress="handleRowKeyPress(event, ${id})">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold text-secondary" style="font-size: 13px;">Kondisi Barang</label>
                        <select class="form-select" id="kondisi-select-${id}" name="kondisi_return[]">
                            <option value="" disabled ${!prefilledData ? 'selected' : ''}>Pilih Kondisi...</option>
                            <option value="Layak" ${prefilledData ? 'selected' : ''}>Layak (Kembali ke Stok)</option>
                            <option value="Kebesaran">Tukar: Kebesaran</option>
                            <option value="Kekecilan">Tukar: Kekecilan</option>
                            <option value="Cacat Produksi">Rusak: Cacat Produksi</option>
                        </select>
                    </div>
                </div>
            </div>
        `;

        container.insertAdjacentHTML('beforeend', rowHtml);

        if (prefilledData) {
            scannedBarcodes.add(prefilledData.raw);
        }
    }

    function fillRowData(id, parsedData) {
        const input = document.getElementById(`barcode-input-${id}`);
        const desc  = document.getElementById(`item-desc-${id}`);
        const select= document.getElementById(`kondisi-select-${id}`);

        if (input) {
            input.value = parsedData.raw;
            input.readOnly = true;
            input.classList.add('bg-light');
        }
        if (desc) desc.innerText = parsedData.text;
        if (select) select.value = 'Layak';
    }

    function handleRowKeyPress(e, id) {
        if (e.key === 'Enter') {
            e.preventDefault();
            const input = document.getElementById(`barcode-input-${id}`);
            const val = input.value.trim();
            if (val) processBarcode(val);
        }
    }

    function removeItemRow(id) {
        const input = document.getElementById(`barcode-input-${id}`);
        if (input && input.value) {
            scannedBarcodes.delete(input.value.trim());
        }
        const row = document.getElementById(`item-row-${id}`);
        if (row) row.remove();
    }

    function showAlert(message, type) {
        document.getElementById('alertContainer').innerHTML = `
            <div class="alert alert-${type} alert-dismissible fade show py-2 px-3 small mb-3" role="alert">
                ${message}
                <button type="button" class="btn-close py-2" data-bs-dismiss="alert"></button>
            </div>
        `;
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