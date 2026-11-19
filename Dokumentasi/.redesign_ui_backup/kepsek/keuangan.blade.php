@extends('layouts.admin')

@section('title', 'Ringkasan Keuangan PPDB')
@section('header_title', 'Ringkasan Keuangan & Pembayaran PPDB')

@section('content')
<div class="space-y-6">

    <!-- Header Banner -->
    <div class="bg-gradient-to-r from-[#0B182F] via-[#112240] to-[#0B182F] rounded-2xl p-6 sm:p-8 text-white shadow-xl relative overflow-hidden border border-slate-800">
        <div class="relative z-10 max-w-2xl">
            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-rose-500/20 text-rose-300 border border-rose-500/30 mb-3">
                <i data-lucide="wallet" class="w-3.5 h-3.5"></i>
                Pengawasan Keuangan Eksekutif
            </span>
            <h2 class="text-xl sm:text-2xl font-black tracking-tight leading-tight text-white">
                Rekapitulasi Finansial PPDB 2026/2027
            </h2>
            <p class="text-xs sm:text-sm text-slate-300 mt-2 leading-relaxed">
                Pantau penerimaan biaya formulir pendaftaran secara transparan, akuntabel, dan terverifikasi antara pembayaran tunai kasir TU dan gateway online otomatis Midtrans.
            </p>
        </div>
        <div class="absolute -right-8 -bottom-8 w-60 h-60 bg-rose-500/10 rounded-full blur-3xl pointer-events-none"></div>
    </div>

    <!-- 4 KPI Metrics Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- Total Pendapatan Bersih Lunas -->
        <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 p-5 shadow-sm relative overflow-hidden group hover:border-rose-200 dark:hover:border-rose-900 transition-all">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold text-slate-500 dark:text-slate-400">Total Masuk (Lunas)</span>
                <div class="w-10 h-10 rounded-xl bg-rose-50 text-rose-600 dark:bg-rose-950/60 dark:text-rose-400 flex items-center justify-center border border-rose-100 dark:border-rose-900/50">
                    <i data-lucide="badge-dollar-sign" class="w-5 h-5"></i>
                </div>
            </div>
            <div class="mt-3">
                <span class="text-2xl font-black text-slate-900 dark:text-white">Rp {{ number_format($totalPendapatan, 0, ',', '.') }}</span>
            </div>
            <div class="mt-2 text-[11px] text-slate-500 dark:text-slate-400 flex items-center gap-1.5">
                <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                Dari <strong class="text-slate-700 dark:text-slate-300">{{ $totalTransaksiLunas }}</strong> transaksi terverifikasi
            </div>
        </div>

        <!-- Pembayaran Tunai Loket TU -->
        <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 p-5 shadow-sm">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold text-slate-500 dark:text-slate-400">Tunai (Loket TU)</span>
                <div class="w-10 h-10 rounded-xl bg-amber-50 text-amber-600 dark:bg-amber-950/60 dark:text-amber-400 flex items-center justify-center border border-amber-100 dark:border-amber-900/50">
                    <i data-lucide="banknote" class="w-5 h-5"></i>
                </div>
            </div>
            <div class="mt-3">
                <span class="text-2xl font-black text-slate-900 dark:text-white">Rp {{ number_format($pendapatanTunai, 0, ',', '.') }}</span>
            </div>
            <div class="mt-2 text-[11px] text-slate-500 dark:text-slate-400">
                Penerimaan tunai langsung kasir sekolah
            </div>
        </div>

        <!-- Pembayaran Online Midtrans -->
        <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 p-5 shadow-sm">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold text-slate-500 dark:text-slate-400">Online (Midtrans)</span>
                <div class="w-10 h-10 rounded-xl bg-sky-50 text-sky-600 dark:bg-sky-950/60 dark:text-sky-400 flex items-center justify-center border border-sky-100 dark:border-sky-900/50">
                    <i data-lucide="credit-card" class="w-5 h-5"></i>
                </div>
            </div>
            <div class="mt-3">
                <span class="text-2xl font-black text-slate-900 dark:text-white">Rp {{ number_format($pendapatanOnline, 0, ',', '.') }}</span>
            </div>
            <div class="mt-2 text-[11px] text-slate-500 dark:text-slate-400">
                QRIS, VA Bank & E-Wallet terintegrasi
            </div>
        </div>

        <!-- Potongan Beasiswa / Voucher -->
        <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 p-5 shadow-sm">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold text-slate-500 dark:text-slate-400">Subsidi Voucher</span>
                <div class="w-10 h-10 rounded-xl bg-purple-50 text-purple-600 dark:bg-purple-950/60 dark:text-purple-400 flex items-center justify-center border border-purple-100 dark:border-purple-900/50">
                    <i data-lucide="ticket" class="w-5 h-5"></i>
                </div>
            </div>
            <div class="mt-3">
                <span class="text-2xl font-black text-purple-600 dark:text-purple-400">Rp {{ number_format($totalPotongan, 0, ',', '.') }}</span>
            </div>
            <div class="mt-2 text-[11px] text-slate-500 dark:text-slate-400">
                Total keringanan biaya peserta
            </div>
        </div>
    </div>

    <!-- Ringkasan Per Gelombang & Jurusan -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Berdasarkan Gelombang -->
        <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 p-5 shadow-sm">
            <h3 class="text-sm font-bold text-slate-800 dark:text-white flex items-center gap-2 mb-4">
                <div class="w-7 h-7 rounded-lg bg-rose-50 dark:bg-rose-950/50 text-rose-500 flex items-center justify-center">
                    <i data-lucide="calendar" class="w-4 h-4"></i>
                </div>
                Pendapatan per Gelombang
            </h3>
            <div class="space-y-3">
                @forelse($gelombangStats as $g)
                <div class="p-3.5 rounded-xl bg-slate-50 dark:bg-slate-800/60 border border-slate-100 dark:border-slate-800 flex items-center justify-between">
                    <div>
                        <span class="text-xs font-bold text-slate-800 dark:text-white">{{ $g->nama_gelombang }}</span>
                        <div class="text-[11px] text-slate-400 mt-0.5">
                            Tarif: Rp {{ number_format($g->biaya_pendaftaran, 0, ',', '.') }} &bull; {{ $g->transaksi_lunas_count }} pendaftar lunas
                        </div>
                    </div>
                    <span class="text-xs font-extrabold text-rose-600 dark:text-rose-400">
                        Rp {{ number_format($g->total_nominal, 0, ',', '.') }}
                    </span>
                </div>
                @empty
                <p class="text-xs text-slate-400 italic">Belum ada data gelombang.</p>
                @endforelse
            </div>
        </div>

        <!-- Berdasarkan Jurusan Pilihan 1 -->
        <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 p-5 shadow-sm">
            <h3 class="text-sm font-bold text-slate-800 dark:text-white flex items-center gap-2 mb-4">
                <div class="w-7 h-7 rounded-lg bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 flex items-center justify-center">
                    <i data-lucide="book-open" class="w-4 h-4"></i>
                </div>
                Pendapatan per Program Keahlian
            </h3>
            <div class="space-y-3">
                @forelse($jurusanStats as $j)
                <div class="p-3.5 rounded-xl bg-slate-50 dark:bg-slate-800/60 border border-slate-100 dark:border-slate-800 flex items-center justify-between">
                    <div>
                        <span class="text-xs font-bold text-slate-800 dark:text-white">{{ $j->nama_jurusan }} ({{ $j->kode_jurusan }})</span>
                        <div class="text-[11px] text-slate-400 mt-0.5">
                            {{ $j->pendaftar_lunas_count }} calon siswa terverifikasi lunas
                        </div>
                    </div>
                    <span class="text-xs font-extrabold text-slate-900 dark:text-white">
                        Rp {{ number_format($j->total_lunas, 0, ',', '.') }}
                    </span>
                </div>
                @empty
                <p class="text-xs text-slate-400 italic">Belum ada data jurusan.</p>
                @endforelse
            </div>
        </div>
    </div>

    <!-- Filter & Pencarian Transaksi -->
    <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 p-5 shadow-sm">
        <form method="GET" action="{{ route('kepsek.keuangan') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3">
            <div>
                <label class="block text-[11px] font-bold text-slate-600 dark:text-slate-400 mb-1">Cari Pendaftar / Kode</label>
                <input type="text" name="q" value="{{ request('q') }}" placeholder="No. TRX, Nama, NISN..." class="w-full text-xs px-3.5 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-slate-800 dark:text-white focus:outline-none focus:ring-2 focus:ring-rose-500">
            </div>
            <div>
                <label class="block text-[11px] font-bold text-slate-600 dark:text-slate-400 mb-1">Metode Bayar</label>
                <select name="tipe_bayar" class="w-full text-xs px-3.5 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-slate-800 dark:text-white focus:outline-none focus:ring-2 focus:ring-rose-500">
                    <option value="">Semua Metode</option>
                    <option value="tunai" {{ request('tipe_bayar') === 'tunai' ? 'selected' : '' }}>Tunai Loket</option>
                    <option value="midtrans" {{ request('tipe_bayar') === 'midtrans' ? 'selected' : '' }}>Midtrans Online</option>
                </select>
            </div>
            <div>
                <label class="block text-[11px] font-bold text-slate-600 dark:text-slate-400 mb-1">Status Transaksi</label>
                <select name="status" class="w-full text-xs px-3.5 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-slate-800 dark:text-white focus:outline-none focus:ring-2 focus:ring-rose-500">
                    <option value="">Semua Status</option>
                    <option value="settlement" {{ request('status') === 'settlement' ? 'selected' : '' }}>Lunas (Settlement)</option>
                    <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending</option>
                    <option value="expire" {{ request('status') === 'expire' ? 'selected' : '' }}>Kedaluwarsa</option>
                </select>
            </div>
            <div>
                <label class="block text-[11px] font-bold text-slate-600 dark:text-slate-400 mb-1">Gelombang</label>
                <select name="gelombang" class="w-full text-xs px-3.5 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-slate-800 dark:text-white focus:outline-none focus:ring-2 focus:ring-rose-500">
                    <option value="">Semua Gelombang</option>
                    @foreach($daftarGelombang as $g)
                    <option value="{{ $g->id_gelombang }}" {{ request('gelombang') == $g->id_gelombang ? 'selected' : '' }}>{{ $g->nama_gelombang }}</option>
                    @endforeach
                </select>
            </div>
            <div class="flex items-end gap-2">
                <button type="submit" class="w-full text-xs font-bold px-4 py-2.5 bg-rose-500 hover:bg-rose-600 text-white rounded-xl transition-all shadow-md shadow-rose-500/20 flex items-center justify-center gap-1.5">
                    <i data-lucide="filter" class="w-3.5 h-3.5"></i> Filter
                </button>
                <a href="{{ route('kepsek.keuangan') }}" class="text-xs px-3.5 py-2.5 bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-600 dark:text-slate-300 rounded-xl transition-colors">
                    Reset
                </a>
            </div>
        </form>
    </div>

    <!-- Tabel Riwayat Transaksi -->
    <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 overflow-hidden shadow-sm">
        <div class="px-5 py-4 border-b border-slate-200 dark:border-slate-800 flex items-center justify-between">
            <div>
                <h3 class="text-sm font-bold text-slate-800 dark:text-white">Rincian Buku Kas Penerimaan</h3>
                <p class="text-[11px] text-slate-500 dark:text-slate-400">Menampilkan {{ $transaksi->total() }} entri pembayaran</p>
            </div>
            <span class="text-xs font-mono text-rose-600 dark:text-rose-400 bg-rose-50 dark:bg-rose-950/60 px-2.5 py-1 rounded-lg font-bold border border-rose-100 dark:border-rose-900/50">
                Aktual Database
            </span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="border-b border-slate-800 bg-slate-900 text-slate-200 font-bold uppercase text-[10px] tracking-wider">
                        <th class="py-3.5 px-4">No. Transaksi</th>
                        <th class="py-3.5 px-4">Pendaftar</th>
                        <th class="py-3.5 px-4">Jurusan</th>
                        <th class="py-3.5 px-4">Metode</th>
                        <th class="py-3.5 px-4">Nominal</th>
                        <th class="py-3.5 px-4">Potongan</th>
                        <th class="py-3.5 px-4">Total Bayar</th>
                        <th class="py-3.5 px-4">Status</th>
                        <th class="py-3.5 px-4">Waktu</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800/60">
                    @forelse($transaksi as $t)
                    <tr class="hover:bg-slate-50/60 dark:hover:bg-slate-800/30 transition-colors">
                        <td class="py-3 px-4 font-mono font-bold text-slate-800 dark:text-white">
                            {{ $t->kode_transaksi }}
                        </td>
                        <td class="py-3 px-4">
                            <span class="font-bold text-slate-800 dark:text-white">{{ $t->pendaftar->nama ?? 'Tamu' }}</span>
                            <span class="block text-[11px] text-slate-400 font-mono">{{ $t->pendaftar->no_pendaftaran ?? '-' }}</span>
                        </td>
                        <td class="py-3 px-4 text-slate-600 dark:text-slate-300">
                            {{ $t->pendaftar->jurusan1->kode_jurusan ?? '-' }}
                        </td>
                        <td class="py-3 px-4">
                            @if($t->tipe_bayar === 'tunai')
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[10px] font-semibold bg-amber-50 dark:bg-amber-950/50 text-amber-700 dark:text-amber-400 border border-amber-200 dark:border-amber-800">
                                    <i data-lucide="banknote" class="w-3 h-3"></i> Tunai Loket
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[10px] font-semibold bg-sky-50 dark:bg-sky-950/50 text-sky-700 dark:text-sky-400 border border-sky-200 dark:border-sky-800">
                                    <i data-lucide="credit-card" class="w-3 h-3"></i> Midtrans
                                </span>
                            @endif
                        </td>
                        <td class="py-3 px-4 text-slate-600 dark:text-slate-300">
                            Rp {{ number_format($t->nominal, 0, ',', '.') }}
                        </td>
                        <td class="py-3 px-4 text-purple-600 dark:text-purple-400 font-bold">
                            {{ $t->potongan > 0 ? '-Rp ' . number_format($t->potongan, 0, ',', '.') : '-' }}
                        </td>
                        <td class="py-3 px-4 font-extrabold text-emerald-600 dark:text-emerald-400">
                            Rp {{ number_format($t->total_bayar, 0, ',', '.') }}
                        </td>
                        <td class="py-3 px-4">
                            @if($t->status_transaksi === 'settlement')
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-700 dark:bg-emerald-950/60 dark:text-emerald-300">
                                    LUNAS
                                </span>
                            @elseif($t->status_transaksi === 'pending')
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-amber-100 text-amber-700 dark:bg-amber-950/60 dark:text-amber-300">
                                    PENDING
                                </span>
                            @else
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-rose-100 text-rose-700 dark:bg-rose-950/60 dark:text-rose-300">
                                    {{ strtoupper($t->status_transaksi) }}
                                </span>
                            @endif
                        </td>
                        <td class="py-3 px-4 text-[11px] text-slate-400 font-mono">
                            {{ $t->waktu_transaksi ? $t->waktu_transaksi->format('d/m/Y H:i') : $t->created_at->format('d/m/Y H:i') }}
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="py-8 text-center text-slate-400 italic">
                            Tidak ditemukan data transaksi pembayaran.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($transaksi->hasPages())
        <div class="px-5 py-4 border-t border-slate-200 dark:border-slate-800">
            {{ $transaksi->links() }}
        </div>
        @endif
    </div>

</div>
@endsection
