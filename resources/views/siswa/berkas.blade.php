@extends('layouts.siswa')

@section('title', 'Unggah Berkas Persyaratan')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">

        <!-- Page Header -->
        <div class="flex items-center justify-between flex-wrap gap-4">
            <div>
                <a href="{{ route('siswa.dashboard') }}" class="text-xs font-semibold text-emerald-600 dark:text-emerald-400 hover:underline flex items-center gap-1 mb-1">
                    <i data-lucide="arrow-left" class="w-3.5 h-3.5"></i> Kembali ke Dashboard
                </a>
                <h1 class="text-2xl font-extrabold text-slate-900 dark:text-white">Unggah Berkas Persyaratan</h1>
                <p class="text-xs text-slate-500 dark:text-slate-400">Pastikan scan atau foto berkas tampak jelas, tidak terpotong, dan dapat terbaca.</p>
            </div>
            <span class="px-3 py-1.5 rounded-full text-xs font-bold 
                @if($pendaftar->status_verifikasi === 'Terverifikasi') bg-emerald-100 text-emerald-800 dark:bg-emerald-950 dark:text-emerald-300
                @elseif($pendaftar->status_verifikasi === 'Ditolak') bg-rose-100 text-rose-800 dark:bg-rose-950 dark:text-rose-300
                @else bg-amber-100 text-amber-800 dark:bg-amber-950 dark:text-amber-300 @endif flex items-center gap-1.5">
                <i data-lucide="shield-check" class="w-4 h-4"></i> Status Berkas: {{ $pendaftar->status_verifikasi }}
            </span>
        </div>

        @if($pendaftar->catatan_verifikasi)
        <div class="p-4 rounded-2xl bg-rose-50 dark:bg-rose-950/40 border border-rose-200 dark:border-rose-800 text-rose-800 dark:text-rose-300 text-xs flex items-start gap-3">
            <i data-lucide="alert-circle" class="w-5 h-5 shrink-0 text-rose-600 mt-0.5"></i>
            <div>
                <strong class="block mb-0.5 font-bold">Catatan Revisi dari Petugas TU:</strong>
                {{ $pendaftar->catatan_verifikasi }}
            </div>
        </div>
        @endif

        <!-- 4 Essential Documents Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            @php
                $berkasList = [
                    ['key' => 'ijazah', 'label' => 'Ijazah SMP / SKL', 'desc' => 'Scan Ijazah atau Surat Keterangan Lulus resmi dari sekolah asal.'],
                    ['key' => 'kk', 'label' => 'Kartu Keluarga (KK)', 'desc' => 'Scan/foto Kartu Keluarga terbaru yang memuat nama siswa.'],
                    ['key' => 'pasfoto', 'label' => 'Pas Foto Formal (3x4)', 'desc' => 'Foto berwarna berseragam SMP dengan latar belakang merah/biru.'],
                    ['key' => 'akta', 'label' => 'Akta Kelahiran', 'desc' => 'Scan Akta Kelahiran resmi dari Disdukcapil.'],
                ];
            @endphp

            @foreach($berkasList as $b)
            @php
                $item = $berkas->where('jenis_berkas', $b['key'])->first();
            @endphp
            <div class="p-6 rounded-3xl bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 shadow-sm flex flex-col justify-between space-y-4">
                <div class="space-y-3">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-bold text-slate-900 dark:text-white uppercase tracking-wider">{{ $b['label'] }}</span>
                        @if($item)
                            @if($item->status_berkas === 'Valid')
                            <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800 dark:bg-emerald-950 dark:text-emerald-300 flex items-center gap-1">
                                <i data-lucide="check" class="w-3 h-3"></i> Valid
                            </span>
                            @elseif($item->status_berkas === 'Tidak Valid')
                            <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-rose-100 text-rose-800 dark:bg-rose-950 dark:text-rose-300 flex items-center gap-1">
                                <i data-lucide="x" class="w-3 h-3"></i> Tidak Valid
                            </span>
                            @else
                            <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-amber-100 text-amber-800 dark:bg-amber-950 dark:text-amber-300 flex items-center gap-1">
                                <i data-lucide="clock" class="w-3 h-3"></i> Menunggu Verifikasi
                            </span>
                            @endif
                        @else
                        <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-slate-100 text-slate-500 dark:bg-slate-700 dark:text-slate-400">
                            Belum Ada File
                        </span>
                        @endif
                    </div>

                    <p class="text-xs text-slate-500 dark:text-slate-400">{{ $b['desc'] }}</p>

                    @if($item)
                    <div class="p-3 rounded-2xl bg-slate-50 dark:bg-slate-900/60 border border-slate-200 dark:border-slate-700 flex items-center justify-between text-xs">
                        <div class="flex items-center gap-2 truncate">
                            <i data-lucide="file" class="w-4 h-4 text-emerald-600 shrink-0"></i>
                            <span class="truncate font-medium text-slate-700 dark:text-slate-300">{{ $item->nama_file }}</span>
                        </div>
                        <a href="{{ route('dokumen.berkas', $item->id_berkas) }}" target="_blank" class="text-xs text-emerald-600 dark:text-emerald-400 font-bold hover:underline shrink-0 ml-2">
                            Lihat File
                        </a>
                    </div>
                    @if($item->catatan)
                    <p class="text-[11px] text-rose-600 dark:text-rose-400"><strong>Catatan:</strong> {{ $item->catatan }}</p>
                    @endif
                    @endif
                </div>

                <!-- Upload Form -->
                <form action="{{ route('siswa.berkas.upload') }}" method="POST" enctype="multipart/form-data" class="pt-2 border-t border-slate-100 dark:border-slate-700 space-y-2">
                    @csrf
                    <input type="hidden" name="jenis_berkas" value="{{ $b['key'] }}">
                    <div class="flex items-center gap-2">
                        <input type="file" name="file" required accept="image/*,.pdf" class="block w-full text-xs text-slate-500 dark:text-slate-400 file:mr-2 file:py-2 file:px-3 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-slate-100 dark:file:bg-slate-700 file:text-slate-700 dark:file:text-slate-200 hover:file:bg-slate-200">
                        <button type="submit" class="px-3.5 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs shadow-sm transition-colors shrink-0">
                            {{ $item ? 'Ganti' : 'Unggah' }}
                        </button>
                    </div>
                </form>
            </div>
            @endforeach
        </div>

        <!-- Jalur Prestasi: Sertifikat Kejuaraan -->
        @if($pendaftar->jalur_pendaftaran === 'Prestasi')
        <div class="p-6 md:p-8 rounded-3xl bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 shadow-sm space-y-6">
            <div class="border-b border-slate-200 dark:border-slate-700 pb-3 flex items-center justify-between flex-wrap gap-2">
                <div>
                    <h3 class="text-base font-bold text-slate-900 dark:text-white flex items-center gap-2">
                        <i data-lucide="award" class="w-5 h-5 text-amber-500"></i> Sertifikat Piagam Kejuaraan (Jalur Prestasi)
                    </h3>
                    <p class="text-xs text-slate-500 dark:text-slate-400">Unggah bukti piagam lomba tahfiz, akademik, olahraga, atau seni budaya yang Anda miliki.</p>
                </div>
            </div>

            <!-- List Existing Prestasi -->
            @if($prestasi->isNotEmpty())
            <div class="space-y-3">
                @foreach($prestasi as $pr)
                <div class="p-4 rounded-2xl bg-slate-50 dark:bg-slate-900/60 border border-slate-200 dark:border-slate-700 flex items-center justify-between text-xs">
                    <div class="space-y-1">
                        <span class="font-bold text-slate-900 dark:text-white text-sm block">{{ $pr->nama_prestasi }}</span>
                        <div class="text-slate-500 dark:text-slate-400">
                            {{ $pr->peringkat }} | Kategori: {{ $pr->kategori }} | Tingkat: {{ $pr->tingkat }} (Tahun {{ $pr->tahun }})
                        </div>
                        <div class="text-[11px] text-emerald-600 dark:text-emerald-400 font-semibold">
                            Penyelenggara: {{ $pr->penyelenggara }} | Skor Tambahan: +{{ $pr->skor_bobot }} Poin
                        </div>
                    </div>
                    <div class="text-right shrink-0">
                        <span class="px-2.5 py-1 rounded-full text-[10px] font-bold 
                            @if($pr->status_verifikasi === 'Valid') bg-emerald-100 text-emerald-800 dark:bg-emerald-950 dark:text-emerald-300
                            @elseif($pr->status_verifikasi === 'Ditolak') bg-rose-100 text-rose-800 dark:bg-rose-950 dark:text-rose-300
                            @else bg-amber-100 text-amber-800 dark:bg-amber-950 dark:text-amber-300 @endif">
                            {{ $pr->status_verifikasi }}
                        </span>
                        <div class="mt-2">
                            <a href="{{ route('dokumen.prestasi', $pr->id_prestasi) }}" target="_blank" class="text-xs text-emerald-600 dark:text-emerald-400 hover:underline">Lihat Sertifikat &rarr;</a>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
            @endif

            <!-- Form Tambah Prestasi -->
            <form action="{{ route('siswa.prestasi.upload') }}" method="POST" enctype="multipart/form-data" class="p-5 rounded-2xl bg-slate-50 dark:bg-slate-900/40 border border-slate-200 dark:border-slate-700 space-y-4">
                @csrf
                <div class="text-xs font-bold text-slate-800 dark:text-white uppercase tracking-wider">Tambah Piagam Prestasi Baru</div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Nama Prestasi / Kejuaraan</label>
                        <input type="text" name="nama_prestasi" required placeholder="Contoh: Juara 1 MTQ / Tahfiz 3 Juz" class="w-full px-3 py-2.5 rounded-xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-xs">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Kategori</label>
                        <select name="kategori" required class="w-full px-3 py-2.5 rounded-xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-xs">
                            <option value="Keagamaan/Tahfiz">Keagamaan / Tahfiz Al-Qur'an</option>
                            <option value="Akademik">Akademik (Olimpiade / Sains)</option>
                            <option value="Olahraga">Olahraga & Bela Diri</option>
                            <option value="Seni/Budaya">Seni Budaya & Musik Tradisional</option>
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Tingkat</label>
                        <select name="tingkat" required class="w-full px-3 py-2.5 rounded-xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-xs">
                            <option value="Kecamatan">Kecamatan</option>
                            <option value="Kabupaten/Kota">Kabupaten / Kota</option>
                            <option value="Provinsi">Provinsi</option>
                            <option value="Nasional">Nasional</option>
                            <option value="Internasional">Internasional</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Peringkat / Capaian</label>
                        <input type="text" name="peringkat" required placeholder="Juara 1 / Emas / Harapan" class="w-full px-3 py-2.5 rounded-xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-xs">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Tahun Perolehan</label>
                        <input type="number" name="tahun" required min="2020" max="{{ date('Y') }}" value="{{ date('Y') }}" class="w-full px-3 py-2.5 rounded-xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-xs">
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Instansi Penyelenggara</label>
                        <input type="text" name="penyelenggara" required placeholder="LPTQ / Kemenag / Dispora" class="w-full px-3 py-2.5 rounded-xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-xs">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Unggah Scan Piagam (JPG/PNG/PDF)</label>
                        <input type="file" name="file_sertifikat" required accept="image/*,.pdf" class="block w-full text-xs text-slate-500 file:mr-2 file:py-2 file:px-3 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-white dark:file:bg-slate-800">
                    </div>
                </div>

                <button type="submit" class="px-5 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs shadow-sm transition-all flex items-center gap-2">
                    <i data-lucide="plus" class="w-4 h-4"></i> Tambah Piagam Prestasi
                </button>
            </form>
        </div>
        @endif

    </div>
@endsection

