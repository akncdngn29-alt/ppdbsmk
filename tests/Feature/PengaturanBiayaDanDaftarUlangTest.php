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
        $tu = Admin::where('role', 'tu')->first() ?: Admin::factory()->create(['role' => 'tu']);
        $kepsek = Admin::where('role', 'kepala_sekolah')->first() ?: Admin::factory()->create(['role' => 'kepala_sekolah']);

        $gelombang = Gelombang::first();
        $this->assertNotNull($gelombang);

        // Petugas TU ditolak (403)
        $responseTU = $this->actingAs($tu, 'admin')
            ->post(route('admin.pengaturan.biaya_pendaftaran'), [
                'gelombang' => [
                    $gelombang->id_gelombang => [
                        'biaya_pendaftaran' => 200000,
                    ],
                ],
            ]);
        $responseTU->assertStatus(403);

        // Kepala Sekolah berwenang mengubah
        $response = $this->actingAs($kepsek, 'admin')
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
        $tu = Admin::where('role', 'tu')->first() ?: Admin::factory()->create(['role' => 'tu']);
        $kepsek = Admin::where('role', 'kepala_sekolah')->first() ?: Admin::factory()->create(['role' => 'kepala_sekolah']);

        // Petugas TU ditolak (403)
        $responseTU = $this->actingAs($tu, 'admin')
            ->post(route('admin.pengaturan.biaya_daftar_ulang'), [
                'biaya_daftar_ulang' => 1750000,
                'keterangan_daftar_ulang' => 'Paket Seragam dan MPLS T.A 2026/2027',
                'instruksi_daftar_ulang' => 'Bawa fotokopi KK ke loket TU.',
            ]);
        $responseTU->assertStatus(403);

        // Kepala Sekolah berwenang mengubah
        $response = $this->actingAs($kepsek, 'admin')
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

    public function test_endpoint_umum_pengaturan_profil_tidak_dapat_mengubah_kredensial_midtrans()
    {
        $tu = Admin::where('role', 'tu')->first() ?: Admin::factory()->create(['role' => 'tu']);
        $pengaturanAwal = PengaturanSekolah::getPengaturan();
        $serverKeyAwal = $pengaturanAwal->midtrans_server_key;

        $response = $this->actingAs($tu, 'admin')
            ->post(route('admin.pengaturan.simpan'), [
                'nama_sekolah' => $pengaturanAwal->nama_sekolah,
                'npsn' => $pengaturanAwal->npsn,
                'ijin_operasional' => $pengaturanAwal->ijin_operasional,
                'alamat' => $pengaturanAwal->alamat,
                'kota' => $pengaturanAwal->kota,
                'provinsi' => $pengaturanAwal->provinsi,
                'no_telp' => $pengaturanAwal->no_telp,
                'email' => $pengaturanAwal->email,
                'website' => $pengaturanAwal->website,
                'nama_kepsek' => $pengaturanAwal->nama_kepsek,
                'nip_kepsek' => $pengaturanAwal->nip_kepsek,
                // Upaya injeksi parameter Midtrans pada endpoint profil umum
                'midtrans_server_key' => 'SB-Mid-server-HACKEDKEY999',
                'midtrans_client_key' => 'SB-Mid-client-HACKEDKEY999',
                'midtrans_is_production' => '1',
            ]);

        $response->assertSessionHas('success');
        $pengaturanFresh = PengaturanSekolah::getPengaturan();
        // Pastikan kredensial Midtrans di basis data sama sekali TIDAK berubah
        $this->assertEquals($serverKeyAwal, $pengaturanFresh->midtrans_server_key);
        $this->assertNotEquals('SB-Mid-server-HACKEDKEY999', $pengaturanFresh->midtrans_server_key);
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

    public function test_siswa_diterima_dapat_membuat_tagihan_snap_daftar_ulang()
    {
        $user = User::first();
        $pendaftar = Pendaftar::where('id_user', $user->id_user)->first();

        $pendaftar->update([
            'status_kelulusan' => 'Diterima',
            'status_daftar_ulang' => 'Belum',
        ]);

        config(['services.midtrans.server_key' => 'SB-Mid-server-TEST-DU']);

        \Illuminate\Support\Facades\Http::fake([
            'https://app.sandbox.midtrans.com/snap/v1/transactions' => \Illuminate\Support\Facades\Http::response([
                'token' => 'SNAP-TOKEN-DU-TEST',
                'redirect_url' => 'https://app.sandbox.midtrans.com/snap/v2/vtweb/SNAP-TOKEN-DU-TEST',
            ], 201),
        ]);

        $response = $this->actingAs($user, 'web')
            ->postJson(route('siswa.daftar_ulang.snap'), [
                'channel' => 'bca_va',
            ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
        ]);
        $this->assertNotNull($response->json('order_id'));
        $this->assertEquals('SNAP-TOKEN-DU-TEST', $response->json('snap_token'));
    }
}
