<?php
// lang/en.php
return [
    // ==========================================
    // 1. HEADER, SIDEBAR & GENERAL
    // ==========================================
    'dashboard_title'       => 'Dashboard',
    'dashboard_subtitle'    => 'View item transaction statistical data',
    'menu_dashboard'        => 'Dashboard',
    'menu_pending'          => 'Pending',
    'menu_transaksi'        => 'Transactions',
    'menu_return'           => 'Returns',
    'menu_stok'             => 'Inventory',
    'menu_generate_barcode' => 'Generate Barcode',
    'menu_laporan'          => 'Reports',
    'menu_log_activity'     => 'Activity Log',
    'menu_closing'          => 'Closing',
    'search_placeholder'    => 'Search ...',
    'btn_process'           => 'Process',
    'btn_cancel'            => 'Cancel',
    'table_no'              => 'NO',
    'table_status'          => 'STATUS',
    'table_action'          => 'ACTION',
    'req_sa_pria'           => 'Male SA Uniform Request',
    'req_sa_wanita'         => 'Female SA Uniform Request',

    // ==========================================
    // 2. DASHBOARD (STATISTIK)
    // ==========================================
    'kpi_pending'           => 'PENDING REQUESTS',
    'kpi_transaksi'         => 'TOTAL TRANSACTIONS',
    'kpi_return'            => 'TOTAL RETURNS',
    'unit_pcs'              => 'Pcs',
    'remaining_stock_title' => 'Remaining Stock (Current)',
    'remaining_stock_desc'  => 'Total active items ready for use',
    'badge_active'          => 'Available (Active)',
    'returned_stock_title'  => 'Returned Stock',
    'returned_stock_desc'   => 'Total returned/inactive items',
    'badge_inactive'        => 'Available (Inactive)',
    'item_baju_pria'        => 'Men\'s Shirt',
    'item_celana_pria'      => 'Men\'s Pants',
    'item_baju_wanita'      => 'Women\'s Shirt',
    'item_celana_wanita'    => 'Women\'s Pants',

    // ==========================================
    // 3. HALAMAN PENDING REQUEST
    // ==========================================
    'pending_title'         => 'Uniform Request Management',
    'pending_subtitle'      => 'Processing and history of SA uniform request status',
    'tab_pending_req'       => 'Pending Requests',
    'tab_req_done'          => 'Completed Requests',
    'th_no_request'         => 'REQUEST NO.',
    'th_detail_karyawan'    => 'EMPLOYEE & ITEM DETAILS',
    'badge_pending'          => 'Pending',
    'badge_done'             => 'Completed',
    'btn_view_details'        => 'View Details',
    'modal_trx_selesai'     => 'Transaction Completed',
    'modal_approve_trx'      => 'Approve Transaction',
    'modal_detail_request' => 'Request Details',

    // ==========================================
    // 4. HALAMAN TRANSAKSI
    // ==========================================
    'trx_title'             => 'Transactions',
    'trx_subtitle'          => 'Management for outgoing item processing',
    'trx_sect_pesanan'      => 'Order Details',
    'trx_info_pesanan'      => 'Request item details will appear automatically here.',
    'trx_sect_tiket'        => 'Ticket Information',
    'trx_lbl_id_req'        => 'Request ID',
    'trx_plc_id_req'        => 'Example: FR-110726',
    'trx_lbl_nama_sa'       => 'SA Name',
    'trx_lbl_dept'          => 'Department',
    'trx_plc_dept'          => 'Select Department',
    'trx_lbl_id_sales'      => 'SA ID',
    'trx_plc_id_sales'      => 'Enter SA ID',
    'trx_badge_wajib'       => 'Required',
    'trx_sect_scan'         => 'Item Scanning',
    'trx_info_scan'         => 'Auto-Scan',
    'trx_plc_scan'          => 'Scan barcode..',
    'btn_validate_items'    => 'Validate Items',
    'btn_proses_trx'        => 'Process Transaction',

    // ==========================================
    // 5. HALAMAN RETURN (PENGEMBALIAN)
    // ==========================================
    'ret_title'             => 'Return Submission',
    'ret_subtitle'          => 'Management for return submissions',
    'th_no_transaksi'       => 'TRANSACTION NO.',
    'th_brand'              => 'BRAND',
    'th_id_sales'           => 'SALES ID',
    'th_detail_item_trx'    => 'TRANSACTION ITEM DETAILS',
    'btn_proses_return'     => 'Process Return',

    // ==========================================
    // 6. HALAMAN STOK BARANG
    // ==========================================
    'stok_title'            => 'Inventory',
    'stok_subtitle'         => 'Uniform and equipment inventory management',
    'tab_semua'             => 'All',
    'tab_available'         => 'Available',
    'tab_sold_out'          => 'Sold Out',
    'tab_inactive'          => 'Inactive',
    'th_barcode'            => 'BARCODE',
    'th_detail_item'        => 'ITEM DETAILS',
    'th_kategori'           => 'CATEGORY',
    'lbl_ukuran'            => 'Size:',

    // ==========================================
    // 7. HALAMAN GENERATE BARCODE
    // ==========================================
    'gen_title'             => 'Generate Barcode',
    'gen_subtitle'          => 'Barcode creation for item naming',
    'gen_sect_prefix'       => '1. Barcode Prefix',
    'gen_lbl_gender'        => 'Gender',
    'gen_plc_gender'        => '-- Select Gender --',
    'gen_lbl_tipe'          => 'Clothing Type',
    'gen_plc_tipe'          => '-- Select Type --',
    'gen_lbl_ukuran'        => 'Size',
    'gen_plc_ukuran'        => '-- Select Size --',
    'btn_cek_running'       => 'Check Last Running Number',
    'gen_sect_range'        => '2. Range Running Number',
    'gen_lbl_range'         => 'Range Running Number (4 Digits)',
    'gen_lbl_mulai'         => 'Start',
    'gen_lbl_sampai'        => 'To',
    'btn_preview_label'     => 'Preview Barcode Label',
    'gen_sect_cetak'        => 'Print Sheet',
    'gen_info_cetak'        => 'Select the SKU combination above to preview the barcode sticker.',
    'btn_cetak_stiker'      => 'Print Barcode Sticker',
    'btn_simpan_stok_batch' => 'Save Batch Inventory',

    // ==========================================
    // 8. HALAMAN LAPORAN
    // ==========================================
    'rep_title'             => 'Stock Movement Report',
    'rep_subtitle'          => 'Recapitulation of uniform distribution & returns',
    'tab_lap_stok'          => 'Stock Report',
    'tab_lap_keuangan'      => 'Financial Report',
    'rep_card_master'       => 'TOTAL MASTER STOCK',
    'rep_card_trx'          => 'TOTAL TRANSACTIONS',
    'rep_card_retur'        => 'TOTAL RETURNS',
    'rep_card_stok'         => 'TOTAL STOCK',
    'rep_card_total_req'    => 'TOTAL REQUESTS',
    'rep_card_total_pcs'    => 'TOTAL ITEM (PCS)',
    'rep_card_akumulasi'    => 'TOTAL STOCK ACCUMULATION',
    'rep_unit_transaksi'    => 'Transactions',
    'rep_unit_item'         => 'Items',
    'rep_lbl_tgl_mulai'     => 'Start Date',
    'rep_lbl_tgl_selesai'   => 'End Date',
    'btn_export_excel'      => 'Export To Excel',
    'btn_tampil_laporan'    => 'Show Report',
    'rep_sect_rincian'      => 'Stock Movement Details per Transaction',
    'th_tanggal'            => 'DATE',
    'th_detail_pesanan'     => 'ORDER DETAILS',
    'th_nama_sa'            => 'SA NAME',
    'th_item_diberikan'     => 'ITEMS PROVIDED',
    'th_item_return'        => 'RETURNED ITEMS',
    'rep_th_total_tagihan' => 'TOTAL BILLING',
    'rep_th_metode_pembayaran' => 'PAYMENT METHOD',
    'rep_th_perusahaan_brand' => 'COMPANY / BRAND',

    // ==========================================
    // 9. HALAMAN LOG AKTIVITAS
    // ==========================================
    'log_search_placeholder' => 'Search activity...',
    'th_waktu' => 'TIME',
    'th_pengguna' => 'USER',
    'th_aktivitas' => 'ACTIVITY',
    'th_modul' => 'MODULE',
];