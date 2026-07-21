<?php
include '../includes/db.php';
$conn = new mysqli($host, $user, $pass, $db);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $no_return        = $_POST['no_return'] ?? '';
    $id_request_awal  = $_POST['id_request_awal'] ?? '';
    $nama             = $_POST['nama'] ?? '';
    $tgl_return       = $_POST['tgl_return'] ?? date('Y-m-d');
    
    $barcodes         = $_POST['barcode_return'] ?? [];
    $kondisi          = $_POST['kondisi_return'] ?? [];

    if (!empty($barcodes)) {
        // Mulai Transaksi Database
        $conn->begin_transaction();

        try {
            for ($i = 0; $i < count($barcodes); $i++) {
                $barcode_item = $conn->real_escape_string($barcodes[$i]);
                $kondisi_item = $conn->real_escape_string($kondisi[$i] ?? 'Layak');

                // 1. Tentukan Status Baru berdasarkan Kondisi Return
                if ($kondisi_item === 'Layak') {
                    $st_transaksi = 'Available';
                    $st_barang    = 'Active';
                } else {
                    $st_transaksi = 'Sold Out';
                    $st_barang    = 'Inactive';
                }

                // 2. Update Status di master_item
                $sqlUpdate = "UPDATE master_item 
                              SET status_transaksi = '$st_transaksi', 
                                  status_barang = '$st_barang' 
                              WHERE barcode = '$barcode_item'";
                $conn->query($sqlUpdate);

                // 3. (Opsional) Simpan/Catat History Log Return jika ada tabel log
            }

            // Commit perubahan jika semua query sukses
            $conn->commit();

            // Redirect kembali ke return.php dengan pesan sukses
            header("Location: return.php?status=success_return");
            exit;

        } catch (Exception $e) {
            $conn->rollback();
            echo "Gagal memproses return: " . $e->getMessage();
        }
    } else {
        header("Location: return.php?status=empty_items");
        exit;
    }
}
?>