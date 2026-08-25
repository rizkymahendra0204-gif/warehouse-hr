<?php
// dashboard.php / controllers/query_dashboard.php
include 'includes/db.php';

$conn = new mysqli("localhost", "root", "", "db_warehouse");

// ==========================================
// 1. QUERY MENGHITUNG DATA KPI CARDS
// ==========================================

// KPI 1: Pending Request
$kpi_pending = 0;
$res_pending = $conn->query("SELECT COUNT(*) as total FROM request_form WHERE status = 'Pending'");
if ($res_pending) {
    $row = $res_pending->fetch_assoc();
    $kpi_pending = (int)$row['total'];
}

// KPI 2: Total Transaksi
$kpi_transaksi = 0;
$res_transaksi = $conn->query("SELECT COUNT(DISTINCT transaction_id) as total FROM transaksi");
if ($res_transaksi) {
    $row = $res_transaksi->fetch_assoc();
    $kpi_transaksi = (int)$row['total'];
}

// KPI 3: Total Return
$kpi_return = 0;
$res_return = $conn->query("SELECT COUNT(*) as total FROM return_items");
if ($res_return) {
    $row = $res_return->fetch_assoc();
    $kpi_return = (int)$row['total'];
}

// ==========================================
// 2. QUERY STOK WAREHOUSE ACTIVE (Status: Available)
// ==========================================
$active_stock = [
    'total'        => 0,
    'baju_pria'    => 0,
    'celana_pria'  => 0,
    'baju_wanita'  => 0,
    'celana_wanita' => 0
];

$sql_active = "SELECT 
    COUNT(barcode) AS total,
    SUM(CASE WHEN tipe = 'Baju' AND (gender = 'Pria' OR gender = 'male' OR gender = '1') THEN 1 ELSE 0 END) AS baju_pria,
    SUM(CASE WHEN tipe = 'Celana' AND (gender = 'Pria' OR gender = 'male' OR gender = '1') THEN 1 ELSE 0 END) AS celana_pria,
    SUM(CASE WHEN tipe = 'Baju' AND (gender = 'Wanita' OR gender = 'female' OR gender = '2') THEN 1 ELSE 0 END) AS baju_wanita,
    SUM(CASE WHEN (tipe = 'Celana' OR tipe = 'Rok') AND (gender = 'Wanita' OR gender = 'female' OR gender = '2') THEN 1 ELSE 0 END) AS celana_wanita
FROM master_item 
WHERE status_transaksi = 'available'
AND status_barang = 'active'"; 

$res_active = $conn->query($sql_active);
if ($res_active) {
    $row = $res_active->fetch_assoc();
    $active_stock['total']        = (int)($row['total'] ?? 0);
    $active_stock['baju_pria']    = (int)($row['baju_pria'] ?? 0);
    $active_stock['celana_pria']  = (int)($row['celana_pria'] ?? 0);
    $active_stock['baju_wanita']  = (int)($row['baju_wanita'] ?? 0);
    $active_stock['celana_wanita'] = (int)($row['celana_wanita'] ?? 0);
}

// ==========================================
// 3. QUERY STOK WAREHOUSE INACTIVE (Status: Inactive / Retur)
// ==========================================
$inactive_stock = [
    'total'        => 0,
    'baju_pria'    => 0,
    'celana_pria'  => 0,
    'baju_wanita'  => 0,
    'celana_wanita' => 0
];

$sql_inactive = "SELECT 
    COUNT(barcode) AS total,
    SUM(CASE WHEN tipe = 'Baju' AND (gender = 'Pria' OR gender = 'male' OR gender = '1') THEN 1 ELSE 0 END) AS baju_pria,
    SUM(CASE WHEN tipe = 'Celana' AND (gender = 'Pria' OR gender = 'male' OR gender = '1') THEN 1 ELSE 0 END) AS celana_pria,
    SUM(CASE WHEN tipe = 'Baju' AND (gender = 'Wanita' OR gender = 'female' OR gender = '2') THEN 1 ELSE 0 END) AS baju_wanita,
    SUM(CASE WHEN (tipe = 'Celana' OR tipe = 'Rok') AND (gender = 'Wanita' OR gender = 'female' OR gender = '2') THEN 1 ELSE 0 END) AS celana_wanita
FROM master_item 
WHERE status_barang = 'inactive'";

$res_inactive = $conn->query($sql_inactive);
if ($res_inactive) {
    $row = $res_inactive->fetch_assoc();
    $inactive_stock['total']        = (int)($row['total'] ?? 0);
    $inactive_stock['baju_pria']    = (int)($row['baju_pria'] ?? 0);
    $inactive_stock['celana_pria']  = (int)($row['celana_pria'] ?? 0);
    $inactive_stock['baju_wanita']  = (int)($row['baju_wanita'] ?? 0);
    $inactive_stock['celana_wanita'] = (int)($row['celana_wanita'] ?? 0);
}

// ==========================================
// 4. PENENTUAN STYLING CARD RETURN (SETELAH DATA DI-FETCH)
// ==========================================
$total_return = (int)($inactive_stock['total'] ?? 0);

if ($total_return >= 15) {
    // 🔴 Merah Gelap (20+)
    $card_style  = 'background-color: #842029; border-color: #842029;';
    $text_class  = 'text-white';
    $unit_class  = 'text-white-50';
} elseif ($total_return >= 8) {
    // 🟠 Merah Sedang (8 - 15)
    $card_style  = 'background-color: #f8d7da; border-color: #f5c2c7;';
    $text_class  = 'text-danger';
    $unit_class  = 'text-secondary';
} else {
    // 🟡 Merah Terang / Soft (< 8)
    $card_style  = 'background-color: #fef2f2; border-color: #fee2e2;';
    $text_class  = 'text-danger';
    $unit_class  = 'text-muted';
}
?>