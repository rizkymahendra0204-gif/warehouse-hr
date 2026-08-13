<?php 
require_once __DIR__ . '/includes/auth_check.php';

// Include controller backend audit/management
include 'controllers/query_audit.php';
?>
<!DOCTYPE html>
<html lang="<?php echo $_SESSION['lang'] ?? 'id'; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Warehouse Management - HR Warehouse</title>

    <link rel="icon" type="image/png" href="assets/img/favicon.png">
    
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
            <!-- Header & Tombol Action Toggle -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h4 class="fw-bold mb-1">Warehouse Management</h4>
                    <p class="text-muted small mb-0"><?php echo $lang['audit_subtitle'] ?? 'Ringkasan ketersediaan stok gudang dan evaluasi barang return'; ?></p>
                </div>
                
                <!-- Tombol Toggle Mode -->
                <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
                <form method="POST" action="">
                    <input type="hidden" name="action_type" value="toggle_audit">
                    <?php if ($is_audit_active): ?>
                        <input type="hidden" name="audit_status" value="0">
                        <button type="submit" class="btn btn-proses fw-bold shadow-sm" onclick="return confirm('Tutup periode Warehouse Management?')">
                            <i class="bi bi-unlock-fill me-1"></i> Tutup Periode
                        </button>
                    <?php else: ?>
                        <input type="hidden" name="audit_status" value="1">
                        <button type="submit" class="btn btn-proses-custom fw-bold shadow-sm" onclick="return confirm('Buka periode Warehouse Management?')">
                            <i class="bi bi-lock-fill me-1"></i> Buka Periode
                        </button>
                    <?php endif; ?>
                </form>
                <?php endif; ?>
            </div>

            <!-- 1. CONTAINER RINGKASAN: STOK TERSEDIA DI GUDANG (AVAILABLE & ACTIVE) -->
            <div class="bg-white border rounded-3 p-4 mb-4 shadow-sm">
                <div class="d-flex align-items-center mb-3">
                    <div class="text-success fw-bold text-uppercase" style="font-size: 13px; letter-spacing: 0.5px;">
                        <i class="bi bi-box-seam me-2"></i> Stok Tersedia di Gudang (Available & Active)
                    </div>
                </div>
                <div class="row g-4">
                    <?php 
                    $order = [
                        ['g' => 'Pria', 't' => 'Baju', 'label' => 'Atasan'],
                        ['g' => 'Pria', 't' => 'Celana', 'label' => 'Bawahan'],
                        ['g' => 'Wanita', 't' => 'Baju', 'label' => 'Atasan'],
                        ['g' => 'Wanita', 't' => 'Celana', 'label' => 'Bawahan'],
                    ];
                    foreach ($order as $kat):
                        $g = $kat['g']; $t = $kat['t']; $lbl = $kat['label'];
                        $badge = ($g === 'Pria') ? 'bg-primary' : 'bg-danger';
                        $border = ($g === 'Pria') ? '#0d6efd' : '#dc3545';
                        $sizes = $stok_tersedia[$g][$t] ?? [];
                    ?>
                        <div class="col-md-6 col-lg-3">
                            <div class="border rounded-3 p-3 bg-white h-100 shadow-sm border-top border-3" style="border-top-color: <?= $border ?> !important;">
                                <div class="fw-bold text-dark mb-3 border-bottom pb-2 d-flex align-items-center" style="font-size: 13px;">
                                    <span class="badge <?= $badge ?> me-2"><?= $g ?></span> <?= $t ?> (<?= $lbl ?>)
                                </div>
                                <div class="row g-2">
                                    <?php foreach ($sizes as $s => $total): ?>
                                        <div class="col-3">
                                            <div class="bg-light border rounded text-center py-2 px-1 shadow-sm">
                                                <div class="fw-bold text-secondary mb-1" style="font-size: 11px;"><?= htmlspecialchars($s) ?></div>
                                                <div class="fw-bold <?= ($total > 0) ? 'text-dark' : 'text-muted' ?>" style="font-size: 14px;"><?= number_format($total) ?></div>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- 2. CONTAINER RINGKASAN: STOK RETURN -->
            <div class="bg-white border rounded-3 p-4 mb-4 shadow-sm">
                <div class="d-flex align-items-center mb-3">
                    <div class="text-warning fw-bold text-uppercase" style="font-size: 13px; letter-spacing: 0.5px;">
                        <i class="bi bi-arrow-counterclockwise me-2"></i> Stok Masuk Return (Dikembalikan)
                    </div>
                </div>
                <div class="row g-4">
                    <?php 
                    foreach ($order as $kat):
                        $g = $kat['g']; $t = $kat['t']; $lbl = $kat['label'];
                        $sizes = $stok_return[$g][$t] ?? [];
                    ?>
                        <div class="col-md-6 col-lg-3">
                            <div class="border rounded-3 p-3 bg-white h-100 shadow-sm border-top border-3" style="border-top-color: #ffc107 !important;">
                                <div class="fw-bold text-dark mb-3 border-bottom pb-2 d-flex align-items-center" style="font-size: 13px;">
                                    <span class="badge bg-warning text-dark me-2"><?= $g ?></span> <?= $t ?> (<?= $lbl ?>)
                                </div>
                                <div class="row g-2">
                                    <?php foreach ($sizes as $s => $total): ?>
                                        <div class="col-3">
                                            <div class="bg-light border rounded text-center py-2 px-1 shadow-sm">
                                                <div class="fw-bold text-secondary mb-1" style="font-size: 11px;"><?= htmlspecialchars($s) ?></div>
                                                <div class="fw-bold <?= ($total > 0) ? 'text-danger' : 'text-muted' ?>" style="font-size: 14px;"><?= number_format($total) ?></div>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Banner Pemberitahuan Status Audit -->
            <?php if (!$is_audit_active): ?>
                <div class="alert alert-warning d-flex align-items-center mb-3" role="alert">
                    <i class="bi bi-lock-fill me-2 fs-5"></i>
                    <div>
                        <strong>Periode Warehouse Management Ditutup:</strong> Perubahan status barang dikunci sementara.
                    </div>
                </div>
            <?php else: ?>
                <div class="alert alert-success d-flex align-items-center mb-3" role="alert">
                    <i class="bi bi-check-circle-fill me-2 fs-5"></i>
                    <div>
                        <strong>Mode Warehouse Management Aktif:</strong> Silakan lakukan perubahan status.
                    </div>
                </div>
            <?php endif; ?>

            <!-- Alert Notifikasi Session -->
            <div class="floating-alert-container">
            <?php if (isset($_SESSION['alert_message'])): ?>
                <div class="alert alert-<?php echo $_SESSION['alert_type']; ?> alert-dismissible fade show shadow-sm mb-3" role="alert">
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

            <!-- Tabel Data Stok Evaluasi Audit -->
            <div class="bg-white border rounded-3 p-4 shadow-sm">
                <h6 class="fw-bold mb-3"><i class="bi bi-list-task me-1"></i> Daftar Barang Evaluasi Warehouse Management</h6>
                <div class="table-responsive">
                    <table class="table table-hover align-middle m-0" style="font-size: 14px;">
                        <thead class="text-secondary small border-bottom bg-light">
                            <tr>
                                <th class="py-3 text-center">BARCODE</th>
                                <th class="py-3">DETAIL ITEM</th>
                                <th class="py-3 text-center">STATUS</th>
                                <th class="py-3 text-center">AKSI</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($items)): ?>
                                <?php foreach ($items as $item): ?>
                                    <?php
                                    $barcode   = htmlspecialchars($item['barcode']);
                                    $detail    = htmlspecialchars($item['tipe'] . " " . $item['gender'] . " - Size " . $item['size']);
                                    $st_tx     = htmlspecialchars($item['status_transaksi']);
                                    $st_brg    = htmlspecialchars($item['status_barang']);

                                    $is_inactive_available = (strtolower($st_tx) === 'available' && strtolower($st_brg) === 'inactive');
                                    $status_color = $is_inactive_available ? 'text-danger' : 'text-success';
                                    ?>
                                    <tr class="border-bottom">
                                        <td class="text-center fw-bold text-primary">#<?php echo $barcode; ?></td>
                                        <td>
                                            <div class="fw-bold"><?php echo $detail; ?></div>
                                        </td>
                                        <td class="text-center fw-bold <?php echo $status_color; ?>">
                                            <?php echo $st_tx . ' (' . $st_brg . ')'; ?>
                                        </td>
                                        <td class="text-center">
                                            <?php if ($is_audit_active && $is_inactive_available): ?>
                                                <button type="button" 
                                                        class="btn btn-sm btn-proses-custom fw-bold px-3"
                                                        data-bs-toggle="modal" 
                                                        data-bs-target="#modalAudit"
                                                        onclick="setAuditData('<?php echo $barcode; ?>', '<?php echo addslashes($detail); ?>', '<?php echo $st_tx; ?>', '<?php echo $st_brg; ?>')">
                                                    <i class="bi bi-pencil-square me-1"></i> Ubah Status
                                                </button>
                                            <?php elseif (!$is_audit_active): ?>
                                                <button type="button" 
                                                        class="btn btn-proses fw-bold px-3" 
                                                        disabled 
                                                        title="Akses dikunci: Periode sedang ditutup">
                                                    <i class="bi bi-lock-fill me-1"></i> Terkunci
                                                </button>
                                            <?php else: ?>
                                                <button type="button" 
                                                        class="btn btn-sm btn-light text-muted fw-bold px-3" 
                                                        disabled 
                                                        title="Hanya status Available (Inactive) yang dapat diubah">
                                                    <i class="bi bi-check2-circle me-1"></i> Sesuai
                                                </button>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="4" class="text-center py-4 text-muted">Tidak ada data item ditemukan.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>
