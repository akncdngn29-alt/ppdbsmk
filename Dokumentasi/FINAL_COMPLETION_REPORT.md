# LAPORAN PENYELESAIAN TOTAL SISTEM PPDB
## SMK ISLAM BINA KHALIFAH BANGSA (KOTA METRO, LAMPUNG)

**Tanggal Penyelesaian**: 05 November 2026  
**Status Akhir**: **SELESAI & SIAP DEMONSTRASI (100% TERUJI & TERINTEGRASI)**  
**Framework**: Laravel 13.17 | PHP 8.5.12 | MySQL/MariaDB  
**Hasil Test Suite**: **115 PASSED, 550 ASSERTIONS, 0 FAILED**  

---

## 1. RINGKASAN KONDISI PROYEK SEBELUM DAN SESUDAH PENYELESAIAN

| Aspek | Kondisi Sebelum Penyelesaian | Kondisi Sesudah Penyelesaian |
| :--- | :--- | :--- |
| **Penyimpanan Formulir** | Validasi kaku mewajibkan seluruh kolom terisi sekaligus. Calon siswa tidak dapat menyimpan draft parsial. | Didukung mekanisme draft bertahap (*multi-step draft*) dan finalisasi ketat dengan validasi lengkap sebelum data dikunci (*is_locked*). |
| **Jadwal & Ruang Seleksi** | Informasi jadwal dan ruang ujian pada kartu peserta masih bersifat teks statis (*hardcoded*). | Modul manajemen jadwal dan ruang seleksi interaktif dengan alokasi otomatis berkapasitas ruang, penempatan manual, dan kartu ujian dinamis ber-QR Code. |
| **Audit Trail (Log Aktivitas)** | Terdapat bug nama atribut model di Blade kepsek (`$log->user->name`), aksi satuan dan unduhan laporan belum tercatat, siswa dan TU tidak punya antarmuka log. | Terintegrasi penuh untuk 3 role (`calon_siswa`, `tu`, `kepala_sekolah`) dengan filter pencarian, filter role/status/tanggal, dan modal rincian diff perubahan data (dengan auto-redaksi data sensitif). |
| **Keamanan Otentikasi** | Rute login belum dipasangi pembatasan laju (*rate limiting*). | Proteksi `RateLimiter` aktif pada guard `web` dan `admin` (kunci per-guard, identifier ternormalisasi, dan IP), mitigasi serangan brute-force. |
| **Import Data Pendaftar** | Belum tersedia antarmuka atau modul untuk import data siswa offline. | Modul Import Excel/CSV massal siap pakai dengan validasi baris, pemetaan kolom otomatis, deteksi duplikasi NISN, dan laporan ringkasan impor. |
| **Manajemen Akun Staf** | Penambahan staf admin hanya bisa dilakukan via database seeder atau konsol artisan. | CRUD Web Manajemen Akun Staf (`/admin/staf`) lengkap dengan validasi peran, proteksi pencegahan eskalasi hak akses (*privilege escalation*), dan audit logging. |
| **Ringkasan Keuangan Kepsek** | Kepala Sekolah belum memiliki menu pemantauan finansial PPDB secara terpusat. | Tersedia halaman Ringkasan Keuangan (`/kepsek/keuangan`) dengan kartu KPI pendapatan, breakdown per gelombang dan jurusan, serta tabel buku kas transaksi. |
| **Pusat Bantuan Calon Siswa** | FAQ hanya ada di landing page luar, belum ada di portal dalam siswa. | Menu khusus `/siswa/bantuan` di sidebar calon siswa memuat panduan 6 tahapan, informasi jam pelayanan loket TU, kontak panitia, dan accordion FAQ interaktif. |
| **Pencadangan Database** | Belum ada fitur backup database 1-klik di panel admin. | Modul Backup & Restore Database mandiri (`/admin/backup`) dengan dump SQL native portabel, download aman, pemulihan terproteksi, serta command CLI `php artisan db:backup`. |
| **Notifikasi Eksternal** | Terdapat fungsi pengiriman ke provider WhatsApp pihak ketiga dan SMTP. | **Seluruh pengiriman keluar dinonaktifkan secara permanen** sesuai Section 4.E tugas.md; dialihkan ke logging/simulator internal. |
| **Halaman Error Sistem** | Masih menggunakan halaman default framework. | Tersedia halaman error khusus bernuansa islami-modern SMK IB untuk kode status HTTP 403, 404, dan 500. |

