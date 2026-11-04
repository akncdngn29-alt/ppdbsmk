@extends('layouts.siswa')

@section('title', 'Pengaturan Akun')

@section('content')
<div class="max-w-2xl mx-auto space-y-6">

        <!-- Page Header -->
        <div>
            <a href="{{ route('siswa.dashboard') }}" class="text-xs font-semibold text-emerald-600 dark:text-emerald-400 hover:underline flex items-center gap-1 mb-1">
                <i data-lucide="arrow-left" class="w-3.5 h-3.5"></i> Kembali ke Dashboard
            </a>
            <h1 class="text-2xl font-extrabold text-slate-900 dark:text-white">Pengaturan Akun Siswa</h1>
            <p class="text-xs text-slate-500 dark:text-slate-400">Kelola nomor WhatsApp untuk informasi seleksi dan perbarui kata sandi akun Anda.</p>
        </div>

        <!-- Form Card -->
        <div class="p-6 md:p-8 rounded-3xl bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 shadow-sm space-y-6">
            
            <!-- Identitas Akun (Read-only) -->
            <div>
                <h3 class="text-xs font-bold uppercase tracking-wider text-emerald-600 dark:text-emerald-400 mb-3 pb-2 border-b border-slate-100 dark:border-slate-700 flex items-center gap-2">
                    <i data-lucide="shield" class="w-4 h-4"></i> Identitas Pendaftar
                </h3>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs">
                    <div class="p-3 rounded-2xl bg-slate-50 dark:bg-slate-900/60 border border-slate-200 dark:border-slate-700">
                        <span class="text-slate-400 block text-[10px] uppercase">Nama Lengkap</span>
                        <strong class="text-slate-900 dark:text-white font-semibold">{{ $user->nama }}</strong>
                    </div>
                    <div class="p-3 rounded-2xl bg-slate-50 dark:bg-slate-900/60 border border-slate-200 dark:border-slate-700">
                        <span class="text-slate-400 block text-[10px] uppercase">NISN (10 Digit)</span>
                        <strong class="font-mono text-slate-900 dark:text-white">{{ $user->nisn }}</strong>
                    </div>
                    <div class="p-3 rounded-2xl bg-slate-50 dark:bg-slate-900/60 border border-slate-200 dark:border-slate-700">
                        <span class="text-slate-400 block text-[10px] uppercase">Username</span>
                        <strong class="font-mono text-slate-900 dark:text-white">{{ $user->username }}</strong>
                    </div>
                    <div class="p-3 rounded-2xl bg-slate-50 dark:bg-slate-900/60 border border-slate-200 dark:border-slate-700">
                        <span class="text-slate-400 block text-[10px] uppercase">Alamat Email</span>
                        <strong class="text-slate-900 dark:text-white">{{ $user->email }}</strong>
                    </div>
                </div>
            </div>

            <!-- Form Ubah No. HP & Kata Sandi -->
            <form action="{{ route('siswa.pengaturan.simpan') }}" method="POST" class="space-y-4">
                @csrf

                <div>
                    <h3 class="text-xs font-bold uppercase tracking-wider text-emerald-600 dark:text-emerald-400 mb-3 pb-2 border-b border-slate-100 dark:border-slate-700 flex items-center gap-2">
                        <i data-lucide="phone" class="w-4 h-4"></i> Pembaruan Kontak WhatsApp
                    </h3>
                    <div>
                        <label for="hp_siswa" class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                            Nomor WhatsApp Siswa Aktif
                        </label>
                        <input type="text" id="hp_siswa" name="hp_siswa" 
                               value="{{ old('hp_siswa', $pendaftar ? $pendaftar->hp_siswa : '') }}" 
                               placeholder="Contoh: 082188990011"
                               class="w-full px-4 py-2.5 rounded-xl border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 text-slate-800 dark:text-white text-xs font-mono focus:outline-none focus:ring-2 focus:ring-emerald-500">
                        <span class="text-[10px] text-slate-400 mt-1 block">Nomor ini digunakan panitia untuk mengirimkan info pengumuman & jadwal tes.</span>
                    </div>
                </div>

                <div class="pt-2">
                    <h3 class="text-xs font-bold uppercase tracking-wider text-emerald-600 dark:text-emerald-400 mb-3 pb-2 border-b border-slate-100 dark:border-slate-700 flex items-center gap-2">
                        <i data-lucide="lock" class="w-4 h-4"></i> Perbarui Kata Sandi (Opsional)
                    </h3>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label for="password" class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Kata Sandi Baru</label>
                            <input type="password" id="password" name="password" placeholder="Kosongkan jika tidak diubah"
                                   class="w-full px-4 py-2.5 rounded-xl border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 text-slate-800 dark:text-white text-xs focus:outline-none focus:ring-2 focus:ring-emerald-500">
                        </div>
                        <div>
                            <label for="password_confirmation" class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Ulangi Kata Sandi Baru</label>
                            <input type="password" id="password_confirmation" name="password_confirmation" placeholder="Ulangi kata sandi baru"
                                   class="w-full px-4 py-2.5 rounded-xl border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 text-slate-800 dark:text-white text-xs focus:outline-none focus:ring-2 focus:ring-emerald-500">
                        </div>
                    </div>
                </div>

                <div class="pt-4 border-t border-slate-100 dark:border-slate-700 flex justify-end">
                    <button type="submit" class="px-6 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs flex items-center gap-2 shadow-md shadow-emerald-600/30 transition-all">
                        <i data-lucide="save" class="w-4 h-4"></i> Simpan Perubahan Akun
                    </button>
                </div>
            </form>

        </div>
    </div>
@endsection

