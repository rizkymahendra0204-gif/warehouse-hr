<?php
// controllers/proses_tambah_item.php
session_start();
require_once __DIR__ . '/../includes/db.php'; 

// Cek apakah koneksi $pdo dari db.php berhasil dipanggil
if (!isset($pdo)) {
    die("Koneksi Database Gagal: Variabel \$pdo tidak ditemukan di db.php");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Menangkap barcode & detail item dari form
    $barcode = trim($_POST['barcode'] ?? ''); 
    $gender  = trim($_POST['gender'] ?? ''); 
    $tipe    = trim($_POST['tipe'] ?? '');   
    $size    = trim($_POST['size'] ?? '');   

    if (empty($barcode)) {
        echo "<script>alert('Error: Barcode tidak boleh kosong!'); window.history.back();</script>";
        exit();
    }

    try {
        // ==========================================
        // 1. VALIDASI DUPLIKASI BARCODE
        // ==========================================
        $stmt_cek = $pdo->prepare("SELECT barcode FROM master_item WHERE barcode = ?");
        $stmt_cek->execute([$barcode]);
        $item_ada = $stmt_cek->fetch(PDO::FETCH_ASSOC);

        if ($item_ada) {
            echo "<script>alert('Error: Barcode " . addslashes($barcode) . " sudah terdaftar di sistem!'); window.location.href='../stok_barang.php';</script>";
            exit();
        }

        // Mulai Transaksi Database (Atomic)
        $pdo->beginTransaction();

        // ==========================================
        // 2. INSERT DATA BARANG BARU KE master_item
        // ==========================================
        $sql_insert = "INSERT INTO master_item (barcode, gender, tipe, size) VALUES (?, ?, ?, ?)";
        $stmt_insert = $pdo->prepare($sql_insert);
        $stmt_insert->execute([$barcode, $gender, $tipe, $size]);

        // ==========================================
        // 3. CATAT REKAM JEJAK KE TABEL log_activity (SUDAH DIPERBAIKI)
        // ==========================================
        $user_id    = $_SESSION['user_id'] ?? NULL;
        $admin_nama = $_SESSION['nama_lengkap'] ?? $_SESSION['username'] ?? 'Admin HR';
        $admin_role = $_SESSION['role'] ?? 'Administrator';

        $aktivitas  = "Tambah Item Baru";
        $keterangan = "Menambahkan item baru dengan Barcode: {$barcode} ({$gender} | {$tipe} | Size: {$size})";
        $modul      = "Inventory";

        $sql_log  = "INSERT INTO log_activity (user_id, nama_user, role, aktivitas, keterangan, modul, created_at) 
                    VALUES (?, ?, ?, ?, ?, ?, NOW())";
        $stmt_log = $pdo->prepare($sql_log);
        $stmt_log->execute([$user_id, $admin_nama, $admin_role, $aktivitas, $keterangan, $modul]);

        // Commit transaksi (Simpan Barang + Simpan Log secara bersamaan)
        $pdo->commit();

        header("Location: ../stok_barang.php");
        exit();

    } catch (Exception $e) {
        // Rollback jika terjadi error
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        echo "<script>alert('Gagal menyimpan data: " . addslashes($e->getMessage()) . "'); window.history.back();</script>";
        exit();
    }
}
?>