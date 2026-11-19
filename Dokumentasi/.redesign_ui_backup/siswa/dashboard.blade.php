@extends('layouts.siswa')

@section('title', 'Dashboard Calon Siswa')

@section('content')
<div class="space-y-6">

    <!-- Welcome Banner (Navy Dominant dengan Aksen Pink) -->
    <div class="p-6 md:p-8 rounded-3xl bg-[#0B182F] text-white shadow-xl border border-slate-800 flex flex-col md:flex-row md:items-center justify-between gap-6">
        <div class="space-y-2">
            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-pink-500/20 border border-pink-500/30 text-pink-300 text-xs font-bold">
                <i data-lucide="sparkles" class="w-3.5 h-3.5 text-[#f43f5e]"></i>
                <span>Calon Siswa Baru T.A 2026/2027</span>
            </div>
            <h1 class="text-2xl md:text-3xl font-black tracking-tight text-white">
                Selamat Datang, <span class="text-[#f43f5e]">{{ $user->nama }}</span>!
            </h1>
            <p class="text-xs md:text-sm text-slate-300">
                NISN: <span class="font-mono font-bold text-white">{{ $user->nisn }}</span>
                @if($pendaftar)
                &bull; No. Pendaftaran: <span class="font-mono font-bold text-pink-400">{{ $pendaftar->no_pendaftaran }}</span>
                @endif
            </p>
        </div>

        <!-- Quick Action Button -->
        <div class="flex flex-wrap items-center gap-3">
            @if(!$pendaftar)
            <a href="{{ route('siswa.formulir') }}" class="px-6 py-3 rounded-full bg-[#f43f5e] hover:bg-[#e11d48] text-white font-bold text-xs md:text-sm shadow-md hover:shadow-lg transition-all flex items-center gap-2">
                <i data-lucide="file-plus" class="w-4 h-4"></i> Lengkapi Formulir Sekarang &rarr;
            </a>
            @elseif(!$pendaftar->is_locked)
            <a href="{{ route('siswa.formulir') }}" class="px-6 py-3 rounded-full bg-amber-500 hover:bg-amber-600 text-white font-bold text-xs md:text-sm shadow-md transition-all flex items-center gap-2">
                <i data-lucide="edit" class="w-4 h-4"></i> Lanjutkan / Edit Formulir
            </a>
            @elseif($pendaftar->status_pembayaran !== 'Paid')
            <a href="{{ route('siswa.pembayaran') }}" class="px-6 py-3 rounded-full bg-[#f43f5e] hover:bg-[#e11d48] text-white font-bold text-xs md:text-sm shadow-md hover:shadow-lg transition-all flex items-center gap-2">
                <i data-lucide="credit-card" class="w-4 h-4"></i> Bayar Biaya Formulir &rarr;
            </a>
            @endif
        </div>
    </div>

    <!-- 7-Step Stepper Progress Bar -->
    <div class="p-6 md:p-8 rounded-3xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-xs space-y-6">
        <div class="flex items-center justify-between flex-wrap gap-2">
            <div>
                <h2 class="text-base md:text-lg font-bold text-[#0B182F] dark:text-white">Alur Progres Pendaftaran Anda</h2>
                <p class="text-xs text-slate-500 dark:text-slate-400">Pantau setiap tahapan secara berkala hingga resmi diterima dan menyelesaikan daftar ulang.</p>
            </div>
            <span class="px-3 py-1 rounded-full text-xs font-bold bg-pink-50 border border-pink-200 text-[#f43f5e]">
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
                $isDone = $stage > $st['no'];
                $isCurrent = $stage === $st['no'];
            @endphp
            <div class="p-4 rounded-2xl border text-center transition-all 
                @if($isDone) bg-emerald-50 dark:bg-emerald-950/40 border-emerald-200 dark:border-emerald-800 text-emerald-800 dark:text-emerald-300
                @elseif($isCurrent) bg-[#fff5f7] border-pink-300 shadow-sm ring-2 ring-pink-500/20 text-[#0B182F]
                @else bg-slate-50 dark:bg-slate-800/40 border-slate-200 dark:border-slate-700 text-slate-400 @endif">
                <div class="w-7 h-7 rounded-full mx-auto mb-2 flex items-center justify-center text-xs font-bold
                    @if($isDone) bg-emerald-600 text-white
                    @elseif($isCurrent) bg-[#f43f5e] text-white animate-pulse
                    @else bg-slate-200 text-slate-600 @endif">
                    @if($isDone) <i data-lucide="check" class="w-4 h-4"></i> @else {{ $st['no'] }} @endif
                </div>
                <div class="text-xs font-bold truncate">{{ $st['title'] }}</div>
                <div class="text-[10px] mt-0.5 truncate font-medium
                    @if($isDone) text-emerald-600 dark:text-emerald-400 @elseif($isCurrent) text-[#f43f5e] font-bold @endif">
                    {{ $st['desc'] }}
                </div>
            </div>
            @endforeach
        </div>
    </div>

    <!-- 3 Action Cards (Form, Pembayaran, Berkas) -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        
        <!-- Card 1: Formulir -->
        <div class="p-6 rounded-3xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-xs flex flex-col justify-between space-y-4">
            <div class="space-y-3">
                <div class="flex items-center justify-between">
                    <div class="w-10 h-10 rounded-xl bg-pink-50 text-[#f43f5e] flex items-center justify-center">
                        <i data-lucide="file-text" class="w-5 h-5"></i>
                    </div>
                    <span class="px-2.5 py-0.5 rounded-full text-[11px] font-bold 
                        @if($pendaftar && $pendaftar->is_locked) bg-emerald-100 text-emerald-800 border border-emerald-200
                        @elseif($pendaftar) bg-amber-100 text-amber-800 border border-amber-200
                        @else bg-slate-100 text-slate-600 @endif">
                        {{ $pendaftar ? ($pendaftar->is_locked ? 'Difinalisasi' : 'Draft Tersimpan') : 'Belum Diisi' }}
                    </span>
                </div>
                <div>
                    <h3 class="text-base font-bold text-[#0B182F] dark:text-white">Formulir Pendaftaran</h3>
                    <p class="text-xs text-slate-500 dark:text-slate-400">Biodata diri, asal SMP, data orang tua/wali, ukuran seragam & pilihan jurusan.</p>
                </div>
                @if($pendaftar)
                <div class="text-xs text-slate-700 dark:text-slate-300 space-y-1 pt-2 border-t border-slate-100 dark:border-slate-800">
                    <div><strong>Pilihan 1:</strong> {{ $pendaftar->jurusan1 ? $pendaftar->jurusan1->nama_jurusan : 'Belum dipilih' }}</div>
                    <div><strong>Pilihan 2:</strong> {{ $pendaftar->jurusan2 ? $pendaftar->jurusan2->nama_jurusan : '-' }}</div>
                    <div><strong>Jalur:</strong> {{ $pendaftar->jalur_pendaftaran ?: 'Belum dipilih' }}</div>
                </div>
                @endif
            </div>

            <div class="pt-2">
                @if(!$pendaftar || !$pendaftar->is_locked)
                <a href="{{ route('siswa.formulir') }}" class="w-full py-2.5 px-4 rounded-xl bg-slate-100 dark:bg-slate-800 text-slate-800 dark:text-slate-200 font-bold text-xs hover:bg-[#f43f5e] hover:text-white transition-colors flex items-center justify-center gap-2">
                    <i data-lucide="edit-3" class="w-4 h-4"></i> Isi / Edit Formulir
                </a>
                @else
                <a href="{{ route('siswa.cetak.formulir') }}" target="_blank" class="w-full py-2.5 px-4 rounded-xl bg-pink-50 text-[#f43f5e] border border-pink-200 font-bold text-xs hover:bg-pink-100 transition-colors flex items-center justify-center gap-2">
                    <i data-lucide="printer" class="w-4 h-4"></i> Cetak Lembar Formulir (PDF)
                </a>
                @endif
            </div>
        </div>

        <!-- Card 2: Pembayaran -->
        <div class="p-6 rounded-3xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-xs flex flex-col justify-between space-y-4">
            <div class="space-y-3">
                <div class="flex items-center justify-between">
                    <div class="w-10 h-10 rounded-xl bg-pink-50 text-[#f43f5e] flex items-center justify-center">
                        <i data-lucide="credit-card" class="w-5 h-5"></i>
                    </div>
                    <span class="px-2.5 py-0.5 rounded-full text-[11px] font-bold 
                        @if($pendaftar && $pendaftar->status_pembayaran === 'Paid') bg-emerald-100 text-emerald-800 border border-emerald-200
                        @else bg-amber-100 text-amber-800 border border-amber-200 @endif">
                        {{ $pendaftar ? ($pendaftar->status_pembayaran === 'Paid' ? 'LUNAS' : 'Menunggu Bayar') : 'Belum Ada Tagihan' }}
                    </span>
                </div>
                <div>
                    <h3 class="text-base font-bold text-[#0B182F] dark:text-white">Biaya Formulir PPDB</h3>
                    <p class="text-xs text-slate-500 dark:text-slate-400">Pembayaran online instan via Midtrans (QRIS/VA) atau bayar tunai di loket TU.</p>
                </div>
                @if($pendaftar && $pendaftar->pembayaran)
                <div class="text-xs text-slate-700 dark:text-slate-300 space-y-1 pt-2 border-t border-slate-100 dark:border-slate-800">
                    <div><strong>Total Tagihan:</strong> Rp {{ number_format($pendaftar->pembayaran->total_bayar, 0, ',', '.') }}</div>
                    <div><strong>Metode:</strong> {{ $pendaftar->pembayaran->metode_pembayaran ?: 'Belum dipilih' }}</div>
                    <div><strong>Kode Transaksi:</strong> <span class="font-mono">{{ $pendaftar->pembayaran->kode_transaksi }}</span></div>
                </div>
                @endif
            </div>

            <div class="pt-2 flex flex-col gap-2">
                <a href="{{ route('siswa.pembayaran') }}" class="w-full py-2.5 px-4 rounded-xl bg-slate-100 dark:bg-slate-800 text-slate-800 dark:text-slate-200 font-bold text-xs hover:bg-[#0B182F] hover:text-white transition-colors flex items-center justify-center gap-2">
                    <i data-lucide="wallet" class="w-4 h-4 text-[#f43f5e]"></i> Rincian & Checkout Pembayaran
                </a>
                @if($pendaftar && $pendaftar->status_pembayaran === 'Paid')
                <a href="{{ route('siswa.cetak.kwitansi') }}" target="_blank" class="w-full py-1.5 px-4 rounded-xl text-center text-xs font-bold text-[#f43f5e] hover:underline">
                    Cetak Kwitansi Pembayaran Lunas &rarr;
                </a>
                @endif
            </div>
        </div>

        <!-- Card 3: Berkas Digital -->
        <div class="p-6 rounded-3xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-xs flex flex-col justify-between space-y-4">
            <div class="space-y-3">
                <div class="flex items-center justify-between">
                    <div class="w-10 h-10 rounded-xl bg-pink-50 text-[#f43f5e] flex items-center justify-center">
                        <i data-lucide="folder-up" class="w-5 h-5"></i>
                    </div>
                    <div class="flex items-center gap-1.5 flex-wrap justify-end">
                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold {{ $allDocsUploaded ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-100 text-slate-600' }}">
                            {{ $uploadedDocsCount }}/4 Dokumen
                        </span>
                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold 
                            @if($pendaftar && $pendaftar->status_verifikasi === 'Terverifikasi') bg-emerald-100 text-emerald-800
                            @elseif($pendaftar && $pendaftar->status_verifikasi === 'Ditolak') bg-rose-100 text-rose-800
                            @else bg-amber-100 text-amber-800 @endif">
                            {{ $pendaftar ? $pendaftar->status_verifikasi : 'Belum Diunggah' }}
                        </span>
                    </div>
                </div>
                <div>
                    <h3 class="text-base font-bold text-[#0B182F] dark:text-white">Dokumen Persyaratan</h3>
                    <p class="text-xs text-slate-500 dark:text-slate-400">Scan Ijazah/SKL, Kartu Keluarga, Pas Foto, Akta, dan Piagam Prestasi.</p>
                </div>
                @if($pendaftar)
                <div class="text-xs text-slate-700 dark:text-slate-300 space-y-1 pt-2 border-t border-slate-100 dark:border-slate-800">
                    <div><strong>Kelengkapan:</strong> {{ $uploadedDocsCount }}/4 Dokumen Wajib {{ $allDocsUploaded ? '(Lengkap)' : '(Belum Lengkap)' }}</div>
                    <div><strong>Status Verifikasi TU:</strong> {{ $pendaftar->status_verifikasi }}</div>
                    @if($pendaftar->catatan_verifikasi)
                    <div class="text-rose-600 text-[11px]"><strong>Catatan TU:</strong> {{ $pendaftar->catatan_verifikasi }}</div>
                    @endif
                </div>
                @endif
            </div>

            <div class="pt-2">
                <a href="{{ route('siswa.berkas') }}" class="w-full py-2.5 px-4 rounded-xl bg-slate-100 dark:bg-slate-800 text-slate-800 dark:text-slate-200 font-bold text-xs hover:bg-[#f43f5e] hover:text-white transition-colors flex items-center justify-center gap-2">
                    <i data-lucide="upload" class="w-4 h-4"></i> Kelola & Unggah Berkas
                </a>
            </div>
        </div>

    </div>

    <!-- Tes Seleksi & Kelulusan Section (If eligible) -->
    @if($pendaftar && $pendaftar->status_pembayaran === 'Paid')
    <div class="p-6 md:p-8 rounded-3xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-xs space-y-6">
        <div class="flex items-center justify-between flex-wrap gap-4">
            <div>
                <h2 class="text-lg font-bold text-[#0B182F] dark:text-white flex items-center gap-2">
                    <i data-lucide="award" class="w-5 h-5 text-[#f43f5e]"></i> Kartu Tes & Hasil Seleksi Kelulusan
                </h2>
                <p class="text-xs text-slate-500 dark:text-slate-400">Cetak kartu ujian masuk dan pantau status kelulusan final yang disahkan Kepala Sekolah.</p>
            </div>
            <a href="{{ route('siswa.cetak.kartu_ujian') }}" target="_blank" class="px-5 py-2.5 rounded-full bg-[#0B182F] hover:bg-slate-800 text-white font-bold text-xs shadow-md transition-all flex items-center gap-2">
                <i data-lucide="printer" class="w-4 h-4 text-pink-400"></i> Cetak Kartu Peserta Ujian (PDF)
            </a>
        </div>

        <div class="p-5 rounded-2xl bg-[#fff5f7] border border-pink-100 flex flex-col md:flex-row items-center justify-between gap-4">
            <div class="space-y-1 text-center md:text-left">
                <span class="text-xs text-slate-500 uppercase font-bold">Status Kelulusan Final</span>
                <div class="text-xl font-black text-[#0B182F] flex items-center justify-center md:justify-start gap-2">
                    @if($pendaftar->status_kelulusan === 'Diterima')
                    <span class="text-emerald-700 flex items-center gap-1.5 font-black">
                        <i data-lucide="check-circle-2" class="w-5 h-5 text-emerald-600"></i> DITERIMA
                    </span>
                    <span class="text-xs font-semibold text-slate-600">di Jurusan {{ $pendaftar->terima ? $pendaftar->terima->jurusan->nama_jurusan : ($pendaftar->jurusan1 ? $pendaftar->jurusan1->nama_jurusan : '-') }}</span>
                    @elseif($pendaftar->status_kelulusan === 'Cadangan')
                    <span class="text-sky-700 flex items-center gap-1.5 font-black">
                        <i data-lucide="help-circle" class="w-5 h-5 text-sky-600"></i> CADANGAN / WAITING LIST
                    </span>
                    @elseif($pendaftar->status_kelulusan === 'Tidak Lolos')
                    <span class="text-rose-700 flex items-center gap-1.5 font-black">
                        <i data-lucide="x-circle" class="w-5 h-5 text-rose-600"></i> BELUM LOLOS
                    </span>
                    @else
                    <span class="text-amber-700 flex items-center gap-1.5 font-black">
                        <i data-lucide="clock" class="w-5 h-5 text-amber-600"></i> SEDANG DIPROSES
                    </span>
                    @endif
                </div>
            </div>

            <div class="flex items-center gap-3">
                @if($pendaftar->status_kelulusan === 'Diterima')
                <a href="{{ route('siswa.cetak.skl') }}" target="_blank" class="px-4 py-2.5 rounded-full bg-white text-[#f43f5e] border border-pink-200 font-bold text-xs hover:bg-pink-50 transition-colors flex items-center gap-2 shadow-xs">
                    <i data-lucide="file-check" class="w-4 h-4"></i> Unduh SKL Resmi (PDF)
                </a>
                @if($pendaftar->status_daftar_ulang !== 'Selesai')
                <form action="{{ route('siswa.daftar_ulang.konfirmasi') }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin mengonfirmasi pendaftaran ulang sebagai Siswa Baru SMK IB Khalifah Bangsa?');">
                    @csrf
                    <button type="submit" class="px-5 py-2.5 rounded-full bg-[#f43f5e] hover:bg-[#e11d48] text-white font-bold text-xs shadow-md transition-all flex items-center gap-2">
                        <i data-lucide="user-check" class="w-4 h-4"></i> Konfirmasi Daftar Ulang
                    </button>
                </form>
                @else
                <span class="px-4 py-2.5 rounded-full bg-emerald-100 text-emerald-800 font-bold text-xs flex items-center gap-1.5">
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
