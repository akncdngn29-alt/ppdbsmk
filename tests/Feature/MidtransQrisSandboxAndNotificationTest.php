<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Admin;
use App\Models\Pendaftar;
use App\Models\Gelombang;
use App\Models\Pembayaran;
use App\Models\PengaturanSekolah;
use App\Models\VoucherPendaftaran;
use App\Services\NotificationService;
use App\Services\MidtransService;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;

class MidtransQrisSandboxAndNotificationTest extends TestCase
{
    /**
     * 1. Halaman Pengaturan tidak lagi memiliki kolom WhatsApp Gateway API Token.
     */
    public function test_admin_pengaturan_does_not_contain_whatsapp_token_input(): void
    {
        $admin = Admin::where('role', 'tu')->first();
        $this->assertNotNull($admin);

        $response = $this->actingAs($admin, 'admin')->get('/admin/pengaturan');
        $response->assertStatus(200);

        // Pastikan input name="whatsapp_api_token" dan label Fonnte/Wablas tidak ada lagi
        $response->assertDontSee('name="whatsapp_api_token"', false);
        $response->assertDontSee('Fonnte / Wablas');
        $response->assertDontSee('WhatsApp Gateway API Token');

        // Pastikan field Midtrans tetap ada
        $response->assertSee('name="midtrans_server_key"', false);
        $response->assertSee('name="midtrans_client_key"', false);
    }

    /**
     * 2. Simpan pengaturan tanpa WhatsApp token berhasil.
     */
    public function test_simpan_pengaturan_succeeds_without_whatsapp_token(): void
    {
        $admin = Admin::where('role', 'tu')->first();
        $pengaturan = PengaturanSekolah::getPengaturan();

        $response = $this->actingAs($admin, 'admin')->post('/admin/pengaturan', [
            'nama_sekolah' => $pengaturan->nama_sekolah,
            'npsn' => $pengaturan->npsn,
            'ijin_operasional' => $pengaturan->ijin_operasional,
            'alamat' => $pengaturan->alamat,
            'kota' => $pengaturan->kota,
            'provinsi' => $pengaturan->provinsi,
            'no_telp' => $pengaturan->no_telp,
            'email' => $pengaturan->email,
            'website' => $pengaturan->website,
            'nama_kepsek' => $pengaturan->nama_kepsek,
            'nip_kepsek' => $pengaturan->nip_kepsek,
            'midtrans_server_key' => 'SB-Mid-server-test-123',
            'midtrans_client_key' => 'SB-Mid-client-test-123',
        ]);

        $response->assertSessionHas('success');
    }

    /**
     * 3. Pengiriman eksternal WhatsApp dan Email dinonaktifkan permanen dan tidak memicu panggilan jaringan.
     */
    public function test_external_notifications_disabled_permanently(): void
    {
        Http::fake();
        Mail::fake();

        $service = new NotificationService();

        $waRes = $service->sendWhatsApp('081234567890', 'Uji Pesan WA');
        $this->assertTrue($waRes['success']);
        $this->assertEquals('simulated', $waRes['mode']);

        $mailRes = $service->sendEmail('user@test.com', 'Subjek Uji', 'Isi Body');
        $this->assertTrue($mailRes['success']);
        $this->assertEquals('disabled', $mailRes['mode']);

        $this->assertNull($service->getWhatsAppToken());

        Http::assertNothingSent();
        Mail::assertNothingSent();
    }

    /**
     * 4. Jika Server Key belum dikonfigurasi, sistem secara jujur melaporkan unconfigured dan tidak merekayasa kode.
     */
    public function test_qris_returns_unconfigured_when_server_key_is_missing(): void
    {
        config(['services.midtrans.server_key' => null]);
        $pengaturan = PengaturanSekolah::getPengaturan();
        $pengaturan->update(['midtrans_server_key' => null]);

        $user = User::where('email', 'siswa@smkib.com')->first();
        $this->assertNotNull($user);
        $user->pendaftar->update(['status_pembayaran' => 'Pending']);

        $response = $this->actingAs($user, 'web')->postJson('/siswa/pembayaran/qris', []);
        $response->assertStatus(200);
        $response->assertJson([
            'success' => false,
            'configured' => false,
        ]);
        $this->assertStringContainsString('belum dikonfigurasi', $response->json('message'));
    }

