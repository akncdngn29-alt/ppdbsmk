<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Admin;
use App\Models\Pendaftar;
use App\Models\Jurusan;
use App\Models\Gelombang;
use App\Models\BerkasPendaftar;
use App\Models\LogAktivitas;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class Tahap1PerbaikanTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        RateLimiter::clear('login:web:*');
        RateLimiter::clear('login:admin:*');
    }

    private function getOrCreateMasterData(): array
    {
        $jurusan = Jurusan::firstOrCreate(['kode_jurusan' => 'TKJ'], [
            'nama_jurusan' => 'Teknik Komputer dan Jaringan',
            'kuota' => 72,
        ]);

        $gelombang = Gelombang::firstOrCreate(['nama_gelombang' => 'Gelombang 1 (Testing)'], [
            'tahun_ajaran' => '2026/2027',
            'tgl_buka' => now()->subDays(5)->format('Y-m-d'),
            'tgl_tutup' => now()->addDays(30)->format('Y-m-d'),
            'biaya_pendaftaran' => 100000.00,
            'status_aktif' => true,
        ]);

        $adminTU = Admin::firstOrCreate(['username' => 'tu_tester'], [
            'nama_admin' => 'Petugas TU Penguji',
            'email' => 'tu_tester@smkib.com',
            'password' => Hash::make('password123'),
            'role' => 'tu',
        ]);

        $kepsek = Admin::firstOrCreate(['username' => 'kepsek_tester'], [
            'nama_admin' => 'Kepala Sekolah Penguji',
            'email' => 'kepsek_tester@smkib.com',
            'password' => Hash::make('password123'),
            'role' => 'kepala_sekolah',
        ]);

        $studentPassword = 'password123';
        $user = User::firstOrCreate(['email' => 'siswa_tahap1@smkib.com'], [
            'nama' => 'Ahmad Calon Siswa',
            'nisn' => '1234567890',
            'username' => 'ahmad_siswa',
            'password' => Hash::make($studentPassword),
        ]);

        $pendaftar = Pendaftar::firstOrCreate(['id_user' => $user->id_user], [
            'no_pendaftaran' => 'PPDB-2026-T1-' . rand(100, 999),
            'id_gelombang' => $gelombang->id_gelombang,
            'id_jurusan_1' => $jurusan->id_jurusan,
            'nama' => $user->nama,
            'nisn' => $user->nisn,
            'tempat_lahir' => 'Kota Metro',
            'tanggal_lahir' => '2010-01-01',
            'jenis_kelamin' => 'Laki-laki',
            'agama' => 'Islam',
            'hp_siswa' => '081234567890',
            'almt_siswa' => 'Jl. Ki Hajar Dewantara No. 1 Metro',
            'asal_sekolah' => 'SMP Negeri 1 Metro',
            'nama_ayah' => 'Ayah Ahmad',
            'nama_ibu' => 'Ibu Ahmad',
            'almt_ortu' => 'Jl. Ki Hajar Dewantara No. 1 Metro',
            'hp_ortu' => '081234567891',
            'kerja_ayah' => 'Wiraswasta',
            'kerja_ibu' => 'Ibu Rumah Tangga',
            'penghasilan_ortu' => 'Rp 3.000.000 - Rp 5.000.000',
            'status_pembayaran' => 'Paid',
            'status_verifikasi' => 'Terverifikasi',
            'status_kelulusan' => 'Diterima',
            'tgl_daftar' => now(),
        ]);

        return compact('jurusan', 'gelombang', 'adminTU', 'kepsek', 'user', 'pendaftar');
    }

    // =========================================================================
    // PEKERJAAN 1 — PENGUJIAN AUDIT LOG KEPALA SEKOLAH
    // =========================================================================

    /**
     * Uji 1.1: Halaman audit log Kepala Sekolah dapat dibuka dan menampilkan data tanpa error Blade.
     */
    public function test_kepsek_audit_log_view_renders_correctly(): void
    {
        $data = $this->getOrCreateMasterData();

        // Buat aneka ragam log untuk memverifikasi atribut nama, aksi, id_data, serta null safety
        LogAktivitas::create([
            'id_admin' => $data['kepsek']->id_admin,
            'aksi' => 'PENGESAHAN_SKL',
            'tabel_terkait' => 'terima',
            'id_data' => 999,
            'deskripsi' => 'Pengesahan kelulusan siswa uji',
            'ip_address' => '127.0.0.1',
        ]);

        LogAktivitas::create([
            'id_user' => $data['user']->id_user,
            'aksi' => 'SIMPAN_FORMULIR',
            'tabel_terkait' => 'pendaftar',
            'id_data' => $data['pendaftar']->id_pendaftar,
            'deskripsi' => 'Siswa menyimpan formulir biodata',
            'ip_address' => '127.0.0.1',
        ]);

        // Uji kasus relasi user yang dihapus (id_user menjadi null sesuai ON DELETE SET NULL)
        $tempUser = User::create([
            'nama' => 'User Dihapus',
            'nisn' => '9999888877',
            'username' => 'user_dihapus_' . uniqid(),
            'email' => 'user_dihapus_' . uniqid() . '@test.com',
            'password' => Hash::make('password123'),
        ]);

        $tempLog = LogAktivitas::create([
            'id_user' => $tempUser->id_user,
            'aksi' => 'LOGOUT',
            'deskripsi' => 'Logout pengguna yang kemudian dihapus',
            'ip_address' => '127.0.0.1',
        ]);

        $tempUser->delete(); // On delete set null mengubah id_user menjadi null

        LogAktivitas::create([
            'id_admin' => null,
            'id_user' => null,
            'aksi' => 'SISTEM_CRON',
            'deskripsi' => 'Sinkronisasi cron job sistem otomatis',
            'ip_address' => '127.0.0.1',
        ]);

        $this->actingAs($data['kepsek'], 'admin');

        $response = $this->get(route('kepsek.audit_log'));
        $response->assertStatus(200);
        $response->assertSee('Transparansi');
        $response->assertSee('Akuntabilitas Sistem PPDB');
        $response->assertSee($data['user']->nama); // atribut 'nama' bukan 'name'
        $response->assertSee('PENGESAHAN_SKL'); // kolom 'aksi' bukan 'tipe_aktivitas'
        $response->assertSee('#999'); // kolom 'id_data' bukan 'id_entitas'
        $response->assertSee('Sistem Otomatis');
    }

    /**
     * Uji 1.2: Hak akses audit log dibatasi hanya untuk Kepala Sekolah.
     */
    public function test_audit_log_access_control(): void
    {
        $data = $this->getOrCreateMasterData();

        // 1. Tamu tanpa login ditolak -> redirect ke login admin
        $this->get(route('kepsek.audit_log'))
            ->assertRedirect(route('admin.login'));

        // 2. Calon siswa (web guard) dilarang -> redirect ke admin login
        $this->actingAs($data['user'], 'web');
        $this->get(route('kepsek.audit_log'))
            ->assertRedirect(route('admin.login'));

        // 3. Petugas TU dilarang mengakses audit log Kepala Sekolah -> 403 Forbidden
        $this->actingAs($data['adminTU'], 'admin');
        $this->get(route('kepsek.audit_log'))
            ->assertStatus(403);

        // 4. Kepala Sekolah diizinkan -> 200 OK
        $this->actingAs($data['kepsek'], 'admin');
        $this->get(route('kepsek.audit_log'))
            ->assertStatus(200);
    }

    // =========================================================================
    // PEKERJAAN 2 — PENGUJIAN RATE LIMITING LOGIN
    // =========================================================================

    /**
     * Uji 2.1: Login calon siswa berhasil dan membersihkan rate limiter.
     */
    public function test_student_login_success_and_clears_throttle(): void
    {
        $data = $this->getOrCreateMasterData();

        $response = $this->post(route('login.post'), [
            'login' => $data['user']->email,
            'password' => 'password123',
        ]);

        $response->assertRedirect(route('siswa.dashboard'));
        $this->assertAuthenticatedAs($data['user'], 'web');
    }

    /**
     * Uji 2.2: Login gagal berulang memicu pembatasan (Rate Limiting) pada percobaan ke-6.
     */
    public function test_student_login_rate_limiting_after_repeated_failures(): void
    {
        $data = $this->getOrCreateMasterData();
        $targetEmail = $data['user']->email;

        // Bersihkan throttle awal
        $throttleKey = 'login:web:' . Str::transliterate(Str::lower(trim($targetEmail))) . '|127.0.0.1';
        RateLimiter::clear($throttleKey);

        // Lakukan 5 percobaan login gagal
        for ($i = 1; $i <= 5; $i++) {
            $response = $this->from(route('login'))->post(route('login.post'), [
                'login' => $targetEmail,
                'password' => 'salah_password_' . $i,
            ]);

            $response->assertRedirect(route('login'));
            $response->assertSessionHasErrors('login');
            // Pastikan belum kena limit pada 5 percobaan awal
            $errors = session('errors')->get('login');
            $this->assertStringContainsString('tidak sesuai', $errors[0]);
        }

        // Percobaan ke-6 HARUS diblokir oleh RateLimiter
        $response6 = $this->from(route('login'))->post(route('login.post'), [
            'login' => $targetEmail,
            'password' => 'password_apapun',
        ]);

        $response6->assertRedirect(route('login'));
        $response6->assertSessionHasErrors('login');
        $errors6 = session('errors')->get('login');
        $this->assertStringContainsString('Terlalu banyak percobaan login', $errors6[0]);
        $this->assertStringContainsString('detik', $errors6[0]);

        // Password TIDAK BOLEH dikembalikan ke input session (hanya 'login')
        $this->assertEquals($targetEmail, session('_old_input.login'));
        $this->assertArrayNotHasKey('password', session('_old_input', []));

        // Pemulihan: setelah rate limit kadaluarsa/dibersihkan, login benar berhasil kembali
        RateLimiter::clear($throttleKey);

        $recoveryResponse = $this->post(route('login.post'), [
            'login' => $targetEmail,
            'password' => 'password123',
        ]);
        $recoveryResponse->assertRedirect(route('siswa.dashboard'));
        $this->assertAuthenticatedAs($data['user'], 'web');
    }

    /**
     * Uji 2.3: Rate limiter tidak mengunci akun lain yang menggunakan IP yang sama (anti-IP denial).
     */
    public function test_rate_limiter_does_not_lock_different_account_on_same_ip(): void
    {
        $data = $this->getOrCreateMasterData();

        $user2 = User::firstOrCreate(['email' => 'siswa_kedua@smkib.com'], [
            'nama' => 'Budi Siswa Kedua',
            'nisn' => '9876543210',
            'username' => 'budi_siswa',
            'password' => Hash::make('password123'),
        ]);

        $throttleKey1 = 'login:web:' . Str::transliterate(Str::lower(trim($data['user']->email))) . '|127.0.0.1';
        $throttleKey2 = 'login:web:' . Str::transliterate(Str::lower(trim($user2->email))) . '|127.0.0.1';

        RateLimiter::clear($throttleKey1);
        RateLimiter::clear($throttleKey2);

        // Kunci akun 1 dengan 5x kegagalan
        for ($i = 0; $i < 5; $i++) {
            RateLimiter::hit($throttleKey1, 60);
        }

        $this->assertTrue(RateLimiter::tooManyAttempts($throttleKey1, 5));
        // Akun 2 pada IP yang sama TIDAK boleh ikut terkunci
        $this->assertFalse(RateLimiter::tooManyAttempts($throttleKey2, 5));

        // Login akun 2 tetap sukses
        $response = $this->post(route('login.post'), [
            'login' => $user2->email,
            'password' => 'password123',
        ]);
        $response->assertRedirect(route('siswa.dashboard'));
        $this->assertAuthenticatedAs($user2, 'web');
    }

    /**
     * Uji 2.4: Rate limiting juga aktif untuk login staf/admin.
     */
    public function test_admin_login_rate_limiting(): void
    {
        $data = $this->getOrCreateMasterData();
        $adminUsername = $data['adminTU']->username;
        $throttleKey = 'login:admin:' . Str::transliterate(Str::lower(trim($adminUsername))) . '|127.0.0.1';
        RateLimiter::clear($throttleKey);

        // 5x gagal
        for ($i = 1; $i <= 5; $i++) {
            $response = $this->from(route('admin.login'))->post(route('admin.login.post'), [
                'login' => $adminUsername,
                'password' => 'salah123',
            ]);
            $response->assertRedirect(route('admin.login'));
            $response->assertSessionHasErrors('login');
        }

        // Percobaan ke-6 kena throttle
        $response6 = $this->from(route('admin.login'))->post(route('admin.login.post'), [
            'login' => $adminUsername,
            'password' => 'salah123',
        ]);
        $response6->assertRedirect(route('admin.login'));
        $response6->assertSessionHasErrors('login');
        $errors = session('errors')->get('login');
        $this->assertStringContainsString('Terlalu banyak percobaan login administrator', $errors[0]);

        // Pemulihan
        RateLimiter::clear($throttleKey);
        $resSuccess = $this->post(route('admin.login.post'), [
            'login' => $adminUsername,
            'password' => 'password123',
        ]);
        $resSuccess->assertRedirect(route('admin.dashboard'));
        $this->assertAuthenticatedAs($data['adminTU'], 'admin');
    }

    // =========================================================================
    // PEKERJAAN 3 — PENGUJIAN MELENGKAPI AUDIT TRAIL
    // =========================================================================

    /**
     * Uji 3.1: Verifikasi berkas per lembar via verifikasiSingleBerkas dicatat ke log_aktivitas.
     */
    public function test_verifikasi_single_berkas_logs_activity(): void
    {
        $data = $this->getOrCreateMasterData();

        $berkas = BerkasPendaftar::create([
            'id_pendaftar' => $data['pendaftar']->id_pendaftar,
            'jenis_berkas' => 'ijazah',
            'nama_file' => 'ijazah_siswa_test.pdf',
            'path_file' => 'berkas_pendaftar/test_ijazah.pdf',
            'status_berkas' => 'Menunggu',
            'catatan' => 'Menunggu pengecekan fisik',
        ]);

        $this->actingAs($data['adminTU'], 'admin');

        $response = $this->post(route('admin.berkas.verifikasi', $berkas->id_berkas), [
            'status_berkas' => 'Valid',
            'catatan' => 'Dokumen asli terverifikasi stempel legalisir basah.',
        ]);

        $response->assertSessionHas('success');
        $berkas->refresh();
        $this->assertEquals('Valid', $berkas->status_berkas);

        // Verifikasi pencatatan log aktivitas
        $log = LogAktivitas::where('aksi', 'VERIFIKASI_BERKAS')
            ->where('id_data', $berkas->id_berkas)
            ->latest('id_log')
            ->first();

        $this->assertNotNull($log, 'Log aktivitas verifikasi single berkas harus tercatat.');
        $this->assertEquals($data['adminTU']->id_admin, $log->id_admin);
        $this->assertEquals('berkas_pendaftar', $log->tabel_terkait);
        $this->assertStringContainsString('IJAZAH', $log->deskripsi);
        $this->assertStringContainsString('Valid', $log->deskripsi);
        $this->assertEquals('Menunggu', $log->data_lama['status_berkas'] ?? null);
        $this->assertEquals('Valid', $log->data_baru['status_berkas'] ?? null);
    }

    /**
     * Uji 3.2: Unduhan laporan ekspor CSV, XLSX, Dapodik, dan Rekap PDF dicatat ke log_aktivitas.
     */
    public function test_export_and_report_downloads_log_activity_for_tu_and_kepsek(): void
    {
        $data = $this->getOrCreateMasterData();

        // 1. Petugas TU mengunduh Ekspor Excel (CSV)
        $this->actingAs($data['adminTU'], 'admin');
        $resCsv = $this->get(route('admin.ekspor.excel'));
        $resCsv->assertStatus(200);

        $logCsv = LogAktivitas::where('aksi', 'EKSPOR_CSV')->latest('id_log')->first();
        $this->assertNotNull($logCsv, 'Ekspor CSV harus dicatat di log_aktivitas.');
        $this->assertEquals($data['adminTU']->id_admin, $logCsv->id_admin);
        $this->assertStringContainsString('Petugas TU', $logCsv->deskripsi);

        // 2. Petugas TU mengunduh Ekspor XLSX
        $resXlsx = $this->get(route('admin.ekspor.xlsx'));
        $resXlsx->assertStatus(200);

        $logXlsx = LogAktivitas::where('aksi', 'EKSPOR_XLSX')->latest('id_log')->first();
        $this->assertNotNull($logXlsx, 'Ekspor XLSX harus dicatat di log_aktivitas.');

        // 3. Kepala Sekolah mengunduh Ekspor Dapodik
        $this->actingAs($data['kepsek'], 'admin');
        $resDapodik = $this->get(route('admin.ekspor.dapodik'));
        $resDapodik->assertStatus(200);

        $logDapodik = LogAktivitas::where('aksi', 'EKSPOR_DAPODIK')->latest('id_log')->first();
        $this->assertNotNull($logDapodik, 'Ekspor Dapodik oleh Kepala Sekolah harus dicatat di log_aktivitas.');
        $this->assertEquals($data['kepsek']->id_admin, $logDapodik->id_admin);
        $this->assertStringContainsString('Kepala Sekolah', $logDapodik->deskripsi);

        // 4. Kepala Sekolah mengunduh Cetak Rekap PDF
        $resPdf = $this->get(route('admin.laporan.rekap_pdf', ['jurusan' => $data['jurusan']->id_jurusan]));
        $resPdf->assertStatus(200);

        $logPdf = LogAktivitas::where('aksi', 'UNDUH_REKAP_PDF')->latest('id_log')->first();
        $this->assertNotNull($logPdf, 'Cetak rekap PDF harus dicatat di log_aktivitas.');
        $this->assertEquals($data['jurusan']->id_jurusan, $logPdf->id_data);
    }
}

