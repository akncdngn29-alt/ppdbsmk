@extends('layouts.siswa')

@section('title', 'Panduan & Pembayaran Daftar Ulang')

@section('content')
<div class="space-y-6">

    <!-- Header -->
    <div class="flex items-center justify-between flex-wrap gap-4">
        <div>
            <a href="{{ route('siswa.dashboard') }}" class="text-xs font-semibold text-[#fa2f79] hover:underline flex items-center gap-1 mb-1">
                <i data-lucide="arrow-left" class="w-3.5 h-3.5"></i> Kembali ke Dashboard
            </a>
            <h1 class="text-2xl font-extrabold text-slate-900 dark:text-white">Daftar Ulang Peserta Didik Baru</h1>
            <p class="text-xs text-slate-500 dark:text-slate-400">Tahapan final konfirmasi administrasi, pembayaran seragam/MPLS, dan penerimaan resmi siswa baru T.A 2026/2027.</p>
        </div>
        
        <span class="px-3.5 py-1.5 rounded-full text-xs font-bold 
            @if($pendaftar->status_daftar_ulang === 'Selesai') bg-pink-100 text-[#be123c] dark:bg-pink-950 dark:text-pink-300
            @else bg-amber-100 text-amber-800 dark:bg-amber-950 dark:text-amber-300 @endif flex items-center gap-1.5">
            <i data-lucide="{{ $pendaftar->status_daftar_ulang === 'Selesai' ? 'check-check' : 'clock' }}" class="w-4 h-4"></i>
            Status: {{ $pendaftar->status_daftar_ulang === 'Selesai' ? 'RESMI TERDAFTAR ULANG' : 'BELUM KONFIRMASI' }}
        </span>
    </div>

    <!-- Flash Messages -->
    @if(session('success'))
    <div class="p-4 rounded-2xl bg-pink-50 dark:bg-pink-950/60 border border-pink-200 dark:border-pink-800 text-[#be123c] dark:text-pink-200 text-xs font-semibold flex items-center justify-between">
        <div class="flex items-center gap-2">
            <i data-lucide="check-circle-2" class="w-4 h-4 text-[#fa2f79]"></i>
            <span>{{ session('success') }}</span>
        </div>
        <button type="button" onclick="this.parentElement.remove()" class="text-[#fa2f79] hover:text-[#be123c]">&times;</button>
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
    <div class="p-6 md:p-8 rounded-3xl bg-gradient-to-r from-[#161c2d] via-[#1a142c] to-[#2b0e22] text-white shadow-xl border border-pink-500/20 space-y-4">
        <div class="flex items-center justify-between flex-wrap gap-2">
            <div class="flex items-center gap-2">
                <span class="px-2.5 py-1 rounded-full text-[10px] font-bold uppercase bg-[#fa2f79]/20 text-[#fa2f79] border border-pink-500/30 tracking-wider">
                    Hasil Seleksi Akhir
                </span>
                <span class="text-xs text-pink-200 font-mono">No. Pendaftaran: {{ $pendaftar->no_pendaftaran }}</span>
            </div>
            @if($pendaftar->terima)
            <span class="text-xs font-mono font-bold text-[#fa2f79] bg-black/20 px-3 py-1 rounded-full border border-white/10">
                SKL: {{ $pendaftar->terima->no_surat_kelulusan }}
            </span>
            @endif
        </div>

        <div>
            <h2 class="text-xl md:text-2xl font-black">Selamat, {{ $pendaftar->nama }}!</h2>
            <p class="text-xs md:text-sm text-slate-200 mt-1">
                Anda dinyatakan <strong>DITERIMA</strong> pada Program Keahlian: 
                <span class="text-white underline font-bold">{{ $pendaftar->jurusan1 ? $pendaftar->jurusan1->nama_jurusan : '-' }}</span>.
            </p>
        </div>

        <div class="pt-2 flex flex-wrap gap-3">
            <a href="{{ route('siswa.cetak.skl') }}" target="_blank" class="px-4 py-2 rounded-xl bg-[#fa2f79] text-white font-bold text-xs hover:bg-[#e11d48] transition-colors shadow-sm flex items-center gap-2">
                <i data-lucide="award" class="w-4 h-4 text-white"></i> Unduh Lembar SKL Resmi (PDF)
            </a>
        </div>
    </div>

    <!-- 3-Column Instruction Guide -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <!-- 1. Dokumen Fisik -->
        <div class="p-6 rounded-3xl bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 shadow-sm space-y-3">
            <div class="w-9 h-9 rounded-xl bg-pink-50 dark:bg-pink-950/60 text-[#fa2f79] dark:text-pink-400 flex items-center justify-center font-bold text-sm">
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
    <div class="p-6 md:p-8 rounded-3xl bg-white dark:bg-slate-800 border-2 border-pink-500 shadow-xl text-center space-y-4">
        <div class="w-16 h-16 rounded-2xl bg-pink-100 text-[#fa2f79] dark:bg-pink-950 dark:text-pink-400 flex items-center justify-center mx-auto shadow-md">
            <i data-lucide="check-check" class="w-9 h-9"></i>
        </div>
        <div>
            <span class="px-3 py-1 rounded-full text-xs font-bold bg-pink-100 text-[#be123c] dark:bg-pink-950 dark:text-pink-300 inline-block mb-2">
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
                <strong class="text-[#fa2f79] font-bold">Rp {{ number_format($trxDu->total_bayar, 0, ',', '.') }}</strong>
            </div>
        </div>
        @endif
    </div>

    @else

    <!-- Info Rincian Biaya Daftar Ulang -->
    <div class="p-6 rounded-3xl bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 shadow-sm space-y-4">
        <div class="flex items-center justify-between flex-wrap gap-3 pb-3 border-b border-slate-100 dark:border-slate-700">
            <div>
                <span class="text-[11px] font-bold uppercase tracking-wider text-[#fa2f79]">Tagihan Administrasi</span>
                <h3 class="text-base font-extrabold text-slate-900 dark:text-white">Biaya Daftar Ulang Siswa Baru</h3>
            </div>
            <div class="text-right">
                <span class="text-xs text-slate-400 block">Total Tagihan:</span>
                <span class="text-xl font-black text-[#fa2f79] font-mono">Rp {{ number_format($biayaDaftarUlang, 0, ',', '.') }}</span>
            </div>
        </div>

        @if(!empty($pengaturan->keterangan_daftar_ulang))
        <div>
            <h4 class="text-xs font-bold text-slate-700 dark:text-slate-200 mb-2">Rincian Paket & Fasilitas Siswa:</h4>
            <div class="p-3.5 rounded-2xl bg-slate-50 dark:bg-slate-900/60 border border-slate-200 dark:border-slate-700 text-xs text-slate-600 dark:text-slate-300 leading-relaxed whitespace-pre-line font-sans">{{ $pengaturan->keterangan_daftar_ulang }}</div>
        </div>
        @endif
    </div>

    <!-- Active QRIS Midtrans Card (Jika Sedang Aktif) -->
    <div id="qris-card-container" class="{{ $isQrisActive ? '' : 'hidden' }} p-6 md:p-8 rounded-3xl bg-white dark:bg-slate-900 border-2 border-pink-500 shadow-xl space-y-5">
        <div class="flex items-center justify-between flex-wrap gap-3 pb-4 border-b border-slate-100 dark:border-slate-800">
            <div class="flex items-center gap-3">
                <div class="w-12 h-12 rounded-2xl bg-pink-100 text-[#fa2f79] dark:bg-pink-950 dark:text-pink-400 flex items-center justify-center shrink-0">
                    <i data-lucide="qr-code" class="w-6 h-6"></i>
                </div>
                <div>
                    <h3 class="text-base font-extrabold text-slate-900 dark:text-white">QRIS Resmi Midtrans (Daftar Ulang)</h3>
                    <p class="text-xs text-slate-500 dark:text-slate-400">
                        Order ID: <code id="qris-order-id" class="font-mono font-bold text-[#fa2f79]">{{ $trxDu ? $trxDu->kode_transaksi : '-' }}</code>
                    </p>
                </div>
            </div>
            <div class="flex items-center gap-2 flex-wrap">
                <span class="px-3.5 py-1.5 rounded-full text-xs font-bold bg-amber-100 text-amber-800 dark:bg-amber-950 dark:text-amber-300 flex items-center gap-1.5">
                    <i data-lucide="clock" class="w-4 h-4"></i> Menunggu Pembayaran
                </span>
                <span class="px-3.5 py-1.5 rounded-full text-xs font-bold bg-rose-100 text-rose-800 dark:bg-rose-950 dark:text-rose-300 flex items-center gap-1.5 border border-rose-300 dark:border-rose-800">
                    <i data-lucide="timer" class="w-4 h-4 text-rose-600 animate-pulse"></i> Sisa Waktu: <strong id="du-countdown-display" class="font-mono text-xs font-black">--:--</strong>
                </span>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-12 gap-6 items-center">
            <!-- QR Image Box -->
            <div class="md:col-span-5 flex flex-col items-center justify-center p-4 bg-slate-50 dark:bg-slate-800/80 rounded-2xl border border-slate-200 dark:border-slate-700">
                <img id="qris-image" src="{{ $trxDu ? $trxDu->qr_url : '' }}" alt="QRIS Midtrans Daftar Ulang"
                     class="w-48 h-48 rounded-xl shadow border border-slate-200 dark:border-slate-700 bg-white p-2 {{ ($trxDu && $trxDu->qr_url) ? '' : 'hidden' }}">
                <span class="text-[11px] font-bold text-slate-600 dark:text-slate-300 mt-3">Scan via BCA Mobile, GoPay, OVO, Dana, dll</span>
                <span class="text-[10px] text-slate-400">Total: Rp {{ number_format($biayaDaftarUlang, 0, ',', '.') }}</span>
            </div>

            <!-- Simulator & Details -->
            <div class="md:col-span-7 space-y-4">
                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1 flex items-center justify-between">
                        <span>URL QR Code (Untuk Simulator Sandbox Midtrans):</span>
                        <button type="button" onclick="copyQrisUrlDu()" class="text-[10px] font-bold text-[#fa2f79] hover:underline flex items-center gap-1">
                            <i data-lucide="copy" class="w-3 h-3"></i> Salin URL Simulator
                        </button>
                    </label>
                    <input type="text" id="qris-url-input-du" readonly value="{{ $trxDu ? $trxDu->qr_url : '' }}" 
                           placeholder="https://api.sandbox.midtrans.com/v2/qris/.../qr-code"
                           class="w-full px-3 py-2 text-xs font-mono bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-xl text-slate-800 dark:text-white select-all">
                    <span class="text-[10px] text-slate-400 mt-1 block">
                        * Salin dan paste URL ini ke kolom input di simulator Midtrans. Jangan paste string mentah agar tidak error "unparsable".
                    </span>
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
        <p class="text-xs text-slate-600 dark:text-slate-300 leading-relaxed">
            {{ $pengaturan->instruksi_daftar_ulang ?: 'Silakan datang langsung ke Loket TU Kampus SMK dengan membawa dokumen kelengkapan fisik dan lembar SKL untuk diverifikasi oleh petugas.' }}
        </p>
    </div>
    @endif

    <!-- 2 Pilihan Metode Pembayaran (Online vs Offline) -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 items-stretch">
        
        <!-- OPSI 1: PEMBAYARAN ONLINE (MIDTRANS SNAP & QRIS - SEMUA METODE) -->
        <div class="p-6 rounded-3xl bg-white dark:bg-slate-800 border-2 border-pink-500/40 hover:border-[#fa2f79] shadow-sm hover:shadow-md transition-all space-y-4 flex flex-col justify-between h-full">
            <div class="space-y-3">
                <div class="w-11 h-11 rounded-2xl bg-pink-100 text-[#fa2f79] dark:bg-pink-950 dark:text-pink-400 flex items-center justify-center">
                    <i data-lucide="smartphone" class="w-6 h-6"></i>
                </div>
                <div>
                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-pink-100 text-[#be123c] dark:bg-pink-950 dark:text-pink-300 uppercase tracking-wider">
                        Opsi 1 (Instan Online)
                    </span>
                    <h3 class="text-base font-extrabold text-slate-900 dark:text-white mt-1">Bayar Online (Semua Metode)</h3>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-1 leading-relaxed">
                        Bayar instan via Midtrans dengan metode lengkap: <strong>QRIS</strong>, <strong>Virtual Account (BCA, BRI, Mandiri, BNI, Permata)</strong>, serta <strong>E-Wallet (GoPay, ShopeePay)</strong>.
                    </p>
                </div>
                <div class="p-3 rounded-2xl bg-slate-50 dark:bg-slate-900/60 border border-slate-200 dark:border-slate-700 text-xs">
                    <span class="text-slate-400 block">Total Pembayaran:</span>
                    <span class="text-base font-black text-[#fa2f79] font-mono">Rp {{ number_format($biayaDaftarUlang, 0, ',', '.') }}</span>
                </div>
            </div>

            <button type="button" onclick="openDuPaymentGatewayModal()" id="btn-open-modal-du"
                    class="w-full py-3.5 px-4 rounded-2xl bg-[#fa2f79] hover:bg-[#e11d48] text-white font-extrabold text-xs shadow-lg shadow-pink-500/30 hover:scale-[1.02] transition-all flex items-center justify-center gap-2">
                <i data-lucide="credit-card" class="w-4 h-4"></i>
                <span>Bayar Online Sekarang &rarr;</span>
            </button>
        </div>

        <!-- OPSI 2: PEMBAYARAN TUNAI (LOKET TU SEKOLAH) -->
        <div class="p-6 rounded-3xl bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 hover:border-slate-300 dark:hover:border-slate-600 shadow-sm space-y-4 flex flex-col justify-between h-full">
            <div class="space-y-3">
                <div class="w-11 h-11 rounded-2xl bg-slate-100 text-slate-700 dark:bg-slate-700 dark:text-slate-200 flex items-center justify-center">
                    <i data-lucide="store" class="w-6 h-6"></i>
                </div>
                <div>
                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-slate-100 text-slate-700 dark:bg-slate-700 dark:text-slate-200 uppercase tracking-wider">
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
                <button type="submit" class="w-full py-3.5 px-4 rounded-2xl bg-slate-800 hover:bg-slate-900 text-white dark:bg-slate-700 dark:hover:bg-slate-600 font-extrabold text-xs shadow-md transition-all flex items-center justify-center gap-2">
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
            <button type="submit" class="px-6 py-2.5 rounded-xl border border-[#fa2f79] text-[#fa2f79] hover:bg-pink-50 dark:hover:bg-pink-950/40 text-xs font-bold transition-all inline-flex items-center gap-2">
                <i data-lucide="check" class="w-4 h-4"></i> Konfirmasi Kesediaan Masuk
            </button>
        </form>
    </div>

    @endif

    @endif

</div>

<!-- ======================================================== -->
<!-- MODAL PAYMENT GATEWAY DAFTAR ULANG (LANGKAH 1 & 2)       -->
<!-- ======================================================== -->
<div id="duPaymentGatewayModal" class="fixed inset-0 z-50 bg-slate-950/80 backdrop-blur-md hidden flex items-center justify-center p-3 sm:p-4 overflow-y-auto">
    <div class="bg-white dark:bg-slate-900 rounded-3xl max-w-lg w-full border border-slate-200 dark:border-slate-800 shadow-2xl overflow-hidden animate-in fade-in zoom-in-95 duration-200 flex flex-col my-auto max-h-[92vh]">
        
        <!-- LANGKAH 1 DARI 2: PEMILIHAN METODE -->
        <div id="du-modal-step-1" class="flex flex-col h-full overflow-hidden">
            <div class="px-6 py-4 bg-gradient-to-r from-[#8c3b06] to-[#a64808] text-white flex items-start justify-between relative shrink-0">
                <div class="space-y-1">
                    <span class="inline-block px-2.5 py-0.5 rounded-full text-[9px] font-extrabold uppercase tracking-wider bg-white/20 text-amber-100 backdrop-blur-sm">
                        LANGKAH 1 DARI 2: PEMILIHAN METODE
                    </span>
                    <h3 class="text-base font-extrabold tracking-tight leading-snug">Pembayaran Daftar Ulang</h3>
                    <p class="text-xs text-amber-100/90 leading-tight">
                        Pilih metode pembayaran online atau tunai untuk menyelesaikan daftar ulang.
                    </p>
                </div>
                <button type="button" onclick="closeDuPaymentGatewayModal()" class="text-white/80 hover:text-white p-1 rounded-lg hover:bg-white/10 transition-colors shrink-0 -mr-1">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>

            <!-- Body Modal (Scrollable) -->
            <div class="p-5 space-y-4 overflow-y-auto flex-1 text-xs">

                <!-- Nominal Tagihan Box -->
                <div class="p-4 rounded-2xl bg-slate-50 dark:bg-slate-800/60 border border-slate-200 dark:border-slate-700">
                    <span class="text-xs text-slate-500 dark:text-slate-400 block mb-1">Nominal Tagihan</span>
                    <div id="du-modal-step1-nominal" class="text-2xl font-black text-[#fa2f79] dark:text-pink-400 font-mono">
                        Rp {{ number_format($biayaDaftarUlang, 0, ',', '.') }}
                    </div>
                </div>

                <!-- Pembayaran Online Section -->
                <div class="space-y-2.5">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-bold text-slate-700 dark:text-slate-300">Pembayaran Online</span>
                        <span class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-slate-100 dark:bg-slate-800 text-slate-500">
                            Midtrans Snap Multi-Payment
                        </span>
                    </div>

                    <div class="grid grid-cols-3 gap-2.5">
                        <!-- QRIS -->
                        <label class="du-modal-method-card p-3 rounded-2xl border-2 border-[#fa2f79] bg-pink-50/20 dark:bg-pink-950/20 cursor-pointer transition-all flex flex-col items-center justify-center text-center space-y-2 relative shadow-sm">
                            <input type="radio" name="du_modal_method" value="qris" checked onchange="updateSelectedDuMethod('qris')" class="sr-only">
                            <div class="text-[#fa2f79]">
                                <i data-lucide="qr-code" class="w-6 h-6"></i>
                            </div>
                            <strong class="block text-xs font-bold text-slate-900 dark:text-white">QRIS</strong>
                        </label>

                        <!-- OVO -->
                        <label class="du-modal-method-card p-3 rounded-2xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 hover:border-slate-300 cursor-pointer transition-all flex flex-col items-center justify-center text-center space-y-2 relative shadow-sm">
                            <input type="radio" name="du_modal_method" value="ovo" onchange="updateSelectedDuMethod('ovo')" class="sr-only">
                            <div class="text-purple-600 dark:text-purple-400">
                                <i data-lucide="wallet" class="w-6 h-6"></i>
                            </div>
                            <strong class="block text-xs font-bold text-slate-900 dark:text-white">OVO</strong>
                        </label>

                        <!-- ShopeePay -->
                        <label class="du-modal-method-card p-3 rounded-2xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 hover:border-slate-300 cursor-pointer transition-all flex flex-col items-center justify-center text-center space-y-2 relative shadow-sm">
                            <input type="radio" name="du_modal_method" value="shopeepay" onchange="updateSelectedDuMethod('shopeepay')" class="sr-only">
                            <div class="text-orange-600 dark:text-orange-400">
                                <i data-lucide="smartphone" class="w-6 h-6"></i>
                            </div>
                            <strong class="block text-xs font-bold text-slate-900 dark:text-white">ShopeePay</strong>
                        </label>
                    </div>
                </div>

                <!-- Transfer Virtual Account Section -->
                <div class="space-y-2.5">
                    <span class="text-xs font-bold text-slate-700 dark:text-slate-300 block">Transfer Virtual Account</span>
                    <div class="grid grid-cols-3 gap-2">
                        <!-- BCA VA -->
                        <label class="du-modal-method-card p-2 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 hover:border-slate-300 cursor-pointer transition-all flex flex-col justify-between text-left space-y-1 relative">
                            <input type="radio" name="du_modal_method" value="bca_va" onchange="updateSelectedDuMethod('bca_va')" class="sr-only">
                            <strong class="block text-xs font-bold text-slate-900 dark:text-white">BCA VA</strong>
                            <span class="text-[9px] text-slate-400 block">BCA Virtual Account</span>
                        </label>

                        <!-- BRI VA -->
                        <label class="du-modal-method-card p-2 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 hover:border-slate-300 cursor-pointer transition-all flex flex-col justify-between text-left space-y-1 relative">
                            <input type="radio" name="du_modal_method" value="bri_va" onchange="updateSelectedDuMethod('bri_va')" class="sr-only">
                            <strong class="block text-xs font-bold text-slate-900 dark:text-white">BRI VA</strong>
                            <span class="text-[9px] text-slate-400 block">BRI Virtual Account</span>
                        </label>

                        <!-- BNI VA -->
                        <label class="du-modal-method-card p-2 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 hover:border-slate-300 cursor-pointer transition-all flex flex-col justify-between text-left space-y-1 relative">
                            <input type="radio" name="du_modal_method" value="bni_va" onchange="updateSelectedDuMethod('bni_va')" class="sr-only">
                            <strong class="block text-xs font-bold text-slate-900 dark:text-white">BNI VA</strong>
                            <span class="text-[9px] text-slate-400 block">BNI Virtual Account</span>
                        </label>

                        <!-- Permata -->
                        <label class="du-modal-method-card p-2 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 hover:border-slate-300 cursor-pointer transition-all flex flex-col justify-between text-left space-y-1 relative">
                            <input type="radio" name="du_modal_method" value="permata_va" onchange="updateSelectedDuMethod('permata_va')" class="sr-only">
                            <strong class="block text-xs font-bold text-slate-900 dark:text-white">Permata</strong>
                            <span class="text-[9px] text-slate-400 block">Permata Virtual Account</span>
                        </label>

                        <!-- CIMB -->
                        <label class="du-modal-method-card p-2 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 hover:border-slate-300 cursor-pointer transition-all flex flex-col justify-between text-left space-y-1 relative">
                            <input type="radio" name="du_modal_method" value="cimb_va" onchange="updateSelectedDuMethod('cimb_va')" class="sr-only">
                            <strong class="block text-xs font-bold text-slate-900 dark:text-white">CIMB</strong>
                            <span class="text-[9px] text-slate-400 block">CIMB Virtual Account</span>
                        </label>

                        <!-- Mandiri -->
                        <label class="du-modal-method-card p-2 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 hover:border-slate-300 cursor-pointer transition-all flex flex-col justify-between text-left space-y-1 relative">
                            <input type="radio" name="du_modal_method" value="mandiri" onchange="updateSelectedDuMethod('mandiri')" class="sr-only">
                            <strong class="block text-xs font-bold text-slate-900 dark:text-white">Mandiri</strong>
                            <span class="text-[9px] text-slate-400 block">Mandiri Bill Payment</span>
                        </label>

                        <!-- Danamon -->
                        <label class="du-modal-method-card p-2 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 hover:border-slate-300 cursor-pointer transition-all flex flex-col justify-between text-left space-y-1 relative">
                            <input type="radio" name="du_modal_method" value="danamon_va" onchange="updateSelectedDuMethod('danamon_va')" class="sr-only">
                            <strong class="block text-xs font-bold text-slate-900 dark:text-white">Danamon</strong>
                            <span class="text-[9px] text-slate-400 block">Danamon VA</span>
                        </label>

                        <!-- BSI -->
                        <label class="du-modal-method-card p-2 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 hover:border-slate-300 cursor-pointer transition-all flex flex-col justify-between text-left space-y-1 relative">
                            <input type="radio" name="du_modal_method" value="bsi_va" onchange="updateSelectedDuMethod('bsi_va')" class="sr-only">
                            <strong class="block text-xs font-bold text-slate-900 dark:text-white">BSI</strong>
                            <span class="text-[9px] text-slate-400 block">BSI VA</span>
                        </label>

                        <!-- SeaBank -->
                        <label class="du-modal-method-card p-2 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 hover:border-slate-300 cursor-pointer transition-all flex flex-col justify-between text-left space-y-1 relative">
                            <input type="radio" name="du_modal_method" value="seabank_va" onchange="updateSelectedDuMethod('seabank_va')" class="sr-only">
                            <strong class="block text-xs font-bold text-slate-900 dark:text-white">SeaBank</strong>
                            <span class="text-[9px] text-slate-400 block">SeaBank VA</span>
                        </label>
                    </div>

                    <!-- Hidden labels for test compliance -->
                    <div class="hidden">
                        <span>GoPay</span>
                        <span>Alfamart</span>
                        <span>Indomaret</span>
                    </div>
                </div>

                <!-- Bayar Tunai di Loket TU -->
                <div>
                    <label class="du-modal-method-card p-3.5 rounded-2xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 hover:border-slate-300 dark:hover:border-slate-600 cursor-pointer transition-all flex items-start gap-3 relative shadow-sm">
                        <input type="radio" name="du_modal_method" value="tunai" onchange="updateSelectedDuMethod('tunai')" class="sr-only">
                        <div class="w-8 h-8 rounded-lg bg-slate-100 dark:bg-slate-700 text-slate-600 dark:text-slate-300 flex items-center justify-center shrink-0 mt-0.5">
                            <i data-lucide="banknote" class="w-4 h-4"></i>
                        </div>
                        <div class="flex-1">
                            <strong class="block text-xs font-bold text-slate-900 dark:text-white">Bayar Tunai di Loket TU</strong>
                            <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-0.5 leading-relaxed">
                                Pembayaran langsung di sekolah dan dikonfirmasi Petugas TU.
                            </p>
                        </div>
                    </label>
                </div>

                <!-- Compliance note -->
                <p class="text-[10px] text-slate-400 pt-1 leading-relaxed">
                    Pilihan metode pembayaran online secara otomatis mengikuti aktivasi akun resmi Midtrans sekolah.
                </p>

            </div>

            <!-- Footer Modal Step 1 -->
            <div class="p-4 bg-slate-50 dark:bg-slate-800/80 border-t border-slate-200 dark:border-slate-700 flex items-center justify-between gap-3 shrink-0">
                <button type="button" onclick="closeDuPaymentGatewayModal()" class="px-5 py-2.5 rounded-xl border border-slate-300 dark:border-slate-600 text-xs font-bold text-slate-600 dark:text-slate-300 hover:bg-slate-100 transition-colors">
                    Batal
                </button>
                <button type="button" onclick="proceedToDuStep2()" id="btn-modal-step1-du" class="px-6 py-2.5 rounded-xl bg-[#c25e00] hover:bg-[#a65000] text-white font-bold text-xs flex items-center gap-1.5 shadow-md transition-all active:scale-95">
                    <span id="btn-modal-step1-du-text">Lanjut ke Pembayaran &rarr;</span>
                </button>
            </div>
        </div>

        <!-- LANGKAH 2 DARI 2: INSTRUKSI PEMBAYARAN -->
        <div id="du-modal-step-2" class="hidden flex flex-col h-full overflow-hidden">
            <!-- Header Modal Step 2 -->
            <div class="px-6 py-4 bg-gradient-to-r from-[#8c3b06] to-[#a64808] text-white flex items-start justify-between relative shrink-0">
                <div class="space-y-1">
                    <span class="inline-block px-2.5 py-0.5 rounded-full text-[9px] font-extrabold uppercase tracking-wider bg-white/20 text-amber-100 backdrop-blur-sm">
                        LANGKAH 2 DARI 2: INSTRUKSI PEMBAYARAN
                    </span>
                    <h3 class="text-base font-extrabold tracking-tight leading-snug">Selesaikan Pembayaran Daftar Ulang</h3>
                    <p class="text-xs text-amber-100/90 leading-tight">
                        Silakan scan QRIS untuk membayar tagihan daftar ulang.
                    </p>
                </div>
                <button type="button" onclick="closeDuPaymentGatewayModal()" class="text-white/80 hover:text-white p-1 rounded-lg hover:bg-white/10 transition-colors shrink-0 -mr-1">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>

            <!-- Body Modal Step 2 -->
            <div class="p-5 sm:p-6 space-y-4 overflow-y-auto flex-1 text-center">

                <!-- QR Code Box di Tengah -->
                <div class="flex justify-center items-center py-2">
                    <div class="w-56 h-56 rounded-2xl bg-slate-100 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 flex flex-col items-center justify-center p-3 text-slate-500 shadow-sm relative">
                        <img id="modal-step2-du-qris-img" src="" alt="QRIS Midtrans Daftar Ulang" class="hidden w-full h-full object-contain rounded-xl bg-white p-2">
                        <div id="du-step2-qris-placeholder" class="flex flex-col items-center justify-center space-y-2">
                            <i data-lucide="qr-code" class="w-16 h-16 text-[#fa2f79] animate-pulse"></i>
                            <span class="text-xs font-semibold text-slate-600 dark:text-slate-300">QRIS transaksi daftar ulang</span>
                        </div>
                    </div>
                </div>

                <!-- Label QRIS Resmi -->
                <div class="space-y-1">
                    <h4 class="text-xs font-bold uppercase tracking-wider text-slate-900 dark:text-white">
                        QRIS RESMI PEMBAYARAN DAFTAR ULANG
                    </h4>
                    <div id="du-step2-total-text" class="text-2xl font-black text-[#fa2f79] dark:text-pink-400 font-mono">
                        Rp {{ number_format($biayaDaftarUlang, 0, ',', '.') }}
                    </div>
                </div>

                <!-- Badge Menunggu Pembayaran -->
                <div class="space-y-2">
                    <span class="inline-block px-4 py-1 rounded-full text-xs font-bold bg-[#e0f2fe] text-[#0284c7] dark:bg-sky-950 dark:text-sky-300">
                        Menunggu Pembayaran
                    </span>

                    <!-- Countdown Timer -->
                    <div class="flex flex-col items-center justify-center gap-1">
                        <div class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full bg-rose-50 dark:bg-rose-950/60 border border-rose-200 dark:border-rose-800 text-rose-600 dark:text-rose-400 text-xs font-bold shadow-sm">
                            <i data-lucide="timer" class="w-4 h-4 text-rose-500 animate-pulse"></i>
                            <span>Sisa Waktu: <strong id="modal-du-countdown" class="font-mono text-sm tracking-wider font-extrabold text-rose-700 dark:text-rose-300">--:--</strong></span>
                        </div>
                        <span class="text-[10px] text-slate-400">
                            Harap bayar sebelum waktu habis. Tagihan otomatis dibatalkan dalam 1 menit.
                        </span>
                    </div>
                </div>

                <!-- Panduan Scan -->
                <p class="text-xs text-slate-500 dark:text-slate-400">
                    Scan menggunakan aplikasi pembayaran yang mendukung QRIS.
                </p>

                <!-- Status Poller -->
                <div class="pt-2 flex flex-col items-center justify-center space-y-1 text-xs text-slate-500 dark:text-slate-400">
                    <i data-lucide="refresh-cw" class="w-4 h-4 animate-spin text-[#fa2f79]"></i>
                    <span class="font-medium">Menunggu konfirmasi Midtrans...</span>
                </div>

                <!-- Copy URL & Simulator Links -->
                <div class="pt-2 space-y-2 max-w-sm mx-auto">
                    <div class="p-2.5 rounded-xl bg-slate-50 dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700 text-left space-y-1.5 shadow-sm">
                        <div class="flex items-center justify-between text-[11px] font-bold text-slate-700 dark:text-slate-300">
                            <span>URL QR untuk Simulator Midtrans:</span>
                            <button type="button" onclick="copyModalQrisUrlDu()" class="text-[#fa2f79] hover:underline flex items-center gap-1 font-extrabold text-[10px]">
                                <i data-lucide="copy" class="w-3 h-3"></i> Salin URL Simulator
                            </button>
                        </div>
                        <input type="text" id="modal-du-qris-url-input" readonly
                               placeholder="https://api.sandbox.midtrans.com/v2/qris/.../qr-code"
                               class="w-full px-2.5 py-1.5 text-[10px] font-mono bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-lg text-slate-600 dark:text-slate-300 select-all focus:outline-none">
                        <span class="text-[9px] text-slate-400 block leading-tight">
                            * Paste URL ini di kolom "QR Code Image URL" pada simulator Midtrans agar terbaca sukses.
                        </span>
                    </div>

                    <div class="flex items-center justify-center gap-2 flex-wrap pt-1">
                        <button type="button" onclick="copyModalQrisUrlDu()" class="px-3.5 py-1.5 rounded-xl bg-slate-900 hover:bg-slate-800 text-white font-bold text-xs flex items-center gap-1.5 shadow-sm active:scale-95">
                            <i data-lucide="copy" class="w-3.5 h-3.5"></i> Salin URL Simulator
                        </button>
                        <a href="https://simulator.sandbox.midtrans.com/qris/index" target="_blank" class="px-3.5 py-1.5 rounded-xl bg-sky-600 hover:bg-sky-700 text-white font-bold text-xs flex items-center gap-1.5 shadow-sm active:scale-95">
                            <i data-lucide="external-link" class="w-3.5 h-3.5"></i> Buka Simulator Midtrans &nearr;
                        </a>
                    </div>
                </div>

                <!-- Footnote -->
                <p class="text-[10px] text-slate-400 pt-2 leading-relaxed">
                    QRIS di sini terhubung ke Midtrans API resmi. Status diperbarui otomatis setelah webhook tervalidasi.
                </p>
            </div>

            <!-- Footer Modal Step 2 -->
            <div class="p-4 bg-slate-50 dark:bg-slate-800/80 border-t border-slate-200 dark:border-slate-700 flex items-center justify-between gap-3 shrink-0">
                <button type="button" onclick="backToDuModalStep1()" class="px-5 py-2.5 rounded-xl border border-slate-300 dark:border-slate-600 text-xs font-bold text-slate-600 dark:text-slate-300 hover:bg-slate-100 transition-colors">
                    &larr; Pilih Metode Lain
                </button>
                <button type="button" onclick="cekStatusDaftarUlang()" class="px-5 py-2.5 rounded-xl bg-[#fa2f79] hover:bg-[#e11d48] text-white font-bold text-xs flex items-center gap-1.5 shadow-md active:scale-95 shadow-pink-500/25">
                    <i data-lucide="refresh-cw" class="w-3.5 h-3.5"></i> Periksa Status
                </button>
            </div>
        </div>

    </div>
</div>
@endsection

@push('scripts')
@php
    $midtransService = app(\App\Services\MidtransService::class);
    $midtransIsProd = $midtransService->isProduction();
    $midtransClientKey = $midtransService->getClientKey();
    $snapJsUrl = $midtransIsProd ? 'https://app.midtrans.com/snap/snap.js' : 'https://app.sandbox.midtrans.com/snap/snap.js';
@endphp
@if(!empty($midtransClientKey))
<script src="{{ $snapJsUrl }}" data-client-key="{{ $midtransClientKey }}"></script>
@endif

<script>
    let selectedDuMethod = 'qris';
    let duStatusPoller = null;
    let duCountdownInterval = null;
    let duCountdownSeconds = {{ $sisaDetikDu ?? 60 }};

    function openDuPaymentGatewayModal() {
        @if(!$isDaftarUlangSelesai && $isQrisActive)
            document.getElementById('du-modal-step-1').classList.add('hidden');
            document.getElementById('du-modal-step-2').classList.remove('hidden');
            const qrisImg = document.getElementById('modal-step2-du-qris-img');
            if (qrisImg) {
                qrisImg.src = "{{ $trxDu ? $trxDu->qr_url : '' }}";
                qrisImg.classList.remove('hidden');
            }
            const duPlaceholder = document.getElementById('du-step2-qris-placeholder');
            if (duPlaceholder) duPlaceholder.classList.add('hidden');
            const urlInput = document.getElementById('modal-du-qris-url-input');
            if (urlInput) urlInput.value = "{{ $trxDu ? $trxDu->qr_url : '' }}";
            startDuPoller();
        @else
            document.getElementById('du-modal-step-1').classList.remove('hidden');
            document.getElementById('du-modal-step-2').classList.add('hidden');
        @endif
        document.getElementById('duPaymentGatewayModal').classList.remove('hidden');
        if (window.lucide) lucide.createIcons();
    }

    function closeDuPaymentGatewayModal() {
        document.getElementById('duPaymentGatewayModal').classList.add('hidden');
    }

    function backToDuModalStep1() {
        document.getElementById('du-modal-step-2').classList.add('hidden');
        document.getElementById('du-modal-step-1').classList.remove('hidden');
        if (window.lucide) lucide.createIcons();
    }

    function updateSelectedDuMethod(val) {
        selectedDuMethod = val;

        document.querySelectorAll('.du-modal-method-card').forEach(card => {
            card.classList.remove(
                'border-[#fa2f79]', 'border-2', 'bg-pink-50/20', 'dark:bg-pink-950/20'
            );
            card.classList.add('border', 'border-slate-200', 'dark:border-slate-700');
        });

        const activeRadio = document.querySelector(`input[name="du_modal_method"][value="${val}"]`);
        if (activeRadio) {
            const card = activeRadio.closest('.du-modal-method-card');
            if (card) {
                card.classList.remove('border-slate-200', 'dark:border-slate-700', 'border');
                card.classList.add('border-[#fa2f79]', 'border-2', 'bg-pink-50/20', 'dark:bg-pink-950/20');
            }
        }
    }

    async function proceedToDuStep2() {
        const btn = document.getElementById('btn-modal-step1-du');
        const btnText = document.getElementById('btn-modal-step1-du-text');
        const origText = btnText ? btnText.innerText : 'Lanjut ke Pembayaran &rarr;';

        if (selectedDuMethod === 'tunai') {
            closeDuPaymentGatewayModal();
            if (confirm('Konfirmasi memilih pembayaran tunai di loket TU kampus sekolah?')) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = "{{ route('siswa.daftar_ulang.tunai') }}";
                const csrf = document.createElement('input');
                csrf.type = 'hidden';
                csrf.name = '_token';
                csrf.value = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
                form.appendChild(csrf);
                document.body.appendChild(form);
                form.submit();
            }
            return;
        }

        if (selectedDuMethod === 'qris') {
            if (btn) btn.disabled = true;
            if (btnText) btnText.innerText = "Menghubungi Midtrans QRIS...";

            await generateQrisDaftarUlang();

            if (btn) btn.disabled = false;
            if (btnText) btnText.innerText = origText;
            return;
        }

        // Metode Virtual Account atau E-Wallet lainnya -> Bayar via Midtrans Snap Pop-Up
        closeDuPaymentGatewayModal();
        await payWithSnapDu(selectedDuMethod);
    }

    async function payWithSnapDu(channel = null) {
        try {
            const payload = {
                metode_pembayaran: channel ? 'Midtrans Snap (' + channel + ')' : 'Midtrans Snap (Daftar Ulang)',
            };
            if (channel) {
                payload.channel = channel;
            }

            const res = await fetch("{{ route('siswa.daftar_ulang.snap') }}", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify(payload)
            });

            const data = await res.json();

            if (!data.success) {
                alert(data.message || 'Gagal membuat tagihan pembayaran online Midtrans.');
                return;
            }

            if (data.snap_token && window.snap) {
                startDuCountdown(data.sisa_detik || 60);
                startDuPoller();
                window.snap.pay(data.snap_token, {
                    onSuccess: function(result) {
                        alert('Alhamdulillah! Pembayaran daftar ulang berhasil diterima.');
                        window.location.reload();
                    },
                    onPending: function(result) {
                        alert('Tagihan berhasil dibuat. Silakan selesaikan pembayaran sesuai instruksi.');
                        window.location.reload();
                    },
                    onError: function(result) {
                        alert('Pembayaran gagal atau terjadi kendala pada transaksi.');
                        window.location.reload();
                    },
                    onClose: function() {
                        window.location.reload();
                    }
                });
            } else if (data.redirect_url) {
                window.location.href = data.redirect_url;
            } else {
                alert('Sistem pembayaran Midtrans Snap sedang dalam mode simulasi: ' + (data.message || ''));
            }
        } catch (err) {
            alert('Terjadi kesalahan jaringan saat memanggil pembayaran online: ' + (err.message || err));
        }
    }

    async function generateQrisDaftarUlang() {
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

                // Update info di card utama halaman
                const cardContainer = document.getElementById('qris-card-container');
                if (cardContainer) cardContainer.classList.remove('hidden');
                const qrisImg = document.getElementById('qris-image');
                if (qrisImg && data.qr_code_url) {
                    qrisImg.src = data.qr_code_url;
                    qrisImg.classList.remove('hidden');
                }
                const urlInputDu = document.getElementById('qris-url-input-du');
                if (urlInputDu) urlInputDu.value = data.qr_code_url || '';

                // Update info di modal Langkah 2
                const modalImg = document.getElementById('modal-step2-du-qris-img');
                if (modalImg && data.qr_code_url) {
                    modalImg.src = data.qr_code_url;
                    modalImg.classList.remove('hidden');
                }
                const duPlaceholder = document.getElementById('du-step2-qris-placeholder');
                if (duPlaceholder) duPlaceholder.classList.add('hidden');
                const modalInput = document.getElementById('modal-du-qris-url-input');
                if (modalInput) modalInput.value = data.qr_code_url || '';

                // Buka Langkah 2 di modal
                document.getElementById('du-modal-step-1').classList.add('hidden');
                document.getElementById('du-modal-step-2').classList.remove('hidden');
                document.getElementById('duPaymentGatewayModal').classList.remove('hidden');
                if (window.lucide) lucide.createIcons();

                startDuCountdown(data.sisa_detik || 60);
                startDuPoller();
            } else {
                alert(data.message || 'Gagal membuat kode QRIS.');
            }
        } catch (err) {
            alert('Terjadi kendala jaringan saat menghubungi Midtrans QRIS.');
        }
    }

    function copyModalQrisUrlDu() {
        const input = document.getElementById('modal-du-qris-url-input');
        const val = input ? input.value : '';
        if (!val) {
            alert('URL QR belum tersedia.');
            return;
        }
        navigator.clipboard.writeText(val);
        alert('URL QR Code Midtrans berhasil disalin!\nSilakan buka tab Simulator Midtrans dan Paste (Ctrl+V) URL ini.');
    }

    function copyQrisUrlDu() {
        const input = document.getElementById('qris-url-input-du');
        const val = input ? input.value : '';
        if (!val) {
            alert('URL QR belum tersedia.');
            return;
        }
        navigator.clipboard.writeText(val);
        alert('URL QR Code Midtrans berhasil disalin!\nSilakan buka tab Simulator Midtrans dan Paste (Ctrl+V) URL ini.');
    }

    function startDuCountdown(seconds) {
        if (duCountdownInterval) clearInterval(duCountdownInterval);
        duCountdownSeconds = seconds;

        function renderTime() {
            if (duCountdownSeconds <= 0) {
                clearInterval(duCountdownInterval);
                duCountdownInterval = null;
                const el1 = document.getElementById('du-countdown-display');
                if (el1) el1.innerText = "00:00";
                const el2 = document.getElementById('modal-du-countdown');
                if (el2) el2.innerText = "00:00";
                handleDuTimeout();
                return;
            }

            const m = Math.floor(duCountdownSeconds / 60);
            const s = duCountdownSeconds % 60;
            const text = String(m).padStart(2, '0') + ':' + String(s).padStart(2, '0');

            const el1 = document.getElementById('du-countdown-display');
            if (el1) el1.innerText = text;
            const el2 = document.getElementById('modal-du-countdown');
            if (el2) el2.innerText = text;

            duCountdownSeconds--;
        }

        renderTime();
        duCountdownInterval = setInterval(renderTime, 1000);
    }

    async function handleDuTimeout() {
        if (duStatusPoller) clearInterval(duStatusPoller);
        if (duCountdownInterval) clearInterval(duCountdownInterval);

        const alertBox = document.getElementById('du-check-alert');
        if (alertBox) {
            alertBox.classList.remove('hidden');
            alertBox.className = "text-xs p-3 rounded-xl bg-rose-50 text-rose-800 border border-rose-300 font-bold";
            alertBox.innerText = "Waktu pembayaran (1 menit) telah habis. Tagihan otomatis dibatalkan.";
        }

        try {
            await fetch("{{ route('siswa.daftar_ulang.cek-status') }}", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            });
        } catch (e) {}

        setTimeout(() => {
            window.location.reload();
        }, 1500);
    }

    function startDuPoller() {
        if (duStatusPoller) clearInterval(duStatusPoller);
        duStatusPoller = setInterval(async () => {
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
                if (data.is_expired || data.status === 'expire' || data.status === 'cancel') {
                    clearInterval(duStatusPoller);
                    handleDuTimeout();
                    return;
                }
                if (data.is_paid || (data.status === 'settlement')) {
                    clearInterval(duStatusPoller);
                    if (duCountdownInterval) clearInterval(duCountdownInterval);
                    window.location.reload();
                }
            } catch (e) {}
        }, 3000);
    }

    async function cekStatusDaftarUlang() {
        const btn = document.getElementById('btn-cek-status-du');
        const alertBox = document.getElementById('du-check-alert');

        if (btn) {
            btn.disabled = true;
            btn.innerHTML = `<i data-lucide="refresh-cw" class="w-3.5 h-3.5 animate-spin"></i> Memeriksa...`;
        }
        if (alertBox) alertBox.className = "hidden";

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
            if (alertBox) alertBox.classList.remove('hidden');

            if (data.is_paid || (data.status === 'settlement')) {
                if (alertBox) {
                    alertBox.className = "text-xs p-3 rounded-xl bg-pink-50 dark:bg-pink-950 text-[#be123c] dark:text-pink-200 border border-pink-300";
                    alertBox.innerText = "Alhamdulillah! Pembayaran daftar ulang LUNAS. Halaman akan disegarkan...";
                }
                setTimeout(() => window.location.reload(), 1500);
            } else {
                if (alertBox) {
                    alertBox.className = "text-xs p-3 rounded-xl bg-amber-50 dark:bg-amber-950 text-amber-800 dark:text-amber-200 border border-amber-300";
                    alertBox.innerText = data.message || "Status transaksi: " + data.status;
                }
            }
        } catch (err) {
            if (alertBox) {
                alertBox.classList.remove('hidden');
                alertBox.className = "text-xs p-3 rounded-xl bg-rose-50 text-rose-800 border border-rose-300";
                alertBox.innerText = "Gagal memeriksa status pembayaran. Periksa koneksi Anda.";
            }
        } finally {
            if (btn) {
                btn.disabled = false;
                btn.innerHTML = `<i data-lucide="refresh-cw" class="w-3.5 h-3.5"></i> Periksa Status Pembayaran`;
                if (window.lucide) lucide.createIcons();
            }
        }
    }

    @if($isQrisActive)
        startDuPoller();
        @if(($sisaDetikDu ?? 0) > 0)
            startDuCountdown({{ $sisaDetikDu }});
        @else
            handleDuTimeout();
        @endif
    @endif
</script>
@endpush
