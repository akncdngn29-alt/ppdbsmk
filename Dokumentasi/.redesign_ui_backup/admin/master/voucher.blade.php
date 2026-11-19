@extends('layouts.admin')

@section('title', 'Manajemen Voucher Diskon - PPDB SMK Islam Bina Khalifah Bangsa')
@section('header_title', 'Manajemen Voucher Diskon & Beasiswa')

@section('content')
<div class="space-y-6">

    @if(session('success'))
    <div class="p-4 rounded-2xl bg-emerald-50 dark:bg-emerald-950/60 border border-emerald-200 dark:border-emerald-800 text-emerald-800 dark:text-emerald-200 text-xs font-semibold flex items-center justify-between">
        <div class="flex items-center gap-2">
            <i data-lucide="check-circle" class="w-4 h-4 text-emerald-600"></i>
            <span>{{ session('success') }}</span>
        </div>
        <button type="button" onclick="this.parentElement.remove()" class="text-emerald-600 hover:text-emerald-800 font-bold">&times;</button>
    </div>
    @endif

    @if(session('error'))
    <div class="p-4 rounded-2xl bg-rose-50 dark:bg-rose-950/60 border border-rose-200 dark:border-rose-800 text-rose-800 dark:text-rose-200 text-xs font-semibold flex items-center justify-between">
        <div class="flex items-center gap-2">
            <i data-lucide="alert-circle" class="w-4 h-4 text-rose-600"></i>
            <span>{{ session('error') }}</span>
        </div>
        <button type="button" onclick="this.parentElement.remove()" class="text-rose-600 hover:text-rose-800 font-bold">&times;</button>
    </div>
    @endif

    @if($errors->any())
    <div class="p-4 rounded-2xl bg-rose-50 dark:bg-rose-950/60 border border-rose-200 dark:border-rose-800 text-rose-800 dark:text-rose-200 text-xs space-y-1">
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

    <!-- Statistic Cards -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="p-5 rounded-3xl bg-white dark:bg-slate-900 border border-slate-200/90 dark:border-slate-800 shadow-sm flex items-center gap-4 transition hover:shadow-md">
            <div class="w-12 h-12 rounded-2xl bg-navy-50 dark:bg-navy-900/50 text-navy-900 dark:text-navy-300 flex items-center justify-center shrink-0">
                <i data-lucide="ticket" class="w-6 h-6"></i>
            </div>
            <div>
                <span class="text-[11px] font-bold text-slate-500 uppercase tracking-wider block">Total Voucher</span>
                <span class="text-xl font-extrabold text-navy-950 dark:text-white">{{ $stats['total'] ?? $vouchers->count() }}</span>
            </div>
        </div>

        <div class="p-5 rounded-3xl bg-white dark:bg-slate-900 border border-slate-200/90 dark:border-slate-800 shadow-sm flex items-center gap-4 transition hover:shadow-md">
            <div class="w-12 h-12 rounded-2xl bg-pink-50 dark:bg-pink-950/50 text-pink-600 dark:text-pink-400 flex items-center justify-center shrink-0">
                <i data-lucide="badge-percent" class="w-6 h-6"></i>
            </div>
            <div>
                <span class="text-[11px] font-bold text-slate-500 uppercase tracking-wider block">Voucher Aktif</span>
                <span class="text-xl font-extrabold text-pink-600 dark:text-pink-400">{{ $stats['aktif'] ?? $vouchers->where('status_aktif', true)->count() }}</span>
            </div>
        </div>

        <div class="p-5 rounded-3xl bg-white dark:bg-slate-900 border border-slate-200/90 dark:border-slate-800 shadow-sm flex items-center gap-4 transition hover:shadow-md">
            <div class="w-12 h-12 rounded-2xl bg-amber-50 dark:bg-amber-950/50 text-amber-600 dark:text-amber-400 flex items-center justify-center shrink-0">
                <i data-lucide="users" class="w-6 h-6"></i>
            </div>
            <div>
                <span class="text-[11px] font-bold text-slate-500 uppercase tracking-wider block">Total Terpakai</span>
                <span class="text-xl font-extrabold text-amber-600 dark:text-amber-400">{{ $stats['terpakai'] ?? $vouchers->sum('kuota_terpakai') }} kali</span>
            </div>
        </div>

        <div class="p-5 rounded-3xl bg-white dark:bg-slate-900 border border-slate-200/90 dark:border-slate-800 shadow-sm flex items-center gap-4 transition hover:shadow-md">
            <div class="w-12 h-12 rounded-2xl bg-sky-50 dark:bg-sky-950/50 text-sky-600 dark:text-sky-400 flex items-center justify-center shrink-0">
                <i data-lucide="coins" class="w-6 h-6"></i>
            </div>
            <div>
                <span class="text-[11px] font-bold text-slate-500 uppercase tracking-wider block">Total Potongan Diberikan</span>
                <span class="text-lg font-extrabold text-sky-600 dark:text-sky-400 font-mono">
                    Rp {{ number_format($stats['total_potongan'] ?? 0, 0, ',', '.') }}
                </span>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        <!-- Form Tambah Voucher Baru -->
        <div class="p-6 rounded-3xl bg-white dark:bg-slate-900 border border-slate-200/90 dark:border-slate-800 shadow-sm space-y-4">
            <div class="flex items-center gap-2 pb-3 border-b border-slate-100 dark:border-slate-800">
                <div class="w-8 h-8 rounded-xl bg-pink-100 dark:bg-pink-950 text-pink-600 flex items-center justify-center">
                    <i data-lucide="plus-circle" class="w-4 h-4"></i>
                </div>
                <div>
                    <h3 class="text-sm font-bold text-navy-950 dark:text-white">Buat Voucher Baru</h3>
                    <p class="text-[11px] text-slate-400">Buat kode promo, diskon, atau beasiswa calon siswa</p>
                </div>
            </div>

            <form action="{{ route('admin.master.voucher.simpan') }}" method="POST" class="space-y-3 text-xs">
                @csrf
                <div>
                    <label class="block font-bold text-slate-700 dark:text-slate-300 mb-1">
                        Kode Voucher <span class="text-rose-500">*</span>
                    </label>
                    <input type="text" name="kode_voucher" value="{{ old('kode_voucher') }}" required placeholder="Contoh: TAHFIZ100, DISMP25" class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-xs font-mono font-bold uppercase tracking-wider focus:outline-none focus:ring-2 focus:ring-pink-500">
                    <span class="text-[10px] text-slate-400">Kode unik huruf kapital & angka tanpa spasi.</span>
                </div>

                <div>
                    <label class="block font-bold text-slate-700 dark:text-slate-300 mb-1">Nama Promo / Keterangan Singkat</label>
                    <input type="text" name="nama_voucher" value="{{ old('nama_voucher') }}" placeholder="Contoh: Beasiswa Tahfiz 30 Juz / Diskon Gelombang 1" class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-xs focus:outline-none focus:ring-2 focus:ring-pink-500">
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block font-bold text-slate-700 dark:text-slate-300 mb-1">Jenis Diskon <span class="text-rose-500">*</span></label>
                        <select name="tipe" id="create_tipe" onchange="toggleTipeInput('create')" class="w-full px-3 py-2.5 rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-xs font-bold focus:outline-none focus:ring-2 focus:ring-pink-500">
                            <option value="nominal" {{ old('tipe') === 'nominal' ? 'selected' : '' }}>Nominal (Rp)</option>
                            <option value="persen" {{ old('tipe') === 'persen' ? 'selected' : '' }}>Persentase (%)</option>
                        </select>
                    </div>

                    <div>
                        <label class="block font-bold text-slate-700 dark:text-slate-300 mb-1">
                            Besaran Potongan <span class="text-rose-500">*</span>
                        </label>
                        <input type="number" name="nilai_potongan" id="create_nilai_potongan" value="{{ old('nilai_potongan', 50000) }}" required min="1" step="any" class="w-full px-3 py-2.5 rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-xs font-mono font-bold focus:outline-none focus:ring-2 focus:ring-pink-500">
                        <span id="create_nilai_hint" class="text-[10px] text-slate-400">Nominal rupiah dipotong langsung.</span>
                    </div>
                </div>

                <div>
                    <label class="block font-bold text-slate-700 dark:text-slate-300 mb-1">
                        Batas Penggunaan (Kuota) <span class="text-rose-500">*</span>
                    </label>
                    <input type="number" name="kuota_voucher" value="{{ old('kuota_voucher', 50) }}" required min="1" class="w-full px-3 py-2.5 rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-xs font-mono font-bold focus:outline-none focus:ring-2 focus:ring-pink-500">
                    <span class="text-[10px] text-slate-400">Batas maksimal berapa kali voucher ini dapat digunakan.</span>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block font-bold text-slate-700 dark:text-slate-300 mb-1">Berlaku Mulai</label>
                        <input type="date" name="berlaku_mulai" value="{{ old('berlaku_mulai', date('Y-m-d')) }}" class="w-full px-3 py-2 rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-xs focus:outline-none focus:ring-2 focus:ring-pink-500">
                    </div>
                    <div>
                        <label class="block font-bold text-slate-700 dark:text-slate-300 mb-1">Berlaku Sampai</label>
                        <input type="date" name="berlaku_sampai" value="{{ old('berlaku_sampai') }}" class="w-full px-3 py-2 rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-xs focus:outline-none focus:ring-2 focus:ring-pink-500">
                    </div>
                </div>
                <span class="text-[10px] text-slate-400 block -mt-1">Kosongkan 'Berlaku Sampai' jika berlaku tanpa batas waktu.</span>

                <div>
                    <label class="block font-bold text-slate-700 dark:text-slate-300 mb-1">Deskripsi / Syarat Ketentuan Promo</label>
                    <textarea name="deskripsi" rows="2" placeholder="Informasi promo yang ditampilkan kepada calon siswa..." class="w-full px-3 py-2 rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-xs focus:outline-none focus:ring-2 focus:ring-pink-500">{{ old('deskripsi') }}</textarea>
                </div>

                <div class="pt-2">
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" name="status_aktif" value="1" checked class="rounded border-slate-300 text-pink-600 focus:ring-pink-500">
                        <span class="font-bold text-slate-800 dark:text-slate-200">Aktifkan Voucher Ini Sekarang</span>
                    </label>
                </div>

                <button type="submit" class="w-full py-2.5 rounded-xl bg-pink-600 hover:bg-pink-700 text-white font-bold text-xs shadow-md shadow-pink-600/20 transition flex items-center justify-center gap-2">
                    <i data-lucide="check" class="w-4 h-4"></i> Simpan & Terbitkan Voucher
                </button>
            </form>
        </div>

        <!-- Daftar Voucher -->
        <div class="lg:col-span-2 p-6 rounded-3xl bg-white dark:bg-slate-900 border border-slate-200/90 dark:border-slate-800 shadow-sm space-y-4">
            <div class="flex items-center justify-between flex-wrap gap-2 pb-3 border-b border-slate-100 dark:border-slate-800">
                <div>
                    <h3 class="text-base font-bold text-navy-950 dark:text-white flex items-center gap-2">
                        <i data-lucide="tags" class="w-5 h-5 text-pink-600"></i>
                        Daftar Voucher & Ketentuan Promo
                    </h3>
                    <p class="text-xs text-slate-400">Kelola status aktif, edit masa berlaku, dan pantau batas penggunaan voucher</p>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead class="bg-navy-50/60 dark:bg-slate-800/60 text-navy-950 dark:text-slate-300 uppercase font-bold text-[10px] tracking-wider">
                        <tr>
                            <th class="p-3">Kode & Nama</th>
                            <th class="p-3">Jenis Potongan</th>
                            <th class="p-3">Masa Berlaku</th>
                            <th class="p-3">Penggunaan / Batas</th>
                            <th class="p-3">Status</th>
                            <th class="p-3 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                        @forelse($vouchers as $v)
                        @php
                            $terpakai = (int)$v->kuota_terpakai;
                            $kuota = (int)$v->kuota_voucher;
                            $persenPakai = $kuota > 0 ? min(100, round(($terpakai / $kuota) * 100)) : 0;
                            $isExpired = $v->isExpired();
                            $isNotStarted = $v->isNotStartedYet();
                            $isFull = $terpakai >= $kuota;
                        @endphp
                        <tr class="hover:bg-slate-50/70 dark:hover:bg-slate-800/40 transition">
                            <td class="p-3">
                                <div class="flex items-center gap-1.5">
                                    <span class="font-mono font-extrabold text-pink-600 dark:text-pink-400 text-sm tracking-wide">
                                        {{ $v->kode_voucher }}
                                    </span>
                                </div>
                                <div class="text-[11px] font-semibold text-slate-800 dark:text-slate-200 mt-0.5">
                                    {{ $v->nama_voucher ?: '-' }}
                                </div>
                                @if($v->deskripsi)
                                <p class="text-[10px] text-slate-400 line-clamp-1 mt-0.5" title="{{ $v->deskripsi }}">
                                    {{ $v->deskripsi }}
                                </p>
                                @endif
                            </td>

                            <td class="p-3">
                                <span class="px-2 py-0.5 rounded-md text-[10px] font-bold uppercase {{ $v->tipe === 'persen' ? 'bg-purple-100 text-purple-800 dark:bg-purple-950 dark:text-purple-300' : 'bg-navy-50 text-navy-950 dark:bg-navy-900/60 dark:text-navy-300' }}">
                                    {{ $v->tipe === 'persen' ? 'Persentase' : 'Nominal' }}
                                </span>
                                <div class="font-mono font-bold text-navy-950 dark:text-white mt-1">
                                    {{ $v->tipe === 'nominal' ? 'Rp ' . number_format($v->nilai_potongan, 0, ',', '.') : rtrim(rtrim(number_format((float)$v->nilai_potongan, 2, ',', '.'), '0'), ',') . '%' }}
                                </div>
                            </td>

                            <td class="p-3">
                                <div class="text-[11px] font-medium text-slate-700 dark:text-slate-300">
                                    @if($v->berlaku_mulai && $v->berlaku_sampai)
                                        <span>{{ $v->berlaku_mulai->format('d/m/Y') }} &ndash; {{ $v->berlaku_sampai->format('d/m/Y') }}</span>
                                    @elseif($v->berlaku_sampai)
                                        <span>s.d. {{ $v->berlaku_sampai->format('d/m/Y') }}</span>
                                    @elseif($v->berlaku_mulai)
                                        <span>Mulai {{ $v->berlaku_mulai->format('d/m/Y') }}</span>
                                    @else
                                        <span class="text-slate-400 italic">Selalu Berlaku</span>
                                    @endif
                                </div>
                                @if($isExpired)
                                    <span class="inline-block mt-0.5 px-2 py-0.5 rounded text-[10px] font-bold bg-rose-100 text-rose-700 dark:bg-rose-950 dark:text-rose-300">
                                        Kedaluwarsa
                                    </span>
                                @elseif($isNotStarted)
                                    <span class="inline-block mt-0.5 px-2 py-0.5 rounded text-[10px] font-bold bg-amber-100 text-amber-700 dark:bg-amber-950 dark:text-amber-300">
                                        Belum Dimulai
                                    </span>
                                @endif
                            </td>

                            <td class="p-3 min-w-[130px]">
                                <div class="flex items-center justify-between text-[11px] font-mono mb-1">
                                    <span class="font-bold text-slate-800 dark:text-slate-200">{{ $terpakai }} / {{ $kuota }}</span>
                                    <span class="text-[10px] text-slate-400">({{ $v->sisaKuota() }} sisa)</span>
                                </div>
                                <div class="w-full bg-slate-100 dark:bg-slate-700 h-1.5 rounded-full overflow-hidden">
                                    <div class="h-full rounded-full {{ $persenPakai >= 100 ? 'bg-rose-500' : ($persenPakai > 70 ? 'bg-amber-500' : 'bg-pink-500') }}" style="width: {{ $persenPakai }}%"></div>
                                </div>
                            </td>

                            <td class="p-3">
                                @if(!$v->status_aktif)
                                    <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-400">
                                        Nonaktif
                                    </span>
                                @elseif($isExpired)
                                    <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-rose-100 text-rose-800 dark:bg-rose-950 dark:text-rose-300">
                                        Kedaluwarsa
                                    </span>
                                @elseif($isFull)
                                    <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-amber-100 text-amber-800 dark:bg-amber-950 dark:text-amber-300">
                                        Kuota Habis
                                    </span>
                                @else
                                    <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800 dark:bg-emerald-950 dark:text-emerald-300 flex items-center gap-1 w-fit">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span> Aktif
                                    </span>
                                @endif
                            </td>

                            <td class="p-3 text-right">
                                <div class="flex items-center justify-end gap-1.5">
                                    <!-- Toggle Status Button -->
                                    <form action="{{ route('admin.master.voucher.toggle', $v->id_voucher) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin {{ $v->status_aktif ? 'menonaktifkan' : 'mengaktifkan' }} voucher {{ $v->kode_voucher }}?')">
                                        @csrf
                                        <button type="submit" title="{{ $v->status_aktif ? 'Klik untuk Menonaktifkan' : 'Klik untuk Mengaktifkan' }}" class="px-2.5 py-1.5 rounded-lg text-[11px] font-bold transition flex items-center gap-1 {{ $v->status_aktif ? 'bg-amber-50 hover:bg-amber-100 text-amber-700 dark:bg-amber-950/40 dark:text-amber-300 border border-amber-200 dark:border-amber-800' : 'bg-pink-50 hover:bg-pink-100 text-pink-700 dark:bg-pink-950/40 dark:text-pink-300 border border-pink-200 dark:border-pink-800' }}">
                                            <i data-lucide="{{ $v->status_aktif ? 'power-off' : 'power' }}" class="w-3.5 h-3.5"></i>
                                            <span>{{ $v->status_aktif ? 'Nonaktifkan' : 'Aktifkan' }}</span>
                                        </button>
                                    </form>

                                    <!-- Edit Button -->
                                    <button type="button" onclick="openEditModal({{ json_encode($v) }})" title="Edit Voucher" class="px-2.5 py-1.5 rounded-lg bg-navy-50 hover:bg-navy-900 hover:text-white dark:bg-slate-800 dark:hover:bg-slate-700 text-navy-900 dark:text-slate-300 font-bold text-[11px] transition flex items-center gap-1">
                                        <i data-lucide="edit-3" class="w-3.5 h-3.5"></i>
                                        <span>Edit</span>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="p-8 text-center text-slate-400">
                                <i data-lucide="inbox" class="w-8 h-8 mx-auto mb-2 text-slate-300"></i>
                                Belum ada voucher diskon yang dibuat. Silakan gunakan form di samping untuk membuat voucher baru.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

    </div>

