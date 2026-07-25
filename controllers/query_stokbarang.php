<?php
// 1. Panggil koneksi database
include 'includes/db.php';

// Jika $conn sudah di-include dari db.php, baris bawah ini tidak perlu diulang:
if (!isset($conn)) {
    $conn = new mysqli("localhost", "root", "", "db_warehouse");
}

// 2. Tangkap parameter filter dan pencarian dari URL
$tab = isset($_GET['tab']) ? $_GET['tab'] : 'semua';
$search = isset($_GET['search']) ? $_GET['search'] : '';

// 3. Bangun Query SQL secara dinamis
$conditions = [];

// Filter berdasarkan Tab Status (Disesuaikan dengan href HTML)
if ($tab === 'available') {
    $conditions[] = "LOWER(status_transaksi) = 'available' AND LOWER(status_barang) = 'active'";
} elseif ($tab === 'soldout') {
    // Menyesuaikan jika sold out / terdistribusi
    $conditions[] = "(LOWER(status_transaksi) = 'sold out' OR LOWER(status_transaksi) = 'distributed')";
} elseif ($tab === 'inactive') {
    $conditions[] = "LOWER(status_barang) = 'inactive'";
}

// Filter berdasarkan Input Pencarian (Cari SKU/Barcode atau Nama Tipe)
if (!empty($search)) {
    $search_safe = $conn->real_escape_string($search);
    $conditions[] = "(barcode LIKE '%$search_safe%' OR tipe LIKE '%$search_safe%' OR gender LIKE '%$search_safe%')";
}

// Gabungkan semua kondisi ke dalam Query Utama
$sql = "SELECT * FROM master_item";
if (count($conditions) > 0) {
    $sql .= " WHERE " . implode(' AND ', $conditions);
}
$sql .= " ORDER BY barcode DESC";

$result = $conn->query($sql);
?>