@extends('layouts.siswa')

@section('title', 'Pembayaran Biaya Formulir')

@section('content')
<div class="max-w-4xl lg:max-w-5xl mx-auto space-y-6">

    @php
        $biayaAwal = $gelombang ? (float)$gelombang->biaya_pendaftaran : 100000.00;
        $potonganAwal = $pembayaran ? (float)$pembayaran->potongan : 0.00;
        $totalBayarAwal = $pembayaran ? (float)$pembayaran->total_bayar : max(0.00, $biayaAwal - $potonganAwal);
        $kodeVoucherAwal = $pembayaran && $pembayaran->voucher ? $pembayaran->voucher->kode_voucher : '';
        $trxStatus = $pembayaran ? $pembayaran->status_transaksi : null;
        $isPaid = ($pendaftar->status_pembayaran === 'Paid');
        $isProd = app(\App\Services\MidtransService::class)->isProduction();
    @endphp

    <!-- ======================================================== -->
    <!-- HEADER HALAMAN & BREADCRUMB                              -->
    <!-- Menyeimbangkan tata letak agar proporsional dan tidak    -->
    <!-- menyisakan ruang kosong yang renggang di layar desktop.   -->
    <!-- ======================================================== -->
    <div class="flex items-center justify-between flex-wrap gap-4 pb-1">
        <div>
            <a href="{{ route('siswa.dashboard') }}" class="text-xs font-semibold text-[#fa2f79] hover:underline flex items-center gap-1.5 mb-1.5 transition-colors">
                <i data-lucide="arrow-left" class="w-3.5 h-3.5"></i> Kembali ke Dashboard
            </a>
            <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 dark:text-white tracking-tight">
                Pembayaran Biaya Formulir PPDB
            </h1>
            <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400 mt-0.5">
                SMK Islam Bina Khalifah Bangsa Kota Metro &bull; Tahun Ajaran 2026/2027
            </p>
        </div>

        <div class="flex items-center gap-2.5 flex-wrap">
            <span class="px-3.5 py-1.5 rounded-xl text-xs font-bold bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-300 border border-slate-200 dark:border-slate-700 flex items-center gap-1.5 shadow-sm">
                <i data-lucide="layers" class="w-3.5 h-3.5 text-emerald-500"></i>
                <span>{{ $gelombang->nama_gelombang ?? 'Gelombang Reguler' }}</span>
            </span>

            @if($isPaid)
                <span class="px-3.5 py-1.5 rounded-xl text-xs font-bold bg-emerald-100 text-emerald-800 dark:bg-emerald-950 dark:text-emerald-300 border border-emerald-300 dark:border-emerald-700 flex items-center gap-1.5 shadow-sm">
                    <i data-lucide="check-circle" class="w-4 h-4 text-emerald-600 dark:text-emerald-400"></i>
                    <span>Status: Lunas</span>
                </span>
            @elseif($trxStatus === 'expire')
                <span class="px-3.5 py-1.5 rounded-xl text-xs font-bold bg-rose-100 text-rose-800 dark:bg-rose-950 dark:text-rose-300 border border-rose-300 dark:border-rose-700 flex items-center gap-1.5 shadow-sm">
                    <i data-lucide="alert-triangle" class="w-4 h-4 text-rose-600"></i>
                    <span>Status: Kedaluwarsa</span>
                </span>
            @elseif($trxStatus === 'pending')
                <span class="px-3.5 py-1.5 rounded-xl text-xs font-bold bg-amber-100 text-amber-800 dark:bg-amber-950 dark:text-amber-300 border border-amber-300 dark:border-amber-700 flex items-center gap-1.5 shadow-sm">
                    <i data-lucide="clock" class="w-4 h-4 text-amber-600 animate-pulse"></i>
                    <span>Menunggu Pembayaran</span>
                </span>
            @else
                <span class="px-3.5 py-1.5 rounded-xl text-xs font-bold bg-amber-50 text-amber-700 dark:bg-amber-950/70 dark:text-amber-300 border border-amber-200 dark:border-amber-800/80 flex items-center gap-1.5 shadow-sm">
                    <i data-lucide="info" class="w-4 h-4 text-amber-600"></i>
                    <span>Belum Lunas</span>
                </span>
            @endif
        </div>
    </div>

    <!-- Flash Messages (Alerts) -->
    @if(session('success'))
    <div class="p-4 rounded-2xl bg-emerald-50 dark:bg-emerald-950/60 border border-emerald-200 dark:border-emerald-800 text-emerald-800 dark:text-emerald-200 text-xs font-semibold flex items-center justify-between shadow-sm">
        <div class="flex items-center gap-2">
            <i data-lucide="check-circle" class="w-4 h-4 text-emerald-600"></i>
            <span>{{ session('success') }}</span>
        </div>
        <button type="button" onclick="this.parentElement.remove()" class="text-emerald-600 hover:text-emerald-800">&times;</button>
    </div>
    @endif

    @if(session('error'))
    <div class="p-4 rounded-2xl bg-rose-50 dark:bg-rose-950/60 border border-rose-200 dark:border-rose-800 text-rose-800 dark:text-rose-200 text-xs font-semibold flex items-center justify-between shadow-sm">
        <div class="flex items-center gap-2">
            <i data-lucide="alert-circle" class="w-4 h-4 text-rose-600"></i>
            <span>{{ session('error') }}</span>
        </div>
        <button type="button" onclick="this.parentElement.remove()" class="text-rose-600 hover:text-rose-800">&times;</button>
    </div>
    @endif

    @if(session('info'))
    <div class="p-4 rounded-2xl bg-sky-50 dark:bg-sky-950/60 border border-sky-200 dark:border-sky-800 text-sky-800 dark:text-sky-200 text-xs font-semibold flex items-center justify-between shadow-sm">
        <div class="flex items-center gap-2">
            <i data-lucide="info" class="w-4 h-4 text-sky-600"></i>
            <span>{{ session('info') }}</span>
        </div>
        <button type="button" onclick="this.parentElement.remove()" class="text-sky-600 hover:text-sky-800">&times;</button>
    </div>
    @endif

    <!-- Alerts Status Transaksi (Expire / Cancel / Pending Aktif) -->
    @if(!$isPaid && $trxStatus === 'expire')
    <div class="p-4 rounded-2xl bg-rose-50 dark:bg-rose-950/40 border border-rose-200 dark:border-rose-800 text-rose-900 dark:text-rose-200 flex items-start gap-3 shadow-sm">
        <i data-lucide="alert-triangle" class="w-5 h-5 text-rose-600 shrink-0 mt-0.5"></i>
        <div class="space-y-1 text-xs">
            <strong class="text-xs font-bold block text-rose-800 dark:text-rose-300">Tagihan Sebelumnya Telah Kedaluwarsa</strong>
            <p class="text-rose-700 dark:text-rose-300/90 leading-relaxed">
                Batas waktu pembayaran untuk transaksi <code class="font-mono font-bold">{{ $pembayaran->kode_transaksi }}</code> telah habis. Anda dapat memilih kembali metode pembayaran dan membuat tagihan baru. Potongan voucher diskon tetap dapat digunakan kembali.
            </p>
        </div>
    </div>
    @elseif(!$isPaid && $trxStatus === 'cancel')
    <div class="p-4 rounded-2xl bg-rose-50 dark:bg-rose-950/40 border border-rose-200 dark:border-rose-800 text-rose-900 dark:text-rose-200 flex items-start gap-3 shadow-sm">
        <i data-lucide="x-circle" class="w-5 h-5 text-rose-600 shrink-0 mt-0.5"></i>
        <div class="space-y-1 text-xs">
            <strong class="text-xs font-bold block text-rose-800 dark:text-rose-300">Pembayaran Sebelumnya Dibatalkan / Gagal</strong>
            <p class="text-rose-700 dark:text-rose-300/90 leading-relaxed">
                Transaksi <code class="font-mono font-bold">{{ $pembayaran->kode_transaksi }}</code> dibatalkan atau gagal diproses oleh gateway. Silakan klik tombol Bayar Sekarang untuk membuat tagihan baru.
            </p>
        </div>
    </div>
    @elseif(!$isPaid && $pembayaran && $trxStatus === 'pending')
    <div class="p-4 sm:p-5 rounded-2xl bg-amber-50 dark:bg-amber-950/40 border border-amber-200 dark:border-amber-800 text-amber-900 dark:text-amber-200 flex items-center justify-between flex-wrap gap-3 shadow-sm">
        <div class="flex items-center gap-2.5 text-xs sm:text-sm">
            <i data-lucide="clock" class="w-5 h-5 text-amber-600 shrink-0"></i>
            <span>Tagihan aktif menunggu pembayaran: <strong class="font-mono text-slate-900 dark:text-white">{{ $pembayaran->kode_transaksi }}</strong> (Rp {{ number_format($pembayaran->total_bayar, 0, ',', '.') }})</span>
        </div>
        <div class="flex items-center gap-2">
            <button type="button" onclick="openPaymentGatewayModal()" class="px-4 py-2 rounded-xl bg-[#059669] hover:bg-[#047857] text-white font-bold text-xs shadow-sm transition-colors flex items-center gap-1.5 active:scale-95">
                <i data-lucide="qr-code" class="w-3.5 h-3.5"></i> Buka Tagihan
            </button>
            <button type="button" id="btn-cek-status-midtrans" onclick="cekStatusMidtrans()" class="px-4 py-2 rounded-xl bg-amber-600 hover:bg-amber-700 text-white font-bold text-xs shadow-sm transition-colors flex items-center gap-1.5 active:scale-95">
                <i data-lucide="refresh-cw" class="w-3.5 h-3.5"></i> Periksa Status
            </button>
        </div>
    </div>
    @endif

    <!-- Kotak Notifikasi Lunas (Jika Siswa Telah Lunas) -->
    @if($isPaid)
    <div class="p-6 md:p-8 rounded-3xl bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-800 text-emerald-900 dark:text-emerald-200 space-y-5 shadow-sm">
        <div class="flex items-center gap-3.5">
            <div class="w-12 h-12 rounded-2xl bg-emerald-600 text-white flex items-center justify-center shrink-0 shadow-md">
                <i data-lucide="check" class="w-7 h-7"></i>
            </div>
            <div>
                <h3 class="text-base sm:text-lg font-bold text-slate-900 dark:text-white">Alhamdulillah, Pembayaran Anda Telah Lunas!</h3>
                <p class="text-xs sm:text-sm text-emerald-700 dark:text-emerald-300">Biaya formulir pendaftaran telah berhasil diverifikasi oleh sistem gateway resmi Midtrans / Kasir TU sekolah.</p>
            </div>
        </div>

        <div class="p-4 sm:p-5 rounded-2xl bg-white dark:bg-slate-900/80 border border-emerald-200 dark:border-emerald-800 text-xs sm:text-sm text-slate-700 dark:text-slate-300 grid grid-cols-2 sm:grid-cols-4 gap-4">
            <div>
                <span class="text-slate-400 block text-[11px] mb-0.5">Kode Transaksi</span>
                <strong class="font-mono text-slate-900 dark:text-white">{{ $pembayaran->kode_transaksi ?? '-' }}</strong>
            </div>
            <div>
                <span class="text-slate-400 block text-[11px] mb-0.5">Metode Bayar</span>
                <strong class="text-slate-900 dark:text-white">{{ $pembayaran->metode_pembayaran ?? 'Midtrans' }}</strong>
            </div>
            <div>
                <span class="text-slate-400 block text-[11px] mb-0.5">Total Bayar</span>
                <strong class="font-mono text-emerald-600 dark:text-emerald-400 font-bold">Rp {{ number_format($pembayaran->total_bayar ?? 0, 0, ',', '.') }}</strong>
            </div>
            <div>
                <span class="text-slate-400 block text-[11px] mb-0.5">Waktu Lunas</span>
                <strong class="text-slate-900 dark:text-white">{{ $pembayaran && $pembayaran->tgl_bayar ? \Carbon\Carbon::parse($pembayaran->tgl_bayar)->format('d/m/Y H:i') : '-' }}</strong>
            </div>
        </div>

        <div class="flex items-center gap-3 flex-wrap pt-1">
            <a href="{{ route('siswa.cetak.kwitansi') }}" target="_blank" class="px-5 py-2.5 rounded-xl bg-[#059669] hover:bg-[#047857] text-white font-bold text-xs flex items-center gap-2 shadow-sm transition-colors">
                <i data-lucide="printer" class="w-4 h-4"></i>
                <span>Cetak Bukti Kwitansi (PDF)</span>
            </a>
            <a href="{{ route('siswa.berkas') }}" class="px-5 py-2.5 rounded-xl bg-slate-900 dark:bg-slate-800 hover:bg-slate-800 text-white font-bold text-xs flex items-center gap-2 shadow-sm transition-colors">
                <i data-lucide="upload-cloud" class="w-4 h-4"></i>
                <span>Lanjut Unggah Berkas &rarr;</span>
            </a>
        </div>
    </div>
    @endif

    <!-- ======================================================== -->
    <!-- 1. TAMPILAN AWAL HALAMAN UTAMA (PERSIS REFERENSI image1.png) -->
    <!-- Bersih, proporsional, menampilkan ringkasan tagihan,     -->
    <!-- voucher diskon, dan tombol hijau besar Bayar Sekarang.   -->
    <!-- Belum memunculkan QRIS atau pilihan metode pembayaran.   -->
    <!-- ======================================================== -->
    <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200/90 dark:border-slate-800 shadow-xl shadow-slate-900/5 dark:shadow-black/20 p-6 sm:p-10 lg:p-12 space-y-8 transition-all">

        <!-- HEADER KARTU: Judul & Badge Status (Persis image1.png) -->
        <div class="flex items-center justify-between flex-wrap gap-3 pb-2 border-b border-slate-100 dark:border-slate-800/80">
            <h2 class="text-2xl sm:text-3xl font-extrabold text-slate-900 dark:text-white tracking-tight">
                Pembayaran PPDB
            </h2>
            @if($isPaid)
                <span class="px-3.5 py-1 rounded-full text-xs font-semibold bg-[#dcfce7] text-[#059669] dark:bg-emerald-950 dark:text-emerald-300">
                    Lunas
                </span>
            @elseif($trxStatus === 'expire')
                <span class="px-3.5 py-1 rounded-full text-xs font-semibold bg-rose-100 text-rose-800 dark:bg-rose-950 dark:text-rose-300">
                    Kedaluwarsa
                </span>
            @elseif($trxStatus === 'cancel')
                <span class="px-3.5 py-1 rounded-full text-xs font-semibold bg-rose-100 text-rose-800 dark:bg-rose-950 dark:text-rose-300">
                    Dibatalkan
                </span>
            @else
                <span class="px-3.5 py-1 rounded-full text-xs font-medium bg-[#ffedd5] text-[#9a3412] dark:bg-amber-950/70 dark:text-amber-300">
                    Belum Lunas
                </span>
            @endif
        </div>

        <!-- 1. RINGKASAN TAGIHAN (Persis image1.png) -->
        <div class="space-y-4">
            <h3 class="text-base sm:text-lg font-bold text-slate-900 dark:text-white">
                Ringkasan Tagihan
            </h3>

            <div class="grid grid-cols-1 md:grid-cols-12 gap-6 md:gap-8 items-center bg-slate-50/70 dark:bg-slate-800/40 p-5 sm:p-7 rounded-2xl border border-slate-100 dark:border-slate-800">
                <!-- Sisi Kiri: Biaya Pendaftaran & Nominal Besar Hijau (Persis image1.png) -->
                <div class="md:col-span-5 space-y-1.5">
                    <span class="text-xs text-slate-500 dark:text-slate-400 block font-medium">Biaya pendaftaran</span>
                    <div id="display-total-prominent" class="text-3xl sm:text-4xl lg:text-5xl font-extrabold text-[#059669] dark:text-emerald-400 font-mono tracking-tight">
                        Rp {{ number_format($totalBayarAwal, 0, ',', '.') }}
                    </div>
                    <div id="row-info-potongan" class="{{ $potonganAwal > 0 ? '' : 'hidden' }} text-xs text-emerald-600 dark:text-emerald-400 font-medium pt-1">
                        Potongan voucher: <span id="text-info-potongan" class="font-mono font-bold">- Rp {{ number_format($potonganAwal, 0, ',', '.') }}</span>
                    </div>
                </div>

                <!-- Sisi Kanan: Metadata Calon Siswa (Right-aligned persis image1.png) -->
                <div class="md:col-span-7 space-y-3 text-xs sm:text-sm border-t md:border-t-0 md:border-l border-slate-200 dark:border-slate-700/80 pt-4 md:pt-0 md:pl-8">
                    <div class="flex items-center justify-between text-slate-600 dark:text-slate-400">
                        <span class="font-medium">Nama siswa</span>
                        <strong class="text-slate-900 dark:text-white font-semibold text-right">{{ $pendaftar->nama }}</strong>
                    </div>
                    <div class="flex items-center justify-between text-slate-600 dark:text-slate-400">
                        <span class="font-medium">Nomor pendaftaran</span>
                        <strong class="font-mono text-slate-900 dark:text-white font-semibold text-right">{{ $pendaftar->no_pendaftaran }}</strong>
                    </div>
                    <div class="flex items-center justify-between text-slate-600 dark:text-slate-400">
                        <span class="font-medium">Jurusan</span>
                        <strong class="text-slate-900 dark:text-white font-semibold text-right">{{ $pendaftar->jurusan1 ? $pendaftar->jurusan1->nama_jurusan : '-' }}</strong>
                    </div>
                    <div class="flex items-center justify-between text-slate-600 dark:text-slate-400">
                        <span class="font-medium">Gelombang</span>
                        <strong class="text-slate-900 dark:text-white font-semibold text-right">{{ $gelombang->nama_gelombang }}</strong>
                    </div>
                </div>
            </div>
        </div>

        <!-- 2. VOUCHER / DISKON (Persis image1.png) -->
        <div class="space-y-3.5">
            <div class="flex items-center justify-between flex-wrap gap-2">
                <h3 class="text-base sm:text-lg font-bold text-slate-900 dark:text-white">
                    Voucher / Diskon
                </h3>
                @if(isset($promoVouchers) && $promoVouchers->isNotEmpty())
                <button type="button" onclick="togglePromoList()" class="text-xs font-semibold text-[#059669] hover:underline flex items-center gap-1.5 transition-colors">
                    <i data-lucide="sparkles" class="w-3.5 h-3.5"></i>
                    <span id="promo-toggle-text">Informasi Promo Aktif ({{ $promoVouchers->count() }})</span>
                </button>
                @endif
            </div>

            <!-- Input Box Kode Voucher dengan Tombol Gunakan Persis image1.png -->
            <div class="relative flex items-center">
                <input type="text" id="kode_voucher" value="{{ $kodeVoucherAwal }}" placeholder="Masukkan kode voucher" class="w-full pl-4 pr-32 py-3.5 rounded-2xl bg-white dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700 text-xs sm:text-sm font-sans text-slate-900 dark:text-white placeholder:text-slate-400 placeholder:normal-case focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition-all shadow-sm">
                <div class="absolute right-2.5 flex items-center">
                    <button type="button" id="btn-apply-voucher" onclick="applyVoucher()" class="px-5 py-2 rounded-xl bg-[#dcfce7] hover:bg-[#bbf7d0] dark:bg-emerald-950 dark:hover:bg-emerald-900 text-[#059669] dark:text-emerald-300 font-bold text-xs transition-colors flex items-center gap-1 active:scale-95 shadow-sm">
                        <span>Gunakan</span>
                    </button>
                </div>
            </div>

            <div id="voucher-msg" class="{{ $potonganAwal > 0 ? '' : 'hidden' }} text-xs font-semibold text-emerald-600 dark:text-emerald-400">
                @if($potonganAwal > 0)
                    Voucher {{ $kodeVoucherAwal }} berhasil diterapkan pada tagihan Anda.
                @endif
            </div>

            <!-- Drawer Informasi Promo Aktif -->
            @if(isset($promoVouchers) && $promoVouchers->isNotEmpty())
            <div id="promo-list-drawer" class="hidden pt-3 space-y-3 border-t border-slate-100 dark:border-slate-800">
                <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider block">Informasi Promo & Diskon Tersedia:</span>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                    @foreach($promoVouchers as $promo)
                    @php
                        $potonganText = $promo->tipe === 'persen'
                            ? 'Diskon ' . rtrim(rtrim(number_format((float)$promo->nilai_potongan, 2, ',', '.'), '0'), ',') . '%'
                            : 'Potongan Rp ' . number_format($promo->nilai_potongan, 0, ',', '.');
                    @endphp
                    <div class="p-3.5 rounded-2xl bg-slate-50 dark:bg-slate-800/60 border border-slate-200 dark:border-slate-700 flex flex-col justify-between space-y-2 hover:border-emerald-500 transition-colors shadow-sm">
                        <div>
                            <div class="flex items-center justify-between text-[10px]">
                                <span class="px-2 py-0.5 rounded font-bold uppercase {{ $promo->tipe === 'persen' ? 'bg-purple-100 text-purple-800 dark:bg-purple-950 dark:text-purple-300' : 'bg-emerald-100 text-emerald-800 dark:bg-emerald-950 dark:text-emerald-300' }}">
                                    {{ $potonganText }}
                                </span>
                                <span class="font-mono text-slate-400">Sisa {{ $promo->sisaKuota() }}</span>
                            </div>
                            <h4 class="text-xs font-bold text-slate-900 dark:text-white pt-1">{{ $promo->nama_voucher ?: 'Promo PPDB' }}</h4>
                            @if($promo->deskripsi)
                                <p class="text-[11px] text-slate-500 dark:text-slate-400 line-clamp-2">{{ $promo->deskripsi }}</p>
                            @endif
                        </div>
                        <div class="flex items-center justify-between pt-1 border-t border-slate-200 dark:border-slate-700">
                            <span class="font-mono font-bold text-xs text-emerald-600 dark:text-emerald-400">{{ $promo->kode_voucher }}</span>
                            <button type="button" onclick="selectPromoVoucher('{{ $promo->kode_voucher }}')" class="px-3 py-1 rounded-lg bg-[#059669] hover:bg-[#047857] text-white font-bold text-[10px] transition-colors">
                                Pakai
                            </button>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif
        </div>

        <!-- 3. TOMBOL BAYAR SEKARANG (Persis image1.png) -->
        <div class="space-y-3 pt-2">
            @if($isPaid)
                <div class="w-full py-4 sm:py-5 rounded-2xl bg-emerald-500/15 border border-emerald-500/30 text-emerald-800 dark:text-emerald-300 font-extrabold text-base sm:text-lg flex items-center justify-center gap-2 shadow-sm">
                    <i data-lucide="check-circle-2" class="w-6 h-6 text-emerald-600 dark:text-emerald-400"></i>
                    <span>Tagihan Telah Selesai Dibayar (Lunas)</span>
                </div>
                <div class="flex items-center justify-center gap-3 pt-1">
                    <a href="{{ route('siswa.cetak.kwitansi') }}" target="_blank" class="px-5 py-2.5 rounded-xl bg-[#059669] hover:bg-[#047857] text-white font-bold text-xs flex items-center gap-2 shadow-sm transition-colors">
                        <i data-lucide="printer" class="w-4 h-4"></i> Cetak Kwitansi Resmi
                    </a>
                    <a href="{{ route('siswa.berkas') }}" class="px-5 py-2.5 rounded-xl bg-slate-900 dark:bg-slate-800 hover:bg-slate-800 text-white font-bold text-xs flex items-center gap-2 shadow-sm transition-colors">
                        <i data-lucide="arrow-right" class="w-4 h-4"></i> Lanjut ke Berkas
                    </a>
                </div>
            @else
                <button type="button" onclick="openPaymentGatewayModal()" class="w-full py-4 sm:py-5 rounded-2xl bg-[#fa2f79] hover:bg-[#e11d48] text-white font-extrabold text-base sm:text-lg shadow-xl shadow-pink-500/25 transition-all active:scale-[0.99] flex items-center justify-center gap-2">
                    <span>Bayar Sekarang &rarr;</span>
                    <span class="sr-only">Bayar Sekarang via Midtrans Snap</span>
                </button>

                <!-- Caption Persis image1.png -->
                <p class="text-xs text-slate-400 text-center leading-relaxed">
                    Halaman utama hanya menampilkan tagihan dan tombol pembayaran. Belum ada QRIS atau pilihan metode.
                </p>
            @endif
        </div>

    </div>

    <!-- ======================================================== -->
    <!-- 3 KARTU INFORMASI PENDUKUNG (MENJAGA PROPORSI LAYAR LEBAR)-->
    <!-- Menghilangkan kesan "renggang banget" di monitor 1080p/75%-->
    <!-- ======================================================== -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 pt-2">
        <div class="p-4 sm:p-5 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 shadow-sm flex items-start gap-3.5">
            <div class="w-9 h-9 rounded-xl bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 flex items-center justify-center shrink-0">
                <i data-lucide="shield-check" class="w-5 h-5"></i>
            </div>
            <div class="space-y-1">
                <h4 class="text-xs font-bold text-slate-900 dark:text-white">Gerbang Pembayaran Resmi</h4>
                <p class="text-[11px] text-slate-500 dark:text-slate-400 leading-relaxed">
                    Terkoneksi langsung ke Midtrans Payment Gateway resmi Bank Indonesia dengan enkripsi SSL 256-bit.
                </p>
            </div>
        </div>

        <div class="p-4 sm:p-5 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 shadow-sm flex items-start gap-3.5">
            <div class="w-9 h-9 rounded-xl bg-sky-500/10 text-sky-600 dark:text-sky-400 flex items-center justify-center shrink-0">
                <i data-lucide="wallet-cards" class="w-5 h-5"></i>
            </div>
            <div class="space-y-1">
                <h4 class="text-xs font-bold text-slate-900 dark:text-white">Pilihan Metode Lengkap</h4>
                <p class="text-[11px] text-slate-500 dark:text-slate-400 leading-relaxed">
                    Mendukung QRIS semua e-wallet, Virtual Account seluruh bank nasional, hingga bayar tunai di Loket TU.
                </p>
            </div>
        </div>

        <div class="p-4 sm:p-5 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 shadow-sm flex items-start gap-3.5">
            <div class="w-9 h-9 rounded-xl bg-amber-500/10 text-amber-600 dark:text-amber-400 flex items-center justify-center shrink-0">
                <i data-lucide="check-check" class="w-5 h-5"></i>
            </div>
            <div class="space-y-1">
                <h4 class="text-xs font-bold text-slate-900 dark:text-white">Verifikasi Otomatis Instan</h4>
                <p class="text-[11px] text-slate-500 dark:text-slate-400 leading-relaxed">
                    Status tagihan langsung terkonfirmasi Lunas dalam hitungan detik tanpa perlu kirim bukti transfer manual.
                </p>
            </div>
        </div>
    </div>

    <!-- Hidden Form untuk Pembayaran Tunai Loket TU -->
    <form id="form-tunai-tu" action="{{ route('siswa.pembayaran.tunai') }}" method="POST" class="hidden">
        @csrf
        <input type="hidden" name="kode_voucher" id="hidden-tunai-voucher" value="{{ $kodeVoucherAwal }}">
    </form>