---

## 2. SELURUH FITUR YANG SUDAH BERFUNGSI

### A. Portal Calon Siswa (`web` guard)
1. **Registrasi Akun Baru**: Pendaftaran akun mandiri via NISN 10 digit, nama lengkap, email, nomor kontak WhatsApp, dan kata sandi.
2. **Login Multi-Identifier**: Login menggunakan salah satu dari 3 opsi: NISN, Alamat Email, atau Username, terproteksi Rate Limiter.
3. **Dashboard Progres 6-Tahap**: Menampilkan stepper dinamis (Formulir -> Pembayaran -> Verifikasi Berkas -> Seleksi Masuk -> Kelulusan -> Daftar Ulang), status kelulusan, dan pengumuman sekolah.
4. **Formulir Biodata & Simpan Draft**: Pengisian bertahap data calon siswa, data orang tua/wali, data sekolah asal, ukuran seragam, dan pilihan jurusan. Dapat disimpan kapan saja sebagai draft sementara.
5. **Finalisasi & Penguncian Data**: Pemeriksaan kelengkapan seluruh kolom wajib sebelum formulir dikunci (*is_locked = true*).
6. **Upload & Pratinjau Dokumen**: Pengunggahan berkas digital (Ijazah/SKL, KK, Akta Kelahiran, Pasfoto, dan Sertifikat Prestasi) ke private storage berizin (UU PDP).
7. **Checklist Status Berkas**: Indikator status berkas dinamis (*Menunggu*, *Terverifikasi*, *Ditolak/Revisi*) disertai catatan koreksi langsung dari panitia TU.
8. **Pembayaran Biaya Formulir**: Pilihan pembayaran via gateway Midtrans Snap (QRIS, VA Bank) dan pembayaran tunai di loket kasir TU sekolah.
9. **Jadwal & Kartu Ujian**: Halaman jadwal seleksi dinamis yang menampilkan jadwal sesi tes, ruangan ujian yang telah dialokasikan panitia, dan tombol cetak Kartu Peserta Ujian ber-QR Code.
10. **Pengaturan Akun & Ganti Password**: Pembaruan kontak aktif dan ganti kata sandi secara aman tanpa menyimpan hash di audit trail.
11. **Riwayat Aktivitas Siswa**: Pemantauan transparansi log aktivitas akun pribadi (login, draft, upload berkas, pembayaran, unduh kartu).
12. **Pusat Bantuan & FAQ**: Informasi jam loket TU, alamat kampus, kontak panitia, dan tanya-jawab umum seputar PPDB.
13. **Pencetakan Dokumen Resmi (Format A4)**:
    - Lembar Formulir Pendaftaran Siswa Baru
    - Kartu Tanda Peserta Seleksi Masuk ber-QR Code
    - Kwitansi Pembayaran Lunas Resmi
    - Surat Keterangan Lulus (SKL) Resmi ber-QR Code
14. **Konfirmasi Daftar Ulang**: Formulir kesediaan masuk dan pemilihan ukuran seragam pasca pengumuman diterima.

