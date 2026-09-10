<?php
function wh_activity(PDO $pdo, string $activity, string $description, string $module): void {
    $stmt = $pdo->prepare('INSERT INTO log_activity (user_id, nama_user, role, aktivitas, keterangan, modul) VALUES (?, ?, ?, ?, ?, ?)');
    $stmt->execute([$_SESSION['user_id'], $_SESSION['nama_lengkap'], $_SESSION['role'], $activity, $description, $module]);
}
