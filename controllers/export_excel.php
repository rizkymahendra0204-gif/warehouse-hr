<?php
// 1. Barikade Autentikasi (Cek session & login)
require_once __DIR__ . '/../includes/auth_check.php';

// 2. Load Autoloader Composer & Database PDO
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../includes/db.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;

// Tangkap filter tanggal dari URL
$start_date = $_GET['start_date'] ?? date('Y-m-01');
$end_date   = $_GET['end_date']   ?? date('Y-m-t');

try {
    // Query mengambil data transaksi keluar (Disamakan dengan Controller Laporan)
    $sql_table = "SELECT 
                t.tgl_transaksi,
                t.transaction_id,
                rf.request_id,
                rf.perusahaan,
                rf.brand,
                rf.nama_sa,
                GROUP_CONCAT(CONCAT(mi.tipe, ' ', mi.gender, ' - Size ', mi.size) SEPARATOR ', ') AS all_items,
                (COUNT(td.barcode) - COALESCE(ret.qty_return, 0)) AS total_pcs,
                rf.total_harga AS harga,
                rf.pembayaran,
                ret.items_returned_raw
            FROM transaksi t
            INNER JOIN transaksi_detail td ON t.transaction_id = td.transaction_id
            INNER JOIN request_form rf ON t.request_id = rf.request_id
            INNER JOIN master_item mi ON TRIM(td.barcode) = TRIM(mi.barcode)
            LEFT JOIN (
                SELECT 
                    ri.transaction_id,
                    GROUP_CONCAT(CONCAT(mir.tipe, ' ', mir.gender, ' - Size ', mir.size) SEPARATOR ', ') AS items_returned_raw,
                    COUNT(ri.barcode) AS qty_return
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
                ret.items_returned_raw, 
                ret.qty_return
            ORDER BY t.tgl_transaksi DESC";

    $stmt = $pdo->prepare($sql_table);
    $stmt->execute([
        ':start_date' => $start_date,
        ':end_date'   => $end_date
    ]);
    $list_transaksi = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    die("Error Export Excel: " . $e->getMessage());
}

// 3. Inisialisasi PhpSpreadsheet
$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();
$sheet->setTitle('Laporan Transaksi');

// 4. Set Header Kolom (Total 11 Kolom: A-K)
$headers = [
    'NO', 
    'TANGGAL', 
    'ID TRX', 
    'PERUSAHAAN', 
    'BRAND', 
    'NAMA SA', 
    'ITEM DIBERIKAN', 
    'ITEM RETURN', 
    'TOTAL (PCS)', 
    'VALUE (RP)', 
    'PEMBAYARAN'
];
$sheet->fromArray($headers, NULL, 'A1');

// Style Header (Warna Hijau #198754, Teks Putih Bold, Tengah)
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
$sheet->getStyle('A1:K1')->applyFromArray($headerStyle);

// 5. Isi Data ke Spreadsheet
$rowNum = 2; // Data dimulai dari baris ke-2

if (!empty($list_transaksi)) {
    $no = 1;
    foreach ($list_transaksi as $row) {
        $tgl = date('d/m/Y', strtotime($row['tgl_transaksi']));

        // Grouping Item Diberikan
        $raw_items_array = explode(',', $row['all_items']);
        $item_counts = array_count_values($raw_items_array);

        $formatted_items = [];
        foreach ($item_counts as $nama_item => $jumlah) {
            $formatted_items[] = trim($nama_item) . " ($jumlah)";
        }
        $string_item_diberikan = implode(', ', $formatted_items);

        // Grouping Item Return
        if (!empty($row['items_returned_raw'])) {
            $raw_returns_array = explode(',', $row['items_returned_raw']);
            $return_counts = array_count_values($raw_returns_array);

            $formatted_returns = [];
            foreach ($return_counts as $nama_item => $jumlah) {
                $formatted_returns[] = trim($nama_item) . " ($jumlah)";
            }
            $string_item_direturn = implode(', ', $formatted_returns);
        } else {
            $string_item_direturn = '-';
        }

        // Nilai Harga & Pembayaran
        $harga_val = (float)($row['harga'] ?? 0);
        $pembayaran_val = !empty($row['pembayaran']) ? $row['pembayaran'] : '-';

        // Masukkan data ke cell (A-K)
        $sheet->setCellValue("A{$rowNum}", $no++);
        $sheet->setCellValue("B{$rowNum}", $tgl);
        $sheet->setCellValue("C{$rowNum}", '#' . $row['transaction_id']);
        $sheet->setCellValue("D{$rowNum}", $row['perusahaan']);
        $sheet->setCellValue("E{$rowNum}", $row['brand'] ?? '-');
        $sheet->setCellValue("F{$rowNum}", $row['nama_sa']);
        $sheet->setCellValue("G{$rowNum}", $string_item_diberikan);
        $sheet->setCellValue("H{$rowNum}", $string_item_direturn);
        $sheet->setCellValue("I{$rowNum}", (int)$row['total_pcs']);
        $sheet->setCellValue("J{$rowNum}", $harga_val);
        $sheet->setCellValue("K{$rowNum}", $pembayaran_val);

        $rowNum++;
    }

    $lastRow = $rowNum - 1;

    // Formatting alignment & style isi tabel
    $sheet->getStyle("A2:A{$lastRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
    $sheet->getStyle("B2:B{$lastRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
    $sheet->getStyle("C2:C{$lastRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
    $sheet->getStyle("I2:I{$lastRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
    $sheet->getStyle("I2:I{$lastRow}")->getFont()->setBold(true);
    
    // Formatting Kolom Value (Rp)
    $sheet->getStyle("J2:J{$lastRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
    $sheet->getStyle("J2:J{$lastRow}")->getNumberFormat()->setFormatCode('"Rp "#,##0');
    
    // Formatting Kolom Pembayaran
    $sheet->getStyle("K2:K{$lastRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

    // Text Wrapping untuk kolom daftar item agar rapi
    $sheet->getStyle("G2:H{$lastRow}")->getAlignment()->setWrapText(true);

    // Border untuk seluruh tabel A1:K{$lastRow}
    $borderStyle = [
        'borders' => [
            'allBorders' => [
                'borderStyle' => Border::BORDER_THIN,
                'color' => ['rgb' => '000000']
            ]
        ]
    ];
    $sheet->getStyle("A1:K{$lastRow}")->applyFromArray($borderStyle);

} else {
    // Jika data kosong merge A2:K2
    $sheet->mergeCells('A2:K2');
    $sheet->setCellValue('A2', 'Tidak ada transaksi pada periode tanggal ini.');
    $sheet->getStyle('A2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
}

// Auto-size Lebar Kolom A sampai K
foreach (range('A', 'K') as $col) {
    $sheet->getColumnDimension($col)->setAutoSize(true);
}

// 6. Header Download File (.xlsx)
$filename = "Laporan_Transaksi_" . date('d-m-Y', strtotime($start_date)) . "_sd_" . date('d-m-Y', strtotime($end_date)) . ".xlsx";

// Bersihkan output buffer
if (ob_get_length()) ob_end_clean();

header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="' . $filename . '"');
header('Cache-Control: max-age=0');

$writer = new Xlsx($spreadsheet);
$writer->save('php://output');
exit;