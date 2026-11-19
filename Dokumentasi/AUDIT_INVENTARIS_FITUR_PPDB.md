# LAPORAN AUDIT INVENTARIS FITUR SISTEM PPDB
## SMK ISLAM BINA KHALIFAH BANGSA (KOTA METRO, LAMPUNG)
**Tanggal Pelaksanaan Audit**: 05 November 2026  
**Metodologi**: Pemeriksaan Statis Kode Sumber (*Static Code Analysis*), Penelaahan Relasi Basis Data 3NF, Pemeriksaan Keamanan Akses, dan Verifikasi Alur Fungsionalitas secara *Read-Only*.  
**Dokumen Referensi**: `SPEC_PPDB.md`, `projek skripsi.pdf`, dan berkas `tugas.md`.

---

## 1. RINGKASAN KONDISI PROYEK

Aplikasi Penerimaan Peserta Didik Baru (PPDB) SMK Islam Bina Khalifah Bangsa dikembangkan menggunakan kerangka kerja **Laravel** berbasis arsitektur basis data relasional **Third Normal Form (3NF)** dengan konvensi primary key eksplisit berformat `id_<nama_tabel>` (seperti `id_user`, `id_pendaftar`, `id_jurusan`, `id_gelombang`, `id_terima`, `id_log`).

### Arsitektur Multi-Peran & Pemisahan Akses:
1. **Portal Publik & Calon Siswa** (`web` guard): Melayani registrasi akun, pengisian biodata, pemilihan jurusan & peminatan ekskul, pengunggahan berkas digital & sertifikat prestasi, checkout pembayaran (Midtrans Snap & tunai loket TU), pelacakan tahapan progres (6-step stepper), cetak formulir, kartu ujian ber-QR Code, kwitansi, dan Surat Keterangan Lulus (SKL).
2. **Panel Petugas Tata Usaha (TU)** (`admin` guard dengan peran `role = 'tu'`): Mengelola verifikasi berkas pendaftar, pemeriksaan berkas via *in-browser viewer* (rotasi & zoom), pembukaan kunci formulir (*unlock*), verifikasi pelunasan tunai loket, manajemen master jurusan, kuota, gelombang, voucher, publikasi pengumuman, saklar mode pemeliharaan, serta ekspor data (CSV aman dari formula injection, native XLSX, Dapodik, dan rekap PDF).
3. **Panel Kepala Sekolah** (`admin` guard dengan peran `role = 'kepala_sekolah'`): Menampilkan dashboard eksekutif KPI penerimaan, visualisasi tren Chart.js, analisis asal sekolah (*Market Intelligence*), modul sidang kelulusan (pengesahan status Diterima/Cadangan/Tidak Lolos dan penerbitan nomor SKL), laporan resmi, serta audit trail aktivitas.

### Batasan Audit & Integritas Sistem:
Pemeriksaan ini dilaksanakan secara **read-only** tanpa mengubah berkas kode, tanpa menjalankan migrasi/seeder, tanpa memanipulasi database, dan tanpa mengubah konfigurasi `.env`. Status kelayakan dibagi ke dalam 4 kategori resmi:
- **SUDAH ADA DAN BERFUNGSI**: Kode, controller, rute, model, dan antarmuka telah terintegrasi secara utuh dan siap digunakan.
- **SUDAH ADA TETAPI BELUM LENGKAP**: Fitur telah diimplementasikan sebagian namun masih memiliki kekurangan validasi, antarmuka parsial, atau bug runtime pada Blade/variabel.
- **BELUM ADA**: Fitur belum diimplementasikan pada rute, controller, maupun antarmuka.
- **PERLU PENGUJIAN RUNTIME**: Fitur bergantung pada layanan eksternal (seperti payment gateway Midtrans / WhatsApp API) atau membutuhkan simulasi interaksi browser nyata untuk memastikan fungsionalitas end-to-end.

---

## 2. HASIL INVENTARIS PORTAL CALON SISWA

