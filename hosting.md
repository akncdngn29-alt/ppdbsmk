# Panduan Lengkap Hosting Laravel di Vercel

Dokumen ini memuat langkah-langkah komprehensif untuk mendeploy aplikasi Laravel ini ke **Vercel**.

---

## 📌 Penting: Memahami Karakteristik Vercel untuk Laravel

Vercel dirancang sebagai platform **Serverless** (khususnya untuk frontend dan Node.js). Menjalankan Laravel di Vercel dimungkinkan menggunakan community runtime **`vercel-php`**, namun ada beberapa hal penting yang perlu diperhatikan:

1. **Filesystem bersifat Read-Only**: Serverless instance di Vercel tidak mengizinkan penulisan file, kecuali pada folder sementara `/tmp`. Cache framework, view compile Blade, dan session file harus diarahkan ke `/tmp` atau memori.
2. **Stateless / Ephemeral**: Instance Vercel mati dan hidup secara dinamis. Anda **tidak bisa menggunakan SQLite lokal** untuk produksi karena database akan ter-reset setiap kali instance baru dibuat. Anda wajib menggunakan **Database Cloud Eksternal** (MySQL / PostgreSQL).
3. **Upload File**: File yang diupload ke `storage/app/public` tidak akan permanen. Gunakan layanan cloud storage seperti **AWS S3**, **Supabase Storage**, atau **Cloudinary** jika ada fitur upload file.
4. **Queue Worker & Cron Scheduler**: Vercel tidak mendukung background worker yang berjalan terus menerus (`queue:work`).

---

## 🛠️ File Konfigurasi yang Telah Dibuat

Proyek ini telah dilengkapi dengan 3 file konfigurasi utama untuk Vercel:

### 1. `api/index.php`
Berfungsi sebagai jembatan (*serverless entry point*) yang menerima request dari Vercel, memastikan direktori `/tmp` tersedia, lalu meneruskannya ke `public/index.php` Laravel.

### 2. `vercel.json`
Mengatur:
- Runtime PHP 8.3 via `vercel-php@0.7.4`.
- Routing aset statis hasil kompilasi Vite (`/build/(.*)` -> `/public/build/$1`).
- Routing seluruh request ke `api/index.php`.
- Konfigurasi environment cache & view agar mengarah ke `/tmp`.

### 3. `.vercelignore`
Mengecualikan file dan folder yang tidak perlu diupload ke Vercel (seperti `node_modules`, `vendor`, `.env`, folder dokumentasi/pengujian).

---

## 🗄️ Langkah 1: Siapkan & Ekspor-Impor Database Cloud Eksternal

Karena Vercel bersifat **Serverless** (tidak memiliki server database lokal yang menyala terus-menerus), Anda membutuhkan **Database Cloud Eksternal** yang dapat diakses secara online selama 24 jam.

---

### A. Panduan Lengkap Ekspor Database dari phpMyAdmin (Bahasa Indonesia)

Jika Anda saat ini mengembangkan aplikasi di lokal menggunakan XAMPP/Laragon dan databasenya ada di phpMyAdmin, ikuti langkah berikut:

1. **Buka phpMyAdmin**:
   - Buka browser Anda dan akses `http://localhost/phpmyadmin`.
2. **Pilih Database Proyek**:
   - Pada panel sebelah kiri, cari dan **klik nama database Anda** (contoh: `ppdb_smk`).
   - > ⚠️ **Penting**: Pastikan Anda sudah mengklik nama database, bukan sedang berada di halaman beranda / server global phpMyAdmin.
3. **Buka Menu Ekspor**:
   - Pada bilah menu di bagian atas, klik tab **"Ekspor"**.
4. **Pilih Metode Ekspor**:
   - **Pilihan 1: Metode Cepat (Paling Sederhana)**:
     - Pilih opsi radio button: **"Cepat - hanya tampilkan opsi minimal"**.
     - Pastikan **Format** adalah **"SQL"**.
     - Klik tombol **"Ekspor"** (atau **"Kirim"**).
   - **Pilihan 2: Metode Kustom (Sangat Direkomendasikan)**:
     - Pilih **"Kustom - tampilkan semua opsi yang mungkin"**.
     - **Tabel**: Pastikan semua tabel terpilih (**"Pilih Semua"**).
     - **Keluaran**: Pilih **"Simpan keluaran ke berkas"** (karakter: `utf-8`).
     - **Opsi pembuatan objek**: Centang opsi **`Tambahkan pernyataan DROP TABLE / TRIGGER`** (tujuannya agar jika Anda mengimpor ulang, tabel lama otomatis dihapus bersih tanpa error *"table already exists"*).
     - Gulir ke paling bawah dan klik tombol **"Ekspor"** (atau **"Kirim"**).
