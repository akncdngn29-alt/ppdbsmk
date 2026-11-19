@extends('layouts.app')

@section('title', 'Masuk Akun - PPDB SMK IB Khalifah Bangsa')

@section('content')
<div class="py-10 md:py-16 flex items-center justify-center px-4 bg-slate-50 min-h-[calc(100vh-140px)]">
    <div class="w-full max-w-md bg-[#0B182F] rounded-3xl p-6 sm:p-8 border border-slate-800 shadow-2xl text-slate-200">
        
        <!-- Header with Official Logo -->
        <div class="text-center mb-6 space-y-2">
            <img src="{{ asset('images/logo.png') }}" alt="Logo SMK Islam Bina Khalifah Bangsa" class="w-14 h-14 mx-auto object-contain drop-shadow">
            <h2 class="text-xl sm:text-2xl font-black text-white tracking-tight">Selamat Datang Kembali!</h2>
            <p class="text-xs text-slate-300">Silakan masukkan email atau NISN dan kata sandi akun Anda</p>
        </div>

        <!-- Alert Session Status -->
        @if(session('info'))
        <div class="mb-4 px-4 py-2.5 rounded-xl bg-sky-950/70 border border-sky-500/40 text-sky-300 text-xs flex items-center gap-2">
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
        <form action="{{ route('login.post') }}" method="POST" class="space-y-4">
            @csrf
            <div>
                <label for="login" class="block text-xs font-bold text-slate-300 mb-1.5">Alamat Email / NISN</label>
                <div class="relative">
                    <i data-lucide="user" class="w-4 h-4 text-slate-400 absolute left-3.5 top-1/2 -translate-y-1/2"></i>
                    <input type="text" id="login" name="login" required value="{{ old('login') }}" placeholder="siswa@smkib.com atau 0078912345"
                           class="w-full pl-10 pr-4 py-2.5 rounded-xl bg-[#060E1D] border border-slate-700/80 text-white placeholder-slate-500 text-xs focus:outline-none focus:border-[#f43f5e] focus:ring-1 focus:ring-[#f43f5e] font-medium transition-all">
                </div>
            </div>

            <div>
                <div class="flex items-center justify-between mb-1.5">
                    <label for="password" class="block text-xs font-bold text-slate-300">Kata Sandi</label>
                    <a href="{{ route('password.request') }}" class="text-xs font-semibold text-[#f43f5e] hover:underline">Lupa Password?</a>
                </div>
                <div class="relative">
                    <i data-lucide="lock" class="w-4 h-4 text-slate-400 absolute left-3.5 top-1/2 -translate-y-1/2"></i>
                    <input type="password" id="password" name="password" required placeholder="••••••••••••"
                           class="w-full pl-10 pr-10 py-2.5 rounded-xl bg-[#060E1D] border border-slate-700/80 text-white placeholder-slate-500 text-xs focus:outline-none focus:border-[#f43f5e] focus:ring-1 focus:ring-[#f43f5e] font-medium transition-all">
                    <button type="button" onclick="togglePassword('password', this)" class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-200">
                        <i data-lucide="eye" class="w-4 h-4"></i>
                    </button>
                </div>
            </div>

            <div class="flex items-center">
                <input type="checkbox" id="remember" name="remember" class="w-4 h-4 rounded bg-[#060E1D] border-slate-700 text-[#f43f5e] focus:ring-[#f43f5e]">
                <label for="remember" class="ml-2 text-xs text-slate-300 cursor-pointer">Ingat Saya</label>
            </div>

            <button type="submit" class="w-full py-3 rounded-full bg-[#f43f5e] hover:bg-[#e11d48] text-white font-extrabold text-xs uppercase tracking-wider shadow-md hover:shadow-lg transition-all active:scale-[0.99] flex items-center justify-center gap-2">
                <span>MASUK</span>
                <i data-lucide="arrow-right" class="w-4 h-4"></i>
            </button>
        </form>

        <div class="mt-4 text-center">
            <span class="text-xs text-slate-400">Belum punya akun? </span>
            <a href="{{ route('register') }}" class="text-xs font-bold text-[#f43f5e] hover:underline">Daftar Disini</a>
        </div>

        <!-- Center Pill Divider -->
        <div class="relative my-6 flex items-center justify-center">
            <div class="absolute inset-0 flex items-center">
                <div class="w-full border-t border-slate-800"></div>
            </div>
            <div class="relative px-3 py-1 bg-[#060E1D] border border-slate-700/80 rounded-full flex items-center gap-3 text-slate-400 text-xs">
                <i data-lucide="school" class="w-3.5 h-3.5 text-pink-400"></i>
                <span>&bull;</span>
                <i data-lucide="credit-card" class="w-3.5 h-3.5 text-pink-400"></i>
                <span>&bull;</span>
                <i data-lucide="shield-check" class="w-3.5 h-3.5 text-pink-400"></i>
            </div>
        </div>

        <!-- Quick Access / Shortcuts Grid -->
        <div class="space-y-2">
            <div class="text-[11px] font-bold text-slate-400 text-center uppercase tracking-wider">
                Akses Masuk Cepat (Pintasan Resmi)
            </div>

            <div>
                <!-- Calon Siswa (Buka Modal Pilihan Siswa) -->
                <button type="button" onclick="openStudentModal()" class="w-full p-3 rounded-2xl bg-[#060E1D] border border-slate-700/70 hover:border-[#f43f5e] transition-all text-left flex items-center justify-between gap-3 group">
                    <div class="flex items-center gap-3">
                        <div class="w-9 h-9 rounded-full bg-pink-500/20 border-2 border-[#f43f5e] flex items-center justify-center text-[#f43f5e] shrink-0 group-hover:scale-105 transition-transform">
                            <i data-lucide="graduation-cap" class="w-5 h-5"></i>
                        </div>
                        <div>
                            <span class="block text-xs font-bold text-white group-hover:text-pink-400 transition-colors">Calon Siswa</span>
                            <span class="block text-[10px] text-slate-400">Pilih akun siswa aktif dari daftar untuk masuk instan</span>
                        </div>
                    </div>
                    <span class="px-2.5 py-1 rounded-full bg-[#f43f5e]/20 text-pink-300 text-[10px] font-bold group-hover:bg-[#f43f5e] group-hover:text-white transition-colors">
                        Pilih Akun &rarr;
                    </span>
                </button>
            </div>
        </div>

        <div class="mt-6 pt-4 border-t border-slate-800/80 text-center">
            <a href="{{ route('admin.login') }}" class="text-xs text-slate-400 hover:text-pink-400 transition-colors flex items-center justify-center gap-1.5">
                <i data-lucide="shield" class="w-3.5 h-3.5"></i>
                <span>Portal Khusus Administrator & Kepala Sekolah &rarr;</span>
            </a>
        </div>

    </div>
