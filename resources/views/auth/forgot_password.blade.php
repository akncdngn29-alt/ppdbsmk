@extends('layouts.app')

@section('title', 'Lupa Kata Sandi - PPDB SMK IB Khalifah Bangsa')

@section('content')
<div class="py-12 md:py-20 flex items-center justify-center px-4">
    <div class="w-full max-w-md bg-white dark:bg-slate-800 rounded-3xl p-6 sm:p-8 border border-slate-200 dark:border-slate-700 shadow-xl transition-colors">
        <div class="text-center mb-8 space-y-2">
            <div class="w-12 h-12 rounded-2xl bg-amber-500 text-white flex items-center justify-center mx-auto shadow-md">
                <i data-lucide="key-round" class="w-6 h-6"></i>
            </div>
            <h2 class="text-2xl font-extrabold text-slate-900 dark:text-white">Lupa Kata Sandi</h2>
            <p class="text-xs text-slate-500 dark:text-slate-400">Masukkan Email atau NISN Anda untuk menerima kode reset.</p>
        </div>

        <form action="{{ route('password.email') }}" method="POST" class="space-y-4">
            @csrf
            <div>
                <label for="identifier" class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1.5">Email Aktif / NISN Siswa</label>
                <div class="relative">
                    <i data-lucide="mail" class="w-5 h-5 text-slate-400 absolute left-3.5 top-1/2 -translate-y-1/2"></i>
                    <input type="text" id="identifier" name="identifier" required placeholder="siswa@smkib.com atau 0078912345" class="w-full pl-11 pr-4 py-3 rounded-xl bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-900 dark:text-white text-sm focus:outline-none focus:border-emerald-500">
                </div>
            </div>

            <button type="submit" class="w-full py-3.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-sm shadow-md transition-all">
                Kirim Kode Reset
            </button>
        </form>

        <div class="mt-6 pt-6 border-t border-slate-200 dark:border-slate-700 text-center">
            <a href="{{ route('login') }}" class="text-xs font-semibold text-emerald-600 dark:text-emerald-400 hover:underline">
                &larr; Ingat kata sandi? Masuk sekarang
            </a>
        </div>
    </div>
</div>
@endsection

