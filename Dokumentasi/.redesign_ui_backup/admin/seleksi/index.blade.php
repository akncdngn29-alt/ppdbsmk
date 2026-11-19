@extends('layouts.admin')

@section('title', 'Seleksi & Penilaian Calon Siswa - PPDB SMK Islam Bina Khalifah Bangsa')
@section('header_title', 'Input Nilai Seleksi & Uji Kompetensi Kejuruan')

@section('content')
<div class="space-y-6">

    <div class="p-6 rounded-3xl bg-white dark:bg-slate-900 border border-slate-200/90 dark:border-slate-800 shadow-sm space-y-4">
        <div>
            <h2 class="text-base font-bold text-navy-950 dark:text-white">Penilaian Tes Seleksi Masuk</h2>
            <p class="text-xs text-slate-500">Input nilai rata-rata rapor SMP, tes wawancara, dan tes buta warna (khusus SMK) untuk perangkingan kelulusan.</p>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-navy-50/60 dark:bg-slate-800 text-navy-950 dark:text-slate-300 uppercase font-bold text-[10px] tracking-wider">
                    <tr>
                        <th class="p-3">Nama Siswa</th>
                        <th class="p-3">Pilihan Jurusan</th>
                        <th class="p-3">Nilai Rapor</th>
                        <th class="p-3">Nilai Wawancara</th>
                        <th class="p-3">Tes Buta Warna</th>
                        <th class="p-3">Total Nilai</th>
                        <th class="p-3">Aksi Simpan</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                    @forelse($pendaftar as $p)
                    @php
                        $nilai = $p->nilaiSeleksi;
                    @endphp
                    <tr class="hover:bg-slate-50/80 dark:hover:bg-slate-800/50 transition">
                        <td class="p-3">
                            <span class="font-bold text-navy-950 dark:text-white block">{{ $p->nama }}</span>
                            <span class="text-[10px] text-slate-400 font-mono">{{ $p->no_pendaftaran }} | {{ $p->asal_sekolah }}</span>
                        </td>
                        <td class="p-3 font-semibold text-navy-900 dark:text-navy-300">
                            {{ $p->jurusan1 ? $p->jurusan1->nama_jurusan : '-' }}
                        </td>
                        <form action="{{ route('admin.seleksi.simpan', $p->id_pendaftar) }}" method="POST">
                            @csrf
                            <td class="p-3">
                                <input type="number" step="0.1" min="0" max="100" name="nilai_rapor" value="{{ old('nilai_rapor', $nilai ? $nilai->nilai_rapor : '80.00') }}" required class="w-20 px-2 py-1.5 rounded-lg bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-xs font-bold text-center focus:ring-2 focus:ring-pink-500 focus:outline-none">
                            </td>
                            <td class="p-3">
                                <input type="number" step="0.1" min="0" max="100" name="nilai_wawancara" value="{{ old('nilai_wawancara', $nilai ? $nilai->nilai_wawancara : '85.00') }}" required class="w-20 px-2 py-1.5 rounded-lg bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-xs font-bold text-center focus:ring-2 focus:ring-pink-500 focus:outline-none">
                            </td>
                            <td class="p-3">
                                <select name="tes_buta_warna" required class="px-2 py-1.5 rounded-lg bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-xs font-semibold focus:ring-2 focus:ring-pink-500 focus:outline-none">
                                    <option value="Normal" {{ $nilai && $nilai->tes_buta_warna === 'Normal' ? 'selected' : '' }}>Normal</option>
                                    <option value="Parsial" {{ $nilai && $nilai->tes_buta_warna === 'Parsial' ? 'selected' : '' }}>Parsial</option>
                                    <option value="Total" {{ $nilai && $nilai->tes_buta_warna === 'Total' ? 'selected' : '' }}>Total</option>
                                </select>
                            </td>
                            <td class="p-3 font-bold font-mono text-pink-600 dark:text-pink-400">
                                {{ $nilai ? number_format($nilai->total_nilai, 2) : '-' }}
                            </td>
                            <td class="p-3">
                                <button type="submit" class="px-3 py-1.5 rounded-lg bg-pink-600 hover:bg-pink-700 text-white font-bold text-xs shadow-sm transition">
                                    Simpan Nilai
                                </button>
                            </td>
                        </form>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="p-8 text-center text-slate-400">Belum ada siswa yang lunas biaya pendaftaran.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="pt-4 border-t border-slate-100 dark:border-slate-800">
            {{ $pendaftar->links() }}
        </div>
    </div>

</div>
@endsection
