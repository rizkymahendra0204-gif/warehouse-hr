<?php
// controllers/proses_tambah_item.php
require_once '../includes/db.php'; 

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    die("Koneksi Database Gagal: " . $conn->connect_error);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Menangkap barcode langsung dari input scanner form
    $barcode = trim($_POST['barcode']); 
    $gender  = $_POST['gender']; 
    $tipe    = $_POST['tipe'];   
    $size    = $_POST['size'];   

    // Validasi duplikasi: Pastikan barcode fisik belum terdaftar sebelumnya di database
    $sql_cek = "SELECT barcode FROM master_item WHERE barcode = ?";
    $stmt_cek = $conn->prepare($sql_cek);
    $stmt_cek->bind_param("s", $barcode);
    $stmt_cek->execute();
    $result_cek = $stmt_cek->get_result();

    if ($result_cek->num_rows > 0) {
        echo "<script>alert('Error: Barcode " . $barcode . " sudah terdaftar di sistem!'); window.location.href='../stok_barang.php';</script>";
        $stmt_cek->close();
        exit();
    }
    $stmt_cek->close();

    // Simpan data ke database master_item menggunakan barcode fisik hasil scan
    $sql_insert = "INSERT INTO master_item (barcode, gender, tipe, size) VALUES (?, ?, ?, ?)";
    $stmt_insert = $conn->prepare($sql_insert);
    $stmt_insert->bind_param("ssss", $barcode, $gender, $tipe, $size);
    
    if ($stmt_insert->execute()) {
        header("Location: ../stok_barang.php");
        exit();
    } else {
        echo "Gagal menyimpan data: " . $conn->error;
    }
    $stmt_insert->close();
}

if(isset($conn)){ $conn->close(); }
?>