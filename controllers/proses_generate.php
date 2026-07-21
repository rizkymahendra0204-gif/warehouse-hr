<?php
include 'includes/db.php'; 

$conn = new mysqli($host, $user, $pass, $db);

$success_msg = "";
$error_msg = "";
$generated_barcodes = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'generate_range') {
    $tipe        = isset($_POST['tipe']) ? trim($_POST['tipe']) : '';
    $gender      = isset($_POST['gender']) ? trim($_POST['gender']) : '';
    $ukuran      = isset($_POST['ukuran']) ? trim($_POST['ukuran']) : '';
    $range_awal  = isset($_POST['range_awal']) ? (int)$_POST['range_awal'] : 1;
    $range_akhir = isset($_POST['range_akhir']) ? (int)$_POST['range_akhir'] : 1;

    // Pemetaan Singkatan SKU
    $tipe_code = '';
    if ($tipe === 'Baju') $tipe_code = '1';
    elseif ($tipe === 'Celana') $tipe_code = '2';

    $gender_code = ($gender === 'Pria') ? '01' : '02';

    if (empty($tipe) || empty($gender) || empty($ukuran)) {
        $error_msg = "Semua parameter SKU wajib diisi!";
    } elseif ($range_awal > $range_akhir) {
        $error_msg = "Range awal tidak boleh lebih besar dari range akhir!";
    } elseif (($range_akhir - $range_awal) > 300) {
        $error_msg = "Batasi pembuatan maksimal 300 barcode per sesi cetak.";
    } else {
        // Murni me-looping dan membuat kode stiker tanpa beban ke database
        for ($i = $range_awal; $i <= $range_akhir; $i++) {
            $running_number = sprintf("%04d", $i);
            // Menghasilkan format SKU (Contoh: BJ-P-S-0001)
            $barcode_comb = $tipe_code . $gender_code . $ukuran . $running_number;
            
            $generated_barcodes[] = $barcode_comb;
        }
        $success_msg = "Berhasil membuat <strong>" . count($generated_barcodes) . "</strong> label barcode siap cetak!";
    }
}
?>