@extends('layouts.admin')

@section('title', 'Master Jurusan & Kuota - PPDB SMK IB')
@section('header_title', 'Master Data Program Keahlian & Kuota')

@section('content')
<div class="space-y-6">

    <!-- Flash Messages -->
    @if(session('success'))
    <div class="p-4 rounded-2xl bg-pink-50 dark:bg-pink-950/60 border border-pink-200 dark:border-pink-800 text-pink-800 dark:text-pink-200 text-xs font-semibold flex items-center justify-between shadow-sm animate-in fade-in duration-200">
        <div class="flex items-center gap-2">
            <i data-lucide="check-circle" class="w-4 h-4 text-[#fa2f79]"></i>
            <span>{{ session('success') }}</span>
        </div>
        <button type="button" onclick="this.parentElement.remove()" class="text-[#fa2f79] hover:text-pink-800 font-bold">&times;</button>
    </div>
    @endif

    @if(session('error'))
    <div class="p-4 rounded-2xl bg-rose-50 dark:bg-rose-950/60 border border-rose-200 dark:border-rose-800 text-rose-800 dark:text-rose-200 text-xs font-semibold flex items-center justify-between shadow-sm animate-in fade-in duration-200">
        <div class="flex items-center gap-2">
            <i data-lucide="alert-circle" class="w-4 h-4 text-rose-600"></i>
            <span>{{ session('error') }}</span>
        </div>
        <button type="button" onclick="this.parentElement.remove()" class="text-rose-600 hover:text-rose-800 font-bold">&times;</button>
    </div>
    @endif

    @if($errors->any())
    <div class="p-4 rounded-2xl bg-rose-50 dark:bg-rose-950/60 border border-rose-200 dark:border-rose-800 text-rose-800 dark:text-rose-200 text-xs space-y-1 shadow-sm">
        <div class="font-bold flex items-center gap-2">
            <i data-lucide="alert-triangle" class="w-4 h-4 text-rose-600"></i> Terjadi kesalahan input:
        </div>
        <ul class="list-disc list-inside space-y-0.5">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    <!-- Statistic Summary Cards -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <div class="p-5 rounded-3xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-sm flex items-center gap-4">
            <div class="w-12 h-12 rounded-2xl bg-pink-50 dark:bg-pink-950/50 text-[#fa2f79] dark:text-pink-400 flex items-center justify-center shrink-0">
                <i data-lucide="graduation-cap" class="w-6 h-6"></i>
            </div>
            <div>
                <span class="text-[11px] font-bold text-slate-500 uppercase tracking-wider block">Total Program</span>
                <span class="text-xl font-extrabold text-slate-900 dark:text-white">{{ $jurusan->count() }}</span>
            </div>
        </div>

        <div class="p-5 rounded-3xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-sm flex items-center gap-4">
            <div class="w-12 h-12 rounded-2xl bg-blue-50 dark:bg-blue-950/50 text-blue-600 dark:text-blue-400 flex items-center justify-center shrink-0">
                <i data-lucide="check-circle-2" class="w-6 h-6"></i>
            </div>
            <div>
                <span class="text-[11px] font-bold text-slate-500 uppercase tracking-wider block">Jurusan Aktif</span>
                <span class="text-xl font-extrabold text-blue-600 dark:text-blue-400">{{ $jurusan->where('status_aktif', true)->count() }}</span>
            </div>
        </div>

        <div class="p-5 rounded-3xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-sm flex items-center gap-4">
            <div class="w-12 h-12 rounded-2xl bg-amber-50 dark:bg-amber-950/50 text-amber-600 dark:text-amber-400 flex items-center justify-center shrink-0">
                <i data-lucide="layers" class="w-6 h-6"></i>
            </div>
            <div>
                <span class="text-[11px] font-bold text-slate-500 uppercase tracking-wider block">Total Kuota</span>
                <span class="text-xl font-extrabold text-amber-600 dark:text-amber-400">{{ $jurusan->sum('kuota') }} Siswa</span>
            </div>
        </div>

        <div class="p-5 rounded-3xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-sm flex items-center gap-4">
            <div class="w-12 h-12 rounded-2xl bg-purple-50 dark:bg-purple-950/50 text-purple-600 dark:text-purple-400 flex items-center justify-center shrink-0">
                <i data-lucide="users" class="w-6 h-6"></i>
            </div>
            <div>
                <span class="text-[11px] font-bold text-slate-500 uppercase tracking-wider block">Total Pendaftar (P1)</span>
                <span class="text-xl font-extrabold text-purple-600 dark:text-purple-400">{{ $jurusan->sum('pendaftar_pilihan1_count') }} Siswa</span>
            </div>
        </div>
    </div>

    <!-- Main Container: Master Program Keahlian (Design Referensi) -->
    <div class="p-6 md:p-8 rounded-3xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-sm space-y-6">

        <!-- Top Header & Badges (Matching referensi/image.png) -->
        <div class="flex items-center justify-between flex-wrap gap-3 pb-4 border-b border-slate-100 dark:border-slate-800">
            <div>
                <div class="flex items-center gap-2">
                    <h2 class="text-lg font-extrabold text-slate-900 dark:text-white">Master Program Keahlian</h2>
                    <span class="px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-pink-100 text-pink-800 dark:bg-pink-950 dark:text-pink-300">
                        Rekomendasi
                    </span>
                </div>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Kelola data program keahlian, kapasitas daya tampung siswa, prospek kerja lulusan, dan status penerimaan.</p>
            </div>

            <button type="button" onclick="openTambahModal()" class="px-4 py-2.5 rounded-xl bg-[#fa2f79] hover:bg-[#e11d48] text-white font-bold text-xs flex items-center gap-2 shadow-md shadow-pink-500/25 transition-all transform hover:-translate-y-0.5">
                <i data-lucide="plus-circle" class="w-4 h-4"></i>
                <span>+ Tambah Program Keahlian</span>
            </button>
        </div>

        <!-- Section Title: Daftar Program Keahlian & Counter (Matching referensi/image.png) -->
        <div class="flex items-center justify-between">
            <h3 class="text-base font-bold text-slate-900 dark:text-white flex items-center gap-2">
                <span>Daftar Program Keahlian</span>
            </h3>
            <span class="px-3 py-1 rounded-full text-xs font-bold bg-blue-50 text-blue-600 dark:bg-blue-950/60 dark:text-blue-400 border border-blue-200 dark:border-blue-800">
                {{ $jurusan->count() }} Program
            </span>
        </div>

        <!-- Program Keahlian Cards Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            @foreach($jurusan as $j)
            @php
                $p1Count = $j->pendaftar_pilihan1_count ?? 0;
                $percent = $j->kuota > 0 ? min(100, round(($p1Count / $j->kuota) * 100)) : 0;
                $sisaKuota = max(0, $j->kuota - $p1Count);
                $hasPendaftar = ($p1Count > 0) || (($j->pendaftar_pilihan2_count ?? 0) > 0) || (($j->siswa_diterima_count ?? 0) > 0);
            @endphp
            <div class="p-6 rounded-3xl bg-slate-50/70 dark:bg-slate-800/70 border border-slate-200 dark:border-slate-700/80 shadow-sm hover:shadow-md transition-all flex flex-col justify-between space-y-4">
                
                <!-- Card Header (Design Referensi: Title, Kuota, Status Badge) -->
                <div>
                    <div class="flex items-start justify-between gap-3 mb-2">
                        <div class="flex items-center gap-2.5">
                            <span class="w-9 h-9 rounded-xl bg-white dark:bg-slate-900 text-[#fa2f79] dark:text-pink-400 font-mono font-extrabold flex items-center justify-center text-xs shadow-sm border border-slate-200 dark:border-slate-700">
                                {{ $j->kode_jurusan }}
                            </span>
                            <div>
                                <h4 class="text-base font-bold text-slate-900 dark:text-white leading-snug">{{ $j->nama_jurusan }}</h4>
                                <span class="text-xs text-slate-500 dark:text-slate-400 font-medium">Kuota: {{ $j->kuota }} siswa</span>
                            </div>
                        </div>

                        <!-- Status Badge (Matching referensi: [Aktif]) -->
                        <div>
                            @if($j->status_aktif)
                            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-xs font-bold bg-pink-100 text-pink-800 dark:bg-pink-950 dark:text-pink-300">
                                <span class="w-1.5 h-1.5 rounded-full bg-pink-500 animate-pulse"></span>
                                Aktif
                            </span>
                            @else
                            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-xs font-bold bg-slate-200 text-slate-600 dark:bg-slate-700 dark:text-slate-300">
                                Nonaktif
                            </span>
                            @endif
                        </div>
                    </div>

                    <!-- Kuota Progress & Applicant Summary -->
                    <div class="mt-4 pt-3 border-t border-slate-200 dark:border-slate-700/60 space-y-2">
                        <div class="flex items-center justify-between text-xs font-semibold">
                            <span class="text-slate-600 dark:text-slate-400">Pendaftar Prioritas 1:</span>
                            <span class="text-slate-900 dark:text-white font-mono">{{ $p1Count }} / {{ $j->kuota }} Siswa</span>
                        </div>
                        <div class="w-full bg-slate-200 dark:bg-slate-700 h-2 rounded-full overflow-hidden">
                            <div class="h-full rounded-full transition-all duration-500 {{ $percent >= 100 ? 'bg-rose-500' : ($percent >= 80 ? 'bg-amber-500' : 'bg-[#fa2f79]') }}"
                                 style="width: {{ $percent }}%"></div>
                        </div>
                        <div class="flex items-center justify-between text-[11px] text-slate-500 dark:text-slate-400">
                            <span>Terisi {{ $percent }}%</span>
                            <span class="font-semibold text-[#fa2f79] dark:text-pink-400">Sisa: {{ $sisaKuota }} Kursi</span>
                        </div>
                    </div>

                    <!-- Description & Prospek Kerja Preview -->
                    @if($j->deskripsi || $j->prospek_kerja)
                    <div class="mt-3 p-3 rounded-2xl bg-white/70 dark:bg-slate-900/60 border border-slate-200/60 dark:border-slate-700/50 space-y-1.5 text-xs">
                        @if($j->deskripsi)
                        <p class="text-slate-600 dark:text-slate-300 line-clamp-2">
                            <strong class="text-slate-800 dark:text-slate-200">Deskripsi:</strong> {{ $j->deskripsi }}
                        </p>
                        @endif
                        @if($j->prospek_kerja)
                        <p class="text-slate-600 dark:text-slate-300 line-clamp-2">
                            <strong class="text-[#fa2f79] dark:text-pink-400">Prospek Kerja:</strong> {{ $j->prospek_kerja }}
                        </p>
                        @endif
                    </div>
                    @endif
                </div>

                <!-- Action Buttons: Detail, Edit, Ubah Kuota, Toggle, Hapus -->
                <div class="pt-3 border-t border-slate-200 dark:border-slate-700/60 flex items-center justify-between flex-wrap gap-2 text-xs">
                    <div class="flex items-center gap-1.5">
                        <!-- Lihat Detail -->
                        <button type="button" 
                                onclick="openDetailModal({{ json_encode($j) }}, {{ $p1Count }}, {{ $sisaKuota }}, {{ $hasPendaftar ? 'true' : 'false' }})"
                                class="px-3 py-1.5 rounded-xl bg-white dark:bg-slate-900 hover:bg-slate-100 dark:hover:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-300 font-bold transition-colors flex items-center gap-1.5 shadow-sm"
                                title="Lihat Detail Jurusan">
                            <i data-lucide="eye" class="w-3.5 h-3.5 text-slate-500"></i>
                            <span>Detail</span>
                        </button>

                        <!-- Edit Jurusan -->
                        <button type="button" 
                                onclick="openEditModal({{ json_encode($j) }})"
                                class="px-3 py-1.5 rounded-xl bg-pink-50 dark:bg-pink-950/60 hover:bg-pink-100 text-[#fa2f79] dark:text-pink-300 border border-pink-200 dark:border-pink-800 font-bold transition-colors flex items-center gap-1.5 shadow-sm"
                                title="Edit Data Jurusan">
                            <i data-lucide="edit-3" class="w-3.5 h-3.5 text-[#fa2f79]"></i>
                            <span>Edit</span>
                        </button>

                        <!-- Ubah Kuota Cepat -->
                        <button type="button" 
                                onclick="openUbahKuotaModal({{ $j->id_jurusan }}, '{{ addslashes($j->nama_jurusan) }}', '{{ $j->kode_jurusan }}', {{ $j->kuota }}, {{ $p1Count }})"
                                class="px-3 py-1.5 rounded-xl bg-amber-50 dark:bg-amber-950/60 hover:bg-amber-100 text-amber-700 dark:text-amber-300 border border-amber-200 dark:border-amber-800 font-bold transition-colors flex items-center gap-1.5 shadow-sm"
                                title="Ubah Kuota Penerimaan">
                            <i data-lucide="sliders" class="w-3.5 h-3.5 text-amber-600"></i>
                            <span>Ubah Kuota</span>
                        </button>
                    </div>

                    <div class="flex items-center gap-1.5">
                        <!-- Toggle Status Aktif/Nonaktif -->
                        <form action="{{ route('admin.master.jurusan.toggle', $j->id_jurusan) }}" method="POST"
                              onsubmit="return confirm('Apakah Anda yakin ingin {{ $j->status_aktif ? 'menonaktifkan' : 'mengaktifkan' }} program keahlian {{ $j->nama_jurusan }} ({{ $j->kode_jurusan }})? {{ $j->status_aktif ? 'Jurusan nonaktif tidak akan muncul sebagai pilihan baru pada pendaftaran.' : 'Jurusan akan kembali dapat dipilih calon siswa.' }}')">
                            @csrf
                            <button type="submit" 
                                    class="px-2.5 py-1.5 rounded-xl font-bold transition-colors flex items-center gap-1 border shadow-sm {{ $j->status_aktif ? 'bg-slate-100 hover:bg-slate-200 text-slate-700 dark:bg-slate-800 dark:text-slate-300 border-slate-300 dark:border-slate-600' : 'bg-[#fa2f79] hover:bg-[#e11d48] text-white border-[#fa2f79] shadow-pink-500/20' }}"
                                    title="{{ $j->status_aktif ? 'Nonaktifkan Jurusan' : 'Aktifkan Jurusan' }}">
                                <i data-lucide="{{ $j->status_aktif ? 'pause' : 'play' }}" class="w-3.5 h-3.5"></i>
                                <span>{{ $j->status_aktif ? 'Nonaktifkan' : 'Aktifkan' }}</span>
                            </button>
                        </form>

                        <!-- Hapus Jurusan (Aman dari Foreign Key) -->
                        @if($hasPendaftar)
                        <button type="button" 
                                onclick="alert('Program keahlian \'{{ addslashes($j->nama_jurusan) }}\' tidak dapat dihapus permanen karena sudah memiliki data pendaftar/riwayat seleksi. Gunakan tombol Nonaktifkan untuk menutup penerimaan baru.')"
                                class="p-1.5 rounded-xl bg-slate-100 dark:bg-slate-800 text-slate-400 dark:text-slate-600 border border-slate-200 dark:border-slate-700 cursor-not-allowed"
                                title="Terkunci: Sudah memiliki data pendaftar">
                            <i data-lucide="lock" class="w-3.5 h-3.5"></i>
                        </button>
                        @else
                        <form action="{{ route('admin.master.jurusan.hapus', $j->id_jurusan) }}" method="POST"
                              onsubmit="return confirm('PERINGATAN: Apakah Anda yakin ingin menghapus permanen program keahlian {{ $j->nama_jurusan }} ({{ $j->kode_jurusan }})? Tindakan ini tidak dapat dibatalkan.')">
                            @csrf
                            <button type="submit" 
                                    class="p-1.5 rounded-xl bg-rose-50 dark:bg-rose-950/60 hover:bg-rose-100 text-rose-600 dark:text-rose-400 border border-rose-200 dark:border-rose-800 transition-colors shadow-sm"
                                    title="Hapus Jurusan (Belum Ada Pendaftar)">
                                <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
                            </button>
                        </form>
                        @endif
                    </div>
                </div>

            </div>
            @endforeach
        </div>

        <!-- Dashed Card: Tambah Program Keahlian (Exact design matching referensi/image.png) -->
        <div onclick="openTambahModal()" 
             class="cursor-pointer group relative rounded-3xl p-8 border-2 border-dashed border-pink-400/80 dark:border-pink-500/60 bg-pink-50/20 dark:bg-slate-900/40 hover:bg-pink-50/70 dark:hover:bg-pink-950/30 hover:border-pink-500 transition-all duration-200 flex flex-col items-center justify-center text-center space-y-2">
            
            <div class="w-12 h-12 rounded-full border-2 border-[#fa2f79] dark:border-pink-400 text-[#fa2f79] dark:text-pink-400 flex items-center justify-center group-hover:scale-110 transition-transform">
                <i data-lucide="plus" class="w-6 h-6 stroke-[2.5]"></i>
            </div>
            
            <h4 class="text-base font-bold text-slate-900 dark:text-white">Tambah Program Keahlian</h4>
            <p class="text-xs text-slate-500 dark:text-slate-400">Tambahkan jurusan baru kapan saja</p>
        </div>

    </div>

