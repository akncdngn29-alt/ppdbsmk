# LAPORAN IMPLEMENTASI TAHAP 4: MANAJEMEN JADWAL DAN RUANG SELEKSI

**Proyek:** PPDB SMK Islam Bina Khalifah Bangsa, Kota Metro, Lampung  
**Tanggal:** 5 November 2026  
**Framework:** Laravel 13.17 | PHP 8.5.12 | MariaDB / MySQL (XAMPP)  
**Status Implementasi:** Selesai (100% Lulus Pengujian)

---

## 1. Kondisi Awal Sistem

Sebelum implementasi Tahap 4 dimulai:
- Entitas jadwal seleksi, ruang seleksi, dan penempatan peserta belum tersedia pada skema database.
- Tampilan kartu tanda peserta tes seleksi masuk (`resources/views/siswa/cetak_kartu_ujian.blade.php`) masih menggunakan informasi statis / teks *hardcoded* (contoh: "Ruang Laboratorium 1").
- Menu portal calon siswa menampilkan langsung "Kartu Peserta Ujian" tanpa halaman ringkasan jadwal dan alokasi ruang ujian.
- Panel Petugas Tata Usaha (TU) dan Kepala Sekolah belum memiliki menu dan antarmuka untuk mengelola sesi tes, kapasitas ruang, alokasi penempatan otomatis, pemindahan manual, serta pemantauan (*monitoring*) ruang seleksi.

---

## 2. Temuan dari Audit Kode

1. **Belum Ada Relasi Jadwal & Ruang**: Model `Pendaftar` belum terhubung ke entitas jadwal atau ruang seleksi manapun.
2. **Kebutuhan Isolasi Akses Tiga Role**:
   - **Petugas TU (`role: tu`)**: Memerlukan kontrol CRUD penuh atas jadwal, ruang, alokasi otomatis, penempatan manual, dan cetak daftar hadir ruang.
   - **Kepala Sekolah (`role: kepala_sekolah`)**: Memerlukan akses pengawasan (*read-only*) terhadap rekapitulasi agenda jadwal dan tingkat keterisian ruang tanpa hak modifikasi.
   - **Calon Siswa (`role: calon_siswa`)**: Memerlukan portal terintegrasi pada menu "Jadwal dan Kartu Ujian" yang menampilkan jadwal miliknya secara dinamis, kartu ujian resmi, serta banner penjelasan jika penempatan belum ditentukan panitia.
3. **Pencegahan Konflik & Data Anomali**:
   - Diperlukan *unique constraint* di tingkat database (`id_jadwal`, `id_pendaftar`) untuk mencegah penempatan ganda.
   - Diperlukan validasi overlap waktu (*schedule collision*) jika seorang peserta didaftarkan pada lebih dari satu jadwal dengan jam pelaksanaan yang bertabrakan.
   - Diperlukan proteksi penurunan kapasitas ruang agar tidak lebih kecil daripada jumlah peserta yang sudah ditempatkan pada ruang tersebut.

---

## 3. File yang Dibuat dan Diubah

### File Baru

| No | Path File | Keterangan |
|---|---|---|
| 1 | `database/migrations/2026_11_05_200001_create_jadwal_seleksi_table.php` | Migrasi tabel `jadwal_seleksi` |
| 2 | `database/migrations/2026_11_05_200002_create_ruang_seleksi_table.php` | Migrasi tabel `ruang_seleksi` |
| 3 | `database/migrations/2026_11_05_200003_create_penempatan_peserta_table.php` | Migrasi tabel `penempatan_peserta` |
| 4 | `app/Models/JadwalSeleksi.php` | Model Eloquent Jadwal Seleksi |
| 5 | `app/Models/RuangSeleksi.php` | Model Eloquent Ruang Seleksi & Kapasitas |
| 6 | `app/Models/PenempatanPeserta.php` | Model Eloquent Penempatan Peserta |
| 7 | `app/Http/Controllers/JadwalSeleksiController.php` | Controller manajemen jadwal, ruang, alokasi otomatis/manual, dan monitoring kepsek |
| 8 | `resources/views/admin/jadwal/index.blade.php` | View daftar agenda jadwal seleksi & statistik TU |
| 9 | `resources/views/admin/jadwal/form.blade.php` | View form tambah & edit jadwal seleksi |
| 10 | `resources/views/admin/jadwal/ruang.blade.php` | View manajemen ruang seleksi & modal form |
| 11 | `resources/views/admin/jadwal/penempatan.blade.php` | View alokasi otomatis, alokasi manual, dan progress keterisian per ruang |
| 12 | `resources/views/admin/jadwal/cetak_daftar.blade.php` | Format cetak dokumen resmi daftar hadir peserta tes per ruang |
| 13 | `resources/views/kepsek/jadwal_monitoring.blade.php` | View monitoring eksekutif Kepala Sekolah (*read-only*) |
| 14 | `resources/views/siswa/jadwal.blade.php` | View portal calon siswa: "Jadwal dan Kartu Ujian" |
| 15 | `tests/Feature/Tahap4JadwalSeleksiTest.php` | Test suite 20 skenario uji otomatis Tahap 4 |

