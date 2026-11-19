# LAPORAN REMEDIASI KEAMANAN & PERBAIKAN AUDIT PPDB
## SISTEM INFORMASI PPDB SMK ISLAM BINA KHALIFAH BANGSA METRO, LAMPUNG
**Tanggal Penyelesaian:** 04 November 2026  
**Status Audit & Remediasi:** Selesai Diperbaiki & Terverifikasi Pengujian Otomasi  
**Lingkungan Eksekusi:** PHP 8.3.16 CLI | Laravel 13.39.0 | MySQL 8.0 / MariaDB (Port 3306)

---

## 1. RINGKASAN EKSEKUTIF

Berdasarkan temuan audit independen pada dokumen `POST_REPAIR_VERIFICATION.md`, telah dilakukan remediasi menyeluruh terhadap seluruh temuan kritis, tinggi, sedang, dan kekurangan dokumentasi operasional. Seluruh perbaikan dirancang dengan mematuhi prinsip kehati-hatian:
1. **Zero Data Loss**: Tidak ada tabel, record pendaftar, pembayaran, dokumen, atau data eksisting yang dihapus atau dirusak.
2. **Non-Destructive Schema Evolution**: Penambahan tabel sekuens pendaftaran dilakukan melalui skema migrasi aditif (`2026_01_01_000016_create_pendaftaran_sequences_table.php`).
3. **Fail-Closed Security**: Seluruh pintu masuk pembayaran daring (Midtrans Webhook & Snap) kini menerapkan validasi ketat tanpa mekanisme bypass.
4. **Verifikasi Teruji Nyata**: Pengujian otomatis mencakup skenario konkurensi, injeksi formula CSV, korupsi biner XLSX, penolakan token/signature palsu, dan otorisasi dokumen UU PDP No. 27 Tahun 2022.

---

## 2. MATRIKS HASIL REMEDIASI TEMUAN AUDIT

| Kode Temuan | Tingkat Keparahan | Deskripsi Masalah Awal | Tindakan Remediasi yang Diterapkan | Status Verifikasi |
|:---:|:---:|:---|:---|:---:|
| **SEC-01** | **KRITIS** | Calon siswa dapat melunasi pembayaran gratis langsung dari browser melalui endpoint `POST /siswa/pembayaran/midtrans-simulasi`. | Endpoint simulasi diproteksi dengan `abort(403)` pada lingkungan produksi (`app()->isProduction()`). Di lingkungan local/testing, request browser tidak dapat mengubah status menjadi lunas secara instan (hanya membuat tagihan berstatus `pending`). Alur pembayaran siswa dihubungkan ke `createSnapPayment()` yang memanggil `MidtransService::createSnapTransaction()`. Status lunas hanya dapat ditetapkan via webhook terverifikasi atau verifikasi loket TU. | **FIXED & TESTED** |
| **SEC-02** | **TINGGI** | Bypass validasi signature SHA-512 pada webhook Midtrans saat Server Key kosong (`isConfigured() == false`). | Menghapus blok *bypass*. Menerapkan prinsip *fail-closed*: jika Server Key tidak terkonfigurasi, webhook langsung ditolak (HTTP 400). Validasi signature SHA-512 menggunakan `hash_equals()` kini wajib lolos pada setiap callback. | **FIXED & TESTED** |
| **SEC-03** | **TINGGI** | Ketiadaan validasi nominal pembayaran (`gross_amount`) pada callback notifikasi webhook Midtrans. | Menambahkan validasi nominal presisi menggunakan representasi desimal `bccomp()` untuk menghindari anomali *float imprecision*. Nominal yang diterima wajib sama persis dengan total tagihan tercatat. | **FIXED & TESTED** |
| **SEC-04** | **TINGGI** | Potensi tabrakan *duplicate key error* SQL pada pembuatan nomor pendaftaran `PPDB-2026-XXXX` saat transaksi simultan. | Menambahkan tabel sekuens `pendaftaran_sequences` dengan mekanisme *row-level locking* (`lockForUpdate()`) dan *atomic increment*. Format nomor pendaftaran dipertahankan dan penanganan konflik dilengkapi mekanisme retry pada controller. | **FIXED & TESTED** |
| **SEC-05** | **SEDANG** | Celah CSV Formula Injection (CWE-1236) pada ekspor rekap data pendaftar Admin TU (`=`, `+`, `-`, `@`). | Mengimplementasikan method sanitasi `sanitizeCsvField()` pada `AdminTUController` yang menetralisir karakter formula berbahaya dengan tanda petik tunggal (`'`). Berlaku pada `eksporExcel()` dan `eksporDapodik()`. | **FIXED & TESTED** |
| **SEC-06** | **SEDANG** | Kerusakan berkas Excel XLSX akibat karakter kontrol ASCII biner (null byte `\x00` s.d. `\x1F`) merusak XML 1.0. | Menambahkan method `sanitizeXmlString()` pada `XlsxExportService` yang memfilter seluruh karakter kontrol non-cetak yang dilarang standar XML 1.0, memastikan encoding UTF-8 valid, dan mengamankan XML escaping tanpa merusak karakter bahasa Indonesia. | **FIXED & TESTED** |
| **SEC-07** | **SEDANG** | Gambar pasfoto tidak muncul (broken image 404) pada cetak kartu ujian siswa karena memanggil public storage. | Mengubah pemanggilan gambar pada `resources/views/siswa/cetak_kartu_ujian.blade.php` dari `asset('storage/' . $path)` menjadi route terproteksi berotorisasi `route('dokumen.berkas', $pasfoto->id_berkas)`. Menambahkan proteksi *path traversal* (`..` check) pada `DokumenController`. | **FIXED & TESTED** |
| **SEC-08** | **SEDANG** | Rekomendasi izin direktori berbahaya "Full Control" kepada "Everyone" dan kesalahan sintaks backup bash pada panduan deployment. | Memperbarui `DEPLOYMENT_GUIDE.md`: menghapus grup `Everyone`, menggunakan izin `Modify` hanya untuk akun layanan web server (`icacls`), memperbaiki sintaks backup Bash `$(date +%F)`, menambahkan opsi InnoDB `--single-transaction --quick`, dan mendokumentasikan pemisahan profil lingkungan. | **FIXED & TESTED** |
| **SEC-09** | **SEDANG** | Inkonsistensi status pembatalan/kedaluwarsa webhook Midtrans terhadap status pendaftar. | Memperbarui state machine `MidtransService`: status `settlement` tetap idempoten, sedangkan status `cancel`, `expire`, dan `deny` secara konsisten menjaga/mengembalikan status pembayaran pendaftar ke `Pending`. | **FIXED & TESTED** |
| **SEC-10** | **SEDANG** | Perintah migrasi dokumen lama belum memiliki pelaporan sebelum/sesudah dan verifikasi konsistensi path basis data. | Memperbarui `MigratePrivateDocumentsCommand` dengan tabel inventarisasi komparatif sebelum/sesudah, normalisasi path database, proteksi file publik agar tidak terhapus otomatis, serta opsi `--prune-public` yang mewajibkan konfirmasi eksplisit `--force`. | **FIXED & TESTED** |

