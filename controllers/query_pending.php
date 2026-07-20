<?php
include 'includes/db.php';

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    die("Koneksi Database Gagal: " . $conn->connect_error);
}

// 2. Ambil data request yang statusnya 'Pending'
$sql = "SELECT * FROM request_form WHERE LOWER(status) = 'pending' ORDER BY tgl_request ASC";
$result = $conn->query($sql);
?>