@extends('layouts.siswa')

@section('title', 'Pembayaran Biaya Formulir')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">

        <!-- Page Header -->
        <div class="flex items-center justify-between flex-wrap gap-4">
            <div>
                <a href="{{ route('siswa.dashboard') }}" class="text-xs font-semibold text-emerald-600 dark:text-emerald-400 hover:underline flex items-center gap-1 mb-1">
                    <i data-lucide="arrow-left" class="w-3.5 h-3.5"></i> Kembali ke Dashboard
                </a>
                <h1 class="text-2xl font-extrabold text-slate-900 dark:text-white">Pembayaran Formulir PPDB</h1>
                <p class="text-xs text-slate-500 dark:text-slate-400">Pilih metode pembayaran online instan via Midtrans (QRIS / VA) atau bayar tunai di Loket TU.</p>
            </div>
            <span class="px-3.5 py-1.5 rounded-full text-xs font-bold 
                @if($pendaftar->status_pembayaran === 'Paid') bg-emerald-100 text-emerald-800 dark:bg-emerald-950 dark:text-emerald-300
                @else bg-amber-100 text-amber-800 dark:bg-amber-950 dark:text-amber-300 @endif flex items-center gap-1.5">
                <i data-lucide="{{ $pendaftar->status_pembayaran === 'Paid' ? 'check-circle-2' : 'clock' }}" class="w-4 h-4"></i>
                Status: {{ $pendaftar->status_pembayaran === 'Paid' ? 'LUNAS' : 'MENUNGGU PEMBAYARAN' }}
            </span>
        </div>

        @if($pendaftar->status_pembayaran === 'Paid')
        <!-- Paid Notice Box -->
        <div class="p-6 md:p-8 rounded-3xl bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-800 text-emerald-900 dark:text-emerald-200 space-y-4">
            <div class="flex items-center gap-3">
                <div class="w-12 h-12 rounded-2xl bg-emerald-600 text-white flex items-center justify-center shrink-0 shadow-md">
                    <i data-lucide="check" class="w-7 h-7"></i>
                </div>
                <div>
                    <h3 class="text-lg font-bold">Alhamdulillah, Pembayaran Anda Telah Lunas!</h3>
                    <p class="text-xs text-emerald-700 dark:text-emerald-300">Biaya formulir pendaftaran telah diverifikasi oleh sistem secara resmi.</p>
                </div>
            </div>

            <div class="p-4 rounded-2xl bg-white dark:bg-slate-900/60 border border-emerald-200 dark:border-emerald-800 text-xs text-slate-700 dark:text-slate-300 grid grid-cols-1 sm:grid-cols-3 gap-3">
                <div>
                    <span class="text-slate-400 block text-[10px] uppercase">Kode Transaksi</span>
                    <strong class="font-mono text-slate-900 dark:text-white">{{ $pembayaran ? $pembayaran->kode_transaksi : '-' }}</strong>
                </div>
                <div>
                    <span class="text-slate-400 block text-[10px] uppercase">Metode Pembayaran</span>
                    <strong class="text-slate-900 dark:text-white">{{ $pembayaran ? $pembayaran->metode_pembayaran : 'Midtrans' }}</strong>
                </div>
                <div>
                    <span class="text-slate-400 block text-[10px] uppercase">Waktu Pembayaran</span>
                    <strong class="text-slate-900 dark:text-white">{{ $pembayaran && $pembayaran->waktu_transaksi ? $pembayaran->waktu_transaksi->translatedFormat('d F Y H:i') : '-' }}</strong>
                </div>
            </div>

            <div class="pt-2 flex flex-wrap gap-3">
                <a href="{{ route('siswa.cetak.kwitansi') }}" target="_blank" class="px-5 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs shadow-md transition-all flex items-center gap-2">
                    <i data-lucide="printer" class="w-4 h-4"></i> Cetak Kwitansi Pembayaran Resmi (PDF)
                </a>
                <a href="{{ route('siswa.berkas') }}" class="px-5 py-2.5 rounded-xl bg-slate-900 hover:bg-slate-800 dark:bg-slate-700 text-white font-bold text-xs transition-colors flex items-center gap-2">
                    <i data-lucide="arrow-right" class="w-4 h-4"></i> Lanjut ke Unggah Berkas
                </a>
            </div>
        </div>
        @else

        <!-- Tagihan & Voucher Card -->
        <div class="p-6 md:p-8 rounded-3xl bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 shadow-sm space-y-6">
            <h2 class="text-base font-bold text-slate-900 dark:text-white pb-3 border-b border-slate-100 dark:border-slate-700 flex items-center gap-2">
                <i data-lucide="receipt" class="w-5 h-5 text-emerald-600"></i>
                Rincian Tagihan Pendaftaran
            </h2>

            <div class="space-y-3 text-xs md:text-sm">
                <div class="flex items-center justify-between py-1 text-slate-600 dark:text-slate-400">
                    <span>Gelombang Pendaftaran</span>
                    <strong class="text-slate-900 dark:text-white">{{ $gelombang->nama_gelombang }} ({{ $gelombang->tahun_ajaran }})</strong>
                </div>
                <div class="flex items-center justify-between py-1 text-slate-600 dark:text-slate-400">
                    <span>Nama Calon Siswa</span>
                    <strong class="text-slate-900 dark:text-white">{{ $pendaftar->nama }} ({{ $pendaftar->nisn }})</strong>
                </div>
                <div class="flex items-center justify-between py-1 text-slate-600 dark:text-slate-400">
                    <span>Pilihan Jurusan</span>
                    <strong class="text-slate-900 dark:text-white">{{ $pendaftar->jurusan1 ? $pendaftar->jurusan1->nama_jurusan : '-' }}</strong>
                </div>
                <div class="flex items-center justify-between py-1 text-slate-600 dark:text-slate-400">
                    <span>Biaya Formulir Standar</span>
                    <span class="font-bold text-slate-900 dark:text-white">Rp {{ number_format($gelombang->biaya_pendaftaran, 0, ',', '.') }}</span>
                </div>
                <div id="voucher-row" class="hidden flex items-center justify-between py-1 text-emerald-600 dark:text-emerald-400 font-semibold">
                    <span>Potongan Beasiswa / Voucher</span>
                    <span id="voucher-potongan">- Rp 0</span>
                </div>
                <div class="pt-3 border-t border-slate-200 dark:border-slate-700 flex items-center justify-between text-sm md:text-base font-extrabold text-slate-900 dark:text-white">
                    <span>Total Yang Harus Dibayar:</span>
                    <span class="text-xl md:text-2xl text-emerald-600 dark:text-emerald-400 font-mono" id="total-bayar-text">
                        Rp {{ number_format($gelombang->biaya_pendaftaran, 0, ',', '.') }}
                    </span>
                </div>
            </div>

            <!-- Voucher Code Box -->
            <div class="p-4 rounded-2xl bg-slate-50 dark:bg-slate-900/60 border border-slate-200 dark:border-slate-700 space-y-2">
                <label for="kode_voucher" class="block text-xs font-bold text-slate-700 dark:text-slate-300">Punya Kode Voucher Beasiswa Tahfiz / Diskon?</label>
                <div class="flex gap-2">
                    <input type="text" id="kode_voucher" placeholder="Contoh: TAHFIZJUARA atau DISMP25" class="w-full px-3.5 py-2.5 rounded-xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-xs uppercase font-mono tracking-wider focus:outline-none focus:border-emerald-500">
                    <button type="button" onclick="applyVoucher()" class="px-5 py-2.5 rounded-xl bg-slate-900 hover:bg-slate-800 dark:bg-slate-700 dark:hover:bg-slate-600 text-white font-bold text-xs shrink-0 transition-colors">
                        Gunakan
                    </button>
                </div>
                <div id="voucher-msg" class="text-[11px] hidden font-semibold"></div>
            </div>

            <!-- Pilihan Metode Pembayaran -->
            <div class="pt-4 space-y-4">
                <h3 class="text-xs font-bold text-slate-500 uppercase tracking-wider">Pilih Jalur Pembayaran:</h3>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <!-- Option 1: Payment Gateway Midtrans (QRIS / Virtual Account) -->
                    <div class="p-5 rounded-3xl border-2 border-emerald-500 bg-emerald-50/40 dark:bg-emerald-950/20 space-y-4 flex flex-col justify-between shadow-sm hover:border-emerald-600 transition-colors">
                        <div class="space-y-2">
                            <div class="flex items-center justify-between">
                                <div class="w-10 h-10 rounded-2xl bg-emerald-600 text-white flex items-center justify-center shadow-md shadow-emerald-600/30">
                                    <i data-lucide="zap" class="w-5 h-5"></i>
                                </div>
                                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase bg-emerald-100 text-emerald-800 dark:bg-emerald-900/60 dark:text-emerald-300">
                                    Instan & Otomatis
                                </span>
                            </div>
                            <h4 class="text-sm font-bold text-slate-900 dark:text-white">Payment Gateway Online (Midtrans)</h4>
                            <p class="text-xs text-slate-500 dark:text-slate-400 leading-relaxed">
                                Bayar langsung terverifikasi otomatis dalam detik menggunakan <strong>QRIS</strong> (GoPay, OVO, Dana, ShopeePay) atau <strong>Virtual Account Bank</strong> (BCA, BRI, Mandiri, BNI).
                            </p>
                        </div>

                        <button type="button" onclick="openPaymentGatewayModal()" class="w-full py-3 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs shadow-md shadow-emerald-600/30 transition-all flex items-center justify-center gap-2">
                            <i data-lucide="credit-card" class="w-4 h-4"></i> Buka Pembayaran Online (Midtrans)
                        </button>
                    </div>

                    <!-- Option 2: Cash at TU Counter -->
                    <div class="p-5 rounded-3xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/40 space-y-4 flex flex-col justify-between shadow-sm">
                        <div class="space-y-2">
                            <div class="flex items-center justify-between">
                                <div class="w-10 h-10 rounded-2xl bg-slate-800 text-white flex items-center justify-center">
                                    <i data-lucide="banknote" class="w-5 h-5"></i>
                                </div>
                                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase bg-slate-200 text-slate-700 dark:bg-slate-700 dark:text-slate-300">
                                    Loket Fisik
                                </span>
                            </div>
                            <h4 class="text-sm font-bold text-slate-900 dark:text-white">Bayar Tunai di Loket TU</h4>
                            <p class="text-xs text-slate-500 dark:text-slate-400 leading-relaxed">
                                Buat kode tagihan resmi untuk dibayarkan langsung secara tunai di kasir Tata Usaha kampus SMK Islam Bina Khalifah Bangsa.
                            </p>
                        </div>

                        <form action="{{ route('siswa.pembayaran.tunai') }}" method="POST">
                            @csrf
                            <button type="submit" class="w-full py-3 rounded-xl border border-slate-300 dark:border-slate-600 text-slate-800 dark:text-slate-200 font-bold text-xs hover:bg-slate-200 dark:hover:bg-slate-700 transition-colors flex items-center justify-center gap-2">
                                <i data-lucide="receipt" class="w-4 h-4"></i> Buat Tagihan Tunai Loket
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        @endif

    </div>
