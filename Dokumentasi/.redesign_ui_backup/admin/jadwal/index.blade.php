@extends('layouts.admin')

@section('title', 'Manajemen Jadwal Seleksi - SMK Islam Bina Khalifah Bangsa')
@section('header_title', 'Manajemen Jadwal Seleksi')

@section('content')
<div class="space-y-6">

    <!-- Header Actions & Breadcrumb -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h2 class="text-xl font-extrabold text-navy-950 dark:text-white">Jadwal Seleksi Masuk</h2>
            <p class="text-xs text-slate-500 dark:text-slate-400">Kelola agenda seleksi, alokasi ruang, dan penempatan peserta tes PPDB.</p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('admin.ruang.index') }}" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl border border-slate-300 dark:border-slate-700 text-xs font-bold text-slate-700 dark:text-slate-200 bg-white dark:bg-slate-800 hover:bg-slate-50 dark:hover:bg-slate-700 transition shadow-sm">
                <i data-lucide="door-open" class="w-4 h-4 text-pink-600 dark:text-pink-400"></i>
                <span>Kelola Ruang Seleksi</span>
            </a>
            <a href="{{ route('admin.jadwal.create') }}" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-pink-600 hover:bg-pink-700 text-white text-xs font-bold shadow-md shadow-pink-600/20 transition">
                <i data-lucide="plus" class="w-4 h-4"></i>
                <span>Tambah Jadwal</span>
            </a>
        </div>
    </div>

    <!-- Statistik Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="p-4 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200/90 dark:border-slate-800 shadow-sm transition hover:shadow-md">
            <div class="flex items-center justify-between">
                <div>
                    <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Total Jadwal</span>
                    <h3 class="text-2xl font-black text-navy-950 dark:text-white mt-0.5">{{ $totalJadwal }}</h3>
                </div>
                <div class="w-10 h-10 rounded-xl bg-pink-50 text-pink-600 dark:bg-pink-950/40 dark:text-pink-400 flex items-center justify-center">
                    <i data-lucide="calendar" class="w-5 h-5"></i>
                </div>
            </div>
            <p class="text-[11px] text-pink-600 dark:text-pink-400 font-medium mt-2">{{ $jadwalAktif }} jadwal aktif</p>
        </div>

        <div class="p-4 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200/90 dark:border-slate-800 shadow-sm transition hover:shadow-md">
            <div class="flex items-center justify-between">
                <div>
                    <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Total Ruang</span>
                    <h3 class="text-2xl font-black text-navy-950 dark:text-white mt-0.5">{{ $totalRuang }}</h3>
                </div>
                <div class="w-10 h-10 rounded-xl bg-sky-500/10 text-sky-600 dark:text-sky-400 flex items-center justify-center">
                    <i data-lucide="layout-grid" class="w-5 h-5"></i>
                </div>
            </div>
            <p class="text-[11px] text-sky-600 dark:text-sky-400 font-medium mt-2">{{ $ruangAktif }} ruang aktif siap pakai</p>
        </div>

        <div class="p-4 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200/90 dark:border-slate-800 shadow-sm transition hover:shadow-md">
            <div class="flex items-center justify-between">
                <div>
                    <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Kapasitas Total</span>
                    <h3 class="text-2xl font-black text-navy-950 dark:text-white mt-0.5">{{ $totalKapasitasRuang }}</h3>
                </div>
                <div class="w-10 h-10 rounded-xl bg-navy-50 text-navy-900 dark:bg-navy-900/60 dark:text-navy-300 flex items-center justify-center">
                    <i data-lucide="users" class="w-5 h-5"></i>
                </div>
            </div>
            <p class="text-[11px] text-slate-500 dark:text-slate-400 font-medium mt-2">Daya tampung ruang aktif</p>
        </div>

        <div class="p-4 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200/90 dark:border-slate-800 shadow-sm transition hover:shadow-md">
            <div class="flex items-center justify-between">
                <div>
                    <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Alur Seleksi</span>
                    <h3 class="text-xs font-extrabold text-slate-700 dark:text-slate-300 mt-1">Otomatis & Manual</h3>
                </div>
                <div class="w-10 h-10 rounded-xl bg-amber-500/10 text-amber-600 dark:text-amber-400 flex items-center justify-center">
                    <i data-lucide="sparkles" class="w-5 h-5"></i>
                </div>
            </div>
            <p class="text-[11px] text-slate-500 dark:text-slate-400 font-medium mt-2">Pemberian ruang stabil & transaksional</p>
        </div>
    </div>

    <!-- Filter & Search -->
    <div class="p-4 rounded-3xl bg-white dark:bg-slate-900 border border-slate-200/90 dark:border-slate-800 shadow-sm">
        <form method="GET" action="{{ route('admin.jadwal.index') }}" class="grid grid-cols-1 sm:grid-cols-12 gap-3 items-end">
            <div class="sm:col-span-5">
                <label class="block text-[11px] font-bold text-slate-600 dark:text-slate-300 uppercase mb-1">Cari Jadwal</label>
                <input type="text" name="q" value="{{ request('q') }}" placeholder="Nama jadwal, jenis seleksi, lokasi..." class="w-full px-3 py-2 text-xs rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-slate-800 dark:text-white focus:outline-none focus:ring-2 focus:ring-pink-500">
            </div>
            <div class="sm:col-span-3">
                <label class="block text-[11px] font-bold text-slate-600 dark:text-slate-300 uppercase mb-1">Status</label>
                <select name="status" class="w-full px-3 py-2 text-xs rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-slate-800 dark:text-white focus:outline-none focus:ring-2 focus:ring-pink-500">
                    <option value="">Semua Status</option>
                    <option value="aktif" {{ request('status') === 'aktif' ? 'selected' : '' }}>Aktif</option>
                    <option value="nonaktif" {{ request('status') === 'nonaktif' ? 'selected' : '' }}>Nonaktif</option>
                </select>
            </div>
            <div class="sm:col-span-2">
                <label class="block text-[11px] font-bold text-slate-600 dark:text-slate-300 uppercase mb-1">Tanggal</label>
                <input type="date" name="tanggal" value="{{ request('tanggal') }}" class="w-full px-3 py-2 text-xs rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-slate-800 dark:text-white focus:outline-none focus:ring-2 focus:ring-pink-500">
            </div>
            <div class="sm:col-span-2 flex gap-2">
                <button type="submit" class="w-full py-2 px-3 rounded-xl bg-pink-600 hover:bg-pink-700 text-white text-xs font-bold transition">
                    Filter
                </button>
                @if(request()->hasAny(['q', 'status', 'tanggal']))
                <a href="{{ route('admin.jadwal.index') }}" class="py-2 px-3 rounded-xl bg-slate-200 dark:bg-slate-700 text-slate-700 dark:text-slate-200 text-xs font-bold hover:bg-slate-300 transition">
                    Reset
                </a>
                @endif
            </div>
        </form>
    </div>

    <!-- Tabel Jadwal -->
    <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200/90 dark:border-slate-800 shadow-sm overflow-hidden">
        <div class="p-4 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between">
            <h3 class="text-sm font-bold text-navy-950 dark:text-white">Daftar Jadwal Seleksi</h3>
            <span class="text-xs text-slate-400">Total: {{ $jadwals->total() }} agenda</span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-xs text-left">
                <thead class="bg-navy-50/60 dark:bg-slate-800/50 text-navy-950 dark:text-slate-300 font-bold uppercase tracking-wider text-[10px] border-b border-slate-200 dark:border-slate-800">
                    <tr>
                        <th class="py-3 px-4">Agenda & Seleksi</th>
                        <th class="py-3 px-4">Tanggal & Jam</th>
                        <th class="py-3 px-4">Lokasi / Gedung</th>
                        <th class="py-3 px-4 text-center">Peserta</th>
                        <th class="py-3 px-4 text-center">Status</th>
                        <th class="py-3 px-4 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800 text-slate-700 dark:text-slate-300">
                    @forelse($jadwals as $j)
                    <tr class="hover:bg-slate-50/70 dark:hover:bg-slate-800/40 transition">
                        <td class="py-3.5 px-4">
                            <span class="font-bold text-navy-950 dark:text-white block">{{ $j->nama_jadwal }}</span>
                            <span class="inline-block mt-0.5 text-[10px] font-semibold px-2 py-0.5 rounded-md bg-navy-50 dark:bg-navy-950/40 text-navy-900 dark:text-navy-300 border border-navy-200/60 dark:border-navy-800/60">
                                {{ $j->jenis_seleksi }}
                            </span>
                        </td>
                        <td class="py-3.5 px-4">
                            <span class="font-semibold block text-slate-900 dark:text-slate-100">{{ \Carbon\Carbon::parse($j->tanggal)->isoFormat('dddd, D MMMM Y') }}</span>
                            <span class="text-[11px] text-slate-500 font-mono">{{ substr($j->waktu_mulai, 0, 5) }} - {{ substr($j->waktu_selesai, 0, 5) }} WIB</span>
                        </td>
                        <td class="py-3.5 px-4">
                            <span class="block font-medium">{{ $j->lokasi ?? 'Kampus Utama' }}</span>
                            @if($j->gedung)
                            <span class="text-[11px] text-slate-400">Gedung: {{ $j->gedung }}</span>
                            @endif
                        </td>
                        <td class="py-3.5 px-4 text-center">
                            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-bold {{ $j->penempatan_count > 0 ? 'bg-pink-50 dark:bg-pink-950/60 text-pink-700 dark:text-pink-300' : 'bg-slate-100 dark:bg-slate-800 text-slate-500' }}">
                                <i data-lucide="users" class="w-3.5 h-3.5"></i>
                                <span>{{ $j->penempatan_count }}</span>
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
                        <td class="py-3.5 px-4 text-right">
                            <div class="flex items-center justify-end gap-1.5">
                                <a href="{{ route('admin.jadwal.penempatan', $j->id_jadwal) }}" title="Penempatan Peserta" class="px-2.5 py-1.5 rounded-lg bg-pink-50 dark:bg-pink-950/50 text-pink-600 dark:text-pink-400 hover:bg-pink-100 text-[11px] font-bold transition inline-flex items-center gap-1">
                                    <i data-lucide="user-check" class="w-3.5 h-3.5"></i>
                                    <span>Penempatan</span>
                                </a>
                                <a href="{{ route('admin.jadwal.edit', $j->id_jadwal) }}" title="Edit Jadwal" class="p-1.5 rounded-lg text-slate-500 hover:text-navy-950 hover:bg-slate-100 dark:hover:bg-slate-800 transition">
                                    <i data-lucide="pencil" class="w-4 h-4"></i>
                                </a>
                                <form action="{{ route('admin.jadwal.destroy', $j->id_jadwal) }}" method="POST" onsubmit="return confirm('Hapus jadwal ini? Data tidak dapat dipulihkan.')" class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" title="Hapus Jadwal" class="p-1.5 rounded-lg text-rose-500 hover:text-rose-700 hover:bg-rose-50 dark:hover:bg-rose-950/50 transition {{ $j->penempatan_count > 0 ? 'opacity-40 cursor-not-allowed' : '' }}" {{ $j->penempatan_count > 0 ? 'disabled' : '' }}>
                                        <i data-lucide="trash-2" class="w-4 h-4"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="py-8 text-center text-slate-400">
                            <i data-lucide="calendar-x" class="w-8 h-8 mx-auto mb-2 text-slate-300"></i>
                            <p class="font-semibold text-xs">Belum ada jadwal seleksi yang ditambahkan.</p>
                            <a href="{{ route('admin.jadwal.create') }}" class="inline-block mt-2 text-pink-600 hover:underline text-xs font-bold">+ Tambah Jadwal Baru</a>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($jadwals->hasPages())
        <div class="p-4 border-t border-slate-100 dark:border-slate-800">
            {{ $jadwals->links() }}
        </div>
        @endif
    </div>

</div>
@endsection
