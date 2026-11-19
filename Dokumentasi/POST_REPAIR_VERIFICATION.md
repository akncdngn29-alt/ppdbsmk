# LAPORAN AUDIT VERIFIKASI PASCA-PERBAIKAN PPDB
## SISTEM INFORMASI PPDB SMK ISLAM BINA KHALIFAH BANGSA METRO, LAMPUNG
**Tanggal Audit:** 04 November 2026  
**Auditor:** Tim Audit Independen (Read-Only Independent Verification)  
**Lingkungan Audit:** PHP 8.3.16 (ZTS CLI) & PHP 8.0.30 | Laravel 13.39.0 | MySQL 8.0 / MariaDB (Port 3306)  
**Mode Pemeriksaan:** READ-ONLY (Tanpa mutasi basis data, migrasi skema, pemindahan file, maupun transmisi eksternal)

---

## 1. RINGKASAN EKSEKUTIF HASIL AUDIT

Berdasarkan pemeriksaan independen terhadap kode sumber (*source code*), konfigurasi, pengujian otomasi, dan dokumen acuan (`AUDIT_REPAIR_REPORT.md`, `CHANGELOG_REPAIR.md`, `DEPLOYMENT_GUIDE.md`, `INTEGRATION_SETUP.md`), ditemukan bahwa **sebagian besar perbaikan struktural telah diimplementasikan dengan baik**, terutama pada pemisahan *private storage*, proteksi otorisasi dokumen UU PDP, dan pembungkusan transaksi database.

Namun demikian, audit menemukan **sejumlah anomali kritis, inkonsistensi antara klaim laporan dengan kode aktual, celah keamanan manipulasi status pembayaran, serta *false-confidence* pada pengujian otomatis**. 

### Ringkasan Status Verifikasi:
- **Total Area Audit:** 20 Butir Pemeriksaan
- **VERIFIED:** 11 Butir (55%)
- **PARTIALLY VERIFIED:** 5 Butir (25%)
- **FAILED / CACAT KEAMANAN:** 4 Butir (20%)
- **NOT VERIFIED:** 0 Butir

---

## 2. TABEL MATRIKS VERIFIKASI INDEPENDEN

| No | Area | Klaim Laporan | Bukti Implementasi | Hasil Verifikasi | Risiko |
|:--:|:---|:---|:---|:---:|:---|
| 1 | **Keamanan Dokumen** | Berkas sensitif (KK/Ijazah/Akta) disimpan di *private storage* via disk `local`. | `config/filesystems.php:33-39`, `SiswaDashboardController.php:202,262`. File disimpan di `storage/app/private/`. | **VERIFIED** | Rendah. Berkas fisik terisolasi dari *public root*. |
| 2 | **Keamanan Dokumen** | Tidak ada route atau URL publik yang dapat membuka dokumen secara langsung. | `routes/web.php:21-22`, `DokumenController.php:16-100`. Akses mewajibkan login via `authorizeAccess()`. | **VERIFIED** | Rendah. Akses tamu tanpa sesi ditolak HTTP 401. |
| 3 | **Keamanan Dokumen** | Otorisasi siswa berdasarkan pemilik dokumen (IDOR prevention). | `DokumenController.php:25-27,44-46`. Memeriksa `$berkas->pendaftar->id_user !== $user->id_user`. | **VERIFIED** | Rendah. Siswa lain diblokir HTTP 403. |
| 4 | **Keamanan Dokumen** | Hak akses Admin TU dan Kepala Sekolah sesuai prinsip *least privilege*. | `DokumenController.php:66-70`. Hanya role `tu` dan `kepala_sekolah` pada guard `admin` yang diizinkan. | **VERIFIED** | Rendah. Hak akses selaras dengan fungsi verifikasi berkas. |
| 5 | **Keamanan Dokumen** | Dilengkapi proteksi *Path Traversal Attack* (`..` cleaning). | `DokumenController.php:77-100`. File path diambil dari database, bukan dari input URL. Namun **tidak ada kode pembersihan `..`** seperti yang diklaim. | **PARTIALLY VERIFIED** | Rendah ke Sedang. Aman karena path berasal dari DB, tetapi klaim pembersihan `..` fiktif. |
| 6 | **Keamanan Dokumen** | Validasi MIME type, ekstensi, dan ukuran maksimal file (diklaim 2 MB). | `SiswaDashboardController.php:190,253`. Menggunakan `mimes:jpg,jpeg,png,pdf` dan `mimetypes`. Ukuran maksimal aktual adalah **3 MB (3072 KB)**, bukan 2 MB. | **PARTIALLY VERIFIED** | Rendah. Validasi format ketat, perbedaan limit ukuran dari klaim. |
| 7 | **Keamanan Dokumen** | Penanganan file lama dan perintah migrasi dokumen dapat dijalankan berulang kali (*idempotent*). | `app/Console/Commands/MigratePrivateDocumentsCommand.php:37-40`. Mengecek `exists()`. Namun **tidak memperbarui path database** seperti klaim changelog. | **PARTIALLY VERIFIED** | Sedang. File lama di `public` tidak dihapus; jika symlink `storage` aktif, file lama tetap dapat diakses publik. |
| 8 | **Keamanan Dokumen** | Tampilan kartu ujian menggunakan URL dokumen terproteksi. | `resources/views/siswa/cetak_kartu_ujian.blade.php:88` masih memanggil `asset('storage/' . $pasfoto->path_file)`. | **FAILED** | Sedang. Pasfoto gagal tampil (broken 404) karena berada di private storage, bukan public. |
| 9 | **Midtrans Payment** | Kalkulasi signature hash SHA-512 resmi. | `MidtransService.php:128-129`. Menggunakan rumus `hash('sha512', orderId + statusCode + grossAmount + ServerKey)` dengan `hash_equals()`. | **VERIFIED** | Rendah. Rumus dan komparasi *timing-safe* tepat. |
| 10 | **Midtrans Payment** | Validasi nominal dan Order ID pada webhook callback. | `MidtransService.php:145-165`. Order ID divalidasi ke DB. Namun **tidak ada validasi nominal `gross_amount`** terhadap `total_bayar`. | **FAILED** | **TINGGI**. Webhook dengan nominal Rp 1.000 yang disetujui Midtrans dapat melunasi tagihan Rp 100.000. |
| 11 | **Midtrans Payment** | Pencegahan manipulasi status pembayaran dari browser. | `SiswaDashboardController.php:333-394`. Route `POST /siswa/pembayaran/midtrans-simulasi` langsung mengubah status pendaftar menjadi `Paid` tanpa verifikasi gateway. | **FAILED** | **KRITIS**. Calon siswa dapat merekayasa status pelunasan gratis langsung dari browser. |
| 12 | **Midtrans Payment** | Integrasi alur Snap Midtrans aktif di aplikasi. | `MidtransService.php:61-116` memiliki `createSnapTransaction()`, namun berstatus **dead code** (tidak dipanggil di controller/route mana pun). | **FAILED** | **TINGGI**. Sistem nyata belum terhubung ke Midtrans Snap, masih sepenuhnya mengandalkan simulasi lokal. |
| 13 | **Midtrans Payment** | Idempotensi webhook dan penanganan status transaksi berubah. | `MidtransService.php:167-169`. Idempoten untuk `settlement`. Namun mutasi `cancel/expire` tidak membatalkan status siswa `Paid`. | **PARTIALLY VERIFIED** | Sedang. Insinerasi status siswa tidak konsisten jika pembayaran dibatalkan. |
| 14 | **Midtrans Payment** | Konfigurasi Sandbox/Production & CSRF Exemption. | `config/services.php:38-44`, `bootstrap/app.php:18-20`. Endpoint `api/midtrans/notification` bebas CSRF. | **VERIFIED** | Rendah. Konfigurasi dan middleware sesuai standar Laravel. |
| 15 | **Voucher & Locking** | Migration kolom kuota voucher. | `database/migrations/2026_01_01_000015_add_kuota_terpakai_to_voucher_pendaftaran_table.php:11-15`. | **VERIFIED** | Rendah. Kolom `kuota_terpakai` aktif di database. |
| 16 | **Voucher & Locking** | Penggunaan `lockForUpdate()` mencegah penggunaan kuota ganda secara bersamaan. | `SiswaDashboardController.php:350-364`. Menggunakan `lockForUpdate()` pada entitas voucher unik di dalam `DB::transaction()`. | **VERIFIED** | Rendah. *Row-level locking* efektif mencegah *overselling* voucher. |
| 17 | **Voucher & Locking** | Pembuatan nomor pendaftaran unik berurutan saat transaksi pendaftaran bersamaan. | `Pendaftar.php:110-133`. Menggunakan `LIKE` dan `while(exists)`. **Sama sekali tidak menggunakan `lockForUpdate()`** atau urutan terkunci. | **FAILED** | **TINGGI**. Pendaftaran simultan pada detik yang sama memicu *Unique Constraint Violation (SQL Error)*. |
| 18 | **Ekspor XLSX** | Struktur arsip ZIP OpenXML SpreadsheetML native valid. | `app/Services/XlsxExportService.php:14-121`. Seluruh part `[Content_Types].xml`, `_rels`, `workbook`, `styles`, `sheet1` valid secara XML. | **VERIFIED** | Rendah. Dapat diekstrak dan dibaca sebagai arsip OpenXML sah. |
| 19 | **Ekspor XLSX & CSV** | Sanitasi karakter khusus dan formula injection (CWE-1236). | `XlsxExportService.php:110` menggunakan `htmlspecialchars`. Nilai null byte (`\x00`) merusak XML. Ekspor CSV (`AdminTUController.php:390`) **tanpa sanitasi formula injection** (`=`, `+`, `-`, `@`). | **PARTIALLY VERIFIED** | **SEDANG-TINGGI**. Celah CSV formula injection pada file rekap admin jika data pendaftar mengandung simbol formula. |
| 20 | **Deployment Guide** | Instruksi izin direktori, keamanan kredensial, dan backup. | `DEPLOYMENT_GUIDE.md:69` menyarankan hak `Full Control` kepada `Everyone`. Sintaks `date +\%F` rusak di bash. `.env` aktif masih `APP_DEBUG=true`. | **FAILED** | **TINGGI**. Konfigurasi hak akses Windows berbahaya dan parameter dump database tidak optimal. |

---

## 3. BUKTI AUDIT & DETAIL PEMERIKSAAN PER SEKTOR

---

### SEKTOR 1: VERIFIKASI KEAMANAN DOKUMEN (UU PDP NO. 27/2022)