| No | Fitur | Role Pengguna | File & Lokasi Implementasi | Rute / Controller / Model / View | Kondisi Implementasi | Kekurangan / Risiko | Status | Rekomendasi Tindak Lanjut |
|---|---|---|---|---|---|---|---|---|
| 1 | **Registrasi dan Login** | Calon Siswa & Publik | `app/Http/Controllers/AuthController.php` | Rute: `/daftar`, `/login`, `/logout`<br>View: `resources/views/auth/register.blade.php`, `auth/login.blade.php`<br>Model: `User` | Mendukung login multi-identifier (NISN 10 digit, email, atau username). Dilengkapi pemisahan akses ketat: menolak kredensial staf admin di halaman siswa dengan pesan pengalihan yang ramah. | Belum dilengkapi pembatasan laju percobaan login (*rate limiting / throttle*). Validasi kata sandi minimal 6 karakter. | **SUDAH ADA DAN BERFUNGSI** | Tambahkan middleware `throttle:login` pada rute otentikasi untuk mitigasi serangan brute-force. |
| 2 | **Dashboard dan Profil** | Calon Siswa | `app/Http/Controllers/SiswaDashboardController.php` | Rute: `siswa.dashboard`<br>Controller: `SiswaDashboardController@index`<br>View: `resources/views/siswa/dashboard.blade.php` | Menampilkan ringkasan akun siswa (NISN, Nama, No. Pendaftaran), Stepper Progres 6 Tahap (Formulir -> Bayar -> Verifikasi -> Seleksi -> Kelulusan -> Daftar Ulang), 3 Kartu Tindakan Cepat, serta modul hasil seleksi. | Tampilan avatar profil di navbar masih berupa placeholder inisial nama teks, belum mendukung foto profil dinamis. | **SUDAH ADA DAN BERFUNGSI** | Tampilkan avatar otomatis dari pasfoto yang diunggah siswa setelah berkas terverifikasi. |
| 3 | **Formulir Biodata dan Penyimpanan Draft** | Calon Siswa | `app/Http/Controllers/SiswaDashboardController.php` | Rute: `siswa.formulir`, `siswa.formulir.simpan`<br>Model: `Pendaftar`, `Jurusan`<br>View: `resources/views/siswa/formulir.blade.php` | Biodata lengkap tersimpan di tabel `pendaftar` dengan status terkunci/terbuka (`is_locked`). Menggunakan generator nomor pendaftaran atomik (`Pendaftar::generateNoPendaftaran`) dengan row lock. | Aturan validasi `simpanFormulir` mewajibkan seluruh field terisi (`required`). Siswa tidak dapat menyimpan "draft parsial" jika hanya mengisi separuh formulir. | **SUDAH ADA TETAPI BELUM LENGKAP** | Izinkan validasi bersyarat (*nullable*) ketika tombol "Simpan Draft" ditekan, dan hanya terapkan validasi ketat saat "Finalisasi". |
| 4 | **Upload serta Pratinjau Dokumen** | Calon Siswa | `app/Http/Controllers/SiswaDashboardController.php`, `app/Http/Controllers/DokumenController.php` | Rute: `siswa.berkas.upload`, `siswa.prestasi.upload`, `dokumen.berkas`<br>Model: `BerkasPendaftar`, `PrestasiPendaftar`<br>View: `resources/views/siswa/berkas.blade.php` | Dokumen disimpan ke disk private `local` (`storage/app/berkas_pendaftar`) dengan nama hash acak 32 karakter untuk kepatuhan perlindungan data pribadi (UU PDP). Pratinjau melalui `DokumenController` dengan otorisasi kepemilikan. | Pratinjau dokumen di sisi siswa membuka tab baru (`target="_blank"`), belum berupa modal pratinjau *in-page* seperti di panel TU. | **SUDAH ADA DAN BERFUNGSI** | Tambahkan modal pratinjau instan di halaman berkas siswa agar pengguna tidak perlu berpindah tab. |
| 5 | **Checklist Kelengkapan Berkas** | Calon Siswa | `resources/views/siswa/berkas.blade.php` | View: `resources/views/siswa/berkas.blade.php`, `resources/views/siswa/dashboard.blade.php` | Menampilkan 4 kartu berkas inti (Ijazah/SKL, KK, Pasfoto, Akta) dengan status dinamis (Belum Ada File, Menunggu Verifikasi, Valid, Tidak Valid) serta indikator catatan revisi. | Tidak ada ringkasan persentase kelengkapan (misal: "3 dari 4 berkas siap"). | **SUDAH ADA DAN BERFUNGSI** | Tambahkan *progress bar* persentase berkas terunggah di bagian atas halaman berkas. |
| 6 | **Pembayaran (Online & Tunai)** | Calon Siswa | `app/Http/Controllers/SiswaDashboardController.php`, `app/Services/MidtransService.php` | Rute: `siswa.pembayaran`, `siswa.pembayaran.snap`, `siswa.pembayaran.tunai`<br>Model: `Pembayaran`, `VoucherPendaftaran`<br>View: `resources/views/siswa/pembayaran.blade.php` | Mendukung checkout Midtrans Snap (QRIS, VA, E-Wallet) dan tagihan pembayaran tunai loket TU. Dilengkapi klaim kode voucher diskon dengan proteksi *row lock* (`lockForUpdate`). Proteksi ketat: status lunas hanya via webhook terverifikasi signature atau verifikasi kasir TU. | Membutuhkan kredensial Server Key Midtrans aktif untuk transaksi produksi riil di luar mode simulasi/sandbox. | **PERLU PENGUJIAN RUNTIME** | Lakukan uji transaksi sandbox Midtrans end-to-end hingga menerima callback notifikasi webhook HTTP 200. |
| 7 | **Jadwal dan Kartu Ujian** | Calon Siswa | `app/Http/Controllers/SiswaDashboardController.php`, `app/Http/Controllers/VerifikasiController.php` | Rute: `siswa.cetak.kartu_ujian`, `verifikasi.kartu_ujian`<br>View: `resources/views/siswa/cetak_kartu_ujian.blade.php`, `resources/views/verifikasi/hasil.blade.php` | Kartu peserta tes seleksi otomatis terbit saat pembayaran lunas (`status_pembayaran = 'Paid'`). Memuat QR Code verifikasi online dengan token unik. | Rincian jadwal, tanggal, ruang ujian, dan penguji masih berupa teks statis di template cetak (belum terhubung ke tabel master jadwal). | **SUDAH ADA TETAPI BELUM LENGKAP** | Integrasikan jadwal ujian dengan data dinamis sesi dan ruangan per peserta. |
| 8 | **Pelacakan Status Pendaftaran** | Calon Siswa | `resources/views/siswa/dashboard.blade.php` | View: `resources/views/siswa/dashboard.blade.php`<br>Controller: `SiswaDashboardController@index` | Stepper progres 6 tahap (Formulir, Pembayaran, Verifikasi, Tes Seleksi, Kelulusan, Daftar Ulang) dengan status deskripsi per tahapan dan indikator warna dinamis. | - | **SUDAH ADA DAN BERFUNGSI** | Pertahankan implementasi visual stepper yang sudah sangat informatif bagi pendaftar. |
| 9 | **Notifikasi dan Pengumuman** | Calon Siswa | `resources/views/siswa/dashboard.blade.php`, `app/Models/Pengumuman.php` | Model: `Pengumuman`<br>View: `resources/views/siswa/dashboard.blade.php` | Menampilkan banner pengumuman aktif sekolah, kartu catatan revisi berkas dari TU, dan flash alert session saat form disimpan. | Belum tersedia *Notification Center* (ikon lonceng riwayat notifikasi in-app) untuk menyimpan riwayat pemberitahuan sistem. | **SUDAH ADA TETAPI BELUM LENGKAP** | Tambahkan tabel notifikasi internal untuk menyimpan daftar pengingat yang dapat dibaca kapan saja. |
| 10 | **Riwayat Aktivitas Pribadi** | Calon Siswa | `app/Models/LogAktivitas.php` | Model: `LogAktivitas` | Aktivitas siswa (login, simpan formulir, upload berkas, pembuatan tagihan) dicatat di database pada tabel `log_aktivitas` dengan `id_user`. | Portal calon siswa tidak memiliki rute dan tampilan antarmuka untuk membaca riwayat aktivitas akunnya sendiri. | **BELUM ADA** | Buat halaman `/siswa/aktivitas` agar siswa dapat melihat audit trail aktivitas akunnya secara transparan. |
| 11 | **Bantuan dan FAQ** | Publik & Calon Siswa | `resources/views/landing.blade.php` | View: `resources/views/landing.blade.php`, layout footer | Terdapat accordion FAQ interaktif di halaman beranda publik, rincian kontak sekolah, integrasi Google Maps, dan tombol direct chat WhatsApp panitia. | Belum tersedia halaman Bantuan/FAQ tersendiri di dalam area otentikasi calon siswa (`/siswa/bantuan`). | **SUDAH ADA TETAPI BELUM LENGKAP** | Tambahkan tautan menu Bantuan/FAQ langsung pada sidebar portal calon siswa. |
| 12 | **Pengaturan Akun** | Calon Siswa | `app/Http/Controllers/SiswaDashboardController.php` | Rute: `siswa.pengaturan`, `siswa.pengaturan.simpan`<br>View: `resources/views/siswa/pengaturan.blade.php` | Calon siswa dapat mengganti kata sandi secara aman (terenkripsi Bcrypt, verifikasi konfirmasi) serta memperbarui nomor kontak WhatsApp aktif. | Tidak ada opsi untuk mengubah username atau alamat email mandiri (perlu konfirmasi TU jika ada koreksi data akun). | **SUDAH ADA DAN BERFUNGSI** | Pertahankan pembatasan perubahan email/NISN mandiri demi mencegah anomali data pendaftaran. |
| 13 | **Indikator Kelengkapan Biodata** | Calon Siswa | `resources/views/siswa/dashboard.blade.php` | View: `resources/views/siswa/dashboard.blade.php`, `resources/views/siswa/formulir.blade.php` | Menampilkan badge status formulir ("Belum Diisi", "Draft Tersimpan", "Difinalisasi/Terkunci") dan ringkasan pilihan jurusan di kartu dashboard. | Belum ada widget persentase kelengkapan field (misal: "Kelengkapan Biodata: 85%"). | **SUDAH ADA DAN BERFUNGSI** | Tambahkan perhitungan persentase kelengkapan field biodata sebagai panduan visual calon siswa. |
| 14 | **Pratinjau Formulir Sebelum Finalisasi** | Calon Siswa | `resources/views/siswa/formulir.blade.php` | View: `resources/views/siswa/formulir.blade.php`, `resources/views/siswa/cetak_formulir.blade.php` | Tombol "Finalisasi & Kunci Data" dilengkapi dialog konfirmasi JavaScript (`confirm()`). Siswa dapat mencetak atau melihat kembali data sebelum mengunci. | Belum tersedia halaman/modal rangkuman pratinjau (*review summary modal*) sebelum konfirmasi finalisasi dieksekusi. | **SUDAH ADA TETAPI BELUM LENGKAP** | Tampilkan modal ikhtisar data pendaftaran sebelum siswa menekan tombol kunci permanen. |
| 15 | **Unduh Bukti Pendaftaran** | Calon Siswa | `app/Http/Controllers/SiswaDashboardController.php` | Rute: `siswa.cetak.formulir`, `siswa.cetak.kartu_ujian`, `siswa.cetak.kwitansi`, `siswa.cetak.skl`<br>Views: `resources/views/siswa/cetak_*.blade.php` | Tersedia 4 dokumen resmi berformat standar cetak A4 (`@media print`) dengan kop surat resmi, stempel/tanda tangan, dan QR Code verifikasi. | Format saat ini mengandalkan fungsi cetak browser (`window.print()`), belum menggunakan engine generator PDF server-side murni (seperti DomPDF / Snappy). | **SUDAH ADA DAN BERFUNGSI** | Desain cetak browser sudah rapi; opsi server-side PDF dapat ditambahkan jika diperlukan arsip otomatis. |

