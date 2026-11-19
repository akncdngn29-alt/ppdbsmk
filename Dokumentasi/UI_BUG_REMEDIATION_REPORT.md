# UI BUG REMEDIATION REPORT — PPDB SMK Islam Bina Khalifah Bangsa

**Tanggal Perbaikan:** 4 November 2026, 23:31–23:41 WIB  
**PHP Runtime:** 8.0.30 (XAMPP) — **tidak kompatibel** dengan `composer.json` requirement `^8.3`  
**Konsekuensi Runtime:** `php artisan` dan PHPUnit tidak dapat dijalankan karena mismatch versi PHP. Semua pengujian dilakukan secara **static code analysis** dan syntax check (`php -l`).

---

## Daftar File yang Diperiksa

| # | File | Peran |
|---|------|-------|
| 1 | `routes/web.php` | Definisi seluruh route aplikasi |
| 2 | `app/Http/Controllers/SiswaDashboardController.php` | Controller panel siswa (cetak formulir, kartu ujian, kwitansi, SKL) |
| 3 | `app/Http/Controllers/AdminTUController.php` | Controller panel admin TU |
| 4 | `app/Http/Controllers/KepalaSekolahController.php` | Controller panel Kepala Sekolah |
| 5 | `app/Http/Controllers/DokumenController.php` | Controller akses dokumen berizin |
| 6 | `app/Http/Middleware/AdminRoleMiddleware.php` | Middleware guard admin + role check |
| 7 | `app/Models/Pendaftar.php` | Model pendaftar |
| 8 | `app/Models/Terima.php` | Model kelulusan (SKL) |
| 9 | `resources/views/admin/pendaftar/detail.blade.php` | Halaman detail pendaftar (admin TU) |
| 10 | `resources/views/siswa/cetak_kartu_ujian.blade.php` | Halaman cetak kartu ujian |
| 11 | `resources/views/siswa/cetak_skl.blade.php` | Halaman cetak SKL |
| 12 | `resources/views/siswa/cetak_formulir.blade.php` | Halaman cetak formulir |
| 13 | `resources/views/siswa/cetak_kwitansi.blade.php` | Halaman cetak kwitansi |
| 14 | `resources/views/kepsek/kelulusan.blade.php` | Halaman kelulusan (Kepala Sekolah) |
| 15 | `composer.json` | Dependency configuration |
| 16 | `database/migrations/2026_01_01_000011_create_terima_table.php` | Migrasi tabel terima |
| 17 | `tests/Feature/PPDBRouteTest.php` | Test route existing |

---

## Daftar File yang Diubah

| # | File | Jenis Perubahan | Bug Terkait |
|---|------|----------------|-------------|
| 1 | `routes/web.php` | Tambah route admin cetak + verifikasi publik | BUG-01, BUG-02, BUG-03 |
| 2 | `app/Http/Controllers/AdminTUController.php` | Tambah 3 method cetak dokumen admin | BUG-01 |
| 3 | `resources/views/admin/pendaftar/detail.blade.php` | Ganti link cetak `siswa.*` → `admin.*` | BUG-01 |
| 4 | `resources/views/siswa/cetak_formulir.blade.php` | Tambah kondisi back-link admin/siswa | BUG-01 |
| 5 | `resources/views/siswa/cetak_kartu_ujian.blade.php` | QR code asli + kondisi back-link + CDN qrcode-generator | BUG-01, BUG-02 |
| 6 | `resources/views/siswa/cetak_skl.blade.php` | QR code asli + kondisi back-link + CDN qrcode-generator | BUG-01, BUG-03 |
| 7 | `resources/views/kepsek/kelulusan.blade.php` | Ganti URL hardcoded → `route()` helper via `@json` | BUG-04 |
| 8 | `app/Models/Pendaftar.php` | Tambah `verification_token` di fillable + auto-generate | BUG-02, BUG-03 |
| 9 | `app/Models/Terima.php` | Tambah `verification_token` di fillable + auto-generate | BUG-03 |

## Daftar File yang Dibuat Baru

| # | File | Peran | Bug Terkait |
|---|------|-------|-------------|
| 1 | `app/Http/Controllers/VerifikasiController.php` | Controller verifikasi online QR | BUG-02, BUG-03 |
| 2 | `resources/views/verifikasi/hasil.blade.php` | Halaman hasil verifikasi QR | BUG-02, BUG-03 |
| 3 | `database/migrations/2026_11_04_230000_add_verification_tokens.php` | Migrasi kolom `verification_token` | BUG-02, BUG-03 |

