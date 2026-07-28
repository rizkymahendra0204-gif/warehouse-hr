<?php
// controllers/query_transaksi.php
include 'includes/db.php';

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
        $brand        = $row['brand']; // Langsung mengambil dari kolom 'brand' sesuai ERD baru
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