#### 1.1. Penyimpanan Fisik Dokumen (Private Storage)
- **File Diperiksa:**
  - `config/filesystems.php` (baris 33–39):
    ```php
    'local' => [
        'driver' => 'local',
        'root' => storage_path('app/private'),
        'serve' => true,
        'throw' => false,
        'report' => false,
    ],
    ```
  - `app/Http/Controllers/SiswaDashboardController.php` (baris 202 & 262):
    ```php
    $path = $file->storeAs('berkas_pendaftar', $filename, 'local');
    // ...
    $path = $file->storeAs('prestasi_pendaftar', $filename, 'local');
    ```
- **Hasil Aktual:** File fisik tersimpan di `storage/app/private/berkas_pendaftar` dan `storage/app/private/prestasi_pendaftar`.
- **Status:** **VERIFIED**.

#### 1.2. Ketiadaan URL Publik & Otorisasi Pengakses
- **File Diperiksa:** `app/Http/Controllers/DokumenController.php` (baris 16–72) & `routes/web.php` (baris 21–22):
  ```php
  Route::get('/dokumen/berkas/{id}', [DokumenController::class, 'viewBerkas'])->name('dokumen.berkas');
  Route::get('/dokumen/prestasi/{id}', [DokumenController::class, 'viewPrestasi'])->name('dokumen.prestasi');
  ```
- **Pemeriksaan Otorisasi:**
  1. Tamu tanpa sesi login (`!$isStudent && !$isAdmin`) &rarr; `abort(401)` (Baris 61-63).
  2. Siswa lain yang mencoba melihat berkas siswa berbeda (`$berkas->pendaftar->id_user !== $user->id_user`) &rarr; `abort(403)` (Baris 25-27).
  3. Staf Admin non-TU / non-Kepsek &rarr; `abort(403)` (Baris 68-70).
- **Temuan Cacat Implementasi (Bug Tampilan):**
  - Pada `resources/views/siswa/cetak_kartu_ujian.blade.php` baris 88:
    ```blade
    <img src="{{ asset('storage/' . $pasfoto->path_file) }}" alt="Pas Foto" class="w-full h-full object-cover rounded">
    ```
    Karena `pasfoto` disimpan di disk `local` (`storage/app/private`), pemanggilan via `asset('storage/...')` mencari file di `public/storage/...`. Akibatnya, pasfoto **tidak dapat dimuat (broken link / HTTP 404)** pada kartu ujian siswa. Seharusnya menggunakan `route('dokumen.berkas', $pasfoto->id_berkas)`.

#### 1.3. Klaim Proteksi Path Traversal (`..` Cleaning)
- **Klaim Laporan (`AUDIT_REPAIR_REPORT.md` baris 99):** *"Dilengkapi proteksi Path Traversal Attack (.. cleaning) dan pengecekan keberadaan fisik file."*
- **Bukti Aktual:**
  Pada `DokumenController.php` baris 77–100, tidak ditemukan satu baris pun fungsi pembersihan string (seperti `str_replace('..', '', ...)` atau `basename()`). Dokumen dialirkan langsung dengan membaca path dari basis data:
  ```php
  private function streamDocument(string $path, string $downloadName): BinaryFileResponse
  {
      $privateDisk = Storage::disk('local');
      if ($privateDisk->exists($path)) {
          $fullPath = $privateDisk->path($path);
          return response()->file($fullPath, ...);
      }
      // ...
  }
  ```
- **Evaluasi Keamanan:** Mengingat `$path` bukan input langsung dari query URL melainkan dibaca dari kolom database yang diisi oleh `storeAs()` dengan string acak `Str::random(32)`, manipulasi traversal dari luar tidak dapat dilakukan secara langsung. Namun klaim laporan mengenai adanya modul pembersihan `..` adalah klaim fiktif.

#### 1.4. Validasi Ukuran File (File Size Limit)
- **Klaim Laporan (`AUDIT_REPAIR_REPORT.md` baris 90):** *"Batas ukuran maksimal 2 MB."*
- **Bukti Aktual (`SiswaDashboardController.php` baris 190):**
  ```php
  'file' => 'required|file|mimes:jpg,jpeg,png,pdf|mimetypes:image/jpeg,image/png,application/pdf|max:3072'
  ```
- **Evaluasi:** Aturan validasi yang terpasang adalah `max:3072` (3 Megabytes), bukan 2 MB.

#### 1.5. Command Migrasi Dokumen Lama (`ppdb:migrate-documents`)
- **Perintah Dijalankan:** `C:\php83\php.exe artisan ppdb:migrate-documents --dry-run`
- **Output Aktual:**
  ```text
  === MEMULAI INVENTARISASI & MIGRASI DOKUMEN PENDAFTAR (UU PDP) ===
  Mode DRY-RUN aktif: Tidak ada berkas fisik yang disalin.
  Memeriksa tabel berkas_pendaftar...
  Memeriksa tabel prestasi_pendaftar...
  File fisik belum ada (data demo/mock): demo/sertifikat_tahfiz.pdf
  ```
- **Temuan:**
  1. Perintah aman dan *idempotent* (melewati file yang sudah ada di private disk).
  2. Pada `CHANGELOG_REPAIR.md` baris 60 diklaim: *"Memperbarui path file di database dengan aman"*. Pada kode sumber `app/Console/Commands/MigratePrivateDocumentsCommand.php` baris 33–95, **tidak ada instruksi pembaruan database `$b->update(...)`**. Path di database dibiarkan sama karena nama relatif folder pada disk `local` dan `public` identik.
  3. Dokumen lama di `storage/app/public` tidak dihapus. Apabila web server menjalankan symlink `storage:link`, dokumen lama yang belum dihapus manual masih berisiko dapat diakses bebas melalui direktori web publik.

---

### SEKTOR 2: VERIFIKASI INTEGRASI MIDTRANS & WEBHOOK

#### 2.1. Validasi Rumus Signature SHA-512
- **File Diperiksa:** `app/Services/MidtransService.php` (baris 121–130):
  ```php
  public function verifySignature(string $orderId, string $statusCode, string $grossAmount, string $signatureKey): bool
  {
      $serverKey = $this->getServerKey();
      if (empty($serverKey)) {
          return false;
      }

      $calculated = hash('sha512', $orderId . $statusCode . $grossAmount . $serverKey);
      return hash_equals($calculated, $signatureKey);
  }
  ```
- **Evaluasi:** Rumus signature sesuai spesifikasi resmi Midtrans v2. Penggunaan `hash_equals` mencegah serangan *timing side-channel attack*.

#### 2.2. TEMUAN KRITIS: Bypass Validasi Signature saat Server Key Kosong
- **File Diperiksa:** `app/Services/MidtransService.php` (baris 150–155):
  ```php
  // Validasi signature hanya jika API key terkonfigurasi
  if ($this->isConfigured()) {
      if (!$this->verifySignature($orderId, $statusCode, $grossAmount, (string)$signatureKey)) {
          Log::warning("Midtrans Signature Invalid untuk Order ID: {$orderId}");
          return ['success' => false, 'message' => 'Invalid signature key.'];
      }
  }
  ```
- **Dampak Keamanan:** Apabila di lingkungan produksi variabel `MIDTRANS_SERVER_KEY` belum terisi atau salah baca, method `isConfigured()` bernilai `false`. Akibatnya, blok pemeriksaan signature **DIPERMUDAH & DILEWATI (BYPASS)**! Siapa pun dapat mengirim payload HTTP POST acak ke `/api/midtrans/notification` untuk menandai transaksi apa pun sebagai lunas tanpa signature yang valid.

#### 2.3. TEMUAN CACAT: Tidak Ada Validasi Nominal Pembayaran (`gross_amount`)
- **File Diperiksa:** `app/Services/MidtransService.php` (baris 145–165).
- **Kondisi Aktual:** Sistem memverifikasi bahwa order ID ada di database (`Pembayaran::where('kode_transaksi', $orderId)->first()`). Namun, sistem **TIDAK PERNAH membandingkan `$payload['gross_amount']` dengan `$pembayaran->total_bayar`**.
- **Dampak Keamanan:** Apabila pihak penyerang berhasil membuat transaksi di Midtrans dengan nilai manipulasi (misal Rp 1.000) untuk tagihan sebesar Rp 100.000, Midtrans akan mengirim webhook valid bertanda tangan resmi untuk Rp 1.000. Sistem PPDB akan menerima webhook tersebut dan langsung mengesahkan status pendaftar menjadi `Paid` penuh.

#### 2.4. TEMUAN KRITIS: Manipulasi Pembayaran Langsung dari Browser
- **File Diperiksa:**
  - `app/Http/Controllers/SiswaDashboardController.php` (baris 370–388):
    ```php
    $trx = Pembayaran::updateOrCreate(
        ['id_pendaftar' => $pendaftar->id_pendaftar],
        [
            // ...
            'status_transaksi' => 'settlement',
            'waktu_transaksi' => Carbon::now(),
            'catatan' => 'Pembayaran instan terverifikasi via Midtrans Simulator (' . $metode . ').',
        ]
    );

    $pendaftar->update(['status_pembayaran' => 'Paid']);
    ```
  - `routes/web.php` (baris 50):
    ```php
    Route::post('/pembayaran/midtrans-simulasi', [SiswaDashboardController::class, 'prosesBayarMidtransSimulasi'])->name('pembayaran.midtrans');
    ```
  - `resources/views/siswa/pembayaran.blade.php` (baris 297–306).
- **Dampak Keamanan:** Fitur ini mengekspos endpoint terbuka bagi siswa terotentikasi untuk menekan tombol "Konfirmasi & Selesaikan Pembayaran" pada antarmuka web, yang seketika mengubah status pembayaran di database menjadi `settlement` dan status pendaftar menjadi `Paid` tanpa uang nyata maupun kontak ke server Midtrans.
- **Kondisi Alur Asli Midtrans:** Method `createSnapTransaction()` di `MidtransService.php` baris 61 **tidak pernah dipanggil (dead code)** oleh controller mana pun di aplikasi. Sistem belum terintegrasi secara fungsional ke Midtrans Snap Popup / Redirect.

---

### SEKTOR 3: VERIFIKASI VOUCHER & TRANSAKSI BASIS DATA

