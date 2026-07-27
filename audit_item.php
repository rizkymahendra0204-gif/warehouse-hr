<?php 
// 1. Deklarasikan variabel audit SEBELUM controller di-include
$is_audit_active = true; 

// 2. Include controller backend
include 'controllers/query_audit.php';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Audit & Kelola Status Stok - HR Warehouse</title>
    
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
            <!-- Header & Tombol Action Toggle Audit -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h4 class="fw-bold mb-1">Kelola Status Stok</h4>
                    <p class="text-muted small mb-0">Evaluasi barang retur/non-aktif untuk dikembalikan</p>
                </div>
                
                <!-- Tombol Toggle Mode Audit -->
                <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
                <form method="POST" action="">
                    <input type="hidden" name="action_type" value="toggle_audit">
                    <?php if ($is_audit_active): ?>
                        <input type="hidden" name="audit_status" value="0">
                        <button type="submit" class="btn btn-outline-danger fw-bold shadow-sm" onclick="return confirm('Tutup periode audit?')">
                            <i class="bi bi-lock-fill me-1"></i> Tutup Periode Audit
                        </button>
                    <?php else: ?>
                        <input type="hidden" name="audit_status" value="1">
                        <button type="submit" class="btn btn-success fw-bold shadow-sm" onclick="return confirm('Buka periode audit?')">
                            <i class="bi bi-unlock-fill me-1"></i> Buka Periode Audit
                        </button>
                    <?php endif; ?>
                </form>
            <?php endif; ?>
            </div>

            <!-- Banner Pemberitahuan Status Audit -->
            <?php if (!$is_audit_active): ?>
                <div class="alert alert-warning d-flex align-items-center mb-3" role="alert">
                    <i class="bi bi-lock-fill me-2 fs-5"></i>
                    <div>
                        <strong>Periode Audit Ditutup:</strong> Perubahan status barang dikunci sementara.
                    </div>
                </div>
            <?php else: ?>
                <div class="alert alert-success d-flex align-items-center mb-3" role="alert">
                    <i class="bi bi-check-circle-fill me-2 fs-5"></i>
                    <div>
                        <strong>Mode Audit Aktif:</strong> Silakan lakukan perubahan status untuk barang yang eligible.
                    </div>
                </div>
            <?php endif; ?>

            <!-- Alert Notifikasi Session (Hasil Action) -->
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

            <!-- Tabel Data Stok -->
            <div class="bg-white border rounded-3 p-4 shadow-sm">
                <div class="table-responsive">
                    <table class="table table-hover align-middle m-0" style="font-size: 14px;">
                        <thead class="text-secondary small border-bottom bg-light">
                            <tr>
                                <th class="py-3 text-center">BARCODE</th>
                                <th class="py-3">DETAIL ITEM</th>
                                <th class="py-3 text-center">STATUS</th>
                                <th class="py-3 text-center">AKSI AUDIT</th>
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
                                                <!-- JIKA AUDIT BUKA & BARANG ELIGIBLE -->
                                                <button type="button" 
                                                        class="btn btn-sm btn-outline-primary fw-bold px-3"
                                                        data-bs-toggle="modal" 
                                                        data-bs-target="#modalAudit"
                                                        onclick="setAuditData('<?php echo $barcode; ?>', '<?php echo addslashes($detail); ?>', '<?php echo $st_tx; ?>', '<?php echo $st_brg; ?>')">
                                                    <i class="bi bi-pencil-square me-1"></i> Ubah Status
                                                </button>
                                            <?php elseif (!$is_audit_active): ?>
                                                <!-- JIKA AUDIT DITUTUP -->
                                                <button type="button" 
                                                        class="btn btn-sm btn-secondary fw-bold px-3" 
                                                        disabled 
                                                        title="Akses dikunci: Periode audit sedang ditutup">
                                                    <i class="bi bi-lock-fill me-1"></i> Terkunci
                                                </button>
                                            <?php else: ?>
                                                <!-- JIKA AUDIT BUKA TAPI BARANG TIDAK ELIGIBLE -->
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

<!-- MODAL AUDIT STATUS -->
<div class="modal fade" id="modalAudit" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form method="POST" action="">
                <input type="hidden" name="action_type" value="update_status">
                <input type="hidden" name="barcode" id="modal_barcode">

                <div class="modal-header">
                    <h5 class="modal-title fw-bold"><i class="bi bi-box-seam me-2"></i>Audit Status Item</h5>
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
                        <i class="bi bi-check-circle me-1"></i> Simpan Hasil Audit
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
function setAuditData(barcode, detail, statusTx, statusBrg) {
    document.getElementById('modal_barcode').value = barcode;
    document.getElementById('modal_barcode_display').innerText = '#' + barcode;
    document.getElementById('modal_detail_display').innerText = detail;
    document.getElementById('modal_status_tx').value = statusTx;
    document.getElementById('modal_status_brg').value = statusBrg;
}
</script>
</body>
</html>