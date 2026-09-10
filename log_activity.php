<?php
require_once __DIR__ . '/includes/auth_check.php';
include 'controllers/query_log.php'; // Controller PDO yang sudah diberi Limit & Offset
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta name="csrf-token" content="<?= wh_escape(wh_csrf_token()) ?>">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>WC | <?= $lang['menu_log_activity'] ?? 'Log Activity' ?></title>

    <link rel="icon" type="image/png" href="assets/img/favicon-icon.png">
    
    <!-- Bootstrap & Icons -->
    <link href="assets/vendor-ui/bootstrap/bootstrap.min.css" rel="stylesheet">
    <link href="assets/vendor-ui/bootstrap-icons/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>

<div class="app-container">

    <!-- Sidebar & Topbar -->
    <?php include 'includes/sidebar.php'; ?>

    <div class="main-wrapper">
        <?php include 'includes/topbar.php'; ?>

        <!-- MAIN CONTENT AREA -->
        <main class="content-area p-4">
            
            <!-- Header Halaman -->
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div class="page-title mb-0">
                    <h4 class="fw-bold m-0" style="color: #1e293b;"><?= $lang['menu_log_activity'] ?? 'Log Activity' ?></h4>
                    <p class="text-secondary m-0 mt-1" style="font-size: 14px;"><?= $lang['log_subtitle'] ?? 'Riwayat aktivitas dan transaksi sistem' ?></p>
                </div>
            </div>

            <!-- Form Filter Tanggal dan Pencarian -->
            <form method="GET" action="log_activity.php" class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
                
                <!-- Filter Tanggal (Date Range) -->
                <div class="date-filter-group shadow-sm">
                    <i class="bi bi-calendar3 text-secondary me-2"></i>
                    <input type="date" name="start_date" value="<?php echo htmlspecialchars($start_date ?? ''); ?>" title="<?= $lang['lbl_mulai_tanggal'] ?? 'Mulai Tanggal' ?>">
                    <span class="date-separator px-2 text-muted">-</span>
                    <input type="date" name="end_date" value="<?php echo htmlspecialchars($end_date ?? ''); ?>" title="<?= $lang['lbl_sampai_tanggal'] ?? 'Sampai Tanggal' ?>">
                    <button type="submit" class="btn btn-action-icon border-0 ms-2 text-primary fw-bold" title="<?= $lang['btn_terapkan_filter'] ?? 'Terapkan Filter' ?>">
                        <i class="bi bi-funnel-fill"></i>
                    </button>
                </div>
                
                <!-- Input Pencarian (Kirim via GET agar Pagination Tetap Berjalan) -->
                <div class="input-group mb-2" style="width: 250px;">
                    <input type="text" id="searchInput" class="form-control ps-3" placeholder="<?= $lang['search_placeholder'] ?? 'Cari ...' ?>" style="font-size: 13px;">
                    <span class="input-group-text bg-white"><i class="bi bi-search text-secondary"></i></span>
                </div>
            </form>
            
            <!-- Table Container (Card) -->
            <div class="table-card shadow-sm border-0 rounded-3 mb-4 bg-white">
                <div class="table-responsive">
                    <table class="table align-middle mb-0">
                        <thead class="table-light border-bottom text-secondary small">
                            <tr>
                                <th scope="col" width="18%" class="py-3 ps-3"><?= $lang['th_waktu'] ?? 'Waktu' ?></th>
                                <th scope="col" width="22%" class="py-3"><?= $lang['th_pengguna'] ?? 'Pengguna' ?></th>
                                <th scope="col" width="47%" class="py-3"><?= $lang['th_aktivitas'] ?? 'Aktivitas' ?></th>
                                <th scope="col" width="13%" class="py-3 text-center"><?= $lang['th_modul'] ?? 'Modul' ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($logs)): ?>
                                <?php foreach ($logs as $row): ?>
                                    <?php 
                                        $timestamp    = strtotime($row['created_at']);
                                        $tgl          = date('d M Y', $timestamp);
                                        $jam          = date('H:i', $timestamp) . ' WIB';
                                        $modul        = !empty($row['modul']) ? $row['modul'] : 'Sistem';
                                        $theme        = getLogTheme($modul);
                                        
                                        $nama_user    = $row['nama_user'] ?? 'User';
                                        $initial_user = strtoupper(substr($nama_user, 0, 1));
                                    ?>
                                    <tr class="border-bottom">
                                        <td class="ps-3">
                                            <div class="fw-bold text-dark" style="font-size: 14px;"><?php echo $tgl; ?></div>
                                            <div class="text-secondary" style="font-size: 12px;"><?php echo $jam; ?></div>
                                        </td>
                                        <td>
                                            <div class="d-flex align-items-center gap-2">
                                                <div class="avatar bg-primary text-white rounded-circle d-flex align-items-center justify-content-center fw-bold shadow-sm" style="width: 36px; height: 36px; font-size: 14px; background-color: #556ee6 !important;">
                                                    <?php echo $initial_user; ?>
                                                </div>
                                                <div>
                                                    <div class="fw-bold text-dark" style="font-size: 13px;"><?php echo htmlspecialchars($nama_user); ?></div>
                                                    <div class="text-secondary" style="font-size: 11px;"><?php echo htmlspecialchars($row['role'] ?? 'User'); ?></div>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="d-flex align-items-center gap-3">
                                                <div class="activity-icon <?php echo $theme['class']; ?>">
                                                    <i class="bi <?php echo $theme['icon']; ?>"></i>
                                                </div>
                                                <div>
                                                    <span class="fw-bold text-dark" style="font-size: 14px;"><?php echo htmlspecialchars($row['aktivitas'] ?? ''); ?></span>
                                                    <p class="text-secondary m-0 mt-1" style="font-size: 13px;"><?php echo htmlspecialchars($row['keterangan'] ?? ''); ?></p>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="text-center">
                                            <span class="badge border px-3 py-2 fw-semibold rounded-pill <?php echo $theme['badge']; ?>">
                                                <?php echo htmlspecialchars($modul); ?>
                                            </span>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="4" class="text-center py-4 text-muted">
                                        <i class="bi bi-inbox display-6 d-block mb-2"></i>
                                        <?= $lang['log_empty'] ?? 'Tidak ada riwayat aktivitas ditemukan pada periode ini.' ?>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <!-- FOOTER / NAVIGASI PAGINATION -->
                <?php if (isset($total_pages) && $total_pages > 1): ?>
                <div class="d-flex justify-content-between align-items-center p-3 border-top">
                    <div class="small text-muted">
                        <?= $lang['page_info_showing'] ?? 'Menampilkan' ?> <b><?= $offset + 1 ?></b> <?= $lang['page_info_to'] ?? 'sampai' ?> <b><?= min($offset + $limit, $total_rows) ?></b> <?= $lang['page_info_of'] ?? 'dari total' ?> <b><?= $total_rows ?></b> <?= $lang['page_info_entries'] ?? 'data' ?>
                    </div>
                    <nav aria-label="Navigasi Halaman Log">
                        <ul class="pagination pagination-sm m-0">
                            <!-- Tombol Previous -->
                            <li class="page-item <?= ($page <= 1) ? 'disabled' : '' ?>">
                                <a class="page-link" href="?page=<?= $page - 1 ?>&start_date=<?= urlencode($start_date) ?>&end_date=<?= urlencode($end_date) ?>&search=<?= urlencode($search) ?>">
                                    <i class="bi bi-chevron-left"></i>
                                </a>
                            </li>

                            <!-- Angka Halaman -->
                            <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                                <li class="page-item <?= ($page == $i) ? 'active' : '' ?>">
                                    <a class="page-link" href="?page=<?= $i ?>&start_date=<?= urlencode($start_date) ?>&end_date=<?= urlencode($end_date) ?>&search=<?= urlencode($search) ?>"><?= $i ?></a>
                                </li>
                            <?php endfor; ?>

                            <!-- Tombol Next -->
                            <li class="page-item <?= ($page >= $total_pages) ? 'disabled' : '' ?>">
                                <a class="page-link" href="?page=<?= $page + 1 ?>&start_date=<?= urlencode($start_date) ?>&end_date=<?= urlencode($end_date) ?>&search=<?= urlencode($search) ?>">
                                    <i class="bi bi-chevron-right"></i>
                                </a>
                            </li>
                        </ul>
                    </nav>
                </div>
                <?php endif; ?>

            </div>
            
        </main>
    </div>
</div>

<script src="assets/vendor-ui/jquery/jquery-3.6.0.min.js"></script>
<script src="assets/vendor-ui/bootstrap/bootstrap.bundle.min.js"></script>
<script src="assets/js/scripts.js?v=<?= time(); ?>"></script>
</body>
</html>

<?php 
// Menutup koneksi PDO dengan aman
if (isset($pdo)) {
    $pdo = null;
}
?>