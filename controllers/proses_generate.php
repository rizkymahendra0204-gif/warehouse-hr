<?php
session_start();
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

            // Cek Duplikasi Barcode
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

            // Insert ke Stok Barang
            $stmtInsert->execute([$code, $gender, $tipe, $size]);
            $insertedCount++;
        }

        if ($insertedCount > 0) {
            // Pencatatan Audit Log Activity (PDO)
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
// 2. AKSI FORM POST: GENERATE PREVIEW LABEL BARCODE
// =========================================================================
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
    $gender_code = ($gender === 'Pria') ? '1' : '2';

    $tipe_code = '';
    if ($tipe === 'Baju') $tipe_code = '01';
    elseif ($tipe === 'Celana') $tipe_code = '02';

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
            $barcode_comb = $gender_code . $tipe_code . $ukuran_code . $running_number;
            $generated_barcodes[] = $barcode_comb;
        }
        $success_msg = "Berhasil membuat <strong>" . count($generated_barcodes) . "</strong> label barcode siap cetak!";
    }
}
?>