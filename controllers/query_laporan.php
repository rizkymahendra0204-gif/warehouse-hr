<?php
// 1. Barikade Autentikasi (Otomatis mengaktifkan session & cek login)
require_once __DIR__ . '/../includes/auth_check.php';

// 2. Load Koneksi Database PDO
require_once __DIR__ . '/../includes/db.php';

// 3. Tangkap Filter Tanggal (Default: Awal Bulan s/d Akhir Bulan)
$start_date = $_GET['start_date'] ?? date('Y-m-01');
$end_date   = $_GET['end_date']   ?? date('Y-m-t');

try {
    // 1. QUERY CARD 1: Total Barang Masuk di Master
    $sql_masuk  = "SELECT COUNT(barcode) FROM master_item";
    $stmt_masuk = $pdo->query($sql_masuk);
    $total_masuk = $stmt_masuk->fetchColumn() ?: 0;

    // 2. QUERY CARD 2: Total Transaksi Unik
    $sql_trx  = "SELECT COUNT(DISTINCT transaction_id) 
                FROM transaksi, request_form
                WHERE DATE(tgl_transaksi) BETWEEN :start_date AND :end_date";
    $stmt_trx = $pdo->prepare($sql_trx);
    $stmt_trx->execute([
        ':start_date' => $start_date,
        ':end_date'   => $end_date
    ]);
    $total_trx = $stmt_trx->fetchColumn() ?: 0;

    // 3. QUERY CARD 3: Total Return
    $sql_return  = "SELECT COUNT(barcode) 
                    FROM return_items 
                    WHERE DATE(tgl_return) BETWEEN :start_date AND :end_date";
    $stmt_return = $pdo->prepare($sql_return);
    $stmt_return->execute([
        ':start_date' => $start_date,
        ':end_date'   => $end_date
    ]);
    $total_return = $stmt_return->fetchColumn() ?: 0;

    // 4. QUERY CARD 4: Grand Total
    $sql_grand_total  = "SELECT SUM(rf.total_harga) 
                         FROM transaksi t
                         INNER JOIN request_form rf ON t.request_id = rf.request_id
                         WHERE DATE(t.tgl_transaksi) BETWEEN :start_date AND :end_date";
    $stmt_grand_total = $pdo->prepare($sql_grand_total);
    $stmt_grand_total->execute([
        ':start_date' => $start_date,
        ':end_date'   => $end_date
    ]);
    $grand_total = $stmt_grand_total->fetchColumn() ?: 0;

    // 4. QUERY TABEL: Rincian Transaksi Keluar (Aman untuk Strict Mode MySQL)
    $sql_table = "SELECT 
                t.tgl_transaksi,
                t.transaction_id,
                rf.request_id,
                rf.perusahaan,
                rf.brand,
                rf.nama_sa,
                GROUP_CONCAT(CONCAT(mi.tipe, ' ', mi.gender, ' - Size ', mi.size) SEPARATOR ', ') AS all_items,
                (COUNT(td.barcode) - COALESCE(ret.qty_return, 0)) AS total_pcs,
                rf.total_harga AS harga,
                rf.pembayaran,
                ret.items_returned_raw
            FROM transaksi t
            INNER JOIN transaksi_detail td ON t.transaction_id = td.transaction_id
            INNER JOIN request_form rf ON t.request_id = rf.request_id 
            INNER JOIN master_item mi ON TRIM(td.barcode) = TRIM(mi.barcode)
            LEFT JOIN (
                SELECT 
                    ri.transaction_id,
                    GROUP_CONCAT(CONCAT(mir.tipe, ' ', mir.gender, ' - Size ', mir.size) SEPARATOR ', ') AS items_returned_raw,
                    COUNT(ri.barcode) AS qty_return
                FROM return_items ri
                INNER JOIN master_item mir ON TRIM(ri.barcode) = TRIM(mir.barcode)
                GROUP BY ri.transaction_id
            ) ret ON t.transaction_id = ret.transaction_id
            WHERE DATE(t.tgl_transaksi) BETWEEN :start_date AND :end_date
            AND rf.request_id LIKE 'FR%'
            GROUP BY 
                t.transaction_id, 
                t.tgl_transaksi, 
                rf.request_id , 
                rf.perusahaan, 
                rf.brand, 
                rf.nama_sa, 
                ret.items_returned_raw, 
                ret.qty_return
            ORDER BY t.tgl_transaksi DESC";

    $stmt_table = $pdo->prepare($sql_table);
    $stmt_table->execute([
        ':start_date' => $start_date,
        ':end_date'   => $end_date
    ]);
    $list_transaksi = $stmt_table->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    die("Error Database: " . $e->getMessage());
}
?>