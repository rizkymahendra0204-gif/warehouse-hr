<?php
// proses_transaksi.php
session_start();
require_once '../includes/db.php';

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    die("Koneksi Database Gagal: " . $conn->connect_error);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 1. Tangkap data dari form transaksi
    $request_id = isset($_POST['id_request']) ? trim($_POST['id_request']) : '';
    $id_sales   = isset($_POST['id_sales']) ? trim($_POST['id_sales']) : '';
    $barcodes   = isset($_POST['barcode_item']) ? $_POST['barcode_item'] : [];

    // Validasi input wajib
    if (empty($id_sales)) {
        echo "<script>alert('Error: ID Sales wajib diisi!'); window.history.back();</script>";
        exit();
    }

    // Filter barcode yang tidak kosong
    $valid_barcodes = array_filter($barcodes, function($value) {
        return !empty(trim($value));
    });

    if (empty($valid_barcodes)) {
        echo "<script>alert('Error: Belum ada barcode item yang di-scan!'); window.history.back();</script>";
        exit();
    }

    // ==========================================
    // PROSES DATABASE DENGAN TRANSACTION SYSTEM
    // ==========================================
    $conn->begin_transaction();

    try {
        // Siapkan kueri untuk validasi master_item, insert transaksi, dan update status
        $sql_check_item  = "SELECT status_transaksi FROM master_item WHERE barcode = ?";
        // Kueri di bawah menggunakan nama tabel 'transaksi', silakan ubah jika nama tabel Anda berbeda
        $sql_insert_tx   = "INSERT INTO transaksi (request_id, barcode, id_sales) VALUES (?, ?, ?)";
        $sql_update_stok = "UPDATE master_item SET status_transaksi = 'Sold Out' WHERE barcode = ?";

        $stmt_check  = $conn->prepare($sql_check_item);
        $stmt_insert = $conn->prepare($sql_insert_tx);
        $stmt_update = $conn->prepare($sql_update_stok);

        foreach ($valid_barcodes as $barcode) {
            $barcode_clean = trim($barcode);

            // 1. Validasi status barang di master_item
            $stmt_check->bind_param("s", $barcode_clean);
            $stmt_check->execute();
            $res_check = $stmt_check->get_result();

            if ($res_check->num_rows === 0) {
                throw new Exception("Barcode " . $barcode_clean . " tidak terdaftar di database gudang!");
            }

            $item_data = $res_check->fetch_assoc();
            if ($item_data['status'] === 'Sold Out') {
                throw new Exception("Barcode " . $barcode_clean . " sudah berstatus SOLD OUT (Double Scan)!");
            }
            if ($item_data['status'] === 'Nonaktif') {
                throw new Exception("Barcode " . $barcode_clean . " berstatus NONAKTIF!");
            }

            // 2. Insert ke tabel transaksi sesuai struktur kolom Anda
            // transaction_id (Auto Increment) dan tgl_transaksi (current_timestamp) terisi otomatis
            $stmt_insert->bind_param("sss", $request_id, $barcode_clean, $id_sales);
            $stmt_insert->execute();

            // 3. Update status item di master_item menjadi SOLD OUT
            $stmt_update->bind_param("s", $barcode_clean);
            $stmt_update->execute();
        }

        $stmt_check->close();
        $stmt_insert->close();
        $stmt_update->close();

        // Jika semua loop berhasil tanpa error, commit data ke database
        $conn->commit();
        
        echo "<script>alert('Transaksi Berhasil Disimpan!'); window.location.href='stok_barang.php';</script>";
        exit();

    } catch (Exception $e) {
        // Jika ada satu saja yang gagal, batalkan semua data yang sempat masuk di commit ini
        $conn->rollback();
        echo "<script>alert('TRANSAKSI GAGAL: " . $e->getMessage() . "'); window.history.back();</script>";
        exit();
    }
}

if (isset($conn)) {
    $conn->close();
}
?>