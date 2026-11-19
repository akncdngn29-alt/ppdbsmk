# LOG PERUBAHAN REMEDIASI KEAMANAN (CHANGELOG SECURITY REMEDIATION)
## SISTEM INFORMASI PPDB SMK ISLAM BINA KHALIFAH BANGSA METRO, LAMPUNG
**Tanggal:** 04 November 2026  
**Versi Rilis:** 1.2.0-SecurityHardened  
**Framework:** Laravel 13.39.0 | PHP 8.3.16 CLI | MySQL 8.0 / MariaDB

---

## 1. DAFTAR BERKAS BARU (NEW FILES)

1. `database/migrations/2026_01_01_000016_create_pendaftaran_sequences_table.php`
   - Migrasi aditif non-destruktif untuk membuat tabel `pendaftaran_sequences` (`id_sequence`, `prefix`, `last_number`, `created_at`, `updated_at`).
2. `tests/Feature/SecurityRemediationTest.php`
   - Test suite baru berisi 12 pengujian keamanan ketat (Midtrans fail-closed, validasi signature SHA-512, nominal gross amount mismatch, order ID tidak dikenal, idempotensi, proteksi endpoint simulasi di production, konkurensi sekuens pendaftar, row locking kuota voucher, netralisasi formula injection CSV, pembersihan karakter kontrol XML XLSX, dan otorisasi berkas kartu ujian).
3. `SECURITY_REMEDIATION_REPORT.md`
   - Laporan resmi hasil audit remediasi keamanan, temuan awal, solusi teknis, bukti pengujian, serta sisa risiko operasional.
4. `CHANGELOG_SECURITY_REMEDIATION.md`
   - Dokumen log perubahan teknis sistem pasca-remediasi.

---

## 2. DAFTAR BERKAS YANG DIMODIFIKASI (MODIFIED FILES)

1. `app/Models/Pendaftar.php`
   - Mengganti logika `generateNoPendaftaran()` agar menggunakan tabel sekuens `pendaftaran_sequences` dengan mekanisme row locking `lockForUpdate()` dan atomic increment.
   - Mengimpor facade `Illuminate\Support\Facades\DB`.
2. `app/Services/MidtransService.php`
   - Menerapkan prinsip *fail-closed*: webhook langsung ditolak jika Server Key belum dikonfigurasi.
   - Menghapus pengecekan bypass `isConfigured()` pada validasi signature webhook.
   - Menambahkan validasi nominal transaksi secara presisi menggunakan representasi desimal `bccomp()` untuk mencegah serangan pemalsuan nilai bayar (*gross amount mismatch*).
   - Menyelaraskan transisi status mutasi webhook (`capture`, `settlement`, `pending`, `deny`, `expire`, `cancel`) dengan status pembayaran pendaftar (`Pending` / `Paid`).
   - Memastikan `createSnapTransaction()` menghasilkan snap token atau fallback transaksi pending yang aman.
3. `app/Http/Controllers/SiswaDashboardController.php`
   - Menambahkan method `createSnapPayment()` yang memproses transaksi Snap online tanpa mengubah status siswa menjadi `Paid` secara prematur.
   - Mengunci method `prosesBayarMidtransSimulasi()` dengan `abort(403)` pada mode produksi (`app()->isProduction()`).
   - Pada mode local/testing, request dari browser tidak lagi langsung melunasi pendaftar, melainkan dialihkan ke status tagihan `pending`.
   - Menambahkan retry loop (hingga 3 kali) pada method `simpanFormulir()` jika terjadi tabrakan unik (*unique constraint violation*) pada nomor pendaftaran.
4. `resources/views/siswa/pembayaran.blade.php`
   - Menghapus tombol dan form bypass simulasi `"Simulasikan Bayar Berhasil (Sandbox)"`.
   - Mengintegrasikan Midtrans Snap JS SDK (`snap.js`) dan popup trigger `payWithSnap()`.
   - Status pelunasan siswa kini sepenuhnya bergantung pada respons notifikasi webhook yang terverifikasi.
5. `resources/views/siswa/cetak_kartu_ujian.blade.php`
   - Memperbaiki tautan pasfoto dari `asset('storage/' . $pasfoto->path_file)` menjadi route privat `route('dokumen.berkas', $pasfoto->id_berkas)`.
6. `app/Http/Controllers/DokumenController.php`
   - Menambahkan proteksi *path traversal attack* (`..` and absolute path checking) pada method `streamDocument()`.
