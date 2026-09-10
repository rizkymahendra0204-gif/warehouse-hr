<?php
require_once __DIR__ . '/../includes/auth_check.php';
require_once __DIR__ . '/../includes/db.php'; // Sesuaikan lokasi koneksi PDO kamu

header('Content-Type: application/json');

$barcode = trim($_GET['barcode'] ?? '');

if (empty($barcode)) {
    echo json_encode(['exists' => false, 'message' => 'Barcode kosong']);
    exit;
}

// Cek apakah barcode sudah tersimpan di tabel master_item
$stmt = $pdo->prepare("SELECT COUNT(*) FROM master_item WHERE barcode = ?");
$stmt->execute([$barcode]);
$count = $stmt->fetchColumn();

if ($count > 0) {
    // Barcode SUDAH TERDAFTAR di database
    echo json_encode([
        'exists'  => true,
        'success' => true,
        'message' => 'Item terdaftar'
    ]);
} else {
    // Barcode BELUM ADA (Baru)
    echo json_encode([
        'exists'  => false,
        'success' => false,
        'message' => 'Barcode baru'
    ]);
}