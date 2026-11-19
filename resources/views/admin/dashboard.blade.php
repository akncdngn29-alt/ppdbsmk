@extends('layouts.admin')

@section('title', 'Dashboard Petugas TU - PPDB SMK IB Khalifah Bangsa')
@section('header_title', 'Dashboard Petugas Tata Usaha (TU)')

@section('content')
<div class="space-y-6">

    <!-- Top KPI Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4">
        <div class="p-5 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-sm flex items-center justify-between">
            <div>
                <span class="text-xs text-slate-500 dark:text-slate-400 font-semibold uppercase">Total Pendaftar</span>
                <div class="text-2xl font-black text-slate-900 dark:text-white mt-1">{{ $totalPendaftar }}</div>
            </div>
            <div class="w-11 h-11 rounded-xl bg-pink-50 dark:bg-pink-950/60 text-[#fa2f79] flex items-center justify-center">
                <i data-lucide="users" class="w-6 h-6"></i>
            </div>
        </div>

        <div class="p-5 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-sm flex items-center justify-between">
            <div>
                <span class="text-xs text-slate-500 dark:text-slate-400 font-semibold uppercase">Pendaftar Hari Ini</span>
                <div class="text-2xl font-black text-sky-600 dark:text-sky-400 mt-1">{{ $pendaftarHariIni }}</div>
            </div>
            <div class="w-11 h-11 rounded-xl bg-sky-50 dark:bg-sky-950/60 text-sky-600 dark:text-sky-400 flex items-center justify-center">
                <i data-lucide="user-plus" class="w-6 h-6"></i>
            </div>
        </div>

        <div class="p-5 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-sm flex items-center justify-between">
            <div>
                <span class="text-xs text-slate-500 dark:text-slate-400 font-semibold uppercase">Biaya Lunas</span>
                <div class="text-2xl font-black text-[#fa2f79] dark:text-pink-400 mt-1">{{ $totalLunas }}</div>
            </div>
            <div class="w-11 h-11 rounded-xl bg-pink-50 dark:bg-pink-950/60 text-[#fa2f79] dark:text-pink-400 flex items-center justify-center">
                <i data-lucide="wallet" class="w-6 h-6"></i>
            </div>
        </div>

        <div class="p-5 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-sm flex items-center justify-between">
            <div>
                <span class="text-xs text-slate-500 dark:text-slate-400 font-semibold uppercase">Perlu Verifikasi</span>
                <div class="text-2xl font-black text-amber-600 dark:text-amber-400 mt-1">{{ $menungguVerifikasi }}</div>
            </div>
            <div class="w-11 h-11 rounded-xl bg-amber-50 dark:bg-amber-950/60 text-amber-600 dark:text-amber-400 flex items-center justify-center">
                <i data-lucide="clock" class="w-6 h-6"></i>
            </div>
        </div>

        <div class="p-5 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-sm flex items-center justify-between">
            <div>
                <span class="text-xs text-slate-500 dark:text-slate-400 font-semibold uppercase">Lolos Seleksi</span>
                <div class="text-2xl font-black text-purple-600 dark:text-purple-400 mt-1">{{ $totalDiterima }}</div>
            </div>
            <div class="w-11 h-11 rounded-xl bg-purple-50 dark:bg-purple-950/60 text-purple-600 dark:text-purple-400 flex items-center justify-center">
                <i data-lucide="award" class="w-6 h-6"></i>
            </div>
        </div>
    </div>

    <!-- Jurusan Quota Progress Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
        @foreach($jurusan as $j)
        @php
            $percent = $j->kuota > 0 ? round(($j->pendaftar_pilihan1_count / $j->kuota) * 100) : 0;
        @endphp
        <div class="p-5 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-sm space-y-3">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-1.5">
                    <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300">
                        {{ $j->kode_jurusan }}
                    </span>
                    @if(!$j->status_aktif)
                    <span class="px-1.5 py-0.5 rounded text-[9px] font-bold bg-slate-200 dark:bg-slate-700 text-slate-600 dark:text-slate-400">
                        Nonaktif
                    </span>
                    @endif
                </div>
                <span class="text-xs font-bold text-slate-500">{{ $j->pendaftar_pilihan1_count }} / {{ $j->kuota }} Siswa</span>
            </div>
            <h3 class="text-sm font-bold text-slate-900 dark:text-white truncate" title="{{ $j->nama_jurusan }}">{{ $j->nama_jurusan }}</h3>
            <!-- Progress Bar -->
            <div>
                <div class="w-full bg-slate-100 dark:bg-slate-800 h-2 rounded-full overflow-hidden">
                    <div class="h-full @if($percent >= 90) bg-[#fa2f79] @elseif($percent >= 75) bg-sky-500 @else bg-pink-500 @endif rounded-full" style="width: {{ min(100, $percent) }}%"></div>
                </div>
                <div class="flex items-center justify-between text-[11px] text-slate-400 mt-1">
                    <span class="@if($percent >= 90) text-[#fa2f79] font-bold @endif">Terisi {{ $percent }}%</span>
                    <span>Sisa: {{ max(0, $j->kuota - $j->pendaftar_pilihan1_count) }} Kursi</span>
                </div>
            </div>
        </div>
        @endforeach
    </div>

    <!-- Recent Applicants Table -->
    <div class="p-6 rounded-3xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-sm space-y-4">
        <div class="flex items-center justify-between flex-wrap gap-2">
            <div>
                <h2 class="text-base font-bold text-slate-900 dark:text-white">Pendaftar Terbaru</h2>
                <p class="text-xs text-slate-500 dark:text-slate-400">Daftar calon siswa yang baru saja menyelesaikan registrasi.</p>
            </div>
            <a href="{{ route('admin.pendaftar') }}" class="text-xs font-bold text-[#fa2f79] hover:underline flex items-center gap-1">
                Lihat Seluruh Pendaftar &rarr;
            </a>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 dark:bg-slate-800 text-slate-500 dark:text-slate-400 uppercase font-bold text-[10px]">
                    <tr>
                        <th class="p-3">No. Registrasi</th>
                        <th class="p-3">Nama Siswa</th>
                        <th class="p-3">Asal Sekolah</th>
                        <th class="p-3">Pilihan Jurusan</th>
                        <th class="p-3">Status Bayar</th>
                        <th class="p-3">Status Berkas</th>
                        <th class="p-3">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                    @forelse($recentPendaftar as $rp)
                    <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/50">
                        <td class="p-3 font-mono font-bold text-slate-900 dark:text-white">{{ $rp->no_pendaftaran }}</td>
                        <td class="p-3">
                            <span class="font-bold text-slate-900 dark:text-white block">{{ $rp->nama }}</span>
                            <span class="text-[10px] text-slate-400 font-mono">{{ $rp->nisn }}</span>
                        </td>
                        <td class="p-3 text-slate-600 dark:text-slate-300">{{ $rp->asal_sekolah }}</td>
                        <td class="p-3 font-medium text-slate-800 dark:text-slate-200">{{ $rp->jurusan1 ? $rp->jurusan1->nama_jurusan : 'Belum dipilih' }}</td>
                        <td class="p-3">
                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold 
                                {{ $rp->status_pembayaran === 'Paid' ? 'bg-pink-100 text-pink-800 dark:bg-pink-950 dark:text-pink-300' : 'bg-amber-100 text-amber-800 dark:bg-amber-950 dark:text-amber-300' }}">
                                {{ $rp->status_pembayaran }}
                            </span>
                        </td>
                        <td class="p-3">
                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold 
                                @if($rp->status_verifikasi === 'Terverifikasi') bg-pink-100 text-pink-800 dark:bg-pink-950 dark:text-pink-300
                                @elseif($rp->status_verifikasi === 'Ditolak') bg-rose-100 text-rose-800 dark:bg-rose-950 dark:text-rose-300
                                @else bg-amber-100 text-amber-800 dark:bg-amber-950 dark:text-amber-300 @endif">
                                {{ $rp->status_verifikasi }}
                            </span>
                        </td>
                        <td class="p-3">
                            <a href="{{ route('admin.pendaftar.detail', $rp->id_pendaftar) }}" class="px-3 py-1.5 rounded-lg bg-slate-100 dark:bg-slate-800 hover:bg-[#fa2f79] hover:text-white text-slate-700 dark:text-slate-300 font-bold transition-colors inline-block">
                                Detail / Periksa
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="p-6 text-center text-slate-400">Belum ada data pendaftar baru.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection

