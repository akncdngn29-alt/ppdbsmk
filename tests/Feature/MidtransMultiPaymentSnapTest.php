<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Pendaftar;
use App\Models\Gelombang;
use App\Models\Pembayaran;
use App\Models\VoucherPendaftaran;
use App\Services\MidtransService;
use App\Services\NotificationService;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Carbon\Carbon;

class MidtransMultiPaymentSnapTest extends TestCase
{
    protected User $siswaUser;
    protected Pendaftar $pendaftar;
    protected Gelombang $gelombang;

    protected function setUp(): void
    {
        parent::setUp();

        $this->siswaUser = User::where('email', 'siswa@smkib.com')->first();
        $this->pendaftar = $this->siswaUser->pendaftar;
        $this->gelombang = Gelombang::where('status_aktif', true)->first() ?: Gelombang::first();

        $this->pendaftar->update([
            'status_pembayaran' => 'Pending',
        ]);
    }

    /**
     * 1. Halaman pembayaran siswa menampilkan seluruh metode pembayaran resmi yang diinginkan:
     *    VA (BCA, BNI, BRI, Mandiri, Permata), QRIS, GoPay, ShopeePay, Alfamart, Indomaret, serta loket TU.
     */
    public function test_halaman_pembayaran_menampilkan_pilihan_metode_lengkap_dan_instruksi(): void
    {
        $response = $this->actingAs($this->siswaUser, 'web')->get(route('siswa.pembayaran'));
        $response->assertStatus(200);

        // Header & Jalur
        $response->assertSee('Midtrans Snap Multi-Payment');
        $response->assertSee('Bayar Sekarang via Midtrans Snap');
        $response->assertSee('Bayar Tunai di Loket TU');

        // Virtual Account BCA, BNI, BRI, Mandiri, Permata
        $response->assertSee('BCA Virtual Account');
        $response->assertSee('BNI Virtual Account');
        $response->assertSee('BRI Virtual Account');
        $response->assertSee('Mandiri Bill Payment');
        $response->assertSee('Permata Virtual Account');

        // QRIS, GoPay, ShopeePay
        $response->assertSee('QRIS');
        $response->assertSee('GoPay');
        $response->assertSee('ShopeePay');

        // Minimarket Alfamart & Indomaret
        $response->assertSee('Alfamart');
        $response->assertSee('Indomaret');

        // Keterangan aktivasi akun Midtrans
        $response->assertSee('aktivasi akun resmi Midtrans', false);
    }

    /**
     * 2. Pembuatan transaksi Snap mengirim parameter enabled_payments yang mencakup
     *    seluruh metode yang didukung dan menyimpannya ke database pembayaran.
     */
    public function test_snap_transaction_sends_supported_enabled_payments_to_midtrans(): void
    {
        $serverKey = 'SB-Mid-server-MULTI-KEY-123';
        config(['services.midtrans.server_key' => $serverKey]);

        Http::fake([
            'https://app.sandbox.midtrans.com/snap/v1/transactions' => Http::response([
                'token' => 'SNAP-TOKEN-TEST-MULTICHANNEL-999',
                'redirect_url' => 'https://app.sandbox.midtrans.com/snap/v2/vtweb/SNAP-TOKEN-TEST-MULTICHANNEL-999',
            ], 201),
        ]);

        $response = $this->actingAs($this->siswaUser, 'web')
            ->postJson(route('siswa.pembayaran.snap'), [
                'metode_pembayaran' => 'Midtrans Snap',
            ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'snap_token' => 'SNAP-TOKEN-TEST-MULTICHANNEL-999',
        ]);

        Http::assertSent(function ($request) {
            $data = $request->data();
            $enabled = $data['enabled_payments'] ?? [];

            return in_array('bca_va', $enabled)
                && in_array('bni_va', $enabled)
                && in_array('bri_va', $enabled)
                && in_array('echannel', $enabled)
                && in_array('permata_va', $enabled)
                && in_array('qris', $enabled)
                && in_array('gopay', $enabled)
                && in_array('shopeepay', $enabled)
                && in_array('cstore', $enabled)
                && in_array('indomaret', $enabled)
                && in_array('alfamart', $enabled);
        });

        // Verifikasi token tersimpan di model Pembayaran
        $pembayaran = Pembayaran::where('id_pendaftar', $this->pendaftar->id_pendaftar)->first();
        $this->assertNotNull($pembayaran);
        $this->assertEquals('SNAP-TOKEN-TEST-MULTICHANNEL-999', $pembayaran->snap_token);
        $this->assertEquals('pending', $pembayaran->status_transaksi);
        $this->assertEquals('Pending', $this->pendaftar->fresh()->status_pembayaran);
    }

