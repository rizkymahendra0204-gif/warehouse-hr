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
                    <button class="btn text-white fw-bold" style="border-radius: 8px;">
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
                    <input type="text" class="form-control border-start-0 ps-0" placeholder="Cari SKU atau Nama Barang...">
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
                            
                            <!-- Baris Data 1 (Tersedia) -->
                            <tr>
                                <td>
                                    <span class="barcode-badge"><i class="bi bi-upc-scan"></i> SRG-AT-001</span>
                                </td>
                                <td>
                                    <div class="fw-bold text-dark" style="font-size: 15px;">Kemeja SA Pria - Size L</div>
                                    <div class="text-secondary mt-1" style="font-size: 13px;">
                                        Brand: CENTRAL | Warna: Putih
                                    </div>
                                </td>
                                <td>
                                    <span class="text-secondary fw-bold" style="font-size: 13px;">Atasan</span>
                                </td>
                                <td>
                                    <span class="badge-status status-tersedia">
                                        <i class="bi bi-check-circle-fill"></i> Tersedia
                                    </span>
                                </td>
                                <td class="text-center">
                                    <div class="d-flex justify-content-center gap-1">
                                        <!-- Ikon Detail (Mata) -->
                                        <a href="#" class="btn-action-icon btn-detail" title="Detail Barang">
                                            <i class="bi bi-eye fs-5"></i>
                                        </a>
                                        
                                        <!-- Ikon Edit (Pensil) -->
                                        <a href="#" class="btn-action-icon btn-edit" title="Edit Barang">
                                            <i class="bi bi-pencil-square fs-5"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>

                            <!-- Baris Data 2 (Terdistribusi) -->
                            <tr>
                                <td>
                                    <span class="barcode-badge"><i class="bi bi-upc-scan"></i> SRG-BW-042</span>
                                </td>
                                <td>
                                    <div class="fw-bold text-dark" style="font-size: 15px;">Celana SA Pria - Size 32</div>
                                    <div class="text-secondary mt-1" style="font-size: 13px;">
                                        Brand: CENTRAL | Dipinjam: Budi Santoso
                                    </div>
                                </td>
                                <td>
                                    <span class="text-secondary fw-bold" style="font-size: 13px;">Bawahan</span>
                                </td>
                                <td>
                                    <span class="badge-status status-sold">
                                        <i class="bi bi-arrow-left-right"></i> Sold Out
                                    </span>
                                </td>
                                <td class="text-center">
                                    <div class="d-flex justify-content-center gap-1">
                                        <!-- Ikon Detail (Mata) -->
                                        <a href="#" class="btn-action-icon btn-detail" title="Detail Barang">
                                            <i class="bi bi-eye fs-5"></i>
                                        </a>
                                        
                                        <!-- Ikon Edit (Pensil) -->
                                        <a href="#" class="btn-action-icon btn-edit" title="Edit Barang">
                                            <i class="bi bi-pencil-square fs-5"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>

                            <!-- Baris Data 3 (Nonaktif) -->
                            <tr>
                                <td>
                                    <span class="barcode-badge"><i class="bi bi-upc-scan"></i> SRG-AT-088</span>
                                </td>
                                <td>
                                    <div class="fw-bold text-dark" style="font-size: 15px;">Kemeja SA Wanita - Size M</div>
                                    <div class="text-secondary mt-1" style="font-size: 13px;">
                                        Kondisi: Ukuran tidak pas | Brand: CENTRAL
                                    </div>
                                </td>
                                <td>
                                    <span class="text-secondary fw-bold" style="font-size: 13px;">Atasan</span>
                                </td>
                                <td>
                                    <span class="badge-status status-nonaktif">
                                        <i class="bi bi-x-circle-fill"></i> Nonaktif
                                    </span>
                                </td>
                                <td class="text-center">
                                    <div class="d-flex justify-content-center gap-1">
                                        <!-- Ikon Detail (Mata) -->
                                        <a href="#" class="btn-action-icon btn-detail" title="Detail Barang">
                                            <i class="bi bi-eye fs-5"></i>
                                        </a>
                                        
                                        <!-- Ikon Edit (Pensil) -->
                                        <a href="#" class="btn-action-icon btn-edit" title="Edit Barang">
                                            <i class="bi bi-pencil-square fs-5"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>

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