### B. Panel Petugas Tata Usaha (TU) (`admin` guard, `role = 'tu'`)
1. **Dashboard Analytics TU**: Ringkasan metrik total pendaftar, pendaftar hari ini, total terverifikasi lunas, berkas pending, dan grafik peminat jurusan.
2. **Manajemen Pendaftar**: Tabel data pendaftar lengkap dengan pencarian multi-kolom, filter jurusan, filter status bayar, filter status berkas, dan paginasi.
3. **Dossier & Verifikasi Berkas**: Pemeriksaan dokumen digital melalui *in-browser viewer* dengan fitur rotasi 90° dan zoom, serta verifikasi menyeluruh atau per lembar berkas.
4. **Buka Kunci Formulir (Unlock)**: Pembukaan kunci biodata siswa yang keliru input tanpa merusak status pembayaran atau nilai seleksi yang sudah tercatat.
5. **Konfirmasi Kasir Pembayaran Tunai**: Verifikasi pelunasan tunai pendaftar di loket TU dengan penerbitan otomatis kwitansi resmi.
6. **Manajemen Jadwal Seleksi (CRUD)**: Pembuatan, pengeditan, dan penghapusan sesi tes seleksi masuk per gelombang pendaftaran.
7. **Manajemen Ruang Seleksi & Kapasitas (CRUD)**: Pengaturan kuota daya tampung ruangan tes dan proteksi penurunan kapasitas jika ruang telah terisi.
8. **Penempatan Peserta Otomatis & Manual**: Penempatan peserta lunas ke dalam ruang ujian secara otomatis (FIFO) dengan pembatasan kapasitas ruang, serta pemindahan ruang secara manual.
9. **Cetak Daftar Hadir Ruang Ujian**: Lembar absensi resmi peserta tes per ruangan siap cetak.
10. **Master Data Jurusan & Kuota**: Pengaturan kapasitas daya tampung dan deskripsi 4 program keahlian.
11. **Master Data Gelombang**: Pembuatan gelombang pendaftaran baru (nama, tahun ajaran, tarif biaya, rentang tanggal buka/tutup, status aktif).
12. **Master Voucher Beasiswa/Diskon**: Pembuatan kode voucher potongan biaya formulir dengan limit kuota pemakaian.
13. **Import Data Pendaftar Excel/CSV**: Fasilitas upload massal pendaftar offline dengan pengunduhan template CSV, validasi data, deteksi duplikasi NISN, dan pembuatan akun otomatis.
14. **Manajemen Akun Staf & Hak Akses**: Pengelolaan akun staf TU dan pembaruan profil staf.
15. **Pencadangan & Pemulihan Basis Data (Backup & Restore)**: Backup 1-klik, unduh berkas dump SQL, dan pemulihan data terproteksi kata sandi konfirmasi.
16. **Pusat Laporan & Ekspor**:
    - Ekspor data Excel native (.xlsx)
    - Ekspor CSV aman dari Formula Injection (CWE-1236) & ber-BOM UTF-8
    - Ekspor format resmi Dapodikdasmen
    - Cetak Rekapitulasi PPDB Lembaga Resmi Format PDF
17. **Riwayat Aktivitas TU**: Pemantauan rekapitulasi audit log kerja staf TU.
18. **Pengaturan CMS & Mode Maintenance**: Publikasi pengumuman ticker banner landing page dan saklar mode pemeliharaan website.

### C. Panel Kepala Sekolah (`admin` guard, `role = 'kepala_sekolah'`)
1. **Dashboard Eksekutif**: Ringkasan KPI penerimaan, visualisasi tren pendaftar 7 hari Chart.js, keterisian kuota jurusan, dan Market Intelligence 5 SMP asal terbanyak.
2. **Sidang Kelulusan & Pengesahan SKL**: Pengesahan status kelulusan peserta (Diterima, Cadangan, Tidak Lolos), penetapan jurusan yang diterima, dan penerbitan nomor SKL unik secara otomatis.
3. **Ringkasan Keuangan Eksekutif**: Monitoring buku kas penerimaan biaya formulir, perbandingan pendapatan tunai vs online Midtrans, subsidi voucher, dan rincian per gelombang/jurusan.
4. **Monitoring Jadwal & Ruang Seleksi**: Pengawasan rekapitulasi agenda tes seleksi dan tingkat keterisian kursi per ruangan secara *read-only*.
5. **Laporan Resmi Lembaga**: Matriks persentase keterisian 4 jurusan, sisa kuota, serta pintasan unduhan laporan resmi ber-NPSN sekolah.
6. **Audit Trail Eksekutif**: Pengawasan riwayat seluruh aktivitas pengguna lintas sistem (Calon Siswa, Petugas TU, Kepala Sekolah) dengan rincian IP, User-Agent, dan modal snapshot diff JSON.

