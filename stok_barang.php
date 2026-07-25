<?php
include 'controllers/query_stokbarang.php';
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
                <!-- Tab Filter (Mempertahankan text pencarian saat tab diklik) -->
                <div class="d-flex gap-2 bg-white p-1 rounded border shadow-sm">
                    <a href="?tab=semua&search=<?= urlencode($search) ?>" class="filter-tab <?= $tab === 'semua' ? 'active' : '' ?>">Semua</a>
                    <a href="?tab=available&search=<?= urlencode($search) ?>" class="filter-tab <?= $tab === 'available' ? 'active' : '' ?>">Available</a>
                    <a href="?tab=soldout&search=<?= urlencode($search) ?>" class="filter-tab <?= $tab === 'sold out' ? 'active' : '' ?>">Sold Out</a>
                    <a href="?tab=inactive&search=<?= urlencode($search) ?>" class="filter-tab <?= $tab === 'inactive' ? 'active' : '' ?>">Inactive</a>
                </div>
                
                <!-- Form Pencarian (Otomatis submit saat menekan tombol Enter) -->
                <form method="GET" action="" class="input-group shadow-sm" style="width: 300px; border-radius: 8px; overflow: hidden;">
                    <!-- Menyimpan state tab aktif saat user mengetik pencarian baru -->
                    <input type="hidden" name="tab" value="<?= htmlspecialchars($tab) ?>">
                    
                    <span class="input-group-text bg-white border-end-0"><i class="bi bi-search text-secondary"></i></span>
                    <input type="text" name="search" id="searchInput" class="form-control border-start-0 ps-0" 
                        placeholder="Cari ..." 
                        value="<?= htmlspecialchars($search) ?>">
                </form>
            </div>
            
            <!-- Table Container (Card) -->
            <div class="table-card">
                <div class="table-responsive">
                    <table class="table align-middle mb-0">
                        <thead>
                            <tr>
                                <th scope="col" width="5%">No</th>
                                <th scope="col" width="15%">Barcode</th>
                                <th scope="col" width="35%">Detail Item</th>
                                <th scope="col" width="15%">Kategori</th>
                                <th scope="col" width="15%" class="text-center">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            
                        <?php if ($result && $result->num_rows > 0):
                            $no = 1; 
                        ?>
                        <?php while ($row = $result->fetch_assoc()): 
                            // 1. Ambil data status dengan fallback string kosong jika null
                            $status_tx  = $row['status_barang'] ?? ''; 
                            $status_brg = $row['status_transaksi'] ?? '';
                            
                            // 2. Normalisasi string (hapus spasi di awal/akhir & ubah ke huruf kecil)
                            $check_tx  = strtolower(trim($status_tx));
                            $check_brg = strtolower(trim($status_brg));
                            
                            // 3. Logika penentuan status badge & ikon
                            if ($check_tx === 'available' && $check_brg === 'active') {
                            // BARANG FRESH / READY
                            $text_color = '#16a34a'; // Hijau
                            $icon_class = 'bi-check-circle-fill';

                            } elseif ($check_brg === 'available' && $check_tx === 'inactive') {
                            // BARANG HASIL RETURN
                            $text_color = '#dc2626'; // Merah
                            $icon_class = 'bi-arrow-counterclockwise'; // Ikon Return / Refund

                            } else {
                            // SOLD OUT / LAINNYA
                            $text_color = '#16a34a'; // Hijau
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
                                    <!-- Detail item -->
                                    <div class="fw-bold text-dark" style="font-size: 15px;">
                                        <?= htmlspecialchars($row['tipe'] ?? '') ?> SA <?= htmlspecialchars($row['gender'] ?? '') ?>
                                    </div>
                                    <div class="text-secondary mt-1" style="font-size: 13px;">
                                        Ukuran: <?= htmlspecialchars($row['size'] ?? '') ?>
                                    </div>
                                </td>
                                <td>
                                    <span class="text-secondary fw-bold" style="font-size: 13px;">
                                        <?= htmlspecialchars($row['tipe'] ?? '') ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="badge-status" style="color: <?= $text_color ?>; font-weight: 600;">
                                        <i class="bi <?= $icon_class ?>" style="font-size: 1.0rem;"></i> <?= htmlspecialchars($status_brg) ?> (<?= htmlspecialchars($status_tx) ?>)
                                    </span>
                                </td>
                            </tr>
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

<!-- MODAL WINDOW FORM TAMBAH BARANG (FIXED-WIDTH DIGIT PARSE MODE) -->
<div class="modal fade" id="modalTambahBarang" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="border-radius: 12px;">
            <div class="modal-header border-0 pt-4 px-4">
                <h5 class="modal-title fw-bold text-dark"><i class="bi bi-upc-scan me-2" style="color: #556ee6;"></i>Tambah / Registrasi Stok</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            
            <form action="controllers/proses_tambah_item.php" method="POST" id="formTambahStok">
                <div class="modal-body px-4 pb-4">
                    <div class="row g-3">
                        
                        <!-- Input Barcode Utama -->
                        <div class="col-md-12">
                            <label class="form-label fw-semibold small text-secondary">Barcode Item (9 Digit Angka)</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light text-secondary"><i class="bi bi-upc-scan"></i></span>
                                <input type="text" class="form-control form-control-lg fw-bold" name="barcode" id="scanBarcodeInput" placeholder="Tembak barcode 9-digit..." maxlength="9" autofocus required autocomplete="off">
                            </div>
                        </div>

                        <!-- Indikator Hasil Analisis Karakter & Status Database -->
                        <div class="col-md-12">
                            <div id="parsingAlertBox" class="p-3 border rounded bg-light text-center small text-secondary" style="border-style: dashed !important; transition: all 0.2s ease;">
                                <i class="bi bi-arrow-left-right d-block mb-1 text-muted fs-5"></i>
                                <span>Silakan scan barcode untuk ekstraksi digit otomatis.</span>
                            </div>
                        </div>

                        <!-- Preview Form Dropdown -->
                        <div class="col-md-12">
                            <div class="row g-2">
                                <div class="col-md-12">
                                    <label class="form-label fw-semibold small text-secondary">Gender</label>
                                    <select class="form-select fw-bold text-dark" name="gender" id="inputGender" required>
                                        <option value="">-- Terdeteksi Otomatis --</option>
                                        <option value="Pria">Pria</option>
                                        <option value="Wanita">Wanita</option>
                                    </select>
                                </div>
                                <div class="col-md-12">
                                    <label class="form-label fw-semibold small text-secondary">Tipe (Kategori)</label>
                                    <select class="form-select fw-bold text-dark" name="tipe" id="inputTipe" required>
                                        <option value="">-- Terdeteksi Otomatis --</option>
                                        <option value="Baju">Baju</option>
                                        <option value="Celana">Celana</option>
                                    </select>
                                </div>
                                <div class="col-md-12">
                                    <label class="form-label fw-semibold small text-secondary">Size (Ukuran)</label>
                                    <select class="form-select fw-bold text-dark" name="size" id="inputSize" required>
                                        <option value="">-- Terdeteksi Otomatis --</option>
                                        <option value="S">S</option>
                                        <option value="M">M</option>
                                        <option value="L">L</option>
                                        <option value="XL">XL</option>
                                        <option value="28">28</option>
                                        <option value="30">30</option>
                                        <option value="32">32</option>
                                        <option value="36">36</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
                <div class="modal-footer border-0 px-4 pb-4 pt-0">
                    <button type="button" class="btn btn-light border text-secondary fw-semibold" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" id="btnSimpanStok" class="btn text-white fw-bold px-4" style="background-color: #556ee6;" disabled>
                        <i class="bi bi-check-lg me-1"></i> Simpan Barang
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
<?php 
if(isset($conn)){ $conn->close(); } 
?>