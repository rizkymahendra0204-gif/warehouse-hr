<?php
// controllers/proses_return.php
session_start();
require_once __DIR__ . '/../includes/db.php';

// Cek apakah koneksi $pdo dari db.php berhasil dipanggil
if (!isset($pdo)) {
    die("Koneksi Database Gagal: Variabel \$pdo tidak ditemukan di db.php");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $transaction_id = isset($_POST['no_return']) ? trim($_POST['no_return']) : '';
    $barcodes       = $_POST['barcode_return'] ?? [];
    $kondisi        = $_POST['kondisi_return'] ?? [];

    if (empty($transaction_id) || empty($barcodes)) {
        echo "<script>alert('Error: ID Transaksi dan Barang Return tidak boleh kosong!'); window.history.back();</script>";
        exit();
    }

    try {
        // Mulai Transaksi Multi-Row (PDO)
        $pdo->beginTransaction();

        $stmt_insert_return = $pdo->prepare("INSERT INTO return_items (transaction_id, barcode, alasan_return, kondisi_barang, tgl_return) VALUES (?, ?, ?, ?, NOW())");
        $stmt_update_master = $pdo->prepare("UPDATE master_item SET status_transaksi = 'Available', status_barang = 'Inactive' WHERE barcode = ?");

        foreach ($barcodes as $index => $barcode) {
            $barcode_clean = trim($barcode);
            $alasan        = isset($kondisi[$index]) ? trim($kondisi[$index]) : 'Layak';
            
            // Penentuan kondisi fisik
            $kondisi_fisik = (strpos(strtolower($alasan), 'cacat') !== false || strpos(strtolower($alasan), 'available') !== false || strpos(strtolower($alasan), 'tukar') !== false) ? 'Rusak' : 'Bagus';

            // 1. Insert ke return_items
            $stmt_insert_return->execute([$transaction_id, $barcode_clean, $alasan, $kondisi_fisik]);

            // 2. Update status master_item menjadi 'Inactive'
            $stmt_update_master->execute([$barcode_clean]);
        }

        // Commit seluruh perubahan ke database
        $pdo->commit();

        echo "<script>alert('Proses Return Berhasil Disimpan!'); window.location.href='../return.php';</script>";
        exit();

    } catch (Exception $e) {
        // Rollback jika ada error saat eksekusi
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        echo "<script>alert('PROSES RETURN GAGAL: " . addslashes($e->getMessage()) . "'); window.history.back();</script>";
        exit();
    }
}
?>