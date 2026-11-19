<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\LandingController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\SiswaDashboardController;
use App\Http\Controllers\AdminTUController;
use App\Http\Controllers\KepalaSekolahController;
use App\Http\Controllers\DokumenController;
use App\Http\Controllers\MidtransWebhookController;
use App\Http\Controllers\VerifikasiController;
use App\Http\Controllers\JadwalSeleksiController;
use App\Http\Controllers\ManajemenStafController;
use App\Http\Controllers\ImportPendaftarController;
use App\Http\Controllers\BackupDatabaseController;

// 1. Landing Page & Public Widgets
Route::get('/', [LandingController::class, 'index'])->name('landing');
Route::post('/cek-kelulusan', [LandingController::class, 'cekKelulusan'])->name('cek.kelulusan');
Route::post('/check-voucher', [LandingController::class, 'checkVoucher'])->name('check.voucher');

// Webhook Midtrans (Exempt CSRF)
Route::post('/api/midtrans/notification', [MidtransWebhookController::class, 'handle'])->name('midtrans.notification');

// Dokumen Pendaftar Berizin (Private Storage Protection - UU PDP)
Route::get('/dokumen/berkas/{id}', [DokumenController::class, 'viewBerkas'])->name('dokumen.berkas');
Route::get('/dokumen/prestasi/{id}', [DokumenController::class, 'viewPrestasi'])->name('dokumen.prestasi');

// Verifikasi Dokumen Online (Publik — untuk pemindaian QR Code)
Route::get('/verifikasi/kartu-ujian/{token}', [VerifikasiController::class, 'verifikasiKartuUjian'])->name('verifikasi.kartu_ujian');
Route::get('/verifikasi/skl/{token}', [VerifikasiController::class, 'verifikasiSKL'])->name('verifikasi.skl');

// 2. Authentication Routes
Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.post');
Route::get('/daftar', [AuthController::class, 'showRegisterForm'])->name('register');
Route::post('/daftar', [AuthController::class, 'register'])->name('register.post');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
Route::get('/lupa-password', [AuthController::class, 'showForgotPassword'])->name('password.request');
Route::post('/lupa-password', [AuthController::class, 'forgotPassword'])->name('password.email');

Route::post('/auth/quick-login', [AuthController::class, 'quickLogin'])->name('auth.quick_login');
Route::get('/admin/login', [AuthController::class, 'showAdminLoginForm'])->name('admin.login');
Route::post('/admin/login', [AuthController::class, 'adminLogin'])->name('admin.login.post');
Route::post('/admin/logout', [AuthController::class, 'adminLogout'])->name('admin.logout');

