@extends('layouts.siswa')

@section('title', 'Panduan & Konfirmasi Daftar Ulang')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">

        <!-- Header -->
        <div class="flex items-center justify-between flex-wrap gap-4">
            <div>
                <a href="{{ route('siswa.dashboard') }}" class="text-xs font-semibold text-emerald-600 dark:text-emerald-400 hover:underline flex items-center gap-1 mb-1">
                    <i data-lucide="arrow-left" class="w-3.5 h-3.5"></i> Kembali ke Dashboard
                </a>
                <h1 class="text-2xl font-extrabold text-slate-900 dark:text-white">Daftar Ulang Peserta Didik Baru</h1>
                <p class="text-xs text-slate-500 dark:text-slate-400">Tahapan final konfirmasi berkas fisik dan penerimaan resmi siswa baru T.A 2026/2027.</p>
            </div>
            
            <span class="px-3.5 py-1.5 rounded-full text-xs font-bold 
                @if($pendaftar->status_daftar_ulang === 'Selesai') bg-emerald-100 text-emerald-800 dark:bg-emerald-950 dark:text-emerald-300
                @else bg-amber-100 text-amber-800 dark:bg-amber-950 dark:text-amber-300 @endif flex items-center gap-1.5">
                <i data-lucide="{{ $pendaftar->status_daftar_ulang === 'Selesai' ? 'check-check' : 'clock' }}" class="w-4 h-4"></i>
                Status: {{ $pendaftar->status_daftar_ulang === 'Selesai' ? 'RESMI TERDAFTAR ULANG' : 'BELUM KONFIRMASI' }}
            </span>
        </div>

        @if($pendaftar->status_kelulusan !== 'Diterima')
        <!-- Not Accepted Warning -->
        <div class="p-6 md:p-8 rounded-3xl bg-amber-50 dark:bg-amber-950/40 border border-amber-200 dark:border-amber-800 text-amber-900 dark:text-amber-200 space-y-3">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-2xl bg-amber-500 text-white flex items-center justify-center shrink-0">
                    <i data-lucide="alert-triangle" class="w-5 h-5"></i>
                </div>
                <div>
                    <h3 class="text-sm font-bold">Menu Daftar Ulang Belum Terbuka</h3>
                    <p class="text-xs text-amber-700 dark:text-amber-300">
                        Tahapan daftar ulang hanya dapat diakses oleh calon siswa yang telah dinyatakan <strong>Diterima</strong> dalam Sidang Kelulusan oleh Kepala Sekolah.
                    </p>
                </div>
            </div>
            <div class="pt-2">
                <a href="{{ route('siswa.dashboard') }}" class="px-4 py-2 rounded-xl bg-amber-600 hover:bg-amber-700 text-white text-xs font-bold inline-flex items-center gap-2">
                    Cek Status Kelulusan di Dashboard
                </a>
            </div>
        </div>
        @else

        <!-- Acceptance Status Summary -->
        <div class="p-6 md:p-8 rounded-3xl bg-gradient-to-r from-emerald-800 to-teal-900 text-white shadow-xl space-y-4">
            <div class="flex items-center justify-between flex-wrap gap-2">
                <div class="flex items-center gap-2">
                    <span class="px-2.5 py-1 rounded-full text-[10px] font-bold uppercase bg-white/20 text-emerald-200 tracking-wider">
                        Hasil Seleksi Akhir
                    </span>
                    <span class="text-xs text-emerald-200 font-mono">No. Pendaftaran: {{ $pendaftar->no_pendaftaran }}</span>
                </div>
                @if($pendaftar->terima)
                <span class="text-xs font-mono font-bold text-emerald-300 bg-black/20 px-3 py-1 rounded-full border border-white/10">
                    SKL: {{ $pendaftar->terima->no_surat_kelulusan }}
                </span>
                @endif
            </div>

            <div>
                <h2 class="text-xl md:text-2xl font-black">Selamat, {{ $pendaftar->nama }}!</h2>
                <p class="text-xs md:text-sm text-emerald-100 mt-1">
                    Anda dinyatakan <strong>DITERIMA</strong> pada Program Keahlian: 
                    <span class="text-white underline font-bold">{{ $pendaftar->jurusan1 ? $pendaftar->jurusan1->nama_jurusan : '-' }}</span>.
                </p>
            </div>

            <div class="pt-2 flex flex-wrap gap-3">
                <a href="{{ route('siswa.cetak.skl') }}" target="_blank" class="px-4 py-2 rounded-xl bg-white text-emerald-900 font-bold text-xs hover:bg-emerald-50 transition-colors shadow-sm flex items-center gap-2">
                    <i data-lucide="award" class="w-4 h-4 text-emerald-600"></i> Unduh Lembar SKL Resmi (PDF)
                </a>
            </div>
        </div>

        <!-- 3-Column Instruction Guide -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <!-- 1. Dokumen Fisik -->
            <div class="p-6 rounded-3xl bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 shadow-sm space-y-3">
                <div class="w-9 h-9 rounded-xl bg-emerald-50 dark:bg-emerald-950/60 text-emerald-600 dark:text-emerald-400 flex items-center justify-center font-bold text-sm">
                    1
                </div>
                <h3 class="text-sm font-bold text-slate-900 dark:text-white">Penyerahan Berkas Fisik</h3>
                <p class="text-xs text-slate-500 dark:text-slate-400 leading-relaxed">
                    Serahkan berkas fisik ke loket TU kampus:
                </p>
                <ul class="text-xs text-slate-600 dark:text-slate-300 space-y-1 list-disc list-inside">
                    <li>Surat Keterangan Lulus (SKL) Asli</li>
                    <li>Fotokopi Kartu Keluarga (2 lbr)</li>
                    <li>Pas Foto 3x4 berwarna (4 lbr)</li>
                    <li>Kwitansi lunas pembayaran</li>
                </ul>
            </div>

            <!-- 2. Pengukuran Seragam -->
            <div class="p-6 rounded-3xl bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 shadow-sm space-y-3">
                <div class="w-9 h-9 rounded-xl bg-teal-50 dark:bg-teal-950/60 text-teal-600 dark:text-teal-400 flex items-center justify-center font-bold text-sm">
                    2
                </div>
                <h3 class="text-sm font-bold text-slate-900 dark:text-white">Seragam & Wearpack</h3>
                <p class="text-xs text-slate-500 dark:text-slate-400 leading-relaxed">
                    Ukuran yang telah Anda pilih saat mendaftar:
                </p>
                <div class="p-3 rounded-2xl bg-slate-50 dark:bg-slate-900/60 border border-slate-200 dark:border-slate-700 text-xs space-y-1">
                    <div class="flex justify-between">
                        <span class="text-slate-400">Seragam Harian:</span>
                        <strong class="text-slate-900 dark:text-white">{{ $pendaftar->ukuran_seragam ?: 'L' }}</strong>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-slate-400">Wearpack Bengkel/Lab:</span>
                        <strong class="text-slate-900 dark:text-white">{{ $pendaftar->ukuran_wearpack ?: 'L' }}</strong>
                    </div>
                </div>
                <span class="text-[11px] text-slate-400 block">Pengambilan paket seragam dijadwalkan saat masa orientasi.</span>
            </div>

            <!-- 3. Jadwal Masuk Sekolah -->
            <div class="p-6 rounded-3xl bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 shadow-sm space-y-3">
                <div class="w-9 h-9 rounded-xl bg-indigo-50 dark:bg-indigo-950/60 text-indigo-600 dark:text-indigo-400 flex items-center justify-center font-bold text-sm">
                    3
                </div>
                <h3 class="text-sm font-bold text-slate-900 dark:text-white">Jadwal MPLS & Masuk</h3>
                <p class="text-xs text-slate-500 dark:text-slate-400 leading-relaxed">
                    Masa Pengenalan Lingkungan Sekolah (MPLS) T.A 2026/2027:
                </p>
                <div class="p-3 rounded-2xl bg-indigo-50/50 dark:bg-indigo-950/30 border border-indigo-200/60 dark:border-indigo-800 text-xs text-indigo-900 dark:text-indigo-300 space-y-1">
                    <div><strong>Pra-MPLS:</strong> 11 Juli 2026 (08.00 WIB)</div>
                    <div><strong>Pelaksanaan MPLS:</strong> 13 - 15 Juli 2026</div>
                </div>
                <span class="text-[11px] text-slate-400 block">Pakaian: Seragam SMP asal rapi & sopan.</span>
            </div>
        </div>

        <!-- Tombol Konfirmasi Daftar Ulang -->
        <div class="p-6 md:p-8 rounded-3xl bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 shadow-sm text-center space-y-4">
            @if($pendaftar->status_daftar_ulang === 'Selesai')
            <div class="w-14 h-14 rounded-2xl bg-emerald-100 text-emerald-600 dark:bg-emerald-950 dark:text-emerald-400 flex items-center justify-center mx-auto shadow-md">
                <i data-lucide="check-check" class="w-8 h-8"></i>
            </div>
            <div>
                <h3 class="text-lg font-extrabold text-slate-900 dark:text-white">Daftar Ulang Anda Telah Terkonfirmasi</h3>
                <p class="text-xs text-slate-500 dark:text-slate-400 max-w-md mx-auto mt-1">
                    Terima kasih telah mengonfirmasi pendaftaran ulang. Selamat bergabung di keluarga besar SMK Islam Bina Khalifah Bangsa!
                </p>
            </div>
            @else
            <div>
                <h3 class="text-base font-extrabold text-slate-900 dark:text-white">Konfirmasi Kesediaan Masuk Sekolah</h3>
                <p class="text-xs text-slate-500 dark:text-slate-400 max-w-lg mx-auto mt-1">
                    Dengan menekan tombol di bawah ini, Anda menyatakan siap mengikuti proses pembelajaran dan mematuhi seluruh tata tertib di SMK Islam Bina Khalifah Bangsa.
                </p>
            </div>

            <form action="{{ route('siswa.daftar_ulang.konfirmasi') }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin mengonfirmasi pendaftaran ulang?');">
                @csrf
                <button type="submit" class="px-8 py-3.5 rounded-2xl bg-emerald-600 hover:bg-emerald-700 text-white font-extrabold text-sm shadow-lg shadow-emerald-600/30 hover:scale-[1.02] transition-all inline-flex items-center gap-2">
                    <i data-lucide="check-circle" class="w-5 h-5"></i>
                    Konfirmasi Daftar Ulang Sekarang
                </button>
            </form>
            @endif
        </div>
        @endif

    </div>
@endsection

