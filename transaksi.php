<?php
include 'controllers/query_transaksi.php';
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Transaksi - HR Warehouse</title>
    
    <!-- Bootstrap & Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    
    <!-- Memanggil file CSS Anda -->
    <link rel="stylesheet" href="assets/css/style.css">

    <style>
        .scan-input-main:focus {
            border-color: #556ee6;
            box-shadow: 0 0 0 0.25rem rgba(85, 110, 230, 0.25);
        }
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
            
            <div class="page-title fw-bold fs-4 mb-3">Transaksi</div>
            
            <!-- Alert Container Flash Message -->
            <div id="alertContainer"></div>

            <div class="container-fluid px-0">
                
                <form action="controllers/proses_transaksi.php" method="POST" id="formTransaksi">
                    
                    <!-- SECTION 1: Informasi Tiket -->
                    <div class="bg-white border rounded-3 p-4 mb-4 shadow-sm">
                        <h6 class="fw-bold mb-4" style="color: #4b5563;"><i class="bi bi-ticket-detailed me-2"></i>Informasi Tiket</h6>
                        <div class="row g-4">
                            <div class="col-md-3">
                                <label class="form-label fw-semibold text-secondary" style="font-size: 13px;">ID Request</label>
                                <input type="text" class="form-control <?php echo $bg_class; ?>" id="id_request" name="id_request" value="<?php echo htmlspecialchars($auto_id_request); ?>" placeholder="Contoh: FR-110726" <?php echo $readonly_attr; ?>>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-semibold text-secondary" style="font-size: 13px;">Brand</label>
                                <input type="text" class="form-control <?php echo $bg_class; ?>" name="brand" value="<?php echo htmlspecialchars($brand); ?>" placeholder="Brand" <?php echo $readonly_attr; ?>>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-semibold text-secondary" style="font-size: 13px;">Nama SA</label>
                                <input type="text" class="form-control <?php echo $bg_class; ?>" name="nama_sa" value="<?php echo htmlspecialchars($nama_sa); ?>" placeholder="Nama SA" <?php echo $readonly_attr; ?>>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-semibold text-secondary" style="font-size: 13px;">ID Sales</label>
                                <input type="text" class="form-control" name="id_sales" placeholder="Masukkan ID Sales" required>
                            </div>
                        </div>
                    </div>

                    <!-- SECTION 2: Detail Pesanan -->
                    <div class="bg-white border rounded-3 p-4 mb-4 shadow-sm">
                        <h6 class="fw-bold mb-4" style="color: #4b5563;"><i class="bi bi-cart-check me-2"></i>Detail Pesanan</h6>

                        <!-- Area Rincian Item Bergaris -->
                        <div class="border-top pt-2">
                            <table class="table table-borderless m-0" style="font-size: 14px;">
                                <tbody id="rincian-item-list">
                                    <?php if ($is_auto && $total_qty > 0): ?>
                                        
                                        <!-- Cek & Tampilkan Atasan jika ada -->
                                        <?php if ($qty_top > 0): ?>
                                        <tr style="border-bottom: 1px solid #f1f5f9;">
                                            <td class="fw-bold py-3 ps-0 text-secondary" width="15%">Item Atasan</td>
                                            <td class="py-3 text-dark">: Baju <?php echo $gender_txt; ?> (Size <?php echo htmlspecialchars($size_top); ?>) - <?php echo $qty_top; ?> Pcs</td>
                                        </tr>
                                        <?php endif; ?>

                                        <!-- Cek & Tampilkan Bawahan jika ada -->
                                        <?php if ($qty_bottoms > 0): ?>
                                        <tr style="border-bottom: 1px solid #f1f5f9;">
                                            <td class="fw-bold py-3 ps-0 text-secondary" width="15%">Item Bawahan</td>
                                            <td class="py-3 text-dark">: Celana <?php echo $gender_txt; ?> (Size <?php echo htmlspecialchars($size_bottoms); ?>) - <?php echo $qty_bottoms; ?> Pcs</td>
                                        </tr>
                                        <?php endif; ?>

                                        <!-- Total Jumlah -->
                                        <tr>
                                            <td class="fw-bold py-3 ps-0 text-secondary">Total Jumlah</td>
                                            <td class="py-3 text-dark fw-bold">: <?php echo $total_qty; ?> Pcs</td>
                                        </tr>
                                        
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="2" class="text-center text-muted py-4" style="font-size: 13px; font-style: italic;">
                                                <i class="bi bi-info-circle me-1"></i> Rincian item request akan muncul secara otomatis di sini.
                                            </td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- SECTION 3: Pemindaian Item dengan Mode Auto-Scan -->
                    <div class="bg-white border rounded-3 p-4 mb-4 shadow-sm">
                        <h6 class="fw-bold mb-3" style="color: #4b5563;"><i class="bi bi-upc-scan me-2"></i>Pemindaian Item</h6>

                        <!-- Fast Auto-Scan Box -->
                        <div class="bg-light p-3 rounded-3 mb-4 border">
                            <label class="form-label small fw-bold mb-1" style="color: #556ee6;">
                                <i class="bi bi-lightning-charge-fill me-1"></i> Mode Cepat Auto-Scan (Auto-Add):
                            </label>
                            <div class="input-group">
                                <span class="input-group-text bg-white" style="color: #556ee6;"><i class="bi bi-upc-scan"></i></span>
                                <input type="text" id="mainBarcodeInput" class="form-control scan-input-main" placeholder="Scan barcode 9 digit di sini..." autocomplete="off" autofocus>
                            </div>
                        </div>

                        <!-- CONTAINER ROW ITEM DINAMIS -->
                        <div id="dynamic-item-container" class="row g-4 mb-3">
                            <!-- Item Card awal akan ter-generate otomatis via JS -->
                        </div>
                            
                        <div class="d-flex justify-content-between align-items-center border-top pt-3">
                            <button type="button" id="btn-tambah-item" class="btn btn-light border fw-bold text-secondary" style="border-radius: 6px; font-size: 14px;">
                                <i class="bi bi-plus-lg me-1"></i>Tambah Baris
                            </button>
                            <button type="button" id="btn-validate" class="btn text-white fw-bold px-4 py-2" style="background-color: #556ee6; border-radius: 6px; font-size: 14px;">
                                <i class="bi bi-check2-circle me-2"></i>Validate Items
                            </button>
                        </div>
                    </div>
                    
                    <!-- Tombol Aksi Bawah -->
                    <div class="d-flex justify-content-end gap-3 mt-4 mb-5">
                        <button type="reset" class="btn btn-light border fw-bold px-4 text-secondary" style="border-radius: 6px;" onclick="resetForm()">Batal</button>
                        <button type="submit" class="btn fw-bold text-white px-5" style="background-color: #556ee6; border-radius: 6px;">Simpan Transaksi</button>
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