---

## 3. FITUR YANG DIPERBAIKI

1. **Audit Log Kepala Sekolah (`kepsek.audit_log`)**: Memperbaiki pemanggilan nama atribut model yang salah di Blade (`$log->user->name` diperbaiki menjadi `$log->user->nama`), field `$log->aksi`, dan `$log->id_data`.
2. **Rate Limiting Login**: Menambahkan proteksi `RateLimiter` pada endpoint autentikasi calon siswa dan staf admin guna mencegah serangan *brute force password guessing*.
3. **Audit Trail Ekspor & Verifikasi Lembaran**: Seluruh aktivitas unduh laporan ekspor (.csv, .xlsx, Dapodik, PDF) dan verifikasi dokumen per lembar kini tercatat secara rinci di `log_aktivitas`.
4. **Formulir Draft Siswa**: Mengubah kolom-kolom biodata pada tabel `pendaftar` menjadi *nullable* agar calon siswa dapat menyimpan draf secara fleksibel sebelum finalisasi.
5. **Integrasi Jadwal pada Kartu Peserta Ujian**: Mengganti teks ruang dan tanggal statis pada kartu ujian dengan data penempatan dinamis dari tabel `penempatan_peserta`, `jadwal_seleksi`, dan `ruang_seleksi`.
6. **Relasi Model Gelombang ke Pembayaran**: Menambahkan method relasi `pembayaran()` pada model `Gelombang` untuk mendukung analitik keuangan.

---

## 4. FITUR BARU YANG DIIMPLEMENTASIKAN

1. **Modul Import Pendaftar Excel/CSV** (`app/Http/Controllers/ImportPendaftarController.php` & `resources/views/admin/pendaftar/import.blade.php`).
2. **Modul Manajemen Akun Staf** (`app/Http/Controllers/ManajemenStafController.php` & `resources/views/admin/staf/index.blade.php`).
3. **Modul Ringkasan Keuangan Kepala Sekolah** (Method `keuangan` pada `KepalaSekolahController.php` & `resources/views/kepsek/keuangan.blade.php`).
4. **Modul Pencadangan & Pemulihan Basis Data** (`app/Http/Controllers/BackupDatabaseController.php`, `app/Console/Commands/DatabaseBackupCommand.php`, & `resources/views/admin/backup/index.blade.php`).
5. **Pusat Bantuan & FAQ Calon Siswa** (Method `bantuan` pada `SiswaDashboardController.php` & `resources/views/siswa/bantuan.blade.php`).
6. **Halaman Error Informatif Bertema Sekolah** (`resources/views/errors/403.blade.php`, `404.blade.php`, `500.blade.php`).

---

## 5. DAFTAR FILE YANG DIBUAT DAN DIUBAH

### A. File Baru (Created)
1. `app/Http/Controllers/ImportPendaftarController.php`
2. `app/Http/Controllers/ManajemenStafController.php`
3. `app/Http/Controllers/BackupDatabaseController.php`
4. `app/Console/Commands/DatabaseBackupCommand.php`
5. `resources/views/admin/pendaftar/import.blade.php`
6. `resources/views/admin/staf/index.blade.php`
7. `resources/views/admin/backup/index.blade.php`
8. `resources/views/kepsek/keuangan.blade.php`
9. `resources/views/siswa/bantuan.blade.php`
10. `resources/views/errors/403.blade.php`
11. `resources/views/errors/404.blade.php`
12. `resources/views/errors/500.blade.php`
13. `tests/Feature/RingkasanKeuanganKepsekTest.php`
14. `tests/Feature/BantuanSiswaTest.php`
15. `tests/Feature/ManajemenStafTest.php`
16. `tests/Feature/ImportPendaftarTest.php`
17. `tests/Feature/BackupDatabaseTest.php`
18. `tests/Feature/ExternalNotificationDisabledTest.php`
19. `FINAL_COMPLETION_REPORT.md`
20. `PANDUAN_PENGGUNAAN_PPDB.md`

