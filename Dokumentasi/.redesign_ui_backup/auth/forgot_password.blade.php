@extends('layouts.app')

@section('title', 'Lupa Kata Sandi - PPDB SMK IB Khalifah Bangsa')

@section('content')
<div class="py-10 md:py-16 flex items-center justify-center px-4 bg-slate-50 min-h-[calc(100vh-140px)]">
    <div class="w-full max-w-md bg-white rounded-3xl p-6 sm:p-8 border border-slate-200 shadow-xl transition-colors">
        
        <div class="text-center mb-6 space-y-2">
            <img src="{{ asset('images/logo.png') }}" alt="Logo SMK Islam Bina Khalifah Bangsa" class="w-14 h-14 mx-auto object-contain drop-shadow">
            <h2 class="text-2xl font-black text-[#0B182F] tracking-tight">Lupa Kata Sandi</h2>
            <p class="text-xs text-slate-500">Masukkan Email atau NISN Anda untuk menerima tautan/kode reset.</p>
        </div>

        <form action="{{ route('password.email') }}" method="POST" class="space-y-4">
            @csrf
            <div>
                <label for="identifier" class="block text-xs font-bold text-slate-700 mb-1.5">Email Aktif / NISN Siswa</label>
                <div class="relative">
                    <i data-lucide="mail" class="w-4 h-4 text-slate-400 absolute left-3.5 top-1/2 -translate-y-1/2"></i>
                    <input type="text" id="identifier" name="identifier" required placeholder="siswa@smkib.com atau 0078912345" class="w-full pl-10 pr-4 py-3 rounded-2xl bg-slate-50 border border-slate-200 text-slate-900 text-xs sm:text-sm focus:outline-none focus:border-[#f43f5e] focus:bg-white transition-all">
                </div>
            </div>

            <button type="submit" class="w-full py-3.5 rounded-full bg-[#f43f5e] hover:bg-[#e11d48] text-white font-bold text-sm shadow-md transition-all flex items-center justify-center gap-2">
                <i data-lucide="key-round" class="w-4 h-4"></i>
                <span>Kirim Kode Reset</span>
            </button>
        </form>

        <div class="mt-6 pt-6 border-t border-slate-200 text-center">
            <a href="{{ route('login') }}" class="text-xs font-bold text-[#f43f5e] hover:underline flex items-center justify-center gap-1.5">
                <i data-lucide="arrow-left" class="w-3.5 h-3.5"></i>
                <span>Ingat kata sandi? Masuk sekarang</span>
            </a>
        </div>
    </div>
</div>
@endsection