<!-- ENGINE JAVASCRIPT AUTO-SCAN & ITEM VALIDATION -->
<script>
    // Schema Barcode 9 Digit
    const GENDER_MAP = { '1': 'Pria', '2': 'Wanita' };
    const TYPE_MAP   = { '01': 'Baju', '02': 'Celana' };
    const SIZE_MAP   = { '01': 'S', '02': 'M', '03': 'L', '04': 'XL' };

    let itemCount = 0;
    let scannedBarcodes = new Set();

    $(document).ready(function() {
        // Generasi 1 kartu kosong awal
        addItemCard();

        // Event handler klik tombol Tambah Baris
        $('#btn-tambah-item').on('click', function() {
            addItemCard();
        });

        // Event handler klik tombol Validate Items Manual
        $('#btn-validate').on('click', function() {
            validateAllItems();
        });
    });

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
            label: `${type.toUpperCase()} ${gender.toUpperCase()} (SIZE ${size}) - #${num}`
        };
    }

    // --- AUTO-SCAN LISTENER (MAIN INPUT) ---
    document.getElementById('mainBarcodeInput').addEventListener('keypress', function (e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            const val = this.value.trim();
            if (!val) return;

            processAutoScan(val);
            this.value = '';
            this.focus();
        }
    });

    function processAutoScan(barcodeVal) {
        const parsed = parseBarcode(barcodeVal);

        if (!parsed) {
            showAlert('Format Barcode tidak valid! Harus berisi 9 digit angka.', 'danger');
            return;
        }

        if (scannedBarcodes.has(parsed.raw)) {
            showAlert(`Barcode <strong>${parsed.raw}</strong> sudah masuk ke dalam daftar transaksi!`, 'warning');
            return;
        }

        // Cari kartu yang masih kosong
        const emptyInput = Array.from(document.querySelectorAll('.barcode-item-input')).find(input => !input.value.trim());

        if (emptyInput) {
            const cardId = emptyInput.dataset.id;
            fillCardData(cardId, parsed);
        } else {
            addItemCard(parsed);
        }

        scannedBarcodes.add(parsed.raw);
        showAlert(`Berhasil memindai <strong>${parsed.label}</strong>`, 'success');
    }

    // --- TAMBAH KARTU ITEM DINAMIS ---
    function addItemCard(prefilledData = null) {
        itemCount++;
        const id = itemCount;
        const container = document.getElementById('dynamic-item-container');

        const cardHtml = `
            <div class="col-md-4 item-row" id="item-card-${id}">
                <div class="bg-white border rounded-3 p-3 h-100" style="box-shadow: 0 1px 3px rgba(0,0,0,0.02);">
                    
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <span class="fw-bold item-number" style="color: #556ee6; font-size: 14px;">
                            <i class="bi bi-box-seam me-2"></i>Item #${id}
                        </span>
                        <button type="button" class="btn btn-sm text-danger btn-remove-item fw-bold" 
                                style="${id === 1 && !prefilledData ? 'display: none;' : ''} background-color: #fee2e2; border-radius: 4px; padding: 2px 8px;" 
                                onclick="removeItemCard(${id})">
                            <i class="bi bi-trash3 me-1"></i>Hapus
                        </button>
                    </div>
                    
                    <div class="mb-2">
                        <label class="form-label fw-bold text-secondary mb-2" style="font-size: 13px;">Barcode Item</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light text-secondary"><i class="bi bi-upc-scan"></i></span>
                            <input type="text" 
                                   class="form-control barcode-item-input ${prefilledData ? 'bg-light' : ''}" 
                                   id="barcode-input-${id}"
                                   data-id="${id}"
                                   name="barcode_item[]" 
                                   value="${prefilledData ? prefilledData.raw : ''}"
                                   placeholder="Scan Barcode"
                                   ${prefilledData ? 'readonly' : ''}
                                   onkeypress="handleCardKeyPress(event, ${id})">
                        </div>
                        
                        <div class="barcode-detail-text text-uppercase fw-bold text-primary mt-2 ps-2" id="detail-text-${id}" style="font-size: 12px; letter-spacing: 0.5px; min-height: 18px;">
                            ${prefilledData ? prefilledData.label : ''}
                        </div>
                        
                        <input type="hidden" name="detail_item[]" id="detail-hidden-${id}" class="barcode-detail-hidden" value="${prefilledData ? prefilledData.label : ''}">
                    </div>
                    
                </div>
            </div>
        `;

        container.insertAdjacentHTML('beforeend', cardHtml);

        if (prefilledData) {
            scannedBarcodes.add(prefilledData.raw);
        }
        updateRemoveButtons();
    }

    function fillCardData(id, parsedData) {
    const input = document.getElementById(`barcode-input-${id}`);
    const text  = document.getElementById(`detail-text-${id}`);
    const hidden= document.getElementById(`detail-hidden-${id}`);

    if (input) {
        input.value = parsedData.raw;
        input.readOnly = true;
        input.classList.add('bg-light');
    }
    if (text) text.innerText = parsedData.label;
    if (hidden) hidden.value = parsedData.label;

    // Refresh status tombol hapus setelah data terisi
    updateRemoveButtons();
}

    function removeItemCard(id) {
    const input = document.getElementById(`barcode-input-${id}`);
    const barcodeVal = input ? input.value.trim() : '';

    // Hapus dari memori duplikasi scan
    if (barcodeVal) {
        scannedBarcodes.delete(barcodeVal);
    }

    const cards = document.querySelectorAll('.item-row');

    // Jika tinggal 1 kartu dan dihapus, reset kartu tersebut jadi kosong (tidak di-remove DOM-nya)
    if (cards.length === 1) {
        if (input) {
            input.value = '';
            input.readOnly = false;
            input.classList.remove('bg-light');
        }
        const textElement = document.getElementById(`detail-text-${id}`);
        const hiddenElement = document.getElementById(`detail-hidden-${id}`);
        
        if (textElement) textElement.innerText = '';
        if (hiddenElement) hiddenElement.value = '';

        showAlert('Item #1 berhasil dikosongkan.', 'info');
    } else {
        // Jika kartu lebih dari 1, hapus kartu tersebut dari DOM
        const card = document.getElementById(`item-card-${id}`);
        if (card) card.remove();
        showAlert('Item berhasil dihapus.', 'info');
    }

    updateRemoveButtons();
}

    function updateRemoveButtons() {
    const cards = document.querySelectorAll('.item-row');
    
    cards.forEach((card) => {
        const btn = card.querySelector('.btn-remove-item');
        const input = card.querySelector('.barcode-item-input');
        const hasValue = input && input.value.trim() !== '';

        if (btn) {
            // Tombol HAPUS muncul jika:
            // 1. Kartu lebih dari 1, ATAU
            // 2. Kartu tersebut sudah terisi / di-scan (meskipun cuma 1 kartu)
            if (cards.length > 1 || hasValue) {
                btn.style.display = 'block';
            } else {
                btn.style.display = 'none';
            }
        }
    });
}

    function validateAllItems() {
        let validCount = 0;
        document.querySelectorAll('.barcode-item-input').forEach(input => {
            const val = input.value.trim();
            if (val) {
                const parsed = parseBarcode(val);
                if (parsed) {
                    const id = input.dataset.id;
                    fillCardData(id, parsed);
                    validCount++;
                }
            }
        });
        showAlert(`Validasi selesai! <strong>${validCount}</strong> item valid.`, 'info');
    }

    function showAlert(msg, type) {
        document.getElementById('alertContainer').innerHTML = `
            <div class="alert alert-${type} alert-dismissible fade show py-2 px-3 small mb-3" role="alert">
                ${msg}
                <button type="button" class="btn-close py-2" data-bs-dismiss="alert"></button>
            </div>
        `;
    }

    function resetForm() {
        scannedBarcodes.clear();
        document.getElementById('dynamic-item-container').innerHTML = '';
        itemCount = 0;
        addItemCard();
    }
</script>

</body>
</html>