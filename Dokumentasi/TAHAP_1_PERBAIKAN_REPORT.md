# LAPORAN HASIL PERBAIKAN TAHAP 1
## SISTEM INFORMASI PPDB — SMK ISLAM BINA KHALIFAH BANGSA, METRO, LAMPUNG
**Tanggal Pelaksanaan**: 05 November 2026  
**Status Keseluruhan**: **PASS (100% Berhasil & Teruji)**  
**Referensi**: `SPEC_PPDB.md`, `AUDIT_INVENTARIS_FITUR_PPDB.md`, `tugas.md`

---

## 1. RINGKASAN PEKERJAAN

Tahap 1 berfokus pada 3 pekerjaan perbaikan krusial pada sistem PPDB yang sudah ada tanpa merusak fungsionalitas yang berjalan, tanpa mengubah struktur database yang ada, dan tanpa memanipulasi file lingkungan `.env`:

1. **Pekerjaan 1 — Perbaikan Bug Audit Log Kepala Sekolah**:
   - Memperbaiki ketidaksesuaian pemanggilan atribut model pada antarmuka audit log (`$log->user->nama`, `$log->aksi`, `$log->id_data`).
   - Menerapkan penanganan relasi kosong (*null safety*) untuk mencegah error runtime jika terdapat entitas user/admin yang tidak ditemukan.
   - Memperkuat kontrol akses agar halaman hanya dapat diakses oleh Kepala Sekolah (*defense-in-depth*).
   - Mempertahankan konsistensi desain UI, kartu metrik, sidebar, dan modal penampil perbedaan data JSON.

2. **Pekerjaan 2 — Rate Limiting Login (Mitigasi Brute Force)**:
   - Menerapkan pembatasan laju percobaan login (*rate limiting*) menggunakan `Illuminate\Support\Facades\RateLimiter` pada kedua guard otentikasi (`web` untuk Calon Siswa dan `admin` untuk Petugas TU / Kepala Sekolah).
   - Kunci throttle dirancang berbasis guard, identitas akun ter-normalisasi, dan alamat IP (`login:{guard}:{identifier}|{ip}`).
   - Mencegah fenomena *denial of service* terhadap alamat IP bersama (misal: lab komputer sekolah atau NAT ISP) sehingga kegagalan satu akun tidak mengunci akun lain pada IP yang sama.
   - Menyediakan pesan umpan balik informatif berbahasa Indonesia dengan hitung mundur detik pemulihan.
   - Menjaga kerahasiaan kredensial: kata sandi tidak pernah disimpan dalam log maupun dikembalikan ke input session.

3. **Pekerjaan 3 — Melengkapi Audit Trail Sistem**:
   - Menambahkan pencatatan audit trail pada verifikasi berkas per lembar (`verifikasiSingleBerkas`) mencakup aktor, role, jenis berkas, pendaftar terkait, serta rekaman *diff* status dan catatan lama vs baru.
   - Menambahkan pencatatan audit trail pada seluruh aksi unduhan laporan dan ekspor: Ekspor CSV (`EKSPOR_CSV`), Ekspor Excel Native (`EKSPOR_XLSX`), Ekspor Template Dapodik (`EKSPOR_DAPODIK`), dan Cetak Rekapitulasi Lembaga PDF (`UNDUH_REKAP_PDF`).
   - Memperbarui rute ekspor agar dapat diakses secara sah oleh Petugas TU maupun Kepala Sekolah (`admin.role:tu,kepala_sekolah`).

---

## 2. DAFTAR FILE YANG DIPERIKSA