5. File database berekstensi `.sql` (contoh: `ppdb_smk.sql`) akan otomatis terunduh ke folder **Downloads** komputer Anda.

---

### B. Membuat Database Cloud Gratis

Daftar layanan database cloud MySQL gratis yang sangat kompatibel dengan Laravel di Vercel:

1. **[TiDB Cloud](https://tidbcloud.com/)** *(Paling Direkomendasikan & Kuota Besar)*:
   - Gratis (Free Serverless Tier hingga 25 GB).
   - 100% kompatibel dengan MySQL.
   - Dilengkapi **Web SQL Editor** bawaan untuk eksekusi file `.sql` langsung di browser tanpa install aplikasi tambahan.
2. **[Aiven for MySQL](https://aiven.io/)**:
   - Free tier MySQL cloud yang stabil dan handal.
3. **[Clever Cloud](https://www.clever-cloud.com/)**:
   - Menyediakan addon MySQL gratis (maksimal 20MB, cocok untuk uji coba/skripsi).

> 📝 **Catat Kredensial Database Cloud Anda**:
> Setelah membuat cluster/database baru di penyedia di atas, catat informasi berikut:
> - **Host**: (Contoh: `gateway01.ap-southeast-1.prod.aws.tidbcloud.com` atau `mysql-xxx.aivencloud.com`)
> - **Port**: (Biasanya `4000` di TiDB, atau `3306` / `25060` di Aiven)
> - **Database**: (Nama database yang Anda buat, misal `ppdb_smk` atau `defaultdb`)
> - **User**: (Username, misal `root` atau `avnadmin`)
> - **Password**: (Password yang diberikan atau Anda atur)

---

### C. Memasukkan (Impor) File SQL ke Database Cloud

Ada 2 cara praktis untuk mengimpor file `ppdb_smk.sql` ke database cloud Anda:

#### Cara 1: Menggunakan Web SQL Editor (Jika Menggunakan TiDB Cloud)
1. Buka dashboard TiDB Cloud cluster Anda.
2. Klik tab **"Chat2Query"** atau **"SQL Editor"**.
3. Buka file `ppdb_smk.sql` yang ada di folder Downloads menggunakan Notepad atau VS Code.
4. Salin seluruh isinya (`Ctrl + A` lalu `Ctrl + C`).
5. Tempel (`Ctrl + V`) ke dalam editor TiDB Cloud, lalu klik tombol **"Run"**. Seluruh struktur tabel dan data langsung terbuat di cloud.

#### Cara 2: Menggunakan Software GUI (HeidiSQL / DBeaver)
1. Buka **HeidiSQL** (bawaan Laragon) atau **DBeaver**.
2. Buat koneksi baru (**Session New**):
   - Masukkan **Host/IP**, **User**, **Password**, dan **Port** dari database cloud Anda.
   - Klik **Open / Connect**.
3. Klik kanan pada nama database Anda di cloud $\rightarrow$ pilih **Load SQL file...** (Muat file SQL).
4. Pilih file `ppdb_smk.sql` dari folder Downloads Anda.
5. Klik tombol **Execute / Jalankan SQL** (`F9`).

---

---

## 🔑 Langkah 2: Dapatkan APP_KEY Produksi

Jalankan perintah ini di terminal lokal Anda untuk mendapatkan kunci enkripsi baru:

```bash
php artisan key:generate --show
```

Simpan string yang dihasilkan (contoh: `base64:xxxx...`), ini akan dimasukkan ke Environment Variables Vercel.

---

## 🚀 Langkah 3: Deploy ke Vercel

### Metode A: Melalui GitHub (Sangat Direkomendasikan)

1. **Commit dan Push proyek Anda ke GitHub**:
   ```bash
   git add .
   git commit -m "feat: tambahkan konfigurasi vercel"
   git push origin main
   ```
2. Buka [Vercel Dashboard](https://vercel.com/dashboard) dan login.
3. Klik **"Add New..."** -> **"Project"**.
4. Pilih repository GitHub proyek Anda, lalu klik **"Import"**.
5. Pada bagian **Build and Output Settings**:
   - Biarkan **Framework Preset** default / `Other`.
   - **Root Directory**: `./` (default).
6. Buka bagian **Environment Variables** (lihat panduan variabel di Langkah 4 di bawah).
7. Klik **"Deploy"**.

---

### Metode B: Melalui Vercel CLI

1. Install Vercel CLI jika belum terpasang:
   ```bash
   npm install -g vercel
   ```
2. Login ke akun Vercel:
   ```bash
   vercel login
   ```
3. Deploy dari root direktori proyek:
   ```bash
   vercel
   ```
   *(Ikuti instruksi di terminal. Pilih `y` untuk link ke project baru).*
4. Untuk deploy ke production:
   ```bash
   vercel --prod
   ```

---

## ⚙️ Langkah 4: Konfigurasi Environment Variables di Vercel

Masuk ke **Project Settings** -> **Environment Variables** di dashboard Vercel. Anda dapat menambahkan variabel satu per satu atau memilih opsi **"Paste .env file contents"** untuk langsung menempelkan seluruh variabel.

Berikut adalah daftar lengkap Environment Variables yang diperlukan:

### 1. Konfigurasi Aplikasi Inti (Wajib)
| Key | Value (Contoh) | Keterangan |
| :--- | :--- | :--- |
| `APP_NAME` | `"PPDB SMK Bina Khalifah"` | Nama aplikasi Anda |
| `APP_ENV` | `production` | Mode produksi |
| `APP_DEBUG` | `false` | Nonaktifkan debug untuk keamanan di produksi |
| `APP_KEY` | `base64:xxxx...` | Hasil perintah `php artisan key:generate --show` |
| `APP_URL` | `https://nama-projek-anda.vercel.app` | URL domain Vercel yang didapatkan setelah deploy |

### 2. Koneksi Database Cloud (Wajib)
*Pilih salah satu sesuai jenis database cloud Anda (MySQL / TiDB / Aiven / Supabase Postgres):*

| Key | Value (Contoh) | Keterangan |
| :--- | :--- | :--- |
| `DB_CONNECTION` | `mysql` *(atau `pgsql`)* | Driver database |
| `DB_HOST` | `gateway.xxxx.aivencloud.com` | Host / Endpoint database cloud |
| `DB_PORT` | `3306` *(atau `5432` untuk Postgres)* | Port database |
| `DB_DATABASE` | `defaultdb` | Nama database |
| `DB_USERNAME` | `avnadmin` | Username database |
| `DB_PASSWORD` | `password_rahasia_anda` | Password database |
| `DB_SSLMODE` | `require` *(opsional, untuk Postgres)* | Diperlukan oleh Supabase / Neon jika SSL wajib |

### 3. Payment Gateway Midtrans (Wajib jika menggunakan pembayaran online)
Dapatkan kunci dari [Midtrans Dashboard](https://dashboard.midtrans.com/) (Menu **Settings** -> **Access Keys**):

| Key | Value (Contoh) | Keterangan |
| :--- | :--- | :--- |
| `MIDTRANS_SERVER_KEY` | `SB-Mid-server-xxxx` | Server Key dari Midtrans (Sandbox diawali `SB-`, Production diawali `Mid-`) |
| `MIDTRANS_CLIENT_KEY` | `SB-Mid-client-xxxx` | Client Key dari Midtrans |
| `MIDTRANS_IS_PRODUCTION` | `false` | `false` untuk mode Sandbox / Testing, `true` jika sudah akun Production aktif |

> 💡 **Pengaturan Webhook Notification URL di Midtrans Dashboard**:
> Buka Midtrans Dashboard -> **Settings** -> **Configuration** -> masukkan URL:
> `https://nama-projek-anda.vercel.app/api/midtrans/notification`

### 4. Optimalisasi Serverless Vercel (Wajib)
Mengarahkan session, cache, dan log agar aman dan kompatibel dengan lingkungan stateless serverless:

| Key | Value | Keterangan |
| :--- | :--- | :--- |
| `SESSION_DRIVER` | `cookie` | Simpan sesi pengguna di cookie browser terenkripsi (bebas dari read-only disk) |
| `CACHE_STORE` | `array` | Cache memori sementara per request |
| `QUEUE_CONNECTION` | `sync` | Eksekusi antrean langsung (tanpa worker background) |
| `LOG_CHANNEL` | `stderr` | Kirim error/log ke Vercel Function Logs |

### 5. Penyimpanan Berkas & Dokumen Upload (Opsional / Rekomendasi)
Karena serverless Vercel bersifat ephemeral (file di disk lokal akan hilang saat instance refresh), untuk penyimpanan dokumen berkas siswa secara permanen dianjurkan menggunakan Cloud Storage (AWS S3 atau Supabase Storage S3-compatible):

| Key | Value (Contoh) | Keterangan |
| :--- | :--- | :--- |
| `FILESYSTEM_DISK` | `local` *(atau `s3`)* | Gunakan `s3` jika mengaktifkan S3 bucket |
| `AWS_ACCESS_KEY_ID` | `xxxx` | Kunci akses cloud storage |
| `AWS_SECRET_ACCESS_KEY` | `xxxx` | Secret cloud storage |
| `AWS_DEFAULT_REGION` | `ap-southeast-1` | Wilayah cloud storage |
| `AWS_BUCKET` | `ppdb-smk-berkas` | Nama bucket dokumen |
| `AWS_ENDPOINT` | `https://xxxx.supabase.co/storage/v1/s3` | Diisi jika menggunakan Supabase S3 / MinIO |

### 6. Email Notifikasi / SMTP (Opsional)
Jika Anda ingin mengirimkan email bukti pendaftaran atau reset password via SMTP:

| Key | Value (Contoh) | Keterangan |
| :--- | :--- | :--- |
| `MAIL_MAILER` | `smtp` *(atau `log` untuk mematikan)* | Driver mailer |
| `MAIL_HOST` | `smtp.gmail.com` | Host SMTP |
| `MAIL_PORT` | `587` | Port SMTP (587 TLS / 465 SSL) |
| `MAIL_USERNAME` | `admin@sekolah.sch.id` | Username / Email |
| `MAIL_PASSWORD` | `xxxx-xxxx-xxxx-xxxx` | Password aplikasi (App Password) |
| `MAIL_ENCRYPTION` | `tls` | Enkripsi email |
| `MAIL_FROM_ADDRESS` | `admin@sekolah.sch.id` | Alamat pengirim |
| `MAIL_FROM_NAME` | `"PPDB SMK Bina Khalifah"` | Nama pengirim |

---

### 📋 Template Siap Copy-Paste untuk Vercel:

Salin teks di bawah ini dan paste langsung di Vercel Dashboard (**Project Settings** -> **Environment Variables** -> klik **"Paste .env file contents"**):

```env
APP_NAME="PPDB SMK Bina Khalifah"
APP_ENV=production
APP_DEBUG=false
APP_KEY=base64:MASUKKAN_APP_KEY_ANDA_DISINI
APP_URL=https://nama-projek-anda.vercel.app

DB_CONNECTION=mysql
DB_HOST=gateway.xxxx.aivencloud.com
DB_PORT=3306
DB_DATABASE=nama_database_anda
DB_USERNAME=username_database_anda
DB_PASSWORD=password_database_anda

MIDTRANS_SERVER_KEY=SB-Mid-server-MASUKKAN_SERVER_KEY
MIDTRANS_CLIENT_KEY=SB-Mid-client-MASUKKAN_CLIENT_KEY
MIDTRANS_IS_PRODUCTION=false

SESSION_DRIVER=cookie
CACHE_STORE=array
QUEUE_CONNECTION=sync
LOG_CHANNEL=stderr

FILESYSTEM_DISK=local
MAIL_MAILER=log
```

> **Catatan Setelah Mengubah Variabel**:
> Setiap kali Anda menambahkan atau mengubah Environment Variables di Vercel, pastikan untuk melakukan **Redeploy** (pada menu Deployments -> klik icon titik tiga -> Redeploy) agar variabel baru terbaca.

---

## 🗃️ Langkah 5: Menjalankan Migrasi Database (`php artisan migrate`)

Karena Vercel serverless tidak menyediakan terminal SSH interaktif:

### Cara Terbaik: Jalankan dari Komputer Lokal
1. Buka file `.env` lokal Anda (atau buat file sementara `.env.production`).
2. Ubah `DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME`, dan `DB_PASSWORD` di lokal agar mengarah ke **Database Cloud** Anda.
3. Jalankan migrasi dan seeder dari terminal lokal:
   ```bash
   php artisan migrate --force
   # Jika ada seeder:
   php artisan db:seed --force
   ```
4. Kembalikan `.env` lokal Anda ke pengaturan lokal semula.

---

## ⚠️ Troubleshooting & Solusi

### 1. Error `500 Server Error` atau Halaman Blank
- Buka dashboard Vercel -> menu **Deployments** -> pilih deployment aktif -> tab **Logs**.
- Periksa pesan error PHP yang muncul.
- Pastikan `APP_KEY` sudah diisi di Environment Variables Vercel.

### 2. Tampilan CSS / JS Tidak Muncul (Vite Asset 404)
- Pastikan build Vite dijalankan. Jika perlu, Anda dapat menjalankan build di lokal terlebih dahulu:
  ```bash
  npm run build
  ```
  Lalu pastikan folder `public/build` ter-commit ke Git.

### 3. Error `Read-only file system`
- Pastikan file `api/index.php` dan `vercel.json` menggunakan konfigurasi cache path ke `/tmp` seperti yang sudah disiapkan.

---

## 💡 Alternatif yang Lebih Ramah untuk Laravel

Jika Anda menginginkan hosting yang mendukung seluruh fitur native Laravel (seperti database lokal, background job/queue, scheduler, dan upload file tanpa konfigurasi rumit), pertimbangkan platform PaaS berikut:
- **[Railway](https://railway.app)**: Sangat mudah, mendukung PHP + MySQL/PostgreSQL + Redis bawaan.
- **[Render](https://render.com)**: Mendukung Web Service PHP dengan persistent disk.
- **[Laravel Cloud](https://cloud.laravel.com)**: Platform resmi dari tim Laravel.
- **VPS (DigitalOcean / IDCloudHost / Linode)**: Kontrol penuh dengan biaya terjangkau.
