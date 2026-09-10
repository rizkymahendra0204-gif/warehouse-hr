<?php
require_once __DIR__ . '/includes/auth_check.php';
include 'controllers/query_transaksi.php';
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta name="csrf-token" content="<?= wh_escape(wh_csrf_token()) ?>">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>WC | <?= $lang['trx_title'] ?? 'Transaksi' ?></title>

    <link rel="icon" type="image/png" href="assets/img/favicon-icon.png">
    
    <!-- Bootstrap & Icons -->
    <link href="assets/vendor-ui/bootstrap/bootstrap.min.css" rel="stylesheet">
    <link href="assets/vendor-ui/bootstrap-icons/bootstrap-icons.css" rel="stylesheet">
    
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
            
            <div class="d-flex justify-content-between align-items-center mb-4">
                    <div class="page-title">
                        <h4 class="fw-bold m-0"><?= $lang['trx_title'] ?? 'Transaksi' ?></h4>
                        <p class="text-secondary m-0 mt-1" style="font-size: 14px;"><?= $lang['trx_subtitle'] ?? 'Manajemen untuk pengelolaan item keluar' ?></p>
                    </div>
                </div>

            <div class="floating-alert-container" id="alertContainer">
                <?php if (isset($_SESSION['alert_message'])): ?>
                    <div class="alert alert-<?php echo $_SESSION['alert_type'] ?? 'info'; ?> alert-dismissible fade show shadow-sm mb-3" role="alert">
                        <i class="bi bi-info-circle-fill me-2"></i>
                        <?php 
                            echo $_SESSION['alert_message']; 
                            unset($_SESSION['alert_message']);
                            unset($_SESSION['alert_type']);
                        ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>
            </div>

            <div class="container-fluid px-0">
                
                <form action="controllers/proses_transaksi" method="POST" id="formTransaksi">
