<?php
require_once __DIR__ . '/../includes/auth_check.php';
include 'includes/db.php';

// Pastikan koneksi PDO tersedia
if (!isset($conn) && isset($pdo)) {
    $conn = $pdo;
}

// 1. VALIDASI LOGIN
if (!isset($_SESSION['username'])) {
    header('Location: login.php');
    exit;
}

$is_admin = isset($_SESSION['role']) && strtolower($_SESSION['role']) === 'admin';

// Atur tab aktif (Default: 'users' untuk Admin, 'notif' untuk User biasa)
$active_tab = $_GET['tab'] ?? ($is_admin ? 'users' : 'notif');

// URL Penangan Bahasa (Menyesuaikan halaman saat ini)
$current_page = 'setting.php?tab=' . urlencode($active_tab);
$url_lang_id  = $current_page . '&lang=id';
$url_lang_en  = $current_page . '&lang=en';

// 2. PENANGANAN FORM NOTIFIKASI (POST HANDLER)
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

        $_SESSION['alert_message'] = $lang['alert_notif_success'] ?? "Pengaturan notifikasi berhasil diperbarui!";
        $_SESSION['alert_type']    = "success";
    } catch (PDOException $e) {
        $_SESSION['notif_settings'] = [
            'request' => $notif_request,
            'return'  => $notif_return,
            'stock'   => $notif_stock
        ];
        $_SESSION['alert_message'] = $lang['alert_notif_session'] ?? "Pengaturan notifikasi disimpan (Session Mode)!";
        $_SESSION['alert_type']    = "success";
    }

    header("Location: setting.php?tab=notif");
    exit;
}

// 3. FETCH DAFTAR USER (KHUSUS ADMIN)
$list_users = [];
if ($is_admin) {
    try {
        $stmt = $conn->query("SELECT * FROM users ORDER BY username ASC");
        $list_users = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        $_SESSION['alert_message'] = "Error mengambil data user: " . $e->getMessage();
        $_SESSION['alert_type'] = "danger";
    }
}

// 4. FETCH DATA USER AKTIF
$current_username = $_SESSION['username'] ?? '';
$stmt_curr = $conn->prepare("SELECT * FROM users WHERE username = :uname LIMIT 1");
$stmt_curr->execute([':uname' => $current_username]);
$data_user = $stmt_curr->fetch(PDO::FETCH_ASSOC) ?: [];
?>