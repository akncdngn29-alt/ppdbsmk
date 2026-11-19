@extends('layouts.app')

@section('title', 'Daftar Akun Siswa Baru - PPDB SMK IB Khalifah Bangsa')

@section('content')
<div class="relative min-h-[calc(100vh-140px)] flex items-center justify-center px-4 py-10 md:py-16 overflow-hidden">
    <!-- Blurred Beranda Background Layer -->
    <div class="absolute inset-0 bg-cover bg-center bg-no-repeat scale-105 filter blur-md transform"
         style="background-image: url('{{ asset('images/hero-siswa-jurusan.jpg') }}');">
    </div>

    <!-- Overlay Gradient Dark (Menyatu dengan tema Navy Beranda & Menjaga Kontras Teks) -->
    <div class="absolute inset-0 bg-gradient-to-b from-[#060c1d]/90 via-[#0b1329]/80 to-[#060c1d]/95 backdrop-blur-sm"></div>

    <!-- Ambient Glow Aksen Pink -->
    <div class="absolute -top-24 left-1/2 -translate-x-1/2 w-96 h-96 bg-[#fa2f79]/15 rounded-full blur-3xl pointer-events-none"></div>

    <!-- Register Card Container Glassmorphism -->
    <div class="relative z-10 w-full max-w-lg bg-[#161f30]/90 backdrop-blur-xl rounded-3xl p-6 sm:p-8 border border-white/10 shadow-2xl text-slate-200">
        <div class="text-center mb-6 space-y-2">
            <div class="flex justify-center mb-3">
                <img src="{{ asset('images/logo-sekolah.png') }}" alt="Logo SMK IB Khalifah Bangsa" class="w-16 h-16 object-contain drop-shadow-md">
            </div>
            <h2 class="text-2xl font-black text-white tracking-tight">Registrasi Calon Siswa</h2>
            <p class="text-xs text-slate-300">Buat akun untuk memulai pengisian formulir pendaftaran online.</p>
        </div>

        <form action="{{ route('register.post') }}" method="POST" class="space-y-4" autocomplete="off">
            @csrf
            <div>
                <label for="nama" class="block text-xs font-bold text-slate-300 mb-1.5">Nama Lengkap (Sesuai Ijazah SMP)</label>
                <div class="relative">
                    <i data-lucide="user" class="w-4 h-4 text-slate-400 absolute left-3.5 top-1/2 -translate-y-1/2"></i>
                    <input type="text" id="nama" name="nama" required value="{{ old('nama') }}" placeholder="Contoh: Muhammad Ihsan"
                           autocomplete="off"
                           class="w-full pl-10 pr-4 py-2.5 rounded-xl bg-[#0d1424] border border-slate-700/80 text-white placeholder-slate-500 text-xs focus:outline-none focus:border-[#ff2e79] focus:ring-1 focus:ring-[#ff2e79] font-medium">
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                <div>
                    <label for="nisn" class="block text-xs font-bold text-slate-300 mb-1.5">NISN (10 Digit Angka)</label>
                    <div class="relative">
                        <i data-lucide="hash" class="w-4 h-4 text-slate-400 absolute left-3.5 top-1/2 -translate-y-1/2"></i>
                        <input type="text" id="nisn" name="nisn" maxlength="10" required value="{{ old('nisn') }}" placeholder="Contoh: 0081234567"
                               autocomplete="off"
                               class="w-full pl-10 pr-4 py-2.5 rounded-xl bg-[#0d1424] border border-slate-700/80 text-white placeholder-slate-500 text-xs focus:outline-none focus:border-[#ff2e79] focus:ring-1 focus:ring-[#ff2e79] font-medium">
                    </div>
                </div>

                <div>
                    <label for="username" class="block text-xs font-bold text-slate-300 mb-1.5">Username (Untuk Login)</label>
                    <div class="relative">
                        <i data-lucide="at-sign" class="w-4 h-4 text-slate-400 absolute left-3.5 top-1/2 -translate-y-1/2"></i>
                        <input type="text" id="username" name="username" required value="{{ old('username') }}" placeholder="Contoh: ihsan2026"
                               autocomplete="off"
                               class="w-full pl-10 pr-4 py-2.5 rounded-xl bg-[#0d1424] border border-slate-700/80 text-white placeholder-slate-500 text-xs focus:outline-none focus:border-[#ff2e79] focus:ring-1 focus:ring-[#ff2e79] font-medium">
                    </div>
                </div>
            </div>

            <div>
                <label for="email" class="block text-xs font-bold text-slate-300 mb-1.5">Alamat Email Aktif</label>
                <div class="relative">
                    <i data-lucide="mail" class="w-4 h-4 text-slate-400 absolute left-3.5 top-1/2 -translate-y-1/2"></i>
                    <input type="email" id="email" name="email" required value="{{ old('email') }}" placeholder="Contoh: ihsan@gmail.com"
                           autocomplete="off"
                           class="w-full pl-10 pr-4 py-2.5 rounded-xl bg-[#0d1424] border border-slate-700/80 text-white placeholder-slate-500 text-xs focus:outline-none focus:border-[#ff2e79] focus:ring-1 focus:ring-[#ff2e79] font-medium">
                </div>
                <p class="text-[10px] text-slate-400 mt-1">Digunakan untuk notifikasi status kelulusan & reset password.</p>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                <div>
                    <label for="password" class="block text-xs font-bold text-slate-300 mb-1.5">Kata Sandi (Min 6 Karakter)</label>
                    <div class="relative">
                        <i data-lucide="lock" class="w-4 h-4 text-slate-400 absolute left-3.5 top-1/2 -translate-y-1/2"></i>
                        <input type="password" id="password" name="password" required placeholder="••••••••"
                               autocomplete="new-password"
                               class="w-full pl-10 pr-4 py-2.5 rounded-xl bg-[#0d1424] border border-slate-700/80 text-white placeholder-slate-500 text-xs focus:outline-none focus:border-[#ff2e79] focus:ring-1 focus:ring-[#ff2e79] font-medium">
                    </div>
                </div>

                <div>
                    <label for="password_confirmation" class="block text-xs font-bold text-slate-300 mb-1.5">Konfirmasi Sandi</label>
                    <div class="relative">
                        <i data-lucide="check" class="w-4 h-4 text-slate-400 absolute left-3.5 top-1/2 -translate-y-1/2"></i>
                        <input type="password" id="password_confirmation" name="password_confirmation" required placeholder="••••••••"
                               autocomplete="new-password"
                               class="w-full pl-10 pr-4 py-2.5 rounded-xl bg-[#0d1424] border border-slate-700/80 text-white placeholder-slate-500 text-xs focus:outline-none focus:border-[#ff2e79] focus:ring-1 focus:ring-[#ff2e79] font-medium">
                    </div>
                </div>
            </div>

            <div class="flex items-start">
                <input type="checkbox" id="terms" required class="w-4 h-4 mt-0.5 rounded bg-[#0d1424] border-slate-700 text-[#ff2e79] focus:ring-[#ff2e79]">
                <label for="terms" class="ml-2 text-xs text-slate-300 leading-normal">
                    Saya menyatakan bahwa data yang saya masukkan adalah benar dan bersedia mengikuti seluruh ketentuan seleksi PPDB SMK Islam Bina Khalifah Bangsa.
                </label>
            </div>

            <button type="submit" class="w-full py-3 rounded-xl bg-[#ff2e79] hover:bg-[#e11d48] text-white font-extrabold text-xs uppercase tracking-wider shadow-lg shadow-pink-600/30 transition-all active:scale-[0.99]">
                Daftar Akun Sekarang
            </button>
        </form>

        <div class="mt-6 pt-4 border-t border-slate-800/80 text-center">
            <p class="text-xs text-slate-400">
                Sudah pernah mendaftar?
                <a href="{{ route('login') }}" class="font-bold text-[#ff2e79] hover:underline">Masuk ke Akun Anda</a>
            </p>
        </div>
    </div>
</div>
@endsection