    /**
     * 3. Pembuatan transaksi Snap menerapkan voucher diskon pada nominal transaksi (gross_amount).
     */
    public function test_snap_payment_applies_voucher_discount_to_gross_amount(): void
    {
        $serverKey = 'SB-Mid-server-VOUCHER-TEST';
        config(['services.midtrans.server_key' => $serverKey]);

        $kodeVoucher = 'SNAPDISC' . rand(100, 999);
        $voucher = VoucherPendaftaran::create([
            'kode_voucher' => $kodeVoucher,
            'tipe' => 'nominal',
            'nilai_potongan' => 35000.00,
            'kuota_voucher' => 10,
            'kuota_terpakai' => 0,
            'status_aktif' => true,
        ]);

        Http::fake([
            'https://app.sandbox.midtrans.com/snap/v1/transactions' => Http::response([
                'token' => 'SNAP-TOKEN-DISC-123',
                'redirect_url' => 'https://app.sandbox.midtrans.com/snap/v2/vtweb/SNAP-TOKEN-DISC-123',
            ], 201),
        ]);

        $nominalAwal = (float)$this->gelombang->biaya_pendaftaran;
        $expectedTotal = max(0.00, $nominalAwal - 35000.00);

        $response = $this->actingAs($this->siswaUser, 'web')
            ->postJson(route('siswa.pembayaran.snap'), [
                'kode_voucher' => $kodeVoucher,
            ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'biaya_awal' => $nominalAwal,
            'potongan' => 35000.00,
            'total_bayar' => $expectedTotal,
            'snap_token' => 'SNAP-TOKEN-DISC-123',
        ]);

        // Pastikan Midtrans menerima gross_amount yang sudah terpotong voucher
        Http::assertSent(function ($request) use ($expectedTotal) {
            return (int)$request->data()['transaction_details']['gross_amount'] === (int)round($expectedTotal);
        });

        $voucher->refresh();
        $this->assertEquals(1, $voucher->kuota_terpakai);
    }

    /**
     * 4. Webhook callback: Status transaksi pending, settlement, expire, dan cancel.
     *    Status siswa hanya menjadi Paid setelah settlement tervalidasi.
     */
    public function test_webhook_handles_all_transaction_statuses_properly(): void
    {
        $serverKey = 'SB-Mid-server-WEBHOOK-MULTI';
        config(['services.midtrans.server_key' => $serverKey]);

        $orderId = 'TRX-TEST-STATUS-' . time();
        $grossAmount = '100000.00';

        $pembayaran = Pembayaran::updateOrCreate(
            ['id_pendaftar' => $this->pendaftar->id_pendaftar],
            [
                'id_gelombang' => $this->gelombang->id_gelombang,
                'kode_transaksi' => $orderId,
                'nominal' => 100000.00,
                'potongan' => 0.00,
                'total_bayar' => 100000.00,
                'tipe_bayar' => 'midtrans',
                'metode_pembayaran' => 'Midtrans Snap',
                'status_transaksi' => 'pending',
            ]
        );
        $this->pendaftar->update(['status_pembayaran' => 'Pending']);

        // A. Callback PENDING via GoPay
        $sigPending = hash('sha512', $orderId . '201' . $grossAmount . $serverKey);
        $resPending = $this->postJson('/api/midtrans/notification', [
            'order_id' => $orderId,
            'status_code' => '201',
            'gross_amount' => $grossAmount,
            'signature_key' => $sigPending,
            'transaction_status' => 'pending',
            'payment_type' => 'gopay',
        ]);
        $resPending->assertStatus(200);
        $pembayaran->refresh();
        $this->assertEquals('pending', $pembayaran->status_transaksi);
        $this->assertEquals('Pending', $this->pendaftar->fresh()->status_pembayaran);

        // B. Callback EXPIRE (Kedaluwarsa)
        $sigExpire = hash('sha512', $orderId . '200' . $grossAmount . $serverKey);
        $resExpire = $this->postJson('/api/midtrans/notification', [
            'order_id' => $orderId,
            'status_code' => '200',
            'gross_amount' => $grossAmount,
            'signature_key' => $sigExpire,
            'transaction_status' => 'expire',
            'payment_type' => 'bca_va',
        ]);
        $resExpire->assertStatus(200);
        $pembayaran->refresh();
        $this->assertEquals('expire', $pembayaran->status_transaksi);
        $this->assertEquals('Pending', $this->pendaftar->fresh()->status_pembayaran);

        // C. Callback CANCEL / DENY (Gagal/Dibatalkan)
        $sigCancel = hash('sha512', $orderId . '200' . $grossAmount . $serverKey);
        $resCancel = $this->postJson('/api/midtrans/notification', [
            'order_id' => $orderId,
            'status_code' => '200',
            'gross_amount' => $grossAmount,
            'signature_key' => $sigCancel,
            'transaction_status' => 'cancel',
            'payment_type' => 'shopeepay',
        ]);
        $resCancel->assertStatus(200);
        $pembayaran->refresh();
        $this->assertEquals('cancel', $pembayaran->status_transaksi);
        $this->assertEquals('Pending', $this->pendaftar->fresh()->status_pembayaran);

        // D. Callback SETTLEMENT (Berhasil Lunas)
        $sigSettlement = hash('sha512', $orderId . '200' . $grossAmount . $serverKey);
        $resSettlement = $this->postJson('/api/midtrans/notification', [
            'order_id' => $orderId,
            'status_code' => '200',
            'gross_amount' => $grossAmount,
            'signature_key' => $sigSettlement,
            'transaction_status' => 'settlement',
            'payment_type' => 'cstore',
            'store' => 'alfamart',
        ]);
        $resSettlement->assertStatus(200);
        $pembayaran->refresh();
        $this->assertEquals('settlement', $pembayaran->status_transaksi);
        $this->assertEquals('Alfamart (Midtrans)', $pembayaran->metode_pembayaran);
        $this->assertEquals('Paid', $this->pendaftar->fresh()->status_pembayaran);
    }

