<?php
// 1. Panggil koneksi database PDO
require_once __DIR__ . '/../includes/db.php';

if (!isset($pdo)) {
    die("Koneksi PDO tidak ditemukan.");
}

// 2. Tangkap parameter filter, pencarian, dan pagination dari URL
$tab           = $_GET['tab'] ?? 'semua';
$search        = trim($_GET['search'] ?? '');
$filter_tipe   = trim($_GET['tipe'] ?? '');   // Filter Kategori (Baju/Celana)
$filter_gender = trim($_GET['gender'] ?? ''); // Filter Gender (Pria/Wanita)
$filter_size   = trim($_GET['size'] ?? '');   // Filter Ukuran (S, M, L, XL, 30, 32, dll)
$export        = $_GET['export'] ?? '';

// Parameter Pagination
$limit = isset($_GET['limit']) ? max(1, (int)$_GET['limit']) : 100; // Default 100 data per halaman
$page  = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$offset = ($page - 1) * $limit;

// 3. Siapkan penampung kondisi WHERE
$conditions = [];
$params     = [];

// Filter Tab Status
if ($tab === 'available') {
    $conditions[] = "LOWER(status_transaksi) = 'available' AND LOWER(status_barang) = 'active'";
} elseif ($tab === 'soldout') {
    $conditions[] = "(LOWER(status_transaksi) = 'sold out' OR LOWER(status_transaksi) = 'distributed')";
} elseif ($tab === 'inactive') {
    $conditions[] = "LOWER(status_barang) = 'inactive'";
}

// Filter Pencarian Global
if (!empty($search)) {
    $conditions[] = "(barcode LIKE :search OR tipe LIKE :search OR gender LIKE :search OR size LIKE :search)";
    $params[':search'] = '%' . $search . '%';
}

// Filter Spesifik (Judul/Tipe, Gender, Size)
if (!empty($filter_tipe)) {
    $conditions[] = "LOWER(tipe) = :filter_tipe";
    $params[':filter_tipe'] = strtolower($filter_tipe);
}
if (!empty($filter_gender)) {
    $conditions[] = "LOWER(gender) = :filter_gender";
    $params[':filter_gender'] = strtolower($filter_gender);
}
if (!empty($filter_size)) {
    $conditions[] = "LOWER(size) = :filter_size";
    $params[':filter_size'] = strtolower($filter_size);
}

// Menyusun Klausa WHERE
$where_sql = "";
if (count($conditions) > 0) {
    $where_sql = " WHERE " . implode(' AND ', $conditions);
}

// 4. JIKA TOMBOL EXPORT DIKLIK, DOWNLOAD EXCEL (Seluruh Data Tanpa Limit)
if ($export === 'excel') {
    try {
        $sql_export = "SELECT * FROM master_item" . $where_sql . " ORDER BY barcode DESC";
        $stmt = $pdo->prepare($sql_export);
        $stmt->execute($params);
        $export_items = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $filename = "Inventory_Stok_" . date('Ymd_His') . ".xls";
        header("Content-Type: application/vnd.ms-excel");
        header("Content-Disposition: attachment; filename=\"$filename\"");
        header("Pragma: no-cache");
        header("Expires: 0");
        ?>
        <table border="1">
            <thead>
                <tr style="background-color: #f2f2f2; font-weight: bold;">
                    <th>NO</th>
                    <th>BARCODE</th>
                    <th>DETAIL ITEM</th>
                    <th>KATEGORI</th>
                    <th>SIZE</th>
                    <th>STATUS</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($export_items)): $no = 1; foreach ($export_items as $row): ?>
                    <tr>
                        <td style="text-align: center;"><?= $no++ ?></td>
                        <td style="mso-number-format:'\@';">'<?= htmlspecialchars($row['barcode']) ?></td>
                        <td><?= htmlspecialchars($row['tipe'] . ' SA ' . $row['gender']) ?></td>
                        <td><?= htmlspecialchars($row['tipe']) ?></td>
                        <td style="text-align: center;"><?= htmlspecialchars($row['size']) ?></td>
                        <td style="text-align: center;"><?= htmlspecialchars($row['status_transaksi'] . ' (' . $row['status_barang'] . ')') ?></td>
                    </tr>
                <?php endforeach; endif; ?>
            </tbody>
        </table>
        <?php
        exit;
    } catch (PDOException $e) {
        die("Error Export: " . $e->getMessage());
    }
}

// 5. HITUNG TOTAL DATA UNTUK KALKULASI PAGINATION
try {
    $sql_count = "SELECT COUNT(*) FROM master_item" . $where_sql;
    $stmt_count = $pdo->prepare($sql_count);
    $stmt_count->execute($params);
    $total_rows  = (int)$stmt_count->fetchColumn();
    $total_pages = ceil($total_rows / $limit);
} catch (PDOException $e) {
    die("Error Count Database: " . $e->getMessage());
}

// 6. Eksekusi Query dengan LIMIT & OFFSET untuk Tampilan Halaman (View)
try {
    $sql_data = "SELECT * FROM master_item" . $where_sql . " ORDER BY barcode DESC LIMIT :limit OFFSET :offset";
    $stmt_data = $pdo->prepare($sql_data);

    // Bind parameter kondisi WHERE
    foreach ($params as $key => $val) {
        $stmt_data->bindValue($key, $val);
    }
    // Bind parameter integer khusus LIMIT & OFFSET
    $stmt_data->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt_data->bindValue(':offset', $offset, PDO::PARAM_INT);

    $stmt_data->execute();
    $list_item = $stmt_data->fetchAll(PDO::FETCH_ASSOC);

    // Nomor awal urutan tabel pada halaman aktif
    $no_awal = $offset + 1;
} catch (PDOException $e) {
    die("Error Query Database: " . $e->getMessage());
}
?>