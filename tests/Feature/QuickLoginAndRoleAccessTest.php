<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Admin;
use App\Models\User;
use App\Models\Gelombang;
use App\Models\PengaturanSekolah;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Config;

class QuickLoginAndRoleAccessTest extends TestCase
{
    use DatabaseTransactions;

    protected Admin $tu;
    protected Admin $kepsek;
    protected User $siswa;

    protected function setUp(): void
    {
        parent::setUp();

        $this->tu = Admin::where('role', 'tu')->first()
            ?: Admin::factory()->create(['role' => 'tu']);

        $this->kepsek = Admin::where('role', 'kepala_sekolah')->first()
            ?: Admin::factory()->create(['role' => 'kepala_sekolah']);

        $this->siswa = User::first()
            ?: User::factory()->create();
    }

    /**
     * Uji 1: Quick Login harus ditolak mutlak (404 Not Found) di lingkungan non-lokal/production.
     */
    public function test_quick_login_ditolak_404_jika_bukan_lingkungan_lokal(): void
    {
        // Simulasikan lingkungan produksi di Vercel/Hosting
        $this->app['env'] = 'production';
        Config::set('app.env', 'production');
        Config::set('app.debug', false);

        $response = $this->withoutMiddleware()
            ->post(route('auth.quick_login'), [
                'role' => 'tu',
            ]);

        $response->assertStatus(404);
    }

    /**
     * Uji 2: Quick Login tetap ditolak 404 meskipun APP_DEBUG=true jika bukan lingkungan lokal.
     */
    public function test_quick_login_tetap_ditolak_404_jika_app_debug_true_di_nonlokal(): void
    {
        $this->app['env'] = 'production';
        Config::set('app.env', 'production');
        Config::set('app.debug', true);

        $response = $this->withoutMiddleware()
            ->post(route('auth.quick_login'), [
                'role' => 'kepala_sekolah',
            ]);

        $response->assertStatus(404);
    }

    /**
     * Uji 3: Petugas TU dilarang mengubah biaya pendaftaran (HTTP 403).
     */
    public function test_petugas_tu_dilarang_mengubah_biaya_pendaftaran(): void
    {
        $gelombang = Gelombang::first() ?: Gelombang::factory()->create();

        $response = $this->actingAs($this->tu, 'admin')
            ->post(route('admin.pengaturan.biaya_pendaftaran'), [
                'gelombang' => [
                    $gelombang->id_gelombang => ['biaya_pendaftaran' => 250000],
                ],
            ]);

        $response->assertStatus(403);
    }

    /**
     * Uji 4: Kepala Sekolah berwenang mengubah biaya pendaftaran.
     */
    public function test_kepala_sekolah_berwenang_mengubah_biaya_pendaftaran(): void
    {
        $gelombang = Gelombang::first() ?: Gelombang::factory()->create();

        $response = $this->actingAs($this->kepsek, 'admin')
            ->post(route('admin.pengaturan.biaya_pendaftaran'), [
                'gelombang' => [
                    $gelombang->id_gelombang => ['biaya_pendaftaran' => 300000],
                ],
            ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('gelombang', [
            'id_gelombang' => $gelombang->id_gelombang,
            'biaya_pendaftaran' => 300000,
        ]);
    }

    /**
     * Uji 5: Petugas TU dilarang mengubah biaya daftar ulang (HTTP 403).
     */
    public function test_petugas_tu_dilarang_mengubah_biaya_daftar_ulang(): void
    {
        $response = $this->actingAs($this->tu, 'admin')
            ->post(route('admin.pengaturan.biaya_daftar_ulang'), [
                'biaya_daftar_ulang' => 1750000,
            ]);

        $response->assertStatus(403);
    }

    /**
     * Uji 6: Kepala Sekolah berwenang mengubah biaya daftar ulang.
     */
    public function test_kepala_sekolah_berwenang_mengubah_biaya_daftar_ulang(): void
    {
        $response = $this->actingAs($this->kepsek, 'admin')
            ->post(route('admin.pengaturan.biaya_daftar_ulang'), [
                'biaya_daftar_ulang' => 1800000,
                'keterangan_daftar_ulang' => 'Rincian seragam dan atribut lengkap.',
            ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('pengaturan_sekolah', [
            'biaya_daftar_ulang' => 1800000,
        ]);
    }

    /**
     * Uji 7: Petugas TU dilarang mengunduh file backup database SQL (HTTP 403).
     */
    public function test_petugas_tu_dilarang_mengunduh_backup_sql(): void
    {
        $response = $this->actingAs($this->tu, 'admin')
            ->get(route('admin.backup.download', 'backup_dummy.sql'));

        $response->assertStatus(403);
    }

    /**
     * Uji 8: Petugas TU dilarang menambahkan akun staf baru (HTTP 403).
     */
    public function test_petugas_tu_dilarang_menambah_akun_staf(): void
    {
        $response = $this->actingAs($this->tu, 'admin')
            ->post(route('admin.staf.store'), [
                'nama_admin' => 'Staf TU Baru',
                'username' => 'staf_baru_' . time(),
                'email' => 'staf_baru_' . time() . '@smkib.test',
                'password' => 'password123',
                'password_confirmation' => 'password123',
                'role' => 'tu',
            ]);

        $response->assertStatus(403);
    }

    /**
     * Uji 9: Petugas TU dilarang menghapus akun staf (HTTP 403).
     */
    public function test_petugas_tu_dilarang_menghapus_akun_staf(): void
    {
        $otherTu = Admin::where('role', 'tu')->where('id_admin', '!=', $this->tu->id_admin)->first()
            ?: Admin::factory()->create(['role' => 'tu']);

        $response = $this->actingAs($this->tu, 'admin')
            ->delete(route('admin.staf.destroy', $otherTu->id_admin));

        $response->assertStatus(403);
    }

    /**
     * Uji 10: Petugas TU dan Kepala Sekolah keduanya dapat mengakses halaman Pengaturan Sekolah (Read-Only bagi TU).
     */
    public function test_tu_dan_kepsek_dapat_melihat_halaman_pengaturan_sekolah(): void
    {
        $resTu = $this->actingAs($this->tu, 'admin')
            ->get(route('admin.pengaturan'));
        $resTu->assertStatus(200);

        $resKepsek = $this->actingAs($this->kepsek, 'admin')
            ->get(route('admin.pengaturan'));
        $resKepsek->assertStatus(200);
    }
}
