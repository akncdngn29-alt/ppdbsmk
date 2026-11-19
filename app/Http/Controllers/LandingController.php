<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Jurusan;
use App\Models\Gelombang;
use App\Models\Pengumuman;
use App\Models\PengaturanSekolah;
use App\Models\Pendaftar;
use App\Models\VoucherPendaftaran;
use Carbon\Carbon;

class LandingController extends Controller
{
    public function index()
    {
        $pengaturan = PengaturanSekolah::getPengaturan();
        $banner = Pengumuman::getActiveBanner();
        $jurusan = Jurusan::where('status_aktif', true)->get();
        $gelombangAktif = Gelombang::where('status_aktif', true)
            ->where('tgl_buka', '<=', Carbon::today())
            ->where('tgl_tutup', '>=', Carbon::today())
            ->first() ?: Gelombang::where('status_aktif', true)->first();

        $totalPendaftar = Pendaftar::count();
        $totalDiterima = Pendaftar::where('status_kelulusan', 'Diterima')->count();

        $ekskul = [
            ['nama' => 'Tilawati Al-Qur\'an', 'icon' => 'book-open', 'desc' => 'Bimbingan tartil dan tajwid Al-Qur\'an standar pesantren modern.'],
            ['nama' => 'Tafsir & Tahfiz Al-Qur\'an', 'icon' => 'award', 'desc' => 'Program unggulan hafalan 30 Juz dan pemahaman tafsir ayat.'],
            ['nama' => 'Pramuka (Gudep SMK IB)', 'icon' => 'compass', 'desc' => 'Penanaman kepemimpinan, kemandirian, dan keterampilan luar ruang.'],
            ['nama' => 'Paskibra', 'icon' => 'flag', 'desc' => 'Disiplin baris-berbaris dan keikutsertaan seleksi pengibar bendera Kota Metro.'],
            ['nama' => 'Karate', 'icon' => 'shield', 'desc' => 'Seni bela diri pembentuk mental tangguh dan prestasi O2SN.'],
            ['nama' => 'Gamelan Karawitan', 'icon' => 'music', 'desc' => 'Pelestarian seni musik gamelan tradisional Nusantara.'],
            ['nama' => 'Gamelan Tolak Balak', 'icon' => 'bell', 'desc' => 'Kesenian musik adat khas budaya Lampung untuk upacara adat.'],
        ];

        return view('landing', compact('pengaturan', 'banner', 'jurusan', 'gelombangAktif', 'totalPendaftar', 'totalDiterima', 'ekskul'));
    }

    public function cekKelulusan(Request $request)
    {
        $request->validate([
            'nisn' => 'required|string',
        ]);

        $nisn = trim($request->nisn);
        $pendaftar = Pendaftar::with(['jurusan1', 'jurusan2', 'terima.jurusan'])
            ->where('nisn', $nisn)
            ->orWhere('no_pendaftaran', $nisn)
            ->first();

        if (!$pendaftar) {
            return response()->json([
                'success' => false,
                'message' => 'Data pendaftar dengan NISN / No. Pendaftaran tersebut tidak ditemukan.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'nama' => $pendaftar->nama,
                'nisn' => $pendaftar->nisn,
                'no_pendaftaran' => $pendaftar->no_pendaftaran,
                'asal_sekolah' => $pendaftar->asal_sekolah,
                'jurusan_1' => $pendaftar->jurusan1 ? $pendaftar->jurusan1->nama_jurusan : '-',
                'status_kelulusan' => $pendaftar->status_kelulusan,
                'jurusan_diterima' => $pendaftar->terima ? $pendaftar->terima->jurusan->nama_jurusan : null,
                'no_skl' => $pendaftar->terima ? $pendaftar->terima->no_surat_kelulusan : null,
                'tgl_diterima' => $pendaftar->terima ? $pendaftar->terima->tgl_diterima->translatedFormat('d F Y') : null,
            ]
        ]);
    }

    public function checkVoucher(Request $request)
    {
        $request->validate([
            'kode_voucher' => 'required|string|max:50',
            'nominal_asli' => 'required|numeric|min:0',
        ]);

        $kode = strtoupper(trim($request->kode_voucher));
        $voucher = VoucherPendaftaran::where('kode_voucher', $kode)->first();

        if (!$voucher) {
            return response()->json([
                'success' => false,
                'message' => "Kode voucher '{$kode}' tidak ditemukan. Pastikan kode yang dimasukkan benar.",
            ], 400);
        }

        $check = $voucher->validateAvailability();
        if (!$check['valid']) {
            return response()->json([
                'success' => false,
                'message' => $check['message'],
            ], 400);
        }

        $nominalAsli = (float) $request->nominal_asli;
        $potongan = $voucher->hitungPotongan($nominalAsli);
        $totalBayar = $voucher->hitungTotalBayar($nominalAsli);

        $potonganFormat = $voucher->tipe === 'persen'
            ? rtrim(rtrim(number_format((float)$voucher->nilai_potongan, 2, ',', '.'), '0'), ',') . '%'
            : 'Rp ' . number_format((float)$voucher->nilai_potongan, 0, ',', '.');

        return response()->json([
            'success' => true,
            'message' => "Voucher {$voucher->kode_voucher} berhasil diterapkan! Potongan {$potonganFormat} (Hemat Rp " . number_format($potongan, 0, ',', '.') . ").",
            'id_voucher' => $voucher->id_voucher,
            'kode_voucher' => $voucher->kode_voucher,
            'nama_voucher' => $voucher->nama_voucher ?: $voucher->kode_voucher,
            'tipe' => $voucher->tipe,
            'nilai_potongan' => (float)$voucher->nilai_potongan,
            'biaya_awal' => $nominalAsli,
            'potongan' => $potongan,
            'total_bayar' => $totalBayar,
            'sisa_kuota' => $voucher->sisaKuota(),
        ]);
    }
}

