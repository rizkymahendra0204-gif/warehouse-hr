<?php
require_once __DIR__ . '/audit_action.php';
require_once __DIR__ . '/../includes/auth_check.php';
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
    error_log('[Warehouse HR] ' . $e);
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
    $sql_return = "SELECT mi.gender, mi.tipe, mi.size, COUNT(DISTINCT mi.barcode) AS total 
                   FROM return_items ri 
                   JOIN master_item mi ON TRIM(ri.barcode) = TRIM(mi.barcode)
                   WHERE LOWER(TRIM(mi.status_transaksi)) = 'available' 
                     AND LOWER(TRIM(mi.status_barang)) = 'inactive'
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
    error_log('[Warehouse HR] ' . $e);
    // Abaikan error
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
    error_log('[Warehouse HR] ' . $e);
    die("Error Database: " . 'Operasi database gagal. Hubungi administrator.');
}

