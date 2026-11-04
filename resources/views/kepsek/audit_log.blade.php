@extends('layouts.admin')

@section('title', 'Audit Trail (Log Aktivitas)')
@section('header_title', 'Audit Trail & Rekam Jejak Aktivitas Sistem')

@section('content')
<div class="space-y-6">

    <!-- Header Info Card -->
    <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 p-6 shadow-sm">
        <div class="flex items-start gap-4">
            <div class="w-12 h-12 rounded-xl bg-indigo-100 text-indigo-600 dark:bg-indigo-950/60 dark:text-indigo-400 flex items-center justify-center shrink-0">
                <i data-lucide="shield" class="w-6 h-6"></i>
            </div>
            <div>
                <h2 class="text-base font-bold text-slate-800 dark:text-white">Transparansi & Akuntabilitas Sistem PPDB</h2>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-1 max-w-3xl leading-relaxed">
                    Setiap tindakan penting di dalam sistem—mulai dari pendaftaran akun siswa, verifikasi berkas oleh Petugas TU, pembayaran transaksi, input penilaian seleksi, hingga pengesahan kelulusan oleh Kepala Sekolah—tercatat secara permanen dengan alamat IP dan rincian data perubahannya.
                </p>
            </div>
        </div>
    </div>

    <!-- Tabel Rekam Jejak Aktivitas -->
    <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-sm overflow-hidden">
        <div class="p-6 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between">
            <h3 class="text-sm font-bold text-slate-800 dark:text-white flex items-center gap-2">
                <i data-lucide="activity" class="w-4 h-4 text-indigo-600"></i>
                Daftar Riwayat Aktivitas Terakhir
            </h3>
            <span class="text-xs text-slate-500 dark:text-slate-400">Total: {{ $logs->total() }} Peristiwa</span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-xs text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50 dark:bg-slate-800/60 text-slate-500 dark:text-slate-400 font-bold border-b border-slate-200 dark:border-slate-800">
                        <th class="py-3 px-4 w-40">Waktu</th>
                        <th class="py-3 px-4 w-44">Pelaku / Aktor</th>
                        <th class="py-3 px-4 text-center w-32">Kategori</th>
                        <th class="py-3 px-4">Deskripsi Aktivitas</th>
                        <th class="py-3 px-4 w-36">IP & Perangkat</th>
                        <th class="py-3 px-4 text-center w-24">Data Perubahan</th>
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
                                    <div class="font-bold text-slate-900 dark:text-white flex items-center gap-1.5">
                                        <i data-lucide="shield-check" class="w-3.5 h-3.5 text-emerald-600"></i>
                                        {{ $log->admin->nama_admin }}
                                    </div>
                                    <span class="inline-block mt-0.5 px-1.5 py-0.5 rounded text-[9px] font-bold uppercase {{ $log->admin->role === 'kepala_sekolah' ? 'bg-purple-100 text-purple-700 dark:bg-purple-950 dark:text-purple-300' : 'bg-blue-100 text-blue-700 dark:bg-blue-950 dark:text-blue-300' }}">
                                        {{ $log->admin->role === 'kepala_sekolah' ? 'Kepala Sekolah' : 'Petugas TU' }}
                                    </span>
                                @elseif($log->user)
                                    <div class="font-bold text-slate-900 dark:text-white flex items-center gap-1.5">
                                        <i data-lucide="user" class="w-3.5 h-3.5 text-sky-600"></i>
                                        {{ $log->user->name }}
                                    </div>
                                    <span class="inline-block mt-0.5 px-1.5 py-0.5 rounded text-[9px] font-bold bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-400 uppercase">
                                        Calon Siswa
                                    </span>
                                @else
                                    <span class="text-slate-400 font-italic text-[11px]">Sistem Otomatis</span>
                                @endif
                            </td>

                            <!-- Kategori Aksi -->
                            <td class="py-3.5 px-4 text-center">
                                @php
                                    $badgeClass = 'bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-300';
                                    if(str_contains($log->tipe_aktivitas, 'PENGESAHAN')) {
                                        $badgeClass = 'bg-emerald-100 text-emerald-800 dark:bg-emerald-950 dark:text-emerald-300 font-black';
                                    } elseif(str_contains($log->tipe_aktivitas, 'VERIFIKASI')) {
                                        $badgeClass = 'bg-teal-100 text-teal-800 dark:bg-teal-950 dark:text-teal-300';
                                    } elseif(str_contains($log->tipe_aktivitas, 'LOGIN') || str_contains($log->tipe_aktivitas, 'AUTH')) {
                                        $badgeClass = 'bg-indigo-100 text-indigo-800 dark:bg-indigo-950 dark:text-indigo-300';
                                    } elseif(str_contains($log->tipe_aktivitas, 'UPDATE') || str_contains($log->tipe_aktivitas, 'SIMPAN')) {
                                        $badgeClass = 'bg-amber-100 text-amber-800 dark:bg-amber-950 dark:text-amber-300';
                                    }
                                @endphp
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold {{ $badgeClass }}">
                                    {{ $log->tipe_aktivitas }}
                                </span>
                            </td>

                            <!-- Deskripsi -->
                            <td class="py-3.5 px-4 text-slate-800 dark:text-slate-200">
                                <p class="leading-relaxed">{{ $log->deskripsi }}</p>
                                @if($log->tabel_terkait)
                                    <div class="text-[10px] text-slate-400 font-mono mt-0.5">
                                        Tabel: {{ $log->tabel_terkait }} #{{ $log->id_entitas }}
                                    </div>
                                @endif
                            </td>

                            <!-- IP & Perangkat -->
                            <td class="py-3.5 px-4">
                                <div class="font-mono text-[11px] text-slate-600 dark:text-slate-400">
                                    {{ $log->ip_address ?: '127.0.0.1' }}
                                </div>
                                @if($log->user_agent)
                                    <div class="text-[9px] text-slate-400 truncate max-w-[140px]" title="{{ $log->user_agent }}">
                                        {{ Str::limit($log->user_agent, 20) }}
                                    </div>
                                @endif
                            </td>

                            <!-- Data Perubahan (Diff JSON Modal) -->
                            <td class="py-3.5 px-4 text-center">
                                @if($log->data_lama || $log->data_baru)
                                    <button type="button" onclick="showLogDetail('{{ $log->id_log }}', {{ json_encode($log->data_lama) }}, {{ json_encode($log->data_baru) }})"
                                            class="px-2 py-1 rounded-lg bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 text-[10px] font-bold hover:bg-slate-200 dark:hover:bg-slate-700 transition-colors">
                                        Lihat Diff
                                    </button>
                                @else
                                    <span class="text-slate-300 dark:text-slate-600 text-[10px]">-</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-12 text-center text-slate-400">
                                Belum ada riwayat aktivitas tercatat.
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
<div id="logDiffModal" class="fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-sm hidden flex items-center justify-center p-4">
    <div class="bg-white dark:bg-slate-900 rounded-2xl max-w-lg w-full border border-slate-200 dark:border-slate-800 shadow-xl overflow-hidden">
        <div class="p-5 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between">
            <h3 class="text-sm font-bold text-slate-800 dark:text-white flex items-center gap-2">
                <i data-lucide="code" class="w-4 h-4 text-indigo-600"></i> Rincian Perubahan Data
            </h3>
            <button onclick="closeLogDetail()" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200">
                <i data-lucide="x" class="w-5 h-5"></i>
            </button>
        </div>
        <div class="p-5 space-y-4 text-xs font-mono">
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
            <button type="button" onclick="closeLogDetail()" class="px-4 py-2 rounded-xl bg-slate-900 dark:bg-white text-white dark:text-slate-900 font-bold text-xs">
                Tutup
            </button>
        </div>
    </div>
</div>

@push('scripts')
<script>
    function showLogDetail(id, lama, baru) {
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

