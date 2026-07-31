<?php
// controllers/proses_transaksi.php
session_start();
require_once __DIR__ . '/../includes/db.php';

// Cek koneksi PDO
if (!isset($pdo)) {
    die("Koneksi Database Gagal: Variabel \$pdo tidak ditemukan di db.php");
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
    // GENERATE TRANSACTION ID UNIK (Contoh Format: TRX-2026-0001)
    // ====================================================================
    $prefix = "TRX-" . date('Y') . "-";
    
    $stmt_max = $pdo->prepare("SELECT MAX(transaction_id) AS max_id FROM transaksi WHERE transaction_id LIKE ?");
    $stmt_max->execute([$prefix . '%']);
    $row_max = $stmt_max->fetch(PDO::FETCH_ASSOC);

    $next_num = 1;
    if ($row_max && !empty($row_max['max_id'])) {
        $last_num = (int) substr($row_max['max_id'], -4);
        $next_num = $last_num + 1;
    }
    
    $transaction_id = $prefix . sprintf("%04d", $next_num);

    // ==========================================
    // PROSES DATABASE DENGAN TRANSACTION SYSTEM (PDO)
    // ==========================================
    try {
        $pdo->beginTransaction();

        // A. INSERT KE TABEL HEADER 'transaksi' (Cukup 1 Kali)
        $sql_insert_tx = "INSERT INTO transaksi (transaction_id, request_id, id_sales, tgl_transaksi) VALUES (?, ?, ?, NOW())";
        $stmt_tx = $pdo->prepare($sql_insert_tx);
        $stmt_tx->execute([$transaction_id, $request_id, $id_sales]);

        // Prepared statements untuk query di dalam loop
        $sql_check_item    = "SELECT status_transaksi, status_barang FROM master_item WHERE barcode = ?";
        $sql_insert_detail = "INSERT INTO transaksi_detail (transaction_id, barcode) VALUES (?, ?)";
        $sql_update_stok   = "UPDATE master_item SET status_transaksi = 'Sold Out', status_barang = 'Active' WHERE barcode = ?";

        $stmt_check  = $pdo->prepare($sql_check_item);
        $stmt_detail = $pdo->prepare($sql_insert_detail);
        $stmt_update = $pdo->prepare($sql_update_stok);

        // B. LOOP SETIAP BARCODE KE TABEL 'transaksi_detail' & UPDATE STOK
        foreach ($valid_barcodes as $barcode) {
            $barcode_clean = trim($barcode);

            // 1. Validasi status barang di master_item
            $stmt_check->execute([$barcode_clean]);
            $item_data = $stmt_check->fetch(PDO::FETCH_ASSOC);

            if (!$item_data) {
                throw new Exception("Barcode " . $barcode_clean . " tidak terdaftar di database gudang!");
            }

            $st_tx  = strtolower($item_data['status_transaksi'] ?? '');
            $st_brg = strtolower($item_data['status_barang'] ?? '');

            if ($st_tx === 'sold out') {
                throw new Exception("Barcode " . $barcode_clean . " sudah berstatus SOLD OUT!");
            }
            if ($st_brg === 'inactive' || $st_brg === 'nonaktif') {
                throw new Exception("Barcode " . $barcode_clean . " berstatus NONAKTIF!");
            }

            // 2. Insert ke tabel transaksi_detail
            $stmt_detail->execute([$transaction_id, $barcode_clean]);

            // 3. Update status item di master_item
            $stmt_update->execute([$barcode_clean]);
        }

        // C. UPDATE STATUS REQUEST MENJADI 'Done'
        if (!empty($request_id)) {
            $sql_update_request = "UPDATE request_form SET status = 'Done' WHERE request_id = ?";
            $stmt_req = $pdo->prepare($sql_update_request);
            $stmt_req->execute([$request_id]);
        }

        // D. CATAT REKAM JEJAK KE TABEL log_activity
        $user_id    = $_SESSION['user_id'] ?? NULL;
        $admin_nama = $_SESSION['nama_lengkap'] ?? $_SESSION['username'] ?? 'Admin HR';
        $admin_role = $_SESSION['role'] ?? 'Administrator';
        $total_item = count($valid_barcodes);

        $aktivitas  = "Approve Request & Transaksi";
        $keterangan = "Menyetujui Request ID #{$request_id} dengan No. Transaksi {$transaction_id} ({$total_item} item)";
        $modul      = "Transaksi";

        $sql_log  = "INSERT INTO log_activity (user_id, nama_user, role, aktivitas, keterangan, modul, created_at) 
                    VALUES (?, ?, ?, ?, ?, ?, NOW())";
        $stmt_log = $pdo->prepare($sql_log);
        $stmt_log->execute([$user_id, $admin_nama, $admin_role, $aktivitas, $keterangan, $modul]);

        // Commit seluruh transaksi database
        $pdo->commit();

        $_SESSION['alert_message'] = "Transaksi Berhasil Disimpan dengan No. Transaksi: <strong>{$transaction_id}</strong>";
        $_SESSION['alert_type']    = "success";

        echo "<script>alert('Transaksi Berhasil Disimpan dengan No. Transaksi: " . $transaction_id . "'); window.location.href='../pending.php';</script>";
        exit();

    } catch (Exception $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        echo "<script>alert('TRANSAKSI GAGAL: " . addslashes($e->getMessage()) . "'); window.history.back();</script>";
        exit();
    }
}
?>