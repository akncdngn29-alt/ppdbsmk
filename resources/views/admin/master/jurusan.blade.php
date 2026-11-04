@extends('layouts.admin')

@section('title', 'Master Jurusan & Kuota - PPDB SMK IB')
@section('header_title', 'Master Data Program Keahlian & Kuota')

@section('content')
<div class="space-y-6">

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        @foreach($jurusan as $j)
        <div class="p-6 rounded-3xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-sm space-y-4">
            <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-800 pb-3">
                <div class="flex items-center gap-2">
                    <span class="w-8 h-8 rounded-lg bg-emerald-50 dark:bg-emerald-950/60 text-emerald-600 dark:text-emerald-400 font-bold flex items-center justify-center text-xs">
                        {{ $j->kode_jurusan }}
                    </span>
                    <h3 class="text-sm font-bold text-slate-900 dark:text-white">{{ $j->nama_jurusan }}</h3>
                </div>
                <span class="text-xs font-bold text-emerald-600 dark:text-emerald-400">{{ $j->pendaftar_pilihan1_count }} Pendaftar</span>
            </div>

            <form action="{{ route('admin.master.jurusan.update', $j->id_jurusan) }}" method="POST" class="space-y-3 text-xs">
                @csrf
                <div>
                    <label class="block font-bold text-slate-700 dark:text-slate-300 mb-1">Nama Program Keahlian</label>
                    <input type="text" name="nama_jurusan" value="{{ $j->nama_jurusan }}" required class="w-full px-3 py-2 rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-xs">
                </div>

                <div>
                    <label class="block font-bold text-slate-700 dark:text-slate-300 mb-1">Kapasitas Kuota Siswa</label>
                    <input type="number" name="kuota" value="{{ $j->kuota }}" min="1" required class="w-full px-3 py-2 rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-xs font-bold font-mono">
                </div>

                <div>
                    <label class="block font-bold text-slate-700 dark:text-slate-300 mb-1">Deskripsi & Prospek Kerja</label>
                    <textarea name="deskripsi" rows="3" class="w-full px-3 py-2 rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-xs">{{ $j->deskripsi }}</textarea>
                </div>

                <button type="submit" class="w-full py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs shadow-sm transition-colors">
                    Simpan Perubahan
                </button>
            </form>
        </div>
        @endforeach
    </div>

</div>
@endsection

