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
    
    <!-- CSS Utama -->
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
            
            <div class="page-title">Transaksi
                <p class="text-secondary m-0 mt-1" style="font-size: 14px;">Manajemen untuk pengelolaan item keluar</p>
            </div>
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
                                        
                                        <!-- Atasan -->
                                        <?php if ($qty_top > 0): ?>
                                        <tr style="border-bottom: 1px solid #f1f5f9;">
                                            <td class="fw-bold py-3 ps-0 text-secondary" width="15%">Item Atasan</td>
                                            <td class="py-3 text-dark">: Baju <?php echo $gender_txt; ?> (Size <?php echo htmlspecialchars($size_top); ?>) - <?php echo $qty_top; ?> Pcs</td>
                                        </tr>
                                        <?php endif; ?>

                                        <!-- Bawahan -->
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
                            <!-- Item Card awal ter-generate otomatis via assets/js/scripts.js -->
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
                        <button type="submit" id="btnSimpanTransaksi" class="btn fw-bold text-white px-5" style="background-color: #556ee6; border-radius: 6px;">Proses Transaksi</button>
                    </div>
                </form>
                
            </div>
        </main>
        
    </div>
</div>

<!-- Scripts -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<!-- BRIDGE DATA PHP KE JAVASCRIPT -->
<script>
    window.transactionData = {
        isAuto: <?php echo json_encode($is_auto ?? false); ?>,
        idRequest: <?php echo json_encode($auto_id_request ?? ''); ?>,
        qtyTop: <?php echo json_encode((int)($qty_top ?? 0)); ?>,
        qtyBottoms: <?php echo json_encode((int)($qty_bottoms ?? 0)); ?>,
        totalQty: <?php echo json_encode((int)($total_qty ?? 0)); ?>,
        sizeTop: <?php echo json_encode($size_top ?? ''); ?>,
        sizeBottoms: <?php echo json_encode($size_bottoms ?? ''); ?>,
        gender: <?php echo json_encode($gender ?? ''); ?>
    };
</script>

<script src="assets/js/scripts.js"></script>

</body>
</html>