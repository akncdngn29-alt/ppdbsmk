<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\LandingController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\SiswaDashboardController;
use App\Http\Controllers\AdminTUController;
use App\Http\Controllers\KepalaSekolahController;
use App\Http\Controllers\DokumenController;
use App\Http\Controllers\MidtransWebhookController;

// 1. Landing Page & Public Widgets
Route::get('/', [LandingController::class, 'index'])->name('landing');
Route::post('/cek-kelulusan', [LandingController::class, 'cekKelulusan'])->name('cek.kelulusan');
Route::post('/check-voucher', [LandingController::class, 'checkVoucher'])->name('check.voucher');

// Webhook Midtrans (Exempt CSRF)
Route::post('/api/midtrans/notification', [MidtransWebhookController::class, 'handle'])->name('midtrans.notification');

// Dokumen Pendaftar Berizin (Private Storage Protection - UU PDP)
Route::get('/dokumen/berkas/{id}', [DokumenController::class, 'viewBerkas'])->name('dokumen.berkas');
Route::get('/dokumen/prestasi/{id}', [DokumenController::class, 'viewPrestasi'])->name('dokumen.prestasi');

// 2. Authentication Routes
Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.post');
Route::get('/daftar', [AuthController::class, 'showRegisterForm'])->name('register');
Route::post('/daftar', [AuthController::class, 'register'])->name('register.post');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
Route::get('/lupa-password', [AuthController::class, 'showForgotPassword'])->name('password.request');
Route::post('/lupa-password', [AuthController::class, 'forgotPassword'])->name('password.email');

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
    Route::post('/prestasi/upload', [SiswaDashboardController::class, 'uploadPrestasi'])->name('prestasi.upload');
    
    Route::get('/pembayaran', [SiswaDashboardController::class, 'pembayaran'])->name('pembayaran');
    Route::post('/pembayaran/tunai', [SiswaDashboardController::class, 'prosesBayarTunai'])->name('pembayaran.tunai');
    Route::post('/pembayaran/midtrans-simulasi', [SiswaDashboardController::class, 'prosesBayarMidtransSimulasi'])->name('pembayaran.midtrans');
    
    // Printable / PDF routes
    Route::get('/cetak/formulir', [SiswaDashboardController::class, 'cetakFormulir'])->name('cetak.formulir');
    Route::get('/cetak/kartu-ujian', [SiswaDashboardController::class, 'cetakKartuUjian'])->name('cetak.kartu_ujian');
    Route::get('/cetak/kwitansi', [SiswaDashboardController::class, 'cetakKwitansi'])->name('cetak.kwitansi');
    Route::get('/cetak/skl', [SiswaDashboardController::class, 'unduhSKL'])->name('cetak.skl');
    
    // Daftar Ulang & Pengaturan Akun
    Route::get('/daftar-ulang', [SiswaDashboardController::class, 'daftarUlang'])->name('daftar_ulang');
    Route::post('/daftar-ulang/konfirmasi', [SiswaDashboardController::class, 'konfirmasiDaftarUlang'])->name('daftar_ulang.konfirmasi');
    Route::get('/pengaturan', [SiswaDashboardController::class, 'pengaturanAkun'])->name('pengaturan');
    Route::post('/pengaturan', [SiswaDashboardController::class, 'simpanPengaturanAkun'])->name('pengaturan.simpan');
});

// 4. Petugas TU Routes (Protected)
Route::middleware(['admin.role:tu'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [AdminTUController::class, 'index'])->name('dashboard');
    Route::get('/pendaftar', [AdminTUController::class, 'pendaftar'])->name('pendaftar');
    Route::get('/pendaftar/{id}', [AdminTUController::class, 'detailPendaftar'])->name('pendaftar.detail');
    Route::post('/pendaftar/{id}/verifikasi', [AdminTUController::class, 'verifikasiBerkas'])->name('pendaftar.verifikasi');
    Route::post('/berkas/{id}/verifikasi', [AdminTUController::class, 'verifikasiSingleBerkas'])->name('berkas.verifikasi');
    Route::post('/pendaftar/{id}/bayar-tunai', [AdminTUController::class, 'konfirmasiBayarTunai'])->name('pendaftar.bayar_tunai');
    
    Route::get('/seleksi', [AdminTUController::class, 'seleksiPenilaian'])->name('seleksi');
    Route::post('/seleksi/{id}', [AdminTUController::class, 'simpanPenilaian'])->name('seleksi.simpan');
    Route::post('/pendaftar/{id}/alihkan', [AdminTUController::class, 'alihkanJurusan'])->name('pendaftar.alihkan');
    
    Route::get('/master/jurusan', [AdminTUController::class, 'masterJurusan'])->name('master.jurusan');
    Route::post('/master/jurusan/{id}', [AdminTUController::class, 'updateJurusan'])->name('master.jurusan.update');
    Route::get('/master/gelombang', [AdminTUController::class, 'masterGelombang'])->name('master.gelombang');
    Route::post('/master/gelombang', [AdminTUController::class, 'simpanGelombang'])->name('master.gelombang.simpan');
    Route::get('/master/voucher', [AdminTUController::class, 'masterVoucher'])->name('master.voucher');
    Route::post('/master/voucher', [AdminTUController::class, 'simpanVoucher'])->name('master.voucher.simpan');
    
    Route::get('/pengumuman', [AdminTUController::class, 'pengumuman'])->name('pengumuman');
    Route::post('/pengumuman', [AdminTUController::class, 'simpanPengumuman'])->name('pengumuman.simpan');
    Route::post('/pengaturan/maintenance', [AdminTUController::class, 'toggleMaintenance'])->name('maintenance.toggle');
    Route::get('/pengaturan', [AdminTUController::class, 'pengaturanSekolah'])->name('pengaturan');
    Route::post('/pengaturan', [AdminTUController::class, 'simpanPengaturanSekolah'])->name('pengaturan.simpan');
    
    Route::get('/ekspor/excel', [AdminTUController::class, 'eksporExcel'])->name('ekspor.excel');
    Route::get('/ekspor/xlsx', [AdminTUController::class, 'eksporXlsx'])->name('ekspor.xlsx');
    Route::get('/ekspor/dapodik', [AdminTUController::class, 'eksporDapodik'])->name('ekspor.dapodik');
    Route::get('/laporan/rekap-pdf', [AdminTUController::class, 'cetakRekapPDF'])->name('laporan.rekap_pdf');
});

// 5. Kepala Sekolah Routes (Protected)
Route::middleware(['admin.role:kepala_sekolah'])->prefix('kepsek')->name('kepsek.')->group(function () {
    Route::get('/dashboard', [KepalaSekolahController::class, 'index'])->name('dashboard');
    Route::get('/kelulusan', [KepalaSekolahController::class, 'kelulusan'])->name('kelulusan');
    Route::post('/kelulusan/{id}', [KepalaSekolahController::class, 'sahkanKelulusan'])->name('kelulusan.sahkan');
    Route::get('/laporan', [KepalaSekolahController::class, 'laporan'])->name('laporan');
    Route::get('/audit-log', [KepalaSekolahController::class, 'auditLog'])->name('audit_log');
});
