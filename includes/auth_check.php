<?php 
require_once __DIR__ . '/db.php';
$is_audit_active = true; 
include __DIR__ . '/../controllers/query_audit.php';

// Load sistem bahasa secara global untuk semua halaman
require_once __DIR__ . '/language.php';


if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Jika belum login, set alert dan tendang ke login.php
if (!isset($_SESSION['user_id'])) {
    $_SESSION['alert_message'] = "Anda harus login terlebih dahulu untuk mengakses halaman ini.";
    $_SESSION['alert_type']    = "danger";
    header("Location: login.php");
    exit; // Wajib dipanggil untuk menghentikan eksekusi script selanjutnya
}
?>