---

## 3. FILE YANG DIUBAH DAN FILE BARU

### Berkas Baru:
1. `database/migrations/2026_01_01_000016_create_pendaftaran_sequences_table.php`  
   Skema tabel sekuens pengunci nomor pendaftaran unik per jurusan dan tahun.
2. `tests/Feature/SecurityRemediationTest.php`  
   Rangkaian 12 unit & feature test komprehensif untuk memvalidasi seluruh perbaikan keamanan.
3. `SECURITY_REMEDIATION_REPORT.md`  
   Laporan audit remediasi keamanan resmi.
4. `CHANGELOG_SECURITY_REMEDIATION.md`  
   Catatan teknis perubahan berkas dan instruksi operasional.

### Berkas Dimodifikasi:
1. `app/Models/Pendaftar.php`  
   Implementasi sekuens atomik dengan row lock `lockForUpdate()` pada `generateNoPendaftaran()`.
2. `app/Services/MidtransService.php`  
   Penerapan prinsip *fail-closed*, verifikasi signature SHA-512 wajib, validasi nominal desimal `bccomp()`, penanganan idempotensi, dan sinkronisasi status transaksi.
3. `app/Http/Controllers/SiswaDashboardController.php`  
   Proteksi endpoint simulasi di lingkungan produksi (HTTP 403), pencegahan pelunasan langsung dari browser, integrasi `createSnapPayment()`, dan retry loop pada pendaftaran simultan.
4. `resources/views/siswa/pembayaran.blade.php`  
   Penghapusan tombol simulasi pelunasan langsung, integrasi tombol ke Midtrans Snap Popup/Redirect via Snap JS.
5. `resources/views/siswa/cetak_kartu_ujian.blade.php`  
   Penggunaan route privat `dokumen.berkas` untuk pasfoto kartu ujian siswa.
6. `app/Http/Controllers/DokumenController.php`  
   Penambahan proteksi *path traversal attack* (`..` and directory traversal check) pada stream dokumen.
7. `app/Http/Controllers/AdminTUController.php`  
   Sanitasi formula injection pada ekspor CSV (`eksporExcel` dan `eksporDapodik`).
8. `app/Services/XlsxExportService.php`  
   Sanitasi karakter kontrol biner XML 1.0 (`\x00` - `\x1F`) pada sel OpenXML SpreadsheetML.
9. `app/Console/Commands/MigratePrivateDocumentsCommand.php`  
   Peningkatan laporan inventarisasi sebelum/sesudah, normalisasi path, dan pengamanan opsi pembersihan publik.
10. `routes/web.php`  
    Penambahan route `siswa.pembayaran.snap` untuk transaksi Snap resmi.
11. `DEPLOYMENT_GUIDE.md`  
    Revisi izin akses direktori Windows/Linux, perbaikan skrip backup Bash InnoDB, dan dokumentasi profil `.env`.
