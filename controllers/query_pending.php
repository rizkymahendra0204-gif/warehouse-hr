<?php
include 'includes/db.php';

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    die("Koneksi Database Gagal: " . $conn->connect_error);
}

$sql_pending = "SELECT rf.* FROM request_form rf 
                LEFT JOIN transaksi t ON rf.request_id = t.request_id 
                WHERE t.request_id IS NULL 
                ORDER BY rf.request_id DESC";
$result_pending = $conn->query($sql_pending);

$sql_done = "SELECT rf.*, MAX(t.tgl_transaksi) AS tgl_transaksi 
             FROM request_form rf 
             INNER JOIN transaksi t ON rf.request_id = t.request_id 
             GROUP BY rf.request_id 
             ORDER BY tgl_transaksi DESC, rf.request_id DESC";
$result_done = $conn->query($sql_done);
?>