<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Admin;
use App\Models\User;
use App\Models\Pendaftar;
use App\Models\Jurusan;
use App\Models\Gelombang;
use App\Models\Pembayaran;
use App\Models\LogAktivitas;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class RingkasanKeuanganKepsekTest extends TestCase
{
    use DatabaseTransactions;

    protected Admin $kepsek;
    protected Admin $tu;
    protected User $siswa;

    protected function setUp(): void
    {
        parent::setUp();

        $this->kepsek = Admin::where('role', 'kepala_sekolah')->first()
            ?: Admin::factory()->create(['role' => 'kepala_sekolah', 'username' => 'kepsek_test', 'nama_admin' => 'Kepsek Test']);

        $this->tu = Admin::where('role', 'tu')->first()
            ?: Admin::factory()->create(['role' => 'tu', 'username' => 'tu_test', 'nama_admin' => 'TU Test']);

        $this->siswa = User::first()
            ?: User::factory()->create();
    }

    public function test_kepala_sekolah_dapat_mengakses_halaman_ringkasan_keuangan(): void
    {
        $response = $this->actingAs($this->kepsek, 'admin')
            ->get(route('kepsek.keuangan'));

        $response->assertStatus(200);
        $response->assertSee('Ringkasan Keuangan');
        $response->assertSee('Rekapitulasi Finansial PPDB');
        $response->assertSee('Total Masuk (Lunas)');
        $response->assertSee('Tunai (Loket TU)');
        $response->assertSee('Online (Midtrans)');
    }

    public function test_petugas_tu_tidak_dapat_mengakses_halaman_keuangan_kepsek(): void
    {
        $response = $this->actingAs($this->tu, 'admin')
            ->get(route('kepsek.keuangan'));

        $response->assertStatus(403);
    }

    public function test_calon_siswa_tidak_dapat_mengakses_halaman_keuangan_kepsek(): void
    {
        $response = $this->actingAs($this->siswa, 'web')
            ->get(route('kepsek.keuangan'));

        $response->assertRedirect(route('admin.login'));
    }

    public function test_tamu_diarahkan_ke_login_saat_mengakses_halaman_keuangan(): void
    {
        $response = $this->get(route('kepsek.keuangan'));
        $response->assertRedirect(route('admin.login'));
    }

    public function test_pencarian_dan_filter_transaksi_keuangan_berfungsi(): void
    {
        $response = $this->actingAs($this->kepsek, 'admin')
            ->get(route('kepsek.keuangan', [
                'tipe_bayar' => 'tunai',
                'status' => 'settlement',
            ]));

        $response->assertStatus(200);
        $response->assertSee('Rincian Buku Kas Penerimaan');
    }

    public function test_akses_keuangan_tercatat_di_log_aktivitas(): void
    {
        $this->actingAs($this->kepsek, 'admin')
            ->get(route('kepsek.keuangan'));

        $this->assertDatabaseHas('log_aktivitas', [
            'id_admin' => $this->kepsek->id_admin,
            'aksi' => 'PENGAWASAN',
            'role' => 'kepala_sekolah',
        ]);
    }
}
