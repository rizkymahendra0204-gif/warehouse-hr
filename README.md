# Sistem Manajemen Pengelolahan Seragam SA

Sistem Informasi Manajemen Pengelolahan Seragam SA berbasis web yang dirancang untuk mengelola lalu lintas barang keluar, pencatatan pengembalian (_return_), serta menyajikan laporan rekapitulasi secara otomatis dan akurat.

---

## 📌 Fitur Utama Sistem

### 1. Manajemen Master Data Item (`master_item`)

- Pencatatan data barang/pakaian (tipe, _gender_, dan pemetaan _barcode_ unik).
- Manajemen stok dan identifikasi barang berdasarkan pemindaian _barcode_.

### 2. Transaksi Barang Keluar (`request_form` & `transaksi`)

- Pencatatan formulir permintaan barang berdasarkan Perusahaan dan _Sales Assistant_ (SA).
- Pemindaian (_scan_) _barcode_ item untuk pencatatan transaksi barang keluar.
- Pengelompokan data transaksi berdasarkan ID Transaksi unik.

### 3. Pengembalian Barang / Return (`return_items`)

- Pencatatan barang yang dikembalikan (_return_) oleh SA/Perusahaan.
- Validasi pengembalian barang yang terhubung langsung dengan ID Transaksi awal.

### 4. Modul Pelaporan & Monitoring (`laporan.php`)

- **Filter Periode:** Filter data transaksi berdasarkan rentang tanggal (_start date_ s.d. _end date_).
- **Perhitungan Net Quantity:** Kalkulasi otomatis jumlah bersih barang keluar dengan rumus:  
  $$\text{TOTAL (PCS)} = \text{Total Barang Diberikan} - \text{Total Barang Di-return}$$
- **Pengelompokan Rincian Item:** Menampilkan gabungan item beserta jumlahnya secara ringkas (contoh: `Baju Pria (1), Celana Pria (1)`).
- **Pencatatan Return Terpisah:** Menampilkan rincian barang yang di-return dengan penanda khusus.

### 5. Ekspor Laporan Excel (`export_laporan.php`)

- Ekspor data laporan berformat Microsoft Excel (`.xlsx`) secara _real-time_.
- Menggunakan library **PhpSpreadsheet** dengan pemformatan profesional:
  - Header tabel dengan skema warna khusus.
  - Penyesuaian lebar kolom otomatis (_auto-fit column width_).
  - Border dan tata letak teks yang teratur.
  - Fitur _output buffer sanitization_ untuk menjamin file tidak korup.

---

## 🛠️ Teknologi & Tools

- **Backend:** PHP (PDO Extension)
- **Database:** MySQL / MariaDB
- **Environment:** XAMPP (Apache & MySQL)
- **Dependency Manager:** [Composer](https://getcomposer.org/)
- **Libraries:** [PhpOffice/PhpSpreadsheet](https://github.com/PHPOffice/PhpSpreadsheet)
- **Frontend & Styling:** HTML5, CSS3, JavaScript, Bootstrap 5

---

## 🗄️ Skema & Relasi Database

Proyek ini terhubung melalui 4 tabel utama:

| Tabel              | Deskripsi                                                                                       |
| :----------------- | :---------------------------------------------------------------------------------------------- |
| **`master_item`**  | Menyimpan data detail barang (`barcode`, `tipe`, `gender`).                                     |
| **`request_form`** | Menyimpan data pemohon (`request_id`, `perusahaan`, `nama_sa`).                                 |
| **`transaksi`**    | Menyimpan transaksi barang keluar (`transaction_id`, `request_id`, `barcode`, `tgl_transaksi`). |
| **`return_items`** | Menyimpan data barang yang di-return (`transaction_id`, `barcode`).                             |
| **`setting`**      | Untuk menutup dan membuka Periode Audit (`setting_key`, `setting_value`).                       |
| **`users`**        | Menyimpan data user untuk Login (`id`, `username`, `password`).                                 |

---

## 📂 Struktur Folder Proyek

```text
WAREHOUSE-HR/
├── assets/                    # Asset statis frontend
│   ├── css/
│   │   └── style.css          # File styling utama
│   ├── img/                   # Asset gambar
│   └── js/
│       └── scripts.js         # Script JavaScript frontend

├── controllers/               # LOGIKA PEMROSESAN BACKEND & QUERY
│   ├── cek_barcode.php        # Pengecekan status barcode (BERHUBUNGAN DENGAN HALAMAN STOK BARANG)
│   ├── export_excel.php       # Handler ekspor laporan ke format Excel (.xlsx)
│   ├── proses_generate.php    # Proses generate data/barcode (BERHUBUNGAN DENGAN HALAMAN GENERATE BARCODE)
│   ├── proses_return.php      # Logika pemrosesan transaksi return (BERHUNGAN DENGAN HALAMAN RETURN)
│   ├── proses_tambah_item.php # Proses penambahan item baru (BERHUBUNGAN DENGAN HALAMAN STOK GUDANG)
│   ├── proses_transaksi.php   # Logika pemrosesan barang keluar (BERHUBUNGAN DENGAN HALAMAN TRANSAKSI)
│   ├── query_audit.php        # Query data audit ( BERHUBUNGAN DENGAN AUDIT_ITEM)
│   ├── query_dashboard.php    # Query data statistik dashboard
│   ├── query_laporan.php      # Query rekapitulasi laporan transaksi (BERHUBUNGAN DENGAN HALAMAN LAPORAN)
│   ├── query_pending.php      # Query data transaksi pending (BERHUBUNGAN DENGAN HALAMAN PENDING)
│   ├── query_stokbarang.php   # Query manajemen stok (BERHUBUNGAN DENGAN HALAMAN STOK GUDANG)
│   ├── query_transaksi.php    # Query riwayat transaksi (BERHUBUNGAN DENGAN HALAMAN TRANSAKSI)
│   └── validate_barcode.php   # Validasi input barcode (BERHUBUNGAN DENGAN HALAMAN TRANSAKSI)

├── includes/                  # Komponen UI modular & konfigurasi global
│   ├── db.php                 # Koneksi database PDO
│   ├── sidebar.php            # Komponen navigasi samping
│   └── topbar.php             # Komponen navigasi atas

├── vendor/                    # Dependensi Composer (PhpSpreadsheet, dll.)

├── audit_item.php             # Halaman untuk mengubah status transaksi dan barang
├── change_password.php        # Halaman mengubah password saat pertama kali login

├── composer.json              # Konfigurasi dependensi PHP
├── composer.lock              # Lockfile versi dependensi Composer

├── generate_barcode.php       # Halaman generate barcode item
├── index.php                  # Halaman utama / Dashboard
├── laporan.php                # Halaman tampilan laporan transaksi
├── log_activity.php           # Halaman catatan log aktivitas sistem
├── pending.php                # Halaman kelola transaksi pending
├── login.php                  # Halaman untuk login
├── logout.php                 # logout
├── return.php                 # Halaman input pengembalian barang (return)
├── stok_barang.php            # Halaman manajemen stok barang
└── transaksi.php              # Halaman input transaksi barang keluar
```
