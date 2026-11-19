<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Admin;
use App\Models\Pendaftar;
use App\Models\Gelombang;
use App\Models\VoucherPendaftaran;
use App\Models\Pembayaran;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class VoucherDiskonPPDBTest extends TestCase
{
    use DatabaseTransactions;

    protected Admin $tu;
    protected User $siswaUser;
    protected Pendaftar $pendaftar;
    protected Gelombang $gelombang;

    protected function setUp(): void
    {
        parent::setUp();

        $this->tu = Admin::where('role', 'tu')->first()
            ?: Admin::create([
                'username' => 'test_admin_tu_' . uniqid(),
                'nama_admin' => 'Admin TU Test',
                'email' => 'admin_tu_test_' . uniqid() . '@smkib.com',
                'password' => bcrypt('password'),
                'role' => 'tu',
            ]);

        $this->gelombang = Gelombang::where('status_aktif', true)->first()
            ?: Gelombang::create([
                'nama_gelombang' => 'Gelombang Uji',
                'tahun_ajaran' => '2026/2027',
                'tgl_buka' => Carbon::today()->subDays(10),
                'tgl_tutup' => Carbon::today()->addDays(30),
                'biaya_pendaftaran' => 100000.00,
                'status_aktif' => true,
            ]);

        $this->siswaUser = User::create([
            'nama' => 'Calon Siswa Voucher Test',
            'username' => 'siswa_v_' . uniqid(),
            'email' => 'siswa_voucher_' . uniqid() . '@smkib.com',
            'nisn' => '99' . str_pad((string) rand(10000000, 99999999), 8, '0', STR_PAD_LEFT),
            'password' => bcrypt('password123'),
        ]);

        $jurusan1 = \App\Models\Jurusan::first() ?: \App\Models\Jurusan::create([
            'kode_jurusan' => 'TKJ',
            'nama_jurusan' => 'Teknik Komputer dan Jaringan',
            'kuota' => 100,
        ]);

        $this->pendaftar = Pendaftar::create([
            'id_user' => $this->siswaUser->id_user,
            'id_gelombang' => $this->gelombang->id_gelombang,
            'id_jurusan_1' => $jurusan1->id_jurusan,
            'no_pendaftaran' => 'REG-' . date('Y') . '-' . rand(10000, 99999),
            'nama' => $this->siswaUser->nama,
            'nisn' => $this->siswaUser->nisn,
            'tempat_lahir' => 'Kota Metro',
            'tanggal_lahir' => '2010-01-01',
            'jenis_kelamin' => 'Laki-laki',
            'hp_siswa' => '081234567890',
            'almt_siswa' => 'Jl. Test No. 123',
            'asal_sekolah' => 'SMP Test',
            'nama_ayah' => 'Ayah Test',
            'nama_ibu' => 'Ibu Test',
            'almt_ortu' => 'Jl. Test No. 123',
            'hp_ortu' => '081234567891',
            'kerja_ayah' => 'Swasta',
            'kerja_ibu' => 'Ibu Rumah Tangga',
            'penghasilan_ortu' => 'Rp 3.000.000',
            'status_pembayaran' => 'Pending',
            'status_verifikasi' => 'Menunggu',
            'tgl_daftar' => now(),
        ]);
    }

    public function test_01_petugas_tu_dapat_melihat_halaman_master_voucher_dan_metrik(): void
    {
        $response = $this->actingAs($this->tu, 'admin')
            ->get(route('admin.master.voucher'));

        $response->assertStatus(200);
        $response->assertSee('Manajemen Voucher Diskon');
        $response->assertSee('Total Voucher');
        $response->assertSee('Voucher Aktif');
        $response->assertSee('Total Terpakai');
        $response->assertSee('Total Potongan Diberikan');
    }

    public function test_02_petugas_tu_dapat_membuat_voucher_diskon_baru(): void
    {
        $kode = 'PROMO' . rand(100, 999);

        $response = $this->actingAs($this->tu, 'admin')
            ->post(route('admin.master.voucher.simpan'), [
                'kode_voucher' => $kode,
                'nama_voucher' => 'Promo Early Bird Pendaftaran',
                'tipe' => 'nominal',
                'nilai_potongan' => 30000,
                'kuota_voucher' => 20,
                'berlaku_mulai' => Carbon::today()->toDateString(),
                'berlaku_sampai' => Carbon::today()->addDays(14)->toDateString(),
                'deskripsi' => 'Diskon khusus pendaftar minggu pertama',
                'status_aktif' => 1,
            ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('voucher_pendaftaran', [
            'kode_voucher' => $kode,
            'nama_voucher' => 'Promo Early Bird Pendaftaran',
            'tipe' => 'nominal',
            'nilai_potongan' => 30000.00,
            'kuota_voucher' => 20,
            'status_aktif' => 1,
        ]);
    }

    public function test_03_petugas_tu_dapat_mengedit_voucher(): void
    {
        $voucher = VoucherPendaftaran::create([
            'kode_voucher' => 'EDIT' . rand(100, 999),
            'nama_voucher' => 'Voucher Sebelum Diedit',
            'tipe' => 'persen',
            'nilai_potongan' => 15.00,
            'kuota_voucher' => 10,
            'kuota_terpakai' => 0,
            'status_aktif' => true,
        ]);

        $response = $this->actingAs($this->tu, 'admin')
            ->put(route('admin.master.voucher.update', $voucher->id_voucher), [
                'kode_voucher' => $voucher->kode_voucher,
                'nama_voucher' => 'Voucher Sesudah Diedit',
                'tipe' => 'persen',
                'nilai_potongan' => 25.00,
                'kuota_voucher' => 30,
                'berlaku_mulai' => Carbon::today()->toDateString(),
                'berlaku_sampai' => Carbon::today()->addDays(20)->toDateString(),
                'deskripsi' => 'Deskripsi terbaru',
                'status_aktif' => 1,
            ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $voucher->refresh();
        $this->assertEquals('Voucher Sesudah Diedit', $voucher->nama_voucher);
        $this->assertEquals(25.00, (float)$voucher->nilai_potongan);
        $this->assertEquals(30, $voucher->kuota_voucher);
    }

    public function test_04_petugas_tu_dapat_mengaktifkan_dan_menonaktifkan_voucher(): void
    {
        $voucher = VoucherPendaftaran::create([
            'kode_voucher' => 'TOGGLE' . rand(100, 999),
            'nama_voucher' => 'Voucher Toggle Test',
            'tipe' => 'nominal',
            'nilai_potongan' => 10000.00,
            'kuota_voucher' => 10,
            'kuota_terpakai' => 0,
            'status_aktif' => true,
        ]);

        // Toggle nonaktif
        $response = $this->actingAs($this->tu, 'admin')
            ->post(route('admin.master.voucher.toggle', $voucher->id_voucher));

        $response->assertRedirect();
        $voucher->refresh();
        $this->assertFalse((bool)$voucher->status_aktif);

        // Toggle aktifkan kembali
        $response2 = $this->actingAs($this->tu, 'admin')
            ->post(route('admin.master.voucher.toggle', $voucher->id_voucher));

        $response2->assertRedirect();
        $voucher->refresh();
        $this->assertTrue((bool)$voucher->status_aktif);
    }

    public function test_05_calon_siswa_dapat_melihat_informasi_promo_di_halaman_pembayaran(): void
    {
        $promoKode = 'PROMOTEST' . rand(100, 999);
        VoucherPendaftaran::create([
            'kode_voucher' => $promoKode,
            'nama_voucher' => 'Beasiswa Super Tahfiz',
            'tipe' => 'nominal',
            'nilai_potongan' => 50000.00,
            'kuota_voucher' => 10,
            'kuota_terpakai' => 0,
            'berlaku_mulai' => Carbon::today()->subDay(),
            'berlaku_sampai' => Carbon::today()->addDays(10),
            'deskripsi' => 'Promo potongan SPP dan pendaftaran untuk hafiz',
            'status_aktif' => true,
        ]);

        $response = $this->actingAs($this->siswaUser, 'web')
            ->get(route('siswa.pembayaran'));

        $response->assertStatus(200);
        $response->assertSee('Informasi Promo');
        $response->assertSee($promoKode);
        $response->assertSee('Beasiswa Super Tahfiz');
        $response->assertSee('Potongan Rp 50.000');
    }

    public function test_06_sistem_memvalidasi_voucher_dan_menghitung_potongan_secara_otomatis(): void
    {
        $kode = 'VALID' . rand(100, 999);
        VoucherPendaftaran::create([
            'kode_voucher' => $kode,
            'nama_voucher' => 'Voucher 50 Persen',
            'tipe' => 'persen',
            'nilai_potongan' => 50.00,
            'kuota_voucher' => 100,
            'kuota_terpakai' => 0,
            'status_aktif' => true,
        ]);

        $response = $this->postJson(route('check.voucher'), [
            'kode_voucher' => $kode,
            'nominal_asli' => 100000,
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'kode_voucher' => $kode,
            'biaya_awal' => 100000,
            'potongan' => 50000,
            'total_bayar' => 50000,
        ]);
    }

    public function test_07_cegah_penggunaan_voucher_tidak_valid(): void
    {
        $response = $this->postJson(route('check.voucher'), [
            'kode_voucher' => 'KODE_PALSU_TIDAK_ADA_9999',
            'nominal_asli' => 100000,
        ]);

        $response->assertStatus(400);
        $response->assertJson([
            'success' => false,
        ]);
    }

    public function test_08_cegah_penggunaan_voucher_nonaktif(): void
    {
        $kode = 'NONAKTIF' . rand(100, 999);
        VoucherPendaftaran::create([
            'kode_voucher' => $kode,
            'tipe' => 'nominal',
            'nilai_potongan' => 20000.00,
            'kuota_voucher' => 50,
            'kuota_terpakai' => 0,
            'status_aktif' => false,
        ]);

        $response = $this->postJson(route('check.voucher'), [
            'kode_voucher' => $kode,
            'nominal_asli' => 100000,
        ]);

        $response->assertStatus(400);
        $response->assertJson([
            'success' => false,
        ]);
        $response->assertSee('tidak aktif');
    }

    public function test_09_cegah_penggunaan_voucher_kedaluwarsa(): void
    {
        $kode = 'EXPIRED' . rand(100, 999);
        VoucherPendaftaran::create([
            'kode_voucher' => $kode,
            'tipe' => 'nominal',
            'nilai_potongan' => 20000.00,
            'kuota_voucher' => 50,
            'kuota_terpakai' => 0,
            'berlaku_mulai' => Carbon::today()->subDays(30),
            'berlaku_sampai' => Carbon::today()->subDay(), // Kemarin (kedaluwarsa)
            'status_aktif' => true,
        ]);

        $response = $this->postJson(route('check.voucher'), [
            'kode_voucher' => $kode,
            'nominal_asli' => 100000,
        ]);

        $response->assertStatus(400);
        $response->assertJson([
            'success' => false,
        ]);
        $response->assertSee('kedaluwarsa');
    }

    public function test_10_cegah_penggunaan_voucher_yang_belum_mulai(): void
    {
        $kode = 'FUTURE' . rand(100, 999);
        VoucherPendaftaran::create([
            'kode_voucher' => $kode,
            'tipe' => 'nominal',
            'nilai_potongan' => 20000.00,
            'kuota_voucher' => 50,
            'kuota_terpakai' => 0,
            'berlaku_mulai' => Carbon::today()->addDays(5), // Masa depan
            'berlaku_sampai' => Carbon::today()->addDays(20),
            'status_aktif' => true,
        ]);

        $response = $this->postJson(route('check.voucher'), [
            'kode_voucher' => $kode,
            'nominal_asli' => 100000,
        ]);

        $response->assertStatus(400);
        $response->assertJson([
            'success' => false,
        ]);
        $response->assertSee('belum dapat digunakan');
    }

    public function test_11_cegah_penggunaan_voucher_melebihi_batas_kuota(): void
    {
        $kode = 'HABIS' . rand(100, 999);
        VoucherPendaftaran::create([
            'kode_voucher' => $kode,
            'tipe' => 'nominal',
            'nilai_potongan' => 20000.00,
            'kuota_voucher' => 5,
            'kuota_terpakai' => 5, // Habis
            'status_aktif' => true,
        ]);

        $response = $this->postJson(route('check.voucher'), [
            'kode_voucher' => $kode,
            'nominal_asli' => 100000,
        ]);

        $response->assertStatus(400);
        $response->assertJson([
            'success' => false,
        ]);
        $response->assertSee('kuota');
    }

    public function test_12_pembayaran_tunai_memperhitungkan_potongan_voucher_dan_menambah_kuota_terpakai(): void
    {
        $kode = 'CASH' . rand(100, 999);
        $voucher = VoucherPendaftaran::create([
            'kode_voucher' => $kode,
            'tipe' => 'nominal',
            'nilai_potongan' => 40000.00,
            'kuota_voucher' => 10,
            'kuota_terpakai' => 0,
            'status_aktif' => true,
        ]);

        $response = $this->actingAs($this->siswaUser, 'web')
            ->post(route('siswa.pembayaran.tunai'), [
                'kode_voucher' => $kode,
            ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $voucher->refresh();
        $this->assertEquals(1, $voucher->kuota_terpakai);

        $this->assertDatabaseHas('pembayaran', [
            'id_pendaftar' => $this->pendaftar->id_pendaftar,
            'id_voucher' => $voucher->id_voucher,
            'nominal' => 100000.00,
            'potongan' => 40000.00,
            'total_bayar' => 60000.00,
            'tipe_bayar' => 'tunai_tu',
        ]);
    }

    public function test_13_pembayaran_midtrans_snap_menolak_voucher_kedaluwarsa(): void
    {
        $kode = 'SNAPEX' . rand(100, 999);
        VoucherPendaftaran::create([
            'kode_voucher' => $kode,
            'tipe' => 'nominal',
            'nilai_potongan' => 50000.00,
            'kuota_voucher' => 10,
            'kuota_terpakai' => 0,
            'berlaku_sampai' => Carbon::yesterday(),
            'status_aktif' => true,
        ]);

        $response = $this->actingAs($this->siswaUser, 'web')
            ->postJson(route('siswa.pembayaran.snap'), [
                'kode_voucher' => $kode,
            ]);

        $response->assertStatus(422);
        $response->assertJson([
            'success' => false,
        ]);
        $response->assertSee('kedaluwarsa');
    }

    public function test_14_petugas_tu_konfirmasi_tunai_tetap_mempertahankan_voucher_dan_potongan(): void
    {
        $kode = 'VERIF' . rand(100, 999);
        $voucher = VoucherPendaftaran::create([
            'kode_voucher' => $kode,
            'tipe' => 'nominal',
            'nilai_potongan' => 50000.00,
            'kuota_voucher' => 10,
            'kuota_terpakai' => 1,
            'status_aktif' => true,
        ]);

        $pembayaran = Pembayaran::create([
            'id_pendaftar' => $this->pendaftar->id_pendaftar,
            'id_gelombang' => $this->gelombang->id_gelombang,
            'id_voucher' => $voucher->id_voucher,
            'kode_transaksi' => Pembayaran::generateKodeTransaksi(),
            'nominal' => 100000.00,
            'potongan' => 50000.00,
            'total_bayar' => 50000.00,
            'tipe_bayar' => 'tunai_tu',
            'metode_pembayaran' => 'Loket TU',
            'status_transaksi' => 'pending',
        ]);

        $response = $this->actingAs($this->tu, 'admin')
            ->post(route('admin.pendaftar.bayar_tunai', $this->pendaftar->id_pendaftar));

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $pembayaran->refresh();
        $this->assertEquals('settlement', $pembayaran->status_transaksi);
        $this->assertEquals(50000.00, (float)$pembayaran->potongan);
        $this->assertEquals(50000.00, (float)$pembayaran->total_bayar);
        $this->assertEquals($voucher->id_voucher, $pembayaran->id_voucher);

        $this->pendaftar->refresh();
        $this->assertEquals('Paid', $this->pendaftar->status_pembayaran);
    }

    public function test_15_kwitansi_pembayaran_menampilkan_biaya_awal_potongan_dan_total_pembayaran(): void
    {
        $kode = 'KWITANSI' . rand(100, 999);
        $voucher = VoucherPendaftaran::create([
            'kode_voucher' => $kode,
            'tipe' => 'nominal',
            'nilai_potongan' => 35000.00,
            'kuota_voucher' => 10,
            'kuota_terpakai' => 1,
            'status_aktif' => true,
        ]);

        Pembayaran::create([
            'id_pendaftar' => $this->pendaftar->id_pendaftar,
            'id_gelombang' => $this->gelombang->id_gelombang,
            'id_voucher' => $voucher->id_voucher,
            'kode_transaksi' => Pembayaran::generateKodeTransaksi(),
            'nominal' => 100000.00,
            'potongan' => 35000.00,
            'total_bayar' => 65000.00,
            'tipe_bayar' => 'tunai_tu',
            'metode_pembayaran' => 'Loket TU',
            'status_transaksi' => 'settlement',
            'waktu_transaksi' => Carbon::now(),
        ]);

        $this->pendaftar->update(['status_pembayaran' => 'Paid']);

        $response = $this->actingAs($this->siswaUser, 'web')
            ->get(route('siswa.cetak.kwitansi'));

        $response->assertStatus(200);
        $response->assertSee('Biaya Formulir Awal');
        $response->assertSee('100.000');
        $response->assertSee($kode);
        $response->assertSee('35.000');
        $response->assertSee('65.000');
    }
}
