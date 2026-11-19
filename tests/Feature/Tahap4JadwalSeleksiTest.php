<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Admin;
use App\Models\Pendaftar;
use App\Models\Jurusan;
use App\Models\Gelombang;
use App\Models\JadwalSeleksi;
use App\Models\RuangSeleksi;
use App\Models\PenempatanPeserta;
use App\Models\LogAktivitas;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class Tahap4JadwalSeleksiTest extends TestCase
{
    private function setupTestEnvironment(): array
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

        $adminTU = Admin::firstOrCreate(['username' => 'petugas_tu_tahap4'], [
            'nama_admin' => 'Petugas TU Tahap 4',
            'email' => 'tu_tahap4@smkib.com',
            'password' => Hash::make('password123'),
            'role' => 'tu',
        ]);

        $adminKepsek = Admin::firstOrCreate(['username' => 'kepsek_tahap4'], [
            'nama_admin' => 'Kepala Sekolah Tahap 4',
            'email' => 'kepsek_tahap4@smkib.com',
            'password' => Hash::make('password123'),
            'role' => 'kepala_sekolah',
        ]);

        $user = User::create([
            'nama' => 'Calon Siswa Uji ' . uniqid(),
            'nisn' => '98' . str_pad((string) mt_rand(1, 99999999), 8, '0', STR_PAD_LEFT),
            'username' => 'siswa_t4_' . uniqid(),
            'email' => 'siswa_t4_' . uniqid() . '@test.com',
            'password' => Hash::make('password123'),
        ]);

        $pendaftar = Pendaftar::create([
            'id_user' => $user->id_user,
            'id_gelombang' => $gelombang->id_gelombang,
            'id_jurusan_1' => $jurusan->id_jurusan,
            'no_pendaftaran' => Pendaftar::generateNoPendaftaran('TKJ'),
            'tgl_daftar' => now()->format('Y-m-d'),
            'nisn' => $user->nisn,
            'nama' => $user->nama,
            'tempat_lahir' => 'Metro',
            'tanggal_lahir' => '2010-05-15',
            'jenis_kelamin' => 'Laki-laki',
            'agama' => 'Islam',
            'nama_ayah' => 'Ayah Uji',
            'nama_ibu' => 'Ibu Uji',
            'almt_ortu' => 'Jl. Uji No. 1 Metro',
            'hp_ortu' => '081234567890',
            'jalur_pendaftaran' => 'Reguler',
            'is_locked' => true,
            'status_verifikasi' => 'Terverifikasi',
            'status_pembayaran' => 'Paid',
        ]);

        return compact('jurusan', 'gelombang', 'adminTU', 'adminKepsek', 'user', 'pendaftar');
    }

    private function createPesertaEligible(string $prefix = 'TEST'): Pendaftar
    {
        $jurusan = Jurusan::firstOrCreate(['kode_jurusan' => 'TKJ'], [
            'nama_jurusan' => 'Teknik Komputer dan Jaringan',
            'kuota' => 72,
        ]);

        $gelombang = Gelombang::where('status_aktif', true)->first();

        $user = User::create([
            'nama' => 'Peserta ' . $prefix . ' ' . uniqid(),
            'nisn' => '99' . str_pad((string) mt_rand(1, 99999999), 8, '0', STR_PAD_LEFT),
            'username' => 'usr_' . strtolower($prefix) . '_' . uniqid(),
            'email' => 'usr_' . strtolower($prefix) . '_' . uniqid() . '@test.com',
            'password' => Hash::make('password123'),
        ]);

        return Pendaftar::create([
            'id_user' => $user->id_user,
            'id_gelombang' => $gelombang->id_gelombang,
            'id_jurusan_1' => $jurusan->id_jurusan,
            'no_pendaftaran' => Pendaftar::generateNoPendaftaran('TKJ'),
            'tgl_daftar' => now()->format('Y-m-d'),
            'nisn' => $user->nisn,
            'nama' => $user->nama,
            'tempat_lahir' => 'Metro',
            'tanggal_lahir' => '2010-01-01',
            'jenis_kelamin' => 'Laki-laki',
            'agama' => 'Islam',
            'nama_ayah' => 'Ayah',
            'nama_ibu' => 'Ibu',
            'almt_ortu' => 'Metro',
            'hp_ortu' => '081299999999',
            'jalur_pendaftaran' => 'Reguler',
            'is_locked' => true,
            'status_verifikasi' => 'Terverifikasi',
            'status_pembayaran' => 'Paid',
        ]);
    }

    /**
     * Skenario 1: Petugas TU dapat membuat jadwal seleksi baru.
     */
    public function test_01_petugas_tu_dapat_membuat_jadwal(): void
    {
        $env = $this->setupTestEnvironment();

        $payload = [
            'nama_jadwal' => 'Tes Minat Bakat Sesi 1',
            'jenis_seleksi' => 'Wawancara & Tahfiz',
            'tanggal' => Carbon::tomorrow()->format('Y-m-d'),
            'waktu_mulai' => '08:00',
            'waktu_selesai' => '11:00',
            'lokasi' => 'Kampus SMK Islam Bina Khalifah Bangsa',
            'gedung' => 'Gedung A',
            'keterangan' => 'Membawa kartu peserta ujian fisik',
            'status' => 'aktif',
        ];

        $response = $this->actingAs($env['adminTU'], 'admin')
            ->post(route('admin.jadwal.store'), $payload);

        $response->assertRedirect(route('admin.jadwal.index'));
        $this->assertDatabaseHas('jadwal_seleksi', [
            'nama_jadwal' => 'Tes Minat Bakat Sesi 1',
            'jenis_seleksi' => 'Wawancara & Tahfiz',
            'status' => 'aktif',
        ]);
    }

    /**
     * Skenario 2: Petugas TU dapat mengubah data jadwal.
     */
    public function test_02_petugas_tu_dapat_mengubah_jadwal(): void
    {
        $env = $this->setupTestEnvironment();

        $jadwal = JadwalSeleksi::create([
            'nama_jadwal' => 'Jadwal Sebelum Diubah',
            'jenis_seleksi' => 'Tes Buta Warna',
            'tanggal' => Carbon::tomorrow()->format('Y-m-d'),
            'waktu_mulai' => '08:00',
            'waktu_selesai' => '10:00',
            'status' => 'aktif',
        ]);

        $response = $this->actingAs($env['adminTU'], 'admin')
            ->put(route('admin.jadwal.update', $jadwal->id_jadwal), [
                'nama_jadwal' => 'Jadwal Setelah Diubah',
                'jenis_seleksi' => 'Tes Buta Warna',
                'tanggal' => Carbon::tomorrow()->format('Y-m-d'),
                'waktu_mulai' => '09:00',
                'waktu_selesai' => '11:30',
                'status' => 'aktif',
            ]);

        $response->assertRedirect(route('admin.jadwal.index'));
        $this->assertDatabaseHas('jadwal_seleksi', [
            'id_jadwal' => $jadwal->id_jadwal,
            'nama_jadwal' => 'Jadwal Setelah Diubah',
        ]);
    }

    /**
     * Skenario 3: Petugas TU tidak dapat membuat jadwal dengan data tidak valid (waktu selesai sebelum waktu mulai).
     */
    public function test_03_petugas_tu_tidak_dapat_membuat_jadwal_dengan_data_tidak_valid(): void
    {
        $env = $this->setupTestEnvironment();

        $payload = [
            'nama_jadwal' => '', // kosong (invalid)
            'jenis_seleksi' => 'Wawancara',
            'tanggal' => 'bukan-tanggal',
            'waktu_mulai' => '10:00',
            'waktu_selesai' => '08:00', // waktu selesai sebelum mulai (invalid)
            'status' => 'invalid_status',
        ];

        $response = $this->actingAs($env['adminTU'], 'admin')
            ->post(route('admin.jadwal.store'), $payload);

        $response->assertSessionHasErrors(['nama_jadwal', 'tanggal', 'waktu_selesai', 'status']);
    }

    /**
     * Skenario 4: Petugas TU dapat membuat ruang seleksi baru.
     */
    public function test_04_petugas_tu_dapat_membuat_ruang(): void
    {
        $env = $this->setupTestEnvironment();
        $kodeRuang = 'R-T4-' . mt_rand(100, 999);

        $payload = [
            'kode_ruang' => $kodeRuang,
            'nama_ruang' => 'Lab Komputer TKJ',
            'gedung' => 'Gedung B Lt. 2',
            'kapasitas' => 25,
            'keterangan' => 'Dilengkapi 30 PC dan AC',
            'status_aktif' => 1,
        ];

        $response = $this->actingAs($env['adminTU'], 'admin')
            ->post(route('admin.ruang.store'), $payload);

        $response->assertRedirect(route('admin.ruang.index'));
        $this->assertDatabaseHas('ruang_seleksi', [
            'kode_ruang' => $kodeRuang,
            'kapasitas' => 25,
            'status_aktif' => 1,
        ]);
    }

    /**
     * Skenario 5: Kapasitas ruang harus valid (tidak boleh nol, negatif, atau non-angka).
     */
    public function test_05_kapasitas_ruang_harus_valid(): void
    {
        $env = $this->setupTestEnvironment();

        // Uji kapasitas 0
        $response = $this->actingAs($env['adminTU'], 'admin')
            ->post(route('admin.ruang.store'), [
                'kode_ruang' => 'R-INVALID-0',
                'nama_ruang' => 'Ruang Kapasitas Nol',
                'kapasitas' => 0, // min:1
            ]);

        $response->assertSessionHasErrors(['kapasitas']);

        // Uji kapasitas negatif
        $responseNeg = $this->actingAs($env['adminTU'], 'admin')
            ->post(route('admin.ruang.store'), [
                'kode_ruang' => 'R-INVALID-NEG',
                'nama_ruang' => 'Ruang Kapasitas Negatif',
                'kapasitas' => -5,
            ]);

        $responseNeg->assertSessionHasErrors(['kapasitas']);
    }

    /**
     * Skenario 6: Sistem dapat membagi peserta secara otomatis.
     */
    public function test_06_sistem_dapat_membagi_peserta_secara_otomatis(): void
    {
        $env = $this->setupTestEnvironment();

        $jadwal = JadwalSeleksi::create([
            'nama_jadwal' => 'Seleksi Otomatis ' . uniqid(),
            'jenis_seleksi' => 'Wawancara & Tahfiz',
            'tanggal' => Carbon::tomorrow()->format('Y-m-d'),
            'waktu_mulai' => '08:00',
            'waktu_selesai' => '11:00',
            'status' => 'aktif',
        ]);

        $ruang = RuangSeleksi::create([
            'kode_ruang' => 'R-AUTO-' . mt_rand(100, 999),
            'nama_ruang' => 'Ruang Uji Otomatis',
            'kapasitas' => 10,
            'status_aktif' => true,
        ]);

        $peserta1 = $this->createPesertaEligible('P1');
        $peserta2 = $this->createPesertaEligible('P2');

        $response = $this->actingAs($env['adminTU'], 'admin')
            ->post(route('admin.jadwal.penempatan.otomatis', $jadwal->id_jadwal), [
                'pendaftar_ids' => [$peserta1->id_pendaftar, $peserta2->id_pendaftar],
            ]);

        $response->assertRedirect(route('admin.jadwal.penempatan', $jadwal->id_jadwal));
        $this->assertDatabaseHas('penempatan_peserta', [
            'id_jadwal' => $jadwal->id_jadwal,
            'id_pendaftar' => $peserta1->id_pendaftar,
        ]);
        $this->assertDatabaseHas('penempatan_peserta', [
            'id_jadwal' => $jadwal->id_jadwal,
            'id_pendaftar' => $peserta2->id_pendaftar,
        ]);
    }

    /**
     * Skenario 7: Pembagian otomatis tidak melebihi kapasitas ruang.
     */
    public function test_07_pembagian_otomatis_tidak_melebihi_kapasitas(): void
    {
        $env = $this->setupTestEnvironment();

        $jadwal = JadwalSeleksi::create([
            'nama_jadwal' => 'Seleksi Kuota Terbatas ' . uniqid(),
            'jenis_seleksi' => 'Wawancara',
            'tanggal' => Carbon::tomorrow()->format('Y-m-d'),
            'waktu_mulai' => '08:00',
            'waktu_selesai' => '11:00',
            'status' => 'aktif',
        ]);

        // Ruang kapasitas kecil (hanya 2 kursi)
        RuangSeleksi::query()->update(['status_aktif' => false]);

        $ruangKecil = RuangSeleksi::create([
            'kode_ruang' => 'R-MINI-' . mt_rand(100, 999),
            'nama_ruang' => 'Ruang Mini',
            'kapasitas' => 2,
            'status_aktif' => true,
        ]);

        // Siapkan 4 peserta
        $p1 = $this->createPesertaEligible('K1');
        $p2 = $this->createPesertaEligible('K2');
        $p3 = $this->createPesertaEligible('K3');
        $p4 = $this->createPesertaEligible('K4');

        $this->actingAs($env['adminTU'], 'admin')
            ->post(route('admin.jadwal.penempatan.otomatis', $jadwal->id_jadwal), [
                'pendaftar_ids' => [$p1->id_pendaftar, $p2->id_pendaftar, $p3->id_pendaftar, $p4->id_pendaftar],
            ]);

        $terisi = PenempatanPeserta::where('id_jadwal', $jadwal->id_jadwal)
            ->where('id_ruang', $ruangKecil->id_ruang)
            ->count();

        // Kembalikan status aktif ruang lain
        RuangSeleksi::query()->update(['status_aktif' => true]);

        // Tidak boleh melebihi kapasitas 2
        $this->assertLessThanOrEqual($ruangKecil->kapasitas, $terisi);
        $this->assertEquals(2, $terisi);
    }

    /**
     * Skenario 8: Tidak ada peserta yang mendapat penempatan ganda pada jadwal yang sama.
     */
    public function test_08_tidak_ada_peserta_yang_mendapat_penempatan_ganda(): void
    {
        $env = $this->setupTestEnvironment();

        $jadwal = JadwalSeleksi::create([
            'nama_jadwal' => 'Seleksi Anti Ganda ' . uniqid(),
            'jenis_seleksi' => 'Wawancara',
            'tanggal' => Carbon::tomorrow()->format('Y-m-d'),
            'waktu_mulai' => '08:00',
            'waktu_selesai' => '11:00',
            'status' => 'aktif',
        ]);

        $ruang1 = RuangSeleksi::create([
            'kode_ruang' => 'R-G1-' . mt_rand(100, 999),
            'nama_ruang' => 'Ruang Ganda 1',
            'kapasitas' => 10,
            'status_aktif' => true,
        ]);

        $peserta = $this->createPesertaEligible('GANDA');

        // Tempatkan pertama kali
        PenempatanPeserta::create([
            'id_jadwal' => $jadwal->id_jadwal,
            'id_ruang' => $ruang1->id_ruang,
            'id_pendaftar' => $peserta->id_pendaftar,
        ]);

        // Coba tempatkan otomatis lagi dengan ID peserta yang sama
        $this->actingAs($env['adminTU'], 'admin')
            ->post(route('admin.jadwal.penempatan.otomatis', $jadwal->id_jadwal), [
                'pendaftar_ids' => [$peserta->id_pendaftar],
            ]);

        // Jumlah penempatan harus tetap tepat 1
        $count = PenempatanPeserta::where('id_jadwal', $jadwal->id_jadwal)
            ->where('id_pendaftar', $peserta->id_pendaftar)
            ->count();

        $this->assertEquals(1, $count);
    }

    /**
     * Skenario 9: Peserta yang melebihi kapasitas ruang tetap tercatat sebagai belum ditempatkan.
     */
    public function test_09_peserta_yang_melebihi_kapasitas_ruang_tetap_tercatat_belum_ditempatkan(): void
    {
        $env = $this->setupTestEnvironment();

        $jadwal = JadwalSeleksi::create([
            'nama_jadwal' => 'Seleksi Kapasitas Penuh ' . uniqid(),
            'jenis_seleksi' => 'Akademik',
            'tanggal' => Carbon::tomorrow()->format('Y-m-d'),
            'waktu_mulai' => '08:00',
            'waktu_selesai' => '10:00',
            'status' => 'aktif',
        ]);

        // Nonaktifkan semua ruang aktif lama agar uji terisolasi
        RuangSeleksi::query()->update(['status_aktif' => false]);

        $ruangKecil = RuangSeleksi::create([
            'kode_ruang' => 'R-LIMIT-' . mt_rand(100, 999),
            'nama_ruang' => 'Ruang Hanya Satu Kursi',
            'kapasitas' => 1,
            'status_aktif' => true,
        ]);

        $p1 = $this->createPesertaEligible('LIMIT1');
        $p2 = $this->createPesertaEligible('LIMIT2');

        $this->actingAs($env['adminTU'], 'admin')
            ->post(route('admin.jadwal.penempatan.otomatis', $jadwal->id_jadwal), [
                'pendaftar_ids' => [$p1->id_pendaftar, $p2->id_pendaftar],
            ]);

        // Salah satu ditempatkan, salah satu belum
        $ditempatkanCount = PenempatanPeserta::where('id_jadwal', $jadwal->id_jadwal)->count();
        $this->assertEquals(1, $ditempatkanCount);

        // Akses halaman penempatan untuk memverifikasi peserta yang belum ditempatkan muncul di view
        $response = $this->actingAs($env['adminTU'], 'admin')
            ->get(route('admin.jadwal.penempatan', $jadwal->id_jadwal));

        $response->assertStatus(200);
        $response->assertViewHas('belumDitempatkan', function ($belum) {
            return $belum->count() >= 1;
        });

        // Kembalikan status aktif ruang lain
        RuangSeleksi::query()->update(['status_aktif' => true]);
    }

    /**
     * Skenario 10: Petugas TU dapat memindahkan peserta secara manual ke ruang lain.
     */
    public function test_10_petugas_tu_dapat_memindahkan_peserta_secara_manual(): void
    {
        $env = $this->setupTestEnvironment();

        $jadwal = JadwalSeleksi::create([
            'nama_jadwal' => 'Seleksi Pindah Ruang ' . uniqid(),
            'jenis_seleksi' => 'Wawancara',
            'tanggal' => Carbon::tomorrow()->format('Y-m-d'),
            'waktu_mulai' => '08:00',
            'waktu_selesai' => '10:00',
            'status' => 'aktif',
        ]);

        $ruangAsal = RuangSeleksi::create([
            'kode_ruang' => 'R-ASAL-' . mt_rand(100, 999),
            'nama_ruang' => 'Ruang Asal',
            'kapasitas' => 10,
            'status_aktif' => true,
        ]);

        $ruangTujuan = RuangSeleksi::create([
            'kode_ruang' => 'R-TUJUAN-' . mt_rand(100, 999),
            'nama_ruang' => 'Ruang Tujuan',
            'kapasitas' => 10,
            'status_aktif' => true,
        ]);

        $peserta = $this->createPesertaEligible('PINDAH');

        // Tempatkan di ruang asal
        PenempatanPeserta::create([
            'id_jadwal' => $jadwal->id_jadwal,
            'id_ruang' => $ruangAsal->id_ruang,
            'id_pendaftar' => $peserta->id_pendaftar,
        ]);

        // Pindahkan ke ruang tujuan
        $response = $this->actingAs($env['adminTU'], 'admin')
            ->post(route('admin.jadwal.penempatan.manual'), [
                'id_jadwal' => $jadwal->id_jadwal,
                'id_pendaftar' => $peserta->id_pendaftar,
                'id_ruang' => $ruangTujuan->id_ruang,
                'catatan' => 'Dipindahkan atas permintaan peserta',
            ]);

        $response->assertRedirect(route('admin.jadwal.penempatan', $jadwal->id_jadwal));
        $this->assertDatabaseHas('penempatan_peserta', [
            'id_jadwal' => $jadwal->id_jadwal,
            'id_pendaftar' => $peserta->id_pendaftar,
            'id_ruang' => $ruangTujuan->id_ruang,
        ]);
    }

    /**
     * Skenario 11: Pemindahan manual ditolak jika ruang penuh.
     */
    public function test_11_pemindahan_manual_ditolak_jika_ruang_penuh(): void
    {
        $env = $this->setupTestEnvironment();

        $jadwal = JadwalSeleksi::create([
            'nama_jadwal' => 'Seleksi Ruang Penuh ' . uniqid(),
            'jenis_seleksi' => 'Wawancara',
            'tanggal' => Carbon::tomorrow()->format('Y-m-d'),
            'waktu_mulai' => '08:00',
            'waktu_selesai' => '10:00',
            'status' => 'aktif',
        ]);

        $ruangAsal = RuangSeleksi::create([
            'kode_ruang' => 'R-AS-' . mt_rand(100, 999),
            'nama_ruang' => 'Ruang Asal Bebas',
            'kapasitas' => 10,
            'status_aktif' => true,
        ]);

        $ruangPenuh = RuangSeleksi::create([
            'kode_ruang' => 'R-FULL-' . mt_rand(100, 999),
            'nama_ruang' => 'Ruang Kapasitas Penuh',
            'kapasitas' => 1,
            'status_aktif' => true,
        ]);

        // Isi ruangPenuh sampai kapasitas maksimal
        $pesertaLain = $this->createPesertaEligible('PENGISI');
        PenempatanPeserta::create([
            'id_jadwal' => $jadwal->id_jadwal,
            'id_ruang' => $ruangPenuh->id_ruang,
            'id_pendaftar' => $pesertaLain->id_pendaftar,
        ]);

        // Peserta yang ingin dipindah
        $pesertaMauPindah = $this->createPesertaEligible('MAUPINDAH');
        PenempatanPeserta::create([
            'id_jadwal' => $jadwal->id_jadwal,
            'id_ruang' => $ruangAsal->id_ruang,
            'id_pendaftar' => $pesertaMauPindah->id_pendaftar,
        ]);

        // Coba pindah ke ruangPenuh
        $response = $this->actingAs($env['adminTU'], 'admin')
            ->post(route('admin.jadwal.penempatan.manual'), [
                'id_jadwal' => $jadwal->id_jadwal,
                'id_pendaftar' => $pesertaMauPindah->id_pendaftar,
                'id_ruang' => $ruangPenuh->id_ruang,
            ]);

        $response->assertSessionHas('error');
        // Pastikan ruang tetap di ruangAsal
        $this->assertDatabaseHas('penempatan_peserta', [
            'id_jadwal' => $jadwal->id_jadwal,
            'id_pendaftar' => $pesertaMauPindah->id_pendaftar,
            'id_ruang' => $ruangAsal->id_ruang,
        ]);
    }

    /**
     * Skenario 12: Jadwal bentrok peserta ditolak.
     */
    public function test_12_jadwal_bentrok_peserta_ditolak(): void
    {
        $env = $this->setupTestEnvironment();
        $tgl = Carbon::tomorrow()->format('Y-m-d');

        // Jadwal A: 08:00 - 10:00
        $jadwalA = JadwalSeleksi::create([
            'nama_jadwal' => 'Jadwal Pagi A ' . uniqid(),
            'jenis_seleksi' => 'Wawancara',
            'tanggal' => $tgl,
            'waktu_mulai' => '08:00',
            'waktu_selesai' => '10:00',
            'status' => 'aktif',
        ]);

        // Jadwal B: 09:00 - 11:00 (bentrok tumpang tindih waktu dengan A)
        $jadwalB = JadwalSeleksi::create([
            'nama_jadwal' => 'Jadwal Tumpang Tindih B ' . uniqid(),
            'jenis_seleksi' => 'Tes Fisik',
            'tanggal' => $tgl,
            'waktu_mulai' => '09:00',
            'waktu_selesai' => '11:00',
            'status' => 'aktif',
        ]);

        $ruang = RuangSeleksi::create([
            'kode_ruang' => 'R-BENTROK-' . mt_rand(100, 999),
            'nama_ruang' => 'Ruang Bentrok',
            'kapasitas' => 10,
            'status_aktif' => true,
        ]);

        $peserta = $this->createPesertaEligible('BENTROK');

        // Tempatkan di Jadwal A
        PenempatanPeserta::create([
            'id_jadwal' => $jadwalA->id_jadwal,
            'id_ruang' => $ruang->id_ruang,
            'id_pendaftar' => $peserta->id_pendaftar,
        ]);

        // Coba tempatkan peserta yang sama di Jadwal B (waktu bentrok)
        $response = $this->actingAs($env['adminTU'], 'admin')
            ->post(route('admin.jadwal.penempatan.manual'), [
                'id_jadwal' => $jadwalB->id_jadwal,
                'id_pendaftar' => $peserta->id_pendaftar,
                'id_ruang' => $ruang->id_ruang,
            ]);

        $response->assertSessionHas('error');
        $this->assertDatabaseMissing('penempatan_peserta', [
            'id_jadwal' => $jadwalB->id_jadwal,
            'id_pendaftar' => $peserta->id_pendaftar,
        ]);
    }

    /**
     * Skenario 13: Calon siswa hanya dapat melihat jadwal miliknya sendiri.
     */
    public function test_13_calon_siswa_hanya_dapat_melihat_jadwal_miliknya(): void
    {
        $env = $this->setupTestEnvironment();

        $jadwal = JadwalSeleksi::create([
            'nama_jadwal' => 'Jadwal Seleksi Calon Siswa ' . uniqid(),
            'jenis_seleksi' => 'Wawancara & Tahfiz',
            'tanggal' => Carbon::tomorrow()->format('Y-m-d'),
            'waktu_mulai' => '08:00',
            'waktu_selesai' => '10:00',
            'status' => 'aktif',
        ]);

        $ruang = RuangSeleksi::create([
            'kode_ruang' => 'R-SISWA-' . mt_rand(100, 999),
            'nama_ruang' => 'Lab Komputer Siswa',
            'kapasitas' => 20,
            'status_aktif' => true,
        ]);

        PenempatanPeserta::create([
            'id_jadwal' => $jadwal->id_jadwal,
            'id_ruang' => $ruang->id_ruang,
            'id_pendaftar' => $env['pendaftar']->id_pendaftar,
        ]);

        $response = $this->actingAs($env['user'], 'web')
            ->get(route('siswa.jadwal'));

        $response->assertStatus(200);
        $response->assertSee($jadwal->nama_jadwal);
        $response->assertSee($ruang->nama_ruang);
    }

    /**
     * Skenario 14: Calon siswa tidak dapat mengubah jadwal.
     */
    public function test_14_calon_siswa_tidak_dapat_mengubah_jadwal(): void
    {
        $env = $this->setupTestEnvironment();

        $jadwal = JadwalSeleksi::create([
            'nama_jadwal' => 'Jadwal Asli Dilindungi ' . uniqid(),
            'jenis_seleksi' => 'Wawancara',
            'tanggal' => Carbon::tomorrow()->format('Y-m-d'),
            'waktu_mulai' => '08:00',
            'waktu_selesai' => '10:00',
            'status' => 'aktif',
        ]);

        // Calon siswa mencoba update jadwal
        $response = $this->actingAs($env['user'], 'web')
            ->put(route('admin.jadwal.update', $jadwal->id_jadwal), [
                'nama_jadwal' => 'Dirusak oleh Siswa',
            ]);

        // Ditolak oleh middleware admin.role:tu (302 redirect ke admin login atau 403)
        $this->assertTrue(in_array($response->getStatusCode(), [302, 403]));
        $this->assertDatabaseMissing('jadwal_seleksi', [
            'nama_jadwal' => 'Dirusak oleh Siswa',
        ]);
    }

    /**
     * Skenario 15: Kepala Sekolah hanya dapat melihat data jadwal (read-only).
     */
    public function test_15_kepala_sekolah_hanya_dapat_melihat_data_jadwal(): void
    {
        $env = $this->setupTestEnvironment();

        $jadwal = JadwalSeleksi::create([
            'nama_jadwal' => 'Jadwal Monitoring Kepsek ' . uniqid(),
            'jenis_seleksi' => 'Wawancara',
            'tanggal' => Carbon::tomorrow()->format('Y-m-d'),
            'waktu_mulai' => '08:00',
            'waktu_selesai' => '10:00',
            'status' => 'aktif',
        ]);

        $response = $this->actingAs($env['adminKepsek'], 'admin')
            ->get(route('kepsek.jadwal_monitoring'));

        $response->assertStatus(200);
        $response->assertSee($jadwal->nama_jadwal);
    }

    /**
     * Skenario 16: Kepala Sekolah tidak dapat mengubah atau menghapus jadwal.
     */
    public function test_16_kepala_sekolah_tidak_dapat_mengubah_jadwal(): void
    {
        $env = $this->setupTestEnvironment();

        $jadwal = JadwalSeleksi::create([
            'nama_jadwal' => 'Jadwal Terlindungi dari Kepsek ' . uniqid(),
            'jenis_seleksi' => 'Wawancara',
            'tanggal' => Carbon::tomorrow()->format('Y-m-d'),
            'waktu_mulai' => '08:00',
            'waktu_selesai' => '10:00',
            'status' => 'aktif',
        ]);

        // Kepsek mencoba delete jadwal
        $response = $this->actingAs($env['adminKepsek'], 'admin')
            ->delete(route('admin.jadwal.destroy', $jadwal->id_jadwal));

        // Ditolak oleh middleware admin.role:tu
        $this->assertTrue(in_array($response->getStatusCode(), [302, 403]));
        $this->assertDatabaseHas('jadwal_seleksi', [
            'id_jadwal' => $jadwal->id_jadwal,
        ]);
    }

    /**
     * Skenario 17: Aktivitas jadwal dan penempatan tercatat di log_aktivitas.
     */
    public function test_17_aktivitas_jadwal_dan_penempatan_tercatat_di_log_aktivitas(): void
    {
        $env = $this->setupTestEnvironment();

        // 1. Tambah Jadwal via Controller/Route
        $this->actingAs($env['adminTU'], 'admin')
            ->post(route('admin.jadwal.store'), [
                'nama_jadwal' => 'Seleksi Audit Trail ' . uniqid(),
                'jenis_seleksi' => 'Wawancara & Tahfiz',
                'tanggal' => Carbon::tomorrow()->format('Y-m-d'),
                'waktu_mulai' => '08:00',
                'waktu_selesai' => '11:00',
                'status' => 'aktif',
            ]);

        $this->assertDatabaseHas('log_aktivitas', [
            'aksi' => 'TAMBAH_JADWAL',
            'status' => 'SUCCESS',
            'role' => 'tu',
        ]);

        // 2. Tambah Ruang
        $this->actingAs($env['adminTU'], 'admin')
            ->post(route('admin.ruang.store'), [
                'kode_ruang' => 'R-LOG-' . mt_rand(100, 999),
                'nama_ruang' => 'Ruang Audit Log',
                'kapasitas' => 15,
                'status_aktif' => 1,
            ]);

        $this->assertDatabaseHas('log_aktivitas', [
            'aksi' => 'TAMBAH_RUANG',
            'status' => 'SUCCESS',
            'role' => 'tu',
        ]);
    }

    /**
     * Skenario 18: Data jadwal dan penempatan tidak hilang setelah logout dan login kembali.
     */
    public function test_18_data_jadwal_dan_penempatan_tidak_hilang_setelah_logout_dan_login(): void
    {
        $env = $this->setupTestEnvironment();

        $jadwal = JadwalSeleksi::create([
            'nama_jadwal' => 'Jadwal Persistensi Sesi ' . uniqid(),
            'jenis_seleksi' => 'Wawancara',
            'tanggal' => Carbon::tomorrow()->format('Y-m-d'),
            'waktu_mulai' => '08:00',
            'waktu_selesai' => '10:00',
            'status' => 'aktif',
        ]);

        $ruang = RuangSeleksi::create([
            'kode_ruang' => 'R-PERSIST-' . mt_rand(100, 999),
            'nama_ruang' => 'Ruang Persistensi',
            'kapasitas' => 20,
            'status_aktif' => true,
        ]);

        PenempatanPeserta::create([
            'id_jadwal' => $jadwal->id_jadwal,
            'id_ruang' => $ruang->id_ruang,
            'id_pendaftar' => $env['pendaftar']->id_pendaftar,
        ]);

        // Simulasikan logout
        Auth::guard('web')->logout();
        Auth::guard('admin')->logout();

        // Login kembali sebagai Calon Siswa
        $response = $this->actingAs($env['user'], 'web')
            ->get(route('siswa.jadwal'));

        $response->assertStatus(200);
        $response->assertSee($jadwal->nama_jadwal);
        $response->assertSee($ruang->nama_ruang);
    }

    /**
     * Skenario 19: Fitur kartu ujian yang sudah ada tetap berfungsi.
     */
    public function test_19_fitur_kartu_ujian_yang_sudah_ada_tetap_berfungsi(): void
    {
        $env = $this->setupTestEnvironment();

        $response = $this->actingAs($env['user'], 'web')
            ->get(route('siswa.cetak.kartu_ujian'));

        $response->assertStatus(200);
        $response->assertSee('KARTU TANDA PESERTA TES SELEKSI MASUK');
        $response->assertSee($env['pendaftar']->no_pendaftaran);
    }

    /**
     * Skenario 20: Fitur Riwayat Aktivitas Tahap 2 tetap berfungsi.
     */
    public function test_20_fitur_riwayat_aktivitas_tahap2_tetap_berfungsi(): void
    {
        $env = $this->setupTestEnvironment();

        // Admin TU melihat riwayat aktivitas
        $responseTU = $this->actingAs($env['adminTU'], 'admin')
            ->get(route('admin.riwayat_aktivitas'));
        $responseTU->assertStatus(200);

        // Kepala Sekolah melihat audit log
        $responseKepsek = $this->actingAs($env['adminKepsek'], 'admin')
            ->get(route('kepsek.audit_log'));
        $responseKepsek->assertStatus(200);

        // Siswa melihat riwayat aktivitas akun miliknya
        $responseSiswa = $this->actingAs($env['user'], 'web')
            ->get(route('siswa.riwayat_aktivitas'));
        $responseSiswa->assertStatus(200);
    }
}

