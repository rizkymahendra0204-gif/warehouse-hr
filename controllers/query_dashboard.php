<?php
// dashboard.php
include 'includes/db.php';

$conn = new mysqli("localhost", "root", "", "db_warehouse");

// ==========================================
// 1. QUERY MENGHITUNG DATA KPI CARDS
// ==========================================

// KPI 1: Pending Request (Menghitung baris di tabel request_form)
$kpi_pending = 0;
$res_pending = $conn->query("SELECT COUNT(*) as total FROM request_form WHERE status = 'Pending'");
if ($res_pending) {
    $row = $res_pending->fetch_assoc();
    $kpi_pending = (int)$row['total'];
}

// KPI 2: Total Transaksi (Menghitung jumlah total item/barcode unik yang sudah didistribusikan)
$kpi_transaksi = 0;
$res_transaksi = $conn->query("SELECT COUNT(DISTINCT transaction_id) as total FROM transaksi");
if ($res_transaksi) {
    $row = $res_transaksi->fetch_assoc();
    $kpi_transaksi = (int)$row['total'];
}

// KPI 3: Total Return (Silakan sesuaikan nama tabel 'transaksi_return' jika berbeda di database Anda)
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
    'total'     => 0,
    'baju_pria' => 0,
    'celana_pria'  => 0,
    'baju_wanita'  => 0,
    'celana_wanita'   => 0
];

$sql_active = "SELECT 
    COUNT(barcode) AS total,
    SUM(CASE WHEN tipe = 'Baju' AND (gender = 'Pria' OR gender = 'male' OR gender = '1') THEN 1 ELSE 0 END) AS baju_pria,
    SUM(CASE WHEN tipe = 'Celana' AND (gender = 'Pria' OR gender = 'male' OR gender = '1') THEN 1 ELSE 0 END) AS celana_pria,
    SUM(CASE WHEN tipe = 'Baju' AND (gender = 'Wanita' OR gender = 'female' OR gender = '2') THEN 1 ELSE 0 END) AS baju_wanita,
    SUM(CASE WHEN (tipe = 'Celana' OR tipe = 'Rok') AND (gender = 'Wanita' OR gender = 'female' OR gender = '2') THEN 1 ELSE 0 END) AS celana_wanita
FROM master_item 
WHERE status_transaksi = 'available'";

$res_active = $conn->query($sql_active);
if ($res_active) {
    $row = $res_active->fetch_assoc();
    $active_stock['total']     = (int)($row['total'] ?? 0);
    $active_stock['baju_pria'] = (int)($row['baju_pria'] ?? 0);
    $active_stock['celana_pria']  = (int)($row['celana_pria'] ?? 0);
    $active_stock['baju_wanita']  = (int)($row['baju_wanita'] ?? 0);
    $active_stock['celana_wanita']   = (int)($row['celana_wanita'] ?? 0);
}


// ==========================================
// 3. QUERY STOK WAREHOUSE INACTIVE (Status: Inactive / Retur)
// ==========================================
$inactive_stock = [
    'total'     => 0,
    'baju_pria' => 0,
    'celana_pria'  => 0,
    'baju_wanita'  => 0,
    'celana_wanita'   => 0
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
    $inactive_stock['total']     = (int)($row['total'] ?? 0);
    $inactive_stock['baju_pria'] = (int)($row['baju_pria'] ?? 0);
    $inactive_stock['celana_pria']  = (int)($row['celana_pria'] ?? 0);
    $inactive_stock['baju_wanita']  = (int)($row['baju_wanita'] ?? 0);
    $inactive_stock['celana_wanita']   = (int)($row['celana_wanita'] ?? 0);
}
?>