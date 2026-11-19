<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Admin;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class BantuanSiswaTest extends TestCase
{
    use DatabaseTransactions;

    protected User $siswa;
    protected Admin $tu;

    protected function setUp(): void
    {
        parent::setUp();

        $this->siswa = User::first()
            ?: User::factory()->create();

        $this->tu = Admin::where('role', 'tu')->first()
            ?: Admin::factory()->create(['role' => 'tu']);
    }

    public function test_calon_siswa_dapat_mengakses_halaman_bantuan_dan_faq(): void
    {
        $response = $this->actingAs($this->siswa, 'web')
            ->get(route('siswa.bantuan'));

        $response->assertStatus(200);
        $response->assertSee('Pusat Bantuan');
        $response->assertSee('Tahapan Alur PPDB');
        $response->assertSee('Pertanyaan yang Sering Diajukan');
        $response->assertSee('Lokasi Kampus');
        $response->assertSee('Jam Pelayanan');
    }

    public function test_tamu_diarahkan_ke_login_saat_mengakses_halaman_bantuan_siswa(): void
    {
        $response = $this->get(route('siswa.bantuan'));
        $response->assertRedirect(route('login'));
    }
}
