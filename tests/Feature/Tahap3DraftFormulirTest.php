<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Admin;
use App\Models\Pendaftar;
use App\Models\Jurusan;
use App\Models\Gelombang;
use App\Models\LogAktivitas;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class Tahap3DraftFormulirTest extends TestCase
{
    private function setupTestEnvironment(): array
    {
        $jurusan1 = Jurusan::firstOrCreate(['kode_jurusan' => 'TKJ'], [
            'nama_jurusan' => 'Teknik Komputer dan Jaringan',
            'kuota' => 72,
        ]);

        $jurusan2 = Jurusan::firstOrCreate(['kode_jurusan' => 'RPL'], [
            'nama_jurusan' => 'Rekayasa Perangkat Lunak',
            'kuota' => 72,
        ]);

        $gelombang = Gelombang::firstOrCreate(['nama_gelombang' => 'Gelombang 1 (Early Bird)'], [
            'tahun_ajaran' => '2026/2027',
            'tgl_buka' => now()->subDays(10)->format('Y-m-d'),
            'tgl_tutup' => now()->addDays(30)->format('Y-m-d'),
            'biaya_pendaftaran' => 100000.00,
            'status_aktif' => true,
        ]);

        $uniqueNisn = '97' . str_pad((string) mt_rand(1, 99999999), 8, '0', STR_PAD_LEFT);
        $user = User::create([
            'nama' => 'Siswa Uji Draft ' . uniqid(),
            'nisn' => $uniqueNisn,
            'username' => 'siswa_draft_' . uniqid(),
            'email' => 'siswa_draft_' . uniqid() . '@test.com',
            'password' => Hash::make('password123'),
        ]);

        $adminTU = Admin::firstOrCreate(['username' => 'admin_tu_draft_test'], [
            'nama_admin' => 'Admin TU Draft Test',
            'email' => 'admin_tu_draft@smkib.com',
            'password' => Hash::make('password123'),
            'role' => 'tu',
        ]);

        return compact('jurusan1', 'jurusan2', 'gelombang', 'user', 'adminTU');
    }

    /**
     * 1. Siswa dapat menyimpan draft dengan sebagian field kosong.
     */
    public function test_01_siswa_dapat_menyimpan_draft_dengan_sebagian_field_kosong(): void
    {
        $env = $this->setupTestEnvironment();
        $user = $env['user'];

        $this->actingAs($user, 'web');

        // Siswa hanya mengisi tempat lahir dan no hp, field lain kosong
        $partialData = [
            'action' => 'draft',
            'tempat_lahir' => 'Kota Metro',
            'hp_siswa' => '081234567890',
            'nama_ayah' => 'Bambang',
        ];

        $response = $this->post(route('siswa.formulir.simpan'), $partialData);

        $response->assertStatus(302);
        $response->assertSessionHas('success');

        $pendaftar = Pendaftar::where('id_user', $user->id_user)->first();
        $this->assertNotNull($pendaftar, 'Record draft pendaftar harus dibuat.');
        $this->assertEquals('Kota Metro', $pendaftar->tempat_lahir);
        $this->assertEquals('081234567890', $pendaftar->hp_siswa);
        $this->assertEquals('Bambang', $pendaftar->nama_ayah);
        $this->assertNull($pendaftar->asal_sekolah, 'Field yang tidak diisi harus bernilai NULL.');
        $this->assertFalse($pendaftar->is_locked, 'Draft tidak boleh berstatus terkunci.');
    }

    /**
     * 2. Data draft benar-benar tersimpan di database secara permanen.
     */
    public function test_02_data_draft_benar_benar_tersimpan_di_database(): void
    {
        $env = $this->setupTestEnvironment();
        $user = $env['user'];
        $jurusan1 = $env['jurusan1'];

        $this->actingAs($user, 'web');

        $draftData = [
            'action' => 'draft',
            'id_jurusan_1' => $jurusan1->id_jurusan,
            'tempat_lahir' => 'Lampung Tengah',
            'almt_siswa' => 'Jl. Merdeka No. 45',
        ];

        $this->post(route('siswa.formulir.simpan'), $draftData);

        $this->assertDatabaseHas('pendaftar', [
            'id_user' => $user->id_user,
            'id_jurusan_1' => $jurusan1->id_jurusan,
            'tempat_lahir' => 'Lampung Tengah',
            'almt_siswa' => 'Jl. Merdeka No. 45',
            'is_locked' => 0,
        ]);
    }

    /**
     * 3. Draft tetap tersedia setelah logout dan login kembali.
     */
    public function test_03_draft_tetap_tersedia_setelah_logout_dan_login_kembali(): void
    {
        $env = $this->setupTestEnvironment();
        $user = $env['user'];

        // 1. Simpan draft
        $this->actingAs($user, 'web');
        $this->post(route('siswa.formulir.simpan'), [
            'action' => 'draft',
            'tempat_lahir' => 'Metro Pusat',
            'nama_ayah' => 'Sutrisno',
        ]);

        // 2. Simulasi logout
        auth('web')->logout();
        $this->assertGuest('web');

        // 3. Login kembali
        $this->actingAs($user, 'web');
        $response = $this->get(route('siswa.formulir'));

        $response->assertStatus(200);
        $response->assertSee('Metro Pusat');
        $response->assertSee('Sutrisno');
        $response->assertSee('Draft Tersimpan');
    }

    /**
     * 4. Siswa dapat melanjutkan pengisian dari data terakhir yang tersimpan.
     */
    public function test_04_siswa_dapat_melanjutkan_pengisian_dari_data_terakhir(): void
    {
        $env = $this->setupTestEnvironment();
        $user = $env['user'];
        $jurusan1 = $env['jurusan1'];

        $this->actingAs($user, 'web');

        // Pengisian tahap pertama
        $this->post(route('siswa.formulir.simpan'), [
            'action' => 'draft',
            'tempat_lahir' => 'Kota Metro',
        ]);

        // Pengisian tahap kedua (melanjutkan dan menambah asal sekolah & jurusan)
        $this->post(route('siswa.formulir.simpan'), [
            'action' => 'draft',
            'id_jurusan_1' => $jurusan1->id_jurusan,
            'tempat_lahir' => 'Kota Metro',
            'asal_sekolah' => 'SMP Negeri 1 Metro',
            'nama_ibu' => 'Sulastri',
        ]);

        $pendaftar = Pendaftar::where('id_user', $user->id_user)->first();
        $this->assertEquals('Kota Metro', $pendaftar->tempat_lahir);
        $this->assertEquals('SMP Negeri 1 Metro', $pendaftar->asal_sekolah);
        $this->assertEquals('Sulastri', $pendaftar->nama_ibu);
        $this->assertEquals($jurusan1->id_jurusan, $pendaftar->id_jurusan_1);
        $this->assertFalse($pendaftar->is_locked);
    }

    /**
     * 5. Field yang diisi tetap divalidasi format dan keamanannya saat simpan draft.
     */
    public function test_05_field_yang_diisi_pada_draft_tetap_divalidasi(): void
    {
        $env = $this->setupTestEnvironment();
        $user = $env['user'];
        $jurusan1 = $env['jurusan1'];

        $this->actingAs($user, 'web');

        // Kirim data tidak valid: tanggal lahir salah format, anak ke minus, jurusan 2 sama dengan jurusan 1
        $invalidData = [
            'action' => 'draft',
            'id_jurusan_1' => $jurusan1->id_jurusan,
            'id_jurusan_2' => $jurusan1->id_jurusan, // tidak boleh sama
            'tanggal_lahir' => 'bukan-tanggal-valid',
            'anak_ke' => -3, // min 1
            'jenis_kelamin' => 'Alien', // harus Laki-laki atau Perempuan
        ];

        $response = $this->post(route('siswa.formulir.simpan'), $invalidData);

        $response->assertStatus(302);
        $response->assertSessionHasErrors(['id_jurusan_2', 'tanggal_lahir', 'anak_ke', 'jenis_kelamin']);

        // Pastikan tidak ada data rusak yang disimpan
        $this->assertDatabaseMissing('pendaftar', [
            'id_user' => $user->id_user,
            'tanggal_lahir' => 'bukan-tanggal-valid',
        ]);
    }

    /**
     * 6. Draft tidak otomatis mengubah status formulir menjadi final/terkunci.
     */
    public function test_06_draft_tidak_otomatis_mengubah_status_menjadi_final(): void
    {
        $env = $this->setupTestEnvironment();
        $user = $env['user'];

        $this->actingAs($user, 'web');

        for ($i = 1; $i <= 3; $i++) {
            $this->post(route('siswa.formulir.simpan'), [
                'action' => 'draft',
                'tempat_lahir' => "Metro Iterasi $i",
            ]);
        }

        $pendaftar = Pendaftar::where('id_user', $user->id_user)->first();
        $this->assertNotNull($pendaftar);
        $this->assertFalse($pendaftar->is_locked, 'Draft tidak boleh mengubah is_locked menjadi true.');
    }

    /**
     * 7. Finalisasi ditolak jika field wajib belum lengkap.
     */
    public function test_07_finalisasi_ditolak_jika_field_wajib_belum_lengkap(): void
    {
        $env = $this->setupTestEnvironment();
        $user = $env['user'];

        $this->actingAs($user, 'web');

        // Simpan draft hanya sebagian
        $this->post(route('siswa.formulir.simpan'), [
            'action' => 'draft',
            'tempat_lahir' => 'Metro',
        ]);

        // Coba finalisasi via POST /siswa/finalisasi
        $responseFinalisasi = $this->post(route('siswa.finalisasi'));

        $responseFinalisasi->assertStatus(302);
        $responseFinalisasi->assertSessionHas('error');
        $responseFinalisasi->assertSessionHasErrors(['id_jurusan_1', 'nama_ayah', 'nama_ibu', 'almt_siswa', 'asal_sekolah']);

        $pendaftar = Pendaftar::where('id_user', $user->id_user)->first();
        $this->assertFalse($pendaftar->is_locked, 'Status harus tetap terbuka (is_locked = false) saat finalisasi ditolak.');

        // Cek log aktivitas mencatat percobaan gagal
        $this->assertDatabaseHas('log_aktivitas', [
            'id_user' => $user->id_user,
            'aksi' => 'FINALISASI_GAGAL',
            'status' => 'FAILED',
        ]);
    }

    /**
     * 8. Finalisasi berhasil jika seluruh persyaratan terpenuhi.
     */
    public function test_08_finalisasi_berhasil_jika_seluruh_persyaratan_terpenuhi(): void
    {
        $env = $this->setupTestEnvironment();
        $user = $env['user'];
        $jurusan1 = $env['jurusan1'];
        $jurusan2 = $env['jurusan2'];

        $this->actingAs($user, 'web');

        $completeData = [
            'action' => 'finalisasi',
            'id_jurusan_1' => $jurusan1->id_jurusan,
            'id_jurusan_2' => $jurusan2->id_jurusan,
            'tempat_lahir' => 'Kota Metro',
            'tanggal_lahir' => '2008-01-15',
            'jenis_kelamin' => 'Laki-laki',
            'agama' => 'Islam',
            'anak_ke' => 1,
            'jml_saudara' => 2,
            'hp_siswa' => '081234567890',
            'almt_siswa' => 'Jl. Jendral Sudirman No. 10',
            'berat_badan' => '55',
            'tinggi_badan' => '165',
            'golongan_darah' => 'B',
            'asal_sekolah' => 'SMP Negeri 1 Metro',
            'alamat_sekolah' => 'Jl. Raden Intan Metro',
            'nama_ayah' => 'Haryanto',
            'nama_ibu' => 'Endang',
            'nama_wali' => null,
            'almt_ortu' => 'Jl. Jendral Sudirman No. 10',
            'hp_ortu' => '081234567899',
            'kerja_ayah' => 'PNS',
            'kerja_ibu' => 'Wiraswasta',
            'kerja_wali' => null,
            'penghasilan_ortu' => 'Rp 3.000.000 - Rp 5.000.000',
            'tanggungan_anak' => 2,
            'jalur_pendaftaran' => 'Reguler',
            'ukuran_seragam' => 'L',
            'ukuran_wearpack' => 'L',
            'pilihan_ekskul' => ['Pramuka', 'Paskibra'],
        ];

        $response = $this->post(route('siswa.formulir.simpan'), $completeData);

        $response->assertStatus(302);
        $response->assertRedirect(route('siswa.dashboard'));
        $response->assertSessionHas('success');

        $pendaftar = Pendaftar::where('id_user', $user->id_user)->first();
        $this->assertNotNull($pendaftar);
        $this->assertTrue($pendaftar->is_locked, 'is_locked harus bernilai true setelah difinalisasi.');

        // Verifikasi log aktivitas
        $this->assertDatabaseHas('log_aktivitas', [
            'id_user' => $user->id_user,
            'aksi' => 'FINALISASI',
            'status' => 'SUCCESS',
        ]);
    }

    /**
     * 9. Formulir yang terkunci tidak dapat diedit oleh calon siswa.
     */
    public function test_09_formulir_yang_terkunci_tidak_dapat_diedit(): void
    {
        $env = $this->setupTestEnvironment();
        $user = $env['user'];
        $jurusan1 = $env['jurusan1'];

        // Siapkan pendaftar yang sudah terkunci
        $pendaftar = Pendaftar::create([
            'id_user' => $user->id_user,
            'id_gelombang' => $env['gelombang']->id_gelombang,
            'id_jurusan_1' => $jurusan1->id_jurusan,
            'no_pendaftaran' => Pendaftar::generateNoPendaftaran('TKJ'),
            'nama' => $user->nama,
            'nisn' => $user->nisn,
            'tempat_lahir' => 'Kota Metro',
            'tanggal_lahir' => '2008-01-15',
            'jenis_kelamin' => 'Laki-laki',
            'hp_siswa' => '081234567890',
            'almt_siswa' => 'Jl. Merdeka',
            'asal_sekolah' => 'SMPN 1 Metro',
            'nama_ayah' => 'Ayah Asli',
            'nama_ibu' => 'Ibu Asli',
            'almt_ortu' => 'Jl. Merdeka',
            'hp_ortu' => '081234567899',
            'kerja_ayah' => 'PNS',
            'kerja_ibu' => 'PNS',
            'penghasilan_ortu' => 'Rp 3.000.000 - Rp 5.000.000',
            'is_locked' => true,
            'tgl_daftar' => now(),
        ]);

        $this->actingAs($user, 'web');

        // Coba akses halaman formulir saat terkunci
        $responseGet = $this->get(route('siswa.formulir'));
        $responseGet->assertStatus(302);
        $responseGet->assertRedirect(route('siswa.dashboard'));
        $responseGet->assertSessionHas('info');

        // Coba kirim update saat terkunci
        $responsePost = $this->post(route('siswa.formulir.simpan'), [
            'action' => 'draft',
            'tempat_lahir' => 'Tempat Diretas',
        ]);

        $responsePost->assertStatus(302);
        $responsePost->assertSessionHas('error');

        $pendaftar->refresh();
        $this->assertEquals('Kota Metro', $pendaftar->tempat_lahir, 'Data formulir tidak boleh berubah ketika terkunci.');
    }

    /**
     * 10. Formulir hanya dapat diedit kembali setelah di-unlock oleh Admin TU.
     */
    public function test_10_formulir_hanya_dapat_diedit_kembali_setelah_diunlock_admin_tu(): void
    {
        $env = $this->setupTestEnvironment();
        $user = $env['user'];
        $adminTU = $env['adminTU'];
        $jurusan1 = $env['jurusan1'];

        $pendaftar = Pendaftar::create([
            'id_user' => $user->id_user,
            'id_gelombang' => $env['gelombang']->id_gelombang,
            'id_jurusan_1' => $jurusan1->id_jurusan,
            'no_pendaftaran' => Pendaftar::generateNoPendaftaran('TKJ'),
            'nama' => $user->nama,
            'nisn' => $user->nisn,
            'tempat_lahir' => 'Kota Metro',
            'tanggal_lahir' => '2008-01-15',
            'jenis_kelamin' => 'Laki-laki',
            'hp_siswa' => '081234567890',
            'almt_siswa' => 'Jl. Merdeka',
            'asal_sekolah' => 'SMPN 1 Metro',
            'nama_ayah' => 'Ayah',
            'nama_ibu' => 'Ibu',
            'almt_ortu' => 'Jl. Merdeka',
            'hp_ortu' => '081234567899',
            'kerja_ayah' => 'PNS',
            'kerja_ibu' => 'PNS',
            'penghasilan_ortu' => 'Rp 3.000.000 - Rp 5.000.000',
            'is_locked' => true,
            'tgl_daftar' => now(),
        ]);

        // 1. Admin TU membuka kunci
        $this->actingAs($adminTU, 'admin');
        $resUnlock = $this->post(route('admin.pendaftar.unlock', $pendaftar->id_pendaftar));
        $resUnlock->assertStatus(302);
        $resUnlock->assertSessionHas('success');

        $pendaftar->refresh();
        $this->assertFalse($pendaftar->is_locked, 'Status is_locked harus false setelah diunlock TU.');

        // 2. Siswa login kembali dan mengedit formulir
        $this->actingAs($user, 'web');
        $resGet = $this->get(route('siswa.formulir'));
        $resGet->assertStatus(200);

        $resUpdate = $this->post(route('siswa.formulir.simpan'), [
            'action' => 'draft',
            'tempat_lahir' => 'Kota Metro Dikoreksi',
        ]);
        $resUpdate->assertStatus(302);
        $resUpdate->assertSessionHas('success');

        $pendaftar->refresh();
        $this->assertEquals('Kota Metro Dikoreksi', $pendaftar->tempat_lahir);
    }

    /**
     * 11. Aktivitas penyimpanan draft dan finalisasi tercatat di tabel log_aktivitas.
     */
    public function test_11_aktivitas_penyimpanan_draft_dan_finalisasi_tercatat(): void
    {
        $env = $this->setupTestEnvironment();
        $user = $env['user'];

        $this->actingAs($user, 'web');

        // Simpan draft
        $this->post(route('siswa.formulir.simpan'), [
            'action' => 'draft',
            'tempat_lahir' => 'Metro Log Uji',
        ]);

        $logDraft = LogAktivitas::where('id_user', $user->id_user)
            ->where('aksi', 'SIMPAN_DRAFT')
            ->first();

        $this->assertNotNull($logDraft, 'Log aktivitas SIMPAN_DRAFT harus tercatat.');
        $this->assertEquals('calon_siswa', $logDraft->role);
        $this->assertEquals('SUCCESS', $logDraft->status);
    }

    /**
     * 12. Siswa tidak dapat mengubah formulir milik siswa lain.
     */
    public function test_12_siswa_tidak_dapat_mengubah_formulir_milik_siswa_lain(): void
    {
        $env = $this->setupTestEnvironment();
        $userA = $env['user'];
        $jurusan1 = $env['jurusan1'];

        // Pendaftar Siswa A
        $pendaftarA = Pendaftar::create([
            'id_user' => $userA->id_user,
            'id_gelombang' => $env['gelombang']->id_gelombang,
            'id_jurusan_1' => $jurusan1->id_jurusan,
            'no_pendaftaran' => Pendaftar::generateNoPendaftaran('TKJ'),
            'nama' => $userA->nama,
            'nisn' => $userA->nisn,
            'tempat_lahir' => 'Data Asli Siswa A',
            'tanggal_lahir' => '2008-01-15',
            'jenis_kelamin' => 'Laki-laki',
            'hp_siswa' => '081234567890',
            'almt_siswa' => 'Jl. Merdeka',
            'asal_sekolah' => 'SMPN 1 Metro',
            'nama_ayah' => 'Ayah A',
            'nama_ibu' => 'Ibu A',
            'almt_ortu' => 'Jl. Merdeka',
            'hp_ortu' => '081234567899',
            'kerja_ayah' => 'PNS',
            'kerja_ibu' => 'PNS',
            'penghasilan_ortu' => 'Rp 3.000.000 - Rp 5.000.000',
            'is_locked' => false,
            'tgl_daftar' => now(),
        ]);

        // Siswa B
        $uniqueNisnB = '96' . str_pad((string) mt_rand(1, 99999999), 8, '0', STR_PAD_LEFT);
        $userB = User::create([
            'nama' => 'Siswa B Intruder',
            'nisn' => $uniqueNisnB,
            'username' => 'siswa_b_' . uniqid(),
            'email' => 'siswa_b_' . uniqid() . '@test.com',
            'password' => Hash::make('password123'),
        ]);

        // Siswa B login dan mencoba memodifikasi dengan menginjeksi id_pendaftar Siswa A
        $this->actingAs($userB, 'web');
        $this->post(route('siswa.formulir.simpan'), [
            'action' => 'draft',
            'id_pendaftar' => $pendaftarA->id_pendaftar,
            'tempat_lahir' => 'Disusupi Oleh Siswa B',
        ]);

        $pendaftarA->refresh();
        $this->assertEquals('Data Asli Siswa A', $pendaftarA->tempat_lahir, 'Formulir Siswa A tidak boleh terpengaruh oleh aksi Siswa B.');

        // Siswa B mendapatkan draft miliknya sendiri
        $pendaftarB = Pendaftar::where('id_user', $userB->id_user)->first();
        $this->assertNotNull($pendaftarB);
        $this->assertEquals('Disusupi Oleh Siswa B', $pendaftarB->tempat_lahir);
    }

    /**
     * 13. Fitur formulir dan penguncian yang sudah ada tetap berfungsi dengan baik.
     */
    public function test_13_fitur_formulir_dan_penguncian_yang_sudah_ada_tetap_berfungsi(): void
    {
        $env = $this->setupTestEnvironment();
        $user = $env['user'];

        $this->actingAs($user, 'web');

        // Akses dashboard ketika belum isi
        $resDash = $this->get(route('siswa.dashboard'));
        $resDash->assertStatus(200);
        $resDash->assertSee('Belum Diisi');

        // Simpan draft
        $this->post(route('siswa.formulir.simpan'), [
            'action' => 'draft',
            'tempat_lahir' => 'Kota Metro',
        ]);

        // Akses dashboard setelah draft
        $resDash2 = $this->get(route('siswa.dashboard'));
        $resDash2->assertStatus(200);
        $resDash2->assertSee('Draft Tersimpan');
    }
}