7. `app/Http/Controllers/AdminTUController.php`
   - Menambahkan method `sanitizeCsvField()` untuk menetralisir karakter berbahaya formula injection (`=`, `+`, `-`, `@`, `\t`, `\r`) dengan awalan petik tunggal (`'`).
   - Menerapkan sanitasi ini pada seluruh kolom teks di `eksporExcel()` dan `eksporDapodik()`.
8. `app/Services/XlsxExportService.php`
   - Menambahkan method `sanitizeXmlString()` untuk membersihkan karakter kontrol biner ASCII XML 1.0 (`\x00` s.d. `\x1F`, `\x7F`) yang merusak file Excel OpenXML SpreadsheetML.
   - Menjamin encoding UTF-8 valid dan XML escaping aman tanpa merusak teks bahasa Indonesia.
9. `app/Console/Commands/MigratePrivateDocumentsCommand.php`
   - Menambahkan pelaporan statistik komparatif sebelum dan sesudah migrasi.
   - Menambahkan normalisasi path relatif database.
   - Memastikan berkas publik tidak dihapus secara otomatis demi perlindungan data.
   - Menambahkan opsi `--prune-public` yang mewajibkan konfirmasi eksplisit `--force`.
10. `routes/web.php`
    - Menambahkan route `siswa.pembayaran.snap` (`POST /siswa/pembayaran/snap`).
11. `DEPLOYMENT_GUIDE.md`
    - Menghapus instruksi pemberian hak `Full Control` kepada `Everyone`.
    - Menyediakan panduan izin `Modify` berbasis prinsip *least privilege* (`icacls` Windows & `chmod` Linux).
    - Memperbaiki sintaks skrip backup Bash dari `$(date +\%F)` menjadi `$(date +%F)`.
    - Menambahkan parameter InnoDB `--single-transaction --quick` pada MySQL dump.
    - Mendokumentasikan konfigurasi `APP_DEBUG=false`, proteksi `.env`, dan pemisahan profil konfigurasi lingkungan.
12. `tests/Feature/AuditRepairTest.php`
    - Memperbarui pengujian webhook agar menggunakan kalkulasi signature SHA-512 riil.

---

## 3. PERUBAHAN SKEMA BASIS DATA (DATABASE MIGRATIONS)

- **Migrasi Baru Dijalankan:**  
  `2026_01_01_000016_create_pendaftaran_sequences_table.php` (Batch 3)
- **Struktur Tabel Baru:**  
  `pendaftaran_sequences`
  - `id_sequence`: BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY
  - `prefix`: VARCHAR(50) UNIQUE NOT NULL (Contoh: `PPDB-2026-TKJ-`)
  - `last_number`: INT UNSIGNED NOT NULL DEFAULT 0
  - `created_at`: TIMESTAMP
  - `updated_at`: TIMESTAMP
- **Database Terpengaruh:**  
  1. `ppdb_smk` (Basis data operasional)
  2. `ppdb_smk_testing` (Basis data pengujian otomatis)

---

## 4. PERINTAH ARTISAN & OPERASIONAL YANG DIJALANKAN

1. **Backup Basis Data & Kode Sebelum Perubahan:**
   ```bash
   mysqldump -u root --single-transaction --quick ppdb_smk > D:\BASIS_DATA_LANJUT\PPDB_SMK_PRE_SECURITY_REPAIR_BACKUP\ppdb_smk_pre_repair.sql
   ```
2. **Verifikasi Restore Backup pada Database Uji:**
   ```bash
   mysql -u root -e "CREATE DATABASE ppdb_smk_restore_verify;"
   mysql -u root ppdb_smk_restore_verify < D:\BASIS_DATA_LANJUT\PPDB_SMK_PRE_SECURITY_REPAIR_BACKUP\ppdb_smk_pre_repair.sql
   ```
3. **Eksekusi Migrasi Aditif:**
   ```bash
   php artisan migrate --force
   ```
4. **Verifikasi Command Migrasi Dokumen:**
   ```bash
   php artisan ppdb:migrate-documents --dry-run
   ```
5. **Eksekusi Rangkaian Pengujian Otomasi (PHPUnit):**
   ```bash
   php artisan test --testdox
   # Hasil: OK (28 tests, 127 assertions)
   ```
6. **Pemeriksaan Daftar Route:**
   ```bash
   php artisan route:list
   # Total: 67 routes terverifikasi aktif
   ```

