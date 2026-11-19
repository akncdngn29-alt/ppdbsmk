<?php

namespace App\Services;

use App\Models\User;
use App\Models\Pendaftar;
use App\Models\Pembayaran;
use App\Models\PengaturanSekolah;
use App\Models\LogAktivitas;
use Illuminate\Support\Facades\Log;

class NotificationService
{
    /**
     * Ambil WhatsApp API token dari .env atau pengaturan database.
     * Sesuai Ketentuan Section 4.E, pengiriman eksternal dinonaktifkan secara permanen.
     */
    public function getWhatsAppToken(): ?string
    {
        return null;
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
     * Kirim pesan WhatsApp (Dinonaktifkan Permanen Sesuai Kebijakan tugas.md Section 4.E).
     * Tidak mengirim pesan keluar ke provider manapun.
     */
    public function sendWhatsApp(string $phone, string $message): array
    {
        $formattedPhone = $this->formatPhoneNumber($phone);
        
        Log::info("WhatsApp Notification BLOCKED/DISABLED (Internal Policy) to [{$formattedPhone}]: {$message}");

        return [
            'success' => true,
            'mode' => 'simulated',
            'phone' => $formattedPhone,
            'message' => 'Pengiriman notifikasi eksternal WhatsApp dinonaktifkan / simulator internal sesuai spesifikasi sistem.',
        ];
    }

    /**
     * Kirim notifikasi Email (Dinonaktifkan Permanen Sesuai Kebijakan tugas.md Section 4.E).
     * Tidak mengirim email keluar via SMTP.
     */
    public function sendEmail(string $recipientEmail, string $subject, string $bodyText): array
    {
        Log::info("Email Notification BLOCKED/DISABLED (Internal Policy) to [{$recipientEmail}] - {$subject}");

        return [
            'success' => true,
            'mode' => 'disabled',
            'email' => $recipientEmail,
            'message' => 'Pengiriman email eksternal dinonaktifkan sesuai spesifikasi sistem.',
        ];
    }

    /**
     * Pemicu 1: Akun Siswa Baru Berhasil Didaftarkan.
     */
    public function notifyAccountRegistered(User $user, ?string $plainPassword = null): void
    {
        // Notifikasi eksternal dinonaktifkan. Flash message internal ditangani oleh AuthController.
        Log::info("Pemberitahuan registrasi akun {$user->username} tercatat di sistem internal.");
    }

    /**
     * Pemicu 2: Pembayaran Formulir Terverifikasi Lunas (Settlement).
     */
    public function notifyPaymentReceived(Pembayaran $pembayaran): void
    {
        // Notifikasi eksternal dinonaktifkan. Status lunas langsung tampil di dashboard calon siswa.
        Log::info("Pemberitahuan pelunasan pembayaran {$pembayaran->kode_transaksi} tercatat di sistem internal.");
    }

    /**
     * Pemicu 3: Pengesahan Kelulusan Calon Siswa (SKL).
     */
    public function notifyGraduationAnnounced(Pendaftar $pendaftar, string $status, ?string $noSkl = null): void
    {
        // Notifikasi eksternal dinonaktifkan. Status kelulusan dan unduhan SKL langsung tersedia di portal siswa.
        Log::info("Pemberitahuan kelulusan {$pendaftar->no_pendaftaran} ({$status}) tercatat di sistem internal.");
    }
}
