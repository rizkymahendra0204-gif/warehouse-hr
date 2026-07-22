<?php
session_start();
require_once __DIR__ . '/../includes/db.php';

// Penanganan nama variabel koneksi PDO
if (!isset($conn) && isset($pdo)) {
    $conn = $pdo;
}

// Tangkap filter tanggal dari URL
$start_date = isset($_GET['start_date']) ? $_GET['start_date'] : date('Y-m-01');
$end_date   = isset($_GET['end_date']) ? $_GET['end_date'] : date('Y-m-t');

// Set Header HTTP agar browser langsung mengunduh sebagai file Excel (.xls)
$filename = "Laporan_" . date('d-m-Y', strtotime($start_date)) . "_sd_" . date('d-m-Y', strtotime($end_date)) . ".xls";

header("Content-Type: application/vnd.ms-excel; charset=utf-8");
header("Content-Disposition: attachment; filename=Laporan.xls");
header("Pragma: no-cache");
header("Expires: 0");
try {
    // Query mengambil data transaksi keluar sesuai filter tanggal
    $sql_table = "SELECT 
                    t.tgl_transaksi,
                    t.transaction_id,
                    rf.request_id,
                    rf.perusahaan,
                    GROUP_CONCAT(CONCAT(mi.tipe, ' ', mi.gender) SEPARATOR ',') AS all_items,
                    COUNT(t.barcode) AS qty_total
                FROM transaksi t
                INNER JOIN request_form rf ON t.request_id = rf.request_id
                INNER JOIN master_item mi ON TRIM(t.barcode) = TRIM(mi.barcode)
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
?>

<!-- Format Tabel Excel -->
<table border="1">
    <thead>
        <tr style="background-color: #198754; color: #ffffff; font-weight: bold; text-align: center;">
            <th>NO</th>
            <th>TANGGAL</th>
            <th>ID TRX</th>
            <th>PERUSAHAAN</th>
            <th>ITEM DIBERIKAN</th>
            <th>TOTAL (PCS)</th>
        </tr>
    </thead>
    <tbody>
        <?php if (!empty($list_transaksi)): ?>
            <?php 
            $no = 1;
            foreach ($list_transaksi as $row): 
                $tgl = date('d/m/Y', strtotime($row['tgl_transaksi']));
                
                // Grouping nama item ("Celana Pria (1), Baju Pria (1)")
                $raw_items_array = explode(',', $row['all_items']);
                $item_counts = array_count_values($raw_items_array);
                
                $formatted_items = [];
                foreach ($item_counts as $nama_item => $jumlah) {
                    $formatted_items[] = trim($nama_item) . " ($jumlah)";
                }
                $string_item_diberikan = implode(', ', $formatted_items);
            ?>
                <tr>
                    <td align="center"><?php echo $no++; ?></td>
                    <td align="center"><?php echo $tgl; ?></td>
                    <td>#<?php echo htmlspecialchars($row['transaction_id']); ?></td>
                    <td><?php echo htmlspecialchars($row['perusahaan']); ?></td>
                    <td><?php echo htmlspecialchars($string_item_diberikan); ?></td>
                    <td align="center"><b><?php echo $row['qty_total']; ?></b></td>
                </tr>
            <?php endforeach; ?>
        <?php else: ?>
            <tr>
                <td colspan="6" align="center">Tidak ada transaksi pada periode tanggal ini.</td>
            </tr>
        <?php endif; ?>
    </tbody>
</table>