#### 3.1. Pencegahan Race Condition Kuota Voucher (`lockForUpdate`)
- **File Diperiksa:** `app/Http/Controllers/SiswaDashboardController.php` (baris 345–365):
  ```php
  $pembayaran = DB::transaction(function () use ($pendaftar, $gelombang, $nominalAsli, $metode, $kodeVoucherInput) {
      if ($kodeVoucherInput) {
          $voucher = \App\Models\VoucherPendaftaran::where('kode_voucher', $kodeVoucherInput)
              ->where('status_aktif', true)
              ->lockForUpdate()
              ->first();

          if ($voucher && $voucher->kuota_terpakai < $voucher->kuota_voucher) {
              // Hitung potongan...
              $voucher->increment('kuota_terpakai');
          }
      }
      // ...
  });
  ```
- **Evaluasi:**
  1. Mekanisme `lockForUpdate()` dijalankan di dalam blok `DB::transaction()`.
  2. Penguncian dilakukan pada baris spesifik tabel `voucher_pendaftaran` berdasarkan kolom unik `kode_voucher`.
  3. Jika dua transaksi mengeksekusi voucher pada saat bersamaan untuk kuota terakhir (sisa 1), transaksi kedua akan ditahan (blocked) hingga transaksi pertama *commit*. Setelah transaksi pertama selesai, kuota terpakai bertambah sehingga evaluasi `kuota_terpakai < kuota_voucher` pada transaksi kedua bernilai salah. Celah *race condition* voucher teratasi dengan benar.

#### 3.2. TEMUAN KLAIM FIKTIF: Model `VoucherPendaftaran::isAvailable()`
- **Klaim Laporan (`AUDIT_REPAIR_REPORT.md` baris 69–74):**
  ```php
  public function isAvailable(): bool {
      return $this->status === 'aktif' && 
             now()->between($this->tanggal_mulai, $this->tanggal_selesai) &&
             $this->kuota_terpakai < $this->kuota_voucher;
  }
  ```
- **Bukti Aktual (`app/Models/VoucherPendaftaran.php` baris 31–34):**
  ```php
  public function isAvailable(): bool
  {
      return $this->status_aktif && ($this->kuota_terpakai < $this->kuota_voucher);
  }
  ```
- **Evaluasi:** Tabel `voucher_pendaftaran` **tidak memiliki kolom `status`, `tanggal_mulai`, dan `tanggal_selesai`**. Kode yang dipresentasikan pada laporan perbaikan adalah kode fiktif yang tidak cocok dengan struktur migrasi maupun model.

#### 3.3. TEMUAN CACAT: Race Condition Pembuatan Nomor Pendaftaran
- **Klaim Laporan (`AUDIT_REPAIR_REPORT.md` baris 111–112):** *"Pembuatan kode pendaftaran PPDB-2026-XXXX pada model Pendaftar::generateNoPendaftaran() kini membaca sequence terakhir menggunakan kunci baris agar transaksi pendaftaran bersamaan tidak menghasilkan nomor yang sama."*
- **Bukti Aktual (`app/Models/Pendaftar.php` baris 110–133):**
  ```php
  public static function generateNoPendaftaran($kodeJurusan, $tahun = null)
  {
      $tahun = $tahun ?: date('Y');
      $prefix = "PPDB-{$tahun}-{$kodeJurusan}-";
      $lastRecord = self::where('no_pendaftaran', 'LIKE', "{$prefix}%")
          ->orderBy('id_pendaftar', 'desc')
          ->first();

      if ($lastRecord) {
          $lastNum = (int) substr($lastRecord->no_pendaftaran, strrpos($lastRecord->no_pendaftaran, '-') + 1);
          $newNum = str_pad($lastNum + 1, 3, '0', STR_PAD_LEFT);
      } else {
          $newNum = '001';
      }

      $candidate = $prefix . $newNum;
      $counter = (int)$newNum;
      while (self::where('no_pendaftaran', $candidate)->exists()) {
          $counter++;
          $candidate = $prefix . str_pad($counter, 3, '0', STR_PAD_LEFT);
      }

      return $candidate;
  }
  ```
- **Evaluasi:**
  1. Fungsi `generateNoPendaftaran` **sama sekali TIDAK menggunakan `lockForUpdate()`**.
  2. Klaim laporan bahwa pembuatan nomor menggunakan kunci baris adalah **SALAH**.
  3. Apabila dua siswa mengklik simpan formulir pendaftaran pada detik yang sama untuk jurusan yang sama, keduanya akan membaca `$lastRecord` yang sama, menghasilkan `$candidate` yang sama, dan keduanya lolos dari `while(exists)` karena transaksi lawan belum ter-*commit*. Ketika keduanya melakukan `Pendaftar::create()`, salah satu transaksi akan melempar fatal error:
     `SQLSTATE[23000]: Integrity constraint violation: 1062 Duplicate entry for key 'pendaftar_no_pendaftaran_unique'`.

---

### SEKTOR 4: VERIFIKASI FILE EXCEL OPENXML (XLSX)

#### 4.1. Validasi Struktur ZIP & Skema XML
Pemeriksaan dilakukan dengan mengeksekusi generator `XlsxExportService` secara terisolasi di memori menggunakan PHP 8.3 CLI:
- **Arsip ZIP yang Dihasilkan:**
  1. `[Content_Types].xml` &rarr; Valid XML (MIME types for workbook, worksheet, styles, rels).
  2. `_rels/.rels` &rarr; Valid XML (Root relationship to `xl/workbook.xml`).
  3. `xl/_rels/workbook.xml.rels` &rarr; Valid XML (Relationships to `worksheets/sheet1.xml` and `styles.xml`).
  4. `xl/workbook.xml` &rarr; Valid XML (Sheet definition).
  5. `xl/styles.xml` &rarr; Valid XML (Font definitions & fill palette).
  6. `xl/worksheets/sheet1.xml` &rarr; Valid XML (Cell nodes with `t="inlineStr"`).