### B. File yang Diubah (Modified)
1. `app/Services/NotificationService.php` — Penonaktifan total transmisi eksternal WhatsApp dan Email sesuai Section 4.E.
2. `app/Http/Controllers/KepalaSekolahController.php` — Penambahan method `keuangan(Request $request)`.
3. `app/Http/Controllers/SiswaDashboardController.php` — Penambahan method `bantuan()`.
4. `app/Models/Gelombang.php` — Penambahan relasi `pembayaran()`.
5. `routes/web.php` — Penambahan rute keuangan, bantuan, staf, import, dan backup (total 109 rute terdaftar).
6. `resources/views/layouts/admin.blade.php` — Penambahan tautan sidebar Keuangan (Kepsek), Import Pendaftar (TU), Akun Staf (TU), dan Backup Database (TU).
7. `resources/views/layouts/siswa.blade.php` — Penambahan tautan sidebar Pusat Bantuan & FAQ.
8. `resources/views/admin/pendaftar/index.blade.php` — Penambahan tombol pintas Import Excel/CSV.

### C. Direktori Backup
Seluruh berkas sebelum modifikasi telah dicadangkan secara aman pada folder:  
`d:\BASIS_DATA_LANJUT\PPDB_SMK\.backups_final\`

---

## 6. MIGRATION YANG DITERAPKAN

Semua migrasi berada dalam status **Ran** tanpa ada data pendaftar yang terhapus:

1. `2026_01_01_000001_create_user_table` (Batch 1)
2. `2026_01_01_000002_create_admin_table` (Batch 1)
3. `2026_01_01_000003_create_jurusan_table` (Batch 1)
4. `2026_01_01_000004_create_gelombang_table` (Batch 1)
5. `2026_01_01_000005_create_voucher_table` (Batch 1)
6. `2026_01_01_000006_create_pendaftar_table` (Batch 1)
7. `2026_01_01_000007_create_berkas_pendaftar_table` (Batch 1)
8. `2026_01_01_000008_create_pembayaran_table` (Batch 1)
9. `2026_01_01_000009_create_prestasi_pendaftar_table` (Batch 1)
10. `2026_01_01_000010_create_nilai_seleksi_table` (Batch 1)
11. `2026_01_01_000011_create_terima_table` (Batch 1)
12. `2026_01_01_000012_create_pengumuman_table` (Batch 1)
13. `2026_01_01_000013_create_log_aktivitas_table` (Batch 1)
14. `2026_01_01_000014_create_pengaturan_sekolah_table` (Batch 1)
15. `2026_01_01_000015_add_kuota_terpakai_to_voucher_pendaftaran_table` (Batch 2)
16. `2026_01_01_000016_create_pendaftaran_sequences_table` (Batch 3)
17. `2026_11_04_230000_add_verification_tokens` (Batch 4)
18. `2026_11_05_170000_add_role_and_status_to_log_aktivitas_table` (Batch 5)
19. `2026_11_05_193000_make_pendaftar_fields_nullable_for_draft` (Batch 6)
20. `2026_11_05_200001_create_jadwal_seleksi_table` (Batch 7)
21. `2026_11_05_200002_create_ruang_seleksi_table` (Batch 7)
22. `2026_11_05_200003_create_penempatan_peserta_table` (Batch 7)

---

## 7. HASIL PENGUJIAN SETIAP FITUR

| No | Modul / Fitur yang Diuji | File Test Case | Hasil | Keterangan |
| :--- | :--- | :--- | :--- | :--- |
| 1 | **Audit Repair & Keamanan Umum** | `tests/Feature/AuditRepairTest.php` | **PASS (13/13)** | Verifikasi format NISN, export xlsx, dan sanitasi. |
| 2 | **Unlock Formulir oleh TU** | `tests/Feature/FormulirUnlockTest.php` | **PASS (11/11)** | Pembukaan kunci aman tanpa menghapus pembayaran. |
| 3 | **Audit Trail Tiga Role** | `tests/Feature/RiwayatAktivitasTest.php` | **PASS (12/12)** | Pencatatan login, password change, dan akses log. |
| 4 | **Perbaikan Keamanan & Rate Limit** | `tests/Feature/SecurityRemediationTest.php` | **PASS (10/10)** | Mitigasi brute force dan otorisasi dokumen privat. |
| 5 | **Perbaikan Tahap 1 (Audit Log Bug)** | `tests/Feature/Tahap1PerbaikanTest.php` | **PASS (6/6)** | Perbaikan properti view audit trail kepsek. |
| 6 | **Formulir Draft Siswa** | `tests/Feature/Tahap3DraftFormulirTest.php` | **PASS (13/13)** | Penyimpanan draft bertahap dan finalisasi. |
| 7 | **Manajemen Jadwal & Ruang** | `tests/Feature/Tahap4JadwalSeleksiTest.php` | **PASS (20/20)** | Alokasi otomatis, penempatan manual, dan kuota ruang. |
| 8 | **Rute & Hak Akses Multi-Guard** | `tests/Feature/PPDBRouteTest.php` | **PASS (4/4)** | Isolasi guard web dan admin. |
| 9 | **Ringkasan Keuangan Kepsek** | `tests/Feature/RingkasanKeuanganKepsekTest.php` | **PASS (6/6)** | Perhitungan KPI, breakdown, dan isolasi role. |
| 10 | **Pusat Bantuan & FAQ Siswa** | `tests/Feature/BantuanSiswaTest.php` | **PASS (2/2)** | Tampilan informasi helpdesk dan FAQ. |
| 11 | **Manajemen Akun Staf** | `tests/Feature/ManajemenStafTest.php` | **PASS (6/6)** | CRUD staf, anti-eskalasi hak akses, dan log aktivitas. |
| 12 | **Import Pendaftar Excel/CSV** | `tests/Feature/ImportPendaftarTest.php` | **PASS (5/5)** | Parse berkas, deteksi duplikasi NISN, template CSV. |
| 13 | **Pencadangan Basis Data** | `tests/Feature/BackupDatabaseTest.php` | **PASS (4/4)** | Dump SQL, akses admin, dan command `db:backup`. |
| 14 | **Penonaktifan Notifikasi Eksternal** | `tests/Feature/ExternalNotificationDisabledTest.php` | **PASS (3/3)** | WA dan email dipastikan tidak mengirim keluar. |

---

## 8. HASIL KESELURUHAN TEST SUITE

Pemeriksaan otomatis dijalankan melalui PHPUnit:

```
Tests:    115 passed (550 assertions)
Duration: 9.03s
Result:   SUCCESS (100% PASS)
```

Seluruh 115 test case berjalan sukses tanpa ada satupun *failure* maupun *error*.

---

## 9. HASIL PEMERIKSAAN KEAMANAN SISTEM

1. **Pemisahan Guard Otentikasi**: Guard `web` (Calon Siswa) dan guard `admin` (Staf TU dan Kepala Sekolah) terpisah total di level session dan middleware (`AdminRoleMiddleware`).
2. **Rate Limiting Login**: Menolak percobaan login ke-6 yang berturut-turut gagal dalam durasi 60 detik dengan pesan hitung mundur.
3. **Proteksi Unggahan Dokumen (UU PDP)**: Berkas pendaftar disimpan di private disk `storage/app/berkas_pendaftar` dengan penamaan hash acak 32 karakter dan hanya dapat diunduh melalui controller berizin kepemilikan (`DokumenController`).
4. **Proteksi CSV / XLSX Formula Injection (CWE-1236)**: Seluruh string teks yang diawali karakter formula (`=`, `+`, `-`, `@`) otomatis di-prefix apostrof (`'`) sebelum diekspor.
5. **Anti Privilege Escalation**: Petugas TU biasa tidak dapat membuat, mengedit, atau menghapus akun Kepala Sekolah pada modul Manajemen Staf.
6. **Perlindungan Data Sensitif Audit Trail**: Kolom `password`, `remember_token`, dan `snap_token` otomatis diganti menjadi `[REDACTED]` dalam snapshot log aktivitas.