</div>

<!-- Modal Pilihan Akun Siswa -->
<div id="studentModal" class="fixed inset-0 z-50 bg-[#0B182F]/80 backdrop-blur-xs hidden flex items-center justify-center p-4">
    <div class="bg-[#0B182F] rounded-3xl max-w-md w-full border border-slate-800 shadow-2xl p-6 text-slate-200 space-y-4">
        <div class="flex items-center justify-between pb-3 border-b border-slate-800">
            <div class="flex items-center gap-2.5">
                <div class="w-8 h-8 rounded-xl bg-pink-500/20 text-[#f43f5e] flex items-center justify-center">
                    <i data-lucide="users" class="w-4 h-4"></i>
                </div>
                <div>
                    <h3 class="text-sm font-bold text-white">Pilih Akun Calon Siswa</h3>
                    <p class="text-[11px] text-slate-400">Pilih salah satu siswa untuk langsung masuk ke dashboard</p>
                </div>
            </div>
            <button type="button" onclick="closeStudentModal()" aria-label="Tutup Modal" class="text-slate-400 hover:text-white">
                <i data-lucide="x" class="w-5 h-5"></i>
            </button>
        </div>

        <div class="max-h-72 overflow-y-auto space-y-2 pr-1">
            @forelse($sampleStudents as $st)
            <form action="{{ route('auth.quick_login') }}" method="POST">
                @csrf
                <input type="hidden" name="role" value="siswa">
                <input type="hidden" name="user_id" value="{{ $st->id_user }}">
                <button type="submit" class="w-full p-3 rounded-2xl bg-[#060E1D] hover:bg-white/10 border border-slate-800 hover:border-[#f43f5e] transition-all text-left flex items-center justify-between group">
                    <div class="flex items-center gap-3 truncate">
                        <div class="w-9 h-9 rounded-xl bg-slate-800 text-pink-400 flex items-center justify-center font-bold text-xs shrink-0 group-hover:bg-[#f43f5e] group-hover:text-white transition-colors">
                            {{ strtoupper(substr($st->nama, 0, 1)) }}
                        </div>
                        <div class="truncate">
                            <span class="block text-xs font-bold text-white truncate">{{ $st->nama }}</span>
                            <span class="block text-[10px] text-slate-400 font-mono">NISN: {{ $st->nisn ?? '-' }} &bull; {{ $st->email }}</span>
                            @if($st->pendaftar && $st->pendaftar->jurusan1)
                            <span class="text-[9px] px-2 py-0.5 rounded-full bg-pink-500/20 text-pink-300 font-semibold">{{ $st->pendaftar->jurusan1->nama_jurusan }}</span>
                            @endif
                        </div>
                    </div>
                    <span class="text-xs text-[#f43f5e] font-bold group-hover:translate-x-1 transition-transform shrink-0 ml-2">
                        Masuk &rarr;
                    </span>
                </button>
            </form>
            @empty
            <p class="text-xs text-slate-400 text-center py-4">Belum ada akun calon siswa terdaftar.</p>
            @endforelse
        </div>

        <div class="pt-2 border-t border-slate-800 text-right">
            <button type="button" onclick="closeStudentModal()" class="px-4 py-2 rounded-full bg-slate-800 hover:bg-slate-700 text-white text-xs font-bold">
                Tutup
            </button>
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

    function openStudentModal() {
        document.getElementById('studentModal').classList.remove('hidden');
    }

    function closeStudentModal() {
        document.getElementById('studentModal').classList.add('hidden');
    }
</script>
@endpush
