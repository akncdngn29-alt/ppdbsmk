@extends('layouts.admin')

@section('title', 'Statistik Eksekutif')
@section('header_title', 'Statistik & Dashboard Eksekutif Kepala Sekolah')

@section('content')
<div class="space-y-6">

    <!-- Banner Sambutan Kepala Sekolah -->
    <div class="bg-gradient-to-r from-[#161c2d] via-[#1c142e] to-[#2b0e22] rounded-2xl p-6 sm:p-8 text-white shadow-xl border border-pink-500/20 relative overflow-hidden">
        <div class="absolute -right-10 -bottom-10 w-64 h-64 bg-[#fa2f79]/10 rounded-full blur-3xl pointer-events-none"></div>
        <div class="relative z-10 max-w-2xl">
            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-[#fa2f79]/15 text-[#fa2f79] border border-pink-500/30 mb-3">
                <i data-lucide="shield-check" class="w-3.5 h-3.5"></i>
                Panel Pengambilan Keputusan PPDB 2026/2027
            </span>
            <h2 class="text-xl sm:text-2xl font-black tracking-tight leading-tight">
                Selamat Datang, Bapak {{ $pengaturan->nama_kepsek }}
            </h2>
            <p class="text-xs sm:text-sm text-slate-300 mt-2 leading-relaxed">
                Pantau perkembangan real-time penerimaan peserta didik baru, analisis distribusi asal sekolah, dan sahkan hasil seleksi kelulusan calon siswa SMK Islam Bina Khalifah Bangsa.
            </p>
            <div class="mt-4 flex flex-wrap gap-3">
                <a href="{{ route('kepsek.kelulusan') }}" class="px-4 py-2.5 rounded-xl bg-[#fa2f79] hover:bg-[#e11d48] text-white font-bold text-xs transition-colors shadow-lg shadow-pink-500/25 flex items-center gap-2">
                    <i data-lucide="award" class="w-4 h-4"></i> Sidang Pengesahan Kelulusan
                </a>
                <a href="{{ route('kepsek.laporan') }}" class="px-4 py-2.5 rounded-xl bg-white/10 hover:bg-white/15 text-white font-bold text-xs transition-colors border border-white/10 flex items-center gap-2">
                    <i data-lucide="file-text" class="w-4 h-4 text-slate-300"></i> Lihat Rekapitulasi Resmi
                </a>
            </div>
        </div>
    </div>

    <!-- 4 KPI Metrics Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- Total Pendaftar -->
        <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 p-5 shadow-sm">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold text-slate-500 dark:text-slate-400">Total Pendaftar</span>
                <div class="w-9 h-9 rounded-xl bg-pink-100 text-[#fa2f79] dark:bg-pink-950/60 dark:text-pink-300 flex items-center justify-center">
                    <i data-lucide="users" class="w-5 h-5"></i>
                </div>
            </div>
            <div class="mt-3 flex items-baseline gap-2">
                <span class="text-2xl font-black text-slate-800 dark:text-white">{{ $totalPendaftar }}</span>
                <span class="text-xs text-slate-400 font-medium">calon siswa</span>
            </div>
            <div class="mt-2 text-[11px] text-slate-500 dark:text-slate-400">
                Data terakumulasi dari seluruh gelombang
            </div>
        </div>

        <!-- Siswa Diterima -->
        <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 p-5 shadow-sm">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold text-slate-500 dark:text-slate-400">Siswa Diterima</span>
                <div class="w-9 h-9 rounded-xl bg-emerald-100 text-emerald-600 dark:bg-emerald-950/60 dark:text-emerald-400 flex items-center justify-center">
                    <i data-lucide="user-check" class="w-5 h-5"></i>
                </div>
            </div>
            <div class="mt-3 flex items-baseline gap-2">
                <span class="text-2xl font-black text-emerald-600 dark:text-emerald-400">{{ $totalDiterima }}</span>
                <span class="text-xs text-slate-400 font-medium">
                    ({{ $totalPendaftar > 0 ? round(($totalDiterima / $totalPendaftar) * 100, 1) : 0 }}%)
                </span>
            </div>
            <div class="mt-2 text-[11px] text-emerald-600 dark:text-emerald-400 font-medium flex items-center gap-1">
                <i data-lucide="check-circle" class="w-3.5 h-3.5"></i> Telah disahkan Kepala Sekolah
            </div>
        </div>

        <!-- Cadangan -->
        <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 p-5 shadow-sm">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold text-slate-500 dark:text-slate-400">Status Cadangan</span>
                <div class="w-9 h-9 rounded-xl bg-amber-100 text-amber-600 dark:bg-amber-950/60 dark:text-amber-400 flex items-center justify-center">
                    <i data-lucide="clock" class="w-5 h-5"></i>
                </div>
            </div>
            <div class="mt-3 flex items-baseline gap-2">
                <span class="text-2xl font-black text-amber-600 dark:text-amber-400">{{ $totalCadangan }}</span>
                <span class="text-xs text-slate-400 font-medium">siswa</span>
            </div>
            <div class="mt-2 text-[11px] text-amber-600 dark:text-amber-400 font-medium">
                Dapat mengisi kuota limpahan
            </div>
        </div>

        <!-- Tidak Lolos -->
        <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 p-5 shadow-sm">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold text-slate-500 dark:text-slate-400">Tidak Lolos</span>
                <div class="w-9 h-9 rounded-xl bg-rose-100 text-rose-600 dark:bg-rose-950/60 dark:text-rose-400 flex items-center justify-center">
                    <i data-lucide="user-x" class="w-5 h-5"></i>
                </div>
            </div>
            <div class="mt-3 flex items-baseline gap-2">
                <span class="text-2xl font-black text-rose-600 dark:text-rose-400">{{ $totalDitolak }}</span>
                <span class="text-xs text-slate-400 font-medium">siswa</span>
            </div>
            <div class="mt-2 text-[11px] text-rose-600 dark:text-rose-400 font-medium">
                Hasil evaluasi nilai & tes
            </div>
        </div>
    </div>

    <!-- Charts Row: Distribusi Pendaftar & Tren Harian -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Chart 1: Pendaftar vs Diterima vs Kuota per Jurusan -->
        <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 p-6 shadow-sm">
            <div class="flex items-center justify-between mb-4 pb-3 border-b border-slate-100 dark:border-slate-800">
                <div class="flex items-center gap-2">
                    <i data-lucide="bar-chart" class="w-5 h-5 text-emerald-600"></i>
                    <h3 class="text-sm font-bold text-slate-800 dark:text-white">Pendaftar & Siswa Diterima vs Kuota</h3>
                </div>
            </div>
            <div class="h-64">
                <canvas id="jurusanChart"></canvas>
            </div>
        </div>

        <!-- Chart 2: Tren Pendaftaran 7 Hari Terakhir -->
        <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 p-6 shadow-sm">
            <div class="flex items-center justify-between mb-4 pb-3 border-b border-slate-100 dark:border-slate-800">
                <div class="flex items-center gap-2">
                    <i data-lucide="trending-up" class="w-5 h-5 text-[#fa2f79]"></i>
                    <h3 class="text-sm font-bold text-slate-800 dark:text-white">Tren Pendaftaran (7 Hari Terakhir)</h3>
                </div>
            </div>
            <div class="h-64">
                <canvas id="trendChart"></canvas>
            </div>
        </div>
    </div>

    <!-- Detail Keterisian Kuota & Market Intelligence (Top 5 SMP Asal) -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Tabel Keterisian Kuota Per Jurusan -->
        <div class="lg:col-span-2 bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 p-6 shadow-sm">
            <div class="flex items-center justify-between mb-4 pb-3 border-b border-slate-100 dark:border-slate-800">
                <div class="flex items-center gap-2">
                    <i data-lucide="pie-chart" class="w-5 h-5 text-emerald-600"></i>
                    <h3 class="text-sm font-bold text-slate-800 dark:text-white">Status Keterisian Kuota Jurusan</h3>
                </div>
                <a href="{{ route('kepsek.kelulusan') }}" class="text-xs font-bold text-emerald-600 hover:text-emerald-700">Kelola Kelulusan &rarr;</a>
            </div>

            <div class="space-y-4">
                @foreach($jurusan as $j)
                    @php
                        $persenTerisi = $j->kuota > 0 ? round(($j->siswa_diterima_count / $j->kuota) * 100, 1) : 0;
                        $sisaKuota = max(0, $j->kuota - $j->siswa_diterima_count);
                    @endphp
                    <div class="p-4 rounded-xl border border-slate-100 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-800/40">
                        <div class="flex items-center justify-between text-xs mb-1.5">
                            <div>
                                <span class="font-bold text-slate-900 dark:text-white">{{ $j->nama_jurusan }}</span>
                                <span class="text-slate-400 font-mono text-[11px] ml-1">({{ $j->kode_jurusan }})</span>
                                @if(!$j->status_aktif)
                                <span class="px-1.5 py-0.5 rounded text-[9px] font-bold bg-slate-200 dark:bg-slate-700 text-slate-600 dark:text-slate-400 ml-1">
                                    Nonaktif
                                </span>
                                @endif
                            </div>
                            <div class="font-bold">
                                <span class="text-emerald-600 dark:text-emerald-400">{{ $j->siswa_diterima_count }}</span>
                                <span class="text-slate-400">/ {{ $j->kuota }} Siswa</span>
                                <span class="text-[10px] text-slate-500 ml-1">({{ $persenTerisi }}%)</span>
                            </div>
                        </div>
                        <!-- Progress Bar -->
                        <div class="w-full bg-slate-200 dark:bg-slate-700 rounded-full h-2.5 overflow-hidden">
                            <div class="bg-emerald-600 h-2.5 rounded-full transition-all" style="width: {{ min(100, $persenTerisi) }}%"></div>
                        </div>
                        <div class="flex items-center justify-between text-[11px] text-slate-500 dark:text-slate-400 mt-2">
                            <span>Total Peminat (Pilihan 1): <strong>{{ $j->pendaftar_pilihan1_count }}</strong></span>
                            <span>Sisa Kuota: <strong class="{{ $sisaKuota === 0 ? 'text-rose-500' : 'text-slate-700 dark:text-slate-300' }}">{{ $sisaKuota }} kursi</strong></span>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <!-- Top 5 Asal SMP/MTs (Market Intelligence) -->
        <div class="lg:col-span-1 bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 p-6 shadow-sm">
            <div class="flex items-center gap-2 mb-4 pb-3 border-b border-slate-100 dark:border-slate-800">
                <i data-lucide="target" class="w-5 h-5 text-teal-600"></i>
                <h3 class="text-sm font-bold text-slate-800 dark:text-white">Top 5 Asal SMP / MTs</h3>
            </div>

            @if($topAsalSekolah->isEmpty())
                <div class="py-8 text-center text-slate-400 text-xs">
                    Belum ada data pendaftar.
                </div>
            @else
                <div class="space-y-3">
                    @foreach($topAsalSekolah as $index => $asal)
                        <div class="flex items-center justify-between p-3 rounded-xl border border-slate-100 dark:border-slate-800 hover:bg-slate-50 dark:hover:bg-slate-800/40 transition-colors">
                            <div class="flex items-center gap-3">
                                <span class="w-6 h-6 rounded-lg bg-emerald-100 text-emerald-700 dark:bg-emerald-950 dark:text-emerald-400 text-xs font-black flex items-center justify-center shrink-0">
                                    {{ $index + 1 }}
                                </span>
                                <span class="text-xs font-semibold text-slate-800 dark:text-slate-200 truncate max-w-[150px]">
                                    {{ $asal->asal_sekolah }}
                                </span>
                            </div>
                            <span class="px-2.5 py-1 rounded-full text-xs font-bold bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 font-mono">
                                {{ $asal->total }} siswa
                            </span>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>

</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Chart 1: Jurusan Bar Chart
        const ctxJurusan = document.getElementById('jurusanChart').getContext('2d');
        new Chart(ctxJurusan, {
            type: 'bar',
            data: {
                labels: {!! json_encode($jurusan->pluck('kode_jurusan')) !!},
                datasets: [
                    {
                        label: 'Peminat (Pilihan 1)',
                        data: {!! json_encode($jurusan->pluck('pendaftar_pilihan1_count')) !!},
                        backgroundColor: 'rgba(59, 130, 246, 0.75)',
                        borderColor: '#2563eb',
                        borderWidth: 1,
                        borderRadius: 6
                    },
                    {
                        label: 'Siswa Diterima',
                        data: {!! json_encode($jurusan->pluck('siswa_diterima_count')) !!},
                        backgroundColor: 'rgba(16, 185, 129, 0.85)',
                        borderColor: '#059669',
                        borderWidth: 1,
                        borderRadius: 6
                    },
                    {
                        label: 'Kuota Maksimal',
                        data: {!! json_encode($jurusan->pluck('kuota')) !!},
                        backgroundColor: 'rgba(148, 163, 184, 0.4)',
                        borderColor: '#64748b',
                        borderWidth: 1,
                        borderRadius: 6
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'top',
                        labels: { boxWidth: 12, font: { size: 10 } }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: { stepSize: 10 }
                    }
                }
            }
        });

        // Chart 2: Daily Trend Line Chart
        const ctxTrend = document.getElementById('trendChart').getContext('2d');
        const trendData = {!! json_encode($dailyTrend) !!};
        const trendLabels = trendData.map(d => d.date);
        const trendCounts = trendData.map(d => d.count);

        new Chart(ctxTrend, {
            type: 'line',
            data: {
                labels: trendLabels.length ? trendLabels : ['Hari Ini'],
                datasets: [{
                    label: 'Pendaftar Baru / Hari',
                    data: trendCounts.length ? trendCounts : [0],
                    borderColor: '#fa2f79',
                    backgroundColor: 'rgba(250, 47, 121, 0.15)',
                    tension: 0.35,
                    fill: true,
                    pointBackgroundColor: '#fa2f79',
                    pointRadius: 4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'top',
                        labels: { boxWidth: 12, font: { size: 10 } }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: { stepSize: 1 }
                    }
                }
            }
        });
    });
</script>
@endpush
@endsection

