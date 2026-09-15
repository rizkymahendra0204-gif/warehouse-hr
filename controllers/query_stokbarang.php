<?php
require_once __DIR__ . '/../includes/auth_check.php';
// 1. Panggil koneksi database PDO
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../vendor/autoload.php';

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
$limit = isset($_GET['limit']) ? min(500, max(1, (int)$_GET['limit'])) : 100; // Default 100 data per halaman
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

        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $spreadsheet->getProperties()
            ->setCreator('Warehouse HR')
            ->setTitle('Inventory Stok');

        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Inventory Stok');
        $headings = ['NO', 'BARCODE', 'DETAIL ITEM', 'KATEGORI', 'SIZE', 'STATUS'];
        $sheet->fromArray($headings, null, 'A1');
        $sheet->getStyle('A1:F1')->getFont()->setBold(true);
        $sheet->getStyle('A1:F1')->getFill()
            ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
            ->getStartColor()->setARGB('FFF2F2F2');

        $rowNumber = 2;
        foreach ($export_items as $index => $row) {
            $sheet->setCellValue("A{$rowNumber}", $index + 1);
            $values = [
                'B' => (string)$row['barcode'],
                'C' => (string)$row['tipe'] . ' SA ' . (string)$row['gender'],
                'D' => (string)$row['tipe'],
                'E' => (string)$row['size'],
                'F' => (string)$row['status_transaksi'] . ' (' . (string)$row['status_barang'] . ')',
            ];
            foreach ($values as $column => $value) {
                $sheet->setCellValueExplicit(
                    "{$column}{$rowNumber}",
                    $value,
                    \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING
                );
            }
            $rowNumber++;
        }

        $sheet->freezePane('A2');
        $sheet->setAutoFilter('A1:F' . max(1, $rowNumber - 1));
        foreach (range('A', 'F') as $column) {
            $sheet->getColumnDimension($column)->setAutoSize(true);
        }

        $filename = 'Inventory_Stok_' . date('Ymd_His') . '.xlsx';
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Cache-Control: max-age=0, no-store');
        header('Pragma: public');

        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $writer->save('php://output');
        $spreadsheet->disconnectWorksheets();
        exit;
    } catch (PDOException $e) {
    error_log('[Warehouse HR] ' . $e);
        die("Error Export: " . 'Operasi database gagal. Hubungi administrator.');
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
    error_log('[Warehouse HR] ' . $e);
    die("Error Count Database: " . 'Operasi database gagal. Hubungi administrator.');
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
    error_log('[Warehouse HR] ' . $e);
    die("Error Query Database: " . 'Operasi database gagal. Hubungi administrator.');
}
?>
