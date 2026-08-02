<?php
// 1. Panggil koneksi database PDO
require_once __DIR__ . '/../includes/db.php';

// Pastikan variabel $pdo tersedia dari db.php
if (!isset($pdo)) {
    die("Koneksi PDO tidak ditemukan.");
}

// 2. Tangkap parameter filter dan pencarian dari URL
$tab    = $_GET['tab'] ?? 'semua';
$search = trim($_GET['search'] ?? '');

// 3. Siapkan penampung kondisi WHERE dan Parameter Binding
$conditions = [];
$params     = [];

// Filter berdasarkan Tab Status
if ($tab === 'available') {
    $conditions[] = "LOWER(status_transaksi) = 'available' AND LOWER(status_barang) = 'active'";
} elseif ($tab === 'soldout') {
    $conditions[] = "(LOWER(status_transaksi) = 'sold out' OR LOWER(status_transaksi) = 'distributed')";
} elseif ($tab === 'inactive') {
    $conditions[] = "LOWER(status_barang) = 'inactive'";
}

// Filter berdasarkan Input Pencarian (Safe dengan Prepared Statement)
if (!empty($search)) {
    $conditions[] = "(barcode LIKE :search OR tipe LIKE :search OR gender LIKE :search)";
    $params[':search'] = '%' . $search . '%';
}

// Gabungkan semua kondisi ke dalam Query Utama
$sql = "SELECT * FROM master_item";
if (count($conditions) > 0) {
    $sql .= " WHERE " . implode(' AND ', $conditions);
}
$sql .= " ORDER BY barcode DESC";

// 4. Eksekusi Query menggunakan PDO
try {
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $list_item = $stmt->fetchAll(PDO::FETCH_ASSOC); // Mengembalikan array assosiatif
} catch (PDOException $e) {
    die("Error Query Database: " . $e->getMessage());
}
?>