// 3. Calon Siswa Routes (Protected)
Route::middleware(['auth'])->prefix('siswa')->name('siswa.')->group(function () {
    Route::get('/dashboard', [SiswaDashboardController::class, 'index'])->name('dashboard');
    Route::get('/formulir', [SiswaDashboardController::class, 'formulir'])->name('formulir');
    Route::post('/formulir', [SiswaDashboardController::class, 'simpanFormulir'])->name('formulir.simpan');
    Route::post('/finalisasi', [SiswaDashboardController::class, 'finalisasi'])->name('finalisasi');
    
    Route::get('/berkas', [SiswaDashboardController::class, 'berkas'])->name('berkas');
    Route::post('/berkas/upload', [SiswaDashboardController::class, 'uploadBerkas'])->name('berkas.upload');
    Route::delete('/berkas/{id}', [SiswaDashboardController::class, 'hapusBerkas'])->name('berkas.hapus');
    Route::post('/prestasi/upload', [SiswaDashboardController::class, 'uploadPrestasi'])->name('prestasi.upload');
    
    Route::get('/pembayaran', [SiswaDashboardController::class, 'pembayaran'])->name('pembayaran');
    Route::post('/pembayaran/tunai', [SiswaDashboardController::class, 'prosesBayarTunai'])->name('pembayaran.tunai');
    Route::post('/pembayaran/snap', [SiswaDashboardController::class, 'createSnapPayment'])->name('pembayaran.snap');
    Route::post('/pembayaran/qris', [SiswaDashboardController::class, 'generateQrisPayment'])->name('pembayaran.qris');
    Route::post('/pembayaran/cek-status', [SiswaDashboardController::class, 'cekStatusMidtrans'])->name('pembayaran.cek-status');
    Route::post('/pembayaran/midtrans-simulasi', [SiswaDashboardController::class, 'prosesBayarMidtransSimulasi'])->name('pembayaran.midtrans');
    
    // Printable / PDF routes
    Route::get('/jadwal', [SiswaDashboardController::class, 'jadwalSiswa'])->name('jadwal');
    Route::get('/cetak/formulir', [SiswaDashboardController::class, 'cetakFormulir'])->name('cetak.formulir');
    Route::get('/cetak/kartu-ujian', [SiswaDashboardController::class, 'cetakKartuUjian'])->name('cetak.kartu_ujian');
    Route::get('/cetak/kwitansi', [SiswaDashboardController::class, 'cetakKwitansi'])->name('cetak.kwitansi');
    Route::get('/cetak/skl', [SiswaDashboardController::class, 'unduhSKL'])->name('cetak.skl');
    
    // Daftar Ulang & Pengaturan Akun
    Route::get('/daftar-ulang', [SiswaDashboardController::class, 'daftarUlang'])->name('daftar_ulang');
    Route::post('/daftar-ulang/konfirmasi', [SiswaDashboardController::class, 'konfirmasiDaftarUlang'])->name('daftar_ulang.konfirmasi');
    Route::post('/daftar-ulang/qris', [SiswaDashboardController::class, 'generateQrisDaftarUlang'])->name('daftar_ulang.qris');
    Route::post('/daftar-ulang/tunai', [SiswaDashboardController::class, 'prosesBayarDaftarUlangTunai'])->name('daftar_ulang.tunai');
    Route::post('/daftar-ulang/cek-status', [SiswaDashboardController::class, 'cekStatusDaftarUlangMidtrans'])->name('daftar_ulang.cek-status');
    Route::get('/pengaturan', [SiswaDashboardController::class, 'pengaturanAkun'])->name('pengaturan');
    Route::post('/pengaturan', [SiswaDashboardController::class, 'simpanPengaturanAkun'])->name('pengaturan.simpan');
    Route::get('/riwayat-aktivitas', [SiswaDashboardController::class, 'riwayatAktivitas'])->name('riwayat_aktivitas');
    Route::get('/bantuan', [SiswaDashboardController::class, 'bantuan'])->name('bantuan');
});

