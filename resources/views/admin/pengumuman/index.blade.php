@extends('layouts.admin')

@section('title', 'Banner Pengumuman & Pemeliharaan')
@section('header_title', 'Kelola Pengumuman & Maintenance')

@section('content')
<div class="space-y-6">

    <!-- Mode Pemeliharaan (Maintenance Mode) Toggle Card -->
    <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 p-6 shadow-sm">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div class="flex items-start gap-4">
                <div class="w-12 h-12 rounded-xl {{ $pengaturan->mode_maintenance ? 'bg-rose-100 text-rose-600 dark:bg-rose-950/60 dark:text-rose-400' : 'bg-pink-100 text-[#fa2f79] dark:bg-pink-950/60 dark:text-pink-400' }} flex items-center justify-center shrink-0">
                    <i data-lucide="{{ $pengaturan->mode_maintenance ? 'alert-triangle' : 'shield-check' }}" class="w-6 h-6"></i>
                </div>
                <div>
                    <div class="flex items-center gap-2">
                        <h2 class="text-base font-bold text-slate-800 dark:text-white">Status Mode Pemeliharaan (Maintenance)</h2>
                        <span class="px-2.5 py-0.5 rounded-full text-xs font-bold {{ $pengaturan->mode_maintenance ? 'bg-rose-500 text-white animate-pulse' : 'bg-[#fa2f79] text-white' }}">
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
                <button type="submit" class="px-5 py-2.5 rounded-xl font-bold text-xs flex items-center gap-2 transition-all shadow-sm {{ $pengaturan->mode_maintenance ? 'bg-[#fa2f79] hover:bg-[#e11d48] text-white' : 'bg-rose-600 hover:bg-rose-700 text-white' }}">
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
                    <i data-lucide="megaphone" class="w-5 h-5 text-[#fa2f79]"></i>
                    <h3 class="text-sm font-bold text-slate-800 dark:text-white">Buat Pengumuman Baru</h3>
                </div>

                <form action="{{ route('admin.pengumuman.simpan') }}" method="POST" class="space-y-4">
                    @csrf
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Judul Pengumuman *</label>
                        <input type="text" name="judul" required placeholder="Contoh: Jadwal Tes Wawancara Gelombang 1"
                               class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-xs text-slate-800 dark:text-white focus:outline-none focus:ring-2 focus:ring-pink-400">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Kategori / Tipe *</label>
                        <select name="tipe" required class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-xs text-slate-800 dark:text-white focus:outline-none focus:ring-2 focus:ring-pink-400">
                            <option value="info">Informasi (Biru)</option>
                            <option value="penting">Penting (Merah)</option>
                            <option value="peringatan">Peringatan (Kuning)</option>
                            <option value="maintenance">Maintenance (Ungu)</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Isi Pesan / Maklumat *</label>
                        <textarea name="isi_pengumuman" rows="4" required placeholder="Tuliskan isi pengumuman atau instruksi untuk calon siswa..."
                                  class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-xs text-slate-800 dark:text-white focus:outline-none focus:ring-2 focus:ring-pink-400"></textarea>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Tanggal Mulai *</label>
                            <input type="date" name="tgl_mulai" required value="{{ date('Y-m-d') }}"
                                   class="w-full px-3 py-2 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-xs text-slate-800 dark:text-white focus:outline-none focus:ring-2 focus:ring-pink-400">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Tanggal Selesai *</label>
                            <input type="date" name="tgl_selesai" required value="{{ date('Y-m-d', strtotime('+30 days')) }}"
                                   class="w-full px-3 py-2 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-xs text-slate-800 dark:text-white focus:outline-none focus:ring-2 focus:ring-pink-400">
                        </div>
                    </div>

                    <div class="space-y-2 pt-2 border-t border-slate-100 dark:border-slate-800">
                        <label class="flex items-center gap-2 text-xs text-slate-700 dark:text-slate-300 cursor-pointer">
                            <input type="checkbox" name="tampil_di_banner" value="1" checked class="w-4 h-4 rounded text-[#fa2f79] focus:ring-[#fa2f79]">
                            <span>Tampilkan di Running Banner / Landing Page</span>
                        </label>
                        <label class="flex items-center gap-2 text-xs text-slate-700 dark:text-slate-300 cursor-pointer">
                            <input type="checkbox" name="status_aktif" value="1" checked class="w-4 h-4 rounded text-[#fa2f79] focus:ring-[#fa2f79]">
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
                        <i data-lucide="list" class="w-5 h-5 text-[#fa2f79]"></i>
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
                                            <span class="px-2 py-0.5 rounded text-[10px] font-semibold bg-pink-100 text-pink-700 dark:bg-pink-950 dark:text-pink-400">Aktif</span>
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
                                <p class="text-xs text-slate-600 dark:text-slate-300 leading-relaxed whitespace-pre-line mb-3">{{ $item->isi_pengumuman }}</p>

                                {{-- Tombol Aksi --}}
                                <div class="flex items-center gap-2 pt-2 border-t border-slate-100 dark:border-slate-800">
                                    {{-- Edit --}}
                                    <button type="button"
                                        onclick="bukaModalEditPengumuman({{ json_encode($item) }})"
                                        class="flex items-center gap-1 px-2.5 py-1.5 rounded-lg bg-pink-50 hover:bg-pink-100 dark:bg-pink-950/40 dark:hover:bg-pink-900/60 text-[#fa2f79] text-[10px] font-bold transition-colors">
                                        <i data-lucide="edit-3" class="w-3.5 h-3.5"></i> Edit
                                    </button>

                                    {{-- Toggle Aktif/Nonaktif --}}
                                    <form action="{{ route('admin.pengumuman.toggle', $item->id_pengumuman) }}" method="POST">
                                        @csrf
                                        <button type="submit"
                                            class="flex items-center gap-1 px-2.5 py-1.5 rounded-lg text-[10px] font-bold transition-colors {{ $item->status_aktif ? 'bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-600 dark:text-slate-300' : 'bg-green-50 hover:bg-green-100 dark:bg-green-950/40 text-green-700 dark:text-green-400' }}">
                                            <i data-lucide="{{ $item->status_aktif ? 'eye-off' : 'eye' }}" class="w-3.5 h-3.5"></i>
                                            {{ $item->status_aktif ? 'Nonaktifkan' : 'Aktifkan' }}
                                        </button>
                                    </form>

                                    {{-- Hapus --}}
                                    <form action="{{ route('admin.pengumuman.hapus', $item->id_pengumuman) }}" method="POST"
                                        onsubmit="return confirm('Hapus pengumuman \'{{ addslashes($item->judul) }}\'?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                            class="flex items-center gap-1 px-2.5 py-1.5 rounded-lg bg-rose-50 hover:bg-rose-100 dark:bg-rose-950/40 dark:hover:bg-rose-900/60 text-rose-600 dark:text-rose-400 text-[10px] font-bold transition-colors">
                                            <i data-lucide="trash-2" class="w-3.5 h-3.5"></i> Hapus
                                        </button>
                                    </form>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    </div>

