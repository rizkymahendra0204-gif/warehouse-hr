<?php
session_start();
include 'controllers/query_log.php'; // Koneksi PDO Anda
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Log Activity - HR Warehouse</title>
    
    <!-- Bootstrap & Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
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
            
            <!-- Header Halaman & Tombol Export -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h4 class="fw-bold m-0" style="color: #1e293b;">Log Activity</h4>
                    <p class="text-secondary m-0 mt-1" style="font-size: 14px;">Riwayat aktivitas dan transaksi sistem</p>
                </div>
            </div>

            <!-- Form Filter Tanggal dan Pencarian -->
            <form method="GET" action="log_activity.php" class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
                
                <!-- Filter Tanggal (Date Range) -->
                <div class="date-filter-group shadow-sm">
                    <i class="bi bi-calendar3 text-secondary me-2"></i>
                    <input type="date" name="start_date" value="<?php echo $start_date; ?>" title="Mulai Tanggal">
                    <span class="date-separator px-2 text-muted">-</span>
                    <input type="date" name="end_date" value="<?php echo $end_date; ?>" title="Sampai Tanggal">
                    <button type="submit" class="btn btn-light border-0 ms-2 text-primary fw-bold" title="Terapkan Filter">
                        <i class="bi bi-funnel-fill"></i>
                    </button>
                </div>
                
                <!-- Input Pencarian -->
                <div class="input-group shadow-sm" style="width: 320px; border-radius: 8px; overflow: hidden;">
                    <span class="input-group-text bg-white border-end-0"><i class="bi bi-search text-secondary"></i></span>
                    <input type="text" name="search" class="form-control border-start-0 ps-0" 
                           placeholder="Cari aktivitas, user, atau ket..." 
                           value="<?php echo htmlspecialchars($search); ?>">
                    <button class="btn btn-primary" type="submit">Cari</button>
                </div>
            </form>
            
            <!-- Table Container (Card) -->
            <div class="table-card bg-white rounded-3 border shadow-sm">
                <div class="table-responsive">
                    <table class="table align-middle mb-0">
                        <thead class="bg-light">
                            <tr>
                                <th scope="col" width="18%" class="py-3 ps-3">Waktu</th>
                                <th scope="col" width="22%" class="py-3">Pengguna</th>
                                <th scope="col" width="45%" class="py-3">Aktivitas</th>
                                <th scope="col" width="15%" class="py-3 text-center">Modul</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($logs)): ?>
                                <?php foreach ($logs as $row): ?>
                                    <?php 
                                        $timestamp = strtotime($row['created_at']);
                                        $tgl = date('d M Y', $timestamp);
                                        $jam = date('H:i', $timestamp) . ' WIB';
                                        $modul = !empty($row['modul']) ? $row['modul'] : 'Sistem';
                                        $theme = getLogTheme($modul);
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
                                                    <div class="fw-bold text-dark" style="font-size: 13px;"><?php echo htmlspecialchars($row['nama_user']); ?></div>
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
                                                    <span class="fw-bold text-dark" style="font-size: 14px;"><?php echo htmlspecialchars($row['aktivitas']); ?></span>
                                                    <p class="text-secondary m-0 mt-1" style="font-size: 13px;"><?php echo $row['keterangan']; ?></p>
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
                                        Tidak ada riwayat aktivitas ditemukan pada periode ini.
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

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="assets/js/scripts.js"></script>
</body>
</html>