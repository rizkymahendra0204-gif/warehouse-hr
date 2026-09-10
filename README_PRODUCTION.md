# Warehouse HR — paket perbaikan menuju production

Paket ini memperbaiki source dari `warehouse-hr(2).zip` dan menyiapkan migrasi untuk `db_warehouse (8)(1).sql`. Desain, warna, dan stylesheet utama dipertahankan. **Ini kandidat untuk staging/UAT; pemasangan di server production tetap memerlukan konfigurasi dan uji penerimaan di bawah.** Tidak ada koneksi ke server/database operasional Anda selama pengerjaan.

## Isi paket

- Aplikasi lengkap dengan dependensi PHP yang sudah ada; koneksi database terpusat.
- `config/local.example.php`: contoh konfigurasi, tanpa password database nyata.
- `database/migrate.php`: pemeriksaan awal dan migrasi data lama, dijalankan melalui PHP CLI.
- `database/migrations/001_support_tables.sql`: tabel pendukung yang digunakan migrator; bukan pengganti seluruh proses migrasi.
- `database/grants.example.sql`: contoh hak akses akun aplikasi.
- `database/check.php`: pemeriksaan konfigurasi dan skema setelah pemasangan.
- `tests/run.py`: pengujian HTTP, database, dan konkurensi memakai data dummy dalam database sementara.
- `tests/results.json`: hasil pengujian yang benar-benar dijalankan pada paket ini.
- `docs/CHANGES.md` dan `docs/TESTING.md`: perubahan, bukti pengujian, dan batasannya.

**Jangan mengimpor `tests/schema.sql` ke database operasional. Jangan menimpa database lama dengan database kosong.** SQL sumber yang Anda unggah tidak diubah; migrator melakukan perubahan yang diperlukan pada salinannya atau database tujuan yang Anda pilih.

## 1. Persiapan server/staging

Gunakan PHP **8.3 atau versi lebih baru yang kompatibel dengan Composer lock**, dengan patch keamanan terbaru dari penyedia runtime. Aktifkan `pdo_mysql`, `mysqli`, `mbstring`, `gd`, `fileinfo`, `dom`, `xml`, `xmlreader`, `xmlwriter`, `simplexml`, dan `zip`, beserta ekstensi standar yang diperiksa Composer. Paket diuji pada PHP 8.3 dan MariaDB 10.11. MySQL 8 dan versi MariaDB lain perlu diuji ulang di staging; tidak diklaim sudah diuji pada semua versi.

Jika PHP XAMPP Anda masih 8.0.30, siapkan runtime staging yang lebih baru terlebih dahulu. Jangan sekadar mengganti berkas PHP di instalasi aktif tanpa memeriksa kompatibilitas aplikasi lain.

1. Buat folder staging terpisah, misalnya `C:\xampp\htdocs\warehouse-hr-staging`.
2. Ekstrak isi folder `warehouse-hr` dari ZIP ke sana. Jangan sampai menjadi folder bertingkat `warehouse-hr/warehouse-hr`.
3. Buat database staging terpisah dan pulihkan salinan backup database Anda ke sana.
4. Salin `config/local.example.php` menjadi `config/local.php`.
5. Isi `DB_HOST`, `DB_PORT`, `DB_NAME`, `DB_USER`, dan `DB_PASSWORD` sesuai database **staging**. Untuk staging, set `APP_ENV` menjadi `staging`, `APP_REQUIRE_HTTPS` menjadi `false`, dan `APP_BASE_PATH` sesuai folder, misalnya `/warehouse-hr-staging`.
6. Pastikan `session.save_path` di `php.ini` merupakan direktori yang ada dan dapat ditulis akun PHP. `assets/img/profile` juga perlu dapat ditulis akun web server. Source/config/vendor cukup dapat dibaca. Atur `display_errors=Off` dan `log_errors=On` pada konfigurasi PHP production, termasuk untuk error sebelum aplikasi dimuat.

Environment variable mengungguli nilai `config/local.php`. Jangan menyimpan password nyata di `local.example.php`, membagikan `local.php`, atau memasukkannya ke Git.

## 2. Backup dan migrasi

**Migrasi bukan proses otomatis saat membuka aplikasi.** Jalankan saat seluruh aplikasi yang menulis database yang sama sedang dihentikan/maintenance, termasuk aplikasi Request Form. DDL MySQL/MariaDB melakukan commit implisit: kegagalan di tengah migrasi tidak otomatis membatalkan ALTER yang sudah berhasil.

Simpan backup database, source lama, dan folder unggahan/foto di luar folder web. Contoh backup menggunakan CMD Windows, sesuaikan lokasi executable:

```bat
C:\xampp\mysql\bin\mysqldump.exe -h 127.0.0.1 -u AKUN_BACKUP -p --single-transaction --routines --triggers db_warehouse > D:\Backup\warehouse-sebelum-migrasi.sql
```