---

## 3. HASIL INVENTARIS PANEL PETUGAS TU

| No | Fitur | Role Pengguna | File & Lokasi Implementasi | Rute / Controller / Model / View | Kondisi Implementasi | Kekurangan / Risiko | Status | Rekomendasi Tindak Lanjut |
|---|---|---|---|---|---|---|---|---|
| 1 | **Dashboard dan Statistik** | Petugas TU | `app/Http/Controllers/AdminTUController.php` | Rute: `admin.dashboard`<br>Controller: `AdminTUController@index`<br>View: `resources/views/admin/dashboard.blade.php` | Menyajikan metrik Total Pendaftar, Pendaftar Hari Ini, Total Lunas, Menunggu Verifikasi, Total Diterima, Grafik peminat per jurusan, dan tabel 8 pendaftar terkini. | - | **SUDAH ADA DAN BERFUNGSI** | Pertahankan metrik analitik dashboard yang sudah berjalan dengan baik. |
| 2 | **Manajemen Pendaftar** | Petugas TU | `app/Http/Controllers/AdminTUController.php` | Rute: `admin.pendaftar`, `admin.pendaftar.detail`<br>View: `resources/views/admin/pendaftar/index.blade.php`, `detail.blade.php` | Tabel pendaftar dengan paginasi, pencarian multi-kolom (nama, nisn, no. registrasi, asal sekolah), filter per jurusan, status bayar, verifikasi, dan kelulusan. Dossier lengkap pada halaman detail. | Belum ada fitur bulk action (misal: verifikasi massal atau cetak formulir massal dari tabel). | **SUDAH ADA DAN BERFUNGSI** | Tambahkan fitur seleksi checkbox untuk aksi massal (*bulk action*) pendaftar. |
| 3 | **Verifikasi Biodata dan Dokumen** | Petugas TU | `app/Http/Controllers/AdminTUController.php` | Rute: `admin.pendaftar.verifikasi`, `admin.berkas.verifikasi`<br>View: `resources/views/admin/pendaftar/detail.blade.php` | Verifikasi status berkas global (Terverifikasi, Ditolak, Menunggu) dan verifikasi granular per item berkas (Valid, Tidak Valid) disertai catatan revisi. Dilengkapi penampil gambar interaktif (rotasi 90° & zoom). | - | **SUDAH ADA DAN BERFUNGSI** | Pertahankan penampil dokumen interaktif yang memudahkan pemeriksaan dokumen miring/terbalik. |
| 4 | **Unlock Formulir** | Petugas TU | `app/Http/Controllers/AdminTUController.php` | Rute: `admin.pendaftar.unlock`<br>Controller: `AdminTUController@unlockFormulir`<br>View: `resources/views/admin/pendaftar/detail.blade.php` | Membuka status kunci biodata siswa (`is_locked = false`) secara aman tanpa merusak status pembayaran atau nilai seleksi, dicatat lengkap ke `log_aktivitas`. | - | **SUDAH ADA DAN BERFUNGSI** | Fitur telah terverifikasi dengan tes otomatis `FormulirUnlockTest`. |
| 5 | **Manajemen Pembayaran** | Petugas TU | `app/Http/Controllers/AdminTUController.php` | Rute: `admin.pendaftar.bayar_tunai`<br>Controller: `AdminTUController@konfirmasiBayarTunai`<br>Model: `Pembayaran` | Petugas TU dapat memverifikasi pelunasan tunai pendaftar di loket kasir, otomatis mengupdate status pendaftar menjadi `Paid` dan status transaksi `settlement`. | Tombol verifikasi pembayaran tunai baru tersedia di halaman detail pendaftar perorangan, belum ada antarmuka khusus "Menu Loket Pembayaran / Buku Kas TU". | **SUDAH ADA TETAPI BELUM LENGKAP** | Buat menu navigasi khusus kasir loket pembayaran tunai pada panel TU untuk mempercepat pelayanan tatap muka. |
| 6 | **Manajemen Jurusan dan Gelombang** | Petugas TU | `app/Http/Controllers/AdminTUController.php` | Rute: `admin.master.jurusan`, `admin.master.gelombang`<br>Views: `resources/views/admin/master/jurusan.blade.php`, `gelombang.blade.php` | Pengaturan data 4 jurusan utama dan pembuatan gelombang pendaftaran baru (nama, tahun ajaran, biaya, tanggal buka/tutup, status aktif). | Belum ada fitur hapus (*soft delete*) gelombang yang sudah lewat. | **SUDAH ADA DAN BERFUNGSI** | Tambahkan opsi pengarsipan (*archive / toggle status*) gelombang kadaluarsa. |
| 7 | **Manajemen Kuota Penerimaan** | Petugas TU | `app/Http/Controllers/AdminTUController.php` | Rute: `admin.master.jurusan.update`, `admin.pendaftar.alihkan`<br>Controller: `AdminTUController@alihkanJurusan` | Petugas TU dapat mengubah kapasitas kuota per jurusan dan mengalihkan calon siswa ke jurusan pilihan kedua jika kuota pilihan utama telah penuh. | Belum ada mekanisme peringatan otomatis (*auto-warning indicator*) saat kuota pendaftar terverifikasi melebihi daya tampung. | **SUDAH ADA DAN BERFUNGSI** | Tambahkan penanda warna merah pada tabel jurusan saat peminat melebihi daya tampung. |
| 8 | **Jadwal Seleksi** | Petugas TU | - | Model: Tidak ada model jadwal<br>Rute: Tidak ada rute | Tidak ditemukan modul manajemen jadwal seleksi (tanggal, sesi waktu, ruang ujian, dan penguji). | Rincian jadwal tes seleksi masuk saat ini masih tertulis secara hardcoded pada template kartu ujian siswa. | **BELUM ADA** | Buat entitas tabel `jadwal_seleksi` dan menu pengaturan jadwal tes per gelombang/sesi di panel TU. |
| 9 | **Pengelolaan Pengumuman** | Petugas TU | `app/Http/Controllers/AdminTUController.php` | Rute: `admin.pengumuman`, `admin.pengumuman.simpan`<br>Model: `Pengumuman`<br>View: `resources/views/admin/pengumuman/index.blade.php` | Publikasi pengumuman (judul, isi, tipe info/peringatan/penting/maintenance, rentang tanggal aktif, toggle banner landing page). | Belum ada tombol hapus (*delete*) atau edit pengumuman yang sudah dipublikasikan. | **SUDAH ADA DAN BERFUNGSI** | Tambahkan aksi edit dan hapus pada tabel daftar pengumuman. |
| 10 | **Laporan dan Ekspor** | Petugas TU | `app/Http/Controllers/AdminTUController.php`, `app/Services/XlsxExportService.php` | Rute: `admin.ekspor.excel`, `admin.ekspor.xlsx`, `admin.ekspor.dapodik`, `admin.laporan.rekap_pdf`<br>View: `resources/views/admin/laporan/rekap_pdf.blade.php` | Ekspor CSV dengan proteksi CSV Formula Injection (CWE-1236) & UTF-8 BOM, ekspor format Excel OpenXML (.xlsx) native, ekspor template resmi Dapodik, dan cetak lembar rekap PDF. | - | **SUDAH ADA DAN BERFUNGSI** | Modul ekspor telah teruji tahan terhadap eksploitasi formula injection dan kompatibel dengan MS Excel. |
| 11 | **Riwayat Aktivitas Administrasi** | Petugas TU | `app/Models/LogAktivitas.php` | Model: `LogAktivitas` | Seluruh aktivitas staf TU (verifikasi berkas, unlock, pelunasan, input nilai, switch jurusan) dicatat oleh backend ke tabel `log_aktivitas`. | Petugas TU tidak memiliki menu antarmuka untuk melihat audit log di panelnya (hanya Kepala Sekolah yang memiliki rute audit log). | **BELUM ADA** | Buat menu "Riwayat Aktivitas Saya" di panel TU agar staf dapat meninjau log kerjanya sendiri. |
| 12 | **Deteksi Pendaftar Ganda** | Petugas TU | `database/migrations/2026_01_01_000001_create_user_table.php` | Model: `User`, `Pendaftar` | Di level database dan form registrasi, NISN, email, dan username diproteksi indeks unik (`unique:user,nisn`). Tidak mungkin ada pendaftar ganda dengan NISN sama. | Belum ada alat bantu analitik di panel TU untuk mendeteksi kesamaan data non-unik (misal: pendaftar dengan nama dan tanggal lahir sama tapi mendaftar dengan akun/NISN berbeda). | **SUDAH ADA TETAPI BELUM LENGKAP** | Tambahkan widget pencarian kesamaan nama dan asal sekolah untuk mengidentifikasi potensi registrasi ganda tak disengaja. |
| 13 | **Catatan Revisi Berkas** | Petugas TU | `app/Http/Controllers/AdminTUController.php` | Rute: `admin.pendaftar.verifikasi`, `admin.berkas.verifikasi`<br>Kolom: `pendaftar.catatan_verifikasi`, `berkas_pendaftar.catatan` | Petugas TU dapat memberikan catatan alasan penolakan secara menyeluruh maupun per item berkas yang langsung tampil di dashboard calon siswa. | - | **SUDAH ADA DAN BERFUNGSI** | Fitur telah berjalan terintegrasi antara panel TU dan dashboard siswa. |
| 14 | **Import Data Excel** | Petugas TU | - | Model: Tidak ada<br>Rute: Tidak ada rute import | Sistem menyediakan ekspor lengkap, namun belum memiliki modul untuk mengimpor data pendaftar dari berkas Excel/CSV ke database. | Penginputan data siswa luar jaringan (offline) harus dilakukan manual satu per satu melalui formulir registrasi. | **BELUM ADA** | Kembangkan fitur Import Excel pendaftar offline dengan validasi baris otomatis dan mapping kolom Dapodik. |
| 15 | **Manajemen Hak Akses** | Petugas TU | `database/seeders/DatabaseSeeder.php` | Model: `Admin`<br>Tabel: `admin` | Pengelolaan hak akses menggunakan kolom `role` (`tu`, `kepala_sekolah`) yang dilindungi middleware `AdminRoleMiddleware`. Akun staf terdaftar di tabel `admin`. | Tidak tersedia antarmuka pengguna (CRUD) untuk menambah, mengedit, atau menonaktifkan akun staf TU di panel web. Akun staf baru harus dibuat melalui seeder atau konsol artisan. | **BELUM ADA** | Buat menu "Manajemen Akun Staf" yang hanya dapat dikelola oleh Kepala Sekolah atau Administrator Utama. |