</div>

<!-- ======================================================== -->
<!-- MODAL 1: TAMBAH PROGRAM KEAHLIAN                         -->
<!-- ======================================================== -->
<div id="tambahJurusanModal" class="fixed inset-0 z-50 bg-slate-950/75 backdrop-blur-sm hidden flex items-center justify-center p-4">
    <div class="bg-white dark:bg-slate-900 rounded-3xl max-w-lg w-full border border-slate-200 dark:border-slate-800 shadow-2xl overflow-hidden animate-in fade-in zoom-in-95 duration-200">
        
        <div class="px-6 py-4 bg-slate-900 text-white flex items-center justify-between border-b border-slate-800">
            <div class="flex items-center gap-2.5">
                <div class="w-8 h-8 rounded-xl bg-[#fa2f79] text-white flex items-center justify-center">
                    <i data-lucide="plus" class="w-4 h-4"></i>
                </div>
                <div>
                    <h3 class="text-sm font-extrabold">Tambah Program Keahlian</h3>
                    <span class="text-[10px] text-pink-300 font-medium">Buat jurusan baru dan tentukan kapasitas kuota PPDB</span>
                </div>
            </div>
            <button type="button" onclick="closeTambahModal()" class="text-slate-400 hover:text-white transition-colors">
                <i data-lucide="x" class="w-5 h-5"></i>
            </button>
        </div>

        <form action="{{ route('admin.master.jurusan.simpan') }}" method="POST" 
              onsubmit="return confirm('Apakah Anda yakin data program keahlian baru sudah sesuai dan siap disimpan?');"
              class="p-6 space-y-4 text-xs">
            @csrf

            <div class="grid grid-cols-3 gap-3">
                <div>
                    <label class="block font-bold text-slate-700 dark:text-slate-300 mb-1">
                        Kode Jurusan <span class="text-rose-500">*</span>
                    </label>
                    <input type="text" name="kode_jurusan" required placeholder="Cth: RPL, DKV" maxlength="20"
                           class="w-full px-3 py-2.5 rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-xs font-mono font-bold uppercase tracking-wider focus:outline-none focus:border-[#fa2f79]">
                </div>

                <div class="col-span-2">
                    <label class="block font-bold text-slate-700 dark:text-slate-300 mb-1">
                        Nama Program Keahlian <span class="text-rose-500">*</span>
                    </label>
                    <input type="text" name="nama_jurusan" required placeholder="Cth: Rekayasa Perangkat Lunak" maxlength="100"
                           class="w-full px-3 py-2.5 rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-xs font-semibold focus:outline-none focus:border-[#fa2f79]">
                </div>
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block font-bold text-slate-700 dark:text-slate-300 mb-1">
                        Kapasitas Kuota Siswa <span class="text-rose-500">*</span>
                    </label>
                    <input type="number" name="kuota" required min="0" value="72"
                           class="w-full px-3 py-2.5 rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-xs font-mono font-bold focus:outline-none focus:border-[#fa2f79]">
                    <span class="text-[10px] text-slate-400">Tidak boleh bernilai negatif.</span>
                </div>

                <div>
                    <label class="block font-bold text-slate-700 dark:text-slate-300 mb-1">
                        Status Awal <span class="text-rose-500">*</span>
                    </label>
                    <select name="status_aktif" required class="w-full px-3 py-2.5 rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-xs font-bold focus:outline-none focus:border-[#fa2f79]">
                        <option value="1" selected>Aktif (Buka Pendaftaran)</option>
                        <option value="0">Nonaktif (Tutup Pendaftaran)</option>
                    </select>
                </div>
            </div>

            <div>
                <label class="block font-bold text-slate-700 dark:text-slate-300 mb-1">
                    Deskripsi Program Keahlian
                </label>
                <textarea name="deskripsi" rows="3" placeholder="Jelaskan fokus keahlian, keunggulan, materi praktikum, atau kompetensi jurusan..."
                          class="w-full px-3 py-2.5 rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-xs focus:outline-none focus:border-[#fa2f79]"></textarea>
            </div>

            <div>
                <label class="block font-bold text-slate-700 dark:text-slate-300 mb-1">
                    Prospek Kerja Lulusan
                </label>
                <textarea name="prospek_kerja" rows="2" placeholder="Cth: Web Developer, Mobile App Engineer, UI/UX Designer, IT Consultant..."
                          class="w-full px-3 py-2.5 rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-xs focus:outline-none focus:border-[#fa2f79]"></textarea>
            </div>

            <div class="pt-3 border-t border-slate-200 dark:border-slate-800 flex items-center justify-end gap-2">
                <button type="button" onclick="closeTambahModal()" class="px-4 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 text-slate-700 dark:text-slate-300 font-bold transition-colors">
                    Batal
                </button>
                <button type="submit" class="px-5 py-2.5 rounded-xl bg-[#fa2f79] hover:bg-[#e11d48] text-white font-bold shadow-md shadow-pink-500/25 transition-colors flex items-center gap-2">
                    <i data-lucide="check" class="w-4 h-4"></i>
                    <span>Simpan Program Keahlian</span>
                </button>
            </div>
        </form>

    </div>
