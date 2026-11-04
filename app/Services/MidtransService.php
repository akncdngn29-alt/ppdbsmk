<?php

namespace App\Services;

use App\Models\Pembayaran;
use App\Models\Pendaftar;
use App\Models\PengaturanSekolah;
use App\Models\LogAktivitas;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class MidtransService
{
    public function getServerKey(): ?string
    {
        $key = config('services.midtrans.server_key');
        if (empty($key)) {
            $pengaturan = PengaturanSekolah::getPengaturan();
            $key = $pengaturan->midtrans_server_key;
        }
        return $key;
    }

    public function getClientKey(): ?string
    {
        $key = config('services.midtrans.client_key');
        if (empty($key)) {
            $pengaturan = PengaturanSekolah::getPengaturan();
            $key = $pengaturan->midtrans_client_key;
        }
        return $key;
    }

    public function isProduction(): bool
    {
        $isProd = config('services.midtrans.is_production');
        if ($isProd === null) {
            $pengaturan = PengaturanSekolah::getPengaturan();
            $isProd = (bool)$pengaturan->midtrans_is_production;
        }
        return (bool)$isProd;
    }

    public function isConfigured(): bool
    {
        return !empty($this->getServerKey());
    }

    public function getSnapBaseUrl(): string
    {
        return $this->isProduction()
            ? 'https://app.midtrans.com/snap/v1/transactions'
            : 'https://app.sandbox.midtrans.com/snap/v1/transactions';
    }

    /**
     * Membuat Snap Token transaksi ke Midtrans.
     */
    public function createSnapTransaction(Pendaftar $pendaftar, float $grossAmount, string $orderId): array
    {
        if (!$this->isConfigured()) {
            // Mode simulasi/fallback jika kunci API belum dipasang di .env
            return [
                'token' => 'SIMULATED-SNAP-' . strtoupper(substr(md5($orderId), 0, 16)),
                'redirect_url' => route('siswa.pembayaran'),
                'is_simulated' => true,
            ];
        }

        $params = [
            'transaction_details' => [
                'order_id' => $orderId,
                'gross_amount' => (int) round($grossAmount),
            ],
            'customer_details' => [
                'first_name' => $pendaftar->nama,
                'email' => $pendaftar->user ? $pendaftar->user->email : 'siswa@smkib.com',
                'phone' => $pendaftar->hp_siswa,
            ],
            'item_details' => [
                [
                    'id' => 'FORM-PPDB-' . ($pendaftar->jurusan1 ? $pendaftar->jurusan1->kode_jurusan : 'SMK'),
                    'price' => (int) round($grossAmount),
                    'quantity' => 1,
                    'name' => 'Biaya Formulir PPDB SMK IB Khalifah Bangsa',
                ]
            ],
        ];

        try {
            $response = Http::withBasicAuth($this->getServerKey(), '')
                ->timeout(15)
                ->post($this->getSnapBaseUrl(), $params);

            if ($response->successful()) {
                $body = $response->json();
                return [
                    'token' => $body['token'] ?? null,
                    'redirect_url' => $body['redirect_url'] ?? null,
                    'is_simulated' => false,
                ];
            }

            Log::error('Midtrans Snap Error: ' . $response->body());
        } catch (\Exception $e) {
            Log::error('Midtrans Connection Exception: ' . $e->getMessage());
        }

        return [
            'token' => 'SIMULATED-SNAP-' . strtoupper(substr(md5($orderId), 0, 16)),
            'redirect_url' => route('siswa.pembayaran'),
            'is_simulated' => true,
        ];
    }

    /**
     * Memvalidasi signature SHA-512 webhook resmi dari Midtrans.
     */
    public function verifySignature(string $orderId, string $statusCode, string $grossAmount, string $signatureKey): bool
    {
        $serverKey = $this->getServerKey();
        if (empty($serverKey)) {
            return false;
        }

        $calculated = hash('sha512', $orderId . $statusCode . $grossAmount . $serverKey);
        return hash_equals($calculated, $signatureKey);
    }

    /**
     * Memproses callback notifikasi Midtrans secara idempotent.
     */
    public function processNotification(array $payload): array
    {
        $orderId = $payload['order_id'] ?? null;
        $statusCode = $payload['status_code'] ?? null;
        $grossAmount = $payload['gross_amount'] ?? null;
        $signatureKey = $payload['signature_key'] ?? null;
        $transactionStatus = $payload['transaction_status'] ?? null;
        $fraudStatus = $payload['fraud_status'] ?? null;
        $paymentType = $payload['payment_type'] ?? 'midtrans';

        if (!$orderId || !$statusCode || !$grossAmount) {
            return ['success' => false, 'message' => 'Payload tidak lengkap.'];
        }

        // Validasi signature hanya jika API key terkonfigurasi
        if ($this->isConfigured()) {
            if (!$this->verifySignature($orderId, $statusCode, $grossAmount, (string)$signatureKey)) {
                Log::warning("Midtrans Signature Invalid untuk Order ID: {$orderId}");
                return ['success' => false, 'message' => 'Invalid signature key.'];
            }
        }

        $pembayaran = Pembayaran::with('pendaftar')
            ->where('kode_transaksi', $orderId)
            ->first();

        if (!$pembayaran) {
            Log::warning("Midtrans Webhook: Transaksi {$orderId} tidak ditemukan di database.");
            return ['success' => false, 'message' => 'Transaksi tidak ditemukan.'];
        }

        // Idempotent: jika transaksi sudah 'settlement', jangan ubah lagi
        if ($pembayaran->status_transaksi === 'settlement' && in_array($transactionStatus, ['settlement', 'capture'])) {
            return ['success' => true, 'message' => 'Transaksi sudah berstatus settlement sebelumnya (Idempotent).'];
        }

        DB::transaction(function () use ($pembayaran, $transactionStatus, $fraudStatus, $paymentType, $orderId) {
            $isPaid = false;

            if ($transactionStatus === 'capture') {
                if ($fraudStatus === 'accept') {
                    $pembayaran->status_transaksi = 'settlement';
                    $isPaid = true;
                }
            } elseif ($transactionStatus === 'settlement') {
                $pembayaran->status_transaksi = 'settlement';
                $isPaid = true;
            } elseif ($transactionStatus === 'pending') {
                $pembayaran->status_transaksi = 'pending';
            } elseif (in_array($transactionStatus, ['deny', 'expire', 'cancel'])) {
                $pembayaran->status_transaksi = ($transactionStatus === 'expire') ? 'expire' : 'cancel';
            }

            $pembayaran->metode_pembayaran = $paymentType;
            $pembayaran->waktu_transaksi = Carbon::now();
            $pembayaran->catatan = "Status mutasi Midtrans Webhook: {$transactionStatus} ({$paymentType})";
            $pembayaran->save();

            if ($isPaid && $pembayaran->pendaftar) {
                $pembayaran->pendaftar->update(['status_pembayaran' => 'Paid']);
            }

            LogAktivitas::log(
                'UPDATE',
                "Webhook Midtrans: Transaksi {$orderId} diupdate ke status {$pembayaran->status_transaksi}",
                'pembayaran',
                $pembayaran->id_pembayaran
            );
        });

        return ['success' => true, 'status' => $pembayaran->status_transaksi];
    }
}

