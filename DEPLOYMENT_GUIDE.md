# PANDUAN DEPLOYMENT SISTEM (DEPLOYMENT GUIDE)
## SISTEM INFORMASI PPDB SMK ISLAM BINA KHALIFAH BANGSA METRO, LAMPUNG
**Versi:** 1.1.0-ProductionReady  
**Tanggal Rilis:** 04 November 2026

---

## 1. PRASYARAT SERVER & LINGKUNGAN

### Spesifikasi Minimum Perangkat Keras:
- **Processor**: 2 Core CPU (Rekomendasi: 4 Core untuk masa puncak pendaftaran).
- **RAM**: 2 GB (Rekomendasi: 4 GB).
- **Penyimpanan**: 20 GB SSD (Dibutuhkan ruang cukup untuk dokumen pendaftar KK/Ijazah).

### Perangkat Lunak:
- **Operating System**: Linux (Ubuntu 22.04 LTS / Debian 12) atau Windows Server.
- **PHP**: PHP 8.3.x (Wajib; ekstensi: `pdo_mysql`, `mbstring`, `openssl`, `tokenizer`, `xml`, `ctype`, `json`, `fileinfo`, `zip`, `curl`).
- **Web Server**: Nginx atau Apache 2.4.
- **Database Engine**: MySQL 8.0+ atau MariaDB 10.5+.

> [!IMPORTANT]
> **Catatan Khusus Pengguna Windows XAMPP**:
> Di lingkungan Windows yang memiliki XAMPP terpasang (misal PHP 8.0 di `C:\xampp\php`), pastikan perintah CLI selalu mengarah ke binary **PHP 8.3** (misal `C:\php83\php.exe`), atau letakkan path PHP 8.3 di urutan teratas pada System Environment Variables `PATH`.

---

## 2. LANGKAH-LANGKAH DEPLOYMENT & INSTALASI

### Langkah 1: Kloning / Ekstraksi Source Code
Pastikan kode proyek berada di direktori web root (contoh di Linux: `/var/www/ppdb_smk` atau Windows: `D:\BASIS_DATA_LANJUT\PPDB_SMK`).

### Langkah 2: Konfigurasi File Lingkungan (`.env`)
Salin file template `.env.example` menjadi `.env` (jika belum ada) dan atur konfigurasi berikut:

```env
APP_NAME="PPDB SMK Islam Bina Khalifah Bangsa"
APP_ENV=production
APP_DEBUG=false
APP_URL=https://ppdb.smkib.com

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=ppdb_smk
DB_USERNAME=ppdb_user
DB_PASSWORD=rahasia_password_db

# File Storage
FILESYSTEM_DISK=local
```

### Langkah 3: Setup Izin Direktori & Storage
Sistem menggunakan dua area penyimpanan:
1. **Public Storage** (`storage/app/public`): Untuk logo sekolah, banner brosur, dan konten CMS publik.
2. **Private Storage** (`storage/app/private`): Untuk dokumen sensitif pendaftar (KK, Akta, Ijazah, KTP Orang Tua).

Jalankan perintah pembuatan direktori dan symlink:
```bash
php artisan storage:link
```

Pastikan folder `storage` dan `bootstrap/cache` memiliki hak akses tulis (*write permissions*):
- **Linux**:
  ```bash
  sudo chown -R www-data:www-data /var/www/ppdb_smk/storage /var/www/ppdb_smk/bootstrap/cache
  sudo chmod -R 775 /var/www/ppdb_smk/storage /var/www/ppdb_smk/bootstrap/cache
  ```
- **Windows (IIS / Apache)**:
  Berikan hak `Full Control` atau `Modify` pada folder `storage` dan `bootstrap/cache` untuk user `IUSR` atau `Everyone`.

### Langkah 4: Menjalankan Migrasi Database Aman
Jalankan migrasi hanya untuk menambahkan tabel/kolom yang belum ada. **JANGAN PERNAH** menggunakan flag `--fresh` di server produksi:

```bash
php artisan migrate --force
```

### Langkah 5: Migrasi Dokumen Lama ke Private Storage (Opsional)
Jika sistem sebelumnya memiliki berkas pendaftar yang tersimpan di direktori publik:
```bash
php artisan ppdb:migrate-documents
```

### Langkah 6: Optimasi Cache Produksi
Jalankan perintah optimasi kompilasi konfigurasi, route, dan view:
```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

---

## 3. KONFIGURASI WEB SERVER

### Contoh Konfigurasi Nginx (`/etc/nginx/sites-available/ppdb.smkib.com`)
```nginx
server {
    listen 80;
    server_name ppdb.smkib.com;
    root /var/www/ppdb_smk/public;

    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";

    index index.php;
    charset utf-8;

    # Batas ukuran upload berkas (KK/Ijazah/Foto)
    client_max_body_size 10M;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    error_page 404 /index.php;

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.3-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    # Blokir akses langsung ke file tersembunyi
    location ~ /\.(?!well-known).* {
        deny all;
    }
}
```

---

## 4. PROSEDUR BACKUP & RESTORE BASIS DATA

### Backup Rutin (MySQL Dump)
Jalankan perintah ini setiap hari atau atur via cron job:
```bash
mysqldump -u ppdb_user -p ppdb_smk > /var/backups/ppdb_smk_$(date +\%F).sql
```

### Prosedur Pemulihan (Restore)
Jika terjadi insiden sistem:
```bash
mysql -u ppdb_user -p ppdb_smk < /var/backups/ppdb_smk_backup_20261104.sql
```

---

## 5. PENGECEKAN KESEHATAN SISTEM (HEALTH CHECK)

Setelah deployment selesai, jalankan verifikasi berikut:
1. `php artisan about` &rarr; Memastikan cache status berstatus *Cached* dan versi PHP terdeteksi 8.3+.
2. Buka `https://ppdb.smkib.com/up` &rarr; Harus merespons status HTTP 200 OK (Laravel Health Endpoint).
3. Buka halaman login admin dan pastikan dashboard Admin TU serta Kepala Sekolah dapat dibuka.
4. Lakukan pendaftaran 1 akun siswa uji coba, input formulir, dan upload 1 berkas dummy untuk memastikan berkas masuk ke `storage/app/private/berkas_pendaftar` dan tidak bisa diakses tanpa login.