</div>

<!-- ======================================================== -->
<!-- MODAL 2: EDIT PROGRAM KEAHLIAN                           -->
<!-- ======================================================== -->
<div id="editJurusanModal" class="fixed inset-0 z-50 bg-slate-950/75 backdrop-blur-sm hidden flex items-center justify-center p-4">
    <div class="bg-white dark:bg-slate-900 rounded-3xl max-w-lg w-full border border-slate-200 dark:border-slate-800 shadow-2xl overflow-hidden animate-in fade-in zoom-in-95 duration-200">
        
        <div class="px-6 py-4 bg-slate-900 text-white flex items-center justify-between border-b border-slate-800">
            <div class="flex items-center gap-2.5">
                <div class="w-8 h-8 rounded-xl bg-[#fa2f79] text-white flex items-center justify-center">
                    <i data-lucide="edit-3" class="w-4 h-4"></i>
                </div>
                <div>
                    <h3 class="text-sm font-extrabold" id="editModalHeaderTitle">Edit Program Keahlian</h3>
                    <span class="text-[10px] text-pink-300 font-medium">Ubah nama, kode, kuota, prospek kerja, atau status</span>
                </div>
            </div>
            <button type="button" onclick="closeEditModal()" class="text-slate-400 hover:text-white transition-colors">
                <i data-lucide="x" class="w-5 h-5"></i>
            </button>
        </div>

        <form id="editJurusanForm" method="POST" action=""
              onsubmit="return confirm('Konfirmasi: Simpan seluruh perubahan data pada program keahlian ini?');"
              class="p-6 space-y-4 text-xs">
            @csrf

            <div class="grid grid-cols-3 gap-3">
                <div>
                    <label class="block font-bold text-slate-700 dark:text-slate-300 mb-1">
                        Kode Jurusan <span class="text-rose-500">*</span>
                    </label>
                    <input type="text" id="edit_kode_jurusan" name="kode_jurusan" required maxlength="20"
                           class="w-full px-3 py-2.5 rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-xs font-mono font-bold uppercase tracking-wider focus:outline-none focus:border-[#fa2f79]">
                </div>

                <div class="col-span-2">
                    <label class="block font-bold text-slate-700 dark:text-slate-300 mb-1">
                        Nama Program Keahlian <span class="text-rose-500">*</span>
                    </label>
                    <input type="text" id="edit_nama_jurusan" name="nama_jurusan" required maxlength="100"
                           class="w-full px-3 py-2.5 rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-xs font-semibold focus:outline-none focus:border-[#fa2f79]">
                </div>
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block font-bold text-slate-700 dark:text-slate-300 mb-1">
                        Kapasitas Kuota Siswa <span class="text-rose-500">*</span>
                    </label>
                    <input type="number" id="edit_kuota" name="kuota" required min="0"
                           class="w-full px-3 py-2.5 rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-xs font-mono font-bold focus:outline-none focus:border-[#fa2f79]">
                    <span class="text-[10px] text-slate-400">Tidak boleh bernilai negatif.</span>
                </div>

                <div>
                    <label class="block font-bold text-slate-700 dark:text-slate-300 mb-1">
                        Status Penerimaan <span class="text-rose-500">*</span>
                    </label>
                    <select id="edit_status_aktif" name="status_aktif" required class="w-full px-3 py-2.5 rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-xs font-bold focus:outline-none focus:border-[#fa2f79]">
                        <option value="1">Aktif (Menerima Pendaftar)</option>
                        <option value="0">Nonaktif (Tutup Pendaftaran)</option>
                    </select>
                </div>
            </div>

            <div>
                <label class="block font-bold text-slate-700 dark:text-slate-300 mb-1">
                    Deskripsi Program Keahlian
                </label>
                <textarea id="edit_deskripsi" name="deskripsi" rows="3"
                          class="w-full px-3 py-2.5 rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-xs focus:outline-none focus:border-[#fa2f79]"></textarea>
            </div>

            <div>
                <label class="block font-bold text-slate-700 dark:text-slate-300 mb-1">
                    Prospek Kerja Lulusan
                </label>
                <textarea id="edit_prospek_kerja" name="prospek_kerja" rows="2"
                          class="w-full px-3 py-2.5 rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-xs focus:outline-none focus:border-[#fa2f79]"></textarea>
            </div>

            <div class="pt-3 border-t border-slate-200 dark:border-slate-800 flex items-center justify-end gap-2">
                <button type="button" onclick="closeEditModal()" class="px-4 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 text-slate-700 dark:text-slate-300 font-bold transition-colors">
                    Batal
                </button>
                <button type="submit" class="px-5 py-2.5 rounded-xl bg-[#fa2f79] hover:bg-[#e11d48] text-white font-bold shadow-md shadow-pink-500/25 transition-colors flex items-center gap-2">
                    <i data-lucide="save" class="w-4 h-4"></i>
                    <span>Simpan Perubahan</span>
                </button>
            </div>
        </form>

    </div>
