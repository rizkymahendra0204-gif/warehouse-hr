<?php
// 1. Panggil koneksi database
require_once 'includes/db.php';
$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    die("Koneksi Database Gagal: " . $conn->connect_error);
}

// 2. Ambil data dari tabel master_item
$sql = "SELECT * FROM master_item ORDER BY barcode DESC";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Stok Barang - HR Warehouse</title>
    
    <!-- Bootstrap & Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    
    <!-- Memanggil file CSS Anda -->
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>

<div class="app-container">

    <!-- Memanggil file Sidebar -->
    <?php include 'includes/sidebar.php'; ?>

    <div class="main-wrapper">
        
        <!-- Memanggil file Topbar -->
        <?php include 'includes/topbar.php'; ?>

        <!-- MAIN CONTENT AREA -->
        <main class="content-area p-4">
            
            <!-- Header Halaman & Tombol Aksi -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div class="page-title">Stok Barang
                    <p class="text-secondary m-0 mt-1" style="font-size: 14px;">Manajemen inventaris seragam dan kelengkapan</p>
                </div>
                <div class="d-flex gap-3">
                    <!-- Memicu Jendela Modal Tambah Barang -->
                    <button class="btn text-white fw-bold" data-bs-toggle="modal" data-bs-target="#modalTambahBarang" style="background-color: #556ee6; border-radius: 8px;">
                        <i class="bi bi-plus-lg me-2"></i>Tambah Barang
                    </button>
                </div>
            </div>

            <!-- Area Filter dan Pencarian -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div class="d-flex gap-2 bg-white p-1 rounded border shadow-sm">
                    <a href="#" class="filter-tab active">Semua</a>
                    <a href="#" class="filter-tab">Tersedia</a>
                    <a href="#" class="filter-tab">Terdistribusi</a>
                    <a href="#" class="filter-tab">Nonaktif</a>
                </div>
                
                <div class="input-group shadow-sm" style="width: 300px; border-radius: 8px; overflow: hidden;">
                    <span class="input-group-text bg-white border-end-0"><i class="bi bi-search text-secondary"></i></span>
                    <input type="text" id="searchInput" class="form-control border-start-0 ps-0" placeholder="Cari SKU atau Nama Barang...">
                </div>
            </div>
            
            <!-- Table Container (Card) -->
            <div class="table-card">
                <div class="table-responsive">
                    <table class="table align-middle mb-0">
                        <thead>
                            <tr>
                                <th scope="col" width="15%">Barcode</th>
                                <th scope="col" width="35%">Detail Item</th>
                                <th scope="col" width="15%">Kategori</th>
                                <th scope="col" width="20%">Status</th>
                                <th scope="col" width="15%" class="text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            

                        <?php if ($result && $result->num_rows > 0): ?>
                            <?php while ($row = $result->fetch_assoc()): 
                                // Cek status untuk menentukan warna badge
                                $status_tx   = $row['status_transaksi']; 
                                $status_brg  = $row['status_barang'];
                                
                                $badge_class = 'status-tersedia';
                                $icon_class   = 'bi-check-circle-fill';
                                
                                if (strtolower($status_tx) !== 'available' || strtolower($status_brg) !== 'active') {
                                    $badge_class = 'status-nonaktif';
                                    $icon_class  = 'bi-x-circle-fill';
                                }
                            ?>
                                <tr>
                                    <td>
                                        <span class="barcode-badge"><i class="bi bi-upc-scan"></i> <?= htmlspecialchars($row['barcode']) ?></span>
                                    </td>
                                    <td>
                                        <!-- Menggabungkan tipe, gender, dan size sebagai nama detail item -->
                                        <div class="fw-bold text-dark" style="font-size: 15px;">
                                            <?= htmlspecialchars($row['tipe']) ?> SA <?= htmlspecialchars($row['gender']) ?>
                                        </div>
                                        <div class="text-secondary mt-1" style="font-size: 13px;">
                                            Ukuran: <?= htmlspecialchars($row['size']) ?>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="text-secondary fw-bold" style="font-size: 13px;"><?= htmlspecialchars($row['tipe']) ?></span>
                                    </td>
                                    <td>
                                        <span class="badge-status <?= $badge_class ?>">
                                            <i class="bi <?= $icon_class ?>"></i> <?= htmlspecialchars($status_tx) ?> (<?= htmlspecialchars($status_brg) ?>)
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <div class="d-flex justify-content-center gap-1">
                                            <a href="#" class="btn-action-icon btn-detail" title="Detail Barang"><i class="bi bi-eye fs-5"></i></a>
                                            <a href="#" class="btn-action-icon btn-edit" title="Edit Barang"><i class="bi bi-pencil-square fs-5"></i></a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="5" class="text-center text-muted py-5">
                                    <i class="bi bi-box fs-1 d-block mb-2"></i> Belum ada data barang di database.
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

<!-- MODAL WINDOW FORM TAMBAH BARANG (MODE SCAN FISIK) -->
<div class="modal fade" id="modalTambahBarang" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-0 pt-4 px-4">
                <h5 class="modal-title fw-bold text-dark"><i class="bi bi-upc-scan me-2" style="color: #556ee6;"></i>Scan Barang Baru</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="controllers/proses_tambah_item.php" method="POST">
                <div class="modal-body px-4 pb-4">
                    <div class="row g-3">
                        <!-- Kolom Scan Barcode Baru -->
                        <div class="col-md-12">
                            <label class="form-label fw-semibold small text-secondary">Barcode Item (Scan di Sini)</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light text-secondary"><i class="bi bi-upc-scan"></i></span>
                                <input type="text" class="form-control" name="barcode" placeholder="Klik di sini lalu tembak barcode barang..." autofocus required>
                            </div>
                        </div>
                        <div class="col-md-12">
                            <label class="form-label fw-semibold small text-secondary">Gender</label>
                            <select class="form-select" name="gender" required>
                                <option value="Pria">Pria</option>
                                <option value="Wanita">Wanita</option>
                            </select>
                        </div>
                        <div class="col-md-12">
                            <label class="form-label fw-semibold small text-secondary">Tipe (Kategori)</label>
                            <select class="form-select" name="tipe" required>
                                <option value="Baju">Baju</option>
                                <option value="Celana">Celana</option>
                            </select>
                        </div>
                        <div class="col-md-12">
                            <label class="form-label fw-semibold small text-secondary">Size (Ukuran)</label>
                            <select class="form-select" name="size" required>
                                <option value="S">S</option>
                                <option value="M">M</option>
                                <option value="L">L</option>
                                <option value="XL">XL</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0 px-4 pb-4 pt-0">
                    <button type="button" class="btn btn-light border text-secondary fw-semibold" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn text-white fw-bold px-4" style="background-color: #556ee6;">Simpan Barang</button>
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
<?php 
if(isset($conn)){ $conn->close(); } 
?>