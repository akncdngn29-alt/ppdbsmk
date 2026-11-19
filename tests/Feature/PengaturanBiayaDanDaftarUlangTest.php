<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Admin;
use App\Models\User;
use App\Models\Pendaftar;
use App\Models\Gelombang;
use App\Models\PengaturanSekolah;
use App\Models\Pembayaran;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class PengaturanBiayaDanDaftarUlangTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();
    }

    public function test_admin_dapat_mengubah_biaya_pendaftaran_gelombang()
    {
        $admin = Admin::where('role', 'tu')->first();
        if (!$admin) {
            $admin = Admin::factory()->create(['role' => 'tu']);
        }

        $gelombang = Gelombang::first();
        $this->assertNotNull($gelombang);

        $response = $this->actingAs($admin, 'admin')
            ->post(route('admin.pengaturan.biaya_pendaftaran'), [
                'gelombang' => [
                    $gelombang->id_gelombang => [
                        'biaya_pendaftaran' => 200000,
                    ],
                ],
            ]);

        $response->assertRedirect(route('admin.pengaturan', ['tab' => 'biaya_pendaftaran']));
        $this->assertEquals(200000, (float) $gelombang->fresh()->biaya_pendaftaran);
    }

    public function test_admin_dapat_mengubah_biaya_daftar_ulang()
    {
        $admin = Admin::where('role', 'tu')->first();
        if (!$admin) {
            $admin = Admin::factory()->create(['role' => 'tu']);
        }

        $response = $this->actingAs($admin, 'admin')
            ->post(route('admin.pengaturan.biaya_daftar_ulang'), [
                'biaya_daftar_ulang' => 1750000,
                'keterangan_daftar_ulang' => 'Paket Seragam dan MPLS T.A 2026/2027',
                'instruksi_daftar_ulang' => 'Bawa fotokopi KK ke loket TU.',
            ]);

        $response->assertRedirect(route('admin.pengaturan', ['tab' => 'biaya_daftar_ulang']));
        
        $pengaturan = PengaturanSekolah::getPengaturan();
        $this->assertEquals(1750000, (float) $pengaturan->fresh()->biaya_daftar_ulang);
        $this->assertEquals('Paket Seragam dan MPLS T.A 2026/2027', $pengaturan->fresh()->keterangan_daftar_ulang);
    }

    public function test_siswa_diterima_dapat_memilih_opsi_tunai_daftar_ulang()
    {
        $user = User::first();
        $this->assertNotNull($user);

        $pendaftar = Pendaftar::where('id_user', $user->id_user)->first();
        $this->assertNotNull($pendaftar);

        $pendaftar->update([
            'status_kelulusan' => 'Diterima',
            'status_daftar_ulang' => 'Belum',
        ]);

        $response = $this->actingAs($user)
            ->post(route('siswa.daftar_ulang.tunai'));

        $response->assertSessionHas('success');

        $pembayaranDu = Pembayaran::where('id_pendaftar', $pendaftar->id_pendaftar)
            ->where('jenis_pembayaran', 'daftar_ulang')
            ->first();

        $this->assertNotNull($pembayaranDu);
        $this->assertEquals('tunai_tu', $pembayaranDu->tipe_bayar);
        $this->assertEquals('pending', $pembayaranDu->status_transaksi);
        $this->assertStringStartsWith('DU-PPDB-', $pembayaranDu->kode_transaksi);
    }

    public function test_petugas_tu_dapat_mengonfirmasi_tunai_daftar_ulang()
    {
        $admin = Admin::where('role', 'tu')->first();
        if (!$admin) {
            $admin = Admin::factory()->create(['role' => 'tu']);
        }

        $user = User::first();
        $pendaftar = Pendaftar::where('id_user', $user->id_user)->first();

        $pendaftar->update([
            'status_kelulusan' => 'Diterima',
            'status_daftar_ulang' => 'Belum',
        ]);

        $response = $this->actingAs($admin, 'admin')
            ->post(route('admin.pembayaran.daftar_ulang.konfirmasi', $pendaftar->id_pendaftar));

        $response->assertSessionHas('success');
        $this->assertEquals('Selesai', $pendaftar->fresh()->status_daftar_ulang);

        $pembayaranDu = Pembayaran::where('id_pendaftar', $pendaftar->id_pendaftar)
            ->where('jenis_pembayaran', 'daftar_ulang')
            ->first();

        $this->assertNotNull($pembayaranDu);
        $this->assertEquals('settlement', $pembayaranDu->status_transaksi);
    }
}
