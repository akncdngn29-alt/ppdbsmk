@extends('layouts.admin')

@section('title', 'Master Gelombang PPDB - SMK IB')
@section('header_title', 'Manajemen Gelombang Pendaftaran & Biaya')

@section('content')
<div class="space-y-6">

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        <!-- Form Tambah Gelombang -->
        <div class="p-6 rounded-3xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-sm space-y-4">
            <h3 class="text-base font-bold text-slate-900 dark:text-white">Tambah Gelombang Baru</h3>
            <form action="{{ route('admin.master.gelombang.simpan') }}" method="POST" class="space-y-3 text-xs">
                @csrf
                <div>
                    <label class="block font-bold text-slate-700 dark:text-slate-300 mb-1">Nama Gelombang</label>
                    <input type="text" name="nama_gelombang" required placeholder="Contoh: Gelombang 3 (Susulan)" class="w-full px-3 py-2 rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-xs">
                </div>

                <div>
                    <label class="block font-bold text-slate-700 dark:text-slate-300 mb-1">Tahun Ajaran</label>
                    <input type="text" name="tahun_ajaran" required value="2026/2027" class="w-full px-3 py-2 rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-xs">
                </div>

                <div class="grid grid-cols-2 gap-2">
                    <div>
                        <label class="block font-bold text-slate-700 dark:text-slate-300 mb-1">Tgl Mulai</label>
                        <input type="date" name="tgl_buka" required value="{{ date('Y-m-d') }}" class="w-full px-2 py-2 rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-xs">
                    </div>
                    <div>
                        <label class="block font-bold text-slate-700 dark:text-slate-300 mb-1">Tgl Selesai</label>
                        <input type="date" name="tgl_tutup" required value="{{ date('Y-m-d', strtotime('+30 days')) }}" class="w-full px-2 py-2 rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-xs">
                    </div>
                </div>

                <div>
                    <label class="block font-bold text-slate-700 dark:text-slate-300 mb-1">Tarif Biaya Formulir (Rp)</label>
                    <input type="number" name="biaya_pendaftaran" required min="0" step="5000" value="100000" class="w-full px-3 py-2 rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-xs font-bold font-mono">
                </div>

                <div>
                    <label class="block font-bold text-slate-700 dark:text-slate-300 mb-1">Status Langsung Aktif?</label>
                    <select name="status_aktif" class="w-full px-3 py-2 rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-xs">
                        <option value="1">Aktif Sekarang</option>
                        <option value="0">Tidak Aktif (Jadwal Nanti)</option>
                    </select>
                </div>

                <button type="submit" class="w-full py-2.5 rounded-xl bg-[#fa2f79] hover:bg-[#e11d48] text-white font-bold text-xs shadow-md shadow-pink-500/25 transition-colors">
                    Simpan Gelombang Baru
                </button>
            </form>
        </div>

        <!-- List Gelombang -->
        <div class="lg:col-span-2 p-6 rounded-3xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-sm space-y-4">
            <h3 class="text-base font-bold text-slate-900 dark:text-white">Daftar Gelombang Pendaftaran</h3>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead class="bg-slate-50 dark:bg-slate-800 text-slate-500 uppercase font-bold text-[10px]">
                        <tr>
                            <th class="p-3">Gelombang</th>
                            <th class="p-3">Rentang Jadwal</th>
                            <th class="p-3">Biaya Formulir</th>
                            <th class="p-3">Pendaftar</th>
                            <th class="p-3">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                        @foreach($gelombang as $g)
                        <tr>
                            <td class="p-3">
                                <strong class="text-slate-900 dark:text-white block">{{ $g->nama_gelombang }}</strong>
                                <span class="text-[10px] text-slate-400">T.A {{ $g->tahun_ajaran }}</span>
                            </td>
                            <td class="p-3 text-slate-600 dark:text-slate-300">
                                {{ $g->tgl_buka->format('d/m/Y') }} s/d {{ $g->tgl_tutup->format('d/m/Y') }}
                            </td>
                            <td class="p-3 font-mono font-bold text-slate-900 dark:text-white">
                                Rp {{ number_format($g->biaya_pendaftaran, 0, ',', '.') }}
                            </td>
                            <td class="p-3 font-bold">{{ $g->pendaftar_count }} Siswa</td>
                            <td class="p-3">
                                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold 
                                    {{ $g->status_aktif ? 'bg-emerald-100 text-emerald-800 dark:bg-emerald-950 dark:text-emerald-300' : 'bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-400' }}">
                                    {{ $g->status_aktif ? 'AKTIF' : 'NONAKTIF' }}
                                </span>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

    </div>

</div>
@endsection

