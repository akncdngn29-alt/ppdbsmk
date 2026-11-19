<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Admin;
use App\Models\Pendaftar;
use App\Models\Gelombang;
use App\Models\Pembayaran;
use App\Models\PengaturanSekolah;
use App\Models\BerkasPendaftar;
use Illuminate\Support\Facades\Config;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class ModifikasiLoginPembayaranBerkasTest extends TestCase
{
    protected Admin $adminTU;
    protected Admin $kepsek;
    protected User $siswaUser;
    protected Pendaftar $pendaftar;
    protected Gelombang $gelombang;

    protected function setUp(): void
    {
        parent::setUp();

        $this->adminTU = Admin::where('role', 'tu')->first() ?: Admin::factory()->create(['role' => 'tu']);
        $this->kepsek = Admin::where('role', 'kepala_sekolah')->first() ?: Admin::factory()->create(['role' => 'kepala_sekolah']);
        $this->siswaUser = User::first() ?: User::factory()->create();
        $this->pendaftar = $this->siswaUser->pendaftar ?: Pendaftar::first();
        $this->gelombang = Gelombang::where('status_aktif', true)->first() ?: Gelombang::first();
    }

    /**
     * 1. QUICK LOGIN VERIFICATION
     */
    public function test_halaman_login_menampilkan_shortcut_quick_login(): void
    {
        // 1. Login Calon Siswa hanya menampilkan pintasan Calon Siswa (tanpa Admin TU, Kepala Sekolah, Loket TU)
        $responseSiswa = $this->get(route('login'));
        $responseSiswa->assertStatus(200);
        $responseSiswa->assertSee('Akses Masuk Cepat');
        $responseSiswa->assertSee('Calon Siswa');
        $responseSiswa->assertDontSee('Loket TU');

        // 2. Login Staf & Pimpinan hanya menampilkan Admin TU dan Kepala Sekolah (tanpa Loket TU dan Calon Siswa)
        $responseAdmin = $this->get(route('admin.login'));
        $responseAdmin->assertStatus(200);
        $responseAdmin->assertSee('Akses Masuk Cepat');
        $responseAdmin->assertSee('Admin TU');
        $responseAdmin->assertSee('Kepala Sekolah');
        $responseAdmin->assertDontSee('Loket TU');
    }

    public function test_quick_login_berhasil_untuk_tu_dan_kepsek(): void
    {
        // Test TU
        $responseTU = $this->post(route('auth.quick_login'), [
            'role' => 'tu',
        ]);
        $responseTU->assertRedirect(route('admin.dashboard'));
        $this->assertAuthenticatedAs($this->adminTU, 'admin');

        // Test Kepsek
        $responseKepsek = $this->post(route('auth.quick_login'), [
            'role' => 'kepala_sekolah',
        ]);
        $responseKepsek->assertRedirect(route('kepsek.dashboard'));
        $this->assertAuthenticatedAs($this->kepsek, 'admin');
    }

    public function test_quick_login_berhasil_untuk_calon_siswa(): void
    {
        $response = $this->post(route('auth.quick_login'), [
            'role' => 'siswa',
            'user_id' => $this->siswaUser->id_user,
        ]);
        $response->assertRedirect(route('siswa.dashboard'));
        $this->assertAuthenticatedAs($this->siswaUser, 'web');
    }

    public function test_quick_login_ditolak_jika_mode_debug_nonaktif_atau_role_tidak_valid(): void
    {
        // Invalid role
        $responseInvalid = $this->post(route('auth.quick_login'), [
            'role' => 'hacker',
        ]);
        $responseInvalid->assertSessionHasErrors(['role']);

        // When debug is false in production
        Config::set('app.debug', false);
        Config::set('app.env', 'production');

        $responseProd = $this->post(route('auth.quick_login'), [
            'role' => 'tu',
        ]);
        $responseProd->assertSessionHasErrors(['login']);

        // Reset config
        Config::set('app.debug', true);
        Config::set('app.env', 'local');
    }

    /**
     * 2. PAYMENT GATEWAY TAB PADA PENGATURAN SEKOLAH
     */
    public function test_admin_tu_dapat_melihat_tab_payment_gateway(): void
    {
        $response = $this->actingAs($this->adminTU, 'admin')->get(route('admin.pengaturan'));
        $response->assertStatus(200);
        $response->assertSee('Payment Gateway');
        $response->assertSee('Pengaturan Payment Gateway (Midtrans)');
        $response->assertSee('Uji Koneksi Midtrans');
        $response->assertSee('WEBHOOK');
    }

    public function test_admin_tu_dapat_menyimpan_pengaturan_payment_gateway(): void
    {
        $response = $this->actingAs($this->adminTU, 'admin')->post(route('admin.pengaturan.payment_gateway'), [
            'midtrans_merchant_id' => 'G123456789',
            'midtrans_server_key' => 'SB-Mid-server-TEST123456789',
            'midtrans_client_key' => 'SB-Mid-client-TEST123456789',
            'midtrans_environment' => 'sandbox',
            'midtrans_is_active' => '1',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('pengaturan_sekolah', [
            'midtrans_merchant_id' => 'G123456789',
            'midtrans_client_key' => 'SB-Mid-client-TEST123456789',
            'midtrans_is_active' => 1,
        ]);
    }

    public function test_admin_tu_uji_koneksi_midtrans(): void
    {
        \Illuminate\Support\Facades\Http::fake([
            'https://api.sandbox.midtrans.com/*' => \Illuminate\Support\Facades\Http::response(['status_code' => '404', 'status_message' => 'Transaction doesn\'t exist.'], 404),
        ]);

        $response = $this->actingAs($this->adminTU, 'admin')->postJson(route('admin.pengaturan.test_midtrans'), [
            'server_key' => 'SB-Mid-server-TEST123456789',
            'environment' => 'sandbox',
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
        ]);
    }

    /**
     * 3. PEMILIHAN METODE & BAYAR TUNAI LOKET TU OLEH SISWA
     */
    public function test_siswa_dapat_membuat_tagihan_tunai_loket_tu(): void
    {
        // Reset status to Pending
        $this->pendaftar->update(['status_pembayaran' => 'Pending']);
        Pembayaran::where('id_pendaftar', $this->pendaftar->id_pendaftar)->delete();

        $response = $this->actingAs($this->siswaUser, 'web')->post(route('siswa.pembayaran.tunai'));
        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('pembayaran', [
            'id_pendaftar' => $this->pendaftar->id_pendaftar,
            'tipe_bayar' => 'tunai_tu',
            'status_transaksi' => 'pending',
        ]);
    }

    /**
     * 4. VERIFIKASI PEMBAYARAN TUNAI OLEH PETUGAS TU
     */
    public function test_petugas_tu_dapat_melihat_halaman_verifikasi_tunai(): void
    {
        $response = $this->actingAs($this->adminTU, 'admin')->get(route('admin.pembayaran.tunai'));
        $response->assertStatus(200);
        $response->assertSee('Verifikasi Pembayaran Tunai Loket TU');
        $response->assertSee('Cari Siswa');
        $response->assertSee('Cari berdasarkan Nama Siswa');
    }

    public function test_petugas_tu_berhasil_mengonfirmasi_pembayaran_tunai(): void
    {
        // Ensure pending cash payment exists
        $pembayaran = Pembayaran::updateOrCreate(
            ['id_pendaftar' => $this->pendaftar->id_pendaftar],
            [
                'kode_transaksi' => 'TRX-TEST-' . time(),
                'nominal' => 150000,
                'total_bayar' => 150000,
                'metode_pembayaran' => 'tunai_tu',
                'tipe_bayar' => 'tunai_tu',
                'status_transaksi' => 'pending',
            ]
        );
        $this->pendaftar->update(['status_pembayaran' => 'Pending']);

        $response = $this->actingAs($this->adminTU, 'admin')->post(route('admin.pembayaran.tunai.konfirmasi', $this->pendaftar->id_pendaftar), [
            'catatan' => 'Diterima tunai di loket TU SMK IB',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $pembayaran->refresh();
        $this->assertEquals('settlement', $pembayaran->status_transaksi);
        $this->assertEquals($this->adminTU->id_admin, $pembayaran->id_admin_verifikator);
        $this->assertNotNull($pembayaran->waktu_konfirmasi_tu);

        $this->pendaftar->refresh();
        $this->assertEquals('Paid', $this->pendaftar->status_pembayaran);
    }

    /**
     * 5. INDEPENDENT FILE UPLOAD & PER-DOCUMENT REVISION
     */
    public function test_verifikasi_berkas_independen_perlu_perbaikan_dan_reupload(): void
    {
        Storage::fake('local');

        // Setup berkas KK and Ijazah
        $berkasKK = BerkasPendaftar::updateOrCreate(
            [
                'id_pendaftar' => $this->pendaftar->id_pendaftar,
                'jenis_berkas' => 'kk',
            ],
            [
                'nama_file' => 'kk_sample.pdf',
                'path_file' => 'berkas/kk_sample.pdf',
                'status_berkas' => 'Valid',
                'catatan' => null,
            ]
        );

        $berkasIjazah = BerkasPendaftar::updateOrCreate(
            [
                'id_pendaftar' => $this->pendaftar->id_pendaftar,
                'jenis_berkas' => 'ijazah',
            ],
            [
                'nama_file' => 'ijazah_sample.pdf',
                'path_file' => 'berkas/ijazah_sample.pdf',
                'status_berkas' => 'Menunggu',
                'catatan' => null,
            ]
        );

        // 1. Admin TU rejects Ijazah with reason
        $responseTU = $this->actingAs($this->adminTU, 'admin')->post(route('admin.berkas.verifikasi', $berkasIjazah->id_berkas), [
            'status_berkas' => 'Perlu Perbaikan',
            'catatan' => 'Scan ijazah buram dan nomor seri terpotong, mohon unggah ulang.',
        ]);

        $responseTU->assertRedirect();
        $berkasIjazah->refresh();
        $this->assertEquals('Perlu Perbaikan', $berkasIjazah->status_berkas);
        $this->assertStringContainsString('Scan ijazah buram', $berkasIjazah->catatan);

        // KK should remain Valid
        $berkasKK->refresh();
        $this->assertEquals('Valid', $berkasKK->status_berkas);

        // 2. Siswa views berkas page and sees the rejection reason
        $responseSiswa = $this->actingAs($this->siswaUser, 'web')->get(route('siswa.berkas'));
        $responseSiswa->assertStatus(200);
        $responseSiswa->assertSee('Perlu Perbaikan');
        $responseSiswa->assertSee('Scan ijazah buram dan nomor seri terpotong');

        // 3. Siswa re-uploads only Ijazah
        $fakeFile = UploadedFile::fake()->create('ijazah_baru.pdf', 500, 'application/pdf');
        $responseUpload = $this->actingAs($this->siswaUser, 'web')->post(route('siswa.berkas.upload'), [
            'jenis_berkas' => 'ijazah',
            'file' => $fakeFile,
        ]);
        $responseUpload->assertRedirect();

        // 4. Verify Ijazah is back to Menunggu and KK is still Valid
        $berkasIjazah->refresh();
        $berkasKK->refresh();

        $this->assertEquals('Menunggu', $berkasIjazah->status_berkas);
        $this->assertEquals('Valid', $berkasKK->status_berkas);
    }
}
