<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kwitansi Pembayaran PPDB - {{ $pendaftar->nama }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @media print {
            .no-print { display: none !important; }
            body { background: white !important; color: black !important; padding: 0 !important; }
            @page { size: A4 landscape; margin: 15mm; }
        }
    </style>
</head>
<body class="bg-slate-100 text-slate-900 font-sans p-4 md:p-8">

    <div class="max-w-2xl mx-auto mb-6 flex items-center justify-between no-print">
        <a href="{{ route('siswa.pembayaran') }}" class="px-4 py-2 rounded-xl bg-slate-200 text-slate-700 font-bold text-xs hover:bg-slate-300">
            &larr; Kembali ke Rincian Pembayaran
        </a>
        <button onclick="window.print()" class="px-6 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs shadow-md">
            Cetak Kwitansi Lunas (PDF)
        </button>
    </div>

    <!-- Kwitansi Container -->
    <div class="max-w-2xl mx-auto bg-white p-8 border-2 border-emerald-900 rounded-2xl shadow-xl text-xs space-y-5 relative overflow-hidden">
        <!-- Stamp "LUNAS" watermark in background -->
        <div class="absolute right-12 top-1/2 -translate-y-1/2 rotate-[-20deg] border-4 border-emerald-600/30 text-emerald-600/30 font-black text-5xl uppercase px-6 py-2 rounded-2xl pointer-events-none tracking-widest">
            LUNAS
        </div>

        <!-- Header -->
        <div class="border-b-2 border-slate-900 pb-3 flex items-center justify-between">
            <div>
                <h2 class="text-xs font-bold uppercase tracking-wider text-slate-600">KWITANSI TANDA TERIMA RESMI</h2>
                <h1 class="text-base font-extrabold uppercase text-slate-900">{{ $pengaturan->nama_sekolah }}</h1>
                <p class="text-[10px] text-slate-600">{{ $pengaturan->alamat }}, {{ $pengaturan->kota }}</p>
            </div>
            <div class="text-right">
                <span class="text-[10px] text-slate-500 uppercase block">No. Transaksi</span>
                <span class="font-mono font-bold text-sm text-emerald-800">{{ $pendaftar->pembayaran ? $pendaftar->pembayaran->kode_transaksi : '-' }}</span>
            </div>
        </div>

        <!-- Body Details -->
        <table class="w-full text-xs">
            <tr>
                <td class="w-40 py-1.5 font-bold text-slate-600">Telah Diterima Dari</td>
                <td class="font-bold uppercase text-slate-900">: {{ $pendaftar->nama }} (NISN: {{ $pendaftar->nisn }})</td>
            </tr>
            <tr>
                <td class="py-1.5 font-bold text-slate-600">Asal Sekolah</td>
                <td class="text-slate-900">: {{ $pendaftar->asal_sekolah }}</td>
            </tr>
            <tr>
                <td class="py-1.5 font-bold text-slate-600">Program Keahlian</td>
                <td class="font-semibold text-slate-900">: {{ $pendaftar->jurusan1 ? $pendaftar->jurusan1->nama_jurusan : '-' }}</td>
            </tr>
            <tr>
                <td class="py-1.5 font-bold text-slate-600">Untuk Pembayaran</td>
                <td class="text-slate-900">: Biaya Formulir Pendaftaran PPDB T.A {{ $pendaftar->gelombang ? $pendaftar->gelombang->tahun_ajaran : '2026/2027' }} ({{ $pendaftar->gelombang ? $pendaftar->gelombang->nama_gelombang : 'Gelombang 1' }})</td>
            </tr>
            <tr>
                <td class="py-1.5 font-bold text-slate-600">Metode Transaksi</td>
                <td class="font-medium text-slate-900">: {{ $pendaftar->pembayaran ? $pendaftar->pembayaran->metode_pembayaran : 'Midtrans' }}</td>
            </tr>
            @if($pendaftar->pembayaran && $pendaftar->pembayaran->potongan > 0)
            <tr>
                <td class="py-1.5 font-bold text-slate-600">Voucher Diskon</td>
                <td class="font-mono font-bold text-emerald-700">: {{ $pendaftar->pembayaran->voucher ? $pendaftar->pembayaran->voucher->kode_voucher : 'VOUCHER PROMO' }} (Potongan Rp {{ number_format($pendaftar->pembayaran->potongan, 0, ',', '.') }})</td>
            </tr>
            @endif
            <tr>
                <td class="py-1.5 font-bold text-slate-600">Status Transaksi</td>
                <td class="font-bold text-emerald-700">: LUNAS / SETTLEMENT (TERVERIFIKASI)</td>
            </tr>
        </table>

        <!-- Amount Box -->
        <div class="p-3 bg-emerald-50 border border-emerald-300 rounded-xl space-y-1">
            @if($pendaftar->pembayaran && $pendaftar->pembayaran->potongan > 0)
            <div class="flex items-center justify-between text-[11px] text-slate-600 border-b border-emerald-200 pb-1">
                <span>Biaya Formulir Awal:</span>
                <span class="font-mono">Rp {{ number_format($pendaftar->pembayaran->nominal, 0, ',', '.') }}</span>
            </div>
            <div class="flex items-center justify-between text-[11px] text-emerald-700 font-semibold border-b border-emerald-200 pb-1">
                <span>Potongan Diskon ({{ $pendaftar->pembayaran->voucher ? $pendaftar->pembayaran->voucher->kode_voucher : 'PROMO' }}):</span>
                <span class="font-mono">- Rp {{ number_format($pendaftar->pembayaran->potongan, 0, ',', '.') }}</span>
            </div>
            @endif
            <div class="flex items-center justify-between pt-1">
                <span class="text-xs font-bold text-emerald-900 uppercase">Jumlah Uang Dibayar:</span>
                <span class="text-xl font-extrabold text-emerald-800 font-mono">
                    Rp {{ number_format($pendaftar->pembayaran ? $pendaftar->pembayaran->total_bayar : 0, 0, ',', '.') }}
                </span>
            </div>
        </div>

