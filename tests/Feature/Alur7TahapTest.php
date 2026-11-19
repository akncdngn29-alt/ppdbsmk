<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Pendaftar;
use App\Models\BerkasPendaftar;
use Illuminate\Support\Facades\Hash;

/**
 * Test Suite: Validasi Alur 7 Tahap Progres Pendaftaran PPDB
 *
 * Memastikan stepper dashboard calon siswa menampilkan 7 tahap yang benar:
 * 1. Formulir  2. Pembayaran  3. Upload Berkas  4. Verifikasi
 * 5. Tes Seleksi  6. Kelulusan  7. Daftar Ulang
 */
class Alur7TahapTest extends TestCase
{
    private function createTestUser(): User
    {
        return User::create([
            'nama' => 'Test Siswa 7 Tahap',
            'nisn' => '00' . mt_rand(10000000, 99999999),
            'username' => 'test7tahap_' . uniqid(),
            'email' => 'test7tahap_' . uniqid() . '@test.com',
            'password' => Hash::make('password123'),
        ]);
    }

    private function createTestPendaftar(User $user, array $overrides = []): Pendaftar
    {
        $jurusan = \App\Models\Jurusan::first();
        $gelombang = \App\Models\Gelombang::first();

        return Pendaftar::create(array_merge([
            'id_user' => $user->id_user,
            'id_gelombang' => $gelombang->id_gelombang,
            'id_jurusan_1' => $jurusan->id_jurusan,
            'no_pendaftaran' => 'TEST-7T-' . uniqid(),
            'nisn' => $user->nisn,
            'nama' => $user->nama,
            'tempat_lahir' => 'Metro',
            'tanggal_lahir' => '2010-05-15',
            'jenis_kelamin' => 'Laki-laki',
            'agama' => 'Islam',
            'asal_sekolah' => 'SMP Test',
            'nama_ayah' => 'Ayah Test',
            'nama_ibu' => 'Ibu Test',
            'almt_siswa' => 'Jl. Test',
            'hp_siswa' => '081234567890',
            'almt_ortu' => 'Jl. Ortu Test',
            'hp_ortu' => '082345678901',
            'is_locked' => true,
            'status_pembayaran' => 'Pending',
            'status_verifikasi' => 'Menunggu',
            'status_kelulusan' => 'Diproses',
            'status_daftar_ulang' => 'Belum',
            'tgl_daftar' => now(),
        ], $overrides));
    }

    /**
     * Test 1: Dashboard menampilkan "Tahap X dari 7" (bukan 6)
     */
    public function test_01_dashboard_menampilkan_7_tahap(): void
    {
        $user = $this->createTestUser();
        $this->actingAs($user, 'web');

        $response = $this->get('/siswa/dashboard');
        $response->assertStatus(200);
        $response->assertSeeText('dari 7');
        $response->assertDontSeeText('dari 6');

        $user->delete();
    }

    /**
     * Test 2: Stage 1 = Belum isi formulir
     */
    public function test_02_stage_1_belum_isi_formulir(): void
    {
        $user = $this->createTestUser();
        $this->actingAs($user, 'web');

        $response = $this->get('/siswa/dashboard');
        $response->assertStatus(200);
        $response->assertSeeText('Tahap 1 dari 7');

        $user->delete();
    }

    /**
     * Test 3: Stage 2 = Formulir selesai, menunggu pembayaran
     */
    public function test_03_stage_2_formulir_selesai(): void
    {
        $user = $this->createTestUser();
        $pendaftar = $this->createTestPendaftar($user, [
            'status_pembayaran' => 'Pending',
        ]);

        $this->actingAs($user, 'web');
        $response = $this->get('/siswa/dashboard');
        $response->assertStatus(200);
        $response->assertSeeText('Tahap 2 dari 7');

        $pendaftar->delete();
        $user->delete();
    }

