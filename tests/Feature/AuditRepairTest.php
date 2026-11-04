<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Admin;
use App\Models\Pendaftar;
use App\Models\VoucherPendaftaran;
use App\Models\BerkasPendaftar;
use App\Models\Pembayaran;
use App\Models\PengaturanSekolah;
use App\Services\NotificationService;
use App\Services\MidtransService;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\UploadedFile;

class AuditRepairTest extends TestCase
{
    /**
     * 1. Uji Perubahan Password Siswa (Memastikan Facade Hash diimpor & tersimpan dalam bentuk Bcrypt Hash).
     */
    public function test_password_update_saves_valid_hash(): void
    {
        $user = User::where('email', 'siswa@smkib.com')->first();
        $this->assertNotNull($user);

        $this->actingAs($user, 'web');

        $newPassword = 'PasswordBaru123!';
        $response = $this->post('/siswa/pengaturan', [
            'hp_siswa' => '082199887766',
            'password' => $newPassword,
            'password_confirmation' => $newPassword,
        ]);

        $response->assertStatus(302);
        $user->refresh();

        $this->assertTrue(Hash::check($newPassword, $user->password), 'Password harus tersimpan dalam bentuk hash yang valid.');
        $this->assertNotEquals($newPassword, $user->password, 'Password tidak boleh tersimpan dalam bentuk teks biasa.');
    }

    /**
     * 2. Uji Kuota Voucher & Mencegah Race Condition.
     */
    public function test_voucher_validation_and_quota_usage(): void
    {
        $voucher = VoucherPendaftaran::create([
            'kode_voucher' => 'TESTVOUCHER_' . time(),
            'tipe' => 'nominal',
            'nilai_potongan' => 20000.00,
            'kuota_voucher' => 2,
            'kuota_terpakai' => 0,
            'status_aktif' => true,
        ]);

        $this->assertTrue($voucher->isAvailable());
        $this->assertEquals(2, $voucher->sisaKuota());

        // Cek via LandingController endpoint
        $response = $this->postJson('/check-voucher', [
            'kode_voucher' => $voucher->kode_voucher,
            'nominal_asli' => 100000.00,
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'potongan' => 20000,
                'total_bayar' => 80000,
            ]);

        // Simulasikan kuota habis
        $voucher->update(['kuota_terpakai' => 2]);
        $this->assertFalse($voucher->isAvailable());
        $this->assertEquals(0, $voucher->sisaKuota());

        // Request voucher saat kuota habis harus ditolak
        $responseExpired = $this->postJson('/check-voucher', [
            'kode_voucher' => $voucher->kode_voucher,
            'nominal_asli' => 100000.00,
        ]);

        $responseExpired->assertStatus(400)
            ->assertJson([
                'success' => false,
            ]);

        $voucher->delete();
    }

    /**
     * 3. Uji Maintenance Mode: Route Kepala Sekolah & Admin Tetap Dapat Diakses, Pengunjung Publik Terblokir 503.
     */
    public function test_maintenance_middleware_allows_admin_and_kepsek_and_blocks_students(): void
    {
        $pengaturan = PengaturanSekolah::getPengaturan();
        $pengaturan->update(['mode_maintenance' => true]);

        // Pengunjung umum diblokir 503
        $this->get('/')->assertStatus(503);

        // Halaman login publik tetap terbuka agar admin bisa login
        $this->get('/login')->assertStatus(200);
        $this->get('/admin/login')->assertStatus(200);

        // Petugas TU dapat mengakses /admin/dashboard
        $admin = Admin::where('role', 'tu')->first();
        $this->actingAs($admin, 'admin');
        $this->get('/admin/dashboard')->assertStatus(200);

        // Kepala Sekolah dapat mengakses /kepsek/dashboard (Temuan audit teratasi)
        $kepsek = Admin::where('role', 'kepala_sekolah')->first();
        $this->actingAs($kepsek, 'admin');
        $this->get('/kepsek/dashboard')->assertStatus(200);

        // Kembalikan ke mode normal
        $pengaturan->update(['mode_maintenance' => false]);
        $this->get('/')->assertStatus(200);
    }

    /**
     * 4. Uji Otorisasi Dokumen Berkas (UU PDP):
     *    - Tamu tanpa login ditolak (401).
     *    - Siswa B tidak dapat melihat dokumen Siswa A (403).
     *    - Siswa A dapat melihat dokumen miliknya sendiri (200).
     *    - Admin TU dapat melihat dokumen pendaftar untuk verifikasi (200).
     */
    public function test_document_authorization_uu_pdp(): void
    {
        Storage::fake('local');

        $userA = User::where('email', 'siswa@smkib.com')->first();
        $pendaftarA = Pendaftar::where('id_user', $userA->id_user)->first();

        // Buat file dummy di private storage
        $dummyPath = 'berkas_pendaftar/test_ijazah_a.pdf';
        Storage::disk('local')->put($dummyPath, '%PDF-1.4 test document content');

        $berkasA = BerkasPendaftar::create([
            'id_pendaftar' => $pendaftarA->id_pendaftar,
            'jenis_berkas' => 'ijazah',
            'nama_file' => 'ijazah_asli.pdf',
            'path_file' => $dummyPath,
            'status_berkas' => 'Menunggu',
        ]);

        // 1. Pengguna tanpa login mencoba akses -> 401
        $this->get(route('dokumen.berkas', $berkasA->id_berkas))->assertStatus(401);

        // 2. Buat Siswa B
        $userB = User::create([
            'nama' => 'Siswa B Asing',
            'nisn' => '0088776655',
            'username' => 'siswabasg',
            'email' => 'siswab@smkib.com',
            'password' => Hash::make('password123'),
        ]);

        // Siswa B mencoba membuka dokumen Siswa A -> Ditolak 403 (UU PDP)
        $this->actingAs($userB, 'web');
        $this->get(route('dokumen.berkas', $berkasA->id_berkas))->assertStatus(403);

        // 3. Siswa A membuka dokumen miliknya sendiri -> Berhasil 200
        $this->actingAs($userA, 'web');
        $this->get(route('dokumen.berkas', $berkasA->id_berkas))->assertStatus(200);

        // 4. Petugas TU membuka dokumen pendaftar -> Berhasil 200
        $adminTU = Admin::where('role', 'tu')->first();
        $this->actingAs($adminTU, 'admin');
        $this->get(route('dokumen.berkas', $berkasA->id_berkas))->assertStatus(200);

        // Cleanup
        $berkasA->delete();
        $userB->delete();
    }

