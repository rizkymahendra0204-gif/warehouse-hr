<?php
require_once __DIR__ . '/includes/auth_check.php';
include 'controllers/proses_generate.php';


if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="<?= $_SESSION['lang'] ?? 'id'; ?>">
<head>
    <meta name="csrf-token" content="<?= wh_escape(wh_csrf_token()) ?>">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <link rel="icon" type="image/png" href="assets/img/favicon-icon.png">

    <title>WC | <?= $lang['gen_title'] ?? 'Generate Barcode' ?></title>
    <link href="assets/vendor-ui/bootstrap/bootstrap.min.css" rel="stylesheet">
    <link href="assets/vendor-ui/bootstrap-icons/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>

<div class="app-container">
    <?php include 'includes/sidebar.php'; ?>

    <div class="main-wrapper">
        <?php include 'includes/topbar.php'; ?>

        <main class="content-area p-4">
        
            <div class="d-flex justify-content-between align-items-center mb-4">
                    <div class="page-title">
                        <h4 class="fw-bold m-0"><?= $lang['gen_title'] ?? 'Generate Barcode' ?></h4>
                        <p class="text-secondary m-0 mt-1" style="font-size: 14px;"><?= $lang['gen_subtitle'] ?? 'Pembuatan Barcode untuk penamaan item' ?></p>
                    </div>
                </div>

            <!-- AREA ALERT -->
            <div class="floating-alert-container">
                <?php if (!empty($info_msg)): ?>
                    <div class="alert alert-info alert-dismissible fade show" role="alert">
                        <i class="bi bi-info-circle-fill me-2"></i> <?php echo $info_msg; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

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
            </div>

            <!-- FORM UNIFIED SETUP BARCODE -->
            <form action="generate_barcode" method="POST" class="d-print-none" id="formGenerateBarcode">
<?= wh_csrf_field() ?>
                <div class="row g-4">
                    <!-- CARD 1: PARAMETER SKU -->
                    <div class="col-md-6 mb-3">
                        <div class="card shadow-sm border-0 p-4 h-100" style="border-radius: 12px;">
                            <h5 class="fw-bold mb-4" style="color: #1e293b;"><i class="bi bi-sliders me-2 text-primary"></i><?= $lang['gen_sect_prefix'] ?? '1. Barcode Prefiks' ?></h5>

                            <div class="mb-3">
                                <label class="form-label fw-semibold text-secondary"><?= $lang['gen_lbl_gender'] ?? 'Gender' ?></label>
                                <select class="form-select" name="gender" id="selectGender" required>
                                    <option value=""><?= $lang['gen_plc_gender'] ?? '-- Pilih Gender --' ?></option>
                                    <option value="Pria" <?= $gender === 'Pria' ? 'selected' : '' ?>>Pria (1)</option>
                                    <option value="Wanita" <?= $gender === 'Wanita' ? 'selected' : '' ?>>Wanita (2)</option>
                                </select>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label fw-semibold text-secondary"><?= $lang['gen_lbl_tipe'] ?? 'Tipe Pakaian' ?></label>
                                <select class="form-select" name="tipe" id="selectTipe" required>
                                    <option value=""><?= $lang['gen_plc_tipe'] ?? '-- Pilih Tipe --' ?></option>
                                    <option value="Baju" <?= $tipe === 'Baju' ? 'selected' : '' ?>>Baju (01)</option>
                                    <option value="Celana" <?= $tipe === 'Celana' ? 'selected' : '' ?>>Celana (02)</option>
                                </select>
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-semibold text-secondary"><?= $lang['gen_lbl_ukuran'] ?? 'Ukuran' ?></label>
                                <select class="form-select" id="select_ukuran" name="ukuran" required>
                                    <option value=""><?= $lang['gen_plc_ukuran'] ?? '-- Pilih Ukuran --' ?></option>
                                    <?php 
                                        $optsAlfabet    = ['01' => 'S', '02' => 'M', '03' => 'L', '04' => 'XL'];
                                        $optsCelanaPria = ['28' => '28', '30' => '30', '32' => '32', '34' => '34', '36' => '36'];

                                        $isPria   = isset($gender) && ($gender === '1' || strtolower($gender) === 'pria' || strtolower($gender) === 'male');
                                        $isCelana = isset($tipe) && ($tipe === '02' || strtolower($tipe) === 'celana');

                                        if ($isPria && $isCelana) {
                                            $opts = $optsCelanaPria;
                                        } elseif (isset($tipe) && $tipe !== '') {
                                            $opts = $optsAlfabet;
                                        } else {
                                            $opts = array_merge($optsAlfabet, $optsCelanaPria);
                                        }

                                        $sizeMap = ['S' => '01', 'M' => '02', 'L' => '03', 'XL' => '04'];
                                        $currentUkuran = $sizeMap[$ukuran ?? ''] ?? ($ukuran ?? '');

                                        foreach($opts as $val => $label): 
                                    ?>
                                        <option value="<?= $val ?>" <?= ($currentUkuran === (string)$val) ? 'selected' : '' ?>><?= $label ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <button type="submit" name="action" value="check_last" class="btn btn-proses-custom w-100 fw-bold py-2 shadow-sm" style="border-radius: 8px;">
                                <i class="bi bi-search me-2"></i><?= $lang['btn_cek_running'] ?? 'Cek Running Terakhir' ?>
                            </button>
                        </div>
                    </div>

                    <!-- CARD 2: RUNNING NUMBER RANGE -->
                    <div class="col-md-6 mb-3">
                        <div class="card shadow-sm border-0 p-4 h-100" style="border-radius: 12px;">
                            <h5 class="fw-bold mb-4" style="color: #1e293b;"><i class="bi bi-hash me-2 text-primary"></i><?= $lang['gen_sect_range'] ?? '2. Range Running Number' ?></h5>

                            <div class="mb-4">
                                <label class="form-label fw-semibold text-secondary"><?= $lang['gen_lbl_range'] ?? 'Range Running Number (4 Digit)' ?></label>
                                <div class="row g-2">
                                    <div class="col-6">
                                        <div class="input-group">
                                            <span class="input-group-text bg-light" style="font-size: 12px;"><?= $lang['gen_lbl_mulai'] ?? 'Mulai' ?></span>
                                            <input type="text" class="form-control bg-light fw-bold text-primary" value="<?= sprintf("%04d", $range_awal) ?>" disabled>
                                            <input type="hidden" name="range_awal" value="<?= $range_awal ?>">
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div class="input-group">
                                            <span class="input-group-text bg-light" style="font-size: 12px;"><?= $lang['gen_lbl_sampai'] ?? 'Sampai' ?></span>
                                            <input type="number" class="form-control fw-bold" name="range_akhir" min="<?= $range_awal ?>" max="9999" value="<?= max($range_akhir, $range_awal) ?>" required>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="mt-auto">
                                <button type="submit" name="action" value="generate_range" class="btn btn-proses-custom text-white w-100 fw-bold py-2 shadow-sm" style="border-radius: 8px;">
                                    <i class="bi bi-eye-fill me-2"></i><?= $lang['btn_preview_label'] ?? 'Preview Label Barcode' ?>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </form>

            <!-- PREVIEW & TOMBOL PRINT & EXPORT -->
            <div class="row mt-2">
                <div class="col-12">
                    <div class="card shadow-sm border-0 p-4" style="border-radius: 12px;">
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <h5 class="fw-bold m-0" style="color: #1e293b;"><i class="bi bi-layout-three-columns me-2 text-success"></i><?= $lang['gen_sect_cetak'] ?? 'Lembar Cetak' ?></h5>
                            <?php if (!empty($generated_barcodes)): ?>
                                <div>
                                    <!-- Tombol 1: Cetak -->
                                    <button type="button" id="btnPrintBarcode"onclick="window.print();" class="btn btn-cetak-custom fw-bold px-3 d-print-none btn-print-trigger me-2">
                                        <i class="bi bi-printer-fill me-1"></i> <?= $lang['btn_cetak_stiker'] ?? 'Cetak ke Kertas Stiker' ?>
                                    </button>

                                    <!-- Tombol 2: Export to Excel -->
                                    <a href="controllers/export_excel?type=barcode&gender=<?= urlencode($gender) ?>&tipe=<?= urlencode($tipe) ?>&ukuran=<?= urlencode($ukuran) ?>&range_awal=<?= $range_awal ?>&range_akhir=<?= $range_akhir ?>" 
                                        id="btnExportExcel" class="btn btn-cetak-excel fw-bold px-3 d-print-none me-2">
                                        <i class="bi bi-file-earmark-excel-fill me-1"></i> <?= $lang['btn_export_excel'] ?? 'Export ke Excel' ?>
                                    </a>

                                    <!-- Tombol 3: Simpan ke Stok -->
                                    <button type="button" 
                                            id="btnSimpanStokBatch" 
                                            class="btn btn-simpan-custom fw-bold d-print-none" 
                                            disabled>
                                        <i class="bi bi-download me-1"></i> Save Batch Inventory
                                    </button>
                                </div>
                            <?php endif; ?>
                        </div>
                        
                        <div id="print-area-wrapper">
                            <?php if (!empty($generated_barcodes)): ?>
                                <div class="barcode-grid">
                                    <?php foreach ($generated_barcodes as $code): ?>
                                        <div class="barcode-print-card">
                                            <div style="font-size: 9px; font-weight: bold; color: #64748b; margin-bottom: 2px;">
                                                <?php echo htmlspecialchars($tipe); ?>&nbsp;<?php echo htmlspecialchars($gender); ?>&nbsp;<?php echo htmlspecialchars($ukuran); ?>
                                            </div>
                                            <svg class="barcode-element" data-value="<?php echo htmlspecialchars($code); ?>"></svg>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php else: ?>
                                <div class="text-center py-5 border rounded bg-light text-secondary">
                                    <i class="bi bi-printer display-6 d-block mb-2"></i>
                                    <?= $lang['gen_info_cetak'] ?? 'Pilih kombinasi SKU di atas untuk melihat preview stiker barcode.' ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>

<!-- Scripts -->
<script src="assets/vendor-ui/jquery/jquery-3.6.0.min.js"></script>
<script src="assets/vendor-ui/jsbarcode/JsBarcode.all.min.js"></script>
<script src="assets/vendor-ui/bootstrap/bootstrap.bundle.min.js"></script>
<script src="assets/js/scripts.js"></script>

<!-- SCRIPT AKSI RESET OTOMATIS TIPE DAN UKURAN SAAT GENDER DIUBAH -->
<script>
$(document).ready(function() {
    $('#selectGender').on('change', function() {
        // Reset elemen dropdown Tipe Pakaian & Ukuran ke Opsi Pertama (Default)
        $('#selectTipe').val('');
        $('#select_ukuran').val('');
    });
});
</script>

</body>
</html>