# LAPORAN PELAKSANAAN TAHAP 2: IMPLEMENTASI RIWAYAT AKTIVITAS TIGA ROLE
## PPDB SMK ISLAM BINA KHALIFAH BANGSA (KOTA METRO, LAMPUNG)

**Tanggal Penyelesaian**: 5 November 2026  
**Status**: SELESAI & TERVERIFIKASI 100% PADA TEST SUITE (56 PASSED, 357 ASSERTIONS)

---

## 1. KONDISI AWAL SISTEM

Sebelum pelaksanaan Tahap 2:
1. **Pondasi Basis Data**: Tabel `log_aktivitas` telah ada sejak migrasi awal (`2026_01_01_000013_create_log_aktivitas_table.php`), memuat kolom `id_log`, `id_admin`, `id_user`, `aksi`, `tabel_terkait`, `id_data`, `deskripsi`, `data_lama`, `data_baru`, `ip_address`, `user_agent`, dan timestamps. Namun belum ada kolom eksplisit `role` (peran aktor) dan `status` (indikator hasil aksi: SUCCESS/FAILED).
2. **Cakupan Pencatatan**: Telah ada pemanggilan `LogAktivitas::log(...)` sporadis di beberapa controller (misal login dasar, unlock formulir, ekspor xlsx), namun belum mencatat percobaan login gagal, perubahan kata sandi terpisah, unduh dokumen resmi oleh calon siswa, dan aktivitas pengawasan Kepala Sekolah.
3. **Ketersediaan Antarmuka (UI)**:
   - **Calon Siswa**: Belum memiliki halaman atau menu untuk memantau riwayat peristiwa akunnya sendiri.
   - **Petugas TU**: Belum memiliki halaman rekapitulasi riwayat aktivitas (baik operasional TU maupun pemantauan seluruh calon siswa).
   - **Kepala Sekolah**: Telah memiliki tampilan awal `kepsek.audit_log`, tetapi masih bersifat statis tabel biasa tanpa pencarian fleksibel, tanpa filter role/status/tanggal, dan tanpa kartu ringkasan metrik.
4. **Test Suite Proyek**: Terdapat 44 tes otomatis bawaan yang lulus (`tests=44, passed=44`).

---

## 2. STRUKTUR TABEL DAN MEKANISME PENCATATAN

### A. Struktur Tabel `log_aktivitas`
Struktur tabel dioptimalkan tanpa menghapus data historis yang sudah ada:

| Kolom | Tipe Data | Keterangan |
| :--- | :--- | :--- |
| `id_log` | BIGINT UNSIGNED (PK) | Auto-increment primary key |
| `id_admin` | BIGINT UNSIGNED (FK) | Nullable, berelasi ke tabel `admin` (`id_admin`) |
| `id_user` | BIGINT UNSIGNED (FK) | Nullable, berelasi ke tabel `user` (`id_user`) |
| `role` | VARCHAR(50) (Index) | Peran aktor: `calon_siswa`, `tu`, `kepala_sekolah`, `sistem` |
| `aksi` | VARCHAR(50) | Kode peristiwa (misal: `LOGIN`, `LOGIN_GAGAL`, `UBAH_PASSWORD`, dll) |
| `tabel_terkait` | VARCHAR(50) | Entitas objek yang diubah (`pendaftar`, `user`, `berkas_pendaftar`, dll) |
| `id_data` | BIGINT UNSIGNED | ID record entitas terkait |
| `deskripsi` | VARCHAR(255) | Rincian aktivitas dalam bahasa manusia |
| `status` | VARCHAR(50) (Index) | Status hasil: `SUCCESS` atau `FAILED` |
| `data_lama` | LONGTEXT / JSON | Snapshot data sebelum perubahan (tersanitasi) |
| `data_baru` | LONGTEXT / JSON | Snapshot data sesudah perubahan (tersanitasi) |
| `ip_address` | VARCHAR(45) | IP Address pengakses |
| `user_agent` | TEXT | Informasi peramban / perangkat klien |
| `created_at` | TIMESTAMP | Waktu pencatatan aktivitas |
| `updated_at` | TIMESTAMP | Waktu pembaruan record |

### B. Mekanisme Pencatatan Terpusat
Pencatatan dipusatkan melalui metode statis `LogAktivitas::log(...)` dengan keunggulan arsitektural:
1. **Deteksi Otomatis & Fleksibel**: Otomatis mendeteksi guard aktif (`auth('admin')` atau `auth('web')`), namun mendukung override jika ada event latar belakang (background/cron).
2. **Auto-Resolusi Relasi Siswa**: Jika Petugas TU atau Kepala Sekolah melakukan tindakan administratif terhadap entitas pendaftar/berkas/pembayaran/kelulusan, metode secara otomatis menelusuri `id_user` siswa target sehingga riwayat tersebut tetap tertaut ke profil siswa yang bersangkutan.
3. **Pembersihan Data Sensitif (Auto-Sanitasi)**: Payload `data_lama` dan `data_baru` diperiksa secara rekursif; seluruh key sensitif seperti `password`, `password_confirmation`, `remember_token`, `token`, `secret`, dan `snap_token` otomatis diganti menjadi `[REDACTED]`.

