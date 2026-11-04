# LOG PERUBAHAN SISTEM (CHANGELOG REPAIR)
## PPDB SMK ISLAM BINA KHALIFAH BANGSA METRO, LAMPUNG
**Tanggal:** 04 November 2026  
**Versi Sistem:** 1.1.0-ProductionReady  
**Framework:** Laravel 13.39.0 | PHP 8.3.16

---

## 1. RINGKASAN PERUBAHAN
Seluruh modifikasi kode dilakukan secara presisi dengan mempertahankan arsitektur MVC bawaan Laravel. Tidak ada file inti framework yang diubah secara tidak sah. Seluruh perubahan terbagi ke dalam:
- **16 Berkas Dimodifikasi**
- **6 Berkas / Modul Baru Dibuat**
- **1 Skrip Migrasi Aditif Dijalankan**

---

## 2. DAFTAR BERKAS YANG DIBUAT (NEW FILES)

### 1. `app/Http/Controllers/DokumenController.php`
- **Tujuan**: Controller pelindung akses dokumen pendaftar (UU PDP No. 27 Tahun 2022).
- **Fungsi**:
  - `viewBerkas(Request $request, $id)`: Memeriksa hak otorisasi calon siswa terhadap berkas kartu keluarga, ijazah, akta, atau foto pendaftar miliknya. Menolak akses dari pendaftar lain (HTTP 403 Forbidden). Mengizinkan Admin TU dan Kepala Sekolah untuk verifikasi.
  - `viewPrestasi(Request $request, $id)`: Memeriksa otorisasi dokumen sertifikat atau piagam prestasi calon siswa.
  - Mengirim stream biner file dengan header MIME type yang tepat (`response()->file()`).

### 2. `app/Http/Controllers/MidtransWebhookController.php`
- **Tujuan**: Menerima panggilan webhook notifikasi pembayaran server-to-server dari Midtrans Payment Gateway.
- **Fungsi**:
  - `handle(Request $request)`: Menerima payload JSON notifikasi Midtrans.
  - Memanggil `MidtransService::processNotification($payload)` untuk memvalidasi signature SHA-512 dan mengupdate status pembayaran secara idempoten.

### 3. `app/Services/MidtransService.php`
- **Tujuan**: Service layer penghubung transaksi pembayaran online.
- **Fungsi**:
  - `createSnapTransaction(Pembayaran $pembayaran)`: Membuat transaksi Snap Midtrans untuk mendapatkan URL redirect pembayaran atau snap token.
  - `verifySignature(array $payload)`: Menghitung hash SHA-512 `order_id + status_code + gross_amount + ServerKey` untuk memastikan keaslian data dari Midtrans.
  - `processNotification(array $payload)`: Memperbarui record pembayaran menjadi `lunas`, `kadaluarsa`, atau `gagal`, serta mengupdate status `pendaftar` menjadi `berkas_diverifikasi` / `menunggu_verifikasi` di dalam `DB::transaction()`.
  - Fallback simulation mode saat kredensial Midtrans belum diset di `.env`.

### 4. `app/Services/NotificationService.php`
- **Tujuan**: Service layer multi-kanal pengiriman notifikasi pendaftaran, pembayaran, dan kelulusan.
- **Fungsi**:
  - `formatPhone(string $phone)`: Menstandarkan nomor telepon Indonesia (misalnya `0812...` &rarr; `62812...`).
  - `sendWhatsApp(string $to, string $message)`: Mengirim HTTP POST request ke gateway WhatsApp (Fonnte/Wablas).
  - `sendEmail(string $to, string $subject, string $message)`: Mengirim email berbasis template Laravel Mail.
  - `notifyPendaftaranBerhasil()`, `notifyPembayaranLunas()`, `notifyHasilKelulusan()`: Pemicu event bisnis PPDB.
  - Mode simulasi: Mencatat pesan ke dalam log sistem jika konfigurasi gateway eksternal belum diaktifkan.