// 4. Petugas TU Routes (Protected)
Route::middleware(['admin.role:tu'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [AdminTUController::class, 'index'])->name('dashboard');
    Route::get('/pendaftar', [AdminTUController::class, 'pendaftar'])->name('pendaftar');
    Route::get('/pendaftar/import', [ImportPendaftarController::class, 'index'])->name('import.index');
    Route::get('/pendaftar/import/template', [ImportPendaftarController::class, 'downloadTemplate'])->name('import.template');
    Route::post('/pendaftar/import', [ImportPendaftarController::class, 'import'])->name('import.proses');
    Route::get('/pendaftar/{id}', [AdminTUController::class, 'detailPendaftar'])->name('pendaftar.detail');
    Route::post('/pendaftar/{id}/verifikasi', [AdminTUController::class, 'verifikasiBerkas'])->name('pendaftar.verifikasi');
    Route::post('/pendaftar/{id}/unlock', [AdminTUController::class, 'unlockFormulir'])->name('pendaftar.unlock');
    Route::post('/berkas/{id}/verifikasi', [AdminTUController::class, 'verifikasiSingleBerkas'])->name('berkas.verifikasi');
    Route::post('/pendaftar/{id}/bayar-tunai', [AdminTUController::class, 'konfirmasiBayarTunai'])->name('pendaftar.bayar_tunai');
    
    Route::get('/seleksi', [AdminTUController::class, 'seleksiPenilaian'])->name('seleksi');
    Route::post('/seleksi/{id}', [AdminTUController::class, 'simpanPenilaian'])->name('seleksi.simpan');
    Route::post('/pendaftar/{id}/alihkan', [AdminTUController::class, 'alihkanJurusan'])->name('pendaftar.alihkan');
    
    Route::get('/master/jurusan', [AdminTUController::class, 'masterJurusan'])->name('master.jurusan');
    Route::post('/master/jurusan', [AdminTUController::class, 'simpanJurusan'])->name('master.jurusan.simpan');
    Route::get('/master/jurusan/{id}', [AdminTUController::class, 'detailJurusan'])->name('master.jurusan.detail');
    Route::post('/master/jurusan/{id}', [AdminTUController::class, 'updateJurusan'])->name('master.jurusan.update');
    Route::put('/master/jurusan/{id}', [AdminTUController::class, 'updateJurusan'])->name('master.jurusan.update.put');
    Route::post('/master/jurusan/{id}/kuota', [AdminTUController::class, 'ubahKuota'])->name('master.jurusan.kuota');
    Route::post('/master/jurusan/{id}/toggle', [AdminTUController::class, 'toggleStatusJurusan'])->name('master.jurusan.toggle');
    Route::delete('/master/jurusan/{id}', [AdminTUController::class, 'hapusJurusan'])->name('master.jurusan.destroy');
    Route::post('/master/jurusan/{id}/hapus', [AdminTUController::class, 'hapusJurusan'])->name('master.jurusan.hapus');
    Route::get('/master/gelombang', [AdminTUController::class, 'masterGelombang'])->name('master.gelombang');
    Route::post('/master/gelombang', [AdminTUController::class, 'simpanGelombang'])->name('master.gelombang.simpan');
    Route::get('/master/voucher', [AdminTUController::class, 'masterVoucher'])->name('master.voucher');
    Route::post('/master/voucher', [AdminTUController::class, 'simpanVoucher'])->name('master.voucher.simpan');
    Route::put('/master/voucher/{id}', [AdminTUController::class, 'updateVoucher'])->name('master.voucher.update');
    Route::post('/master/voucher/{id}', [AdminTUController::class, 'updateVoucher'])->name('master.voucher.update.post');
    Route::post('/master/voucher/{id}/toggle', [AdminTUController::class, 'toggleStatusVoucher'])->name('master.voucher.toggle');
    
    Route::get('/pengumuman', [AdminTUController::class, 'pengumuman'])->name('pengumuman');
    Route::post('/pengumuman', [AdminTUController::class, 'simpanPengumuman'])->name('pengumuman.simpan');
    Route::post('/pengaturan/maintenance', [AdminTUController::class, 'toggleMaintenance'])->name('maintenance.toggle');
    Route::get('/pengaturan', [AdminTUController::class, 'pengaturanSekolah'])->name('pengaturan');
    Route::post('/pengaturan', [AdminTUController::class, 'simpanPengaturanSekolah'])->name('pengaturan.simpan');
    Route::post('/pengaturan/biaya-pendaftaran', [AdminTUController::class, 'simpanBiayaPendaftaran'])->name('pengaturan.biaya_pendaftaran');
    Route::post('/pengaturan/biaya-daftar-ulang', [AdminTUController::class, 'simpanBiayaDaftarUlang'])->name('pengaturan.biaya_daftar_ulang');
    Route::post('/pengaturan/payment-gateway', [AdminTUController::class, 'simpanPaymentGateway'])->name('pengaturan.payment_gateway');
    Route::post('/pengaturan/test-midtrans', [AdminTUController::class, 'testMidtransConnection'])->name('pengaturan.test_midtrans');
    
    // Verifikasi Pembayaran Tunai di Loket TU
    Route::get('/verifikasi-pembayaran-tunai', [AdminTUController::class, 'verifikasiPembayaranTunai'])->name('pembayaran.tunai');
    Route::post('/verifikasi-pembayaran-tunai/{id}/konfirmasi', [AdminTUController::class, 'prosesKonfirmasiBayarTunai'])->name('pembayaran.tunai.konfirmasi');
    Route::post('/verifikasi-pembayaran-tunai/{id}/konfirmasi-daftar-ulang', [AdminTUController::class, 'prosesKonfirmasiBayarDaftarUlangTunai'])->name('pembayaran.daftar_ulang.konfirmasi');
    
    // Cetak dokumen pendaftar oleh admin TU
    Route::get('/pendaftar/{id}/cetak/formulir', [AdminTUController::class, 'cetakFormulirPendaftar'])->name('cetak.formulir');
    Route::get('/pendaftar/{id}/cetak/kartu-ujian', [AdminTUController::class, 'cetakKartuUjianPendaftar'])->name('cetak.kartu_ujian');
    Route::get('/pendaftar/{id}/cetak/skl', [AdminTUController::class, 'cetakSKLPendaftar'])->name('cetak.skl');

    // Riwayat Aktivitas untuk Petugas TU
    Route::get('/riwayat-aktivitas', [AdminTUController::class, 'riwayatAktivitas'])->name('riwayat_aktivitas');

    // Manajemen Jadwal & Ruang Seleksi (Tahap 4)
    Route::get('/jadwal', [JadwalSeleksiController::class, 'index'])->name('jadwal.index');
    Route::get('/jadwal/tambah', [JadwalSeleksiController::class, 'create'])->name('jadwal.create');
    Route::post('/jadwal', [JadwalSeleksiController::class, 'store'])->name('jadwal.store');
    Route::get('/jadwal/{id}/edit', [JadwalSeleksiController::class, 'edit'])->name('jadwal.edit');
    Route::put('/jadwal/{id}', [JadwalSeleksiController::class, 'update'])->name('jadwal.update');
    Route::delete('/jadwal/{id}', [JadwalSeleksiController::class, 'destroy'])->name('jadwal.destroy');

    Route::get('/ruang-seleksi', [JadwalSeleksiController::class, 'manajemenRuang'])->name('ruang.index');
    Route::post('/ruang-seleksi', [JadwalSeleksiController::class, 'tambahRuang'])->name('ruang.store');
    Route::put('/ruang-seleksi/{id}', [JadwalSeleksiController::class, 'updateRuang'])->name('ruang.update');
    Route::delete('/ruang-seleksi/{id}', [JadwalSeleksiController::class, 'hapusRuang'])->name('ruang.destroy');

    Route::get('/jadwal/{id}/penempatan', [JadwalSeleksiController::class, 'penempatanOtomatis'])->name('jadwal.penempatan');
    Route::post('/jadwal/{id}/penempatan-otomatis', [JadwalSeleksiController::class, 'prosesOtomatis'])->name('jadwal.penempatan.otomatis');
    Route::post('/penempatan-manual', [JadwalSeleksiController::class, 'penempatanManual'])->name('jadwal.penempatan.manual');
    Route::delete('/penempatan/{id}', [JadwalSeleksiController::class, 'batalkanPenempatan'])->name('jadwal.penempatan.batal');
    Route::get('/jadwal/{idJadwal}/ruang/{idRuang}/cetak', [JadwalSeleksiController::class, 'cetakDaftarPeserta'])->name('jadwal.cetak');
});

