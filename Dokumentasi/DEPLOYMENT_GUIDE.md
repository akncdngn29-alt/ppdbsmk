# PANDUAN DEPLOYMENT SISTEM (DEPLOYMENT GUIDE)
## SISTEM INFORMASI PPDB SMK ISLAM BINA KHALIFAH BANGSA METRO, LAMPUNG
**Versi:** 1.2.0-ProductionHardened  
**Tanggal Rilis:** 04 November 2026

---

## 1. PRASYARAT SERVER & LINGKUNGAN

### Spesifikasi Minimum Perangkat Keras:
- **Processor**: 2 Core CPU (Rekomendasi: 4 Core untuk masa puncak pendaftaran).
- **RAM**: 2 GB (Rekomendasi: 4 GB).
- **Penyimpanan**: 20 GB SSD (Dibutuhkan ruang cukup untuk dokumen pendaftar KK/Ijazah).

### Perangkat Lunak:
- **Operating System**: Linux (Ubuntu 22.04 LTS / Debian 12) atau Windows Server (2019/2022).
- **PHP**: PHP 8.3.x (Wajib; ekstensi: `pdo_mysql`, `mbstring`, `openssl`, `tokenizer`, `xml`, `ctype`, `json`, `fileinfo`, `zip`, `curl`).
- **Web Server**: Nginx 1.20+ atau Apache 2.4.
- **Database Engine**: MySQL 8.0+ atau MariaDB 10.5+ (Engine: InnoDB).

> [!IMPORTANT]
> **Catatan Khusus Pengguna Windows XAMPP**:
> Di lingkungan Windows yang memiliki XAMPP terpasang (misal PHP 8.0 di `C:\xampp\php`), pastikan perintah CLI selalu mengarah ke binary **PHP 8.3** (misal `C:\php83\php.exe`), atau letakkan path PHP 8.3 di urutan teratas pada System Environment Variables `PATH`.

---

## 2. PENGATURAN PROFIL LINGKUNGAN (.env CONFIGURATION PROFILES)

> [!CAUTION]
> **KEAMANAN BERKAS `.env`**:
> Berkas `.env` memuat kunci rahasia aplikasi, kredensial database, dan API key payment gateway.
> - **DILARANG KERAS** mempublikasikan file `.env` ke publik atau menyertakannya ke dalam repository Git.
> - Pastikan `.gitignore` selalu menyertakan `.env`.
> - Pada server produksi, atur permission file `.env` menjadi `chmod 600 .env` (hanya dapat dibaca oleh user pemilik).

### Matriks Konfigurasi Berdasarkan Lingkungan:

| Parameter Konfigurasi | Local Development | Automated Testing | Midtrans Sandbox | Production (Resmi) |
|:---|:---|:---|:---|:---|
| `APP_ENV` | `local` | `testing` | `staging` / `local` | `production` |
| `APP_DEBUG` | `true` | `false` | `false` | `false` (Wajib!) |
| `APP_URL` | `http://localhost:8000` | `http://localhost` | `https://staging-ppdb.smkib.com` | `https://ppdb.smkib.com` |
| `DB_DATABASE` | `ppdb_smk` | `ppdb_smk_testing` | `ppdb_smk_sandbox` | `ppdb_smk` |
| `FILESYSTEM_DISK` | `local` | `local` | `local` | `local` |
| `MIDTRANS_SERVER_KEY` | *(Kosong / Opsional)* | *(Mocking / Test Key)* | `SB-Mid-server-xxxx` | `Mid-server-xxxx` |
| `MIDTRANS_CLIENT_KEY` | *(Kosong / Opsional)* | *(Mocking / Test Key)* | `SB-Mid-client-xxxx` | `Mid-client-xxxx` |
| `MIDTRANS_IS_PRODUCTION` | `false` | `false` | `false` | `true` |
| `WHATSAPP_PROVIDER` | `log` / `simulated` | `array` | `fonnte` | `fonnte` |

### Penjelasan Variabel Kritis Produksi:
1. **`APP_DEBUG=false`**: Wajib disetel `false` di server produksi. Mode debug aktif (`true`) mengekspos variabel lingkungan, kata sandi basis data, dan stack trace sistem saat terjadi exception kepada pengunjung umum.
2. **`FILESYSTEM_DISK=local`**: Memastikan private storage disk (`storage/app/private`) aktif sebagai storage target berkas sensitif pendaftar UU PDP No. 27/2022.

---

## 3. LANGKAH-LANGKAH DEPLOYMENT & INSTALASI

### Langkah 1: Kloning / Ekstraksi Source Code
Pastikan kode proyek berada di direktori web root (contoh di Linux: `/var/www/ppdb_smk` atau Windows: `D:\BASIS_DATA_LANJUT\PPDB_SMK`).

### Langkah 2: Konfigurasi File Lingkungan Produksi
Salin template konfigurasi dan sesuaikan kredensial server produksi:
```bash
cp .env.example .env
php artisan key:generate
```
Pastikan `APP_DEBUG=false` dan isi kredensial MySQL produksi.

### Langkah 3: Setup Izin Direktori & Prinsip Least Privilege

> [!WARNING]
> **BAHAYA IZIN "FULL CONTROL" KEPADA "EVERYONE"**:
> Jangan pernah memberikan izin `Full Control` kepada grup `Everyone` pada sistem Windows.
> Izin tersebut memungkinkan semua pengguna/proses lokal membaca, memodifikasi, dan menghapus berkas rahasia aplikasi.

