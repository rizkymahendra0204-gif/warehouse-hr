-- Legacy schema only; contains no user or operational data.
SET SQL_MODE='NO_AUTO_VALUE_ON_ZERO';
CREATE TABLE `log_activity` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `nama_user` varchar(100) NOT NULL,
  `role` varchar(50) DEFAULT 'Admin HR',
  `aktivitas` varchar(100) NOT NULL,
  `modul` varchar(50) DEFAULT 'Sistem',
  `keterangan` text NOT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `master_item` (
  `barcode` varchar(50) NOT NULL,
  `gender` varchar(20) NOT NULL,
  `tipe` varchar(20) NOT NULL,
  `size` varchar(10) NOT NULL,
  `status_transaksi` varchar(20) NOT NULL DEFAULT 'Available',
  `status_barang` varchar(20) NOT NULL DEFAULT 'Active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `request_form` (
  `request_id` varchar(50) NOT NULL,
  `perusahaan` varchar(100) NOT NULL,
  `brand` varchar(100) NOT NULL,
  `nama_pengisi` varchar(100) DEFAULT NULL,
  `jabatan` varchar(100) DEFAULT NULL,
  `nama_sa` varchar(100) NOT NULL,
  `alamat` text DEFAULT NULL,
  `gender` varchar(20) DEFAULT NULL,
  `qty_top` int(11) DEFAULT 0,
  `qty_bottoms` int(11) DEFAULT 0,
  `total_harga` decimal(12,2) DEFAULT 0.00,
  `pembayaran` varchar(50) DEFAULT NULL,
  `upload` varchar(255) DEFAULT NULL,
  `tgl_request` datetime DEFAULT current_timestamp(),
  `status` varchar(20) DEFAULT 'Pending'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `return_items` (
  `return_id` int(11) NOT NULL,
  `transaction_id` varchar(50) DEFAULT NULL,
  `barcode` varchar(20) NOT NULL,
  `tgl_return` datetime DEFAULT current_timestamp(),
  `alasan_return` varchar(255) DEFAULT NULL,
  `kondisi_barang` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `settings` (
  `setting_key` varchar(50) NOT NULL,
  `setting_value` varchar(100) NOT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `transaksi` (
  `transaction_id` varchar(50) NOT NULL,
  `request_id` varchar(50) DEFAULT NULL,
  `id_sales` int(50) DEFAULT NULL,
  `tgl_transaksi` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `transaksi_detail` (
  `id_detail` int(11) NOT NULL,
  `transaction_id` varchar(50) NOT NULL,
  `barcode` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `users` (
  `user_id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `nama_lengkap` varchar(100) NOT NULL,
  `role` enum('admin','staff') NOT NULL DEFAULT 'staff',
  `foto_profil` varchar(255) DEFAULT 'default.png',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `is_first_login` tinyint(1) DEFAULT 1,
  `lang` varchar(10) DEFAULT 'en',
  `notif_request` tinyint(1) DEFAULT 1,
  `notif_return` tinyint(1) DEFAULT 1,
  `notif_stock` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

ALTER TABLE `master_item`
  ADD PRIMARY KEY (`barcode`);

ALTER TABLE `request_form`
  ADD PRIMARY KEY (`request_id`);

ALTER TABLE `return_items`
  ADD PRIMARY KEY (`return_id`),
  ADD KEY `transaction_id` (`transaction_id`),
  ADD KEY `barcode` (`barcode`);

ALTER TABLE `transaksi`
  ADD PRIMARY KEY (`transaction_id`),
  ADD KEY `request_id` (`request_id`);

ALTER TABLE `transaksi_detail`
  ADD PRIMARY KEY (`id_detail`),
  ADD KEY `transaction_id` (`transaction_id`),
  ADD KEY `barcode` (`barcode`);

ALTER TABLE `return_items`
  MODIFY `return_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

ALTER TABLE `transaksi_detail`
  MODIFY `id_detail` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=28;

ALTER TABLE `return_items`
  ADD CONSTRAINT `return_items_ibfk_1` FOREIGN KEY (`barcode`) REFERENCES `master_item` (`barcode`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `return_items_ibfk_2` FOREIGN KEY (`transaction_id`) REFERENCES `transaksi` (`transaction_id`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `transaksi`
  ADD CONSTRAINT `transaksi_ibfk_1` FOREIGN KEY (`request_id`) REFERENCES `request_form` (`request_id`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `transaksi_detail`
  ADD CONSTRAINT `transaksi_detail_ibfk_1` FOREIGN KEY (`transaction_id`) REFERENCES `transaksi` (`transaction_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `transaksi_detail_ibfk_2` FOREIGN KEY (`barcode`) REFERENCES `master_item` (`barcode`) ON UPDATE CASCADE;