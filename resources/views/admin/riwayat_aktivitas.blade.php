@extends('layouts.admin')

@section('title', 'Riwayat Aktivitas')
@section('header_title', 'Riwayat Aktivitas & Audit Operasional TU')

@section('content')
<div class="space-y-6">

    <!-- Header Banner -->
    <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 p-6 shadow-sm">
        <div class="flex items-start gap-4">
            <div class="w-12 h-12 rounded-xl bg-emerald-100 text-emerald-600 dark:bg-emerald-950/60 dark:text-emerald-400 flex items-center justify-center shrink-0">
                <i data-lucide="history" class="w-6 h-6"></i>
            </div>
            <div>
                <h2 class="text-base font-bold text-slate-800 dark:text-white">Audit Trail & Log Aktivitas Terintegrasi</h2>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-1 max-w-3xl leading-relaxed">
                    Pantau seluruh jejak operasional pendaftaran Calon Siswa (login, formulir, berkas, pembayaran, unduh kartu) serta rekam jejak verifikasi berkas dan tindakan administratif Petugas Tata Usaha (TU).
                </p>
            </div>
        </div>
    </div>

    <!-- Ringkasan Statistik Log TU -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-white dark:bg-slate-900 rounded-2xl p-5 border border-slate-200 dark:border-slate-800 shadow-sm flex items-center gap-4">
            <div class="w-11 h-11 rounded-xl bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-300 flex items-center justify-center shrink-0">
                <i data-lucide="database" class="w-5 h-5"></i>
            </div>
            <div>
                <span class="text-[11px] text-slate-500 dark:text-slate-400 font-bold uppercase">Total Log</span>
                <p class="text-lg font-black text-slate-800 dark:text-white">{{ number_format($stats['total'] ?? 0) }}</p>
            </div>
        </div>

        <div class="bg-white dark:bg-slate-900 rounded-2xl p-5 border border-slate-200 dark:border-slate-800 shadow-sm flex items-center gap-4">
            <div class="w-11 h-11 rounded-xl bg-sky-100 text-sky-600 dark:bg-sky-950/60 dark:text-sky-400 flex items-center justify-center shrink-0">
                <i data-lucide="users" class="w-5 h-5"></i>
            </div>
            <div>
                <span class="text-[11px] text-slate-500 dark:text-slate-400 font-bold uppercase">Aktivitas Siswa</span>
                <p class="text-lg font-black text-slate-800 dark:text-white">{{ number_format($stats['siswa'] ?? 0) }}</p>
            </div>
        </div>

        <div class="bg-white dark:bg-slate-900 rounded-2xl p-5 border border-slate-200 dark:border-slate-800 shadow-sm flex items-center gap-4">
            <div class="w-11 h-11 rounded-xl bg-emerald-100 text-emerald-600 dark:bg-emerald-950/60 dark:text-emerald-400 flex items-center justify-center shrink-0">
                <i data-lucide="user-cog" class="w-5 h-5"></i>
            </div>
            <div>
                <span class="text-[11px] text-slate-500 dark:text-slate-400 font-bold uppercase">Operasional TU</span>
                <p class="text-lg font-black text-slate-800 dark:text-white">{{ number_format($stats['tu'] ?? 0) }}</p>
            </div>
        </div>

        <div class="bg-white dark:bg-slate-900 rounded-2xl p-5 border border-slate-200 dark:border-slate-800 shadow-sm flex items-center gap-4">
            <div class="w-11 h-11 rounded-xl bg-rose-100 text-rose-600 dark:bg-rose-950/60 dark:text-rose-400 flex items-center justify-center shrink-0">
                <i data-lucide="shield-alert" class="w-5 h-5"></i>
            </div>
            <div>
                <span class="text-[11px] text-slate-500 dark:text-slate-400 font-bold uppercase">Percobaan Gagal</span>
                <p class="text-lg font-black text-rose-600 dark:text-rose-400">{{ number_format($stats['gagal'] ?? 0) }}</p>
            </div>
        </div>
    </div>

    <!-- Tab Filter Lingkup Aktivitas -->
    <div class="flex items-center gap-2 border-b border-slate-200 dark:border-slate-800 pb-3">
        <a href="{{ route('admin.riwayat_aktivitas', array_merge(request()->except(['tab', 'page']), ['tab' => 'all'])) }}" 
           class="px-4 py-2 rounded-xl text-xs font-bold transition-colors flex items-center gap-2 {{ ($tab ?? 'all') === 'all' ? 'bg-[#fa2f79] text-white shadow-sm shadow-pink-500/20' : 'bg-white dark:bg-slate-900 text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 border border-slate-200 dark:border-slate-800' }}">
            <i data-lucide="layers" class="w-3.5 h-3.5"></i>
            Semua Aktivitas
        </a>
        <a href="{{ route('admin.riwayat_aktivitas', array_merge(request()->except(['tab', 'page']), ['tab' => 'siswa'])) }}" 
           class="px-4 py-2 rounded-xl text-xs font-bold transition-colors flex items-center gap-2 {{ ($tab ?? '') === 'siswa' ? 'bg-[#fa2f79] text-white shadow-sm shadow-pink-500/20' : 'bg-white dark:bg-slate-900 text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 border border-slate-200 dark:border-slate-800' }}">
            <i data-lucide="graduation-cap" class="w-3.5 h-3.5"></i>
            Aktivitas Calon Siswa
        </a>
        <a href="{{ route('admin.riwayat_aktivitas', array_merge(request()->except(['tab', 'page']), ['tab' => 'tu'])) }}" 
           class="px-4 py-2 rounded-xl text-xs font-bold transition-colors flex items-center gap-2 {{ ($tab ?? '') === 'tu' ? 'bg-[#fa2f79] text-white shadow-sm shadow-pink-500/20' : 'bg-white dark:bg-slate-900 text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 border border-slate-200 dark:border-slate-800' }}">
            <i data-lucide="briefcase" class="w-3.5 h-3.5"></i>
            Aktivitas Petugas TU
        </a>
    </div>

    <!-- Filter & Pencarian Lengkap -->
    <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 p-5 shadow-sm">
        <form action="{{ route('admin.riwayat_aktivitas') }}" method="GET" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-6 gap-3">
            <input type="hidden" name="tab" value="{{ $tab ?? 'all' }}">

            <!-- Pencarian Teks Bebas (Nama siswa, No Pendaftaran, Deskripsi) -->
            <div class="lg:col-span-2">
                <label class="block text-[11px] font-bold text-slate-600 dark:text-slate-400 uppercase mb-1">Cari Kata Kunci / Nama / No. Reg</label>
                <div class="relative">
                    <i data-lucide="search" class="w-4 h-4 text-slate-400 absolute left-3 top-3"></i>
                    <input type="text" name="q" value="{{ request('q') }}" placeholder="Nama siswa, no pendaftaran, aksi..." 
                           class="w-full pl-9 pr-3 py-2 rounded-xl border border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-800/50 text-xs text-slate-800 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-emerald-500">
                </div>
            </div>

            <!-- Filter Jenis Aksi -->
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
                    <option value="SUCCESS" {{ request('status') === 'SUCCESS' ? 'selected' : '' }}>Sukses (SUCCESS)</option>
                    <option value="FAILED" {{ request('status') === 'FAILED' ? 'selected' : '' }}>Gagal (FAILED)</option>
                </select>
            </div>

            <!-- Tanggal Mulai -->
            <div>
                <label class="block text-[11px] font-bold text-slate-600 dark:text-slate-400 uppercase mb-1">Tgl Mulai</label>
                <input type="date" name="tanggal_mulai" value="{{ request('tanggal_mulai') }}" 
                       class="w-full px-3 py-2 rounded-xl border border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-800/50 text-xs text-slate-800 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-emerald-500">
            </div>

            <!-- Tanggal Akhir & Tombol Submit -->
            <div class="flex items-end gap-2">
                <div class="flex-1">
                    <label class="block text-[11px] font-bold text-slate-600 dark:text-slate-400 uppercase mb-1">Tgl Akhir</label>
                    <input type="date" name="tanggal_akhir" value="{{ request('tanggal_akhir') }}" 
                           class="w-full px-3 py-2 rounded-xl border border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-800/50 text-xs text-slate-800 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-emerald-500">
                </div>
                <button type="submit" class="px-4 py-2 rounded-xl bg-[#fa2f79] hover:bg-[#e11d48] text-white font-bold text-xs transition-colors shadow-sm shadow-pink-500/20" title="Terapkan Filter">
                    <i data-lucide="filter" class="w-4 h-4"></i>
                </button>
                @if(request()->anyFilled(['q', 'aksi', 'status', 'tanggal_mulai', 'tanggal_akhir']))
                    <a href="{{ route('admin.riwayat_aktivitas', ['tab' => $tab ?? 'all']) }}" class="px-3 py-2 rounded-xl bg-slate-200 dark:bg-slate-800 hover:bg-slate-300 dark:hover:bg-slate-700 text-slate-600 dark:text-slate-300 font-bold text-xs transition-colors" title="Reset Filter">
                        <i data-lucide="rotate-ccw" class="w-4 h-4"></i>
                    </a>
                @endif
            </div>

        </form>
    </div>

    <!-- Tabel Riwayat Aktivitas Admin TU -->
    <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-sm overflow-hidden">
        <div class="p-5 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between">
            <h3 class="text-sm font-bold text-slate-800 dark:text-white flex items-center gap-2">
                <i data-lucide="list-checks" class="w-4 h-4 text-emerald-600"></i>
                Daftar Peristiwa & Catatan Log
            </h3>
            <span class="text-xs text-slate-500 dark:text-slate-400 font-medium">Total: {{ $logs->total() }} catatan</span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-xs text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50 dark:bg-slate-800/60 text-slate-500 dark:text-slate-400 font-bold border-b border-slate-200 dark:border-slate-800">
                        <th class="py-3 px-4 w-40">Waktu</th>
                        <th class="py-3 px-4 w-44">Aktor / Pelaku</th>
                        <th class="py-3 px-4 w-48">Siswa Terkait</th>
                        <th class="py-3 px-4 text-center w-36">Kategori</th>
                        <th class="py-3 px-4">Deskripsi Aktivitas</th>
                        <th class="py-3 px-4 text-center w-24">Status</th>
                        <th class="py-3 px-4 w-36">IP & Perangkat</th>
                        <th class="py-3 px-4 text-center w-20">Diff</th>
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

                            <!-- Aktor / Pelaku -->
                            <td class="py-3.5 px-4">
                                @if($log->admin)
                                    <div class="font-bold text-slate-900 dark:text-white flex items-center gap-1.5 truncate">
                                        <i data-lucide="shield-check" class="w-3.5 h-3.5 text-emerald-600 shrink-0"></i>
                                        <span class="truncate">{{ $log->admin->nama_admin }}</span>
                                    </div>
                                    <span class="inline-block mt-0.5 px-1.5 py-0.5 rounded text-[9px] font-bold uppercase {{ $log->admin->role === 'kepala_sekolah' ? 'bg-purple-100 text-purple-700 dark:bg-purple-950 dark:text-purple-300' : 'bg-emerald-100 text-emerald-700 dark:bg-emerald-950 dark:text-emerald-300' }}">
                                        {{ $log->admin->role === 'kepala_sekolah' ? 'Kepala Sekolah' : 'Petugas TU' }}
                                    </span>
                                @elseif($log->user)
                                    <div class="font-bold text-slate-900 dark:text-white flex items-center gap-1.5 truncate">
                                        <i data-lucide="user" class="w-3.5 h-3.5 text-sky-600 shrink-0"></i>
                                        <span class="truncate">{{ $log->user->nama ?? $log->user->username ?? 'Calon Siswa' }}</span>
                                    </div>
                                    <span class="inline-block mt-0.5 px-1.5 py-0.5 rounded text-[9px] font-bold bg-sky-100 text-sky-700 dark:bg-sky-950 dark:text-sky-300 uppercase">
                                        Calon Siswa
                                    </span>
                                @else
                                    <span class="text-slate-400 italic text-[11px]">Sistem Otomatis</span>
                                @endif
                            </td>

                            <!-- Siswa Terkait -->
                            <td class="py-3.5 px-4">
                                @if($log->user)
                                    <div class="font-bold text-slate-800 dark:text-slate-200 truncate">
                                        {{ $log->user->nama ?? 'Siswa #' . $log->id_user }}
                                    </div>
                                    <div class="text-[10px] text-slate-400 font-mono mt-0.5 truncate">
                                        @if($log->user->pendaftar)
                                            No. Reg: {{ $log->user->pendaftar->no_pendaftaran }}
                                        @else
                                            NISN: {{ $log->user->nisn ?? '-' }}
                                        @endif
                                    </div>
                                @else
                                    <span class="text-slate-400 italic text-[10px]">-</span>
                                @endif
                            </td>

                            <!-- Kategori Aksi -->
                            <td class="py-3.5 px-4 text-center">
                                @php
                                    $aksi = strtoupper($log->aksi ?? '');
                                    $badgeClass = 'bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-300';
                                    if(str_contains($aksi, 'LOGIN_GAGAL')) {
                                        $badgeClass = 'bg-rose-100 text-rose-800 dark:bg-rose-950 dark:text-rose-300 font-bold';
                                    } elseif(str_contains($aksi, 'VERIFIKASI') || str_contains($aksi, 'PENGESAHAN')) {
                                        $badgeClass = 'bg-teal-100 text-teal-800 dark:bg-teal-950 dark:text-teal-300 font-bold';
                                    } elseif(str_contains($aksi, 'UNLOCK')) {
                                        $badgeClass = 'bg-amber-100 text-amber-800 dark:bg-amber-950 dark:text-amber-300 font-black';
                                    } elseif(str_contains($aksi, 'LOGIN') || str_contains($aksi, 'LOGOUT')) {
                                        $badgeClass = 'bg-indigo-100 text-indigo-800 dark:bg-indigo-950 dark:text-indigo-300';
                                    } elseif(str_contains($aksi, 'EKSPOR') || str_contains($aksi, 'UNDUH')) {
                                        $badgeClass = 'bg-cyan-100 text-cyan-800 dark:bg-cyan-950 dark:text-cyan-300';
                                    } elseif(str_contains($aksi, 'BAYAR') || str_contains($aksi, 'PAYMENT')) {
                                        $badgeClass = 'bg-lime-100 text-lime-800 dark:bg-lime-950 dark:text-lime-300';
                                    } elseif(str_contains($aksi, 'PASSWORD') || str_contains($aksi, 'PROFIL')) {
                                        $badgeClass = 'bg-purple-100 text-purple-800 dark:bg-purple-950 dark:text-purple-300';
                                    }
                                @endphp
                                <span class="inline-block px-2 py-0.5 rounded-full text-[10px] font-bold {{ $badgeClass }}">
                                    {{ $log->aksi ?: '-' }}
                                </span>
                            </td>

                            <!-- Deskripsi -->
                            <td class="py-3.5 px-4 text-slate-800 dark:text-slate-200">
                                <p class="leading-relaxed">{{ $log->deskripsi }}</p>
                                @if($log->tabel_terkait)
                                    <div class="text-[10px] text-slate-400 font-mono mt-0.5">
                                        Tabel: {{ $log->tabel_terkait }}@if($log->id_data) #{{ $log->id_data }}@endif
                                    </div>
                                @endif
                            </td>

                            <!-- Status -->
                            <td class="py-3.5 px-4 text-center">
                                @if(strtoupper($log->status) === 'FAILED')
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-black bg-rose-100 text-rose-700 dark:bg-rose-950 dark:text-rose-300">
                                        <i data-lucide="alert-circle" class="w-3 h-3"></i> Gagal
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-black bg-emerald-100 text-emerald-700 dark:bg-emerald-950 dark:text-emerald-300">
                                        <i data-lucide="check-circle" class="w-3 h-3"></i> Sukses
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
                                        {{ Str::limit($log->user_agent, 18) }}
                                    </div>
                                @endif
                            </td>

                            <!-- Data Perubahan (Diff Modal) -->
                            <td class="py-3.5 px-4 text-center">
                                @if($log->data_lama || $log->data_baru)
                                    <button type="button" onclick="showAdminLogDetail({{ json_encode($log->deskripsi) }}, {{ json_encode($log->data_lama) }}, {{ json_encode($log->data_baru) }})"
                                            class="px-2 py-1 rounded-lg bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 text-[10px] font-bold hover:bg-slate-200 dark:hover:bg-slate-700 transition-colors">
                                        Diff
                                    </button>
                                @else
                                    <span class="text-slate-300 dark:text-slate-600 text-[10px]">-</span>
                                @endif
                            </td>

                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="py-12 text-center text-slate-400">
                                <i data-lucide="inbox" class="w-8 h-8 mx-auto mb-2 text-slate-300"></i>
                                Belum ada riwayat aktivitas yang sesuai filter pencarian.
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

