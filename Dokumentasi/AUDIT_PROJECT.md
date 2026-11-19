# LAPORAN AUDIT MENYELURUH PROYEK PPDB SMK ISLAM BINA KHALIFAH BANGSA
**Tanggal Audit:** 04 November 2026  
**Auditor:** Antigravity AI Code Auditor  
**Status Proyek:** Berjalan (Audit & Verification Phase - No destructive changes executed)  

---

## DAFTAR ISI
1. [Ringkasan Eksekutif](#1-ringkasan-eksekutif)
2. [Analisis Dokumen Referensi 1: Proyek Skripsi (2022)](#2-analisis-dokumen-referensi-1-proyek-skripsi-2022)
3. [Analisis Dokumen Referensi 2: SPEC_PPDB.md (Spesifikasi Modern)](#3-analisis-dokumen-referensi-2-spec_ppdbmd-spesifikasi-modern)
4. [Analisis Komparatif: Skripsi vs SPEC_PPDB.md](#4-analisis-komparatif-skripsi-vs-spec_ppdbmd)
5. [Audit Lingkungan Teknologi & Prasyarat](#5-audit-lingkungan-teknologi--prasyarat)
6. [Audit Arsitektur & Struktur Proyek Laravel](#6-audit-arsitektur--struktur-proyek-laravel)
7. [Audit Basis Data & Normalisasi 3NF](#7-audit-basis-data--normalisasi-3nf)
8. [Audit Fitur Bisnis PPDB (18 Modul)](#8-audit-fitur-bisnis-ppdb-18-modul)
9. [Hasil Pengujian Sistem (Automated Tests & Safe Commands)](#9-hasil-pengujian-sistem-automated-tests--safe-commands)
10. [Audit Keamanan Sistem & Kepatuhan Privasi (UU PDP)](#10-audit-keamanan-sistem--kepatuhan-privasi-uu-pdp)
11. [Katalog Temuan Audit Berdasarkan Kategori](#11-katalog-temuan-audit-berdasarkan-kategori)
12. [Matriks Perbandingan Fitur: SPEC vs Implementasi Nyata](#12-matriks-perbandingan-fitur-spec-vs-implementasi-nyata)
13. [Rekomendasi Rencana Perbaikan (Action Plan) Menunggu Persetujuan](#13-rekomendasi-rencana-perbaikan-action-plan-menunggu-persetujuan)

---

## 1. RINGKASAN EKSEKUTIF

Audit ini dilakukan secara menyeluruh terhadap implementasi kode proyek Laravel PPDB SMK Islam Bina Khalifah Bangsa dengan membandingkan sistem terhadap dua dokumen rujukan utama:
1. **Dokumen Skripsi Asli (2022)**: *"Rancang Bangun Sistem Informasi Penerimaan Peserta Didik Baru Berbasis Web Pada SMK IB Khalifah Bangsa"* oleh Anggun Puspita Dewi (Universitas Muhammadiyah Metro).
2. **Dokumen Spesifikasi Modern (`SPEC_PPDB.md`)**: Arsitektur sistem informasi PPDB modern berbasis Laravel dengan standar normalisasi basis data 1NF, 2NF, hingga 3NF, multi-guard RBAC, integrasi pembayaran, verifikasi digital, dan panel eksekutif Kepala Sekolah.

### Status Temuan Utama:
- **Fondasi Framework**: Proyek telah dibangun di atas **Laravel 13.39.0** dan **PHP 8.3.16** dengan koneksi **MySQL** (`ppdb_smk`).
- **Skema Basis Data**: Telah mengadopsi 14 tabel migration dengan konvensi Primary Key eksplisit `id_<tabel>` dan relasi Foreign Key terindeks (3NF).
- **Pengujian Otomasi**: Pengujian fitur HTTP (`tests/Feature/PPDBRouteTest.php`) menghasilkan status **100% Passed (8 tests, 45 assertions)** untuk seluruh route publik, calon siswa, admin TU, dan kepala sekolah.
- **Kritikal / Bug yang Ditemukan**:
  1. *Runtime Error pada Pengaturan Akun Siswa*: Controller `SiswaDashboardController` memanggil `Hash::make()` tanpa mengimpor `use Illuminate\Support\Facades\Hash;`.
  2. *SQL Error pada Simulasi Voucher*: Kode memanggil kolom `kuota_terpakai` pada model/tabel `voucher_pendaftaran`, padahal kolom tersebut belum ada di skema migrasi.
  3. *Blokir Rute Kepala Sekolah saat Maintenance*: Middleware `CheckMaintenanceMiddleware` mengecualikan route `admin*`, `login`, dan `up`, namun lupa mengecualikan route `kepsek*`.
  4. *Konflik Versi PHP Lingkungan Windows*: Di system `PATH`, `C:\xampp\php` (PHP 8.0.30) mendahului `C:\php83` (PHP 8.3.16), sehingga pemanggilan CLI global `php` tanpa path absolut memicu error inkompatibilitas Laravel 13.
  5. *Storage Link*: Direktori `public/storage` belum di-link (`artisan storage:link`), dan berkas pendaftar disimpan di disk `public` alih-alih `private storage` berizin sesuai klausul privasi UU PDP pada `SPEC_PPDB.md`.

---

## 2. ANALISIS DOKUMEN REFERENSI 1: PROYEK SKRIPSI (2022)

Dokumen `projek skripsi.pdf` (37 halaman lengkap) telah dibaca dan ditelaah dengan rincian data faktual berikut:

- **Judul Penelitian**: Rancang Bangun Sistem Informasi Penerimaan Peserta Didik Baru Berbasis Web Pada SMK IB Khalifah Bangsa.
- **Peneliti**: Anggun Puspita Dewi (NPM: 18430006), Program Studi Ilmu Komputer, Fakultas Ilmu Komputer, Universitas Muhammadiyah Metro, Tahun 2022.
- **Dosen Pembimbing**: 
  1. Pembimbing I: Sudarmaji, S.Kom., M.MKom.
  2. Pembimbing II: Guna Yanti K.S Siregar, S.Kom., M.T.I.
- **Pihak Sekolah yang Terlibat**:
  1. Asliman Puja Kesuma, S.Pd (Kepala Sekolah SMK Islam Bina Khalifah Bangsa).
  2. Rendy Ardiansyah, S.Pd (Guru / Panitia PPDB).
- **Latar Belakang Penelitian**:
  - Pendaftaran siswa baru masih manual: calon siswa dan orang tua harus datang langsung ke sekolah untuk mencari informasi persyaratan dan mengambil formulir fisik.
  - Calon siswa harus kembali lagi ke sekolah untuk menyerahkan formulir yang telah diisi beserta berkas fisik. Hal ini memakan waktu dan biaya, terutama bagi calon siswa yang bertempat tinggal jauh (Lampung Timur, Metro Utara, Lampung Tengah).
  - Proses registrasi dan pemberkasan terhambat karena tumpukan berkas fisik ditangani secara manual oleh panitia TU.
  - Rekapitulasi laporan hasil pendaftaran membutuhkan waktu lama karena harus disortir manual per jurusan.
- **Rumusan Masalah**: "Bagaimana rancang bangun sistem informasi penerimaan peserta didik baru berbasis web pada SMK Islam Bina Khalifah Bangsa?"
- **Batasan Masalah**:
  1. Website dibangun menggunakan bahasa pemrograman PHP framework Laravel, database dan webserver XAMPP (MySQL & Apache), teks editor Sublime Text, dan pengujian menggunakan metode *Black Box Testing*.
  2. Ruang lingkup sistem meliputi: penerimaan informasi, registrasi akun, pengisian formulir biodata, dan pelaporan hasil penerimaan siswa baru.
  3. Menggunakan pendekatan terstruktur dengan metode pengembangan perangkat lunak SDLC (*Software Development Life Cycle*).
- **Tujuan Penelitian**: Merancang dan membangun sistem informasi penerimaan peserta didik baru berbasis web pada SMK Islam Bina Khalifah Bangsa guna mempercepat pendaftaran dan otomatisasi pelaporan.
- **Profil Lembaga dalam Skripsi**:
  - Berdiri tahun 2013, Izin Operasional Disdikbudpora Kota Metro No: `115/KPTS/D.3/03/2013` tanggal 30 Juli 2013, NPSN: `69773555`.
  - Memiliki 4 Kompetensi Keahlian aktif:
    1. Farmasi Klinis dan Komunitas
    2. Teknik Komputer dan Jaringan (TKJ)
    3. Teknik Kendaraan Ringan (TKR)
    4. Teknik dan Bisnis Sepeda Motor (TBSM)
  - 7 Ekstrakurikuler aktif (hasil wawancara hal. 36): Pramuka, Paskib, Tilawati Qur'an, Tafsir dan Tahfiz Qur'an, Karate, Gamelan Karawitan, Gamelan Tolak Balak.
  - Rata-rata pendaftar: ±150 siswa per tahun. Jurusan paling diminati: TKJ dan Farmasi.
  - Alamat website rujukan: `https://smkib.com`.
- **Metode Penelitian**: Kualitatif dengan teknik pengumpulan data: Observasi lapangan, Wawancara terstruktur, Dokumentasi arsip sekolah, serta Studi Pustaka.
- **Metode Pengembangan Sistem**: SDLC (*Software Development Life Cycle*) waterfall klasik: Analisis Kebutuhan &rarr; Desain &rarr; Implementasi (Pengkodean) &rarr; Testing (Pengujian).
- **Analisis Sistem yang Berjalan (Manual)**:
  Calon Siswa datang ke sekolah &rarr; Ambil formulir &rarr; Isi data di rumah &rarr; Bawa kembali berkas ke TU &rarr; TU memverifikasi fisik &rarr; TU merekap ke buku besar &rarr; TU membuat laporan manual &rarr; Diserahkan ke Kepala Sekolah untuk tanda tangan basah.
- **Sistem yang Diusulkan**:
  Calon Siswa membuat akun secara daring &rarr; Login &rarr; Input data formulir lengkap &rarr; Siswa mencetak bukti formulir pendaftaran mandiri &rarr; Petugas TU mencetak laporan pendaftar keseluruhan & laporan per jurusan &rarr; Laporan diserahkan ke Kepala Sekolah untuk ditandatangani.
- **Rancangan Basis Data Skripsi (5 Tabel Non-Normal)**:
  1. `user` (id, nama, nisn, username, email, password)
  2. `pendaftar` (id, id_user, nisn, nama, tempat_lahir, jenis_kelamin, tanggal_lahir, agama, jurusan, anak_ke, jml_saudara, hp_siswa, almt_siswa, berat_badan, tinggi_badan, golongan_darah, asal_sekolah, alamat_sekolah, nama_ayah, nama_ibu, alamt_ortu, hp_ortu, kerja_ayah, kerja_ibu, penghasilan_ortu, tanggungan_anak, tgl_daftar)
  3. `terima` (id, id_user, nama_lengkap, jenis_kelamin, jurusan, nisn, sekolah_asal)
  4. `admin` (id, nama_admin, username, email, password)
  5. `jurusan` (id, nama_jurusan)
- **Kelemahan Kritis Sistem dalam Skripsi (Pengakuan Peneliti di Bab IV Hal. 60)**:
  > *"Rancang bangun sistem informasi penerimaan peserta didik baru berbasis web pada SMK Islam Bina Khalifah Bangsa yang sudah penulis rancang masih terdapat kekurangan dalam program, yaitu **belum terdapat menu seleksi**. Disarankan untuk peneliti selanjutnya dapat menambahkan menu seleksi pada website ini."*
  Selain itu, dalam skripsi belum terdapat modul upload berkas digital, modul pembayaran, kuota/gelombang, jalur pendaftaran, dan modul daftar ulang.

---

## 3. ANALISIS DOKUMEN REFERENSI 2: SPEC_PPDB.MD (SPESIFIKASI MODERN)

Dokumen `SPEC_PPDB.md` merancang penyempurnaan menyeluruh terhadap kekurangan sistem skripsi untuk mentransformasikan prototipe akademik menjadi sistem produksi enterprise:

- **Standar Basis Data**: 1NF, 2NF, hingga 3NF dengan Primary Key eksplisit `id_<tabel>` dan Foreign Key berelasi ketat.
- **14 Tabel Entitas 3NF**:
  1. `jurusan` (`id_jurusan`, `nama_jurusan`, `kode_jurusan`, `kuota`, `deskripsi`)
  2. `gelombang` (`id_gelombang`, `nama_gelombang`, `tahun_ajaran`, `tgl_buka`, `tgl_tutup`, `biaya_pendaftaran`, `status_aktif`)
  3. `voucher_pendaftaran` (`id_voucher`, `kode_voucher`, `tipe`, `nilai_potongan`, `kuota_voucher`, `status_aktif`)
  4. `user` (`id_user`, `nama`, `nisn`, `username`, `email`, `password`)
  5. `admin` (`id_admin`, `nama_admin`, `username`, `email`, `password`, `role`: 'tu', 'kepala_sekolah')
  6. `pendaftar` (`id_pendaftar`, `id_user`, `id_gelombang`, `id_jurusan_1`, `id_jurusan_2`, `no_pendaftaran`, `nisn`, `nama`, biodata, orang tua, ukuran perlengkapan, status terpadu, `is_locked`)
  7. `berkas_pendaftar` (`id_berkas`, `id_pendaftar`, `jenis_berkas`, `path_file`, `status_berkas`, `catatan`)
  8. `pembayaran` (`id_pembayaran`, `id_pendaftar`, `id_gelombang`, `kode_transaksi`, `snap_token`, `nominal`, `potongan`, `total_bayar`, `tipe_bayar`, `status_transaksi`)
  9. `prestasi_pendaftar` (`id_prestasi`, `id_pendaftar`, `nama_prestasi`, `tingkat`, `kategori`, `peringkat`, `tahun`, `penyelenggara`, `berkas_sertifikat`, `skor_bobot`, `status_verifikasi`)
  10. `nilai_seleksi` (`id_nilai`, `id_pendaftar`, `nilai_rapor`, `nilai_wawancara`, `tes_buta_warna`, `total_nilai`, `rekomendasi_jurusan`)
  11. `terima` (`id_terima`, `id_pendaftar`, `id_jurusan`, `id_admin_pengesah`, `no_surat_kelulusan`, `tgl_diterima`, `keterangan`)
  12. `pengumuman` (`id_pengumuman`, `id_admin`, `judul`, `isi_pengumuman`, `tipe`, `tampil_di_banner`, `status_aktif`, `tgl_mulai`, `tgl_selesai`)
  13. `log_aktivitas` (`id_log`, `id_admin`, `id_user`, `aksi`, `tabel_terkait`, `id_data`, `deskripsi`, `data_lama`, `data_baru`, `ip_address`, `user_agent`)
  14. `pengaturan_sekolah` (`id_pengaturan`, profil sekolah, kontak, branding, kunci gateway, sakelar maintenance)
- **Role Pengguna (3 Aktor Utama)**:
  1. *Calon Siswa* (`web` guard): Registrasi, stepper progres 6 tahap, pengisian form 3NF, kunci formulir, upload berkas, pembayaran ganda (Midtrans/Loket), cetak kartu ujian, kwitansi, SKL, dan daftar ulang.
  2. *Petugas Tata Usaha / Admin TU* (`admin` guard, role `tu`): Operasional verifikasi berkas in-browser, input nilai tes & buta warna, pengalihan limpahan kuota (Pilihan 1 ke 2), loket kasir tunai, master data gelombang/jurusan/voucher, CMS pengumuman & maintenance, ekspor data multi-format.
  3. *Kepala Sekolah* (`admin` guard, role `kepala_sekolah`): Panel eksekutif statistik Chart.js, market intelligence asal sekolah, pengesahan kelulusan resmi (terbit nomor SKL), pengesahan tanda tangan rekapitulasi, pemantauan audit trail.
- **Fitur Lapangan Khas SMK**:
  - Pendataan ukuran seragam dan wearpack praktek.
  - Validasi tes buta warna (syarat Farmasi & Otomotif).
  - Limpahan kuota otomatis antara Jurusan Pilihan 1 dan Pilihan 2.
  - Cek kelulusan mandiri tanpa login di landing page.
  - Mode Gelap (Dark Mode) persisten dengan Tailwind CSS.

---

## 4. ANALISIS KOMPARATIF: SKRIPSI VS SPEC_PPDB.MD

| No | Dimensi Perbandingan | Dokumen Skripsi (2022) | Dokumen SPEC_PPDB.md | Status & Rekomendasi Auditor |
|---|---|---|---|---|
| 1 | **Tingkat Normalisasi Basis Data** | Non-Normal (Banyak atribut transitif & redundan di tabel `pendaftar` dan `terima`). | Standar 3NF (14 tabel terpisah, eliminasi anomali pembaruan & hapus). | **Sesuai & Direkomendasikan**: Implementasi menggunakan skema 3NF SPEC_PPDB.md demi integritas data jangka panjang. |
| 2 | **Pilihan Program Keahlian** | Hanya 1 pilihan jurusan disimpan sebagai teks bebas (`varchar`). | 2 Pilihan Jurusan (`id_jurusan_1` dan `id_jurusan_2`) dengan foreign key ke master `jurusan`. | **Sesuai & Ditingkatkan**: Mengakomodasi kebutuhan nyata SMK di mana siswa memiliki pilihan cadangan jika kuota jurusan utama penuh. |
| 3 | **Modul Seleksi Peserta** | **Tidak ada** (Diakui sebagai kekurangan program di Bab IV Hal. 60). | **Ada**: Tabel `nilai_seleksi`, tes buta warna, nilai rapor & wawancara, skor prestasi. | **Fitur Baru Penting**: Mewujudkan saran langsung dari Bab V Skripsi. |
| 4 | **Pemberkasan Calon Siswa** | Berkas fisik diserahkan manual di loket sekolah. | Digital upload (Ijazah, KK, Akta, Pasfoto, Piagam Prestasi) dengan status verifikasi per item. | **Fitur Baru Penting**: Menjawab masalah latar belakang skripsi tentang antrean fisik. |
| 5 | **Pembayaran Formulir** | Belum diatur dalam sistem. | Pembayaran Ganda: Payment Gateway Midtrans (QRIS/VA) + Kasir Tunai di loket TU sekolah + Voucher beasiswa. | **Fitur Baru Penting**: Sesuai realitas lapangan Kota Metro. |
| 6 | **Aktor Kepala Sekolah** | Hanya aktor pasif luar sistem (menerima lembar cetak laporan fisik untuk ditandatangani basah). | Aktor aktif dalam sistem (memiliki kredensial login, role `kepala_sekolah`, panel eksekutif, pengesahan kelulusan digital). | **Kebutuhan Diperluas**: Tetap menyediakan format cetak fisik untuk ditandatangani seperti di skripsi, sekaligus menyediakan akses digital. |
| 7 | **Manajemen Gelombang** | Bersifat manual/statis (Januari - Mei). | Tabel `gelombang` otomatis dengan countdown timer dan penyesuaian tarif dinamis. | **Fitur Baru Penting**: Meningkatkan daya tarik calon pendaftar. |
| 8 | **Format Cetak Dokumen** | Form pendaftaran & Rekapitulasi pendaftar (Gambar 23, 29, 31). | Formulir Siswa, Rekapitulasi per Jurusan/Semua, Kartu Ujian QR Code, Kwitansi Lunas, Surat Keterangan Lulus (SKL). | **Sesuai & Kompatibel**: Seluruh format cetak dari skripsi tetap dipertahankan dan ditambahkan dokumen pendukung baru. |

---

## 5. AUDIT LINGKUNGAN TEKNOLOGI & PRASYARAT

### A. Versi Laravel dan Framework
- **Versi Terpasang**: **Laravel Framework 13.39.0** (Terkonfirmasi via `php artisan --version`).
- **Komparasi dengan Laravel 13**:
  - Proyek ini menggunakan Laravel 13 modern (`laravel/framework: ^13.17` di `composer.json`, terinstal versi `13.39.0`).
  - Struktur bootstrap modern `bootstrap/app.php` tanpa `Kernel.php` warisan lama.
  - Model Eloquent mendefinisikan casting via method `protected function casts(): array` sesuai standar Laravel 11/12/13.

### B. Versi PHP dan Eksekutabel
- **PHP CLI Aktif di Proyek**: **PHP 8.3.16 (ZTS Visual C++ 2019 x64)** yang berada di `C:\php83\php.exe`.
- **Temuan Konflik PATH**:
  - Perintah `php -v` global di terminal Windows memanggil `C:\xampp\php\php.exe` yang versinya adalah **PHP 8.0.30**.
  - Versi PHP 8.0.30 tidak memenuhi syarat Laravel 13 (yang membutuhkan PHP `^8.3`).
  - Oleh karena itu, perintah artisan harus dijalankan menggunakan path biner `C:\php83\php.exe` atau PATH sistem Windows harus diperbarui agar memprioritaskan `C:\php83`.

### C. Composer dan Node.js
- **Composer**: Tersedia file arsip biner `C:\php83\composer.phar` (Composer 2.x).
- **Node.js & NPM**: Perintah `node -v` dan `npm -v` mengembalikan error *Command Not Found* (Node.js tidak terdaftar di environment PATH Windows).
- **Strategi Frontend yang Digunakan**: Seluruh antarmuka Blade (`layouts/app.blade.php`, `layouts/siswa.blade.php`, `layouts/admin.blade.php`) telah dikonfigurasi menggunakan **Tailwind CSS CDN**, **Lucide Icons CDN**, dan **Chart.js CDN**. Oleh karena itu, aplikasi dapat berjalan dan me-render antarmuka secara sempurna di peramban tanpa memerlukan proses kompilasi lokal `npm run build` / Vite server.

### D. Database MySQL
- **DBMS**: MySQL Server aktif pada `127.0.0.1:3306`.
- **Nama Basis Data**: `ppdb_smk`.
- **Kredensial**: Menggunakan user `root` standar lokal (informasi rahasia tidak diekspos).
- **Status Koneksi**: Berfungsi normal dan dapat diakses oleh Eloquent ORM.

### E. Status Dependensi Composer
- Dependensi PHP telah terpasang lengkap di folder `vendor/` sesuai dengan `composer.lock`.

---

## 6. AUDIT ARSITEKTUR & STRUKTUR PROYEK LARAVEL

```
d:\BASIS_DATA_LANJUT\PPDB_SMK
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── AdminTUController.php       (Operasional TU, seleksi, master data, ekspor)
│   │   │   ├── AuthController.php          (Autentikasi Calon Siswa & Admin Multi-Guard)
│   │   │   ├── Controller.php              (Base controller)
│   │   │   ├── KepalaSekolahController.php (Dashboard eksekutif, SKL, audit log)
│   │   │   ├── LandingController.php       (Halaman depan, cek kelulusan publik, voucher)
│   │   │   └── SiswaDashboardController.php(Portal pendaftar, formulir, berkas, bayar)
│   │   └── Middleware/
│   │       ├── AdminRoleMiddleware.php     (Proteksi role tu vs kepala_sekolah)
│   │       └── CheckMaintenanceMiddleware.php (Pencegat mode pemeliharaan)
│   └── Models/ (14 Model Eloquent 3NF)
├── bootstrap/
│   └── app.php                             (Konfigurasi routes, middleware, exceptions)
├── config/
│   └── auth.php                            (Multi-guard: 'web' -> user, 'admin' -> admin)
├── database/
│   ├── migrations/                         (14 migration file terindeks)
│   └── seeders/
│       └── DatabaseSeeder.php              (Seeder master data & data sampel skripsi)
├── resources/
│   └── views/
│       ├── admin/                          (Views panel TU & rekap PDF)
│       ├── auth/                           (Login, registrasi, lupa password)
│       ├── kepsek/                         (Dashboard eksekutif, kelulusan, log)
│       ├── layouts/                        (Master layout: app, siswa, admin)
│       ├── siswa/                          (Dashboard, form, berkas, bayar, cetak)
│       ├── landing.blade.php               (Landing page terpadu)
│       └── maintenance.blade.php           (Halaman mode pemeliharaan)
├── routes/
│   └── web.php                             (62 routes terdefinisi lengkap)
└── tests/
    └── Feature/
        └── PPDBRouteTest.php               (5 test suites, 8 tests, 45 assertions)
```

---

## 7. AUDIT BASIS DATA & NORMALISASI 3NF

### A. Status Migrasi Database
Pemeriksaan via `php artisan migrate:status` mengonfirmasi bahwa seluruh **14 file migrasi telah dieksekusi (Batch 1)**:
1. `2026_01_01_000001_create_user_table` &rarr; Ran
2. `2026_01_01_000002_create_admin_table` &rarr; Ran
3. `2026_01_01_000003_create_jurusan_table` &rarr; Ran
4. `2026_01_01_000004_create_gelombang_table` &rarr; Ran
5. `2026_01_01_000005_create_voucher_table` &rarr; Ran
6. `2026_01_01_000006_create_pendaftar_table` &rarr; Ran
7. `2026_01_01_000007_create_berkas_pendaftar_table` &rarr; Ran
8. `2026_01_01_000008_create_pembayaran_table` &rarr; Ran
9. `2026_01_01_000009_create_prestasi_pendaftar_table` &rarr; Ran
10. `2026_01_01_000010_create_nilai_seleksi_table` &rarr; Ran
11. `2026_01_01_000011_create_terima_table` &rarr; Ran
12. `2026_01_01_000012_create_pengumuman_table` &rarr; Ran
13. `2026_01_01_000013_create_log_aktivitas_table` &rarr; Ran
14. `2026_01_01_000014_create_pengaturan_sekolah_table` &rarr; Ran

### B. Konvensi Primary Key dan Foreign Key
- **Primary Key (PK)**: Seluruh tabel konsisten menggunakan format baku `id_<tabel>` (`id_user`, `id_admin`, `id_jurusan`, `id_gelombang`, `id_voucher`, `id_pendaftar`, `id_berkas`, `id_pembayaran`, `id_prestasi`, `id_nilai`, `id_terima`, `id_pengumuman`, `id_log`, `id_pengaturan`).
- **Foreign Key (FK)**:
  - `pendaftar.id_user` &rarr; `user.id_user` (ON DELETE CASCADE)
  - `pendaftar.id_gelombang` &rarr; `gelombang.id_gelombang`
  - `pendaftar.id_jurusan_1` &rarr; `jurusan.id_jurusan`
  - `pendaftar.id_jurusan_2` &rarr; `jurusan.id_jurusan` (NULL ON DELETE)
  - `berkas_pendaftar.id_pendaftar` &rarr; `pendaftar.id_pendaftar` (CASCADE)
  - `pembayaran.id_pendaftar` &rarr; `pendaftar.id_pendaftar` (CASCADE)
  - `pembayaran.id_gelombang` &rarr; `gelombang.id_gelombang`
  - `pembayaran.id_voucher` &rarr; `voucher_pendaftaran.id_voucher` (NULL ON DELETE)
  - `prestasi_pendaftar.id_pendaftar` &rarr; `pendaftar.id_pendaftar` (CASCADE)
  - `nilai_seleksi.id_pendaftar` &rarr; `pendaftar.id_pendaftar` (CASCADE)
  - `terima.id_pendaftar` &rarr; `pendaftar.id_pendaftar` (CASCADE, UNIQUE 1:1)
  - `terima.id_jurusan` &rarr; `jurusan.id_jurusan`
  - `terima.id_admin_pengesah` &rarr; `admin.id_admin` (NULL ON DELETE)
  - `log_aktivitas.id_admin` &rarr; `admin.id_admin`, `log_aktivitas.id_user` &rarr; `user.id_user`

### C. Evaluasi Normalisasi 1NF, 2NF, 3NF
- **1NF**: Terpenuhi. Seluruh nilai kolom bersifat atomik. Tidak ada data berulang yang digabung dalam satu field tanpa relasi terstruktur.
- **2NF**: Terpenuhi. Seluruh tabel menggunakan primary key tunggal (`id_<tabel>`), sehingga seluruh atribut non-kunci bergantung penuh pada PK.
- **3NF**: Terpenuhi dengan baik. Ketergantungan transitif yang ada pada skripsi 2022 (seperti menyimpan nama jurusan langsung di tabel pendaftar atau menduplikasi nama dan asal sekolah di tabel `terima`) telah dieliminasi melalui foreign key dan query join.

### D. Temuan Inkonsistensi Kolom Skema Basis Data
- **Model `VoucherPendaftaran` vs Migration 5**:
  - Pada `SiswaDashboardController.php` baris 328 & 334, kode program mengakses `$voucher->kuota_terpakai` dan menjalankan `$voucher->increment('kuota_terpakai')`.
  - Pada file migrasi `2026_01_01_000005_create_voucher_table.php`, tabel hanya memiliki kolom `kuota_voucher`, **tanpa kolom `kuota_terpakai`**.
  - **Dampak**: Jika pendaftar menginput kode voucher saat simulasi pembayaran, sistem akan melempar SQL exception: `Unknown column 'kuota_terpakai'`.

---

## 8. AUDIT FITUR BISNIS PPDB (18 MODUL)

| No | Modul / Fitur PPDB | Status Implementasi | Bukti Kode & Verifikasi |
|---|---|---|---|
| 1 | **Website Publik & Beranda** | SUDAH SESUAI & BERFUNGSI | `LandingController@index`, `resources/views/landing.blade.php`. Route `/` berstatus HTTP 200. Memuat hero section, statistik live, dan countdown gelombang. |
| 2 | **Profil Sekolah** | SUDAH SESUAI & BERFUNGSI | `LandingController.php`, memuat profil resmi SMK IB Khalifah Bangsa, NPSN 69773555, Izin Operasional 115/KPTS/D.3/03/2013, dan Kepala Sekolah Asliman Puja Kesuma, S.Pd. |
| 3 | **Informasi 4 Program Keahlian** | SUDAH SESUAI & BERFUNGSI | Tabel `jurusan` memuat Farmasi Klinis (`FAR`), TKJ (`TKJ`), Otomotif Mobil (`TKR`), Sepeda Motor (`TBSM`) beserta kuota masing-masing 72 siswa. Tampil di landing page dan opsi formulir. |
| 4 | **Informasi Gelombang Pendaftaran** | SUDAH SESUAI & BERFUNGSI | Tabel `gelombang`, `LandingController@index`. Menampilkan gelombang aktif (Gelombang 1 Early Bird) dan tarif formulir dinamis. |
| 5 | **Registrasi & Login Calon Siswa** | SUDAH SESUAI & BERFUNGSI | `AuthController@register` & `login`. Multi-guard teruji di `PPDBRouteTest`. Terdapat pemisahan ketat antara portal siswa (`/login`) dan portal staf (`/admin/login`). |
| 6 | **Dashboard Calon Siswa** | SUDAH SESUAI & BERFUNGSI | `SiswaDashboardController@index`, `siswa/dashboard.blade.php`. Menyajikan Interactive Stepper 6 Tahap progres pendaftaran. |
| 7 | **Formulir Pendaftaran Siswa Baru** | SUDAH SESUAI & BERFUNGSI | `SiswaDashboardController@formulir`, `simpanFormulir`, `finalisasi`. Mengakomodasi 23 field biodata skripsi, 2 pilihan jurusan, jalur pendaftaran, ukuran perlengkapan, dan fitur kunci data (`is_locked`). |
| 8 | **Unggah Berkas Digital** | SUDAH DIBUAT TETAPI PERLU PERBAIKAN | `SiswaDashboardController@uploadBerkas` & `uploadPrestasi`. Validasi format jpg/png/pdf max 3MB berjalan. Namun symlink `public/storage` belum dibuat dan file masih disimpan di folder publik (isu privasi data sensitif). |
| 9 | **Pembayaran Formulir** | SUDAH SESUAI (SIMULASI) | `SiswaDashboardController@pembayaran`, `prosesBayarTunai`, `prosesBayarMidtransSimulasi`. Pilihan bayar ganda (Loket TU & Midtrans Simulator) berjalan. Catatan: terdapat bug voucher `kuota_terpakai`. |
| 10 | **Verifikasi Berkas oleh TU** | SUDAH SESUAI & BERFUNGSI | `AdminTUController@verifikasiBerkas`, `verifikasiSingleBerkas`. Admin dapat menandai status Valid / Tidak Valid dengan catatan alasan revisi. |
| 11 | **Seleksi & Penilaian Peserta** | SUDAH SESUAI & BERFUNGSI | `AdminTUController@seleksiPenilaian`, `simpanPenilaian`, `alihkanJurusan`. Mengakomodasi nilai rapor, wawancara, tes buta warna (Normal/Parsial/Total), dan pengalihan ke Jurusan Pilihan 2. |
| 12 | **Pengumuman Hasil Kelulusan** | SUDAH SESUAI & BERFUNGSI | Internal: `KepalaSekolahController@kelulusan` & `sahkanKelulusan`. Publik: `LandingController@cekKelulusan` via AJAX modal menggunakan NISN / No. Pendaftaran. |
| 13 | **Daftar Ulang Pasca-Lulus** | SUDAH SESUAI & BERFUNGSI | `SiswaDashboardController@daftarUlang`, `konfirmasiDaftarUlang`. Mengubah status menjadi `Selesai` dan mencatat log aktivitas. |
| 14 | **Dashboard Petugas TU** | SUDAH SESUAI & BERFUNGSI | `AdminTUController@index`, `admin/dashboard.blade.php`. Metrik live statistik pendaftar harian, kuota jurusan, dan antrean verifikasi. |
| 15 | **Dashboard Eksekutif Kepala Sekolah** | SUDAH SESUAI & BERFUNGSI | `KepalaSekolahController@index`, `kepsek/dashboard.blade.php`. Visualisasi Chart.js, tren pendaftar 7 hari, dan Market Intelligence "Top 5 SMP Asal". |
| 16 | **Laporan & Ekspor Data** | SUDAH SESUAI & BERFUNGSI | `AdminTUController@eksporExcel` (CSV UTF-8 BOM), `eksporDapodik` (format resmi Dapodik Kemendikbud), dan `cetakRekapPDF` (kop resmi & TTD Kepala Sekolah). |
| 17 | **Modul Notifikasi Otomatis** | BELUM SELESAI | Token WhatsApp API dan SMTP Mailer disiapkan di tabel pengaturan, namun pengiriman riil via HTTP Fonnte/Wablas masih berupa simulasi local flash. |
| 18 | **Pengaturan Sistem & CMS** | SUDAH DIBUAT (BUG MINOR) | `AdminTUController@pengaturanSekolah`, `toggleMaintenance`. Sakelar mode pemeliharaan berfungsi namun memblokir route kepala sekolah (`kepsek*`). |

---

## 9. HASIL PENGUJIAN SISTEM (AUTOMATED TESTS & SAFE COMMANDS)

### A. Rekapitulasi Perintah Diagnostik yang Dijalankan
1. `php artisan --version`:
   - **Hasil**: `Laravel Framework 13.39.0` (Berhasil).
2. `php artisan about`:
   - **Hasil**: Konfigurasi terbaca lengkap, Environment `local`, Cache views aktif, Drivers MySQL/Session/Queue/Mail terkonfigurasi (Berhasil).
3. `php artisan route:list`:
   - **Hasil**: 62 rute terdaftar tanpa konflik sintaks controller (Berhasil).
4. `php artisan migrate:status`:
   - **Hasil**: 14/14 migrasi berstatus `[1] Ran` (Berhasil).
5. `php artisan test`:
   - **Hasil**: `PPDBRouteTest.php` & `ExampleTest.php` &rarr; **8 passed, 45 assertions, Zero Errors (100% Passed)** (Berhasil).
6. `npm run build`:
   - **Hasil**: Tidak dapat dijalankan karena Node.js / NPM belum terpasang di sistem operasi Windows.
   - **Analisis Dampak**: Tidak menghambat jalannya aplikasi karena antarmuka sepenuhnya menggunakan Tailwind CSS CDN, Lucide CDN, dan Chart.js CDN yang ter-load langsung oleh peramban.

---

## 10. AUDIT KEAMANAN SISTEM & KEPATUHAN PRIVASI (UU PDP)

1. **Proteksi Autentikasi & Multi-Guard**:
   - Guard `web` (Siswa) dan guard `admin` (TU & Kepsek) terisolasi secara tegas. Calon siswa tidak dapat login ke `/admin/login`, dan staf admin tidak dapat login ke `/login`.
   - Session di-regenerate saat login (`$request->session()->regenerate()`) untuk mencegah serangan *Session Fixation*.
2. **Role & Permission Access Control**:
   - `AdminRoleMiddleware` memvalidasi peran pengguna secara dinamis (`admin.role:tu` vs `admin.role:kepala_sekolah`).
3. **Validasi Formulir (Input Sanitization)**:
   - Validasi ketat pada NISN (10 digit angka), email unik, username unik, password confirmed, dan batasan ukuran berkas.
4. **Proteksi CSRF, XSS, & SQL Injection**:
   - Token `@csrf` terpasang pada seluruh form mutasi data.
   - Blade engine mengeksekusi html escaping `{{ ... }}` secara menyeluruh.
   - Eloquent ORM menggunakan parameterized bindings sehingga aman dari SQL Injection.
5. **Keamanan Penyimpanan Dokumen Sensitif (Isu Privasi UU PDP)**:
   - Berkas Ijazah, Akta Kelahiran, dan KK yang memuat NIK saat ini disimpan di disk `public` (`storage/app/public/berkas_pendaftar`).
   - Sesuai `SPEC_PPDB.md` butir 6.6, berkas kependudukan semestinya disimpan pada `private storage` dan hanya dapat diunduh melalui Signed URL atau Controller dengan kebijakan otorisasi (`authorize`).
6. **Keamanan Transaksi Pembayaran**:
   - Saat ini pembayaran menggunakan mode simulasi lokal instan. Rute webhook publik (`/api/midtrans/notification`) dengan validasi signature hash SHA512 belum diimplementasikan untuk transaksi live Midtrans.

---

## 11. KATALOG TEMUAN AUDIT BERDASARKAN KATEGORI

### Kategori 1: SUDAH SESUAI DAN BERFUNGSI
1. **Landing Page Publik & Profil Sekolah**: Tampil lengkap dengan identitas resmi Kota Metro, 4 program keahlian, dan 7 ekstrakurikuler.
2. **Pendaftaran Akun & Autentikasi Multi-Guard**: Registrasi siswa, pemisahan portal admin dan portal siswa.
3. **Formulir Pendaftaran Terpadu**: Biodata lengkap 23 field, 2 pilihan jurusan, jalur pendaftaran, ukuran perlengkapan khas SMK.
4. **Fitur Finalisasi & Kunci Data**: Mencegah calon siswa mengubah data secara sepihak setelah diserahkan.
5. **Dashboard Calon Siswa (Stepper 6 Tahap)**: Menampilkan alur pendaftaran secara transparan dan dinamis.
6. **Modul Seleksi & Penilaian (Kekurangan Skripsi Teratasi)**: Input nilai rapor, wawancara, tes buta warna, dan limpahan kuota antar-jurusan.
7. **Pengesahan Kelulusan oleh Kepala Sekolah**: Penetapan status Diterima/Cadangan/Tidak Lolos dan penerbitan nomor SKL otomatis.
8. **Widget Cek Kelulusan Mandiri**: Akses instan pencarian NISN di halaman utama tanpa login.
9. **Cetak Dokumen Resmi Skripsi**: Format Cetak Formulir Siswa dan Laporan Rekapitulasi ber-kop surat resmi dan kolom tanda tangan Kepala Sekolah Asliman Puja Kesuma, S.Pd.
10. **Laporan Multi-Format**: Ekspor CSV UTF-8 Excel dan Template Import Dapodikdasmen 1-Klik.
11. **Mode Gelap (Dark Mode)**: Tombol switch matahari/bulan persisten di seluruh halaman.

### Kategori 2: SUDAH DIBUAT TETAPI BELUM TERUJI
1. **Penerapan Kupon Diskon / Voucher Beasiswa pada Pembayaran**: Logika pemotongan harga telah ditulis di controller, namun terhalang bug kolom basis data saat dieksekusi.
2. **Cetak Massal PDF Formulir Siswa per Jurusan**: Tombol dan alur cetak siap, belum dilakukan uji coba volume beban banyak pendaftar.

### Kategori 3: BELUM SELESAI
1. **Webhook Real-Time Payment Gateway**: Webhook handler asynchronous Midtrans (`/api/midtrans/notification`) belum dibuat; saat ini masih menggunakan simulasi settlement lokal.
2. **Integrasi Gateway WhatsApp / SMTP Live**: Logika pemanggilan HTTP API Fonnte/Wablas ke nomor siswa belum tersambung secara riil (masih berupa session demo message).
3. **Penyimpanan Berkas Privat (Private Storage Streaming)**: Berkas belum diarahkan ke private storage non-publik.

### Kategori 4: TIDAK SESUAI DENGAN SPEC_PPDB.MD
1. **Format File Ekspor Excel**: Pada `SPEC_PPDB.md` poin 14.1 disebutkan ekspor `.xlsx` dan `.csv`. Implementasi saat ini mengalirkan stream CSV bertipe MIME CSV dengan nama file berakhiran `.csv` (belum menggunakan generator file biner Excel `.xlsx` native seperti PhpSpreadsheet).
2. **Pengecualian Rute Mode Pemeliharaan**: SPEC_PPDB.md menyatakan Admin TU dan Kepala Sekolah dapat mengakses sistem saat mode pemeliharaan aktif. Kode middleware hanya mengecualikan `admin*`, sehingga Kepala Sekolah (`kepsek*`) ikut terblokir halaman 503.

### Kategori 5: ERROR ATAU BUG
1. **Bug Missing Import Hash di SiswaDashboardController**:
   - *Lokasi*: `app/Http/Controllers/SiswaDashboardController.php` baris 393.
   - *Penyebab*: Memanggil `Hash::make($request->password)` pada method `simpanPengaturanAkun`, namun baris `use Illuminate\Support\Facades\Hash;` belum ada di header controller.
   - *Akibat*: Fatal runtime error `Class 'App\Http\Controllers\Hash' not found` ketika siswa memperbarui kata sandi.
2. **Bug Kolom Tidak Ditemukan pada Tabel Voucher**:
   - *Lokasi*: `app/Http/Controllers/SiswaDashboardController.php` baris 328 & 334.
   - *Penyebab*: Kode memanggil `$voucher->kuota_terpakai` dan `$voucher->increment('kuota_terpakai')`, namun kolom `kuota_terpakai` tidak ada pada tabel `voucher_pendaftaran`.
   - *Akibat*: SQL error saat pendaftar memasukkan kode voucher valid.
3. **Bug Pemblokiran Route Kepala Sekolah saat Maintenance**:
   - *Lokasi*: `app/Http/Middleware/CheckMaintenanceMiddleware.php` baris 15.
   - *Penyebab*: Pemeriksaan hanya `if ($request->is('admin*') || $request->is('login') || $request->is('up'))`. Route `kepsek*` tidak disertakan.
   - *Akibat*: Kepala Sekolah terlempar ke halaman error 503 saat TU mengaktifkan mode maintenance.

### Kategori 6: RISIKO KEAMANAN
1. **Privasi Dokumen Kependudukan (UU PDP)**: Berkas ijazah, akta lahir, dan KK disimpan pada disk public. Jika symlink public storage aktif, berkas dapat ditebak melalui URL publik jika pola nama file diketahui.
2. **Ketiadaan Validasi Signature Webhook**: Pembayaran live memerlukan pengecekan hash signature SHA512 dari Midtrans agar status pendaftar tidak dapat dimanipulasi dari luar.

### Kategori 7: PERLU PERBAIKAN
1. **Penyelarasan Environment PATH PHP**: Sistem operasi Windows saat ini mengarahkan perintah global `php` ke PHP 8.0.30 XAMPP, padahal proyek memerlukan `C:\php83\php.exe`.
2. **Symlink Storage**: Direktori `public/storage` belum ditautkan (`storage:link`).
3. **Import Namespace Hash**: Tambahkan `use Illuminate\Support\Facades\Hash;` pada `SiswaDashboardController.php`.
4. **Penambahan Kolom Migrasi `kuota_terpakai`**: Tambahkan kolom `kuota_terpakai INT NOT NULL DEFAULT 0` pada tabel `voucher_pendaftaran`.
5. **Perbaikan Whitelist Middleware Maintenance**: Tambahkan `$request->is('kepsek*')` pada `CheckMaintenanceMiddleware.php`.

---

## 12. MATRIKS PERBANDINGAN FITUR: SPEC VS IMPLEMENTASI NYATA

| Fitur / Komponen pada SPEC_PPDB.md | Status Implementasi Nyata | Bukti File / Rute / Pengujian |
|---|:---:|---|
| **Profil Sekolah & 4 Program Keahlian** | **Selesai (100%)** | `resources/views/landing.blade.php`, `app/Models/Jurusan.php` |
| **7 Ekstrakurikuler Sekolah** | **Selesai (100%)** | `LandingController.php` baris 29–37, `landing.blade.php` |
| **Normalisasi Basis Data 3NF (14 Tabel)** | **Selesai (95%)** | 14 Migration file telah berjalan. Ada kekurangan kolom `kuota_terpakai` pada tabel `voucher_pendaftaran`. |
| **Multi-Guard RBAC (Siswa, TU, Kepsek)** | **Selesai (100%)** | `config/auth.php`, `AdminRoleMiddleware.php`, lolos test role separation. |
| **Formulir Pendaftaran & Kunci Data** | **Selesai (100%)** | `SiswaDashboardController@formulir`, `is_locked` boolean flag. |
| **Upload Berkas Digital** | **Selesai (90%)** | `SiswaDashboardController@uploadBerkas`, tersimpan di disk public. |
| **Jalur Prestasi & Piagam Kejuaraan** | **Selesai (100%)** | `app/Models/PrestasiPendaftar.php`, upload sertifikat dan skor bobot. |
| **Pembayaran Ganda (Midtrans & Tunai TU)** | **Selesai (90%)** | `prosesBayarTunai` & `prosesBayarMidtransSimulasi` berfungsi. Webhook live belum ada. |
| **Modul Seleksi, Rapor & Tes Buta Warna** | **Selesai (100%)** | `AdminTUController@simpanPenilaian`, `app/Models/NilaiSeleksi.php`. |
| **Limpahan Kuota Pilihan 1 ke Pilihan 2** | **Selesai (100%)** | `AdminTUController@alihkanJurusan`, route `POST /admin/pendaftar/{id}/alihkan`. |
| **Pengesahan Kelulusan & SKL Digital** | **Selesai (100%)** | `KepalaSekolahController@sahkanKelulusan`, generator No. SKL resmi. |
| **Cetak Kartu Ujian Seleksi QR Code** | **Selesai (100%)** | `SiswaDashboardController@cetakKartuUjian`, `siswa/cetak_kartu_ujian.blade.php`. |
| **Cetak Formulir Standar Skripsi** | **Selesai (100%)** | `siswa/cetak_formulir.blade.php` (replika Gambar 23, 31, 33 Skripsi 2022). |
| **Cetak Rekapitulasi Nilai & Pendaftar PDF** | **Selesai (100%)** | `admin/laporan/rekap_pdf.blade.php` (replika Gambar 20, 24, 29, 30 Skripsi 2022). |
| **Ekspor Excel & Template Dapodik** | **Selesai (95%)** | `AdminTUController@eksporExcel` & `eksporDapodik` (format CSV UTF-8 Excel). |
| **Panel Eksekutif & Market Intelligence Kepsek** | **Selesai (100%)** | `KepalaSekolahController@index`, Chart.js tren & Top 5 Asal SMP. |
| **Audit Trail (Log Aktivitas Sistem)** | **Selesai (100%)** | `app/Models/LogAktivitas.php`, mencatat mutasi data, IP, dan User-Agent. |
| **Mode Pemeliharaan (Maintenance Mode)** | **Selesai (90%)** | Sakelar satu-klik aktif di panel admin, view `maintenance.blade.php`. Perlu perbaikan whitelist Kepsek. |
| **Mode Gelap (Dark Mode)** | **Selesai (100%)** | Toggle navbar, class `dark` Tailwind CSS, tersimpan di `localStorage`. |

---

## 13. REKOMENDASI RENCANA PERBAIKAN (ACTION PLAN) MENUNGGU PERSETUJUAN

Berikut adalah daftar perbaikan konkret yang direkomendasikan untuk dieksekusi setelah persetujuan pemilik proyek:

1. **Perbaikan Kode Cepat (Zero Risk Patches)**:
   - Menambahkan baris `use Illuminate\Support\Facades\Hash;` pada `app/Http/Controllers/SiswaDashboardController.php`.
   - Menambahkan rute `kepsek*` ke dalam whitelist `CheckMaintenanceMiddleware.php`:
     ```php
     if ($request->is('admin*') || $request->is('kepsek*') || $request->is('login') || $request->is('up'))
     ```
2. **Penyempurnaan Skema Migrasi Voucher**:
   - Menambahkan migration penambahan kolom `kuota_terpakai` (tipe `integer`, default `0`) pada tabel `voucher_pendaftaran` tanpa menghapus data yang ada.
3. **Penyelarasan Tautan Storage**:
   - Menjalankan `C:\php83\php.exe artisan storage:link` agar file berkas yang diunggah dapat ditampilkan dan ditinjau pada viewer panel verifikasi Admin TU.
4. **Peningkatan Format Ekspor Excel (.xlsx)**:
   - Jika diperlukan file biner `.xlsx` murni (bukan CSV UTF-8), dapat disiapkan generator spreadsheet native.
5. **Konfigurasi Lingkungan Windows Pengguna**:
   - Memberikan panduan kepada pengguna untuk memperbarui urutan variabel `PATH` di Windows agar `C:\php83` berada di atas `C:\xampp\php`, sehingga perintah `php` dan `composer` dapat dipanggil secara langsung di terminal mana pun.

---

> **CATATAN AUDITOR:**  
> Sesuai batasan instruksi kerja, tahap audit ini telah selesai dilaksanakan secara mendalam tanpa memodifikasi kode aplikasi, tanpa menghapus file, dan tanpa merusak struktur database yang sudah ada.  
> **Auditor berhenti di sini dan menunggu instruksi/persetujuan resmi Anda untuk melanjutkan ke tahap perbaikan.**