---

## 4. HASIL INVENTARIS PANEL KEPALA SEKOLAH

| No | Fitur | Role Pengguna | File & Lokasi Implementasi | Rute / Controller / Model / View | Kondisi Implementasi | Kekurangan / Risiko | Status | Rekomendasi Tindak Lanjut |
|---|---|---|---|---|---|---|---|---|
| 1 | **Dashboard Eksekutif** | Kepala Sekolah | `app/Http/Controllers/KepalaSekolahController.php` | Rute: `kepsek.dashboard`<br>Controller: `KepalaSekolahController@index`<br>View: `resources/views/kepsek/dashboard.blade.php` | Menampilkan sambutan Kepala Sekolah, 4 metrik KPI (Total Pendaftar, Diterima, Cadangan, Tidak Lolos), visualisasi Chart.js keterisian kuota per jurusan, tren pendaftar 7 hari, dan Market Intelligence Top 5 Asal SMP. | - | **SUDAH ADA DAN BERFUNGSI** | Pertahankan analitik visual eksekutif yang sangat membantu pengambilan keputusan strategis. |
| 2 | **Statistik dan KPI PPDB** | Kepala Sekolah | `app/Http/Controllers/KepalaSekolahController.php` | View: `resources/views/kepsek/dashboard.blade.php`<br>Script: Integrasi library Chart.js | Perhitungan rasio keterisian kuota, persentase kelulusan, dan grafik perbandingan peminat pilihan 1 vs kuota daya tampung. | - | **SUDAH ADA DAN BERFUNGSI** | Statistik telah tersaji secara akurat dan responsif. |
| 3 | **Monitoring Pendaftar** | Kepala Sekolah | `app/Http/Controllers/KepalaSekolahController.php` | Rute: `kepsek.kelulusan`, `kepsek.laporan`<br>Views: `resources/views/kepsek/kelulusan.blade.php`, `laporan.blade.php` | Kepala Sekolah dapat memantau seluruh pendaftar yang siap dinilai, riwayat nilai tes wawancara, tes buta warna, dan skor piagam prestasi. | Belum ada fitur pencarian teks instan nama siswa di halaman sidang kelulusan (hanya filter jurusan). | **SUDAH ADA DAN BERFUNGSI** | Tambahkan kolom pencarian teks nama/NISN pada tabel sidang kelulusan. |
| 4 | **Persetujuan Hasil Seleksi** | Kepala Sekolah | `app/Http/Controllers/KepalaSekolahController.php` | Rute: `kepsek.kelulusan.sahkan`<br>Controller: `KepalaSekolahController@sahkanKelulusan`<br>Model: `Terima`, `Pendaftar` | Kepala Sekolah menetapkan status kelulusan final (Diterima, Cadangan, Tidak Lolos), memilih jurusan yang diterima, otomatis menerbitkan nomor SKL (`Terima::generateNoSuratKelulusan()`), dan mencatat ke audit trail. | - | **SUDAH ADA DAN BERFUNGSI** | Terverifikasi berjalan dengan baik dan tersinkronisasi dengan penerbitan Surat Keterangan Lulus (SKL). |
| 5 | **Ringkasan Keuangan** | Kepala Sekolah | - | Model: `Pembayaran`<br>Rute: Tidak ada rute ringkasan keuangan | Belum tersedia kartu ringkasan atau laporan keuangan di panel Kepala Sekolah (total pendapatan biaya formulir, perbandingan pembayaran tunai vs online, potongan voucher). | Kepala Sekolah belum dapat melihat rekapitulasi finansial PPDB secara terpusat pada panel eksekutifnya. | **BELUM ADA** | Tambahkan kartu ringkasan pendapatan PPDB (Total Masuk, Tunai Loket, Online Midtrans) di dashboard Kepala Sekolah. |
| 6 | **Laporan Penerimaan** | Kepala Sekolah | `app/Http/Controllers/KepalaSekolahController.php` | Rute: `kepsek.laporan`<br>View: `resources/views/kepsek/laporan.blade.php` | Matriks keterisian kuota 4 jurusan, persentase sisa kursi, pintasan cetak rekap PDF resmi, ekspor Excel, ekspor Dapodik, serta pernyataan validasi integritas lembaga ber-NPSN. | - | **SUDAH ADA DAN BERFUNGSI** | Format laporan resmi telah memenuhi standar pelaporan institusi pendidikan. |
| 7 | **Riwayat Aktivitas Pengawasan** | Kepala Sekolah | `app/Http/Controllers/KepalaSekolahController.php` | Rute: `kepsek.audit_log`<br>View: `resources/views/kepsek/audit_log.blade.php`<br>Model: `LogAktivitas` | Menampilkan tabel riwayat aktivitas sistem dengan paginasi, rincian IP/User-Agent, dan modal interaktif penampil diff perubahan data JSON lama vs baru. | **Terdapat Bug Runtime Nama Variabel Blade**: baris 72 menggunakan `$log->user->name` (seharusnya `$log->user->nama`), baris 86 & 97 menggunakan `$log->tipe_aktivitas` (seharusnya `$log->aksi`), baris 106 menggunakan `$log->id_entitas` (seharusnya `$log->id_data`). Menyebabkan data aktor siswa dan badge aksi kosong saat dirender! | **SUDAH ADA TETAPI BELUM LENGKAP** | Sesuaikan pemanggilan properti pada `audit_log.blade.php` agar cocok dengan skema tabel `log_aktivitas` (`aksi`, `id_data`) dan `user` (`nama`). |

