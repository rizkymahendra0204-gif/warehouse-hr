# Pengujian

`tests/results.json` berisi daftar pemeriksaan dan hasil aktual. Pengujian memakai PHP CLI, MariaDB lokal terisolasi, server HTTP PHP khusus pengujian, dan data dummy. Source tidak diarahkan ke database operasional pengguna.

## Hasil aktual pada 9 September 2026

- **86/86 pemeriksaan lulus**: 85 pemeriksaan backend/HTTP/database/migrasi/ekspor dan satu rangkaian DOM + HTTP scanner.
- **54 file PHP** lolos `php -l`; JavaScript aplikasi lolos `node --check`.
- Composer manifest/lock valid dan semua persyaratan platform yang diperiksa tersedia pada runtime test. Audit dependency lengkap melalui Composer belum selesai; jalankan `composer audit --locked` saat deployment.
- Perbandingan dengan SQL asli: 1.431 item, 16 request, 7 transaksi, 15 detail, dan 2 retur tetap utuh. Seluruh isi 47 log lama dipertahankan; ID log baru unik. Dua akun dipertahankan, dengan satu role kosong diperbaiki menjadi staff.
- **Browser Chrome nyata belum berhasil dijalankan**: proses Chrome berhenti sebelum halaman dimuat pada lingkungan ini. Rangkaian Playwright disertakan untuk dijalankan di staging, dan tidak dihitung sebagai lulus. Uji DOM + HTTP tidak membuktikan rendering visual atau scanner fisik.
- Tidak ada pengujian terhadap server/database production pengguna.

## Menjalankan pengujian backend

Diperlukan Python 3, PHP CLI beserta ekstensi aplikasi, dan MySQL/MariaDB **lokal**. Akun test harus boleh membuat dan menghapus database dengan nama `wh_test_*`; jangan gunakan akun/database production.

Contoh PowerShell:

```powershell
Set-Location C:\xampp\htdocs\warehouse-hr-staging
$env:WH_PHP = "C:\xampp\php\php.exe"
$env:DB_HOST = "127.0.0.1"
$env:DB_PORT = "3306"
$warehouseTestCredential = Get-Credential -UserName "warehouse_test" -Message "Akun database test lokal"
$env:DB_USER = $warehouseTestCredential.UserName
$env:DB_PASSWORD = $warehouseTestCredential.GetNetworkCredential().Password
python tests\run.py
Remove-Item Env:DB_USER, Env:DB_PASSWORD
```

Runner membuat database dengan nama acak `wh_test_` diikuti 12 digit heksadesimal, memuat schema tanpa data pengguna, memasang fixture dummy, menjalankan migrasi, memulai server HTTP sementara, menguji aplikasi, lalu menghapus database yang dibuatnya. Helper CLI menolak nama database lain dan host nonlokal. Test konkurensi menggunakan beberapa proses PHP terpisah sehingga tidak mengandalkan kemampuan multiworker PHP development server di Windows.

Jika proses dihentikan paksa, database test mungkin tertinggal. Pastikan nama database sesuai pola test dan bukan data operasional sebelum administrator menghapusnya.

## Menjalankan pengujian DOM + HTTP yang sudah diverifikasi

Dari folder aplikasi, setelah menyiapkan environment database/PHP di bawah:

```powershell
npm install --prefix tests
$env:WH_DOM_TEST = "1"
python tests\run.py
Remove-Item Env:WH_DOM_TEST
```

Ini menjalankan JavaScript dan jQuery asli aplikasi dengan jsdom serta validasi HTTP nyata ke server test. Mode ini memeriksa kartu kosong, barcode tidak terdaftar, scan valid/duplikat, penghapusan kartu terakhir, scan cepat, validasi, dan pengiriman form yang sebenarnya. Jalankan mode DOM dan mode browser secara terpisah.

## Menjalankan pengujian browser

Diperlukan Node.js dan Chromium untuk Playwright. Dari folder aplikasi:

```powershell
npm install --prefix tests
npx --prefix tests playwright install chromium
$env:WH_UI_TEST = "1"
python tests\run.py
Remove-Item Env:WH_UI_TEST
```

Runner backend harus mendapat environment PHP/database seperti contoh sebelumnya. Browser menguji form login/CSRF, tidak ada kartu kosong, scan tidak dikenal, scan valid, scan berulang, penghapusan kartu terakhir, scan beruntun, validasi dan submit transaksi, penggantian bahasa, serta tidak ada error JavaScript tak tertangani. Semua akun dan barang yang dipakai merupakan fixture dummy.

`WH_CHROMIUM_PATH` opsional jika memakai executable Chrome/Chromium yang sudah tersedia. `WH_PLAYWRIGHT_MODULE` opsional untuk lingkungan CI yang menyediakan Playwright di lokasi lain. Tidak diperlukan untuk instalasi normal melalui `tests/package.json`.

## Cakupan dan batas

Pengujian mencakup autentikasi, role, CSRF, migrasi berulang, pelestarian ID lama, pembatasan login, aktivasi akun, pencabutan sesi, validasi stok, retur silang, retur ganda, transaksi bersamaan, rollback ketika log gagal, foto palsu/valid, dan file XLSX yang dihasilkan. Kode PHP juga diperiksa menggunakan `php -l`, JavaScript dengan `node --check`, serta persyaratan Composer dengan `check-platform-reqs`.

Pengujian HTTP menggunakan `tests/router.php`, sehingga **tidak memvalidasi konfigurasi Apache/IIS/Nginx production**. Jalankan UAT pada server staging yang sama jenisnya dengan server production. Belum mencakup uji beban skala kantor, sertifikat TLS, scanner/printer fisik, pemulihan backup di infrastruktur Anda, atau seluruh alur aplikasi Request Form eksternal.

Jangan mempublikasikan folder test dan jangan menjalankan server PHP development sebagai server production. `.htaccess` paket memblokir folder test pada Apache; web server lain memerlukan konfigurasi setara.
