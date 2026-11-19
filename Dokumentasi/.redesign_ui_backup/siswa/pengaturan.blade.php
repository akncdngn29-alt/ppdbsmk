@extends('layouts.siswa')

@section('title', 'Pengaturan Akun')

@section('content')
<div class="space-y-6">

    <!-- Page Header -->
    <div>
        <a href="{{ route('siswa.dashboard') }}" class="text-xs font-bold text-[#f43f5e] hover:underline flex items-center gap-1 mb-1">
            <i data-lucide="arrow-left" class="w-3.5 h-3.5"></i> Kembali ke Dashboard
        </a>
        <h1 class="text-2xl font-black text-[#0B182F] dark:text-white">Pengaturan Akun Siswa</h1>
        <p class="text-xs text-slate-500 dark:text-slate-400">Kelola nomor WhatsApp untuk informasi seleksi dan perbarui kata sandi akun Anda.</p>
    </div>

    <!-- Form Card -->
    <div class="p-6 md:p-8 rounded-3xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-xs space-y-6">
        
        <!-- Identitas Akun (Read-only) -->
        <div>
            <h3 class="text-xs font-bold uppercase tracking-wider text-[#0B182F] dark:text-white mb-3 pb-2 border-b border-slate-100 dark:border-slate-800 flex items-center gap-2">
                <i data-lucide="shield" class="w-4 h-4 text-[#f43f5e]"></i> Identitas Pendaftar
            </h3>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs">
                <div class="p-3.5 rounded-2xl bg-slate-50 dark:bg-slate-800/60 border border-slate-200 dark:border-slate-700">
                    <span class="text-slate-400 block text-[10px] uppercase font-bold">Nama Lengkap</span>
                    <strong class="text-[#0B182F] dark:text-white font-bold">{{ $user->nama }}</strong>
                </div>
                <div class="p-3.5 rounded-2xl bg-slate-50 dark:bg-slate-800/60 border border-slate-200 dark:border-slate-700">
                    <span class="text-slate-400 block text-[10px] uppercase font-bold">NISN (10 Digit)</span>
                    <strong class="font-mono text-[#0B182F] dark:text-white">{{ $user->nisn }}</strong>
                </div>
                <div class="p-3.5 rounded-2xl bg-slate-50 dark:bg-slate-800/60 border border-slate-200 dark:border-slate-700">
                    <span class="text-slate-400 block text-[10px] uppercase font-bold">Username</span>
                    <strong class="font-mono text-[#0B182F] dark:text-white">{{ $user->username }}</strong>
                </div>
                <div class="p-3.5 rounded-2xl bg-slate-50 dark:bg-slate-800/60 border border-slate-200 dark:border-slate-700">
                    <span class="text-slate-400 block text-[10px] uppercase font-bold">Alamat Email</span>
                    <strong class="text-[#0B182F] dark:text-white">{{ $user->email }}</strong>
                </div>
            </div>
        </div>

        <!-- Form Ubah No. HP & Kata Sandi -->
        <form action="{{ route('siswa.pengaturan.simpan') }}" method="POST" class="space-y-4">
            @csrf

            <div>
                <h3 class="text-xs font-bold uppercase tracking-wider text-[#0B182F] dark:text-white mb-3 pb-2 border-b border-slate-100 dark:border-slate-800 flex items-center gap-2">
                    <i data-lucide="phone" class="w-4 h-4 text-[#f43f5e]"></i> Pembaruan Kontak WhatsApp
                </h3>
                <div>
                    <label for="hp_siswa" class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">
                        Nomor WhatsApp Siswa Aktif
                    </label>
                    <input type="text" id="hp_siswa" name="hp_siswa" 
                           value="{{ old('hp_siswa', $pendaftar ? $pendaftar->hp_siswa : '') }}" 
                           placeholder="Contoh: 082188990011"
                           class="w-full px-4 py-2.5 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-white text-xs font-mono focus:outline-none focus:border-[#f43f5e] focus:ring-1 focus:ring-[#f43f5e]">
                    <span class="text-[10px] text-slate-400 mt-1 block">Nomor ini digunakan panitia untuk mengirimkan info pengumuman & jadwal tes seleksi.</span>
                </div>
            </div>

            <div class="pt-2">
                <h3 class="text-xs font-bold uppercase tracking-wider text-[#0B182F] dark:text-white mb-3 pb-2 border-b border-slate-100 dark:border-slate-800 flex items-center gap-2">
                    <i data-lucide="lock" class="w-4 h-4 text-[#f43f5e]"></i> Perbarui Kata Sandi (Opsional)
                </h3>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label for="password" class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Kata Sandi Baru</label>
                        <input type="password" id="password" name="password" placeholder="Kosongkan jika tidak diubah"
                               class="w-full px-4 py-2.5 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-white text-xs focus:outline-none focus:border-[#f43f5e] focus:ring-1 focus:ring-[#f43f5e]">
                    </div>
                    <div>
                        <label for="password_confirmation" class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Ulangi Kata Sandi Baru</label>
                        <input type="password" id="password_confirmation" name="password_confirmation" placeholder="Ulangi kata sandi baru"
                               class="w-full px-4 py-2.5 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-white text-xs focus:outline-none focus:border-[#f43f5e] focus:ring-1 focus:ring-[#f43f5e]">
                    </div>
                </div>
            </div>

            <div class="pt-4 border-t border-slate-100 dark:border-slate-800 flex justify-end">
                <button type="submit" class="px-6 py-2.5 rounded-full bg-[#f43f5e] hover:bg-[#e11d48] text-white font-bold text-xs flex items-center gap-2 shadow-md transition-all">
                    <i data-lucide="save" class="w-4 h-4"></i> Simpan Perubahan Akun
                </button>
            </div>
        </form>

    </div>
</div>
@endsection