---

## 5. HASIL PEMERIKSAAN KEAMANAN DAN PENGELOLAAN SISTEM

| No | Komponen Keamanan | Lokasi Implementasi | Kondisi Saat Ini | Temuan Risiko / Celah | Status | Rekomendasi Tindak Lanjut |
|---|---|---|---|---|---|---|
| 1 | **Keamanan Login dan Hak Akses** | `app/Http/Controllers/AuthController.php`, `app/Http/Middleware/AdminRoleMiddleware.php` | Memisahkan dua guard otentikasi (`web` untuk siswa dan `admin` untuk staf). Sesi diregenerasi saat login (`session()->regenerate()`). Blokade login silang antar peran diimplementasikan. | Proteksi akses rute antar peran sudah sangat ketat dan terisolasi. | **SUDAH ADA DAN BERFUNGSI** | Pertahankan isolasi multi-guard yang sudah berjalan. |
| 2 | **Riwayat Perangkat Login** | - | Tidak ditemukan tabel atau fitur perekaman riwayat sesi perangkat aktif (*device history / active sessions list*). | Pengguna tidak dapat memantau perangkat lain yang sedang login menggunakan akunnya. | **BELUM ADA** | Implementasikan database session driver untuk mencatat riwayat perangkat aktif pengguna. |
| 3 | **Logout Semua Perangkat** | `app/Http/Controllers/AuthController.php` | Logout hanya menginvaliasi sesi pada perangkat yang sedang digunakan saat ini (`session()->invalidate()`). | Belum ada fitur `Auth::logoutOtherDevices` untuk mengeluarkan akun dari perangkat lain saat ganti password. | **BELUM ADA** | Tambahkan pemanggilan `Auth::logoutOtherDevices($password)` ketika calon siswa atau staf mengganti kata sandi. |
| 4 | **Pembatasan Percobaan Login (Rate Limiting)** | `routes/web.php` | Rute POST `/login` dan POST `/admin/login` belum dipasangi middleware `throttle`. | Rentan terhadap serangan *brute force password guessing* otomatis menggunakan kamus kata sandi. | **BELUM ADA** | Pasang middleware `throttle:5,1` (maksimal 5 percobaan gagal per menit) pada seluruh endpoint otentikasi. |
| 5 | **Two-Factor Authentication (2FA)** | - | Belum tersedia modul 2FA berbasis TOTP (Google Authenticator) maupun kode OTP via WhatsApp/Email. | Keamanan akun staf admin dan Kepala Sekolah hanya bergantung pada kata sandi statis. | **BELUM ADA** | Implementasikan 2FA untuk akun peran `tu` dan `kepala_sekolah` demi perlindungan data strategis. |
| 6 | **Pengingat Masa Sesi (Session Timeout Warning)** | `config/session.php` | Sesi diatur dengan masa kedaluwarsa 120 menit (`SESSION_LIFETIME=120`), namun tidak ada indikator visual atau pop-up peringatan sebelum sesi berakhir. | Pengguna yang sedang mengisi formulir panjang bisa kehilangan data jika sesi habis tanpa peringatan. | **BELUM ADA** | Tambahkan modal timer JavaScript di antarmuka formulir yang mengingatkan pengguna 5 menit sebelum sesi berakhir. |
| 7 | **Backup dan Pemulihan Database** | - | Belum ada rute, controller, atau command artisan internal untuk membuat dump SQL basis data secara otomatis atau satu klik. | Pencadangan data saat ini masih harus dilakukan secara manual melalui phpMyAdmin atau mysqldump di terminal hosting. | **BELUM ADA** | Kembangkan fitur backup basis data 1-klik di panel admin yang mengekspor file dump `.sql.gz` secara aman. |
| 8 | **Pengaturan Masa Pendaftaran** | `app/Models/Gelombang.php`, `app/Http/Controllers/LandingController.php` | Masa pendaftaran dibatasi oleh tanggal `tgl_buka` dan `tgl_tutup` serta flag `status_aktif` pada tabel `gelombang`. Landing page otomatis memeriksa tanggal berlaku. | Kontrol masa buka gelombang sudah otomatis dan terkelola dengan baik. | **SUDAH ADA DAN BERFUNGSI** | Pertahankan integrasi waktu gelombang aktif. |
| 9 | **Halaman Pemeliharaan (Maintenance Mode)** | `app/Http/Middleware/CheckMaintenanceMiddleware.php`, `app/Http/Controllers/AdminTUController.php` | Saklar Mode Maintenance diatur melalui basis data (`pengaturan_sekolah.mode_maintenance`). Pengunjung diarahkan ke halaman HTTP 503 yang ramah, sementara rute admin tetap dapat diakses. | Mekanisme *zero-code maintenance* berjalan mulus tanpa mengganggu akses staf operasional. | **SUDAH ADA DAN BERFUNGSI** | Pertahankan arsitektur middleware maintenance yang sudah fleksibel ini. |
| 10 | **Monitoring Error** | `bootstrap/app.php`, `storage/logs/laravel.log` | Penanganan error berjalan menggunakan logging standar Laravel Monolog (`LOG_CHANNEL=stack`) ke berkas teks harian/single. | Belum ada dashboard penampil log error grafis terintegrasi (seperti Laravel Telescope, Sentry, atau log viewer internal). | **SUDAH ADA TETAPI BELUM LENGKAP** | Pasang log viewer terproteksi di panel admin untuk memantau exception runtime tanpa harus membuka server storage secara manual. |

---

## 6. HASIL PEMERIKSAAN RIWAYAT AKTIVITAS (AUDIT TRAIL)

Audit mendalam terhadap implementasi tabel `log_aktivitas` dan mekanisme pencatatannya menghasilkan kesimpulan berikut:

