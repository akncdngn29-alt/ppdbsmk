@extends('layouts.siswa')

@section('title', 'Dashboard Calon Siswa')

@section('content')
<div class="space-y-6">

        <!-- Welcome Banner -->
        <div class="p-6 md:p-8 rounded-3xl bg-gradient-to-r from-[#161c2d] via-[#1a142c] to-[#2b0e22] text-white shadow-2xl border border-pink-500/20 relative overflow-hidden flex flex-col md:flex-row md:items-center justify-between gap-6">
            <div class="absolute -right-10 -bottom-10 w-64 h-64 bg-[#fa2f79]/10 rounded-full blur-3xl pointer-events-none"></div>
            <div class="space-y-2 relative z-10">
                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-[#fa2f79]/15 text-[#fa2f79] border border-pink-500/30 text-xs font-bold">
                    <i data-lucide="sparkles" class="w-3.5 h-3.5"></i> Calon Siswa Baru T.A 2026/2027
                </div>
                <h1 class="text-2xl md:text-3xl font-extrabold tracking-tight">
                    Ahlan wa Sahlan, {{ $user->nama }}!
                </h1>
                <p class="text-xs md:text-sm text-slate-300">
                    NISN: <span class="font-mono font-bold text-white">{{ $user->nisn }}</span>
                    @if($pendaftar)
                    | No. Pendaftaran: <span class="font-mono font-bold text-[#fa2f79]">{{ $pendaftar->no_pendaftaran }}</span>
                    @endif
                </p>
            </div>

            <!-- Quick Action Button -->
            <div class="flex flex-wrap items-center gap-3 relative z-10">
                @if(!$pendaftar)
                <a href="{{ route('siswa.formulir') }}" class="px-6 py-3.5 rounded-2xl bg-[#fa2f79] hover:bg-[#e11d48] text-white font-bold text-xs md:text-sm shadow-lg shadow-pink-500/30 transition-all flex items-center gap-2">
                    <i data-lucide="file-plus" class="w-4 h-4"></i> Lengkapi Formulir Sekarang
                </a>
                @elseif(!$pendaftar->is_locked)
                <a href="{{ route('siswa.formulir') }}" class="px-6 py-3.5 rounded-2xl bg-amber-500 hover:bg-amber-600 text-white font-bold text-xs md:text-sm shadow-lg shadow-amber-500/30 transition-all flex items-center gap-2">
                    <i data-lucide="edit" class="w-4 h-4"></i> Lanjutkan / Edit Formulir
                </a>
                @elseif($pendaftar->status_pembayaran !== 'Paid')
                <a href="{{ route('siswa.pembayaran') }}" class="px-6 py-3.5 rounded-2xl bg-[#fa2f79] hover:bg-[#e11d48] text-white font-bold text-xs md:text-sm shadow-lg shadow-pink-500/30 transition-all flex items-center gap-2">
                    <i data-lucide="credit-card" class="w-4 h-4"></i> Bayar Biaya Formulir
                </a>
                @endif
            </div>
        </div>

        <!-- 7-Step Stepper Progress Bar -->
        <div class="p-6 md:p-8 rounded-3xl bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 shadow-sm space-y-6">
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-base md:text-lg font-bold text-slate-900 dark:text-white">Alur Progres Pendaftaran Anda</h2>
                    <p class="text-xs text-slate-500 dark:text-slate-400">Pantau setiap tahapan secara berkala hingga resmi diterima dan daftar ulang.</p>
                </div>
                <span class="px-3 py-1 rounded-full text-xs font-bold bg-pink-50 dark:bg-pink-950/60 text-[#fa2f79] dark:text-pink-300 border border-pink-200 dark:border-pink-800/60">
                    Tahap {{ $stage }} dari 7
                </span>
            </div>

            <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-7 gap-3">
                @php
                    $stepsData = [
                        ['no' => 1, 'title' => 'Formulir', 'desc' => $pendaftar ? ($pendaftar->is_locked ? 'Terkunci' : 'Draft') : 'Belum Isi'],
                        ['no' => 2, 'title' => 'Pembayaran', 'desc' => $pendaftar ? ($pendaftar->status_pembayaran === 'Paid' ? 'Lunas' : 'Menunggu') : 'Pending'],
                        ['no' => 3, 'title' => 'Upload Berkas', 'desc' => $pendaftar ? ($uploadedDocsCount . '/4 Dokumen') : 'Belum Upload'],
                        ['no' => 4, 'title' => 'Verifikasi', 'desc' => $pendaftar ? $pendaftar->status_verifikasi : 'Menunggu'],
                        ['no' => 5, 'title' => 'Tes Seleksi', 'desc' => ($pendaftar && $pendaftar->nilaiSeleksi) ? 'Selesai' : 'Siap Tes'],
                        ['no' => 6, 'title' => 'Kelulusan', 'desc' => $pendaftar ? $pendaftar->status_kelulusan : 'Diproses'],
                        ['no' => 7, 'title' => 'Daftar Ulang', 'desc' => ($pendaftar && $pendaftar->status_daftar_ulang === 'Selesai') ? 'Resmi Siswa' : 'Konfirmasi'],
                    ];
                @endphp

                @foreach($stepsData as $st)
                @php
                    $isAllFinished = $pendaftar && $pendaftar->status_daftar_ulang === 'Selesai';
                    $isDone = $stage > $st['no'] || ($st['no'] === 7 && $isAllFinished);
                    $isCurrent = $stage === $st['no'] && !$isAllFinished;
                @endphp
                <div class="p-4 rounded-2xl border text-center transition-all 
                    @if($isDone) bg-pink-50 dark:bg-pink-950/40 border-pink-200 dark:border-pink-800 text-pink-900 dark:text-pink-200
                    @elseif($isCurrent) bg-pink-50/50 dark:bg-[#1a1226] border-[#fa2f79] shadow-md ring-2 ring-pink-500/20 text-slate-900 dark:text-white
                    @else bg-slate-50 dark:bg-slate-800/40 border-slate-200 dark:border-slate-700 text-slate-400 dark:text-slate-500 @endif">
                    <div class="w-7 h-7 rounded-full mx-auto mb-2 flex items-center justify-center text-xs font-bold
                        @if($isDone) bg-[#fa2f79] text-white
                        @elseif($isCurrent) bg-[#fa2f79] text-white animate-pulse
                        @else bg-slate-200 dark:bg-slate-700 text-slate-600 dark:text-slate-400 @endif">
                        @if($isDone) <i data-lucide="check" class="w-4 h-4"></i> @else {{ $st['no'] }} @endif
                    </div>
                    <div class="text-xs font-bold truncate">{{ $st['title'] }}</div>
                    <div class="text-[10px] mt-0.5 truncate font-medium
                        @if($isDone) text-pink-600 dark:text-pink-400
                        @elseif($isCurrent) text-[#fa2f79] font-bold
                        @endif">
                        {{ $st['desc'] }}
                    </div>
                </div>
                @endforeach
            </div>
        </div>

        <!-- 3 Action Cards (Form, Berkas, Pembayaran) -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <!-- Card 1: Formulir -->
            <div class="p-6 rounded-3xl bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 shadow-sm flex flex-col justify-between space-y-4">
                <div class="space-y-3">
                    <div class="flex items-center justify-between">
                        <div class="w-10 h-10 rounded-xl bg-pink-50 dark:bg-pink-950/60 text-[#fa2f79] flex items-center justify-center">
                            <i data-lucide="file-text" class="w-5 h-5"></i>
                        </div>
                        <span class="px-2.5 py-0.5 rounded-full text-[11px] font-bold 
                            @if($pendaftar && $pendaftar->is_locked) bg-pink-100 text-[#be123c] dark:bg-pink-950 dark:text-pink-300
                            @elseif($pendaftar) bg-amber-100 text-amber-800 dark:bg-amber-950 dark:text-amber-300
                            @else bg-slate-100 text-slate-600 dark:bg-slate-700 dark:text-slate-300 @endif">
                            {{ $pendaftar ? ($pendaftar->is_locked ? 'Difinalisasi' : 'Draft Tersimpan') : 'Belum Diisi' }}
                        </span>
                    </div>
                    <div>
                        <h3 class="text-base font-bold text-slate-900 dark:text-white">Formulir Pendaftaran</h3>
                        <p class="text-xs text-slate-500 dark:text-slate-400">Biodata diri, asal SMP, data orang tua/wali, ukuran seragam & pilihan jurusan.</p>
                    </div>
                    @if($pendaftar)
                    <div class="text-xs text-slate-700 dark:text-slate-300 space-y-1 pt-2 border-t border-slate-100 dark:border-slate-700">
                        <div><strong>Pilihan 1:</strong> {{ $pendaftar->jurusan1 ? $pendaftar->jurusan1->nama_jurusan : 'Belum dipilih' }}</div>
                        <div><strong>Pilihan 2:</strong> {{ $pendaftar->jurusan2 ? $pendaftar->jurusan2->nama_jurusan : '-' }}</div>
                        <div><strong>Jalur:</strong> {{ $pendaftar->jalur_pendaftaran ?: 'Belum dipilih' }}</div>
                    </div>
                    @endif
                </div>

                <div class="pt-2">
                    @if(!$pendaftar || !$pendaftar->is_locked)
                    <a href="{{ route('siswa.formulir') }}" class="w-full py-2.5 px-4 rounded-xl bg-slate-100 dark:bg-slate-700 text-slate-800 dark:text-slate-200 font-bold text-xs hover:bg-[#fa2f79] hover:text-white transition-colors flex items-center justify-center gap-2">
                        <i data-lucide="edit-3" class="w-4 h-4"></i> Isi / Edit Formulir
                    </a>
                    @else
                    <a href="{{ route('siswa.cetak.formulir') }}" target="_blank" class="w-full py-2.5 px-4 rounded-xl bg-pink-50 dark:bg-pink-950/50 text-[#fa2f79] dark:text-pink-300 font-bold text-xs hover:bg-pink-100 transition-colors flex items-center justify-center gap-2">
                        <i data-lucide="printer" class="w-4 h-4"></i> Cetak Lembar Formulir (PDF)
                    </a>
                    @endif
                </div>
            </div>

            <!-- Card 2: Pembayaran -->
            <div class="p-6 rounded-3xl bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 shadow-sm flex flex-col justify-between space-y-4">
                <div class="space-y-3">
                    <div class="flex items-center justify-between">
                        <div class="w-10 h-10 rounded-xl bg-pink-50 dark:bg-pink-950/60 text-[#fa2f79] flex items-center justify-center">
                            <i data-lucide="credit-card" class="w-5 h-5"></i>
                        </div>
                        <span class="px-2.5 py-0.5 rounded-full text-[11px] font-bold 
                            @if($pendaftar && $pendaftar->status_pembayaran === 'Paid') bg-pink-100 text-[#be123c] dark:bg-pink-950 dark:text-pink-300
                            @else bg-pink-100 text-[#fa2f79] dark:bg-pink-950/80 dark:text-pink-300 @endif">
                            {{ $pendaftar ? ($pendaftar->status_pembayaran === 'Paid' ? 'LUNAS' : 'Menunggu Bayar') : 'Belum Ada Tagihan' }}
                        </span>
                    </div>
                    <div>
                        <h3 class="text-base font-bold text-slate-900 dark:text-white">Biaya Formulir PPDB</h3>
                        <p class="text-xs text-slate-500 dark:text-slate-400">Pembayaran online instan via Midtrans (QRIS/VA) atau tunai di loket TU.</p>
                    </div>
                    @if($pendaftar && $pendaftar->pembayaran)
                    <div class="text-xs text-slate-700 dark:text-slate-300 space-y-1 pt-2 border-t border-slate-100 dark:border-slate-700">
                        <div><strong>Total Tagihan:</strong> Rp {{ number_format($pendaftar->pembayaran->total_bayar, 0, ',', '.') }}</div>
                        <div><strong>Metode:</strong> {{ $pendaftar->pembayaran->metode_pembayaran ?: 'Belum dipilih' }}</div>
                        <div><strong>Kode Transaksi:</strong> <span class="font-mono">{{ $pendaftar->pembayaran->kode_transaksi }}</span></div>
                    </div>
                    @endif
                </div>

                <div class="pt-2 flex flex-col gap-2">
                    <a href="{{ route('siswa.pembayaran') }}" class="w-full py-2.5 px-4 rounded-xl @if($pendaftar && $pendaftar->status_pembayaran !== 'Paid') bg-[#fa2f79] hover:bg-[#e11d48] text-white shadow-md shadow-pink-500/25 @else bg-slate-100 dark:bg-slate-700 text-slate-800 dark:text-slate-200 hover:bg-[#fa2f79] hover:text-white @endif font-bold text-xs transition-colors flex items-center justify-center gap-2">
                        <i data-lucide="wallet" class="w-4 h-4"></i> Rincian & Checkout Pembayaran
                    </a>
                    @if($pendaftar && $pendaftar->status_pembayaran === 'Paid')
                    <a href="{{ route('siswa.cetak.kwitansi') }}" target="_blank" class="w-full py-2 px-4 rounded-xl text-center text-xs font-semibold text-[#fa2f79] dark:text-pink-400 hover:underline">
                        Cetak Kwitansi Pembayaran Lunas &rarr;
                    </a>
                    @endif
                </div>
            </div>

            <!-- Card 3: Berkas Digital -->
            <div class="p-6 rounded-3xl bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 shadow-sm flex flex-col justify-between space-y-4">
                <div class="space-y-3">
                    <div class="flex items-center justify-between">
                        <div class="w-10 h-10 rounded-xl bg-purple-50 dark:bg-purple-950/60 text-purple-600 dark:text-purple-400 flex items-center justify-center">
                            <i data-lucide="folder-up" class="w-5 h-5"></i>
                        </div>
                        <div class="flex items-center gap-1.5 flex-wrap justify-end">
                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold {{ $allDocsUploaded ? 'bg-emerald-100 text-emerald-800 dark:bg-emerald-950 dark:text-emerald-300' : 'bg-slate-100 text-slate-600 dark:bg-slate-700 dark:text-slate-300' }}">
                                {{ $uploadedDocsCount }}/4 Upload
                            </span>
                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold 
                                @if($pendaftar && $pendaftar->status_verifikasi === 'Terverifikasi') bg-pink-100 text-[#be123c] dark:bg-pink-950 dark:text-pink-300
                                @elseif($pendaftar && $pendaftar->status_verifikasi === 'Ditolak') bg-rose-100 text-rose-800 dark:bg-rose-950 dark:text-rose-300
                                @else bg-amber-100 text-amber-800 dark:bg-amber-950 dark:text-amber-300 @endif">
                                {{ $pendaftar ? $pendaftar->status_verifikasi : 'Belum Diunggah' }}
                            </span>
                        </div>
                    </div>
                    <div>
                        <h3 class="text-base font-bold text-slate-900 dark:text-white">Dokumen Persyaratan</h3>
                        <p class="text-xs text-slate-500 dark:text-slate-400">Scan Ijazah/SKL, Kartu Keluarga, Pas Foto, Akta, dan Piagam Prestasi.</p>
                    </div>
                    @if($pendaftar)
                    <div class="text-xs text-slate-700 dark:text-slate-300 space-y-1 pt-2 border-t border-slate-100 dark:border-slate-700">
                        <div><strong>Kelengkapan Berkas:</strong> {{ $uploadedDocsCount }}/4 Dokumen Wajib {{ $allDocsUploaded ? '(Lengkap)' : '(Belum Lengkap)' }}</div>
                        <div><strong>Status Verifikasi TU:</strong> {{ $pendaftar->status_verifikasi }}</div>
                        @if($pendaftar->catatan_verifikasi)
                        <div class="text-rose-600 dark:text-rose-400 text-[11px]"><strong>Catatan TU:</strong> {{ $pendaftar->catatan_verifikasi }}</div>
                        @endif
                    </div>
                    @endif
                </div>

                <div class="pt-2">
                    <a href="{{ route('siswa.berkas') }}" class="w-full py-2.5 px-4 rounded-xl bg-slate-100 dark:bg-slate-700 text-slate-800 dark:text-slate-200 font-bold text-xs hover:bg-[#fa2f79] hover:text-white transition-colors flex items-center justify-center gap-2">
                        <i data-lucide="upload" class="w-4 h-4"></i> Kelola & Unggah Berkas
                    </a>
                </div>
            </div>
        </div>

        <!-- Tes Seleksi & Kelulusan Section (If eligible) -->
        @if($pendaftar && $pendaftar->status_pembayaran === 'Paid')
        <div class="p-6 md:p-8 rounded-3xl bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 shadow-sm space-y-6">
            <div class="flex items-center justify-between flex-wrap gap-4">
                <div>
                    <h2 class="text-lg font-bold text-slate-900 dark:text-white flex items-center gap-2">
                        <i data-lucide="award" class="w-5 h-5 text-amber-500"></i> Kartu Tes & Hasil Seleksi Kelulusan
                    </h2>
                    <p class="text-xs text-slate-500 dark:text-slate-400">Cetak kartu ujian masuk dan pantau status kelulusan final yang disahkan Kepala Sekolah.</p>
                </div>
                <a href="{{ route('siswa.cetak.kartu_ujian') }}" target="_blank" class="px-5 py-2.5 rounded-xl bg-[#fa2f79] hover:bg-[#e11d48] text-white font-bold text-xs shadow-md transition-all flex items-center gap-2">
                    <i data-lucide="printer" class="w-4 h-4"></i> Cetak Kartu Peserta Ujian (PDF)
                </a>
            </div>

            <div class="p-4 rounded-2xl bg-slate-50 dark:bg-slate-900/60 border border-slate-200 dark:border-slate-700 flex flex-col md:flex-row items-center justify-between gap-4">
                <div class="space-y-1 text-center md:text-left">
                    <span class="text-xs text-slate-400 uppercase font-semibold">Status Kelulusan Final</span>
                    <div class="text-xl font-extrabold text-slate-900 dark:text-white flex items-center justify-center md:justify-start gap-2">
                        @if($pendaftar->status_kelulusan === 'Diterima')
                        <span class="text-[#fa2f79] dark:text-pink-400 flex items-center gap-1.5">
                            <i data-lucide="check-circle" class="w-5 h-5"></i> DITERIMA
                        </span>
                        <span class="text-xs font-semibold text-slate-500">di Jurusan {{ $pendaftar->terima ? $pendaftar->terima->jurusan->nama_jurusan : ($pendaftar->jurusan1 ? $pendaftar->jurusan1->nama_jurusan : '-') }}</span>
                        @elseif($pendaftar->status_kelulusan === 'Cadangan')
                        <span class="text-sky-600 dark:text-sky-400 flex items-center gap-1.5">
                            <i data-lucide="help-circle" class="w-5 h-5"></i> CADANGAN / WAITING LIST
                        </span>
                        @elseif($pendaftar->status_kelulusan === 'Tidak Lolos')
                        <span class="text-rose-600 dark:text-rose-400 flex items-center gap-1.5">
                            <i data-lucide="x-circle" class="w-5 h-5"></i> BELUM LOLOS
                        </span>
                        @else
                        <span class="text-amber-600 dark:text-amber-400 flex items-center gap-1.5">
                            <i data-lucide="clock" class="w-5 h-5"></i> SEDANG DIPROSES
                        </span>
                        @endif
                    </div>
                </div>

                <div class="flex items-center gap-3">
                    @if($pendaftar->status_kelulusan === 'Diterima')
                    <a href="{{ route('siswa.cetak.skl') }}" target="_blank" class="px-4 py-2.5 rounded-xl bg-pink-50 dark:bg-pink-950/60 text-[#fa2f79] dark:text-pink-300 border border-pink-200 dark:border-pink-800 font-bold text-xs hover:bg-pink-100 transition-colors flex items-center gap-2">
                        <i data-lucide="file-check" class="w-4 h-4"></i> Unduh SKL Resmi (PDF)
                    </a>
                    @if($pendaftar->status_daftar_ulang !== 'Selesai')
                    <form action="{{ route('siswa.daftar_ulang.konfirmasi') }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin mengonfirmasi pendaftaran ulang sebagai Siswa Baru SMK IB Khalifah Bangsa?');">
                        @csrf
                        <button type="submit" class="px-5 py-2.5 rounded-xl bg-[#fa2f79] hover:bg-[#e11d48] text-white font-bold text-xs shadow-md transition-all flex items-center gap-2">
                            <i data-lucide="user-check" class="w-4 h-4"></i> Konfirmasi Daftar Ulang
                        </button>
                    </form>
                    @else
                    <span class="px-4 py-2.5 rounded-xl bg-pink-100 dark:bg-pink-950 text-[#be123c] dark:text-pink-300 font-bold text-xs flex items-center gap-1.5">
                        <i data-lucide="check" class="w-4 h-4"></i> Daftar Ulang Selesai
                    </span>
                    @endif
                    @endif
                </div>
            </div>
        </div>
        @endif

    </div>
@endsection