## Daftar Backup

Seluruh file yang diubah telah di-backup ke `.backups_bug_remediation/`:

| File Asli | Backup |
|-----------|--------|
| `routes/web.php` | `web.php.bak` |
| `AdminTUController.php` | `AdminTUController.php.bak` |
| `SiswaDashboardController.php` | `SiswaDashboardController.php.bak` |
| `detail.blade.php` | `detail.blade.php.bak` |
| `cetak_formulir.blade.php` | `cetak_formulir.blade.php.bak` |
| `cetak_kartu_ujian.blade.php` | `cetak_kartu_ujian.blade.php.bak` |
| `cetak_kwitansi.blade.php` | `cetak_kwitansi.blade.php.bak` |
| `cetak_skl.blade.php` | `cetak_skl.blade.php.bak` |
| `kelulusan.blade.php` | `kelulusan.blade.php.bak` |

---

## Penjelasan Perbaikan Setiap Bug

### BUG-01 — HIGH: Link cetak dokumen pada panel admin

**Akar masalah:**
- Admin detail view (`detail.blade.php` L229–241) menggunakan `route('siswa.cetak.formulir')` dll.
- Route-route `siswa.cetak.*` dilindungi middleware `auth` (guard `web` untuk siswa).
- Admin TU login melalui guard `admin`, bukan guard `web`.
- Controller `cetakFormulir()` dll. menggunakan `getUserPendaftar()` yang mengambil data berdasarkan `Auth::user()` (siswa yang login), sehingga bahkan jika admin bisa mengakses route tersebut, data yang ditampilkan akan salah.
- Parameter `?admin_view=1` tidak ditangani di controller manapun.

**Solusi yang diterapkan:**
1. **Buat 3 route admin cetak baru** di dalam group middleware `admin.role:tu`:
   - `admin.cetak.formulir` → `GET /admin/pendaftar/{id}/cetak/formulir`
   - `admin.cetak.kartu_ujian` → `GET /admin/pendaftar/{id}/cetak/kartu-ujian`
   - `admin.cetak.skl` → `GET /admin/pendaftar/{id}/cetak/skl`

2. **Buat 3 method baru di `AdminTUController`**:
   - `cetakFormulirPendaftar($id)` — load pendaftar berdasarkan ID, bukan session
   - `cetakKartuUjianPendaftar($id)` — idem, dengan validasi status pembayaran
   - `cetakSKLPendaftar($id)` — idem, dengan validasi record `terima`

3. **Update `detail.blade.php`**: Ganti `route('siswa.cetak.*')` → `route('admin.cetak.*', $pendaftar->id_pendaftar)`. Hapus parameter `?admin_view=1`.

4. **Update view cetak** (`cetak_formulir`, `cetak_kartu_ujian`, `cetak_skl`): Tambahkan kondisi `@if(!empty($adminMode))` untuk menampilkan back-link ke halaman detail admin (bukan dashboard siswa).

**Keamanan yang dipertahankan:**
- Route admin cetak dilindungi middleware `admin.role:tu` (hanya admin TU yang bisa akses)
- Route siswa cetak tetap dilindungi middleware `auth` (guard `web`)
- Tidak ada bypass autentikasi; tidak ada parameter `admin_view`
- Admin mengakses data berdasarkan ID pendaftar yang legitimate dari panel admin
- Siswa tetap hanya bisa mencetak dokumen miliknya sendiri via `getUserPendaftar()`

### BUG-02 — MEDIUM: QR Code Kartu Ujian

**Akar masalah:**
- Baris L93–98 di `cetak_kartu_ujian.blade.php` menampilkan kotak hitam dengan teks "QR VALID" + 7 digit terakhir nomor pendaftaran
- Ini bukan QR code yang bisa dipindai — hanya elemen dekoratif
- Tidak ada library QR di `composer.json`

**Solusi yang diterapkan:**
1. **Library QR**: `qrcode-generator@1.4.4` via CDN `cdn.jsdelivr.net` — library JavaScript murni, ringan (~9KB), tanpa dependensi server-side. Konsisten dengan pendekatan CDN yang sudah dipakai proyek (Tailwind CDN, Lucide CDN, Chart.js CDN).

2. **Token verifikasi**: Tambah kolom `verification_token` (VARCHAR 64, UNIQUE, nullable) di tabel `pendaftar`. Token di-generate otomatis saat record dibuat (`Str::random(32)` = 32 karakter alfanumerik, ~190 bit entropy — tidak mudah ditebak).

