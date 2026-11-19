<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Admin;
use App\Models\Pendaftar;
use App\Models\Jurusan;
use App\Models\Gelombang;
use App\Models\BerkasPendaftar;
use App\Models\Pembayaran;
use App\Models\LogAktivitas;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class RiwayatAktivitasTest extends TestCase
{
    private function createTestingContext(): array
    {
        $jurusan = Jurusan::firstOrCreate(['kode_jurusan' => 'TKJ'], [
            'nama_jurusan' => 'Teknik Komputer dan Jaringan',
            'kuota' => 72,
        ]);

        $gelombang = Gelombang::firstOrCreate(['nama_gelombang' => 'Gelombang 1 (Early Bird)'], [
            'tahun_ajaran' => '2026/2027',
            'tgl_buka' => now()->subDays(10)->format('Y-m-d'),
            'tgl_tutup' => now()->addDays(30)->format('Y-m-d'),
            'biaya_pendaftaran' => 100000.00,
            'status_aktif' => true,
        ]);

        $adminTU = Admin::firstOrCreate(['username' => 'admin_tu_audit'], [
            'nama_admin' => 'Petugas TU Penguji',
            'email' => 'admin_tu_audit@smkib.com',
            'password' => Hash::make('password123'),
            'role' => 'tu',
        ]);

        $kepsek = Admin::firstOrCreate(['username' => 'kepsek_audit'], [
            'nama_admin' => 'Kepala Sekolah Penguji',
            'email' => 'kepsek_audit@smkib.com',
            'password' => Hash::make('password123'),
            'role' => 'kepala_sekolah',
        ]);

        $nisnA = '88' . str_pad((string) mt_rand(1, 99999999), 8, '0', STR_PAD_LEFT);
        $userA = User::create([
            'nama' => 'Siswa Audit A ' . uniqid(),
            'nisn' => $nisnA,
            'username' => 'siswa_a_' . uniqid(),
            'email' => 'siswa_a_' . uniqid() . '@smkib.com',
            'password' => Hash::make('password123'),
        ]);

        $pendaftarA = Pendaftar::create([
            'id_user' => $userA->id_user,
            'id_gelombang' => $gelombang->id_gelombang,
            'id_jurusan_1' => $jurusan->id_jurusan,
            'no_pendaftaran' => Pendaftar::generateNoPendaftaran('TKJ'),
            'nama' => $userA->nama,
            'nisn' => $userA->nisn,
            'tempat_lahir' => 'Metro',
            'tanggal_lahir' => '2008-05-15',
            'jenis_kelamin' => 'Laki-laki',
            'agama' => 'Islam',
            'almt_siswa' => 'Jl. Pattimura No. 12',
            'almt_ortu' => 'Jl. Pattimura No. 12',
            'hp_siswa' => '081234567890',
            'asal_sekolah' => 'SMP Negeri 1 Metro',
            'nama_ayah' => 'Budi',
            'nama_ibu' => 'Siti',
            'hp_ortu' => '081234567891',
            'kerja_ayah' => 'Wiraswasta',
            'kerja_ibu' => 'Guru',
            'penghasilan_ortu' => 'Rp 3.000.000 - Rp 5.000.000',
            'jalur_pendaftaran' => 'Reguler',
            'ukuran_seragam' => 'L',
            'ukuran_wearpack' => 'L',
            'status_pembayaran' => 'Paid',
            'status_verifikasi' => 'Terverifikasi',
            'status_kelulusan' => 'Diterima',
            'status_daftar_ulang' => 'Belum',
            'is_locked' => true,
            'tgl_daftar' => now(),
        ]);

        $nisnB = '88' . str_pad((string) mt_rand(1, 99999999), 8, '0', STR_PAD_LEFT);
        $userB = User::create([
            'nama' => 'Siswa Audit B ' . uniqid(),
            'nisn' => $nisnB,
            'username' => 'siswa_b_' . uniqid(),
            'email' => 'siswa_b_' . uniqid() . '@smkib.com',
            'password' => Hash::make('password123'),
        ]);

        $pendaftarB = Pendaftar::create([
            'id_user' => $userB->id_user,
            'id_gelombang' => $gelombang->id_gelombang,
            'id_jurusan_1' => $jurusan->id_jurusan,
            'no_pendaftaran' => Pendaftar::generateNoPendaftaran('TKJ'),
            'nama' => $userB->nama,
            'nisn' => $userB->nisn,
            'tempat_lahir' => 'Bandar Lampung',
            'tanggal_lahir' => '2008-07-20',
            'jenis_kelamin' => 'Perempuan',
            'agama' => 'Islam',
            'almt_siswa' => 'Jl. Raden Intan',
            'almt_ortu' => 'Jl. Raden Intan',
            'hp_siswa' => '081298765432',
            'asal_sekolah' => 'SMP Negeri 2 Metro',
            'nama_ayah' => 'Santoso',
            'nama_ibu' => 'Rahayu',
            'hp_ortu' => '081298765433',
            'kerja_ayah' => 'PNS',
            'kerja_ibu' => 'Ibu Rumah Tangga',
            'penghasilan_ortu' => 'Rp 3.000.000 - Rp 5.000.000',
            'jalur_pendaftaran' => 'Reguler',
            'ukuran_seragam' => 'M',
            'ukuran_wearpack' => 'M',
            'status_pembayaran' => 'Pending',
            'status_verifikasi' => 'Menunggu',
            'status_kelulusan' => 'Diproses',
            'status_daftar_ulang' => 'Belum',
            'is_locked' => false,
            'tgl_daftar' => now(),
        ]);

        return compact('jurusan', 'gelombang', 'adminTU', 'kepsek', 'userA', 'pendaftarA', 'userB', 'pendaftarB');
    }

    /**
     * 1 & 2. Calon Siswa hanya dapat melihat riwayat miliknya sendiri dan tidak dapat melihat siswa lain.
     */
    public function test_01_siswa_hanya_dapat_melihat_riwayat_milik_sendiri_dan_tidak_melihat_siswa_lain(): void
    {
        $ctx = $this->createTestingContext();

        $pesanRahasiaA = 'Aktivitas Khusus Siswa A ' . uniqid();
        $pesanRahasiaB = 'Aktivitas Rahasia Siswa B ' . uniqid();

        LogAktivitas::log('SIMPAN_FORMULIR', $pesanRahasiaA, 'pendaftar', $ctx['pendaftarA']->id_pendaftar, null, null, 'SUCCESS', $ctx['userA']->id_user, null, 'calon_siswa');
        LogAktivitas::log('SIMPAN_FORMULIR', $pesanRahasiaB, 'pendaftar', $ctx['pendaftarB']->id_pendaftar, null, null, 'SUCCESS', $ctx['userB']->id_user, null, 'calon_siswa');

        $this->actingAs($ctx['userA'], 'web');
        $response = $this->get(route('siswa.riwayat_aktivitas'));

        $response->assertStatus(200);
        $response->assertSee($pesanRahasiaA);
        $response->assertDontSee($pesanRahasiaB);
    }

    /**
     * 2. Calon Siswa tidak dapat melihat log operasional internal TU atau Kepsek.
     */
    public function test_02_siswa_tidak_dapat_melihat_log_internal_petugas(): void
    {
        $ctx = $this->createTestingContext();

        $pesanInternalTU = 'Operasional Internal Petugas TU Mengubah Setting ' . uniqid();
        LogAktivitas::log('UPDATE_SETTING', $pesanInternalTU, 'pengaturan_sekolah', 1, null, null, 'SUCCESS', null, $ctx['adminTU']->id_admin, 'tu');

        $this->actingAs($ctx['userA'], 'web');
        $response = $this->get(route('siswa.riwayat_aktivitas'));

        $response->assertStatus(200);
        $response->assertDontSee($pesanInternalTU);
    }

    /**
     * 3 & 4. Admin TU dapat melihat aktivitas calon siswa dan aktivitas operasional TU.
     */
    public function test_03_admin_tu_dapat_melihat_aktivitas_siswa_dan_operasional_tu(): void
    {
        $ctx = $this->createTestingContext();

        $pesanSiswa = 'Calon Siswa A Membayar ' . uniqid();
        $pesanTU = 'Admin TU Verifikasi Berkas Siswa ' . uniqid();

        LogAktivitas::log('BAYAR_PPDB', $pesanSiswa, 'pembayaran', 1, null, null, 'SUCCESS', $ctx['userA']->id_user, null, 'calon_siswa');
        LogAktivitas::log('VERIFIKASI_BERKAS', $pesanTU, 'berkas_pendaftar', 1, null, null, 'SUCCESS', $ctx['userA']->id_user, $ctx['adminTU']->id_admin, 'tu');

        $this->actingAs($ctx['adminTU'], 'admin');

        // Tab Semua Aktivitas
        $responseAll = $this->get(route('admin.riwayat_aktivitas'));
        $responseAll->assertStatus(200);
        $responseAll->assertSee($pesanSiswa);
        $responseAll->assertSee($pesanTU);

        // Tab Aktivitas Siswa
        $responseSiswa = $this->get(route('admin.riwayat_aktivitas', ['tab' => 'siswa']));
        $responseSiswa->assertStatus(200);
        $responseSiswa->assertSee($pesanSiswa);

        // Tab Operasional TU
        $responseTU = $this->get(route('admin.riwayat_aktivitas', ['tab' => 'tu']));
        $responseTU->assertStatus(200);
        $responseTU->assertSee($pesanTU);

        // Pencarian berdasarkan nomor pendaftaran siswa
        $responseSearch = $this->get(route('admin.riwayat_aktivitas', ['q' => $ctx['pendaftarA']->no_pendaftaran]));
        $responseSearch->assertStatus(200);
        $responseSearch->assertSee($ctx['pendaftarA']->no_pendaftaran);
    }

    /**
     * 5, 6, 7. Kepala Sekolah dapat melihat aktivitas seluruh role (Siswa, TU, Kepsek).
     */
    public function test_04_kepala_sekolah_dapat_melihat_seluruh_role(): void
    {
        $ctx = $this->createTestingContext();

        $pesanSiswa = 'Siswa B Unggah Berkas ' . uniqid();
        $pesanTU = 'Admin TU Buka Kunci Siswa ' . uniqid();
        $pesanKepsek = 'Kepala Sekolah Pengawasan Khusus ' . uniqid();

        LogAktivitas::log('UNGGAH_BERKAS', $pesanSiswa, 'pendaftar', $ctx['pendaftarB']->id_pendaftar, null, null, 'SUCCESS', $ctx['userB']->id_user, null, 'calon_siswa');
        LogAktivitas::log('UNLOCK', $pesanTU, 'pendaftar', $ctx['pendaftarA']->id_pendaftar, null, null, 'SUCCESS', $ctx['userA']->id_user, $ctx['adminTU']->id_admin, 'tu');
        LogAktivitas::log('PENGAWASAN', $pesanKepsek, null, null, null, null, 'SUCCESS', null, $ctx['kepsek']->id_admin, 'kepala_sekolah');

        $this->actingAs($ctx['kepsek'], 'admin');
        $response = $this->get(route('kepsek.audit_log'));

        $response->assertStatus(200);
        $response->assertSee($pesanSiswa);
        $response->assertSee($pesanTU);
        $response->assertSee($pesanKepsek);
    }

    /**
     * 8. Login dan Logout tercatat dengan benar untuk Siswa dan Admin.
     */
    public function test_05_login_dan_logout_tercatat_dengan_benar(): void
    {
        $ctx = $this->createTestingContext();

        // 1. Siswa Login
        $responseLoginSiswa = $this->post(route('login.post'), [
            'login' => $ctx['userA']->email,
            'password' => 'password123',
        ]);
        $responseLoginSiswa->assertRedirect(route('siswa.dashboard'));

        $logLoginSiswa = LogAktivitas::where('id_user', $ctx['userA']->id_user)
            ->where('aksi', 'LOGIN')
            ->latest('id_log')
            ->first();
        $this->assertNotNull($logLoginSiswa);
        $this->assertEquals('SUCCESS', $logLoginSiswa->status);
        $this->assertEquals('calon_siswa', $logLoginSiswa->role);

        // 2. Siswa Logout
        $responseLogoutSiswa = $this->post(route('logout'));
        $responseLogoutSiswa->assertRedirect(route('login'));

        $logLogoutSiswa = LogAktivitas::where('id_user', $ctx['userA']->id_user)
            ->where('aksi', 'LOGOUT')
            ->latest('id_log')
            ->first();
        $this->assertNotNull($logLogoutSiswa);

        // 3. Admin Login
        $responseLoginAdmin = $this->post(route('admin.login.post'), [
            'login' => $ctx['adminTU']->email,
            'password' => 'password123',
        ]);
        $responseLoginAdmin->assertRedirect(route('admin.dashboard'));

        $logLoginAdmin = LogAktivitas::where('id_admin', $ctx['adminTU']->id_admin)
            ->where('aksi', 'LOGIN')
            ->latest('id_log')
            ->first();
        $this->assertNotNull($logLoginAdmin);
        $this->assertEquals('tu', $logLoginAdmin->role);

        // 4. Admin Logout
        $responseLogoutAdmin = $this->post(route('admin.logout'));
        $responseLogoutAdmin->assertRedirect(route('admin.login'));

        $logLogoutAdmin = LogAktivitas::where('id_admin', $ctx['adminTU']->id_admin)
            ->where('aksi', 'LOGOUT')
            ->latest('id_log')
            ->first();
        $this->assertNotNull($logLogoutAdmin);
    }

    /**
     * 7 & 9. Percobaan login gagal tercatat dengan status FAILED tanpa kebocoran password.
     */
    public function test_06_login_gagal_tercatat_tanpa_kebocoran_password(): void
    {
        $ctx = $this->createTestingContext();

        $secretAttemptPassword = 'WrongPasswordSecret999!';

        // Login Siswa Gagal
        $this->post(route('login.post'), [
            'login' => $ctx['userA']->email,
            'password' => $secretAttemptPassword,
        ]);

        $failedLog = LogAktivitas::where('id_user', $ctx['userA']->id_user)
            ->where('aksi', 'LOGIN_GAGAL')
            ->latest('id_log')
            ->first();

        $this->assertNotNull($failedLog);
        $this->assertEquals('FAILED', $failedLog->status);
        $this->assertEquals('calon_siswa', $failedLog->role);

        // Pastikan password tidak bocor ke database log
        $this->assertStringNotContainsString($secretAttemptPassword, $failedLog->deskripsi);
        $this->assertStringNotContainsString($secretAttemptPassword, json_encode($failedLog->data_lama));
        $this->assertStringNotContainsString($secretAttemptPassword, json_encode($failedLog->data_baru));
    }

    /**
     * 9. Perubahan kata sandi tercatat tanpa membocorkan kata sandi.
     */
    public function test_07_perubahan_kata_sandi_tercatat_tanpa_menyimpan_kata_sandi(): void
    {
        $ctx = $this->createTestingContext();
        $newPlainPassword = 'BaruSecretSecurePass123!';

        $this->actingAs($ctx['userA'], 'web');
        $response = $this->post(route('siswa.pengaturan.simpan'), [
            'password' => $newPlainPassword,
            'password_confirmation' => $newPlainPassword,
        ]);

        $response->assertSessionHas('success');

        $logPassword = LogAktivitas::where('id_user', $ctx['userA']->id_user)
            ->where('aksi', 'UBAH_PASSWORD')
            ->latest('id_log')
            ->first();

        $this->assertNotNull($logPassword);
        $this->assertEquals('SUCCESS', $logPassword->status);

        // Pastikan kata sandi baru tidak tertulis di deskripsi, data lama, maupun data baru
        $this->assertStringNotContainsString($newPlainPassword, $logPassword->deskripsi);
        $this->assertStringNotContainsString($newPlainPassword, json_encode($logPassword->data_lama));
        $this->assertStringNotContainsString($newPlainPassword, json_encode($logPassword->data_baru));
    }

    /**
     * 10. Aktivitas verifikasi dokumen tercatat.
     */
    public function test_08_verifikasi_dokumen_tercatat(): void
    {
        $ctx = $this->createTestingContext();

        $berkas = BerkasPendaftar::create([
            'id_pendaftar' => $ctx['pendaftarA']->id_pendaftar,
            'jenis_berkas' => 'ijazah',
            'nama_file' => 'ijazah_asli.pdf',
            'path_file' => 'berkas_pendaftar/ijazah_asli.pdf',
            'status_berkas' => 'Menunggu',
        ]);

        $this->actingAs($ctx['adminTU'], 'admin');
        $response = $this->post(route('admin.berkas.verifikasi', $berkas->id_berkas), [
            'status_berkas' => 'Valid',
            'catatan' => 'Berkas ijazah legalisir terverifikasi valid.',
        ]);

        $response->assertSessionHas('success');

        $logBerkas = LogAktivitas::where('aksi', 'VERIFIKASI_BERKAS')
            ->where('id_data', $berkas->id_berkas)
            ->latest('id_log')
            ->first();

        $this->assertNotNull($logBerkas);
        $this->assertEquals('tu', $logBerkas->role);
        $this->assertEquals($ctx['adminTU']->id_admin, $logBerkas->id_admin);
        $this->assertEquals($ctx['userA']->id_user, $logBerkas->id_user);
    }

    /**
     * 11. Aktivitas pembukaan kunci formulir tercatat.
     */
    public function test_09_aktivitas_pembukaan_kunci_formulir_tercatat(): void
    {
        $ctx = $this->createTestingContext();

        $this->actingAs($ctx['adminTU'], 'admin');
        $response = $this->post(route('admin.pendaftar.unlock', $ctx['pendaftarA']->id_pendaftar));
        $response->assertSessionHas('success');

        $logUnlock = LogAktivitas::where('aksi', 'UNLOCK')
            ->where('id_data', $ctx['pendaftarA']->id_pendaftar)
            ->latest('id_log')
            ->first();

        $this->assertNotNull($logUnlock);
        $this->assertEquals('tu', $logUnlock->role);
        $this->assertEquals($ctx['userA']->id_user, $logUnlock->id_user);
    }

    /**
     * 12. Aktivitas ekspor laporan tercatat.
     */
    public function test_10_aktivitas_ekspor_laporan_tercatat(): void
    {
        $ctx = $this->createTestingContext();

        $this->actingAs($ctx['adminTU'], 'admin');
        $response = $this->get(route('admin.ekspor.excel'));
        $this->assertEquals(200, $response->getStatusCode());

        $logExport = LogAktivitas::where('aksi', 'EKSPOR_CSV')
            ->latest('id_log')
            ->first();

        $this->assertNotNull($logExport);
        $this->assertEquals('tu', $logExport->role);
    }

    /**
     * 13. Perlindungan hak akses & proteksi lintas role.
     */
    public function test_11_proteksi_lintas_role_unauthorized(): void
    {
        $ctx = $this->createTestingContext();

        // 1. Siswa dilarang membuka panel riwayat aktivitas Petugas TU -> Redirect ke admin.login (302)
        $this->actingAs($ctx['userA'], 'web');
        $responseSiswaToAdmin = $this->get(route('admin.riwayat_aktivitas'));
        $responseSiswaToAdmin->assertRedirect(route('admin.login'));

        // 2. Siswa dilarang membuka panel audit trail Kepala Sekolah -> Redirect ke admin.login (302)
        $responseSiswaToKepsek = $this->get(route('kepsek.audit_log'));
        $responseSiswaToKepsek->assertRedirect(route('admin.login'));

        // 3. Petugas TU dilarang membuka audit trail Kepala Sekolah -> Wajib HTTP 403
        $this->actingAs($ctx['adminTU'], 'admin');
        $responseTUToKepsek = $this->get(route('kepsek.audit_log'));
        $responseTUToKepsek->assertStatus(403);

        // 4. Kepala Sekolah dilarang membuka panel operasional khusus Petugas TU -> Wajib HTTP 403
        $this->actingAs($ctx['kepsek'], 'admin');
        $responseKepsekToTU = $this->get(route('admin.riwayat_aktivitas'));
        $responseKepsekToTU->assertStatus(403);

        // 5. Pengunjung anonim diarahkan ke login
        auth()->logout();
        auth('admin')->logout();
        $responseGuest = $this->get(route('siswa.riwayat_aktivitas'));
        $responseGuest->assertRedirect(route('login'));
    }

    /**
     * 14. Pengunduhan dokumen resmi siswa mencatat riwayat aktivitas.
     */
    public function test_12_unduh_dokumen_resmi_siswa_tercatat(): void
    {
        $ctx = $this->createTestingContext();

        $this->actingAs($ctx['userA'], 'web');

        // Cetak Formulir
        $this->get(route('siswa.cetak.formulir'))->assertStatus(200);
        $this->assertDatabaseHas('log_aktivitas', [
            'id_user' => $ctx['userA']->id_user,
            'aksi' => 'UNDUH_BUKTI_DAFTAR',
        ]);

        // Cetak Kartu Ujian (sudah lunas)
        $this->get(route('siswa.cetak.kartu_ujian'))->assertStatus(200);
        $this->assertDatabaseHas('log_aktivitas', [
            'id_user' => $ctx['userA']->id_user,
            'aksi' => 'UNDUH_KARTU_UJIAN',
        ]);
    }
}