</div>

<!-- ======================================================== -->
<!-- MODAL EDIT VOUCHER -->
<!-- ======================================================== -->
<div id="editVoucherModal" class="fixed inset-0 z-50 bg-slate-950/75 backdrop-blur-sm hidden flex items-center justify-center p-4">
    <div class="bg-white dark:bg-slate-900 rounded-3xl max-w-lg w-full border border-slate-200 dark:border-slate-800 shadow-2xl overflow-hidden animate-in fade-in zoom-in-95 duration-200">
        
        <div class="px-6 py-4 bg-navy-950 text-white flex items-center justify-between border-b border-navy-900">
            <div class="flex items-center gap-2">
                <div class="w-8 h-8 rounded-xl bg-pink-600 text-white flex items-center justify-center">
                    <i data-lucide="edit" class="w-4 h-4"></i>
                </div>
                <div>
                    <h3 class="text-sm font-extrabold" id="editModalTitle">Edit Voucher Diskon</h3>
                    <span class="text-[10px] text-pink-400 font-medium">Ubah parameter, masa berlaku, atau batas kuota voucher</span>
                </div>
            </div>
            <button type="button" onclick="closeEditModal()" class="text-slate-400 hover:text-white transition">
                <i data-lucide="x" class="w-5 h-5"></i>
            </button>
        </div>

        <form id="editVoucherForm" method="POST" action="" class="p-6 space-y-4 text-xs">
            @csrf
            @method('PUT')

            <div>
                <label class="block font-bold text-slate-700 dark:text-slate-300 mb-1">
                    Kode Voucher <span class="text-rose-500">*</span>
                </label>
                <input type="text" id="edit_kode_voucher" name="kode_voucher" required class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-xs font-mono font-bold uppercase tracking-wider focus:outline-none focus:ring-2 focus:ring-pink-500">
            </div>

            <div>
                <label class="block font-bold text-slate-700 dark:text-slate-300 mb-1">Nama Promo / Keterangan Singkat</label>
                <input type="text" id="edit_nama_voucher" name="nama_voucher" class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-xs focus:outline-none focus:ring-2 focus:ring-pink-500">
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block font-bold text-slate-700 dark:text-slate-300 mb-1">Jenis Diskon <span class="text-rose-500">*</span></label>
                    <select name="tipe" id="edit_tipe" onchange="toggleTipeInput('edit')" class="w-full px-3 py-2.5 rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-xs font-bold focus:outline-none focus:ring-2 focus:ring-pink-500">
                        <option value="nominal">Nominal Rupiah (Rp)</option>
                        <option value="persen">Persentase (%)</option>
                    </select>
                </div>

                <div>
                    <label class="block font-bold text-slate-700 dark:text-slate-300 mb-1">
                        Besaran Potongan <span class="text-rose-500">*</span>
                    </label>
                    <input type="number" name="nilai_potongan" id="edit_nilai_potongan" required min="1" step="any" class="w-full px-3 py-2.5 rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-xs font-mono font-bold focus:outline-none focus:ring-2 focus:ring-pink-500">
                    <span id="edit_nilai_hint" class="text-[10px] text-slate-400">Nominal rupiah.</span>
                </div>
            </div>

            <div>
                <label class="block font-bold text-slate-700 dark:text-slate-300 mb-1">
                    Batas Penggunaan (Kuota) <span class="text-rose-500">*</span>
                </label>
                <input type="number" id="edit_kuota_voucher" name="kuota_voucher" required min="1" class="w-full px-3 py-2.5 rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-xs font-mono font-bold focus:outline-none focus:ring-2 focus:ring-pink-500">
                <span id="edit_kuota_hint" class="text-[10px] text-slate-400 block mt-0.5">Jumlah kuota batas penggunaan total.</span>
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block font-bold text-slate-700 dark:text-slate-300 mb-1">Masa Berlaku Mulai</label>
                    <input type="date" id="edit_berlaku_mulai" name="berlaku_mulai" class="w-full px-3 py-2 rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-xs focus:outline-none focus:ring-2 focus:ring-pink-500">
                </div>
                <div>
                    <label class="block font-bold text-slate-700 dark:text-slate-300 mb-1">Masa Berlaku Sampai</label>
                    <input type="date" id="edit_berlaku_sampai" name="berlaku_sampai" class="w-full px-3 py-2 rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-xs focus:outline-none focus:ring-2 focus:ring-pink-500">
                </div>
            </div>

            <div>
                <label class="block font-bold text-slate-700 dark:text-slate-300 mb-1">Deskripsi / Syarat Ketentuan Promo</label>
                <textarea id="edit_deskripsi" name="deskripsi" rows="2" class="w-full px-3 py-2 rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-xs focus:outline-none focus:ring-2 focus:ring-pink-500"></textarea>
            </div>

            <div class="pt-2">
                <label class="flex items-center gap-2 cursor-pointer">
                    <input type="checkbox" id="edit_status_aktif" name="status_aktif" value="1" class="rounded border-slate-300 text-pink-600 focus:ring-pink-500">
                    <span class="font-bold text-slate-800 dark:text-slate-200">Voucher Aktif</span>
                </label>
            </div>

            <div class="pt-4 flex items-center justify-end gap-2 border-t border-slate-100 dark:border-slate-800">
                <button type="button" onclick="closeEditModal()" class="px-4 py-2.5 rounded-xl border border-slate-300 dark:border-slate-700 text-slate-600 dark:text-slate-300 font-bold text-xs hover:bg-slate-100 dark:hover:bg-slate-800 transition">
                    Batal
                </button>
                <button type="submit" class="px-5 py-2.5 rounded-xl bg-pink-600 hover:bg-pink-700 text-white font-bold text-xs shadow-md shadow-pink-600/20 transition flex items-center gap-1.5">
                    <i data-lucide="save" class="w-4 h-4"></i> Simpan Perubahan
                </button>
            </div>
        </form>

    </div>
