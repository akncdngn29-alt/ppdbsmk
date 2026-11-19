<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Admin;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class ManajemenStafTest extends TestCase
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

    public function test_staf_admin_dapat_melihat_daftar_akun_staf(): void
    {
        $response = $this->actingAs($this->tu, 'admin')
            ->get(route('admin.staf.index'));

        $response->assertStatus(200);
        $response->assertSee('Akun Staf & Hak Akses');
        $response->assertSee('Total Akun Staf');
    }

    public function test_calon_siswa_dilarang_mengakses_manajemen_staf(): void
    {
        $response = $this->actingAs($this->siswa, 'web')
            ->get(route('admin.staf.index'));

        $response->assertRedirect(route('admin.login'));
    }

    public function test_tambah_akun_staf_tu_baru_berhasil(): void
    {
        $random = rand(1000, 9999);
        $response = $this->actingAs($this->kepsek, 'admin')
            ->post(route('admin.staf.store'), [
                'nama_admin' => "Staff Baru {$random}",
                'username' => "staf_{$random}",
                'email' => "staf_{$random}@smkib.com",
                'role' => 'tu',
                'password' => 'password123',
                'password_confirmation' => 'password123',
            ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('admin', [
            'username' => "staf_{$random}",
            'role' => 'tu',
        ]);
        $this->assertDatabaseHas('log_aktivitas', [
            'aksi' => 'TAMBAH_STAF',
        ]);
    }

    public function test_petugas_tu_tidak_dapat_membuat_akun_berperan_kepala_sekolah(): void
    {
        $random = rand(1000, 9999);
        $response = $this->actingAs($this->tu, 'admin')
            ->post(route('admin.staf.store'), [
                'nama_admin' => "Penyusup Kepsek {$random}",
                'username' => "palsu_{$random}",
                'email' => "palsu_{$random}@smkib.com",
                'role' => 'kepala_sekolah',
                'password' => 'password123',
                'password_confirmation' => 'password123',
            ]);

        $response->assertSessionHas('error');
        $this->assertDatabaseMissing('admin', [
            'username' => "palsu_{$random}",
        ]);
    }

    public function test_perbarui_data_staf_berhasil(): void
    {
        $stafBaru = Admin::create([
            'nama_admin' => 'Staf Akan Diupdate',
            'username' => 'staf_update_' . rand(100, 999),
            'email' => 'update_' . rand(100, 999) . '@smkib.com',
            'password' => bcrypt('password123'),
            'role' => 'tu',
        ]);

        $response = $this->actingAs($this->kepsek, 'admin')
            ->put(route('admin.staf.update', $stafBaru->id_admin), [
                'nama_admin' => 'Staf Sudah Diupdate',
                'username' => $stafBaru->username,
                'email' => $stafBaru->email,
                'role' => 'tu',
            ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('admin', [
            'id_admin' => $stafBaru->id_admin,
            'nama_admin' => 'Staf Sudah Diupdate',
        ]);
    }

    public function test_admin_tidak_dapat_menghapus_akunnya_sendiri(): void
    {
        $response = $this->actingAs($this->tu, 'admin')
            ->delete(route('admin.staf.destroy', $this->tu->id_admin));

        $response->assertSessionHas('error');
        $this->assertDatabaseHas('admin', [
            'id_admin' => $this->tu->id_admin,
        ]);
    }
}
