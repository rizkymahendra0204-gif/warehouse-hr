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
// 2. QUERY GRAFIK STOK WAREHOUSE ACTIVE (Status: Tersedia)
// ==========================================
$active_stock = ['baju_pria' => 0, 'cln_pria' => 0, 'baju_wnt' => 0, 'cln_wnt' => 0];
$sql_active = "SELECT 
    SUM(CASE WHEN tipe = 'Baju' AND gender = 'Pria' THEN 1 ELSE 0 END) as baju_pria,
    SUM(CASE WHEN tipe = 'Celana' AND gender = 'Pria' THEN 1 ELSE 0 END) as cln_pria,
    SUM(CASE WHEN tipe = 'Baju' AND gender = 'Wanita' THEN 1 ELSE 0 END) as baju_wnt,
    SUM(CASE WHEN tipe = 'Celana' AND gender = 'Wanita' THEN 1 ELSE 0 END) as cln_wnt
    FROM master_item WHERE status_transaksi = 'Available'";

$res_active = $conn->query($sql_active);
if ($res_active) {
    $row = $res_active->fetch_assoc();
    $active_stock['baju_pria'] = (int)($row['baju_pria'] ?? 0);
    $active_stock['cln_pria'] = (int)($row['cln_pria'] ?? 0);
    $active_stock['baju_wnt'] = (int)($row['baju_wnt'] ?? 0);
    $active_stock['cln_wnt'] = (int)($row['cln_wnt'] ?? 0);
}

// Hitung persentase tinggi grafik Active secara proporsional
$max_active = max(1, max($active_stock));
$h_active_baju_pria = ($active_stock['baju_pria'] / $max_active) * 100;
$h_active_cln_pria = ($active_stock['cln_pria'] / $max_active) * 100;
$h_active_baju_wnt  = ($active_stock['baju_wnt'] / $max_active) * 100;
$h_active_cln_wnt  = ($active_stock['cln_wnt'] / $max_active) * 100;


// ==========================================
// 3. QUERY GRAFIK STOK WAREHOUSE INACTIVE (Status: Nonaktif)
// ==========================================
$inactive_stock = ['baju_pria' => 0, 'cln_pria' => 0, 'baju_wnt' => 0, 'cln_wnt' => 0];
$sql_inactive = "SELECT 
    SUM(CASE WHEN tipe = 'Baju' AND gender = 'Pria' THEN 1 ELSE 0 END) as baju_pria,
    SUM(CASE WHEN tipe = 'Celana' AND gender = 'Pria' THEN 1 ELSE 0 END) as cln_pria,
    SUM(CASE WHEN tipe = 'Baju' AND gender = 'Wanita' THEN 1 ELSE 0 END) as baju_wnt,
    SUM(CASE WHEN (tipe = 'Celana' OR tipe = 'Rok') AND gender = 'Wanita' THEN 1 ELSE 0 END) as cln_wnt
    FROM master_item WHERE status_barang = 'Inactive'";

$res_inactive = $conn->query($sql_inactive);
if ($res_inactive) {
    $row = $res_inactive->fetch_assoc();
    $inactive_stock['baju_pria'] = (int)($row['baju_pria'] ?? 0);
    $inactive_stock['cln_pria'] = (int)($row['cln_pria'] ?? 0);
    $inactive_stock['baju_wnt'] = (int)($row['baju_wnt'] ?? 0);
    $inactive_stock['cln_wnt'] = (int)($row['cln_wnt'] ?? 0);
}

// Hitung persentase tinggi grafik Inactive secara proporsional
$max_inactive = max(1, max($inactive_stock));
$h_inactive_baju_pria = ($inactive_stock['baju_pria'] / $max_inactive) * 100;
$h_inactive_cln_pria = ($inactive_stock['cln_pria'] / $max_inactive) * 100;
$h_inactive_baju_wnt  = ($inactive_stock['baju_wnt'] / $max_inactive) * 100;
$h_inactive_cln_wnt  = ($inactive_stock['cln_wnt'] / $max_inactive) * 100;
?>