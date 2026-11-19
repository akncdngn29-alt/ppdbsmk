@extends('layouts.app')

@section('title', 'Portal Masuk Administrator & Kepala Sekolah')

@section('content')
<div class="py-8 md:py-14 flex items-center justify-center px-4 bg-slate-950 min-h-[calc(100vh-140px)]">
    <div class="w-full max-w-md bg-[#161f30] rounded-3xl p-6 sm:p-8 border border-slate-800 shadow-2xl text-slate-200">
        
        <!-- Header -->
        <div class="text-center mb-6 space-y-1.5">
            <div class="flex justify-center mb-3">
                <img src="{{ asset('images/logo-sekolah.png') }}" alt="Logo SMK IB Khalifah Bangsa" class="w-16 h-16 object-contain">
            </div>
            <h2 class="text-2xl font-black text-white tracking-tight">Portal Staf & Pimpinan</h2>
            <p class="text-xs text-slate-400">Khusus Petugas Tata Usaha (TU) dan Kepala Sekolah</p>
        </div>

        @if(session('info'))
        <div class="mb-4 px-4 py-2.5 rounded-xl bg-slate-800/80 border border-slate-700 text-slate-300 text-xs flex items-center gap-2">
            <i data-lucide="info" class="w-4 h-4 text-sky-400 shrink-0"></i>
            <span>{{ session('info') }}</span>
        </div>
        @endif

        @if(session('success'))
        <div class="mb-4 px-4 py-2.5 rounded-xl bg-emerald-950/70 border border-emerald-500/40 text-emerald-300 text-xs flex items-center gap-2">
            <i data-lucide="check-circle-2" class="w-4 h-4 text-emerald-400 shrink-0"></i>
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

        <!-- Login Form -->
        <form action="{{ route('admin.login.post') }}" method="POST" class="space-y-4">
            @csrf
            <div>
                <label for="login" class="block text-xs font-bold text-slate-300 mb-1.5">Email / Username Administrator</label>
                <div class="relative">
                    <i data-lucide="mail" class="w-4 h-4 text-slate-400 absolute left-3.5 top-1/2 -translate-y-1/2"></i>
                    <input type="text" id="login" name="login" required value="{{ old('login') }}" placeholder="admin@smkib.com atau kepsek@smkib.com"
                           class="w-full pl-10 pr-4 py-2.5 rounded-xl bg-[#0d1424] border border-slate-700/80 text-white placeholder-slate-500 text-xs focus:outline-none focus:border-[#fa2f79] focus:ring-1 focus:ring-[#fa2f79] font-medium">
                </div>
            </div>

            <div>
                <label for="password" class="block text-xs font-bold text-slate-300 mb-1.5">Kata Sandi</label>
                <div class="relative">
                    <i data-lucide="lock" class="w-4 h-4 text-slate-400 absolute left-3.5 top-1/2 -translate-y-1/2"></i>
                    <input type="password" id="password" name="password" required placeholder="••••••••••••"
                           class="w-full pl-10 pr-10 py-2.5 rounded-xl bg-[#0d1424] border border-slate-700/80 text-white placeholder-slate-500 text-xs focus:outline-none focus:border-[#fa2f79] focus:ring-1 focus:ring-[#fa2f79] font-medium">
                    <button type="button" onclick="togglePassword('password', this)" class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-200">
                        <i data-lucide="eye" class="w-4 h-4"></i>
                    </button>
                </div>
            </div>

            <div class="flex items-center">
                <input type="checkbox" id="remember" name="remember" class="w-4 h-4 rounded bg-[#0d1424] border-slate-700 text-[#fa2f79] focus:ring-[#fa2f79]">
                <label for="remember" class="ml-2 text-xs text-slate-400 cursor-pointer">Ingat Sesi Masuk</label>
            </div>

            <button type="submit" class="w-full py-3 rounded-xl bg-[#fa2f79] hover:bg-[#e11d48] text-white font-extrabold text-xs uppercase tracking-wider shadow-lg shadow-pink-500/30 transition-all active:scale-[0.99] flex items-center justify-center gap-2">
                <i data-lucide="shield-check" class="w-4 h-4"></i>
                <span>MASUK KE PANEL ADMIN</span>
            </button>
        </form>

        <!-- Center Pill Divider with Icons -->
        <div class="relative my-6 flex items-center justify-center">
            <div class="absolute inset-0 flex items-center">
                <div class="w-full border-t border-slate-800"></div>
            </div>
            <div class="relative px-3 py-1 bg-[#101826] border border-slate-700/80 rounded-full flex items-center gap-3 text-slate-400 text-xs">
                <i data-lucide="shield" class="w-3.5 h-3.5"></i>
                <span>&rsaquo;</span>
                <i data-lucide="key" class="w-3.5 h-3.5"></i>
                <span>&rsaquo;</span>
                <i data-lucide="user-check" class="w-3.5 h-3.5"></i>
            </div>
        </div>

        <!-- Quick Access Shortcuts Grid for Testing/Demo -->
        <div class="space-y-2">
            <div class="text-[11px] font-bold text-slate-400 text-center uppercase tracking-wider">
                Akses Masuk Cepat (Pintasan Resmi)
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                <!-- 1. Petugas TU -->
                <form action="{{ route('auth.quick_login') }}" method="POST">
                    @csrf
                    <input type="hidden" name="role" value="tu">
                    <button type="submit" class="w-full p-2.5 rounded-xl bg-[#0d1424] border border-slate-700/70 hover:border-emerald-500 transition-all text-left flex items-center gap-2.5 group">
                        <div class="w-8 h-8 rounded-full bg-emerald-950 border-2 border-emerald-500 flex items-center justify-center text-emerald-400 shrink-0 group-hover:scale-105 transition-transform">
                            <i data-lucide="file-check-2" class="w-4 h-4"></i>
                        </div>
                        <div class="truncate">
                            <span class="block text-xs font-bold text-white group-hover:text-emerald-400 transition-colors">Petugas TU</span>
                            <span class="block text-[10px] text-slate-400">Panitia PPDB</span>
                        </div>
                    </button>
                </form>

                <!-- 2. Kepala Sekolah -->
                <form action="{{ route('auth.quick_login') }}" method="POST">
                    @csrf
                    <input type="hidden" name="role" value="kepsek">
                    <button type="submit" class="w-full p-2.5 rounded-xl bg-[#0d1424] border border-slate-700/70 hover:border-purple-500 transition-all text-left flex items-center gap-2.5 group">
                        <div class="w-8 h-8 rounded-full bg-purple-950 border-2 border-purple-500 flex items-center justify-center text-purple-400 shrink-0 group-hover:scale-105 transition-transform">
                            <i data-lucide="user-check" class="w-4 h-4"></i>
                        </div>
                        <div class="truncate">
                            <span class="block text-xs font-bold text-white group-hover:text-purple-400 transition-colors">Kepala Sekolah</span>
                            <span class="block text-[10px] text-slate-400">Pimpinan & Validasi</span>
                        </div>
                    </button>
                </form>
            </div>
        </div>

        <div class="mt-6 pt-4 border-t border-slate-800 text-center">
            <a href="{{ route('login') }}" class="text-xs text-slate-400 hover:text-emerald-400 transition-colors">
                &larr; Kembali ke Halaman Masuk Siswa
            </a>
        </div>

    </div>
</div>
@endsection

@push('scripts')
<script>
    function togglePassword(id, btn) {
        const inp = document.getElementById(id);
        if (inp.type === 'password') {
            inp.type = 'text';
            btn.innerHTML = `<i data-lucide="eye-off" class="w-4 h-4"></i>`;
        } else {
            inp.type = 'password';
            btn.innerHTML = `<i data-lucide="eye" class="w-4 h-4"></i>`;
        }
        lucide.createIcons();
    }
</script>
@endpush
