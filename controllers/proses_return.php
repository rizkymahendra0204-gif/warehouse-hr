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

        // 1. Prepare Statement untuk Validasi Pengecekan Status Barcode di Backend (GUARD)
        $stmt_check_status = $pdo->prepare("SELECT status_transaksi, status_barang FROM master_item WHERE barcode = ?");

        // 2. Prepare Statement untuk Insert Return dan Update Master
        $stmt_insert_return = $pdo->prepare("INSERT INTO return_items (transaction_id, barcode, alasan_return, kondisi_barang, tgl_return) VALUES (?, ?, ?, ?, NOW())");
        $stmt_update_master = $pdo->prepare("UPDATE master_item SET status_transaksi = 'Available', status_barang = 'Inactive' WHERE barcode = ?");

        foreach ($barcodes as $index => $barcode) {
            $barcode_clean = trim($barcode);
            $alasan        = isset($kondisi[$index]) ? trim($kondisi[$index]) : 'Layak';

            // ====================================================================
            // BACKEND GUARD: CEK STATUS BARANG SEBELUM PROSES RETURN
            // ====================================================================
            $stmt_check_status->execute([$barcode_clean]);
            $item = $stmt_check_status->fetch(PDO::FETCH_ASSOC);

            if (!$item) {
                throw new Exception("Barcode {$barcode_clean} tidak ditemukan dalam database master!");
            }

            $st_transaksi = strtolower(trim($item['status_transaksi'] ?? ''));
            $st_barang    = strtolower(trim($item['status_barang'] ?? ''));

            // Cegah proses jika barang sudah di-return sebelumnya (Available/Inactive)
            if ($st_transaksi === 'available' || $st_barang === 'inactive') {
                throw new Exception("Gagal! Item dengan Barcode {$barcode_clean} sudah pernah direturn sebelumnya (Status: Available/Inactive).");
            }
            // ====================================================================

            // Penentuan kondisi fisik
            $kondisi_fisik = (strpos(strtolower($alasan), 'cacat') !== false || strpos(strtolower($alasan), 'available') !== false || strpos(strtolower($alasan), 'tukar') !== false) ? 'Rusak' : 'Bagus';

            // A. Insert ke return_items
            $stmt_insert_return->execute([$transaction_id, $barcode_clean, $alasan, $kondisi_fisik]);

            // B. Update status master_item menjadi 'Available' & 'Inactive'
            $stmt_update_master->execute([$barcode_clean]);
        }

        // ====================================================================
        // 3. CATAT REKAM JEJAK KE TABEL log_activity
        // ====================================================================
        $admin_nama = $_SESSION['user_name'] ?? $_SESSION['nama'] ?? 'Admin HR';
        $admin_role = $_SESSION['role'] ?? 'Administrator';
        $total_item = count($barcodes);

        $aktivitas  = "Proses Return Barang";
        $keterangan = "Memproses return {$total_item} item untuk Transaksi #{$transaction_id}";
        $modul      = "Return";

        $sql_log = "INSERT INTO log_activity (nama_user, role, aktivitas, keterangan, modul, created_at) 
                    VALUES (:nama_user, :role, :aktivitas, :keterangan, :modul, NOW())";
        
        $stmt_log = $pdo->prepare($sql_log);
        $stmt_log->execute([
            ':nama_user'  => $admin_nama,
            ':role'       => $admin_role,
            ':aktivitas'  => $aktivitas,
            ':keterangan' => $keterangan,
            ':modul'      => $modul
        ]);

        // Commit seluruh perubahan ke database (return_items + master_item + log_activity)
        $pdo->commit();

        echo "<script>alert('Proses Return Berhasil Disimpan!'); window.location.href='../return';</script>";
        exit();

    } catch (Exception $e) {
        // Rollback jika ada error/validasi gagal saat eksekusi
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        echo "<script>alert('PROSES RETURN GAGAL: " . addslashes($e->getMessage()) . "'); window.history.back();</script>";
        exit();
    }
}
?>