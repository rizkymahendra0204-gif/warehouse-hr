<?php
// Perbaikan path include agar tidak Fatal Error
require_once __DIR__ . '/../includes/auth_check.php';
require_once __DIR__ . '/../includes/db.php';

if (!isset($conn) && isset($pdo)) {
    $conn = $pdo;
}

// 1. Validasi Akses Admin
$is_admin = isset($_SESSION['role']) && strtolower($_SESSION['role']) === 'admin';
$active_tab = $_GET['tab'] ?? ($is_admin ? 'users' : 'notif');

// 4. Penanganan Form Simpan Notifikasi
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_notifications'])) {
    $notif_request = isset($_POST['notif_request']) ? 1 : 0;
    $notif_return  = isset($_POST['notif_return'])  ? 1 : 0;
    $notif_stock   = isset($_POST['notif_stock'])   ? 1 : 0;
    $username_curr = $_SESSION['username'] ?? '';

    try {
        $stmt_notif = $conn->prepare("UPDATE users SET 
            notif_request = :notif_request, 
            notif_return  = :notif_return, 
            notif_stock   = :notif_stock 
            WHERE username = :uname");

        $stmt_notif->execute([
            ':notif_request' => $notif_request,
            ':notif_return'  => $notif_return,
            ':notif_stock'   => $notif_stock,
            ':uname'         => $username_curr
        ]);

        // Perbarui Session
        $_SESSION['notif_settings'] = [
            'request' => $notif_request,
            'return'  => $notif_return,
            'stock'   => $notif_stock
        ];

        $_SESSION['alert_message'] = $lang['alert_notif_success'] ?? "Pengaturan notifikasi berhasil diperbarui!";
        $_SESSION['alert_type']    = "success";
    } catch (PDOException $e) {
    error_log('[Warehouse HR] ' . $e);
        $_SESSION['alert_message'] = "Error: " . 'Operasi database gagal. Hubungi administrator.';
        $_SESSION['alert_type']    = "danger";
    }

    header("Location: setting?tab=notif");
    exit;
}

// 5. Fetch Data Pengguna Aktif & Daftar Users
$current_username = $_SESSION['username'] ?? '';
$stmt_curr = $conn->prepare("SELECT * FROM users WHERE username = :uname LIMIT 1");
$stmt_curr->execute([':uname' => $current_username]);
$data_user = $stmt_curr->fetch(PDO::FETCH_ASSOC) ?: [];

$list_users = [];
if ($is_admin) {
    try {
        $stmt_users = $conn->query("SELECT * FROM users ORDER BY username ASC");
        $list_users = $stmt_users->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
    error_log('[Warehouse HR] ' . $e);}
}
?>