    /**
     * 5. QRIS memanggil API resmi Midtrans dan mengembalikan qr_string serta actions url resmi dengan nominal yang presisi.
     */
    public function test_qris_payment_fetches_real_midtrans_response_and_does_not_mark_paid(): void
    {
        $serverKey = 'SB-Mid-server-TEST-KEY-123';
        config(['services.midtrans.server_key' => $serverKey]);

        $officialQrString = '00020101021226680016ID.CO.SHOPEE.WWW011893600918000000000002150000000000000000303UME51440014ID.CO.GOPAY.WWW02150000000000000000303UME52045999530336054061000005802ID5911MIDTRANS MER6007JAKARTA61051219062070703A016304ABCD';
        $officialQrUrl = 'https://api.sandbox.midtrans.com/v2/qris/test-tx-uuid/qr-code';

        Http::fake([
            'https://api.sandbox.midtrans.com/v2/charge' => Http::response([
                'status_code' => '201',
                'status_message' => 'QRIS transaction is created',
                'transaction_id' => 'test-tx-uuid',
                'order_id' => 'TRX-TEST-QRIS-001',
                'gross_amount' => '100000.00',
                'currency' => 'IDR',
                'payment_type' => 'qris',
                'transaction_status' => 'pending',
                'actions' => [
                    [
                        'name' => 'generate-qr-code',
                        'method' => 'GET',
                        'url' => $officialQrUrl,
                    ]
                ],
                'qr_string' => $officialQrString,
            ], 201),
        ]);

        $user = User::where('email', 'siswa@smkib.com')->first();
        $pendaftar = $user->pendaftar;
        $this->assertNotNull($pendaftar);
        $pendaftar->update(['status_pembayaran' => 'Pending']);

        $response = $this->actingAs($user, 'web')->postJson('/siswa/pembayaran/qris', []);
        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'configured' => true,
            'qr_string' => $officialQrString,
            'qr_code_url' => $officialQrUrl,
            'status' => 'pending',
        ]);

        // Pastikan kode berasal dari Midtrans dan status pendaftar TIDAK menjadi Paid
        $pendaftar->refresh();
        $this->assertEquals('Pending', $pendaftar->status_pembayaran);

        $pembayaran = Pembayaran::where('id_pendaftar', $pendaftar->id_pendaftar)->first();
        $this->assertNotNull($pembayaran);
        $this->assertEquals('pending', $pembayaran->status_transaksi);
        $this->assertEquals($officialQrString, $pembayaran->qr_string);
        $this->assertEquals($officialQrUrl, $pembayaran->qr_url);
    }

    /**
     * 6. Webhook callback: Status hanya berubah menjadi Lunas setelah callback settlement terverifikasi valid signature SHA-512.
     */
    public function test_midtrans_webhook_validates_signature_and_updates_status(): void
    {
        $serverKey = 'SB-Mid-server-MOCK-WEBHOOK-KEY';
        config(['services.midtrans.server_key' => $serverKey]);

        $user = User::where('email', 'siswa@smkib.com')->first();
        $pendaftar = $user->pendaftar;
        $orderId = 'TRX-QRIS-VAL-' . time();
        $grossAmount = '100000.00';

        $pembayaran = Pembayaran::updateOrCreate(
            ['id_pendaftar' => $pendaftar->id_pendaftar],
            [
                'id_gelombang' => 1,
                'kode_transaksi' => $orderId,
                'nominal' => 100000.00,
                'potongan' => 0.00,
                'total_bayar' => 100000.00,
                'tipe_bayar' => 'midtrans',
                'metode_pembayaran' => 'QRIS (Midtrans Sandbox)',
                'status_transaksi' => 'pending',
            ]
        );
        $pendaftar->update(['status_pembayaran' => 'Pending']);

        // 1. Coba kirim callback dengan signature PALSU -> Harus ditolak (fail-closed)
        $fakeSignature = hash('sha512', 'SALAH_KEY');
        $badResponse = $this->postJson('/api/midtrans/notification', [
            'order_id' => $orderId,
            'status_code' => '200',
            'gross_amount' => $grossAmount,
            'signature_key' => $fakeSignature,
            'transaction_status' => 'settlement',
            'payment_type' => 'qris',
        ]);
        $badResponse->assertStatus(400);

        $pembayaran->refresh();
        $this->assertEquals('pending', $pembayaran->status_transaksi);
        $pendaftar->refresh();
        $this->assertEquals('Pending', $pendaftar->status_pembayaran);

        // 2. Coba kirim status 'pending' -> status tetap pending, TIDAK menjadi Paid
        $statusCode = '201';
        $validSignaturePending = hash('sha512', $orderId . $statusCode . $grossAmount . $serverKey);
        $pendingResponse = $this->postJson('/api/midtrans/notification', [
            'order_id' => $orderId,
            'status_code' => $statusCode,
            'gross_amount' => $grossAmount,
            'signature_key' => $validSignaturePending,
            'transaction_status' => 'pending',
            'payment_type' => 'qris',
        ]);
        $pendingResponse->assertStatus(200);

        $pembayaran->refresh();
        $this->assertEquals('pending', $pembayaran->status_transaksi);
        $pendaftar->refresh();
        $this->assertEquals('Pending', $pendaftar->status_pembayaran);

        // 3. Coba kirim status 'expire' -> status expire, TIDAK menjadi Paid
        $statusCodeExpire = '200';
        $validSignatureExpire = hash('sha512', $orderId . $statusCodeExpire . $grossAmount . $serverKey);
        $expireResponse = $this->postJson('/api/midtrans/notification', [
            'order_id' => $orderId,
            'status_code' => $statusCodeExpire,
            'gross_amount' => $grossAmount,
            'signature_key' => $validSignatureExpire,
            'transaction_status' => 'expire',
            'payment_type' => 'qris',
        ]);
        $expireResponse->assertStatus(200);

        $pembayaran->refresh();
        $this->assertEquals('expire', $pembayaran->status_transaksi);
        $pendaftar->refresh();
        $this->assertEquals('Pending', $pendaftar->status_pembayaran);

        // 4. Kirim callback resmi 'settlement' dengan signature valid -> Status berubah LUNAS
        $statusCodeSettlement = '200';
        $validSignature = hash('sha512', $orderId . $statusCodeSettlement . $grossAmount . $serverKey);
        $goodResponse = $this->postJson('/api/midtrans/notification', [
            'order_id' => $orderId,
            'status_code' => $statusCodeSettlement,
            'gross_amount' => $grossAmount,
            'signature_key' => $validSignature,
            'transaction_status' => 'settlement',
            'payment_type' => 'qris',
        ]);
        $goodResponse->assertStatus(200);

        $pembayaran->refresh();
        $this->assertEquals('settlement', $pembayaran->status_transaksi);
        $pendaftar->refresh();
        $this->assertEquals('Paid', $pendaftar->status_pembayaran);

        // 5. Idempotent check: kirim ulang payload settlement yang sama
        $idempotentResponse = $this->postJson('/api/midtrans/notification', [
            'order_id' => $orderId,
            'status_code' => $statusCodeSettlement,
            'gross_amount' => $grossAmount,
            'signature_key' => $validSignature,
            'transaction_status' => 'settlement',
            'payment_type' => 'qris',
        ]);
        $idempotentResponse->assertStatus(200);
        $this->assertTrue($idempotentResponse->json('data.success'));
    }

    /**
     * 7. Endpoint cek-status menyinkronkan status dari API Midtrans ke database lokal.
     */
    public function test_cek_status_endpoint_syncs_with_midtrans_api(): void
    {
        $serverKey = 'SB-Mid-server-CHECK-KEY';
        config(['services.midtrans.server_key' => $serverKey]);

        $user = User::where('email', 'siswa@smkib.com')->first();
        $pendaftar = $user->pendaftar;
        $orderId = 'TRX-SYNC-' . time();
        $grossAmount = '100000.00';
        $signature = hash('sha512', $orderId . '200' . $grossAmount . $serverKey);

        $pembayaran = Pembayaran::updateOrCreate(
            ['id_pendaftar' => $pendaftar->id_pendaftar],
            [
                'id_gelombang' => 1,
                'kode_transaksi' => $orderId,
                'nominal' => 100000.00,
                'potongan' => 0.00,
                'total_bayar' => 100000.00,
                'tipe_bayar' => 'midtrans',
                'metode_pembayaran' => 'QRIS (Midtrans Sandbox)',
                'status_transaksi' => 'pending',
            ]
        );
        $pendaftar->update(['status_pembayaran' => 'Pending']);

        Http::fake([
            'https://api.sandbox.midtrans.com/v2/' . $orderId . '/status' => Http::response([
                'status_code' => '200',
                'status_message' => 'Success, transaction found',
                'order_id' => $orderId,
                'gross_amount' => $grossAmount,
                'signature_key' => $signature,
                'transaction_status' => 'settlement',
                'payment_type' => 'qris',
            ], 200),
        ]);

        $response = $this->actingAs($user, 'web')->postJson('/siswa/pembayaran/cek-status');
        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'is_paid' => true,
            'status' => 'settlement',
        ]);

        $pembayaran->refresh();
        $this->assertEquals('settlement', $pembayaran->status_transaksi);
        $pendaftar->refresh();
        $this->assertEquals('Paid', $pendaftar->status_pembayaran);
    }
}
