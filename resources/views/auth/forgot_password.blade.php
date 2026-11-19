@extends('layouts.app')

@section('title', 'Daftar Akun Siswa Baru - PPDB SMK IB Khalifah Bangsa')

@section('content')
<div class="py-12 md:py-20 flex items-center justify-center px-4">
    <div class="w-full max-w-lg bg-white dark:bg-slate-800 rounded-3xl p-6 sm:p-8 border border-slate-200 dark:border-slate-700 shadow-xl transition-colors">
        <div class="text-center mb-8 space-y-2">
            <div class="w-12 h-12 rounded-2xl bg-emerald-600 text-white flex items-center justify-center mx-auto shadow-md shadow-emerald-600/30">
                <i data-lucide="user-plus" class="w-6 h-6"></i>
            </div>
            <h2 class="text-2xl font-extrabold text-slate-900 dark:text-white">Registrasi Akun Calon Siswa</h2>
            <p class="text-xs text-slate-500 dark:text-slate-400">Buat akun untuk memulai pengisian formulir pendaftaran online.</p>
        </div>

        <form action="{{ route('register.post') }}" method="POST" class="space-y-4">
            @csrf
            <div>
                <label for="nama" class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1.5">Nama Lengkap (Sesuai Ijazah SMP)</label>
                <div class="relative">
                    <i data-lucide="user" class="w-5 h-5 text-slate-400 absolute left-3.5 top-1/2 -translate-y-1/2"></i>
                    <input type="text" id="nama" name="nama" required value="{{ old('nama') }}" placeholder="Contoh: Muhammad Ihsan" class="w-full pl-11 pr-4 py-3 rounded-xl bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-900 dark:text-white text-sm focus:outline-none focus:border-emerald-500">
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label for="nisn" class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1.5">NISN (10 Digit Angka)</label>
                    <div class="relative">
                        <i data-lucide="hash" class="w-5 h-5 text-slate-400 absolute left-3.5 top-1/2 -translate-y-1/2"></i>
                        <input type="text" id="nisn" name="nisn" maxlength="10" required value="{{ old('nisn') }}" placeholder="Contoh: 0081234567" class="w-full pl-11 pr-4 py-3 rounded-xl bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-900 dark:text-white text-sm focus:outline-none focus:border-emerald-500">
                    </div>
                </div>

                <div>
                    <label for="username" class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1.5">Username (Untuk Login)</label>
                    <div class="relative">
                        <i data-lucide="at-sign" class="w-5 h-5 text-slate-400 absolute left-3.5 top-1/2 -translate-y-1/2"></i>
                        <input type="text" id="username" name="username" required value="{{ old('username') }}" placeholder="Contoh: ihsan2026" class="w-full pl-11 pr-4 py-3 rounded-xl bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-900 dark:text-white text-sm focus:outline-none focus:border-emerald-500">
                    </div>
                </div>
            </div>

            <div>
                <label for="email" class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1.5">Alamat Email Aktif</label>
                <div class="relative">
                    <i data-lucide="mail" class="w-5 h-5 text-slate-400 absolute left-3.5 top-1/2 -translate-y-1/2"></i>
                    <input type="email" id="email" name="email" required value="{{ old('email') }}" placeholder="Contoh: ihsan@gmail.com" class="w-full pl-11 pr-4 py-3 rounded-xl bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-900 dark:text-white text-sm focus:outline-none focus:border-emerald-500">
                </div>
                <p class="text-[10px] text-slate-400 mt-1">Digunakan untuk menerima notifikasi status kelulusan & reset password.</p>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label for="password" class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1.5">Kata Sandi (Min 6 Karakter)</label>
                    <div class="relative">
                        <i data-lucide="lock" class="w-5 h-5 text-slate-400 absolute left-3.5 top-1/2 -translate-y-1/2"></i>
                        <input type="password" id="password" name="password" required placeholder="••••••••" class="w-full pl-11 pr-4 py-3 rounded-xl bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-900 dark:text-white text-sm focus:outline-none focus:border-emerald-500">
                    </div>
                </div>

                <div>
                    <label for="password_confirmation" class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1.5">Konfirmasi Kata Sandi</label>
                    <div class="relative">
                        <i data-lucide="check" class="w-5 h-5 text-slate-400 absolute left-3.5 top-1/2 -translate-y-1/2"></i>
                        <input type="password" id="password_confirmation" name="password_confirmation" required placeholder="••••••••" class="w-full pl-11 pr-4 py-3 rounded-xl bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-900 dark:text-white text-sm focus:outline-none focus:border-emerald-500">
                    </div>
                </div>
            </div>

            <div class="flex items-start">
                <input type="checkbox" id="terms" required class="w-4 h-4 mt-0.5 text-emerald-600 border-slate-300 rounded focus:ring-emerald-500">
                <label for="terms" class="ml-2 text-xs text-slate-600 dark:text-slate-400 leading-normal">
                    Saya menyatakan bahwa data yang saya masukkan adalah benar dan bersedia mengikuti seluruh ketentuan seleksi PPDB SMK Islam Bina Khalifah Bangsa.
                </label>
            </div>

            <button type="submit" class="w-full py-3.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-sm shadow-md shadow-emerald-600/25 transition-all">
                Daftar Akun Sekarang
            </button>
        </form>

        <div class="mt-6 pt-6 border-t border-slate-200 dark:border-slate-700 text-center">
            <p class="text-xs text-slate-600 dark:text-slate-400">
                Sudah pernah mendaftar?
                <a href="{{ route('login') }}" class="font-bold text-emerald-600 dark:text-emerald-400 hover:underline">Masuk ke Akun Anda</a>
            </p>
        </div>
    </div>
</div>
@endsection