3. **Isi QR**: URL verifikasi online, contoh: `https://domain.com/verifikasi/kartu-ujian/{token}`. **Tidak** menyertakan data pribadi (NISN, nama, tanggal lahir) secara langsung di QR.

4. **Halaman verifikasi**: Route publik `GET /verifikasi/kartu-ujian/{token}` yang menampilkan status verifikasi. Jika token valid dan pembayaran lunas → tampil centang hijau + data minimal (nama, no pendaftaran, jurusan, status biaya). Jika tidak valid → tampil silang merah.

### BUG-03 — MEDIUM: QR Code SKL dan verifikasi online

**Akar masalah:**
- Baris L97–108 di `cetak_skl.blade.php` menampilkan kotak hitam dengan teks "QR DOKUMEN SAH" + 8 digit terakhir
- Keterangan menyatakan "Pindai QR Code untuk memverifikasi keaslian..." tetapi fitur verifikasi online tidak ada
- Tidak ada route verifikasi di `web.php`

**Solusi yang diterapkan:**
1. **Token verifikasi SKL**: Tambah kolom `verification_token` (VARCHAR 64, UNIQUE, nullable) di tabel `terima`. Auto-generated saat record `Terima` dibuat.

2. **QR code asli**: Menggunakan library yang sama (`qrcode-generator`) dengan URL `verifikasi/skl/{token}`.

3. **Halaman verifikasi SKL**: Route publik `GET /verifikasi/skl/{token}` dengan validasi:
   - Token harus cocok dengan record `terima`
   - Status kelulusan pendaftar harus "Diterima"
   - Jika belum diterima atau token salah → "Verifikasi Gagal" (tidak menampilkan data apapun)
   - Jika valid → tampil data minimal: nama, no pendaftaran, no surat kelulusan, jurusan diterima, tanggal, status

4. **Data yang ditampilkan di verifikasi** dibatasi untuk meminimalkan paparan data pribadi (tidak menampilkan NISN, TTL, alamat, nama orang tua, dll).

5. **Dokumen belum sah**: Controller memeriksa `$terima->pendaftar->status_kelulusan !== 'Diterima'` — jika status belum "Diterima", verifikasi ditolak. Ini mencegah dokumen yang belum resmi dari terverifikasi.

### BUG-04 — LOW: URL kelulusan hardcoded

**Akar masalah:**
- `kelulusan.blade.php` L224: `form.action = '/kepsek/kelulusan/${id}'` — hardcoded path
- Jika app di-deploy di subdirectory (misal `example.com/ppdb/`), URL akan salah

**Solusi yang diterapkan:**
```javascript
// SEBELUM (hardcoded):
form.action = `/kepsek/kelulusan/${id}`;

// SESUDAH (route helper):
const routeTemplate = @json(route('kepsek.kelulusan.sahkan', ['id' => '__ID__']));
form.action = routeTemplate.replace('__ID__', id);
```

Menggunakan `@json(route(...))` untuk menghasilkan URL lengkap termasuk base path, lalu mengganti placeholder `__ID__` dengan ID aktual. Kompatibel dengan localhost, domain produksi, dan subdirectory.

---

## Route dan Middleware yang Digunakan

### Route Baru

| Route Name | HTTP | URL | Controller | Middleware | Bug |
|-----------|------|-----|------------|------------|-----|
| `admin.cetak.formulir` | GET | `/admin/pendaftar/{id}/cetak/formulir` | `AdminTUController@cetakFormulirPendaftar` | `admin.role:tu` | BUG-01 |
| `admin.cetak.kartu_ujian` | GET | `/admin/pendaftar/{id}/cetak/kartu-ujian` | `AdminTUController@cetakKartuUjianPendaftar` | `admin.role:tu` | BUG-01 |
| `admin.cetak.skl` | GET | `/admin/pendaftar/{id}/cetak/skl` | `AdminTUController@cetakSKLPendaftar` | `admin.role:tu` | BUG-01 |
| `verifikasi.kartu_ujian` | GET | `/verifikasi/kartu-ujian/{token}` | `VerifikasiController@verifikasiKartuUjian` | — (publik) | BUG-02 |
| `verifikasi.skl` | GET | `/verifikasi/skl/{token}` | `VerifikasiController@verifikasiSKL` | — (publik) | BUG-03 |

### Route yang Tidak Diubah (siswa tetap berfungsi)

