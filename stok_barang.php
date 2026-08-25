<?php
require_once __DIR__ . '/includes/auth_check.php';
include 'controllers/query_stokbarang.php';

// Pastikan variabel default pagination aman
$page        = $page ?? 1;
$limit       = $limit ?? 100;
$total_rows  = $total_rows ?? 0;
$total_pages = $total_pages ?? 1;
$no_awal     = $no_awal ?? 1;

// Tangkap parameter filter untuk form UI
$filter_tipe   = $_GET['tipe'] ?? '';
$filter_gender = $_GET['gender'] ?? '';
$filter_size   = $_GET['size'] ?? '';
?>

<!DOCTYPE html>
<html lang="<?= $_SESSION['lang'] ?? 'en' ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>WC | <?= $lang['stok_title'] ?? 'Inventory' ?></title>

    <link rel="icon" type="image/png" href="assets/img/favicon-icon.png">
    
    <!-- Bootstrap & Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    
    <!-- File CSS Utama -->
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body id="page-top">

<div class="app-container">

    <!-- Sidebar -->
    <?php include 'includes/sidebar.php'; ?>

    <div class="main-wrapper">
        
        <!-- Topbar -->
        <?php include 'includes/topbar.php'; ?>

        <!-- MAIN CONTENT AREA -->
        <main class="content-area p-4">
            
            <!-- Header Halaman -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div class="page-title">
                    <h4 class="fw-bold m-0"><?= $lang['stok_title'] ?? 'Inventory' ?></h4>
                    <p class="text-secondary m-0 mt-1" style="font-size: 14px;"><?= $lang['stok_subtitle'] ?? 'Uniform and equipment inventory management' ?></p>
                </div>
            </div>
            
            <!-- Area Filter Utama (Tab Status & Export) -->
            <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
                <!-- Tab Filter Status (Kiri) -->
                <div class="d-flex gap-2 bg-white p-1 rounded border shadow-sm align-items-center">
                    <a href="?tab=semua&tipe=<?= urlencode($filter_tipe) ?>&gender=<?= urlencode($filter_gender) ?>&size=<?= urlencode($filter_size) ?>&search=<?= urlencode($search) ?>&limit=<?= $limit ?>" class="filter-tab <?= $tab === 'semua' ? 'active' : '' ?>"><?= $lang['tab_semua'] ?? 'All' ?></a>
                    <a href="?tab=available&tipe=<?= urlencode($filter_tipe) ?>&gender=<?= urlencode($filter_gender) ?>&size=<?= urlencode($filter_size) ?>&search=<?= urlencode($search) ?>&limit=<?= $limit ?>" class="filter-tab <?= $tab === 'available' ? 'active' : '' ?>"><?= $lang['tab_available'] ?? 'Available' ?></a>
                    <a href="?tab=soldout&tipe=<?= urlencode($filter_tipe) ?>&gender=<?= urlencode($filter_gender) ?>&size=<?= urlencode($filter_size) ?>&search=<?= urlencode($search) ?>&limit=<?= $limit ?>" class="filter-tab <?= $tab === 'soldout' ? 'active' : '' ?>"><?= $lang['tab_sold_out'] ?? 'Sold Out' ?></a>
                    <a href="?tab=inactive&tipe=<?= urlencode($filter_tipe) ?>&gender=<?= urlencode($filter_gender) ?>&size=<?= urlencode($filter_size) ?>&search=<?= urlencode($search) ?>&limit=<?= $limit ?>" class="filter-tab <?= $tab === 'inactive' ? 'active' : '' ?>"><?= $lang['tab_inactive'] ?? 'Inactive' ?></a>
                </div>
                
                <!-- Tombol Export Excel (Kanan) -->
                <div>
                    <?php 
                    $export_params = $_GET;
                    $export_params['export'] = 'excel';
                    ?>
                    <a href="?<?= http_build_query($export_params) ?>" class="btn btn-cetak-excel fw-bold btn-sm px-3 py-2 shadow-sm text-nowrap" style="font-size: 13px;">
                        <i class="bi bi-file-earmark-excel me-1"></i> <?= $lang['btn_export_excel'] ?? 'Export To Excel' ?>
                    </a>
                </div>
            </div>

            <!-- Form Filter Dropdown Tambahan & Pencarian -->
            <div class="card border-0 shadow-sm rounded-3 p-3 mb-4 bg-white">
                <form method="GET" action="" class="row g-2 align-items-center">
                    <input type="hidden" name="tab" value="<?= htmlspecialchars($tab) ?>">

                    <!-- Filter Kategori / Tipe -->
                    <div class="col-6 col-md-2">
                        <select name="tipe" class="form-select" onchange="this.form.submit()">
                            <option value="">-- <?= $lang['th_kategori'] ?? 'Category' ?> --</option>
                            <option value="Baju" <?= strtolower($filter_tipe) === 'baju' ? 'selected' : '' ?>><?= $lang['type_baju'] ?? 'Shirt' ?></option>
                            <option value="Celana" <?= strtolower($filter_tipe) === 'celana' ? 'selected' : '' ?>><?= $lang['type_celana'] ?? 'Pants' ?></option>
                        </select>
                    </div>

                    <!-- Filter Gender -->
                    <div class="col-6 col-md-2">
                        <select name="gender" class="form-select" onchange="this.form.submit()">
                            <option value="">-- <?= $lang['gen_lbl_gender'] ?? 'Gender' ?> --</option>
                            <option value="Pria" <?= strtolower($filter_gender) === 'pria' ? 'selected' : '' ?>><?= $lang['gender_pria'] ?? 'Men' ?></option>
                            <option value="Wanita" <?= strtolower($filter_gender) === 'wanita' ? 'selected' : '' ?>><?= $lang['gender_wanita'] ?? 'Women' ?></option>
                        </select>
                    </div>

                    <!-- Filter Size -->
                    <div class="col-6 col-md-2">
                        <select name="size" class="form-select" onchange="this.form.submit()">
                            <option value="">-- <?= $lang['gen_lbl_ukuran'] ?? 'Size' ?> --</option>
                            <?php foreach (['S','M','L','XL','28','30','32','34','36'] as $sz): ?>
                                <option value="<?= $sz ?>" <?= strtolower($filter_size) === strtolower($sz) ? 'selected' : '' ?>><?= $sz ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- Limit Per Halaman -->
                    <!--<div class="col-6 col-md-2">
                        <select name="limit" class="form-select" onchange="this.form.submit()">
                            <option value="100" <?= $limit == 100 ? 'selected' : '' ?>>100 Per Halaman</option>
                            <option value="250" <?= $limit == 250 ? 'selected' : '' ?>>250 Per Halaman</option>
                            <option value="500" <?= $limit == 500 ? 'selected' : '' ?>>500 Per Halaman</option>
                        </select>
                    </div>-->

                    <!-- Search Input & Buttons -->
                    <div class="col-12 col-md-4 ms-auto d-flex justify-content-end">
                        <div class="input-group" style="width: 250px;">
                            <input type="text" id="searchInput" class="form-control ps-4" placeholder="<?= $lang['search_placeholder'] ?? 'Search ...' ?>" value="<?= htmlspecialchars($search) ?>">
                            <span class="input-group-text bg-white"><i class="bi bi-search text-secondary"></i></span>
                        </div>
                    </div>
                </form>
            </div>
            
            <!-- Table Container (Card) -->
            <div class="table-card shadow-sm border-0 rounded-3 mb-3">
                <div class="table-responsive">
                    <table class="table align-middle mb-0" id="stokTable">
                        <thead class="table-light text-secondary small border-bottom">
                            <tr>
                                <th scope="col" width="5%"><?= $lang['table_no'] ?? 'NO' ?></th>
                                <th scope="col" width="15%"><?= $lang['th_barcode'] ?? 'BARCODE' ?></th>
                                <th scope="col" width="35%"><?= $lang['th_detail_item'] ?? 'ITEM DETAILS' ?></th>
                                <th scope="col" width="15%"><?= $lang['th_kategori'] ?? 'CATEGORY' ?></th>
                                <th scope="col" width="15%" class="text-center"><?= $lang['table_status'] ?? 'STATUS' ?></th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php if (!empty($list_item)): 
                            $no = $no_awal; 
                        ?>
                            <?php foreach ($list_item as $row): 
                                $status_tx  = $row['status_barang'] ?? ''; 
                                $status_brg = $row['status_transaksi'] ?? '';
                                
                                $check_tx  = strtolower(trim($status_tx));
                                $check_brg = strtolower(trim($status_brg));
                                
                                if ($check_tx === 'available' && $check_brg === 'active') {
                                    $text_color = '#16a34a'; 
                                    $icon_class = 'bi-check-circle-fill';
                                } elseif ($check_brg === 'available' && $check_tx === 'inactive') {
                                    $text_color = '#dc2626'; 
                                    $icon_class = 'bi-arrow-counterclockwise';
                                } else {
                                    $text_color = '#16a34a'; 
                                    $icon_class = 'bi-check-circle-fill';
                                }
                            ?>
                                <tr>
                                    <td>
                                        <span class="fw-bold text-dark" style="font-size: 15px;">
                                            <?= $no++ ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="barcode-badge">
                                            <i class="bi bi-upc-scan"></i> <?= htmlspecialchars($row['barcode'] ?? '') ?>
                                        </span>
                                    </td>
                                    <td>
                                        <div class="fw-bold text-dark" style="font-size: 15px;">
                                            <?= htmlspecialchars($row['tipe'] ?? '') ?> SA <?= htmlspecialchars($row['gender'] ?? '') ?>
                                        </div>
                                        <div class="text-secondary mt-1" style="font-size: 13px;">
                                            <?= $lang['lbl_ukuran'] ?? 'Size:' ?> <?= htmlspecialchars($row['size'] ?? '') ?>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="text-secondary fw-bold" style="font-size: 13px;">
                                            <?= htmlspecialchars($row['tipe'] ?? '') ?>
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge-status" style="color: <?= $text_color ?>; font-weight: 600;">
                                            <i class="bi <?= $icon_class ?>" style="font-size: 1.0rem;"></i> <?= htmlspecialchars($status_brg) ?> (<?= htmlspecialchars($status_tx) ?>)
                                        </span>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="5" class="text-center text-muted py-5">
                                    <i class="bi bi-box fs-1 d-block mb-2"></i> <?= $lang['empty_stok'] ?? $lang['rep_empty_stok'] ?? 'No stock data found.' ?>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                    </table>
                </div>
            </div>

            <!-- BAGIAN NAVIGASI PAGINATION -->
            <?php if ($total_rows > 0): ?>
                <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 px-1">
                    <!-- Informasi Baris Data -->
                    <div class="text-secondary small">
                        Showing <strong class="text-dark"><?= min($no_awal, $total_rows) ?></strong> - <strong class="text-dark"><?= min($no_awal + count($list_item) - 1, $total_rows) ?></strong> of <strong class="text-dark"><?= $total_rows ?></strong> items
                    </div>

                    <!-- Tombol Navigasi Halaman -->
                    <?php if ($total_pages > 1): ?>
                        <ul class="pagination pagination-sm m-0">
                            <!-- Button Previous -->
                            <li class="page-item <?= ($page <= 1) ? 'disabled' : '' ?>">
                                <?php $prev_params = $_GET; $prev_params['page'] = $page - 1; ?>
                                <a class="page-link" href="?<?= http_build_query($prev_params) ?>">
                                    <i class="bi bi-chevron-left"></i>
                                </a>
                            </li>

                            <!-- Angka Halaman -->
                            <?php 
                            $start_page = max(1, $page - 2);
                            $end_page   = min($total_pages, $page + 2);
                            for ($p = $start_page; $p <= $end_page; $p++): 
                                $num_params = $_GET; 
                                $num_params['page'] = $p; 
                            ?>
                                <li class="page-item <?= ($page == $p) ? 'active' : '' ?>">
                                    <a class="page-link" href="?<?= http_build_query($num_params) ?>"><?= $p ?></a>
                                </li>
                            <?php endfor; ?>

                            <!-- Button Next -->
                            <li class="page-item <?= ($page >= $total_pages) ? 'disabled' : '' ?>">
                                <?php $next_params = $_GET; $next_params['page'] = $page + 1; ?>
                                <a class="page-link" href="?<?= http_build_query($next_params) ?>">
                                    <i class="bi bi-chevron-right"></i>
                                </a>
                            </li>
                        </ul>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
            
        </main>
    </div>
</div>

<!-- ELEMEN TOMBOL SCROLL TO TOP -->
<a class="scroll-to-top rounded" href="#page-top" id="scrollToTopBtn">
    <i class="bi bi-chevron-up fs-5"></i>
</a>

<!-- Scripts -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="assets/js/scripts.js"></script>

</body>
</html>