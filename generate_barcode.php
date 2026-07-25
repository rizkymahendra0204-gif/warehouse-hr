<?php
include 'controllers/proses_generate.php';
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cetak Label SKU - HR Warehouse</title>
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
            <div>
                <h4 class="page-title mb-1">Generate Barcode</h4>
                <p class="text-muted small mb-8">Pembuatan Barcode untuk penamaan item</p>
            </div>

            <?php if (!empty($success_msg)): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="bi bi-printer-fill me-2"></i> <?php echo $success_msg; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>
            
            <?php if (!empty($error_msg)): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="bi bi-exclamation-triangle-fill me-2"></i> <?php echo $error_msg; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <div class="row g-4">
                <!-- FORM SETUP PARAMETER BATCH -->
                <div class="col-12 mb-4 d-print-none">
                    <div class="card shadow-sm border-0 p-4" style="border-radius: 12px;">
                        <h5 class="fw-bold mb-4" style="color: #1e293b;"><i class="bi bi-sliders me-2 text-primary"></i>Barcode Prefik</h5>
                        
                        <form action="generate_barcode.php" method="POST">
                            <input type="hidden" name="action" value="generate_range">
                            
                            <div class="mb-3">
                                <label class="form-label fw-semibold text-secondary">1. Tipe Pakaian</label>
                                <select class="form-select" name="tipe" required>
                                    <option value="">-- Pilih Tipe --</option>
                                    <option value="Baju">Baju (01)</option>
                                    <option value="Celana">Celana (02)</option>
                                </select>
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-semibold text-secondary">2. Gender</label>
                                <select class="form-select" name="gender" required>
                                    <option value="">-- Pilih Gender --</option>
                                    <option value="Pria">Pria (1)</option>
                                    <option value="Wanita">Wanita (2)</option>
                                </select>
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-semibold text-secondary">3. Ukuran</label>
                                <select class="form-select" name="ukuran" required>
                                    <option value="">-- Pilih Ukuran --</option>
                                    <option value="S">S</option>
                                    <option value="M">M</option>
                                    <option value="L">L</option>
                                    <option value="XL">XL</option>
                                    <option value="28">28</option>
                                    <option value="30">30</option>
                                    <option value="32">32</option>
                                    <option value="36">36</option>
                                    <option value="38">38</option>
                                </select>
                            </div>

                            <div class="mb-4">
                                <label class="form-label fw-semibold text-secondary">4. Range Running Number (4 Digit)</label>
                                <div class="row g-2">
                                    <div class="col-6">
                                        <div class="input-group">
                                            <span class="input-group-text bg-light" style="font-size: 12px;">Mulai</span>
                                            <input type="number" class="form-control" name="range_awal" min="1" max="9999" value="1" required>
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div class="input-group">
                                            <span class="input-group-text bg-light" style="font-size: 12px;">Sampai</span>
                                            <input type="number" class="form-control" name="range_akhir" min="1" max="9999" value="10" required>
                                        </div>
                                    </div>
                                </div>
                                <small class="text-muted d-block mt-1">Sistem otomatis mengisi format padding angka 0 di depan.</small>
                            </div>

                            <button type="submit" class="btn text-white w-100 fw-bold py-2 shadow-sm" style="background-color: #556ee6; border-radius: 8px;">
                                <i class="bi bi-eye-fill me-2"></i>Preview Label Barcode
                            </button>
                        </form>
                    </div>
                </div>

                <!-- PREVIEW & TOMBOL PRINT -->
                <div class="col-12">
                    <div class="card shadow-sm border-0 p-4" style="border-radius: 12px;">
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <h5 class="fw-bold m-0" style="color: #1e293b;"><i class="bi bi-layout-three-columns me-2 text-success"></i>Lembar Cetak</h5>
                            <?php if (!empty($generated_barcodes)): ?>
                                <button type="button" onclick="window.print();" class="btn btn-success btn-sm fw-bold px-3 btn-print-trigger">
                                    <i class="bi bi-printer-fill me-1"></i> Cetak ke Kertas Stiker
                                </button>
                                <button type="button" onclick="exportExcel();" class="btn btn-success btn-sm fw-bold px-3 btn-print-trigger">
                                        <i class="bi bi-file-earmark-excel-fill me-1"></i> Export ke Excel
                                </button>
                            <?php endif; ?>
                        </div>
                        
                        <div id="print-area-wrapper">
                            <?php if (!empty($generated_barcodes)): ?>
                                <div class="barcode-grid">
                                    <?php foreach ($generated_barcodes as $code): ?>
                                    <div class="barcode-print-card">
                                        <div style="font-size: 9px; font-weight: bold; color: #64748b; margin-bottom: 2px;">
                                            <?php echo $tipe; ?>&nbsp;<?php echo $gender; ?>&nbsp;<?php echo $ukuran; ?>
                                        </div>
                                        <svg class="barcode-element" data-value="<?php echo $code; ?>"></svg>
                                    </div>
                                <?php endforeach; ?>
                                </div>
                            <?php else: ?>
                                <div class="text-center py-5 border rounded bg-light text-secondary">
                                    <i class="bi bi-printer display-6 d-block mb-2"></i>
                                    Masukkan kombinasi SKU di atas untuk melihat preview stiker.
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/jsbarcode@3.11.5/dist/JsBarcode.all.min.js"></script>
<script src="https://cdn.sheetjs.com/xlsx-0.20.1/package/dist/xlsx.full.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/xlsx-js-style@1.2.0/dist/xlsx.bundle.js"></script>

<script src="assets/js/scripts.js"></script>

</body>
</html>