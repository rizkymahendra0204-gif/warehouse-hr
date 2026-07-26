<?php
function insertLog($conn, $nama_user, $role, $aktivitas, $keterangan, $modul) {
    try {
        $sql = "INSERT INTO log_activity (nama_user, role, aktivitas, keterangan, modul, created_at) 
                VALUES (:nama_user, :role, :aktivitas, :keterangan, :modul, NOW())";
        
        $stmt = $conn->prepare($sql);
        $stmt->execute([
            ':nama_user'  => $nama_user,
            ':role'       => $role,
            ':aktivitas'  => $aktivitas,
            ':keterangan' => $keterangan,
            ':modul'      => $modul
        ]);
    } catch (PDOException $e) {
        // Mencegah error log menghentikan transaksi utama
        error_log("Gagal mencatat log activity: " . $e->getMessage());
    }
}
?>