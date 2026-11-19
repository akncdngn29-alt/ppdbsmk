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

    public function isOnlineActive(): bool
    {
        $pengaturan = PengaturanSekolah::getPengaturan();
        return (bool) ($pengaturan->midtrans_is_active ?? true);
    }

    public function getSnapBaseUrl(): string
    {
        return $this->isProduction()
            ? 'https://app.midtrans.com/snap/v1/transactions'
            : 'https://app.sandbox.midtrans.com/snap/v1/transactions';
    }

    public function getCoreApiBaseUrl(): string
    {
        return $this->isProduction()
            ? 'https://api.midtrans.com/v2'
            : 'https://api.sandbox.midtrans.com/v2';
    }

    /**
     * Membuat transaksi QRIS resmi ke Midtrans Core API (Sandbox / Production).
     * Mengembalikan qr_string resmi dan action URL QR code untuk disimulasikan.
     */
    public function createQrisTransaction(Pendaftar $pendaftar, float $grossAmount, string $orderId, ?string $customItemName = null): array
    {
        if (!$this->isConfigured()) {
            return [
                'success' => false,
                'configured' => false,
                'order_id' => $orderId,
                'total_bayar' => $grossAmount,
                'message' => 'Kredensial Midtrans Server Key belum dikonfigurasi pada file .env atau Pengaturan Admin. Kode pembayaran QRIS hanya dapat diambil dari respons resmi Midtrans dan tidak dibuat secara rekayasa.',
            ];
        }

        $itemName = $customItemName ?: 'Biaya Formulir PPDB SMK IB Khalifah Bangsa';

        $params = [
            'payment_type' => 'qris',
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
                    'id' => 'TAGIHAN-PPDB-' . ($pendaftar->jurusan1 ? $pendaftar->jurusan1->kode_jurusan : 'SMK'),
                    'price' => (int) round($grossAmount),
                    'quantity' => 1,
                    'name' => mb_substr($itemName, 0, 50),
                ]
            ],
            'qris' => [
                'acquirer' => 'gopay',
            ],
        ];

        try {
            $http = Http::withBasicAuth($this->getServerKey(), '')->timeout(15);
            if (!$this->isProduction()) {
                $http = $http->withoutVerifying();
            }
            $response = $http->post($this->getCoreApiBaseUrl() . '/charge', $params);

            $body = $response->json();

            if ($response->successful()) {
                $qrString = $body['qr_string'] ?? null;
                $qrCodeUrl = null;
                if (!empty($body['actions']) && is_array($body['actions'])) {
                    foreach ($body['actions'] as $action) {
                        if (($action['name'] ?? '') === 'generate-qr-code') {
                            $qrCodeUrl = $action['url'] ?? null;
                            break;
                        }
                    }
                }

                return [
                    'success' => true,
                    'configured' => true,
                    'order_id' => $body['order_id'] ?? $orderId,
                    'transaction_id' => $body['transaction_id'] ?? null,
                    'gross_amount' => (float)($body['gross_amount'] ?? $grossAmount),
                    'qr_string' => $qrString,
                    'qr_code_url' => $qrCodeUrl,
                    'transaction_status' => $body['transaction_status'] ?? 'pending',
                    'simulator_url' => 'https://simulator.sandbox.midtrans.com/qris/index',
                    'raw_response' => $body,
                ];
            }

            // Jika status 406 (Order ID telah ada di Midtrans sebelumnya), periksa status transaksi terkini
            if ($response->status() === 406 || ($body['status_code'] ?? '') === '406') {
                $statusCheck = $this->checkTransactionStatus($orderId);
                if ($statusCheck['success']) {
                    return [
                        'success' => true,
                        'configured' => true,
                        'order_id' => $orderId,
                        'gross_amount' => (float)($statusCheck['data']['gross_amount'] ?? $grossAmount),
                        'qr_string' => $statusCheck['data']['qr_string'] ?? null,
                        'qr_code_url' => $statusCheck['qr_code_url'] ?? null,
                        'transaction_status' => $statusCheck['data']['transaction_status'] ?? 'pending',
                        'simulator_url' => 'https://simulator.sandbox.midtrans.com/qris/index',
                    ];
                }
            }

            Log::error('Midtrans QRIS Charge Error: ' . $response->body());
            return [
                'success' => false,
                'configured' => true,
                'order_id' => $orderId,
                'message' => 'Gagal membuat QRIS di Midtrans: ' . ($body['status_message'] ?? $response->body()),
            ];
        } catch (\Exception $e) {
            Log::error('Midtrans QRIS Exception: ' . $e->getMessage());
            return [
                'success' => false,
                'configured' => true,
                'order_id' => $orderId,
                'message' => 'Koneksi ke Midtrans API terputus: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Memeriksa status transaksi langsung ke Midtrans API (Core API GET /v2/{order_id}/status).
     */
    public function checkTransactionStatus(string $orderId): array
    {
        if (!$this->isConfigured()) {
            return [
                'success' => false,
                'configured' => false,
                'message' => 'Midtrans Server Key belum dikonfigurasi.',
            ];
        }

        try {
            $http = Http::withBasicAuth($this->getServerKey(), '')->timeout(15);
            if (!$this->isProduction()) {
                $http = $http->withoutVerifying();
            }
            $response = $http->get($this->getCoreApiBaseUrl() . '/' . urlencode($orderId) . '/status');

            $body = $response->json();

            if ($response->successful()) {
                $qrCodeUrl = null;
                if (!empty($body['actions']) && is_array($body['actions'])) {
                    foreach ($body['actions'] as $action) {
                        if (($action['name'] ?? '') === 'generate-qr-code') {
                            $qrCodeUrl = $action['url'] ?? null;
                            break;
                        }
                    }
                }

                return [
                    'success' => true,
                    'configured' => true,
                    'data' => $body,
                    'qr_code_url' => $qrCodeUrl,
                    'status' => $body['transaction_status'] ?? 'pending',
                ];
            }

            return [
                'success' => false,
                'configured' => true,
                'status_code' => $response->status(),
                'message' => $body['status_message'] ?? 'Transaksi tidak ditemukan di Midtrans.',
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'configured' => true,
                'message' => 'Gagal menghubungi server Midtrans: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Membuat Snap Token transaksi ke Midtrans.
     * Menggunakan enabled_payments agar metode pembayaran yang ditampilkan
     * pada jendela Midtrans Snap hanya metode yang didukung dan aktif pada akun merchant.
     */
    public function createSnapTransaction(Pendaftar $pendaftar, float $grossAmount, string $orderId, ?array $enabledPayments = null): array
    {
        if (!$this->isConfigured()) {
            // Mode simulasi/fallback jika kunci API belum dipasang di .env
            return [
                'token' => 'SIMULATED-SNAP-' . strtoupper(substr(md5($orderId), 0, 16)),
                'redirect_url' => route('siswa.pembayaran'),
                'is_simulated' => true,
            ];
        }

        // Daftar metode pembayaran resmi yang didukung PPDB sesuai aktivasi akun Midtrans:
        // 1. Virtual Account (BCA, BNI, BRI, Mandiri/echannel, Permata)
        // 2. QRIS
        // 3. GoPay
        // 4. ShopeePay
        // 5. Alfamart dan Indomaret (cstore)
        $defaultEnabledPayments = [
            'bca_va',
            'bni_va',
            'bri_va',
            'echannel',
            'permata_va',
            'qris',
            'gopay',
            'shopeepay',
            'cstore',
            'indomaret',
            'alfamart',
        ];

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
            'enabled_payments' => !empty($enabledPayments) ? $enabledPayments : $defaultEnabledPayments,
        ];

        try {
            $http = Http::withBasicAuth($this->getServerKey(), '')->timeout(15);
            if (!$this->isProduction()) {
                $http = $http->withoutVerifying();
            }
            $response = $http->post($this->getSnapBaseUrl(), $params);

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
     * Memproses callback notifikasi Midtrans secara fail-closed dan idempotent.
     */
    public function processNotification(array $payload): array
    {
        $serverKey = $this->getServerKey();
        if (empty($serverKey)) {
            Log::error("Midtrans Webhook: Server key tidak dikonfigurasi. Webhook ditolak (fail-closed).");
            return ['success' => false, 'message' => 'Server key not configured. Webhook rejected.'];
        }

        $orderId = $payload['order_id'] ?? null;
        $statusCode = $payload['status_code'] ?? null;
        $grossAmount = $payload['gross_amount'] ?? null;
        $signatureKey = $payload['signature_key'] ?? null;
        $transactionStatus = $payload['transaction_status'] ?? null;
        $fraudStatus = $payload['fraud_status'] ?? null;
        $paymentType = $payload['payment_type'] ?? 'midtrans';

        if (!$orderId || !$statusCode || !$grossAmount || !$signatureKey) {
            return ['success' => false, 'message' => 'Payload tidak lengkap.'];
        }

        // 1. Verifikasi Signature SHA-512 secara timing-safe (prinsip fail-closed)
        if (!$this->verifySignature((string)$orderId, (string)$statusCode, (string)$grossAmount, (string)$signatureKey)) {
            Log::warning("Midtrans Webhook: Signature SHA-512 tidak valid untuk Order ID: {$orderId}");
            return ['success' => false, 'message' => 'Invalid signature key.'];
        }

        // 2. Validasi hubungan Order ID dengan database pendaftaran
        $pembayaran = Pembayaran::with('pendaftar')
            ->where('kode_transaksi', $orderId)
            ->first();

        if (!$pembayaran) {
            Log::warning("Midtrans Webhook: Transaksi {$orderId} tidak ditemukan di database.");
            return ['success' => false, 'message' => 'Transaksi tidak ditemukan.'];
        }

        if (!$pembayaran->pendaftar) {
            Log::warning("Midtrans Webhook: Transaksi {$orderId} tidak memiliki pendaftar terkait.");
            return ['success' => false, 'message' => 'Hubungan transaksi dengan pendaftar tidak valid.'];
        }

        // 3. Validasi mata uang jika disertakan (harus IDR)
        if (!empty($payload['currency']) && strtoupper($payload['currency']) !== 'IDR') {
            Log::warning("Midtrans Webhook: Mata uang tidak valid: {$payload['currency']}");
            return ['success' => false, 'message' => 'Mata uang tidak valid. Harus IDR.'];
        }

        // 4. Validasi nominal tagihan secara presisi menggunakan representasi desimal (mencegah float imprecision)
        $expectedAmount = number_format((float)$pembayaran->total_bayar, 2, '.', '');
        $receivedAmount = number_format((float)$grossAmount, 2, '.', '');

        if (bccomp($expectedAmount, $receivedAmount, 2) !== 0) {
            Log::warning("Midtrans Webhook: Nominal tidak cocok. Tagihan tercatat: {$expectedAmount}, Diterima: {$receivedAmount}");
            return ['success' => false, 'message' => 'Gross amount mismatch. Tagihan tercatat tidak sama dengan nominal diterima.'];
        }

        // 5. Idempotensi: jika status sudah settlement, callback ulang tidak menggandakan transaksi
        if ($pembayaran->status_transaksi === 'settlement' && in_array($transactionStatus, ['settlement', 'capture'])) {
            return [
                'success' => true,
                'message' => 'Transaksi sudah berstatus settlement sebelumnya (Idempotent).',
                'status' => 'settlement'
            ];
        }

        // 6. Validasi status transaksi resmi Midtrans
        $validStatuses = ['capture', 'settlement', 'pending', 'deny', 'expire', 'cancel'];
        if (!in_array($transactionStatus, $validStatuses)) {
            Log::warning("Midtrans Webhook: Status transaksi tidak dikenali: {$transactionStatus}");
            return ['success' => false, 'message' => "Unknown transaction status: {$transactionStatus}"];
        }

        // 7. Eksekusi mutasi status transaksi secara konsisten di dalam DB transaction
        DB::transaction(function () use ($pembayaran, $transactionStatus, $fraudStatus, $paymentType, $orderId, $payload) {
            $isPaid = false;

            if ($transactionStatus === 'capture') {
                if ($fraudStatus === 'accept') {
                    $pembayaran->status_transaksi = 'settlement';
                    $isPaid = true;
                } elseif ($fraudStatus === 'challenge') {
                    $pembayaran->status_transaksi = 'pending';
                    $isPaid = false;
                } else {
                    $pembayaran->status_transaksi = 'cancel';
                    $isPaid = false;
                }
            } elseif ($transactionStatus === 'settlement') {
                $pembayaran->status_transaksi = 'settlement';
                $isPaid = true;
            } elseif ($transactionStatus === 'pending') {
                $pembayaran->status_transaksi = 'pending';
                $isPaid = false;
            } elseif ($transactionStatus === 'expire') {
                $pembayaran->status_transaksi = 'expire';
                $isPaid = false;
            } elseif ($transactionStatus === 'cancel' || $transactionStatus === 'deny') {
                $pembayaran->status_transaksi = 'cancel';
                $isPaid = false;
            }

            // Format label metode pembayaran yang rapi
            $metodeLabel = match ($paymentType) {
                'qris' => 'QRIS (Midtrans)',
                'gopay' => 'GoPay (Midtrans)',
                'shopeepay' => 'ShopeePay (Midtrans)',
                'echannel' => 'Mandiri Bill Payment (Midtrans)',
                'cstore' => !empty($payload['store']) ? ucfirst($payload['store']) . ' (Midtrans)' : 'Minimarket (Midtrans)',
                'bank_transfer' => !empty($payload['va_numbers'][0]['bank'])
                    ? strtoupper($payload['va_numbers'][0]['bank']) . ' Virtual Account (Midtrans)'
                    : (!empty($payload['permata_va_number']) ? 'Permata Virtual Account (Midtrans)' : 'Virtual Account (Midtrans)'),
                default => ucfirst(str_replace('_', ' ', $paymentType)),
            };

            $pembayaran->metode_pembayaran = $metodeLabel;
            $pembayaran->waktu_transaksi = Carbon::now();
            $pembayaran->catatan = "Status mutasi Midtrans Webhook: {$transactionStatus} ({$paymentType})";
            $pembayaran->save();

            if ($pembayaran->pendaftar) {
                if ($pembayaran->jenis_pembayaran === 'daftar_ulang') {
                    $newStatus = $isPaid ? 'Selesai' : 'Belum';
                    if ($pembayaran->pendaftar->status_daftar_ulang !== $newStatus) {
                        $pembayaran->pendaftar->update(['status_daftar_ulang' => $newStatus]);
                    }
                } else {
                    $newStatus = $isPaid ? 'Paid' : 'Pending';
                    if ($pembayaran->pendaftar->status_pembayaran !== $newStatus) {
                        $pembayaran->pendaftar->update(['status_pembayaran' => $newStatus]);
                    }
                }
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

