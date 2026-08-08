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
                    COALESCE(t.tgl_transaksi, rf.tgl_request) AS tgl_transaksi,
                    rf.request_id,
                    rf.perusahaan,
                    rf.brand,
                    rf.nama_sa,
                    rf.pembayaran,
                    rf.total_harga AS harga,
                    COALESCE(COUNT(td.barcode), 0) AS total_pcs
                FROM request_form rf
                LEFT JOIN transaksi t ON rf.request_id = t.request_id
                LEFT JOIN transaksi_detail td ON t.transaction_id = td.transaction_id
                WHERE DATE(COALESCE(t.tgl_transaksi, rf.tgl_request)) BETWEEN :start_date AND :end_date
                  AND rf.request_id LIKE '%FR%'
                GROUP BY 
                    rf.request_id, 
                    t.transaction_id, 
                    t.tgl_transaksi, 
                    rf.tgl_request, 
                    rf.perusahaan, 
                    rf.brand, 
                    rf.nama_sa, 
                    rf.pembayaran, 
                    rf.total_harga
                ORDER BY COALESCE(t.tgl_transaksi, rf.tgl_request) DESC";

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
                    rf.gender,
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
                  AND rf.request_id LIKE '%FR%'
                GROUP BY 
                    t.transaction_id, 
                    t.tgl_transaksi, 
                    rf.request_id, 
                    rf.perusahaan, 
                    rf.brand, 
                    rf.nama_sa, 
                    rf.gender,
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
    $headers = ['NO', 'TANGGAL', 'NO REQUEST', 'PERUSAHAAN / BRAND', 'NAMA SA', 'METODE PEMBAYARAN', 'TOTAL PCS', 'TOTAL TAGIHAN (RP)'];
    $sheet->fromArray($headers, NULL, 'A1');
    $sheet->getStyle('A1:H1')->applyFromArray($headerStyle);

    $rowNum = 2;
    if (!empty($data)) {
        $no = 1;
        foreach ($data as $row) {
            $req_id = str_replace('#', '', $row['request_id']);
            $perusahaan_brand = $row['perusahaan'] . ' (' . ($row['brand'] ?? '-') . ')';

            $sheet->setCellValue("A{$rowNum}", $no++);
            $sheet->setCellValue("B{$rowNum}", date('d/m/Y', strtotime($row['tgl_transaksi'])));
            $sheet->setCellValue("C{$rowNum}", '#' . $req_id);
            $sheet->setCellValue("D{$rowNum}", $perusahaan_brand);
            $sheet->setCellValue("E{$rowNum}", $row['nama_sa']);
            $sheet->setCellValue("F{$rowNum}", ucfirst($row['pembayaran'] ?? '-'));
            $sheet->setCellValue("G{$rowNum}", (int)$row['total_pcs'] . ' Pcs');
            $sheet->setCellValue("H{$rowNum}", (float)($row['harga'] ?? 0));
            $rowNum++;
        }
        $lastRow = $rowNum - 1;

        $sheet->getStyle("A2:C{$lastRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle("F2:G{$lastRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle("H2:H{$lastRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
        $sheet->getStyle("H2:H{$lastRow}")->getNumberFormat()->setFormatCode('"Rp "#,##0');
        $sheet->getStyle("A1:H{$lastRow}")->applyFromArray(['borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]]]);
    } else {
        $sheet->mergeCells('A2:H2');
        $sheet->setCellValue('A2', 'Tidak ada data transaksi keuangan pada periode ini.');
        $sheet->getStyle('A2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
    }
    $lastCol = 'H';

} else {
    // ------------------------------------------------------------------
    // EXPORT MODE INTERNAL (TERPISAH DENGAN SUB-HEADER TINGKAT GANDA)
    // ------------------------------------------------------------------
    $sheet->setTitle('Laporan Stok Internal');
    
    // 1. Merge Header Atas
    $sheet->mergeCells('A1:A2'); // NO
    $sheet->mergeCells('B1:B2'); // TANGGAL
    $sheet->mergeCells('C1:E1'); // DETAIL PESANAN (Group 3 Kolom)
    $sheet->mergeCells('F1:F2'); // BRAND
    $sheet->mergeCells('G1:G2'); // NAMA SA
    $sheet->mergeCells('H1:I1'); // ITEM DIBERIKAN (Group 2 Kolom)
    $sheet->mergeCells('J1:J2'); // ITEM RETURN

    // 2. Teks Header Baris 1
    $sheet->setCellValue('A1', 'NO');
    $sheet->setCellValue('B1', 'TANGGAL');
    $sheet->setCellValue('C1', 'DETAIL PESANAN');
    $sheet->setCellValue('F1', 'BRAND');
    $sheet->setCellValue('G1', 'NAMA SA');
    $sheet->setCellValue('H1', 'ITEM DIBERIKAN');
    $sheet->setCellValue('J1', 'ITEM RETURN');

    // 3. Teks Sub-Header Baris 2
    $sheet->setCellValue('C2', 'PERUSAHAAN');
    $sheet->setCellValue('D2', 'SERAGAM');
    $sheet->setCellValue('E2', 'ITEM REQUEST');
    $sheet->setCellValue('H2', 'RINCIAN ITEM');
    $sheet->setCellValue('I2', 'TOTAL PCS');

    // Apply Style Header Baris 1 & 2
    $sheet->getStyle('A1:J2')->applyFromArray($headerStyle);

    // 4. Pengisian Data (Mulai Baris ke-3)
    $rowNum = 3;
    if (!empty($data)) {
        $no = 1;
        foreach ($data as $row) {
            // Processing Item Diberikan
            $raw_items = array_count_values(array_filter(explode(', ', $row['raw_items'] ?? '')));
            $formatted_items = [];
            foreach ($raw_items as $name => $qty) {
                $formatted_items[] = trim($name) . " ($qty)";
            }
            $str_items = !empty($formatted_items) ? implode(', ', $formatted_items) : '-';
            $total_pcs_diberikan = array_sum($raw_items);

            // Processing Item Retur
            if (!empty($row['raw_returns'])) {
                $raw_returns = array_count_values(array_filter(explode(', ', $row['raw_returns'])));
                $formatted_returns = [];
                foreach ($raw_returns as $name => $qty) {
                    $formatted_returns[] = trim($name) . " ($qty)";
                }
                $str_returns = implode(', ', $formatted_returns);
            } else {
                $str_returns = '-';
            }

            // Processing Gender (Disertai Prefix 'Seragam SA')
            $raw_gender = strtolower(trim($row['gender'] ?? ''));
            $gender_txt = 'Seragam ' . (($raw_gender === 'male' || $raw_gender === 'pria' || $raw_gender === '1') ? 'SA Pria' : 'SA Wanita');

            // Set Data Cell ke Kolom Masing-Masing
            $sheet->setCellValue("A{$rowNum}", $no++);
            $sheet->setCellValue("B{$rowNum}", date('d/m/Y', strtotime($row['tgl_transaksi'])));
            $sheet->setCellValue("C{$rowNum}", $row['perusahaan']);
            $sheet->setCellValue("D{$rowNum}", $gender_txt);
            $sheet->setCellValue("E{$rowNum}", $row['raw_items'] ?? '-');
            $sheet->setCellValue("F{$rowNum}", $row['brand'] ?? '-');
            $sheet->setCellValue("G{$rowNum}", $row['nama_sa']);
            $sheet->setCellValue("H{$rowNum}", $str_items);
            $sheet->setCellValue("I{$rowNum}", $total_pcs_diberikan . ' Pcs');
            $sheet->setCellValue("J{$rowNum}", $str_returns);
            $rowNum++;
        }
        $lastRow = $rowNum - 1;

        // Alignment & Formatting Data
        $sheet->getStyle("A3:B{$lastRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle("D3:D{$lastRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle("I3:I{$lastRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle("C3:C{$lastRow}")->getAlignment()->setWrapText(true);
        $sheet->getStyle("E3:H{$lastRow}")->getAlignment()->setWrapText(true);
        $sheet->getStyle("J3:J{$lastRow}")->getAlignment()->setWrapText(true);
        $sheet->getStyle("A1:J{$lastRow}")->applyFromArray(['borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]]]);
    } else {
        $sheet->mergeCells('A3:J3');
        $sheet->setCellValue('A3', 'Tidak ada pergerakan stok pada periode ini.');
        $sheet->getStyle('A3')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
    }
    $lastCol = 'J';
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