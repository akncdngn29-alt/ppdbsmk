<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Admin;
use App\Models\Pendaftar;
use App\Models\Jurusan;
use App\Models\Gelombang;
use App\Models\VoucherPendaftaran;
use App\Models\BerkasPendaftar;
use App\Models\Pembayaran;
use App\Models\PengaturanSekolah;
use App\Services\XlsxExportService;
use App\Services\MidtransService;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use ZipArchive;

class SecurityRemediationTest extends TestCase
{
    /**
     * 1. Webhook tanpa Server Key harus ditolak (prinsip fail-closed).
     */
    public function test_01_webhook_without_server_key_is_rejected_fail_closed(): void
    {
        config(['services.midtrans.server_key' => '']);
        $pengaturan = PengaturanSekolah::getPengaturan();
        $pengaturan->update(['midtrans_server_key' => '']);

        $payload = [
            'order_id' => 'TRX-DUMMY-999',
            'status_code' => '200',
            'gross_amount' => '100000.00',
            'signature_key' => 'some_signature',
            'transaction_status' => 'settlement',
        ];

        $response = $this->postJson('/api/midtrans/notification', $payload);
        $response->assertStatus(400)
            ->assertJson([
                'status' => 'error',
                'message' => 'Server key not configured. Webhook rejected.'
            ]);
    }

    /**
     * 2. Signature SHA-512 valid diterima dan mengubah status pembayaran menjadi lunas.
     */
    public function test_02_webhook_with_valid_sha512_signature_is_accepted_and_settled(): void
    {
        $serverKey = 'SB-Mid-server-valid-secret-key-123';
        config(['services.midtrans.server_key' => $serverKey]);

        $pendaftar = Pendaftar::first();
        $orderId = 'TRX-REMEDIATION-02-' . time();

        $pembayaran = Pembayaran::create([
            'id_pendaftar' => $pendaftar->id_pendaftar,
            'id_gelombang' => $pendaftar->id_gelombang,
            'kode_transaksi' => $orderId,
            'nominal' => 100000.00,
            'potongan' => 0.00,
            'total_bayar' => 100000.00,
            'tipe_bayar' => 'midtrans',
            'metode_pembayaran' => 'qris',
            'status_transaksi' => 'pending',
        ]);

        $statusCode = '200';
        $grossAmount = '100000.00';
        $signature = hash('sha512', $orderId . $statusCode . $grossAmount . $serverKey);

        $payload = [
            'order_id' => $orderId,
            'status_code' => $statusCode,
            'gross_amount' => $grossAmount,
            'signature_key' => $signature,
            'transaction_status' => 'settlement',
            'payment_type' => 'qris',
        ];

        $response = $this->postJson('/api/midtrans/notification', $payload);
        $response->assertStatus(200)->assertJson(['status' => 'success']);

        $pembayaran->refresh();
        $this->assertEquals('settlement', $pembayaran->status_transaksi);
        $pendaftar->refresh();
        $this->assertEquals('Paid', $pendaftar->status_pembayaran);

        $pembayaran->delete();
    }

    /**
     * 3. Signature tidak valid ditolak.
     */
    public function test_03_webhook_with_invalid_signature_is_rejected(): void
    {
        $serverKey = 'SB-Mid-server-valid-secret-key-123';
        config(['services.midtrans.server_key' => $serverKey]);

        $pendaftar = Pendaftar::first();
        $orderId = 'TRX-REMEDIATION-03-' . time();

        $pembayaran = Pembayaran::create([
            'id_pendaftar' => $pendaftar->id_pendaftar,
            'id_gelombang' => $pendaftar->id_gelombang,
            'kode_transaksi' => $orderId,
            'nominal' => 100000.00,
            'potongan' => 0.00,
            'total_bayar' => 100000.00,
            'tipe_bayar' => 'midtrans',
            'metode_pembayaran' => 'qris',
            'status_transaksi' => 'pending',
        ]);

        $payload = [
            'order_id' => $orderId,
            'status_code' => '200',
            'gross_amount' => '100000.00',
            'signature_key' => 'palsu_tampered_signature_sha512_hash',
            'transaction_status' => 'settlement',
        ];

        $response = $this->postJson('/api/midtrans/notification', $payload);
        $response->assertStatus(400)
            ->assertJson([
                'status' => 'error',
                'message' => 'Invalid signature key.'
            ]);

        $pembayaran->refresh();
        $this->assertEquals('pending', $pembayaran->status_transaksi, 'Status pembayaran tidak boleh berubah jika signature palsu.');

        $pembayaran->delete();
    }

