# Perubahan paket

## Keamanan akses

Semua controller aplikasi diperiksa terhadap sesi login yang masih berlaku. Role dimuat kembali dari database agar perubahan/penghapusan akun tidak meninggalkan akses lama. Admin diwajibkan untuk pengelolaan pengguna serta buka/tutup periode; staff tetap dapat melakukan operasi gudang dan mengevaluasi barang Available/Inactive ketika periode terbuka.

CSRF ditambahkan pada semua form POST, login, logout, perubahan bahasa, fetch validasi barcode, dan AJAX simpan batch. Permintaan JSON mendapat respons JSON dengan status HTTP yang sesuai. Tindakan mutasi tidak lagi bergantung pada tombol yang ditampilkan browser.

Login hanya memakai `password_verify`; ID sesi diperbarui setelah autentikasi/perubahan password. Sesi mempunyai batas absolut 8 jam dan batas tanpa permintaan 30 menit; polling dihitung sebagai permintaan. Pembatas percobaan login disimpan di database agar tidak hilang ketika cookie dibersihkan. Cookie sesi memakai HttpOnly dan SameSite=Lax; Secure digunakan pada HTTPS. Akun baru wajib mengganti password pertama. Pergantian password mencabut sesi lain menggunakan `auth_version`.

## Data dan transaksi

Validasi request, kuantitas tiap kategori (termasuk nol), gender, status stok, dan duplikasi barcode dilakukan server. Barang dan request dikunci saat disimpan. Penomoran memakai baris sequence per tahun, diinisialisasi dari nomor transaksi lama, sehingga tidak bergantung pada `MAX + 1` yang dapat bertabrakan.

Retur wajib terkait transaksi keluar terakhir barcode tersebut. Seluruh item dalam satu transaksi/retur beserta log dicatat dalam satu transaksi database. Kegagalan log membatalkan perubahan stok dan header juga. Retur ulang ditolak oleh validasi, penguncian, dan constraint database. Penghitungan stok retur dalam evaluasi menggunakan barcode unik agar barang yang pernah mengalami beberapa siklus retur tidak terhitung berulang sebagai stok fisik.

Migrasi tidak menghapus catatan lama. ID log lama disimpan di `legacy_id`; ID pengguna `0` tetap berlaku; role kosong akibat ketidakcocokan form lama diubah menjadi staff dengan salinan nilai lama di `legacy_role`. Migrator memeriksa duplikasi/relasi terlebih dahulu dan dapat dijalankan ulang. Migrasi DDL bukan transaksi yang bisa di-rollback otomatis.

## UI dan integrasi

File `assets/css/style.css` dipertahankan identik dengan ZIP sumber. Perubahan markup dibatasi untuk token tersembunyi, form yang aman, informasi password, escaping data, penghapusan satu sel aksi duplikat yang berada di luar tabel, dan pembatasan pilihan status yang tidak sesuai alur transaksi.

Kartu item disembunyikan sebelum scan valid; scanner memanggil validasi database sebelum membuat kartu. Pindaian cepat diproses berurutan untuk mencegah duplikasi. Menghapus kartu terakhir mengosongkan daftar. Kuantitas nol juga diperiksa pada validasi browser.

Pencarian request manual mendapat endpoint yang sebelumnya tidak ada. Penambahan staff mengirim role yang sesuai enum. Pengelolaan pengguna kembali ke halaman `setting`. Bug `id_user` pada aktivasi akun diperbaiki menjadi `user_id`. Logout/ganti bahasa memakai POST.

Foto profil dibatasi 2 MB dan 2048×2048 piksel, diperiksa MIME serta isi gambar, lalu di-encode ulang sebagai PNG dengan nama acak. Isi request/nama pada HTML, JavaScript, dan Excel diberi encoding sesuai konteks. Jalur bukti pembayaran untuk ekspor dibatasi ke direktori yang dikonfigurasi.

## Konfigurasi

Database menggunakan konfigurasi lokal/environment terpusat untuk PDO maupun mysqli. Root/password kosong bukan fallback. Kesalahan internal masuk log server dan tidak ditampilkan sebagai SQL/detail koneksi kepada pengguna. Folder kode privat dan backup dibatasi melalui aturan Apache.

Persyaratan runtime Composer diperbarui ke PHP 8.3, pemeriksaan platform diaktifkan, dan autoloader dibangun ulang. Versi paket PHP yang terpasang tetap dipertahankan, termasuk PhpSpreadsheet 1.30.6. JS/CSS Bootstrap, ikon, jQuery, dan JsBarcode disediakan lokal dengan versi yang dipakai UI lama; sumber dan checksum tercatat dalam `ui-assets.json`. Font Inter masih menggunakan Google Fonts dengan fallback CSS yang ada.

## Hal yang perlu diverifikasi di server Anda

- Apache/clean URL pada subfolder yang benar, TLS, reverse proxy, izin direktori sesi/foto, dan kredensial database.
- Hak akses akun runtime dengan grant per tabel; jangan memakai akun migrasi untuk aplikasi harian.
- Integrasi Request Form dan direktori bukti pembayaran yang tidak disertakan dalam ZIP ini.
- Rekonsiliasi stok/laporan dengan definisi bisnis Anda, khususnya filter kode request `FR` yang dipertahankan dari source.
- Scanner fisik, printer label, beban kerja kantor, backup/restore, dan pembaruan dependency pada tanggal deployment.

Referensi implementasi: [OWASP Authorization](https://cheatsheetseries.owasp.org/cheatsheets/Authorization_Cheat_Sheet.html), [OWASP CSRF](https://cheatsheetseries.owasp.org/cheatsheets/Cross-Site_Request_Forgery_Prevention_Cheat_Sheet.html), [PHP session regeneration](https://www.php.net/manual/en/function.session-regenerate-id.php), [MariaDB FOR UPDATE](https://mariadb.com/docs/server/reference/sql-statements/data-manipulation/selecting-data/for-update). Daftar advisori [PhpSpreadsheet](https://packagist.org/packages/phpoffice/phpspreadsheet/advisories) yang diperiksa pada 9 September 2026 mencantumkan versi terdampak terakhir pada lini 1.30 sebagai 1.30.5; pemeriksaan ini bukan pengganti `composer audit --locked` untuk seluruh dependency saat deployment.

## Revisi HTTPS opsional

Sesuai permintaan pengguna, default `APP_REQUIRE_HTTPS` menjadi `false`, termasuk pada production. Pemeriksaan konfigurasi melaporkan pilihan ini sebagai informasi. HSTS hanya diaktifkan saat kewajiban HTTPS aktif; cookie Secure tetap mengikuti koneksi. Panduan menjelaskan konfigurasi instalasi baru dan yang sudah ada.
