<?php
require_once __DIR__ . '/../includes/auth_check.php';
require_once __DIR__ . '/../services/inventory.php';
$jsonData = wh_input();
if (($jsonData['action'] ?? '') === 'simpan_stok_batch') {
    wh_require_post();
    try {
        $result = wh_add_stock($pdo, $jsonData['barcodes'] ?? []);
        wh_json(['success' => true, 'message' => $result['inserted'] . ' barcode disimpan; ' . $result['skipped'] . ' sudah terdaftar.', 'data' => $result]);
    } catch (DomainException $error) { wh_http_error(422, $error->getMessage()); }
}
// =========================================================================
// 2. LOGIKA FORM PROCESS & RUNNING NUMBER CHECKING
// =========================================================================
$success_msg = "";
$error_msg   = "";
$info_msg    = "";
$generated_barcodes = [];

// Tangkap nilai input POST agar tidak ter-reset di halaman tampilan
$gender = trim($_POST['gender'] ?? '');
$tipe   = trim($_POST['tipe']   ?? '');
$ukuran = trim($_POST['ukuran'] ?? '');

// Default awal running number
$last_number = 0;
$range_awal  = 1;
$range_akhir = 10;

// 🔧 PEMETAAN FLEXIBEL SKU (Mendukung Kode Digit Maupun Teks Alfabet)
$gender_code = '';
if (in_array((string)$gender, ['1', 'Pria', 'pria', 'male', 'Pria (1)'])) {
    $gender_code = '1';
} elseif (in_array((string)$gender, ['2', 'Wanita', 'wanita', 'female', 'Wanita (2)'])) {
    $gender_code = '2';
}

$tipe_code = '';
if (in_array((string)$tipe, ['01', '1', 'Baju', 'baju', 'Baju (01)'])) {
    $tipe_code = '01';
} elseif (in_array((string)$tipe, ['02', '2', 'Celana', 'celana', 'Celana (02)'])) {
    $tipe_code = '02';
}

$ukuran_code = '';
$ukuran_upper = strtoupper((string)$ukuran);
if (in_array($ukuran_upper, ['01', 'S'])) {
    $ukuran_code = '01';
} elseif (in_array($ukuran_upper, ['02', 'M'])) {
    $ukuran_code = '02';
} elseif (in_array($ukuran_upper, ['03', 'L'])) {
    $ukuran_code = '03';
} elseif (in_array($ukuran_upper, ['04', 'XL'])) {
    $ukuran_code = '04';
} elseif (in_array($ukuran, ['28', '30', '32', '34', '36'])) {
    $ukuran_code = $ukuran;
}

// Tentukan Prefix Barcode (5 Digit Pertama)
$prefix = $gender_code . $tipe_code . $ukuran_code;

// Otomatis Hitung Running Number Terakhir dari DB jika Parameter SKU Lengkap (5 Digit)
if (!empty($prefix) && strlen($prefix) === 5 && isset($pdo)) {
    try {
        $stmt_last = $pdo->prepare("SELECT MAX(CAST(RIGHT(barcode, 4) AS UNSIGNED)) FROM master_item WHERE barcode LIKE :prefix");
        $stmt_last->execute(['prefix' => $prefix . '%']);
        $raw_last = $stmt_last->fetchColumn();

        $last_number = ($raw_last !== null && $raw_last !== false) ? (int)$raw_last : 0;
        $range_awal  = $last_number + 1; // Otomatis mulai dari nomor urut berikutnya
    } catch (PDOException $e) {
    error_log('[Warehouse HR] ' . $e);
        $error_msg = "Terjadi kesalahan database: " . 'Operasi database gagal. Hubungi administrator.';
    }
}

// Proses Aksi Form POST
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];

    // Aksi 1: Cek Running Number Terakhir
    if ($action === 'check_last') {
        if (strlen($prefix) !== 5) {
            $error_msg = "Harap pilih Gender, Tipe, dan Ukuran terlebih dahulu!";
        } else {
            if ($last_number > 0) {
                $last_barcode_full = $prefix . sprintf("%04d", $last_number);
                $info_msg = "Barcode terakhir di database untuk SKU ini adalah <strong>{$last_barcode_full}</strong>. Running number berikutnya otomatis dimulai dari <strong>" . sprintf("%04d", $range_awal) . "</strong>.";
            } else {
                $info_msg = "Belum ada barcode di database untuk SKU ini. Running number dimulai dari <strong>0001</strong>.";
            }
        }
    } 
    // Aksi 2: Generate Preview Range Barcode
    elseif ($action === 'generate_range') {
        if (strlen($prefix) !== 5) {
            $error_msg = "Semua parameter SKU (Gender, Tipe, Ukuran) wajib diisi!";
        } else {
            // Hitung range_akhir & jumlah cetak
            if (isset($_POST['jumlah_cetak']) && (int)$_POST['jumlah_cetak'] > 0) {
                $jumlah_cetak = (int)$_POST['jumlah_cetak'];
                $range_akhir  = $range_awal + $jumlah_cetak - 1;
            } else {
                $user_r_akhir = isset($_POST['range_akhir']) ? (int)$_POST['range_akhir'] : 0;
                
                if ($user_r_akhir >= $range_awal) {
                    $range_akhir  = $user_r_akhir;
                    $jumlah_cetak = ($range_akhir - $range_awal) + 1;
                } else {
                    $jumlah_cetak = $user_r_akhir > 0 ? $user_r_akhir : 10;
                    $range_akhir  = $range_awal + $jumlah_cetak - 1;
                }
            }

            // Validasi Batas Pembuatan
            if ($jumlah_cetak <= 0) {
                $error_msg = "Jumlah barcode yang dibuat minimal 1!";
            } elseif ($range_akhir > 9999) {
                $error_msg = 'Nomor urut barcode sudah melewati 9999 untuk SKU ini.';
            } elseif ($jumlah_cetak > 300) {
                $error_msg = "Batasi pembuatan maksimal 300 barcode per sesi cetak.";
            } else {
                // Looping Pembuatan Array Kode Barcode (9 Digit)
                for ($i = $range_awal; $i <= $range_akhir; $i++) {
                    $running_number = sprintf("%04d", $i);
                    $barcode_comb   = $prefix . $running_number;
                    $generated_barcodes[] = $barcode_comb;
                }

                $success_msg = "Berhasil membuat <strong>" . count($generated_barcodes) . "</strong> label barcode siap cetak! (Urutan #" . sprintf("%04d", $range_awal) . " s/d #" . sprintf("%04d", $range_akhir) . ")";
            }
        }
    }
}
?>