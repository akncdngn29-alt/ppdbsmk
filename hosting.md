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

## 🗄️ Langkah 1: Siapkan Database Cloud Eksternal

Karena SQLite lokal tidak bisa digunakan di Vercel, siapkan database cloud gratis/freemium terlebih dahulu:

### Pilihan Penyedia Database Gratis:
- **MySQL:**
  - [Aiven](https://aiven.io/) (Free tier MySQL)
  - [TiDB Cloud](https://tidbcloud.com/) (Kompatibel MySQL, gratis)
  - [Clever Cloud](https://www.clever-cloud.com/)
- **PostgreSQL:**
  - [Supabase](https://supabase.com/) (Sangat direkomendasikan, gratis & cepat)
  - [Neon.tech](https://neon.tech/) (Serverless Postgres)

> Catat kredensial database Anda: **Host**, **Port**, **Database Name**, **Username**, dan **Password**.

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

Masuk ke **Project Settings** -> **Environment Variables** di dashboard Vercel, lalu tambahkan variabel berikut:

| Key | Value (Contoh) | Keterangan |
| :--- | :--- | :--- |
| `APP_NAME` | `PPDB SMK` | Nama aplikasi |
| `APP_ENV` | `production` | Mode aplikasi |
| `APP_DEBUG` | `false` | Nonaktifkan debug di production |
| `APP_KEY` | `base64:xxxx...` | Hasil dari `key:generate --show` |
| `APP_URL` | `https://nama-projek-anda.vercel.app` | URL domain Vercel Anda |
| `DB_CONNECTION` | `mysql` atau `pgsql` | Sesuai database cloud yang dipilih |
| `DB_HOST` | `gateway.xxxx.aivencloud.com` | Host database cloud |
| `DB_PORT` | `3306` atau `5432` | Port database |
| `DB_DATABASE` | `nama_database` | Nama database |
| `DB_USERNAME` | `user_database` | Username database |
| `DB_PASSWORD` | `password_database` | Password database |
| `SESSION_DRIVER` | `cookie` | Aman untuk serverless |
| `CACHE_STORE` | `array` | Memori sementara |
| `LOG_CHANNEL` | `stderr` | Agar log tampil di log Vercel |

*(Tambahkan juga variabel Midtrans / Mailer jika aplikasi Anda menggunakannya).*

Setelah menambahkan Environment Variables, lakukan **Redeploy** pada deployment terakhir Anda.

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
