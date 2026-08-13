<?php
require_once __DIR__ . '/../includes/db.php'; 

if (!isset($conn) && isset($pdo)) {
    $conn = $pdo;
}

// 1. AMBIL STATUS MANAJEMEN GUDANG DARI DATABASE
try {
    $stmt_setting = $conn->prepare("SELECT setting_value FROM settings WHERE setting_key = 'audit_active'");
    $stmt_setting->execute();
    $audit_val = $stmt_setting->fetchColumn();
    $is_audit_active = ($audit_val === '1');
} catch (PDOException $e) {
    $is_audit_active = false;
}

// ==========================================
// 2. LOGIKA PERHITUNGAN RINGKASAN STOK 4 KOLOM
// ==========================================
$framework = [
    'Pria' => [
        'Baju'   => ['S' => 0, 'M' => 0, 'L' => 0, 'XL' => 0],
        'Celana' => ['30' => 0, '32' => 0, '34' => 0, '36' => 0]
    ],
    'Wanita' => [
        'Baju'   => ['S' => 0, 'M' => 0, 'L' => 0, 'XL' => 0],
        'Celana' => ['S' => 0, 'M' => 0, 'L' => 0, 'XL' => 0]
    ]
];

$stok_tersedia = $framework;
$stok_return   = $framework;

// Helper Fungsi Normalisasi Gender (Aman dari bentrokan/redeclare)
if (!function_exists('normalizeGender')) {
    function normalizeGender($raw_gender) {
        $g = strtolower(trim($raw_gender ?? ''));
        if ($g === 'male' || $g === 'pria' || $g === '1') {
            return 'Pria';
        } elseif ($g === 'female' || $g === 'wanita' || $g === '2') {
            return 'Wanita';
        }
        return ucfirst($g);
    }
}

// Helper Fungsi Normalisasi Tipe (Aman dari bentrokan/redeclare)
if (!function_exists('normalizeTipe')) {
    function normalizeTipe($raw_tipe) {
        $t = strtolower(trim($raw_tipe ?? ''));
        if ($t === 'baju' || $t === '01' || $t === 'atasan') {
            return 'Baju';
        } elseif ($t === 'celana' || $t === '02' || $t === 'bawahan') {
            return 'Celana';
        }
        return ucfirst($t);
    }
}

try {
    // 1. Hitung Stok Tersedia (Available & Active)
    $sql_available = "SELECT gender, tipe, size, COUNT(barcode) AS total FROM master_item 
                      WHERE LOWER(TRIM(status_transaksi)) = 'available' 
                        AND LOWER(TRIM(status_barang)) = 'active' 
                      GROUP BY gender, tipe, size";
    $stmt_av = $conn->query($sql_available);
    foreach ($stmt_av->fetchAll(PDO::FETCH_ASSOC) as $row) {
        $g = normalizeGender($row['gender'] ?? '');
        $t = normalizeTipe($row['tipe'] ?? '');
        $s = strtoupper(trim($row['size'] ?? ''));
        
        if (isset($stok_tersedia[$g][$t][$s])) {
            $stok_tersedia[$g][$t][$s] = (int)$row['total'];
        }
    }

    // 2. Hitung Stok Return
    $sql_return = "SELECT mi.gender, mi.tipe, mi.size, COUNT(ri.barcode) AS total 
                   FROM return_items ri 
                   JOIN master_item mi ON TRIM(ri.barcode) = TRIM(mi.barcode) 
                   GROUP BY mi.gender, mi.tipe, mi.size";
    $stmt_ret = $conn->query($sql_return);
    foreach ($stmt_ret->fetchAll(PDO::FETCH_ASSOC) as $row) {
        $g = normalizeGender($row['gender'] ?? '');
        $t = normalizeTipe($row['tipe'] ?? '');
        $s = strtoupper(trim($row['size'] ?? ''));
        
        if (isset($stok_return[$g][$t][$s])) {
            $stok_return[$g][$t][$s] = (int)$row['total'];
        }
    }
} catch (PDOException $e) {
    // Abaikan error
}