| No | File | Deskripsi Pemeriksaan |
|---|---|---|
| 1 | `resources/views/kepsek/audit_log.blade.php` | Antarmuka audit trail pengawasan Kepala Sekolah. |
| 2 | `app/Http/Controllers/KepalaSekolahController.php` | Controller modul kepsek: dashboard, sidang kelulusan, laporan, audit log. |
| 3 | `app/Http/Controllers/AuthController.php` | Pengendali autentikasi Calon Siswa dan Staf Administrasi. |
| 4 | `app/Http/Controllers/AdminTUController.php` | Pengendali operasional TU: verifikasi berkas, ekspor CSV, XLSX, Dapodik, Rekap PDF. |
| 5 | `routes/web.php` | Definisi rute, middleware peran multi-guard, dan endpoint sistem. |
| 6 | `app/Models/LogAktivitas.php` | Model audit trail `log_aktivitas` dan helper method `log()`. |
| 7 | `app/Models/User.php` | Model akun calon siswa (`user`). |
| 8 | `app/Models/Admin.php` | Model staf tata usaha dan kepala sekolah (`admin`). |
| 9 | `app/Models/BerkasPendaftar.php` | Model dokumen berkas pendaftar digital (`berkas_pendaftar`). |
| 10 | `app/Models/Pendaftar.php` | Model biodata pendaftar (`pendaftar`). |
| 11 | `database/migrations/2026_01_01_000013_create_log_aktivitas_table.php` | Skema fisik tabel `log_aktivitas`. |

---

## 3. DAFTAR FILE YANG DIUBAH & LOKASI BACKUP

Sebelum dilakukan perubahan, seluruh file telah dicadangkan secara aman pada direktori `.backups_tahap_1/`:

| No | File yang Dimodifikasi | File Backup Asli |
|---|---|---|
| 1 | `resources/views/kepsek/audit_log.blade.php` | `.backups_tahap_1/audit_log.blade.php.bak` |
| 2 | `app/Http/Controllers/KepalaSekolahController.php` | `.backups_tahap_1/KepalaSekolahController.php.bak` |
| 3 | `app/Http/Controllers/AuthController.php` | `.backups_tahap_1/AuthController.php.bak` |
| 4 | `app/Http/Controllers/AdminTUController.php` | `.backups_tahap_1/AdminTUController.php.bak` |
| 5 | `routes/web.php` | `.backups_tahap_1/web.php.bak` |
| 6 | `tests/Feature/Tahap1PerbaikanTest.php` *(File Baru)* | *-* |

---

## 4. PENJELASAN PERBAIKAN SETIAP PEKERJAAN

### A. Pekerjaan 1: Perbaikan Bug Audit Log Kepala Sekolah
1. **Atribut Nama Aktor Calon Siswa**:
   - *Sebelum*: Mengakses `$log->user->name` yang menyebabkan error/nilai kosong karena model `User` dan tabel `user` menggunakan atribut `nama`.
   - *Sesudah*: Mengakses `{{ $log->user->nama ?? $log->user->username ?? 'Calon Siswa' }}`.
2. **Kategori / Jenis Aksi**:
   - *Sebelum*: Mengakses `$log->tipe_aktivitas` yang tidak ada pada tabel `log_aktivitas`.
   - *Sesudah*: Mengakses `$log->aksi` dengan pengondisian badge warna untuk `PENGESAHAN`, `VERIFIKASI`, `LOGIN/AUTH`, `UPDATE/SIMPAN/UNLOCK`, `EKSPOR/DOWNLOAD/UNDUH`, dan `BAYAR/PAYMENT`.
3. **ID Objek Entitas**:
   - *Sebelum*: Mengakses `$log->id_entitas` yang tidak sesuai skema database.
   - *Sesudah*: Mengakses `$log->id_data`.
4. **Penanganan Kasus Null/Orphaned**:
   - Menambahkan blok penanganan `@elseif($log->id_admin)` dan `@elseif($log->id_user)` jika ID tercatat namun relasi model telah terhapus, serta fallback `Sistem Otomatis` jika keduanya bernilai `null`.
5. **Otorisasi Ketat Kepala Sekolah**:
   - Menambahkan pengamanan ganda (*defense-in-depth*) pada `KepalaSekolahController::auditLog()` dengan memanggil:
     `abort_if(!auth('admin')->check() || auth('admin')->user()->role !== 'kepala_sekolah', 403, 'Akses khusus Kepala Sekolah.');`

