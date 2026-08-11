<?php
require_once __DIR__ . '/../includes/db.php';

try {
    // Base URL ke project Form Request (menggunakan port 8080 sesuai XAMPP)
    $base_upload_url = '/Request.Form.2/';

    // 1. Query Data Pending (Request yang BELUM ada di tabel transaksi)
    $sql_pending = "SELECT rf.* FROM request_form rf 
                    LEFT JOIN transaksi t ON rf.request_id = t.request_id 
                    WHERE t.request_id IS NULL 
                    ORDER BY rf.request_id DESC";
                    
    $stmt_pending = $pdo->prepare($sql_pending);
    $stmt_pending->execute();
    $raw_pending = $stmt_pending->fetchAll(PDO::FETCH_ASSOC);

    // Olah Data Pending (Fix Path Gambar & Metode Pembayaran)
    $requests_pending = [];
    foreach ($raw_pending as $row) {
        // Fix Path Gambar
        if (!empty($row['upload'])) {
            $clean_path = ltrim($row['upload'], '/');
            $row['file_path'] = $base_upload_url . $clean_path;
            $row['file_name'] = basename($row['upload']);
        } else {
            $row['file_path'] = '#';
            $row['file_name'] = 'Tidak ada file';
        }

        // Fix Metode Pembayaran (Ubah nilai 0/NULL/kosong jadi 'TRANSFER')
        $pay = trim($row['pembayaran'] ?? '');
        if ($pay === '0' || $pay === '' || $pay === 'NULL') {
            $row['pembayaran_text'] = 'TRANSFER';
        } else {
            $row['pembayaran_text'] = strtoupper(str_replace('_', ' ', $pay));
        }

        $requests_pending[] = $row;
    }

    // 2. Query Data Selesai / Done (Request yang SUDAH ada di tabel transaksi)
    $sql_done = "SELECT rf.*, MAX(t.tgl_transaksi) AS tgl_transaksi 
                 FROM request_form rf 
                 INNER JOIN transaksi t ON rf.request_id = t.request_id 
                 GROUP BY rf.request_id 
                 ORDER BY tgl_transaksi DESC, rf.request_id DESC";
                 
    $stmt_done = $pdo->prepare($sql_done);
    $stmt_done->execute();
    $raw_done = $stmt_done->fetchAll(PDO::FETCH_ASSOC);

    // Olah Data Done
    $requests_done = [];
    foreach ($raw_done as $row_done) {
        if (!empty($row_done['upload'])) {
            $clean_path = ltrim($row_done['upload'], '/');
            $row_done['file_path'] = $base_upload_url . $clean_path;
            $row_done['file_name'] = basename($row_done['upload']);
        } else {
            $row_done['file_path'] = '#';
            $row_done['file_name'] = 'Tidak ada file';
        }

        $pay = trim($row_done['pembayaran'] ?? '');
        if ($pay === '0' || $pay === '' || $pay === 'NULL') {
            $row_done['pembayaran_text'] = 'TRANSFER';
        } else {
            $row_done['pembayaran_text'] = strtoupper(str_replace('_', ' ', $pay));
        }

        $requests_done[] = $row_done;
    }

} catch (PDOException $e) {
    // Fallback jika terjadi kesalahan query
    $requests_pending = [];
    $requests_done    = [];
}
?>