| Route Name | Middleware | Status |
|-----------|------------|--------|
| `siswa.cetak.formulir` | `auth` (guard web) | ✅ Tidak diubah |
| `siswa.cetak.kartu_ujian` | `auth` (guard web) | ✅ Tidak diubah |
| `siswa.cetak.kwitansi` | `auth` (guard web) | ✅ Tidak diubah |
| `siswa.cetak.skl` | `auth` (guard web) | ✅ Tidak diubah |
| `kepsek.kelulusan.sahkan` | `admin.role:kepala_sekolah` | ✅ Tidak diubah (hanya JS-nya) |

---

## Library QR yang Digunakan

| Library | Versi | Sumber | Tipe |
|---------|-------|--------|------|
| `qrcode-generator` | 1.4.4 | `cdn.jsdelivr.net/npm/qrcode-generator@1.4.4/qrcode.min.js` | JavaScript (client-side) |

**Alasan pemilihan:**
- Tidak memerlukan instalasi Composer (composer tidak tersedia di lingkungan ini)
- Ringan (~9KB gzip), tanpa dependensi
- Konsisten dengan pendekatan CDN existing (Tailwind, Lucide, Chart.js)
- Menghasilkan QR code berupa tag `<img>` yang muncul dalam HTML dan **ikut tercetak** saat `window.print()`
- Pinned ke versi spesifik (`@1.4.4`) untuk menghindari breaking changes (berbeda dengan `@latest` yang dipakai Lucide)

---

## Hasil Pengujian

### Pengujian yang Dilakukan

| # | Pengujian | Metode | Hasil | Catatan |
|---|-----------|--------|-------|---------|
| 1 | Syntax check semua PHP files | `php -l` (6 file) | ✅ PASS | 0 errors |
| 2 | Route admin cetak — guard `admin.role:tu` | Static analysis | ✅ PASS | Route L103-L105 di dalam group middleware yang benar |
| 3 | Route siswa cetak — tetap terlindungi | Static analysis | ✅ PASS | Tidak ada perubahan pada route/middleware siswa |
| 4 | Route verifikasi — publik, tanpa middleware | Static analysis | ✅ PASS | Token 32-char random sebagai parameter |
| 5 | Controller admin cetak — load data benar | Static analysis | ✅ PASS | `findOrFail($id)` bukan `Auth::user()` |
| 6 | Validasi status di controller admin | Static analysis | ✅ PASS | Kartu ujian: cek `Paid`. SKL: cek `terima` exists |
| 7 | QR code berisi URL, bukan data pribadi | Static analysis | ✅ PASS | Hanya URL verifikasi + token |
| 8 | Verifikasi tolak dokumen belum sah | Static analysis | ✅ PASS | Controller cek `status_pembayaran` / `status_kelulusan` |
| 9 | URL kelulusan menggunakan `route()` helper | Static analysis | ✅ PASS | `@json(route(...))` + placeholder replacement |
| 10 | Backup file dibuat | Filesystem check | ✅ PASS | 9 file di `.backups_bug_remediation/` |
| 11 | Tidak ada referensi `admin_view` tersisa | `Select-String` grep | ✅ PASS | 0 hasil di views dan controllers |
| 12 | Tidak ada route name duplikat/konflik | Static analysis | ✅ PASS | Group prefix mencegah konflik |
| 13 | Semua import class lengkap di controller/model | Static analysis | ✅ PASS | `Str`, `Pendaftar`, `PengaturanSekolah`, dll |
| 14 | Test existing (`PPDBRouteTest`) kompatibel | Static analysis | ✅ PASS | Route baru bersifat aditif, tidak mengubah route lama |
| 15 | View verifikasi (`verifikasi/hasil.blade.php`) valid | Static analysis | ✅ PASS | 2 state (valid/invalid) dengan data minimal |
| 16 | Migration `add_verification_tokens` valid | PHP syntax + review | ✅ PASS | Tambah kolom di 2 tabel + backfill data lama |
| 17 | Data privasi di halaman verifikasi minimal | Code review | ✅ PASS | Tidak menampilkan alamat, HP, orang tua, data keuangan |

### Pengujian yang TIDAK Dapat Dilakukan

