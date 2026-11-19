@extends('layouts.admin')

@section('title', 'Import Data Pendaftar')
@section('header_title', 'Import Data Pendaftar (Excel / CSV)')

@section('content')
<div class="space-y-6">

    <!-- Header & Action -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-xl font-black text-slate-800 dark:text-white tracking-tight">Import Pendaftar Massal</h1>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">
                Unggah pendaftar luar jaringan (offline) sekaligus melalui berkas CSV atau format Excel
            </p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('admin.import.template') }}" class="px-4 py-2.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-white font-bold text-xs transition-colors flex items-center gap-2 shadow-sm">
                <i data-lucide="download" class="w-4 h-4 text-emerald-400"></i> Unduh Template CSV
            </a>
            <a href="{{ route('admin.pendaftar') }}" class="px-4 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 font-bold text-xs transition-colors flex items-center gap-2">
                <i data-lucide="arrow-left" class="w-4 h-4"></i> Kembali ke Pendaftar
            </a>
        </div>
    </div>

    <!-- Alert Sukses / Gagal -->
    @if(session('success'))
    <div class="p-4 rounded-xl bg-emerald-50 dark:bg-emerald-950/50 border border-emerald-200 dark:border-emerald-800 flex items-start gap-3 text-xs text-emerald-800 dark:text-emerald-200">
        <i data-lucide="check-circle-2" class="w-5 h-5 text-emerald-600 shrink-0 mt-0.5"></i>
        <div>
            <span class="font-bold block text-sm">{{ session('success') }}</span>
            @if(session('import_stats'))
                <span class="text-[11px] text-emerald-700 dark:text-emerald-300 mt-1 block">
                    Total data diproses: {{ session('import_stats')['diproses'] }} &bull; Berhasil disimpan: {{ session('import_stats')['berhasil'] }} &bull; Dilewati/Duplikat: {{ session('import_stats')['dilewati'] }}
                </span>
            @endif
        </div>
    </div>
    @endif

    @if(session('error'))
    <div class="p-4 rounded-xl bg-rose-50 dark:bg-rose-950/50 border border-rose-200 dark:border-rose-800 flex items-center gap-3 text-xs text-rose-800 dark:text-rose-200">
        <i data-lucide="alert-circle" class="w-5 h-5 text-rose-600 shrink-0"></i>
        <span>{{ session('error') }}</span>
    </div>
    @endif

    <!-- Daftar Error Detail Jika Ada Baris yang Dilewati -->
    @if(session('import_errors') && count(session('import_errors')) > 0)
    <div class="p-4 rounded-xl bg-amber-50 dark:bg-amber-950/50 border border-amber-200 dark:border-amber-800 text-xs text-amber-900 dark:text-amber-200">
        <div class="flex items-center gap-2 font-bold mb-2">
            <i data-lucide="alert-triangle" class="w-4 h-4 text-amber-600"></i>
            <span>Catatan Baris yang Dilewati ({{ count(session('import_errors')) }} Catatan):</span>
        </div>
        <ul class="list-disc list-inside space-y-1 text-[11px] max-h-48 overflow-y-auto font-mono text-amber-800 dark:text-amber-300">
            @foreach(session('import_errors') as $err)
            <li>{{ $err }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        
        <!-- Formulir Upload Berkas (2 Kolom) -->
        <div class="lg:col-span-2 bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 p-6 shadow-sm">
            <h2 class="text-sm font-bold text-slate-800 dark:text-white flex items-center gap-2 mb-4">
                <i data-lucide="upload" class="w-4 h-4 text-emerald-500"></i> Unggah Berkas Pendaftar
            </h2>

            <form action="{{ route('admin.import.proses') }}" method="POST" enctype="multipart/form-data" class="space-y-4">
                @csrf
                
                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">
                        Pilih Gelombang Pendaftaran Tujuan <span class="text-rose-500">*</span>
                    </label>
                    <select name="id_gelombang" required class="w-full text-xs px-3 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-slate-800 dark:text-white focus:ring-2 focus:ring-emerald-500">
                        @if($gelombangAktif)
                            <option value="{{ $gelombangAktif->id_gelombang }}" selected>
                                {{ $gelombangAktif->nama_gelombang }} (Aktif - {{ $gelombangAktif->tahun_ajaran }})
                            </option>
                        @endif
                    </select>
                    <p class="text-[11px] text-slate-400 mt-1">Calon siswa yang diimpor akan otomatis terhubung ke gelombang ini.</p>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">
                        Berkas CSV / Excel (.csv, .xlsx) <span class="text-rose-500">*</span>
                    </label>
                    <div class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-slate-300 dark:border-slate-700 border-dashed rounded-2xl hover:border-[#fa2f79] dark:hover:border-[#fa2f79] transition-colors bg-slate-50/50 dark:bg-slate-800/30">
                        <div class="space-y-2 text-center">
                            <i data-lucide="file-spreadsheet" class="mx-auto h-10 w-10 text-[#fa2f79]"></i>
                            <div class="flex text-xs text-slate-600 dark:text-slate-400 justify-center">
                                <label for="berkas_file" class="relative cursor-pointer rounded-md font-bold text-[#fa2f79] hover:text-[#e11d48] focus-within:outline-none">
                                    <span>Pilih Berkas Komputer</span>
                                    <input id="berkas_file" name="berkas" type="file" required accept=".csv,.xlsx,.txt" class="sr-only" onchange="updateFileLabel(this)">
                                </label>
                            </div>
                            <p id="file_label" class="text-[11px] text-slate-500 dark:text-slate-400">
                                Berkas CSV atau XLSX standar (Maks. 5 MB)
                            </p>
                        </div>
                    </div>
                </div>

                <div class="pt-2 flex items-center justify-between">
                    <span class="text-[11px] text-slate-400">
                        Password bawaan akun siswa baru: <strong class="text-slate-700 dark:text-slate-300 font-mono">siswa123</strong>
                    </span>
                    <button type="submit" class="px-5 py-2.5 rounded-xl bg-[#fa2f79] hover:bg-[#e11d48] text-white font-bold text-xs transition-colors flex items-center gap-2 shadow-sm shadow-pink-500/25">
                        <i data-lucide="play" class="w-4 h-4"></i> Mulai Proses Import
                    </button>
                </div>
            </form>
        </div>

        <!-- Panduan Kolom & Format (1 Kolom) -->
        <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 p-6 shadow-sm space-y-4">
            <h3 class="text-sm font-bold text-slate-800 dark:text-white flex items-center gap-2">
                <i data-lucide="info" class="w-4 h-4 text-sky-500"></i> Ketentuan Header Kolom
            </h3>
            
            <p class="text-xs text-slate-600 dark:text-slate-300 leading-relaxed">
                Berkas harus memiliki baris pertama berupa header dengan nama kolom berikut:
            </p>

            <div class="space-y-2 text-[11px] font-mono text-slate-600 dark:text-slate-400">
                <div class="p-2 rounded-lg bg-slate-50 dark:bg-slate-800/80 border border-slate-100 dark:border-slate-800">
                    <strong class="text-emerald-600 dark:text-emerald-400">nama</strong> : Nama lengkap siswa (Wajib)
                </div>
                <div class="p-2 rounded-lg bg-slate-50 dark:bg-slate-800/80 border border-slate-100 dark:border-slate-800">
                    <strong class="text-emerald-600 dark:text-emerald-400">nisn</strong> : 10 digit NISN unik (Wajib)
                </div>
                <div class="p-2 rounded-lg bg-slate-50 dark:bg-slate-800/80 border border-slate-100 dark:border-slate-800">
                    <strong class="text-emerald-600 dark:text-emerald-400">kode_jurusan_1</strong> : Kode jurusan (Wajib)
                    <div class="text-[10px] text-slate-500 mt-1">
                        Pilihan: 
                        @foreach($daftarJurusan as $j)
                            <span class="px-1.5 py-0.5 rounded bg-slate-200 dark:bg-slate-700 text-slate-800 dark:text-slate-200 mr-1">{{ $j->kode_jurusan }}</span>
                        @endforeach
                    </div>
                </div>
                <div class="p-2 rounded-lg bg-slate-50 dark:bg-slate-800/80 border border-slate-100 dark:border-slate-800">
                    <strong class="text-slate-700 dark:text-slate-300">tempat_lahir</strong> : Kota lahir
                </div>
                <div class="p-2 rounded-lg bg-slate-50 dark:bg-slate-800/80 border border-slate-100 dark:border-slate-800">
                    <strong class="text-slate-700 dark:text-slate-300">tanggal_lahir</strong> : YYYY-MM-DD
                </div>
                <div class="p-2 rounded-lg bg-slate-50 dark:bg-slate-800/80 border border-slate-100 dark:border-slate-800">
                    <strong class="text-slate-700 dark:text-slate-300">jenis_kelamin</strong> : Laki-laki / Perempuan
                </div>
                <div class="p-2 rounded-lg bg-slate-50 dark:bg-slate-800/80 border border-slate-100 dark:border-slate-800">
                    <strong class="text-slate-700 dark:text-slate-300">asal_sekolah</strong> : SMP asal
                </div>
            </div>

            <div class="p-3 rounded-xl bg-amber-50 dark:bg-amber-950/40 border border-amber-200 dark:border-amber-800 text-[11px] text-amber-800 dark:text-amber-300">
                <i data-lucide="shield-alert" class="w-3.5 h-3.5 inline mr-1 text-amber-600"></i>
                Baris yang memiliki NISN ganda dengan data di database akan <strong>dilewati secara otomatis</strong> tanpa membatalkan baris pendaftar valid lainnya.
            </div>
        </div>

    </div>

</div>

<script>
    function updateFileLabel(input) {
        if (input.files && input.files[0]) {
            document.getElementById('file_label').innerHTML = '<strong class="text-emerald-600 dark:text-emerald-400 font-bold">' + input.files[0].name + '</strong> (' + Math.round(input.files[0].size / 1024) + ' KB)';
        }
    }
</script>
@endsection