// ==========================================
// 3. POST HANDLER
// ==========================================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // A. PROSES TOGGLE BUKA/TUTUP PERIODE WAREHOUSE MANAGEMENT
    if (isset($_POST['action_type']) && $_POST['action_type'] === 'toggle_audit') {
        $new_status = ($_POST['audit_status'] === '1') ? '1' : '0';
        
        try {
            $conn->beginTransaction();

            $stmt_toggle = $conn->prepare("UPDATE settings SET setting_value = :val WHERE setting_key = 'audit_active'");
            $stmt_toggle->execute([':val' => $new_status]);

            $user_id    = $_SESSION['user_id'] ?? NULL;
            $admin_nama = $_SESSION['nama_lengkap'] ?? $_SESSION['username'] ?? 'Admin HR';
            $admin_role = $_SESSION['role'] ?? 'Administrator';
            $status_txt = ($new_status === '1') ? 'DIBUKA' : 'DITUTUP';

            $aktivitas  = ($new_status === '1') ? 'Buka Periode Management' : 'Tutup Periode Management';
            $keterangan = "Mengubah status periode warehouse management menjadi {$status_txt}";
            $modul      = "Warehouse Management";

            $sql_log  = "INSERT INTO log_activity (user_id, nama_user, role, aktivitas, keterangan, modul, created_at) 
                        VALUES (?, ?, ?, ?, ?, ?, NOW())";
            $stmt_log = $conn->prepare($sql_log);
            $stmt_log->execute([$user_id, $admin_nama, $admin_role, $aktivitas, $keterangan, $modul]);

            $conn->commit();

            $_SESSION['alert_message'] = ($new_status === '1') 
                ? "Periode Warehouse Management berhasil <b>DIBUKA</b>." 
                : "Periode Warehouse Management berhasil <b>DITUTUP</b>.";
            $_SESSION['alert_type'] = ($new_status === '1') ? 'success' : 'warning';

        } catch (PDOException $e) {
            if ($conn->inTransaction()) {
                $conn->rollBack();
            }
            $_SESSION['alert_message'] = "Gagal mengubah mode: " . $e->getMessage();
            $_SESSION['alert_type']    = 'danger';
        }

        header("Location: " . $_SERVER['PHP_SELF']);
        exit;
    }

    // B. PROSES UPDATE STATUS ITEM (EVALUASI CLOSING)
    if (isset($_POST['action_type']) && $_POST['action_type'] === 'update_status') {
        if (!$is_audit_active) {
            $_SESSION['alert_message'] = "Gagal: Periode warehouse management sedang ditutup.";
            $_SESSION['alert_type']    = "danger";
            header("Location: " . $_SERVER['PHP_SELF']);
            exit;
        }

        $barcode          = trim($_POST['barcode']);
        $status_transaksi = $_POST['status_transaksi'];
        $status_barang    = $_POST['status_barang'];

        try {
            $conn->beginTransaction();

            // Dihapus kondisi WHERE status_transaksi & status_barang opsional agar UPDATE fleksibel
            $sql_update = "UPDATE master_item 
                           SET status_transaksi = :status_tx, 
                               status_barang = :status_brg 
                           WHERE TRIM(barcode) = :barcode";
            
            $stmt_update = $conn->prepare($sql_update);
            $stmt_update->execute([
                ':status_tx'  => $status_transaksi,
                ':status_brg' => $status_barang,
                ':barcode'    => $barcode
            ]);

            if ($stmt_update->rowCount() > 0) {
                $user_id    = $_SESSION['user_id'] ?? NULL;
                $admin_nama = $_SESSION['nama_lengkap'] ?? $_SESSION['username'] ?? 'Admin HR';
                $admin_role = $_SESSION['role'] ?? 'Administrator';

                $aktivitas  = "Update Status Barcode";
                $keterangan = "Memperbarui status barcode {$barcode} menjadi {$status_transaksi} ({$status_barang}) via Warehouse Management";
                $modul      = "Warehouse Management";

                $sql_log = "INSERT INTO log_activity (user_id, nama_user, role, aktivitas, keterangan, modul, created_at) 
                            VALUES (?, ?, ?, ?, ?, ?, NOW())";
                $stmt_log = $conn->prepare($sql_log);
                $stmt_log->execute([$user_id, $admin_nama, $admin_role, $aktivitas, $keterangan, $modul]);

                $conn->commit();

                $_SESSION['alert_message'] = "Status barcode <b>{$barcode}</b> berhasil diperbarui menjadi <b>{$status_transaksi} ({$status_barang})</b>.";
                $_SESSION['alert_type']    = 'success';

            } else {
                $conn->rollBack();
                $_SESSION['alert_message'] = "Gagal memperbarui: Barcode <b>{$barcode}</b> tidak ditemukan.";
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
// 4. QUERY DAFTAR BARANG UNTUK EVALUASI
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
                 WHERE LOWER(TRIM(mi.status_transaksi)) = 'available' 
                   AND LOWER(TRIM(mi.status_barang)) = 'inactive'
                 ORDER BY mi.barcode ASC";
                 
    $stmt_list = $conn->query($sql_list);
    $items = $stmt_list->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Error Database: " . $e->getMessage());
}
?>