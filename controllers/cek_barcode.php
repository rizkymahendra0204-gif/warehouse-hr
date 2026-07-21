<?php
// controllers/cek_barcode.php
header('Content-Type: application/json');

// Memanggil file koneksi dengan path absolut relatif terhadap folder controllers
require_once __DIR__ . '/../includes/db.php';

// Pastikan variabel $conn dari db.php berhasil dimuat
if (!isset($conn) || !$conn) {
    echo json_encode([
        'success' => false, 
        'message' => 'Koneksi database gagal terhubung.'
    ]);
    exit();
}

if (isset($_GET['barcode'])) {
    $barcode = mysqli_real_escape_string($conn, trim($_GET['barcode']));
    
    // Cek keberadaan barcode di master_item
    $query = mysqli_query($conn, "SELECT barcode FROM master_item WHERE barcode = '$barcode'");
    
    if ($query && mysqli_num_rows($query) > 0) {
        $response['success'] = true; // Barang LAMA / Sudah terdaftar
    } else {
        $response['success'] = false; // Barang BARU / Belum terdaftar
    }
}

echo json_encode($response);
exit();
?>