// 4b. Rute Ekspor Data & Rekap Laporan (Dapat diakses Petugas TU & Kepala Sekolah)
Route::middleware(['admin.role:tu,kepala_sekolah'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/ekspor/excel', [AdminTUController::class, 'eksporExcel'])->name('ekspor.excel');
    Route::get('/ekspor/xlsx', [AdminTUController::class, 'eksporXlsx'])->name('ekspor.xlsx');
    Route::get('/ekspor/dapodik', [AdminTUController::class, 'eksporDapodik'])->name('ekspor.dapodik');
    Route::get('/laporan/rekap-pdf', [AdminTUController::class, 'cetakRekapPDF'])->name('laporan.rekap_pdf');

    // Manajemen Akun Staf & Hak Akses (Section 4.B.13)
    Route::get('/staf', [ManajemenStafController::class, 'index'])->name('staf.index');
    Route::post('/staf', [ManajemenStafController::class, 'store'])->name('staf.store');
    Route::put('/staf/{id}', [ManajemenStafController::class, 'update'])->name('staf.update');
    Route::delete('/staf/{id}', [ManajemenStafController::class, 'destroy'])->name('staf.destroy');

    // Pencadangan & Pemulihan Basis Data (Section 4.D.10 & 11)
    Route::get('/backup', [BackupDatabaseController::class, 'index'])->name('backup.index');
    Route::post('/backup', [BackupDatabaseController::class, 'createBackup'])->name('backup.create');
    Route::get('/backup/download/{filename}', [BackupDatabaseController::class, 'download'])->name('backup.download');
    Route::post('/backup/restore/{filename}', [BackupDatabaseController::class, 'restore'])->name('backup.restore');
    Route::delete('/backup/{filename}', [BackupDatabaseController::class, 'destroy'])->name('backup.destroy');
});

// 5. Kepala Sekolah Routes (Protected)
Route::middleware(['admin.role:kepala_sekolah'])->prefix('kepsek')->name('kepsek.')->group(function () {
    Route::get('/dashboard', [KepalaSekolahController::class, 'index'])->name('dashboard');
    Route::get('/keuangan', [KepalaSekolahController::class, 'keuangan'])->name('keuangan');
    Route::get('/kelulusan', [KepalaSekolahController::class, 'kelulusan'])->name('kelulusan');
    Route::post('/kelulusan/{id}', [KepalaSekolahController::class, 'sahkanKelulusan'])->name('kelulusan.sahkan');
    Route::get('/laporan', [KepalaSekolahController::class, 'laporan'])->name('laporan');
    Route::get('/audit-log', [KepalaSekolahController::class, 'auditLog'])->name('audit_log');
    Route::get('/riwayat-aktivitas', [KepalaSekolahController::class, 'auditLog'])->name('riwayat_aktivitas');
    Route::get('/jadwal-seleksi', [JadwalSeleksiController::class, 'monitoringJadwal'])->name('jadwal_monitoring');
});
