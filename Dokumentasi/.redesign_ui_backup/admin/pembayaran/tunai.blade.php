@extends('layouts.admin')

@section('title', 'Verifikasi Pembayaran Tunai Loket TU - SMK Islam Bina Khalifah Bangsa')
@section('header_title', 'Verifikasi Pembayaran Tunai di Loket TU')

@section('content')
<div class="space-y-6">

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

    @if(session('info'))
    <div class="p-4 rounded-2xl bg-sky-50 dark:bg-sky-950/60 border border-sky-200 dark:border-sky-800 text-sky-800 dark:text-sky-200 text-xs font-semibold flex items-center justify-between">
        <div class="flex items-center gap-2">
            <i data-lucide="info" class="w-4 h-4 text-sky-600"></i>
            <span>{{ session('info') }}</span>
        </div>
        <button type="button" onclick="this.parentElement.remove()" class="text-sky-600 hover:text-sky-800">&times;</button>
    </div>
    @endif

    <!-- Banner Header & Info Kasir -->
    <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200/90 dark:border-slate-800 p-6 shadow-sm">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div class="flex items-start gap-4">
                <div class="w-12 h-12 rounded-2xl bg-pink-50 dark:bg-pink-950/60 text-pink-600 dark:text-pink-400 flex items-center justify-center shrink-0">
                    <i data-lucide="banknote" class="w-6 h-6"></i>
                </div>
                <div>
                    <h2 class="text-base font-bold text-navy-950 dark:text-white">Loket Pembayaran Tunai Resmi</h2>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">
                        Gunakan menu ini saat calon siswa atau orang tua datang langsung membayar biaya formulir ke loket Tata Usaha sekolah. Setelah dikonfirmasi, status pendaftar otomatis berubah menjadi <strong>Lunas</strong> dan kwitansi resmi langsung aktif.
                    </p>
                </div>
            </div>
            <div class="flex items-center gap-2 shrink-0">
                <span class="px-3.5 py-1.5 rounded-full text-xs font-bold bg-navy-50 text-navy-950 dark:bg-navy-900/60 dark:text-navy-300 border border-navy-200/70 dark:border-navy-800 flex items-center gap-1.5">
                    <i data-lucide="user-check" class="w-4 h-4 text-pink-600"></i> Kasir: {{ auth('admin')->user()->nama_admin ?? 'Petugas TU' }}
                </span>
            </div>
        </div>
    </div>

    <!-- Search Form -->
    <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200/90 dark:border-slate-800 p-4 shadow-sm">
        <form action="{{ route('admin.pembayaran.tunai') }}" method="GET" class="flex flex-col sm:flex-row items-center gap-3">
            <div class="relative flex-1 w-full">
                <i data-lucide="search" class="w-4 h-4 text-slate-400 absolute left-3.5 top-1/2 -translate-y-1/2"></i>
                <input type="text" name="q" value="{{ $search }}" placeholder="Cari berdasarkan Nama Siswa, NISN (10 digit), atau No. Pendaftaran..."
                       class="w-full pl-10 pr-4 py-2.5 rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-xs text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-pink-500">
            </div>
            <button type="submit" class="w-full sm:w-auto px-5 py-2.5 rounded-xl bg-pink-600 hover:bg-pink-700 text-white font-bold text-xs flex items-center justify-center gap-2 transition">
                <i data-lucide="filter" class="w-4 h-4"></i> Cari Siswa
            </button>
            @if($search)
            <a href="{{ route('admin.pembayaran.tunai') }}" class="w-full sm:w-auto px-4 py-2.5 rounded-xl border border-slate-300 dark:border-slate-700 text-xs font-bold text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 text-center transition">
                Reset
            </a>
            @endif
        </form>
    </div>

    <!-- Antrean Menunggu Pembayaran Tunai -->
    <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200/90 dark:border-slate-800 shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between flex-wrap gap-2">
            <div>
                <h3 class="text-sm font-bold text-navy-950 dark:text-white flex items-center gap-2">
                    <i data-lucide="clock" class="w-4 h-4 text-amber-500"></i> Antrean Siswa Menunggu Pembayaran Tunai
                </h3>
                <span class="text-[11px] text-slate-400">Total ditemukan: {{ $antreanTunai->total() }} pendaftar belum lunas</span>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-600 dark:text-slate-300">
                <thead class="bg-navy-50/60 dark:bg-slate-800 text-[11px] uppercase font-bold text-navy-950 dark:text-slate-300 border-b border-slate-100 dark:border-slate-800 tracking-wider">
                    <tr>
                        <th class="px-5 py-3.5">Calon Siswa & NISN</th>
                        <th class="px-4 py-3.5">No. Pendaftaran</th>
                        <th class="px-4 py-3.5">Pilihan Jurusan</th>
                        <th class="px-4 py-3.5">Gelombang</th>
                        <th class="px-4 py-3.5">Nominal Tagihan</th>
                        <th class="px-4 py-3.5">Metode Terpilih</th>
                        <th class="px-5 py-3.5 text-center">Aksi Konfirmasi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                    @forelse($antreanTunai as $p)
                    @php
                        $gel = $p->gelombang;
                        $biayaAwal = $gel ? (float)$gel->biaya_pendaftaran : 100000.00;
                        $pot = $p->pembayaran ? (float)$p->pembayaran->potongan : 0.00;
                        $totalTagihan = max(0.00, $biayaAwal - $pot);
                        $isTunaiSelected = $p->pembayaran && $p->pembayaran->tipe_bayar === 'tunai_tu';
                    @endphp
                    <tr class="hover:bg-slate-50/80 dark:hover:bg-slate-800/40 transition">
                        <td class="px-5 py-3.5">
                            <strong class="block text-navy-950 dark:text-white font-bold">{{ $p->nama }}</strong>
                            <span class="text-[10px] text-slate-400 font-mono">NISN: {{ $p->nisn }} &bull; {{ $p->hp_siswa }}</span>
                        </td>
                        <td class="px-4 py-3.5 font-mono font-bold text-slate-700 dark:text-slate-200">
                            {{ $p->no_pendaftaran ?? 'DRAFT' }}
                        </td>
                        <td class="px-4 py-3.5">
                            <span class="px-2 py-0.5 rounded-md bg-navy-50 dark:bg-slate-800 text-[10px] font-bold text-navy-950 dark:text-slate-300">
                                {{ $p->jurusan1 ? $p->jurusan1->kode_jurusan : '-' }}
                            </span>
                        </td>
                        <td class="px-4 py-3.5">
                            {{ $gel ? $gel->nama_gelombang : 'Gelombang 1' }}
                        </td>
                        <td class="px-4 py-3.5">
                            <strong class="block font-mono text-emerald-600 dark:text-emerald-400 font-bold">
                                Rp {{ number_format($totalTagihan, 0, ',', '.') }}
                            </strong>
                            @if($pot > 0)
                            <span class="text-[10px] text-pink-600 dark:text-pink-400 font-semibold">
                                (Diskon Rp {{ number_format($pot, 0, ',', '.') }})
                            </span>
                            @endif
                        </td>
                        <td class="px-4 py-3.5">
                            @if($isTunaiSelected)
                            <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-amber-100 text-amber-800 dark:bg-amber-950 dark:text-amber-300 inline-flex items-center gap-1">
                                <i data-lucide="hand-coins" class="w-3 h-3"></i> Loket TU (Tunai)
                            </span>
                            @else
                            <span class="px-2.5 py-1 rounded-full text-[10px] font-semibold bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-300">
                                {{ $p->pembayaran ? $p->pembayaran->metode_pembayaran : 'Belum Memilih' }}
                            </span>
                            @endif
                        </td>
                        <td class="px-5 py-3.5 text-center">
                            <form action="{{ route('admin.pembayaran.tunai.konfirmasi', $p->id_pendaftar) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin menerima uang tunai Rp {{ number_format($totalTagihan, 0, ',', '.') }} dari calon siswa dan mengonfirmasi status menjadi LUNAS?');">
                                @csrf
                                <button type="submit" class="px-3.5 py-1.5 rounded-xl bg-pink-600 hover:bg-pink-700 text-white font-bold text-[11px] shadow-sm flex items-center gap-1.5 mx-auto transition active:scale-95">
                                    <i data-lucide="check" class="w-3.5 h-3.5"></i> Konfirmasi Pembayaran
                                </button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="px-6 py-8 text-center text-slate-400 text-xs">
                            <i data-lucide="inbox" class="w-8 h-8 mx-auto mb-2 opacity-50"></i>
                            Tidak ada antrean calon siswa yang menunggu pembayaran saat ini.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($antreanTunai->hasPages())
        <div class="p-4 border-t border-slate-100 dark:border-slate-800">
            {{ $antreanTunai->links() }}
        </div>
        @endif
    </div>

    <!-- Antrean Pembayaran Tunai Daftar Ulang (Siswa Diterima) -->
    <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200/90 dark:border-slate-800 shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between flex-wrap gap-2">
            <div>
                <h3 class="text-sm font-bold text-navy-950 dark:text-white flex items-center gap-2">
                    <i data-lucide="badge-check" class="w-4 h-4 text-pink-600"></i> Antrean Verifikasi Tunai Daftar Ulang (Siswa Diterima)
                </h3>
                <span class="text-[11px] text-slate-400">Total ditemukan: {{ count($antreanDaftarUlang ?? []) }} siswa diterima belum menyelesaikan daftar ulang</span>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-600 dark:text-slate-300">
                <thead class="bg-navy-50/60 dark:bg-slate-800 text-[11px] uppercase font-bold text-navy-950 dark:text-slate-300 border-b border-slate-100 dark:border-slate-800 tracking-wider">
                    <tr>
                        <th class="px-5 py-3.5">Calon Siswa & NISN</th>
                        <th class="px-4 py-3.5">No. Pendaftaran</th>
                        <th class="px-4 py-3.5">Diterima di Jurusan</th>
                        <th class="px-4 py-3.5">Nominal Daftar Ulang</th>
                        <th class="px-4 py-3.5">Status Pembayaran</th>
                        <th class="px-5 py-3.5 text-center">Aksi Konfirmasi TU</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                    @forelse($antreanDaftarUlang ?? [] as $du)
                    @php
                        $nominalDu = (float) (\App\Models\PengaturanSekolah::getPengaturan()->biaya_daftar_ulang ?? 1500000.00);
                        $trxDu = $du->pembayaranDaftarUlang;
                    @endphp
                    <tr class="hover:bg-slate-50/80 dark:hover:bg-slate-800/40 transition">
                        <td class="px-5 py-3.5">
                            <strong class="text-navy-950 dark:text-white text-xs block">{{ $du->nama }}</strong>
                            <span class="text-[11px] text-slate-400 font-mono">NISN: {{ $du->nisn }}</span>
                        </td>
                        <td class="px-4 py-3.5 font-mono font-bold text-slate-800 dark:text-slate-200">
                            {{ $du->no_pendaftaran }}
                        </td>
                        <td class="px-4 py-3.5 font-semibold text-navy-900 dark:text-navy-300">
                            {{ $du->jurusan1 ? $du->jurusan1->nama_jurusan : '-' }}
                        </td>
                        <td class="px-4 py-3.5 font-mono font-bold text-navy-950 dark:text-white">
                            Rp {{ number_format($nominalDu, 0, ',', '.') }}
                        </td>
                        <td class="px-4 py-3.5">
                            @if($trxDu && $trxDu->tipe_bayar === 'tunai_tu')
                            <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-blue-100 text-blue-800 dark:bg-blue-950 dark:text-blue-300 inline-flex items-center gap-1">
                                <i data-lucide="clock" class="w-3 h-3"></i> Menunggu di Loket TU
                            </span>
                            @elseif($trxDu && $trxDu->tipe_bayar === 'midtrans')
                            <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-amber-100 text-amber-800 dark:bg-amber-950 dark:text-amber-300 inline-flex items-center gap-1">
                                <i data-lucide="qr-code" class="w-3 h-3"></i> Pending Online Midtrans
                            </span>
                            @else
                            <span class="px-2.5 py-1 rounded-full text-[10px] font-medium bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-400">
                                Belum Memilih Opsi
                            </span>
                            @endif
                        </td>
                        <td class="px-5 py-3.5 text-center">
                            <form action="{{ route('admin.pembayaran.daftar_ulang.konfirmasi', $du->id_pendaftar) }}" method="POST"
                                  onsubmit="return confirm('Konfirmasi penerimaan pembayaran tunai daftar ulang sebesar Rp {{ number_format($nominalDu, 0, ',', '.') }} untuk siswa {{ $du->nama }}?');">
                                @csrf
                                <button type="submit" class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-xl bg-pink-600 hover:bg-pink-700 text-white font-bold text-xs shadow-sm hover:shadow transition">
                                    <i data-lucide="check-circle" class="w-3.5 h-3.5"></i>
                                    Konfirmasi Lunas Tunai
                                </button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-6 py-8 text-center text-slate-400 text-xs">
                            <i data-lucide="inbox" class="w-8 h-8 mx-auto mb-2 opacity-50"></i>
                            Tidak ada antrean daftar ulang yang menunggu verifikasi saat ini.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Riwayat Konfirmasi Transaksi Tunai Terakhir -->
    <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200/90 dark:border-slate-800 shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between">
            <h3 class="text-sm font-bold text-navy-950 dark:text-white flex items-center gap-2">
                <i data-lucide="receipt" class="w-4 h-4 text-pink-600"></i> Riwayat Pembayaran Tunai Dikonfirmasi Lunas
            </h3>
            <span class="text-[11px] text-slate-400">20 Transaksi Kasir Terakhir</span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-600 dark:text-slate-300">
                <thead class="bg-navy-50/60 dark:bg-slate-800 text-[11px] uppercase font-bold text-navy-950 dark:text-slate-300 border-b border-slate-100 dark:border-slate-800 tracking-wider">
                    <tr>
                        <th class="px-5 py-3.5">Kode Transaksi</th>
                        <th class="px-4 py-3.5">Waktu Konfirmasi</th>
                        <th class="px-4 py-3.5">Nama Calon Siswa</th>
                        <th class="px-4 py-3.5">Petugas Kasir TU</th>
                        <th class="px-4 py-3.5">Nominal Dibayar</th>
                        <th class="px-4 py-3.5">Status Transaksi</th>
                        <th class="px-5 py-3.5 text-center">Bukti / Struk</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                    @forelse($riwayatTunai as $r)
                    <tr class="hover:bg-slate-50/80 dark:hover:bg-slate-800/40 transition">
                        <td class="px-5 py-3 font-mono font-bold text-navy-950 dark:text-white">
                            {{ $r->kode_transaksi }}
                        </td>
                        <td class="px-4 py-3 text-slate-500 font-mono text-[11px]">
                            {{ $r->waktu_konfirmasi_tu ? $r->waktu_konfirmasi_tu->format('d/m/Y H:i') : ($r->waktu_transaksi ? $r->waktu_transaksi->format('d/m/Y H:i') : '-') }} WIB
                        </td>
                        <td class="px-4 py-3">
                            <strong class="text-navy-950 dark:text-white block">{{ $r->pendaftar ? $r->pendaftar->nama : '-' }}</strong>
                            <span class="text-[10px] text-slate-400 font-mono">{{ $r->pendaftar ? $r->pendaftar->no_pendaftaran : '-' }}</span>
                        </td>
                        <td class="px-4 py-3">
                            <span class="inline-flex items-center gap-1 text-[11px] font-semibold text-slate-700 dark:text-slate-200">
                                <i data-lucide="user" class="w-3.5 h-3.5 text-pink-600"></i>
                                {{ $r->adminVerifikator ? $r->adminVerifikator->nama_admin : 'Petugas TU' }}
                            </span>
                        </td>
                        <td class="px-4 py-3 font-mono font-bold text-emerald-600 dark:text-emerald-400">
                            Rp {{ number_format($r->total_bayar, 0, ',', '.') }}
                        </td>
                        <td class="px-4 py-3">
                            <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800 dark:bg-emerald-950 dark:text-emerald-300 inline-flex items-center gap-1">
                                <i data-lucide="check-circle-2" class="w-3 h-3"></i> LUNAS (Tunai)
                            </span>
                        </td>
                        <td class="px-5 py-3 text-center">
                            @if($r->pendaftar)
                            <a href="{{ route('admin.pendaftar.detail', $r->pendaftar->id_pendaftar) }}" class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg bg-navy-50 hover:bg-navy-900 hover:text-white dark:bg-slate-800 dark:hover:bg-slate-700 text-navy-900 dark:text-slate-300 font-bold text-[10px] transition">
                                <i data-lucide="file-text" class="w-3 h-3"></i> Detail Berkas
                            </a>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="px-6 py-6 text-center text-slate-400 text-xs">
                            Belum ada riwayat pembayaran tunai yang dikonfirmasi.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection
