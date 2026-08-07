<?php
// Pastikan session sudah diaktifkan untuk pencatatan Audit Log
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../includes/db.php';

// =========================================================================
// 1. AKSI AJAX: SIMPAN BATCH KE STOK BARANG (PDO)
// =========================================================================
$rawInput = file_get_contents('php://input');
$jsonData = json_decode($rawInput, true);

if (isset($jsonData['action']) && $jsonData['action'] === 'simpan_stok_batch') {
    header('Content-Type: application/json');
    
    $barcodes = $jsonData['barcodes'] ?? [];

    if (empty($barcodes) || !is_array($barcodes)) {
        echo json_encode(['success' => false, 'message' => 'Data barcode kosong.']);
        exit();
    }

    // Mulai Transaksi Atomic PDO
    $pdo->beginTransaction();

    try {
        $insertedCount = 0;
        $skippedCount  = 0;

        // Pemetaan Komponen Digit Barcode (9 Digit)
        $genderMap = ['1' => 'Pria', '2' => 'Wanita'];
        $typeMap   = ['01' => 'Baju', '02' => 'Celana'];
        $sizeMap   = [
            '01' => 'S', '02' => 'M', '03' => 'L', '04' => 'XL',
            '28' => '28', '30' => '30', '32' => '32', '34' => '34', '36' => '36'
        ];

        // Prepared Statements PDO
        $stmtCek = $pdo->prepare("SELECT COUNT(*) FROM master_item WHERE barcode = ?");
        $stmtInsert = $pdo->prepare("INSERT INTO master_item (barcode, gender, tipe, size, status_transaksi, status_barang) VALUES (?, ?, ?, ?, 'Available', 'Active')");

        foreach ($barcodes as $code) {
            $code = trim($code);
            if (strlen($code) !== 9 || !ctype_digit($code)) {
                continue; // Skip jika format tidak 9 digit angka
            }

            // Cek Duplikasi Barcode di Database
            $stmtCek->execute([$code]);
            $exists = $stmtCek->fetchColumn();

            if ($exists > 0) {
                $skippedCount++;
                continue; // Skip jika sudah ada di database
            }

            // Parse Digit Barcode
            $genderCode = substr($code, 0, 1);
            $typeCode   = substr($code, 1, 2);
            $sizeCode   = substr($code, 3, 2);

            $gender = $genderMap[$genderCode] ?? 'Unknown';
            $tipe   = $typeMap[$typeCode]   ?? 'Item';
            $size   = $sizeMap[$sizeCode]   ?? 'Unknown';

            // Insert ke Tabel Stok Barang
            $stmtInsert->execute([$code, $gender, $tipe, $size]);
            $insertedCount++;
        }

        if ($insertedCount > 0) {
            // Pencatatan Audit Log Activity
            $user_id   = $_SESSION['user_id'] ?? 1;
            $nama_user = $_SESSION['nama_user'] ?? ($_SESSION['nama_lengkap'] ?? 'Staff');
            $role      = $_SESSION['role'] ?? 'User';
            
            $keterangan = "Berhasil menambahkan {$insertedCount} item barcode baru ke Stok Barang.";
            if ($skippedCount > 0) {
                $keterangan .= " ({$skippedCount} item dilewati karena sudah terdaftar).";
            }

            $stmtLog = $pdo->prepare("INSERT INTO log_activity (user_id, nama_user, role, aktivitas, keterangan, modul, created_at) VALUES (?, ?, ?, 'Batch Generate Stok', ?, 'Generate Barcode', NOW())");
            $stmtLog->execute([$user_id, $nama_user, $role, $keterangan]);

            // Commit Transaksi
            $pdo->commit();

            echo json_encode([
                'success' => true,
                'message' => "{$insertedCount} barcode berhasil disimpan ke Stok Barang!" . ($skippedCount > 0 ? " ({$skippedCount} barcode dilewati karena sudah terdaftar)." : "")
            ]);
        } else {
            $pdo->rollBack();
            echo json_encode([
                'success' => false,
                'message' => 'Seluruh barcode pada preview ini sudah pernah terdaftar di database.'
            ]);
        }

    } catch (Exception $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        echo json_encode(['success' => false, 'message' => 'Gagal menyimpan stok: ' . $e->getMessage()]);
    }
    exit();
}

// =========================================================================
// 2. LOGIKA FORM PROCESS & RUNNING NUMBER CHECKING
// =========================================================================
$success_msg = "";
$error_msg   = "";
$info_msg    = "";
$generated_barcodes = [];

// Tangkap nilai input POST agar tidak ter-reset di halaman tampilan
$gender = $_POST['gender'] ?? '';
$tipe   = $_POST['tipe']   ?? '';
$ukuran = $_POST['ukuran'] ?? '';

// Default awal running number
$last_number = 0;
$range_awal  = 1;
$range_akhir = 10;

// Pemetaan Singkatan SKU (Format 9 Digit: [Gender:1][Tipe:2][Ukuran:2][Running:4])
$gender_code = ($gender === 'Pria') ? '1' : (($gender === 'Wanita') ? '2' : '');

$tipe_code = '';
if ($tipe === 'Baju') $tipe_code = '01';
elseif ($tipe === 'Celana') $tipe_code = '02';

$ukuran_code = '';
if ($ukuran === 'S') $ukuran_code = '01';
elseif ($ukuran === 'M') $ukuran_code = '02';
elseif ($ukuran === 'L') $ukuran_code = '03';
elseif ($ukuran === 'XL') $ukuran_code = '04';
elseif (in_array($ukuran, ['28', '30', '32', '34', '36'])) $ukuran_code = $ukuran;

// Tentukan Prefix Barcode (5 Digit Pertama)
$prefix = $gender_code . $tipe_code . $ukuran_code;

// Otomatis Hitung Running Number Terakhir dari DB jika Parameter SKU Lengkap
if (!empty($prefix) && strlen($prefix) === 5) {
    try {
        $stmt_last = $pdo->prepare("SELECT MAX(RIGHT(barcode, 4)) FROM master_item WHERE barcode LIKE :prefix");
        $stmt_last->execute(['prefix' => $prefix . '%']);
        $raw_last = $stmt_last->fetchColumn();

        $last_number = $raw_last !== null ? (int)$raw_last : 0;
        $range_awal  = $last_number + 1; // Otomatis mulai dari nomor urut berikutnya
    } catch (PDOException $e) {
        $error_msg = "Terjadi kesalahan database: " . $e->getMessage();
    }
}

// Proses Aksi Form POST
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];

    // Aksi 1: Cek Running Number Terakhir
    if ($action === 'check_last') {
        if (empty($gender) || empty($tipe) || empty($ukuran)) {
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
        if (empty($gender) || empty($tipe) || empty($ukuran)) {
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
                    // Jika input 'Sampai' lebih kecil dari range_awal otomatis,
                    // Dianggap pengguna memasukkan "Jumlah Qty" yang ingin dicetak
                    $jumlah_cetak = $user_r_akhir > 0 ? $user_r_akhir : 10;
                    $range_akhir  = $range_awal + $jumlah_cetak - 1;
                }
            }

            // Validasi Batas Pembuatan
            if ($jumlah_cetak <= 0) {
                $error_msg = "Jumlah barcode yang dibuat minimal 1!";
            } elseif ($jumlah_cetak > 300) {
                $error_msg = "Batasi pembuatan maksimal 300 barcode per sesi cetak.";
            } else {
                // Looping Pembuatan Array Kode Barcode
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