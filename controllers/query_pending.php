<?php
require_once __DIR__ . '/../includes/db.php';

try {
    // 1. Query Data Pending (Request yang BELUM ada di tabel transaksi)
    $sql_pending = "SELECT rf.* FROM request_form rf 
                    LEFT JOIN transaksi t ON rf.request_id = t.request_id 
                    WHERE t.request_id IS NULL 
                    ORDER BY rf.request_id DESC";
                    
    $stmt_pending = $pdo->prepare($sql_pending);
    $stmt_pending->execute();
    $requests_pending = $stmt_pending->fetchAll(PDO::FETCH_ASSOC);

    // 2. Query Data Selesai / Done (Request yang SUDAH ada di tabel transaksi)
    $sql_done = "SELECT rf.*, MAX(t.tgl_transaksi) AS tgl_transaksi 
                 FROM request_form rf 
                 INNER JOIN transaksi t ON rf.request_id = t.request_id 
                 GROUP BY rf.request_id 
                 ORDER BY tgl_transaksi DESC, rf.request_id DESC";
                 
    $stmt_done = $pdo->prepare($sql_done);
    $stmt_done->execute();
    $requests_done = $stmt_done->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    // Fallback jika terjadi kesalahan query
    $requests_pending = [];
    $requests_done    = [];
}