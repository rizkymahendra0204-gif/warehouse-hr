<?php
// includes/db.php

$host     = 'localhost';
$db     = 'db_warehouse';
$user   = 'root';
$pass   = '';

// 1. Baca status dari cookie (default: 'collapsed' jika cookie belum ada)
$sidebar_cookie = $_COOKIE['sidebar_state'] ?? 'collapsed';

// 2. Tentukan class CSS yang akan dipasang
$sidebar_class = ($sidebar_cookie === 'collapsed') ? 'sidebar-collapsed' : '';

try {
    // Membuat koneksi ke database
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $user, $pass);
    
    // Mengatur mode error ke Exception agar script berhenti jika ada query salah
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Mengatur default fetch mode menjadi array asosiatif (bisa dipanggil dengan nama kolom)
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    
} catch (PDOException $e) {
    // Jika koneksi gagal, tampilkan error yang jelas
    die("Koneksi database gagal: " . $e->getMessage());
}
?>