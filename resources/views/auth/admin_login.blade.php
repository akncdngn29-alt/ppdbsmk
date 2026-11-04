@extends('layouts.app')

@section('title', 'Portal Masuk Administrator & Kepala Sekolah')

@section('content')
<div class="py-12 md:py-20 flex items-center justify-center px-4 bg-slate-100 dark:bg-slate-950 min-h-[calc(100vh-160px)]">
    <div class="w-full max-w-md bg-white dark:bg-slate-900 rounded-3xl p-6 sm:p-8 border border-slate-200 dark:border-slate-800 shadow-2xl transition-colors">
        <div class="text-center mb-8 space-y-2">
            <div class="w-12 h-12 rounded-2xl bg-slate-900 dark:bg-emerald-600 text-white flex items-center justify-center mx-auto shadow-md">
                <i data-lucide="shield-check" class="w-6 h-6"></i>
            </div>
            <h2 class="text-2xl font-extrabold text-slate-900 dark:text-white">Portal Staf & Pimpinan</h2>
            <p class="text-xs text-slate-500 dark:text-slate-400">Khusus Petugas Tata Usaha (TU) dan Kepala Sekolah.</p>
        </div>

        <form action="{{ route('admin.login.post') }}" method="POST" class="space-y-4">
            @csrf
            <div>
                <label for="login" class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1.5">Email / Username Administrator</label>
                <div class="relative">
                    <i data-lucide="mail" class="w-5 h-5 text-slate-400 absolute left-3.5 top-1/2 -translate-y-1/2"></i>
                    <input type="text" id="login" name="login" required value="{{ old('login') }}" placeholder="admin@smkib.com" class="w-full pl-11 pr-4 py-3 rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-900 dark:text-white text-sm focus:outline-none focus:border-emerald-500">
                </div>
            </div>

            <div>
                <label for="password" class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1.5">Kata Sandi</label>
                <div class="relative">
                    <i data-lucide="lock" class="w-5 h-5 text-slate-400 absolute left-3.5 top-1/2 -translate-y-1/2"></i>
                    <input type="password" id="password" name="password" required placeholder="••••••••" class="w-full pl-11 pr-4 py-3 rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-900 dark:text-white text-sm focus:outline-none focus:border-emerald-500">
                </div>
            </div>

            <div class="flex items-center">
                <input type="checkbox" id="remember" name="remember" class="w-4 h-4 text-emerald-600 border-slate-300 rounded focus:ring-emerald-500">
                <label for="remember" class="ml-2 text-xs text-slate-600 dark:text-slate-400">Ingat sesi saya</label>
            </div>

            <button type="submit" class="w-full py-3.5 rounded-xl bg-slate-900 hover:bg-slate-800 dark:bg-emerald-600 dark:hover:bg-emerald-700 text-white font-bold text-sm shadow-md transition-all">
                Masuk ke Panel Khusus
            </button>
        </form>

        <!-- Quick Credentials Helper -->
        <div class="mt-6 p-4 rounded-2xl bg-slate-50 dark:bg-slate-800/60 border border-slate-200 dark:border-slate-700 text-xs space-y-2">
            <span class="font-bold text-slate-700 dark:text-slate-300 block">Kredensial Akses Pengujian:</span>
            <div class="flex items-center justify-between">
                <span>Petugas TU: <code class="font-mono bg-white dark:bg-slate-900 px-1 py-0.5 rounded">admin@smkib.com</code> / <code class="font-mono">admin123</code></span>
                <button type="button" onclick="fillAdmin('admin@smkib.com', 'admin123')" class="text-[10px] text-emerald-600 dark:text-emerald-400 font-bold underline">Isi TU</button>
            </div>
            <div class="flex items-center justify-between">
                <span>Kepala Sekolah: <code class="font-mono bg-white dark:bg-slate-900 px-1 py-0.5 rounded">kepsek@smkib.com</code> / <code class="font-mono">kepsek123</code></span>
                <button type="button" onclick="fillAdmin('kepsek@smkib.com', 'kepsek123')" class="text-[10px] text-emerald-600 dark:text-emerald-400 font-bold underline">Isi Kepsek</button>
            </div>
        </div>

        <div class="mt-6 pt-6 border-t border-slate-200 dark:border-slate-700 text-center">
            <a href="{{ route('login') }}" class="text-xs text-emerald-600 dark:text-emerald-400 hover:underline">
                &larr; Kembali ke Portal Calon Siswa
            </a>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    function fillAdmin(user, pass) {
        document.getElementById('login').value = user;
        document.getElementById('password').value = pass;
    }
</script>
@endpush

