<?php
session_start();

// 1. Load Autoloader Composer & Database
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../includes/db.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

// Penanganan nama variabel koneksi PDO
if (!isset($conn) && isset($pdo)) {
    $conn = $pdo;
}

// Tangkap filter tanggal dari URL
$start_date = isset($_GET['start_date']) ? $_GET['start_date'] : date('Y-m-01');
$end_date   = isset($_GET['end_date']) ? $_GET['end_date'] : date('Y-m-t');

try {
    // Query mengambil data transaksi keluar sesuai filter tanggal
    $sql_table = "SELECT 
                t.tgl_transaksi,
                t.transaction_id,
                rf.request_id,
                rf.perusahaan,
                rf.nama_sa,
                GROUP_CONCAT(CONCAT(mi.tipe, ' ', mi.gender) SEPARATOR ',') AS all_items,
                (COUNT(t.barcode) - COALESCE(ret.qty_return, 0)) AS total_pcs,
                ret.items_returned_raw
            FROM transaksi t
            INNER JOIN request_form rf ON t.request_id = rf.request_id
            INNER JOIN master_item mi ON TRIM(t.barcode) = TRIM(mi.barcode)
            LEFT JOIN (
                SELECT 
                    ri.transaction_id,
                    GROUP_CONCAT(CONCAT(mir.tipe, ' ', mir.gender) SEPARATOR ',') AS items_returned_raw,
                    COUNT(ri.barcode) AS qty_return
                FROM return_items ri
                INNER JOIN master_item mir ON TRIM(ri.barcode) = TRIM(mir.barcode)
                GROUP BY ri.transaction_id
            ) ret ON t.transaction_id = ret.transaction_id
            WHERE DATE(t.tgl_transaksi) BETWEEN :start_date AND :end_date
            GROUP BY t.transaction_id
            ORDER BY t.tgl_transaksi DESC";

    $stmt = $conn->prepare($sql_table);
    $stmt->execute([
        ':start_date' => $start_date,
        ':end_date'   => $end_date
    ]);
    $list_transaksi = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    die("Error Export: " . $e->getMessage());
}

// 2. Inisialisasi PhpSpreadsheet
$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();
$sheet->setTitle('Laporan Transaksi');

// 3. Set Header Kolom (Total 9 Kolom: A-I)
$headers = ['NO', 'TANGGAL', 'ID TRX', 'PERUSAHAAN', 'NAMA SA', 'ITEM DIBERIKAN', 'ITEM RETURN', 'TOTAL (PCS)'];
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
        'vertical' => Alignment::VERTICAL_CENTER
    ]
];
// FIX: Range disesuaikan sampai I1
$sheet->getStyle('A1:I1')->applyFromArray($headerStyle);

// 4. Isi Data ke Spreadsheet
$rowNum = 2; // Data dimulai dari baris ke-2

if (!empty($list_transaksi)) {
    $no = 1;
    foreach ($list_transaksi as $row) {
        $tgl = date('d/m/Y', strtotime($row['tgl_transaksi']));

        // Format Item Diberikan ("Celana Pria (1), Baju Pria (1)")
        $raw_items_array = explode(',', $row['all_items']);
        $item_counts = array_count_values($raw_items_array);

        $formatted_items = [];
        foreach ($item_counts as $nama_item => $jumlah) {
            $formatted_items[] = trim($nama_item) . " ($jumlah)";
        }
        $string_item_diberikan = implode(', ', $formatted_items);

        // FIX: Format Item Di-Return jika ada
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

        // Masukkan data ke cell
        $sheet->setCellValue("A{$rowNum}", $no++);
        $sheet->setCellValue("B{$rowNum}", $tgl);
        $sheet->setCellValue("C{$rowNum}", '#' . $row['transaction_id']);
        $sheet->setCellValue("D{$rowNum}", $row['perusahaan']);
        $sheet->setCellValue("E{$rowNum}", $row['nama_sa']);
        $sheet->setCellValue("F{$rowNum}", $string_item_diberikan);
        $sheet->setCellValue("G{$rowNum}", $string_item_direturn);
        $sheet->setCellValue("H{$rowNum}", $row['total_pcs']);

        $rowNum++;
    }

    $lastRow = $rowNum - 1;

    // FIX 2: Formatting alignment & style isi tabel (Kolom A, B, H rata tengah)
    $sheet->getStyle("A2:A{$lastRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
    $sheet->getStyle("B2:B{$lastRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
    $sheet->getStyle("H2:H{$lastRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
    $sheet->getStyle("H2:H{$lastRow}")->getFont()->setBold(true); // Total (PCS) tebal

    // FIX 2: Border untuk seluruh tabel A1:H
    $borderStyle = [
        'borders' => [
            'allBorders' => [
                'borderStyle' => Border::BORDER_THIN,
                'color' => ['rgb' => '000000']
            ]
        ]
    ];
    $sheet->getStyle("A1:H{$lastRow}")->applyFromArray($borderStyle);

} else {
    // FIX 2: Jika data kosong merge A2:H2
    $sheet->mergeCells('A2:H2');
    $sheet->setCellValue('A2', 'Tidak ada transaksi pada periode tanggal ini.');
    $sheet->getStyle('A2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
}

// FIX 2: Auto-size Lebar Kolom A sampai H
foreach (range('A', 'H') as $col) {
    $sheet->getColumnDimension($col)->setAutoSize(true);
}

// 5. Header Download File (.xlsx)
$filename = "Laporan_" . date('d-m-Y', strtotime($start_date)) . "_sd_" . date('d-m-Y', strtotime($end_date)) . ".xlsx";

// Bersihkan output buffer jika ada karakter/ruang kosong tersembunyi
if (ob_get_length()) ob_end_clean();

header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="' . $filename . '"');
header('Cache-Control: max-age=0');

$writer = new Xlsx($spreadsheet);
$writer->save('php://output');
exit;