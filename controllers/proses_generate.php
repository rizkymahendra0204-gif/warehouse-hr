<?php
include 'includes/db.php'; 

$conn = new mysqli("localhost", "root", "", "db_warehouse");

$success_msg = "";
$error_msg = "";
$generated_barcodes = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'generate_range') {
    $tipe        = isset($_POST['tipe']) ? trim($_POST['tipe']) : '';
    $gender      = isset($_POST['gender']) ? trim($_POST['gender']) : '';
    $ukuran      = isset($_POST['ukuran']) ? trim($_POST['ukuran']) : '';
    $range_awal  = isset($_POST['range_awal']) ? (int)$_POST['range_awal'] : 1;
    $range_akhir = isset($_POST['range_akhir']) ? (int)$_POST['range_akhir'] : 1;

    // Pemetaan Singkatan SKU (Format 9 Digit: [Gender:1][Tipe:2][Ukuran:2][Running:4])

    // 1. Digit Pertama: Gender (1 digit)
    $gender_code = ($gender === 'Pria') ? '1' : '2';

    // 2. Digit Ke-2 & 3: Tipe (2 digit)
    $tipe_code = '';
    if ($tipe === 'Baju') $tipe_code = '01';
    elseif ($tipe === 'Celana') $tipe_code = '02';

    // 3. Digit Ke-4 & 5: Ukuran (2 digit)
    $ukuran_code = '';
    if ($ukuran === 'S') $ukuran_code = '01';
    elseif ($ukuran === 'M') $ukuran_code = '02';
    elseif ($ukuran === 'L') $ukuran_code = '03';
    elseif ($ukuran === 'XL') $ukuran_code = '04';
    elseif ($ukuran === '28') $ukuran_code = '28';
    elseif ($ukuran === '30') $ukuran_code = '30';
    elseif ($ukuran === '32') $ukuran_code = '32';
    elseif ($ukuran === '34') $ukuran_code = '34';
    elseif ($ukuran === '36') $ukuran_code = '36';    

    // Validasi Form
    if (empty($tipe) || empty($gender) || empty($ukuran)) {
        $error_msg = "Semua parameter SKU wajib diisi!";
    } elseif ($range_awal > $range_akhir) {
        $error_msg = "Range awal tidak boleh lebih besar dari range akhir!";
    } elseif (($range_akhir - $range_awal) > 300) {
        $error_msg = "Batasi pembuatan maksimal 300 barcode per sesi cetak.";
    } else {
        // Murni me-looping dan membuat kode stiker
        for ($i = $range_awal; $i <= $range_akhir; $i++) {
            $running_number = sprintf("%04d", $i);
            
            // Penggabungan Sesuai Urutan: Gender (1) + Tipe (2) + Ukuran (2) + Running Number (4)
            $barcode_comb = $gender_code . $tipe_code . $ukuran_code . $running_number;
            
            $generated_barcodes[] = $barcode_comb;
        }
        $success_msg = "Berhasil membuat <strong>" . count($generated_barcodes) . "</strong> label barcode siap cetak!";
    }
}
?>