</div>

<!-- ======================================================== -->
<!-- MODAL MIDTRANS SNAP / PAYMENT GATEWAY SIMULATOR -->
<!-- ======================================================== -->
<div id="paymentModal" class="fixed inset-0 z-50 bg-slate-950/75 backdrop-blur-md hidden flex items-center justify-center p-4">
    <div class="bg-white dark:bg-slate-900 rounded-3xl max-w-lg w-full border border-slate-200 dark:border-slate-800 shadow-2xl overflow-hidden animate-in fade-in zoom-in-95 duration-200 flex flex-col max-h-[90vh]">
        
        <!-- Modal Header (Midtrans Brand Style) -->
        <div class="px-6 py-4 bg-slate-900 text-white flex items-center justify-between border-b border-slate-800">
            <div class="flex items-center gap-2.5">
                <div class="w-7 h-7 rounded-lg bg-sky-500 text-white flex items-center justify-center font-bold text-xs">
                    M
                </div>
                <div>
                    <h3 class="text-sm font-extrabold tracking-tight">Midtrans Payment Gateway</h3>
                    <span class="text-[10px] text-sky-400 font-medium">Sandbox Simulator PPDB 2026</span>
                </div>
            </div>
            <button onclick="closePaymentGatewayModal()" class="text-slate-400 hover:text-white transition-colors">
                <i data-lucide="x" class="w-5 h-5"></i>
            </button>
        </div>

        <!-- Modal Body (Scrollable) -->
        <div class="p-6 space-y-5 overflow-y-auto flex-1">
            
            <!-- Amount & Expiry Timer Banner -->
            <div class="p-4 rounded-2xl bg-slate-50 dark:bg-slate-800/60 border border-slate-200 dark:border-slate-700 flex items-center justify-between">
                <div>
                    <span class="text-[10px] uppercase font-bold text-slate-400 block">Total Tagihan</span>
                    <span id="modal-total-bayar" class="text-lg sm:text-xl font-black text-slate-900 dark:text-white font-mono">
                        Rp {{ number_format($gelombang->biaya_pendaftaran, 0, ',', '.') }}
                    </span>
                </div>
                <div class="text-right">
                    <span class="text-[10px] uppercase font-bold text-slate-400 block">Sisa Waktu Bayar</span>
                    <span class="text-xs font-mono font-bold text-rose-500 flex items-center gap-1 justify-end">
                        <i data-lucide="clock" class="w-3.5 h-3.5"></i> 14:59
                    </span>
                </div>
            </div>

            <!-- Tab Channels: QRIS vs Virtual Account -->
            <div>
                <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-2">Pilih Saluran Pembayaran:</label>
                <div class="grid grid-cols-2 gap-2 text-xs font-bold">
                    <button type="button" id="tab-qris" onclick="switchChannel('qris')" class="py-2.5 px-3 rounded-xl border-2 border-emerald-500 bg-emerald-50 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-300 flex items-center justify-center gap-1.5 transition-all">
                        <i data-lucide="qr-code" class="w-4 h-4"></i> QRIS Instan
                    </button>
                    <button type="button" id="tab-va" onclick="switchChannel('va')" class="py-2.5 px-3 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-600 dark:text-slate-300 flex items-center justify-center gap-1.5 transition-all">
                        <i data-lucide="landmark" class="w-4 h-4"></i> Virtual Account
                    </button>
                </div>
            </div>

            <!-- CHANNEL 1: QRIS VIEW -->
            <div id="channel-qris" class="space-y-4 text-center">
                <div class="p-4 bg-white rounded-2xl border-2 border-dashed border-slate-300 inline-block shadow-inner">
                    <!-- Simulated Dynamic QRIS SVG -->
                    <svg class="w-48 h-48 mx-auto" viewBox="0 0 100 100" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <rect width="100" height="100" fill="white"/>
                        <rect x="10" y="10" width="25" height="25" fill="#047857"/>
                        <rect x="15" y="15" width="15" height="15" fill="white"/>
                        <rect x="18" y="18" width="9" height="9" fill="#047857"/>
                        <rect x="65" y="10" width="25" height="25" fill="#047857"/>
                        <rect x="70" y="15" width="15" height="15" fill="white"/>
                        <rect x="73" y="18" width="9" height="9" fill="#047857"/>
                        <rect x="10" y="65" width="25" height="25" fill="#047857"/>
                        <rect x="15" y="70" width="15" height="15" fill="white"/>
                        <rect x="18" y="73" width="9" height="9" fill="#047857"/>
                        <rect x="42" y="12" width="6" height="6" fill="#047857"/>
                        <rect x="52" y="22" width="6" height="6" fill="#047857"/>
                        <rect x="40" y="40" width="20" height="20" fill="#065f46" rx="4"/>
                        <text x="50" y="53" text-anchor="middle" font-size="7" fill="white" font-weight="bold">QRIS</text>
                        <rect x="42" y="68" width="6" height="6" fill="#047857"/>
                        <rect x="52" y="78" width="6" height="6" fill="#047857"/>
                        <rect x="68" y="42" width="6" height="6" fill="#047857"/>
                        <rect x="78" y="52" width="6" height="6" fill="#047857"/>
                        <rect x="80" y="75" width="8" height="8" fill="#047857"/>
                    </svg>
                    <span class="text-[10px] font-bold text-slate-500 uppercase tracking-widest block mt-1">Scan via Aplikasi E-Wallet / Bank</span>
                </div>
                <div class="text-[11px] text-slate-500 dark:text-slate-400 space-y-1">
                    <p>Mendukung: <strong>GoPay, OVO, DANA, ShopeePay, LinkAja, BCA Mobile, Livin Mandiri, BRImo</strong></p>
                    <p class="text-[10px] text-emerald-600 dark:text-emerald-400 font-semibold">Status terverifikasi otomatis dalam 1-3 detik setelah scan.</p>
                </div>
            </div>

            <!-- CHANNEL 2: VIRTUAL ACCOUNT VIEW -->
            <div id="channel-va" class="hidden space-y-3">
                <div class="space-y-2">
                    <label class="block text-[11px] font-bold text-slate-600 dark:text-slate-300">Pilih Bank Virtual Account:</label>
                    <select id="select-bank-va" onchange="updateVANumber()" class="w-full px-3 py-2 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-xs font-semibold">
                        <option value="BCA">BCA Virtual Account</option>
                        <option value="BRI">BRI Virtual Account (BRIVA)</option>
                        <option value="Mandiri">Mandiri Bill Payment</option>
                        <option value="BNI">BNI Virtual Account</option>
                    </select>
                </div>

                <div class="p-4 rounded-2xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 space-y-1.5">
                    <span class="text-[10px] text-slate-400 uppercase font-bold block" id="va-bank-label">Nomor BCA Virtual Account</span>
                    <div class="flex items-center justify-between">
                        <span id="va-number" class="text-base font-black font-mono text-slate-900 dark:text-white">
                            12800{{ $pendaftar->nisn }}
                        </span>
                        <button type="button" onclick="copyVA()" class="px-2.5 py-1 rounded-lg bg-emerald-100 text-emerald-700 dark:bg-emerald-950 dark:text-emerald-400 text-xs font-bold hover:bg-emerald-200 transition-colors">
                            Salin
                        </button>
                    </div>
                </div>

                <ul class="text-[11px] text-slate-500 dark:text-slate-400 space-y-1 list-disc list-inside">
                    <li>Masukkan nomor Virtual Account pada menu Transfer VA di m-Banking/ATM Anda.</li>
                    <li>Total nominal tagihan akan muncul otomatis sesuai rincian.</li>
                </ul>
            </div>

        </div>

        <!-- Modal Footer: Action Buttons -->
        <div class="p-4 bg-slate-50 dark:bg-slate-800/80 border-t border-slate-200 dark:border-slate-700 flex items-center justify-between gap-3">
            <button type="button" onclick="closePaymentGatewayModal()" class="px-4 py-2.5 rounded-xl border border-slate-300 dark:border-slate-600 text-xs font-bold text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700">
                Batal
            </button>

            <!-- Form Simulation Submit -->
            <form id="form-midtrans-simulator" action="{{ route('siswa.pembayaran.midtrans') }}" method="POST">
                @csrf
                <input type="hidden" name="kode_voucher" id="sim-kode-voucher" value="">
                <input type="hidden" name="metode_pembayaran" id="sim-metode-pembayaran" value="QRIS (Midtrans Sandbox)">
                
                <button type="submit" class="px-5 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs flex items-center gap-2 shadow-md shadow-emerald-600/30 transition-all">
                    <i data-lucide="check-check" class="w-4 h-4"></i>
                    Simulasikan Bayar Berhasil (Sandbox)
                </button>
            </form>
        </div>

    </div>
