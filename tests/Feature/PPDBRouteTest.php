<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Admin;
use App\Models\Pendaftar;
use Illuminate\Support\Facades\Hash;

class PPDBRouteTest extends TestCase
{
    public function test_public_routes(): void
    {
        $this->get('/')->assertStatus(200);
        $this->get('/login')->assertStatus(200);
        $this->get('/daftar')->assertStatus(200);
        $this->get('/admin/login')->assertStatus(200);
        $this->get('/lupa-password')->assertStatus(200);
    }

    public function test_strict_role_separation_login(): void
    {
        // Admin logging in via /login (student portal) -> REJECTED (session has error)
        $response = $this->post('/login', [
            'login' => 'admin@smkib.com',
            'password' => 'admin123',
        ]);
        $response->assertSessionHasErrors('login');
        $this->assertGuest('web');
        $this->assertGuest('admin');

        // Student logging in via /admin/login (admin portal) -> REJECTED (session has error)
        $response = $this->post('/admin/login', [
            'login' => 'siswa@smkib.com',
            'password' => 'siswa123',
        ]);
        $response->assertSessionHasErrors('login');
        $this->assertGuest('web');
        $this->assertGuest('admin');
    }

    public function test_calon_siswa_completed_routes(): void
    {
        $user = User::where('email', 'siswa@smkib.com')->first();
        $this->assertNotNull($user, 'Siswa user should exist in database.');

        $this->actingAs($user, 'web');

        // Dashboard accessible
        $this->get('/siswa/dashboard')->assertStatus(200);

        // Locked formulir redirects to dashboard
        $this->get('/siswa/formulir')->assertStatus(302)->assertRedirect(route('siswa.dashboard'));

        // Document & Payment pages accessible
        $this->get('/siswa/berkas')->assertStatus(200);
        $this->get('/siswa/pembayaran')->assertStatus(200);

        // Printable outputs accessible
        $this->get('/siswa/cetak/formulir')->assertStatus(200);
        $this->get('/siswa/cetak/kartu-ujian')->assertStatus(200);
        $this->get('/siswa/cetak/kwitansi')->assertStatus(200);
        $this->get('/siswa/cetak/skl')->assertStatus(200);

        // Daftar Ulang & Pengaturan Akun
        $this->get('/siswa/daftar-ulang')->assertStatus(200);
        $this->get('/siswa/pengaturan')->assertStatus(200);
    }

    public function test_calon_siswa_new_registration_can_access_formulir(): void
    {
        // Test a newly registered user who hasn't locked the form yet
        $newUser = User::create([
            'nama' => 'Siswa Baru Belum Kunci',
            'nisn' => '0099887766',
            'username' => 'siswabaru',
            'email' => 'siswabaru@test.com',
            'password' => Hash::make('password123'),
        ]);

        $this->actingAs($newUser, 'web');
        $this->get('/siswa/formulir')->assertStatus(200);

        // Clean up
        $newUser->delete();
    }

    public function test_admin_tu_authenticated_routes(): void
    {
        $admin = Admin::where('role', 'tu')->first();
        $this->assertNotNull($admin, 'Admin TU should exist in database.');

        $this->actingAs($admin, 'admin');

        $this->get('/admin/dashboard')->assertStatus(200);
        $this->get('/admin/pendaftar')->assertStatus(200);
        
        $pendaftar = Pendaftar::first();
        if ($pendaftar) {
            $this->get('/admin/pendaftar/' . $pendaftar->id_pendaftar)->assertStatus(200);
        }

        $this->get('/admin/seleksi')->assertStatus(200);
        $this->get('/admin/master/jurusan')->assertStatus(200);
        $this->get('/admin/master/gelombang')->assertStatus(200);
        $this->get('/admin/master/voucher')->assertStatus(200);
        $this->get('/admin/pengumuman')->assertStatus(200);
        $this->get('/admin/pengaturan')->assertStatus(200);
        $this->get('/admin/laporan/rekap-pdf')->assertStatus(200);
    }

    public function test_kepala_sekolah_authenticated_routes(): void
    {
        $kepsek = Admin::where('role', 'kepala_sekolah')->first();
        $this->assertNotNull($kepsek, 'Kepala Sekolah should exist in database.');

        $this->actingAs($kepsek, 'admin');

        $this->get('/kepsek/dashboard')->assertStatus(200);
        $this->get('/kepsek/kelulusan')->assertStatus(200);
        $this->get('/kepsek/laporan')->assertStatus(200);
        $this->get('/kepsek/audit-log')->assertStatus(200);
    }
}
