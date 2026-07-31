<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../includes/db.php'; 

// Penanganan variabel koneksi ($conn / $pdo)
if (!isset($pdo) && isset($conn)) {
    $pdo = $conn;
}

// Proteksi Halaman: Pastikan user sudah login & user_id tersimpan
if (empty($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$pesan_sukses = '';
$pesan_error = '';

// ==========================================
// 1. PROSES UPDATE PROFIL / DATA DIRI
// ==========================================
if (isset($_POST['update_profile'])) {
    $nama_lengkap = trim($_POST['nama_lengkap']);

    try {
        // Ambil data foto saat ini berdasarkan user_id
        $stmt_current = $pdo->prepare("SELECT foto_profil FROM users WHERE user_id = ?");
        $stmt_current->execute([$user_id]);
        $current_user = $stmt_current->fetch();
        $nama_foto = $current_user['foto_profil'] ?? 'default.png';

        // Proses Upload Foto Profil Baru (jika ada file diunggah)
        if (isset($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
            $file_tmp    = $_FILES['foto']['tmp_name'];
            $file_name   = $_FILES['foto']['name'];
            $file_ext    = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
            $allowed_ext = ['jpg', 'jpeg', 'png', 'webp'];

            if (in_array($file_ext, $allowed_ext)) {
                $nama_foto  = 'user_' . $user_id . '_' . time() . '.' . $file_ext;
                $target_dir = 'assets/img/profile/';
                
                if (!is_dir($target_dir)) {
                    mkdir($target_dir, 0777, true);
                }

                move_uploaded_file($file_tmp, $target_dir . $nama_foto);
            } else {
                $pesan_error = "Format foto harus JPG, JPEG, PNG, atau WEBP!";
            }
        }

        if (empty($pesan_error)) {
            // Update Database (Hanya kolom yang ada di tabel users)
            $stmt = $pdo->prepare("UPDATE users SET nama_lengkap = ?, foto_profil = ? WHERE user_id = ?");
            $stmt->execute([$nama_lengkap, $nama_foto, $user_id]);

            // Update Session agar nama & foto di header langsung berubah
            $_SESSION['nama_lengkap'] = $nama_lengkap;
            $_SESSION['foto_profil']  = $nama_foto;

            // Catat Log Activity jika fungsi tersedia
            if (function_exists('writeLog')) {
                writeLog($pdo, $user_id, $_SESSION['nama_lengkap'], $_SESSION['role'], 'Edit Profil', 'Profil', 'Pengguna memperbarui data profil');
            }

            $pesan_sukses = "Profil berhasil diperbarui!";
        }
    } catch (PDOException $e) {
        $pesan_error = "Terjadi kesalahan database: " . $e->getMessage();
    }
}

// ==========================================
// 2. PROSES UBAH PASSWORD
// ==========================================
if (isset($_POST['update_password'])) {
    $pass_lama  = $_POST['pass_lama'];
    $pass_baru  = $_POST['pass_baru'];
    $konfirmasi = $_POST['konfirmasi_pass'];

    if (empty($pass_lama) || empty($pass_baru) || empty($konfirmasi)) {
        $pesan_error = "Semua bidang password wajib diisi!";
    } elseif ($pass_baru !== $konfirmasi) {
        $pesan_error = "Konfirmasi password baru tidak cocok!";
    } elseif (strlen($pass_baru) < 6) {
        $pesan_error = "Password baru minimal harus 6 karakter!";
    } else {
        try {
            // Ambil password tersimpan
            $stmt = $pdo->prepare("SELECT password FROM users WHERE user_id = ?");
            $stmt->execute([$user_id]);
            $user = $stmt->fetch();

            if ($user && password_verify($pass_lama, $user['password'])) {
                // Hash Password Baru
                $hashed_password = password_hash($pass_baru, PASSWORD_DEFAULT);

                $update = $pdo->prepare("UPDATE users SET password = ? WHERE user_id = ?");
                $update->execute([$hashed_password, $user_id]);

                if (function_exists('writeLog')) {
                    writeLog($pdo, $user_id, $_SESSION['nama_lengkap'], $_SESSION['role'], 'Ubah Password', 'Keamanan', 'Pengguna berhasil mengubah password akun');
                }

                $pesan_sukses = "Password berhasil diperbarui!";
            } else {
                $pesan_error = "Password saat ini salah!";
            }
        } catch (PDOException $e) {
            $pesan_error = "Terjadi kesalahan database: " . $e->getMessage();
        }
    }
}

// Fetch Data User Terbaru untuk Tampilan Form Profil
$stmt = $pdo->prepare("SELECT * FROM users WHERE user_id = ?");
$stmt->execute([$user_id]);
$data_user = $stmt->fetch();
?>