</div>

<!-- ======================================================== -->
<!-- MODAL 3: LIHAT DETAIL PROGRAM KEAHLIAN                   -->
<!-- ======================================================== -->
<div id="detailJurusanModal" class="fixed inset-0 z-50 bg-slate-950/75 backdrop-blur-sm hidden flex items-center justify-center p-4">
    <div class="bg-white dark:bg-slate-900 rounded-3xl max-w-lg w-full border border-slate-200 dark:border-slate-800 shadow-2xl overflow-hidden animate-in fade-in zoom-in-95 duration-200">
        
        <div class="px-6 py-4 bg-slate-900 text-white flex items-center justify-between border-b border-slate-800">
            <div class="flex items-center gap-2.5">
                <span id="detail_kode_badge" class="px-2.5 py-1 rounded-lg bg-[#fa2f79] text-white font-mono font-extrabold text-xs">
                    FAR
                </span>
                <div>
                    <h3 class="text-sm font-extrabold" id="detail_nama_jurusan">Detail Program Keahlian</h3>
                    <span class="text-[10px] text-slate-400 font-medium">Informasi spesifikasi lengkap & statistik kuota</span>
                </div>
            </div>
            <button type="button" onclick="closeDetailModal()" class="text-slate-400 hover:text-white transition-colors">
                <i data-lucide="x" class="w-5 h-5"></i>
            </button>
        </div>

        <div class="p-6 space-y-4 text-xs">
            <!-- Status & Kuota Stat Box -->
            <div class="grid grid-cols-3 gap-3 p-4 rounded-2xl bg-slate-50 dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700/60 text-center">
                <div>
                    <span class="text-[10px] text-slate-500 uppercase tracking-wider block font-bold">Status</span>
                    <span id="detail_status_badge" class="mt-1 inline-block px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-pink-100 text-pink-800 dark:bg-pink-950 dark:text-pink-300">
                        Aktif
                    </span>
                </div>
                <div>
                    <span class="text-[10px] text-slate-500 uppercase tracking-wider block font-bold">Kapasitas Kuota</span>
                    <span id="detail_kuota" class="mt-1 block text-base font-extrabold text-slate-900 dark:text-white font-mono">72</span>
                </div>
                <div>
                    <span class="text-[10px] text-slate-500 uppercase tracking-wider block font-bold">Sisa Kursi</span>
                    <span id="detail_sisa_kuota" class="mt-1 block text-base font-extrabold text-[#fa2f79] dark:text-pink-400 font-mono">72</span>
                </div>
            </div>

            <!-- Detail Pendaftar -->
            <div class="space-y-2">
                <h5 class="font-bold text-slate-800 dark:text-slate-200 flex items-center gap-1.5">
                    <i data-lucide="users" class="w-4 h-4 text-[#fa2f79]"></i>
                    <span>Statistik Pendaftar:</span>
                </h5>
                <div class="grid grid-cols-2 gap-2 text-[11px]">
                    <div class="p-2.5 rounded-xl bg-slate-100/70 dark:bg-slate-800 border border-slate-200/60 dark:border-slate-700/60">
                        <span class="text-slate-500 block">Pilihan Prioritas 1</span>
                        <strong id="detail_p1_count" class="text-slate-900 dark:text-white text-xs font-mono">0 Siswa</strong>
                    </div>
                    <div class="p-2.5 rounded-xl bg-slate-100/70 dark:bg-slate-800 border border-slate-200/60 dark:border-slate-700/60">
                        <span class="text-slate-500 block">Pilihan Cadangan 2</span>
                        <strong id="detail_p2_count" class="text-slate-900 dark:text-white text-xs font-mono">0 Siswa</strong>
                    </div>
                </div>
            </div>

            <!-- Deskripsi -->
            <div class="space-y-1">
                <h5 class="font-bold text-slate-800 dark:text-slate-200 flex items-center gap-1.5">
                    <i data-lucide="file-text" class="w-4 h-4 text-[#fa2f79]"></i>
                    <span>Deskripsi Program Keahlian:</span>
                </h5>
                <div id="detail_deskripsi" class="p-3 rounded-2xl bg-slate-50 dark:bg-slate-800/60 border border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-300 leading-relaxed text-xs">
                    -
                </div>
            </div>

            <!-- Prospek Kerja -->
            <div class="space-y-1">
                <h5 class="font-bold text-slate-800 dark:text-slate-200 flex items-center gap-1.5">
                    <i data-lucide="briefcase" class="w-4 h-4 text-[#fa2f79]"></i>
                    <span>Prospek Kerja Lulusan:</span>
                </h5>
                <div id="detail_prospek_kerja" class="p-3 rounded-2xl bg-pink-50/50 dark:bg-pink-950/30 border border-pink-200/60 dark:border-pink-800/50 text-slate-700 dark:text-slate-300 leading-relaxed text-xs">
                    -
                </div>
            </div>

            <div class="pt-3 border-t border-slate-200 dark:border-slate-800 flex items-center justify-end">
                <button type="button" onclick="closeDetailModal()" class="px-5 py-2.5 rounded-xl bg-slate-900 text-white font-bold hover:bg-slate-800 transition-colors">
                    Tutup
                </button>
            </div>
        </div>

    </div>