</div>

{{-- MODAL EDIT PENGUMUMAN --}}
<div id="modalEditPengumuman" class="fixed inset-0 z-50 bg-slate-950/75 backdrop-blur-sm hidden flex items-center justify-center p-4">
    <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200 dark:border-slate-800 p-6 max-w-lg w-full shadow-xl max-h-[90vh] overflow-y-auto">
        <div class="flex items-center justify-between pb-4 border-b border-slate-100 dark:border-slate-800 mb-4">
            <h3 class="text-sm font-bold text-slate-800 dark:text-white flex items-center gap-2">
                <i data-lucide="edit-3" class="w-4 h-4 text-[#fa2f79]"></i> Edit Pengumuman
            </h3>
            <button onclick="tutupModalEditPengumuman()" class="p-1 rounded-lg text-slate-400 hover:text-white hover:bg-slate-800">
                <i data-lucide="x" class="w-4 h-4"></i>
            </button>
        </div>

        <form id="formEditPengumuman" method="POST" class="space-y-3 text-xs">
            @csrf
            @method('PUT')

            <div>
                <label class="block font-bold text-slate-700 dark:text-slate-300 mb-1">Judul Pengumuman *</label>
                <input type="text" id="edit_judul" name="judul" required
                    class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-xs text-slate-800 dark:text-white focus:outline-none focus:ring-2 focus:ring-pink-400">
            </div>

            <div>
                <label class="block font-bold text-slate-700 dark:text-slate-300 mb-1">Kategori / Tipe *</label>
                <select id="edit_tipe" name="tipe" required
                    class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-xs text-slate-800 dark:text-white focus:outline-none focus:ring-2 focus:ring-pink-400">
                    <option value="info">Informasi (Biru)</option>
                    <option value="penting">Penting (Merah)</option>
                    <option value="peringatan">Peringatan (Kuning)</option>
                    <option value="maintenance">Maintenance (Ungu)</option>
                </select>
            </div>

            <div>
                <label class="block font-bold text-slate-700 dark:text-slate-300 mb-1">Isi / Konten Pengumuman *</label>
                <textarea id="edit_isi_pengumuman" name="isi_pengumuman" required rows="4"
                    class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-xs text-slate-800 dark:text-white focus:outline-none focus:ring-2 focus:ring-pink-400 resize-none"></textarea>
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block font-bold text-slate-700 dark:text-slate-300 mb-1">Tanggal Mulai *</label>
                    <input type="date" id="edit_tgl_mulai" name="tgl_mulai" required
                        class="w-full px-3 py-2.5 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-xs text-slate-800 dark:text-white focus:outline-none focus:ring-2 focus:ring-pink-400">
                </div>
                <div>
                    <label class="block font-bold text-slate-700 dark:text-slate-300 mb-1">Tanggal Selesai *</label>
                    <input type="date" id="edit_tgl_selesai" name="tgl_selesai" required
                        class="w-full px-3 py-2.5 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-xs text-slate-800 dark:text-white focus:outline-none focus:ring-2 focus:ring-pink-400">
                </div>
            </div>

            <div class="flex items-center gap-6 pt-1">
                <label class="flex items-center gap-2 text-xs text-slate-700 dark:text-slate-300 cursor-pointer">
                    <input type="checkbox" id="edit_tampil_di_banner" name="tampil_di_banner" value="1" class="w-4 h-4 rounded text-[#fa2f79] focus:ring-[#fa2f79]">
                    <span>Tampil di Banner</span>
                </label>
                <label class="flex items-center gap-2 text-xs text-slate-700 dark:text-slate-300 cursor-pointer">
                    <input type="checkbox" id="edit_status_aktif" name="status_aktif" value="1" class="w-4 h-4 rounded text-[#fa2f79] focus:ring-[#fa2f79]">
                    <span>Status Aktif</span>
                </label>
            </div>

            <div class="pt-3 flex gap-2">
                <button type="submit" class="flex-1 py-2.5 rounded-xl bg-[#fa2f79] hover:bg-[#e11d48] text-white font-bold text-xs shadow-md shadow-pink-500/25 transition-colors flex items-center justify-center gap-2">
                    <i data-lucide="save" class="w-4 h-4"></i> Simpan Perubahan
                </button>
                <button type="button" onclick="tutupModalEditPengumuman()" class="px-4 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-600 dark:text-slate-300 font-bold text-xs transition-colors">
                    Batal
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    function bukaModalEditPengumuman(item) {
        const form = document.getElementById('formEditPengumuman');
        form.action = "{{ url('admin/pengumuman') }}/" + item.id_pengumuman;

        document.getElementById('edit_judul').value          = item.judul || '';
        document.getElementById('edit_tipe').value           = item.tipe || 'info';
        document.getElementById('edit_isi_pengumuman').value = item.isi_pengumuman || '';
        document.getElementById('edit_tgl_mulai').value      = item.tgl_mulai ? item.tgl_mulai.substring(0, 10) : '';
        document.getElementById('edit_tgl_selesai').value    = item.tgl_selesai ? item.tgl_selesai.substring(0, 10) : '';
        document.getElementById('edit_tampil_di_banner').checked = Boolean(item.tampil_di_banner);
        document.getElementById('edit_status_aktif').checked     = Boolean(item.status_aktif);

        document.getElementById('modalEditPengumuman').classList.remove('hidden');
        if (window.lucide) lucide.createIcons();
    }

    function tutupModalEditPengumuman() {
        document.getElementById('modalEditPengumuman').classList.add('hidden');
    }

    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') tutupModalEditPengumuman();
    });
</script>
@endsection

