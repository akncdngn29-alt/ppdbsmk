<?php

namespace App\Http\Controllers;

use App\Models\Pendaftar;
use App\Models\Terima;
use App\Models\PengaturanSekolah;

class VerifikasiController extends Controller
{
    /**
     * Verifikasi kartu ujian pendaftar via QR Code.
     * Halaman ini bersifat publik agar pemindai QR dapat mengaksesnya.
     * Hanya menampilkan data yang diperlukan untuk verifikasi (tanpa data pribadi sensitif).
     */
    public function verifikasiKartuUjian(string $token)
    {
        $pendaftar = Pendaftar::with(['jurusan1'])
            ->where('verification_token', $token)
            ->first();

        if (!$pendaftar || $pendaftar->status_pembayaran !== 'Paid') {
            return view('verifikasi.hasil', [
                'valid' => false,
                'tipe' => 'Kartu Peserta Ujian',
                'pesan' => 'Token verifikasi tidak valid atau dokumen belum diterbitkan.',
                'pengaturan' => PengaturanSekolah::getPengaturan(),
            ]);
        }

        return view('verifikasi.hasil', [
            'valid' => true,
            'tipe' => 'Kartu Peserta Ujian',
            'data' => [
                'nama' => $pendaftar->nama,
                'no_pendaftaran' => $pendaftar->no_pendaftaran,
                'jurusan' => $pendaftar->jurusan1 ? $pendaftar->jurusan1->nama_jurusan : '-',
                'status_pembayaran' => 'Lunas (Terverifikasi)',
            ],
            'pengaturan' => PengaturanSekolah::getPengaturan(),
        ]);
    }

    /**
     * Verifikasi SKL (Surat Keterangan Lulus) via QR Code.
     * Halaman ini bersifat publik agar pemindai QR dapat mengaksesnya.
     * Hanya menampilkan informasi yang diperlukan untuk memastikan keaslian SKL.
     */
    public function verifikasiSKL(string $token)
    {
        $terima = Terima::with(['pendaftar', 'jurusan'])
            ->where('verification_token', $token)
            ->first();

        if (!$terima || !$terima->pendaftar || $terima->pendaftar->status_kelulusan !== 'Diterima') {
            return view('verifikasi.hasil', [
                'valid' => false,
                'tipe' => 'Surat Keterangan Lulus (SKL)',
                'pesan' => 'Token verifikasi tidak valid atau dokumen belum diterbitkan secara resmi.',
                'pengaturan' => PengaturanSekolah::getPengaturan(),
            ]);
        }

        return view('verifikasi.hasil', [
            'valid' => true,
            'tipe' => 'Surat Keterangan Lulus (SKL)',
            'data' => [
                'nama' => $terima->pendaftar->nama,
                'no_pendaftaran' => $terima->pendaftar->no_pendaftaran,
                'no_surat_kelulusan' => $terima->no_surat_kelulusan,
                'jurusan_diterima' => $terima->jurusan->nama_jurusan ?? '-',
                'tgl_diterima' => $terima->tgl_diterima->translatedFormat('d F Y'),
                'status' => 'LULUS / DITERIMA',
            ],
            'pengaturan' => PengaturanSekolah::getPengaturan(),
        ]);
    }
}

