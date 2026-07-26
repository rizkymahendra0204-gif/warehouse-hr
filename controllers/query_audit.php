<?php
session_start();
require_once __DIR__ . '/../includes/db.php'; 

if (!isset($conn) && isset($pdo)) {
    $conn = $pdo;
}

// 1. AMBIL STATUS AUDIT DARI DATABASE
try {
    $stmt_setting = $conn->prepare("SELECT setting_value FROM settings WHERE setting_key = 'audit_active'");
    $stmt_setting->execute();
    $audit_val = $stmt_setting->fetchColumn();
    $is_audit_active = ($audit_val === '1');
} catch (PDOException $e) {
    $is_audit_active = false;
}

// ==========================================
// 2. POST HANDLER
// ==========================================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // ------------------------------------------------------------------
    // A. PROSES TOGGLE BUKA/TUTUP PERIODE AUDIT
    // ------------------------------------------------------------------
    if (isset($_POST['action_type']) && $_POST['action_type'] === 'toggle_audit') {
        $new_status = ($_POST['audit_status'] === '1') ? '1' : '0';
        
        try {
            $conn->beginTransaction();

            // 1. Update status settings
            $stmt_toggle = $conn->prepare("UPDATE settings SET setting_value = :val WHERE setting_key = 'audit_active'");
            $stmt_toggle->execute([':val' => $new_status]);

            // 2. Catat Log Activity (SUDAH DISESUAIKAN SESSION & USER_ID)
            $user_id    = $_SESSION['user_id'] ?? NULL;
            $admin_nama = $_SESSION['nama_lengkap'] ?? $_SESSION['username'] ?? 'Admin HR';
            $admin_role = $_SESSION['role'] ?? 'Administrator';
            $status_txt = ($new_status === '1') ? 'DIBUKA' : 'DITUTUP';

            $aktivitas  = ($new_status === '1') ? 'Buka Periode Audit' : 'Tutup Periode Audit';
            $keterangan = "Mengubah status periode audit internal menjadi {$status_txt}";
            $modul      = "Audit";

            $sql_log  = "INSERT INTO log_activity (user_id, nama_user, role, aktivitas, keterangan, modul, created_at) 
                        VALUES (?, ?, ?, ?, ?, ?, NOW())";
            $stmt_log = $conn->prepare($sql_log);
            $stmt_log->execute([$user_id, $admin_nama, $admin_role, $aktivitas, $keterangan, $modul]);

            // Commit perubahan setting + log
            $conn->commit();

            $_SESSION['alert_message'] = ($new_status === '1') 
                ? "Periode Audit Internal berhasil <b>DIBUKA</b>." 
                : "Periode Audit Internal berhasil <b>DITUTUP</b>.";
            $_SESSION['alert_type'] = ($new_status === '1') ? 'success' : 'warning';

        } catch (PDOException $e) {
            if ($conn->inTransaction()) {
                $conn->rollBack();
            }
            $_SESSION['alert_message'] = "Gagal mengubah mode audit: " . $e->getMessage();
            $_SESSION['alert_type']    = 'danger';
        }

        header("Location: " . $_SERVER['PHP_SELF']);
        exit;
    }

    // ------------------------------------------------------------------
    // B. PROSES UPDATE STATUS ITEM AUDIT
    // ------------------------------------------------------------------
    if (isset($_POST['action_type']) && $_POST['action_type'] === 'update_status') {
        if (!$is_audit_active) {
            $_SESSION['alert_message'] = "Gagal: Periode audit internal sedang ditutup.";
            $_SESSION['alert_type']    = "danger";
            header("Location: " . $_SERVER['PHP_SELF']);
            exit;
        }

        $barcode          = trim($_POST['barcode']);
        $status_transaksi = $_POST['status_transaksi'];
        $status_barang    = $_POST['status_barang'];

        try {
            $conn->beginTransaction();

            // 1. Update item status di master_item
            $sql_update = "UPDATE master_item 
                           SET status_transaksi = :status_tx, 
                               status_barang = :status_brg 
                           WHERE TRIM(barcode) = :barcode 
                             AND status_transaksi = 'Available' 
                             AND status_barang = 'Inactive'";
            
            $stmt_update = $conn->prepare($sql_update);
            $stmt_update->execute([
                ':status_tx'  => $status_transaksi,
                ':status_brg' => $status_barang,
                ':barcode'    => $barcode
            ]);

            if ($stmt_update->rowCount() > 0) {
                // 2. Catat Log Activity jika update berhasil
                $user_id    = $_SESSION['user_id'] ?? NULL;
                $admin_nama = $_SESSION['nama_lengkap'] ?? $_SESSION['username'] ?? 'Admin HR';
                $admin_role = $_SESSION['role'] ?? 'Administrator';

                $aktivitas  = "Audit Barcode";
                $keterangan = "Memperbarui status barcode {$barcode} menjadi {$status_transaksi} ({$status_barang}) via Audit Internal";
                $modul      = "Audit";

                $sql_log = "INSERT INTO log_activity (user_id, nama_user, role, aktivitas, keterangan, modul, created_at) 
                            VALUES (?, ?, ?, ?, ?, ?, NOW())";
                $stmt_log = $conn->prepare($sql_log);
                $stmt_log->execute([
                    $user_id,
                    $admin_nama,
                    $admin_role,
                    $aktivitas,
                    $keterangan,
                    $modul
                ]);

                // Commit transaksi + log
                $conn->commit();

                $_SESSION['alert_message'] = "Status barcode <b>{$barcode}</b> berhasil diperbarui menjadi <b>{$status_transaksi} ({$status_barang})</b>.";
                $_SESSION['alert_type']    = 'success';

            } else {
                $conn->rollBack();
                $_SESSION['alert_message'] = "Gagal memperbarui: Barcode <b>{$barcode}</b> tidak ditemukan atau status awalnya bukan <b>Available (Inactive)</b>.";
                $_SESSION['alert_type']    = 'warning';
            }

        } catch (PDOException $e) {
            if ($conn->inTransaction()) {
                $conn->rollBack();
            }
            $_SESSION['alert_message'] = "Gagal memperbarui status: " . $e->getMessage();
            $_SESSION['alert_type']    = 'danger';
        }

        header("Location: " . $_SERVER['PHP_SELF']);
        exit;
    }
}

// ==========================================
// 3. QUERY DAFTAR BARANG UNTUK AUDIT
// ==========================================
try {
    $sql_list = "SELECT 
                    mi.barcode,
                    mi.tipe,
                    mi.gender,
                    mi.size,
                    mi.status_transaksi,
                    mi.status_barang
                 FROM master_item mi
                 WHERE mi.status_transaksi = 'Available' 
                   AND mi.status_barang = 'Inactive'
                 ORDER BY mi.barcode ASC";
                 
    $stmt_list = $conn->query($sql_list);
    $items = $stmt_list->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Error Database: " . $e->getMessage());
}
?>