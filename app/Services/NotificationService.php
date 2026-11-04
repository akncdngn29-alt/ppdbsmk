<?php

namespace App\Services;

use App\Models\User;
use App\Models\Pendaftar;
use App\Models\Pembayaran;
use App\Models\PengaturanSekolah;
use App\Models\LogAktivitas;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class NotificationService
{
    /**
     * Ambil WhatsApp API token dari .env atau pengaturan database.
     */
    public function getWhatsAppToken(): ?string
    {
        $token = env('WHATSAPP_API_TOKEN');
        if (empty($token)) {
            $pengaturan = PengaturanSekolah::getPengaturan();
            $token = $pengaturan->whatsapp_api_token;
        }
        return $token;
    }

    /**
     * Format nomor ponsel Indonesia ke format internasional (628...).
     */
    public function formatPhoneNumber(string $phone): string
    {
        $cleaned = preg_replace('/[^0-9]/', '', $phone);
        if (str_starts_with($cleaned, '0')) {
            $cleaned = '62' . substr($cleaned, 1);
        } elseif (str_starts_with($cleaned, '8')) {
            $cleaned = '62' . $cleaned;
        }
        return $cleaned;
    }

    /**
     * Kirim pesan WhatsApp (Fonnte / Wablas API dengan fallback Simulator).
     */
    public function sendWhatsApp(string $phone, string $message): array
    {
        $token = $this->getWhatsAppToken();
        $formattedPhone = $this->formatPhoneNumber($phone);

        // Jika token belum terkonfigurasi, alihkan ke Mode Simulasi Terpuji (Logger Mode)
        if (empty($token) || $token === 'DEMO_SIMULATOR_TOKEN') {
            Log::info("WhatsApp Notification (Simulator Mode) to [{$formattedPhone}]: {$message}");
            return [
                'success' => true,
                'mode' => 'simulated',
                'phone' => $formattedPhone,
                'message' => 'Terkirim via Simulator (Kredensial WhatsApp API belum disetel di .env)',
            ];
        }

        try {
            // Default provider: Fonnte API
            $response = Http::withHeaders([
                'Authorization' => $token,
            ])->timeout(10)->post('https://api.fonnte.com/send', [
                'target' => $formattedPhone,
                'message' => $message,
                'countryCode' => '62',
            ]);

            if ($response->successful()) {
                Log::info("WhatsApp Notification SENT to [{$formattedPhone}]");
                return [
                    'success' => true,
                    'mode' => 'live',
                    'response' => $response->json(),
                ];
            }

            Log::error("WhatsApp Provider Error [{$formattedPhone}]: " . $response->body());
            return [
                'success' => false,
                'mode' => 'live',
                'error' => 'Provider error',
            ];
        } catch (\Exception $e) {
            Log::error("WhatsApp Notification Exception [{$formattedPhone}]: " . $e->getMessage());
            return [
                'success' => false,
                'mode' => 'live',
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Kirim notifikasi Email (SMTP Mailer).
     */
    public function sendEmail(string $recipientEmail, string $subject, string $bodyText): array
    {
        try {
            Mail::raw($bodyText, function ($message) use ($recipientEmail, $subject) {
                $pengaturan = PengaturanSekolah::getPengaturan();
                $message->to($recipientEmail)
                    ->subject($subject)
                    ->from(config('mail.from.address', 'ppdb@smkib.com'), $pengaturan->nama_sekolah);
            });

            Log::info("Email Notification SENT to [{$recipientEmail}] - {$subject}");
            return ['success' => true];
        } catch (\Exception $e) {
            Log::error("Email Notification Exception to [{$recipientEmail}]: " . $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Pemicu 1: Akun Siswa Baru Berhasil Didaftarkan.
     */
    public function notifyAccountRegistered(User $user, ?string $plainPassword = null): void
    {
        $msg = "Assalamu'alaikum Wr. Wb.\n"
             . "Selamat! Akun PPDB SMK Islam Bina Khalifah Bangsa Anda telah aktif.\n"
             . "Nama: {$user->nama}\n"
             . "NISN: {$user->nisn}\n"
             . "Username: {$user->username}\n"
             . "Silakan login ke https://smkib.com/login untuk melanjutkan pengisian formulir.";

        if ($user->email) {
            $this->sendEmail($user->email, "Akun PPDB SMK Islam Bina Khalifah Bangsa", $msg);
        }
    }

    /**
     * Pemicu 2: Pembayaran Formulir Terverifikasi Lunas (Settlement).
     */
    public function notifyPaymentReceived(Pembayaran $pembayaran): void
    {
        $pendaftar = $pembayaran->pendaftar;
        if (!$pendaftar) return;

        $msg = "Assalamu'alaikum Wr. Wb.\n"
             . "Pembayaran Biaya Formulir PPDB SMK Islam Bina Khalifah Bangsa BERHASIL.\n"
             . "Nama: {$pendaftar->nama}\n"
             . "No. Transaksi: {$pembayaran->kode_transaksi}\n"
             . "Nominal: Rp " . number_format($pembayaran->total_bayar, 0, ',', '.') . "\n"
             . "Status: LUNAS\n"
             . "Kwitansi resmi dapat diunduh di dashboard pendaftar. Tahap selanjutnya: Silakan lengkapi berkas untuk verifikasi.";

        if ($pendaftar->hp_siswa) {
            $this->sendWhatsApp($pendaftar->hp_siswa, $msg);
        }
        if ($pendaftar->user && $pendaftar->user->email) {
            $this->sendEmail($pendaftar->user->email, "Bukti Pembayaran Lunas PPDB", $msg);
        }
    }

    /**
     * Pemicu 3: Pengesahan Kelulusan Calon Siswa (SKL).
     */
    public function notifyGraduationAnnounced(Pendaftar $pendaftar, string $status, ?string $noSkl = null): void
    {
        $jurusanNama = $pendaftar->jurusan1 ? $pendaftar->jurusan1->nama_jurusan : 'SMK';

        if ($status === 'Diterima') {
            $msg = "Pengumuman Resmi PPDB SMK Islam Bina Khalifah Bangsa:\n"
                 . "Yth. {$pendaftar->nama}, Selamat Anda Dinyatakan DITERIMA pada Program Keahlian: {$jurusanNama}!\n"
                 . "No. SKL: {$noSkl}\n"
                 . "Silakan unduh Surat Keterangan Lulus (SKL) resmi dan lakukan konfirmasi Daftar Ulang di dashboard akun Anda.";
        } elseif ($status === 'Cadangan') {
            $msg = "Pengumuman PPDB SMK Islam Bina Khalifah Bangsa:\n"
                 . "Yth. {$pendaftar->nama}, Anda saat ini berstatus CADANGAN pada Program Keahlian: {$jurusanNama}.\n"
                 . "Panitia akan menghubungi Anda kembali jika terdapat kuota limpahan yang tersedia.";
        } else {
            $msg = "Pengumuman PPDB SMK Islam Bina Khalifah Bangsa:\n"
                 . "Yth. {$pendaftar->nama}, Terima kasih atas partisipasi Anda dalam seleksi PPDB. Mohon maaf Anda belum lolos pada periode ini.";
        }

        if ($pendaftar->hp_siswa) {
            $this->sendWhatsApp($pendaftar->hp_siswa, $msg);
        }
    }
}

