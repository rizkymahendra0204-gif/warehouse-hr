<?php
// Panggil koneksi database
require_once 'includes/db.php';
$conn = new mysqli($host, $user, $pass, $db);

// Menangkap ID otomatis dari URL
$auto_id_request = isset($_GET['id']) ? $_GET['id'] : '';
$is_auto = !empty($auto_id_request); 

$readonly_attr = $is_auto ? 'readonly' : '';
$bg_class      = $is_auto ? 'bg-light' : '';

// Inisialisasi variabel kosong
$brand = ''; 
$nama_sa = ''; 
$gender_txt = '';
$qty_top = 0; 
$size_top = '';
$qty_bottoms = 0; 
$size_bottoms = '';
$total_qty = 0;

// Jika ID ada di URL, ambil data lengkapnya dari database
if ($is_auto && !$conn->connect_error) {
    $sql = "SELECT * FROM request_form WHERE request_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $auto_id_request);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($row = $result->fetch_assoc()) {
        $brand        = $row['perusahaan']; // Menggunakan 'perusahaan' sebagai Brand
        $nama_sa      = $row['nama_sa'];
        $gender_txt   = ($row['gender'] === 'male') ? 'SA Pria' : 'SA Wanita';
        $qty_top      = $row['qty_top'];
        $size_top     = $row['size_top'];
        $qty_bottoms  = $row['qty_bottoms'];
        $size_bottoms = $row['size_bottoms'];
        $total_qty    = $qty_top + $qty_bottoms;
    }
    $stmt->close();
}
?>

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
            
            <div class="page-title">Transaksi</div>
            
            <div class="container-fluid px-0">
                
                <form action="controllers/proses_transaksi.php" method="POST">
                    
                    <!-- SECTION 1: Informasi Tiket -->
                    <div class="bg-white border rounded-3 p-4 mb-4 shadow-sm">
                        <h6 class="fw-bold mb-4" style="color: #4b5563;"><i class="bi bi-ticket-detailed me-2"></i>Informasi Tiket</h6>
                        <div class="row g-4">
                            <div class="col-md-3">
                                <label class="form-label fw-semibold text-secondary" style="font-size: 13px;">ID Request</label>
                                <input type="text" class="form-control <?php echo $bg_class; ?>" id="id_request" name="id_request" value="<?php echo htmlspecialchars($auto_id_request); ?>" placeholder="Contoh: FR-110726" <?php echo $readonly_attr; ?>>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-semibold text-secondary" style="font-size: 13px;">Brand</label>
                                <!-- Perbaikan name="brand" dan value dinamis -->
                                <input type="text" class="form-control <?php echo $bg_class; ?>" name="brand" value="<?php echo htmlspecialchars($brand); ?>" placeholder="Brand" <?php echo $readonly_attr; ?>>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-semibold text-secondary" style="font-size: 13px;">Nama SA</label>
                                <!-- Perbaikan name="nama_sa" dan value dinamis -->
                                <input type="text" class="form-control <?php echo $bg_class; ?>" name="nama_sa" value="<?php echo htmlspecialchars($nama_sa); ?>" placeholder="Nama SA" <?php echo $readonly_attr; ?>>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-semibold text-secondary" style="font-size: 13px;">ID Sales</label>
                                <input type="text" class="form-control" name="id_sales" placeholder="Masukkan ID Sales" required>
                            </div>
                        </div>
                    </div>

                    <!-- SECTION 2: Detail Pesanan -->
                    <div class="bg-white border rounded-3 p-4 mb-4 shadow-sm">
                        <h6 class="fw-bold mb-4" style="color: #4b5563;"><i class="bi bi-cart-check me-2"></i>Detail Pesanan</h6>

                        <!-- Area Rincian Item Bergaris -->
                        <div class="border-top pt-2">
                            <table class="table table-borderless m-0" style="font-size: 14px;">
                                <tbody id="rincian-item-list">
                                    <?php if ($is_auto && $total_qty > 0): ?>
                                        
                                        <!-- Cek & Tampilkan Atasan jika ada -->
                                        <?php if ($qty_top > 0): ?>
                                        <tr style="border-bottom: 1px solid #f1f5f9;">
                                            <td class="fw-bold py-3 ps-0 text-secondary" width="15%">Item Atasan</td>
                                            <td class="py-3 text-dark">: Baju <?php echo $gender_txt; ?> (Size <?php echo htmlspecialchars($size_top); ?>) - <?php echo $qty_top; ?> Pcs</td>
                                        </tr>
                                        <?php endif; ?>

                                        <!-- Cek & Tampilkan Bawahan jika ada -->
                                        <?php if ($qty_bottoms > 0): ?>
                                        <tr style="border-bottom: 1px solid #f1f5f9;">
                                            <td class="fw-bold py-3 ps-0 text-secondary" width="15%">Item Bawahan</td>
                                            <td class="py-3 text-dark">: Celana <?php echo $gender_txt; ?> (Size <?php echo htmlspecialchars($size_bottoms); ?>) - <?php echo $qty_bottoms; ?> Pcs</td>
                                        </tr>
                                        <?php endif; ?>

                                        <!-- Total Jumlah -->
                                        <tr>
                                            <td class="fw-bold py-3 ps-0 text-secondary">Total Jumlah</td>
                                            <td class="py-3 text-dark fw-bold">: <?php echo $total_qty; ?> Pcs</td>
                                        </tr>
                                        
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="2" class="text-center text-muted py-4" style="font-size: 13px; font-style: italic;">
                                                <i class="bi bi-info-circle me-1"></i> Rincian item request akan muncul secara otomatis di sini.
                                            </td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- SECTION 3: Pemindaian Item -->
                    <div class="bg-white border rounded-3 p-4 mb-4 shadow-sm">
                        <h6 class="fw-bold mb-4" style="color: #4b5563;"><i class="bi bi-upc-scan me-2"></i>Pemindaian Item</h6>

                        <!-- CONTAINER ROW ITEM DINAMIS -->
                        <div id="dynamic-item-container" class="row g-4 mb-3">
                            
                            <!-- Item Row Default -->
                            <div class="col-md-4 item-row">
                                <div class="bg-white border rounded-3 p-3 h-100" style="box-shadow: 0 1px 3px rgba(0,0,0,0.02);">
                                    
                                    <!-- Header Internal Item -->
                                    <div class="d-flex justify-content-between align-items-center mb-3">
                                        <span class="fw-bold item-number" style="color: #556ee6; font-size: 14px;">
                                            <i class="bi bi-box-seam me-2"></i>Item #1
                                        </span>
                                        <button type="button" class="btn btn-sm text-danger btn-remove-item fw-bold" style="display: none; background-color: #fee2e2; border-radius: 4px; padding: 2px 8px;">
                                            <i class="bi bi-trash3 me-1"></i>Hapus
                                        </button>
                                    </div>
                                    
                                    <!-- Input Barcode Murni Tanpa Tombol Internal -->
                                    <div class="mb-2">
                                        <label class="form-label fw-bold text-secondary mb-2" style="font-size: 13px;">Barcode Item</label>
                                        <div class="input-group">
                                            <span class="input-group-text bg-light text-secondary"><i class="bi bi-upc-scan"></i></span>
                                            <input type="text" class="form-control" name="barcode_item[]" placeholder="Scan Barcode">
                                        </div>
                                        
                                        <!-- Teks Rincian di bawah input -->
                                        <div class="barcode-detail-text text-uppercase fw-bold mt-2 ps-2" style="font-size: 12px; letter-spacing: 0.5px; min-height: 18px;">
                                            
                                        </div>
                                        
                                        <!-- Hidden Input untuk menampung data ke PHP -->
                                        <input type="hidden" name="detail_item[]" class="barcode-detail-hidden">
                                    </div>
                                    
                                </div>
                            </div>                            
                        </div>

                        <!-- Group Tombol Aksi Kiri & Kanan (Setara di Bawah) -->
                        <div class="d-flex gap-2 mt-2">
                            <button type="button" id="btn-tambah-item" class="btn fw-bold px-3 py-2" style="background-color: #f1f5f9; color: #475569; border: 1px dashed #cbd5e1; border-radius: 6px; font-size: 14px;">
                                <i class="bi bi-plus-circle me-2"></i>Tambah Baris Item
                            </button>
                            
                            <!-- Tombol Validate Baru Setara dengan Tambah Item -->
                            <button type="button" id="btn-validate" class="btn text-white fw-bold px-4 py-2" style="background-color: #556ee6; border-radius: 6px; font-size: 14px;">
                                <i class="bi bi-check2-circle me-2"></i>Validate Items
                            </button>
                        </div>
                    </div>

                    <!-- Tombol Aksi Bawah -->
                    <div class="d-flex justify-content-end gap-3 mt-4 mb-5">
                        <button type="reset" class="btn btn-light border fw-bold px-4 text-secondary" style="border-radius: 6px;">Batal</button>
                        <button type="submit" class="btn fw-bold text-white px-5" style="background-color: #556ee6; border-radius: 6px;">Simpan Transaksi</button>
                    </div>
                </form>
                
            </div>
        </main>
        
    </div>
</div>

<!-- Scripts -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="assets/js/scripts.js"></script>

</body>
</html>
<?php 
if(isset($conn)){
    $conn->close();
}
?>