### B. Pekerjaan 2: Rate Limiting Login
1. **Mekanisme Throttle Key**:
   - Dibangun dengan method terisolasi:
     ```php
     protected function throttleKey(Request $request, string $guard): string
     {
         $identifier = Str::lower(trim((string) $request->input('login')));
         return 'login:' . $guard . ':' . Str::transliterate($identifier) . '|' . $request->ip();
     }
     ```
2. **Batas Percobaan & Cooldown**:
   - Maksimal **5 kali** percobaan gagal berturut-turut.
   - Waktu penguncian sementara (*decay rate*): **60 detik**.
   - Setiap percobaan gagal memanggil `RateLimiter::hit($throttleKey, 60)`.
   - Percobaan ke-6 langsung diblokir sebelum mencoba query autentikasi dengan pesan:
     `"Terlalu banyak percobaan login. Silakan coba lagi dalam {$seconds} detik."`
3. **Pembersihan Otomatis Saat Berhasil**:
   - Saat kombinasi kredensial valid, sistem memanggil `RateLimiter::clear($throttleKey)` dan merestart sesi (`session()->regenerate()`).
4. **Anti-Penguncian Massal IP**:
   - Karena kunci menggabungkan akun dan IP, kegagalan akun A pada IP tertentu tidak akan mengunci akun B pada IP yang sama.

### C. Pekerjaan 3: Melengkapi Audit Trail
1. **Verifikasi Single Berkas**:
   - Pada `AdminTUController::verifikasiSingleBerkas()`, ditambahkan pencatatan aktivitas:
     ```php
     LogAktivitas::log(
         'VERIFIKASI_BERKAS',
         "{$namaAdmin} memverifikasi berkas {$jenisBerkas} milik {$namaSiswa}: {$request->status_berkas}",
         'berkas_pendaftar',
         $berkas->id_berkas,
         ['status_berkas' => $oldStatus, 'catatan' => $oldCatatan],
         ['status_berkas' => $request->status_berkas, 'catatan' => $request->catatan]
     );
     ```
2. **Unduhan Laporan & Ekspor**:
   - `eksporExcel()`: Mencatat `EKSPOR_CSV` dengan nama file dan jumlah baris data pendaftar.
   - `eksporXlsx()`: Mencatat `EKSPOR_XLSX` dengan parameter filter (jurusan, status bayar, status kelulusan).
   - `eksporDapodik()`: Mencatat `EKSPOR_DAPODIK` khusus data calon siswa yang berstatus Diterima.
   - `cetakRekapPDF()`: Mencatat `UNDUH_REKAP_PDF` beserta informasi program keahlian yang dicetak.
3. **Akses Multi-Role Rute Ekspor**:
   - Rute ekspor dipindahkan ke grup middleware `admin.role:tu,kepala_sekolah` sehingga tombol cetak rekap dan ekspor pada panel Kepala Sekolah (`resources/views/kepsek/laporan.blade.php`) dapat diakses tanpa menghasilkan error HTTP 403 Forbidden.

---

## 5. PERUBAHAN BASIS DATA

**Tidak ada perubahan struktur DDL tabel utama yang dilakukan.**  
Skema tabel `log_aktivitas` yang sudah ada terbukti telah memenuhi kaidah Third Normal Form (3NF) dan mendukung kolom JSON `data_lama` serta `data_baru`. Seluruh data riwayat, pendaftar, akun, dan pembayaran tetap utuh tanpa eksekusi `migrate:fresh` maupun `db:wipe`.

---

## 6. HASIL PENGUJIAN OTOMATIS

### A. Uji Sintaks PHP (Linting)
| File | Perintah | Status |
|---|---|---|
| `app/Http/Controllers/KepalaSekolahController.php` | `php -l app/Http/Controllers/KepalaSekolahController.php` | **PASS** |
| `app/Http/Controllers/AuthController.php` | `php -l app/Http/Controllers/AuthController.php` | **PASS** |
| `app/Http/Controllers/AdminTUController.php` | `php -l app/Http/Controllers/AdminTUController.php` | **PASS** |
| `routes/web.php` | `php -l routes/web.php` | **PASS** |