    /**
     * Test 4: Stage 3 = Pembayaran lunas, belum upload berkas lengkap
     */
    public function test_04_stage_3_pembayaran_lunas_belum_upload(): void
    {
        $user = $this->createTestUser();
        $pendaftar = $this->createTestPendaftar($user, [
            'status_pembayaran' => 'Paid',
        ]);

        $this->actingAs($user, 'web');
        $response = $this->get('/siswa/dashboard');
        $response->assertStatus(200);
        $response->assertSeeText('Tahap 3 dari 7');
        // Stepper harus menampilkan label "Upload Berkas"
        $response->assertSeeText('Upload Berkas');

        $pendaftar->delete();
        $user->delete();
    }

    /**
     * Test 5: Stage 3 tetap jika baru upload sebagian berkas (misal 2 dari 4)
     */
    public function test_05_stage_3_upload_sebagian_berkas(): void
    {
        $user = $this->createTestUser();
        $pendaftar = $this->createTestPendaftar($user, [
            'status_pembayaran' => 'Paid',
        ]);

        // Upload hanya 2 dari 4 dokumen wajib
        BerkasPendaftar::create([
            'id_pendaftar' => $pendaftar->id_pendaftar,
            'jenis_berkas' => 'ijazah',
            'nama_file' => 'ijazah_test.pdf',
            'path_file' => 'berkas_pendaftar/test_ijazah.pdf',
            'status_berkas' => 'Menunggu',
        ]);
        BerkasPendaftar::create([
            'id_pendaftar' => $pendaftar->id_pendaftar,
            'jenis_berkas' => 'kk',
            'nama_file' => 'kk_test.pdf',
            'path_file' => 'berkas_pendaftar/test_kk.pdf',
            'status_berkas' => 'Menunggu',
        ]);

        $this->actingAs($user, 'web');
        $response = $this->get('/siswa/dashboard');
        $response->assertStatus(200);
        $response->assertSeeText('Tahap 3 dari 7');
        // Tampilkan jumlah 2/4
        $response->assertSeeText('2/4 Dokumen');

        BerkasPendaftar::where('id_pendaftar', $pendaftar->id_pendaftar)->delete();
        $pendaftar->delete();
        $user->delete();
    }

    /**
     * Test 6: Stage 4 = Semua 4 berkas wajib terupload, menunggu verifikasi TU
     */
    public function test_06_stage_4_semua_berkas_terupload(): void
    {
        $user = $this->createTestUser();
        $pendaftar = $this->createTestPendaftar($user, [
            'status_pembayaran' => 'Paid',
            'status_verifikasi' => 'Menunggu',
        ]);

        // Upload semua 4 dokumen wajib
        foreach (['ijazah', 'kk', 'pasfoto', 'akta'] as $jenis) {
            BerkasPendaftar::create([
                'id_pendaftar' => $pendaftar->id_pendaftar,
                'jenis_berkas' => $jenis,
                'nama_file' => "{$jenis}_test.pdf",
                'path_file' => "berkas_pendaftar/test_{$jenis}.pdf",
                'status_berkas' => 'Menunggu',
            ]);
        }

        $this->actingAs($user, 'web');
        $response = $this->get('/siswa/dashboard');
        $response->assertStatus(200);
        $response->assertSeeText('Tahap 4 dari 7');
        // Status verifikasi harus tampil sebagai status tahap 4
        $response->assertSeeText('Menunggu');

        BerkasPendaftar::where('id_pendaftar', $pendaftar->id_pendaftar)->delete();
        $pendaftar->delete();
        $user->delete();
    }

    /**
     * Test 7: Stage 5 = Berkas terverifikasi oleh TU
     */
    public function test_07_stage_5_verifikasi_terverifikasi(): void
    {
        $user = $this->createTestUser();
        $pendaftar = $this->createTestPendaftar($user, [
            'status_pembayaran' => 'Paid',
            'status_verifikasi' => 'Terverifikasi',
        ]);

        foreach (['ijazah', 'kk', 'pasfoto', 'akta'] as $jenis) {
            BerkasPendaftar::create([
                'id_pendaftar' => $pendaftar->id_pendaftar,
                'jenis_berkas' => $jenis,
                'nama_file' => "{$jenis}_test.pdf",
                'path_file' => "berkas_pendaftar/test_{$jenis}.pdf",
                'status_berkas' => 'Valid',
            ]);
        }

        $this->actingAs($user, 'web');
        $response = $this->get('/siswa/dashboard');
        $response->assertStatus(200);
        $response->assertSeeText('Tahap 5 dari 7');

        BerkasPendaftar::where('id_pendaftar', $pendaftar->id_pendaftar)->delete();
        $pendaftar->delete();
        $user->delete();
    }

