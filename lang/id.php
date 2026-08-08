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

    // ==========================================
    // 10. JAVASCRIPT & ALERT MESSAGES
    // ==========================================
    'alert_failed_submit'      => '<strong>Gagal Submit:</strong> Harap lakukan <b>Validate Items</b> terlebih dahulu!',
    'alert_invalid_barcode'    => 'Format Barcode tidak valid! Harus berisi 9 digit angka.',
    'alert_max_scan'           => '<strong>Batas Maksimum Scan!</strong> Permintaan tiket ini hanya membutuhkan <b>{max} Pcs</b> item.',
    'alert_already_scanned'    => 'Barcode <strong>{barcode}</strong> sudah masuk ke dalam daftar transaksi!',
    'alert_scan_success'       => 'Berhasil memindai <strong>{label}</strong>',
    'alert_no_barcode_preview' => 'Tidak ada barcode di lembar preview untuk disimpan!',
    'swal_confirm_title'       => 'Input ke Stok Barang?',
    'swal_confirm_text'        => 'Apakah Anda yakin ingin mendaftarkan {count} item barcode ini secara otomatis ke stok barang?',
    'btn_yes_save'             => 'Ya, Simpan Stok!',
    'btn_saving'               => 'Menyimpan...',
    'btn_saved'                => 'Sudah Disimpan',
    'btn_save_to_stock'        => 'Simpan ke Stok Barang',
    'alert_analyzing_code'     => 'Menganalisis status pendaftaran kode...',
    'alert_item_exists'        => '<strong>Item Sudah Terdaftar!</strong> Ganti dengan barcode lain !',
    'alert_new_barcode'        => '<strong>Barcode Baru!</strong> Mendaftarkan item <strong>{tipe} {gender} ({size})</strong>.',
    'alert_validation_blocked' => 'Validasi terhambat. Data lokal siap disimpan.',
    'err_unrecognized_component' => 'Kode komponen tidak dikenali sistem.',
    'err_barcode_9_digits'     => 'Barcode harus berjumlah tepat 9 digit angka penuh.',
    'alert_item1_cleared'      => 'Item #1 berhasil dikosongkan.',
    'alert_item_deleted'       => 'Item berhasil dihapus.',
    'err_db_connect_barcode'   => 'Gagal terhubung ke database untuk barcode <b>{barcode}</b>',
    'alert_no_barcode_input'   => 'Belum ada barcode yang diinputkan',
    'alert_failed_db_val'      => '<strong>Gagal Validasi Database:</strong><br>• {errors}',
    'err_mixed_gender'         => 'Kamu memasukkan pakaian <b>Pria</b> dan <b>Wanita</b> sekaligus dalam satu transaksi.',
    'err_gender_mismatch'      => 'Item #{num} (<b>{type}</b>): Gender barang (<b>{gender}</b>) tidak sesuai pesanan tiket (<b>{target}</b>)',
    'err_qty_top_mismatch'     => 'Jumlah Baju yang di-scan (<b>{scanned} Pcs</b>) tidak sesuai pesanan tiket (<b>{target} Pcs</b>)',
    'err_qty_bottom_mismatch'  => 'Jumlah Celana yang di-scan (<b>{scanned} Pcs</b>) tidak sesuai pesanan tiket (<b>{target} Pcs</b>)',
    'alert_failed_trx_val'     => '<strong>Gagal Validasi Transaksi:</strong><br>• {errors}',
    'alert_val_success'        => '<strong>Validasi Berhasil!</strong> Seluruh ({count}) item terdaftar & cocok dengan tiket.',
    'alert_no_barcode_trx'     => 'Tidak ada barcode terdeteksi pada transaksi ini.',
    'alert_parse_code_failed'  => '<strong>Gagal Mengurai Kode!</strong><br><span class="small">{reason} (Input: <code>{input}</code>)</span>',
    'alert_scan_extract_prompt'=> 'Silakan scan barcode untuk ekstraksi digit otomatis.',
    'alert_success_prefix'     => '<strong>Berhasil!</strong> {message}',
    'alert_failed_prefix'      => '<strong>Gagal:</strong> {message}',
    'alert_network_error'      => 'Terjadi kesalahan sistem / jaringan!',
    'alert_unsaved_changes'    => 'Perubahan belum disimpan, yakin ingin meninggalkan halaman?',
    'status_ready_print'       => 'Status: Siap Registrasi / Cetak',
    'status_registered_db'     => 'Terdaftar di Database',

    // ==========================================
    // 11. HALAMAN CLOSING / AUDIT
    // ==========================================
    'audit_page_title'      => 'Audit & Kelola Status Stok',
    'audit_subtitle'        => 'Evaluasi barang return untuk dikembalikan',
    'btn_close_period'      => 'Tutup Periode',
    'btn_open_period'       => 'Buka Periode',
    'confirm_close_period'  => 'Tutup periode audit?',
    'confirm_open_period'   => 'Buka periode audit?',
    'audit_banner_closed'   => '<strong>Periode Closing Ditutup:</strong> Perubahan status barang dikunci sementara.',
    'audit_banner_active'   => '<strong>Mode Closing Aktif:</strong> Silakan lakukan perubahan status.',
    'btn_change_status'     => 'Ubah Status',
    'title_access_locked'   => 'Akses dikunci: Periode audit sedang ditutup',
    'badge_locked'          => 'Terkunci',
    'title_eligible_only'   => 'Hanya status Available (Inactive) yang dapat diubah',
    'badge_compliant'       => 'Sesuai',
    'no_item_data'          => 'Tidak ada data item ditemukan.',
    'modal_audit_title'     => 'Audit Status Item',
    'modal_target_item'     => 'Target Item:',
    'lbl_status_transaksi'  => 'Status Transaksi',
    'opt_available'         => 'Available (Siap Dijual / Direquest)',
    'opt_sold_out'          => 'Sold Out (Sudah Terjual)',
    'help_status_tx'        => 'Pilih <b>Available</b> agar barang bisa dipilih kembali pada transaksi baru.',
    'lbl_status_barang'     => 'Status Barang (Kondisi fisik)',
    'opt_active'            => 'Active (Layak Pakai / Bagus)',
    'opt_inactive'          => 'Inactive (Rusak / Afkir / Perlu Perbaikan)',
    'btn_save_audit'        => 'Simpan Hasil Audit',
    
];