---

## 3. FILE YANG DIBUAT ATAU DIUBAH

### A. File Baru (Created)
1. `database/migrations/2026_11_05_170000_add_role_and_status_to_log_aktivitas_table.php`  
   Migration aman untuk menambahkan kolom `role` dan `status` lengkap dengan backfill data lama.
2. `resources/views/siswa/riwayat_aktivitas.blade.php`  
   Halaman riwayat aktivitas Calon Siswa (desain modern, kartu statistik, pencarian, filter jenis/status/tanggal, dan modal rincian diff).
3. `resources/views/admin/riwayat_aktivitas.blade.php`  
   Halaman riwayat aktivitas Petugas TU dengan tabulasi pemisahan (Semua, Aktivitas Calon Siswa, Aktivitas Operasional TU), pencarian nomor registrasi/nama/deskripsi, filter status/tanggal, serta modal diff JSON.
4. `tests/Feature/RiwayatAktivitasTest.php`  
   Test suite komprehensif memvalidasi seluruh skenario pengujian 3 role (12 unit tes fitur).
5. `TAHAP_2_RIWAYAT_AKTIVITAS_REPORT.md`  
   Laporan resmi hasil implementasi Tahap 2.

### B. File Diperbarui (Modified)
1. `app/Models/LogAktivitas.php`:  
   Menambahkan atribut `role` dan `status` ke `$fillable`, accessor `nama_aktor` dan `role_label`, logika relasi, serta filter sanitasi payload otomatis.
2. `app/Http/Controllers/AuthController.php`:  
   Mencatat `LOGIN` berhasil, `LOGOUT`, `DAFTAR_AKUN`, serta mencatat `LOGIN_GAGAL` secara aman tanpa merekam kredensial kata sandi yang diketik.
3. `app/Http/Controllers/SiswaDashboardController.php`:  
   Mencatat `UBAH_PASSWORD`, `UBAH_PROFIL`, `UNGGAH_BERKAS`, `PENGGANTIAN_BERKAS`, `HAPUS_BERKAS`, serta pengunduhan dokumen resmi (`UNDUH_BUKTI_DAFTAR`, `UNDUH_KARTU_UJIAN`, `UNDUH_KWITANSI`, `UNDUH_SKL`), dan method `riwayatAktivitas()`.
4. `app/Http/Controllers/AdminTUController.php`:  
   Mencatat cetak dokumen siswa oleh staf (`CETAK_DOKUMEN_SISWA`), menyediakan method `riwayatAktivitas()` dengan pemilahan tab Calon Siswa vs Operasional TU.
5. `app/Http/Controllers/KepalaSekolahController.php`:  
   Mencatat aktivitas pengawasan eksekutif (`PENGAWASAN`), memperkaya method `auditLog()` dengan filter multi-kriteria (q, role, aksi, status, rentang tanggal) dan kartu statistik eksekutif.
6. `routes/web.php`:  
   Mendaftarkan rute `siswa.riwayat_aktivitas`, `siswa.berkas.hapus`, `admin.riwayat_aktivitas`, dan `kepsek.riwayat_aktivitas`.
7. `resources/views/layouts/siswa.blade.php`:  
   Menambahkan navigasi menu "Riwayat Aktivitas" dengan ikon Lucide `history`.
8. `resources/views/layouts/admin.blade.php`:  
   Menambahkan navigasi menu "Riwayat Aktivitas" pada sidebar Petugas TU.
9. `resources/views/kepsek/audit_log.blade.php`:  
   Memperbarui antarmuka dengan form filter pencarian, filter role/status/tanggal, kartu statistik 5 metrik, dan tabel responsif.

---

## 4. FITUR YANG BERHASIL DIIMPLEMENTASIKAN

### A. Fitur Calon Siswa
- **Isolasi Penuh**: Siswa hanya dapat melihat log aktivitas akunnya sendiri.
- **Peristiwa yang Dicatat**:
  1. Login berhasil & Logout.
  2. Percobaan login gagal pada akun siswa tersebut (status `FAILED`).
  3. Pembaruan kata sandi akun (dicatat tanpa kata sandi).
  4. Perubahan profil / nomor handphone.
  5. Pengisian dan penyimpanan formulir pendaftaran.
  6. Finalisasi / penguncian formulir.
  7. Unggah berkas baru, penggantian berkas yang sudah ada, serta penghapusan berkas.
  8. Pembayaran tunai loket TU maupun pembuatan tagihan online Midtrans.
  9. Pengunduhan formulir, kartu ujian, kwitansi lunas, dan SKL kelulusan.
  10. Perubahan status dari pihak sekolah yang berdampak langsung pada siswa (verifikasi berkas, pembukaan kunci formulir, konfirmasi pembayaran, pengesahan kelulusan).