</div>

<!-- ======================================================== -->
<!-- MODAL 4: UBAH KUOTA PENERIMAAN                           -->
<!-- ======================================================== -->
<div id="ubahKuotaModal" class="fixed inset-0 z-50 bg-slate-950/75 backdrop-blur-sm hidden flex items-center justify-center p-4">
    <div class="bg-white dark:bg-slate-900 rounded-3xl max-w-md w-full border border-slate-200 dark:border-slate-800 shadow-2xl overflow-hidden animate-in fade-in zoom-in-95 duration-200">
        
        <div class="px-6 py-4 bg-slate-900 text-white flex items-center justify-between border-b border-slate-800">
            <div class="flex items-center gap-2.5">
                <div class="w-8 h-8 rounded-xl bg-amber-600 text-white flex items-center justify-center">
                    <i data-lucide="sliders" class="w-4 h-4"></i>
                </div>
                <div>
                    <h3 class="text-sm font-extrabold">Ubah Kuota Penerimaan</h3>
                    <span id="kuotaModalSubTitle" class="text-[10px] text-amber-400 font-medium">Sesuaikan daya tampung jurusan</span>
                </div>
            </div>
            <button type="button" onclick="closeUbahKuotaModal()" class="text-slate-400 hover:text-white transition-colors">
                <i data-lucide="x" class="w-5 h-5"></i>
            </button>
        </div>

        <form id="ubahKuotaForm" method="POST" action=""
              onsubmit="return validateAndConfirmKuota();"
              class="p-6 space-y-4 text-xs">
            @csrf

            <div>
                <label class="block font-bold text-slate-700 dark:text-slate-300 mb-1">
                    Program Keahlian
                </label>
                <div id="kuota_nama_display" class="p-3 rounded-xl bg-slate-100 dark:bg-slate-800 font-bold text-slate-900 dark:text-white">
                    -
                </div>
            </div>

            <div>
                <label class="block font-bold text-slate-700 dark:text-slate-300 mb-1">
                    Kapasitas Kuota Baru (Siswa) <span class="text-rose-500">*</span>
                </label>
                <input type="number" id="kuota_input" name="kuota" required min="0"
                       class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-xs font-mono font-bold text-slate-900 dark:text-white focus:outline-none focus:border-amber-500">
                <p id="kuota_warning" class="text-[11px] text-amber-600 dark:text-amber-400 mt-1 hidden font-medium">
                    <i data-lucide="alert-triangle" class="w-3.5 h-3.5 inline"></i> Kuota baru lebih kecil dari jumlah pendaftar saat ini (<span id="current_p1_text">0</span> pendaftar).
                </p>
            </div>

            <div class="pt-3 border-t border-slate-200 dark:border-slate-800 flex items-center justify-end gap-2">
                <button type="button" onclick="closeUbahKuotaModal()" class="px-4 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 text-slate-700 dark:text-slate-300 font-bold transition-colors">
                    Batal
                </button>
                <button type="submit" class="px-5 py-2.5 rounded-xl bg-amber-600 hover:bg-amber-700 text-white font-bold shadow-sm transition-colors flex items-center gap-2">
                    <i data-lucide="check" class="w-4 h-4"></i>
                    <span>Simpan Kuota</span>
                </button>
            </div>
        </form>

    </div>
