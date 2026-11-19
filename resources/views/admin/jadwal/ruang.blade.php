@extends('layouts.admin')

@section('title', 'Manajemen Ruang Seleksi')
@section('header_title', 'Ruang Seleksi')

@section('content')
<div class="space-y-6">

    <!-- Header Actions & Breadcrumb -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 mb-1">
                <a href="{{ route('admin.jadwal.index') }}" class="text-xs font-bold text-slate-500 hover:text-emerald-600 transition-colors">Jadwal Seleksi</a>
                <span class="text-xs text-slate-400">/</span>
                <span class="text-xs font-bold text-slate-800 dark:text-white">Manajemen Ruang</span>
            </div>
            <h2 class="text-xl font-extrabold text-slate-800 dark:text-white">Ruang Seleksi & Kapasitas</h2>
            <p class="text-xs text-slate-500 dark:text-slate-400">Kelola laboratorium, ruang kelas, dan kapasitas daya tampung tes PPDB.</p>
        </div>
        <div class="flex items-center gap-2">
            <button onclick="document.getElementById('modal-tambah-ruang').classList.remove('hidden')" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-[#fa2f79] hover:bg-[#e11d48] text-white text-xs font-bold shadow-md shadow-pink-500/20 transition-colors">
                <i data-lucide="plus" class="w-4 h-4"></i>
                <span>Tambah Ruang Baru</span>
            </button>
        </div>
    </div>

    <!-- Statistik Ringkasan Ruang -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="p-4 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-sm flex items-center justify-between">
            <div>
                <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Total Ruang</span>
                <h3 class="text-2xl font-black text-slate-800 dark:text-white mt-0.5">{{ $totalRuang }}</h3>
                <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-1">Terdaftar di sistem</p>
            </div>
            <div class="w-12 h-12 rounded-2xl bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 flex items-center justify-center">
                <i data-lucide="door-open" class="w-6 h-6"></i>
            </div>
        </div>

        <div class="p-4 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-sm flex items-center justify-between">
            <div>
                <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Ruang Aktif</span>
                <h3 class="text-2xl font-black text-emerald-600 dark:text-emerald-400 mt-0.5">{{ $ruangAktif }}</h3>
                <p class="text-[11px] text-emerald-600 dark:text-emerald-400 mt-1 font-medium">Siap digunakan penempatan</p>
            </div>
            <div class="w-12 h-12 rounded-2xl bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 flex items-center justify-center">
                <i data-lucide="check-circle" class="w-6 h-6"></i>
            </div>
        </div>

        <div class="p-4 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-sm flex items-center justify-between">
            <div>
                <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Total Kapasitas</span>
                <h3 class="text-2xl font-black text-indigo-600 dark:text-indigo-400 mt-0.5">{{ $totalKapasitas }}</h3>
                <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-1">Daya tampung seluruh ruang aktif</p>
            </div>
            <div class="w-12 h-12 rounded-2xl bg-indigo-500/10 text-indigo-600 dark:text-indigo-400 flex items-center justify-center">
                <i data-lucide="users" class="w-6 h-6"></i>
            </div>
        </div>
    </div>

    <!-- Tabel Daftar Ruang -->
    <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-sm overflow-hidden">
        <div class="p-4 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between">
            <h3 class="text-sm font-bold text-slate-800 dark:text-white">Daftar Ruang Seleksi</h3>
            <span class="text-xs text-slate-400">Total: {{ $ruangs->total() }} ruang</span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-xs text-left">
                <thead class="bg-slate-50 dark:bg-slate-800/50 text-slate-500 dark:text-slate-400 font-bold uppercase tracking-wider text-[10px] border-b border-slate-200 dark:border-slate-800">
                    <tr>
                        <th class="py-3 px-4">Kode & Nama Ruang</th>
                        <th class="py-3 px-4">Gedung / Lokasi</th>
                        <th class="py-3 px-4 text-center">Kapasitas Maksimal</th>
                        <th class="py-3 px-4 text-center">Total Penempatan</th>
                        <th class="py-3 px-4">Keterangan</th>
                        <th class="py-3 px-4 text-center">Status</th>
                        <th class="py-3 px-4 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800 text-slate-700 dark:text-slate-300">
                    @forelse($ruangs as $r)
                    <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-800/40 transition-colors">
                        <td class="py-3.5 px-4">
                            <span class="font-mono font-bold text-emerald-600 dark:text-emerald-400 text-xs block">{{ $r->kode_ruang }}</span>
                            <span class="font-bold text-slate-800 dark:text-white">{{ $r->nama_ruang }}</span>
                        </td>
                        <td class="py-3.5 px-4">
                            <span class="font-medium">{{ $r->gedung ?: '-' }}</span>
                        </td>
                        <td class="py-3.5 px-4 text-center">
                            <span class="inline-flex items-center gap-1 px-3 py-1 rounded-full text-xs font-black bg-indigo-50 dark:bg-indigo-950/50 text-indigo-700 dark:text-indigo-300 border border-indigo-200/50">
                                {{ $r->kapasitas }} Peserta
                            </span>
                        </td>
                        <td class="py-3.5 px-4 text-center">
                            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-semibold bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300">
                                {{ $r->penempatan_count }} pendaftar
                            </span>
                        </td>
                        <td class="py-3.5 px-4">
                            <span class="text-[11px] text-slate-500 line-clamp-1">{{ $r->keterangan ?: '-' }}</span>
                        </td>
                        <td class="py-3.5 px-4 text-center">
                            @if($r->status_aktif)
                            <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-emerald-100 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-300">
                                Aktif
                            </span>
                            @else
                            <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-slate-200 dark:bg-slate-800 text-slate-600 dark:text-slate-400">
                                Nonaktif
                            </span>
                            @endif
                        </td>
                        <td class="py-3.5 px-4 text-right">
                            <div class="flex items-center justify-end gap-1.5">
                                <button type="button" onclick="openEditModal({{ json_encode($r) }})" class="p-1.5 rounded-lg text-slate-500 hover:text-slate-800 hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors" title="Edit Ruang">
                                    <i data-lucide="pencil" class="w-4 h-4"></i>
                                </button>
                                <form action="{{ route('admin.ruang.destroy', $r->id_ruang) }}" method="POST" onsubmit="return confirm('Hapus ruang seleksi {{ $r->nama_ruang }}?')" class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" title="Hapus Ruang" class="p-1.5 rounded-lg text-rose-500 hover:text-rose-700 hover:bg-rose-50 dark:hover:bg-rose-950/50 transition-colors {{ $r->penempatan_count > 0 ? 'opacity-40 cursor-not-allowed' : '' }}" {{ $r->penempatan_count > 0 ? 'disabled' : '' }}>
                                        <i data-lucide="trash-2" class="w-4 h-4"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="py-8 text-center text-slate-400">
                            <i data-lucide="door-closed" class="w-8 h-8 mx-auto mb-2 text-slate-300"></i>
                            <p class="font-semibold text-xs">Belum ada ruang seleksi yang terdaftar.</p>
                            <button onclick="document.getElementById('modal-tambah-ruang').classList.remove('hidden')" class="inline-block mt-2 text-emerald-600 hover:underline text-xs font-bold">+ Tambah Ruang Baru</button>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($ruangs->hasPages())
        <div class="p-4 border-t border-slate-100 dark:border-slate-800">
            {{ $ruangs->links() }}
        </div>
        @endif
    </div>

</div>

<!-- Modal Tambah Ruang -->
<div id="modal-tambah-ruang" class="fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-sm hidden flex items-center justify-center p-4">
    <div class="bg-white dark:bg-slate-900 rounded-2xl max-w-lg w-full border border-slate-200 dark:border-slate-800 shadow-xl overflow-hidden animate-in fade-in zoom-in-95 duration-150">
        <div class="p-5 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between">
            <h3 class="text-sm font-extrabold text-slate-800 dark:text-white">Tambah Ruang Seleksi</h3>
            <button onclick="document.getElementById('modal-tambah-ruang').classList.add('hidden')" class="p-1 rounded-lg text-slate-400 hover:text-slate-600 dark:hover:text-white">
                <i data-lucide="x" class="w-4 h-4"></i>
            </button>
        </div>
        <form action="{{ route('admin.ruang.store') }}" method="POST" class="p-5 space-y-4">
            @csrf
            <div>
                <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase mb-1">
                    Kode Ruang <span class="text-rose-500">*</span>
                </label>
                <input type="text" name="kode_ruang" required placeholder="Contoh: R-LAB-01" class="w-full px-3 py-2 text-xs rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-slate-800 dark:text-white focus:ring-2 focus:ring-emerald-500">
            </div>
            <div>
                <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase mb-1">
                    Nama Ruang <span class="text-rose-500">*</span>
                </label>
                <input type="text" name="nama_ruang" required placeholder="Contoh: Lab Komputer TKJ 1" class="w-full px-3 py-2 text-xs rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-slate-800 dark:text-white focus:ring-2 focus:ring-emerald-500">
            </div>
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase mb-1">
                        Gedung / Lantai
                    </label>
                    <input type="text" name="gedung" placeholder="Gedung B Lt. 2" class="w-full px-3 py-2 text-xs rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-slate-800 dark:text-white focus:ring-2 focus:ring-emerald-500">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase mb-1">
                        Kapasitas Maksimal <span class="text-rose-500">*</span>
                    </label>
                    <input type="number" name="kapasitas" min="1" max="500" value="30" required class="w-full px-3 py-2 text-xs rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-slate-800 dark:text-white focus:ring-2 focus:ring-emerald-500">
                </div>
            </div>
            <div>
                <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase mb-1">
                    Keterangan
                </label>
                <textarea name="keterangan" rows="2" placeholder="Fasilitas AC, 35 PC, Proyektor..." class="w-full px-3 py-2 text-xs rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-slate-800 dark:text-white focus:ring-2 focus:ring-emerald-500"></textarea>
            </div>
            <div class="flex items-center gap-2 pt-1">
                <input type="checkbox" name="status_aktif" value="1" id="status_aktif_tambah" checked class="rounded border-slate-300 text-emerald-600 focus:ring-emerald-500">
                <label for="status_aktif_tambah" class="text-xs font-semibold text-slate-700 dark:text-slate-300">Ruang Aktif (dapat digunakan untuk penempatan)</label>
            </div>
            <div class="border-t border-slate-100 dark:border-slate-800 pt-4 flex justify-end gap-2">
                <button type="button" onclick="document.getElementById('modal-tambah-ruang').classList.add('hidden')" class="px-4 py-2 rounded-xl border border-slate-200 text-xs font-bold text-slate-600 hover:bg-slate-50">Batal</button>
                <button type="submit" class="px-5 py-2 rounded-xl bg-[#fa2f79] hover:bg-[#e11d48] text-white text-xs font-bold transition-colors">Simpan Ruang</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Edit Ruang -->
<div id="modal-edit-ruang" class="fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-sm hidden flex items-center justify-center p-4">
    <div class="bg-white dark:bg-slate-900 rounded-2xl max-w-lg w-full border border-slate-200 dark:border-slate-800 shadow-xl overflow-hidden animate-in fade-in zoom-in-95 duration-150">
        <div class="p-5 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between">
            <h3 class="text-sm font-extrabold text-slate-800 dark:text-white">Ubah Ruang Seleksi</h3>
            <button onclick="document.getElementById('modal-edit-ruang').classList.add('hidden')" class="p-1 rounded-lg text-slate-400 hover:text-slate-600 dark:hover:text-white">
                <i data-lucide="x" class="w-4 h-4"></i>
            </button>
        </div>
        <form id="form-edit-ruang" action="" method="POST" class="p-5 space-y-4">
            @csrf
            @method('PUT')
            <div>
                <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase mb-1">
                    Kode Ruang <span class="text-rose-500">*</span>
                </label>
                <input type="text" id="edit_kode_ruang" name="kode_ruang" required class="w-full px-3 py-2 text-xs rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-slate-800 dark:text-white focus:ring-2 focus:ring-emerald-500">
            </div>
            <div>
                <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase mb-1">
                    Nama Ruang <span class="text-rose-500">*</span>
                </label>
                <input type="text" id="edit_nama_ruang" name="nama_ruang" required class="w-full px-3 py-2 text-xs rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-slate-800 dark:text-white focus:ring-2 focus:ring-emerald-500">
            </div>
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase mb-1">
                        Gedung / Lantai
                    </label>
                    <input type="text" id="edit_gedung" name="gedung" class="w-full px-3 py-2 text-xs rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-slate-800 dark:text-white focus:ring-2 focus:ring-emerald-500">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase mb-1">
                        Kapasitas Maksimal <span class="text-rose-500">*</span>
                    </label>
                    <input type="number" id="edit_kapasitas" name="kapasitas" min="1" max="500" required class="w-full px-3 py-2 text-xs rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-slate-800 dark:text-white focus:ring-2 focus:ring-emerald-500">
                </div>
            </div>
            <div>
                <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase mb-1">
                    Keterangan
                </label>
                <textarea id="edit_keterangan" name="keterangan" rows="2" class="w-full px-3 py-2 text-xs rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-slate-800 dark:text-white focus:ring-2 focus:ring-emerald-500"></textarea>
            </div>
            <div class="flex items-center gap-2 pt-1">
                <input type="checkbox" name="status_aktif" value="1" id="edit_status_aktif" class="rounded border-slate-300 text-emerald-600 focus:ring-emerald-500">
                <label for="edit_status_aktif" class="text-xs font-semibold text-slate-700 dark:text-slate-300">Ruang Aktif (dapat digunakan untuk penempatan)</label>
            </div>
            <div class="border-t border-slate-100 dark:border-slate-800 pt-4 flex justify-end gap-2">
                <button type="button" onclick="document.getElementById('modal-edit-ruang').classList.add('hidden')" class="px-4 py-2 rounded-xl border border-slate-200 text-xs font-bold text-slate-600 hover:bg-slate-50">Batal</button>
                <button type="submit" class="px-5 py-2 rounded-xl bg-[#fa2f79] hover:bg-[#e11d48] text-white text-xs font-bold transition-colors">Simpan Perubahan</button>
            </div>
        </form>
    </div>
</div>

<script>
    function openEditModal(ruang) {
        document.getElementById('form-edit-ruang').action = '/admin/ruang-seleksi/' + ruang.id_ruang;
        document.getElementById('edit_kode_ruang').value = ruang.kode_ruang;
        document.getElementById('edit_nama_ruang').value = ruang.nama_ruang;
        document.getElementById('edit_gedung').value = ruang.gedung || '';
        document.getElementById('edit_kapasitas').value = ruang.kapasitas;
        document.getElementById('edit_keterangan').value = ruang.keterangan || '';
        document.getElementById('edit_status_aktif').checked = Boolean(ruang.status_aktif);
        document.getElementById('modal-edit-ruang').classList.remove('hidden');
    }
</script>
@endsection

