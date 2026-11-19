@extends('layouts.admin')

@section('title', 'Banner Pengumuman & Pemeliharaan')
@section('header_title', 'Kelola Pengumuman & Maintenance')

@section('content')
<div class="space-y-6">

    <!-- Mode Pemeliharaan (Maintenance Mode) Toggle Card -->
    <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 p-6 shadow-sm">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div class="flex items-start gap-4">
                <div class="w-12 h-12 rounded-xl {{ $pengaturan->mode_maintenance ? 'bg-rose-100 text-rose-600 dark:bg-rose-950/60 dark:text-rose-400' : 'bg-emerald-100 text-emerald-600 dark:bg-emerald-950/60 dark:text-emerald-400' }} flex items-center justify-center shrink-0">
                    <i data-lucide="{{ $pengaturan->mode_maintenance ? 'alert-triangle' : 'shield-check' }}" class="w-6 h-6"></i>
                </div>
                <div>
                    <div class="flex items-center gap-2">
                        <h2 class="text-base font-bold text-slate-800 dark:text-white">Status Mode Pemeliharaan (Maintenance)</h2>
                        <span class="px-2.5 py-0.5 rounded-full text-xs font-bold {{ $pengaturan->mode_maintenance ? 'bg-rose-500 text-white animate-pulse' : 'bg-emerald-500 text-white' }}">
                            {{ $pengaturan->mode_maintenance ? 'AKTIF (Situs Dikunci)' : 'TIDAK AKTIF (Situs Online)' }}
                        </span>
                    </div>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-1 max-w-2xl leading-relaxed">
                        Jika mode pemeliharaan diaktifkan, seluruh calon siswa dan publik yang mengakses web akan otomatis dialihkan ke halaman pemeliharaan khusus, kecuali Petugas TU dan Kepala Sekolah yang memiliki akses ke panel admin.
                    </p>
                </div>
            </div>

            <form action="{{ route('admin.maintenance.toggle') }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin mengubah status mode pemeliharaan?');" class="shrink-0">
                @csrf
                <button type="submit" class="px-5 py-2.5 rounded-xl font-bold text-xs flex items-center gap-2 transition-all shadow-sm {{ $pengaturan->mode_maintenance ? 'bg-emerald-600 hover:bg-emerald-700 text-white' : 'bg-rose-600 hover:bg-rose-700 text-white' }}">
                    <i data-lucide="power" class="w-4 h-4"></i>
                    {{ $pengaturan->mode_maintenance ? 'Nonaktifkan Mode Pemeliharaan' : 'Aktifkan Mode Pemeliharaan' }}
                </button>
            </form>
        </div>
    </div>

    <!-- Layout Grid: Form Tambah & Daftar Pengumuman -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Form Buat Pengumuman Baru -->
        <div class="lg:col-span-1">
            <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 p-6 shadow-sm sticky top-6">
                <div class="flex items-center gap-2 mb-4 pb-3 border-b border-slate-100 dark:border-slate-800">
                    <i data-lucide="megaphone" class="w-5 h-5 text-emerald-600"></i>
                    <h3 class="text-sm font-bold text-slate-800 dark:text-white">Buat Pengumuman Baru</h3>
                </div>

                <form action="{{ route('admin.pengumuman.simpan') }}" method="POST" class="space-y-4">
                    @csrf
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Judul Pengumuman *</label>
                        <input type="text" name="judul" required placeholder="Contoh: Jadwal Tes Wawancara Gelombang 1"
                               class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-xs text-slate-800 dark:text-white focus:outline-none focus:ring-2 focus:ring-emerald-500">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Kategori / Tipe *</label>
                        <select name="tipe" required class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-xs text-slate-800 dark:text-white focus:outline-none focus:ring-2 focus:ring-emerald-500">
                            <option value="info">Informasi (Biru)</option>
                            <option value="penting">Penting (Merah)</option>
                            <option value="peringatan">Peringatan (Kuning)</option>
                            <option value="maintenance">Maintenance (Ungu)</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Isi Pesan / Maklumat *</label>
                        <textarea name="isi_pengumuman" rows="4" required placeholder="Tuliskan isi pengumuman atau instruksi untuk calon siswa..."
                                  class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-xs text-slate-800 dark:text-white focus:outline-none focus:ring-2 focus:ring-emerald-500"></textarea>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Tanggal Mulai *</label>
                            <input type="date" name="tgl_mulai" required value="{{ date('Y-m-d') }}"
                                   class="w-full px-3 py-2 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-xs text-slate-800 dark:text-white focus:outline-none focus:ring-2 focus:ring-emerald-500">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Tanggal Selesai *</label>
                            <input type="date" name="tgl_selesai" required value="{{ date('Y-m-d', strtotime('+30 days')) }}"
                                   class="w-full px-3 py-2 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-xs text-slate-800 dark:text-white focus:outline-none focus:ring-2 focus:ring-emerald-500">
                        </div>
                    </div>

                    <div class="space-y-2 pt-2 border-t border-slate-100 dark:border-slate-800">
                        <label class="flex items-center gap-2 text-xs text-slate-700 dark:text-slate-300 cursor-pointer">
                            <input type="checkbox" name="tampil_di_banner" value="1" checked class="w-4 h-4 rounded text-emerald-600 focus:ring-emerald-500">
                            <span>Tampilkan di Running Banner / Landing Page</span>
                        </label>
                        <label class="flex items-center gap-2 text-xs text-slate-700 dark:text-slate-300 cursor-pointer">
                            <input type="checkbox" name="status_aktif" value="1" checked class="w-4 h-4 rounded text-emerald-600 focus:ring-emerald-500">
                            <span>Status Langsung Aktif</span>
                        </label>
                    </div>

                    <button type="submit" class="w-full py-2.5 rounded-xl bg-[#fa2f79] hover:bg-[#e11d48] text-white font-bold text-xs flex items-center justify-center gap-2 shadow-md shadow-pink-500/20 transition-colors mt-2">
                        <i data-lucide="send" class="w-4 h-4"></i> Publikasikan Pengumuman
                    </button>
                </form>
            </div>
        </div>

        <!-- Daftar Pengumuman Aktif & Arsip -->
        <div class="lg:col-span-2">
            <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 p-6 shadow-sm">
                <div class="flex items-center justify-between mb-4 pb-3 border-b border-slate-100 dark:border-slate-800">
                    <div class="flex items-center gap-2">
                        <i data-lucide="list" class="w-5 h-5 text-emerald-600"></i>
                        <h3 class="text-sm font-bold text-slate-800 dark:text-white">Daftar Pengumuman ({{ count($pengumuman) }})</h3>
                    </div>
                </div>

                @if($pengumuman->isEmpty())
                    <div class="py-12 text-center text-slate-400">
                        <i data-lucide="inbox" class="w-12 h-12 mx-auto stroke-1 mb-2"></i>
                        <p class="text-xs">Belum ada pengumuman yang diterbitkan.</p>
                    </div>
                @else
                    <div class="space-y-4">
                        @foreach($pengumuman as $item)
                            <div class="p-4 rounded-xl border border-slate-200 dark:border-slate-800 hover:border-slate-300 dark:hover:border-slate-700 transition-colors">
                                <div class="flex items-start justify-between gap-3 mb-2">
                                    <div class="flex items-center gap-2 flex-wrap">
                                        @if($item->tipe === 'penting')
                                            <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-rose-100 text-rose-700 dark:bg-rose-950 dark:text-rose-400">PENTING</span>
                                        @elseif($item->tipe === 'peringatan')
                                            <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-amber-100 text-amber-700 dark:bg-amber-950 dark:text-amber-400">PERINGATAN</span>
                                        @elseif($item->tipe === 'maintenance')
                                            <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-purple-100 text-purple-700 dark:bg-purple-950 dark:text-purple-400">MAINTENANCE</span>
                                        @else
                                            <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-sky-100 text-sky-700 dark:bg-sky-950 dark:text-sky-400">INFORMASI</span>
                                        @endif

                                        @if($item->status_aktif)
                                            <span class="px-2 py-0.5 rounded text-[10px] font-semibold bg-emerald-100 text-emerald-700 dark:bg-emerald-950 dark:text-emerald-400">Aktif</span>
                                        @else
                                            <span class="px-2 py-0.5 rounded text-[10px] font-semibold bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-400">Nonaktif</span>
                                        @endif

                                        @if($item->tampil_di_banner)
                                            <span class="px-2 py-0.5 rounded text-[10px] font-semibold bg-indigo-50 text-indigo-600 dark:bg-indigo-950/60 dark:text-indigo-400">Banner On</span>
                                        @endif
                                    </div>

                                    <span class="text-[10px] text-slate-400 shrink-0">
                                        {{ $item->tgl_mulai->format('d/m/Y') }} s/d {{ $item->tgl_selesai->format('d/m/Y') }}
                                    </span>
                                </div>

                                <h4 class="text-xs font-bold text-slate-900 dark:text-white mb-1.5">{{ $item->judul }}</h4>
                                <p class="text-xs text-slate-600 dark:text-slate-300 leading-relaxed whitespace-pre-line">{{ $item->isi_pengumuman }}</p>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    </div>

</div>
@endsection

