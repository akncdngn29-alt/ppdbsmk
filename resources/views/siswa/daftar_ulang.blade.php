@extends('layouts.siswa')

@section('title', 'Panduan & Pembayaran Daftar Ulang')

@section('content')
<div class="space-y-6">

    <!-- Header -->
    <div class="flex items-center justify-between flex-wrap gap-4">
        <div>
            <a href="{{ route('siswa.dashboard') }}" class="text-xs font-semibold text-emerald-600 dark:text-emerald-400 hover:underline flex items-center gap-1 mb-1">
                <i data-lucide="arrow-left" class="w-3.5 h-3.5"></i> Kembali ke Dashboard
            </a>
            <h1 class="text-2xl font-extrabold text-slate-900 dark:text-white">Daftar Ulang Peserta Didik Baru</h1>
            <p class="text-xs text-slate-500 dark:text-slate-400">Tahapan final konfirmasi administrasi, pembayaran seragam/MPLS, dan penerimaan resmi siswa baru T.A 2026/2027.</p>
        </div>
        
        <span class="px-3.5 py-1.5 rounded-full text-xs font-bold 
            @if($pendaftar->status_daftar_ulang === 'Selesai') bg-emerald-100 text-emerald-800 dark:bg-emerald-950 dark:text-emerald-300
            @else bg-amber-100 text-amber-800 dark:bg-amber-950 dark:text-amber-300 @endif flex items-center gap-1.5">
            <i data-lucide="{{ $pendaftar->status_daftar_ulang === 'Selesai' ? 'check-check' : 'clock' }}" class="w-4 h-4"></i>
            Status: {{ $pendaftar->status_daftar_ulang === 'Selesai' ? 'RESMI TERDAFTAR ULANG' : 'BELUM KONFIRMASI' }}
        </span>
    </div>

    <!-- Flash Messages -->
    @if(session('success'))
    <div class="p-4 rounded-2xl bg-emerald-50 dark:bg-emerald-950/60 border border-emerald-200 dark:border-emerald-800 text-emerald-800 dark:text-emerald-200 text-xs font-semibold flex items-center justify-between">
        <div class="flex items-center gap-2">
            <i data-lucide="check-circle-2" class="w-4 h-4 text-emerald-600"></i>
            <span>{{ session('success') }}</span>
        </div>
        <button type="button" onclick="this.parentElement.remove()" class="text-emerald-600 hover:text-emerald-800">&times;</button>
    </div>
    @endif

    @if(session('error'))
    <div class="p-4 rounded-2xl bg-rose-50 dark:bg-rose-950/60 border border-rose-200 dark:border-rose-800 text-rose-800 dark:text-rose-200 text-xs font-semibold flex items-center justify-between">
        <div class="flex items-center gap-2">
            <i data-lucide="alert-circle" class="w-4 h-4 text-rose-600"></i>
            <span>{{ session('error') }}</span>
        </div>
        <button type="button" onclick="this.parentElement.remove()" class="text-rose-600 hover:text-rose-800">&times;</button>
    </div>
    @endif

    @if($pendaftar->status_kelulusan !== 'Diterima')
    <!-- Not Accepted Warning -->
    <div class="p-6 md:p-8 rounded-3xl bg-amber-50 dark:bg-amber-950/40 border border-amber-200 dark:border-amber-800 text-amber-900 dark:text-amber-200 space-y-3">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-2xl bg-amber-500 text-white flex items-center justify-center shrink-0">
                <i data-lucide="alert-triangle" class="w-5 h-5"></i>
            </div>
            <div>
                <h3 class="text-sm font-bold">Menu Daftar Ulang Belum Terbuka</h3>
                <p class="text-xs text-amber-700 dark:text-amber-300">
                    Tahapan daftar ulang hanya dapat diakses oleh calon siswa yang telah dinyatakan <strong>Diterima</strong> dalam Sidang Kelulusan oleh Kepala Sekolah.
                </p>
            </div>
        </div>
        <div class="pt-2">
            <a href="{{ route('siswa.dashboard') }}" class="px-4 py-2 rounded-xl bg-amber-600 hover:bg-amber-700 text-white text-xs font-bold inline-flex items-center gap-2">
                Cek Status Kelulusan di Dashboard
            </a>
        </div>
    </div>
    @else

    <!-- Acceptance Status Summary -->
    <div class="p-6 md:p-8 rounded-3xl bg-gradient-to-r from-emerald-800 to-teal-900 text-white shadow-xl space-y-4">
        <div class="flex items-center justify-between flex-wrap gap-2">
            <div class="flex items-center gap-2">
                <span class="px-2.5 py-1 rounded-full text-[10px] font-bold uppercase bg-white/20 text-emerald-200 tracking-wider">
                    Hasil Seleksi Akhir
                </span>
                <span class="text-xs text-emerald-200 font-mono">No. Pendaftaran: {{ $pendaftar->no_pendaftaran }}</span>
            </div>
            @if($pendaftar->terima)
            <span class="text-xs font-mono font-bold text-emerald-300 bg-black/20 px-3 py-1 rounded-full border border-white/10">
                SKL: {{ $pendaftar->terima->no_surat_kelulusan }}
            </span>
            @endif
        </div>

        <div>
            <h2 class="text-xl md:text-2xl font-black">Selamat, {{ $pendaftar->nama }}!</h2>
            <p class="text-xs md:text-sm text-emerald-100 mt-1">
                Anda dinyatakan <strong>DITERIMA</strong> pada Program Keahlian: 
                <span class="text-white underline font-bold">{{ $pendaftar->jurusan1 ? $pendaftar->jurusan1->nama_jurusan : '-' }}</span>.
            </p>
        </div>

        <div class="pt-2 flex flex-wrap gap-3">
            <a href="{{ route('siswa.cetak.skl') }}" target="_blank" class="px-4 py-2 rounded-xl bg-white text-emerald-900 font-bold text-xs hover:bg-emerald-50 transition-colors shadow-sm flex items-center gap-2">
                <i data-lucide="award" class="w-4 h-4 text-emerald-600"></i> Unduh Lembar SKL Resmi (PDF)
            </a>
        </div>
    </div>

    <!-- 3-Column Instruction Guide -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <!-- 1. Dokumen Fisik -->
        <div class="p-6 rounded-3xl bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 shadow-sm space-y-3">
            <div class="w-9 h-9 rounded-xl bg-emerald-50 dark:bg-emerald-950/60 text-emerald-600 dark:text-emerald-400 flex items-center justify-center font-bold text-sm">
                1
            </div>
            <h3 class="text-sm font-bold text-slate-900 dark:text-white">Penyerahan Berkas Fisik</h3>
            <p class="text-xs text-slate-500 dark:text-slate-400 leading-relaxed">
                Serahkan berkas fisik ke loket TU kampus:
            </p>
            <ul class="text-xs text-slate-600 dark:text-emerald-100 space-y-1 list-disc list-inside">
                <li>Surat Keterangan Lulus (SKL) Asli</li>
                <li>Fotokopi Kartu Keluarga (2 lbr)</li>
                <li>Pas Foto 3x4 berwarna (4 lbr)</li>
                <li>Kwitansi/Bukti Lunas Daftar Ulang</li>
            </ul>
        </div>

        <!-- 2. Pengukuran Seragam -->
        <div class="p-6 rounded-3xl bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 shadow-sm space-y-3">
            <div class="w-9 h-9 rounded-xl bg-teal-50 dark:bg-teal-950/60 text-teal-600 dark:text-teal-400 flex items-center justify-center font-bold text-sm">
                2
            </div>
            <h3 class="text-sm font-bold text-slate-900 dark:text-white">Seragam & Wearpack</h3>
            <p class="text-xs text-slate-500 dark:text-slate-400 leading-relaxed">
                Ukuran yang telah Anda pilih saat mendaftar:
            </p>
            <div class="p-3 rounded-2xl bg-slate-50 dark:bg-slate-900/60 border border-slate-200 dark:border-slate-700 text-xs space-y-1">
                <div class="flex justify-between">
                    <span class="text-slate-400">Seragam Harian:</span>
                    <strong class="text-slate-900 dark:text-white">{{ $pendaftar->ukuran_seragam ?: 'L' }}</strong>
                </div>
                <div class="flex justify-between">
                    <span class="text-slate-400">Wearpack Bengkel/Lab:</span>
                    <strong class="text-slate-900 dark:text-white">{{ $pendaftar->ukuran_wearpack ?: 'L' }}</strong>
                </div>
            </div>
            <span class="text-[11px] text-slate-400 block">Pengambilan paket seragam dijadwalkan saat masa orientasi.</span>
        </div>

        <!-- 3. Jadwal Masuk Sekolah -->
        <div class="p-6 rounded-3xl bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 shadow-sm space-y-3">
            <div class="w-9 h-9 rounded-xl bg-indigo-50 dark:bg-indigo-950/60 text-indigo-600 dark:text-indigo-400 flex items-center justify-center font-bold text-sm">
                3
            </div>
            <h3 class="text-sm font-bold text-slate-900 dark:text-white">Jadwal MPLS & Masuk</h3>
            <p class="text-xs text-slate-500 dark:text-slate-400 leading-relaxed">
                Masa Pengenalan Lingkungan Sekolah (MPLS) T.A 2026/2027:
            </p>
            <div class="p-3 rounded-2xl bg-indigo-50/50 dark:bg-indigo-950/30 border border-indigo-200/60 dark:border-indigo-800 text-xs text-indigo-900 dark:text-indigo-300 space-y-1">
                <div><strong>Pra-MPLS:</strong> 11 Juli 2026 (08.00 WIB)</div>
                <div><strong>Pelaksanaan MPLS:</strong> 13 - 15 Juli 2026</div>
            </div>
            <span class="text-[11px] text-slate-400 block">Pakaian: Seragam SMP asal rapi & sopan.</span>
        </div>
    </div>

    <!-- ======================================================== -->
    <!-- SECTION PEMBAYARAN DAFTAR ULANG (ONLINE & OFFLINE) -->
    <!-- ======================================================== -->
    @php
        $biayaDaftarUlang = (float) ($pengaturan->biaya_daftar_ulang ?? 1500000.00);
        $isDaftarUlangSelesai = ($pendaftar->status_daftar_ulang === 'Selesai');
        $trxDu = $pembayaranDaftarUlang ?? null;
        $isQrisActive = ($trxDu && $trxDu->tipe_bayar === 'midtrans' && $trxDu->status_transaksi === 'pending' && (!empty($trxDu->qr_url) || !empty($trxDu->qr_string)));
        $isTunaiActive = ($trxDu && $trxDu->tipe_bayar === 'tunai_tu' && $trxDu->status_transaksi === 'pending');
    @endphp

    @if($isDaftarUlangSelesai)
    <!-- Status Selesai / Lunas -->
    <div class="p-6 md:p-8 rounded-3xl bg-white dark:bg-slate-800 border-2 border-emerald-500 shadow-xl text-center space-y-4">
        <div class="w-16 h-16 rounded-2xl bg-emerald-100 text-emerald-600 dark:bg-emerald-950 dark:text-emerald-400 flex items-center justify-center mx-auto shadow-md">
            <i data-lucide="check-check" class="w-9 h-9"></i>
        </div>
        <div>
            <span class="px-3 py-1 rounded-full text-xs font-bold bg-emerald-100 text-emerald-800 dark:bg-emerald-950 dark:text-emerald-300 inline-block mb-2">
                DAFTAR ULANG SELESAI & LUNAS
            </span>
            <h3 class="text-xl font-extrabold text-slate-900 dark:text-white">Anda Resmi Menjadi Siswa Baru!</h3>
            <p class="text-xs text-slate-500 dark:text-slate-400 max-w-md mx-auto mt-1">
                Alhamdulillah, administrasi daftar ulang Anda telah terkonfirmasi. Silakan hadir sesuai jadwal masa orientasi (MPLS) dan simpan bukti pendaftaran Anda.
            </p>
        </div>
        @if($trxDu)
        <div class="inline-block p-4 rounded-2xl bg-slate-50 dark:bg-slate-900/60 border border-slate-200 dark:border-slate-700 text-xs text-left max-w-sm w-full space-y-1">
            <div class="flex justify-between text-slate-500">
                <span>Kode Transaksi:</span>
                <strong class="font-mono text-slate-800 dark:text-white">{{ $trxDu->kode_transaksi }}</strong>
            </div>
            <div class="flex justify-between text-slate-500">
                <span>Metode:</span>
                <strong class="text-slate-800 dark:text-white">{{ $trxDu->metode_pembayaran }}</strong>
            </div>
            <div class="flex justify-between text-slate-500">
                <span>Total Biaya:</span>
                <strong class="text-emerald-600 font-bold">Rp {{ number_format($trxDu->total_bayar, 0, ',', '.') }}</strong>
            </div>
        </div>
        @endif
    </div>

    @else

    <!-- Info Rincian Biaya Daftar Ulang -->
    <div class="p-6 rounded-3xl bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 shadow-sm space-y-4">
        <div class="flex items-center justify-between flex-wrap gap-3 pb-3 border-b border-slate-100 dark:border-slate-700">
            <div>
                <span class="text-[11px] font-bold uppercase tracking-wider text-emerald-600">Tagihan Administrasi</span>
                <h3 class="text-base font-extrabold text-slate-900 dark:text-white">Biaya Daftar Ulang Siswa Baru</h3>
            </div>
            <div class="text-right">
                <span class="text-xs text-slate-400 block">Total Tagihan:</span>
                <span class="text-xl font-black text-emerald-600 font-mono">Rp {{ number_format($biayaDaftarUlang, 0, ',', '.') }}</span>
            </div>
        </div>

        @if(!empty($pengaturan->keterangan_daftar_ulang))
        <div>
            <h4 class="text-xs font-bold text-slate-700 dark:text-emerald-100 mb-2">Rincian Paket & Fasilitas Siswa:</h4>
            <div class="p-3.5 rounded-2xl bg-slate-50 dark:bg-slate-900/60 border border-slate-200 dark:border-slate-700 text-xs text-slate-600 dark:text-emerald-100 leading-relaxed whitespace-pre-line font-sans">{{ $pengaturan->keterangan_daftar_ulang }}</div>
        </div>
        @endif
    </div>

    <!-- Active QRIS Midtrans Card (Jika Sedang Aktif) -->
    <div id="qris-card-container" class="{{ $isQrisActive ? '' : 'hidden' }} p-6 md:p-8 rounded-3xl bg-white dark:bg-slate-900 border-2 border-emerald-500 shadow-xl space-y-5">
        <div class="flex items-center justify-between flex-wrap gap-3 pb-4 border-b border-slate-100 dark:border-slate-800">
            <div class="flex items-center gap-3">
                <div class="w-12 h-12 rounded-2xl bg-emerald-100 text-emerald-600 dark:bg-emerald-950 dark:text-emerald-400 flex items-center justify-center shrink-0">
                    <i data-lucide="qr-code" class="w-6 h-6"></i>
                </div>
                <div>
                    <h3 class="text-base font-extrabold text-slate-900 dark:text-white">QRIS Resmi Midtrans (Daftar Ulang)</h3>
                    <p class="text-xs text-slate-500 dark:text-slate-400">
                        Order ID: <code id="qris-order-id" class="font-mono font-bold text-emerald-600">{{ $trxDu ? $trxDu->kode_transaksi : '-' }}</code>
                    </p>
                </div>
            </div>
            <span class="px-3.5 py-1.5 rounded-full text-xs font-bold bg-amber-100 text-amber-800 dark:bg-amber-950 dark:text-amber-300 flex items-center gap-1.5">
                <i data-lucide="clock" class="w-4 h-4"></i> Menunggu Pembayaran
            </span>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-12 gap-6 items-center">
            <!-- QR Image Box -->
            <div class="md:col-span-5 flex flex-col items-center justify-center p-4 bg-slate-50 dark:bg-slate-800/80 rounded-2xl border border-slate-200 dark:border-slate-700">
                <img id="qris-image" src="{{ $trxDu ? $trxDu->qr_url : '' }}" alt="QRIS Midtrans Daftar Ulang"
                     class="w-48 h-48 rounded-xl shadow border border-slate-200 dark:border-slate-700 bg-white p-2 {{ ($trxDu && $trxDu->qr_url) ? '' : 'hidden' }}">
                <span class="text-[11px] font-bold text-slate-600 dark:text-emerald-100 mt-3">Scan via BCA Mobile, GoPay, OVO, Dana, dll</span>
                <span class="text-[10px] text-slate-400">Total: Rp {{ number_format($biayaDaftarUlang, 0, ',', '.') }}</span>
            </div>

            <!-- Simulator & Details -->
            <div class="md:col-span-7 space-y-4">
                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-emerald-100 mb-1 flex items-center justify-between">
                        <span>String Kode QR (Sandbox Simulator):</span>
                        <button type="button" onclick="copyQrisString()" class="text-[10px] font-bold text-emerald-600 hover:underline">Salin Kode</button>
                    </label>
                    <textarea id="qris-string-area" readonly rows="3" class="w-full px-3 py-2 text-xs font-mono bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-xl text-slate-800 dark:text-white select-all">{{ $trxDu ? $trxDu->qr_string : '' }}</textarea>
                </div>

                <div class="flex items-center gap-2 flex-wrap">
                    <a id="qris-sim-link" href="https://simulator.sandbox.midtrans.com/qris/index" target="_blank"
                       class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-800 dark:text-white font-bold text-xs border border-slate-300 dark:border-slate-600 transition-colors">
                        <i data-lucide="external-link" class="w-3.5 h-3.5"></i> Buka Simulator Midtrans &nearr;
                    </a>

                    <button type="button" onclick="cekStatusDaftarUlang()" id="btn-cek-status-du"
                            class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl bg-[#fa2f79] hover:bg-[#e11d48] text-white font-bold text-xs shadow-md shadow-pink-500/25 transition-all">
                        <i data-lucide="refresh-cw" class="w-3.5 h-3.5"></i> Periksa Status Pembayaran
                    </button>
                </div>
                <div id="du-check-alert" class="hidden text-xs p-3 rounded-xl"></div>
            </div>
        </div>
    </div>

    <!-- Active Cash / Tunai Notice (Jika Memilih Bayar di TU) -->
    @if($isTunaiActive)
    <div class="p-6 rounded-3xl bg-blue-50 dark:bg-blue-950/40 border border-blue-200 dark:border-blue-800 space-y-3">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-2xl bg-blue-600 text-white flex items-center justify-center shrink-0 shadow-sm">
                <i data-lucide="banknote" class="w-5 h-5"></i>
            </div>
            <div>
                <h4 class="text-sm font-bold text-blue-900 dark:text-blue-200">Menunggu Verifikasi Pembayaran Tunai di Loket TU</h4>
                <p class="text-xs text-blue-700 dark:text-blue-300">
                    Kode Tagihan: <code class="font-mono font-bold">{{ $trxDu->kode_transaksi }}</code> | Nominal: <strong>Rp {{ number_format($biayaDaftarUlang, 0, ',', '.') }}</strong>
                </p>
            </div>
        </div>
        <p class="text-xs text-slate-600 dark:text-emerald-100 leading-relaxed">
            {{ $pengaturan->instruksi_daftar_ulang ?: 'Silakan datang langsung ke Loket TU Kampus SMK dengan membawa dokumen kelengkapan fisik dan lembar SKL untuk diverifikasi oleh petugas.' }}
        </p>
    </div>
    @endif

    <!-- 2 Pilihan Metode Pembayaran (Online vs Offline) -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 items-stretch">
        
        <!-- OPSI 1: PEMBAYARAN ONLINE (MIDTRANS QRIS) -->
        <div class="p-6 rounded-3xl bg-white dark:bg-slate-800 border-2 border-emerald-500/50 hover:border-emerald-500 shadow-sm hover:shadow-md transition-all space-y-4 flex flex-col justify-between h-full">
            <div class="space-y-3">
                <div class="w-11 h-11 rounded-2xl bg-emerald-100 text-emerald-600 dark:bg-emerald-950 dark:text-emerald-400 flex items-center justify-center">
                    <i data-lucide="smartphone" class="w-6 h-6"></i>
                </div>
                <div>
                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-700 dark:bg-emerald-950 dark:text-emerald-300 uppercase tracking-wider">
                        Opsi 1 (Instan)
                    </span>
                    <h3 class="text-base font-extrabold text-slate-900 dark:text-white mt-1">Bayar Online (Midtrans QRIS)</h3>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-1 leading-relaxed">
                        Bayar dari rumah menggunakan QRIS melalui aplikasi Mobile Banking (BCA, Mandiri, BRI, BNI) atau E-Wallet (GoPay, OVO, Dana, ShopeePay).
                    </p>
                </div>
                <div class="p-3 rounded-2xl bg-slate-50 dark:bg-slate-900/60 border border-slate-200 dark:border-slate-700 text-xs">
                    <span class="text-slate-400 block">Total Pembayaran:</span>
                    <span class="text-base font-black text-emerald-600 font-mono">Rp {{ number_format($biayaDaftarUlang, 0, ',', '.') }}</span>
                </div>
            </div>

            <button type="button" onclick="generateQrisDaftarUlang()" id="btn-generate-qris-du"
                    class="w-full py-3 px-4 rounded-2xl bg-[#fa2f79] hover:bg-[#e11d48] text-white font-extrabold text-xs shadow-lg shadow-pink-500/30 hover:scale-[1.02] transition-all flex items-center justify-center gap-2">
                <i data-lucide="qr-code" class="w-4 h-4"></i>
                <span id="btn-qris-text">{{ $isQrisActive ? 'Tampilkan Ulang QRIS' : 'Bayar Online via QRIS Midtrans' }}</span>
            </button>
        </div>

        <!-- OPSI 2: PEMBAYARAN TUNAI (LOKET TU SEKOLAH) -->
        <div class="p-6 rounded-3xl bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 hover:border-slate-300 dark:hover:border-slate-600 shadow-sm space-y-4 flex flex-col justify-between h-full">
            <div class="space-y-3">
                <div class="w-11 h-11 rounded-2xl bg-slate-100 text-slate-700 dark:bg-slate-700 dark:text-slate-200 flex items-center justify-center">
                    <i data-lucide="store" class="w-6 h-6"></i>
                </div>
                <div>
                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-slate-100 text-slate-700 dark:bg-slate-700 dark:text-emerald-100 uppercase tracking-wider">
                        Opsi 2 (Offline)
                    </span>
                    <h3 class="text-base font-extrabold text-slate-900 dark:text-white mt-1">Bayar Tunai di Loket TU</h3>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-1 leading-relaxed">
                        Bayar langsung menggunakan uang tunai ke loket Tata Usaha (TU) di kampus sekolah saat datang mengukur seragam dan menyerahkan berkas fisik.
                    </p>
                </div>
                <div class="p-3 rounded-2xl bg-slate-50 dark:bg-slate-900/60 border border-slate-200 dark:border-slate-700 text-xs">
                    <span class="text-slate-400 block">Total yang Disiapkan:</span>
                    <span class="text-base font-black text-slate-800 dark:text-white font-mono">Rp {{ number_format($biayaDaftarUlang, 0, ',', '.') }}</span>
                </div>
            </div>

            <form action="{{ route('siswa.daftar_ulang.tunai') }}" method="POST" onsubmit="return confirm('Pilih opsi bayar tunai di Loket TU sekolah? Anda dapat langsung datang ke loket TU untuk melakukan pembayaran.');">
                @csrf
                <button type="submit" class="w-full py-3 px-4 rounded-2xl bg-slate-800 hover:bg-slate-900 text-white dark:bg-slate-700 dark:hover:bg-slate-600 font-extrabold text-xs shadow-md transition-all flex items-center justify-center gap-2">
                    <i data-lucide="banknote" class="w-4 h-4"></i>
                    <span>Pilih Bayar Tunai di TU</span>
                </button>
            </form>
        </div>

    </div>

    <!-- Tombol Konfirmasi Langsung (Alternative Komitmen) -->
    <div class="p-6 rounded-3xl bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-center space-y-3">
        <p class="text-xs text-slate-500 dark:text-slate-400 max-w-md mx-auto">
            Atau, jika Anda telah menyelesaikan administrasi langsung dengan panitia, klik tombol di bawah untuk konfirmasi pendaftaran:
        </p>
        <form action="{{ route('siswa.daftar_ulang.konfirmasi') }}" method="POST" onsubmit="return confirm('Konfirmasi pendaftaran ulang sebagai Siswa Baru SMK IB Khalifah Bangsa?');">
            @csrf
            <button type="submit" class="px-6 py-2.5 rounded-xl border border-emerald-600 text-emerald-600 hover:bg-emerald-50 dark:hover:bg-emerald-950/40 text-xs font-bold transition-all inline-flex items-center gap-2">
                <i data-lucide="check" class="w-4 h-4"></i> Konfirmasi Kesediaan Masuk
            </button>
        </form>
    </div>

    @endif

    @endif

</div>
@endsection

@push('scripts')
<script>
    async function generateQrisDaftarUlang() {
        const btn = document.getElementById('btn-generate-qris-du');
        const btnText = document.getElementById('btn-qris-text');
        const cardContainer = document.getElementById('qris-card-container');
        const qrisImg = document.getElementById('qris-image');
        const qrisOrderCode = document.getElementById('qris-order-id');
        const qrisStringArea = document.getElementById('qris-string-area');

        btn.disabled = true;
        btnText.innerText = "Membuat Kode QRIS...";

        try {
            const res = await fetch("{{ route('siswa.daftar_ulang.qris') }}", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            });

            const data = await res.json();

            if (data.success) {
                if (data.is_paid) {
                    window.location.reload();
                    return;
                }

                cardContainer.classList.remove('hidden');
                qrisOrderCode.innerText = data.order_id;
                qrisStringArea.value = data.qr_string || '';

                if (data.qr_code_url) {
                    qrisImg.src = data.qr_code_url;
                    qrisImg.classList.remove('hidden');
                }

                cardContainer.scrollIntoView({ behavior: 'smooth' });
            } else {
                alert(data.message || 'Gagal membuat QRIS.');
            }
        } catch (err) {
            alert('Terjadi kesalahan jaringan.');
        } finally {
            btn.disabled = false;
            btnText.innerText = "Tampilkan Ulang QRIS";
            lucide.createIcons();
        }
    }

    async function cekStatusDaftarUlang() {
        const btn = document.getElementById('btn-cek-status-du');
        const alertBox = document.getElementById('du-check-alert');

        btn.disabled = true;
        btn.innerHTML = `<i data-lucide="refresh-cw" class="w-3.5 h-3.5 animate-spin"></i> Memeriksa...`;
        alertBox.className = "hidden";

        try {
            const res = await fetch("{{ route('siswa.daftar_ulang.cek-status') }}", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            });

            const data = await res.json();
            alertBox.classList.remove('hidden');

            if (data.is_paid || (data.status === 'settlement')) {
                alertBox.className = "text-xs p-3 rounded-xl bg-emerald-50 dark:bg-emerald-950 text-emerald-800 dark:text-emerald-200 border border-emerald-300";
                alertBox.innerText = "Alhamdulillah! Pembayaran daftar ulang LUNAS. Halaman akan disegarkan...";
                setTimeout(() => window.location.reload(), 1500);
            } else {
                alertBox.className = "text-xs p-3 rounded-xl bg-amber-50 dark:bg-amber-950 text-amber-800 dark:text-amber-200 border border-amber-300";
                alertBox.innerText = data.message || "Status transaksi: " + data.status;
            }
        } catch (err) {
            alertBox.classList.remove('hidden');
            alertBox.className = "text-xs p-3 rounded-xl bg-rose-50 text-rose-800 border border-rose-300";
            alertBox.innerText = "Gagal memeriksa status.";
        } finally {
            btn.disabled = false;
            btn.innerHTML = `<i data-lucide="refresh-cw" class="w-3.5 h-3.5"></i> Periksa Status Pembayaran`;
            lucide.createIcons();
        }
    }

    function copyQrisString() {
        const area = document.getElementById('qris-string-area');
        area.select();
        navigator.clipboard.writeText(area.value);
        alert('String kode QRIS berhasil disalin untuk simulator Midtrans.');
    }
</script>
@endpush
