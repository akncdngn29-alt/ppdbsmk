<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Admin;
use App\Models\Pendaftar;
use App\Models\Jurusan;
use App\Models\Gelombang;
use App\Models\Pembayaran;
use App\Models\NilaiSeleksi;
use App\Models\LogAktivitas;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class FormulirUnlockTest extends TestCase
{
    private function createTestEnvironment(): array
    {
        $jurusan1 = Jurusan::firstOrCreate(['kode_jurusan' => 'TKJ'], [
            'nama_jurusan' => 'Teknik Komputer dan Jaringan',
            'kuota' => 72,
        ]);

        $jurusan2 = Jurusan::firstOrCreate(['kode_jurusan' => 'FAR'], [
            'nama_jurusan' => 'Farmasi Klinis dan Komunitas',
            'kuota' => 72,
        ]);

        $gelombang = Gelombang::firstOrCreate(['nama_gelombang' => 'Gelombang 1 (Early Bird)'], [
            'tahun_ajaran' => '2026/2027',
            'tgl_buka' => now()->subDays(10)->format('Y-m-d'),
            'tgl_tutup' => now()->addDays(30)->format('Y-m-d'),
            'biaya_pendaftaran' => 100000.00,
            'status_aktif' => true,
        ]);

        $adminTU = Admin::firstOrCreate(['username' => 'admin_tu_test'], [
            'nama_admin' => 'Admin TU Penguji',
            'email' => 'admin_tu_test@smkib.com',
            'password' => Hash::make('password123'),
            'role' => 'tu',
        ]);

        $kepsek = Admin::firstOrCreate(['username' => 'kepsek_test'], [
            'nama_admin' => 'Kepala Sekolah Penguji',
            'email' => 'kepsek_test@smkib.com',
            'password' => Hash::make('password123'),
            'role' => 'kepala_sekolah',
        ]);

        $uniqueNisn = '99' . str_pad((string) mt_rand(1, 99999999), 8, '0', STR_PAD_LEFT);
        $user = User::create([
            'nama' => 'Siswa Uji Unlock',
            'nisn' => $uniqueNisn,
            'username' => 'siswa_unlock_' . uniqid(),
            'email' => 'siswa_unlock_' . uniqid() . '@test.com',
            'password' => Hash::make('password123'),
        ]);

        $noPend = 'PPDB-2026-TKJ-' . uniqid();
        $pendaftar = Pendaftar::create([
            'id_user' => $user->id_user,
            'id_gelombang' => $gelombang->id_gelombang,
            'id_jurusan_1' => $jurusan1->id_jurusan,
            'id_jurusan_2' => $jurusan2->id_jurusan,
            'no_pendaftaran' => $noPend,
            'nisn' => $uniqueNisn,
            'nama' => 'Siswa Uji Unlock',
            'tempat_lahir' => 'Kota Metro',
            'tanggal_lahir' => '2010-01-01',
            'jenis_kelamin' => 'Laki-laki',
            'agama' => 'Islam',
            'anak_ke' => 1,
            'jml_saudara' => 1,
            'hp_siswa' => '081234567890',
            'almt_siswa' => 'Jl. Uji No. 123 Metro',
            'berat_badan' => '50',
            'tinggi_badan' => '160',
            'golongan_darah' => 'O',
            'asal_sekolah' => 'SMP Negeri 1 Metro',
            'nama_ayah' => 'Ayah Siswa Uji',
            'nama_ibu' => 'Ibu Siswa Uji',
            'almt_ortu' => 'Jl. Uji No. 123 Metro',
            'hp_ortu' => '081234567891',
            'kerja_ayah' => 'Wiraswasta',
            'kerja_ibu' => 'Ibu Rumah Tangga',
            'penghasilan_ortu' => 'Rp 3.000.000 - Rp 5.000.000',
            'tanggungan_anak' => 2,
            'jalur_pendaftaran' => 'Reguler',
            'ukuran_seragam' => 'L',
            'ukuran_wearpack' => 'L',
            'status_pembayaran' => 'Paid',
            'status_verifikasi' => 'Menunggu',
            'status_kelulusan' => 'Diproses',
            'status_daftar_ulang' => 'Belum',
            'is_locked' => true,
            'tgl_daftar' => now(),
        ]);

        return compact('adminTU', 'kepsek', 'user', 'pendaftar', 'jurusan1', 'jurusan2', 'gelombang');
    }

    /**
     * 1. Admin TU dapat membuka kunci formulir yang terkunci.
     */
    public function test_01_admin_tu_can_unlock_formulir(): void
    {
        $env = $this->createTestEnvironment();
        $adminTU = $env['adminTU'];
        $pendaftar = $env['pendaftar'];

        $this->assertTrue($pendaftar->is_locked, 'Formulir awalnya harus dalam status terkunci.');

        $this->actingAs($adminTU, 'admin');
        $response = $this->post(route('admin.pendaftar.unlock', $pendaftar->id_pendaftar));

        $response->assertStatus(302);
        $response->assertSessionHas('success');

        $pendaftar->refresh();
        $this->assertFalse($pendaftar->is_locked, 'Formulir harus berhasil dibuka kunci (is_locked = false).');
    }

    /**
     * 2. Kepala Sekolah tidak dapat membuka kunci formulir (Akses ditolak 403).
     */
    public function test_02_kepala_sekolah_cannot_unlock_formulir(): void
    {
        $env = $this->createTestEnvironment();
        $kepsek = $env['kepsek'];
        $pendaftar = $env['pendaftar'];

        $this->actingAs($kepsek, 'admin');
        $response = $this->post(route('admin.pendaftar.unlock', $pendaftar->id_pendaftar));

        $response->assertStatus(403);

        $pendaftar->refresh();
        $this->assertTrue($pendaftar->is_locked, 'Formulir harus tetap terkunci saat diakses oleh Kepala Sekolah.');
    }

    /**
     * 3. Calon Siswa tidak dapat membuka kunci formulir sendiri.
     */
    public function test_03_calon_siswa_cannot_unlock_formulir_themselves(): void
    {
        $env = $this->createTestEnvironment();
        $user = $env['user'];
        $pendaftar = $env['pendaftar'];

        $this->actingAs($user, 'web');
        $response = $this->post(route('admin.pendaftar.unlock', $pendaftar->id_pendaftar));

        // Karena route dilindungi middleware admin.role:tu, siswa diarahkan ke admin login
        $response->assertStatus(302);
        $response->assertRedirect(route('admin.login'));

        $pendaftar->refresh();
        $this->assertTrue($pendaftar->is_locked, 'Formulir harus tetap terkunci saat siswa mencoba membuka kunci sendiri.');
    }

    /**
     * 4. Pengguna yang belum login ditolak (redirect ke admin login).
     */
    public function test_04_unauthenticated_guest_is_rejected(): void
    {
        $env = $this->createTestEnvironment();
        $pendaftar = $env['pendaftar'];

        $response = $this->post(route('admin.pendaftar.unlock', $pendaftar->id_pendaftar));

        $response->assertStatus(302);
        $response->assertRedirect(route('admin.login'));

        $pendaftar->refresh();
        $this->assertTrue($pendaftar->is_locked, 'Formulir tetap terkunci jika diakses pengunjung tanpa autentikasi.');
    }

    /**
     * 5. Formulir yang dibuka kembali dapat diedit dan disimpan oleh calon siswa.
     */
    public function test_05_unlocked_formulir_can_be_edited_by_student(): void
    {
        $env = $this->createTestEnvironment();
        $adminTU = $env['adminTU'];
        $user = $env['user'];
        $pendaftar = $env['pendaftar'];
        $jurusan1 = $env['jurusan1'];
        $jurusan2 = $env['jurusan2'];

        // Langkah A: Admin TU membuka kunci
        $this->actingAs($adminTU, 'admin');
        $this->post(route('admin.pendaftar.unlock', $pendaftar->id_pendaftar));
        $pendaftar->refresh();
        $this->assertFalse($pendaftar->is_locked);

        // Langkah B: Siswa mengakses halaman formulir
        $this->actingAs($user, 'web');
        $responseGet = $this->get(route('siswa.formulir'));
        $responseGet->assertStatus(200);

        // Langkah C: Siswa mengubah dan menyimpan formulir biodata
        $updatedData = [
            'id_jurusan_1' => $jurusan1->id_jurusan,
            'id_jurusan_2' => $jurusan2->id_jurusan,
            'tempat_lahir' => 'Kota Metro Direvisi',
            'tanggal_lahir' => '2010-02-02',
            'jenis_kelamin' => 'Laki-laki',
            'agama' => 'Islam',
            'anak_ke' => 2,
            'jml_saudara' => 3,
            'hp_siswa' => '081299998888',
            'almt_siswa' => 'Jl. Merdeka No. 99 Kota Metro',
            'berat_badan' => '55',
            'tinggi_badan' => '168',
            'golongan_darah' => 'B',
            'asal_sekolah' => 'SMP Negeri 2 Metro',
            'nama_ayah' => 'Nama Ayah Baru',
            'nama_ibu' => 'Nama Ibu Baru',
            'almt_ortu' => 'Jl. Merdeka No. 99 Kota Metro',
            'hp_ortu' => '081299997777',
            'kerja_ayah' => 'PNS',
            'kerja_ibu' => 'Guru',
            'penghasilan_ortu' => 'Rp 5.000.000 - Rp 10.000.000',
            'tanggungan_anak' => 3,
            'jalur_pendaftaran' => 'Reguler',
            'ukuran_seragam' => 'XL',
            'ukuran_wearpack' => 'XL',
        ];

        $responsePost = $this->post(route('siswa.formulir.simpan'), $updatedData);
        $responsePost->assertStatus(302);
        $responsePost->assertSessionHas('success');

        $pendaftar->refresh();
        $this->assertEquals('Kota Metro Direvisi', $pendaftar->tempat_lahir);
        $this->assertEquals('Nama Ayah Baru', $pendaftar->nama_ayah);
        $this->assertEquals('XL', $pendaftar->ukuran_seragam);
    }

    /**
     * 6. Pembukaan kunci tidak mengubah status pembayaran, seleksi, kelulusan, atau data lain.
     */
    public function test_06_unlock_does_not_modify_payment_selection_or_other_data(): void
    {
        $env = $this->createTestEnvironment();
        $adminTU = $env['adminTU'];
        $pendaftar = $env['pendaftar'];

        // Atur status lengkap pada pendaftar
        $pendaftar->update([
            'status_pembayaran' => 'Paid',
            'status_verifikasi' => 'Terverifikasi',
            'status_kelulusan' => 'Diterima',
            'status_daftar_ulang' => 'Belum',
            'catatan_verifikasi' => 'Berkas valid dan sesuai.',
            'is_locked' => true,
        ]);

        $snapshotBefore = [
            'status_pembayaran' => $pendaftar->status_pembayaran,
            'status_verifikasi' => $pendaftar->status_verifikasi,
            'status_kelulusan' => $pendaftar->status_kelulusan,
            'status_daftar_ulang' => $pendaftar->status_daftar_ulang,
            'catatan_verifikasi' => $pendaftar->catatan_verifikasi,
            'id_jurusan_1' => $pendaftar->id_jurusan_1,
            'id_jurusan_2' => $pendaftar->id_jurusan_2,
            'no_pendaftaran' => $pendaftar->no_pendaftaran,
            'nisn' => $pendaftar->nisn,
            'nama' => $pendaftar->nama,
        ];

        $this->actingAs($adminTU, 'admin');
        $this->post(route('admin.pendaftar.unlock', $pendaftar->id_pendaftar));

        $pendaftar->refresh();

        $this->assertFalse($pendaftar->is_locked, 'is_locked harus berubah menjadi false.');
        $this->assertEquals($snapshotBefore['status_pembayaran'], $pendaftar->status_pembayaran);
        $this->assertEquals($snapshotBefore['status_verifikasi'], $pendaftar->status_verifikasi);
        $this->assertEquals($snapshotBefore['status_kelulusan'], $pendaftar->status_kelulusan);
        $this->assertEquals($snapshotBefore['status_daftar_ulang'], $pendaftar->status_daftar_ulang);
        $this->assertEquals($snapshotBefore['catatan_verifikasi'], $pendaftar->catatan_verifikasi);
        $this->assertEquals($snapshotBefore['id_jurusan_1'], $pendaftar->id_jurusan_1);
        $this->assertEquals($snapshotBefore['id_jurusan_2'], $pendaftar->id_jurusan_2);
        $this->assertEquals($snapshotBefore['no_pendaftaran'], $pendaftar->no_pendaftaran);
        $this->assertEquals($snapshotBefore['nisn'], $pendaftar->nisn);
        $this->assertEquals($snapshotBefore['nama'], $pendaftar->nama);
    }

    /**
     * 7. Seeder menghasilkan status is_locked = false untuk akun contoh yang masih menunggu dan bersifat idempoten.
     */
    public function test_07_seeder_produces_is_locked_false_for_waiting_sample_account_and_is_idempotent(): void
    {
        // Jalankan seeder pada database testing
        $seeder = new DatabaseSeeder();
        $seeder->run();

        // Cari record Amanda Manopo (akun contoh yang masih berstatus 'Menunggu')
        $pendaftarAmanda = Pendaftar::where('no_pendaftaran', 'PPDB-2026-FAR-002')->first();
        $this->assertNotNull($pendaftarAmanda, 'Pendaftar Amanda Manopo harus ada dari Seeder.');
        $this->assertEquals('Menunggu', $pendaftarAmanda->status_verifikasi);
        $this->assertFalse($pendaftarAmanda->is_locked, 'Akun Amanda Manopo yang berstatus Menunggu harus memiliki is_locked = false.');

        // Cari record Ahmad Fauzi (akun contoh yang berstatus 'Terverifikasi' dan 'Diterima')
        $pendaftarAhmad = Pendaftar::where('no_pendaftaran', 'PPDB-2026-TKJ-001')->first();
        $this->assertNotNull($pendaftarAhmad, 'Pendaftar Ahmad Fauzi harus ada dari Seeder.');
        $this->assertEquals('Terverifikasi', $pendaftarAhmad->status_verifikasi);
        $this->assertEquals('Diterima', $pendaftarAhmad->status_kelulusan);
        $this->assertTrue($pendaftarAhmad->is_locked, 'Akun Ahmad Fauzi yang sudah Diterima harus tetap is_locked = true.');

        // Uji Idempotensi: Jalankan seeder kedua kali, tidak boleh melempar exception atau duplikasi
        $seeder->run();

        $amandaSecondRun = Pendaftar::where('no_pendaftaran', 'PPDB-2026-FAR-002')->count();
        $this->assertEquals(1, $amandaSecondRun, 'Seeder harus idempoten dan tidak membuat duplikat pendaftar.');
    }

    /**
     * 8. Aktivitas unlock tercatat dengan benar pada tabel log_aktivitas.
     */
    public function test_08_unlock_activity_is_logged_in_log_aktivitas(): void
    {
        $env = $this->createTestEnvironment();
        $adminTU = $env['adminTU'];
        $pendaftar = $env['pendaftar'];

        $this->actingAs($adminTU, 'admin');
        $this->post(route('admin.pendaftar.unlock', $pendaftar->id_pendaftar));

        $log = LogAktivitas::where('aksi', 'UNLOCK')
            ->where('tabel_terkait', 'pendaftar')
            ->where('id_data', $pendaftar->id_pendaftar)
            ->latest('id_log')
            ->first();

        $this->assertNotNull($log, 'Log aktivitas dengan aksi UNLOCK harus tercatat di database.');
        $this->assertEquals($adminTU->id_admin, $log->id_admin);
        $this->assertStringContainsString($pendaftar->nama, $log->deskripsi);
        $this->assertStringContainsString($pendaftar->no_pendaftaran, $log->deskripsi);
        $this->assertEquals(['is_locked' => true], $log->data_lama);
        $this->assertEquals(['is_locked' => false], $log->data_baru);
    }
}