Uji bahwa backup tersebut dapat dipulihkan ke database lain. Database staging harus mempunyai nama berbeda dari production.

Jalankan migrator menggunakan akun database khusus migrasi yang memiliki hak `SELECT`, `INSERT`, `UPDATE`, `CREATE`, `ALTER`, `INDEX`, dan `REFERENCES` pada database tujuan. Akun aplikasi sehari-hari tidak memerlukan hak mengubah skema. Contoh PowerShell:

```powershell
Set-Location C:\xampp\htdocs\warehouse-hr-staging
$warehouseCredential = Get-Credential -UserName "warehouse_migrator" -Message "Akun migrasi database"
$env:DB_USER = $warehouseCredential.UserName
$env:DB_PASSWORD = $warehouseCredential.GetNetworkCredential().Password

# Pemeriksaan awal; tidak mengubah data/struktur
& C:\xampp\php\php.exe database\migrate.php

# Jalankan setelah backup dan penghentian seluruh writer benar-benar dilakukan
& C:\xampp\php\php.exe database\migrate.php --apply --backup-confirmed

Remove-Item Env:DB_USER, Env:DB_PASSWORD
```

Nama database diambil dari konfigurasi. Baca nama yang ditampilkan migrator sebelum meneruskan. `--backup-confirmed` merupakan pernyataan operator bahwa backup sudah tersedia; program tidak membuat backup atau menghentikan server untuk Anda.

Migrasi melakukan hal berikut:

- Menambahkan primary key/auto-increment pengguna, serta unique username. ID pengguna lama `0` dipertahankan; ID pengguna baru dibuat otomatis.
- Role lama kosong/`user` dipetakan ke `staff`, bukan admin. Nilai sebelumnya tersimpan di `users.legacy_role`.
- Memberikan ID unik pada setiap catatan aktivitas. ID lama disimpan di `log_activity.legacy_id`; tidak ada catatan log yang dihapus.
- Menambahkan unique request per transaksi, unique pasangan transaksi/barcode pada detail dan retur, serta foreign key retur ke detail transaksi asal.
- Menambahkan versi sesi akun, penyimpanan batas percobaan login, nomor urut transaksi yang dikunci saat digunakan, dan indeks untuk pencarian utama.

Program berhenti jika ditemukan duplikasi pengguna/request/detail/retur atau retur tanpa transaksi asal yang perlu direkonsiliasi. **Program tidak menghapus data bermasalah secara otomatis.** Perbaiki berdasarkan catatan operasional atau pulihkan backup jika perlu. Migrator dapat dijalankan ulang setelah penyebab kegagalan ditangani.

## 3. Akun database dan konfigurasi production

Setelah migrasi, buat akun runtime menggunakan `database/grants.example.sql` sebagai template. Ganti password contoh dengan password acak yang kuat; sesuaikan nama database dan host akun. Jangan menjalankan template yang masih berisi placeholder. Grant diberikan per tabel sehingga akun aplikasi tidak bisa mengubah skema atau menghapus riwayat stok/transaksi.

Untuk production, konfigurasi harus memuat:

```php
'APP_ENV' => 'production',
'APP_BASE_PATH' => '/warehouse-hr',
'APP_REQUIRE_HTTPS' => false,
'DB_HOST' => '127.0.0.1',
'DB_PORT' => '3306',
'DB_NAME' => 'db_warehouse',
'DB_USER' => 'warehouse_app',
'DB_PASSWORD' => 'PASSWORD_DATABASE_ANDA',
```

HTTPS bersifat opsional: konfigurasi bawaan mengizinkan HTTP, termasuk pada mode production. Pada instalasi yang sudah ada, ubah `APP_REQUIRE_HTTPS` menjadi `false` di `config/local.php`; pastikan environment variable dengan nama sama tidak masih bernilai `true`. HTTP tidak mengenkripsi password dan sesi saat melewati jaringan. Jika ingin mewajibkan HTTPS pada production, set `APP_REQUIRE_HTTPS` menjadi `true` dan pasang sertifikat yang dipercaya perangkat pengguna. Jika TLS berhenti di reverse proxy, isi `APP_TRUSTED_PROXY_IPS` dengan IP proxy yang tepat dan pastikan backend hanya menerima trafik dari proxy itu. Header `X-Forwarded-Proto` tidak dipercaya dari alamat lain. Akses HTTP production hanya ditolak jika `APP_REQUIRE_HTTPS` diaktifkan. Cookie sesi tetap memakai `HttpOnly` dan `SameSite=Lax`; atribut `Secure` mengikuti koneksi HTTPS. Header HSTS hanya dikirim pada HTTPS ketika kewajiban HTTPS diaktifkan.

Gunakan Apache dengan `mod_rewrite` dan pengaturan `AllowOverride` yang mengizinkan aturan `.htaccess`. Paket mempertahankan clean URL. Aturan redirect `.php` hanya diterapkan pada GET/HEAD agar isi POST tidak hilang. Untuk IIS/Nginx, terjemahkan pembatasan folder privat dan routing; `.htaccess` tidak berlaku di server tersebut.

