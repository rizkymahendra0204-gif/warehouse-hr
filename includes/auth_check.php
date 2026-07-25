<?php 
require_once 'includes/auth_check.php'; // Pemasangan barikade login
$is_audit_active = true; 
include 'controllers/query_audit.php';
?>

<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Jika belum login, tendang ke halaman login
if (!isset($_SESSION['user_id'])) {
    $_SESSION['alert_message'] = "Anda harus login terlebih dahulu untuk mengakses halaman ini.";
    $_SESSION['alert_type']    = "danger";
    header("Location: login.php");
    exit;
}
?>