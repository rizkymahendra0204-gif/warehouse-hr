<?php
// controllers/validate_barcode.php
header('Content-Type: application/json');
require_once '../includes/db.php';
require_once '../includes/language.php';

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    echo json_encode(['success' => false, 'message' => 'Koneksi database gagal']);
    exit();
}

// Menerima data JSON dari Fetch API JavaScript
$input = json_decode(file_get_contents('php://input'), true);
$barcode = isset($input['barcode']) ? trim($input['barcode']) : '';

if (empty($barcode)) {
    echo json_encode(['success' => false, 'message' => 'Barcode tidak boleh kosong']);
    exit();
}

// Ambil data item berdasarkan barcode
$sql = "SELECT gender, tipe, size FROM master_item WHERE barcode = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $barcode);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $item = $result->fetch_assoc();
    echo json_encode([
        'success' => true,
        'message' => "✓ {$item['tipe']} {$item['gender']} ({$item['size']})",
        'data' => $item
    ]);
} else {
    echo json_encode([
        'success' => false,
        'message' => $lang['err_barcode_not_found'] ?? '❌ Barcode tidak terdaftar di database!'
    ]);
}

$stmt->close();
$conn->close();
?>