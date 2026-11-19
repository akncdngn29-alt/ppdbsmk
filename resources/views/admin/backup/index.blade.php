@extends('layouts.admin')

@section('title', 'Pencadangan & Pemulihan Basis Data')
@section('header_title', 'Pencadangan & Pemulihan Basis Data (Backup & Restore)')

@section('content')
<div class="space-y-6">

    <!-- Header & Action -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-xl font-black text-slate-800 dark:text-white tracking-tight">Pencadangan Basis Data</h1>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">
                Amankan data pendaftaran, biodata siswa, berkas, dan histori seleksi melalui dump SQL berkala
            </p>
        </div>
        <form action="{{ route('admin.backup.create') }}" method="POST">
            @csrf
            <button type="submit" class="px-5 py-2.5 rounded-xl bg-[#fa2f79] hover:bg-[#e11d48] text-white font-bold text-xs shadow-md shadow-pink-500/25 transition-colors flex items-center justify-center gap-2">
                <i data-lucide="database" class="w-4 h-4"></i> Buat Backup Sekarang (1-Klik)
            </button>
        </form>
    </div>

    <!-- Alert Sukses / Gagal -->
    @if(session('success'))
    <div class="p-4 rounded-xl bg-pink-50 dark:bg-pink-950/50 border border-pink-200 dark:border-pink-800 flex items-center gap-3 text-xs text-pink-800 dark:text-pink-200">
        <i data-lucide="check-circle-2" class="w-4 h-4 text-[#fa2f79] shrink-0"></i>
        <span>{{ session('success') }}</span>
    </div>
    @endif

    @if(session('error'))
    <div class="p-4 rounded-xl bg-rose-50 dark:bg-rose-950/50 border border-rose-200 dark:border-rose-800 flex items-center gap-3 text-xs text-rose-800 dark:text-rose-200">
        <i data-lucide="alert-circle" class="w-4 h-4 text-rose-600 shrink-0"></i>
        <span>{{ session('error') }}</span>
    </div>
    @endif

    <!-- 3 KPI Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 p-5 shadow-sm">
            <span class="text-xs font-bold text-slate-500 dark:text-slate-400">Nama Database Aktif</span>
            <div class="mt-2 text-xl font-mono font-black text-[#fa2f79] dark:text-pink-400 truncate">{{ $dbName }}</div>
            <div class="mt-1 text-[11px] text-slate-400">Driver MySQL / MariaDB</div>
        </div>
        <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 p-5 shadow-sm">
            <span class="text-xs font-bold text-slate-500 dark:text-slate-400">Total File Backup Tersedia</span>
            <div class="mt-2 text-2xl font-black text-slate-800 dark:text-white">{{ $totalBackups }}</div>
            <div class="mt-1 text-[11px] text-slate-400">Tersimpan di storage privat</div>
        </div>
        <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 p-5 shadow-sm">
            <span class="text-xs font-bold text-slate-500 dark:text-slate-400">Status Integritas Backup</span>
            <div class="mt-2 flex items-center gap-2">
                <span class="w-2.5 h-2.5 rounded-full bg-pink-500 animate-pulse"></span>
                <span class="text-sm font-bold text-[#fa2f79] dark:text-pink-400">Siap Dipulihkan</span>
            </div>
            <div class="mt-1 text-[11px] text-slate-400">Proteksi foreign key & dump transaksional</div>
        </div>
    </div>

    <!-- Tabel Daftar Backup -->
    <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 overflow-hidden shadow-sm">
        <div class="px-5 py-4 border-b border-slate-200 dark:border-slate-800 flex items-center justify-between">
            <h3 class="text-sm font-bold text-slate-800 dark:text-white flex items-center gap-2">
                <i data-lucide="archive" class="w-4 h-4 text-[#fa2f79]"></i>
                Arsip File Backup SQL
            </h3>
            <span class="text-xs text-slate-400 font-mono">storage/app/backups/</span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="border-b border-slate-200 dark:border-slate-800 bg-slate-50/80 dark:bg-slate-800/40 text-slate-500 dark:text-slate-400 font-bold uppercase text-[10px]">
                        <th class="py-3 px-4">Nama File Backup</th>
                        <th class="py-3 px-4">Ukuran</th>
                        <th class="py-3 px-4">Waktu Pembuatan</th>
                        <th class="py-3 px-4 text-center">Aksi Operasional</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800/60">
                    @forelse($backups as $b)
                    <tr class="hover:bg-slate-50/60 dark:hover:bg-slate-800/30 transition-colors">
                        <td class="py-3 px-4 font-mono font-bold text-slate-800 dark:text-white flex items-center gap-2">
                            <i data-lucide="file-code" class="w-4 h-4 text-[#fa2f79] shrink-0"></i>
                            <span class="truncate">{{ $b['filename'] }}</span>
                        </td>
                        <td class="py-3 px-4 font-mono text-slate-600 dark:text-slate-300">
                            {{ $b['size'] }}
                        </td>
                        <td class="py-3 px-4 text-slate-500 dark:text-slate-400">
                            {{ $b['created_at']->format('d/m/Y H:i:s') }}
                            <span class="text-[10px] text-slate-400 block font-mono">({{ $b['created_at']->diffForHumans() }})</span>
                        </td>
                        <td class="py-3 px-4 text-center">
                            @if(auth('admin')->user()->role === 'kepala_sekolah')
                            <div class="flex items-center justify-center gap-2">
                                <!-- Download -->
                                <a href="{{ route('admin.backup.download', $b['filename']) }}" class="px-2.5 py-1.5 rounded-lg bg-pink-50 dark:bg-pink-950/40 hover:bg-pink-100 text-[#fa2f79] dark:text-pink-300 font-bold text-[11px] transition-colors flex items-center gap-1 border border-pink-200/50 dark:border-pink-800/50">
                                    <i data-lucide="download" class="w-3.5 h-3.5"></i> Unduh
                                </a>
                                <!-- Restore Modal Trigger -->
                                <button type="button" onclick="bukaModalRestore('{{ $b['filename'] }}')" class="px-2.5 py-1.5 rounded-lg bg-amber-50 dark:bg-amber-950/40 hover:bg-amber-100 text-amber-700 dark:text-amber-300 font-bold text-[11px] transition-colors flex items-center gap-1">
                                    <i data-lucide="rotate-ccw" class="w-3.5 h-3.5"></i> Pulihkan
                                </button>
                                <!-- Hapus -->
                                <form action="{{ route('admin.backup.destroy', $b['filename']) }}" method="POST" onsubmit="return confirm('Hapus file backup {{ $b['filename'] }} secara permanen?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="p-1.5 rounded-lg text-slate-400 hover:text-rose-600 hover:bg-rose-50 dark:hover:bg-rose-950/40 transition-colors" title="Hapus Backup">
                                        <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
                                    </button>
                                </form>
                            </div>
                            @else
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-slate-100 dark:bg-slate-800 text-slate-500 text-[10px] font-semibold">
                                <i data-lucide="lock" class="w-3 h-3 text-slate-400"></i> Khusus Kepala Sekolah
                            </span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="py-8 text-center text-slate-400 italic">
                            Belum ada file backup database yang tersimpan. Klik tombol "Buat Backup Sekarang" di atas untuk membuat pencadangan perdana.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>