@endsection

@push('scripts')
<script>
    let appliedVoucherCode = '';
    let currentChannel = 'qris';

    async function applyVoucher() {
        const kode = document.getElementById('kode_voucher').value.trim();
        const msg = document.getElementById('voucher-msg');
        const nominalAsli = {{ $gelombang->biaya_pendaftaran }};

        if (!kode) return;

        try {
            const res = await fetch("{{ route('check.voucher') }}", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({ kode_voucher: kode, nominal_asli: nominalAsli })
            });

            const data = await res.json();
            msg.classList.remove('hidden');

            if (data.success) {
                appliedVoucherCode = kode;
                document.getElementById('sim-kode-voucher').value = kode;
                msg.className = "text-[11px] text-emerald-600 dark:text-emerald-400 font-semibold";
                msg.innerText = data.message;
                document.getElementById('voucher-row').classList.remove('hidden');
                document.getElementById('voucher-potongan').innerText = "- Rp " + new Intl.NumberFormat('id-ID').format(data.potongan);
                document.getElementById('total-bayar-text').innerText = "Rp " + new Intl.NumberFormat('id-ID').format(data.total_bayar);
                document.getElementById('modal-total-bayar').innerText = "Rp " + new Intl.NumberFormat('id-ID').format(data.total_bayar);
            } else {
                appliedVoucherCode = '';
                document.getElementById('sim-kode-voucher').value = '';
                msg.className = "text-[11px] text-rose-600 dark:text-rose-400 font-semibold";
                msg.innerText = data.message;
            }
        } catch (e) {
            msg.classList.remove('hidden');
            msg.className = "text-[11px] text-rose-600 font-semibold";
            msg.innerText = "Gagal memverifikasi voucher.";
        }
    }

    function openPaymentGatewayModal() {
        document.getElementById('paymentModal').classList.remove('hidden');
    }

    function closePaymentGatewayModal() {
        document.getElementById('paymentModal').classList.add('hidden');
    }

    function switchChannel(channel) {
        currentChannel = channel;
        const qrisView = document.getElementById('channel-qris');
        const vaView = document.getElementById('channel-va');
        const tabQris = document.getElementById('tab-qris');
        const tabVa = document.getElementById('tab-va');

        if (channel === 'qris') {
            qrisView.classList.remove('hidden');
            vaView.classList.add('hidden');
            tabQris.className = "py-2.5 px-3 rounded-xl border-2 border-emerald-500 bg-emerald-50 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-300 flex items-center justify-center gap-1.5 transition-all";
            tabVa.className = "py-2.5 px-3 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-600 dark:text-slate-300 flex items-center justify-center gap-1.5 transition-all";
            document.getElementById('sim-metode-pembayaran').value = "QRIS (Midtrans Sandbox)";
        } else {
            qrisView.classList.add('hidden');
            vaView.classList.remove('hidden');
            tabVa.className = "py-2.5 px-3 rounded-xl border-2 border-emerald-500 bg-emerald-50 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-300 flex items-center justify-center gap-1.5 transition-all";
            tabQris.className = "py-2.5 px-3 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-600 dark:text-slate-300 flex items-center justify-center gap-1.5 transition-all";
            updateVANumber();
        }
    }

    function updateVANumber() {
        const bank = document.getElementById('select-bank-va').value;
        const nisn = "{{ $pendaftar->nisn }}";
        const label = document.getElementById('va-bank-label');
        const num = document.getElementById('va-number');

        if (bank === 'BCA') {
            label.innerText = "Nomor BCA Virtual Account";
            num.innerText = "12800" + nisn;
            document.getElementById('sim-metode-pembayaran').value = "BCA Virtual Account (Midtrans)";
        } else if (bank === 'BRI') {
            label.innerText = "Nomor BRI Virtual Account (BRIVA)";
            num.innerText = "26215" + nisn;
            document.getElementById('sim-metode-pembayaran').value = "BRIVA (Midtrans)";
        } else if (bank === 'Mandiri') {
            label.innerText = "Kode Perusahaan & Bill Key Mandiri";
            num.innerText = "70012 " + nisn;
            document.getElementById('sim-metode-pembayaran').value = "Mandiri Bill (Midtrans)";
        } else {
            label.innerText = "Nomor BNI Virtual Account";
            num.innerText = "8808" + nisn;
            document.getElementById('sim-metode-pembayaran').value = "BNI Virtual Account (Midtrans)";
        }
    }

    function copyVA() {
        const text = document.getElementById('va-number').innerText;
        navigator.clipboard.writeText(text);
        alert('Nomor Virtual Account ' + text + ' berhasil disalin!');
    }
</script>
@endpush
