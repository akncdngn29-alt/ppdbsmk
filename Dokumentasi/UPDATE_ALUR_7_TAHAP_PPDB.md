# LAPORAN PEMBARUAN: ALUR PROGRES 7 TAHAP PENDAFTARAN PPDB ONLINE
**SMK ISLAM BINA KHALIFAH BANGSA KOTA METRO**  
*Tanggal: 5 November 2026*  
*Dokumen: `UPDATE_ALUR_7_TAHAP_PPDB.md`*

---

## 1. RINGKASAN EKSEKUTIF

Berdasarkan kebutuhan operasional sistem PPDB, telah dilakukan penyesuaian progres alur pendaftaran calon siswa dari sebelumnya **6 tahap** menjadi **7 tahap** terstruktur. Pembaruan ini secara spesifik memisahkan **Upload Berkas** (tanggung jawab pemenuhan berkas oleh calon siswa) dari **Verifikasi** (proses audit kelayakan dokumen oleh Petugas Tata Usaha/TU). 

Seluruh status tahap kini mencerminkan data aktual dari database secara real-time tanpa saling tertukar. Seluruh fitur pendaftaran, sistem upload, otentikasi multi-role, struktur database, serta alur pendaftaran yang telah berjalan tetap dipertahankan utuh dan lulus pengujian 100%.

---

## 2. STRUKTUR DAN DEFINISI 7 TAHAP PENDAFTARAN

| No | Nama Tahap | Penanggung Jawab | Sumber Data Aktual Database | Kriteria Indikator / Status |
|---|---|---|---|---|
| **1** | **Formulir** | Calon Siswa | `pendaftar.is_locked` | **Belum Isi** (belum ada data) / **Draft** (`is_locked = false`) / **Terkunci** (`is_locked = true`) |
| **2** | **Pembayaran** | Calon Siswa & Loket/Midtrans | `pendaftar.status_pembayaran` | **Pending** (menunggu checkout/pembayaran) / **Lunas** (`Paid`) |
| **3** | **Upload Berkas** | Calon Siswa | `berkas_pendaftar` (relasi `pendaftar.berkas`) | Kelengkapan 4 dokumen wajib: Ijazah, KK, Pas Foto, Akta (**0/4** s.d. **4/4 Dokumen**) |
| **4** | **Verifikasi** | Petugas Tata Usaha (TU) | `pendaftar.status_verifikasi` | Hasil pemeriksaan dokumen fisik/digital oleh TU: **Menunggu** / **Terverifikasi** / **Ditolak** |
| **5** | **Tes Seleksi** | Panitia Seleksi & Calon Siswa | `pendaftar.nilaiSeleksi` | **Siap Tes** (jadwal & cetak kartu ujian tersedia) / **Selesai** (nilai tes telah diinput) |
| **6** | **Kelulusan** | Kepala Sekolah (Sidang Pleno) | `pendaftar.status_kelulusan` | Hasil keputusan Kepala Sekolah: **Diproses** / **Diterima** / **Cadangan** / **Tidak Lolos** |
| **7** | **Daftar Ulang** | Calon Siswa & Petugas TU | `pendaftar.status_daftar_ulang` | **Konfirmasi** (menunggu konfirmasi siswa lolos) / **Resmi Siswa** (`Selesai`) |

---

## 3. PEMISAHAN STATUS: UPLOAD BERKAS VS VERIFIKASI

Sesuai instruksi, kedua tahap ini dipisahkan secara tegas agar tidak ambigu dan tidak saling tertukar:

1. **Tahap 3 - Upload Berkas (Kelengkapan Dokumen):**
   - **Fokus:** Mengukur apakah calon siswa telah mengunggah seluruh dokumen persyaratan wajib (`ijazah`, `kk`, `pasfoto`, `akta`).
   - **Tampilan Status:** Menampilkan rasio kelengkapan aktual, contohnya: `0/4 Dokumen`, `2/4 Dokumen`, atau `4/4 Dokumen`.
   - **Kondisi Selesai:** Tahap dianggap terpenuhi ketika seluruh 4 dokumen wajib berhasil diunggah (`uploadedDocsCount >= 4`).

2. **Tahap 4 - Verifikasi (Pemeriksaan Dokumen oleh Petugas TU):**
   - **Fokus:** Hasil penilaian keabsahan dan keaslian dokumen oleh Petugas TU melalui panel verifikasi Admin TU.
   - **Tampilan Status:** `Menunggu` (sedang dalam antrean periksa), `Terverifikasi` (dokumen sah & lengkap), atau `Ditolak` (dokumen buram/tidak sesuai, disertai catatan revisi dari TU).
   - **Kondisi Selesai:** Siswa dapat melanjutkan ke tahap berikutnya (Tes Seleksi) hanya setelah status verifikasi berubah menjadi `Terverifikasi`.

---

## 4. PENYESUAIAN URUTAN MENU SIDEBAR CALON SISWA