<!-- Modal Konfirmasi Restore Database -->
<div id="modalRestore" class="fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-sm hidden flex items-center justify-center p-4">
    <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 p-6 max-w-md w-full shadow-xl">
        <div class="flex items-center gap-3 pb-3 border-b border-slate-100 dark:border-slate-800">
            <div class="w-10 h-10 rounded-xl bg-amber-100 dark:bg-amber-950/60 text-amber-600 flex items-center justify-center shrink-0">
                <i data-lucide="alert-triangle" class="w-5 h-5"></i>
            </div>
            <div>
                <h3 class="text-sm font-bold text-slate-800 dark:text-white">Konfirmasi Pemulihan Basis Data</h3>
                <p class="text-[11px] text-slate-400">Tindakan berisiko tinggi (*High Impact*)</p>
            </div>
        </div>

        <form id="formRestore" method="POST" class="mt-4 space-y-4">
            @csrf
            <div class="p-3 rounded-xl bg-amber-50 dark:bg-amber-950/40 border border-amber-200 dark:border-amber-800 text-xs text-amber-800 dark:text-amber-300 leading-relaxed">
                Pemulihan akan menimpa data yang ada saat ini dengan data yang ada pada berkas cadangan:
                <div class="font-mono font-bold mt-1 text-slate-900 dark:text-white" id="restore_filename"></div>
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">
                    Ketik kata <span class="text-rose-600 font-mono font-black">PULIHKAN</span> untuk melanjutkan:
                </label>
                <input type="text" name="konfirmasi" required placeholder="PULIHKAN" class="w-full text-xs px-3 py-2 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-slate-800 dark:text-white uppercase font-mono font-bold focus:ring-2 focus:ring-amber-500">
            </div>

            <div class="pt-2 flex gap-2">
                <button type="submit" class="flex-1 py-2.5 rounded-xl bg-amber-600 hover:bg-amber-700 text-white font-bold text-xs transition-colors flex items-center justify-center gap-2">
                    <i data-lucide="rotate-ccw" class="w-4 h-4"></i> Eksekusi Pemulihan
                </button>
                <button type="button" onclick="document.getElementById('modalRestore').classList.add('hidden')" class="px-4 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-600 dark:text-slate-300 font-bold text-xs transition-colors">
                    Batal
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    function bukaModalRestore(filename) {
        document.getElementById('formRestore').action = "{{ url('admin/backup/restore') }}/" + filename;
        document.getElementById('restore_filename').innerText = filename;
        document.getElementById('modalRestore').classList.remove('hidden');
    }
</script>
@endsection

