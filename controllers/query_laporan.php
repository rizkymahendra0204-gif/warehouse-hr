<?php
session_start();
require_once __DIR__ . '/../includes/db.php';

// Jika variabel koneksi di db.php Anda bernama $pdo, di-assign ke $conn agar konsisten
if (!isset($conn) && isset($pdo)) {
    $conn = $pdo;
}

// 1. Tangkap Filter Tanggal (Default: Awal Bulan s/d Akhir Bulan)
$start_date = isset($_GET['start_date']) ? $_GET['start_date'] : date('Y-m-01');
$end_date   = isset($_GET['end_date']) ? $_GET['end_date'] : date('Y-m-t');

try {
    // 2. QUERY CARD 1: Total Barang Masuk di Master
    $sql_masuk = "SELECT COUNT(barcode) FROM master_item";
    $stmt_masuk = $conn->query($sql_masuk);
    $total_masuk = $stmt_masuk->fetchColumn() ?: 0;

    // 3. QUERY CARD 2: Total Transaksi Unik
    $sql_trx = "SELECT COUNT(DISTINCT transaction_id) 
                FROM transaksi 
                WHERE DATE(tgl_transaksi) BETWEEN :start_date AND :end_date";
    $stmt_trx = $conn->prepare($sql_trx);
    $stmt_trx->execute([
        ':start_date' => $start_date,
        ':end_date'   => $end_date
    ]);
    $total_trx = $stmt_trx->fetchColumn() ?: 0;

    // 4. QUERY CARD 3: Total Return
    $sql_return = "SELECT COUNT(barcode) 
                   FROM return_items 
                   WHERE DATE(tgl_return) BETWEEN :start_date AND :end_date";
    $stmt_return = $conn->prepare($sql_return);
    $stmt_return->execute([
        ':start_date' => $start_date,
        ':end_date'   => $end_date
    ]);
    $total_return = $stmt_return->fetchColumn() ?: 0;

    // 5. QUERY TABEL: Rincian Transaksi Keluar
    $sql_table = "SELECT 
                    t.tgl_transaksi,
                    t.transaction_id,
                    rf.request_id,
                    rf.perusahaan,
                    GROUP_CONCAT(CONCAT(mi.tipe, ' ', mi.gender) SEPARATOR ',') AS all_items,
                    COUNT(t.barcode) AS qty_total
                FROM transaksi t
                INNER JOIN request_form rf ON t.request_id = rf.request_id
                INNER JOIN master_item mi ON TRIM(t.barcode) = TRIM(mi.barcode)
                WHERE DATE(t.tgl_transaksi) BETWEEN :start_date AND :end_date
                GROUP BY t.transaction_id
                ORDER BY t.tgl_transaksi DESC";

    $stmt_table = $conn->prepare($sql_table);
    $stmt_table->execute([
        ':start_date' => $start_date,
        ':end_date'   => $end_date
    ]);
    $list_transaksi = $stmt_table->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    die("Error Database: " . $e->getMessage());
}
?>