### A. Struktur Basis Data Tabel `log_aktivitas`
Sesuai rancangan 3NF pada migrasi `2026_01_01_000013_create_log_aktivitas_table.php`:
- `id_log` (BigInt UNSIGNED Primary Key Auto Increment)
- `id_admin` (FK ke `admin.id_admin`, nullable, onDelete set null)
- `id_user` (FK ke `user.id_user`, nullable, onDelete set null)
- `aksi` (Varchar 50: `LOGIN`, `LOGOUT`, `INSERT`, `UPDATE`, `VERIFIKASI`, `UNLOCK`, `PENGESAHAN_SKL`, dll.)
- `tabel_terkait` (Varchar 50: nama tabel terdampak seperti `pendaftar`, `berkas_pendaftar`, `pembayaran`, `user`)
- `id_data` (Unsigned BigInt: ID baris entitas yang mengalami mutasi)
- `deskripsi` (Varchar 255: keterangan peristiwa dalam bahasa manusia)
- `data_lama` (JSON: snapshot nilai sebelum perubahan)
- `data_baru` (JSON: snapshot nilai sesudah perubahan)
- `ip_address` (Varchar 45: alamat IPv4/IPv6 pelaku)
- `user_agent` (Text: informasi browser dan perangkat pengakses)
- `created_at` & `updated_at` (Timestamp waktu pencatatan)

### B. Matriks Evaluasi Skenario Pencatatan Aktivitas

| Skenario Aktivitas | Lokasi Kode Pemanggil | Status Pencatatan | Kelengkapan Data Snapshot |
|---|---|---|---|
| **Login dan Logout Siswa** | `AuthController@login`, `AuthController@logout` | **Tercatat** | Aksi `LOGIN`/`LOGOUT`, IP, User-Agent tercatat. |
| **Login dan Logout Staf/Kepsek** | `AuthController@adminLogin`, `AuthController@adminLogout` | **Tercatat** | Aksi `LOGIN`/`LOGOUT`, nama admin, IP, User-Agent tercatat. |
| **Pendaftaran Akun Baru Siswa** | `AuthController@register` | **Tercatat** | Aksi `INSERT`, tabel `user`, id data baru tercatat. |
| **Perubahan Password Akun** | `SiswaDashboardController@simpanPengaturanAkun` | **Tercatat Aman** | Deskripsi mencatat perubahan tanpa menyimpan hash password atau teks password, sehingga kredensial aman terlindungi. |
| **Pengisian & Pembaruan Biodata** | `SiswaDashboardController@simpanFormulir` | **Tercatat** | Aksi `INSERT`/`UPDATE`, tabel `pendaftar`, no pendaftaran tercatat. |
| **Finalisasi & Kunci Data Formulir** | `SiswaDashboardController@finalisasi` | **Tercatat** | Aksi `UPDATE`, mengunci berkas pendaftar. |
| **Unggah Dokumen Berkas & Prestasi** | `SiswaDashboardController@uploadBerkas`, `uploadPrestasi` | **Tercatat** | Aksi `INSERT`, jenis berkas, dan nama sertifikat tercatat. |
| **Penghapusan / Penimpaan Dokumen** | `SiswaDashboardController@uploadBerkas` | **Tercatat Parsial** | Berkas lama yang ditimpa dihapus dari storage fisik, tetapi pada log aktivitas dicatat sebagai aksi `INSERT` baru, bukan `REPLACE`/`DELETE`. |
| **Pemilihan Opsi Bayar Tunai & Snap** | `SiswaDashboardController@prosesBayarTunai`, `createSnapPayment` | **Tercatat** | Aksi `INSERT`, kode transaksi tagihan tercatat. |
| **Verifikasi Berkas Siswa oleh TU** | `AdminTUController@verifikasiBerkas` | **Tercatat Lengkap** | Aksi `VERIFIKASI`, memuat snapshot `data_lama` (status lama) dan `data_baru` (status baru + catatan). |
| **Verifikasi Single Item Berkas** | `AdminTUController@verifikasiSingleBerkas` | **Belum Tercatat** | Pembaruan status per berkas pada method ini belum memanggil helper `LogAktivitas::log(...)`. |
| **Buka Kunci Formulir (Unlock)** | `AdminTUController@unlockFormulir` | **Tercatat Lengkap** | Aksi `UNLOCK`, memuat identitas admin TU, snapshot `['is_locked' => true]` ke `['is_locked' => false]`. |
| **Verifikasi Lunas Kasir Tunai TU** | `AdminTUController@konfirmasiBayarTunai` | **Tercatat** | Aksi `UPDATE`, id pembayaran dan nama siswa tercatat. |
| **Input Nilai Tes Seleksi** | `AdminTUController@simpanPenilaian` | **Tercatat** | Aksi `UPDATE`, mencatat skor total nilai seleksi. |
| **Pengalihan Limpahan Jurusan** | `AdminTUController@alihkanJurusan` | **Tercatat** | Aksi `UPDATE`, mencatat pengalihan ke jurusan cadangan. |
| **Pengesahan Kelulusan & Terbit SKL** | `KepalaSekolahController@sahkanKelulusan` | **Tercatat Lengkap** | Aksi `PENGESAHAN_SKL`, status kelulusan final tercatat. |
| **Ekspor Data Laporan (Excel/PDF)** | `AdminTUController@eksporExcel`, `eksporXlsx`, `eksporDapodik`, `cetakRekapPDF` | **Belum Tercatat** | Pengunduhan data pendaftar massal belum dicatat ke audit trail (potensi risiko kebocoran data tak terlacak). |
| **Akses Laporan oleh Kepala Sekolah** | `KepalaSekolahController@laporan` | **Belum Tercatat** | Rute peninjauan laporan hanya bersifat read-only tanpa logging akses. |

### C. Analisis Pertanyaan Kritis Keamanan Audit:
1. **Apakah aktivitas dicatat melalui backend?**  
   **YA**. Seluruh pencatatan dilakukan di sisi server (*server-side execution*) melalui method statis `LogAktivitas::log(...)`, sehingga tidak dapat dimanipulasi atau di-bypass dari klien browser.
2. **Apakah pengguna hanya dapat melihat aktivitas sesuai kewenangannya?**  
   **YA, SECARA AKSES PANEL**, di mana rute audit trail `/kepsek/audit-log` hanya dapat dibuka oleh pengguna dengan guard `admin` dan peran `kepala_sekolah`. Calon Siswa dan Petugas TU tidak dapat membuka halaman ini.
3. **Apakah riwayat aktivitas dapat diubah atau dihapus tanpa izin?**  
   **AMAN DARI SISI APLIKASI WEB**. Aplikasi tidak menyediakan endpoint rute atau tombol controller apa pun untuk melakukan `UPDATE` atau `DELETE` pada tabel `log_aktivitas`. Riwayat bersifat *append-only*. Namun, di tingkat database belum diterapkan trigger MySQL `BEFORE DELETE / BEFORE UPDATE RESTRICT`.
4. **Apakah informasi sensitif terlindungi?**  
   **YA**. Password pengguna tidak pernah disimpan ke dalam kolom deskripsi atau snapshot JSON `data_lama` / `data_baru`. Hanya identitas ID entitas, jenis tindakan, IP, dan User-Agent yang disimpan.

---

## 7. DAFTAR FITUR YANG SUDAH LENGKAP (SUDAH ADA DAN BERFUNGSI)

Fitur-fitur berikut telah terpasang, terhubung secara menyeluruh dari rute, controller, model, migrasi, dan antarmuka Blade, serta berfungsi sesuai spesifikasi:

