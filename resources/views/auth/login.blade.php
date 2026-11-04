@extends('layouts.app')

@section('title', 'Masuk Akun Calon Siswa - PPDB SMK IB Khalifah Bangsa')

@section('content')
<div class="py-12 md:py-20 flex items-center justify-center px-4">
    <div class="w-full max-w-md bg-white dark:bg-slate-800 rounded-3xl p-6 sm:p-8 border border-slate-200 dark:border-slate-700 shadow-xl transition-colors">
        <div class="text-center mb-8 space-y-2">
            <div class="w-12 h-12 rounded-2xl bg-emerald-600 text-white flex items-center justify-center mx-auto shadow-md shadow-emerald-600/30">
                <i data-lucide="log-in" class="w-6 h-6"></i>
            </div>
            <h2 class="text-2xl font-extrabold text-slate-900 dark:text-white">Masuk Akun Pendaftar</h2>
            <p class="text-xs text-slate-500 dark:text-slate-400">Silakan masukkan NISN, Username, atau Email terdaftar Anda.</p>
        </div>

        <form action="{{ route('login.post') }}" method="POST" class="space-y-4">
            @csrf
            <div>
                <label for="login" class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1.5">NISN / Username / Email</label>
                <div class="relative">
                    <i data-lucide="user" class="w-5 h-5 text-slate-400 absolute left-3.5 top-1/2 -translate-y-1/2"></i>
                    <input type="text" id="login" name="login" required value="{{ old('login') }}" placeholder="Contoh: 0078912345 atau ahmadfauzi" class="w-full pl-11 pr-4 py-3 rounded-xl bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-900 dark:text-white text-sm focus:outline-none focus:border-emerald-500">
                </div>
            </div>

            <div>
                <div class="flex items-center justify-between mb-1.5">
                    <label for="password" class="block text-xs font-bold text-slate-700 dark:text-slate-300">Kata Sandi</label>
                    <a href="{{ route('password.request') }}" class="text-xs font-semibold text-emerald-600 dark:text-emerald-400 hover:underline">Lupa Sandi?</a>
                </div>
                <div class="relative">
                    <i data-lucide="lock" class="w-5 h-5 text-slate-400 absolute left-3.5 top-1/2 -translate-y-1/2"></i>
                    <input type="password" id="password" name="password" required placeholder="••••••••" class="w-full pl-11 pr-11 py-3 rounded-xl bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-900 dark:text-white text-sm focus:outline-none focus:border-emerald-500">
                    <button type="button" onclick="togglePasswordVisibility('password', this)" class="absolute right-3.5 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600">
                        <i data-lucide="eye" class="w-4 h-4"></i>
                    </button>
                </div>
            </div>

            <div class="flex items-center">
                <input type="checkbox" id="remember" name="remember" class="w-4 h-4 text-emerald-600 border-slate-300 rounded focus:ring-emerald-500">
                <label for="remember" class="ml-2 text-xs text-slate-600 dark:text-slate-400">Ingat sesi saya di perangkat ini</label>
            </div>

            <button type="submit" class="w-full py-3.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-sm shadow-md shadow-emerald-600/25 transition-all">
                Masuk Sekarang
            </button>
        </form>

        <!-- Quick Demo Credentials Box -->
        <div class="mt-6 p-3 rounded-2xl bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-800 text-xs text-slate-700 dark:text-slate-300 space-y-1">
            <span class="font-bold text-emerald-700 dark:text-emerald-400 block">Akun Pengujian Cepat:</span>
            <div class="flex items-center justify-between">
                <span>Siswa 1 (Lolos): <code class="font-mono bg-white dark:bg-slate-800 px-1 py-0.5 rounded">siswa@smkib.com</code> / <code class="font-mono">siswa123</code></span>
                <button type="button" onclick="fillLogin('siswa@smkib.com', 'siswa123')" class="text-[10px] text-emerald-600 dark:text-emerald-400 font-bold underline">Isi</button>
            </div>
            <div class="flex items-center justify-between">
                <span>Siswa 2 (Diproses): <code class="font-mono bg-white dark:bg-slate-800 px-1 py-0.5 rounded">amanda@smkib.com</code> / <code class="font-mono">siswa123</code></span>
                <button type="button" onclick="fillLogin('amanda@smkib.com', 'siswa123')" class="text-[10px] text-emerald-600 dark:text-emerald-400 font-bold underline">Isi</button>
            </div>
        </div>

        <div class="mt-6 pt-6 border-t border-slate-200 dark:border-slate-700 text-center space-y-3">
            <p class="text-xs text-slate-600 dark:text-slate-400">
                Belum memiliki akun pendaftar?
                <a href="{{ route('register') }}" class="font-bold text-emerald-600 dark:text-emerald-400 hover:underline">Daftar Akun Baru</a>
            </p>
            <p class="text-xs">
                <a href="{{ route('admin.login') }}" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200">
                    &larr; Masuk sebagai Petugas TU / Kepala Sekolah
                </a>
            </p>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    function togglePasswordVisibility(inputId, btn) {
        const input = document.getElementById(inputId);
        if (input.type === 'password') {
            input.type = 'text';
            btn.innerHTML = `<i data-lucide="eye-off" class="w-4 h-4"></i>`;
        } else {
            input.type = 'password';
            btn.innerHTML = `<i data-lucide="eye" class="w-4 h-4"></i>`;
        }
        lucide.createIcons();
    }

    function fillLogin(login, pass) {
        document.getElementById('login').value = login;
        document.getElementById('password').value = pass;
    }
</script>
@endpush