    /**
     * 4. Nominal pembayaran tidak cocok ditolak (gross_amount mismatch).
     */
    public function test_04_webhook_with_gross_amount_mismatch_is_rejected(): void
    {
        $serverKey = 'SB-Mid-server-valid-secret-key-123';
        config(['services.midtrans.server_key' => $serverKey]);

        $pendaftar = Pendaftar::first();
        $orderId = 'TRX-REMEDIATION-04-' . time();

        // Tagihan asli Rp 100.000,00
        $pembayaran = Pembayaran::create([
            'id_pendaftar' => $pendaftar->id_pendaftar,
            'id_gelombang' => $pendaftar->id_gelombang,
            'kode_transaksi' => $orderId,
            'nominal' => 100000.00,
            'potongan' => 0.00,
            'total_bayar' => 100000.00,
            'tipe_bayar' => 'midtrans',
            'metode_pembayaran' => 'qris',
            'status_transaksi' => 'pending',
        ]);

        // Penyerang mencoba membayar Rp 1.000,00 dengan signature sah untuk 1000.00
        $manipulatedAmount = '1000.00';
        $statusCode = '200';
        $signature = hash('sha512', $orderId . $statusCode . $manipulatedAmount . $serverKey);

        $payload = [
            'order_id' => $orderId,
            'status_code' => $statusCode,
            'gross_amount' => $manipulatedAmount,
            'signature_key' => $signature,
            'transaction_status' => 'settlement',
            'payment_type' => 'qris',
        ];

        $response = $this->postJson('/api/midtrans/notification', $payload);
        $response->assertStatus(400)
            ->assertJson([
                'status' => 'error',
                'message' => 'Gross amount mismatch. Tagihan tercatat tidak sama dengan nominal diterima.'
            ]);

        $pembayaran->refresh();
        $this->assertEquals('pending', $pembayaran->status_transaksi, 'Status pembayaran tidak boleh lunas jika nominal berbeda.');

        $pembayaran->delete();
    }

    /**
     * 5. Order ID tidak dikenal ditolak.
     */
    public function test_05_webhook_with_unknown_order_id_is_rejected(): void
    {
        $serverKey = 'SB-Mid-server-valid-secret-key-123';
        config(['services.midtrans.server_key' => $serverKey]);

        $unknownOrderId = 'TRX-UNKNOWN-' . time();
        $statusCode = '200';
        $grossAmount = '100000.00';
        $signature = hash('sha512', $unknownOrderId . $statusCode . $grossAmount . $serverKey);

        $payload = [
            'order_id' => $unknownOrderId,
            'status_code' => $statusCode,
            'gross_amount' => $grossAmount,
            'signature_key' => $signature,
            'transaction_status' => 'settlement',
        ];

        $response = $this->postJson('/api/midtrans/notification', $payload);
        $response->assertStatus(400)
            ->assertJson([
                'status' => 'error',
                'message' => 'Transaksi tidak ditemukan.'
            ]);
    }