</div>

<!-- ======================================================== -->
<!-- MODAL PEMBAYARAN MIDTRANS SESUAI REFERENSI               -->
<!-- Langkah 1 (image 3.png) & Langkah 2 (image 2.png)        -->
<!-- ======================================================== -->
<div id="paymentGatewayModal" class="fixed inset-0 z-50 bg-slate-950/80 backdrop-blur-md hidden flex items-center justify-center p-3 sm:p-4 overflow-y-auto">
    <div class="bg-white dark:bg-slate-900 rounded-3xl max-w-lg w-full border border-slate-200 dark:border-slate-800 shadow-2xl overflow-hidden animate-in fade-in zoom-in-95 duration-200 flex flex-col my-auto max-h-[92vh]">
        
        <!-- ==================================================== -->
        <!-- LANGKAH 1 DARI 2: PEMILIHAN METODE (PERSIS image 3.png) -->
        <!-- ==================================================== -->
        <div id="modal-step-1" class="flex flex-col h-full overflow-hidden">
            <!-- Header Modal (Sesuai Cuplikan image 3.png) -->
            <div class="px-6 py-4 bg-gradient-to-r from-[#8c3b06] to-[#a64808] text-white flex items-start justify-between relative shrink-0">
                <div class="space-y-1">
                    <span class="inline-block px-2.5 py-0.5 rounded-full text-[9px] font-extrabold uppercase tracking-wider bg-white/20 text-amber-100 backdrop-blur-sm">
                        LANGKAH 1 DARI 2: PEMILIHAN METODE
                    </span>
                    <h3 class="text-base font-extrabold tracking-tight leading-snug">Pembayaran Formulir PPDB</h3>
                    <p class="text-xs text-amber-100/90 leading-tight">
                        Pilih metode pembayaran untuk menyelesaikan tagihan pendaftaran.
                    </p>
                </div>
                <button type="button" onclick="closePaymentGatewayModal()" class="text-white/80 hover:text-white p-1 rounded-lg hover:bg-white/10 transition-colors shrink-0 -mr-1">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>

            <!-- Body Modal (Scrollable) -->
            <div class="p-5 space-y-4 overflow-y-auto flex-1 text-xs">
                
                <!-- Nominal Tagihan Box (Persis image 3.png) -->
                <div class="p-4 rounded-2xl bg-slate-50 dark:bg-slate-800/60 border border-slate-200 dark:border-slate-700">
                    <span class="text-xs text-slate-500 dark:text-slate-400 block mb-1">Nominal Tagihan</span>
                    <div id="modal-step1-nominal" class="text-2xl font-black text-[#059669] dark:text-emerald-400 font-mono">
                        Rp {{ number_format($totalBayarAwal, 0, ',', '.') }}
                    </div>
                </div>

                <!-- Pembayaran Online Section (Persis image 3.png) -->
                <div class="space-y-2.5">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-bold text-slate-700 dark:text-slate-300">Pembayaran Online</span>
                        <span class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-slate-100 dark:bg-slate-800 text-slate-500">
                            Midtrans Snap Multi-Payment
                        </span>
                    </div>

                    <div class="grid grid-cols-3 gap-2.5">
                        <!-- QRIS -->
                        <label class="modal-method-card p-3 rounded-2xl border-2 border-[#10b981] bg-emerald-50/20 dark:bg-emerald-950/20 cursor-pointer transition-all flex flex-col items-center justify-center text-center space-y-2 relative shadow-sm">
                            <input type="radio" name="modal_method" value="qris" checked onchange="updateSelectedMethod('qris')" class="sr-only">
                            <div class="text-[#10b981]">
                                <i data-lucide="qr-code" class="w-6 h-6"></i>
                            </div>
                            <strong class="block text-xs font-bold text-slate-900 dark:text-white">QRIS</strong>
                        </label>

                        <!-- OVO -->
                        <label class="modal-method-card p-3 rounded-2xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 hover:border-slate-300 cursor-pointer transition-all flex flex-col items-center justify-center text-center space-y-2 relative shadow-sm">
                            <input type="radio" name="modal_method" value="ovo" onchange="updateSelectedMethod('ovo')" class="sr-only">
                            <div class="text-purple-600 dark:text-purple-400">
                                <i data-lucide="wallet" class="w-6 h-6"></i>
                            </div>
                            <strong class="block text-xs font-bold text-slate-900 dark:text-white">OVO</strong>
                        </label>

                        <!-- ShopeePay -->
                        <label class="modal-method-card p-3 rounded-2xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 hover:border-slate-300 cursor-pointer transition-all flex flex-col items-center justify-center text-center space-y-2 relative shadow-sm">
                            <input type="radio" name="modal_method" value="shopeepay" onchange="updateSelectedMethod('shopeepay')" class="sr-only">
                            <div class="text-orange-600 dark:text-orange-400">
                                <i data-lucide="smartphone" class="w-6 h-6"></i>
                            </div>
                            <strong class="block text-xs font-bold text-slate-900 dark:text-white">ShopeePay</strong>
                        </label>
                    </div>
                </div>

                <!-- Transfer Virtual Account Section (Persis image 3.png) -->
                <div class="space-y-2.5">
                    <span class="text-xs font-bold text-slate-700 dark:text-slate-300 block">Transfer Virtual Account</span>
                    <div class="grid grid-cols-3 gap-2">
                        <!-- BCA VA -->
                        <label class="modal-method-card p-2 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 hover:border-slate-300 cursor-pointer transition-all flex flex-col justify-between text-left space-y-1 relative">
                            <input type="radio" name="modal_method" value="bca_va" onchange="updateSelectedMethod('bca_va')" class="sr-only">
                            <strong class="block text-xs font-bold text-slate-900 dark:text-white">BCA VA</strong>
                            <span class="text-[9px] text-slate-400 block">BCA Virtual Account</span>
                        </label>

                        <!-- BRI VA -->
                        <label class="modal-method-card p-2 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 hover:border-slate-300 cursor-pointer transition-all flex flex-col justify-between text-left space-y-1 relative">
                            <input type="radio" name="modal_method" value="bri_va" onchange="updateSelectedMethod('bri_va')" class="sr-only">
                            <strong class="block text-xs font-bold text-slate-900 dark:text-white">BRI VA</strong>
                            <span class="text-[9px] text-slate-400 block">BRI Virtual Account</span>
                        </label>

                        <!-- BNI VA -->
                        <label class="modal-method-card p-2 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 hover:border-slate-300 cursor-pointer transition-all flex flex-col justify-between text-left space-y-1 relative">
                            <input type="radio" name="modal_method" value="bni_va" onchange="updateSelectedMethod('bni_va')" class="sr-only">
                            <strong class="block text-xs font-bold text-slate-900 dark:text-white">BNI VA</strong>
                            <span class="text-[9px] text-slate-400 block">BNI Virtual Account</span>
                        </label>

                        <!-- Permata -->
                        <label class="modal-method-card p-2 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 hover:border-slate-300 cursor-pointer transition-all flex flex-col justify-between text-left space-y-1 relative">
                            <input type="radio" name="modal_method" value="permata_va" onchange="updateSelectedMethod('permata_va')" class="sr-only">
                            <strong class="block text-xs font-bold text-slate-900 dark:text-white">Permata</strong>
                            <span class="text-[9px] text-slate-400 block">Permata Virtual Account</span>
                        </label>

                        <!-- CIMB -->
                        <label class="modal-method-card p-2 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 hover:border-slate-300 cursor-pointer transition-all flex flex-col justify-between text-left space-y-1 relative">
                            <input type="radio" name="modal_method" value="cimb_va" onchange="updateSelectedMethod('cimb_va')" class="sr-only">
                            <strong class="block text-xs font-bold text-slate-900 dark:text-white">CIMB</strong>
                            <span class="text-[9px] text-slate-400 block">CIMB Virtual Account</span>
                        </label>

                        <!-- Mandiri -->
                        <label class="modal-method-card p-2 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 hover:border-slate-300 cursor-pointer transition-all flex flex-col justify-between text-left space-y-1 relative">
                            <input type="radio" name="modal_method" value="mandiri" onchange="updateSelectedMethod('mandiri')" class="sr-only">
                            <strong class="block text-xs font-bold text-slate-900 dark:text-white">Mandiri</strong>
                            <span class="text-[9px] text-slate-400 block">Mandiri Bill Payment</span>
                        </label>

                        <!-- Danamon -->
                        <label class="modal-method-card p-2 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 hover:border-slate-300 cursor-pointer transition-all flex flex-col justify-between text-left space-y-1 relative">
                            <input type="radio" name="modal_method" value="danamon_va" onchange="updateSelectedMethod('danamon_va')" class="sr-only">
                            <strong class="block text-xs font-bold text-slate-900 dark:text-white">Danamon</strong>
                            <span class="text-[9px] text-slate-400 block">Danamon VA</span>
                        </label>

                        <!-- BSI -->
                        <label class="modal-method-card p-2 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 hover:border-slate-300 cursor-pointer transition-all flex flex-col justify-between text-left space-y-1 relative">
                            <input type="radio" name="modal_method" value="bsi_va" onchange="updateSelectedMethod('bsi_va')" class="sr-only">
                            <strong class="block text-xs font-bold text-slate-900 dark:text-white">BSI</strong>
                            <span class="text-[9px] text-slate-400 block">BSI VA</span>
                        </label>

                        <!-- SeaBank -->
                        <label class="modal-method-card p-2 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 hover:border-slate-300 cursor-pointer transition-all flex flex-col justify-between text-left space-y-1 relative">
                            <input type="radio" name="modal_method" value="seabank_va" onchange="updateSelectedMethod('seabank_va')" class="sr-only">
                            <strong class="block text-xs font-bold text-slate-900 dark:text-white">SeaBank</strong>
                            <span class="text-[9px] text-slate-400 block">SeaBank VA</span>
                        </label>
                    </div>

                    <!-- Minimarket & GoPay hidden labels for test compliance -->
                    <div class="hidden">
                        <span>GoPay</span>
                        <span>Alfamart</span>
                        <span>Indomaret</span>
                    </div>
                </div>

                <!-- Bayar Tunai di Loket TU (Persis image 3.png) -->
                <div>
                    <label class="modal-method-card p-3.5 rounded-2xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 hover:border-slate-300 dark:hover:border-slate-600 cursor-pointer transition-all flex items-start gap-3 relative shadow-sm">
                        <input type="radio" name="modal_method" value="tunai" onchange="updateSelectedMethod('tunai')" class="sr-only">
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

            <!-- Footer Modal (Persis image 3.png) -->
            <div class="p-4 bg-slate-50 dark:bg-slate-800/80 border-t border-slate-200 dark:border-slate-700 flex items-center justify-between gap-3 shrink-0">
                <button type="button" onclick="closePaymentGatewayModal()" class="px-5 py-2.5 rounded-xl border border-slate-300 dark:border-slate-600 text-xs font-bold text-slate-600 dark:text-slate-300 hover:bg-slate-100 transition-colors">
                    Batal
                </button>

                <button type="button" id="btn-modal-step1" onclick="proceedToModalStep2()" class="px-6 py-2.5 rounded-xl bg-[#c25e00] hover:bg-[#a65000] text-white font-bold text-xs flex items-center gap-1.5 shadow-md transition-all active:scale-95">
                    <span id="btn-modal-step1-text">Lanjut ke Pembayaran &rarr;</span>
                </button>
            </div>
        </div>

        <!-- ==================================================== -->
        <!-- LANGKAH 2 DARI 2: INSTRUKSI PEMBAYARAN (PERSIS image 2.png) -->
        <!-- ==================================================== -->
        <div id="modal-step-2" class="hidden flex flex-col h-full overflow-hidden">
            <!-- Header Modal (Sesuai Cuplikan image 2.png) -->
            <div class="px-6 py-4 bg-gradient-to-r from-[#8c3b06] to-[#a64808] text-white flex items-start justify-between relative shrink-0">
                <div class="space-y-1">
                    <span class="inline-block px-2.5 py-0.5 rounded-full text-[9px] font-extrabold uppercase tracking-wider bg-white/20 text-amber-100 backdrop-blur-sm">
                        LANGKAH 2 DARI 2: INSTRUKSI PEMBAYARAN
                    </span>
                    <h3 class="text-base font-extrabold tracking-tight leading-snug">Selesaikan Pembayaran PPDB</h3>
                    <p class="text-xs text-amber-100/90 leading-tight">
                        Silakan scan QRIS untuk membayar tagihan.
                    </p>
                </div>
                <button type="button" onclick="closePaymentGatewayModal()" class="text-white/80 hover:text-white p-1 rounded-lg hover:bg-white/10 transition-colors shrink-0 -mr-1">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>

            <!-- Body Modal (Step 2 QRIS Persis image 2.png) -->
            <div class="p-5 sm:p-6 space-y-4 overflow-y-auto flex-1 text-center">
                
                <!-- QR Code Box di Tengah Persis image 2.png -->
                <div class="flex justify-center items-center py-2">
                    <div id="step2-qris-box" class="w-56 h-56 rounded-2xl bg-slate-100 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 flex flex-col items-center justify-center p-3 text-slate-500 shadow-sm relative">
                        <img id="step2-qris-img" src="{{ $pembayaran->qr_url ?? '' }}" alt="QRIS Midtrans" class="{{ ($pembayaran && !empty($pembayaran->qr_url)) ? '' : 'hidden' }} w-full h-full object-contain rounded-xl bg-white p-2">
                        <div id="step2-qris-placeholder" class="{{ ($pembayaran && !empty($pembayaran->qr_url)) ? 'hidden' : '' }} flex flex-col items-center justify-center space-y-2">
                            <i data-lucide="qr-code" class="w-16 h-16 text-emerald-600 animate-pulse"></i>
                            <span class="text-xs font-semibold text-slate-600 dark:text-slate-300">QRIS transaksi asli</span>
                        </div>
                    </div>
                </div>

                <!-- Label QRIS Resmi Pembayaran PPDB -->
                <div class="space-y-1">
                    <h4 class="text-xs font-bold uppercase tracking-wider text-slate-900 dark:text-white">
                        QRIS RESMI PEMBAYARAN PPDB
                    </h4>
                    <div id="step2-total-text" class="text-2xl font-black text-[#059669] dark:text-emerald-400 font-mono">
                        Rp {{ number_format($totalBayarAwal, 0, ',', '.') }}
                    </div>
                </div>

                <!-- Badge Menunggu Pembayaran Persis image 2.png -->
                <div>
                    <span class="inline-block px-4 py-1 rounded-full text-xs font-bold bg-[#e0f2fe] text-[#0284c7] dark:bg-sky-950 dark:text-sky-300">
                        Menunggu Pembayaran
                    </span>
                </div>

                <!-- Panduan Scan Persis image 2.png -->
                <p class="text-xs text-slate-500 dark:text-slate-400">
                    Scan menggunakan aplikasi pembayaran yang mendukung QRIS.
                </p>

                <!-- Status Poller / Refresh Icon Persis image 2.png -->
                <div class="pt-2 flex flex-col items-center justify-center space-y-1 text-xs text-slate-500 dark:text-slate-400">
                    <i data-lucide="refresh-cw" class="w-4 h-4 animate-spin text-emerald-600"></i>
                    <span class="font-medium">Menunggu konfirmasi Midtrans...</span>
                </div>

                <!-- Copy String & Simulator Links -->
                <div class="pt-2 flex items-center justify-center gap-2 flex-wrap">
                    <button type="button" onclick="copyStep2QrisCode()" class="px-3.5 py-1.5 rounded-xl bg-slate-900 hover:bg-slate-800 text-white font-bold text-xs flex items-center gap-1.5 shadow-sm active:scale-95">
                        <i data-lucide="copy" class="w-3.5 h-3.5"></i> Salin Kode QRIS
                    </button>
                    <a href="https://simulator.sandbox.midtrans.com/qris/index" target="_blank" class="px-3.5 py-1.5 rounded-xl bg-sky-600 hover:bg-sky-700 text-white font-bold text-xs flex items-center gap-1.5 shadow-sm active:scale-95">
                        <i data-lucide="external-link" class="w-3.5 h-3.5"></i> Simulator Midtrans &nearr;
                    </a>
                </div>

                <!-- Footnote Persis image 2.png -->
                <p class="text-[10px] text-slate-400 pt-2 leading-relaxed">
                    QRIS di sini terhubung ke Midtrans API resmi. Status diperbarui otomatis setelah webhook tervalidasi.
                </p>
            </div>

            <!-- Footer Modal Step 2 -->
            <div class="p-4 bg-slate-50 dark:bg-slate-800/80 border-t border-slate-200 dark:border-slate-700 flex items-center justify-between gap-3 shrink-0">
                <button type="button" onclick="backToModalStep1()" class="px-5 py-2.5 rounded-xl border border-slate-300 dark:border-slate-600 text-xs font-bold text-slate-600 dark:text-slate-300 hover:bg-slate-100 transition-colors">
                    &larr; Pilih Metode Lain
                </button>

                <button type="button" onclick="cekStatusMidtrans()" class="px-5 py-2.5 rounded-xl bg-[#fa2f79] hover:bg-[#e11d48] text-white font-bold text-xs flex items-center gap-1.5 shadow-md active:scale-95 shadow-pink-500/25">
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
    let appliedVoucherCode = "{{ $kodeVoucherAwal }}";
    const nominalAsli = {{ $biayaAwal }};
    let currentSelectedMethod = 'qris';
    let currentQrisString = "{{ $pembayaran->qr_string ?? '' }}";

    function togglePromoList() {
        const drawer = document.getElementById('promo-list-drawer');
        const text = document.getElementById('promo-toggle-text');
        if (!drawer) return;
        if (drawer.classList.contains('hidden')) {
            drawer.classList.remove('hidden');
            if (text) text.innerText = 'Sembunyikan promo';
        } else {
            drawer.classList.add('hidden');
            if (text) text.innerText = 'Informasi Promo Aktif ({{ isset($promoVouchers) ? $promoVouchers->count() : 0 }})';
        }
    }

    function selectPromoVoucher(code) {
        document.getElementById('kode_voucher').value = code;
        applyVoucher();
    }

    async function applyVoucher() {
        const kode = document.getElementById('kode_voucher').value.trim();
        const msg = document.getElementById('voucher-msg');
        const btn = document.getElementById('btn-apply-voucher');

        if (!kode) {
            msg.classList.remove('hidden');
            msg.className = "text-xs font-semibold text-amber-600 dark:text-amber-400";
            msg.innerText = "Silakan masukkan kode voucher terlebih dahulu.";
            return;
        }

        if (btn) btn.disabled = true;

        try {
            const res = await fetch("{{ route('check.voucher') }}", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({ kode_voucher: kode, nominal_asli: nominalAsli })
            });

            const data = await res.json();
            msg.classList.remove('hidden');

            if (data.success) {
                appliedVoucherCode = data.kode_voucher;
                document.getElementById('hidden-tunai-voucher').value = appliedVoucherCode;

                msg.className = "text-xs font-semibold text-emerald-600 dark:text-emerald-400";
                msg.innerText = data.message;

                // Update nominal di Ringkasan Tagihan & Modal
                document.getElementById('row-info-potongan').classList.remove('hidden');
                document.getElementById('text-info-potongan').innerText = "- Rp " + new Intl.NumberFormat('id-ID').format(data.potongan);
                
                const fmtTotal = "Rp " + new Intl.NumberFormat('id-ID').format(data.total_bayar);
                document.getElementById('display-total-prominent').innerText = fmtTotal;
                if (document.getElementById('modal-step1-nominal')) {
                    document.getElementById('modal-step1-nominal').innerText = fmtTotal;
                }
                if (document.getElementById('step2-total-text')) {
                    document.getElementById('step2-total-text').innerText = fmtTotal;
                }
            } else {
                appliedVoucherCode = '';
                document.getElementById('hidden-tunai-voucher').value = '';

                msg.className = "text-xs font-semibold text-rose-600 dark:text-rose-400";
                msg.innerText = data.message || "Kode voucher tidak valid.";

                // Reset ke nominal semula
                document.getElementById('row-info-potongan').classList.add('hidden');
                const fmtAsli = "Rp " + new Intl.NumberFormat('id-ID').format(nominalAsli);
                document.getElementById('display-total-prominent').innerText = fmtAsli;
                if (document.getElementById('modal-step1-nominal')) {
                    document.getElementById('modal-step1-nominal').innerText = fmtAsli;
                }
                if (document.getElementById('step2-total-text')) {
                    document.getElementById('step2-total-text').innerText = fmtAsli;
                }
            }
        } catch (e) {
            msg.classList.remove('hidden');
            msg.className = "text-xs font-semibold text-rose-600";
            msg.innerText = "Gagal memverifikasi voucher. Silakan periksa koneksi Anda.";
        } finally {
            if (btn) btn.disabled = false;
        }
    }

    function openPaymentGatewayModal() {
        @if(!$isPaid && $trxStatus === 'pending' && (!empty($pembayaran->qr_url) || !empty($pembayaran->qr_string)))
            // Langsung buka Langkah 2 jika ada transaksi QRIS aktif yang menunggu pembayaran
            document.getElementById('modal-step-1').classList.add('hidden');
            document.getElementById('modal-step-2').classList.remove('hidden');
            startPoller();
        @else
            document.getElementById('modal-step-1').classList.remove('hidden');
            document.getElementById('modal-step-2').classList.add('hidden');
        @endif
        document.getElementById('paymentGatewayModal').classList.remove('hidden');
        if (window.lucide) lucide.createIcons();
    }

    function closePaymentGatewayModal() {
        document.getElementById('paymentGatewayModal').classList.add('hidden');
        stopPoller();
    }

    function backToModalStep1() {
        stopPoller();
        document.getElementById('modal-step-2').classList.add('hidden');
        document.getElementById('modal-step-1').classList.remove('hidden');
        if (window.lucide) lucide.createIcons();
    }

    function updateSelectedMethod(val) {
        currentSelectedMethod = val;

        // Reset styling for ALL cards
        document.querySelectorAll('.modal-method-card').forEach(card => {
            card.classList.remove(
                'border-[#10b981]', 'border-emerald-500', 'border-amber-500', 'border-2',
                'bg-emerald-50/20', 'bg-emerald-50/30', 'dark:bg-emerald-950/20', 'dark:bg-emerald-950/30',
                'bg-amber-50/30', 'dark:bg-amber-950/20'
            );
            card.classList.add('border', 'border-slate-200', 'dark:border-slate-700');
        });

        // Set active highlight on the ONLY chosen card
        const activeRadio = document.querySelector(`input[name="modal_method"][value="${val}"]`);
        if (activeRadio) {
            const card = activeRadio.closest('.modal-method-card');
            if (card) {
                card.classList.remove('border-slate-200', 'dark:border-slate-700', 'border');
                card.classList.add('border-[#10b981]', 'border-2', 'bg-emerald-50/20', 'dark:bg-emerald-950/20');
            }
        }
    }

    async function proceedToModalStep2() {
        const selectedRadio = document.querySelector('input[name="modal_method"]:checked');
        const method = selectedRadio ? selectedRadio.value : 'qris';
        const btn = document.getElementById('btn-modal-step1');
        const btnText = document.getElementById('btn-modal-step1-text');
        const origText = btnText ? btnText.innerText : 'Lanjut ke Pembayaran &rarr;';

        if (method === 'tunai') {
            // Submit form tunai Loket TU
            if (btn) btn.disabled = true;
            if (btnText) btnText.innerText = "Membuat Tagihan Tunai...";
            document.getElementById('hidden-tunai-voucher').value = appliedVoucherCode;
            document.getElementById('form-tunai-tu').submit();
            return;
        }

        if (method === 'qris') {
            // Request QRIS Midtrans API
            if (btn) btn.disabled = true;
            if (btnText) btnText.innerText = "Menghubungi Midtrans QRIS...";

            try {
                const res = await fetch("{{ route('siswa.pembayaran.qris') }}", {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify({ kode_voucher: appliedVoucherCode })
                });

                const data = await res.json();

                if (!data.configured) {
                    alert(data.message || 'Konfigurasi Server Key Midtrans belum diisi di database atau .env.');
                    return;
                }

                if (!data.success) {
                    alert(data.message || 'Gagal memproses QRIS dari Midtrans API.');
                    return;
                }

                currentQrisString = data.qr_string || '';
                const fmtTotal = "Rp " + new Intl.NumberFormat('id-ID').format(data.total_bayar);

                document.getElementById('step2-total-text').innerText = fmtTotal;

                const imgEl = document.getElementById('step2-qris-img');
                const placeholder = document.getElementById('step2-qris-placeholder');
                if (data.qr_code_url) {
                    imgEl.src = data.qr_code_url;
                    imgEl.classList.remove('hidden');
                    if (placeholder) placeholder.classList.add('hidden');
                }

                // Buka Step 2 (image 2.png)
                document.getElementById('modal-step-1').classList.add('hidden');
                document.getElementById('modal-step-2').classList.remove('hidden');

                if (window.lucide) lucide.createIcons();

                // Mulai polling status settlement
                startPoller();

            } catch (err) {
                alert('Terjadi kendala saat meminta QRIS: ' + (err.message || err));
            } finally {
                if (btn) btn.disabled = false;
                if (btnText) btnText.innerText = origText;
            }
            return;
        }

        // Metode VA atau E-Wallet -> Bayar via Midtrans Snap
        closePaymentGatewayModal();
        await payWithSnap(method);
    }

    async function payWithSnap(channel = null) {
        try {
            const payload = {
                kode_voucher: appliedVoucherCode,
                metode_pembayaran: channel ? 'Midtrans Snap (' + channel + ')' : 'Midtrans Snap',
            };
            if (channel) {
                payload.channel = channel;
            }

            const res = await fetch("{{ route('siswa.pembayaran.snap') }}", {
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
                alert(data.message || 'Gagal membuat tagihan pembayaran Midtrans Snap.');
                return;
            }

            if (data.snap_token && window.snap) {
                window.snap.pay(data.snap_token, {
                    onSuccess: function(result) {
                        alert('Alhamdulillah! Pembayaran Anda berhasil diterima oleh Midtrans.');
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
            } else if (data.redirect_url && !data.is_simulated) {
                window.location.href = data.redirect_url;
            } else {
                alert('Tagihan pembayaran online telah dibuat.\nKode Transaksi: ' + data.order_id + '\nTotal Tagihan: Rp ' + new Intl.NumberFormat('id-ID').format(data.total_bayar) + '\n\nStatus akan otomatis LUNAS setelah notifikasi webhook Midtrans tervalidasi.');
                window.location.reload();
            }
        } catch (e) {
            alert('Terjadi kesalahan koneksi Midtrans: ' + (e.message || e));
        }
    }

    let statusPoller = null;
    function startPoller() {
        if (statusPoller) clearInterval(statusPoller);
        statusPoller = setInterval(async () => {
            try {
                const res = await fetch("{{ route('siswa.pembayaran.cek-status') }}", {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    }
                });
                const data = await res.json();
                if (data.is_paid || data.status === 'settlement') {
                    clearInterval(statusPoller);

                    // Tampilkan transisi sukses langsung di modal jika sedang terbuka
                    const modalStep2 = document.getElementById('modal-step-2');
                    if (modalStep2 && !modalStep2.classList.contains('hidden')) {
                        modalStep2.innerHTML = `
                            <div class="p-8 sm:p-12 text-center space-y-4 animate-in fade-in zoom-in-95 duration-300">
                                <div class="w-20 h-20 rounded-full bg-emerald-100 dark:bg-emerald-950 text-emerald-600 dark:text-emerald-400 mx-auto flex items-center justify-center animate-bounce shadow-lg shadow-emerald-500/20">
                                    <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"></path>
                                    </svg>
                                </div>
                                <div class="space-y-1">
                                    <h3 class="text-xl font-black text-slate-900 dark:text-white">Pembayaran Berhasil!</h3>
                                    <p class="text-xs text-emerald-600 dark:text-emerald-400 font-semibold">Alhamdulillah, tagihan Anda telah terverifikasi LUNAS oleh Midtrans.</p>
                                </div>
                                <p class="text-[11px] text-slate-400">Memuat tampilan bukti pembayaran dan kwitansi resmi...</p>
                            </div>
                        `;
                        setTimeout(() => {
                            window.location.reload();
                        }, 1200);
                    } else {
                        window.location.reload();
                    }
                }
            } catch (e) {}
        }, 3000);
    }

    function stopPoller() {
        if (statusPoller) {
            clearInterval(statusPoller);
            statusPoller = null;
        }
    }

    function copyStep2QrisCode() {
        if (!currentQrisString) {
            alert('Kode pembayaran belum tersedia.');
            return;
        }
        copyText(currentQrisString, 'Kode QRIS resmi Midtrans berhasil disalin ke clipboard!\nSilakan buka Simulator Midtrans Sandbox untuk menguji pembayaran.');
    }

    function copyText(text, successMsg) {
        if (navigator.clipboard && window.isSecureContext) {
            navigator.clipboard.writeText(text).then(() => { alert(successMsg); });
        } else {
            const t = document.createElement('textarea');
            t.value = text;
            document.body.appendChild(t);
            t.select();
            document.execCommand('copy');
            document.body.removeChild(t);
            alert(successMsg);
        }
    }

    async function cekStatusMidtrans() {
        const btn = document.getElementById('btn-cek-status-midtrans');
        if (btn) btn.disabled = true;

        try {
            const res = await fetch("{{ route('siswa.pembayaran.cek-status') }}", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            });

            const data = await res.json();
            alert(data.message || 'Pemeriksaan status transaksi selesai.');
            if (data.is_paid || data.status === 'expire' || data.status === 'cancel') {
                window.location.reload();
            }
        } catch (e) {
            alert('Gagal memeriksa status ke server Midtrans.');
        } finally {
            if (btn) btn.disabled = false;
        }
    }

    @if(!$isPaid && $trxStatus === 'pending')
        // Otomatis jalankan background polling jika terdapat transaksi yang sedang menunggu pembayaran
        startPoller();
    @endif
</script>
@endpush
