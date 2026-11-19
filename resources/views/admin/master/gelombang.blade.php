@extends('layouts.admin')

@section('title', 'Gelombang Pendaftaran PPDB - SMK IB')
@section('header_title', 'Informasi Gelombang Pendaftaran & Biaya')

@section('content')
<div class="space-y-6">

    @if(session('success'))
    <div class="p-4 rounded-xl bg-pink-50 dark:bg-pink-950/50 border border-pink-200 dark:border-pink-800 flex items-center gap-3 text-xs text-pink-800 dark:text-pink-200">
        <i data-lucide="check-circle-2" class="w-4 h-4 text-[#fa2f79] shrink-0"></i>
        <span>{{ session('success') }}</span>
    </div>
    @endif

    @if(session('error'))
    <div class="p-4 rounded-xl bg-rose-50 dark:bg-rose-950/50 border border-rose-200 dark:border-rose-800 flex items-center gap-3 text-xs text-rose-800 dark:text-rose-200">
        <i data-lucide="alert-circle" class="w-4 h-4 text-rose-600 shrink-0"></i>
        <span>{{ session('error') }}</span>
    </div>
    @endif

    <!-- Card Informasi Otoritas Kebijakan (Khusus Petugas TU) -->
    <div class="p-5 rounded-2xl bg-amber-50 dark:bg-amber-950/40 border border-amber-200 dark:border-amber-800 flex items-start gap-3.5">
        <div class="w-10 h-10 rounded-xl bg-amber-500/10 text-amber-600 dark:text-amber-400 flex items-center justify-center shrink-0">
            <i data-lucide="shield-alert" class="w-5 h-5"></i>
        </div>
        <div class="space-y-1">
            <h4 class="text-xs font-bold text-amber-900 dark:text-amber-200">Otoritas Kebijakan Gelombang Pendaftaran</h4>
            <p class="text-[11px] text-amber-800/90 dark:text-amber-300/80 leading-relaxed">
                Penetapan jadwal buka/tutup, penambahan gelombang baru, dan perubahan tarif formulir pendaftaran merupakan wewenang <strong>Kepala Sekolah</strong> (dikelola melalui menu <em>Pengaturan Kebijakan PPDB</em>). Halaman ini berstatus <strong>Read-Only</strong> untuk Petugas Tata Usaha sebagai acuan operasional pelayanan di loket pendaftaran.
            </p>
        </div>
    </div>

    <!-- Tabel Daftar Gelombang (Read-Only) -->
    <div class="p-6 rounded-3xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-sm space-y-4">
        <div class="flex items-center justify-between pb-3 border-b border-slate-100 dark:border-slate-800">
            <div>
                <h3 class="text-base font-bold text-slate-900 dark:text-white flex items-center gap-2">
                    <i data-lucide="calendar" class="w-5 h-5 text-[#fa2f79]"></i>
                    Daftar Gelombang Pendaftaran PPDB
                </h3>
                <p class="text-xs text-slate-400 mt-0.5">Informasi gelombang pendaftaran yang terdaftar di sistem PPDB</p>
            </div>
            <span class="px-3 py-1 rounded-full text-xs font-bold bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300">
                Total: {{ count($gelombang) }} Gelombang
            </span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 dark:bg-slate-800 text-slate-500 uppercase font-bold text-[10px]">
                    <tr>
                        <th class="p-3.5">Nama Gelombang</th>
                        <th class="p-3.5">Tahun Ajaran</th>
                        <th class="p-3.5">Rentang Jadwal</th>
                        <th class="p-3.5">Biaya Formulir</th>
                        <th class="p-3.5">Pendaftar</th>
                        <th class="p-3.5 text-center">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                    @forelse($gelombang as $g)
                    <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-800/40 transition-colors">
                        <td class="p-3.5 font-bold text-slate-900 dark:text-white">
                            {{ $g->nama_gelombang }}
                        </td>
                        <td class="p-3.5 text-slate-600 dark:text-slate-300 font-mono">
                            {{ $g->tahun_ajaran }}
                        </td>
                        <td class="p-3.5 text-slate-600 dark:text-slate-300">
                            {{ $g->tgl_buka ? $g->tgl_buka->format('d/m/Y') : '-' }} s/d {{ $g->tgl_tutup ? $g->tgl_tutup->format('d/m/Y') : '-' }}
                        </td>
                        <td class="p-3.5 font-mono font-bold text-slate-900 dark:text-white">
                            Rp {{ number_format($g->biaya_pendaftaran, 0, ',', '.') }}
                        </td>
                        <td class="p-3.5 font-bold text-slate-700 dark:text-slate-300">
                            {{ $g->pendaftar_count }} Siswa
                        </td>
                        <td class="p-3.5 text-center">
                            @if($g->status_aktif)
                            <span class="inline-flex items-center gap-1 px-3 py-1 rounded-full text-[10px] font-bold bg-pink-100 text-pink-700 dark:bg-pink-950/60 dark:text-pink-400 border border-pink-300 dark:border-pink-800">
                                <span class="w-1.5 h-1.5 rounded-full bg-pink-500 animate-pulse"></span>
                                AKTIF
                            </span>
                            @else
                            <span class="inline-flex items-center gap-1 px-3 py-1 rounded-full text-[10px] font-semibold bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-400">
                                NONAKTIF
                            </span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="p-8 text-center text-slate-400">
                            <i data-lucide="inbox" class="w-8 h-8 mx-auto mb-2 text-slate-300"></i>
                            Belum ada gelombang pendaftaran yang dibuat.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection
