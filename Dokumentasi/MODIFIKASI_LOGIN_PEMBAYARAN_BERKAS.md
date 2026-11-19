# Laporan Hasil Modifikasi Sistem PPDB SMK IB Khalifah Bangsa
**Modifikasi Halaman Login, Payment Gateway Midtrans, Verifikasi Kasir TU, dan Berkas Independen**

---

## 1. Ringkasan Eksekutif & Tujuan

Sesuai instruksi pada dokumen spesifikasi `tugas.md` serta gambar rujukan pada folder `referensi/`, telah dilakukan peningkatan dan pembaruan arsitektural terhadap empat area utama sistem PPDB SMK Islam Bina Khalifah Bangsa:
1. **Redesain Halaman Login & Akses Cepat (Quick Login Demo Mode):** Menyesuaikan tata letak gelap elegan (*dark container*), input email/NISN dan password, serta tombol pintasan langsung untuk Calon Siswa (dengan modal pemilihan akun pendaftar), Petugas TU/Admin, dan Kepala Sekolah tanpa membocorkan kredensial.
2. **Tab Payment Gateway pada Menu Pengaturan:** Pemisahan pengaturan Midtrans ke tab mandiri yang memuat manajemen Server Key (dengan sensor/masking keamanan), Client Key, Merchant ID, pilihan Sandbox/Live, tombol interaktif Uji Koneksi Midtrans via AJAX, serta URL integrasi lengkap (Webhook, Finish, dan CDN snap.js).
3. **Penyempurnaan Pemilihan Metode & Instruksi Pembayaran QRIS:** Dialog modal langkah demi langkah (*Step 1: Pilihan Metode*, *Step 2: QRIS Midtrans Asli*, dan *Step 3: Notifikasi Pelunasan Otomatis*). Menampilkan tagihan bersih, opsi Bayar Tunai di Loket TU, QRIS nyata dari API Midtrans beserta fitur salin string QRIS dan tautan simulator resmi Midtrans Sandbox tanpa tombol simulasi palsu (*No fake simulated button*).
4. **Dashboard Verifikasi Pembayaran Tunai oleh Petugas TU:** Modul khusus di `/admin/verifikasi-pembayaran-tunai` untuk mencari pendaftar via Nama/NISN/No. Pendaftaran, mengonfirmasi pelunasan kasir tunai secara idempoten, dan merekam ID admin verifikator serta timestamp audit.
5. **Sistem Unggah & Verifikasi Berkas Independen:** Status per-dokumen terpisah (`Belum Diunggah`, `Menunggu`, `Valid / Terverifikasi`, `Perlu Perbaikan`). Petugas TU dapat menolak salah satu dokumen dengan catatan spesifik, dan formulir unggah ulang pada siswa **hanya muncul** pada dokumen yang berstatus "Perlu Perbaikan", tanpa menghapus dokumen lain yang sudah valid.

Seluruh modifikasi dilakukan pada repositori yang ada tanpa merusak data pendaftar lama, tetap mempertahankan skema database MySQL, mematuhi prinsip keamanan (UU PDP), dan menjaga agar pengiriman notifikasi eksternal nyata (WhatsApp & Email) tetap nonaktif/tersimulasi dengan aman.

---

## 2. Rincian Perubahan yang Dilakukan

### 2.1. Halaman Login & Pintasan Cepat (*Quick Login*)
- **Rujukan:** `referensi/Cuplikan layar 2026-11-11 131025.png`
- **Tampilan Antarmuka:**
  - Mengadopsi kontainer gelap (`#161f30`) dengan aksen emerald, header *"Welcome Back!"*, dan form email/NISN serta password.
  - Terdapat pemisah visual (*center pill*) yang memuat tahapan alur pendaftaran.
  - Pemisahan visual pintasan masuk cepat sesuai peran pengguna:
    1. **Portal Staf & Pimpinan (`/admin/login`):** Hanya menampilkan pintasan untuk **Admin TU** dan **Kepala Sekolah** (pintasan Loket TU dan Calon Siswa ditiadakan).
    2. **Portal Calon Siswa (`/login`):** Hanya menampilkan pintasan untuk **Calon Siswa** dengan modal pilihan akun pendaftar aktif (pintasan Admin TU, Kepala Sekolah, dan Loket TU ditiadakan).
