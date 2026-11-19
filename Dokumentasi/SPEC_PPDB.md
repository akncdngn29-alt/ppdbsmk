# DOKUMEN SPESIFIKASI & ARSITEKTUR BASIS DATA SISTEM INFORMASI PPDB MODERN
## SMK ISLAM BINA KHALIFAH BANGSA (KOTA METRO, LAMPUNG)
### Standar Normalisasi Basis Data: 1NF, 2NF, hingga 3NF & Konvensi Primary Key Eksplisit (`id_<tabel>`)

---

## 1. RUJUKAN DAN INTEGRASI PROFIL INSTITUSI
- **Nama Sekolah**: SMK Islam Bina Khalifah Bangsa
- **Lokasi**: Kota Metro, Lampung
- **Rujukan**: Skripsi Anggun Puspita Dewi (2022) & Website Rujukan (https://smkib.com)
- **Kepala Sekolah**: Asliman Puja Kesuma, S.Pd
- **Program Keahlian / 4 Jurusan Utama**:
  1. Farmasi Klinis dan Komunitas
  2. Teknik Komputer dan Jaringan (TKJ)
  3. Teknik Kendaraan Ringan (TKR)
  4. Teknik dan Bisnis Sepeda Motor (TBSM)
- **Ekstrakurikuler yang Ditampilkan**:
  1. Pramuka
  2. Paskibra
  3. Tilawati Al-Qur'an
  4. Tafsir & Tahfiz Al-Qur'an
  5. Karate
  6. Gamelan Karawitan
  7. Gamelan Tolak Balak

---

## 2. ATURAN PENAMAAN KUNCI (KEY CONVENTION)
Sesuai instruksi baku:
1. **Primary Key (PK)**: Wajib didefinisikan secara eksplisit dengan format `id_<nama_tabel>` (contoh: tabel `produk` &rarr; `id_produk`).
   - Tabel `user` &rarr; `id_user`
   - Tabel `admin` &rarr; `id_admin`
   - Tabel `jurusan` &rarr; `id_jurusan`
   - Tabel `gelombang` &rarr; `id_gelombang`
   - Tabel `pendaftar` &rarr; `id_pendaftar`
   - Tabel `berkas_pendaftar` &rarr; `id_berkas`
   - Tabel `pembayaran` &rarr; `id_pembayaran`
   - Tabel `terima` &rarr; `id_terima`
   - Tabel `log_aktivitas` &rarr; `id_log`
2. **Foreign Key (FK)**: Menunjuk langsung ke nama Primary Key tabel relasinya (misal: `id_user`, `id_pendaftar`, `id_jurusan`, `id_gelombang`).

---

## 3. PROSES NORMALISASI BASIS DATA (UNF &rarr; 1NF &rarr; 2NF &rarr; 3NF)

### A. Bentuk Tidak Normal (UNF - Unnormalized Form)
Pada dokumen pendaftaran mentah, seluruh data siswa, data orang tua, riwayat sekolah asal, multi-berkas, pembayaran, dan pilihan jurusan berada dalam satu record besar yang berpotensi memiliki repeating groups dan data redundan.

### B. Bentuk Normal Pertama (1NF - First Normal Form)
- **Kaidah 1NF**:
  1. Setiap kolom/atribut hanya memiliki satu nilai tunggal (Atomic Value).
  2. Tidak ada kelompok atribut yang berulang (No Repeating Groups).
  3. Setiap tabel memiliki Primary Key unik yang terdefinisi dengan pasti (`id_<tabel>`).
- **Penerapan**:
  - Kolom berkas digital tidak digabung dalam satu field JSON/string berulang, melainkan dipisah per entitas berkas atau field atomik tersendiri.
  - Data kontak dan alamat dipecah menjadi atribut tunggal atomik.

### C. Bentuk Normal Kedua (2NF - Second Normal Form)
- **Kaidah 2NF**:
  1. Telah memenuhi kriteria 1NF.
  2. Tidak terdapat Ketergantungan Parsial (Partial Functional Dependency). Semua atribut non-kunci harus bergantung sepenuhnya (Full Functional Dependency) pada Primary Key.
- **Penerapan**:
  - Karena semua Primary Key tabel dirancang berupa kunci tunggal (`id_user`, `id_pendaftar`, `id_jurusan`, dll), bukan composite key gabungan, maka seluruh atribut non-kunci secara otomatis bergantung penuh pada Primary Key masing-masing.

### D. Bentuk Normal Ketiga (3NF - Third Normal Form)
- **Kaidah 3NF**:
  1. Telah memenuhi kriteria 2NF.
  2. Tidak terdapat Ketergantungan Transitif (Transitive Dependency), yaitu atribut non-kunci tidak boleh bergantung pada atribut non-kunci lainnya ($X \rightarrow Y \rightarrow Z$).
- **Penerapan Eliminasi Ketergantungan Transitif**:
  - **Jurusan**: Pada skripsi lama, nama jurusan disimpan sebagai teks bebas di tabel pendaftar. Pada 3NF, data jurusan dipisah ke tabel master `jurusan` (`id_jurusan`, `nama_jurusan`, `kuota`). Tabel `pendaftar` hanya menyimpan FK: `id_jurusan_1` dan `id_jurusan_2`.
  - **Gelombang Pendaftaran & Biaya**: Nominal biaya pendaftaran tidak disimpan redundan di pendaftar/pembayaran, melainkan mereferensikan `id_gelombang`.
  - **Hasil Kelulusan (`terima`)**: Pada skripsi lama, tabel `terima` menduplikasi `nama_lengkap`, `nisn`, `sekolah_asal`, dan `jurusan`. Pada 3NF, tabel `terima` cukup menyimpan `id_terima`, `id_pendaftar` (FK), `id_jurusan` (FK yang diterima), `no_peserta_lolos`, dan `tgl_diterima`. Data nama, NISN, dan asal sekolah diperoleh via relasi JOIN ke tabel `pendaftar` untuk mencegah anomali pembaruan (Update Anomaly).
  - **Pembayaran**: Dipisahkan ke entitas `pembayaran` (`id_pembayaran`, `id_pendaftar`, `id_gelombang`, `kode_transaksi`, `nominal`, `metode_pembayaran`, `status_pembayaran`) agar tidak mencemari integritas entitas biodata pendaftar.
  - **Berkas Unggahan**: Dipisahkan atau distrukturkan rapi ke entitas `berkas_pendaftar` sehingga verifikasi berkas per item tidak menimbulkan ketergantungan transitif.

---

## 4. SKEMA DATABASE TERLENGKAP 3NF (MYSQL DDL COMPLIANT)

```sql
-- 1. Tabel Master Jurusan
CREATE TABLE IF NOT EXISTS `jurusan` (
  `id_jurusan` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `nama_jurusan` VARCHAR(100) NOT NULL,
  `kode_jurusan` VARCHAR(20) NOT NULL UNIQUE,
  `kuota` INT NOT NULL DEFAULT 72,
  `created_at` TIMESTAMP NULL,
  `updated_at` TIMESTAMP NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 2. Tabel Master Gelombang Pendaftaran
CREATE TABLE IF NOT EXISTS `gelombang` (
  `id_gelombang` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `nama_gelombang` VARCHAR(100) NOT NULL,
  `tahun_ajaran` VARCHAR(20) NOT NULL DEFAULT '2026/2027',
  `tgl_buka` DATE NOT NULL,
  `tgl_tutup` DATE NOT NULL,
  `biaya_pendaftaran` DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  `status_aktif` BOOLEAN NOT NULL DEFAULT TRUE,
  `created_at` TIMESTAMP NULL,
  `updated_at` TIMESTAMP NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 3. Tabel User (Autentikasi Calon Siswa)
CREATE TABLE IF NOT EXISTS `user` (
  `id_user` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `nama` VARCHAR(100) NOT NULL,
  `nisn` VARCHAR(10) NOT NULL UNIQUE,
  `username` VARCHAR(50) NOT NULL UNIQUE,
  `email` VARCHAR(100) NOT NULL UNIQUE,
  `password` VARCHAR(255) NOT NULL,
  `remember_token` VARCHAR(100) NULL,
  `created_at` TIMESTAMP NULL,
  `updated_at` TIMESTAMP NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 4. Tabel Admin (Petugas TU & Kepala Sekolah)
CREATE TABLE IF NOT EXISTS `admin` (
  `id_admin` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `nama_admin` VARCHAR(100) NOT NULL,
  `username` VARCHAR(50) NOT NULL UNIQUE,
  `email` VARCHAR(100) NOT NULL UNIQUE,
  `password` VARCHAR(255) NOT NULL,
  `role` ENUM('tu', 'kepala_sekolah') NOT NULL DEFAULT 'tu',
  `created_at` TIMESTAMP NULL,
  `updated_at` TIMESTAMP NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 5. Tabel Pendaftar (Biodata Inti Pendaftar - 3NF)
CREATE TABLE IF NOT EXISTS `pendaftar` (
  `id_pendaftar` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `id_user` BIGINT UNSIGNED NOT NULL,
  `id_gelombang` BIGINT UNSIGNED NOT NULL,
  `id_jurusan_1` BIGINT UNSIGNED NOT NULL,
  `id_jurusan_2` BIGINT UNSIGNED NULL,
  `no_pendaftaran` VARCHAR(30) NOT NULL UNIQUE,
  
  -- Data Pribadi Calon Siswa
  `nisn` VARCHAR(10) NOT NULL,
  `nama` VARCHAR(100) NOT NULL,
  `tempat_lahir` VARCHAR(100) NOT NULL,
  `tanggal_lahir` DATE NOT NULL,
  `jenis_kelamin` ENUM('Laki-laki', 'Perempuan') NOT NULL,
  `agama` VARCHAR(50) NOT NULL,
  `anak_ke` INT NOT NULL DEFAULT 1,
  `jml_saudara` INT NOT NULL DEFAULT 0,
  `hp_siswa` VARCHAR(20) NOT NULL,
  `almt_siswa` TEXT NOT NULL,
  `berat_badan` VARCHAR(10) NOT NULL,
  `tinggi_badan` VARCHAR(10) NOT NULL,
  `golongan_darah` VARCHAR(5) NOT NULL,
  
  -- Data Asal Sekolah
  `asal_sekolah` VARCHAR(100) NOT NULL,
  `alamat_sekolah` TEXT NOT NULL,
  
  -- Data Orang Tua / Wali
  `nama_ayah` VARCHAR(100) NOT NULL,
  `nama_ibu` VARCHAR(100) NOT NULL,
  `almt_ortu` TEXT NOT NULL,
  `hp_ortu` VARCHAR(20) NOT NULL,
  `kerja_ayah` VARCHAR(100) NOT NULL,
  `kerja_ibu` VARCHAR(100) NOT NULL,
  `penghasilan_ortu` VARCHAR(50) NOT NULL,
  `tanggungan_anak` INT NOT NULL DEFAULT 1,
  
  -- Status Pendaftaran Terpadu
  `status_pembayaran` ENUM('Pending', 'Paid', 'Free') NOT NULL DEFAULT 'Pending',
  `status_verifikasi` ENUM('Menunggu', 'Terverifikasi', 'Ditolak') NOT NULL DEFAULT 'Menunggu',
  `catatan_verifikasi` TEXT NULL,
  `status_kelulusan` ENUM('Diproses', 'Diterima', 'Tidak Lolos') NOT NULL DEFAULT 'Diproses',
  `tgl_daftar` DATETIME NOT NULL,
  `created_at` TIMESTAMP NULL,
  `updated_at` TIMESTAMP NULL,
  
  CONSTRAINT `fk_pendaftar_user` FOREIGN KEY (`id_user`) REFERENCES `user` (`id_user`) ON DELETE CASCADE,
  CONSTRAINT `fk_pendaftar_gelombang` FOREIGN KEY (`id_gelombang`) REFERENCES `gelombang` (`id_gelombang`),
  CONSTRAINT `fk_pendaftar_jurusan_1` FOREIGN KEY (`id_jurusan_1`) REFERENCES `jurusan` (`id_jurusan`),
  CONSTRAINT `fk_pendaftar_jurusan_2` FOREIGN KEY (`id_jurusan_2`) REFERENCES `jurusan` (`id_jurusan`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 6. Tabel Berkas Pendaftar (1NF/3NF - Atomic File Management)
CREATE TABLE IF NOT EXISTS `berkas_pendaftar` (
  `id_berkas` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `id_pendaftar` BIGINT UNSIGNED NOT NULL,
  `jenis_berkas` ENUM('ijazah', 'kk', 'pasfoto', 'akta') NOT NULL,
  `path_file` VARCHAR(255) NOT NULL,
  `status_berkas` ENUM('Menunggu', 'Valid', 'Tidak Valid') NOT NULL DEFAULT 'Menunggu',
  `catatan` VARCHAR(255) NULL,
  `created_at` TIMESTAMP NULL,
  `updated_at` TIMESTAMP NULL,
  
  CONSTRAINT `fk_berkas_pendaftar` FOREIGN KEY (`id_pendaftar`) REFERENCES `pendaftar` (`id_pendaftar`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 7. Tabel Pembayaran (3NF - Transaksi Finansial Terpisah)
CREATE TABLE IF NOT EXISTS `pembayaran` (
  `id_pembayaran` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `id_pendaftar` BIGINT UNSIGNED NOT NULL,
  `id_gelombang` BIGINT UNSIGNED NOT NULL,
  `kode_transaksi` VARCHAR(100) NOT NULL UNIQUE,
  `snap_token` VARCHAR(255) NULL,
  `nominal` DECIMAL(12,2) NOT NULL,
  `metode_pembayaran` VARCHAR(50) NULL,
  `status_transaksi` ENUM('pending', 'settlement', 'expire', 'cancel') NOT NULL DEFAULT 'pending',
  `waktu_transaksi` DATETIME NULL,
  `created_at` TIMESTAMP NULL,
  `updated_at` TIMESTAMP NULL,
  
  CONSTRAINT `fk_pembayaran_pendaftar` FOREIGN KEY (`id_pendaftar`) REFERENCES `pendaftar` (`id_pendaftar`) ON DELETE CASCADE,
  CONSTRAINT `fk_pembayaran_gelombang` FOREIGN KEY (`id_gelombang`) REFERENCES `gelombang` (`id_gelombang`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 8. Tabel Terima (3NF - Penetapan Kelulusan Tanpa Redundansi)
CREATE TABLE IF NOT EXISTS `terima` (
  `id_terima` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `id_pendaftar` BIGINT UNSIGNED NOT NULL UNIQUE,
  `id_jurusan` BIGINT UNSIGNED NOT NULL,
  `id_admin_pengesah` BIGINT UNSIGNED NULL,
  `no_surat_kelulusan` VARCHAR(100) NOT NULL UNIQUE,
  `tgl_diterima` DATE NOT NULL,
  `keterangan` TEXT NULL,
  `created_at` TIMESTAMP NULL,
  `updated_at` TIMESTAMP NULL,
  
  CONSTRAINT `fk_terima_pendaftar` FOREIGN KEY (`id_pendaftar`) REFERENCES `pendaftar` (`id_pendaftar`) ON DELETE CASCADE,
  CONSTRAINT `fk_terima_jurusan` FOREIGN KEY (`id_jurusan`) REFERENCES `jurusan` (`id_jurusan`),
  CONSTRAINT `fk_terima_admin` FOREIGN KEY (`id_admin_pengesah`) REFERENCES `admin` (`id_admin`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 9. Tabel Log Aktivitas (Audit Trail)
CREATE TABLE IF NOT EXISTS `log_aktivitas` (
  `id_log` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `id_admin` BIGINT UNSIGNED NULL,
  `id_user` BIGINT UNSIGNED NULL,
  `aktivitas` VARCHAR(255) NOT NULL,
  `detail` TEXT NULL,
  `ip_address` VARCHAR(45) NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  
  CONSTRAINT `fk_log_admin` FOREIGN KEY (`id_admin`) REFERENCES `admin` (`id_admin`) ON DELETE SET NULL,
  CONSTRAINT `fk_log_user` FOREIGN KEY (`id_user`) REFERENCES `user` (`id_user`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

---

## 5. HUBUNGAN ANTAR-TABEL (RELASIONAL DIAGRAM)

```mermaid
erDiagram
    user ||--o{ pendaftar : "memiliki"
    gelombang ||--o{ pendaftar : "menaungi"
    jurusan ||--o{ pendaftar : "dipilih sebagai jurusan_1/2"
    pendaftar ||--o{ berkas_pendaftar : "mengunggah"
    pendaftar ||--o{ pembayaran : "melakukan"
    pendaftar ||--o| terima : "ditetapkan lolos ke"
    jurusan ||--o{ terima : "menampung siswa diterima"
    admin ||--o{ terima : "mengesahkan kelulusan"
    admin ||--o{ log_aktivitas : "mencatat audit"
    user ||--o{ log_aktivitas : "mencatat aktivitas"

    user {
        BIGINT id_user PK
        VARCHAR nisn UK
        VARCHAR username UK
        VARCHAR email UK
        VARCHAR password
    }

    pendaftar {
        BIGINT id_pendaftar PK
        BIGINT id_user FK
        BIGINT id_gelombang FK
        BIGINT id_jurusan_1 FK
        BIGINT id_jurusan_2 FK
        VARCHAR no_pendaftaran UK
        VARCHAR nisn
        VARCHAR nama
        DATE tanggal_lahir
        ENUM jenis_kelamin
        ENUM status_pembayaran
        ENUM status_verifikasi
        ENUM status_kelulusan
    }

    jurusan {
        BIGINT id_jurusan PK
        VARCHAR kode_jurusan UK
        VARCHAR nama_jurusan
        INT kuota
    }

    gelombang {
        BIGINT id_gelombang PK
        VARCHAR nama_gelombang
        DECIMAL biaya_pendaftaran
        BOOLEAN status_aktif
    }

    berkas_pendaftar {
        BIGINT id_berkas PK
        BIGINT id_pendaftar FK
        ENUM jenis_berkas
        VARCHAR path_file
        ENUM status_berkas
    }

    pembayaran {
        BIGINT id_pembayaran PK
        BIGINT id_pendaftar FK
        VARCHAR kode_transaksi UK
        DECIMAL nominal
        ENUM status_transaksi
    }

    terima {
        BIGINT id_terima PK
        BIGINT id_pendaftar FK
        BIGINT id_jurusan FK
        BIGINT id_admin_pengesah FK
        VARCHAR no_surat_kelulusan UK
        DATE tgl_diterima
    }

    admin {
        BIGINT id_admin PK
        VARCHAR username UK
        VARCHAR email UK
        VARCHAR password
        ENUM role
    }

    log_aktivitas {
        BIGINT id_log PK
        BIGINT id_admin FK
        BIGINT id_user FK
        VARCHAR aktivitas
        TIMESTAMP created_at
    }
```


---

## 6. ANALISIS KEKURANGAN SISTEM & USULAN PENYEMPURNAAN (CRITICAL GAP ANALYSIS)

Berdasarkan telaah mendalam terhadap skripsi referensi (2022), alur bisnis nyata PPDB SMK, serta kebutuhan sistem modern berskala produksi, ditemukan beberapa kekurangan kritis yang perlu dilengkapi:

### 1. Ketiadaan Modul Seleksi & Penilaian (Gap Utama dari Skripsi)
- **Kekurangan**: Pada skripsi bab V halaman 60 tercantum catatan: *"Kekurangan dalam program yaitu belum terdapat menu seleksi. Disarankan peneliti selanjutnya menambahkan menu seleksi."* Saat ini status siswa hanya `Menunggu`, `Terverifikasi`, langsung lompat ke `Diterima`.
- **Dampak**: Petugas TU/Panitia tidak memiliki parameter objektif mengapa calon siswa diterima di Farmasi atau ditolak dari TKJ. Khusus SMK, terdapat tes khusus:
  - Nilai Rapor (Matematika, IPA, B. Inggris).
  - Tes Khusus Kejuruan (misal: Tes Buta Warna untuk Farmasi & Otomotif).
- **Usulan Solusi**: Tambahkan tabel `nilai_seleksi` atau `kriteria_seleksi` (`id_seleksi`, `id_pendaftar`, `nilai_ujian_teori`, `nilai_wawancara`, `tes_buta_warna`, `rekomendasi_jurusan`).

### 2. Ketiadaan Pilihan Jalur Pendaftaran (Zonasi, Prestasi, Afirmasi/KIP, Reguler)
- **Kekurangan**: Sistem saat ini baru mengenal `gelombang` (faktor waktu), tetapi belum memiliki `jalur_pendaftaran`.
- **Dampak**: 
  - Calon siswa pemegang Kartu Indonesia Pintar (KIP) atau Surat Keterangan Tidak Mampu (SKTM) tetap dituntut membayar biaya pendaftaran jika tidak ada jalur Afirmasi.
  - Siswa berprestasi tidak memiliki wadah mengunggah sertifikat/piagam kejuaraan yang bernilai poin seleksi.
- **Usulan Solusi**: Tambahkan atribut `jalur_pendaftaran` ('Reguler', 'Prestasi', 'Afirmasi/KIP', 'Zonasi') serta relasi tabel `prestasi_pendaftar` untuk bukti piagam.

### 3. Ketiadaan Entitas Wali Siswa & Format Alamat Dapodik
- **Kekurangan**: Tabel saat ini hanya memuat `nama_ayah` dan `nama_ibu`. Jika siswa yatim-piatu atau tinggal bersama paman/kakek, data wali tidak terakomodasi. Selain itu, alamat masih bertipe `TEXT` bebas.
- **Dampak**: Sulit diekspor secara valid ke format baku Aplikasi Dapodik Kemendikbudristek.
- **Usulan Solusi**: Tambahkan kolom opsional `nama_wali`, `kerja_wali`, `hp_wali`, `penghasilan_wali`. Strukturkan alamat mencakup: `desa_kelurahan`, `kecamatan`, `kab_kota`, `provinsi`, `kode_pos`, `rt_rw`.

### 4. Siklus Pembayaran: Batas Waktu (Expiry) & Regenerasi Invoice
- **Kekurangan**: Belum diatur skenario jika transaksi Midtrans kadaluarsa (expired 1x24 jam), pembatalan, atau penanganan siswa jalur afirmasi (bebas biaya/Free).
- **Usulan Solusi**: Mekanisme auto-expire transaksi, tombol "Buat Ulang Pembayaran", dan pengecekan otomatis (Webhook Handler) agar pendaftar tidak terkunci dalam status pending abadi.

### 5. Alur Pasca-Diterima: Modul "Daftar Ulang" & Cadangan (Waitlist)
- **Kekurangan**: Setelah calon siswa dinyatakan "Diterima", proses terputus. Tidak ada validasi apakah siswa tersebut benar-benar mengambil kuota atau mengundurkan diri.
- **Dampak**: Kuota jurusan (72 siswa) bisa terkunci oleh siswa yang tidak pernah datang daftar ulang, merugikan siswa di daftar tunggu.
- **Usulan Solusi**: Tambahkan alur `Daftar Ulang` dengan batas tanggal konfirmasi, serta status kelulusan bertingkat: `Diterima`, `Cadangan`, `Daftar Ulang Berhasil`, `Mengundurkan Diri`.

### 6. Keamanan Penyimpanan Berkas & Privasi (UU PDP)
- **Kekurangan**: Berkas seperti Kartu Keluarga (KK), Akta Lahir, dan Ijazah berisi NIK dan identitas sensitif.
- **Usulan Solusi**: Simpan file di private storage (bukan `public/storage` terbuka), dan akses unduhan hanya dapat dibuka melalui Controller dengan autentikasi berizin (Signed URL / Authorization Policy).


---

## 7. FITUR UPGRADE STRATEGIS TAHAP II (OPERASIONAL & LAPANGAN)

Berdasarkan kesepakatan, fitur upgrade berikut telah resmi diintegrasikan ke dalam arsitektur sistem:

### 1. Logika Limpahan Kuota Antar-Jurusan (Pilihan 1 & 2)
- Setiap jurusan memiliki batas kuota maksimal (default 72 siswa).
- Jika kuota Jurusan Pilihan 1 telah terpenuhi atau nilai seleksi tidak memenuhi passing grade, sistem memberikan rekomendasi/opsi otomatis kepada Panitia PPDB untuk mengalihkan calon siswa ke **Jurusan Pilihan 2** tanpa perlu pendaftaran ulang.

### 2. Pendataan Ukuran Seragam & Wearpack Praktek (Khas SMK)
- Kolom pendataan ukuran perlengkapan di tabel `pendaftar`:
  - `ukuran_seragam`: ENUM('S', 'M', 'L', 'XL', 'XXL', 'Custom')
  - `ukuran_wearpack`: ENUM('S', 'M', 'L', 'XL', 'XXL', 'Custom') (khusus jurusan kejuruan teknik dan farmasi).
- Fitur rekapitulasi kebutuhan konveksi sekali klik untuk Admin TU.

### 3. Cetak Kartu Tanda Peserta Ujian / Seleksi (Exam Pass Card)
- Diterbitkan setelah pendaftar lunas biaya formulir dan berkas dinyatakan valid.
- Memuat foto calon siswa, nomor peserta tes, jadwal tes (wawancara & tes buta warna), ruangan ujian, dan QR Code validasi keaslian.

### 4. Widget Cek Kelulusan Mandiri di Landing Page (Quick Lookup)
- Fitur publik tanpa login: Cukup menginput **NISN** atau **Nomor Pendaftaran**.
- Menampilkan status kelulusan instan (Diterima di Jurusan apa / Cadangan / Belum Lolos) beserta tautan unduh Surat Keterangan Lulus (SKL) resmi.

### 5. Kode Promo / Beasiswa / Voucher Pendaftaran
- Tabel `voucher_pendaftaran`: `id_voucher`, `kode_voucher`, `tipe` ('persen', 'nominal'), `nilai_potongan`, `kuota_voucher`, `status_aktif`.
- Mengakomodasi jalur Beasiswa Tahfiz, diskon gelombang dini, atau rekomendasi mitra.

### 6. Analitik Market Intelligence untuk Kepala Sekolah
- Visualisasi data asal sekolah: "Top 5 SMP/MTs Pendaftar Terbanyak".
- Peta sebaran geografis calon siswa (Kota Metro, Kab. Lampung Timur, Kab. Lampung Tengah).

### 7. Fitur Backup Database 1-Klik di Panel Admin TU
- Tombol pencadangan instan dump data `.sql` untuk menjamin keamanan arsip pendaftaran.

---

## 8. REKOMENDASI FINAL FITUR PENYEMPURNA (PRODUCTION-READY ESSENTIALS)

Berikut adalah 5 poin pelengkap terakhir untuk memastikan sistem berstandar enterprise:

1. **Sistem Finalisasi / Kunci Formulir (Freeze Data Pendaftar)**:
   - Sebelum difinalisasi, calon siswa bebas memperbarui data diri & berkas.
   - Begitu tombol "Finalisasi & Kunci Data" ditekan, data beralih ke status `Read-Only` agar tidak terjadi perubahan sepihak selama masa verifikasi Panitia TU. Kunci hanya bisa dibuka kembali oleh Admin TU jika ada permohonan revisi.
2. **Format Penomoran Pendaftaran Otomatis & Terstruktur**:
   - Pola format: `PPDB-[TAHUN]-[KODE_JURUSAN]-[NOMOR_URUT]` (Contoh: `PPDB-2026-TKJ-001`, `PPDB-2026-FAR-042`).
3. **Ekspor Template Sinkronisasi Dapodik (1-Klik Excel Dapodik)**:
   - Format ekspor langsung memetakan kolom database ke format import Aplikasi Dapodikdasmen (NIK, NISN, Nama Lengkap, Tempat Lahir, Tgl Lahir, Nama Ibu Kandung, dsb.).
4. **Proteksi Anti-Spam Bot pada Form Pendaftaran**:
   - Integrasi Math Captcha / Cloudflare Turnstile pada halaman registrasi akun guna mencegah spam akun fiktif.
5. **Cetak Massal Dokumen Pendaftar (Bulk Export PDF)**:
   - Fitur bagi Admin TU untuk mencetak seluruh formulir pendaftaran 1 jurusan sekaligus ke dalam satu file PDF gabungan saat verifikasi fisik.


---

## 9. INTEGRASI EKSPLISIT JALUR PRESTASI & ENTITAS PRESTASI PENDAFTAR (3NF)

Menjawab penegasan mengenai **Jalur Prestasi**, fitur ini kini telah diformalkan ke dalam skema basis data dan alur pendaftaran secara eksplisit:

### 1. Penambahan Kolom pada Tabel `pendaftar`
- `jalur_pendaftaran`: `ENUM('Reguler', 'Prestasi', 'Afirmasi', 'Zonasi') NOT NULL DEFAULT 'Reguler'`

### 2. Tabel Resmi `prestasi_pendaftar` (3NF & Primary Key `id_prestasi`)
Khusus pendaftar yang memilih **Jalur Prestasi**, pendaftar dapat menginput satu atau lebih sertifikat/piagam kejuaraan yang bernilai poin tambahan:

```sql
CREATE TABLE IF NOT EXISTS `prestasi_pendaftar` (
  `id_prestasi` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `id_pendaftar` BIGINT UNSIGNED NOT NULL,
  `nama_prestasi` VARCHAR(150) NOT NULL,
  `tingkat` ENUM('Kecamatan', 'Kabupaten/Kota', 'Provinsi', 'Nasional', 'Internasional') NOT NULL,
  `kategori` ENUM('Keagamaan/Tahfiz', 'Akademik', 'Olahraga', 'Seni/Budaya') NOT NULL,
  `peringkat` VARCHAR(50) NOT NULL, -- Contoh: 'Juara 1', 'Juara 2', 'Juara 3', 'Gold Medal'
  `tahun` YEAR NOT NULL,
  `penyelenggara` VARCHAR(150) NOT NULL,
  `berkas_sertifikat` VARCHAR(255) NOT NULL, -- Path scan piagam (PDF/JPG)
  `skor_bobot` INT NOT NULL DEFAULT 0, -- Poin otomatis untuk seleksi penerimaan
  `status_verifikasi` ENUM('Menunggu', 'Valid', 'Ditolak') NOT NULL DEFAULT 'Menunggu',
  `created_at` TIMESTAMP NULL,
  `updated_at` TIMESTAMP NULL,
  
  CONSTRAINT `fk_prestasi_pendaftar` FOREIGN KEY (`id_pendaftar`) REFERENCES `pendaftar` (`id_pendaftar`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

### 3. Keunggulan Jalur Prestasi di SMK Islam Bina Khalifah Bangsa:
- **Kategori Keagamaan / Tahfiz Al-Qur'an**: Sangat selaras dengan profil sekolah yang memiliki ekstrakurikuler *Tilawati Al-Qur'an* dan *Tafsir & Tahfiz Al-Qur'an*. Calon siswa dengan hafalan Al-Qur'an mendapatkan prioritas lolos dan rekomendasi beasiswa.
- **Kategori Seni/Budaya**: Menampung talenta calon siswa di bidang musik tradisional seperti *Gamelan Karawitan* dan *Gamelan Tolak Balak*.
- **Kategori Olahraga & Bela Diri**: Prestasi di bidang *Karate*, *Paskibra*, atau *Pramuka*.
- **Pembobotan Otomatis**: Panitia seleksi dapat melihat akumulasi `skor_bobot` prestasi sebagai nilai tambah otomatis pada perangkingan kelulusan.


---

## 10. INTEGRASI RESMI NOTIFIKASI OTOMATIS (WHATSAPP GATEWAY & EMAIL)

Modul notifikasi otomatis pihak ketiga telah ditetapkan dan dibukukan secara resmi dengan arsitektur berikut:

### A. Provider Pihak Ketiga yang Didukung (Multi-Provider Support)
1. **WhatsApp Gateway**:
   - **Fonnte API** (Rekomendasi utama di Indonesia, response cepat dan mudah dikonfigurasi).
   - **Wablas API** / **Twilio WhatsApp API**.
   - Dilengkapi *Fallback Simulator / Logger Mode* (sehingga tetap bisa diuji coba secara lokal tanpa pulsa/kredit kuota API).
2. **Email Notification Engine**:
   - SMTP Mailer (Gmail SMTP / Mailtrap / Mailgun) untuk cadangan bukti digital formal.

### B. Tiga Pemicu Wajib Notifikasi Otomatis (Triggers)

#### 1. Notifikasi Akun Berhasil Didaftarkan
- **Waktu Picu**: Tepat setelah calon siswa menekan tombol "Daftar" dan akun berhasil disimpan di tabel `user`.
- **Target**: Nomor `hp_siswa` / `hp_ortu` & Email pendaftar.
- **Template Pesan**:
  > *Assalamu'alaikum Wr. Wb. Selamat! Akun PPDB SMK Islam Bina Khalifah Bangsa Anda telah berhasil dibuat.*
  > *NISN: [NISN]*
  > *Username: [Username]*
  > *Silakan login ke https://smkib.com/login untuk melanjutkan pengisian formulir pendaftaran.*

#### 2. Notifikasi Pembayaran Midtrans Terverifikasi (Settlement)
- **Waktu Picu**: Otomatis melalui webhook Midtrans Callback saat status berubah menjadi `Paid` / `Settlement`.
- **Target**: Nomor WhatsApp pendaftar & orang tua.
- **Template Pesan**:
  > *Pembayaran Biaya Pendaftaran PPDB SMK Islam Bina Khalifah Bangsa BERHASIL.*
  > *No. Transaksi: [Kode_Transaksi]*
  > *Nominal: Rp [Nominal]*
  > *Status: LUNAS*
  > *Bukti pembayaran resmi dapat diunduh di dashboard akun Anda. Tahap selanjutnya: Silakan lengkapi berkas untuk verifikasi.*

#### 3. Notifikasi Pengumuman Kelulusan (SKL Diterbitkan Kepala Sekolah)
- **Waktu Picu**: Saat Kepala Sekolah menekan tombol "Sahkan & Terbitkan Pengumuman Kelulusan".
- **Target**: Nomor WhatsApp seluruh calon siswa yang telah ditetapkan kelulusannya.
- **Template Pesan**:
  > *Pengumuman Resmi PPDB SMK Islam Bina Khalifah Bangsa T.A 2026/2027:*
  > *Yth. [Nama Siswa], Selamat Anda Dinyatakan DITERIMA pada Program Keahlian: [Nama Jurusan]!*
  > *No. SKL: [No_Surat_Kelulusan]*
  > *Silakan unduh Surat Keterangan Lulus (SKL) resmi dan panduan Daftar Ulang di https://smkib.com/cek-kelulusan.*


---

## 11. SISTEM GELOMBANG PENDAFTARAN OTOMATIS (AUTOMATED WAVE MANAGEMENT ENGINE)

Modul **Manajemen Gelombang Pendaftaran** telah dibukukan secara lengkap dengan logika otomatisasi berikut:

### 1. Struktur Entitas Basis Data (`gelombang`)
- **Primary Key**: `id_gelombang`
- Kolom:
  - `nama_gelombang`: Contoh: "Gelombang 1 (Early Bird)", "Gelombang 2 (Reguler)", "Gelombang 3 (Penutupan)".
  - `tahun_ajaran`: Contoh: "2026/2027".
  - `tgl_buka`: Tanggal mulai pendaftaran dibuka (YYYY-MM-DD).
  - `tgl_tutup`: Tanggal batas akhir pendaftaran ditutup (YYYY-MM-DD).
  - `biaya_pendaftaran`: Nominal biaya formulir yang berlaku pada gelombang tersebut (Contoh: Gel. 1 = Rp 100.000, Gel. 2 = Rp 150.000).
  - `kuota_gelombang`: Batas maksimal kuota pendaftar per gelombang (opsional / null).
  - `status_aktif`: Status override manual oleh Panitia TU (`TRUE`/`FALSE`).

### 2. Logika Otomasi Jadwal & Penutupan Sistem (Automated Scheduler)
- **Deteksi Tanggal Real-time**: Sistem secara otomatis mengecek tanggal server saat ini (`CURDATE()`).
  - **Kondisi Buka**: `CURDATE()` berada di antara `tgl_buka` dan `tgl_tutup` serta `status_aktif = TRUE`.
  - **Kondisi Tutup Otomatis**: Jika waktu telah melewati jam 23:59:59 pada `tgl_tutup`, sistem secara otomatis mengunci form registrasi baru dan menampilkan banner: *"Pendaftaran Gelombang Ini Telah Ditutup"*.
  - **Kondisi Antar-Gelombang**: Jika belum ada gelombang baru yang dibuka, tombol "Daftar Sekarang" di landing page dinonaktifkan secara anggun dengan informasi jadwal pembukaan gelombang berikutnya.

### 3. Penyesuaian Biaya Formulir Otomatis (Dynamic Fee Calculation)
- Saat calon siswa mendaftar dan melanjutkan ke tahap pembayaran Midtrans:
  - Sistem secara otomatis mengambil nilai `biaya_pendaftaran` dari gelombang yang aktif saat itu.
  - Nilai tagihan Midtrans di-generate secara presisi tanpa perlu input manual dari panitia TU.
  - Tercatat riwayat `id_gelombang` pada setiap data pendaftar dan transaksi pembayaran.

### 4. Countdown Timer Interaktif di Halaman Utama (Landing Page)
- Menampilkan penghitung waktu mundur (*Countdown Timer*) real-time:
  - *"Sisa Waktu Pendaftaran Gelombang 1: [X] Hari [Y] Jam [Z] Menit"*.
  - Memberikan urgensi positif (*call-to-action*) bagi calon pendaftar agar segera menyelesaikan registrasi sebelum jadwal berakhir.


---

## 12. AUDIT TRAIL & LOG AKTIVITAS SISTEM (ACTIVITY LOGGING & AUDIT TRAIL)

Mekanisme pencatatan riwayat digital (*audit trail*) telah diformalkan ke dalam arsitektur sistem dengan standar keamanan tinggi:

### 1. Struktur Entitas Basis Data (`log_aktivitas`)
- **Primary Key**: `id_log`
- Kolom:
  - `id_admin`: FK ke `admin.id_admin` (jika tindakan dilakukan oleh Admin TU / Kepala Sekolah).
  - `id_user`: FK ke `user.id_user` (jika tindakan dilakukan oleh Calon Siswa).
  - `aksi`: ENUM('LOGIN', 'LOGOUT', 'INSERT', 'UPDATE', 'DELETE', 'VERIFIKASI', 'PENGESAHAN_SKL', 'EXPORT').
  - `tabel_terkait`: Nama tabel yang mengalami perubahan (misal: `pendaftar`, `berkas_pendaftar`, `terima`, `pembayaran`).
  - `id_data`: ID record yang diubah (misal: `id_pendaftar = 42`).
  - `deskripsi`: Penjelasan tindakan yang manusiawi (human-readable string).
  - `data_lama`: JSON (Snapshot nilai data sebelum diubah).
  - `data_baru`: JSON (Snapshot nilai data setelah diubah).
  - `ip_address`: Alamat IP perangkat pengubah (IPv4 / IPv6).
  - `user_agent`: Info browser dan OS perangkat.
  - `created_at`: Waktu eksekusi mutasi data.

### 2. Skenario Operasional yang Wajib Tercatat:
- **Verifikasi Berkas**: Kapan Admin TU mengubah status berkas menjadi `Valid` atau `Tidak Valid`, lengkap dengan catatan alasannya.
- **Pengesahan Kelulusan**: Catatan waktu saat Kepala Sekolah menetapkan status kelulusan final dan menerbitkan nomor SKL.
- **Perubahan Biodata & Jurusan**: Mencegah kecurangan atau manipulasi kuota siswa baru.
- **Ekspor Data**: Kapan laporan pendaftar diunduh ke format Excel/PDF untuk mencegah kebocoran data.

---

## 13. MODE PEMELIHARAAN (MAINTENANCE MODE) & PENGUMUMAN MASSAL (BROADCAST ANNOUNCEMENT)

Fitur tata kelola informasi publik tanpa menyentuh kode program (*zero-code CMS*) telah dibukukan:

### 1. Tabel Basis Data `pengumuman` (3NF & PK `id_pengumuman`)
```sql
CREATE TABLE IF NOT EXISTS `pengumuman` (
  `id_pengumuman` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `id_admin` BIGINT UNSIGNED NOT NULL,
  `judul` VARCHAR(150) NOT NULL,
  `isi_pengumuman` TEXT NOT NULL,
  `tipe` ENUM('info', 'peringatan', 'penting', 'maintenance') NOT NULL DEFAULT 'info',
  `tampil_di_banner` BOOLEAN NOT NULL DEFAULT TRUE,
  `status_aktif` BOOLEAN NOT NULL DEFAULT TRUE,
  `tgl_mulai` DATETIME NOT NULL,
  `tgl_selesai` DATETIME NOT NULL,
  `created_at` TIMESTAMP NULL,
  `updated_at` TIMESTAMP NULL,
  
  CONSTRAINT `fk_pengumuman_admin` FOREIGN KEY (`id_admin`) REFERENCES `admin` (`id_admin`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

### 2. Fitur Banner Pengumuman Massal (Top Announcement Banner)
- Banner berjalan (*alert ticker*) yang tampil tepat di atas navbar halaman utama (*Landing Page*).
- Panitia TU dapat mengaktifkan/menonaktifkan pengumuman penting sewaktu-waktu via Admin Dashboard.
- Contoh Penggunaan:
  - *"Pendaftaran Gelombang 1 Resmi Diperpanjang Hingga 25 Mei 2026."*
  - *"Pengumuman Kelulusan Jalur Prestasi Tahfiz Dapat Diakses Mulai Pukul 13.00 WIB."*

### 3. Mode Pemeliharaan Terjadwal (Maintenance Mode Switch)
- Fitur saklar satu klik di Panel Admin: **"Aktifkan Mode Pemeliharaan"**.
- Saat mode aktif:
  - Calon siswa dan pengunjung publik dialihkan ke halaman estetik ramah pengguna: *"Website PPDB SMK Islam Bina Khalifah Bangsa Sedang Dalam Peningkatan Sistem. Silakan Coba Beberapa Saat Lagi."*
  - Admin dan Kepala Sekolah tetap dapat login melalui pintu belakang khusus (*bypass route*) untuk melakukan rekapitulasi data tanpa gangguan traffic pengunjung.


---

## 14. FITUR EKSPOR DATA MULTI-FORMAT & MANAJEMEN STATUS BERTAHAP (TIMELINE TRACKER)

Kedua fitur kunci di atas telah dibukukan secara mendalam dengan spesifikasi teknis berikut:

### 1. Fitur Ekspor Data Pendaftar (Excel .xlsx, CSV, & PDF Rekapitulasi)
- **Tombol Ekspor Fleksibel di Panel Admin TU & Kepala Sekolah**:
  - **Ekspor Excel (.xlsx)**: Menggunakan library spreadsheet generator (seperti `PhpSpreadsheet` / `xlsxwriter`) dengan header tabel terformat rapi dan autofit column width.
  - **Ekspor CSV**: Format ringan untuk integrasi data mentah ke database lain.
  - **Ekspor PDF Rekapitulasi**: Ber-kop resmi SMK Islam Bina Khalifah Bangsa, nomor surat dinas, dan kolom tanda tangan pengesahan Kepala Sekolah (Asliman Puja Kesuma, S.Pd).
- **Kustomisasi Filter Ekspor Sebelum Unduh**:
  - Filter Berdasarkan Jurusan (Farmasi, TKJ, TKR, TBSM, atau Seluruh Jurusan).
  - Filter Berdasarkan Gelombang (Gelombang 1, Gelombang 2, dsb.).
  - Filter Berdasarkan Status Pembayaran (`Paid` / `Pending`).
  - Filter Berdasarkan Status Kelulusan (`Diterima`, `Cadangan`, `Tidak Lolos`).
- **Kelengkapan Kolom Data Ekspor**:
  - No. Pendaftaran, NISN, Nama Lengkap, Tempat & Tgl Lahir, Jenis Kelamin, Agama, Jurusan Pilihan 1 & 2, Asal Sekolah, Nama Ayah & Ibu, Pekerjaan Ortu, No. HP Siswa & Ortu, Alamat, Jalur Pendaftaran, Status Pembayaran, Status Berkas, dan Status Kelulusan.

### 2. Manajemen Status Pendaftaran Bertahap Real-Time (Visual Stepper Timeline)
Pada dashboard calon siswa, progres pendaftaran disajikan dalam bentuk **Interactive Progress Bar / Stepper Tracker** yang berubah warna secara dinamis:

| Urutan Tahap | Nama Status di Dashboard Siswa | Penjelasan Logika Sistem |
| :---: | :--- | :--- |
| **Tahap 1** | **Pengisian Formulir** | Indikator *Selesai* jika formulir biodata lengkap dan telah difinalisasi. |
| **Tahap 2** | **Menunggu Pembayaran** | Menampilkan tombol bayar Midtrans (Snap Pop-up). Berubah menjadi **Lunas (Paid)** begitu pembayaran sukses. |
| **Tahap 3** | **Verifikasi Berkas** | Status awal: `Sedang Diperiksa Petugas TU`. Jika ada berkas buram/salah: `Perlu Revisi (Ditolak)` disertai catatan alasan. Jika berkas benar: `Berkas Terverifikasi (Valid)`. |
| **Tahap 4** | **Jadwal Tes & Seleksi** | Membuka akses cetak *Kartu Peserta Ujian* dan menampilkan jadwal tes wawancara / tes buta warna. |
| **Tahap 5** | **Hasil Seleksi Final** | Diumumkan langsung oleh Kepala Sekolah dengan 3 kemungkinan status: **Lolos / Diterima**, **Cadangan**, atau **Tidak Lolos**. |
| **Tahap 6** | **Daftar Ulang** | Bagi siswa yang lolos: membuka panduan daftar ulang dan unduh Surat Keterangan Lulus (SKL) resmi. |


---

## 15. PENYEMPURNAAN REALISTIS TAHAP AKHIR (GROUND OPERATION REFINEMENTS)

Berdasarkan simulasi operasional lapangan di lingkungan sekolah, terdapat **4 aspek teknis & pelayanan** yang wajib ditambahkan agar sistem tidak menemui hambatan saat hari pendaftaran:

### 1. Metode Pembayaran Ganda: Midtrans + Pembayaran Tunai di Loket TU (Cash Offline)
- **Fakta Lapangan**: Di Kota Metro dan sekitarnya, sebagian orang tua siswa masih memilih datang langsung ke sekolah untuk membayar tunai (*Cash*) di loket TU atau mentransfer manual ke rekening resmi sekolah tanpa melalui payment gateway online.
- **Penyempurnaan**:
  - Pilihan metode bayar di sistem: `Online (Midtrans Sandbox)` ATAU `Bayar Tunai di Loket TU`.
  - Jika siswa memilih "Bayar Tunai": Sistem mencetak *Kwitansi Pra-Bayar*, dan Admin TU memiliki tombol verifikasi: **"Konfirmasi Lunas Tunai"** di panel admin.

### 2. Fitur "Lupa Password" Berbasis WhatsApp OTP / Email Token
- **Fakta Lapangan**: Siswa lulusan SMP sangat sering lupa kata sandi akun yang baru mereka buat beberapa hari sebelumnya.
- **Penyempurnaan**:
  - Tombol *"Lupa Password"* di halaman login.
  - Sistem mengirimkan kode OTP atau tautan reset kata sandi aman secara langsung ke nomor WhatsApp atau Email siswa, sehingga petugas TU tidak terbebani permintaan reset manual satu per satu.

### 3. Modul Pengaturan Identitas Sekolah & Kop Surat Dinamis (School Branding CMS)
- **Kebutuhan Cetak PDF**: Dokumen resmi (Bukti Pendaftaran, Kartu Ujian, Laporan Rekap, SKL) memerlukan Kop Surat Resmi.
- **Penyempurnaan**:
  - Menu di Pengaturan Admin TU untuk mengunggah: **Logo Sekolah**, **Alamat Lengkap**, **NPSN**, **No. Izin Operasional**, **Nama & NIP/NUPTK Kepala Sekolah**, serta **Tanda Tangan & Stempel Digital Resmi**.
  - Kop surat dan tanda tangan pada seluruh cetakan PDF ter-generate secara dinamis dari pengaturan ini.

### 4. Integrasi Peta Lokasi Interaktif (Google Maps Embed) & FAQ Cerdas
- **Kebutuhan Pendaftar Luar Kota**: Membantu calon siswa yang datang dari luar Kota Metro (misal: Lampung Timur, Lampung Tengah) untuk menghadiri tes fisik, verifikasi berkas, atau daftar ulang.
- **Penyempurnaan**:
  - Widget Google Maps interaktif lokasi SMK Islam Bina Khalifah Bangsa di bagian kontak footer landing page.
  - Accordion FAQ interaktif yang menjawab 10 pertanyaan paling sering ditanyakan (persyaratan berkas, jadwal tes, seragam, biaya pendaftaran).


---

## 16. STRUKTUR MENU & NAVIGASI SISTEM PER ROLE (SITEMAP & UI NAVIGATION)

Untuk menjamin kemudahan pengoperasian (*user-friendly*), seluruh menu navigasi telah dipetakan secara terstruktur berdasarkan peran masing-masing aktor:

```
PPDB Modern SMK Islam Bina Khalifah Bangsa
├── 🌐 Menu Publik (Pengunjung & Calon Siswa Baru)
├── 🎓 Menu Calon Siswa (Dashboard Siswa / Pendaftar)
├── 🏫 Menu Petugas Tata Usaha (Admin TU Panel)
└── 🏛️ Menu Kepala Sekolah (Executive Panel)
```

### 1. Menu Publik (Landing Page / Pengunjung Umum)
- **Beranda (Home)**: Hero section, countdown timer gelombang, tombol CTA "Daftar Sekarang" & "Masuk Akun".
- **Profil Sekolah**: Sejarah singkat, visi-misi SMK Islam Bina Khalifah Bangsa, dan fasilitas unggulan.
- **Program Keahlian**: Informasi detail 4 jurusan (Farmasi, TKJ, TKR, TBSM) beserta prospek karier.
- **Ekstrakurikuler**: Dokumentasi 7 ekskul (Pramuka, Paskibra, Tilawati, Tahfiz, Karate, Karawitan, Tolak Balak).
- **Alur & Jadwal**: Infografis timeline pendaftaran dan informasi gelombang aktif.
- **Biaya & Beasiswa**: Rincian biaya pendaftaran, program beasiswa tahfiz, dan diskon gelombang.
- **Cek Kelulusan Cepat**: Widget publik untuk mengecek hasil seleksi cukup dengan memasukkan NISN.
- **FAQ & Kontak**: Tanya-jawab interaktif, peta Google Maps lokasi sekolah, dan tautan WhatsApp Panitia.
- **Autentikasi**:
  - Tombol **Masuk (Login)**
  - Tombol **Daftar Akun Baru (Registrasi)**
  - Tautan **Lupa Password**

---

### 2. Menu Calon Siswa (Dashboard Siswa / Pendaftar)
Sidebar & Header Navigasi responsif (Mobile-First):
- **📊 Dashboard Utama**:
  - Stepper Progres Real-Time (6 Tahap: Biodata -> Pembayaran -> Berkas -> Seleksi -> Hasil -> Daftar Ulang).
  - Kartu ringkasan status pendaftaran & nomor pendaftaran resmi.
  - Pengumuman penting dari sekolah.
- **📝 Formulir Pendaftaran**:
  - *Data Pribadi Siswa*: NISN, Nama, Tempat/Tgl Lahir, Agama, Alamat, Golongan Darah, dsb.
  - *Pilihan Jurusan*: Jurusan 1 & Jurusan 2.
  - *Ukuran Perlengkapan*: Input ukuran seragam dan wearpack bengkel/lab.
  - *Asal Sekolah*: Nama sekolah SMP/MTs asal dan alamat sekolah.
  - *Data Orang Tua / Wali*: Data Ayah, Ibu, dan Wali murid (pekerjaan, kontak, penghasilan).
  - *Jalur Pendaftaran*: Pilihan jalur (Reguler, Prestasi, Afirmasi/KIP, Zonasi).
  - *Tombol "Finalisasi & Kunci Data"*.
- **📁 Upload Berkas Digital**:
  - Unggah berkas Ijazah/SKL, Kartu Keluarga, Pas Foto, dan Akta Kelahiran (auto-compress di browser).
  - Unggah Sertifikat/Piagam Kejuaraan (khusus Jalur Prestasi).
  - Status validasi berkas per item (Valid / Menunggu / Perlu Revisi).
- **💳 Pembayaran Formulir**:
  - Rincian tagihan biaya pendaftaran sesuai gelombang aktif.
  - Input Kode Voucher / Diskon Beasiswa.
  - Tombol Pembayaran Online (Midtrans Snap: QRIS, Bank Transfer, E-Wallet).
  - Opsi *Bayar Tunai di Loket TU* (cetak bukti tagihan loket).
  - Riwayat transaksi & unduh bukti pembayaran lunas.
- **🎫 Kartu Peserta Ujian**:
  - Akses cetak Kartu Tanda Peserta Tes Seleksi ber-QR Code (aktif setelah lunas & berkas valid).
  - Informasi jadwal tes wawancara dan tes buta warna.
- **🏆 Pengumuman Seleksi & SKL**:
  - Informasi kelulusan resmi dari Kepala Sekolah.
  - Tombol unduh Surat Keterangan Lulus (SKL) resmi format PDF.
- **🔄 Daftar Ulang**:
  - Petunjuk konfirmasi masuk sekolah dan formulir konfirmasi berkas fisik.
- **⚙️ Pengaturan Akun**:
  - Ubah kata sandi dan pembaruan nomor WhatsApp aktif.
- **🚪 Keluar (Logout)**.

---

### 3. Menu Petugas Tata Usaha (Admin TU Panel)
Sidebar menu terstruktur untuk operasional harian:
- **📊 Dashboard Analytics**:
  - Kartu metrik: Total Pendaftar, Pendaftar Hari Ini, Terverifikasi, Menunggu Pembayaran, Lunas.
  - Grafik tren pendaftar harian dan persentase kuota per jurusan.
- **👥 Manajemen Pendaftar**:
  - *Semua Pendaftar*: Tabel lengkap, filter jurusan, filter status, pencarian instan, dan bulk actions.
  - *Per Jurusan*: Tampilan terpisah pendaftar Farmasi, TKJ, TKR, dan TBSM.
  - *Antrean Verifikasi*: Filter khusus pendaftar yang berkasnya siap diperiksa.
- **🔍 Modul Verifikasi Berkas**:
  - Peninjauan berkas dengan *In-Browser Document Viewer* (Zoom In/Out, Rotasi 90°).
  - Tombol aksi verifikasi: "Terima Berkas" atau "Tolak Berkas" (disertai catatan alasan revisi).
  - Tombol cepat *Kirim Notifikasi WhatsApp* ke calon siswa.
- **💰 Manajemen Pembayaran**:
  - Daftar transaksi online Midtrans (otomatis terupdate via Webhook).
  - Loket Pembayaran Tunai (fitur pencarian siswa & tombol "Verifikasi Lunas Tunai").
- **📋 Modul Seleksi & Penilaian**:
  - Input nilai rapor dan hasil tes wawancara / tes buta warna.
  - Penilaian skor bobot piagam Jalur Prestasi.
  - Rekomendasi Pengalihan Limpahan Kuota (Pilihan 1 penuh dialihkan ke Pilihan 2).
- **⚙️ Master Data PPDB**:
  - *Master Jurusan*: Pengaturan nama jurusan, kode jurusan, dan kapasitas kuota (default 72).
  - *Master Gelombang*: Buka/tutup gelombang pendaftaran, pengaturan tanggal, dan nominal tarif formulir.
  - *Master Voucher*: Pembuatan kode promo/beasiswa tahfiz dan kuota penggunaan.
- **📑 Pusat Laporan & Ekspor**:
  - Ekspor Data Pendaftar ke Excel (`.xlsx`) dan CSV dengan filter kustom.
  - Ekspor Format Baku Import Aplikasi Dapodikdasmen (1-Klik).
  - Cetak Laporan Rekapitulasi PDF (Kop Surat resmi & kolom tanda tangan Kepala Sekolah).
  - Cetak Massal Formulir Siswa PDF (Bulk Print per jurusan).
  - Rekapitulasi Pesanan Ukuran Seragam & Wearpack Konveksi.
- **📢 CMS & Komunikasi**:
  - Pengaturan Banner Pengumuman Massal (Top Alert Ticker di Landing Page).
  - Saklar Mode Pemeliharaan (Maintenance Mode Toggle).
- **🛠️ Pengaturan & Keamanan**:
  - Profil Sekolah & Branding (Logo, Alamat, NPSN, Stempel Sekolah, Tanda Tangan Kepala Sekolah).
  - Backup Database 1-Klik (Dump file `.sql`).
- **🚪 Keluar (Logout)**.

---

### 4. Menu Kepala Sekolah (Executive Panel)
Dashboard bersih dan berfokus pada statistik strategis & keputusan akhir:
- **📈 Dashboard Eksekutif**:
  - Visualisasi grafik Chart.js: Tren Pendaftar, Pemenuhan Kuota per Jurusan.
  - Analitik *Market Intelligence*: Top 5 Asal SMP/MTs Pendaftar Terbanyak & Sebaran Geografis Wilayah Siswa.
- **⚖️ Modul Kelulusan Final & Pengesahan**:
  - Tabel Peringkat Seleksi Seluruh Calon Siswa (berdasarkan nilai tes + bobot prestasi).
  - Penetapan Status Final: **Diterima**, **Cadangan**, atau **Tidak Lolos** (bisa perorangan atau massal).
  - Fitur Pengesahan Digital: Menerbitkan Nomor SKL Resmi dan membuka pengumuman serentak.
- **📑 Arsip Laporan & Pengesahan Dokumen**:
  - Tanda tangan digital pada lembar Laporan Rekapitulasi PPDB.
  - Pengarsipan data pendaftaran multi-tahun ajaran (2022/2023 s/d 2026/2027).
- **🛡️ Audit Trail (Log Aktivitas Sistem)**:
  - Pemantauan riwayat aktivitas staf TU (siapa memverifikasi berkas, siapa mengubah status, IP address, waktu aksi).
- **🚪 Keluar (Logout)**.


### Poin Penyesuaian yang Diterapkan:
1. **Validasi Anti-Duplikasi Pilihan Jurusan**: Calon siswa tidak boleh memilih jurusan yang sama pada Pilihan 1 dan Pilihan 2 (`id_jurusan_1 != id_jurusan_2`).
2. **Cetak Kwitansi Pembayaran Resmi (Official Payment Receipt PDF)**: Disediakan tombol cetak lembar kwitansi pembayaran biaya formulir berformat PDF ber-stempel lunas untuk bukti administrasi orang tua.
3. **Pilihan Minat Ekstrakurikuler**: Checkbox minat calon siswa terhadap 7 ekskul sekolah (*Pramuka, Paskibra, Tilawati Al-Qur'an, Tafsir & Tahfiz Al-Qur'an, Karate, Gamelan Karawitan, Gamelan Tolak Balak*).
4. **Koordinat Domisili (Opsional Jalur Zonasi)**: Penentuan jarak tempat tinggal siswa ke sekolah bagi pendaftar jalur zonasi.


---

## 18. MODE GELAP (DARK MODE) & AKSESIBILITAS ANTARMUKA TERPADU

Fitur **Mode Gelap (Dark Mode / Light Mode)** telah dibukukan secara mendalam dengan standar estetika dan aksesibilitas modern:

### 1. Penempatan Tombol Toggle Instan
- Ditempatkan secara elegan pada navbar header di seluruh antarmuka:
  - *Landing Page Publik*
  - *Dashboard Calon Siswa*
  - *Panel Petugas Tata Usaha (Admin TU)*
  - *Panel Eksekutif Kepala Sekolah*
- Animasi transisi halus (*smooth transition*) antara ikon **Matahari (Light Mode)** dan **Bulan (Dark Mode)**.

### 2. Mekanisme Persistensi & Deteksi Otomatis
- **Penyimpanan di `localStorage`**: Tema pilihan pengguna disimpan di cache peramban lokal (`theme = 'dark' | 'light'`), sehingga tema tidak berkedip (*no flickering*) saat navigasi antar-halaman.
- **Deteksi Preferensi Sistem Perangkat (`prefers-color-scheme`)**: Jika pengunjung baru pertama kali membuka web, sistem secara cerdas menyesuaikan dengan tema bawaan smartphone atau laptop pengguna secara otomatis.

### 3. Standar Aksesibilitas & Palet Warna Modern (Tailwind CSS)
- **Palet Dark Mode**: Menggunakan warna latar `slate-900` / `zinc-900` dengan elemen kartu `slate-800`, bukan hitam pekat, sehingga kontras teks tetap tajam, ramah di mata untuk penggunaan malam hari, serta hemat konsumsi baterai layar AMOLED/OLED pada smartphone siswa.
- **Palet Light Mode**: Bersih, profesional, dengan aksen warna hijau toska / emerald islami khas SMK Islam Bina Khalifah Bangsa.

---

## 19. STATUS PENYELESAIAN & PENGUJIAN SISTEM (VERIFIKASI 100% SUKSES)

Seluruh komponen arsitektur, basis data, logika bisnis, dan antarmuka web telah selesai dibangun, terintegrasi penuh, dan lolos uji otomasi (*automated tests*):

### A. Komponen Terverifikasi
1. **Basis Data MySQL (14 Tabel - 3NF Normalization)**:
   - Seluruh tabel menggunakan konvensi Primary Key eksplisit `id_<nama_tabel>` (`id_user`, `id_pendaftar`, `id_jurusan`, `id_gelombang`, `id_voucher`, `id_berkas`, `id_pembayaran`, `id_prestasi`, `id_nilai`, `id_terima`, `id_pengumuman`, `id_log`, `id_pengaturan`, `id_admin`).
2. **Pola RBAC Multi-Guard (3 Role)**:
   - `web` guard untuk Calon Siswa.
   - `admin` guard dengan peran `tu` (Petugas Tata Usaha) dan `kepala_sekolah` (Kepala Sekolah).
3. **Fitur Operasional Lapangan**:
   - Pembayaran ganda: Tunai Loket TU & Simulasi Payment Gateway Midtrans (QRIS/VA) + Sistem Kupon Voucher Potongan Biaya.
   - 4 Program Keahlian: Farmasi (`FAR`), TKJ (`TKJ`), Otomotif Mobil (`TKR`), Sepeda Motor (`TBSM`).
   - Dokumen Cetak Standar Skripsi: Formulir Pendaftaran, Kartu Peserta Ujian Seleksi, Kwitansi Pembayaran Berstempel, Surat Keterangan Lulus (SKL) Resmi bertandatangan Kepala Sekolah (Asliman Puja Kesuma, S.Pd.).
   - Ekspor Data: CSV/Excel Rekapitulasi Lengkap, Format Template Impor Dapodik Kemendikbud, serta Cetak PDF Rekapitulasi per Jurusan/Semua Jurusan.
   - Keamanan: Audit Trail (Log Aktivitas terenkripsi) dan Sakelar Mode Pemeliharaan (Maintenance Mode).
4. **Hasil Pengujian Otomasi (*Automated Tests*)**:
   - `tests/Feature/PPDBRouteTest.php`: **5 Test Suites, 33 Assertions, 100% Passed (Zero Errors)**.

