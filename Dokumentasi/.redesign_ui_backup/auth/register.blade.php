@extends('layouts.app')

@section('title', 'Daftar Akun Siswa Baru - PPDB SMK IB Khalifah Bangsa')

@section('content')
<div class="py-10 md:py-16 flex items-center justify-center px-4 bg-slate-50 min-h-[calc(100vh-140px)]">
    <div class="w-full max-w-lg bg-white rounded-3xl p-6 sm:p-8 border border-slate-200 shadow-xl transition-colors">
        
        <!-- Header -->
        <div class="text-center mb-6 space-y-2">
            <img src="{{ asset('images/logo.png') }}" alt="Logo SMK Islam Bina Khalifah Bangsa" class="w-14 h-14 mx-auto object-contain drop-shadow">
            <h2 class="text-2xl font-black text-[#0B182F] tracking-tight">Registrasi Akun Calon Siswa</h2>
            <p class="text-xs text-slate-500">Buat akun untuk memulai pengisian formulir pendaftaran PPDB online.</p>
        </div>

        <form action="{{ route('register.post') }}" method="POST" class="space-y-4">
            @csrf
            <div>
                <label for="nama" class="block text-xs font-bold text-slate-700 mb-1.5">Nama Lengkap (Sesuai Ijazah SMP)</label>
                <div class="relative">
                    <i data-lucide="user" class="w-4 h-4 text-slate-400 absolute left-3.5 top-1/2 -translate-y-1/2"></i>
                    <input type="text" id="nama" name="nama" required value="{{ old('nama') }}" placeholder="Contoh: Muhammad Ihsan" class="w-full pl-10 pr-4 py-3 rounded-2xl bg-slate-50 border border-slate-200 text-slate-900 text-xs sm:text-sm focus:outline-none focus:border-[#f43f5e] focus:bg-white transition-all font-medium">
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label for="nisn" class="block text-xs font-bold text-slate-700 mb-1.5">NISN (10 Digit Angka)</label>
                    <div class="relative">
                        <i data-lucide="hash" class="w-4 h-4 text-slate-400 absolute left-3.5 top-1/2 -translate-y-1/2"></i>
                        <input type="text" id="nisn" name="nisn" maxlength="10" required value="{{ old('nisn') }}" placeholder="Contoh: 0081234567" class="w-full pl-10 pr-4 py-3 rounded-2xl bg-slate-50 border border-slate-200 text-slate-900 text-xs sm:text-sm focus:outline-none focus:border-[#f43f5e] focus:bg-white transition-all font-mono">
                    </div>
                </div>

                <div>
                    <label for="username" class="block text-xs font-bold text-slate-700 mb-1.5">Username (Untuk Login)</label>
                    <div class="relative">
                        <i data-lucide="at-sign" class="w-4 h-4 text-slate-400 absolute left-3.5 top-1/2 -translate-y-1/2"></i>
                        <input type="text" id="username" name="username" required value="{{ old('username') }}" placeholder="Contoh: ihsan2026" class="w-full pl-10 pr-4 py-3 rounded-2xl bg-slate-50 border border-slate-200 text-slate-900 text-xs sm:text-sm focus:outline-none focus:border-[#f43f5e] focus:bg-white transition-all font-medium">
                    </div>
                </div>
            </div>

            <div>
                <label for="email" class="block text-xs font-bold text-slate-700 mb-1.5">Alamat Email Aktif</label>
                <div class="relative">
                    <i data-lucide="mail" class="w-4 h-4 text-slate-400 absolute left-3.5 top-1/2 -translate-y-1/2"></i>
                    <input type="email" id="email" name="email" required value="{{ old('email') }}" placeholder="Contoh: ihsan@gmail.com" class="w-full pl-10 pr-4 py-3 rounded-2xl bg-slate-50 border border-slate-200 text-slate-900 text-xs sm:text-sm focus:outline-none focus:border-[#f43f5e] focus:bg-white transition-all font-medium">
                </div>
                <p class="text-[10px] text-slate-500 mt-1">Digunakan untuk menerima notifikasi status kelulusan & reset password.</p>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label for="password" class="block text-xs font-bold text-slate-700 mb-1.5">Kata Sandi (Min 6 Karakter)</label>
                    <div class="relative">
                        <i data-lucide="lock" class="w-4 h-4 text-slate-400 absolute left-3.5 top-1/2 -translate-y-1/2"></i>
                        <input type="password" id="password" name="password" required placeholder="••••••••" class="w-full pl-10 pr-4 py-3 rounded-2xl bg-slate-50 border border-slate-200 text-slate-900 text-xs sm:text-sm focus:outline-none focus:border-[#f43f5e] focus:bg-white transition-all">
                    </div>
                </div>

                <div>
                    <label for="password_confirmation" class="block text-xs font-bold text-slate-700 mb-1.5">Konfirmasi Kata Sandi</label>
                    <div class="relative">
                        <i data-lucide="check" class="w-4 h-4 text-slate-400 absolute left-3.5 top-1/2 -translate-y-1/2"></i>
                        <input type="password" id="password_confirmation" name="password_confirmation" required placeholder="••••••••" class="w-full pl-10 pr-4 py-3 rounded-2xl bg-slate-50 border border-slate-200 text-slate-900 text-xs sm:text-sm focus:outline-none focus:border-[#f43f5e] focus:bg-white transition-all">
                    </div>
                </div>
            </div>

            <div class="flex items-start">
                <input type="checkbox" id="terms" required class="w-4 h-4 mt-0.5 text-[#f43f5e] border-slate-300 rounded focus:ring-[#f43f5e]">
                <label for="terms" class="ml-2 text-xs text-slate-600 leading-normal cursor-pointer">
                    Saya menyatakan bahwa data yang saya masukkan adalah benar dan bersedia mengikuti seluruh ketentuan seleksi PPDB SMK Islam Bina Khalifah Bangsa.
                </label>
            </div>

            <button type="submit" class="w-full py-3.5 rounded-full bg-[#f43f5e] hover:bg-[#e11d48] text-white font-bold text-sm shadow-md hover:shadow-lg transition-all flex items-center justify-center gap-2">
                <i data-lucide="user-plus" class="w-4 h-4"></i>
                <span>Daftar Akun Sekarang</span>
            </button>
        </form>

        <div class="mt-6 pt-6 border-t border-slate-200 text-center">
            <p class="text-xs text-slate-600">
                Sudah pernah mendaftar?
                <a href="{{ route('login') }}" class="font-bold text-[#f43f5e] hover:underline">Masuk ke Akun Anda</a>
            </p>
        </div>
    </div>
</div>
@endsection
