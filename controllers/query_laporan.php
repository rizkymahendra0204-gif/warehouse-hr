<?php
// controllers/query_laporan.php
require_once __DIR__ . '/../includes/db.php';

// 1. Ambil Parameter Tanggal & Tipe Laporan
$start_date  = $_GET['start_date'] ?? date('Y-m-01');
$end_date    = $_GET['end_date']   ?? date('Y-m-t');
$report_type = $_GET['type']       ?? $report_type ?? 'internal'; // Default: internal

// 2. Inisialisasi variabel default agar tidak undefined di View
$list_data            = [];
$data                 = [];
$total_keluar         = 0;
$total_retur          = 0;
$total_master_stok    = 0;
$net_terpakai         = 0;
$total_req            = 0;
$grand_total_biaya    = 0;
$total_pcs_fin        = 0;
$grand_total_all_time = 0;
$total_req_all_time   = 0;

try {
    if ($report_type === 'internal') {
        // ==========================================
        // 📦 LOGIKA LAPORAN INTERNAL (STOK)
        // ==========================================
        
        // Summary Cards Internal
        // 1. Total Transaksi Berhasil (Jumlah Form / Nota yang Selesai)
        $stmt_trx = $pdo->prepare("SELECT COUNT(DISTINCT transaction_id) 
                                FROM transaksi 
                                WHERE DATE(tgl_transaksi) BETWEEN :s AND :e 
                                    AND request_id LIKE '%FR%'"); // 💡 Diganti menjadi %FR% agar terdeteksi #FR-...
        $stmt_trx->execute(['s' => $start_date, 'e' => $end_date]);

        // Simpan nilai ke variabel
        $total_transaksi = (int) $stmt_trx->fetchColumn();
        $total_keluar    = $total_transaksi;

        // 2. Total Barang Keluar (Menghitung Total Pcs Fisik Barang yang Di-scan)
        $stmt_k = $pdo->prepare("SELECT COUNT(td.barcode) 
                                FROM transaksi_detail td
                                INNER JOIN transaksi t ON td.transaction_id = t.transaction_id
                                WHERE DATE(t.tgl_transaksi) BETWEEN :s AND :e 
                                AND t.request_id LIKE '%FR%'");
        $stmt_k->execute(['s' => $start_date, 'e' => $end_date]);
        $total_barang_keluar = (int) $stmt_k->fetchColumn();

        // 3. Total Barang Retur (Menghitung Total Pcs Fisik Barang yang Dikembalikan)
        $stmt_r = $pdo->prepare("SELECT COUNT(ri.barcode) 
                                FROM return_items ri 
                                WHERE DATE(ri.tgl_return) BETWEEN :s AND :e");
        $stmt_r->execute(['s' => $start_date, 'e' => $end_date]);
        $total_retur = (int) $stmt_r->fetchColumn();

        // 4. Total Master Stok
        $total_master_stok = (int) $pdo->query("SELECT COUNT(barcode) FROM master_item")->fetchColumn();

        // 5. NET Terpakai (Pcs Keluar - Pcs Retur)
        $net_terpakai = $total_barang_keluar - $total_retur;

        // Tabel Detail Stok
        $sql_stok = "SELECT 
                t.transaction_id,
                t.tgl_transaksi,
                td.barcode,
                rf.request_id,
                rf.perusahaan,
                rf.brand,
                rf.nama_sa,
                GROUP_CONCAT(CONCAT(mi.tipe, ' ', mi.gender, ' - Size ', mi.size) SEPARATOR ', ') AS raw_items,
                COUNT(td.barcode) AS qty_keluar,
                COALESCE(ret.qty_retur, 0) AS qty_retur,
                (COUNT(td.barcode) - COALESCE(ret.qty_retur, 0)) AS net_terpakai,
                ret.raw_returns
            FROM transaksi t
            INNER JOIN request_form rf ON t.request_id = rf.request_id
            INNER JOIN transaksi_detail td ON t.transaction_id = td.transaction_id
            INNER JOIN master_item mi ON TRIM(td.barcode) = TRIM(mi.barcode)
            LEFT JOIN (
                SELECT 
                    ri.transaction_id,
                    COUNT(ri.barcode) AS qty_retur,
                    GROUP_CONCAT(CONCAT(mir.tipe, ' ', mir.gender, ' - Size ', mir.size) SEPARATOR ', ') AS raw_returns
                FROM return_items ri
                INNER JOIN master_item mir ON TRIM(ri.barcode) = TRIM(mir.barcode)
                GROUP BY ri.transaction_id
            ) ret ON t.transaction_id = ret.transaction_id
            WHERE DATE(t.tgl_transaksi) BETWEEN :s1 AND :e1
            GROUP BY 
                t.transaction_id, 
                t.tgl_transaksi, 
                rf.request_id, 
                rf.perusahaan, 
                rf.brand, 
                rf.nama_sa, 
                ret.qty_retur, 
                ret.raw_returns
            ORDER BY t.tgl_transaksi DESC";

        $stmt_stok = $pdo->prepare($sql_stok);
        $stmt_stok->execute(['s1' => $start_date, 'e1' => $end_date]);
        $list_data = $data = $stmt_stok->fetchAll(PDO::FETCH_ASSOC);

    } else if ($report_type === 'finance') {
        // ==========================================
        // 💰 LOGIKA LAPORAN FINANCE (KEUANGAN)
        // ==========================================

        // 1. Summary Cards Finance (Sesuai Filter Tanggal)
        $sql_sum = "SELECT 
                        COUNT(DISTINCT rf.request_id) AS total_req,
                        COALESCE(SUM(rf.total_harga), 0) AS grand_total_biaya,
                        COALESCE(SUM(td_sum.total_pcs), 0) AS total_pcs
                    FROM request_form rf
                    INNER JOIN transaksi t ON rf.request_id = t.request_id
                    LEFT JOIN (
                        SELECT 
                            transaction_id, 
                            COUNT(barcode) AS total_pcs
                        FROM transaksi_detail
                        GROUP BY transaction_id
                    ) td_sum ON t.transaction_id = td_sum.transaction_id
                    WHERE DATE(t.tgl_transaksi) BETWEEN :s AND :e 
                    AND rf.request_id LIKE 'FR%'";

        $stmt_sum = $pdo->prepare($sql_sum);
        $stmt_sum->execute(['s' => $start_date, 'e' => $end_date]);
        $sum = $stmt_sum->fetch(PDO::FETCH_ASSOC);

        $total_req         = (int) ($sum['total_req'] ?? 0);
        $grand_total_biaya = (float) ($sum['grand_total_biaya'] ?? 0);
        $total_pcs_fin     = (int) ($sum['total_pcs'] ?? 0);

        // 2. Summary Card All Time (Tanpa Filter Tanggal)
        $sql_all_time = "SELECT 
                            COALESCE(SUM(rf.total_harga), 0) AS grand_total_all_time,
                            COUNT(DISTINCT rf.request_id) AS total_req_all_time
                         FROM request_form rf
                         INNER JOIN transaksi t ON rf.request_id = t.request_id
                         WHERE rf.request_id LIKE 'FR%'";

        $stmt_all_time = $pdo->query($sql_all_time);
        $sum_all_time  = $stmt_all_time->fetch(PDO::FETCH_ASSOC);

        $grand_total_all_time = (float) ($sum_all_time['grand_total_all_time'] ?? 0);
        $total_req_all_time   = (int) ($sum_all_time['total_req_all_time'] ?? 0);

        // 3. Tabel Detail Keuangan
        $sql_fin = "SELECT 
                        t.tgl_transaksi, 
                        rf.request_id, 
                        rf.perusahaan, 
                        rf.brand, 
                        rf.nama_sa,
                        rf.pembayaran, 
                        rf.total_harga, 
                        COUNT(td.barcode) AS total_pcs
                    FROM request_form rf
                    INNER JOIN transaksi t ON rf.request_id = t.request_id
                    LEFT JOIN transaksi_detail td ON t.transaction_id = td.transaction_id
                    WHERE DATE(t.tgl_transaksi) BETWEEN :s AND :e 
                      AND rf.request_id LIKE 'FR%'
                    GROUP BY 
                        t.transaction_id,
                        rf.request_id, 
                        t.tgl_transaksi, 
                        rf.perusahaan, 
                        rf.brand, 
                        rf.nama_sa, 
                        rf.pembayaran, 
                        rf.total_harga
                    ORDER BY t.tgl_transaksi DESC";

        $stmt_fin = $pdo->prepare($sql_fin);
        $stmt_fin->execute(['s' => $start_date, 'e' => $end_date]);
        $list_data = $data = $stmt_fin->fetchAll(PDO::FETCH_ASSOC);
    }
} catch (PDOException $e) {
    $list_data = [];
    $data      = [];
    // Hilangkan tanda komentar baris di bawah jika ingin mengintip error SQL saat testing:
    // die("Error Query Laporan: " . $e->getMessage());
}