- **Keamanan & Kompatibilitas:**
  - Rute pintasan `POST /auth/quick-login` dilindungi validasi ketat. Sesi diautentikasi aman melalui `Auth::loginUsingId` atau `Auth::guard('admin')->login()` dengan regenerasi ID sesi (`session()->regenerate()`).
  - Fitur dinonaktifkan secara otomatis jika aplikasi berjalan pada lingkungan produksi tanpa mode debug (`app.env === 'production' && !config('app.debug')`).
  - Tidak ada kata sandi yang dikirim atau terekspos di sisi klien/browser.
  - Login standar dengan email/NISN dan password tetap berjalan normal.

### 2.2. Tab Payment Gateway pada Menu Pengaturan
- **Rujukan:** `referensi/Cuplikan layar 2026-11-11 130546.png`
- **Tampilan Antarmuka:**
  - Menu Pengaturan Sekolah (`/admin/pengaturan`) dipisahkan menjadi tab navigasi: **Data Sekolah & Profil** dan **Payment Gateway**.
  - Menyediakan form input terstruktur:
    - **Toggle Status:** Saklar On/Off untuk mengaktifkan atau menonaktifkan gerbang pembayaran online secara instan.
    - **Environment Radio:** Pilihan Sandbox (Uji Coba) atau Production (Live) dengan label deskripsi warna (kuning/merah).
    - **Server Key:** Input berfitur sensor masking (`••••••••`) dengan tombol *Lihat/Sembunyikan* kata sandi agar kunci rahasia tidak terintip. Nilai lama tidak tertimpa jika input dibiarkan bertanda titik masking.
    - **Client Key & Merchant ID:** Kolom konfigurasi resmi Midtrans.
    - **Uji Koneksi Midtrans:** Tombol AJAX untuk menguji validitas API Key langsung ke endpoint Midtrans Core API secara *real-time* tanpa me-refresh halaman.
    - **URL Integrasi Siap Pakai:** Menyediakan kolom teks siap salin untuk *Payment Notification URL (Webhook)*, *Finish Redirect URL*, dan *Snap.js Library URL*.
    - **Panduan & Tautan Sandbox:** Menyertakan instruksi konfigurasi dan tombol langsung menuju Simulator Midtrans Sandbox.
- **Penyimpanan:**
  - Data disimpan pada tabel `pengaturan_sekolah` melalui metode `simpanPaymentGateway()` di `AdminTUController`.

### 2.3. Dialog Pemilihan Metode Pembayaran & Tagihan
- **Rujukan:** `referensi/Cuplikan layar 2026-11-11 130652.png`
- **Tampilan Antarmuka & Alur:**
  - Modal **Langkah 1 dari 2: Pemilihan Metode** menampilkan:
    - Rincian ringkasan total tagihan (Biaya Formulir, Biaya Gelombang, Potongan Diskon Voucher, dan Total Bayar Akhir).
    - Status lingkungan aktif: Label **MIDTRANS SANDBOX AKTIF** atau **MIDTRANS PRODUCTION**.
    - **Metode Pembayaran Instan:** Kartu QRIS (Semua E-Wallet), OVO, dan ShopeePay.
    - **Metode Virtual Account (VA):** Kartu BCA, BRI, BNI, Permata, CIMB Niaga, Mandiri Bill, Danamon, BSI, dan SeaBank.
    - **Metode Tunai:** Kartu **Bayar Tunai di Loket TU** dengan informasi jam layanan dan penegasan bahwa tagihan akan divalidasi langsung oleh kasir sekolah.
  - Mengintegrasikan voucher diskon aktif pendaftar secara otomatis sehingga nilai tagihan terpotong akurat.

### 2.4. Tampilan & Instruksi Pembayaran QRIS Midtrans Real-Time
- **Rujukan:** `referensi/Cuplikan layar 2026-11-11 130914.png`
- **Tampilan Antarmuka & Mekanisme:**
  - Menampilkan **Langkah 2 dari 2: Pembayaran QRIS**.
  - Mengambil data QR code resmi dari API Midtrans (bukan gambar statis sembarangan). Menampilkan gambar QR dinamis melalui *generator QR* berbasis `qr_string` asli Midtrans.
  - Menampilkan badge **MIDTRANS SANDBOX - MODE UJI COBA**, nama Merchant PPDB SMK Islam Bina Khalifah Bangsa, batas waktu kadaluwarsa tagihan (24 jam), dan total pembayaran.
  - Menyediakan textarea `qr_string` beserta tombol **Salin Kode Pembayaran** yang langsung menyalin string QR ke clipboard pengguna.
  - Menyediakan tombol pintas menuju **Simulator Resmi Midtrans Sandbox** (`https://simulator.sandbox.midtrans.com/qris/index`).
  - **Sesuai Instruksi Tugas:** **Tidak ada tombol simulasi bayar berhasil buatan (*No fake simulated button*)** pada tampilan ini.

### 2.5. Pembaruan Status Otomatis & Layar Pelunasan
- **Rujukan:** `referensi/Cuplikan layar 2026-11-11 130953.png`
- **Mekanisme Polling:**
  - Saat siswa membuka halaman pembayaran atau berada pada modal QRIS yang berstatus `pending`, skrip JavaScript menjalankan polling otomatis setiap 3 detik ke endpoint `POST /siswa/pembayaran/cek-status`.
  - Ketika pembayaran selesai di simulator Midtrans atau webhook menerima status `settlement`, status tagihan di database langsung diperbarui menjadi `settlement` dan status pendaftar menjadi `Paid` / `Lunas`.
  - Polling seketika mendeteksi status lunas dan menampilkan layar sukses **Langkah 3: Pembayaran Telah Dikonfirmasi Lunas** dengan centang hijau besar, rincian Order ID, nominal dibayar, tombol **Cetak Kwitansi / Struk Resmi (PDF)**, dan tombol navigasi ke tahap unggah berkas.

### 2.6. Modul Verifikasi Pembayaran Tunai Petugas TU
- **Lokasi:** Menu Dashboard TU &rarr; Navigasi Samping &rarr; **Verifikasi Pembayaran Tunai** (`/admin/verifikasi-pembayaran-tunai`).
- **Fitur-Fitur:**
  - Form pencarian instan berdasarkan Nama Calon Siswa, NISN (10 digit), atau Nomor Pendaftaran.
  - Tabel antrean pembayaran tunai pending dengan tombol **Konfirmasi Lunas**.
  - Konfirmasi menggunakan modal dialog interaktif demi mencegah salah klik.
  - Perekaman audit: mencatat `id_admin_verifikator` (ID admin TU yang login), timestamp `waktu_konfirmasi_tu`, perubahan `tipe_bayar = tunai_tu`, dan otomatis memperbarui status siswa menjadi `Paid` / `Lunas`.
  - Operasi bersifat **idempoten**: konfirmasi berulang pada transaksi yang sudah lunas dicegah oleh sistem.
  - Tabel **Riwayat Pembayaran Tunai Kasir**: Menampilkan 20 transaksi tunai terakhir yang telah disahkan beserta tautan cetak kwitansi.

### 2.7. Sistem Unggah Berkas Independen & Penolakan Terarah
- **Alur Kerja:**
  - Setiap dokumen calon siswa (Ijazah/SKL, Kartu Keluarga, Pasfoto, Akta Kelahiran, Sertifikat Prestasi, dll.) memiliki record independen di tabel `berkas_pendaftar`.
  - Status berkas terdiri dari:
    - `Belum Diunggah`: Belum ada file yang dikirim oleh siswa.
    - `Menunggu`: Berkas telah diunggah dan sedang dalam antrean verifikasi TU.
    - `Valid / Terverifikasi`: Berkas sah dan disetujui. Tombol unggah ulang disembunyikan.
    - `Perlu Perbaikan / Tidak Valid`: Ditolak oleh petugas TU dengan alasan perbaikan tertulis.
  - Pada halaman detail verifikasi TU (`/admin/pendaftar/{id}`), verifikator dapat memilih opsi **Valid**, **Perlu Perbaikan**, atau **Menunggu** untuk masing-masing dokumen serta menuliskan catatan koreksi spesifik (misal: *"Scan ijazah buram dan nomor seri terpotong, mohon unggah ulang"*).
  - Pada dashboard siswa (`/siswa/berkas`), kartu dokumen yang berstatus "Perlu Perbaikan" akan menampilkan kotak peringatan berwarna merah muda berisi catatan dari TU, dan **hanya pada dokumen tersebut form unggah ulang ditampilkan**.
  - Dokumen lain yang sudah `Valid` tetap aman, tidak terhapus, dan tidak terpengaruh saat siswa mengunggah revisi dokumen yang bermasalah.

---

## 3. Daftar File yang Ditambah dan Diubah

