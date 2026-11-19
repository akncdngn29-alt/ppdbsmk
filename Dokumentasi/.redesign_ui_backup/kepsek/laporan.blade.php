@extends('layouts.admin')

@section('title', 'Laporan Resmi Sekolah')
@section('header_title', 'Laporan Resmi & Arsip PPDB Lembaga')

@section('content')
<div class="space-y-6">

    <!-- Header Card -->
    <div class="bg-gradient-to-r from-[#0B182F] via-[#112240] to-[#0B182F] rounded-2xl p-6 sm:p-7 text-white shadow-xl relative overflow-hidden border border-slate-800">
        <div class="relative z-10 flex flex-col md:flex-row md:items-center justify-between gap-6">
            <div class="flex items-start gap-4">
                <div class="w-12 h-12 rounded-xl bg-rose-500/20 border border-rose-500/30 text-rose-400 flex items-center justify-center shrink-0">
                    <i data-lucide="file-text" class="w-6 h-6"></i>
                </div>
                <div>
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase tracking-wider bg-rose-500/20 text-rose-300 border border-rose-500/30 mb-1.5">
                        Dokumen Resmi Lembaga
                    </span>
                    <h2 class="text-lg font-black text-white">Pusat Laporan & Ekspor Berkas Resmi PPDB</h2>
                    <p class="text-xs text-slate-300 mt-1 max-w-2xl leading-relaxed">
                        Unduh berkas rekapitulasi siswa baru untuk keperluan pelaporan ke Dinas Pendidikan Provinsi Lampung, sinkronisasi data Dapodik Kemendikbudristek, dan arsip tahunan SMK Islam Bina Khalifah Bangsa.
                    </p>
                </div>
            </div>

            <!-- Global Action Buttons -->
            <div class="flex flex-wrap items-center gap-2.5 shrink-0">
                <a href="{{ route('admin.laporan.rekap_pdf') }}" target="_blank" class="px-4 py-2.5 rounded-xl bg-rose-500 hover:bg-rose-600 text-white font-bold text-xs flex items-center gap-2 shadow-lg shadow-rose-500/20 transition-all">
                    <i data-lucide="printer" class="w-4 h-4"></i> Cetak Seluruh Rekap PDF
                </a>
                <a href="{{ route('admin.ekspor.excel') }}" class="px-4 py-2.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-white font-bold text-xs flex items-center gap-2 border border-slate-700 transition-colors">
                    <i data-lucide="file-spreadsheet" class="w-4 h-4 text-emerald-400"></i> Ekspor CSV / Excel
                </a>
                <a href="{{ route('admin.ekspor.dapodik') }}" class="px-4 py-2.5 rounded-xl bg-slate-800/80 hover:bg-slate-700 text-slate-200 font-bold text-xs flex items-center gap-2 border border-slate-700 transition-colors">
                    <i data-lucide="download" class="w-4 h-4 text-sky-400"></i> Format Dapodik
                </a>
            </div>
        </div>
        <div class="absolute -right-8 -bottom-8 w-44 h-44 bg-rose-500/10 rounded-full blur-2xl pointer-events-none"></div>
    </div>

    <!-- Matriks Ringkasan Per Jurusan -->
    <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-sm overflow-hidden">
        <div class="p-5 sm:p-6 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between">
            <h3 class="text-sm font-bold text-slate-800 dark:text-white flex items-center gap-2">
                <i data-lucide="pie-chart" class="w-4 h-4 text-rose-500"></i>
                Matriks Keterisian Kuota & Rekap Per Program Keahlian
            </h3>
            <span class="text-xs font-semibold px-2.5 py-1 rounded-lg bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300">
                Tahun Ajaran 2026/2027
            </span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-xs text-left border-collapse">
                <thead>
                    <tr class="bg-slate-900 text-slate-200 font-bold border-b border-slate-800 uppercase text-[10px] tracking-wider">
                        <th class="py-3.5 px-4">Program Keahlian</th>
                        <th class="py-3.5 px-4 text-center">Kode</th>
                        <th class="py-3.5 px-4 text-center">Kuota Daya Tampung</th>
                        <th class="py-3.5 px-4 text-center">Peminat (Pilihan 1)</th>
                        <th class="py-3.5 px-4 text-center">Siswa Diterima</th>
                        <th class="py-3.5 px-4 text-center">% Keterisian</th>
                        <th class="py-3.5 px-4 text-center">Sisa Kursi</th>
                        <th class="py-3.5 px-4 text-center">Cetak PDF Jurusan</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                    @php
                        $grandTotalKuota = 0;
                        $grandTotalPeminat = 0;
                        $grandTotalDiterima = 0;
                    @endphp
                    @foreach($jurusan as $j)
                        @php
                            $persen = $j->kuota > 0 ? round(($j->siswa_diterima_count / $j->kuota) * 100, 1) : 0;
                            $sisa = max(0, $j->kuota - $j->siswa_diterima_count);
                            $grandTotalKuota += $j->kuota;
                            $grandTotalPeminat += $j->pendaftar_pilihan1_count;
                            $grandTotalDiterima += $j->siswa_diterima_count;
                        @endphp
                        <tr class="hover:bg-slate-50/70 dark:hover:bg-slate-800/40 transition-colors">
                            <td class="py-3.5 px-4 font-bold text-slate-900 dark:text-white">
                                {{ $j->nama_jurusan }}
                                @if(!$j->status_aktif)
                                    <span class="ml-1.5 px-1.5 py-0.5 rounded text-[10px] font-bold bg-slate-200 text-slate-600 dark:bg-slate-700 dark:text-slate-300">Nonaktif</span>
                                @endif
                            </td>
                            <td class="py-3.5 px-4 text-center font-mono font-bold text-rose-600 dark:text-rose-400">
                                {{ $j->kode_jurusan }}
                            </td>
                            <td class="py-3.5 px-4 text-center font-bold">
                                {{ $j->kuota }} Siswa
                            </td>
                            <td class="py-3.5 px-4 text-center">
                                {{ $j->pendaftar_pilihan1_count }} Calon
                            </td>
                            <td class="py-3.5 px-4 text-center font-black text-slate-900 dark:text-white">
                                {{ $j->siswa_diterima_count }}
                            </td>
                            <td class="py-3.5 px-4 text-center">
                                <span class="px-2 py-0.5 rounded text-[11px] font-bold {{ $persen >= 100 ? 'bg-emerald-100 text-emerald-800 dark:bg-emerald-950 dark:text-emerald-300' : 'bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-300' }}">
                                    {{ $persen }}%
                                </span>
                            </td>
                            <td class="py-3.5 px-4 text-center font-semibold {{ $sisa === 0 ? 'text-rose-500 font-bold' : 'text-slate-600 dark:text-slate-400' }}">
                                {{ $sisa }}
                            </td>
                            <td class="py-3.5 px-4 text-center">
                                <a href="{{ route('admin.laporan.rekap_pdf', ['jurusan' => $j->id_jurusan]) }}" target="_blank"
                                   class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg border border-slate-300 dark:border-slate-700 text-[11px] font-bold text-slate-700 dark:text-slate-300 hover:border-rose-500 hover:text-rose-600 dark:hover:text-rose-400 transition-colors">
                                    <i data-lucide="printer" class="w-3.5 h-3.5 text-rose-500"></i> Cetak Rekap
                                </a>
                            </td>
                        </tr>
                    @endforeach
                    <!-- Total Row -->
                    <tr class="bg-slate-100/80 dark:bg-slate-800/80 font-black text-slate-900 dark:text-white border-t-2 border-slate-300 dark:border-slate-700">
                        <td class="py-4 px-4 uppercase">TOTAL KESELURUHAN</td>
                        <td class="py-4 px-4 text-center">-</td>
                        <td class="py-4 px-4 text-center">{{ $grandTotalKuota }} Siswa</td>
                        <td class="py-4 px-4 text-center">{{ $grandTotalPeminat }} Calon</td>
                        <td class="py-4 px-4 text-center text-rose-600 dark:text-rose-400">{{ $grandTotalDiterima }}</td>
                        <td class="py-4 px-4 text-center">
                            {{ $grandTotalKuota > 0 ? round(($grandTotalDiterima / $grandTotalKuota) * 100, 1) : 0 }}%
                        </td>
                        <td class="py-4 px-4 text-center">{{ max(0, $grandTotalKuota - $grandTotalDiterima) }}</td>
                        <td class="py-4 px-4 text-center">
                            <a href="{{ route('admin.laporan.rekap_pdf') }}" target="_blank"
                               class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-rose-500 text-white text-[11px] font-bold hover:bg-rose-600 transition-colors shadow-sm">
                                <i data-lucide="printer" class="w-3.5 h-3.5"></i> Cetak Semua
                            </a>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Pernyataan Pengesahan Kepala Sekolah -->
    <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 p-6 shadow-sm">
        <div class="flex items-start gap-4">
            <div class="w-10 h-10 rounded-xl bg-rose-50 dark:bg-rose-950/50 text-rose-600 dark:text-rose-400 border border-rose-100 dark:border-rose-900/50 flex items-center justify-center shrink-0">
                <i data-lucide="check-check" class="w-5 h-5"></i>
            </div>
            <div class="space-y-1">
                <h4 class="text-xs font-bold uppercase tracking-wider text-slate-800 dark:text-white">Pernyataan Integritas & Validasi Lembaga</h4>
                <p class="text-xs text-slate-500 dark:text-slate-400 leading-relaxed">
                    Data yang disajikan dalam rekapitulasi ini telah melalui tahapan verifikasi berkas oleh Petugas Tata Usaha (TU) dan disahkan melalui Sidang Kelulusan oleh Kepala Sekolah. Seluruh berkas digital dan arsip fisik tersimpan dalam pangkalan data terenkripsi SMK Islam Bina Khalifah Bangsa.
                </p>
                <div class="pt-3 flex items-center gap-4 text-[11px] text-slate-400">
                    <span>Penanggung Jawab: <strong>{{ $pengaturan->nama_kepsek }}</strong></span>
                    <span>&bull;</span>
                    <span>NPSN: <strong>{{ $pengaturan->npsn }}</strong></span>
                </div>
            </div>
        </div>
    </div>

</div>
@endsection
