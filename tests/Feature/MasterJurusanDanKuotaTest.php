<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\Jurusan;
use App\Models\LogAktivitas;
use App\Models\Pendaftar;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class MasterJurusanDanKuotaTest extends TestCase
{
    use DatabaseTransactions;

    protected Admin $adminTU;
    protected Admin $kepsek;
    protected User $siswa;

    protected function setUp(): void
    {
        parent::setUp();

        $this->adminTU = Admin::firstOrCreate(
            ['username' => 'petugas_tu_test'],
            [
                'nama_admin' => 'Petugas TU Penguji',
                'email' => 'tu_test_' . uniqid() . '@smkib.com',
                'password' => Hash::make('password123'),
                'role' => 'tu',
                'status_aktif' => true,
            ]
        );

        $this->kepsek = Admin::firstOrCreate(
            ['username' => 'kepsek_test'],
            [
                'nama_admin' => 'Kepala Sekolah Penguji',
                'email' => 'kepsek_test_' . uniqid() . '@smkib.com',
                'password' => Hash::make('password123'),
                'role' => 'kepala_sekolah',
                'status_aktif' => true,
            ]
        );

        $this->siswa = User::firstOrCreate(
            ['username' => 'siswa_tester'],
            [
                'nama' => 'Calon Siswa Penguji',
                'nisn' => '0098765432',
                'email' => 'siswa_tester_' . uniqid() . '@test.com',
                'password' => Hash::make('password123'),
            ]
        );
    }

    public function test_01_petugas_tu_dapat_mengakses_halaman_master_jurusan(): void
    {
        $response = $this->actingAs($this->adminTU, 'admin')
            ->get(route('admin.master.jurusan'));

        $response->assertStatus(200);
        $response->assertSee('Master Program Keahlian');
        $response->assertSee('Daftar Program Keahlian');
        $response->assertSee('+ Tambah Program Keahlian');
        $response->assertSee('Tambahkan jurusan baru kapan saja');
    }

    public function test_02_pengelolaan_jurusan_hanya_untuk_petugas_tu_dan_dibatasi_dari_role_lain(): void
    {
        // 1. Guest diarahkan ke login
        $this->get(route('admin.master.jurusan'))
            ->assertRedirect(route('admin.login'));

        // 2. Calon Siswa (guard web) tidak memiliki hak akses admin
        $this->actingAs($this->siswa, 'web')
            ->get(route('admin.master.jurusan'))
            ->assertRedirect(route('admin.login'));
    }

    public function test_03_tambah_program_keahlian_baru_berhasil(): void
    {
        $kodeUnik = 'RPL' . mt_rand(10, 99);
        $payload = [
            'kode_jurusan' => $kodeUnik,
            'nama_jurusan' => 'Rekayasa Perangkat Lunak',
            'kuota' => 72,
            'deskripsi' => 'Pengembangan perangkat lunak, web modern, mobile application, dan basis data.',
            'prospek_kerja' => 'Software Engineer, Fullstack Web Developer, Mobile Developer, Database Administrator',
            'status_aktif' => 1,
        ];

        $response = $this->actingAs($this->adminTU, 'admin')
            ->post(route('admin.master.jurusan.simpan'), $payload);

        $response->assertRedirect(route('admin.master.jurusan'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('jurusan', [
            'kode_jurusan' => $kodeUnik,
            'nama_jurusan' => 'Rekayasa Perangkat Lunak',
            'kuota' => 72,
            'status_aktif' => 1,
        ]);

        $this->assertDatabaseHas('log_aktivitas', [
            'aksi' => 'CREATE',
            'tabel_terkait' => 'jurusan',
        ]);
    }

    public function test_04_validasi_kode_jurusan_duplikat(): void
    {
        $jurusanExisting = Jurusan::first();
        $this->assertNotNull($jurusanExisting);

        $payload = [
            'kode_jurusan' => $jurusanExisting->kode_jurusan, // duplikat
            'nama_jurusan' => 'Program Keahlian Duplikat',
            'kuota' => 50,
            'status_aktif' => 1,
        ];

        $response = $this->actingAs($this->adminTU, 'admin')
            ->post(route('admin.master.jurusan.simpan'), $payload);

        $response->assertSessionHasErrors(['kode_jurusan']);
    }

    public function test_05_validasi_kuota_tidak_boleh_negatif(): void
    {
        $payload = [
            'kode_jurusan' => 'DKV' . mt_rand(10, 99),
            'nama_jurusan' => 'Desain Komunikasi Visual',
            'kuota' => -10, // negatif
            'status_aktif' => 1,
        ];

        $response = $this->actingAs($this->adminTU, 'admin')
            ->post(route('admin.master.jurusan.simpan'), $payload);

        $response->assertSessionHasErrors(['kuota']);
    }

    public function test_06_lihat_detail_program_keahlian_via_json_dan_view(): void
    {
        $jurusan = Jurusan::first();
        $this->assertNotNull($jurusan);

        $response = $this->actingAs($this->adminTU, 'admin')
            ->getJson(route('admin.master.jurusan.detail', $jurusan->id_jurusan));

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'data' => [
                'id_jurusan' => $jurusan->id_jurusan,
                'kode_jurusan' => $jurusan->kode_jurusan,
                'nama_jurusan' => $jurusan->nama_jurusan,
                'kuota' => $jurusan->kuota,
            ],
        ]);
    }

    public function test_07_edit_program_keahlian_berhasil(): void
    {
        $jurusan = Jurusan::create([
            'kode_jurusan' => 'MM' . mt_rand(10, 99),
            'nama_jurusan' => 'Multimedia Kreatif',
            'kuota' => 60,
            'deskripsi' => 'Deskripsi lama',
            'prospek_kerja' => 'Video Editor',
            'status_aktif' => true,
        ]);

        $payload = [
            'kode_jurusan' => $jurusan->kode_jurusan,
            'nama_jurusan' => 'Desain Komunikasi Visual & Animasi',
            'kuota' => 75,
            'deskripsi' => 'Pengembangan animasi 3D, sinematografi, dan desain grafis periklanan.',
            'prospek_kerja' => 'Animator 3D, Video Editor, Graphic Designer, Motion Artist',
            'status_aktif' => 1,
        ];

        $response = $this->actingAs($this->adminTU, 'admin')
            ->post(route('admin.master.jurusan.update', $jurusan->id_jurusan), $payload);

        $response->assertRedirect(route('admin.master.jurusan'));
        $response->assertSessionHas('success');

        $jurusan->refresh();
        $this->assertEquals('Desain Komunikasi Visual & Animasi', $jurusan->nama_jurusan);
        $this->assertEquals(75, $jurusan->kuota);
        $this->assertEquals('Animator 3D, Video Editor, Graphic Designer, Motion Artist', $jurusan->prospek_kerja);
    }

    public function test_08_ubah_kuota_penerimaan_jurusan(): void
    {
        $jurusan = Jurusan::first();
        $this->assertNotNull($jurusan);

        $kuotaBaru = $jurusan->kuota + 10;

        $response = $this->actingAs($this->adminTU, 'admin')
            ->postJson(route('admin.master.jurusan.kuota', $jurusan->id_jurusan), [
                'kuota' => $kuotaBaru,
            ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'kuota' => $kuotaBaru,
        ]);

        $jurusan->refresh();
        $this->assertEquals($kuotaBaru, $jurusan->kuota);
    }

    public function test_09_aktifkan_dan_nonaktifkan_jurusan(): void
    {
        $jurusan = Jurusan::create([
            'kode_jurusan' => 'TEST' . mt_rand(10, 99),
            'nama_jurusan' => 'Jurusan Uji Coba Toggle',
            'kuota' => 36,
            'status_aktif' => true,
        ]);

        // 1. Nonaktifkan
        $response = $this->actingAs($this->adminTU, 'admin')
            ->post(route('admin.master.jurusan.toggle', $jurusan->id_jurusan));

        $response->assertRedirect(route('admin.master.jurusan'));
        $jurusan->refresh();
        $this->assertFalse((bool)$jurusan->status_aktif);

        // 2. Aktifkan kembali
        $response2 = $this->actingAs($this->adminTU, 'admin')
            ->post(route('admin.master.jurusan.toggle', $jurusan->id_jurusan));

        $response2->assertRedirect(route('admin.master.jurusan'));
        $jurusan->refresh();
        $this->assertTrue((bool)$jurusan->status_aktif);
    }

    public function test_10_jurusan_nonaktif_tidak_muncul_sebagai_pilihan_pendaftaran_baru(): void
    {
        $jurusanNonaktif = Jurusan::create([
            'kode_jurusan' => 'OFF' . mt_rand(10, 99),
            'nama_jurusan' => 'Jurusan Khusus Ditutup',
            'kuota' => 30,
            'status_aktif' => false, // Nonaktif
        ]);

        $newUser = User::create([
            'nama' => 'Pendaftar Baru Uji Nonaktif',
            'nisn' => '00' . mt_rand(10000000, 99999999),
            'username' => 'ujinonaktif_' . uniqid(),
            'email' => 'ujinonaktif_' . uniqid() . '@test.com',
            'password' => Hash::make('password123'),
        ]);

        // 1. Di halaman formulir, jurusan nonaktif tidak muncul dalam opsi pilihan
        $responseForm = $this->actingAs($newUser, 'web')
            ->get(route('siswa.formulir'));

        $responseForm->assertStatus(200);
        $responseForm->assertDontSee($jurusanNonaktif->nama_jurusan);

        // 2. Calon siswa dicegah memilih jurusan nonaktif saat menyimpan formulir
        $responseSimpan = $this->actingAs($newUser, 'web')
            ->post(route('siswa.formulir.simpan'), [
                'id_jurusan_1' => $jurusanNonaktif->id_jurusan,
            ]);

        $responseSimpan->assertSessionHasErrors(['id_jurusan_1']);
    }

    public function test_11_jurusan_nonaktif_tetap_terlihat_pada_dashboard_dan_laporan_historis(): void
    {
        $jurusanNonaktif = Jurusan::create([
            'kode_jurusan' => 'HIST' . mt_rand(10, 99),
            'nama_jurusan' => 'Jurusan Arsip Historis',
            'kuota' => 40,
            'status_aktif' => false,
        ]);

        // Dashboard Petugas TU tetap menampilkan seluruh jurusan historis
        $this->actingAs($this->adminTU, 'admin')
            ->get(route('admin.dashboard'))
            ->assertStatus(200)
            ->assertSee($jurusanNonaktif->nama_jurusan);

        // Dashboard Kepala Sekolah tetap memantau seluruh jurusan
        $this->actingAs($this->kepsek, 'admin')
            ->get(route('kepsek.dashboard'))
            ->assertStatus(200)
            ->assertSee($jurusanNonaktif->nama_jurusan);

        // Laporan PPDB Kepala Sekolah tetap mencatat data kuota historis
        $this->actingAs($this->kepsek, 'admin')
            ->get(route('kepsek.laporan'))
            ->assertStatus(200)
            ->assertSee($jurusanNonaktif->nama_jurusan);
    }

    public function test_12_keamanan_data_mencegah_penghapusan_jurusan_yang_sudah_memiliki_pendaftar(): void
    {
        $jurusanWithPendaftar = Jurusan::whereHas('pendaftarPilihan1')->first();
        $this->assertNotNull($jurusanWithPendaftar, 'Harus ada jurusan yang memiliki pendaftar untuk uji keamanan.');

        $response = $this->actingAs($this->adminTU, 'admin')
            ->post(route('admin.master.jurusan.hapus', $jurusanWithPendaftar->id_jurusan));

        $response->assertRedirect(route('admin.master.jurusan'));
        $response->assertSessionHas('error');

        // Pastikan jurusan lama TIDAK terhapus dari database
        $this->assertDatabaseHas('jurusan', [
            'id_jurusan' => $jurusanWithPendaftar->id_jurusan,
            'kode_jurusan' => $jurusanWithPendaftar->kode_jurusan,
        ]);
    }

    public function test_13_jurusan_tanpa_pendaftar_dapat_dihapus(): void
    {
        $jurusanKosong = Jurusan::create([
            'kode_jurusan' => 'DEL' . mt_rand(10, 99),
            'nama_jurusan' => 'Jurusan Sementara Tanpa Pendaftar',
            'kuota' => 30,
            'status_aktif' => true,
        ]);

        $response = $this->actingAs($this->adminTU, 'admin')
            ->post(route('admin.master.jurusan.hapus', $jurusanKosong->id_jurusan));

        $response->assertRedirect(route('admin.master.jurusan'));
        $response->assertSessionHas('success');

        $this->assertDatabaseMissing('jurusan', [
            'id_jurusan' => $jurusanKosong->id_jurusan,
        ]);
    }
}