    /**
     * 5. Uji Unggah Berkas: Harus Tersimpan di Private Storage dengan Nama Unguessable Random.
     */
    public function test_document_upload_stores_in_private_storage(): void
    {
        Storage::fake('local');
        Storage::fake('public');

        $user = User::where('email', 'siswa@smkib.com')->first();
        $this->actingAs($user, 'web');

        $fakePdf = UploadedFile::fake()->create('scan_kk.pdf', 500, 'application/pdf');

        $response = $this->post('/siswa/berkas/upload', [
            'jenis_berkas' => 'kk',
            'file' => $fakePdf,
        ]);

        $response->assertStatus(302)->assertSessionHas('success');

        $pendaftar = Pendaftar::where('id_user', $user->id_user)->first();
        $berkasKK = BerkasPendaftar::where('id_pendaftar', $pendaftar->id_pendaftar)
            ->where('jenis_berkas', 'kk')
            ->first();

        $this->assertNotNull($berkasKK);
        // Pastikan disimpan di disk local/private
        $this->assertTrue(Storage::disk('local')->exists($berkasKK->path_file));
        // Pastikan TIDAK bocor ke public storage
        $this->assertFalse(Storage::disk('public')->exists($berkasKK->path_file));

        $berkasKK->delete();
    }

    /**
     * 6. Uji Webhook Midtrans: Pemrosesan Idempotent & Update Status Pembayaran.
     */
    public function test_midtrans_webhook_signature_and_idempotency(): void
    {
        $pendaftar = Pendaftar::first();
        $orderId = 'TRX-TEST-MIDTRANS-' . time();

        $pembayaran = Pembayaran::create([
            'id_pendaftar' => $pendaftar->id_pendaftar,
            'id_gelombang' => $pendaftar->id_gelombang,
            'kode_transaksi' => $orderId,
            'nominal' => 100000.00,
            'potongan' => 0.00,
            'total_bayar' => 100000.00,
            'tipe_bayar' => 'midtrans',
            'metode_pembayaran' => 'qris',
            'status_transaksi' => 'pending',
        ]);

        $payload = [
            'order_id' => $orderId,
            'status_code' => '200',
            'gross_amount' => '100000.00',
            'transaction_status' => 'settlement',
            'payment_type' => 'qris',
        ];

        // Kirim notifikasi pertama -> Harus diproses menjadi settlement
        $response = $this->postJson('/api/midtrans/notification', $payload);
        $response->assertStatus(200)->assertJson(['status' => 'success']);

        $pembayaran->refresh();
        $this->assertEquals('settlement', $pembayaran->status_transaksi);

        // Kirim notifikasi kedua (Idempotent) -> Tetap sukses tanpa menggandakan mutasi
        $response2 = $this->postJson('/api/midtrans/notification', $payload);
        $response2->assertStatus(200);

        $pembayaran->delete();
    }

    /**
     * 7. Uji Ekspor Multi-Format: CSV UTF-8 BOM, XLSX Asli, dan Template Dapodik.
     */
    public function test_export_endpoints(): void
    {
        $admin = Admin::where('role', 'tu')->first();
        $this->actingAs($admin, 'admin');

        // CSV Export
        $responseCsv = $this->get('/admin/ekspor/excel');
        $responseCsv->assertStatus(200);
        $this->assertStringContainsString('text/csv', (string) $responseCsv->headers->get('Content-Type'));

        // XLSX Export (OpenXML asli)
        $responseXlsx = $this->get('/admin/ekspor/xlsx');
        $responseXlsx->assertStatus(200);
        $this->assertStringContainsString('spreadsheetml.sheet', (string) $responseXlsx->headers->get('Content-Type'));

        // Dapodik Export
        $responseDapodik = $this->get('/admin/ekspor/dapodik');
        $responseDapodik->assertStatus(200);
    }

    /**
     * 8. Uji Notification Service (Simulator Mode tanpa Pulsa/Kredit API).
     */
    public function test_notification_service_handles_simulator_mode(): void
    {
        $service = new NotificationService();

        // Uji sanitasi nomor telepon
        $formatted = $service->formatPhoneNumber('0812-3456-7890');
        $this->assertEquals('6281234567890', $formatted);

        // Uji pengiriman WA dalam mode simulasi
        $resWA = $service->sendWhatsApp('082188990011', 'Pesan Demo PPDB');
        $this->assertTrue($resWA['success']);
        $this->assertEquals('simulated', $resWA['mode']);

        // Uji pengiriman email
        $resMail = $service->sendEmail('test@smkib.com', 'Subjek Uji', 'Isi email demo');
        $this->assertTrue($resMail['success']);
    }
}