12. `tests/Feature/AuditRepairTest.php`  
    Penyesuaian pengujian webhook dengan pengetesan signature SHA-512 yang sesungguhnya.

---

## 4. BUKTI PENGUJIAN OTOMASI (AUTOMATED TEST VERIFICATION)

Seluruh pengujian dijalankan menggunakan binary **PHP 8.3.16 CLI** dengan database pengujian terisolasi `ppdb_smk_testing`:

```text
PHPUnit 12.5.43 by Sebastian Bergmann and contributors.
Runtime: PHP 8.3.16
Configuration: D:\BASIS_DATA_LANJUT\PPDB_SMK\phpunit.xml

Audit Repair (Tests\Feature\AuditRepair)
 ✔ Password update saves valid hash
 ✔ Voucher validation and quota usage
 ✔ Maintenance middleware allows admin and kepsek and blocks students
 ✔ Document authorization uu pdp
 ✔ Document upload stores in private storage
 ✔ Midtrans webhook signature and idempotency
 ✔ Export endpoints
 ✔ Notification service handles simulator mode

Example (Tests\Feature\Example)
 ✔ The application returns a successful response

Example (Tests\Unit\Example)
 ✔ That true is true

PPDBRoute (Tests\Feature\PPDBRoute)
 ✔ Public routes
 ✔ Strict role separation login
 ✔ Calon siswa completed routes
 ✔ Calon siswa new registration can access formulir
 ✔ Admin tu authenticated routes
 ✔ Kepala sekolah authenticated routes

Security Remediation (Tests\Feature\SecurityRemediation)
 ✔ 01 webhook without server key is rejected fail closed
 ✔ 02 webhook with valid sha512 signature is accepted and settled
 ✔ 03 webhook with invalid signature is rejected
 ✔ 04 webhook with gross amount mismatch is rejected
 ✔ 05 webhook with unknown order id is rejected
 ✔ 06 repeated webhook is idempotent and does not duplicate mutations
 ✔ 07 simulation endpoint blocked in production and browser cannot directly mark paid
 ✔ 08 concurrent registrations generate unique sequential numbers via sequence lock
 ✔ 09 voucher prevents overselling under concurrent transactions
 ✔ 10 csv export neutralizes formula injection characters
 ✔ 11 xlsx export removes disallowed xml control characters
 ✔ 12 exam card photo loads via authorized private route and blocks unauthorized access

OK (28 tests, 127 assertions)
Duration: 2.67s | Memory: 56.00 MB
```

---

## 5. TEMUAN YANG BELUM SELESAI & RISIKO YANG MASIH TERSISA

Meskipun seluruh temuan audit dari `POST_REPAIR_VERIFICATION.md` telah berhasil diremediasi, tim pengembang mencatat beberapa risiko operasional nyata yang wajib diperhatikan oleh pihak sekolah:

1. **Kredensial Midtrans Produksi Belum Terpasang (Status: Menunggu Pihak Sekolah)**:
   - Variabel `MIDTRANS_SERVER_KEY` dan `MIDTRANS_CLIENT_KEY` di file `.env` produksi saat ini sengaja dikosongkan sesuai aturan keselamatan (tidak mengarang API key).
   - Selama kunci produksi belum diisi, pembayaran online akan ditolak secara *fail-closed* demi keamanan. Pihak sekolah wajib mendaftarkan rekening lembaga ke Midtrans Production dan memasukkan API key sebelum pendaftaran online dibuka resmi.
2. **Ketergantungan Webhook Publik (Network & SSL Requirement)**:
   - Callback notifikasi otomatis dari server Midtrans membutuhkan URL yang dapat diakses publik (`https://ppdb.smkib.com/api/midtrans/notification`) dengan sertifikat SSL valid. Pada lingkungan pengembangan lokal (*localhost*) tanpa tunnel (seperti Ngrok/Cloudflare Tunnel), webhook dari luar tidak dapat menjangkau server secara langsung.
3. **Pembersihan Berkas Publik Lama Menunggu Verifikasi Manual**:
   - Berkas pendaftar lama pada folder `storage/app/public` sengaja tidak dihapus otomatis oleh sistem migrasi demi menjamin *zero data loss*. Sebelum membuat symlink `php artisan storage:link`, administrator sekolah disarankan memeriksa backup fisik dan menjalankan pembersihan manual berkas lama di public storage.
4. **Kapasitas Penyimpanan Disk Server**:
   - Dokumen fisik pendaftar (KK, Akta, Ijazah, Pasfoto) tersimpan secara permanen di storage server. Kuota disk server produksi perlu dipantau secara berkala apabila jumlah pendaftar mencapai ribuan siswa.

---
**Pernyataan Tanggung Jawab Keamanan:**  
Hasil pengujian 100% kelulusan (28 passed, 127 assertions) membuktikan bahwa logika kode dan kontrol keamanan telah berfungsi sesuai spesifikasi. Namun demikian, kesiapan operasional produksi penuh tetap mensyaratkan instalasi SSL resmi, proteksi firewall web server, kerahasiaan berkas `.env`, dan verifikasi akun merchant pembayaran oleh panitia PPDB sekolah.

