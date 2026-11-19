@extends('layouts.admin')

@section('title', 'Monitoring Jadwal & Ruang Seleksi')
@section('header_title', 'Monitoring Jadwal Seleksi')

@section('content')
<div class="space-y-6">

    <!-- Header Banner -->
    <div class="bg-gradient-to-r from-[#0B182F] via-[#112240] to-[#0B182F] rounded-2xl p-6 sm:p-7 text-white shadow-lg relative overflow-hidden border border-slate-800">
        <div class="relative z-10 flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <div class="flex items-center gap-2 mb-2">
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-rose-500/20 text-rose-300 border border-rose-500/30">
                        <i data-lucide="eye" class="w-3.5 h-3.5"></i>
                        Pengawasan Eksekutif (Read-Only)
                    </span>
                </div>
                <h2 class="text-xl sm:text-2xl font-black text-white">Monitoring Jadwal & Keterisian Ruang Tes</h2>
                <p class="text-xs sm:text-sm text-slate-300 mt-1 max-w-2xl leading-relaxed">
                    Pemantauan kesiapan pelaksanaan tes seleksi calon peserta didik baru dan kapasitas ruang ujian secara langsung (real-time).
                </p>
            </div>
            <div class="shrink-0">
                <span class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-slate-800/80 text-xs font-semibold text-slate-200 border border-slate-700">
                    <span class="w-2 h-2 rounded-full bg-rose-500 animate-pulse"></span>
                    Sesi Ujian PPDB 2026/2027
                </span>
            </div>
        </div>
        <div class="absolute -right-8 -bottom-8 w-44 h-44 bg-rose-500/10 rounded-full blur-2xl pointer-events-none"></div>
    </div>

    <!-- Ringkasan Eksekutif Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="p-5 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-sm">
            <div class="flex items-center justify-between">
                <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Total Agenda</span>
                <div class="w-8 h-8 rounded-lg bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 flex items-center justify-center">
                    <i data-lucide="calendar" class="w-4 h-4"></i>
                </div>
            </div>
            <h3 class="text-2xl font-black text-slate-900 dark:text-white mt-2">{{ $jadwals->count() }}</h3>
            <p class="text-[11px] text-rose-600 dark:text-rose-400 font-semibold mt-1">{{ $jadwals->where('status', 'aktif')->count() }} jadwal berstatus aktif</p>
        </div>

        <div class="p-5 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-sm">
            <div class="flex items-center justify-between">
                <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Peserta Terverifikasi</span>
                <div class="w-8 h-8 rounded-lg bg-sky-50 dark:bg-sky-950/50 text-sky-600 dark:text-sky-400 flex items-center justify-center">
                    <i data-lucide="user-check" class="w-4 h-4"></i>
                </div>
            </div>
            <h3 class="text-2xl font-black text-slate-900 dark:text-white mt-2">{{ $totalPesertaEligible }}</h3>
            <p class="text-[11px] text-slate-500 mt-1">Calon siswa berhak mengikuti tes</p>
        </div>

        <div class="p-5 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-sm">
            <div class="flex items-center justify-between">
                <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Sudah Punya Ruang</span>
                <div class="w-8 h-8 rounded-lg bg-emerald-50 dark:bg-emerald-950/50 text-emerald-600 dark:text-emerald-400 flex items-center justify-center">
                    <i data-lucide="check-circle" class="w-4 h-4"></i>
                </div>
            </div>
            <h3 class="text-2xl font-black text-emerald-600 dark:text-emerald-400 mt-2">{{ $totalDitempatkan }}</h3>
            <p class="text-[11px] text-emerald-600 font-medium mt-1">Siap mengikuti sesi ujian</p>
        </div>

        <div class="p-5 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-sm">
            <div class="flex items-center justify-between">
                <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Belum Ditempatkan</span>
                <div class="w-8 h-8 rounded-lg bg-amber-50 dark:bg-amber-950/50 text-amber-600 dark:text-amber-400 flex items-center justify-center">
                    <i data-lucide="alert-circle" class="w-4 h-4"></i>
                </div>
            </div>
            <h3 class="text-2xl font-black {{ $totalBelumDitempatkan > 0 ? 'text-amber-500' : 'text-slate-400' }} mt-2">{{ $totalBelumDitempatkan }}</h3>
            <p class="text-[11px] {{ $totalBelumDitempatkan > 0 ? 'text-amber-600' : 'text-slate-400' }} font-medium mt-1">Perlu alokasi oleh Petugas TU</p>
        </div>
    </div>

    <!-- Ringkasan Keterisian Ruang Seleksi -->
    <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-sm p-6">
        <div class="flex items-center justify-between mb-5">
            <div>
                <h3 class="text-sm font-extrabold text-slate-800 dark:text-white flex items-center gap-2">
                    <i data-lucide="layout-grid" class="w-4 h-4 text-rose-500"></i>
                    Kapasitas & Keterisian Ruang Seleksi
                </h3>
                <p class="text-xs text-slate-500 mt-0.5">Total daya tampung: {{ $totalKapasitasRuang }} kursi di seluruh ruang aktif.</p>
            </div>
            <span class="text-xs font-bold px-2.5 py-1 rounded-lg bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300">
                {{ $ruangs->count() }} Ruang Terdaftar
            </span>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3.5">
            @forelse($ruangs as $ruang)
                @php
                    $terisi = $ruang->penempatan_count ?? 0;
                    $persen = $ruang->kapasitas > 0 ? min(100, round(($terisi / $ruang->kapasitas) * 100)) : 0;
                    $isFull = $terisi >= $ruang->kapasitas;
                @endphp
                <div class="p-4 rounded-xl border border-slate-200 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-800/40 hover:border-slate-300 dark:hover:border-slate-700 transition-colors">
                    <div class="flex items-center justify-between mb-1">
                        <span class="font-bold text-xs text-slate-800 dark:text-white">{{ $ruang->nama_ruang }}</span>
                        <span class="text-[10px] font-mono px-2 py-0.5 rounded bg-rose-50 text-rose-600 dark:bg-rose-950/60 dark:text-rose-400 font-bold border border-rose-100 dark:border-rose-900/50">
                            {{ $ruang->kode_ruang }}
                        </span>
                    </div>
                    <p class="text-[10px] text-slate-400 mb-3">{{ $ruang->gedung ?: 'Gedung Utama' }}</p>
                    <div class="flex items-center justify-between text-[11px] text-slate-600 dark:text-slate-300 mb-1.5">
                        <span>Dialokasikan: <strong>{{ $terisi }}</strong></span>
                        <span class="text-slate-400">Kapasitas: {{ $ruang->kapasitas }}</span>
                    </div>
                    <div class="w-full h-2 bg-slate-200 dark:bg-slate-700 rounded-full overflow-hidden">
                        <div class="h-full {{ $isFull ? 'bg-rose-500' : 'bg-emerald-500' }} transition-all duration-300" style="width: {{ $persen }}%"></div>
                    </div>
                </div>
            @empty
                <div class="col-span-4 py-6 text-center text-slate-400 text-xs">
                    Belum ada ruang seleksi yang didaftarkan.
                </div>
            @endforelse
        </div>
    </div>

    <!-- Tabel Daftar Jadwal Seleksi (Read-Only) -->
    <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-sm overflow-hidden">
        <div class="p-5 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between">
            <h3 class="text-sm font-bold text-slate-800 dark:text-white flex items-center gap-2">
                <i data-lucide="clipboard-list" class="w-4 h-4 text-rose-500"></i>
                Rekapitulasi Agenda Jadwal Seleksi
            </h3>
            <span class="text-xs text-slate-400 font-medium">Pengawasan Tingkat Pimpinan</span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-xs text-left">
                <thead class="bg-slate-900 text-slate-200 font-bold uppercase tracking-wider text-[10px] border-b border-slate-800">
                    <tr>
                        <th class="py-3.5 px-4">Nama Agenda</th>
                        <th class="py-3.5 px-4">Jenis Seleksi</th>
                        <th class="py-3.5 px-4">Tanggal & Jam</th>
                        <th class="py-3.5 px-4">Lokasi & Gedung</th>
                        <th class="py-3.5 px-4 text-center">Jumlah Peserta</th>
                        <th class="py-3.5 px-4 text-center">Ruang Dipakai</th>
                        <th class="py-3.5 px-4 text-center">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800 text-slate-700 dark:text-slate-300">
                    @forelse($jadwals as $j)
                    <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-800/40 transition-colors">
                        <td class="py-3.5 px-4 font-bold text-slate-800 dark:text-white">
                            {{ $j->nama_jadwal }}
                            @if($j->keterangan)
                            <span class="block text-[10px] font-normal text-slate-400 line-clamp-1 mt-0.5">{{ $j->keterangan }}</span>
                            @endif
                        </td>
                        <td class="py-3.5 px-4">
                            <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 border border-slate-200 dark:border-slate-700">
                                {{ $j->jenis_seleksi }}
                            </span>
                        </td>
                        <td class="py-3.5 px-4">
                            <span class="font-semibold block">{{ \Carbon\Carbon::parse($j->tanggal)->isoFormat('dddd, D MMMM Y') }}</span>
                            <span class="text-[11px] text-slate-500 font-mono">{{ substr($j->waktu_mulai, 0, 5) }} - {{ substr($j->waktu_selesai, 0, 5) }} WIB</span>
                        </td>
                        <td class="py-3.5 px-4">
                            <span class="font-medium text-slate-800 dark:text-white">{{ $j->lokasi ?? 'Kampus Utama' }}</span>
                            @if($j->gedung)
                            <span class="text-[11px] text-slate-400 block">{{ $j->gedung }}</span>
                            @endif
                        </td>
                        <td class="py-3.5 px-4 text-center">
                            <span class="inline-flex items-center gap-1 px-3 py-1 rounded-full text-xs font-bold {{ $j->penempatan_count > 0 ? 'bg-rose-50 text-rose-700 dark:bg-rose-950/60 dark:text-rose-300 border border-rose-100 dark:border-rose-900/50' : 'bg-slate-100 dark:bg-slate-800 text-slate-500' }}">
                                <i data-lucide="users" class="w-3.5 h-3.5"></i>
                                <span>{{ $j->penempatan_count }} Peserta</span>
                            </span>
                        </td>
                        <td class="py-3.5 px-4 text-center font-medium">
                            <span class="inline-block px-2.5 py-0.5 rounded-lg bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 text-xs font-bold">
                                {{ $j->ruangPenempatan->unique('id_ruang')->count() }} Ruang
                            </span>
                        </td>
                        <td class="py-3.5 px-4 text-center">
                            @if($j->status === 'aktif')
                            <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-emerald-100 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-300">
                                Aktif
                            </span>
                            @else
                            <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-slate-200 dark:bg-slate-800 text-slate-600 dark:text-slate-400">
                                Nonaktif
                            </span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="py-8 text-center text-slate-400 text-xs font-medium">
                            Belum ada agenda jadwal seleksi yang terdata.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection
