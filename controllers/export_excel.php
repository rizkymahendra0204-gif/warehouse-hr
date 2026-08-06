<?php
// 1. Barikade Autentikasi
require_once __DIR__ . '/../includes/auth_check.php';

// 2. Load Autoloader Composer & Database PDO
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../includes/db.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

// Tangkap Parameter URL
$start_date = $_GET['start_date'] ?? date('Y-m-01');
$end_date   = $_GET['end_date']   ?? date('Y-m-t');
$type       = $_GET['type']       ?? 'internal'; // 'internal' atau 'finance'

try {
    if ($type === 'finance') {
        // ==========================================
        // QUERY LAPORAN FINANCE
        // ==========================================
        $sql = "SELECT 
                    t.tgl_transaksi,
                    rf.request_id,
                    rf.perusahaan,
                    rf.brand,
                    rf.nama_sa,
                    rf.pembayaran,
                    rf.total_harga AS harga,
                    COUNT(td.barcode) AS total_pcs
                FROM request_form rf
                INNER JOIN transaksi t ON rf.request_id = t.request_id
                INNER JOIN transaksi_detail td ON t.transaction_id = td.transaction_id
                WHERE DATE(t.tgl_transaksi) BETWEEN :start_date AND :end_date
                  AND rf.request_id LIKE 'FR%'
                GROUP BY rf.request_id, t.tgl_transaksi, rf.perusahaan, rf.brand, rf.nama_sa, rf.pembayaran, rf.total_harga
                ORDER BY t.tgl_transaksi DESC";

        $stmt = $pdo->prepare($sql);
        $stmt->execute([':start_date' => $start_date, ':end_date' => $end_date]);
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

    } else {
        // ==========================================
        // QUERY LAPORAN INTERNAL (STOK PER TRANSAKSI)
        // ==========================================
        $sql = "SELECT 
                    t.transaction_id,
                    t.tgl_transaksi,
                    rf.request_id,
                    rf.perusahaan,
                    rf.brand,
                    rf.nama_sa,
                    GROUP_CONCAT(CONCAT(mi.tipe, ' ', mi.gender, ' - Size ', mi.size) SEPARATOR ', ') AS raw_items,
                    ret.raw_returns
                FROM transaksi t
                INNER JOIN request_form rf ON t.request_id = rf.request_id
                INNER JOIN transaksi_detail td ON t.transaction_id = td.transaction_id
                INNER JOIN master_item mi ON TRIM(td.barcode) = TRIM(mi.barcode)
                LEFT JOIN (
                    SELECT 
                        ri.transaction_id,
                        GROUP_CONCAT(CONCAT(mir.tipe, ' ', mir.gender, ' - Size ', mir.size) SEPARATOR ', ') AS raw_returns
                    FROM return_items ri
                    INNER JOIN master_item mir ON TRIM(ri.barcode) = TRIM(mir.barcode)
                    GROUP BY ri.transaction_id
                ) ret ON t.transaction_id = ret.transaction_id
                WHERE DATE(t.tgl_transaksi) BETWEEN :start_date AND :end_date
                GROUP BY 
                    t.transaction_id, 
                    t.tgl_transaksi, 
                    rf.request_id, 
                    rf.perusahaan, 
                    rf.brand, 
                    rf.nama_sa, 
                    ret.raw_returns
                ORDER BY t.tgl_transaksi DESC";

        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':start_date' => $start_date,
            ':end_date'   => $end_date
        ]);
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

} catch (PDOException $e) {
    die("Error Export Excel: " . $e->getMessage());
}

// 3. Inisialisasi PhpSpreadsheet
$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();

// Style Header Umum
$headerStyle = [
    'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
    'fill' => [
        'fillType' => Fill::FILL_SOLID,
        'startColor' => ['rgb' => $type === 'finance' ? '198754' : '0D6EFD']
    ],
    'alignment' => [
        'horizontal' => Alignment::HORIZONTAL_CENTER,
        'vertical'   => Alignment::VERTICAL_CENTER
    ]
];

