@extends('layouts.siswa')

@section('title', 'Jadwal dan Kartu Ujian')

@section('content')
<div class="space-y-6">

    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 mb-1">
                <span class="text-xs font-bold text-slate-500">Panel Calon Siswa</span>
                <span class="text-xs text-slate-400">/</span>
                <span class="text-xs font-bold text-slate-800 dark:text-white">Jadwal & Kartu Ujian</span>
            </div>
            <h2 class="text-xl font-extrabold text-slate-800 dark:text-white">Jadwal Seleksi Masuk</h2>
            <p class="text-xs text-slate-500 dark:text-slate-400">Informasi agenda tes wawancara, tes tahfiz, tes fisik, dan ruang ujian Anda.</p>
        </div>
        <div>
            @if($pendaftar->status_pembayaran === 'Paid')
            <a href="{{ route('siswa.cetak.kartu_ujian') }}" target="_blank" class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-[#f43f5e] hover:bg-[#e11d48] text-white text-xs font-bold shadow-md transition-all">
                <i data-lucide="printer" class="w-4 h-4"></i>
                <span>Cetak Kartu Ujian (PDF)</span>
            </a>
            @else
            <span class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl bg-amber-50 dark:bg-amber-950/40 text-amber-700 dark:text-amber-400 border border-amber-200 dark:border-amber-800 text-xs font-semibold">
                <i data-lucide="alert-circle" class="w-4 h-4"></i>
                <span>Lunasi biaya untuk cetak kartu ujian</span>
            </span>
            @endif
        </div>
    </div>

    <!-- Profil Singkat Calon Siswa -->
    <div class="p-4 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-sm flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div class="flex items-center gap-3">
            <div class="w-12 h-12 rounded-xl bg-pink-50 text-[#f43f5e] flex items-center justify-center font-black text-base shrink-0">
                {{ strtoupper(substr($pendaftar->nama, 0, 1)) }}
            </div>
            <div>
                <h3 class="font-extrabold text-sm text-slate-800 dark:text-white uppercase">{{ $pendaftar->nama }}</h3>
                <p class="text-xs text-slate-500">NISN: <span class="font-mono">{{ $pendaftar->nisn }}</span> | No. Daftar: <span class="font-mono font-bold text-[#f43f5e]">{{ $pendaftar->no_pendaftaran }}</span></p>
            </div>
        </div>
        <div class="flex items-center gap-2">
            <span class="text-xs text-slate-500">Pilihan 1:</span>
            <span class="font-bold text-xs text-slate-800 dark:text-white bg-slate-100 dark:bg-slate-800 px-2.5 py-1 rounded-lg">
                {{ $pendaftar->jurusan1 ? $pendaftar->jurusan1->nama_jurusan : '-' }}
            </span>
        </div>
    </div>

    <!-- Kartu Informasi Jadwal Seleksi -->
    @if($penempatan->isNotEmpty())
        <div class="space-y-4">
            @foreach($penempatan as $item)
                @php
                    $jadwal = $item->jadwal;
                    $ruang  = $item->ruang;
                @endphp
                <div class="bg-white dark:bg-slate-900 rounded-2xl border-2 border-pink-200 shadow-md p-6 relative overflow-hidden">
                    <div class="absolute top-0 right-0 px-4 py-1 bg-[#0B182F] text-white text-[10px] font-bold uppercase tracking-wider rounded-bl-xl">
                        Ruang Telah Ditentukan
                    </div>

                    <div class="space-y-4 pt-1">
                        <!-- Judul Agenda -->
                        <div>
                            <span class="inline-block text-[10px] font-bold uppercase tracking-wider px-2 py-0.5 rounded bg-pink-50 text-[#f43f5e] border border-pink-200 mb-1">
                                {{ $jadwal->jenis_seleksi ?? 'Tes Seleksi Masuk' }}
                            </span>
                            <h3 class="text-lg font-black text-slate-800 dark:text-white">{{ $jadwal->nama_jadwal }}</h3>
                        </div>

                        <!-- Grid Informasi Jadwal & Ruang -->
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 pt-2 border-t border-slate-100 dark:border-slate-800">
                            <!-- Tanggal & Waktu -->
                            <div class="p-3.5 rounded-xl bg-slate-50 dark:bg-slate-800/60 border border-slate-200/60 dark:border-slate-700/60 space-y-1">
                                <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider flex items-center gap-1.5">
                                    <i data-lucide="calendar" class="w-3.5 h-3.5 text-[#f43f5e]"></i>
                                    <span>Waktu Pelaksanaan</span>
                                </span>
                                <p class="text-xs font-bold text-slate-800 dark:text-white">
                                    {{ \Carbon\Carbon::parse($jadwal->tanggal)->isoFormat('dddd, D MMMM Y') }}
                                </p>
                                <p class="text-xs font-mono font-semibold text-[#f43f5e] dark:text-emerald-400">
                                    Pukul {{ substr($jadwal->waktu_mulai, 0, 5) }} - {{ substr($jadwal->waktu_selesai, 0, 5) }} WIB
                                </p>
                            </div>

                            <!-- Ruang & Gedung -->
                            <div class="p-3.5 rounded-xl bg-slate-50 dark:bg-slate-800/60 border border-slate-200/60 dark:border-slate-700/60 space-y-1">
                                <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider flex items-center gap-1.5">
                                    <i data-lucide="door-open" class="w-3.5 h-3.5 text-sky-500"></i>
                                    <span>Ruang Ujian</span>
                                </span>
                                <p class="text-xs font-bold text-slate-800 dark:text-white">
                                    {{ $ruang->nama_ruang ?? 'Ruang Seleksi' }} (Kode: {{ $ruang->kode_ruang ?? '-' }})
                                </p>
                                <p class="text-xs text-slate-600 dark:text-slate-400">
                                    {{ $ruang->gedung ?: 'Gedung Kampus SMK' }} | {{ $jadwal->lokasi ?? 'Kampus Utama' }}
                                </p>
                            </div>
                        </div>

                        <!-- Keterangan Tambahan / Tata Tertib -->
                        <div class="p-3.5 rounded-xl bg-amber-50/60 dark:bg-amber-950/30 border border-amber-200/60 dark:border-amber-800/60">
                            <span class="text-[11px] font-bold text-amber-800 dark:text-amber-300 flex items-center gap-1.5 mb-1">
                                <i data-lucide="info" class="w-3.5 h-3.5 text-amber-600"></i>
                                <span>Tata Tertib & Keterangan Panitia:</span>
                            </span>
                            <p class="text-xs text-slate-700 dark:text-slate-300 leading-relaxed">
                                {{ $jadwal->keterangan ?: 'Harap membawa cetakan Kartu Peserta Ujian, mengenakan seragam SMP asal rapi & bersepatu, dan hadir di lokasi 15 menit sebelum tes dimulai.' }}
                            </p>
                            @if($item->catatan)
                            <div class="mt-2 pt-2 border-t border-amber-200/50 text-[11px] text-amber-700 dark:text-amber-300">
                                <strong>Catatan Khusus:</strong> {{ $item->catatan }}
                            </div>
                            @endif
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @else
        <!-- Banner Belum Ditentukan -->
        <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 p-8 text-center space-y-3 shadow-sm">
            <div class="w-14 h-14 rounded-2xl bg-amber-50 dark:bg-amber-950/50 text-amber-500 flex items-center justify-center mx-auto">
                <i data-lucide="clock" class="w-7 h-7"></i>
            </div>
            <div>
                <h3 class="text-base font-extrabold text-slate-800 dark:text-white">Jadwal atau Ruang Seleksi Belum Ditentukan</h3>
                <p class="text-xs text-slate-500 max-w-md mx-auto mt-1 leading-relaxed">
                    Saat ini panitia PPDB sedang menyusun alokasi ruang dan sesi tes ujian untuk Anda. Harap periksa halaman ini secara berkala atau hubungi Petugas Tata Usaha sekolah.
                </p>
            </div>
            @if($pendaftar->status_pembayaran === 'Paid')
            <div class="pt-2">
                <a href="{{ route('siswa.cetak.kartu_ujian') }}" target="_blank" class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold transition-colors">
                    <i data-lucide="printer" class="w-3.5 h-3.5"></i>
                    <span>Tetap Cetak Kartu Ujian Sementara</span>
                </a>
            </div>
            @endif
        </div>
    @endif

    <!-- Panduan / Petunjuk -->
    <div class="p-4 rounded-2xl bg-slate-50 dark:bg-slate-900/60 border border-slate-200 dark:border-slate-800 text-xs text-slate-600 dark:text-slate-400 space-y-2">
        <h4 class="font-bold text-slate-800 dark:text-white flex items-center gap-1.5">
            <i data-lucide="help-circle" class="w-4 h-4 text-[#f43f5e]"></i>
            <span>Informasi Penting Ujian Masuk</span>
        </h4>
        <ul class="list-disc list-inside space-y-1 pl-1 text-[11px]">
            <li>Calon siswa wajib hadir mengenakan pakaian seragam asal sekolah yang rapi, sopan, dan bersepatu.</li>
            <li>Bawa kartu peserta tes seleksi fisik yang sudah dicetak dari halaman ini.</li>
            <li>Seleksi terdiri atas wawancara minat bakat, tes baca Al-Qur'an (tahfiz dasar), dan tes fisik/buta warna.</li>
            <li>Perubahan jadwal hanya dapat dilakukan oleh Petugas Tata Usaha sekolah.</li>
        </ul>
    </div>

</div>
@endsection

