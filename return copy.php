<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Proses Return - HR Warehouse</title>
    
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
            
        <!-- Header Halaman -->
            <div class="d-flex justify-content-between align-items-center mb-0">
                <div class="page-title">Return</div>
            </div>
            
            <div class="container-fluid px-0">
                
                <?php
                    // Simulasi penangkapan data jika diarahkan dari tabel lain
                    $auto_no_return  = isset($_GET['rt']) ? $_GET['rt'] : '';
                    $auto_id_transaksi = isset($_GET['req']) ? $_GET['req'] : '';
                    $auto_perusahaan = isset($_GET['pt']) ? $_GET['pt'] : '';
                    $auto_brand      = isset($_GET['brand']) ? $_GET['brand'] : '';
                    $auto_nama       = isset($_GET['nama']) ? $_GET['nama'] : '';

                    // LOGIKA PINTAR: Mendefinisikan status otomatis/manual berdasarkan input ID Transaksi
                    $is_auto = !empty($auto_id_transaksi); 
                    
                    // Mengatur atribut form berdasarkan asal akses data
                    $readonly_attr = $is_auto ? 'readonly' : '';
                    $bg_class      = $is_auto ? 'bg-light' : '';
                ?>
                
                <form action="proses_return.php" method="POST">
                    
                    <!-- SECTION 1: Informasi Tiket Return -->
                    <div class="bg-white border rounded-3 p-4 mb-4 shadow-sm">
                        <h6 class="fw-bold mb-4" style="color: #4b5563;"><i class="bi bi-arrow-counterclockwise me-2"></i>Informasi Tiket Return</h6>
                        <div class="row g-4">
                            <div class="col-md-6 col-lg-3">
                                <label class="form-label fw-semibold text-secondary" style="font-size: 13px;">ID Transaksi</label>
                                <input type="text" class="form-control <?php echo $bg_class; ?>" name="no_return" value="<?php echo $auto_id_transaksi; ?>" placeholder="Contoh: TRX-110726" <?php echo $readonly_attr; ?>>
                            </div>
                            <div class="col-md-6 col-lg-3">
                                <label class="form-label fw-semibold text-secondary" style="font-size: 13px;">ID Sales</label>
                                <input type="text" class="form-control" name="id_request_awal" placeholder="Masukkan ID Sales">
                            </div>
                            <div class="col-md-6 col-lg-3">
                                <label class="form-label fw-semibold text-secondary" style="font-size: 13px;">Nama SA</label>
                                <input type="text" class="form-control <?php echo $bg_class; ?>" name="nama" value="<?php echo $auto_nama; ?>" placeholder="Nama SA" <?php echo $readonly_attr; ?>>
                            </div>
                            <div class="col-md-6 col-lg-3">
                                <label class="form-label fw-semibold text-secondary" style="font-size: 13px;">Tanggal Return</label>
                                <input type="date" class="form-control bg-light" name="tgl_return" value="<?php echo date('Y-m-d'); ?>" readonly>
                            </div>
                        </div>
                    </div>

                    <!-- SECTION 2: Detail Pesanan (Revisi Total Menyesuaikan Halaman Transaksi) -->
                    <div class="bg-white border rounded-3 p-4 mb-4 shadow-sm">
                        <h6 class="fw-bold mb-4" style="color: #4b5563;"><i class="bi bi-cart-check me-2"></i>Detail Pesanan</h6>

                        <!-- AREA RINCIAN ITEM BERGARIS -->
                        <div class="border-top pt-2">
                            <table class="table table-borderless m-0" style="font-size: 14px;">
                                <tbody id="rincian-item-list">
                                    <?php if ($is_auto): ?>
                                        <!-- Baris ini otomatis muncul & terisi jika diakses dari Pending Request / Ada Parameter ID -->
                                        <tr style="border-bottom: 1px solid #f1f5f9;">
                                            <td class="fw-bold py-3 ps-0 text-secondary" width="15%">Item</td>
                                            <td class="py-3 text-dark">: Baju SA Pria (Size M)</td>
                                        </tr>
                                        <tr style="border-bottom: 1px solid #f1f5f9;">
                                            <td class="fw-bold py-3 ps-0 text-secondary">Item</td>
                                            <td class="py-3 text-dark">: Celana SA Pria (Size 32)</td>
                                        </tr>
                                        <tr>
                                            <td class="fw-bold py-3 ps-0 text-secondary">Jumlah</td>
                                            <td class="py-3 text-dark">: 2 Pcs</td>
                                        </tr>
                                    <?php else: ?>
                                        <!-- Tampilan default saat halaman dibuka secara manual / Kosong -->
                                        <tr>
                                            <td colspan="2" class="text-center text-muted py-4" style="font-size: 13px; font-style: italic;">
                                                <i class="bi bi-info-circle me-1"></i> Rincian item request akan muncul secara otomatis.
                                            </td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- SECTION 3: Pemindaian Item Return Dinamis -->
                    <div class="bg-white border rounded-3 p-4 mb-4 shadow-sm">
                        <h6 class="fw-bold mb-4" style="color: #b91c1c;"><i class="bi bi-upc-scan me-2"></i>Pemindaian Item Return</h6>

                        <!-- CONTAINER ITEM DINAMIS -->
                        <div id="dynamic-item-container">
                            
                            <!-- Item Row Default -->
                            <div class="item-row bg-white border rounded-3 p-3 mb-3" style="box-shadow: 0 2px 4px rgba(0,0,0,0.02);">
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <span class="fw-bold item-number" style="color: #b91c1c; font-size: 14px;"><i class="bi bi-box-seam me-2"></i>Barang #1</span>
                                    <button type="button" class="btn btn-sm text-danger btn-remove-item fw-bold" style="display: none; background-color: #fee2e2; border-radius: 4px; padding: 2px 8px;"><i class="bi bi-trash3 me-1"></i>Hapus</button>
                                </div>
                                
                                <div class="row g-4">
                                    <div class="col-md-4">
                                        <label class="form-label fw-semibold text-secondary" style="font-size: 13px;">Barcode Fisik</label>
                                        <div class="input-group">
                                            <span class="input-group-text bg-light text-danger"><i class="bi bi-upc-scan"></i></span>
                                            <input type="text" class="form-control" name="barcode_return[]" placeholder="Scan/Ketik Barcode">
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label fw-semibold text-secondary" style="font-size: 13px;">Kondisi Barang</label>
                                        <select class="form-select" name="kondisi_return[]">
                                            <option value="" selected disabled>Pilih Kondisi...</option>
                                            <option value="Kekecilan">Tukar: Kekecilan</option>
                                            <option value="Kebesaran">Tukar: Kebesaran</option>
                                            <option value="Cacat Produksi">Rusak: Cacat Produksi</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            
                        </div>

                        <!-- Tombol Tambah Item -->
                        <button type="button" id="btn-tambah-item" class="btn fw-bold mt-2 px-3 py-2" style="background-color: #f1f5f9; color: #475569; border: 1px dashed #cbd5e1; border-radius: 6px;">
                            <i class="bi bi-plus-circle me-2"></i>Tambah Barang Return
                        </button>
                    </div>

                    <!-- SECTION 4: Tombol Aksi Bawah -->
                    <div class="d-flex justify-content-end gap-3 mt-4 mb-5">
                        <button type="reset" class="btn btn-light border fw-bold px-4 text-secondary" style="border-radius: 6px;">Batal</button>
                        <button type="submit" class="btn fw-bold text-white px-5" style="background-color: #b91c1c; border-radius: 6px;">Proses Return</button>
                    </div>
                </form>
                
            </div>
        </main>
        
    </div>
</div>

<!-- Scripts dari Bootstrap dan JS -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="assets/js/scripts.js"></script>

</body>
</html>