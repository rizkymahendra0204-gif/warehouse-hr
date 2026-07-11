<?php
// includes/db.php

$host = 'localhost';
$db   = ''; // Ganti dengan nama DB Anda
$user = '';           // Username default Postgres
$pass = '';      // Password Postgres Anda

try {
    // Membuat koneksi menggunakan PDO agar aman
    $dsn = "pgsql:host=$host;dbname=$db";
    $pdo = new PDO($dsn, $user, $pass, [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
    
    // Koneksi berhasil!
} catch (\PDOException $e) {
    // Jika gagal, tampilkan error (untuk development)
    throw new \PDOException($e->getMessage(), (int)$e->getCode());
}
?>