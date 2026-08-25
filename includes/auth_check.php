<?php 
// 1. Inisialisasi Session Wajib di Baris Teratas
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 2. Load Dependensi Database & Sistem
require_once __DIR__ . '/db.php';
// $is_audit_active = true; 
/// include __DIR__ . '/../controllers/query_audit.php';
require_once __DIR__ . '/language.php';

// 3. Validasi Login Pengguna
if (!isset($_SESSION['user_id']) && !isset($_SESSION['username'])) {
    $_SESSION['alert_message'] = "Anda harus login terlebih dahulu untuk mengakses halaman ini.";
    $_SESSION['alert_type']    = "danger";
    header("Location: login");
    exit;
}
?>