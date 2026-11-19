@extends('layouts.admin')

@section('title', 'Data Pendaftar PPDB - SMK IB Khalifah Bangsa')
@section('header_title', 'Manajemen Data Calon Siswa Baru')

@section('content')
<div class="space-y-6">

    <!-- Filters & Search Bar -->
    <div class="p-6 rounded-3xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-sm space-y-4">
        <form action="{{ route('admin.pendaftar') }}" method="GET" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3">
            <div>
                <label class="block text-[11px] font-bold text-slate-500 uppercase mb-1">Cari Siswa / NISN</label>
                <input type="text" name="q" value="{{ request('q') }}" placeholder="Ketik nama, NISN, sekolah..." class="w-full px-3 py-2 rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-xs">
            </div>

            <div>
                <label class="block text-[11px] font-bold text-slate-500 uppercase mb-1">Filter Jurusan</label>
                <select name="jurusan" class="w-full px-3 py-2 rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-xs">
                    <option value="">-- Semua Jurusan --</option>
                    @foreach($jurusan as $j)
                    <option value="{{ $j->id_jurusan }}" {{ request('jurusan') == $j->id_jurusan ? 'selected' : '' }}>{{ $j->nama_jurusan }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-[11px] font-bold text-slate-500 uppercase mb-1">Status Pembayaran</label>
                <select name="status_bayar" class="w-full px-3 py-2 rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-xs">
                    <option value="">-- Semua Status --</option>
                    <option value="Paid" {{ request('status_bayar') == 'Paid' ? 'selected' : '' }}>Lunas (Paid)</option>
                    <option value="Pending" {{ request('status_bayar') == 'Pending' ? 'selected' : '' }}>Menunggu Bayar</option>
                </select>
            </div>

            <div>
                <label class="block text-[11px] font-bold text-slate-500 uppercase mb-1">Status Verifikasi</label>
                <select name="status_verifikasi" class="w-full px-3 py-2 rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-xs">
                    <option value="">-- Semua Berkas --</option>
                    <option value="Menunggu" {{ request('status_verifikasi') == 'Menunggu' ? 'selected' : '' }}>Menunggu Diperiksa</option>
                    <option value="Terverifikasi" {{ request('status_verifikasi') == 'Terverifikasi' ? 'selected' : '' }}>Terverifikasi (Valid)</option>
                    <option value="Ditolak" {{ request('status_verifikasi') == 'Ditolak' ? 'selected' : '' }}>Perlu Revisi (Ditolak)</option>
                </select>
            </div>

            <div class="flex items-end gap-2">
                <button type="submit" class="w-full py-2 px-3 rounded-xl bg-[#fa2f79] hover:bg-[#e11d48] text-white font-bold text-xs shadow-sm shadow-pink-500/20 flex items-center justify-center gap-1.5 transition-colors">
                    <i data-lucide="filter" class="w-4 h-4"></i> Filter
                </button>
                <a href="{{ route('admin.pendaftar') }}" class="py-2 px-3 rounded-xl border border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-300 font-bold text-xs hover:bg-slate-100 dark:hover:bg-slate-800">
                    Reset
                </a>
            </div>
        </form>

        <!-- Quick Export Buttons Row -->
        <div class="pt-3 border-t border-slate-100 dark:border-slate-800 flex items-center justify-between flex-wrap gap-2 text-xs">
            <span class="text-slate-500">Ditemukan <strong>{{ $pendaftar->total() }}</strong> calon siswa</span>
            <div class="flex items-center gap-2 flex-wrap">
                <a href="{{ route('admin.import.index') }}" class="px-3 py-1.5 rounded-lg bg-[#fa2f79] hover:bg-[#e11d48] text-white font-bold flex items-center gap-1.5 shadow-sm shadow-pink-500/20 transition-colors">
                    <i data-lucide="upload" class="w-3.5 h-3.5"></i> Import Excel/CSV
                </a>
                <a href="{{ route('admin.ekspor.xlsx') }}" class="px-3 py-1.5 rounded-lg border border-slate-200 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-800 text-slate-700 dark:text-slate-200 font-semibold flex items-center gap-1.5">
                    <i data-lucide="file-spreadsheet" class="w-3.5 h-3.5 text-pink-400"></i> Ekspor Excel (.xlsx)
                </a>
                <a href="{{ route('admin.ekspor.excel') }}" class="px-3 py-1.5 rounded-lg border border-slate-200 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-800 text-slate-700 dark:text-slate-200 font-semibold flex items-center gap-1.5">
                    <i data-lucide="file-text" class="w-3.5 h-3.5 text-pink-400"></i> Ekspor CSV
                </a>
                <a href="{{ route('admin.ekspor.dapodik') }}" class="px-3 py-1.5 rounded-lg border border-slate-200 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-800 text-slate-700 dark:text-slate-200 font-semibold flex items-center gap-1.5">
                    <i data-lucide="download" class="w-3.5 h-3.5 text-sky-500"></i> Format Dapodik
                </a>
                <a href="{{ route('admin.laporan.rekap_pdf') }}" target="_blank" class="px-3 py-1.5 rounded-lg bg-[#fa2f79] hover:bg-[#e11d48] text-white font-bold flex items-center gap-1.5 shadow-sm shadow-pink-500/20 transition-colors">
                    <i data-lucide="printer" class="w-3.5 h-3.5"></i> Cetak Rekap PDF
                </a>
            </div>
        </div>
    </div>

    <!-- Applicants Table -->
    <div class="p-6 rounded-3xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-sm space-y-4">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 dark:bg-slate-800 text-slate-500 dark:text-slate-400 uppercase font-bold text-[10px]">
                    <tr>
                        <th class="p-3">No. Pendaftaran</th>
                        <th class="p-3">Nama & NISN</th>
                        <th class="p-3">Pilihan Jurusan 1 & 2</th>
                        <th class="p-3">Jalur</th>
                        <th class="p-3">Status Bayar</th>
                        <th class="p-3">Status Berkas</th>
                        <th class="p-3">Hasil Seleksi</th>
                        <th class="p-3 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                    @forelse($pendaftar as $p)
                    <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/50">
                        <td class="p-3 font-mono font-bold text-slate-900 dark:text-white">
                            <div>{{ $p->no_pendaftaran }}</div>
                            <div class="mt-1">
                                @if($p->is_locked)
                                    <span class="inline-flex items-center gap-1 text-[9px] font-semibold bg-amber-50 dark:bg-amber-950/60 text-amber-700 dark:text-amber-400 px-2 py-0.5 rounded-full border border-amber-200 dark:border-amber-800 font-sans">
                                        <i data-lucide="lock" class="w-2.5 h-2.5"></i> Terkunci
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 text-[9px] font-semibold bg-pink-50 dark:bg-pink-950/60 text-pink-700 dark:text-pink-400 px-2 py-0.5 rounded-full border border-pink-200 dark:border-pink-800 font-sans">
                                        <i data-lucide="unlock" class="w-2.5 h-2.5"></i> Terbuka
                                    </span>
                                @endif
                            </div>
                        </td>
                        <td class="p-3">
                            <span class="font-bold text-slate-900 dark:text-white block">{{ $p->nama }}</span>
                            <span class="text-[10px] text-slate-400 font-mono">NISN: {{ $p->nisn }} | {{ $p->asal_sekolah }}</span>
                        </td>
                        <td class="p-3">
                            <span class="font-bold text-[#fa2f79] dark:text-pink-400 block">{{ $p->jurusan1 ? $p->jurusan1->nama_jurusan : 'Belum dipilih' }}</span>
                            <span class="text-[10px] text-slate-400 block">Pil 2: {{ $p->jurusan2 ? $p->jurusan2->nama_jurusan : '-' }}</span>
                        </td>
                        <td class="p-3">
                            <span class="px-2 py-0.5 rounded text-[10px] font-semibold bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300">
                                {{ $p->jalur_pendaftaran }}
                            </span>
                        </td>
                        <td class="p-3">
                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold 
                                {{ $p->status_pembayaran === 'Paid' ? 'bg-pink-100 text-pink-800 dark:bg-pink-950 dark:text-pink-300' : 'bg-amber-100 text-amber-800 dark:bg-amber-950 dark:text-amber-300' }}">
                                {{ $p->status_pembayaran }}
                            </span>
                        </td>
                        <td class="p-3">
                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold 
                                @if($p->status_verifikasi === 'Terverifikasi') bg-pink-100 text-pink-800 dark:bg-pink-950 dark:text-pink-300
                                @elseif($p->status_verifikasi === 'Ditolak') bg-rose-100 text-rose-800 dark:bg-rose-950 dark:text-rose-300
                                @else bg-amber-100 text-amber-800 dark:bg-amber-950 dark:text-amber-300 @endif">
                                {{ $p->status_verifikasi }}
                            </span>
                        </td>
                        <td class="p-3">
                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold 
                                @if($p->status_kelulusan === 'Diterima') bg-pink-100 text-pink-800 dark:bg-pink-950 dark:text-pink-300
                                @elseif($p->status_kelulusan === 'Cadangan') bg-sky-100 text-sky-800 dark:bg-sky-950 dark:text-sky-300
                                @elseif($p->status_kelulusan === 'Tidak Lolos') bg-rose-100 text-rose-800 dark:bg-rose-950 dark:text-rose-300
                                @else bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-300 @endif">
                                {{ $p->status_kelulusan }}
                            </span>
                        </td>
                        <td class="p-3 text-right">
                            <a href="{{ route('admin.pendaftar.detail', $p->id_pendaftar) }}" class="px-3 py-1.5 rounded-lg bg-[#fa2f79] hover:bg-[#e11d48] text-white font-bold shadow-sm shadow-pink-500/20 transition-colors">
                                Periksa & Berkas
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="p-8 text-center text-slate-400">Tidak ada data pendaftar yang sesuai filter.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination Links -->
        <div class="pt-4 border-t border-slate-100 dark:border-slate-800">
            {{ $pendaftar->links() }}
        </div>
    </div>

</div>
@endsection

