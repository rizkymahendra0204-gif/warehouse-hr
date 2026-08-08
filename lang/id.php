<?php
// lang/id.php
return [
    // ==========================================
    // 1. HEADER, SIDEBAR & GENERAL
    // ==========================================
    'dashboard_title'       => 'Halaman Statistik',
    'dashboard_subtitle'    => 'Melihat data statistik transaksi item',
    'menu_dashboard'        => 'Statistik',
    'menu_pending'          => 'Permintaan',
    'menu_transaksi'        => 'Transaksi',
    'menu_return'           => 'Pengembalian',
    'menu_stok'             => 'Stok Barang',
    'menu_generate_barcode' => 'Pembuatan Kode Batang',
    'menu_laporan'          => 'Laporan',
    'menu_log_activity'     => 'Catatan Aktivitas',
    'menu_closing'          => 'Penutupan',
    'search_placeholder'    => 'Cari ...',
    'btn_process'           => 'Proses',
    'btn_cancel'            => 'Batal',
    'table_no'              => 'NO',
    'table_status'          => 'STATUS',
    'table_action'          => 'AKSI',
    'req_sa_pria'           => 'Permintaan Seragam SA Pria',
    'req_sa_wanita'         => 'Permintaan Seragam SA Wanita',

    // ==========================================
    // 2. DASHBOARD (STATISTIK)
    // ==========================================
    'kpi_pending'           => 'PERMINTAAN TERTUNDA',
    'kpi_transaksi'         => 'TOTAL TRANSAKSI',
    'kpi_return'            => 'TOTAL PENGEMBALIAN',
    'unit_pcs'              => 'Pcs',
    'remaining_stock_title' => 'Stok Tersisa (Saat Ini)',
    'remaining_stock_desc'  => 'Total barang aktif yang siap digunakan',
    'badge_active'          => 'Tersedia (Aktif)',
    'returned_stock_title'  => 'Stok yang Dikembalikan',
    'returned_stock_desc'   => 'Total barang dikembalikan/tidak aktif',
    'badge_inactive'        => 'Tidak Tersedia (Tidak Aktif)',
    'item_baju_pria'        => 'Baju Pria',
    'item_celana_pria'      => 'Celana Pria',
    'item_baju_wanita'      => 'Baju Wanita',
    'item_celana_wanita'    => 'Celana Wanita',

    // ==========================================
    // 3. HALAMAN PENDING REQUEST
    // ==========================================
    'pending_title'         => 'Manajemen Request Seragam',
    'pending_subtitle'      => 'Pengolahan dan riwayat status pengajuan seragam SA',
    'tab_pending_req'       => 'Permintaan Tertunda',
    'tab_req_done'          => 'Permintaan Selesai',
    'th_no_request'         => 'NO. REQUEST',
    'th_detail_karyawan'    => 'DETAIL KARYAWAN & ITEM',
    'badge_pending'          => 'Tertunda',
    'badge_done'             => 'Selesai',
    'btn_view_details'        => 'Lihat Detail',
    'modal_trx_selesai'     => 'Transaksi Selesai',
    'modal_approve_trx'      => 'Setujui Transaksi',
    'modal_detail_request' => 'Detail Permintaan',

    // ==========================================
    // 4. HALAMAN TRANSAKSI
    // ==========================================
    'trx_title'             => 'Transaksi',
    'trx_subtitle'          => 'Manajemen untuk pengelolaan item keluar',
    'trx_sect_pesanan'      => 'Detail Pesanan',
    'trx_info_pesanan'      => 'Rincian item permintaan akan muncul secara otomatis di sini.',
    'trx_sect_tiket'        => 'Informasi Tiket',
    'trx_lbl_id_req'        => 'ID Permintaan',
    'trx_plc_id_req'        => 'Contoh: FR-110726',
    'trx_lbl_nama_sa'       => 'Nama SA',
    'trx_lbl_dept'          => 'Departemen',
    'trx_plc_dept'          => 'Pilih Departemen',
    'trx_lbl_id_sales'      => 'ID SA',
    'trx_plc_id_sales'      => 'Masukkan ID SA',
    'trx_badge_wajib'       => 'Wajib',
    'trx_sect_scan'         => 'Pemindaian Item',
    'trx_info_scan'         => 'Otomatis Memindai:',
    'trx_plc_scan'          => 'Scan barcode...',
    'btn_validate_items'    => 'Validasi Barang',
    'btn_proses_trx'        => 'Proses Transaksi',

    // ==========================================
    // 5. HALAMAN RETURN (PENGEMBALIAN)
    // ==========================================
    'ret_title'             => 'Pengajuan Return',
    'ret_subtitle'          => 'Manajemen untuk pengajuan return',
    'th_no_transaksi'       => 'NO. TRANSAKSI',
    'th_brand'              => 'BRAND',
    'th_id_sales'           => 'ID SALES',
    'th_detail_item_trx'    => 'DETAIL ITEM TRANSAKSI',
    'btn_proses_return'     => 'Proses Return',

    // ==========================================
    // 6. HALAMAN STOK BARANG
    // ==========================================
    'stok_title'            => 'Stok Barang',
    'stok_subtitle'         => 'Manajemen inventaris seragam dan kelengkapan',
    'tab_semua'             => 'Semua',
    'tab_available'         => 'Available',
    'tab_sold_out'          => 'Sold Out',
    'tab_inactive'          => 'Inactive',
    'th_barcode'            => 'BARCODE',
    'th_detail_item'        => 'DETAIL ITEM',
    'th_kategori'           => 'KATEGORI',
    'lbl_ukuran'            => 'Ukuran:',

    // ==========================================
    // 7. HALAMAN GENERATE BARCODE
    // ==========================================
    'gen_title'             => 'Pembuatan Kode Batang',
    'gen_subtitle'          => 'Pembuatan Barcode untuk penamaan item',
    'gen_sect_prefix'       => '1. Awalan Kode Batang',
    'gen_lbl_gender'        => 'Gender',
    'gen_plc_gender'        => '-- Pilih Gender --',
    'gen_lbl_tipe'          => 'Tipe Pakaian',
    'gen_plc_tipe'          => '-- Pilih Tipe --',
    'gen_lbl_ukuran'        => 'Ukuran',
    'gen_plc_ukuran'        => '-- Pilih Ukuran --',
    'btn_cek_running'       => 'Cek Running Terakhir',
    'gen_sect_range'        => '2. Rentang Nomor Urut',
    'gen_lbl_range'         => 'Rentang Nomor',
    'gen_lbl_mulai'         => 'Mulai',
    'gen_lbl_sampai'        => 'Sampai',
    'btn_preview_label'     => 'Pratinjau Label Barcode',
    'gen_sect_cetak'        => 'Lembar Cetak',
    'gen_info_cetak'        => 'Pilih kombinasi SKU di atas untuk melihat preview stiker barcode.',
    'btn_cetak_stiker'      => 'Cetak Stiker Kode Batang',
    'btn_simpan_stok_batch' => 'Simpan Inventaris Batch',


    // ==========================================
    // 8. HALAMAN LAPORAN
    // ==========================================
    'rep_title'             => 'Laporan Pergerakan Stok',
    'rep_subtitle'          => 'Rekapitulasi distribusi & pengembalian seragam',
    'tab_lap_stok'          => 'Laporan Stok',
    'tab_lap_keuangan'      => 'Laporan Keuangan',
    'rep_card_master'       => 'TOTAL SEMUA BARANG',
    'rep_card_trx'          => 'TOTAL TRANSAKSI',
    'rep_card_retur'        => 'TOTAL PENGEMBALIAN',
    'rep_card_stok'         => 'TOTAL STOK',
    'rep_card_total_req'    => 'TOTAL PERMINTAAN',
    'rep_card_total_pcs'    => 'TOTAL ITEM (PCS)',
    'rep_card_total_transaksi' => 'TOTAL TRANSAKSI',
    'rep_card_akumulasi'    => 'TOTAL AKUMULASI STOK',
    'rep_unit_transaksi'    => 'Transaksi',
    'rep_unit_item'         => 'Item',
    'rep_lbl_tgl_mulai'     => 'Tanggal Mulai',
    'rep_lbl_tgl_selesai'   => 'Tanggal Selesai',
    'btn_export_excel'      => 'Export ke Excel',
    'btn_tampil_laporan'    => 'Tampilkan Laporan',
    'rep_sect_rincian'      => 'Rincian Pergerakan Stok per Transaksi',
    'th_tanggal'            => 'TANGGAL',
    'th_detail_pesanan'     => 'DETAIL PESANAN',
    'th_nama_sa'            => 'NAMA SA',
    'th_item_diberikan'     => 'ITEM DIBERIKAN',
    'th_item_return'        => 'ITEM PENGEMBALIAN',
    'rep_th_total_tagihan' => 'TOTAL TAGIHAN',
    'rep_th_metode_pembayaran' => 'METODE PEMBAYARAN',
    'rep_th_perusahaan_brand' => 'PERUSAHAAN / BRAND',


    // ==========================================
    // 9. HALAMAN LOG AKTIVITAS
    // ==========================================
    'log_search_placeholder' => 'Cari aktivitas...',
    'th_waktu' => 'WAKTU',
    'th_pengguna' => 'PENGGUNA',
    'th_aktivitas' => 'AKTIVITAS',
    'th_modul' => 'MODUL',
    
];