    /**
     * 5. Endpoint cek-status sinkronisasi status ke Midtrans API dan memberikan pesan yang jelas.
     */
    public function test_cek_status_midtrans_handles_expired_and_settled_states(): void
    {
        $serverKey = 'SB-Mid-server-CHECK-MULTI';
        config(['services.midtrans.server_key' => $serverKey]);

        $orderId = 'TRX-CHECK-' . time();
        $grossAmount = '100000.00';
        $signature = hash('sha512', $orderId . '200' . $grossAmount . $serverKey);

        $pembayaran = Pembayaran::updateOrCreate(
            ['id_pendaftar' => $this->pendaftar->id_pendaftar],
            [
                'id_gelombang' => $this->gelombang->id_gelombang,
                'kode_transaksi' => $orderId,
                'nominal' => 100000.00,
                'potongan' => 0.00,
                'total_bayar' => 100000.00,
                'tipe_bayar' => 'midtrans',
                'metode_pembayaran' => 'Midtrans Snap',
                'status_transaksi' => 'pending',
            ]
        );
        $this->pendaftar->update(['status_pembayaran' => 'Pending']);

        Http::fake([
            'https://api.sandbox.midtrans.com/v2/' . $orderId . '/status' => Http::response([
                'status_code' => '200',
                'status_message' => 'Success, transaction found',
                'order_id' => $orderId,
                'gross_amount' => $grossAmount,
                'signature_key' => $signature,
                'transaction_status' => 'settlement',
                'payment_type' => 'echannel',
            ], 200),
        ]);

        $response = $this->actingAs($this->siswaUser, 'web')->postJson(route('siswa.pembayaran.cek-status'));
        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'is_paid' => true,
            'status' => 'settlement',
        ]);
        $response->assertSee('LUNAS');

        $pembayaran->refresh();
        $this->assertEquals('settlement', $pembayaran->status_transaksi);
        $this->assertEquals('Paid', $this->pendaftar->fresh()->status_pembayaran);
    }

    /**
     * 6. Pembayaran tunai loket TU tetap dipertahankan dan terpisah dari alur Midtrans.
     */
    public function test_pembayaran_tunai_loket_tu_tetap_berjalan(): void
    {
        $response = $this->actingAs($this->siswaUser, 'web')
            ->post(route('siswa.pembayaran.tunai'), []);

        $response->assertSessionHas('success');

        $pembayaran = Pembayaran::where('id_pendaftar', $this->pendaftar->id_pendaftar)->first();
        $this->assertNotNull($pembayaran);
        $this->assertEquals('tunai_tu', $pembayaran->tipe_bayar);
        $this->assertEquals('pending', $pembayaran->status_transaksi);
        $this->assertEquals('Pending', $this->pendaftar->fresh()->status_pembayaran);
    }
}