</div>

@push('scripts')
<script>
    // Initialize icons
    document.addEventListener('DOMContentLoaded', function () {
        if (window.lucide) {
            window.lucide.createIcons();
        }
    });

    // 1. Modal Tambah
    function openTambahModal() {
        document.getElementById('tambahJurusanModal').classList.remove('hidden');
        if (window.lucide) window.lucide.createIcons();
    }
    function closeTambahModal() {
        document.getElementById('tambahJurusanModal').classList.add('hidden');
    }

    // 2. Modal Edit
    function openEditModal(jurusan) {
        const form = document.getElementById('editJurusanForm');
        form.action = "{{ url('admin/master/jurusan') }}/" + jurusan.id_jurusan;

        document.getElementById('editModalHeaderTitle').textContent = 'Edit: ' + jurusan.nama_jurusan;
        document.getElementById('edit_kode_jurusan').value = jurusan.kode_jurusan || '';
        document.getElementById('edit_nama_jurusan').value = jurusan.nama_jurusan || '';
        document.getElementById('edit_kuota').value = jurusan.kuota || 0;
        document.getElementById('edit_status_aktif').value = (jurusan.status_aktif ? 1 : 0);
        document.getElementById('edit_deskripsi').value = jurusan.deskripsi || '';
        document.getElementById('edit_prospek_kerja').value = jurusan.prospek_kerja || '';

        document.getElementById('editJurusanModal').classList.remove('hidden');
        if (window.lucide) window.lucide.createIcons();
    }
    function closeEditModal() {
        document.getElementById('editJurusanModal').classList.add('hidden');
    }

    // 3. Modal Detail
    function openDetailModal(jurusan, p1Count, sisaKuota, hasPendaftar) {
        document.getElementById('detail_kode_badge').textContent = jurusan.kode_jurusan || '-';
        document.getElementById('detail_nama_jurusan').textContent = jurusan.nama_jurusan || '-';
        document.getElementById('detail_kuota').textContent = (jurusan.kuota || 0) + ' Siswa';
        document.getElementById('detail_sisa_kuota').textContent = sisaKuota + ' Kursi';
        document.getElementById('detail_p1_count').textContent = (p1Count || 0) + ' Siswa';
        document.getElementById('detail_p2_count').textContent = (jurusan.pendaftar_pilihan2_count || 0) + ' Siswa';

        const statusBadge = document.getElementById('detail_status_badge');
        if (jurusan.status_aktif) {
            statusBadge.textContent = 'Aktif';
            statusBadge.className = 'mt-1 inline-block px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-pink-100 text-pink-800 dark:bg-pink-950 dark:text-pink-300';
        } else {
            statusBadge.textContent = 'Nonaktif';
            statusBadge.className = 'mt-1 inline-block px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-slate-200 text-slate-600 dark:bg-slate-700 dark:text-slate-300';
        }

        document.getElementById('detail_deskripsi').textContent = jurusan.deskripsi || 'Belum ada deskripsi untuk program keahlian ini.';
        document.getElementById('detail_prospek_kerja').textContent = jurusan.prospek_kerja || 'Belum ada informasi prospek kerja.';

        document.getElementById('detailJurusanModal').classList.remove('hidden');
        if (window.lucide) window.lucide.createIcons();
    }
    function closeDetailModal() {
        document.getElementById('detailJurusanModal').classList.add('hidden');
    }

    // 4. Modal Ubah Kuota
    let currentApplicantsForKuota = 0;
    function openUbahKuotaModal(id, nama, kode, kuota, p1Count) {
        const form = document.getElementById('ubahKuotaForm');
        form.action = "{{ url('admin/master/jurusan') }}/" + id + "/kuota";

        document.getElementById('kuota_nama_display').textContent = nama + ' (' + kode + ')';
        document.getElementById('kuota_input').value = kuota;
        currentApplicantsForKuota = p1Count;
        document.getElementById('current_p1_text').textContent = p1Count;

        const warning = document.getElementById('kuota_warning');
        if (kuota < p1Count) {
            warning.classList.remove('hidden');
        } else {
            warning.classList.add('hidden');
        }

        document.getElementById('kuota_input').oninput = function () {
            const val = parseInt(this.value, 10) || 0;
            if (val < currentApplicantsForKuota) {
                warning.classList.remove('hidden');
            } else {
                warning.classList.add('hidden');
            }
        };

        document.getElementById('ubahKuotaModal').classList.remove('hidden');
        if (window.lucide) window.lucide.createIcons();
    }
    function closeUbahKuotaModal() {
        document.getElementById('ubahKuotaModal').classList.add('hidden');
    }

    function validateAndConfirmKuota() {
        const val = parseInt(document.getElementById('kuota_input').value, 10);
        if (isNaN(val) || val < 0) {
            alert('Kapasitas kuota tidak boleh bernilai negatif.');
            return false;
        }
        if (val < currentApplicantsForKuota) {
            return confirm('PERINGATAN: Kuota yang dimasukkan (' + val + ') lebih kecil daripada jumlah pendaftar yang sudah ada (' + currentApplicantsForKuota + '). Apakah Anda yakin ingin melanjutkan penyimpanan?');
        }
        return confirm('Apakah Anda yakin ingin mengubah kapasitas kuota program keahlian ini menjadi ' + val + ' siswa?');
    }

    // Close modal on Escape key
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') {
            closeTambahModal();
            closeEditModal();
            closeDetailModal();
            closeUbahKuotaModal();
        }
    });
</script>
@endpush
@endsection
