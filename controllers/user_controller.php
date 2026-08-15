<?php
require_once __DIR__ . '/../includes/auth_check.php';
require_once __DIR__ . '/../includes/db.php';

// Proteksi: Akses Khusus Administrator
if (!isset($_SESSION['role']) || strtolower($_SESSION['role']) !== 'admin') {
    $_SESSION['alert_type'] = 'danger';
    $_SESSION['alert_message'] = 'Akses ditolak! Anda bukan Administrator.';
    header('Location: ../index.php');
    exit;
}

try {
    $conn = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);
} catch (PDOException $e) {
    die("Koneksi gagal: " . $e->getMessage());
}

// -------------------------------------------------------------
// AKSI 1: TAMBAH USER BARU
// -------------------------------------------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'tambah_user') {
    $username     = trim($_POST['username'] ?? '');
    $nama_lengkap = trim($_POST['nama_lengkap'] ?? '');
    $password     = $_POST['password'] ?? '';
    $role         = trim($_POST['role'] ?? 'user');

    if (empty($username) || empty($password) || empty($nama_lengkap)) {
        $_SESSION['alert_type'] = 'danger';
        $_SESSION['alert_message'] = 'Semua kolom formulir wajib diisi!';
        header('Location: ../users.php');
        exit;
    }

    try {
        // Cek duplikasi username
        $stmt_check = $conn->prepare("SELECT COUNT(*) FROM users WHERE username = ?");
        $stmt_check->execute([$username]);
        if ($stmt_check->fetchColumn() > 0) {
            $_SESSION['alert_type'] = 'warning';
            $_SESSION['alert_message'] = 'Username "' . htmlspecialchars($username) . '" sudah terdaftar!';
            header('Location: ../users.php');
            exit;
        }

        // Hashing password dengan Bcrypt
        $password_hash = password_hash($password, PASSWORD_BCRYPT);

        // Simpan user dengan kolom default yang sesuai
        $stmt_insert = $conn->prepare("INSERT INTO users (username, password, nama_lengkap, role, is_first_login, foto_profil) 
                                       VALUES (?, ?, ?, ?, 0, 'default.png')");
        $stmt_insert->execute([$username, $password_hash, $nama_lengkap, $role]);

        $_SESSION['alert_type'] = 'success';
        $_SESSION['alert_message'] = 'Pengguna baru (' . htmlspecialchars($username) . ') berhasil dibuat!';
        header('Location: ../users.php');
        exit;
    } catch (PDOException $e) {
        $_SESSION['alert_type'] = 'danger';
        $_SESSION['alert_message'] = 'Gagal membuat user: ' . $e->getMessage();
        header('Location: ../users.php');
        exit;
    }
}

// -------------------------------------------------------------
// AKSI 2: HAPUS USER
// -------------------------------------------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'hapus_user') {
    $username_target = trim($_POST['username'] ?? '');

    // Cegah admin menghapus akunnya sendiri yang sedang aktif login
    if ($username_target === $_SESSION['username']) {
        $_SESSION['alert_type'] = 'danger';
        $_SESSION['alert_message'] = 'Anda tidak dapat menghapus akun Anda sendiri saat sedang login!';
        header('Location: ../users.php');
        exit;
    }

    try {
        $stmt_del = $conn->prepare("DELETE FROM users WHERE username = ?");
        $stmt_del->execute([$username_target]);

        $_SESSION['alert_type'] = 'success';
        $_SESSION['alert_message'] = 'User "' . htmlspecialchars($username_target) . '" berhasil dihapus!';
        header('Location: ../users.php');
        exit;
    } catch (PDOException $e) {
        $_SESSION['alert_type'] = 'danger';
        $_SESSION['alert_message'] = 'Gagal menghapus user: ' . $e->getMessage();
        header('Location: ../users.php');
        exit;
    }
}