<?php
// processes_transaksi.php
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
    $valid_barcodes = array_values(array_filter($barcodes, function($value) {
        return !empty(trim($value));
    }));

    if (empty($valid_barcodes)) {
        echo "<script>alert('Error: Belum ada barcode item yang di-scan!'); window.history.back();</script>";
        exit();
    }

    // ====================================================================
    // GENERATE 1 TRANSACTION ID UNIK UNTUK SATU BATCH REQUEST/TRANSAKSI
    // Contoh Format: TRX-260722-0001 (atau angka urut)
    // ====================================================================
    $prefix = "TRX-" . date('ymd') . "-";
    $sql_max = "SELECT MAX(transaction_id) AS max_id FROM transaksi WHERE transaction_id LIKE '$prefix%'";
    $res_max = $conn->query($sql_max);
    $next_num = 1;

    if ($res_max && $row_max = $res_max->fetch_assoc()) {
        if (!empty($row_max['max_id'])) {
            $last_num = (int) substr($row_max['max_id'], -4);
            $next_num = $last_num + 1;
        }
    }
    
    // 1 ID Transaksi ini dipakai bersama oleh semua barcode di bawah
    $transaction_id = $prefix . sprintf("%04d", $next_num);


    // ==========================================
    // PROSES DATABASE DENGAN TRANSACTION SYSTEM
    // ==========================================
    $conn->begin_transaction();

    try {
        $sql_check_item  = "SELECT status_transaksi, status_barang FROM master_item WHERE barcode = ?";
        
        // Kueri Insert menyertakan transaction_id yang SAMA untuk seluruh barcode
        $sql_insert_tx   = "INSERT INTO transaksi (transaction_id, request_id, barcode, id_sales, tgl_transaksi) VALUES (?, ?, ?, ?, NOW())";
        
        // Update status master_item menjadi Sold Out & Active
        $sql_update_stok = "UPDATE master_item SET status_transaksi = 'Sold Out', status_barang = 'Active' WHERE barcode = ?";

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
            $st_tx  = strtolower($item_data['status_transaksi'] ?? '');
            $st_brg = strtolower($item_data['status_barang'] ?? '');

            if ($st_tx === 'sold out') {
                throw new Exception("Barcode " . $barcode_clean . " sudah berstatus SOLD OUT (Double Scan)!");
            }
            if ($st_brg === 'inactive' || $st_brg === 'nonaktif') {
                throw new Exception("Barcode " . $barcode_clean . " berstatus NONAKTIF!");
            }

            // 2. Insert ke tabel transaksi ($transaction_id SAMA)
            $stmt_insert->bind_param("ssss", $transaction_id, $request_id, $barcode_clean, $id_sales);
            $stmt_insert->execute();

            // 3. Update status item di master_item
            $stmt_update->bind_param("s", $barcode_clean);
            $stmt_update->execute();
        }

        $stmt_check->close();
        $stmt_insert->close();
        $stmt_update->close();

        // Commit transaksi
        $conn->commit();
        
        echo "<script>alert('Transaksi Berhasil Disimpan dengan No. Transaksi: " . $transaction_id . "'); window.location.href='../return.php';</script>";
        exit();

    } catch (Exception $e) {
        $conn->rollback();
        echo "<script>alert('TRANSAKSI GAGAL: " . addslashes($e->getMessage()) . "'); window.history.back();</script>";
        exit();
    }
}

if (isset($conn)) {
    $conn->close();
}
?>