1. **Registrasi Akun Calon Siswa Baru** (`AuthController@register`).
2. **Login Multi-Identifier Calon Siswa** via NISN/Email/Username (`AuthController@login`).
3. **Login Terisolasi Staf TU & Kepala Sekolah** (`AuthController@adminLogin`).
4. **Logout Mandiri Seluruh Guard** (`AuthController@logout`, `AuthController@adminLogout`).
5. **Dashboard Progres 6-Tahap Calon Siswa** (`SiswaDashboardController@index`).
6. **Pengunggahan Dokumen Berkas Persyaratan ke Private Storage Berizin** (`SiswaDashboardController@uploadBerkas`, `DokumenController@viewBerkas`).
7. **Pengunggahan Sertifikat Piagam Jalur Prestasi** (`SiswaDashboardController@uploadPrestasi`, `DokumenController@viewPrestasi`).
8. **Checklist Status Kelengkapan Dokumen Berkas** (`resources/views/siswa/berkas.blade.php`).
9. **Finalisasi & Kunci Data Formulir Calon Siswa** (`SiswaDashboardController@finalisasi`).
10. **Pengaturan Akun & Ganti Password Calon Siswa** (`SiswaDashboardController@simpanPengaturanAkun`).
11. **Unduh Lembar Formulir Pendaftaran Ramah Cetak** (`SiswaDashboardController@cetakFormulir`).
12. **Unduh Bukti Kwitansi Pelunasan Biaya Formulir** (`SiswaDashboardController@cetakKwitansi`).
13. **Unduh Surat Keterangan Lulus (SKL) Resmi Ber-QR Code** (`SiswaDashboardController@unduhSKL`).
14. **Konfirmasi Pendaftaran Ulang Siswa Baru** (`SiswaDashboardController@konfirmasiDaftarUlang`).
15. **Dashboard Analytics Petugas TU** (`AdminTUController@index`).
16. **Tabel Manajemen & Pencarian Pendaftar Multi-Kriteria** (`AdminTUController@pendaftar`).
17. **Dossier Detail Pendaftar & Peninjau Dokumen Rotasi 90°** (`AdminTUController@detailPendaftar`).
18. **Verifikasi Keseluruhan Berkas Pendaftar** (`AdminTUController@verifikasiBerkas`).
19. **Buka Kunci Formulir Siswa Terkunci (Unlock)** (`AdminTUController@unlockFormulir`).
20. **Verifikasi Granular per Lembar Berkas** (`AdminTUController@verifikasiSingleBerkas`).
21. **Input Nilai Tes Seleksi, Rapor, dan Tes Buta Warna** (`AdminTUController@simpanPenilaian`).
22. **Pengalihan Siswa ke Jurusan Pilihan Kedua (Limpahan Kuota)** (`AdminTUController@alihkanJurusan`).
23. **Master Kapasitas Kuota & Deskripsi Jurusan** (`AdminTUController@updateJurusan`).
24. **Master Pembuatan Gelombang Pendaftaran Baru** (`AdminTUController@simpanGelombang`).
25. **Master Pembuatan Voucher Diskon Pendaftaran** (`AdminTUController@simpanVoucher`).
26. **Manajemen Publikasi Pengumuman Publik & Banner Ticker** (`AdminTUController@simpanPengumuman`).
27. **Saklar Cepat Mode Pemeliharaan (Maintenance Mode Toggle)** (`AdminTUController@toggleMaintenance`).
28. **Pembaruan Profil Lembaga & Konfigurasi Sekolah** (`AdminTUController@simpanPengaturanSekolah`).
29. **Ekspor Data Pendaftar CSV Aman Formula Injection** (`AdminTUController@eksporExcel`).
30. **Ekspor Data Pendaftar Format Excel Asli (.XLSX)** (`AdminTUController@eksporXlsx`).
31. **Ekspor Data Pendaftar Format Baku Dapodikdasmen** (`AdminTUController@eksporDapodik`).
32. **Cetak Rekapitulasi Laporan PPDB Resmi Format PDF** (`AdminTUController@cetakRekapPDF`).
33. **Cetak Dokumen Pendaftar oleh Petugas TU** (`AdminTUController@cetakFormulirPendaftar`, `cetakKartuUjianPendaftar`, `cetakSKLPendaftar`).
34. **Dashboard Eksekutif Kepala Sekolah & Visualisasi Chart.js** (`KepalaSekolahController@index`).
35. **Sidang Pengesahan Kelulusan Final & Penerbitan Nomor SKL** (`KepalaSekolahController@sahkanKelulusan`).
36. **Pusat Rekapitulasi Laporan Resmi Kepala Sekolah** (`KepalaSekolahController@laporan`).
37. **Verifikasi Publik Keaslian Kartu Ujian via QR Code** (`VerifikasiController@verifikasiKartuUjian`).
38. **Verifikasi Publik Keaslian SKL via QR Code** (`VerifikasiController@verifikasiSKL`).
39. **Pengecekan Cepat Kelulusan Publik di Landing Page** (`LandingController@cekKelulusan`).
40. **Pengecekan Validitas & Diskon Voucher via AJAX** (`LandingController@checkVoucher`).
41. **Middleware Keamanan Role Staf Admin & Kepsek** (`AdminRoleMiddleware`).
42. **Middleware Pemeriksaan Mode Pemeliharaan Web** (`CheckMaintenanceMiddleware`).

---

## 8. DAFTAR FITUR YANG PERLU DILENGKAPI (SUDAH ADA TETAPI BELUM LENGKAP)

Fitur-fitur berikut telah diinisiasi dalam proyek, namun memerlukan perbaikan atau penyempurnaan kode:

1. **Penyimpanan Draft Parsial Formulir Biodata**: Method `simpanFormulir` masih mewajibkan seluruh field terisi (`required`). Perlu diubah agar mendukung penyimpanan sebagian isian biodata sebelum finalisasi.
2. **Jadwal Seleksi Masuk pada Kartu Ujian**: Informasi ruangan, tanggal, dan materi ujian pada kartu peserta masih bersifat teks statis, belum bersumber dari data dinamis database.
3. **Pusat Notifikasi Pribadi Calon Siswa**: Notifikasi revisi berkas saat ini hanya berupa kotak alert statis di halaman berkas; belum ada daftar riwayat notifikasi terpadu.
4. **Halaman FAQ & Bantuan Khusus di Panel Siswa**: FAQ interaktif sudah ada di landing page publik, namun belum memiliki rute tersendiri di dalam sidebar calon siswa.
5. **Pratinjau Rangkuman Formulir Sebelum Kunci Permanen**: Tombol finalisasi formulir baru menggunakan dialog konfirmasi browser biasa, belum menampilkan lembar ikhtisar data pendaftaran.
6. **Modul Kasir Loket Pembayaran Tunai Terpadu di Panel TU**: Verifikasi tunai kasir sudah berfungsi, namun tombol aksi hanya ada di halaman detail per siswa, belum memiliki halaman rekapitulasi buku kas khusus.
7. **Deteksi Analitik Pendaftar Ganda**: Validasi unik NISN/Email sudah ada di tingkat basis data, namun panel TU belum memiliki tabel deteksi kesamaan nama/sekolah asal untuk mendeteksi akun ganda non-identik.
8. **Riwayat Aktivitas Pengawasan Kepala Sekolah (Bug Runtime Blade)**: Halaman `resources/views/kepsek/audit_log.blade.php` memiliki kesalahan pemanggilan nama field:
   - Baris 72: memanggil `$log->user->name` (harus diubah menjadi `$log->user->nama`).
   - Baris 86 & 97: memanggil `$log->tipe_aktivitas` (harus diubah menjadi `$log->aksi`).
   - Baris 106: memanggil `$log->id_entitas` (harus diubah menjadi `$log->id_data`).