Urutan navigasi pada sidebar Calon Siswa (`resources/views/layouts/siswa.blade.php`) telah disesuaikan agar selaras dengan alur pendaftaran 7 tahap, tanpa menghapus route, fungsi, atau hak akses yang ada:

```
[SIDEBAR CALON SISWA]
├── 1. Dashboard Utama          (route: 'siswa.dashboard')
├── 2. Formulir Biodata         (route: 'siswa.formulir')
├── 3. Pembayaran PPDB          (route: 'siswa.pembayaran')
├── 4. Upload Berkas            (route: 'siswa.berkas')
│
├── [Dokumen & Cetak]           (Tetap dipertahankan sesuai format skripsi)
│   ├── Cetak Formulir (PDF)    (route: 'siswa.cetak.formulir')
│   ├── Jadwal dan Kartu Ujian  (route: 'siswa.jadwal')
│   ├── Kwitansi Lunas (PDF)    (route: 'siswa.cetak.kwitansi')
│   └── Surat Kelulusan (SKL)   (route: 'siswa.cetak.skl')
│
├── 5. Daftar Ulang             (route: 'siswa.daftar_ulang')
├── 6. Pengaturan Akun          (route: 'siswa.pengaturan')
├── 7. Riwayat Aktivitas        (route: 'siswa.riwayat_aktivitas')
└── 8. Pusat Bantuan & FAQ      (route: 'siswa.bantuan')
```

> **Catatan:** Posisi menu **Pembayaran PPDB** kini berada tepat sebelum **Upload Berkas**, mengikuti alur pendaftaran di mana pembayaran formulir dilakukan sebelum/bersamaan dengan kelengkapan berkas untuk verifikasi. Sub-menu pencetakan dokumen resmi tetap aktif dan terlindungi kondisi prasyarat masing-masing.

---

## 5. RINCIAN PERUBAHAN BERKAS KODE (FILES CHANGED)

### A. `app/Http/Controllers/SiswaDashboardController.php`
- Memperbarui kalkulasi variabel `$stage` dari skala 1–6 menjadi 1–7.
- Menghitung jumlah kelengkapan dokumen wajib (`$uploadedDocsCount`) dan status pemenuhan berkas (`$allDocsUploaded`).
- Menyalurkan variabel `$uploadedDocsCount` dan `$allDocsUploaded` ke view `siswa.dashboard`.

### B. `resources/views/siswa/dashboard.blade.php`
- Memperbarui header stepper menjadi `Tahap {{ $stage }} dari 7`.
- Mengubah layout grid stepper menjadi responsif 7 kolom (`grid grid-cols-2 md:grid-cols-4 lg:grid-cols-7`).
- Memperbarui array `$stepsData` dengan 7 elemen berurutan: Formulir, Pembayaran, Upload Berkas, Verifikasi, Tes Seleksi, Kelulusan, Daftar Ulang.
- Memperbarui Card Aksi "Dokumen Persyaratan" agar menampilkan badge kelengkapan berkas (`X/4 Upload`) dan badge status pemeriksaan TU (`Verifikasi TU: Menunggu/Terverifikasi/Ditolak`) secara terpisah dan jelas.

### C. `resources/views/layouts/siswa.blade.php`
- Menata ulang menu sidebar navigasi Calon Siswa sesuai alur baru: Dashboard, Formulir, Pembayaran, Upload Berkas, Daftar Ulang, Pengaturan Akun, Riwayat Aktivitas, Pusat Bantuan.
- Memperbarui badge pada menu Upload Berkas agar menampilkan indikator kelengkapan dokumen siswa (`X/4` atau `Lengkap`) alih-alih status verifikasi TU, sehingga konsisten dan tidak tertukar.

### D. `resources/views/siswa/berkas.blade.php`
- Memperjelas header halaman unggah berkas dengan menampilkan dua indikator terpisah:
  1. Kelengkapan Berkas Wajib (`X/4 Wajib`)
  2. Status Verifikasi TU (`Verifikasi TU: Menunggu / Terverifikasi / Ditolak`)

### E. `resources/views/landing.blade.php`
- Memperbarui banner section alur pendaftaran menjadi **Alur 7 Tahap PPDB Online**.
- Memperbarui grid 7 kolom interaktif yang menampilkan langkah transparan dari pendaftaran akun hingga daftar ulang.

### F. `resources/views/siswa/bantuan.blade.php`
- Memperbarui section panduan alur pendaftaran menjadi **7 Tahapan Alur PPDB SMK Islam Bina Khalifah Bangsa**.
- Menjabarkan detail masing-masing tahap (Tahap 1 s.d. 7) dengan pemisahan yang jelas antara Upload Berkas dan Verifikasi TU.

### G. `tests/Feature/Alur7TahapTest.php`
- Membuat test suite baru berisikan 15 unit pengujian otomatis untuk memverifikasi alur 7 tahap, stepper, transisi status database, pemisahan status, urutan sidebar, serta keutuhan seluruh route siswa.

