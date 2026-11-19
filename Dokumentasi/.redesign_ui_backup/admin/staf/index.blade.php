@extends('layouts.admin')

@section('title', 'Manajemen Akun Staf - SMK Islam Bina Khalifah Bangsa')
@section('header_title', 'Manajemen Akun Staf & Hak Akses')

@section('content')
<div class="space-y-6">

    <!-- Header & Action -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-xl font-black text-navy-950 dark:text-white tracking-tight">Akun Staf & Hak Akses</h1>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">
                Kelola hak akses administratif untuk Petugas Tata Usaha (TU) dan Kepala Sekolah
            </p>
        </div>
        <button onclick="document.getElementById('modalTambahStaf').classList.remove('hidden')" class="px-4 py-2.5 rounded-xl bg-pink-600 hover:bg-pink-700 text-white font-bold text-xs transition flex items-center justify-center gap-2 shadow-sm shrink-0">
            <i data-lucide="user-plus" class="w-4 h-4"></i> Tambah Akun Staf
        </button>
    </div>

    @if(session('success'))
    <div class="p-4 rounded-2xl bg-emerald-50 dark:bg-emerald-950/50 border border-emerald-200 dark:border-emerald-800 flex items-center gap-3 text-xs text-emerald-800 dark:text-emerald-200">
        <i data-lucide="check-circle-2" class="w-4 h-4 text-emerald-600 shrink-0"></i>
        <span>{{ session('success') }}</span>
    </div>
    @endif

    @if(session('error'))
    <div class="p-4 rounded-2xl bg-rose-50 dark:bg-rose-950/50 border border-rose-200 dark:border-rose-800 flex items-center gap-3 text-xs text-rose-800 dark:text-rose-200">
        <i data-lucide="alert-circle" class="w-4 h-4 text-rose-600 shrink-0"></i>
        <span>{{ session('error') }}</span>
    </div>
    @endif

    <!-- 3 KPI Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200/90 dark:border-slate-800 p-5 shadow-sm transition hover:shadow-md">
            <span class="text-xs font-bold text-slate-500 dark:text-slate-400">Total Akun Staf</span>
            <div class="mt-2 text-2xl font-black text-navy-950 dark:text-white">{{ $stats['total'] }}</div>
        </div>
        <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200/90 dark:border-slate-800 p-5 shadow-sm transition hover:shadow-md">
            <span class="text-xs font-bold text-pink-600 dark:text-pink-400">Petugas Tata Usaha (TU)</span>
            <div class="mt-2 text-2xl font-black text-pink-600 dark:text-pink-400">{{ $stats['tu'] }}</div>
        </div>
        <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200/90 dark:border-slate-800 p-5 shadow-sm transition hover:shadow-md">
            <span class="text-xs font-bold text-purple-600 dark:text-purple-400">Kepala Sekolah</span>
            <div class="mt-2 text-2xl font-black text-purple-600 dark:text-purple-400">{{ $stats['kepsek'] }}</div>
        </div>
    </div>

    <!-- Filter & Pencarian -->
    <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200/90 dark:border-slate-800 p-4 shadow-sm">
        <form method="GET" action="{{ route('admin.staf.index') }}" class="flex flex-col sm:flex-row gap-3">
            <div class="flex-1">
                <input type="text" name="q" value="{{ request('q') }}" placeholder="Cari nama staf, username, email..." class="w-full text-xs px-3 py-2 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-slate-800 dark:text-white focus:ring-2 focus:ring-pink-500 focus:outline-none">
            </div>
            <div class="w-full sm:w-48">
                <select name="role" class="w-full text-xs px-3 py-2 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-slate-800 dark:text-white focus:ring-2 focus:ring-pink-500 focus:outline-none">
                    <option value="">Semua Peran</option>
                    <option value="tu" {{ request('role') === 'tu' ? 'selected' : '' }}>Petugas TU</option>
                    <option value="kepala_sekolah" {{ request('role') === 'kepala_sekolah' ? 'selected' : '' }}>Kepala Sekolah</option>
                </select>
            </div>
            <button type="submit" class="text-xs font-bold px-4 py-2 bg-pink-600 hover:bg-pink-700 text-white rounded-xl transition flex items-center justify-center gap-1.5 shadow-sm">
                <i data-lucide="filter" class="w-3.5 h-3.5"></i> Filter
            </button>
            <a href="{{ route('admin.staf.index') }}" class="text-xs px-3 py-2 bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-600 dark:text-slate-300 rounded-xl transition text-center">
                Reset
            </a>
        </form>
    </div>

    <!-- Tabel Daftar Staf -->
    <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200/90 dark:border-slate-800 overflow-hidden shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="border-b border-slate-200 dark:border-slate-800 bg-navy-50/60 dark:bg-slate-800/40 text-navy-950 dark:text-slate-300 font-bold uppercase text-[10px] tracking-wider">
                        <th class="py-3 px-4">Nama Staf</th>
                        <th class="py-3 px-4">Username</th>
                        <th class="py-3 px-4">Email</th>
                        <th class="py-3 px-4">Hak Akses / Peran</th>
                        <th class="py-3 px-4">Terdaftar</th>
                        <th class="py-3 px-4 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800/60">
                    @forelse($staf as $s)
                    <tr class="hover:bg-slate-50/70 dark:hover:bg-slate-800/30 transition">
                        <td class="py-3 px-4">
                            <span class="font-bold text-navy-950 dark:text-white">{{ $s->nama_admin }}</span>
                            @if($s->id_admin === $currentAdmin->id_admin)
                                <span class="ml-1.5 px-2 py-0.5 rounded text-[9px] font-bold bg-pink-100 text-pink-800 dark:bg-pink-950/60 dark:text-pink-300">Akun Anda</span>
                            @endif
                        </td>
                        <td class="py-3 px-4 font-mono text-slate-600 dark:text-slate-300">{{ $s->username }}</td>
                        <td class="py-3 px-4 text-slate-500 dark:text-slate-400">{{ $s->email }}</td>
                        <td class="py-3 px-4">
                            @if($s->role === 'kepala_sekolah')
                                <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-purple-100 text-purple-800 dark:bg-purple-950/60 dark:text-purple-300 border border-purple-200 dark:border-purple-800">
                                    Kepala Sekolah
                                </span>
                            @else
                                <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-navy-50 text-navy-950 dark:bg-navy-900/60 dark:text-navy-300 border border-navy-200/60 dark:border-navy-800">
                                    Petugas TU
                                </span>
                            @endif
                        </td>
                        <td class="py-3 px-4 text-[11px] text-slate-400">
                            {{ $s->created_at ? $s->created_at->format('d/m/Y') : '-' }}
                        </td>
                        <td class="py-3 px-4 text-center">
                            <div class="flex items-center justify-center gap-1.5">
                                <button onclick="bukaModalEdit({{ json_encode($s) }})" class="p-1.5 rounded-lg text-slate-500 hover:text-pink-600 hover:bg-slate-100 dark:hover:bg-slate-800 transition" title="Edit Akun">
                                    <i data-lucide="edit-3" class="w-4 h-4"></i>
                                </button>
                                @if($s->id_admin !== $currentAdmin->id_admin)
                                <form action="{{ route('admin.staf.destroy', $s->id_admin) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus akun staf {{ $s->nama_admin }}?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="p-1.5 rounded-lg text-slate-400 hover:text-rose-600 hover:bg-rose-50 dark:hover:bg-rose-950/40 transition" title="Hapus Akun">
                                        <i data-lucide="trash-2" class="w-4 h-4"></i>
                                    </button>
                                </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="py-8 text-center text-slate-400 italic">
                            Tidak ditemukan akun staf yang sesuai.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($staf->hasPages())
        <div class="px-5 py-4 border-t border-slate-200 dark:border-slate-800">
            {{ $staf->links() }}
        </div>
        @endif
    </div>