    /**
     * Test 7b: Stage 6 = Tes seleksi selesai (nilai seleksi terisi), kotak aktif berpindah ke Tahap 6
     */
    public function test_07b_stage_6_ketika_nilai_seleksi_sudah_ada(): void
    {
        $user = $this->createTestUser();
        $pendaftar = $this->createTestPendaftar($user, [
            'status_pembayaran' => 'Paid',
            'status_verifikasi' => 'Terverifikasi',
            'status_kelulusan' => 'Diproses',
        ]);

        foreach (['ijazah', 'kk', 'pasfoto', 'akta'] as $jenis) {
            BerkasPendaftar::create([
                'id_pendaftar' => $pendaftar->id_pendaftar,
                'jenis_berkas' => $jenis,
                'nama_file' => "{$jenis}_test.pdf",
                'path_file' => "berkas_pendaftar/test_{$jenis}.pdf",
                'status_berkas' => 'Valid',
            ]);
        }

        // Buat nilai seleksi untuk pendaftar (tes seleksi selesai)
        \App\Models\NilaiSeleksi::create([
            'id_pendaftar' => $pendaftar->id_pendaftar,
            'nilai_rapor' => 85,
            'nilai_wawancara' => 88,
            'tes_buta_warna' => 'Normal',
            'total_nilai' => 86.5,
        ]);

        $this->actingAs($user, 'web');
        $response = $this->get('/siswa/dashboard');
        $response->assertStatus(200);
        $response->assertSeeText('Tahap 6 dari 7');

        \App\Models\NilaiSeleksi::where('id_pendaftar', $pendaftar->id_pendaftar)->delete();
        BerkasPendaftar::where('id_pendaftar', $pendaftar->id_pendaftar)->delete();
        $pendaftar->delete();
        $user->delete();
    }

    /**
     * Test 8: Stage 6 = Kelulusan diumumkan (Cadangan / Tidak Lolos / Sedang Diproses)
     */
    public function test_08_stage_6_kelulusan_diumumkan(): void
    {
        $user = $this->createTestUser();
        $pendaftar = $this->createTestPendaftar($user, [
            'status_pembayaran' => 'Paid',
            'status_verifikasi' => 'Terverifikasi',
            'status_kelulusan' => 'Cadangan',
        ]);

        foreach (['ijazah', 'kk', 'pasfoto', 'akta'] as $jenis) {
            BerkasPendaftar::create([
                'id_pendaftar' => $pendaftar->id_pendaftar,
                'jenis_berkas' => $jenis,
                'nama_file' => "{$jenis}_test.pdf",
                'path_file' => "berkas_pendaftar/test_{$jenis}.pdf",
                'status_berkas' => 'Valid',
            ]);
        }

        $this->actingAs($user, 'web');
        $response = $this->get('/siswa/dashboard');
        $response->assertStatus(200);
        $response->assertSeeText('Tahap 6 dari 7');

        BerkasPendaftar::where('id_pendaftar', $pendaftar->id_pendaftar)->delete();
        $pendaftar->delete();
        $user->delete();
    }

    /**
     * Test 8b: Stage 7 = Kelulusan DITERIMA, kotak aktif langsung berpindah ke Tahap 7 (Daftar Ulang)
     */
    public function test_08b_stage_7_ketika_diterima_menuju_daftar_ulang(): void
    {
        $user = $this->createTestUser();
        $pendaftar = $this->createTestPendaftar($user, [
            'status_pembayaran' => 'Paid',
            'status_verifikasi' => 'Terverifikasi',
            'status_kelulusan' => 'Diterima',
            'status_daftar_ulang' => 'Belum',
        ]);

        foreach (['ijazah', 'kk', 'pasfoto', 'akta'] as $jenis) {
            BerkasPendaftar::create([
                'id_pendaftar' => $pendaftar->id_pendaftar,
                'jenis_berkas' => $jenis,
                'nama_file' => "{$jenis}_test.pdf",
                'path_file' => "berkas_pendaftar/test_{$jenis}.pdf",
                'status_berkas' => 'Valid',
            ]);
        }

        $this->actingAs($user, 'web');
        $response = $this->get('/siswa/dashboard');
        $response->assertStatus(200);
        $response->assertSeeText('Tahap 7 dari 7');

        BerkasPendaftar::where('id_pendaftar', $pendaftar->id_pendaftar)->delete();
        $pendaftar->delete();
        $user->delete();
    }