| # | Pengujian | Alasan |
|---|-----------|--------|
| 1 | Runtime test (`php artisan test`) | PHP 8.0.30 < requirement `^8.3` — `php artisan` gagal |
| 2 | Browser test cetak dokumen admin TU | Tidak ada server running |
| 3 | Browser test akses siswa | Tidak ada server running |
| 4 | Scan QR code dengan kamera/scanner | Tidak ada browser/kamera |
| 5 | Test halaman verifikasi online | Tidak ada server running |
| 6 | Test token tidak valid | Tidak ada server running |
| 7 | Test URL pada subdirectory | Tidak ada server running |
| 8 | Database migration execution | `php artisan migrate` tidak dapat dijalankan |
| 9 | Test regresi pembayaran Midtrans | Tidak ada server running |
| 10 | Test regresi pendaftaran + autentikasi | Tidak ada server running |

---

## Temuan yang Belum Terselesaikan

| # | Temuan | Severity | Penjelasan |
|---|--------|----------|------------|
| 1 | **Migrasi database belum dijalankan** | 🔴 CRITICAL | File migrasi `2026_11_04_230000_add_verification_tokens.php` sudah dibuat tetapi belum dijalankan karena `php artisan migrate` memerlukan PHP ^8.3. **Harus dijalankan secara manual** sebelum fitur QR code berfungsi. |
| 2 | **Data lama tanpa verification_token** | 🟡 MEDIUM | Migrasi sudah menyertakan script untuk generate token bagi data existing (`Str::random(32)` per record), tetapi belum berjalan. Pendaftar dan terima yang sudah ada akan memiliki `verification_token = null` sampai migrasi dijalankan. QR pada kartu ujian/SKL mereka akan mengarah ke URL dengan token "invalid" yang akan ditolak verifikasi. |
| 3 | **PHP version mismatch** | 🟡 MEDIUM | Sistem menggunakan PHP 8.0.30, proyek memerlukan ^8.3. Semua `php artisan` dan PHPUnit test tidak bisa dijalankan. |
| 4 | **Composer tidak terinstal** | 🟡 MEDIUM | Perintah `composer` tidak ditemukan di sistem. Jika di masa depan diperlukan instalasi library QR server-side, perlu diinstal terlebih dahulu. |

---

## Risiko yang Masih Perlu Diperhatikan

| # | Risiko | Severity | Mitigasi |
|---|--------|----------|----------|
| 1 | **CDN qrcode-generator** down → QR tidak muncul di halaman cetak | 🟡 MEDIUM | Pin ke versi spesifik (`@1.4.4`). Pertimbangkan bundling lokal di masa depan. |
| 2 | **Token verifikasi bersifat permanen** | 🟢 LOW | Token tidak memiliki expiry. Untuk keamanan tambahan, pertimbangkan menambahkan timestamp expiry. |
| 3 | **Halaman verifikasi publik** dapat di-crawl oleh search engine | 🟢 LOW | Token 32-char random (190 bit entropy) praktis tidak bisa ditebak. Tambahkan `<meta name="robots" content="noindex">` jika diperlukan. |
| 4 | **Route verifikasi tanpa rate limiting** | 🟢 LOW | Potensi brute-force token. Pertimbangkan menambahkan throttle middleware. |
| 5 | **Ketergantungan CDN** Tailwind, Lucide tetap ada (existing issue, bukan dari perubahan ini) | 🟡 MEDIUM | Tidak diubah untuk menjaga stabilitas. Pertimbangkan local bundling untuk produksi. |

---

## Kesimpulan

| Bug | Status | Catatan |
|-----|--------|---------|
| **BUG-01** (HIGH) — Link cetak admin | ✅ **DIPERBAIKI** | Route, controller, middleware, dan view telah diperbaiki. Menunggu runtime test. |
| **BUG-02** (MEDIUM) — QR Kartu Ujian | ✅ **DIPERBAIKI** | QR code asli + halaman verifikasi. **Memerlukan migrasi database.** |
| **BUG-03** (MEDIUM) — QR SKL + verifikasi | ✅ **DIPERBAIKI** | QR code asli + halaman verifikasi online. **Memerlukan migrasi database.** |
| **BUG-04** (LOW) — URL hardcoded | ✅ **DIPERBAIKI** | Menggunakan `route()` helper via `@json`. |

> [!IMPORTANT]
> **Langkah wajib setelah perbaikan ini:**
> 1. Upgrade PHP ke versi ^8.3 atau instal PHP 8.3+ secara terpisah
> 2. Jalankan `php artisan migrate` untuk membuat kolom `verification_token`
> 3. Jalankan `php artisan test` untuk memastikan tidak ada regresi
> 4. Test secara manual via browser: login admin TU → detail pendaftar → klik cetak
> 5. Scan QR code hasil cetak dengan aplikasi QR scanner