</div>

<!-- Modal Tambah Staf -->
<div id="modalTambahStaf" class="fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-sm hidden flex items-center justify-center p-4">
    <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200 dark:border-slate-800 p-6 max-w-md w-full shadow-xl animate-in fade-in zoom-in-95 duration-150">
        <div class="flex items-center justify-between pb-4 border-b border-slate-100 dark:border-slate-800">
            <h3 class="text-sm font-bold text-navy-950 dark:text-white flex items-center gap-2">
                <i data-lucide="user-plus" class="w-4 h-4 text-pink-500"></i> Tambah Akun Staf Baru
            </h3>
            <button onclick="document.getElementById('modalTambahStaf').classList.add('hidden')" class="p-1 rounded-lg text-slate-400 hover:text-white hover:bg-slate-800">
                <i data-lucide="x" class="w-4 h-4"></i>
            </button>
        </div>

        <form action="{{ route('admin.staf.store') }}" method="POST" class="mt-4 space-y-3">
            @csrf
            <div>
                <label class="block text-[11px] font-bold text-slate-600 dark:text-slate-400 mb-1">Nama Lengkap & Gelar</label>
                <input type="text" name="nama_admin" required placeholder="Contoh: Budi Santoso, S.Kom" class="w-full text-xs px-3 py-2 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-slate-800 dark:text-white focus:ring-2 focus:ring-pink-500 focus:outline-none">
            </div>
            <div>
                <label class="block text-[11px] font-bold text-slate-600 dark:text-slate-400 mb-1">Username Login</label>
                <input type="text" name="username" required placeholder="Contoh: tu_budi" class="w-full text-xs px-3 py-2 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-slate-800 dark:text-white focus:ring-2 focus:ring-pink-500 focus:outline-none">
            </div>
            <div>
                <label class="block text-[11px] font-bold text-slate-600 dark:text-slate-400 mb-1">Alamat Email</label>
                <input type="email" name="email" required placeholder="Contoh: budi@smkib.com" class="w-full text-xs px-3 py-2 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-slate-800 dark:text-white focus:ring-2 focus:ring-pink-500 focus:outline-none">
            </div>
            <div>
                <label class="block text-[11px] font-bold text-slate-600 dark:text-slate-400 mb-1">Hak Akses / Peran</label>
                <select name="role" required class="w-full text-xs px-3 py-2 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-slate-800 dark:text-white focus:ring-2 focus:ring-pink-500 focus:outline-none">
                    <option value="tu">Petugas Tata Usaha (TU)</option>
                    @if($currentAdmin->role === 'kepala_sekolah')
                    <option value="kepala_sekolah">Kepala Sekolah</option>
                    @endif
                </select>
            </div>
            <div>
                <label class="block text-[11px] font-bold text-slate-600 dark:text-slate-400 mb-1">Kata Sandi</label>
                <input type="password" name="password" required minlength="6" placeholder="Minimal 6 karakter" class="w-full text-xs px-3 py-2 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-slate-800 dark:text-white focus:ring-2 focus:ring-pink-500 focus:outline-none">
            </div>
            <div>
                <label class="block text-[11px] font-bold text-slate-600 dark:text-slate-400 mb-1">Konfirmasi Kata Sandi</label>
                <input type="password" name="password_confirmation" required minlength="6" placeholder="Ulangi kata sandi" class="w-full text-xs px-3 py-2 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-slate-800 dark:text-white focus:ring-2 focus:ring-pink-500 focus:outline-none">
            </div>

            <div class="pt-3 flex gap-2">
                <button type="submit" class="flex-1 py-2 rounded-xl bg-pink-600 hover:bg-pink-700 text-white font-bold text-xs transition">
                    Simpan Akun
                </button>
                <button type="button" onclick="document.getElementById('modalTambahStaf').classList.add('hidden')" class="px-4 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-600 dark:text-slate-300 font-bold text-xs transition">
                    Batal
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Edit Staf -->
<div id="modalEditStaf" class="fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-sm hidden flex items-center justify-center p-4">
    <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200 dark:border-slate-800 p-6 max-w-md w-full shadow-xl animate-in fade-in zoom-in-95 duration-150">
        <div class="flex items-center justify-between pb-4 border-b border-slate-100 dark:border-slate-800">
            <h3 class="text-sm font-bold text-navy-950 dark:text-white flex items-center gap-2">
                <i data-lucide="edit-3" class="w-4 h-4 text-pink-500"></i> Edit Akun Staf
            </h3>
            <button onclick="document.getElementById('modalEditStaf').classList.add('hidden')" class="p-1 rounded-lg text-slate-400 hover:text-white hover:bg-slate-800">
                <i data-lucide="x" class="w-4 h-4"></i>
            </button>
        </div>

        <form id="formEditStaf" method="POST" class="mt-4 space-y-3">
            @csrf
            @method('PUT')
            <div>
                <label class="block text-[11px] font-bold text-slate-600 dark:text-slate-400 mb-1">Nama Lengkap & Gelar</label>
                <input type="text" id="edit_nama" name="nama_admin" required class="w-full text-xs px-3 py-2 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-slate-800 dark:text-white focus:ring-2 focus:ring-pink-500 focus:outline-none">
            </div>
            <div>
                <label class="block text-[11px] font-bold text-slate-600 dark:text-slate-400 mb-1">Username Login</label>
                <input type="text" id="edit_username" name="username" required class="w-full text-xs px-3 py-2 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-slate-800 dark:text-white focus:ring-2 focus:ring-pink-500 focus:outline-none">
            </div>
            <div>
                <label class="block text-[11px] font-bold text-slate-600 dark:text-slate-400 mb-1">Alamat Email</label>
                <input type="email" id="edit_email" name="email" required class="w-full text-xs px-3 py-2 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-slate-800 dark:text-white focus:ring-2 focus:ring-pink-500 focus:outline-none">
            </div>
            <div>
                <label class="block text-[11px] font-bold text-slate-600 dark:text-slate-400 mb-1">Hak Akses / Peran</label>
                <select id="edit_role" name="role" required class="w-full text-xs px-3 py-2 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-slate-800 dark:text-white focus:ring-2 focus:ring-pink-500 focus:outline-none">
                    <option value="tu">Petugas Tata Usaha (TU)</option>
                    @if($currentAdmin->role === 'kepala_sekolah')
                    <option value="kepala_sekolah">Kepala Sekolah</option>
                    @endif
                </select>
            </div>
            <div>
                <label class="block text-[11px] font-bold text-slate-600 dark:text-slate-400 mb-1">Kata Sandi Baru (Kosongkan bila tidak ingin diubah)</label>
                <input type="password" name="password" minlength="6" placeholder="Biarkan kosong jika tidak ganti" class="w-full text-xs px-3 py-2 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-slate-800 dark:text-white focus:ring-2 focus:ring-pink-500 focus:outline-none">
            </div>
            <div>
                <label class="block text-[11px] font-bold text-slate-600 dark:text-slate-400 mb-1">Konfirmasi Kata Sandi Baru</label>
                <input type="password" name="password_confirmation" minlength="6" placeholder="Ulangi kata sandi baru" class="w-full text-xs px-3 py-2 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-slate-800 dark:text-white focus:ring-2 focus:ring-pink-500 focus:outline-none">
            </div>

            <div class="pt-3 flex gap-2">
                <button type="submit" class="flex-1 py-2 rounded-xl bg-pink-600 hover:bg-pink-700 text-white font-bold text-xs transition">
                    Perbarui Akun
                </button>
                <button type="button" onclick="document.getElementById('modalEditStaf').classList.add('hidden')" class="px-4 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-600 dark:text-slate-300 font-bold text-xs transition">
                    Batal
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    function bukaModalEdit(staf) {
        document.getElementById('formEditStaf').action = "{{ url('admin/staf') }}/" + staf.id_admin;
        document.getElementById('edit_nama').value = staf.nama_admin;
        document.getElementById('edit_username').value = staf.username;
        document.getElementById('edit_email').value = staf.email;
        document.getElementById('edit_role').value = staf.role;
        document.getElementById('modalEditStaf').classList.remove('hidden');
    }
</script>
@endsection