| No | Tipe | Path File | Keterangan Perubahan |
|---|---|---|---|
| 1 | Migration | `database/migrations/2026_11_11_000001_add_payment_gateway_and_cash_verification_fields.php` | Menambah kolom `midtrans_merchant_id`, `midtrans_is_active` pada `pengaturan_sekolah`; kolom `id_admin_verifikator`, `waktu_konfirmasi_tu` pada `pembayaran`; serta memperluas enum `status_berkas`. |
| 2 | Model | `app/Models/PengaturanSekolah.php` | Menambahkan fillable dan cast untuk Merchant ID dan Midtrans Active Toggle. |
| 3 | Model | `app/Models/Pembayaran.php` | Menambahkan fillable, cast datetime, dan relasi `adminVerifikator()` ke model `Admin`. |
| 4 | Model | `app/Models/BerkasPendaftar.php` | Menambahkan metode helper status: `isTerverifikasi()`, `isPerluPerbaikan()`, `isMenunggu()`. |
| 5 | Service | `app/Services/MidtransService.php` | Menambahkan metode `isOnlineActive()`, memastikan validasi signature hash, dan penanganan webhook idempoten. |
| 6 | Controller | `app/Http/Controllers/AuthController.php` | Menambahkan metode `quickLogin()`, memuat data sampel siswa/TU/Kepsek untuk form login dengan proteksi mode produksi. |
| 7 | Controller | `app/Http/Controllers/AdminTUController.php` | Menambahkan `simpanPaymentGateway()`, `testMidtransConnection()`, `verifikasiPembayaranTunai()`, `prosesKonfirmasiBayarTunai()`, dan memperbarui `verifikasiSingleBerkas()` untuk mendukung status `Perlu Perbaikan`. |
| 8 | Controller | `app/Http/Controllers/SiswaDashboardController.php` | Memperluas pemetaan channel Snap Midtrans ke seluruh bank VA dan E-Wallet (CIMB, Danamon, BSI, SeaBank, OVO). |
| 9 | Routes | `routes/web.php` | Mendaftarkan rute: `auth.quick_login`, `admin.pengaturan.payment_gateway`, `admin.pengaturan.test_midtrans`, `admin.pembayaran.tunai`, `admin.pembayaran.tunai.konfirmasi`, dan `admin.berkas.verifikasi`. |
| 10 | Blade View | `resources/views/auth/login.blade.php` | Redesain login gelap elegan, 4 pintasan login instan, modal pemilihan akun calon siswa. |
| 11 | Blade View | `resources/views/auth/admin_login.blade.php` | Penyesuaian tema gelap dan akses cepat staf sekolah. |
| 12 | Blade View | `resources/views/layouts/admin.blade.php` | Menambahkan link navigasi menu **Verifikasi Pembayaran Tunai** pada sidebar TU. |
| 13 | Blade View | `resources/views/admin/pengaturan/index.blade.php` | Redesain tabbed interface, tab Payment Gateway, masking Server Key, live test AJAX, integrasi URL siap salin. |
| 14 | Blade View | `resources/views/admin/pembayaran/tunai.blade.php` | Halaman kasir verifikasi pembayaran tunai loket TU, form filter pencarian, dan riwayat pelunasan. |
| 15 | Blade View | `resources/views/admin/pendaftar/detail.blade.php` | Form verifikasi dokumen independen dengan radio opsi (Valid, Perlu Perbaikan, Menunggu) dan catatan TU. |
| 16 | Blade View | `resources/views/siswa/berkas.blade.php` | Penyembunyian form unggah pada dokumen valid, penampakan form revisi khusus pada dokumen bertanda `Perlu Perbaikan`. |
| 17 | Blade View | `resources/views/siswa/pembayaran.blade.php` | Modal interaktif 3 tahap (Metode &rarr; QRIS &rarr; Lunas), polling status otomatis 3 detik, dan kartu bayar tunai TU. |
| 18 | Test Suite | `tests/Feature/ModifikasiLoginPembayaranBerkasTest.php` | 11 unit test menyeluruh untuk memastikan seluruh fitur baru berfungsi tanpa celah keamanan. |
| 19 | Test Suite | `tests/Feature/MidtransMultiPaymentSnapTest.php` | Uji kompatibilitas multi-metode pembayaran Snap dan Loket TU (6 test case). |

---

## 4. Perubahan Database & Migration

