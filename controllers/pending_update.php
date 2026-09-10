<?php
require_once __DIR__ . '/../includes/auth_check.php';
// Header anti-cache agar browser selalu membaca data database paling up-to-date
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
header('Content-Type: application/json');

require_once __DIR__ . '/../includes/db.php';

try {
    // Hitung total pending sesuai relasi tabel kamu (LEFT JOIN transaksi)
    $sql = "SELECT COUNT(*) FROM request_form rf 
            LEFT JOIN transaksi t ON rf.request_id = t.request_id 
            WHERE t.request_id IS NULL";
            
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $totalPending = $stmt->fetchColumn();

    echo json_encode([
        'status' => 'success',
        'total_pending' => (int)$totalPending
    ]);
} catch (PDOException $e) {
    error_log('[Warehouse HR] ' . $e);
    echo json_encode([
        'status' => 'error',
        'message' => 'Operasi database gagal. Hubungi administrator.'
    ]);
}