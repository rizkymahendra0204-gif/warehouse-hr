<?php
require_once __DIR__ . '/includes/auth_check.php';
include 'controllers/query_stokbarang.php';
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $lang['stok_title'] ?? 'Inventory' ?> - HR Warehouse</title>

    <link rel="icon" type="image/png" href="assets/img/favicon.png">
    
    <!-- Bootstrap & Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    
    <!-- Memanggil file CSS Anda -->
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body id="page-top">

<div class="app-container">

    <!-- Memanggil file Sidebar -->
    <?php include 'includes/sidebar.php'; ?>

    <div class="main-wrapper">
        
        <!-- Memanggil file Topbar -->
        <?php include 'includes/topbar.php'; ?>

        <!-- MAIN CONTENT AREA -->
        <main class="content-area p-4">
            
            <!-- Header Halaman & Tombol Export Excel -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div class="page-title"><?= $lang['stok_title'] ?? 'Inventory' ?>
                    <p class="text-secondary m-0 mt-1" style="font-size: 14px;"><?= $lang['stok_subtitle'] ?? 'Manajemen inventaris seragam dan kelengkapan' ?></p>
                </div>
                <div class="d-flex gap-2">
                    <!-- Tombol Export Excel langsung memanggil parameter controller ini -->
                    <a href="?tab=<?= urlencode($tab) ?>&search=<?= urlencode($search) ?>&export=excel" class="btn btn-process fw-bold btn-sm px-3 py-2 shadow-sm" style="font-size: 13px;">
                        <i class="bi bi-file-earmark-excel me-1"></i> <?= $lang['btn_export_excel'] ?? 'Export To Excel' ?>
                    </a>
                </div>
            </div>

            <!-- Area Filter dan Pencarian -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <!-- Tab Filter Status -->
                <div class="d-flex gap-2 bg-white p-1 rounded border shadow-sm align-items-center">
                    <a href="?tab=semua&search=<?= urlencode($search) ?>" class="filter-tab <?= $tab === 'semua' ? 'active' : '' ?>"><?= $lang['tab_semua'] ?? 'Semua' ?></a>
                    <a href="?tab=available&search=<?= urlencode($search) ?>" class="filter-tab <?= $tab === 'available' ? 'active' : '' ?>"><?= $lang['tab_available'] ?? 'Available' ?></a>
                    <a href="?tab=soldout&search=<?= urlencode($search) ?>" class="filter-tab <?= $tab === 'soldout' ? 'active' : '' ?>"><?= $lang['tab_sold_out'] ?? 'Sold Out' ?></a>
                    <a href="?tab=inactive&search=<?= urlencode($search) ?>" class="filter-tab <?= $tab === 'inactive' ? 'active' : '' ?>"><?= $lang['tab_inactive'] ?? 'Inactive' ?></a>
                </div>
                
                <!-- Form Pencarian -->
                <form method="GET" action="" class="input-group shadow-sm" style="width: 300px; border-radius: 8px; overflow: hidden;">
                    <input type="hidden" name="tab" value="<?= htmlspecialchars($tab) ?>">
                    <span class="input-group-text bg-white border-end-0"><i class="bi bi-search text-secondary"></i></span>
                    <input type="text" name="search" id="searchInput" class="form-control border-start-0 ps-0" 
                        placeholder="<?= $lang['search_placeholder'] ?? 'Cari ...' ?>" 
                        value="<?= htmlspecialchars($search) ?>">
                </form>
            </div>
            
            <!-- Table Container (Card) -->
            <div class="table-card">
                <div class="table-responsive">
                    <table class="table align-middle mb-0">
                        <thead>
                            <tr>
                                <th scope="col" width="5%"><?= $lang['table_no'] ?? 'No' ?></th>
                                <th scope="col" width="15%"><?= $lang['th_barcode'] ?? 'Barcode' ?></th>
                                <th scope="col" width="35%"><?= $lang['th_detail_item'] ?? 'Detail Item' ?></th>
                                <th scope="col" width="15%"><?= $lang['th_kategori'] ?? 'Kategori' ?></th>
                                <th scope="col" width="15%" class="text-center"><?= $lang['table_status'] ?? 'Status' ?></th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php if (!empty($list_item)): 
                            $no = 1; 
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
                                            <?= $lang['lbl_ukuran'] ?? 'Ukuran:' ?> <?= htmlspecialchars($row['size'] ?? '') ?>
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
                                    <i class="bi bi-box fs-1 d-block mb-2"></i> <?= $lang['empty_stok'] ?? 'Belum ada data barang di database.' ?>
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