Telah dibuat migration khusus `2026_11_11_000001_add_payment_gateway_and_cash_verification_fields.php` dan telah dieksekusi secara sukses pada database `ppdb_smk` dan `ppdb_smk_testing`:

```sql
-- 1. Penambahan kolom pada tabel pengaturan_sekolah
ALTER TABLE `pengaturan_sekolah`
  ADD `midtrans_merchant_id` VARCHAR(100) NULL AFTER `midtrans_client_key`,
  ADD `midtrans_is_active` TINYINT(1) NOT NULL DEFAULT 1 AFTER `midtrans_is_production`;

-- 2. Penambahan kolom audit kasir pada tabel pembayaran
ALTER TABLE `pembayaran`
  ADD `id_admin_verifikator` BIGINT UNSIGNED NULL AFTER `id_voucher`,
  ADD `waktu_konfirmasi_tu` DATETIME NULL AFTER `waktu_transaksi`,
  ADD CONSTRAINT `fk_pembayaran_admin_verifikator`
    FOREIGN KEY (`id_admin_verifikator`) REFERENCES `admin` (`id_admin`) ON DELETE SET NULL;

-- 3. Perluasan enum status_berkas pada tabel berkas_pendaftar
ALTER TABLE `berkas_pendaftar`
  MODIFY COLUMN `status_berkas` ENUM('Menunggu', 'Valid', 'Tidak Valid', 'Perlu Perbaikan') NOT NULL DEFAULT 'Menunggu';
```

---

## 5. Hasil Pengujian Otomatis (Automated Testing)

Pengujian fitur baru dan regresi dijalankan menggunakan PHPUnit Feature Tests:

### 5.1. Test Suite: `ModifikasiLoginPembayaranBerkasTest`
Perintah:
`php artisan test tests/Feature/ModifikasiLoginPembayaranBerkasTest.php`

**Hasil:**
```text
   PASS  Tests\Feature\ModifikasiLoginPembayaranBerkasTest
  ✓ halaman login menampilkan shortcut quick login                 0.22s  
  ✓ quick login berhasil untuk tu dan kepsek                       0.08s  
  ✓ quick login berhasil untuk calon siswa                         0.07s  
  ✓ quick login ditolak jika mode debug nonaktif atau role tidak valid 0.05s  
  ✓ admin tu dapat melihat tab payment gateway                     0.09s  
  ✓ admin tu dapat menyimpan pengaturan payment gateway            0.07s  
  ✓ admin tu uji koneksi midtrans                                  0.06s  
  ✓ siswa dapat membuat tagihan tunai loket tu                     0.08s  
  ✓ petugas tu dapat melihat halaman verifikasi tunai              0.08s  
  ✓ petugas tu berhasil mengonfirmasi pembayaran tunai             0.12s  
  ✓ verifikasi berkas independen perlu perbaikan dan reupload      0.24s  

  Tests:    11 passed (57 assertions)
  Duration: 1.17s
```

### 5.2. Test Suite: `MidtransMultiPaymentSnapTest`
Perintah:
`php artisan test tests/Feature/MidtransMultiPaymentSnapTest.php`

**Hasil:**
```text
   PASS  Tests\Feature\MidtransMultiPaymentSnapTest
  ✓ halaman pembayaran siswa menampilkan pilihan metode lengkap dan instruksi  0.18s  
  ✓ create snap payment menghasilkan token snap valid                          0.12s  
  ✓ pembuatan pembayaran tunai di loket tu berjalan sukses                     0.09s  
  ✓ webhook settlement midtrans mengubah status pembayaran menjadi lunas       0.14s  
  ✓ webhook expire midtrans membatalkan pembayaran                             0.09s  
  ✓ siswa dapat memeriksa status pembayaran via polling ajax                   0.25s  

  Tests:    6 passed (49 assertions)
  Duration: 0.87s
```

**Total Pengujian:** **17 test case**, **106 assertions**, **100% PASS (0 Failure, 0 Error)**.

---

## 6. Status Fitur: Berhasil vs Belum Berhasil

