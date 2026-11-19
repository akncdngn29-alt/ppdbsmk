@extends('layouts.siswa')

@section('title', 'Riwayat Aktivitas Saya')

@section('content')
<div class="space-y-6">

    <!-- Header Banner -->
    <div class="bg-gradient-to-r from-[#161c2d] via-[#1a142c] to-[#2b0e22] rounded-3xl p-6 sm:p-8 text-white shadow-xl relative overflow-hidden border border-pink-500/20">
        <div class="relative z-10 max-w-2xl">
            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-[#fa2f79]/20 border border-pink-500/30 text-pink-300 text-[11px] font-bold uppercase tracking-wider mb-3">
                <i data-lucide="shield-check" class="w-3.5 h-3.5 text-[#fa2f79]"></i>
                Keamanan & Akuntabilitas Akun
            </div>
            <h1 class="text-xl sm:text-2xl font-black tracking-tight">Riwayat Aktivitas Pendaftaran</h1>
            <p class="text-slate-300 text-xs sm:text-sm mt-2 leading-relaxed">
                Setiap tindakan penting pada akun Anda tercatat secara otomatis untuk memastikan keamanan data dan transparansi proses seleksi PPDB SMK Islam Bina Khalifah Bangsa.
            </p>
        </div>
        <div class="absolute -right-8 -bottom-10 opacity-10 pointer-events-none">
            <i data-lucide="history" class="w-64 h-64 text-white"></i>
        </div>
    </div>

    <!-- Ringkasan Statistik Siswa -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="bg-white dark:bg-slate-900 rounded-2xl p-5 border border-slate-200 dark:border-slate-800 shadow-sm flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-pink-100 text-[#fa2f79] dark:bg-pink-950/60 dark:text-pink-400 flex items-center justify-center shrink-0">
                <i data-lucide="activity" class="w-6 h-6"></i>
            </div>
            <div>
                <span class="text-xs text-slate-500 dark:text-slate-400 font-medium">Total Aktivitas</span>
                <p class="text-xl font-black text-slate-800 dark:text-white">{{ number_format($stats['total'] ?? 0) }}</p>
            </div>
        </div>

        <div class="bg-white dark:bg-slate-900 rounded-2xl p-5 border border-slate-200 dark:border-slate-800 shadow-sm flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-indigo-100 text-indigo-600 dark:bg-indigo-950/60 dark:text-indigo-400 flex items-center justify-center shrink-0">
                <i data-lucide="log-in" class="w-6 h-6"></i>
            </div>
            <div class="truncate">
                <span class="text-xs text-slate-500 dark:text-slate-400 font-medium">Sesi Login Terakhir</span>
                <p class="text-xs font-bold text-slate-800 dark:text-white truncate">
                    {{ $stats['login_terakhir'] ? \Carbon\Carbon::parse($stats['login_terakhir'])->format('d M Y, H:i') : 'Saat ini' }}
                </p>
            </div>
        </div>

        <div class="bg-white dark:bg-slate-900 rounded-2xl p-5 border border-slate-200 dark:border-slate-800 shadow-sm flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-amber-100 text-amber-600 dark:bg-amber-950/60 dark:text-amber-400 flex items-center justify-center shrink-0">
                <i data-lucide="download-cloud" class="w-6 h-6"></i>
            </div>
            <div>
                <span class="text-xs text-slate-500 dark:text-slate-400 font-medium">Dokumen Dicetak</span>
                <p class="text-xl font-black text-slate-800 dark:text-white">{{ number_format($stats['total_unduhan'] ?? 0) }} kali</p>
            </div>
        </div>
    </div>

    <!-- Filter & Pencarian -->
    <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 p-5 shadow-sm">
        <form action="{{ route('siswa.riwayat_aktivitas') }}" method="GET" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3">
            
            <!-- Kata Kunci Pencarian -->
            <div class="lg:col-span-2">
                <label class="block text-[11px] font-bold text-slate-600 dark:text-slate-400 uppercase mb-1">Cari Aktivitas</label>
                <div class="relative">
                    <i data-lucide="search" class="w-4 h-4 text-slate-400 absolute left-3 top-3"></i>
                    <input type="text" name="q" value="{{ request('q') }}" placeholder="Cari deskripsi, status..." 
                           class="w-full pl-9 pr-3 py-2 rounded-xl border border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-800/50 text-xs text-slate-800 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-emerald-500">
                </div>
            </div>

            <!-- Filter Kategori / Aksi -->
            <div>
                <label class="block text-[11px] font-bold text-slate-600 dark:text-slate-400 uppercase mb-1">Jenis Aktivitas</label>
                <select name="aksi" class="w-full px-3 py-2 rounded-xl border border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-800/50 text-xs text-slate-800 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-emerald-500">
                    <option value="">Semua Jenis</option>
                    @foreach($daftarAksi as $itemAksi)
                        <option value="{{ $itemAksi }}" {{ request('aksi') === $itemAksi ? 'selected' : '' }}>
                            {{ $itemAksi }}
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Filter Status -->
            <div>
                <label class="block text-[11px] font-bold text-slate-600 dark:text-slate-400 uppercase mb-1">Status</label>
                <select name="status" class="w-full px-3 py-2 rounded-xl border border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-800/50 text-xs text-slate-800 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-emerald-500">
                    <option value="">Semua Status</option>
                    <option value="SUCCESS" {{ request('status') === 'SUCCESS' ? 'selected' : '' }}>Sukses / Berhasil</option>
                    <option value="FAILED" {{ request('status') === 'FAILED' ? 'selected' : '' }}>Gagal</option>
                </select>
            </div>

            <!-- Filter Tanggal & Tombol Aksi -->
            <div class="flex items-end gap-2">
                <div class="flex-1">
                    <label class="block text-[11px] font-bold text-slate-600 dark:text-slate-400 uppercase mb-1">Tanggal</label>
                    <input type="date" name="tanggal" value="{{ request('tanggal') }}" 
                           class="w-full px-3 py-2 rounded-xl border border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-800/50 text-xs text-slate-800 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-emerald-500">
                </div>
                <button type="submit" class="px-4 py-2 rounded-xl bg-[#fa2f79] hover:bg-[#e11d48] text-white font-bold text-xs transition-colors shadow-sm shadow-pink-500/20" title="Terapkan Filter">
                    <i data-lucide="filter" class="w-4 h-4"></i>
                </button>
                @if(request()->anyFilled(['q', 'aksi', 'status', 'tanggal']))
                    <a href="{{ route('siswa.riwayat_aktivitas') }}" class="px-3 py-2 rounded-xl bg-slate-200 dark:bg-slate-800 hover:bg-slate-300 dark:hover:bg-slate-700 text-slate-600 dark:text-slate-300 font-bold text-xs transition-colors" title="Reset Filter">
                        <i data-lucide="rotate-ccw" class="w-4 h-4"></i>
                    </a>
                @endif
            </div>

        </form>
    </div>

    <!-- Tabel Rekam Jejak Aktivitas Siswa -->
    <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-sm overflow-hidden">
        <div class="p-5 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between">
            <h3 class="text-sm font-bold text-slate-800 dark:text-white flex items-center gap-2">
                <i data-lucide="list" class="w-4 h-4 text-emerald-600"></i>
                Daftar Riwayat Peristiwa Akun
            </h3>
            <span class="text-xs text-slate-500 dark:text-slate-400 font-medium">Menampilkan {{ $logs->total() }} catatan</span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-xs text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50 dark:bg-slate-800/60 text-slate-500 dark:text-slate-400 font-bold border-b border-slate-200 dark:border-slate-800">
                        <th class="py-3 px-4 w-44">Waktu</th>
                        <th class="py-3 px-4 text-center w-36">Kategori</th>
                        <th class="py-3 px-4">Deskripsi Aktivitas</th>
                        <th class="py-3 px-4 text-center w-28">Status</th>
                        <th class="py-3 px-4 w-36">IP & Perangkat</th>
                        <th class="py-3 px-4 text-center w-20">Rincian</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                    @forelse($logs as $log)
                        <tr class="hover:bg-slate-50/70 dark:hover:bg-slate-800/40 transition-colors">
                            
                            <!-- Waktu -->
                            <td class="py-3.5 px-4">
                                <div class="font-bold text-slate-800 dark:text-slate-200 font-mono text-[11px]">
                                    {{ $log->created_at->format('d/m/Y H:i:s') }}
                                </div>
                                <div class="text-[10px] text-slate-400 mt-0.5">
                                    {{ $log->created_at->diffForHumans() }}
                                </div>
                            </td>

                            <!-- Kategori Aksi -->
                            <td class="py-3.5 px-4 text-center">
                                @php
                                    $aksi = strtoupper($log->aksi ?? '');
                                    $badgeClass = 'bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-300';
                                    if(str_contains($aksi, 'LOGIN_GAGAL')) {
                                        $badgeClass = 'bg-rose-100 text-rose-800 dark:bg-rose-950 dark:text-rose-300 font-bold';
                                    } elseif(str_contains($aksi, 'LOGIN') || str_contains($aksi, 'LOGOUT')) {
                                        $badgeClass = 'bg-indigo-100 text-indigo-800 dark:bg-indigo-950 dark:text-indigo-300 font-bold';
                                    } elseif(str_contains($aksi, 'VERIFIKASI') || str_contains($aksi, 'PENGESAHAN')) {
                                        $badgeClass = 'bg-emerald-100 text-emerald-800 dark:bg-emerald-950 dark:text-emerald-300 font-bold';
                                    } elseif(str_contains($aksi, 'PASSWORD') || str_contains($aksi, 'PROFIL')) {
                                        $badgeClass = 'bg-purple-100 text-purple-800 dark:bg-purple-950 dark:text-purple-300';
                                    } elseif(str_contains($aksi, 'BERKAS') || str_contains($aksi, 'UNGGAH')) {
                                        $badgeClass = 'bg-blue-100 text-blue-800 dark:bg-blue-950 dark:text-blue-300';
                                    } elseif(str_contains($aksi, 'UNDUH')) {
                                        $badgeClass = 'bg-amber-100 text-amber-800 dark:bg-amber-950 dark:text-amber-300';
                                    } elseif(str_contains($aksi, 'BAYAR') || str_contains($aksi, 'PAYMENT')) {
                                        $badgeClass = 'bg-teal-100 text-teal-800 dark:bg-teal-950 dark:text-teal-300';
                                    }
                                @endphp
                                <span class="inline-block px-2.5 py-1 rounded-full text-[10px] font-bold {{ $badgeClass }}">
                                    {{ $log->aksi ?: '-' }}
                                </span>
                            </td>

                            <!-- Deskripsi -->
                            <td class="py-3.5 px-4 text-slate-800 dark:text-slate-200">
                                <p class="leading-relaxed font-medium">{{ $log->deskripsi }}</p>
                                @if($log->tabel_terkait)
                                    <div class="text-[10px] text-slate-400 font-mono mt-0.5">
                                        Objek: {{ $log->tabel_terkait }}@if($log->id_data) #{{ $log->id_data }}@endif
                                    </div>
                                @endif
                            </td>

                            <!-- Status -->
                            <td class="py-3.5 px-4 text-center">
                                @if(strtoupper($log->status) === 'FAILED')
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-black bg-rose-100 text-rose-700 dark:bg-rose-950/80 dark:text-rose-300">
                                        <i data-lucide="alert-circle" class="w-3 h-3"></i> Gagal
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-black bg-emerald-100 text-emerald-700 dark:bg-emerald-950/80 dark:text-emerald-300">
                                        <i data-lucide="check-circle" class="w-3 h-3"></i> Berhasil
                                    </span>
                                @endif
                            </td>

                            <!-- IP & Perangkat -->
                            <td class="py-3.5 px-4">
                                <div class="font-mono text-[11px] text-slate-600 dark:text-slate-400">
                                    {{ $log->ip_address ?: '127.0.0.1' }}
                                </div>
                                @if($log->user_agent)
                                    <div class="text-[9px] text-slate-400 truncate max-w-[130px]" title="{{ $log->user_agent }}">
                                        {{ Str::limit($log->user_agent, 20) }}
                                    </div>
                                @endif
                            </td>

                            <!-- Rincian Modal -->
                            <td class="py-3.5 px-4 text-center">
                                @if($log->data_lama || $log->data_baru)
                                    <button type="button" onclick="showSiswaLogDetail({{ json_encode($log->deskripsi) }}, {{ json_encode($log->data_lama) }}, {{ json_encode($log->data_baru) }})"
                                            class="px-2 py-1 rounded-lg bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 text-[10px] font-bold transition-colors">
                                        Lihat
                                    </button>
                                @else
                                    <span class="text-slate-300 dark:text-slate-600 text-[10px]">-</span>
                                @endif
                            </td>

                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-12 text-center text-slate-400">
                                <i data-lucide="inbox" class="w-8 h-8 mx-auto mb-2 text-slate-300"></i>
                                Belum ada riwayat aktivitas yang sesuai kriteria pencarian.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="p-4 border-t border-slate-100 dark:border-slate-800">
            {{ $logs->links() }}
        </div>
    </div>

</div>

<!-- Modal Rincian Aktivitas Siswa -->
<div id="siswaLogModal" class="fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-sm hidden flex items-center justify-center p-4">
    <div class="bg-white dark:bg-slate-900 rounded-2xl max-w-lg w-full border border-slate-200 dark:border-slate-800 shadow-xl overflow-hidden">
        <div class="p-5 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between">
            <h3 class="text-sm font-bold text-slate-800 dark:text-white flex items-center gap-2">
                <i data-lucide="info" class="w-4 h-4 text-emerald-600"></i>
                Rincian Informasi Aktivitas
            </h3>
            <button onclick="closeSiswaLogModal()" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200">
                <i data-lucide="x" class="w-5 h-5"></i>
            </button>
        </div>
        <div class="p-5 space-y-4 text-xs font-mono">
            <p id="modalSiswaDeskripsi" class="font-sans text-xs font-semibold text-slate-700 dark:text-slate-300"></p>
            <div id="wrapperModalLama">
                <span class="text-[11px] font-bold text-slate-500 dark:text-slate-400 block mb-1">Data Sebelumnya:</span>
                <pre id="modalSiswaLama" class="p-3 rounded-xl bg-slate-50 dark:bg-slate-800 text-slate-800 dark:text-slate-200 text-[11px] overflow-x-auto max-h-36 border border-slate-200 dark:border-slate-700"></pre>
            </div>
            <div id="wrapperModalBaru">
                <span class="text-[11px] font-bold text-emerald-600 block mb-1">Data Perubahan / Catatan:</span>
                <pre id="modalSiswaBaru" class="p-3 rounded-xl bg-slate-50 dark:bg-slate-800 text-slate-800 dark:text-slate-200 text-[11px] overflow-x-auto max-h-36 border border-slate-200 dark:border-slate-700"></pre>
            </div>
        </div>
        <div class="p-4 border-t border-slate-100 dark:border-slate-800 flex justify-end">
            <button type="button" onclick="closeSiswaLogModal()" class="px-4 py-2 rounded-xl bg-[#fa2f79] hover:bg-[#e11d48] text-white font-bold text-xs transition-colors">
                Tutup
            </button>
        </div>
    </div>
</div>

@push('scripts')
<script>
    function showSiswaLogDetail(deskripsi, lama, baru) {
        document.getElementById('modalSiswaDeskripsi').textContent = deskripsi;
        document.getElementById('modalSiswaLama').textContent = lama ? JSON.stringify(lama, null, 2) : '-';
        document.getElementById('modalSiswaBaru').textContent = baru ? JSON.stringify(baru, null, 2) : '-';
        document.getElementById('siswaLogModal').classList.remove('hidden');
    }
    function closeSiswaLogModal() {
        document.getElementById('siswaLogModal').classList.add('hidden');
    }
</script>
@endpush
@endsection