<?= wh_csrf_field() ?>

                    <div class="row g-4">
                        <div class="col-12">

                            <!-- SECTION 1: Detail Pesanan (STICKY & LEBAR SAMA DENGAN INFORMASI TIKET) -->
                            <div class="bg-white border rounded-3 p-4 mb-4 shadow-sm <!--sticky-detail-pesanan-->">
                                <h6 class="fw-bold mb-3" style="color: #4b5563;">
                                    <i class="bi bi-cart-check me-2"></i><?= $lang['trx_sect_pesanan'] ?? 'Detail Pesanan' ?>
                                </h6>

                                <div class="border-top pt-2">
                                    <table class="table table-borderless m-0" style="font-size: 14px;">
                                        <tbody id="rincian-item-list">
                                            <?php if ($is_auto && $total_qty > 0): ?>
                                                
                                                <?php if (!empty($brand)): ?>
                                                <tr style="border-bottom: 1px solid #f1f5f9;">
                                                    <td class="fw-bold py-2 ps-0 text-secondary" width="25%"><?= $lang['lbl_nama_brand'] ?? 'Nama Brand' ?></td>
                                                    <td class="py-2 text-dark">: <?php echo $brand; ?></td>
                                                </tr>
                                                <?php endif; ?>

                                                <tr>
                                                    <td class="fw-bold py-2 ps-0 text-secondary"><?= $lang['modal_total_jumlah'] ?? 'Total Jumlah' ?></td>
                                                    <td class="py-2 text-dark fw-bold">
                                                        : <?php echo $total_qty; ?> <?= $lang['unit_pcs'] ?? 'Pcs' ?>
                                                        
                                                        <?php 
                                                        // Penentuan label gender
                                                        $g_label = in_array(strtolower($gender ?? ''), ['female', 'wanita', '2']) ? 'Wanita' : 'Pria';
                                                        
                                                        $items_detail = [];
                                                        if (!empty($qty_top) && $qty_top > 0) {
                                                            $items_detail[] = "Atasan {$g_label} {$qty_top}";
                                                        }
                                                        
                                                        $bot_qty = $qty_bottoms ?? $qty_bottom ?? 0;
                                                        if (!empty($bot_qty) && $bot_qty > 0) {
                                                            $items_detail[] = "Bawahan {$g_label} {$bot_qty}";
                                                        }
                                                        
                                                        if (!empty($items_detail)) {
                                                            echo '<span class="fw-normal text-muted ms-1">( ' . implode(' & ', $items_detail) . ' )</span>';
                                                        }
                                                        ?>
                                                    </td>
                                                </tr>
                                                
                                            <?php else: ?>
                                                <tr>
                                                    <td colspan="2" class="text-center text-muted py-3" style="font-size: 13px; font-style: italic;">
                                                        <i class="bi bi-info-circle me-1"></i> <?= $lang['trx_info_pesanan'] ?? 'Rincian item request akan muncul secara otomatis di sini.' ?>
                                                    </td>
                                                </tr>
                                            <?php endif; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            <!-- SECTION 2: Informasi Tiket -->
                            <div class="bg-white border rounded-3 p-4 mb-4 shadow-sm">
                                <h6 class="fw-bold mb-4" style="color: #4b5563;">
                                    <i class="bi bi-ticket-detailed me-2"></i><?= $lang['trx_sect_tiket'] ?? 'Informasi Tiket' ?>
                                </h6>
                                <div class="row g-4 align-items-end">
                                    
                                    <!-- ID Request -->
                                    <div class="col-md-3">
                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                            <label class="form-label fw-semibold text-secondary m-0" style="font-size: 13px;"><?= $lang['trx_lbl_id_req'] ?? 'ID Request' ?></label>
                                        </div>
                                        <input type="text" class="form-control <?php echo $bg_class; ?>" id="id_request" name="id_request" value="<?php echo htmlspecialchars($auto_id_request); ?>" placeholder="<?= $lang['trx_plc_id_req'] ?? 'Contoh: FR-110726' ?>" <?php echo $readonly_attr; ?>>
                                    </div>

                                    <!-- Nama SA -->
                                    <div class="col-md-3">
                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                            <label class="form-label fw-semibold text-secondary m-0" style="font-size: 13px;"><?= $lang['trx_lbl_nama_sa'] ?? 'Nama SA' ?></label>
                                        </div>
                                        <input type="text" class="form-control <?php echo $bg_class; ?>" name="nama_sa" value="<?php echo htmlspecialchars($nama_sa); ?>" placeholder="<?= $lang['trx_lbl_nama_sa'] ?? 'Nama SA' ?>" <?php echo $readonly_attr; ?>>
                                    </div>

                                    <!-- Department (Dropdown Wajib Input) -->
                                    <div class="col-md-3">
                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                            <label class="form-label fw-bold text-dark m-0" style="font-size: 13px;"><?= $lang['trx_lbl_dept'] ?? 'Department' ?></label>
                                            <span class="badge bg-danger-subtle text-danger border border-danger-subtle" style="font-size: 10px;"><?= $lang['trx_badge_wajib'] ?? 'Wajib' ?></span>
                                        </div>
                                        <select class="form-select" id="department" name="department" required>
                                            <option value="" disabled selected hidden><?= $lang['trx_plc_dept'] ?? 'Pilih Department' ?></option>
                                            <?php 
                                            $list_dept = ['Cosmetic & Fragrance', 'Luxury', 'Ladies Shoes & Handbag', 'Ladies Apparel & Lingerie', 'Mens Formal', 'Mens Casual', 'Kids', 'Home', 'Toys'];
                                            foreach ($list_dept as $dept): 
                                            ?>
                                                <option value="<?php echo $dept; ?>"><?php echo $dept; ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>

                                    <!-- ID Sales -->
                                    <div class="col-md-3">
                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                            <label class="form-label fw-bold text-dark m-0" style="font-size: 13px;"><?= $lang['trx_lbl_id_sales'] ?? 'ID Sales' ?></label>
                                            <span class="badge bg-danger-subtle text-danger border border-danger-subtle" style="font-size: 10px;"><?= $lang['trx_badge_wajib'] ?? 'Wajib' ?></span>
                                        </div>
                                        <input type="text" 
                                                class="form-control" 
                                                id="id_sales" 
                                                name="id_sales" 
                                                placeholder="<?= $lang['trx_plc_id_sales'] ?? 'Masukkan ID Sales' ?>" 
                                                inputmode="numeric"
                                                pattern="[0-9]*"
                                                oninput="this.value = this.value.replace(/[^0-9]/g, '')"
                                                required>
                                    </div>

                                </div>
                            </div>

                            <!-- SECTION 3: Pemindaian Item dengan Mode Auto-Scan -->
                            <div class="bg-white border rounded-3 p-4 mb-4 shadow-sm">
                                <h6 class="fw-bold mb-3" style="color: #4b5563;">
                                    <i class="bi bi-upc-scan me-2"></i><?= $lang['trx_sect_scan'] ?? 'Pemindaian Item' ?>
                                </h6>

                                <!-- Fast Auto-Scan Box -->
                                <div class="bg-light p-3 rounded-3 mb-4 border">
                                    <label class="form-label small fw-bold mb-1" style="color: #556ee6;">
                                        <i class="bi bi-lightning-charge-fill me-1"></i> <?= $lang['trx_info_scan'] ?? 'Auto-Scan (Auto-Add):' ?>
                                    </label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-white" style="color: #556ee6;"><i class="bi bi-upc-scan"></i></span>
                                        <input type="text" id="mainBarcodeInput" class="form-control scan-input-main" placeholder="<?= $lang['trx_plc_scan'] ?? 'Scan barcode 9 digit di sini...' ?>" autocomplete="off" autofocus>
                                    </div>
                                </div>

                                <!-- CONTAINER ROW ITEM DINAMIS -->
                                <div id="dynamic-item-container" class="row g-4 mb-3">
                                    <!-- Item Card awal ter-generate otomatis via assets/js/scripts.js -->
                                </div>
                                    
                                <div class="d-flex justify-content-between align-items-center border-top pt-3">
                                    <!-- Tombol Tambah Baris Disembunyikan -->
                                    <button type="button" id="btn-validate" class="btn text-white fw-bold px-4 py-2" style="background-color: #556ee6; border-radius: 6px; font-size: 14px;">
                                        <i class="bi bi-check2-circle me-2"></i><?= $lang['btn_validate_items'] ?? 'Validate Items' ?>
                                    </button>
                                </div>
                            </div>

                            <!-- Tombol Aksi Bawah -->
                            <div class="d-flex justify-content-end gap-3 mt-4 mb-5">
                                <button type="button" class="btn-submit border fw-bold px-4 text-white" style="border-radius: 6px;" onclick="window.location.href='pending.php'">
                                    <?= $lang['btn_cancel'] ?? 'Batal' ?>
                                </button>

                                <button type="submit" id="btnProses" class="btn-proses-custom fw-bold px-4 text-white" disabled>
                                    <?= $lang['btn_proses_trx'] ?? 'Proses Transaksi' ?>
                                </button>
                            </div>

                        </div>
                    </div>

                </form>
                
            </div>
        </main>

    </div>
</div>

<!-- Scripts -->
<script src="assets/vendor-ui/jquery/jquery-3.6.0.min.js"></script>
<script src="assets/vendor-ui/bootstrap/bootstrap.bundle.min.js"></script>

<!-- BRIDGE DATA PHP KE JAVASCRIPT -->
<script>
    window.transactionData = {
        isAuto: <?php echo wh_js($is_auto ?? false); ?>,
        idRequest: <?php echo wh_js($auto_id_request ?? ''); ?>,
        qtyTop: <?php echo wh_js((int)($qty_top ?? 0)); ?>,
        qtyBottoms: <?php echo wh_js((int)($qty_bottoms ?? 0)); ?>,
        totalQty: <?php echo wh_js((int)($total_qty ?? 0)); ?>,
        sizeTop: <?php echo wh_js($size_top ?? ''); ?>,
        sizeBottoms: <?php echo wh_js($size_bottoms ?? ''); ?>,
        gender: <?php echo wh_js($gender ?? ''); ?>
    };
</script>

<script src="assets/js/scripts.js"></script>

</body>
</html>