---

## 10. HASIL PEMERIKSAAN NOTIFIKASI WHATSAPP DAN EMAIL

Sesuai instruksi mutlak pada Section 4.E berkas `tugas.md`:
- Method `NotificationService::sendWhatsApp` telah dinonaktifkan dari memanggil API Fonnte/Wablas keluar.
- Method `NotificationService::sendEmail` telah dinonaktifkan dari pengiriman SMTP.
- `MAIL_MAILER` pada `.env` dan `config/mail.php` dikonfigurasi ke driver `log`.
- Tidak ada queue job, event listener, cron schedule, atau webhook yang mengirimkan pesan keluar.
- Seluruh notifikasi internal aplikasi (pesan flash session, status pendaftaran pada stepper, kartu pengumuman dashboard, dan catatan revisi berkas) tetap berfungsi 100% normal.

---

## 11. AKUN DEMO PENGUJIAN

Untuk keperluan demonstrasi kepada dosen, akun berikut telah tersedia di basis data:

| Peran Pengguna | Username | NISN | Hak Akses | Keterangan Status Akun |
| :--- | :--- | :--- | :--- | :--- |
| **Calon Siswa 1** | `ahmadfauzi` | `0078912345` | Calon Siswa (Portal) | Sudah Lunas, Berkas Terverifikasi, Diterima di TKJ, SKL Terbit. |
| **Calon Siswa 2** | `amandamanopo` | `0078912346` | Calon Siswa (Portal) | Status Menunggu, Formulir Terbuka (Belum Kunci), Siap Uji Draft. |
| **Petugas TU** | `admin_tu` | *-* | Petugas Tata Usaha (Panel TU) | Akses operasional verifikasi, jadwal, import, backup, master data. |
| **Kepala Sekolah** | `kepsek_asliman`| *-* | Kepala Sekolah (Panel Eksekutif) | Akses pengesahan kelulusan, ringkasan keuangan, audit log eksekutif. |