### 5. `app/Services/XlsxExportService.php`
- **Tujuan**: Generator file OpenXML Microsoft Excel (`.xlsx`) murni tanpa dependensi pihak ketiga.
- **Fungsi**:
  - Menggunakan ekstensi native PHP `ZipArchive` untuk menyusun struktur XML standar Office Open XML (`sheet1.xml`, `styles.xml`, `workbook.xml`).
  - Menghasilkan file spreadsheet berformat `.xlsx` yang kompatibel dengan Microsoft Excel 2007+, LibreOffice Calc, dan Google Sheets.

### 6. `app/Console/Commands/MigratePrivateDocumentsCommand.php`
- **Tujuan**: Perintah Artisan `php artisan ppdb:migrate-documents` untuk memindahkan arsip berkas pendaftar lama dari folder publik ke folder penyimpanan privat.
- **Fungsi**:
  - Membaca database `berkas_pendaftar` dan `prestasi_pendaftar`.
  - Menyalin file dari `storage/app/public` ke `storage/app/private`.
  - Memperbarui path file di database dengan aman tanpa menghapus file lama sebelum proses diverifikasi.

### 7. `database/migrations/2026_01_01_000015_add_kuota_terpakai_to_voucher_pendaftaran_table.php`
- **Tujuan**: Skema migrasi aditif untuk menambahkan kolom pelacak penggunaan voucher.
- **Fungsi**:
  - Menambahkan kolom `kuota_terpakai` (tipe integer unsigned default 0) pada tabel `voucher_pendaftaran` tanpa mengganggu struktur data yang sudah berjalan.

### 8. `tests/Feature/AuditRepairTest.php`
- **Tujuan**: Rangkaian tes regresi otomatis untuk memvalidasi seluruh perbaikan audit.
- **Fungsi**:
  - Menguji hashing kata sandi siswa.
  - Menguji pencegahan race condition kuota voucher.
  - Menguji akses rute Kepala Sekolah saat mode maintenance aktif.
  - Menguji pembatasan otorisasi dokumen pendaftar.
  - Menguji upload berkas ke storage privat.
  - Menguji verifikasi signature dan idempotensi webhook Midtrans.
  - Menguji endpoint ekspor CSV, XLSX, dan format Dapodik.
  - Menguji pengiriman notifikasi simulator.

---

## 3. DAFTAR BERKAS YANG DIMODIFIKASI (MODIFIED FILES)

### 1. `app/Http/Controllers/SiswaDashboardController.php`
- **Perubahan**:
  - Mengimpor facade `Hash`, `DB`, dan helper `Str`.
  - Memperbaiki method `simpanPengaturanAkun` sehingga password baru di-hash menggunakan Bcrypt.
  - Membungkus method `simpanFormulir` dalam blok `DB::transaction()` untuk mencegah data pendaftar inkonsisten jika terjadi kegagalan sebagian query.
  - Memperbarui method `uploadBerkas` dan `uploadPrestasi` untuk menyimpan file ke disk `local` (`storage/app/private/`) dengan penamaan hash acak `Str::random(40)`.
  - Memperbarui method `prosesBayarMidtransSimulasi` dengan `lockForUpdate()` pada entitas voucher pendaftaran agar tidak terjadi *overselling* kuota voucher.

### 2. `app/Http/Controllers/AdminTUController.php`
- **Perubahan**:
  - Membungkus method `konfirmasiBayarTunai` dengan `DB::transaction()` demi menjaga konsistensi status pendaftar dan entri pembayaran.
  - Menambahkan method `eksporXlsx()` yang memanfaatkan `XlsxExportService` untuk menghasilkan laporan unduhan berformat `.xlsx`.
  - Menambahkan penulisan UTF-8 BOM (`\xEF\xBB\xBF`) pada method ekspor CSV agar karakter teks tidak rusak saat dibuka di Microsoft Excel.

### 3. `app/Http/Controllers/KepalaSekolahController.php`
- **Perubahan**:
  - Membungkus method `sahkanKelulusan` dalam transaksi database `DB::transaction()`.
  - Menambahkan pencatatan log audit dan integrasi pemanggilan notifikasi kelulusan siswa via `NotificationService`.

