@extends('layouts.app')

@section('title', 'Lupa Kata Sandi - PPDB SMK IB Khalifah Bangsa')

@section('content')
<div class="relative min-h-[calc(100vh-140px)] flex items-center justify-center px-4 py-8 md:py-14 overflow-hidden">
    <!-- Blurred Beranda Background Layer -->
    <div class="absolute inset-0 bg-cover bg-center bg-no-repeat scale-105 filter blur-md transform"
         style="background-image: url('{{ asset('images/hero-siswa-jurusan.jpg') }}');">
    </div>

    <!-- Overlay Gradient Dark (Menyatu dengan tema Navy Beranda & Menjaga Kontras Teks) -->
    <div class="absolute inset-0 bg-gradient-to-b from-[#060c1d]/90 via-[#0b1329]/80 to-[#060c1d]/95 backdrop-blur-sm"></div>

    <!-- Ambient Glow Aksen Pink -->
    <div class="absolute -top-24 left-1/2 -translate-x-1/2 w-96 h-96 bg-[#fa2f79]/15 rounded-full blur-3xl pointer-events-none"></div>

    <!-- Form Card Container Glassmorphism -->
    <div class="relative z-10 w-full max-w-md bg-[#161f30]/90 backdrop-blur-xl rounded-3xl p-6 sm:p-8 border border-white/10 shadow-2xl text-slate-200">
        
        <!-- Header -->
        <div class="text-center mb-6 space-y-1.5">
            <div class="flex justify-center mb-3">
                <img src="{{ asset('images/logo-sekolah.png') }}" alt="Logo SMK IB Khalifah Bangsa" class="w-16 h-16 object-contain drop-shadow-md">
            </div>
            <h2 class="text-2xl font-black text-white tracking-tight">Lupa Kata Sandi?</h2>
            <p class="text-xs text-slate-300">Masukkan NISN atau Email terdaftar untuk memulihkan akses akun Anda</p>
        </div>

        @if(session('success'))
        <div class="mb-4 px-4 py-3 rounded-xl bg-emerald-950/70 border border-emerald-500/40 text-emerald-300 text-xs flex items-start gap-2.5">
            <i data-lucide="check-circle-2" class="w-4 h-4 text-emerald-400 shrink-0 mt-0.5"></i>
            <span>{{ session('success') }}</span>
        </div>
        @endif

        @if($errors->any())
        <div class="mb-4 px-4 py-2.5 rounded-xl bg-rose-950/70 border border-rose-500/40 text-rose-300 text-xs space-y-1">
            @foreach($errors->all() as $err)
            <div class="flex items-center gap-2">
                <i data-lucide="alert-circle" class="w-4 h-4 text-rose-400 shrink-0"></i>
                <span>{{ $err }}</span>
            </div>
            @endforeach
        </div>
        @endif

        <!-- Forgot Password Form -->
        <form action="{{ route('password.email') }}" method="POST" class="space-y-4">
            @csrf
            <div>
                <label for="identifier" class="block text-xs font-bold text-slate-300 mb-1.5">NISN / Alamat Email Siswa</label>
                <div class="relative">
                    <i data-lucide="mail" class="w-4 h-4 text-slate-400 absolute left-3.5 top-1/2 -translate-y-1/2"></i>
                    <input type="text" id="identifier" name="identifier" required value="{{ old('identifier') }}" placeholder="Contoh: 0081234567 atau siswa@email.com"
                           class="w-full pl-10 pr-4 py-2.5 rounded-xl bg-[#0d1424] border border-slate-700/80 text-white placeholder-slate-500 text-xs focus:outline-none focus:border-[#ff2e79] focus:ring-1 focus:ring-[#ff2e79] font-medium">
                </div>
            </div>

            <button type="submit" class="w-full py-3 rounded-xl bg-[#ff2e79] hover:bg-[#e11d48] text-white font-extrabold text-xs uppercase tracking-wider shadow-lg shadow-pink-600/30 transition-all active:scale-[0.99] flex items-center justify-center gap-2">
                <i data-lucide="send" class="w-4 h-4"></i>
                <span>KIRIM INSTRUKSI PEMULIHAN</span>
            </button>
        </form>

        <div class="mt-6 pt-4 border-t border-slate-800 text-center">
            <a href="{{ route('login') }}" class="text-xs text-slate-400 hover:text-emerald-400 transition-colors flex items-center justify-center gap-1.5">
                <i data-lucide="arrow-left" class="w-3.5 h-3.5"></i>
                <span>Kembali ke Halaman Masuk</span>
            </a>
        </div>

    </div>
</div>
@endsection