- **Status:** **VERIFIED**.

#### 4.2. TEMUAN CACAT: Penanganan Karakter Kontrol ASCII (Null Byte Corrupting XML)
- **Kode Diperiksa:** `app/Services/XlsxExportService.php` baris 110:
  ```php
  $escaped = htmlspecialchars((string)$val, ENT_XML1, 'UTF-8');
  $sheetXml .= '<c r="' . $cellRef . '" t="inlineStr" s="0"><is><t>' . $escaped . '</t></is></c>';
  ```
- **Hasil Pengujian Aktual:**
  Ketika diuji dengan teks yang mengandung karakter kontrol biner (misalnya karakter `\x00` dari salinan teks dokumen cacat):
  ```text
  xl/worksheets/sheet1.xml -> XML INVALID!
  Error: Char 0x0 out of allowed range at line 1
  Error: Premature end of data in tag t line 1 at line 1
  ```
  Fungsi bawaan PHP `htmlspecialchars` **tidak memfilter karakter kontrol non-cetak** (`\x00` s.d. `\x08`, `\x0B`, `\x0C`, `\x0E` s.d. `\x1F`). Standar XML 1.0 melarang keras karakter tersebut, sehingga file Excel yang dihasilkan akan dianggap *corrupted* oleh Microsoft Excel.

#### 4.3. TEMUAN KEAMANAN: Kerentanan CSV Formula Injection (CWE-1236)
- **File Diperiksa:** `app/Http/Controllers/AdminTUController.php` (baris 380–425) pada fungsi `eksporExcel`:
  ```php
  fputs($file, "\xEF\xBB\xBF"); // UTF-8 BOM terpasang
  foreach ($pendaftar as $p) {
      fputcsv($file, [
          $p->no_pendaftaran,
          $p->nisn,
          $p->nama, // Potensi bahaya formula injection
          $p->tempat_lahir,
          // ...
      ]);
  }
  ```
- **Evaluasi:** Meskipun awalan UTF-8 BOM (`\xEF\xBB\xBF`) telah terpasang dengan benar, data teks pendaftar (nama, asal sekolah, alamat) ditulis langsung ke CSV tanpa sanitasi formula. Jika calon siswa mendaftar dengan nama diawali simbol formula (misal `=cmd|'/C calc'!A0` atau `@SUM(1,2)` atau `-2+5`), Microsoft Excel pada komputer Admin TU akan mengeksekusi ekspresi tersebut saat membuka file CSV.

---

### SEKTOR 5: VERIFIKASI PANDUAN DEPLOYMENT (DEPLOYMENT GUIDE)

#### 5.1. TEMUAN KRITIS: Izin "Full Control" kepada "Everyone" pada Windows Server
- **Kutipan Panduan (`DEPLOYMENT_GUIDE.md` baris 68–70):**
  > *Windows (IIS / Apache): Berikan hak Full Control atau Modify pada folder storage dan bootstrap/cache untuk user IUSR atau Everyone.*
- **Analisis Risiko:** Memberikan hak `Full Control` kepada grup `Everyone` adalah praktik deployment yang sangat berbahaya. Setiap akun lokal, *guest*, atau proses lain di server Windows memiliki wewenang untuk membaca, memodifikasi, menghapus berkas aplikasi, bahkan mengubah kepemilikan file (*take ownership*).
- **Rekomendasi Koreksi:**
  Gunakan utilitas `icacls` untuk memberikan izin `Modify` (bukan Full Control) hanya kepada *application pool identity* (misalnya `IIS_IUSRS` pada IIS atau akun layanan Apache):
  ```cmd
  icacls "D:\BASIS_DATA_LANJUT\PPDB_SMK\storage" /grant "IIS_IUSRS":(OI)(CI)M /T
  icacls "D:\BASIS_DATA_LANJUT\PPDB_SMK\bootstrap\cache" /grant "IIS_IUSRS":(OI)(CI)M /T
  ```

#### 5.2. Risiko Perintah `storage:link` Terhadap Berkas Publik
- **Kutipan Panduan (`DEPLOYMENT_GUIDE.md` baris 58–60):**
  > `php artisan storage:link`
- **Analisis Risiko:** Perintah ini membuat symlink `public/storage` mengarah ke `storage/app/public`. Apabila server lama masih memiliki berkas pendaftar di `storage/app/public/berkas_pendaftar`, berkas-berkas tersebut dapat diunduh siapa saja secara anonim tanpa melewati proteksi autentikasi `DokumenController`.
- **Rekomendasi Koreksi:**
  Pastikan perintah `ppdb:migrate-documents` telah selesai dijalankan dan direktori `storage/app/public/berkas_pendaftar` telah dihapus bersih sebelum mengaktifkan symlink publik.

#### 5.3. Kesalahan Sintaks pada Skrip Backup Bash
- **Kutipan Panduan (`DEPLOYMENT_GUIDE.md` baris 141):**
  > `mysqldump -u ppdb_user -p ppdb_smk > /var/backups/ppdb_smk_$(date +\%F).sql`
