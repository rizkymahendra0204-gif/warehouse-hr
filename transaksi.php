<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Transaksi - HR Warehouse</title>
    
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

    <!-- MAIN CONTENT -->
    <div class="main-wrapper">
        
        <?php include 'includes/topbar.php'; ?>

        <!-- MAIN CONTENT AREA -->
        <main class="content-area p-4">
            
            <!-- Header Halaman (Disesuaikan dengan halaman lain) -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h4 class="fw-bold m-0" style="color: #1e293b;">Proses Transaksi</h4>
                    <p class="text-secondary m-0 mt-1" style="font-size: 14px;">Pemindaian barcode dan penyelesaian request barang</p>
                </div>
            </div>
            
            <div class="container-fluid px-0">
                
                <?php
                    // Menangkap data otomatis yang dilempar dari pending.php
                    $auto_id_request = isset($_GET['id']) ? $_GET['id'] : '';
                    $auto_perusahaan = isset($_GET['pt']) ? $_GET['pt'] : '';
                    $auto_brand = isset($_GET['brand']) ? $_GET['brand'] : '';
                    $auto_nama = isset($_GET['nama']) ? $_GET['nama'] : '';
                    $auto_alamat = isset($_GET['alamat']) ? $_GET['alamat'] : '';
                ?>
                
                <form action="proses_transaksi.php" method="POST">
                    
                    <!-- SECTION 1: ID Info (Desain Card Baru) -->
                    <div class="bg-white border rounded-3 p-4 mb-4 shadow-sm">
                        <h6 class="fw-bold mb-4" style="color: #4b5563;"><i class="bi bi-ticket-detailed me-2"></i>Informasi Tiket</h6>
                        <div class="row g-4">
                            <div class="col-md-6 col-lg-3">
                                <label class="form-label fw-semibold text-secondary" style="font-size: 13px;">ID Request</label>
                                <input type="text" class="form-control bg-light" name="id_request" value="<?php echo $auto_id_request; ?>" placeholder="Contoh: FR-110726" readonly>
                            </div>
                            <div class="col-md-6 col-lg-3">
                                <label class="form-label fw-semibold text-secondary" style="font-size: 13px;">ID Sales</label>
                                <input type="text" class="form-control" name="id_sales" placeholder="Masukkan ID Sales">
                            </div>
                        </div>
                    </div>

                    <!-- SECTION 2: Detail Data & Barcode Dinamis -->
                    <div class="bg-white border rounded-3 p-4 mb-4 shadow-sm">
                        <h6 class="fw-bold mb-4" style="color: #4b5563;"><i class="bi bi-person-badge me-2"></i>Detail Penerima</h6>
                        
                        <!-- Baris 1: Detail Karyawan/Perusahaan -->
                        <div class="row g-4 mb-4">
                            <div class="col-md-6 col-lg-3">
                                <label class="form-label fw-semibold text-secondary" style="font-size: 13px;">Perusahaan</label>
                                <input type="text" class="form-control bg-light" name="perusahaan" value="<?php echo $auto_perusahaan; ?>" placeholder="Nama Perusahaan" readonly>
                            </div>
                            <div class="col-md-6 col-lg-3">
                                <label class="form-label fw-semibold text-secondary" style="font-size: 13px;">Brand</label>
                                <input type="text" class="form-control bg-light" name="brand" value="<?php echo $auto_brand; ?>" placeholder="Brand" readonly>
                            </div>
                            <div class="col-md-6 col-lg-3">
                                <label class="form-label fw-semibold text-secondary" style="font-size: 13px;">Nama Lengkap</label>
                                <input type="text" class="form-control bg-light" name="nama" value="<?php echo $auto_nama; ?>" placeholder="Nama Lengkap" readonly>
                            </div>
                            <div class="col-md-6 col-lg-3">
                                <label class="form-label fw-semibold text-secondary" style="font-size: 13px;">Alamat</label>
                                <input type="text" class="form-control bg-light" name="alamat" value="<?php echo $auto_alamat; ?>" placeholder="Alamat" readonly>
                            </div>
                        </div>

                        <hr style="border-color: #e5e7eb; margin: 32px 0;">

                        <h6 class="fw-bold mb-4" style="color: #4b5563;"><i class="bi bi-upc-scan me-2"></i>Pemindaian Item</h6>

                        <!-- CONTAINER ITEM DINAMIS -->
                        <div id="dynamic-item-container">
                            
                            <!-- Item Row Default (Akan di-clone) -->
                            <div class="item-row bg-white border rounded-3 p-3 mb-3" style="box-shadow: 0 2px 4px rgba(0,0,0,0.02);">
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <span class="fw-bold item-number" style="color: #556ee6; font-size: 14px;"><i class="bi bi-box-seam me-2"></i>Item #1</span>
                                    <button type="button" class="btn btn-sm text-danger btn-remove-item fw-bold" style="display: none; background-color: #fee2e2;"><i class="bi bi-trash3 me-1"></i>Hapus</button>
                                </div>
                                
                                <div class="row g-4">
                                    <!-- Blok Kiri: Atasan -->
                                    <div class="col-lg-6">
                                        <div class="row g-3">
                                            <div class="col-sm-4">
                                                <label class="form-label fw-semibold text-secondary" style="font-size: 13px;">Atasan</label>
                                                <input type="text" class="form-control" name="atasan[]" placeholder="Item Atasan">
                                            </div>
                                            <div class="col-sm-8">
                                                <label class="form-label fw-semibold text-secondary" style="font-size: 13px;">Barcode Atasan</label>
                                                <div class="input-group">
                                                    <span class="input-group-text bg-light"><i class="bi bi-upc-scan"></i></span>
                                                    <input type="text" class="form-control" name="barcode_atasan[]" placeholder="Scan/Ketik Barcode">
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Blok Kanan: Bawahan -->
                                    <div class="col-lg-6">
                                        <div class="row g-3">
                                            <div class="col-sm-4">
                                                <label class="form-label fw-semibold text-secondary" style="font-size: 13px;">Bawahan</label>
                                                <input type="text" class="form-control" name="bawahan[]" placeholder="Item Bawahan">
                                            </div>
                                            <div class="col-sm-8">
                                                <label class="form-label fw-semibold text-secondary" style="font-size: 13px;">Barcode Bawahan</label>
                                                <div class="input-group">
                                                    <span class="input-group-text bg-light"><i class="bi bi-upc-scan"></i></span>
                                                    <input type="text" class="form-control" name="barcode_bawahan[]" placeholder="Scan/Ketik Barcode">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!-- Akhir Item Row Default -->

                        </div>

                        <!-- Tombol Tambah Item -->
                        <button type="button" id="btn-tambah-item" class="btn fw-bold mt-2 px-3 py-2" style="background-color: #f1f5f9; color: #475569; border: 1px dashed #cbd5e1; border-radius: 6px;">
                            <i class="bi bi-plus-circle me-2"></i>Tambah Baris Item
                        </button>
                    </div>

                    <!-- SECTION 3: Tombol Aksi Bawah -->
                    <div class="d-flex justify-content-end gap-3 mt-2 mb-5">
                        <button type="reset" class="btn btn-light border fw-bold px-4 text-secondary">Batal</button>
                        <button type="submit" class="btn fw-bold text-white px-5" style="background-color: #556ee6;">Transaksi</button>
                    </div>
                </form>
                
            </div>
        </main>

        <!-- END CONTENT AREA -->
        
    </div>
</div>

<!-- Scripts dari Bootstrap dan file JS Anda -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="assets/js/scripts.js"></script>


</body>
</html>