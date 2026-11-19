# PANDUAN PENGGUNAAN SISTEM PPDB
## SMK ISLAM BINA KHALIFAH BANGSA (KOTA METRO, LAMPUNG)

Buku panduan ini disusun untuk memberikan pedoman operasional lengkap bagi pengguna ketiga peran: **Calon Siswa**, **Petugas Tata Usaha (TU)**, dan **Kepala Sekolah**.

---

## DAFTAR ISI
1. [Panduan Portal Calon Siswa](#1-panduan-portal-calon-siswa)
2. [Panduan Panel Petugas Tata Usaha (TU)](#2-panduan-panel-petugas-tata-usaha-tu)
3. [Panduan Panel Kepala Sekolah](#3-panduan-panel-kepala-sekolah)
4. [Penanganan Masalah Umum & Bantuan](#4-penanganan-masalah-umum--bantuan)

---

## 1. PANDUAN PORTAL CALON SISWA

### A. Registrasi Akun
1. Buka peramban (*browser*) dan akses alamat: `http://localhost:8000/daftar`.
2. Masukkan **Nama Lengkap**, **NISN (10 Digit)**, **Alamat Email**, **No. WhatsApp**, dan **Kata Sandi**.
3. Klik tombol **"Daftar Sekarang"**. Akun Anda akan langsung aktif dan otomatis dialihkan ke halaman dashboard.

### B. Login Akun
1. Akses halaman: `http://localhost:8000/login`.
2. Masukkan identitas akun pada kolom login (dapat menggunakan **NISN**, **Email**, atau **Username**) dan masukkan kata sandi Anda.
3. Klik **"Masuk ke Portal"**.

### C. Pengisian Formulir & Penyimpanan Draft
1. Buka menu **"Formulir Biodata"** pada sidebar kiri.
2. Isi data pribadi calon siswa, asal sekolah, data orang tua/wali, dan pilihan program keahlian.
3. Anda dapat menyimpan data sewaktu-waktu meskipun belum terisi semua dengan menekan tombol **"Simpan Draft Sementara"**.
4. Apabila seluruh data sudah lengkap dan yakin benar, tekan tombol **"Finalisasi & Kunci Data"**.
   > *Perhatian: Data yang telah difinalisasi akan terkunci secara otomatis. Jika terdapat kesalahan penulisan setelah dikunci, hubungi Petugas TU untuk membukanya kembali.*

### D. Unggah Dokumen Berkas & Prestasi
1. Buka menu **"Upload Berkas"**.
2. Unggah pindaian (*scan*) atau foto dokumen asli:
   - Ijazah / Surat Keterangan Lulus (SKL) SMP
   - Kartu Keluarga (KK)
   - Akta Kelahiran
   - Pasfoto Resmi (latar belakang merah/biru)
3. Apabila memiliki piagam kejuaraan, unggah pada bagian **"Prestasi Tambahan"** untuk memperoleh skor bobot seleksi.

### E. Pembayaran Biaya Pendaftaran
1. Buka menu **"Pembayaran PPDB"**.
2. Masukkan kode voucher diskon jika memilikinya (misal: `TAHFIZJUARA` atau `DISMP25`).
3. Pilih salah satu metode pembayaran:
   - **Midtrans Online**: Menghasilkan pop-up QRIS, Virtual Account Bank, atau E-Wallet untuk pelunasan instan.
   - **Bayar Tunai di Loket TU**: Menghasilkan kode tagihan untuk diserahkan dan dilunasi langsung kepada staf kasir TU sekolah.
4. Setelah berstatus **LUNAS (Paid)**, tombol cetak kwitansi akan aktif.

### F. Jadwal Seleksi & Cetak Kartu Ujian
1. Buka menu **"Jadwal dan Kartu Ujian"**.
2. Pantau agenda tes seleksi masuk dan nomor ruangan yang dialokasikan oleh panitia.
3. Klik tombol **"Cetak Kartu Ujian (PDF)"** untuk mencetak kartu ber-QR Code resmi sebagai syarat wajib saat hadir di ruang ujian.

### G. Pengumuman Kelulusan & Cetak SKL
1. Status kelulusan ditampilkan pada kartu pengumuman Dashboard utama setelah disahkan Kepala Sekolah.
2. Jika dinyatakan **DITERIMA**, buka menu cetak **"Surat Kelulusan (SKL)"** untuk mengunduh bukti tanda lulus resmi sekolah.
3. Buka menu **"Daftar Ulang"** untuk mengisi konfirmasi kesediaan dan ukuran seragam sekolah.

### H. Memantau Riwayat Aktivitas & Bantuan
1. Buka menu **"Riwayat Aktivitas"** untuk melihat rekam jejak kapan saja akun Anda login, menyimpan draft, atau mengunggah berkas.
2. Buka menu **"Pusat Bantuan & FAQ"** untuk melihat panduan tahapan, kontak panitia, dan tanya-jawab umum.

---

## 2. PANDUAN PANEL PETUGAS TATA USAHA (TU)

### A. Login Petugas TU
1. Buka alamat: `http://localhost:8000/admin/login`.
2. Masukkan username (contoh: `admin_tu`) dan kata sandi.
3. Anda akan diarahkan ke **Dashboard TU**.

### B. Verifikasi Dokumen Pendaftar
1. Buka menu **"Data Pendaftar"**.
2. Klik tombol **"Detail / Periksa"** pada siswa yang dituju.
3. Pada panel berkas digital, klik pratinjau dokumen. Anda dapat memutar orientasi gambar (rotasi 90°) atau memperbesar (*zoom*) untuk membaca tulisan kecil.
4. Tentukan status berkas: **Valid** atau **Perlu Revisi (Ditolak)** dengan menyertakan alasan perbaikan yang jelas.
5. Klik **"Simpan Verifikasi"**.

### C. Membuka Kunci Formulir (Unlock)
1. Jika siswa mengajukan permohonan koreksi data, buka halaman detail pendaftar tersebut.
2. Klik tombol **"Buka Kunci Formulir"** pada kotak status biodata.
3. Formulir siswa akan kembali berstatus draft terbuka (*is_locked = false*) dan aksi unlock Anda akan otomatis tercatat di log aktivitas sistem.

### D. Konfirmasi Pembayaran Tunai Loket Kasir
1. Buka halaman detail pendaftar yang membayar tunai.
2. Pada bagian tagihan pembayaran, klik tombol **"Konfirmasi Pembayaran Tunai (Lunas)"**.
3. Status pendaftar otomatis berubah menjadi **Paid**, transaksi berstatus **settlement**, dan kartu ujian siswa siap diterbitkan.

### E. Manajemen Jadwal & Ruang Seleksi
1. Buka menu **"Jadwal Seleksi"** untuk menambah sesi ujian (tanggal pelaksanaan, jam mulai/selesai, gelombang).
2. Buka menu **"Ruang Seleksi"** untuk mendaftarkan ruang tes beserta kapasitas kursi maksimal.
3. Buka menu **"Penempatan Peserta"**:
   - Klik **"Alokasi Otomatis"** untuk mendistribusikan seluruh peserta lunas ke dalam ruangan yang masih memiliki kursi kosong secara berurutan.
   - Gunakan fitur **"Penempatan Manual"** jika ingin memindahkan peserta tertentu ke ruangan lain.
4. Klik tombol **"Cetak Daftar Hadir"** untuk mencetak lembar absensi peserta per ruangan.

### F. Import Data Pendaftar Massal (Excel / CSV)
1. Buka menu **"Import Pendaftar"** (atau melalui tombol di halaman Data Pendaftar).
2. Klik **"Unduh Template CSV"** untuk mendapatkan format kolom baku yang valid.
3. Isi data pendaftar offline ke dalam template tersebut.
4. Pilih gelombang tujuan dan unggah berkas CSV/XLSX.
5. Klik **"Mulai Proses Import"**. Sistem akan memvalidasi data dan mendeteksi NISN ganda secara otomatis.

### G. Pencadangan Basis Data (Backup & Restore)
1. Buka menu **"Backup Database"** di bagian Audit & Keamanan.
2. Klik tombol **"Buat Backup Sekarang (1-Klik)"**. Berkas dump `.sql` akan tersimpan secara otomatis.
3. Anda dapat mengunduh berkas backup ke penyimpanan lokal komputer.
4. Untuk memulihkan data lama, klik tombol **"Pulihkan"**, ketik kata konfirmasi `PULIHKAN`, dan tekan tombol eksekusi pemulihan.

### H. Ekspor Data Laporan
Gunakan menu **"Laporan & Ekspor"** pada sidebar untuk mengunduh rekapitulasi data pendaftar dalam 4 pilihan format:
- **Ekspor Excel (.xlsx native)**
- **Ekspor CSV (Aman dari Formula Injection)**
- **Format Baku Dapodikdasmen Kemdikbud**
- **Cetak Lembar Rekapitulasi PDF Resmi**

---

## 3. PANDUAN PANEL KEPALA SEKOLAH

### A. Login Kepala Sekolah
1. Akses alamat: `http://localhost:8000/admin/login`.
2. Masukkan username Kepala Sekolah (contoh: `kepsek_asliman`) dan kata sandi.

### B. Dashboard Statistik Eksekutif
1. Pantau metrik KPI real-time: Total Pendaftar, Jumlah Diterima, Cadangan, dan Tidak Lolos.
2. Analisis grafik peminat program keahlian dan tren harian pendaftar.
3. Tinjau *Market Intelligence* untuk mengetahui 5 SMP/MTs penyumbang pendaftar terbanyak.

### C. Ringkasan Keuangan PPDB
1. Buka menu **"Ringkasan Keuangan"** pada kelompok Eksekutif.
2. Pantau total pemasukan uang formulir yang telah berstatus lunas (*settlement*).
3. Tinjau perbandingan antara penerimaan tunai di loket kasir TU dan gateway online Midtrans.
4. Periksa efektivitas potongan beasiswa/voucher dan rincian penerimaan per gelombang/jurusan.
5. Lakukan penelusuran transaksi pada tabel buku kas penerimaan menggunakan filter pencarian.

### D. Sidang Kelulusan & Pengesahan SKL
1. Buka menu **"Kelulusan & Pengesahan SKL"**.
2. Gunakan filter jurusan untuk memilah calon siswa.
3. Tinjau dossier nilai tes wawancara, tes buta warna, dan skor piagam prestasi calon siswa.
4. Tetapkan keputusan akhir: **Diterima**, **Cadangan**, atau **Tidak Lolos**.
5. Jika diterima, tentukan jurusan penerimaan final dan klik **"Sahkan Kelulusan"**. Sistem akan otomatis menerbitkan Nomor SKL resmi dan Surat Keterangan Lulus digital ber-QR Code untuk siswa tersebut.

### E. Monitoring Jadwal & Ruang
Buka menu **"Monitoring Jadwal & Ruang"** untuk mengawasi keterisian kapasitas ruang ujian dan distribusi peserta seleksi tanpa hak modifikasi data (*read-only*).

### F. Audit Trail Eksekutif (Log Aktivitas)
1. Buka menu **"Audit Trail (Log Aktivitas)"**.
2. Kepala Sekolah dapat meninjau seluruh riwayat transaksi digital yang terjadi di sistem.
3. Manfaatkan filter pencarian nama aktor, jenis peran (`calon_siswa`, `tu`, `kepala_sekolah`), kategori aksi, status keberhasilan, dan rentang tanggal.
4. Klik tombol rincian untuk membuka modal perbandingan snapshot nilai lama vs nilai baru (*data diff*).

---

## 4. PENANGANAN MASALAH UMUM & BANTUAN

| Permasalahan | Penyebab Umum | Solusi Tindakan |
| :--- | :--- | :--- |
| **"Terlalu banyak percobaan login"** | Memasukkan kata sandi salah lebih dari 5 kali berturut-turut. | Tunggu selama 60 detik hingga masa penalti *rate limiting* berakhir, lalu coba kembali dengan kredensial yang benar. |
| **Formulir Terkunci & Tidak Bisa Diedit** | Siswa telah menekan tombol "Finalisasi & Kunci Data". | Hubungi Petugas TU di loket sekolah agar status kunci formulir dibuka (*Unlock*). |
| **Status Pembayaran Belum Berubah Lunas** | Transaksi tunai belum diverifikasi kasir TU, atau pembayaran online belum diselesaikan. | Untuk bayar tunai: tunjukkan bukti transaksi di loket TU. Untuk online: selesaikan pembayaran sebelum batas waktu kedaluwarsa. |
| **Kartu Ujian Belum Muncul Ruangan** | Peserta belum ditempatkan ke dalam ruangan tes oleh panitia TU. | Petugas TU perlu membuka menu Penempatan Peserta dan menjalankan alokasi otomatis/manual. |
| **Penyimpanan Draft Gagal** | Koneksi database terputus atau sesi habis. | Muat ulang (*refresh*) halaman, pastikan server MySQL menyala, dan login kembali. |