</div>

@push('scripts')
<script>
    function toggleTipeInput(prefix) {
        const tipe = document.getElementById(prefix + '_tipe').value;
        const hint = document.getElementById(prefix + '_nilai_hint');
        const input = document.getElementById(prefix + '_nilai_potongan');

        if (tipe === 'persen') {
            hint.innerText = "Masukkan nilai persentase diskon (1 - 100%).";
            input.setAttribute('max', '100');
            input.setAttribute('step', '1');
            if (parseFloat(input.value) > 100) input.value = 100;
        } else {
            hint.innerText = "Nominal rupiah potongan langsung.";
            input.removeAttribute('max');
            input.setAttribute('step', '1000');
        }
    }

    function openEditModal(voucher) {
        const form = document.getElementById('editVoucherForm');
        form.action = "{{ url('admin/master/voucher') }}/" + voucher.id_voucher;

        document.getElementById('editModalTitle').innerText = 'Edit Voucher: ' + voucher.kode_voucher;
        document.getElementById('edit_kode_voucher').value = voucher.kode_voucher;
        document.getElementById('edit_nama_voucher').value = voucher.nama_voucher || '';
        document.getElementById('edit_tipe').value = voucher.tipe;
        document.getElementById('edit_nilai_potongan').value = parseFloat(voucher.nilai_potongan);
        document.getElementById('edit_kuota_voucher').value = voucher.kuota_voucher;
        document.getElementById('edit_kuota_voucher').setAttribute('min', Math.max(1, voucher.kuota_terpakai || 0));
        document.getElementById('edit_kuota_hint').innerText = "Sudah terpakai " + (voucher.kuota_terpakai || 0) + " kali. Kuota baru minimal " + Math.max(1, voucher.kuota_terpakai || 0) + ".";

        // Format dates YYYY-MM-DD
        document.getElementById('edit_berlaku_mulai').value = voucher.berlaku_mulai ? voucher.berlaku_mulai.substring(0, 10) : '';
        document.getElementById('edit_berlaku_sampai').value = voucher.berlaku_sampai ? voucher.berlaku_sampai.substring(0, 10) : '';
        document.getElementById('edit_deskripsi').value = voucher.deskripsi || '';
        document.getElementById('edit_status_aktif').checked = Boolean(voucher.status_aktif);

        toggleTipeInput('edit');

        document.getElementById('editVoucherModal').classList.remove('hidden');
        if (window.lucide) {
            lucide.createIcons();
        }
    }

    function closeEditModal() {
        document.getElementById('editVoucherModal').classList.add('hidden');
    }
</script>
@endpush
@endsection
