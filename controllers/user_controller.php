<?php
require_once __DIR__ . '/../includes/auth_check.php';
require_once __DIR__ . '/../includes/activity.php';
wh_require_role(['admin']);
wh_require_post();
try {
        $input = wh_input();
    $action = wh_text($input, 'action', 30);
    $username = wh_text($input, 'username', 50);
    if ($action === 'tambah_user') {
        $name = wh_text($input, 'nama_lengkap', 100);
        $role = wh_text($input, 'role', 10);
        $password = $input['password'] ?? '';
        if (!preg_match('/^[a-zA-Z0-9_.-]{3,50}$/D', $username) || !in_array($role, ['admin', 'staff'], true)) { throw new DomainException('Username atau role tidak valid.'); }
        if (!wh_password_valid($password)) { throw new DomainException('Password harus 12–72 byte.'); }
        $hash = password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
        $pdo->beginTransaction();
        $pdo->prepare("INSERT INTO users (username, password, nama_lengkap, role, is_first_login) VALUES (?, ?, ?, ?, 1)")->execute([$username, $hash, $name, $role]);
        wh_activity($pdo, 'Tambah Pengguna', 'Membuat pengguna ' . $username, 'Pengguna');
        $pdo->commit();
        wh_success('Pengguna berhasil dibuat. Password wajib diganti pada login pertama.', 'setting?tab=users');
    }
    if ($action === 'hapus_user') {
        $pdo->beginTransaction();
        // Lock all administrators in a stable order to protect the last admin.
        $admins = $pdo->query("SELECT user_id FROM users WHERE role = 'admin' ORDER BY user_id FOR UPDATE")->fetchAll(PDO::FETCH_COLUMN);
        $stmt = $pdo->prepare('SELECT user_id, role FROM users WHERE username = ? FOR UPDATE');
        $stmt->execute([$username]); $target = $stmt->fetch();
        if (!$target || (int)$target['user_id'] === (int)$_SESSION['user_id']) { throw new DomainException('Pengguna tidak ditemukan atau merupakan akun yang sedang digunakan.'); }
        if ($target['role'] === 'admin' && count($admins) <= 1) { throw new DomainException('Administrator terakhir tidak dapat dihapus.'); }
        $pdo->prepare('DELETE FROM users WHERE user_id = ?')->execute([$target['user_id']]);
        wh_activity($pdo, 'Hapus Pengguna', 'Menghapus pengguna ' . $username, 'Pengguna');
        $pdo->commit();
        wh_success('Pengguna berhasil dihapus.', 'setting?tab=users');
    }
    throw new DomainException('Tindakan tidak dikenal.');
} catch (Throwable $error) {
    if ($pdo->inTransaction()) { $pdo->rollBack(); }
    if ($error instanceof DomainException) { wh_http_error(422, $error->getMessage()); }
    if ($error instanceof PDOException && ($error->errorInfo[1] ?? 0) === 1062) { wh_http_error(409, 'Username sudah digunakan.'); }
    throw $error;
}