> *Catatan Keamanan: Kata sandi akun demo menggunakan kata sandi standar pengujian lokal yang telah tercantum pada dokumentasi pengembang (`siswa123`, `admin123`, `kepsek123`).*

---

## 12. CARA MENJALANKAN APLIKASI DI LOCALHOST

### Langkah 1: Pastikan Layanan MySQL Aktif
Pastikan Apache dan MySQL (XAMPP) sudah berjalan:
```powershell
# Jalankan MySQL XAMPP jika belum aktif
C:\xampp\mysql_start.bat
```

### Langkah 2: Jalankan Server Pengembangan Laravel
Buka terminal pada direktori proyek `d:\BASIS_DATA_LANJUT\PPDB_SMK`:
```powershell
php artisan serve
```
Server web lokal akan aktif pada alamat: **`http://127.0.0.1:8000`**

### Langkah 3: Perintah Cadangan CLI (Opsional)
Untuk melakukan pencadangan database manual melalui terminal:
```powershell
php artisan db:backup
```

---

## 13. URL HALAMAN UTAMA DAN DASHBOARD SETIAP ROLE

| Halaman / Panel | URL Akses Localhost | Kredensial Demo |
| :--- | :--- | :--- |
| **Beranda Publik (Landing Page)** | `http://127.0.0.1:8000/` | Akses Publik |
| **Formulir Registrasi Calon Siswa** | `http://127.0.0.1:8000/daftar` | Akses Publik |
| **Login Portal Calon Siswa** | `http://127.0.0.1:8000/login` | NISN `0078912345` atau User `ahmadfauzi` |
| **Dashboard Calon Siswa** | `http://127.0.0.1:8000/siswa/dashboard` | Calon Siswa Aktif |
| **Formulir Biodata & Draft** | `http://127.0.0.1:8000/siswa/formulir` | Calon Siswa Aktif |
| **Jadwal & Kartu Ujian Siswa** | `http://127.0.0.1:8000/siswa/jadwal` | Calon Siswa Aktif |
| **Pusat Bantuan & FAQ Siswa** | `http://127.0.0.1:8000/siswa/bantuan` | Calon Siswa Aktif |
| **Login Staf TU & Kepsek** | `http://127.0.0.1:8000/admin/login` | Staf TU / Kepsek |
| **Dashboard Petugas TU** | `http://127.0.0.1:8000/admin/dashboard` | User `admin_tu` |
| **Manajemen Pendaftar TU** | `http://127.0.0.1:8000/admin/pendaftar` | User `admin_tu` |
| **Import Pendaftar Excel/CSV** | `http://127.0.0.1:8000/admin/pendaftar/import` | User `admin_tu` |
| **Manajemen Jadwal Seleksi** | `http://127.0.0.1:8000/admin/jadwal` | User `admin_tu` |
| **Manajemen Ruang Seleksi** | `http://127.0.0.1:8000/admin/ruang-seleksi` | User `admin_tu` |
| **Manajemen Akun Staf** | `http://127.0.0.1:8000/admin/staf` | User `admin_tu` / `kepsek_asliman` |
| **Pencadangan Database TU** | `http://127.0.0.1:8000/admin/backup` | User `admin_tu` |
| **Dashboard Kepala Sekolah** | `http://127.0.0.1:8000/kepsek/dashboard` | User `kepsek_asliman` |
| **Ringkasan Keuangan Kepsek** | `http://127.0.0.1:8000/kepsek/keuangan` | User `kepsek_asliman` |
| **Sidang Kelulusan Kepsek** | `http://127.0.0.1:8000/kepsek/kelulusan` | User `kepsek_asliman` |
| **Audit Trail Eksekutif Kepsek** | `http://127.0.0.1:8000/kepsek/audit-log` | User `kepsek_asliman` |