Pastikan akses HTTP ke `config/local.php`, `database/migrate.php`, `tests/control.php`, `vendor`, berkas backup, dan direktori privat lainnya ditolak. Jangan letakkan SQL/ZIP backup di folder web. Jangan menjalankan PHP built-in server untuk production.

`REQUEST_UPLOAD_BASE_URL` menunjuk URL aplikasi Request Form. `REQUEST_UPLOAD_DIR` opsional menunjuk folder absolut bukti pembayaran jika ekspor Excel perlu menyisipkan gambar. Folder ini tidak tersedia dalam ZIP Warehouse HR. Pengaturan yang salah akan membuat bukti pembayaran tidak tersedia pada laporan; uji integrasi tersebut sebelum go-live.

## 4. Pemeriksaan sebelum go-live

Jalankan dari folder aplikasi:

```bat
C:\xampp\php\php.exe database\check.php
composer validate --no-check-publish
composer check-platform-reqs
composer audit --locked
```

Perintah Composer terakhir memerlukan internet untuk memeriksa advisori terkini. Lakukan kembali saat tanggal deployment; hasil audit lama bukan jaminan keamanan di masa depan. Dependency PhpSpreadsheet tetap dikunci pada versi 1.30.6 dalam paket ini, dan semua ekspor diuji kembali. Jangan mengedit atau menghapus `composer.lock` untuk menyembunyikan masalah dependency.

Jalankan UAT dengan staf dan admin, termasuk scan barcode fisik, kuantitas nol pada salah satu kategori, submit dua kali, retur barang dari transaksi yang salah, retur ulang, buka/tutup periode, foto profil, pembuatan pengguna, perubahan password, dan ekspor laporan berisi data nyata yang sudah dianonimkan. Rekonsiliasi ringkasan stok dan laporan dengan hitungan database. Laporan Finance mempertahankan filter request `FR` dari aplikasi lama; pastikan aturan tersebut memang sesuai kebutuhan operasional.

Saat go-live: hentikan writer, ambil backup terbaru, jalankan migrasi pada database tujuan, pasang paket beserta konfigurasi production, jalankan pemeriksaan dan smoke test, kemudian buka akses pengguna. Semua sesi login lama akan diminta login ulang.

## 5. Perilaku yang berubah

- Password baru minimal 12 karakter dan maksimal 72 byte. Password bcrypt lama tetap dapat dipakai; nilai hash dan MD5 tidak lagi diterima sebagai password.
- Akun baru wajib mengganti password sementara pada login pertama.
- Percobaan login gagal dibatasi per akun dan IP dalam jendela 15 menit. Pergantian password mencabut sesi lain; pengguna yang dihapus tidak bisa memakai sesi lamanya.
- Transaksi harus berasal dari request Pending dan memenuhi jumlah/jenis/gender barang. Request yang sudah diproses tidak dapat disimpan lagi.
- Barang hanya dapat diretur terhadap transaksi keluar terakhir yang benar dan hanya sekali per pasangan transaksi/barcode.
- Retur menghasilkan Available/Inactive. Saat periode dibuka admin, staff dapat mengevaluasi barang tersebut menjadi Active atau tetap Inactive. Status Sold Out hanya dibuat lewat transaksi.
- Kartu item baru muncul setelah scan lolos pemeriksaan database. Setelah kartu terakhir dihapus, daftar kembali kosong.
- Logout dan penggantian bahasa memakai POST dengan CSRF. Koneksi database yang belum dikonfigurasi tidak lagi menggunakan root/password kosong secara otomatis.
- JS/CSS Bootstrap, ikon, jQuery, dan JsBarcode disediakan lokal agar fungsi utama tidak bergantung pada CDN. Tampilan utama tetap menggunakan stylesheet lama.

## 6. Pemulihan dan operasi

Jika pemasangan gagal **sebelum akses pengguna dibuka**, tetap hentikan writer, pulihkan database dari backup ke database pemulihan, pasang source lama beserta konfigurasi yang cocok, verifikasi, lalu arahkan aplikasi kembali. Jangan mencoba mengembalikan DDL dengan menebak ALTER kebalikannya. Bila sudah ada transaksi baru setelah go-live, rekonsiliasikan transaksi tersebut sebelum pemulihan agar tidak hilang.

Sediakan backup terjadwal dan latihan restore, pembatasan akses database, pencatatan error PHP di luar folder web, serta pemantauan disk dan kegagalan transaksi. Atur pembersihan berkala `login_throttle` untuk baris dengan `window_start` lebih lama dari 1 hari; data ini hanya penghitung pembatas login, bukan riwayat transaksi. Ini perlu dijalankan oleh jadwal server Anda, bukan sudah diaktifkan oleh paket.