9. **Pencatatan Audit Trail untuk Verifikasi Berkas Satuan & Unduh Laporan Ekspor**: Method `verifikasiSingleBerkas` dan rute ekspor data belum disisipi pencatatan ke `log_aktivitas`.
10. **Dashboard Monitoring Error**: Error saat ini hanya tercatat di log file teks Monolog, belum ada antarmuka web monitoring error internal.

---

## 9. DAFTAR FITUR YANG BELUM TERSEDIA (BELUM ADA)

Fitur-fitur berikut belum ditemukan implementasinya pada kode sumber, rute, maupun basis data:

1. **Halaman Riwayat Aktivitas Pribadi Calon Siswa** (`/siswa/aktivitas`).
2. **Modul Master Pengaturan Jadwal & Ruang Seleksi** (CRUD Jadwal Seleksi di Panel TU).
3. **Modul Antarmuka Riwayat Aktivitas untuk Petugas TU** (`/admin/audit-log`).
4. **Fitur Import Data Pendaftar dari Excel/CSV** ke dalam sistem.
5. **Manajemen Pengguna Staf & Hak Akses (CRUD Admin)** di antarmuka web.
6. **Ringkasan Finansial Eksekutif di Panel Kepala Sekolah** (Pemasukan, Tunai vs Midtrans, Diskon).
7. **Perekaman Riwayat Sesi Perangkat Login (Device History / Active Sessions)**.
8. **Fitur Logout Seluruh Perangkat Lain** (`Auth::logoutOtherDevices`).
9. **Pembatasan Percobaan Login (Rate Limiting / Throttle)** pada rute otentikasi.
10. **Two-Factor Authentication (2FA)** berbasis TOTP atau WhatsApp OTP.
11. **Peringatan Hitung Mundur Masa Kedaluwarsa Sesi (Session Timeout Warning Modal)**.
12. **Fitur Pencadangan & Pemulihan Basis Data 1-Klik (Database Backup & Restore)**.

---

## 10. DAFTAR FITUR YANG PERLU PENGUJIAN RUNTIME

Fitur-fitur berikut telah terpasang secara struktural pada kode program, namun keabsahan operasionalnya membutuhkan pengujian interaktif langsung di lingkungan runtime aktif:

1. **Transaksi Midtrans Snap (Live / Sandbox Gateway)**:
   - Memeriksa pemunculan pop-up dialog Midtrans Snap di peramban pengguna saat tombol bayar ditekan.
   - Menguji responsivitas penerimaan notifikasi webhook pada rute `/api/midtrans/notification` dengan simulasi payload Midtrans (settlement, capture, pending, expire).
   - Memastikan perhitungan diskon kode voucher terpotong secara benar pada total tagihan Snap.
2. **Pengujian Token QR Code Pemindai Fisik**:
   - Memindai cetakan Kartu Ujian dan Surat Keterangan Lulus (SKL) menggunakan kamera ponsel pintar untuk memastikan tautan publik `/verifikasi/kartu-ujian/{token}` dan `/verifikasi/skl/{token}` menampilkan status keabsahan dokumen tanpa galat 404 atau 500.
3. **Pengujian Unggah File Fisik Dokumen Kependudukan Berukuran Maksimal (3 MB)**:
   - Menguji pengunggahan berkas gambar format JPG/PNG dan dokumen PDF pada batas ukuran 3072 KB.
   - Memastikan file benar-benar tersimpan di folder private `storage/app/berkas_pendaftar` dan dapat dialirkan kembali (*stream*) via `DokumenController`.
4. **Pengujian Rotasi Gambar Berkas di Berbagai Peramban**:
   - Memastikan fungsi JavaScript `rotateImage` pada halaman detail pendaftar di panel TU dapat memutar dokumen orientasi lanskap/miring secara mulus di Google Chrome, Mozilla Firefox, dan peramban ponsel.
5. **Pengujian Ekspor File Excel Native (`.xlsx`) pada Perangkat dengan RAM Terbatas**:
   - Memastikan service `XlsxExportService` mampu mengekspor seluruh basis data pendaftar tanpa mengalami *memory exhaustion* atau *timeout*.
6. **Pengujian Saklar Mode Pemeliharaan (Maintenance Mode)**:
   - Mengaktifkan mode maintenance dari panel TU dan memastikan pengunjung publik menerima status HTTP 503 dengan tampilan blade `maintenance.blade.php`, sembari memastikan sesi Petugas TU tetap dapat mengelola dashboard.

---

## 11. REKOMENDASI PRIORITAS IMPLEMENTASI

Untuk menyempurnakan sistem menuju kesiapan operasional penuh (*production-ready*), perbaikan disarankan dilakukan berdasarkan 3 fase prioritas berikut:

### Prioritas 1: Perbaikan Bug & Keamanan Kritis (Critical & Quick Fixes)
1. **Perbaikan Variabel Blade pada `resources/views/kepsek/audit_log.blade.php`**:
   - Ganti `$log->user->name` menjadi `$log->user->nama`.
   - Ganti `$log->tipe_aktivitas` menjadi `$log->aksi`.
   - Ganti `$log->id_entitas` menjadi `$log->id_data`.
2. **Pemasangan Rate Limiting Otentikasi**:
   - Tambahkan middleware `throttle:6,1` pada rute POST `/login` dan POST `/admin/login` di `routes/web.php` untuk mencegah serangan brute-force.
3. **Penyempurnaan Logging Audit Trail**:
   - Tambahkan pemanggilan `LogAktivitas::log(...)` pada method `verifikasiSingleBerkas` dan rute unduh ekspor dokumen (`eksporExcel`, `eksporXlsx`, `eksporDapodik`).

### Prioritas 2: Penyempurnaan Fungsionalitas Operasional Harian (Operational Usability)
1. **Dukungan Penyimpanan Draft Parsial Biodata**:
   - Sesuaikan aturan validasi di `simpanFormulir` agar siswa dapat mencicil pengisian data sebelum difinalisasi.
2. **Modul Kasir Loket Pembayaran Tunai Terpadu**:
   - Buat halaman terpusat di panel TU yang menampilkan seluruh pendaftar yang memilih metode pembayaran tunai loket beserta tombol cepat verifikasi pelunasan.
3. **Ringkasan Finansial di Panel Kepala Sekolah**:
   - Tambahkan indikator total penerimaan dana formulir dan rekapitulasi pembayaran pada dashboard eksekutif Kepala Sekolah.
4. **Pengaturan Jadwal Ujian Dinamis**:
   - Buat tabel master jadwal seleksi agar tanggal, waktu sesi, dan ruangan yang tercetak di kartu ujian peserta dapat diatur dinamis per gelombang.

### Prioritas 3: Peningkatan Fitur Lanjutan (Enterprise & Security Enhancements)
1. **Fitur Import Data Excel Pendaftar Offline**:
   - Sediakan modul import data siswa dari file template Excel bagi panitia TU untuk mengakomodasi pendaftaran luring (*walk-in applicants*).
2. **Antarmuka Manajemen Akun Staf (CRUD Admin)**:
   - Buat antarmuka berbasis web untuk menambah atau menonaktifkan akun staf TU dan Kepala Sekolah secara aman.
3. **Perekaman Riwayat Perangkat & Logout Semua Perangkat**:
   - Aktifkan perekaman sesi perangkat dan tombol logout dari semua perangkat saat pembaruan kata sandi.
4. **Fitur Pencadangan Database 1-Klik**:
   - Sediakan utilitas pencadangan database terkompresi di menu pengaturan admin.

---
*Dokumen audit ini disusun secara independen dan akurat berdasarkan hasil inspeksi statis kode sumber dan arsitektur basis data sistem PPDB SMK Islam Bina Khalifah Bangsa.*