### File yang Diubah

| No | Path File | Perubahan |
|---|---|---|
| 1 | `routes/web.php` | Mendaftarkan 16 rute baru untuk Petugas TU, Kepala Sekolah, dan Calon Siswa |
| 2 | `app/Models/Pendaftar.php` | Menambahkan relasi `penempatan()`, `penempatanAktif()`, dan `jadwalSeleksi()` |
| 3 | `app/Http/Controllers/SiswaDashboardController.php` | Menambahkan method `jadwalSiswa()` dan mengintegrasikan `$penempatan` pada `cetakKartuUjian()` |
| 4 | `app/Http/Controllers/AdminTUController.php` | Mengintegrasikan data `$penempatan` dinamis pada `cetakKartuUjianPendaftar()` |
| 5 | `resources/views/layouts/admin.blade.php` | Menambahkan navigasi sidebar "Jadwal Seleksi" & "Ruang Seleksi" (TU) serta "Monitoring Jadwal & Ruang" (Kepsek) |
| 6 | `resources/views/layouts/siswa.blade.php` | Menyelaraskan navigasi sidebar menjadi "Jadwal dan Kartu Ujian" mengarah ke `route('siswa.jadwal')` |
| 7 | `resources/views/siswa/cetak_kartu_ujian.blade.php` | Mengubah jadwal dan ruang dari hardcoded menjadi dinamis sesuai alokasi peserta |

---

## 4. Migration dan Perubahan Database

Dijalankan migrasi pada database utama (`ppdb_smk`) dan database pengujian (`ppdb_smk_testing`):

1. **`jadwal_seleksi`**:
   - `id_jadwal` (Primary Key, unsigned bigint)
   - `nama_jadwal` (varchar 150)
   - `jenis_seleksi` (varchar 100)
   - `tanggal` (date)
   - `waktu_mulai` (time)
   - `waktu_selesai` (time)
   - `lokasi` (varchar 200)
   - `gedung` (varchar 100, nullable)
   - `keterangan` (text, nullable)
   - `status` (enum: 'aktif', 'nonaktif', default: 'aktif')
   - `timestamps`

2. **`ruang_seleksi`**:
   - `id_ruang` (Primary Key, unsigned bigint)
   - `kode_ruang` (varchar 20, UNIQUE)
   - `nama_ruang` (varchar 100)
   - `gedung` (varchar 100, nullable)
   - `kapasitas` (unsigned int, default: 30)
   - `keterangan` (text, nullable)
   - `status_aktif` (boolean, default: true)
   - `timestamps`

3. **`penempatan_peserta`**:
   - `id_penempatan` (Primary Key, unsigned bigint)
   - `id_jadwal` (FK ke `jadwal_seleksi.id_jadwal`, cascade on delete)
   - `id_ruang` (FK ke `ruang_seleksi.id_ruang`, cascade on delete)
   - `id_pendaftar` (FK ke `pendaftar.id_pendaftar`, cascade on delete)
   - `catatan` (text, nullable)
   - `timestamps`
   - `UNIQUE KEY uq_jadwal_pendaftar (id_jadwal, id_pendaftar)`

Setiap file migrasi dilengkapi method `down()` yang aman (`Schema::dropIfExists`).

---

## 5. Mekanisme Manajemen Jadwal

1. **Penambahan Jadwal**: Petugas TU menentukan nama agenda, jenis seleksi, tanggal, waktu mulai dan selesai, lokasi kampus, gedung, serta keterangan tata tertib.
2. **Validasi Waktu**: Waktu selesai divalidasi harus setelah waktu mulai (`after:waktu_mulai`).
3. **Pencegahan Hapus Jadwal Berisi Peserta**: Jika masih terdapat penempatan peserta aktif pada suatu jadwal, penghapusan ditolak secara aman dengan instruksi untuk membatalkan penempatan peserta terlebih dahulu.
4. **Pencatatan Audit**: Setiap aksi `TAMBAH_JADWAL`, `UBAH_JADWAL`, dan `HAPUS_JADWAL` otomatis tercatat ke `log_aktivitas`.

---

## 6. Mekanisme Pengelolaan Ruang Seleksi