</div>

<!-- MODAL UPDATE STATUS -->
<div class="modal fade" id="modalAudit" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form method="POST" action="">
                <input type="hidden" name="action_type" value="update_status">
                <input type="hidden" name="barcode" id="modal_barcode">

                <div class="modal-header">
                    <h5 class="modal-title fw-bold"><i class="bi bi-box-seam me-2"></i>Update Status Item</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                
                <div class="modal-body">
                    <div class="mb-3 bg-light p-3 rounded border">
                        <small class="text-muted d-block mb-1">Target Item:</small>
                        <div class="fw-bold text-primary" id="modal_barcode_display">#102320001</div>
                        <div class="small fw-semibold" id="modal_detail_display">Celana Pria - Size 32</div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold small text-secondary">Status Transaksi</label>
                        <select class="form-select" name="status_transaksi" id="modal_status_tx" required>
                            <option value="Available">Available (Siap Dijual / Direquest)</option>
                            <option value="Sold Out">Sold Out (Sudah Terjual)</option>
                        </select>
                        <div class="form-text small">Pilih <b>Available</b> agar barang bisa dipilih kembali pada transaksi baru.</div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold small text-secondary">Status Barang (Kondisi fisik)</label>
                        <select class="form-select" name="status_barang" id="modal_status_brg" required>
                            <option value="Active">Active (Layak Pakai / Bagus)</option>
                            <option value="Inactive">Inactive (Rusak / Afkir / Perlu Perbaikan)</option>
                        </select>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-light border fw-bold" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary fw-bold" style="background-color: #556ee6;">
                        <i class="bi bi-check-circle me-1"></i> Simpan Perubahan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="assets/js/scripts.js"></script>

</body>
</html>