// 4. Proses Ekspor Berdasarkan Tipe Laporan
if ($type === 'finance') {
    // ------------------------------------------------------------------
    // EXPORT MODE FINANCE
    // ------------------------------------------------------------------
    $sheet->setTitle('Laporan Finance');
    $headers = ['NO', 'TANGGAL', 'ID REQUEST', 'PERUSAHAAN', 'BRAND', 'NAMA SA', 'PEMBAYARAN', 'TOTAL (PCS)', 'TOTAL TAGIHAN (RP)'];
    $sheet->fromArray($headers, NULL, 'A1');
    $sheet->getStyle('A1:I1')->applyFromArray($headerStyle);

    $rowNum = 2;
    if (!empty($data)) {
        $no = 1;
        foreach ($data as $row) {
            $sheet->setCellValue("A{$rowNum}", $no++);
            $sheet->setCellValue("B{$rowNum}", date('d/m/Y', strtotime($row['tgl_transaksi'])));
            $sheet->setCellValue("C{$rowNum}", '#' . $row['request_id']);
            $sheet->setCellValue("D{$rowNum}", $row['perusahaan']);
            $sheet->setCellValue("E{$rowNum}", $row['brand'] ?? '-');
            $sheet->setCellValue("F{$rowNum}", $row['nama_sa']);
            $sheet->setCellValue("G{$rowNum}", $row['pembayaran'] ?? '-');
            $sheet->setCellValue("H{$rowNum}", (int)$row['total_pcs']);
            $sheet->setCellValue("I{$rowNum}", (float)($row['harga'] ?? 0));
            $rowNum++;
        }
        $lastRow = $rowNum - 1;

        $sheet->getStyle("A2:C{$lastRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle("G2:H{$lastRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle("I2:I{$lastRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
        $sheet->getStyle("I2:I{$lastRow}")->getNumberFormat()->setFormatCode('"Rp "#,##0');
        $sheet->getStyle("A1:I{$lastRow}")->applyFromArray(['borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]]]);
    } else {
        $sheet->mergeCells('A2:I2');
        $sheet->setCellValue('A2', 'Tidak ada data transaksi keuangan pada periode ini.');
        $sheet->getStyle('A2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
    }
    $lastCol = 'I';

} else {
    // ------------------------------------------------------------------
    // EXPORT MODE INTERNAL (STOK PER TRANSAKSI - CUKUP 8 KOLOM A-H)
    // ------------------------------------------------------------------
    $sheet->setTitle('Laporan Stok Internal');
    
    // Header 8 Kolom (A sampai H)
    $headers = [
        'NO', 
        'TANGGAL', 
        'ID TRX / REQUEST', 
        'PERUSAHAAN', 
        'BRAND', 
        'NAMA SA', 
        'ITEM DIBERIKAN', 
        'ITEM RETURN'
    ];
    $sheet->fromArray($headers, NULL, 'A1');
    $sheet->getStyle('A1:H1')->applyFromArray($headerStyle);

    $rowNum = 2;
    if (!empty($data)) {
        $no = 1;
        foreach ($data as $row) {
            // Grouping String Item Diberikan
            $raw_items = array_count_values(explode(', ', $row['raw_items']));
            $formatted_items = [];
            foreach ($raw_items as $name => $qty) {
                $formatted_items[] = trim($name) . " ($qty)";
            }
            $str_items = implode(', ', $formatted_items);

            // Grouping String Item Retur
            if (!empty($row['raw_returns'])) {
                $raw_returns = array_count_values(explode(', ', $row['raw_returns']));
                $formatted_returns = [];
                foreach ($raw_returns as $name => $qty) {
                    $formatted_returns[] = trim($name) . " ($qty)";
                }
                $str_returns = implode(', ', $formatted_returns);
            } else {
                $str_returns = '-';
            }

            // Set Data Cell hanya dari Kolom A hingga H
            $sheet->setCellValue("A{$rowNum}", $no++);
            $sheet->setCellValue("B{$rowNum}", date('d/m/Y', strtotime($row['tgl_transaksi'])));
            $sheet->setCellValue("C{$rowNum}", '#' . $row['request_id']);
            $sheet->setCellValue("D{$rowNum}", $row['perusahaan']);
            $sheet->setCellValue("E{$rowNum}", $row['brand'] ?? '-');
            $sheet->setCellValue("F{$rowNum}", $row['nama_sa']);
            $sheet->setCellValue("G{$rowNum}", $str_items);
            $sheet->setCellValue("H{$rowNum}", $str_returns);
            $rowNum++;
        }
        $lastRow = $rowNum - 1;

        // Alignment & Border (Hanya sampai Kolom H)
        $sheet->getStyle("A2:C{$lastRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle("G2:H{$lastRow}")->getAlignment()->setWrapText(true);
        $sheet->getStyle("A1:H{$lastRow}")->applyFromArray(['borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]]]);
    } else {
        $sheet->mergeCells('A2:H2');
        $sheet->setCellValue('A2', 'Tidak ada pergerakan stok pada periode ini.');
        $sheet->getStyle('A2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
    }
    $lastCol = 'H';
}

// Auto-size Lebar Kolom
foreach (range('A', $lastCol) as $col) {
    $sheet->getColumnDimension($col)->setAutoSize(true);
}

// 5. Output Download File (.xlsx)
$filename = "Laporan_" . ucfirst($type) . "_" . date('d-m-Y', strtotime($start_date)) . "_sd_" . date('d-m-Y', strtotime($end_date)) . ".xlsx";

if (ob_get_length()) ob_end_clean();

header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="' . $filename . '"');
header('Cache-Control: max-age=0');

$writer = new Xlsx($spreadsheet);
$writer->save('php://output');
exit;