- **Fitur UI**: Pencarian teks, filter jenis aksi, filter status (Sukses/Gagal), filter tanggal, pagination (15 item/halaman), dan modal detail perubahan data.

### B. Fitur Petugas TU
- **Akses Pengawasan Siswa**: Petugas TU dapat melihat riwayat aktivitas seluruh pendaftar, mencari berdasarkan nomor registrasi (`PPDB-2026-xxx`), nama siswa, NISN, atau jenis aksi.
- **Akses Operasional TU**: Memantau aktivitas administratif staf TU, meliputi:
  1. Login dan logout staf TU.
  2. Verifikasi berkas (valid/tidak valid) dan catatan revisi.
  3. Pembukaan kunci formulir (*unlock form*).
  4. Verifikasi lunas pembayaran tunai kasir.
  5. Input penilaian seleksi wawancara dan buta warna.
  6. Pengalihan jurusan limpahan kuota.
  7. Pembaruan master data (jurusan, gelombang, voucher diskon).
  8. Pembaruan banner pengumuman dan pengaturan profil sekolah.
  9. Ekspor laporan CSV, XLSX, Dapodik, dan cetak Rekap PDF resmi.
  10. Pencetakan formulir, kartu ujian, dan SKL milik pendaftar.
- **Fitur UI**: Tabulasi khusus (`Semua`, `Aktivitas Calon Siswa`, `Aktivitas Petugas TU`), pencarian komprehensif, filter status, filter rentang tanggal, pagination (20 item/halaman), dan modal inspeksi diff JSON.

### C. Fitur Kepala Sekolah
- **Akses Pengawasan Eksekutif Menyeluruh**: Kepala Sekolah memiliki hak akses pengawasan penuh atas seluruh aktivitas dari ketiga role (Calon Siswa, Petugas TU, dan pengawasan Kepala Sekolah).
- **Aktivitas Kepala Sekolah yang Dicatat**:
  1. Login dan logout Kepala Sekolah.
  2. Pemantauan analitik dashboard PPDB.
  3. Peninjauan laporan rekapitulasi data pendaftar.
  4. Pengesahan keputusan kelulusan dan penerbitan SKL resmi.
- **Fitur UI**: Filter multi-dimensi (aktor/role, jenis aksi, status, rentang tanggal), pencarian kata kunci, kartu statistik 5 metrik, pagination (25 item/halaman), dan modal inspeksi diff JSON.

---

## 5. PEMBAGIAN HAK AKSES KETIGA ROLE

| Ruang Lingkup Data | Calon Siswa | Petugas TU | Kepala Sekolah |
| :--- | :---: | :---: | :---: |
| Riwayat Akun Siswa Milik Sendiri | **Ya** (Read-Only) | **Ya** (Semua Siswa) | **Ya** (Semua Siswa) |
| Riwayat Akun Siswa Lain | **Ditolak (403/Forbidden)** | **Ya** (Semua Siswa) | **Ya** (Semua Siswa) |
| Log Operasional Harian TU | **Ditolak (Terisolasi)** | **Ya** (Read-Only) | **Ya** (Read-Only) |
| Log Pengawasan Internal Kepala Sekolah | **Ditolak (Terisolasi)** | **Ditolak (Terisolasi)** | **Ya** (Read-Only) |
| Panel URL Akses | `/siswa/riwayat-aktivitas` | `/admin/riwayat-aktivitas` | `/kepsek/audit-log` |
| Middleware Penjaga | `auth:web` | `admin.role:tu` | `admin.role:kepala_sekolah` |

---

## 6. PERUBAHAN DATABASE

Perubahan dilakukan melalui migrasi Laravel resmi:
- **Nama Migration**: `database/migrations/2026_11_05_170000_add_role_and_status_to_log_aktivitas_table.php`
- **Operasi**:
  1. Menambahkan kolom `role` VARCHAR(50) NULL (Indexed).
  2. Menambahkan kolom `status` VARCHAR(50) DEFAULT 'SUCCESS' (Indexed).
  3. Mengisi (backfill) nilai `status = 'SUCCESS'` untuk seluruh 24 record data lama.
  4. Mengisi (backfill) nilai `role` untuk data lama:
     - Record yang memiliki `id_admin` dihubungkan ke tabel `admin` untuk menyalin role aslinya (`tu` / `kepala_sekolah`).
     - Record yang memiliki `id_user` dan tanpa `id_admin` diset ke `calon_siswa`.
     - Record sistem otomatis diset ke `sistem`.
- **Integritas Data**: Dijalankan pada database operasional (`ppdb_smk`) dan database pengujian (`ppdb_smk_testing`). Tidak ada tabel yang di-drop dan tidak ada baris data yang hilang.

---

## 7. DAFTAR BACKUP FILE

Seluruh file yang disentuh telah dibackup terlebih dahulu ke direktori `.backups_tahap_2/`:

```
.backups_tahap_2/
├── LogAktivitas.php             (Backup model)
├── AuthController.php             (Backup auth controller)
├── SiswaDashboardController.php   (Backup siswa dashboard controller)
├── AdminTUController.php          (Backup admin TU controller)
├── KepalaSekolahController.php    (Backup kepsek controller)
├── web.php                        (Backup route web)
├── siswa.blade.php                (Backup layout siswa)
├── admin.blade.php                (Backup layout admin)
└── audit_log.blade.php            (Backup view audit kepsek awal)
```

---

## 8. HASIL PENGUJIAN OTOMATIS

### A. Pengujian Baru Tahap 2 (`tests/Feature/RiwayatAktivitasTest.php`)
Dibuat 12 skenario pengujian menyeluruh:
1. `test_01_siswa_hanya_dapat_melihat_riwayat_milik_sendiri_dan_tidak_melihat_siswa_lain` &rarr; **PASSED**
2. `test_02_siswa_tidak_dapat_melihat_log_internal_petugas` &rarr; **PASSED**
3. `test_03_admin_tu_dapat_melihat_aktivitas_siswa_dan_operasional_tu` &rarr; **PASSED**
4. `test_04_kepala_sekolah_dapat_melihat_seluruh_role` &rarr; **PASSED**
5. `test_05_login_dan_logout_tercatat_dengan_benar` &rarr; **PASSED**
6. `test_06_login_gagal_tercatat_tanpa_kebocoran_password` &rarr; **PASSED**
7. `test_07_perubahan_kata_sandi_tercatat_tanpa_menyimpan_kata_sandi` &rarr; **PASSED**
8. `test_08_verifikasi_dokumen_tercatat` &rarr; **PASSED**
9. `test_09_aktivitas_pembukaan_kunci_formulir_tercatat` &rarr; **PASSED**
10. `test_10_aktivitas_ekspor_laporan_tercatat` &rarr; **PASSED**
11. `test_11_proteksi_lintas_role_unauthorized` &rarr; **PASSED**
12. `test_12_unduh_dokumen_resmi_siswa_tercatat` &rarr; **PASSED**

### B. Hasil Keseluruhan Test Suite Proyek (`php artisan test`)
```
Total Tests : 56 Tests
Passed      : 56 Passed (100%)
Failed      : 0 Failed
Errors      : 0 Errors
Assertions  : 357 Assertions
Duration    : ~6.1 detik
Status      : ALL GREEN (SUCCESS)
```

---

## 9. TEMUAN YANG BELUM TERSELESAIKAN

- **Nihil**: Seluruh butir instruksi pada dokumen `tugas.md` (bagian A hingga J) telah terpenuhi tanpa kendala teknis atau inkonsistensi data.

---

## 10. RISIKO DAN CATATAN KEAMANAN

1. **Kepatuhan Privasi Data (UU Perlindungan Data Pribadi / PDP)**:
   - Sistem **TIDAK PERNAH** mencatat plaintext maupun hash kata sandi lama/baru ke dalam kolom deskripsi maupun JSON `data_lama` / `data_baru`.
   - Token otentikasi sesi, token remember me, dan token rahasia pembayaran Midtrans (`snap_token`) secara otomatis di-redact oleh sanitizer `LogAktivitas::sanitizePayload`.
2. **Immutability (Sifat Anti-Ubah Log Audit)**:
   - Rute audit trail tidak menyediakan aksi `UPDATE` atau `DELETE` pada antarmuka web. Log riwayat hanya dapat dibaca (*read-only*) oleh pengguna berwenang.
3. **Pencegahan Akses Lintas Guard**:
   - Guard `web` (Siswa) dan guard `admin` (Staf TU & Kepala Sekolah) terpisah dengan ketat melalui middleware `admin.role`. Upaya penetrasi lintas guard langsung dialihkan atau menghasilkan `403 Forbidden`.
4. **Pencegahan Duplikasi Log**:
   - Pencatatan log ditempatkan secara terpusat pada event pengendali backend utama (bukan di sisi JavaScript klien), sehingga mencegah pencatatan berulang yang dipicu oleh refresh ganda browser klien.

---

*Laporan ini disusun secara otomatis berdasarkan implementasi kode dan bukti eksekusi pengujian unit/fitur resmi.*

