<?php
require_once __DIR__ . '/../includes/auth_check.php';
require_once __DIR__ . '/../includes/db.php';

if (!isset($conn) && isset($pdo)) {
    $conn = $pdo;
}

// ==========================================
// 1. TANGKAP FILTER TANGGAL, PENCARIAN & PAGINATION
// ==========================================
$start_date = isset($_GET['start_date']) && !empty($_GET['start_date']) ? $_GET['start_date'] : date('Y-m-01');
$end_date   = isset($_GET['end_date']) && !empty($_GET['end_date'])     ? $_GET['end_date']   : date('Y-m-d');
$search     = isset($_GET['search']) ? trim($_GET['search']) : '';

// Pengaturan Pagination (Limit Data)
$limit = 10; // Jumlah data per halaman
$page  = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;
if ($page < 1) { $page = 1; }
$offset = ($page - 1) * $limit;

// ==========================================
// 2. QUERY FETCH DATA LOG ACTIVITY (WITH LIMIT)
// ==========================================
try {
    // Klausa kondisi dasar
    $where_clause = " WHERE DATE(created_at) BETWEEN :start_date AND :end_date";
    $params = [
        ':start_date' => $start_date,
        ':end_date'   => $end_date
    ];

    if (!empty($search)) {
        $where_clause .= " AND (nama_user LIKE :search OR aktivitas LIKE :search OR keterangan LIKE :search OR modul LIKE :search)";
        $params[':search'] = "%{$search}%";
    }

    // A. Hitung Total Data (Untuk mengetahui total halaman)
    $sql_count  = "SELECT COUNT(*) FROM log_activity" . $where_clause;
    $stmt_count = $conn->prepare($sql_count);
    $stmt_count->execute($params);
    $total_rows  = $stmt_count->fetchColumn();
    $total_pages = ceil($total_rows / $limit);

    // B. Query Ambil Data dengan LIMIT & OFFSET
    $sql  = "SELECT * FROM log_activity" . $where_clause . " ORDER BY created_at DESC LIMIT :limit OFFSET :offset";
    $stmt = $conn->prepare($sql);

    // Binding variabel pencarian/tanggal
    foreach ($params as $key => $val) {
        $stmt->bindValue($key, $val, PDO::PARAM_STR);
    }

    // Binding limit & offset khusus integer
    $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
    $stmt->bindValue(':offset', (int)$offset, PDO::PARAM_INT);

    $stmt->execute();
    $logs = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    error_log('[Warehouse HR] ' . $e);
    die("Error Database Log: " . 'Operasi database gagal. Hubungi administrator.');
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