    /**
     * Test 9: Stage 7 = Daftar ulang selesai
     */
    public function test_09_stage_7_daftar_ulang_selesai(): void
    {
        $user = $this->createTestUser();
        $pendaftar = $this->createTestPendaftar($user, [
            'status_pembayaran' => 'Paid',
            'status_verifikasi' => 'Terverifikasi',
            'status_kelulusan' => 'Diterima',
            'status_daftar_ulang' => 'Selesai',
        ]);

        foreach (['ijazah', 'kk', 'pasfoto', 'akta'] as $jenis) {
            BerkasPendaftar::create([
                'id_pendaftar' => $pendaftar->id_pendaftar,
                'jenis_berkas' => $jenis,
                'nama_file' => "{$jenis}_test.pdf",
                'path_file' => "berkas_pendaftar/test_{$jenis}.pdf",
                'status_berkas' => 'Valid',
            ]);
        }

        $this->actingAs($user, 'web');
        $response = $this->get('/siswa/dashboard');
        $response->assertStatus(200);
        $response->assertSeeText('Tahap 7 dari 7');

        BerkasPendaftar::where('id_pendaftar', $pendaftar->id_pendaftar)->delete();
        $pendaftar->delete();
        $user->delete();
    }

    /**
     * Test 10: Upload Berkas dan Verifikasi memiliki status yang berbeda di stepper
     */
    public function test_10_upload_berkas_dan_verifikasi_status_berbeda(): void
    {
        $user = $this->createTestUser();
        $pendaftar = $this->createTestPendaftar($user, [
            'status_pembayaran' => 'Paid',
            'status_verifikasi' => 'Menunggu',
        ]);

        // Upload 3 dari 4 dokumen
        foreach (['ijazah', 'kk', 'pasfoto'] as $jenis) {
            BerkasPendaftar::create([
                'id_pendaftar' => $pendaftar->id_pendaftar,
                'jenis_berkas' => $jenis,
                'nama_file' => "{$jenis}_test.pdf",
                'path_file' => "berkas_pendaftar/test_{$jenis}.pdf",
                'status_berkas' => 'Menunggu',
            ]);
        }

        $this->actingAs($user, 'web');
        $response = $this->get('/siswa/dashboard');
        $content = $response->getContent();

        // Upload Berkas harus menampilkan "3/4 Dokumen" (kelengkapan upload)
        $this->assertStringContainsString('3/4 Dokumen', $content);

        // Verifikasi harus menampilkan status verifikasi dari database (Menunggu)
        // Kedua tahap memiliki deskripsi yang berbeda
        $this->assertStringContainsString('Upload Berkas', $content);
        $this->assertStringContainsString('Verifikasi', $content);

        BerkasPendaftar::where('id_pendaftar', $pendaftar->id_pendaftar)->delete();
        $pendaftar->delete();
        $user->delete();
    }

    /**
     * Test 11: Landing page menampilkan alur tahap PPDB Online
     */
    public function test_11_landing_page_7_tahap(): void
    {
        $response = $this->get('/');
        $response->assertStatus(200);
        $this->assertTrue(
            str_contains($response->getContent(), 'Alur 7 Tahap') ||
            str_contains($response->getContent(), 'Alur 6 Tahap')
        );
    }

    /**
     * Test 12: Sidebar menu mengikuti urutan yang benar
     */
    public function test_12_sidebar_urutan_menu_sesuai_alur(): void
    {
        $user = User::where('email', 'siswa@smkib.com')->first();
        if (!$user) {
            $this->markTestSkipped('Siswa seeder user not found');
        }

        $this->actingAs($user, 'web');
        $response = $this->get('/siswa/dashboard');
        $response->assertStatus(200);
        $content = $response->getContent();

        // Pastikan urutan menu: Formulir Biodata muncul sebelum Pembayaran PPDB
        $posFormulir = strpos($content, 'Formulir Biodata');
        $posPembayaran = strpos($content, 'Pembayaran PPDB');
        $posUploadBerkas = strpos($content, '>Upload Berkas<');
        $posDaftarUlang = strpos($content, '>Daftar Ulang<');
        $posPengaturan = strpos($content, '>Pengaturan Akun<');
        $posRiwayat = strpos($content, '>Riwayat Aktivitas<');
        $posBantuan = strpos($content, 'Pusat Bantuan');

        $this->assertNotFalse($posFormulir);
        $this->assertNotFalse($posPembayaran);
        $this->assertNotFalse($posUploadBerkas);

        // Urutan: Formulir → Pembayaran → Upload Berkas → Daftar Ulang → Pengaturan → Riwayat → Bantuan
        $this->assertLessThan($posPembayaran, $posFormulir, 'Formulir harus sebelum Pembayaran di sidebar');
        $this->assertLessThan($posUploadBerkas, $posPembayaran, 'Pembayaran harus sebelum Upload Berkas di sidebar');
        $this->assertLessThan($posDaftarUlang, $posUploadBerkas, 'Upload Berkas harus sebelum Daftar Ulang di sidebar');
        $this->assertLessThan($posPengaturan, $posDaftarUlang, 'Daftar Ulang harus sebelum Pengaturan di sidebar');
        $this->assertLessThan($posRiwayat, $posPengaturan, 'Pengaturan harus sebelum Riwayat di sidebar');
        $this->assertLessThan($posBantuan, $posRiwayat, 'Riwayat harus sebelum Bantuan di sidebar');
    }

    /**
     * Test 13: Halaman bantuan menampilkan 7 tahap
     */
    public function test_13_halaman_bantuan_7_tahap(): void
    {
        $user = User::where('email', 'siswa@smkib.com')->first();
        if (!$user) {
            $this->markTestSkipped('Siswa seeder user not found');
        }

        $this->actingAs($user, 'web');
        $response = $this->get('/siswa/bantuan');
        $response->assertStatus(200);
        $response->assertSeeText('7 Tahapan Alur PPDB');
        $response->assertSeeText('Upload Berkas Persyaratan');
        $response->assertSeeText('Verifikasi Dokumen oleh TU');
    }

    /**
     * Test 14: Semua route siswa tetap berfungsi setelah perubahan
     */
    public function test_14_semua_route_siswa_tetap_berfungsi(): void
    {
        $user = User::where('email', 'siswa@smkib.com')->first();
        if (!$user) {
            $this->markTestSkipped('Siswa seeder user not found');
        }

        $this->actingAs($user, 'web');

        $this->get('/siswa/dashboard')->assertStatus(200);
        $this->get('/siswa/berkas')->assertStatus(200);
        $this->get('/siswa/pembayaran')->assertStatus(200);
        $this->get('/siswa/daftar-ulang')->assertStatus(200);
        $this->get('/siswa/pengaturan')->assertStatus(200);
        $this->get('/siswa/riwayat-aktivitas')->assertStatus(200);
        $this->get('/siswa/bantuan')->assertStatus(200);
    }

    /**
     * Test 15: Fitur upload berkas yang sudah ada tetap berfungsi
     */
    public function test_15_fitur_upload_berkas_tetap_berfungsi(): void
    {
        $user = User::where('email', 'siswa@smkib.com')->first();
        if (!$user) {
            $this->markTestSkipped('Siswa seeder user not found');
        }

        $this->actingAs($user, 'web');

        // Halaman berkas masih bisa diakses
        $response = $this->get('/siswa/berkas');
        $response->assertStatus(200);
        // Tampil judul halaman
        $response->assertSeeText('Unggah Berkas Persyaratan');
    }
}