<!-- Modal Detail JSON Diff -->
<div id="adminLogModal" class="fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-sm hidden flex items-center justify-center p-4">
    <div class="bg-white dark:bg-slate-900 rounded-2xl max-w-lg w-full border border-slate-200 dark:border-slate-800 shadow-xl overflow-hidden">
        <div class="p-5 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between">
            <h3 class="text-sm font-bold text-slate-800 dark:text-white flex items-center gap-2">
                <i data-lucide="code" class="w-4 h-4 text-emerald-600"></i> Rincian Perubahan Data
            </h3>
            <button onclick="closeAdminLogModal()" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200">
                <i data-lucide="x" class="w-5 h-5"></i>
            </button>
        </div>
        <div class="p-5 space-y-4 text-xs font-mono">
            <p id="modalAdminDeskripsi" class="font-sans text-xs font-semibold text-slate-700 dark:text-slate-300"></p>
            <div>
                <span class="text-[11px] font-bold text-rose-600 block mb-1">Data Sebelumnya:</span>
                <pre id="modalAdminLama" class="p-3 rounded-xl bg-slate-50 dark:bg-slate-800 text-slate-800 dark:text-slate-200 text-[11px] overflow-x-auto max-h-40 border border-slate-200 dark:border-slate-700"></pre>
            </div>
            <div>
                <span class="text-[11px] font-bold text-emerald-600 block mb-1">Data Sesudahnya:</span>
                <pre id="modalAdminBaru" class="p-3 rounded-xl bg-slate-50 dark:bg-slate-800 text-slate-800 dark:text-slate-200 text-[11px] overflow-x-auto max-h-40 border border-slate-200 dark:border-slate-700"></pre>
            </div>
        </div>
        <div class="p-4 border-t border-slate-100 dark:border-slate-800 flex justify-end">
            <button type="button" onclick="closeAdminLogModal()" class="px-4 py-2 rounded-xl bg-[#fa2f79] hover:bg-[#e11d48] text-white font-bold text-xs transition-colors">
                Tutup
            </button>
        </div>
    </div>
</div>

@push('scripts')
<script>
    function showAdminLogDetail(deskripsi, lama, baru) {
        document.getElementById('modalAdminDeskripsi').textContent = deskripsi;
        document.getElementById('modalAdminLama').textContent = lama ? JSON.stringify(lama, null, 2) : 'null (Tidak ada data lama)';
        document.getElementById('modalAdminBaru').textContent = baru ? JSON.stringify(baru, null, 2) : 'null (Tidak ada data baru)';
        document.getElementById('adminLogModal').classList.remove('hidden');
    }
    function closeAdminLogModal() {
        document.getElementById('adminLogModal').classList.add('hidden');
    }
</script>
@endpush
@endsection

