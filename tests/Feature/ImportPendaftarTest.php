<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Admin;
use App\Models\User;
use App\Models\Pendaftar;
use App\Models\Jurusan;
use App\Models\Gelombang;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class ImportPendaftarTest extends TestCase
{
    use DatabaseTransactions;

    protected Admin $tu;
    protected User $siswa;
    protected Gelombang $gelombang;

    protected function setUp(): void
    {
        parent::setUp();

        $this->tu = Admin::where('role', 'tu')->first()
            ?: Admin::factory()->create(['role' => 'tu']);

        $this->siswa = User::first()
            ?: User::factory()->create();

        $this->gelombang = Gelombang::where('status_aktif', true)->first()
            ?: Gelombang::first();
    }

    public function test_petugas_tu_dapat_mengakses_halaman_import(): void
    {
        $response = $this->actingAs($this->tu, 'admin')
            ->get(route('admin.import.index'));

        $response->assertStatus(200);
        $response->assertSee('Import Pendaftar Massal');
        $response->assertSee('Unggah Berkas Pendaftar');
    }

    public function test_calon_siswa_dilarang_mengakses_import_pendaftar(): void
    {
        $response = $this->actingAs($this->siswa, 'web')
            ->get(route('admin.import.index'));

        $response->assertRedirect(route('admin.login'));
    }

    public function test_unduh_template_import_berhasil(): void
    {
        $response = $this->actingAs($this->tu, 'admin')
            ->get(route('admin.import.template'));

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'text/csv; charset=UTF-8');
    }

    public function test_import_pendaftar_csv_berhasil(): void
    {
        $jurusan = Jurusan::first();
        $nisnUnik = '009' . rand(1000000, 9999999);
        $emailUnik = 'testimport_' . rand(100, 999) . '@example.com';

        $csvContent = "nama,nisn,email,hp_siswa,tempat_lahir,tanggal_lahir,jenis_kelamin,agama,asal_sekolah,kode_jurusan_1,nama_ayah,nama_ibu,hp_ortu\n"
                    . "Budi Test,{$nisnUnik},{$emailUnik},0812345678,Metro,2010-01-01,Laki-laki,Islam,SMPN 1 Metro,{$jurusan->kode_jurusan},Ayah Budi,Ibu Budi,0812345679\n";

        $file = UploadedFile::fake()->createWithContent('pendaftar.csv', $csvContent);

        $response = $this->actingAs($this->tu, 'admin')
            ->post(route('admin.import.proses'), [
                'id_gelombang' => $this->gelombang->id_gelombang,
                'berkas' => $file,
            ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('user', [
            'nisn' => $nisnUnik,
        ]);

        $this->assertDatabaseHas('pendaftar', [
            'nisn' => $nisnUnik,
            'id_jurusan_1' => $jurusan->id_jurusan,
        ]);

        $this->assertDatabaseHas('log_aktivitas', [
            'aksi' => 'IMPORT_PENDAFTAR',
        ]);
    }

    public function test_import_mendeteksi_dan_melewati_nisn_duplikat(): void
    {
        $userAda = User::first();
        $jurusan = Jurusan::first();

        $csvContent = "nama,nisn,email,hp_siswa,tempat_lahir,tanggal_lahir,jenis_kelamin,agama,asal_sekolah,kode_jurusan_1,nama_ayah,nama_ibu,hp_ortu\n"
                    . "Duplikat,{$userAda->nisn},email_baru@example.com,0812345678,Metro,2010-01-01,Laki-laki,Islam,SMPN 1,{$jurusan->kode_jurusan},Ayah,Ibu,0812345679\n";

        $file = UploadedFile::fake()->createWithContent('duplikat.csv', $csvContent);

        $response = $this->actingAs($this->tu, 'admin')
            ->post(route('admin.import.proses'), [
                'id_gelombang' => $this->gelombang->id_gelombang,
                'berkas' => $file,
            ]);

        $response->assertRedirect();
        $response->assertSessionHas('import_errors');
    }
}
