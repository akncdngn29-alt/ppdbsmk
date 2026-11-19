@extends('layouts.admin')

@section('title', 'Audit Trail & Rekam Jejak Sistem')
@section('header_title', 'Audit Trail & Pengawasan Eksekutif')

@section('content')
<div class="space-y-6">

    <!-- Header Info Card -->
    <div class="bg-gradient-to-r from-[#0B182F] via-[#112240] to-[#0B182F] rounded-2xl p-6 sm:p-7 text-white shadow-xl relative overflow-hidden border border-slate-800">
        <div class="relative z-10 flex flex-col md:flex-row md:items-center justify-between gap-6">
            <div class="flex items-start gap-4">
                <div class="w-12 h-12 rounded-xl bg-rose-500/20 border border-rose-500/30 text-rose-400 flex items-center justify-center shrink-0">
                    <i data-lucide="shield-check" class="w-6 h-6"></i>
                </div>
                <div>
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase tracking-wider bg-rose-500/20 text-rose-300 border border-rose-500/30 mb-1.5">
                        Integritas Data Lembaga
                    </span>
                    <h2 class="text-lg font-black text-white">Transparansi & Akuntabilitas Sistem PPDB</h2>
                    <p class="text-xs text-slate-300 mt-1 max-w-3xl leading-relaxed">
                        Sebagai pimpinan satuan pendidikan, Kepala Sekolah memiliki akses pengawasan menyeluruh terhadap seluruh aktivitas sistem: registrasi akun, verifikasi berkas dan pembukaan kunci formulir oleh TU, mutasi kuota, pengesahan SKL, hingga ekspor data resmi.
                    </p>
                </div>
            </div>
            <div class="shrink-0">
                <span class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl bg-slate-800/90 text-xs font-semibold text-slate-200 border border-slate-700">
                    <i data-lucide="lock" class="w-4 h-4 text-rose-400"></i>
                    Audit Trail Permanen
                </span>
            </div>
        </div>
        <div class="absolute -right-8 -bottom-8 w-44 h-44 bg-rose-500/10 rounded-full blur-2xl pointer-events-none"></div>
    </div>

    <!-- Ringkasan Statistik Pengawasan -->
    <div class="grid grid-cols-2 lg:grid-cols-5 gap-3">
        <div class="bg-white dark:bg-slate-900 rounded-2xl p-4 border border-slate-200 dark:border-slate-800 shadow-sm flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-300 flex items-center justify-center shrink-0">
                <i data-lucide="activity" class="w-5 h-5"></i>
            </div>
            <div>
                <span class="text-[10px] text-slate-500 dark:text-slate-400 font-bold uppercase">Total Log</span>
                <p class="text-base font-black text-slate-800 dark:text-white">{{ number_format($stats['total'] ?? 0) }}</p>
            </div>
        </div>

        <div class="bg-white dark:bg-slate-900 rounded-2xl p-4 border border-slate-200 dark:border-slate-800 shadow-sm flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-sky-50 text-sky-600 dark:bg-sky-950/60 dark:text-sky-400 flex items-center justify-center shrink-0">
                <i data-lucide="graduation-cap" class="w-5 h-5"></i>
            </div>
            <div>
                <span class="text-[10px] text-slate-500 dark:text-slate-400 font-bold uppercase">Calon Siswa</span>
                <p class="text-base font-black text-slate-800 dark:text-white">{{ number_format($stats['siswa'] ?? 0) }}</p>
            </div>
        </div>

        <div class="bg-white dark:bg-slate-900 rounded-2xl p-4 border border-slate-200 dark:border-slate-800 shadow-sm flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-600 dark:bg-emerald-950/60 dark:text-emerald-400 flex items-center justify-center shrink-0">
                <i data-lucide="briefcase" class="w-5 h-5"></i>
            </div>
            <div>
                <span class="text-[10px] text-slate-500 dark:text-slate-400 font-bold uppercase">Petugas TU</span>
                <p class="text-base font-black text-slate-800 dark:text-white">{{ number_format($stats['tu'] ?? 0) }}</p>
            </div>
        </div>

        <div class="bg-white dark:bg-slate-900 rounded-2xl p-4 border border-slate-200 dark:border-slate-800 shadow-sm flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-rose-50 text-rose-600 dark:bg-rose-950/60 dark:text-rose-400 flex items-center justify-center shrink-0">
                <i data-lucide="crown" class="w-5 h-5"></i>
            </div>
            <div>
                <span class="text-[10px] text-slate-500 dark:text-slate-400 font-bold uppercase">Kepala Sekolah</span>
                <p class="text-base font-black text-rose-600 dark:text-rose-400">{{ number_format($stats['kepsek'] ?? 0) }}</p>
            </div>
        </div>

        <div class="bg-white dark:bg-slate-900 rounded-2xl p-4 border border-slate-200 dark:border-slate-800 shadow-sm flex items-center gap-3 col-span-2 sm:col-span-1">
            <div class="w-10 h-10 rounded-xl bg-amber-50 text-amber-600 dark:bg-amber-950/60 dark:text-amber-400 flex items-center justify-center shrink-0">
                <i data-lucide="shield-alert" class="w-5 h-5"></i>
            </div>
            <div>
                <span class="text-[10px] text-slate-500 dark:text-slate-400 font-bold uppercase">Percobaan Gagal</span>
                <p class="text-base font-black text-amber-600 dark:text-amber-400">{{ number_format($stats['gagal'] ?? 0) }}</p>
            </div>
        </div>
    </div>

    <!-- Filter & Pencarian Lengkap Audit Trail -->
    <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 p-5 shadow-sm">
        <form action="{{ route('kepsek.audit_log') }}" method="GET" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-6 gap-3">

            <!-- Kata Kunci Pencarian -->
            <div class="lg:col-span-2">
                <label class="block text-[11px] font-bold text-slate-600 dark:text-slate-400 uppercase mb-1">Cari Kata Kunci / Aktor / No. Reg</label>
                <div class="relative">
                    <i data-lucide="search" class="w-4 h-4 text-slate-400 absolute left-3 top-3"></i>
                    <input type="text" name="q" value="{{ request('q') }}" placeholder="Nama aktor, siswa, deskripsi, IP..." 
                           class="w-full pl-9 pr-3 py-2 rounded-xl border border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-800/50 text-xs text-slate-800 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-rose-500">
                </div>
            </div>

            <!-- Filter Role -->
            <div>
                <label class="block text-[11px] font-bold text-slate-600 dark:text-slate-400 uppercase mb-1">Peran Aktor</label>
                <select name="role" class="w-full px-3 py-2 rounded-xl border border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-800/50 text-xs text-slate-800 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-rose-500">
                    <option value="">Semua Peran</option>
                    <option value="calon_siswa" {{ request('role') === 'calon_siswa' ? 'selected' : '' }}>Calon Siswa</option>
                    <option value="tu" {{ request('role') === 'tu' ? 'selected' : '' }}>Petugas TU</option>
                    <option value="kepala_sekolah" {{ request('role') === 'kepala_sekolah' ? 'selected' : '' }}>Kepala Sekolah</option>
                </select>
            </div>

            <!-- Filter Jenis Aksi -->
            <div>
                <label class="block text-[11px] font-bold text-slate-600 dark:text-slate-400 uppercase mb-1">Jenis Aktivitas</label>
                <select name="aksi" class="w-full px-3 py-2 rounded-xl border border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-800/50 text-xs text-slate-800 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-rose-500">
                    <option value="">Semua Jenis</option>
                    @foreach($daftarAksi as $itemAksi)
                        <option value="{{ $itemAksi }}" {{ request('aksi') === $itemAksi ? 'selected' : '' }}>
                            {{ $itemAksi }}
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Tanggal Mulai -->
            <div>
                <label class="block text-[11px] font-bold text-slate-600 dark:text-slate-400 uppercase mb-1">Tgl Mulai</label>
                <input type="date" name="tanggal_mulai" value="{{ request('tanggal_mulai') }}" 
                       class="w-full px-3 py-2 rounded-xl border border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-800/50 text-xs text-slate-800 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-rose-500">
            </div>

            <!-- Tanggal Akhir & Tombol Submit -->
            <div class="flex items-end gap-2">
                <div class="flex-1">
                    <label class="block text-[11px] font-bold text-slate-600 dark:text-slate-400 uppercase mb-1">Tgl Akhir</label>
                    <input type="date" name="tanggal_akhir" value="{{ request('tanggal_akhir') }}" 
                           class="w-full px-3 py-2 rounded-xl border border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-800/50 text-xs text-slate-800 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-rose-500">
                </div>
                <button type="submit" class="px-4 py-2 rounded-xl bg-rose-500 hover:bg-rose-600 text-white font-bold text-xs transition-all shadow-md shadow-rose-500/20" title="Terapkan Filter">
                    <i data-lucide="filter" class="w-4 h-4"></i>
                </button>
                @if(request()->anyFilled(['q', 'role', 'aksi', 'status', 'tanggal_mulai', 'tanggal_akhir']))
                    <a href="{{ route('kepsek.audit_log') }}" class="px-3 py-2 rounded-xl bg-slate-200 dark:bg-slate-800 hover:bg-slate-300 dark:hover:bg-slate-700 text-slate-600 dark:text-slate-300 font-bold text-xs transition-colors" title="Reset Filter">
                        <i data-lucide="rotate-ccw" class="w-4 h-4"></i>
                    </a>
                @endif
            </div>

        </form>
    </div>

    <!-- Tabel Rekam Jejak Aktivitas -->
    <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-sm overflow-hidden">
        <div class="p-5 sm:p-6 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between">
            <h3 class="text-sm font-bold text-slate-800 dark:text-white flex items-center gap-2">
                <i data-lucide="activity" class="w-4 h-4 text-rose-500"></i>
                Daftar Riwayat Aktivitas & Perubahan
            </h3>
            <span class="text-xs font-semibold px-2.5 py-1 rounded-lg bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300">
                Total: {{ $logs->total() }} Peristiwa
            </span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-xs text-left border-collapse">
                <thead>
                    <tr class="bg-slate-900 text-slate-200 font-bold border-b border-slate-800 uppercase text-[10px] tracking-wider">
                        <th class="py-3.5 px-4 w-40">Waktu</th>
                        <th class="py-3.5 px-4 w-44">Pelaku / Aktor</th>
                        <th class="py-3.5 px-4 w-44">Siswa Terkait</th>
                        <th class="py-3.5 px-4 text-center w-32">Kategori</th>
                        <th class="py-3.5 px-4">Deskripsi Aktivitas</th>
                        <th class="py-3.5 px-4 text-center w-24">Status</th>
                        <th class="py-3.5 px-4 w-36">IP & Perangkat</th>
                        <th class="py-3.5 px-4 text-center w-20">Diff</th>
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
                                <div class="text-[10px] text-slate-400">
                                    {{ $log->created_at->diffForHumans() }}
                                </div>
                            </td>

                            <!-- Pelaku -->
                            <td class="py-3.5 px-4">
                                @if($log->admin)
                                    <div class="font-bold text-slate-900 dark:text-white flex items-center gap-1.5 truncate">
                                        <i data-lucide="shield-check" class="w-3.5 h-3.5 text-rose-500 shrink-0"></i>
                                        <span class="truncate">{{ $log->admin->nama_admin }}</span>
                                    </div>
                                    <span class="inline-block mt-0.5 px-2 py-0.5 rounded text-[9px] font-bold uppercase {{ $log->admin->role === 'kepala_sekolah' ? 'bg-rose-50 text-rose-700 dark:bg-rose-950 dark:text-rose-300 border border-rose-200 dark:border-rose-900' : 'bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-300' }}">
                                        {{ $log->admin->role === 'kepala_sekolah' ? 'Kepala Sekolah' : 'Petugas TU' }}
                                    </span>
                                @elseif($log->user)
                                    <div class="font-bold text-slate-900 dark:text-white flex items-center gap-1.5 truncate">
                                        <i data-lucide="user" class="w-3.5 h-3.5 text-sky-600 shrink-0"></i>
                                        <span class="truncate">{{ $log->user->nama ?? $log->user->username ?? 'Calon Siswa' }}</span>
                                    </div>
                                    <span class="inline-block mt-0.5 px-2 py-0.5 rounded text-[9px] font-bold bg-sky-100 text-sky-700 dark:bg-sky-950 dark:text-sky-300 uppercase">
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
                                    } elseif(str_contains($aksi, 'PENGESAHAN')) {
                                        $badgeClass = 'bg-rose-50 text-rose-700 dark:bg-rose-950 dark:text-rose-300 font-black border border-rose-200 dark:border-rose-900';
                                    } elseif(str_contains($aksi, 'VERIFIKASI')) {
                                        $badgeClass = 'bg-emerald-100 text-emerald-800 dark:bg-emerald-950 dark:text-emerald-300 font-bold';
                                    } elseif(str_contains($aksi, 'LOGIN') || str_contains($aksi, 'LOGOUT')) {
                                        $badgeClass = 'bg-indigo-100 text-indigo-800 dark:bg-indigo-950 dark:text-indigo-300';
                                    } elseif(str_contains($aksi, 'UPDATE') || str_contains($aksi, 'SIMPAN') || str_contains($aksi, 'UNLOCK')) {
                                        $badgeClass = 'bg-amber-100 text-amber-800 dark:bg-amber-950 dark:text-amber-300';
                                    } elseif(str_contains($aksi, 'EKSPOR') || str_contains($aksi, 'DOWNLOAD') || str_contains($aksi, 'UNDUH')) {
                                        $badgeClass = 'bg-cyan-100 text-cyan-800 dark:bg-cyan-950 dark:text-cyan-300';
                                    } elseif(str_contains($aksi, 'BAYAR') || str_contains($aksi, 'PAYMENT')) {
                                        $badgeClass = 'bg-emerald-100 text-emerald-800 dark:bg-emerald-950 dark:text-emerald-300';
                                    } elseif(str_contains($aksi, 'PENGAWASAN')) {
                                        $badgeClass = 'bg-rose-100 text-rose-800 dark:bg-rose-950 dark:text-rose-300 font-bold';
                                    }
                                @endphp
                                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold {{ $badgeClass }}">
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
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-black bg-rose-100 text-rose-700 dark:bg-rose-950 dark:text-rose-300">
                                        <i data-lucide="alert-circle" class="w-3 h-3"></i> Gagal
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-black bg-emerald-100 text-emerald-700 dark:bg-emerald-950 dark:text-emerald-300">
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

                            <!-- Data Perubahan (Diff JSON Modal) -->
                            <td class="py-3.5 px-4 text-center">
                                @if($log->data_lama || $log->data_baru)
                                    <button type="button" onclick="showLogDetail('{{ $log->id_log }}', {{ json_encode($log->deskripsi) }}, {{ json_encode($log->data_lama) }}, {{ json_encode($log->data_baru) }})"
                                            class="px-2.5 py-1 rounded-lg bg-[#0B182F] hover:bg-rose-500 text-white text-[10px] font-bold transition-colors">
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
                                Belum ada riwayat aktivitas yang sesuai dengan filter pencarian.
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
<div id="logDiffModal" class="fixed inset-0 z-50 bg-[#0B182F]/70 backdrop-blur-sm hidden flex items-center justify-center p-4">
    <div class="bg-white dark:bg-slate-900 rounded-2xl max-w-lg w-full border border-slate-200 dark:border-slate-800 shadow-2xl overflow-hidden animate-in fade-in zoom-in-95 duration-150">
        <div class="p-5 bg-gradient-to-r from-[#0B182F] to-[#112240] text-white flex items-center justify-between">
            <h3 class="text-sm font-bold text-white flex items-center gap-2">
                <i data-lucide="code" class="w-4 h-4 text-rose-400"></i> Rincian Perubahan Data
            </h3>
            <button onclick="closeLogDetail()" class="text-slate-400 hover:text-white transition-colors">
                <i data-lucide="x" class="w-5 h-5"></i>
            </button>
        </div>
        <div class="p-5 space-y-4 text-xs font-mono">
            <p id="modalKepsekDeskripsi" class="font-sans text-xs font-semibold text-slate-700 dark:text-slate-300"></p>
            <div>
                <span class="text-[11px] font-bold text-rose-600 block mb-1">Data Sebelumnya:</span>
                <pre id="modalDataLama" class="p-3 rounded-xl bg-slate-50 dark:bg-slate-800 text-slate-800 dark:text-slate-200 text-[11px] overflow-x-auto max-h-40 border border-slate-200 dark:border-slate-700"></pre>
            </div>
            <div>
                <span class="text-[11px] font-bold text-emerald-600 block mb-1">Data Sesudahnya:</span>
                <pre id="modalDataBaru" class="p-3 rounded-xl bg-slate-50 dark:bg-slate-800 text-slate-800 dark:text-slate-200 text-[11px] overflow-x-auto max-h-40 border border-slate-200 dark:border-slate-700"></pre>
            </div>
        </div>
        <div class="p-4 border-t border-slate-100 dark:border-slate-800 flex justify-end">
            <button type="button" onclick="closeLogDetail()" class="px-5 py-2 rounded-xl bg-rose-500 hover:bg-rose-600 text-white font-bold text-xs transition-colors shadow-md shadow-rose-500/20">
                Tutup
            </button>
        </div>
    </div>
</div>

@push('scripts')
<script>
    function showLogDetail(id, deskripsi, lama, baru) {
        document.getElementById('modalKepsekDeskripsi').textContent = deskripsi;
        document.getElementById('modalDataLama').textContent = lama ? JSON.stringify(lama, null, 2) : 'null (Tidak ada data lama)';
        document.getElementById('modalDataBaru').textContent = baru ? JSON.stringify(baru, null, 2) : 'null (Tidak ada data baru)';
        document.getElementById('logDiffModal').classList.remove('hidden');
    }
    function closeLogDetail() {
        document.getElementById('logDiffModal').classList.add('hidden');
    }
</script>
@endpush
@endsection
