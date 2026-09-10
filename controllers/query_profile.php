<?php
require_once __DIR__ . '/../includes/auth_check.php';
require_once __DIR__ . '/../includes/activity.php';
$username = $_SESSION['username'];
$pesan_sukses = ''; $pesan_error = '';
if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
    $newFile = null;
    try {
        if (isset($_POST['update_profile'])) {
            $name = wh_text($_POST, 'nama_lengkap', 100);
            $stmt = $pdo->prepare('SELECT foto_profil FROM users WHERE user_id = ?');
            $stmt->execute([$_SESSION['user_id']]); $oldPhoto = $stmt->fetchColumn();
            $photo = basename($oldPhoto ?: 'default.png');
            if (isset($_FILES['foto']) && $_FILES['foto']['error'] !== UPLOAD_ERR_NO_FILE) {
                $upload = $_FILES['foto'];
                if ($upload['error'] !== UPLOAD_ERR_OK || $upload['size'] > 2 * 1024 * 1024 || !is_uploaded_file($upload['tmp_name'])) { throw new DomainException('Foto harus berhasil diunggah dan maksimal 2 MB.'); }
                $mime = (new finfo(FILEINFO_MIME_TYPE))->file($upload['tmp_name']);
                $dimensions = @getimagesize($upload['tmp_name']);
                if (!in_array($mime, ['image/jpeg','image/png','image/webp'], true) || !$dimensions || $dimensions[0] > 2048 || $dimensions[1] > 2048) { throw new DomainException('Foto harus JPG/PNG/WEBP dengan ukuran maksimal 2048 × 2048 piksel.'); }
                $image = @imagecreatefromstring(file_get_contents($upload['tmp_name']));
                if (!$image) { throw new DomainException('Isi file foto tidak valid.'); }
                $directory = __DIR__ . '/../assets/img/profile';
                if (!is_dir($directory) && !mkdir($directory, 0750, true)) { throw new RuntimeException('Cannot create profile directory.'); }
                $photo = 'user_' . (int)$_SESSION['user_id'] . '_' . bin2hex(random_bytes(16)) . '.png';
                $newFile = $directory . '/' . $photo;
                if (!imagepng($image, $newFile)) { throw new RuntimeException('Cannot store profile image.'); }
                imagedestroy($image); chmod($newFile, 0640);
            }
            $pdo->beginTransaction();
            $pdo->prepare('UPDATE users SET nama_lengkap = ?, foto_profil = ? WHERE user_id = ?')->execute([$name, $photo, $_SESSION['user_id']]);
            wh_activity($pdo, 'Edit Profil', 'Pengguna memperbarui profil.', 'Profil');
            $pdo->commit();
            $_SESSION['nama_lengkap'] = $name; $_SESSION['foto_profil'] = $photo;
            $newFile = null;
            $pesan_sukses = 'Profil berhasil diperbarui!';
        } elseif (isset($_POST['update_password'])) {
            $old = $_POST['pass_lama'] ?? ''; $new = $_POST['pass_baru'] ?? ''; $confirmation = $_POST['konfirmasi_pass'] ?? '';
            if (!is_string($old) || !wh_password_valid($new) || $new !== $confirmation) { throw new DomainException('Password baru harus 12–72 byte dan sesuai konfirmasi.'); }
            $pdo->beginTransaction();
            $stmt = $pdo->prepare('SELECT * FROM users WHERE user_id = ? FOR UPDATE');
            $stmt->execute([$_SESSION['user_id']]); $account = $stmt->fetch();
            if (!$account || !password_verify($old, $account['password'])) { throw new DomainException('Password saat ini salah.'); }
            if (password_verify($new, $account['password'])) { throw new DomainException('Password baru harus berbeda dari password lama.'); }
            $pdo->prepare('UPDATE users SET password = ?, auth_version = auth_version + 1 WHERE user_id = ?')->execute([password_hash($new, PASSWORD_BCRYPT, ['cost' => 12]), $account['user_id']]);
            wh_activity($pdo, 'Ubah Password', 'Password diganti; sesi lain dicabut.', 'Keamanan');
            $pdo->commit(); $account['auth_version']++; wh_set_login($account);
            $pesan_sukses = 'Password berhasil diperbarui. Sesi login lain telah dicabut.';
        } else { throw new DomainException('Tindakan tidak dikenal.'); }
    } catch (Throwable $error) {
        if ($pdo->inTransaction()) { $pdo->rollBack(); }
        if ($newFile && is_file($newFile)) { unlink($newFile); }
        if ($error instanceof DomainException) { $pesan_error = $error->getMessage(); }
        else { throw $error; }
    }
}
$stmt = $pdo->prepare('SELECT * FROM users WHERE user_id = ?');
$stmt->execute([$_SESSION['user_id']]); $data_user = $stmt->fetch() ?: [];