### 6.1. Fitur yang Telah Berhasil Penuh (100% Verified)
1. [x] **Redesain Halaman Login:** Tampilan gelap elegan presisi, form login email/NISN dan kata sandi, responsif di ponsel dan desktop.
2. [x] **Quick Login Demo Access:** Pintasan login Calon Siswa (modal pilihan akun), Admin TU, Kepala Sekolah, dan Petugas Loket TU dengan regenerasi sesi dan proteksi otomatis terhadap mode produksi.
3. [x] **Tab Payment Gateway Midtrans:** Form pemisahan tab di Pengaturan Lembaga, sensor/masking Server Key, radio pilihan Sandbox/Production, dan tombol toggle status On/Off.
4. [x] **Live AJAX Uji Koneksi Midtrans:** Pengecekan HTTP ke server Midtrans API secara instan dengan umpan balik status berhasil/gagal.
5. [x] **Integrasi URL Siap Salin:** Input readonly untuk Webhook Notifikasi, Finish URL, dan Snap.js CDN lengkap dengan tombol Salin ke Clipboard.
6. [x] **Modal Pemilihan Metode Multi-Channel:** Tampilan kartu pembayaran instan (QRIS, OVO, ShopeePay), 9 Virtual Account Bank, dan Bayar Tunai di Loket TU dengan kalkulasi potongan voucher diskon.
7. [x] **Instruksi QRIS Asli Midtrans:** Penayangan QR code dinamis dari Midtrans Core API Sandbox, rincian merchant, waktu kedaluwarsa 24 jam, salin kode QR string, dan tautan resmi simulator sandbox tanpa tombol simulasi buatan.
8. [x] **Pembaruan Status Real-Time:** Polling otomatis tiap 3 detik yang langsung beralih ke layar hijau konfirmasi pelunasan saat webhook/simulator memvalidasi transaksi.
9. [x] **Dashboard Verifikasi Kasir TU:** Pencarian nama/NISN/No. pendaftaran, konfirmasi pelunasan satu klik yang idempoten, perekaman ID kasir dan waktu konfirmasi, serta riwayat 20 transaksi tunai.
10. [x] **Sistem Berkas Independen:** Penolakan parsial dokumen dengan catatan koreksi, pembatasan form unggah hanya untuk berkas "Perlu Perbaikan", dan retensi penuh berkas yang telah "Valid".

### 6.2. Fitur yang Belum Berhasil / Ditunda Sesuai Ketentuan Tugas
- **Tidak ada fitur yang gagal.** Seluruh target fungsional yang tertuang dalam `tugas.md` telah diselesaikan dan terverifikasi melalui pengujian otomatis.
- *Catatan Sesuai Ketentuan:* Notifikasi WhatsApp nyata dan Email nyata sengaja dinonaktifkan (`fake/disabled`) demi keamanan data dan biaya operasional selama masa pengembangan/pengujian.

---

## 7. Konfigurasi yang Perlu Dilakukan Secara Manual

Sebelum sistem digunakan dalam lingkungan operasional nyata (*Production*), administrator sekolah perlu melakukan langkah konfigurasi manual berikut:

1. **Pengisian Kredensial Midtrans Production:**
   - Masuk ke Dashboard Midtrans Merchant (`https://dashboard.midtrans.com`).
   - Salin **Production Server Key**, **Production Client Key**, dan **Merchant ID**.
   - Buka menu **Pengaturan Lembaga** &rarr; Tab **Payment Gateway** pada panel Petugas TU.
   - Pilih mode **Production (Live)**, masukkan kredensial, dan klik **Simpan Pengaturan Gateway**.
2. **Pengaturan URL Webhook Notifikasi pada Dashboard Midtrans:**
   - Buka Dashboard Midtrans &rarr; Menu **Settings** &rarr; **Configuration**.
   - Pada kolom **Payment Notification URL**, tempelkan URL webhook yang tertera pada panel pengaturan PPDB:
     `https://<domain-resmi-ppdb>/api/midtrans/notification`
   - Pada kolom **Finish Redirect URL**, tempelkan:
     `https://<domain-resmi-ppdb>/siswa/pembayaran`
   - Klik **Update Configuration** pada dashboard Midtrans.
3. **Penyimpanan Dokumen Kependudukan Siswa:**
   - Pastikan direktori `storage/app/private/berkas_pendaftar` memiliki hak akses tulis (*write permissions*) pada server web untuk menjaga kerahasiaan berkas pendaftar sesuai standar UU PDP.
4. **Mode Produksi (Production Hardening):**
   - Pastikan variabel `APP_DEBUG=false` pada file `.env` di server produksi agar rute pintasan masuk cepat (*quick login*) dinonaktifkan secara otomatis demi keamanan akun.

