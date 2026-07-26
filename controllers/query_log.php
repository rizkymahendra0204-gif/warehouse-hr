<?php
require_once __DIR__ . '/../includes/db.php';

if (!isset($conn) && isset($pdo)) {
    $conn = $pdo;
}

// ==========================================
// 1. TANGKAP FILTER TANGGAL & PENCARIAN
// ==========================================
$start_date = isset($_GET['start_date']) && !empty($_GET['start_date']) ? $_GET['start_date'] : date('Y-m-01');
$end_date   = isset($_GET['end_date']) && !empty($_GET['end_date'])     ? $_GET['end_date']   : date('Y-m-d');
$search     = isset($_GET['search']) ? trim($_GET['search']) : '';

// ==========================================
// 2. QUERY FETCH DATA LOG ACTIVITY
// ==========================================
try {
    $sql = "SELECT * FROM log_activity WHERE DATE(created_at) BETWEEN :start_date AND :end_date";
    $params = [
        ':start_date' => $start_date,
        ':end_date'   => $end_date
    ];

    if (!empty($search)) {
        $sql .= " AND (nama_user LIKE :search OR aktivitas LIKE :search OR keterangan LIKE :search OR modul LIKE :search)";
        $params[':search'] = "%{$search}%";
    }

    $sql .= " ORDER BY created_at DESC";

    $stmt = $conn->prepare($sql);
    $stmt->execute($params);
    $logs = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Error Database Log: " . $e->getMessage());
}

// Helper Function untuk Warna Badges & Icon
function getLogTheme($modul) {
    switch (strtolower($modul)) {
        case 'transaksi':
            return ['icon' => 'bi-check2-circle', 'class' => 'icon-transaksi', 'badge' => 'bg-success-subtle text-success border-success'];
        case 'inventory':
            return ['icon' => 'bi-box-seam', 'class' => 'icon-inventory', 'badge' => 'bg-primary-subtle text-primary border-primary'];
        case 'return':
            return ['icon' => 'bi-arrow-counterclockwise', 'class' => 'icon-return', 'badge' => 'bg-warning-subtle text-warning border-warning'];
        case 'audit':
            return ['icon' => 'bi-pencil-square', 'class' => 'icon-audit', 'badge' => 'bg-info-subtle text-info border-info'];
        default: // Sistem / Login
            return ['icon' => 'bi-shield-lock', 'class' => 'icon-system', 'badge' => 'bg-secondary-subtle text-secondary border-secondary'];
    }
}
?>