1. **Validasi Kapasitas**: Kapasitas ruang harus berupa bilangan bulat positif (`min:1, max:500`).
2. **Proteksi Penurunan Kapasitas**: Sistem memeriksa `max(peserta terdaftar per jadwal)` di ruang tersebut. Jika kapasitas baru lebih kecil dari jumlah peserta yang sudah ditempatkan, perubahan ditolak.
3. **Proteksi Hapus Ruang**: Ruang yang masih digunakan dalam penempatan manapun tidak dapat dihapus.
4. **Pencatatan Audit**: Setiap aksi `TAMBAH_RUANG`, `UBAH_RUANG`, dan `HAPUS_RUANG` tercatat ke `log_aktivitas`.

---

## 7. Algoritma Pembagian Peserta Otomatis

Algoritma penempatan otomatis berjalan di dalam `DB::transaction()` dengan tahapan:
1. **Seleksi Peserta**: Petugas TU memilih peserta dari daftar peserta yang memenuhi syarat (*eligible*: `is_locked = true` dan `status_pembayaran = 'Paid'`).
2. **Eksklusi Penempatan Ganda**: Sistem memfilter peserta yang sudah memiliki penempatan di jadwal tersebut.
3. **Urutan Stabil**: Peserta diurutkan secara stabil berdasarkan `no_pendaftaran` (`orderBy('no_pendaftaran')`), tidak diacak secara liar.
4. **Pencegahan Jadwal Bentrok**: Setiap peserta diperiksa terhadap jadwal lain pada tanggal yang sama dengan rentang waktu yang bertabrakan (*overlapping*). Peserta yang bentrok dilewati dan dilaporkan dalam ringkasan.
5. **Alokasi Berimbang Sesuai Kapasitas**:
   - Sistem membaca ruang aktif terurut berdasarkan `kode_ruang`.
   - Menggunakan *pessimistic lock* (`lockForUpdate()`) saat menghitung sisa kapasitas ruang.
   - Menempatkan peserta sampai kapasitas ruang penuh sebelum beralih ke ruang berikutnya.
6. **Laporan Sisa Daya Tampung**: Jika total peserta melebihi daya tampung seluruh ruang aktif, sistem mencatat jumlah peserta yang belum tertampung dan menampilkannya kepada Petugas TU.
7. **Pencatatan Log**: Tercatat aksi `PENEMPATAN_OTOMATIS` dengan ringkasan jumlah yang ditempatkan dan sisa antrean.

---

## 8. Mekanisme Pemindahan Manual

Petugas TU memiliki kendali manual penuh:
1. **Tetapkan Manual**: Menetapkan peserta yang belum memiliki ruang ke ruang tertentu.
2. **Pindah Ruang**: Memindahkan peserta dari satu ruang ke ruang lain pada jadwal yang sama tanpa merusak kuota.
3. **Validasi Pemindahan**:
   - Ruang tujuan harus berstatus aktif.
   - Sisa kapasitas ruang tujuan harus mencukupi (ditolak jika penuh).
   - Memastikan tidak ada jadwal bentrok pada tanggal & jam yang sama.
4. **Batalkan Penempatan**: Menghapus penempatan peserta tertentu sehingga peserta kembali berstatus belum mendapat ruang.
5. **Pencatatan Log**: Tercatat aksi `PENEMPATAN_MANUAL`, `PINDAH_RUANG`, dan `BATAL_PENEMPATAN`.

---

## 9. Integrasi Tiga Panel

1. **Panel Petugas TU (`admin.*`)**:
   - Mengelola master jadwal dan master ruang seleksi.
   - Melakukan penempatan otomatis dan manual.
   - Mencetak dokumen resmi Daftar Hadir Peserta per ruang.
2. **Panel Kepala Sekolah (`kepsek.*`)**:
   - Halaman Monitoring Jadwal & Ruang (`/kepsek/jadwal-seleksi`).
   - Read-only dashboard yang menampilkan total agenda, kapasitas ruang, keterisian per ruang, dan daftar peserta belum tertampung.
   - Dilindungi middleware `admin.role:kepala_sekolah`, tidak memiliki akses manipulasi data jadwal.
3. **Portal Calon Siswa (`siswa.*`)**:
   - Menu terintegrasi: "Jadwal dan Kartu Ujian" (`/siswa/jadwal`).
   - Menampilkan kartu jadwal seleksi miliknya sendiri (tanggal, jam, lokasi, gedung, nama/kode ruang, serta tata tertib panitia).
   - Menampilkan banner edukatif bila penempatan belum ditentukan panitia.
   - Tombol Cetak Kartu Ujian (PDF) yang kini secara otomatis memuat data ruang ujian riil.
   - Read-only, tidak dapat mengubah jadwal atau ruang.