### B. Uji Fitur Khusus Tahap 1 (`tests/Feature/Tahap1PerbaikanTest.php`)
| No | Kasus Uji | Aspek yang Diuji | Status |
|---|---|---|---|
| 1 | `test_kepsek_audit_log_view_renders_correctly` | Render view Blade audit log, verifikasi `$log->user->nama`, `$log->aksi`, `$log->id_data`, serta null safety. | **PASS** |
| 2 | `test_audit_log_access_control` | Hak akses eksklusif Kepala Sekolah (tamu, siswa, dan Petugas TU ditolak). | **PASS** |
| 3 | `test_student_login_success_and_clears_throttle` | Login normal siswa berhasil dan me-reset throttle counter. | **PASS** |
| 4 | `test_student_login_rate_limiting_after_repeated_failures` | Percobaan gagal 5x mengunci login pada percobaan ke-6 dengan pesan hitung mundur dan pulih setelah clear. | **PASS** |
| 5 | `test_rate_limiter_does_not_lock_different_account_on_same_ip` | Kegagalan akun 1 tidak mengunci akun 2 pada IP yang sama. | **PASS** |
| 6 | `test_admin_login_rate_limiting` | Rate limiting aktif pada login Petugas TU / Kepala Sekolah. | **PASS** |
| 7 | `test_verifikasi_single_berkas_logs_activity` | Verifikasi per item berkas tercatat di `log_aktivitas` beserta diff status. | **PASS** |
| 8 | `test_export_and_report_downloads_log_activity_for_tu_and_kepsek` | Unduhan CSV, XLSX, Dapodik, dan Rekap PDF oleh TU & Kepsek tercatat di log. | **PASS** |

**Hasil Uji Tahap 1**: **8 PASS, 0 FAIL** (115 assertions).

### C. Uji Regresi Keseluruhan Test Suite
Menjalankan seluruh rangkaian tes pada proyek (`php artisan test`):
- **Total Pengujian**: 44 Tests
- **Passed**: 44
- **Failed**: 0
- **Total Assertions**: 288
- **Durasi**: 12.92 detik
- **Status Regresi**: **PASS (0 Regression)**

---

## 7. KENDALA YANG BELUM TERSELESAIKAN

Tidak ada kendala pada ruang lingkup perbaikan Tahap 1. Seluruh target pada Pekerjaan 1, 2, dan 3 telah selesai diimplementasikan dan diverifikasi berjalan dengan baik.

---

## 8. RISIKO YANG MASIH PERLU DIPERHATIKAN

1. **Konfigurasi Cache Driver untuk Rate Limiting**:
   - Pada file `.env`, aplikasi saat ini menggunakan `CACHE_STORE=file`. Driver file sudah memadai untuk lingkungan peladen tunggal (*single-server*). Jika di masa depan sistem beralih ke arsitektur multi-server / load balancer, disarankan menggunakan driver Redis (`CACHE_STORE=redis`) agar hitungan rate limiting tersinkronisasi antar-node.
2. **Kredensial Server Key Midtrans**:
   - Untuk pelunasan online produksi, pastikan konfigurasi `MIDTRANS_SERVER_KEY` dan `MIDTRANS_CLIENT_KEY` diisi dengan kredensial akun produksi aktif sebelum pendaftaran umum dibuka secara luas.
3. **Penyimpanan Berkas Pribadi**:
   - Dokumen berkas pendaftar tersimpan secara privat pada disk lokal dengan otorisasi kepemilikan. Pastikan direktori `storage/app/berkas_pendaftar` memiliki izin baca/tulis yang tepat pada sistem operasi peladen.

---
**Pekerjaan Tahap 1 telah selesai sesuai seluruh instruksi pada `tugas.md`. Sistem berada dalam kondisi stabil dan siap untuk instruksi berikutnya.**