### 4. `app/Http/Controllers/LandingController.php`
- **Perubahan**:
  - Memperbarui method `checkVoucher` untuk memanggil helper `isAvailable()` pada model `VoucherPendaftaran`.

### 5. `app/Http/Middleware/CheckMaintenanceMiddleware.php`
- **Perubahan**:
  - Menambahkan pola route `kepsek*` ke dalam daftar `$exemptRoutes`.
  - Menambahkan pemeriksaan autentikasi `Auth::guard('admin')->check()` agar pengguna manajemen sekolah tetap dapat mengakses sistem di masa pemeliharaan.

### 6. `app/Models/VoucherPendaftaran.php`
- **Perubahan**:
  - Menambahkan `kuota_terpakai` ke dalam properti `$fillable` dan `$casts`.
  - Menambahkan method `isAvailable(): bool` untuk validasi masa aktif dan sisa kuota voucher.
  - Menambahkan method `sisaKuota(): int` untuk menghitung sisa ketersediaan voucher.

### 7. `app/Models/Pendaftar.php`
- **Perubahan**:
  - Memperbaiki method `generateNoPendaftaran()` dengan penguncian baris agar pembuatan nomor urut pendaftaran unik tidak bertabrakan saat terjadi pendaftaran massal secara serentak.

### 8. `app/Models/BerkasPendaftar.php`
- **Perubahan**:
  - Mengubah accessor `getFileUrlAttribute()` untuk mengembalikan rute privat terproteksi `route('dokumen.berkas', $this->id_berkas)` menggantikan URL publik langsung.

### 9. `app/Models/PrestasiPendaftar.php`
- **Perubahan**:
  - Mengubah accessor `getFileUrlAttribute()` untuk mengembalikan rute privat terproteksi `route('dokumen.prestasi', $this->id_prestasi)`.

### 10. `bootstrap/app.php`
- **Perubahan**:
  - Mengonfigurasi middleware web untuk mengecualikan verifikasi CSRF token pada endpoint webhook eksternal `api/midtrans/notification`.

### 11. `config/services.php`
- **Perubahan**:
  - Menambahkan konfigurasi layanan pihak ketiga untuk `midtrans` (`server_key`, `client_key`, `is_production`, `is_sanitized`, `is_3ds`).

### 12. `phpunit.xml`
- **Perubahan**:
  - Mengatur environment variable pengujian:
    ```xml
    <env name="DB_CONNECTION" value="mysql"/>
    <env name="DB_DATABASE" value="ppdb_smk_testing"/>
    ```
  - Menjamin pengujian otomasi berjalan pada database terisolasi dan tidak menyentuh database operasional `ppdb_smk`.

### 13. `resources/views/admin/pendaftar/index.blade.php`
- **Perubahan**:
  - Menambahkan tombol unduh "Ekspor Excel (.xlsx)" yang mengarah ke `route('admin.pendaftar.ekspor.xlsx')`.

### 14. `resources/views/admin/pendaftar/detail.blade.php`
- **Perubahan**:
  - Memperbarui tautan tombol pratinjau berkas pendaftar agar menggunakan `route('dokumen.berkas', ...)` dan `route('dokumen.prestasi', ...)`.

### 15. `resources/views/siswa/berkas.blade.php`
- **Perubahan**:
  - Memperbarui tautan pratinjau berkas siswa agar mengarah ke endpoint controller berizin privat.

### 16. `routes/web.php`
- **Perubahan**:
  - Mendaftarkan rute pengunduhan berkas privat:
    - `GET /dokumen/berkas/{id}` &rarr; `[DokumenController::class, 'viewBerkas']`
    - `GET /dokumen/prestasi/{id}` &rarr; `[DokumenController::class, 'viewPrestasi']`
  - Mendaftarkan rute webhook Midtrans:
    - `POST /api/midtrans/notification` &rarr; `[MidtransWebhookController::class, 'handle']`
  - Mendaftarkan rute ekspor XLSX:
    - `GET /admin/ekspor/xlsx` &rarr; `[AdminTUController::class, 'eksporXlsx']`
