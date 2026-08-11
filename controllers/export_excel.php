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
use PhpOffice\PhpSpreadsheet\Cell\DataType;

// 3. Tangkap Parameter URL
$type = $_GET['type'] ?? '';

// AUTO-DETECT: Jika ada parameter gender, tipe, dan ukuran, PAKSA mode ke 'barcode'
if (isset($_GET['gender']) && isset($_GET['tipe']) && isset($_GET['ukuran'])) {
    $type = 'barcode';
}

// 4. Inisialisasi PhpSpreadsheet
$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();

// =========================================================================
// OPSI 1: EXPORT STOCKCARD MASTER BARCODE (type = barcode / stockcard)
// =========================================================================
if ($type === 'barcode' || $type === 'stockcard') {
    $gender     = trim($_GET['gender']     ?? '');
    $tipe       = trim($_GET['tipe']       ?? '');
    $ukuran     = trim($_GET['ukuran']     ?? '');
    $range_awal = (int)($_GET['range_awal'] ?? 1);
    $range_akhir= (int)($_GET['range_akhir'] ?? $range_awal);

    if (empty($gender) || empty($tipe) || empty($ukuran)) {
        die("Error: Parameter (Gender, Tipe, Ukuran) tidak lengkap untuk melakukan export barcode!");
    }

    // Pemetaan Kode Komponen Barcode 9 Digit
    $genderMap  = ['Pria' => '1', 'Wanita' => '2', '1' => '1', '2' => '2'];
    $tipeMap    = ['Baju' => '01', 'Celana' => '02', '01' => '01', '02' => '02'];
    $sizeMap    = ['S' => '01', 'M' => '02', 'L' => '03', 'XL' => '04', '01' => '01', '02' => '02', '03' => '03', '04' => '04'];

    $genderCode = $genderMap[$gender] ?? '1';
    $tipeCode   = $tipeMap[$tipe] ?? '01';
    $sizeCode   = $sizeMap[$ukuran] ?? $ukuran;

    $genderText = ($genderCode === '1') ? 'Pria' : 'Wanita';
    $tipeText   = ($tipeCode === '01') ? 'Baju' : 'Celana';

    // Normalisasi Label Ukuran untuk Keterangan
    $sizeLabels  = ['01' => 'S', '02' => 'M', '03' => 'L', '04' => 'XL'];
    $ukuranLabel = $sizeLabels[$sizeCode] ?? $ukuran;

    $sheet->setTitle('Stockcard Barcode');

    // 1. Header Judul Stockcard (Merge A1 s/d D1)
    $sheet->mergeCells('A1:D1');
    $sheet->setCellValue('A1', 'STOCKCARD BARCODE');
    $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
    $sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

    // 2. Informasi Parameter Header
    $sheet->setCellValue('A3', 'Tipe Pakaian');
    $sheet->setCellValue('B3', ': ' . $tipeText);
    $sheet->setCellValue('A4', 'Gender');
    $sheet->setCellValue('B4', ': ' . $genderText);
    $sheet->setCellValue('A5', 'Ukuran');
    $sheet->setCellValue('B5', ': ' . $ukuranLabel);
    $sheet->getStyle('A3:A5')->getFont()->setBold(true);

    // 3. Header Tabel (Sesuai Layout Gambar)
    $headerStyle = [
        'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
        'fill' => [
            'fillType' => Fill::FILL_SOLID,
            'startColor' => ['rgb' => '198754'] // Hijau Stockcard
        ],
        'alignment' => [
            'horizontal' => Alignment::HORIZONTAL_CENTER,
            'vertical'   => Alignment::VERTICAL_CENTER
        ]
    ];

    $headers = ['NO', 'KODE BARCODE (9 DIGIT)', 'TGL KELUAR', 'PARAF'];
    $sheet->fromArray($headers, NULL, 'A7');
    $sheet->getStyle('A7:D7')->applyFromArray($headerStyle);

    // 4. Loop Generate Data Barcode & Baris Kosong Catatan Manual
    $rowNum = 8;
    $no = 1;

    for ($i = $range_awal; $i <= $range_akhir; $i++) {
        $runningNum  = sprintf("%04d", $i);
        $fullBarcode = $genderCode . $tipeCode . $sizeCode . $runningNum;
        
        // Gabungkan nomor barcode dan detail di bawahnya dengan baris baru (\n)
        $cellBarcodeValue = $fullBarcode . "\n(" . $tipeText . " " . $genderText . " " . $ukuranLabel . ")";

        $sheet->setCellValue("A{$rowNum}", $no++);
        
        // Masukkan sebagai explicit string agar format teks multi-baris aman
        $sheet->setCellValueExplicit("B{$rowNum}", $cellBarcodeValue, DataType::TYPE_STRING);
        
        // Kolom C (TGL KELUAR) dan D (PARAF) dibiarkan kosong
        $sheet->setCellValue("C{$rowNum}", "");
        $sheet->setCellValue("D{$rowNum}", "");

        // Atur tinggi baris agar teks 2 baris muat dengan rapi dan tidak terpotong
        $sheet->getRowDimension($rowNum)->setRowHeight(32);

        $rowNum++;
    }

    $lastRow = $rowNum - 1;

    // 5. Alignment & Formatting Data
    $sheet->getStyle("A8:A{$lastRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
    $sheet->getStyle("A8:A{$lastRow}")->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
    
    // Format khusus Kolom B (Barcode + Detail) agar teks terbungkus rapi secara vertikal & horizontal
    $sheet->getStyle("B8:B{$lastRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
    $sheet->getStyle("B8:B{$lastRow}")->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
    $sheet->getStyle("B8:B{$lastRow}")->getAlignment()->setWrapText(true); // Wajib agar \n berfungsi
    $sheet->getStyle("B8:B{$lastRow}")->getNumberFormat()->setFormatCode('@');

    // 6. Apply Borders
    $sheet->getStyle("A7:D{$lastRow}")->applyFromArray([
        'borders' => [
            'allBorders' => [
                'borderStyle' => Border::BORDER_THIN,
                'color' => ['rgb' => 'D3D3D3']
            ]
        ]
    ]);

    // 7. Pengaturan Lebar Kolom
    $sheet->getColumnDimension('A')->setAutoSize(true);
    $sheet->getColumnDimension('B')->setWidth(25); // Dilebarkan sedikit agar teks detail muat dalam 1 baris
    $sheet->getColumnDimension('C')->setWidth(20); // TGL KELUAR
    $sheet->getColumnDimension('D')->setWidth(20); // PARAF

    $lastCol = 'D';
    $filename = "Stockcard_Barcode_" . $tipeText . "_" . $genderText . "_" . date('Ymd_His') . ".xlsx";

// =========================================================================
// OPSI 2: EXPORT LAPORAN FINANCE (type = finance)
// =========================================================================
} elseif ($type === 'finance') {
    $start_date = $_GET['start_date'] ?? date('Y-m-01');
    $end_date   = $_GET['end_date']   ?? date('Y-m-t');

    try {
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
    } catch (PDOException $e) {
        die("Error Export Finance: " . $e->getMessage());
    }

    $headerStyle = [
        'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
        'fill' => [
            'fillType' => Fill::FILL_SOLID,
            'startColor' => ['rgb' => '198754']
        ],
        'alignment' => [
            'horizontal' => Alignment::HORIZONTAL_CENTER,
            'vertical'   => Alignment::VERTICAL_CENTER
        ]
    ];

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
    $filename = "Laporan_Finance_" . date('d-m-Y', strtotime($start_date)) . "_sd_" . date('d-m-Y', strtotime($end_date)) . ".xlsx";

// =========================================================================
// OPSI 3: EXPORT LAPORAN STOK INTERNAL (type = internal)
// =========================================================================
} else {
    $start_date = $_GET['start_date'] ?? date('Y-m-01');
    $end_date   = $_GET['end_date']   ?? date('Y-m-t');

    try {
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
        $stmt->execute([':start_date' => $start_date, ':end_date' => $end_date]);
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        die("Error Export Internal: " . $e->getMessage());
    }

    $headerStyle = [
        'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
        'fill' => [
            'fillType' => Fill::FILL_SOLID,
            'startColor' => ['rgb' => '0D6EFD']
        ],
        'alignment' => [
            'horizontal' => Alignment::HORIZONTAL_CENTER,
            'vertical'   => Alignment::VERTICAL_CENTER
        ]
    ];

    $sheet->setTitle('Laporan Stok Internal');
    
    // Header Tingkat Ganda
    $sheet->mergeCells('A1:A2');
    $sheet->mergeCells('B1:B2');
    $sheet->mergeCells('C1:E1');
    $sheet->mergeCells('F1:F2');
    $sheet->mergeCells('G1:G2');
    $sheet->mergeCells('H1:I1');
    $sheet->mergeCells('J1:J2');

    $sheet->setCellValue('A1', 'NO');
    $sheet->setCellValue('B1', 'TANGGAL');
    $sheet->setCellValue('C1', 'DETAIL PESANAN');
    $sheet->setCellValue('F1', 'BRAND');
    $sheet->setCellValue('G1', 'NAMA SA');
    $sheet->setCellValue('H1', 'ITEM DIBERIKAN');
    $sheet->setCellValue('J1', 'ITEM RETURN');

    $sheet->setCellValue('C2', 'PERUSAHAAN');
    $sheet->setCellValue('D2', 'SERAGAM');
    $sheet->setCellValue('E2', 'ITEM REQUEST');
    $sheet->setCellValue('H2', 'RINCIAN ITEM');
    $sheet->setCellValue('I2', 'TOTAL PCS');

    $sheet->getStyle('A1:J2')->applyFromArray($headerStyle);

    $rowNum = 3;
    if (!empty($data)) {
        $no = 1;
        foreach ($data as $row) {
            $raw_items = array_count_values(array_filter(explode(', ', $row['raw_items'] ?? '')));
            $formatted_items = [];
            foreach ($raw_items as $name => $qty) {
                $formatted_items[] = trim($name) . " ($qty)";
            }
            $str_items = !empty($formatted_items) ? implode(', ', $formatted_items) : '-';
            $total_pcs_diberikan = array_sum($raw_items);

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

            $raw_gender = strtolower(trim($row['gender'] ?? ''));
            $gender_txt = 'Seragam ' . (($raw_gender === 'male' || $raw_gender === 'pria' || $raw_gender === '1') ? 'SA Pria' : 'SA Wanita');

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
    $filename = "Laporan_Internal_" . date('d-m-Y', strtotime($start_date)) . "_sd_" . date('d-m-Y', strtotime($end_date)) . ".xlsx";
}

// Auto-size Lebar Seluruh Kolom
foreach (range('A', $lastCol) as $col) {
    $sheet->getColumnDimension($col)->setAutoSize(true);
}

// Output Download (.xlsx)
if (ob_get_length()) ob_end_clean();

header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="' . $filename . '"');
header('Cache-Control: max-age=0');

$writer = new Xlsx($spreadsheet);
$writer->save('php://output');
exit;