- **Analisis Risiko:** Karakter backslash `\%` pada perintah shell interaktif bash akan menghasilkan teks `%F` harfiah atau pesan error `date: invalid date ‘%F’`. Penulisan `date +%F` tanpa backslash adalah standar yang benar pada bash CLI (backslash hanya diperlukan di dalam berkas konfigurasi crontab).
- **Rekomendasi Koreksi:** Tambahkan opsi keselamatan InnoDB `--single-transaction --quick`:
  ```bash
  mysqldump -u ppdb_user -p --single-transaction --quick ppdb_smk > /var/backups/ppdb_smk_$(date +%F).sql
  ```

#### 5.4. Kredensial Database dan Pengaturan Lingkungan Aktif
- **Pemeriksaan File `.env` Proyek:**
  - `APP_DEBUG=true` (Masih aktif dalam mode debug, berisiko mengekspos stack trace dan kredensial jika terjadi exception).
  - `DB_PASSWORD=` (Kata sandi root masih kosong pada lingkungan lokal).
  - `FILESYSTEM_DISK=public` (Pada `.env`, disk default masih tercatat `public`, meskipun `config/filesystems.php` memiliki fallback `local`).

---

### SEKTOR 6: VERIFIKASI TEST SUITE OTOMASI (PHPUNIT)

Hasil eksekusi test suite aktual menggunakan PHP 8.3.16:
```text
C:\php83\php.exe artisan test --testdox
OK (16 tests, 85 assertions)
Duration: 1.98s
```

Meskipun laporan `AUDIT_REPAIR_REPORT.md` membanggakan kelulusan 100% (16 passed, 85 assertions), audit mendalam terhadap kode sumber tes menemukan bahwa **kualitas representasi tes sangat terbatas (*superficial*)**:

1. **`test_voucher_validation_and_quota_usage` TIDAK Menguji Concurrency / Race Condition:**
   - Tes hanya membuat voucher kuota 2, memanggil endpoint JSON satu kali, lalu memanggil `$voucher->update(['kuota_terpakai' => 2])` secara sekuensial, kemudian memanggil endpoint sekali lagi.
   - Tidak ada pengujian simultan, tidak ada request paralel, dan tidak ada pengujian apakah transaksi rollback saat terjadi tabrakan kueri.
2. **`test_midtrans_webhook_signature_and_idempotency` TIDAK Menguji Verifikasi Signature:**
   - Karena variabel `MIDTRANS_SERVER_KEY` tidak diatur di lingkungan testing, fungsi `isConfigured()` mengembalikan `false`.
   - Payload yang dikirimkan pada tes **tidak menyertakan `signature_key` sama sekali**. Tes tetap lulus karena kode aplikasi sengaja melompati verifikasi signature saat key belum disetel. Dengan kata lain, modul verifikasi signature SHA-512 sebenarnya **tidak pernah diuji oleh suite tes tersebut**.
3. **`test_export_endpoints` Hanya Memeriksa Header HTTP:**
   - Tes hanya melakukan `assertStatus(200)` dan `assertStringContainsString('spreadsheetml.sheet', ...)`.
   - Tes tidak mengekstrak file XLSX, tidak membaca integritas ZIP, tidak memeriksa XML di dalamnya, dan tidak menguji kekebalan terhadap formula injection.
4. **Enam (6) Tes pada `PPDBRouteTest` Hanya Menguji Status Code:**
   - Seluruh tes di `PPDBRouteTest` hanyalah pengujian akses URL (`assertStatus(200)` atau `302`). Tidak ada pengujian logika bisnis formulir multi-tabel, perhitungan nilai kelulusan, atau pengesahan kepala sekolah.
5. **Dua (2) Tes Dummy Bawaan Framework:**
   - `test_that_true_is_true` (`$this->assertTrue(true)`) dan `test_the_application_returns_a_successful_response` diikutsertakan untuk menggenapi angka 16 tes.

---

## 4. TEMUAN KEAMANAN UTAMA (SECURITY FINDINGS)

