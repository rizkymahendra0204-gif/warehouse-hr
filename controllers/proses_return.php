<?php
// controllers/proses_return.php
session_start();
require_once __DIR__ . '/../includes/db.php';

if (!isset($conn) || $conn->connect_error) {
    die("Koneksi Database Gagal: " . ($conn->connect_error ?? 'Error Koneksi'));
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $transaction_id = isset($_POST['no_return']) ? trim($_POST['no_return']) : '';
    $barcodes       = $_POST['barcode_return'] ?? [];
    $kondisi        = $_POST['kondisi_return'] ?? [];

    if (empty($transaction_id) || empty($barcodes)) {
        echo "<script>alert('Error: ID Transaksi dan Barang Return tidak boleh kosong!'); window.history.back();</script>";
        exit();
    }

    // MULTI-ROW TRANSACTION COMMIT / ROLLBACK
    $conn->begin_transaction();

    try {
        $stmt_insert_return = $conn->prepare("INSERT INTO return_items (transaction_id, barcode, alasan_return, kondisi_barang, tgl_return) VALUES (?, ?, ?, ?, NOW())");
        $stmt_update_master = $conn->prepare("UPDATE master_item SET status_transaksi = 'Returned', status_barang = ? WHERE barcode = ?");

        foreach ($barcodes as $index => $barcode) {
            $barcode_clean = trim($barcode);
            $alasan        = isset($kondisi[$index]) ? trim($kondisi[$index]) : 'Layak';
            
            // Penentuan kondisi fisik
            $kondisi_fisik = (strpos(strtolower($alasan), 'cacat') !== false || strpos(strtolower($alasan), 'rusak') !== false) ? 'Rusak' : 'Bagus';

            // 1. Insert ke return_items
            $stmt_insert_return->bind_param("ssss", $transaction_id, $barcode_clean, $alasan, $kondisi_fisik);
            $stmt_insert_return->execute();

            // 2. Update status master_item menjadi 'Returned'
            $stmt_update_master->bind_param("ss", $kondisi_fisik, $barcode_clean);
            $stmt_update_master->execute();
        }

        $stmt_insert_return->close();
        $stmt_update_master->close();

        // Commit seluruh perubahan
        $conn->commit();

        echo "<script>alert('Proses Return Berhasil Disimpan!'); window.location.href='../return.php';</script>";
        exit();

    } catch (Exception $e) {
        $conn->rollback();
        echo "<script>alert('PROSES RETURN GAGAL: " . addslashes($e->getMessage()) . "'); window.history.back();</script>";
        exit();
    }
}

if (isset($conn)) {
    $conn->close();
}
?>