---

## 14. KENDALA YANG MASIH TERSISA

**TIDAK ADA KENDALA TEKNIS YANG TERSISA.**  
Seluruh alur bisnis, integrasi multi-peran, pencatatan log aktivitas, proteksi keamanan, dan unit testing telah berhasil diselesaikan dan terverifikasi secara fungsional.

---

## 15. LANGKAH-LANGKAH SEBELUM DEMONSTRASI KEPADA DOSEN

1. **Jalankan XAMPP MySQL**: Pastikan modul MySQL pada XAMPP Control Panel menyala (*Running* pada port 3306).
2. **Jalankan Server Laravel**: Buka terminal dan ketik `php artisan serve`.
3. **Gunakan Dua Peramban Berbeda**:
   - Peramban 1 (misal: Google Chrome biasa) untuk login **Calon Siswa** (`http://127.0.0.1:8000/login`).
   - Peramban 2 (misal: Mode Incognito / Microsoft Edge) untuk login **Petugas TU / Kepala Sekolah** (`http://127.0.0.1:8000/admin/login`). Hal ini memudahkan memperlihatkan integrasi data secara langsung (real-time) tanpa harus logout-login berulang kali.
4. **Demonstrasikan Skenario Alur Lengkap**:
   - *Skenario A*: Siswa mengisi formulir bertahap (draft), lalu mengunggah berkas.
   - *Skenario B*: Petugas TU memeriksa berkas via penampil dokumen interaktif, memverifikasi pelunasan tunai di loket, dan menempatkan siswa ke ruang seleksi.
   - *Skenario C*: Siswa mencetak Kartu Ujian ber-QR Code yang sudah terisi ruang dan jadwal otomatis.
   - *Skenario D*: Kepala Sekolah membuka Ringkasan Keuangan untuk melihat pemasukan uang formulir, lalu membuka Sidang Kelulusan untuk meluluskan siswa dan menerbitkan SKL.
   - *Skenario E*: Siswa langsung melihat status DITERIMA dan mencetak Surat Keterangan Lulus (SKL) resmi.
   - *Skenario F*: Kepala Sekolah memperlihatkan Audit Trail di mana seluruh aksi pendaftaran, verifikasi, dan kelulusan tercatat dengan identitas aktor, IP, dan snapshot perubahannya.