Gunakan prinsip hak akses terendah (*Principle of Least Privilege*):
- **Linux (Nginx / Apache)**:
  ```bash
  sudo chown -R www-data:www-data /var/www/ppdb_smk
  sudo find /var/www/ppdb_smk -type f -exec chmod 644 {} \;
  sudo find /var/www/ppdb_smk -type d -exec chmod 755 {} \;
  sudo chmod -R 775 /var/www/ppdb_smk/storage /var/www/ppdb_smk/bootstrap/cache
  ```
- **Windows Server (IIS / Apache Service)**:
  Gunakan utilitas `icacls` untuk memberikan izin `Modify` (bukan Full Control) hanya kepada *Service Account* web server yang relevan:
  ```cmd
  :: Untuk IIS Application Pool:
  icacls "D:\BASIS_DATA_LANJUT\PPDB_SMK\storage" /grant "IIS_IUSRS":(OI)(CI)M /T
  icacls "D:\BASIS_DATA_LANJUT\PPDB_SMK\bootstrap\cache" /grant "IIS_IUSRS":(OI)(CI)M /T

  :: Untuk Apache Service (XAMPP / Standalone):
  icacls "D:\BASIS_DATA_LANJUT\PPDB_SMK\storage" /grant "SYSTEM":(OI)(CI)M /T
  icacls "D:\BASIS_DATA_LANJUT\PPDB_SMK\storage" /grant "Administrators":(OI)(CI)M /T
  ```

### Langkah 4: Pemisahan Storage & Symlink Publik Aman
Sistem memisahkan penyimpanan berkas secara fisik:
1. **Public Storage** (`storage/app/public`): Hanya untuk logo sekolah, brosur PPDB, dan aset CMS umum.
2. **Private Storage** (`storage/app/private`): Untuk dokumen sensitif pendaftar (KK, Akta, Ijazah, KTP Orang Tua, Pasfoto).

Sebelum mengaktifkan symlink publik:
1. Pastikan folder publik tidak memuat folder sensitif `berkas_pendaftar` atau `prestasi_pendaftar`.
2. Jika server melakukan upgrade dari versi lama, jalankan migrasi dokumen:
   ```bash
   php artisan ppdb:migrate-documents
   ```
3. Buat symlink publik:
   ```bash
   php artisan storage:link
   ```

### Langkah 5: Menjalankan Migrasi Database Aman
Jalankan migrasi aditif. **JANGAN PERNAH** menggunakan opsi `--fresh` atau `--refresh` di server operasional:
```bash
php artisan migrate --force
```

### Langkah 6: Optimasi Cache Produksi
Kompilasi konfigurasi, route, dan view untuk kinerja tinggi:
```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

---

## 4. KONFIGURASI WEB SERVER

### Contoh Konfigurasi Nginx (`/etc/nginx/sites-available/ppdb.smkib.com`)
```nginx
server {
    listen 80;
    server_name ppdb.smkib.com;
    return 301 https://$host$request_uri;
}

server {
    listen 443 ssl http2;
    server_name ppdb.smkib.com;
    root /var/www/ppdb_smk/public;

    ssl_certificate /etc/letsencrypt/live/ppdb.smkib.com/fullchain.pem;
    ssl_certificate_key /etc/letsencrypt/live/ppdb.smkib.com/privkey.pem;

    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";
    add_header X-XSS-Protection "1; mode=block";

    index index.php;
    charset utf-8;

    # Batas upload file pendaftar (Max 10 MB per payload)
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

    # Blokir akses langsung ke file konfigurasi tersembunyi
    location ~ /\.(?!well-known).* {
        deny all;
    }
}
```

---

## 5. PROSEDUR BACKUP & RESTORE BASIS DATA

### Prosedur Backup Rutin (MySQL Dump)
Jalankan backup harian menggunakan opsi keselamatan InnoDB `--single-transaction --quick` untuk mencegah tabel terkunci selama backup:
```bash
# Perintah Bash CLI standar (Linux / Git Bash):
mysqldump -u ppdb_user -p --single-transaction --quick ppdb_smk > /var/backups/ppdb_smk_$(date +%F).sql

# Windows PowerShell:
$tgl = Get-Date -Format "yyyy-MM-dd"
& "C:\xampp\mysql\bin\mysqldump.exe" -u ppdb_user -p --single-transaction --quick ppdb_smk > "D:\backups\ppdb_smk_$tgl.sql"
```

> [!NOTE]
> Opsi `--single-transaction` membuat *consistent snapshot* tanpa memblokir pembacaan dan penulisan transaksi pendaftar yang sedang berlangsung pada tabel InnoDB.

### Prosedur Pemulihan (Restore)
Jika terjadi insiden kegagalan sistem:
```bash
mysql -u ppdb_user -p ppdb_smk < /var/backups/ppdb_smk_backup_20261104.sql
```

---

## 6. PENGECEKAN KESEHATAN SISTEM (HEALTH CHECK)

Setelah proses deployment selesai, jalankan prosedur verifikasi:
1. `php artisan about` &rarr; Memastikan cache status berstatus *Cached* dan versi PHP terdeteksi 8.3+.
2. Buka `https://ppdb.smkib.com/up` &rarr; Merespons status HTTP 200 OK (Laravel Health Endpoint).
3. Pastikan `APP_DEBUG=false` aktif dengan mencoba mengakses URL sembarang (harus menampilkan halaman 404 tanpa stack trace).
4. Akses rute `/siswa/pembayaran/midtrans-simulasi` pada mode produksi &rarr; Harus ditolak dengan HTTP 403 Forbidden.
5. Uji upload 1 berkas pendaftar &rarr; Memastikan file tersimpan di `storage/app/private/berkas_pendaftar` dan tidak bisa diakses tanpa sesi autentikasi.
