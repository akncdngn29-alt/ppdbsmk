<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Admin;
use App\Models\User;
use Illuminate\Support\Facades\Storage;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class BackupDatabaseTest extends TestCase
{
    use DatabaseTransactions;

    protected Admin $tu;
    protected User $siswa;

    protected function setUp(): void
    {
        parent::setUp();

        $this->tu = Admin::where('role', 'tu')->first()
            ?: Admin::factory()->create(['role' => 'tu']);

        $this->siswa = User::first()
            ?: User::factory()->create();
    }

    public function test_admin_dapat_melihat_halaman_backup(): void
    {
        $response = $this->actingAs($this->tu, 'admin')
            ->get(route('admin.backup.index'));

        $response->assertStatus(200);
        $response->assertSee('Pencadangan Basis Data');
        $response->assertSee('Buat Backup Sekarang');
    }

    public function test_calon_siswa_dilarang_mengakses_halaman_backup(): void
    {
        $response = $this->actingAs($this->siswa, 'web')
            ->get(route('admin.backup.index'));

        $response->assertRedirect(route('admin.login'));
    }

    public function test_pembuatan_backup_berhasil_dan_tercatat_di_log(): void
    {
        $response = $this->actingAs($this->tu, 'admin')
            ->post(route('admin.backup.create'));

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('log_aktivitas', [
            'id_admin' => $this->tu->id_admin,
            'aksi' => 'BACKUP_DATABASE',
        ]);
    }

    public function test_command_artisan_db_backup_berhasil(): void
    {
        $this->artisan('db:backup', ['--filename' => 'test_cli_backup.sql'])
            ->assertSuccessful();

        $this->assertTrue(Storage::disk('local')->exists('backups/test_cli_backup.sql') || Storage::exists('backups/test_cli_backup.sql'));

        // Bersihkan file uji
        Storage::disk('local')->delete('backups/test_cli_backup.sql');
        Storage::delete('backups/test_cli_backup.sql');
    }
}
