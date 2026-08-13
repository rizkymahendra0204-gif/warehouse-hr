<?php
// 1. Panggil koneksi database PDO
require_once __DIR__ . '/../includes/db.php';

if (!isset($pdo)) {
    die("Koneksi PDO tidak ditemukan.");
}

// 2. Tangkap parameter filter dan pencarian dari URL
$tab    = $_GET['tab'] ?? 'semua';
$search = trim($_GET['search'] ?? '');
$export = $_GET['export'] ?? '';

// 3. Siapkan penampung kondisi WHERE
$conditions = [];
$params     = [];

if ($tab === 'available') {
    $conditions[] = "LOWER(status_transaksi) = 'available' AND LOWER(status_barang) = 'active'";
} elseif ($tab === 'soldout') {
    $conditions[] = "(LOWER(status_transaksi) = 'sold out' OR LOWER(status_transaksi) = 'distributed')";
} elseif ($tab === 'inactive') {
    $conditions[] = "LOWER(status_barang) = 'inactive'";
}

if (!empty($search)) {
    $conditions[] = "(barcode LIKE :search OR tipe LIKE :search OR gender LIKE :search OR size LIKE :search)";
    $params[':search'] = '%' . $search . '%';
}

$sql = "SELECT * FROM master_item";
if (count($conditions) > 0) {
    $sql .= " WHERE " . implode(' AND ', $conditions);
}
$sql .= " ORDER BY barcode DESC";

// 4. JIKA TOMBOL EXPORT DIKLIK, DOWNLOAD EXCEL
if ($export === 'excel') {
    try {
        $stmt = $pdo->prepare($sql);
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

// 5. Eksekusi Query Normal untuk Tampilan Halaman (View)
try {
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $list_item = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Error Query Database: " . $e->getMessage());
}
?>