---

## 6. HASIL PENGUJIAN OTOMATIS (TEST MATRIX)

Pengujian menyeluruh dijalankan menggunakan PHPUnit/Pest di lingkungan Laravel:

```
php artisan test
```

### Hasil Ringkasan Pengujian:
- **Total Test Cases:** 130 tests
- **Total Assertions:** 601 assertions
- **Status:** **PASSED (100% Lolos, 0 Error, 0 Failure)**
- **Durasi Eksekusi:** ~12 detik

### Rincian 15 Pengujian Baru (`Alur7TahapTest`):
1. `test_01_dashboard_menampilkan_7_tahap`: Lolos (memastikan stepper menampilkan "dari 7" dan tidak lagi "dari 6").
2. `test_02_stage_1_belum_isi_formulir`: Lolos (memvalidasi Stage 1 saat akun baru belum mengisi formulir).
3. `test_03_stage_2_formulir_selesai`: Lolos (memvalidasi Stage 2 saat formulir dibuat namun pembayaran pending).
4. `test_04_stage_3_pembayaran_lunas_belum_upload`: Lolos (memvalidasi transisi ke Stage 3 "Upload Berkas" setelah pembayaran lunas).
5. `test_05_stage_3_upload_sebagian_berkas`: Lolos (memvalidasi Stage 3 tetap aktif jika berkas baru terunggah 2/4).
6. `test_06_stage_4_semua_berkas_terupload`: Lolos (memvalidasi transisi ke Stage 4 "Verifikasi" setelah 4/4 berkas terunggah).
7. `test_07_stage_5_verifikasi_terverifikasi`: Lolos (memvalidasi transisi ke Stage 5 "Tes Seleksi" saat verifikasi TU berstatus Terverifikasi).
8. `test_08_stage_6_kelulusan_diumumkan`: Lolos (memvalidasi transisi ke Stage 6 "Kelulusan" saat status kelulusan diumumkan).
9. `test_09_stage_7_daftar_ulang_selesai`: Lolos (memvalidasi transisi ke Stage 7 "Daftar Ulang" saat konfirmasi daftar ulang selesai).
10. `test_10_upload_berkas_dan_verifikasi_status_berbeda`: Lolos (memastikan status kelengkapan berkas dan status verifikasi TU independen).
11. `test_11_landing_page_7_tahap`: Lolos (memvalidasi informasi alur 7 tahap di halaman beranda publik).
12. `test_12_sidebar_urutan_menu_sesuai_alur`: Lolos (memvalidasi urutan navigasi sidebar Calon Siswa).
13. `test_13_halaman_bantuan_7_tahap`: Lolos (memvalidasi dokumentasi 7 tahap di halaman bantuan/FAQ).
14. `test_14_semua_route_siswa_tetap_berfungsi`: Lolos (memastikan seluruh endpoint calon siswa mengembalikan HTTP 200).
15. `test_15_fitur_upload_berkas_tetap_berfungsi`: Lolos (memastikan fitur upload berkas eksisting tidak terganggu).

### Pengujian Regresi Fitur Eksisting (115 Tests):
- `AuditRepairTest`: 8 tests PASSED
- `BackupDatabaseTest`: 4 tests PASSED
- `BantuanSiswaTest`: 3 tests PASSED
- `ExampleTest`: 1 test PASSED
- `ExternalNotificationDisabledTest`: 3 tests PASSED
- `FormulirUnlockTest`: 13 tests PASSED
- `ImportPendaftarTest`: 5 tests PASSED
- `ManajemenStafTest`: 7 tests PASSED
- `PPDBRouteTest`: 6 tests PASSED
- `RingkasanKeuanganKepsekTest`: 4 tests PASSED
- `RiwayatAktivitasTest`: 12 tests PASSED
- `SecurityRemediationTest`: 14 tests PASSED
- `Tahap1PerbaikanTest`: 8 tests PASSED
- `Tahap3DraftFormulirTest`: 7 tests PASSED
- `Tahap4JadwalSeleksiTest`: 20 tests PASSED

---

## 7. KESIMPULAN

Pembaruan alur pendaftaran Calon Siswa menjadi **7 tahap** telah berhasil diselesaikan secara sempurna:
1. **Pemisahan Jelas:** Upload Berkas (kelengkapan dokumen siswa) dan Verifikasi (audit berkas oleh Petugas TU) kini berdiri sebagai dua tahap terpisah dengan status independen.
2. **Navigasi Intuitif:** Sidebar Calon Siswa mengikuti tahapan riil pendaftaran tanpa menghilangkan menu atau merusak hak akses.
3. **Integritas Sistem Terjaga:** Tidak ada perubahan skema database secara destruktif, tidak ada duplikasi fitur upload, notifikasi otomatis tetap tidak aktif, dan 130 pengujian otomasi berhasil 100%.