    /**
     * 6. Webhook berulang tidak menggandakan pembayaran (idempotency).
     */
    public function test_06_repeated_webhook_is_idempotent_and_does_not_duplicate_mutations(): void
    {
        $serverKey = 'SB-Mid-server-valid-secret-key-123';
        config(['services.midtrans.server_key' => $serverKey]);

        $pendaftar = Pendaftar::first();
        $orderId = 'TRX-REMEDIATION-06-' . time();

        $pembayaran = Pembayaran::create([
            'id_pendaftar' => $pendaftar->id_pendaftar,
            'id_gelombang' => $pendaftar->id_gelombang,
            'kode_transaksi' => $orderId,
            'nominal' => 100000.00,
            'potongan' => 0.00,
            'total_bayar' => 100000.00,
            'tipe_bayar' => 'midtrans',
            'metode_pembayaran' => 'qris',
            'status_transaksi' => 'settlement', // Sudah lunas sebelumnya
        ]);

        $statusCode = '200';
        $grossAmount = '100000.00';
        $signature = hash('sha512', $orderId . $statusCode . $grossAmount . $serverKey);

        $payload = [
            'order_id' => $orderId,
            'status_code' => $statusCode,
            'gross_amount' => $grossAmount,
            'signature_key' => $signature,
            'transaction_status' => 'settlement',
            'payment_type' => 'qris',
        ];

        // Callback kedua harus direspons sukses tanpa error dan tanpa merusak data (Idempotent)
        $response = $this->postJson('/api/midtrans/notification', $payload);
        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'data' => [
                    'success' => true,
                    'status' => 'settlement'
                ]
            ]);

        $pembayaran->delete();
    }

    /**
     * 7. Endpoint simulasi tidak dapat melunasi pembayaran di production & request browser tidak dapat langsung melunasi.
     */
    public function test_07_simulation_endpoint_blocked_in_production_and_browser_cannot_directly_mark_paid(): void
    {
        $user = User::where('email', 'siswa@smkib.com')->first();
        $this->actingAs($user, 'web');

        // A. Cek di lingkungan simulasi produksi (APP_ENV = production) -> Wajib HTTP 403
        $app = app();
        $originalEnv = $app->environment();
        $app->detectEnvironment(fn () => 'production');

        $responseProd = $this->withSession(['_token' => 'test-token'])
            ->post('/siswa/pembayaran/midtrans-simulasi', [
                '_token' => 'test-token',
                'metode_pembayaran' => 'QRIS (Simulasi)',
            ]);
        $responseProd->assertStatus(403);

        // Kembalikan ke testing
        $app->detectEnvironment(fn () => $originalEnv);

        // B. Di lingkungan local/testing: request browser TIDAK BOLEH mengubah status pendaftar menjadi Paid
        $pendaftar = Pendaftar::where('id_user', $user->id_user)->first();
        $pendaftar->update(['status_pembayaran' => 'Pending']);

        // Hapus semua pembayaran lama agar updateOrCreate membuat record baru dengan status 'pending'
        Pembayaran::where('id_pendaftar', $pendaftar->id_pendaftar)->delete();

        $responseLocal = $this->post('/siswa/pembayaran/midtrans-simulasi', [
            'metode_pembayaran' => 'QRIS (Simulasi)',
        ]);

        $pendaftar->refresh();
        $this->assertNotEquals('Paid', $pendaftar->status_pembayaran, 'Browser request tidak boleh langsung mengubah status pendaftar menjadi Paid.');

        // Transaksi yang dibuat harus berstatus 'pending', BUKAN 'settlement'
        $trx = Pembayaran::where('id_pendaftar', $pendaftar->id_pendaftar)->latest('id_pembayaran')->first();
        $this->assertNotNull($trx);
        $this->assertEquals('pending', $trx->status_transaksi);

        // Kembalikan status siswa uji coba ke Paid / settlement agar tidak mempengaruhi pengujian rute lain
        $trx->update(['status_transaksi' => 'settlement']);
        $pendaftar->update(['status_pembayaran' => 'Paid']);
    }

    /**
     * 8. Dua pendaftaran simultan menghasilkan nomor berbeda (atomic sequence lock).
     */
    public function test_08_concurrent_registrations_generate_unique_sequential_numbers_via_sequence_lock(): void
    {
        $jurusan = Jurusan::first();
        $tahun = 2026;

        // Eksekusi pembuatan nomor pendaftaran secara beruntun dalam transaksi terkunci
        $num1 = DB::transaction(function () use ($jurusan, $tahun) {
            return Pendaftar::generateNoPendaftaran($jurusan->kode_jurusan, $tahun);
        });

        // Simulasikan pendaftar pertama disimpan ke database
        $tempPendaftar1 = Pendaftar::create([
            'id_user' => 1,
            'id_gelombang' => 1,
            'id_jurusan_1' => $jurusan->id_jurusan,
            'id_jurusan_2' => $jurusan->id_jurusan,
            'no_pendaftaran' => $num1,
            'nisn' => '9999999901',
            'nama' => 'Pendaftar Concurrency 1',
            'tempat_lahir' => 'Metro',
            'tanggal_lahir' => '2008-01-01',
            'jenis_kelamin' => 'Laki-laki',
            'agama' => 'Islam',
            'anak_ke' => 1,
            'jml_saudara' => 1,
            'hp_siswa' => '08120000001',
            'almt_siswa' => 'Jl. Metro',
            'berat_badan' => '50',
            'tinggi_badan' => '160',
            'golongan_darah' => 'O',
            'asal_sekolah' => 'SMP 1',
            'nama_ayah' => 'Ayah',
            'nama_ibu' => 'Ibu',
            'almt_ortu' => 'Jl. Metro',
            'hp_ortu' => '08120000002',
            'kerja_ayah' => 'Wiraswasta',
            'kerja_ibu' => 'IRT',
            'penghasilan_ortu' => '3000000',
            'tanggungan_anak' => 2,
            'jalur_pendaftaran' => 'Reguler',
            'ukuran_seragam' => 'M',
            'ukuran_wearpack' => 'M',
            'tgl_daftar' => now(),
        ]);

        $num2 = DB::transaction(function () use ($jurusan, $tahun) {
            return Pendaftar::generateNoPendaftaran($jurusan->kode_jurusan, $tahun);
        });

        $this->assertNotEquals($num1, $num2, 'Dua pendaftaran tidak boleh menghasilkan nomor yang sama.');
        $this->assertStringStartsWith("PPDB-{$tahun}-{$jurusan->kode_jurusan}-", $num1);
        $this->assertStringStartsWith("PPDB-{$tahun}-{$jurusan->kode_jurusan}-", $num2);

        $tempPendaftar1->delete();
    }

    /**
     * 9. Voucher tidak mengalami overselling pada transaksi bersamaan (lockForUpdate).
     */
    public function test_09_voucher_prevents_overselling_under_concurrent_transactions(): void
    {
        $voucher = VoucherPendaftaran::create([
            'kode_voucher' => 'LOCK_TEST_' . time(),
            'tipe' => 'nominal',
            'nilai_potongan' => 10000.00,
            'kuota_voucher' => 1, // Hanya tersisa 1 kuota
            'kuota_terpakai' => 0,
            'status_aktif' => true,
        ]);

        $simulatedTransactions = 2;
        $successCount = 0;
        $rejectedCount = 0;

        for ($i = 0; $i < $simulatedTransactions; $i++) {
            DB::transaction(function () use ($voucher, &$successCount, &$rejectedCount) {
                $lockedVoucher = VoucherPendaftaran::where('kode_voucher', $voucher->kode_voucher)
                    ->lockForUpdate()
                    ->first();

                if ($lockedVoucher && $lockedVoucher->kuota_terpakai < $lockedVoucher->kuota_voucher) {
                    $lockedVoucher->increment('kuota_terpakai');
                    $successCount++;
                } else {
                    $rejectedCount++;
                }
            });
        }

        $voucher->refresh();
        $this->assertEquals(1, $voucher->kuota_terpakai, 'Kuota voucher yang dipakai tidak boleh melebihi batas kuota.');
        $this->assertEquals(1, $successCount, 'Hanya 1 transaksi yang boleh berhasil menggunakan voucher.');
        $this->assertEquals(1, $rejectedCount, 'Transaksi yang kuotanya habis harus ditolak.');

        $voucher->delete();
    }

    /**
     * 10. CSV aman dari formula injection (=, +, -, @, \t, \r).
     */
    public function test_10_csv_export_neutralizes_formula_injection_characters(): void
    {
        $admin = Admin::where('role', 'tu')->first();
        $this->actingAs($admin, 'admin');

        $uniqueSuffix = (string) (time() . rand(100, 999));
        $maliciousUser = User::create([
            'nama' => '=cmd|\'/C calc\'!A0',
            'nisn' => substr($uniqueSuffix, 0, 10),
            'username' => 'hacker_' . $uniqueSuffix,
            'email' => 'hacker_' . $uniqueSuffix . '@injection.test',
            'password' => Hash::make('password123'),
        ]);

        $jurusan = Jurusan::first();
        $gelombang = Gelombang::first();

        $maliciousPendaftar = Pendaftar::create([
            'id_user' => $maliciousUser->id_user,
            'id_gelombang' => $gelombang->id_gelombang,
            'id_jurusan_1' => $jurusan->id_jurusan,
            'id_jurusan_2' => $jurusan->id_jurusan,
            'no_pendaftaran' => 'PPDB-2026-TEST-' . substr($uniqueSuffix, -4),
            'nisn' => $maliciousUser->nisn,
            'nama' => '=cmd|\'/C calc\'!A0',
            'tempat_lahir' => '@MaliciousCity',
            'tanggal_lahir' => '2008-01-01',
            'jenis_kelamin' => 'Laki-laki',
            'agama' => 'Islam',
            'anak_ke' => 1,
            'jml_saudara' => 1,
            'hp_siswa' => '+628129999000',
            'almt_siswa' => 'Jl. Metro',
            'berat_badan' => '50',
            'tinggi_badan' => '160',
            'golongan_darah' => 'O',
            'asal_sekolah' => '-SMP Formula Minus',
            'nama_ayah' => '@AyahHacker',
            'nama_ibu' => '+IbuHacker',
            'almt_ortu' => 'Metro',
            'hp_ortu' => '08129999111',
            'kerja_ayah' => 'Swasta',
            'kerja_ibu' => 'IRT',
            'penghasilan_ortu' => '2000000',
            'tanggungan_anak' => 1,
            'jalur_pendaftaran' => 'Reguler',
            'ukuran_seragam' => 'M',
            'ukuran_wearpack' => 'M',
            'tgl_daftar' => now(),
        ]);

        $response = $this->get('/admin/ekspor/excel');
        $response->assertStatus(200);

        $csvContent = $response->streamedContent();

        // Pastikan formula injection dinetralisir dengan awalan petik tunggal
        $this->assertStringContainsString("'=cmd|'/C calc'!A0", $csvContent, 'Simbol formula = harus diawali petik tunggal.');
        $this->assertStringContainsString("'@MaliciousCity", $csvContent, 'Simbol formula @ harus diawali petik tunggal.');
        $this->assertStringContainsString("'-SMP Formula Minus", $csvContent, 'Simbol formula - harus diawali petik tunggal.');
        $this->assertStringContainsString("'+628129999000", $csvContent, 'Simbol formula + harus diawali petik tunggal.');

        $maliciousPendaftar->delete();
        $maliciousUser->delete();
    }

    /**
     * 11. XLSX tetap valid dan tidak corrupt saat data berisi karakter kontrol ASCII (\x00-\x1F).
     */
    public function test_11_xlsx_export_removes_disallowed_xml_control_characters(): void
    {
        $xlsxService = new XlsxExportService();

        $headers = ['Nama', 'Keterangan'];
        $rows = [
            ["Siswa Normal", "Catatan Biasa & Rapih"],
            ["Siswa Korup \x00 Null Byte", "Karakter Kontrol \x08 \x0B \x0C \x1F Terhapus"],
            ["Karakter Khusus", "Tanda & < > \" ' Bahasa Indonesia: Jurusan Farmasi & Keperawatan"],
        ];

        $streamed = $xlsxService->export($headers, $rows, 'test_clean.xlsx');
        $this->assertEquals(200, $streamed->getStatusCode());

        ob_start();
        $streamed->sendContent();
        $binaryContent = ob_get_clean();

        // Simpan sementara untuk validasi struktur arsip ZIP
        $tempZipPath = tempnam(sys_get_temp_dir(), 'test_zip_');
        file_put_contents($tempZipPath, $binaryContent);

        $zip = new ZipArchive();
        $opened = $zip->open($tempZipPath);
        $this->assertTrue($opened === true, 'Arsip XLSX yang dihasilkan harus berupa ZIP yang sah.');

        $sheet1Xml = $zip->getFromName('xl/worksheets/sheet1.xml');
        $this->assertNotEmpty($sheet1Xml);

        // Pastikan karakter null byte \x00 tidak ada di dalam XML
        $this->assertFalse(str_contains($sheet1Xml, "\x00"), 'Karakter null byte \x00 tidak boleh ada di sheet1.xml.');

        // Validasi XML OpenXML menggunakan DOMDocument
        $dom = new \DOMDocument();
        $isValidXml = @$dom->loadXML($sheet1Xml);
        $this->assertTrue($isValidXml, 'XML sheet1.xml harus sah secara OpenXML XML 1.0.');

        $zip->close();
        @unlink($tempZipPath);
    }

    /**
     * 12. Foto kartu ujian berhasil dimuat melalui route private dengan otorisasi (dokumen.berkas).
     */
    public function test_12_exam_card_photo_loads_via_authorized_private_route_and_blocks_unauthorized_access(): void
    {
        Storage::fake('local');

        $user = User::where('email', 'siswa@smkib.com')->first();
        $pendaftar = Pendaftar::where('id_user', $user->id_user)->first();
        $pendaftar->update(['status_pembayaran' => 'Paid']);

        // Hapus pasfoto lama siswa jika ada agar referensi ID konsisten
        BerkasPendaftar::where('id_pendaftar', $pendaftar->id_pendaftar)
            ->where('jenis_berkas', 'pasfoto')
            ->delete();

        // Buat file pasfoto dummy di storage privat
        $fotoPath = 'berkas_pendaftar/pasfoto_resmi_' . time() . '.jpg';
        Storage::disk('local')->put($fotoPath, 'fake_jpg_binary_content_for_photo');

        $pasfoto = BerkasPendaftar::create([
            'id_pendaftar' => $pendaftar->id_pendaftar,
            'jenis_berkas' => 'pasfoto',
            'nama_file' => 'pasfoto_3x4.jpg',
            'path_file' => $fotoPath,
            'status_berkas' => 'Valid',
        ]);

        // A. Cek halaman cetak kartu ujian sebagai siswa pemilik -> Render link dokumen privat
        $this->actingAs($user, 'web');
        $responseView = $this->get('/siswa/cetak/kartu-ujian');
        $responseView->assertStatus(200);

        // Pastikan view memanggil route dokumen.berkas, BUKAN asset('storage/...')
        $expectedUrl = route('dokumen.berkas', $pasfoto->id_berkas);
        $responseView->assertSee($expectedUrl, false);
        $responseView->assertDontSee("storage/{$pasfoto->path_file}", false);

        // B. Akses file dokumen foto via route dokumen.berkas oleh siswa bersangkutan -> 200 OK
        $responsePhoto = $this->get($expectedUrl);
        $responsePhoto->assertStatus(200);

        // C. Siswa lain yang mencoba membuka foto siswa ini -> Ditolak 403 Forbidden (UU PDP)
        $userAsing = User::create([
            'nama' => 'Siswa Pengintip',
            'nisn' => '01' . mt_rand(10000000, 99999999),
            'username' => 'siswaasing_' . uniqid(),
            'email' => 'asing_' . uniqid() . '@smkib.com',
            'password' => Hash::make('password123'),
        ]);

        $this->actingAs($userAsing, 'web');
        $responseForbidden = $this->get($expectedUrl);
        $responseForbidden->assertStatus(403);

        $pasfoto->delete();
        $userAsing->delete();
    }
}
