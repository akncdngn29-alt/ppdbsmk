# PANDUAN INTEGRASI PIHAK KETIGA (INTEGRATION SETUP)
## SISTEM INFORMASI PPDB SMK ISLAM BINA KHALIFAH BANGSA METRO, LAMPUNG
**Versi:** 1.1.0-ProductionReady  
**Tanggal:** 04 November 2026

Dokumen ini menjelaskan tata cara menghubungkan sistem PPDB dengan layanan eksternal: Payment Gateway (Midtrans), WhatsApp Gateway (Fonnte/Wablas), dan Server Email SMTP.

---

## DAFTAR ISI
1. [Integrasi Payment Gateway Midtrans (Snap & Webhook)](#1-integrasi-payment-gateway-midtrans)
2. [Integrasi WhatsApp Gateway (Fonnte / Wablas)](#2-integrasi-whatsapp-gateway)
3. [Integrasi Email Transaksional (SMTP)](#3-integrasi-email-transaksional-smtp)
4. [Mode Simulasi (Development / Testing Mode)](#4-mode-simulasi-tanpa-biaya)

---

## 1. INTEGRASI PAYMENT GATEWAY MIDTRANS

Sistem PPDB telah dilengkapi dengan `MidtransService` yang mendukung metode pembayaran modern:
- QRIS (GoPay, OVO, Dana, LinkAja, ShopeePay)
- Virtual Account Bank (BCA, BNI, BRI, Mandiri, Permata)
- Convenience Store (Alfamart / Indomaret)

### Langkah Konfigurasi:

#### A. Registrasi Akun Midtrans
1. Daftar akun di portal Midtrans: [https://dashboard.midtrans.com](https://dashboard.midtrans.com).
2. Selesaikan verifikasi identitas lembaga/sekolah untuk mode produksi (*Production*), atau gunakan mode *Sandbox* untuk masa pengujian.

#### B. Mengambil Kredensial API
1. Buka menu **Settings > Access Keys** pada dashboard Midtrans.
2. Salin **Server Key** dan **Client Key**.

#### C. Memasukkan Kredensial ke `.env`
Buka file `.env` aplikasi dan tambahkan:

```env
# Mode Uji Coba (Sandbox)
MIDTRANS_SERVER_KEY="SB-Mid-server-xxxxxxxxxxxxxxxxx"
MIDTRANS_CLIENT_KEY="SB-Mid-client-xxxxxxxxxxxxxxxxx"
MIDTRANS_IS_PRODUCTION=false
MIDTRANS_IS_SANITIZED=true
MIDTRANS_IS_3DS=true

# Mode Produksi (Aktifkan saat PPDB resmi dibuka)
# MIDTRANS_SERVER_KEY="Mid-server-xxxxxxxxxxxxxxxxx"
# MIDTRANS_CLIENT_KEY="Mid-client-xxxxxxxxxxxxxxxxx"
# MIDTRANS_IS_PRODUCTION=true
```

#### D. Pengaturan Notifikasi Webhook (Payment Notification URL)
Agar status pembayaran terverifikasi otomatis secara *real-time* saat calon siswa selesai membayar di ATM/M-Banking/e-Wallet:
1. Buka menu **Settings > Configuration** di Dashboard Midtrans.
2. Pada field **Payment Notification URL**, masukkan URL:
   ```
   https://ppdb.smkib.com/api/midtrans/notification
   ```
3. Klik **Save Changes**.

> [!NOTE]
> Sistem secara otomatis memvalidasi keaslian signature hash SHA-512 `hash('sha512', order_id + status_code + gross_amount + ServerKey)` untuk mencegah pemalsuan pembayaran oleh pihak luar. Endpoint ini telah dikecualikan dari middleware CSRF token.

---

## 2. INTEGRASI WHATSAPP GATEWAY

Sistem menggunakan `NotificationService` untuk mengirim pesan otomatis ke nomor HP calon siswa dan orang tua.

### Provider yang Didukung:
1. **Fonnte** ([https://fonnte.com](https://fonnte.com)) - Rekomendasi
2. **Wablas** ([https://wablas.com](https://wablas.com))

### Langkah Konfigurasi (Contoh Fonnte):
1. Buat akun di Fonnte dan tautkan nomor WhatsApp resmi sekolah via Scan QR.
2. Salin API Token dari dashboard perangkat Fonnte.
3. Masukkan konfigurasi ke dalam `.env`:

```env
WHATSAPP_PROVIDER=fonnte
WHATSAPP_API_TOKEN="xxxxxxxxxxxxxxxxxxxx"
WHATSAPP_API_URL="https://api.fonnte.com/send"
```

### Event Notifikasi Otomatis:
1. **Pendaftaran Berhasil**: Mengirim nomor pendaftaran (`PPDB-2026-XXXX`), nama siswa, jurusan pilihan, dan link instruksi pembayaran.
2. **Pembayaran Lunas**: Mengirim konfirmasi bahwa biaya formulir pendaftaran telah lunas diverifikasi.
3. **Pengesahan Kelulusan**: Mengirim hasil kelulusan seleksi PPDB (Lulus / Cadangan) beserta jadwal dan dokumen daftar ulang.

> [!TIP]
> Format nomor telepon otomatis disanitasi oleh sistem: nomor berawalan `0812...` atau `+62812...` akan diseragamkan menjadi `62812...` sebelum dikirim ke API gateway.

---

## 3. INTEGRASI EMAIL TRANSAKSIONAL (SMTP)

Selain WhatsApp, sistem dapat mengirimkan tanda bukti pendaftaran dalam bentuk email HTML resmi.

### Konfigurasi `.env` (Contoh Google Workspace / Gmail SMTP):
```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=ppdb@smkib.com
MAIL_PASSWORD="password_aplikasi_16_digit"
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS="ppdb@smkib.com"
MAIL_FROM_NAME="PPDB SMK Islam Bina Khalifah Bangsa"
```

---

## 4. MODE SIMULASI (TANPA BIAYA)

Jika sekolah belum berlangganan kuota WhatsApp API atau belum memiliki akun Midtrans aktif, sistem **TETAP DAPAT DIJALANKAN DENGAN SEMPURNA**:
- **Simulasi Midtrans**: Siswa memilih bayar online &rarr; Sistem menyediakan simulasi pelunasan langsung untuk keperluan demo atau uji coba tanpa memotong saldo.
- **Simulasi WhatsApp & Email**: Jika `WHATSAPP_API_TOKEN` dikosongkan, notifikasi tidak akan melempar error; pesan akan dicatat secara rapi ke berkas log `storage/logs/laravel.log` dengan penanda `[SIMULATION MODE]`.