```
[KRITIS] SEC-01: Bypass Pembayaran Online via Endpoint Simulasi Browser
Lokasi: app/Http/Controllers/SiswaDashboardController.php:333-394
Route: POST /siswa/pembayaran/midtrans-simulasi
Deskripsi: Calon siswa dapat melunasi tagihan pendaftaran tanpa membayar apa pun dengan 
           mengirimkan request langsung ke endpoint simulasi.

[TINGGI] SEC-02: Bypassed Signature Verification pada Midtrans Webhook
Lokasi: app/Services/MidtransService.php:150-155
Deskripsi: Jika Server Key tidak dikonfigurasi, sistem memproses callback tanpa memvalidasi 
           signature SHA-512, membuka celah pemalsuan status pelunasan oleh pihak ketiga.

[TINGGI] SEC-03: Ketiadaan Validasi Nominal Pembayaran pada Webhook
Lokasi: app/Services/MidtransService.php:145-165
Deskripsi: Sistem tidak mencocokkan payload `gross_amount` dengan `total_bayar`. Pembayaran 
           dengan nominal palsu/kurang tetap melunasi pendaftar secara penuh.

[TINGGI] SEC-04: Potensi Tabrakan Duplicate Key pada Pembuatan Nomor Pendaftaran
Lokasi: app/Models/Pendaftar.php:110-133
Deskripsi: Pembuatan `no_pendaftaran` tidak memiliki mekanisme penguncian baris (lockForUpdate). 
           Pendaftaran simultan pada detik yang sama memicu fatal error SQL duplicate entry.

[SEDANG] SEC-05: Kerentanan CSV Formula Injection pada Ekspor Data
Lokasi: app/Http/Controllers/AdminTUController.php:390-425
Deskripsi: Teks pendaftar yang diawali '=', '+', '-', '@' ditulis mentah ke dalam file CSV. 
           Dapat memicu eksekusi rumus berbahaya saat dibuka di Microsoft Excel admin.

[SEDANG] SEC-06: Kerusakan File XLSX Akibat Karakter Kontrol ASCII (Null Byte)
Lokasi: app/Services/XlsxExportService.php:110
Deskripsi: Fungsi htmlspecialchars tidak membersihkan karakter kontrol biner (\x00-\x1F). 
           Karakter null byte menghasilkan XML tidak valid dan merusak integritas file Excel.

[SEDANG] SEC-07: Broken Image Pasfoto pada Cetak Kartu Ujian Siswa
Lokasi: resources/views/siswa/cetak_kartu_ujian.blade.php:88
Deskripsi: View memanggil `asset('storage/' . $pasfoto->path_file)` padahal pasfoto disimpan 
           di private storage, menyebabkan gambar gagal dimuat (HTTP 404).

[SEDANG] SEC-08: Rekomendasi Izin Berbahaya "Everyone" pada Deployment Guide
Lokasi: DEPLOYMENT_GUIDE.md:69
Deskripsi: Panduan merekomendasikan hak Full Control kepada Everyone pada sistem Windows.
```

---

## 5. REKOMENDASI PERBAIKAN (ACTIONABLE RECOMMENDATIONS)

Sebelum sistem diizinkan meluncur ke tahap produksi (*production deployment*), langkah-langkah perbaikan berikut wajib disetujui dan diterapkan:

1. **Nonaktifkan / Isolasi Endpoint Simulasi Pembayaran:**
   - Nonaktifkan rute `POST /siswa/pembayaran/midtrans-simulasi` pada lingkungan produksi (`if (app()->isProduction()) abort(403);`).
   - Hubungkan tombol pembayaran siswa secara nyata ke `MidtransService::createSnapTransaction()` menggunakan Midtrans Snap JS Popup.
2. **Kunci Ketat Webhook Midtrans (*Fail-Closed*):**
   - Hapus pengecekan `if ($this->isConfigured())`. Jika server key tidak terkonfigurasi, webhook harus langsung ditolak dengan HTTP 500 / 401.
   - Tambahkan validasi nominal ketat:
     ```php
     if ((float)$pembayaran->total_bayar !== (float)$grossAmount) {
         Log::warning("Midtrans Webhook: Nominal tidak cocok. Tagihan: {$pembayaran->total_bayar}, Diterima: {$grossAmount}");
         return ['success' => false, 'message' => 'Gross amount mismatch.'];
     }
     ```
3. **Penerapan Sequence Lock pada Pembuatan Nomor Pendaftaran:**
   - Gunakan tabel sekuens terpisah atau kunci baris (`DB::table('pendaftar')->where('no_pendaftaran', 'LIKE', "{$prefix}%")->lockForUpdate()->max('id_pendaftar')`) untuk mencegah tabrakan nomor pendaftaran saat pendaftaran membludak.
4. **Sanitasi Formula Injection & Karakter Kontrol XML:**
   - Pada `XlsxExportService.php`: Bersihkan karakter kontrol XML dengan regex:
     ```php
     $val = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F]/u', '', (string)$val);
     ```
   - Pada `AdminTUController.php::eksporExcel`: Awali nilai sel yang dimulai dengan `=`, `+`, `-`, `@` dengan tanda petik tunggal (`'`).
5. **Perbaikan Tampilan Pasfoto Kartu Ujian:**
   - Ubah `resources/views/siswa/cetak_kartu_ujian.blade.php` baris 88 dari:
     `{{ asset('storage/' . $pasfoto->path_file) }}`
     menjadi:
     `{{ route('dokumen.berkas', $pasfoto->id_berkas) }}`
6. **Revisi Panduan Deployment (`DEPLOYMENT_GUIDE.md`):**
   - Hapus referensi `Everyone` dan `Full Control`. Ganti dengan instruksi `icacls` izin `Modify` untuk `IIS_IUSRS` / user web server khusus.
   - Perbaiki perintah backup bash menjadi `date +%F` dan tambahkan opsi `--single-transaction --quick`.
7. **Peningkatan Kualitas Pengujian Otomatis:**
   - Tambahkan unit test yang menguji validasi signature SHA-512 dengan key tiruan, penolakan nominal tidak sesuai, dan pembuktian integritas biner berkas XLSX yang diekspor.

---
**Catatan Akhir Auditor:** Audit independen ini diselesaikan dalam kepatuhan penuh terhadap instruksi *read-only*. Tidak ada modifikasi kode, data, maupun konfigurasi server yang dilakukan selama audit berlangsung. Laporan ini siap dijadikan dasar keputusan persetujuan perbaikan berikutnya.