---

## 10. Integrasi Riwayat Aktivitas

Seluruh 10 aksi yang dipersyaratkan tercatat di `log_aktivitas` via `LogAktivitas::log(...)`:
1. `TAMBAH_JADWAL`
2. `UBAH_JADWAL`
3. `HAPUS_JADWAL`
4. `TAMBAH_RUANG`
5. `UBAH_RUANG`
6. `HAPUS_RUANG`
7. `PENEMPATAN_OTOMATIS`
8. `PENEMPATAN_MANUAL`
9. `PINDAH_RUANG`
10. `BATAL_PENEMPATAN`

Data sensitif tetap di-redact sesuai standar keamanan sistem.

---

## 11. Daftar Backup

Seluruh file yang dimodifikasi telah dibackup sebelum perubahan ke direktori `.backups_tahap_4/`:
- `.backups_tahap_4/web.php`
- `.backups_tahap_4/admin.blade.php`
- `.backups_tahap_4/siswa.blade.php`
- `.backups_tahap_4/SiswaDashboardController.php`
- `.backups_tahap_4/AdminTUController.php`
- `.backups_tahap_4/KepalaSekolahController.php`
- `.backups_tahap_4/cetak_kartu_ujian.blade.php`

---

## 12. Hasil Seluruh Pengujian

### Pengujian Khusus Tahap 4 (`Tahap4JadwalSeleksiTest.php`)
```
php artisan test tests/Feature/Tahap4JadwalSeleksiTest.php
```
Hasil:
```
Tests:    20 passed (56 assertions)
Duration: 1.30s
```

Semua 20 skenario wajib berhasil:
1. `test_01_petugas_tu_dapat_membuat_jadwal`: PASS
2. `test_02_petugas_tu_dapat_mengubah_jadwal`: PASS
3. `test_03_petugas_tu_tidak_dapat_membuat_jadwal_dengan_data_tidak_valid`: PASS
4. `test_04_petugas_tu_dapat_membuat_ruang`: PASS
5. `test_05_kapasitas_ruang_harus_valid`: PASS
6. `test_06_sistem_dapat_membagi_peserta_secara_otomatis`: PASS
7. `test_07_pembagian_otomatis_tidak_melebihi_kapasitas`: PASS
8. `test_08_tidak_ada_peserta_yang_mendapat_penempatan_ganda`: PASS
9. `test_09_peserta_yang_melebihi_kapasitas_ruang_tetap_tercatat_belum_ditempatkan`: PASS
10. `test_10_petugas_tu_dapat_memindahkan_peserta_secara_manual`: PASS
11. `test_11_pemindahan_manual_ditolak_jika_ruang_penuh`: PASS
12. `test_12_jadwal_bentrok_peserta_ditolak`: PASS
13. `test_13_calon_siswa_hanya_dapat_melihat_jadwal_miliknya`: PASS
14. `test_14_calon_siswa_tidak_dapat_mengubah_jadwal`: PASS
15. `test_15_kepala_sekolah_hanya_dapat_melihat_data_jadwal`: PASS
16. `test_16_kepala_sekolah_tidak_dapat_mengubah_jadwal`: PASS
17. `test_17_aktivitas_jadwal_dan_penempatan_tercatat_di_log_aktivitas`: PASS
18. `test_18_data_jadwal_dan_penempatan_tidak_hilang_setelah_logout_dan_login`: PASS
19. `test_19_fitur_kartu_ujian_yang_sudah_ada_tetap_berfungsi`: PASS
20. `test_20_fitur_riwayat_aktivitas_tahap2_tetap_berfungsi`: PASS

### Hasil Keseluruhan Test Suite Aplikasi
```
php artisan test
```
Hasil:
```
Tests:    89 passed (482 assertions)
Duration: 8.39s
```
Semua 89 test dari Tahap 1, 2, 3, dan 4 lulus tanpa kegagalan (100% PASS).

---

## 13. Risiko atau Pekerjaan yang Belum Selesai

- **Risiko Nol**: Tidak ada data pendaftar yang terhapus atau berubah secara destruktif. Struktur tabel menggunakan skema baru yang sepenuhnya backward-compatible.
- **Catatan Operasional**: Cetak dokumen daftar hadir dan kartu ujian menggunakan stylesheet `@media print` standar A4 yang dapat langsung diekspor sebagai PDF melalui fitur bawaan browser.
- Sesuai instruksi batasan, implementasi berhenti di Tahap 4 dan menunggu arahan selanjutnya sebelum melangkah ke tahap berikutnya.

