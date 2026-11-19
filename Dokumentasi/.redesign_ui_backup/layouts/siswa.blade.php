<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Panel Calon Siswa') - PPDB SMK IB Khalifah Bangsa</title>

    <link rel="icon" type="image/png" href="{{ asset('images/logo.png') }}">

    <!-- Google Fonts & Tailwind CSS CDN -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['"Plus Jakarta Sans"', 'sans-serif'],
                    },
                    colors: {
                        navy: {
                            50: '#f0f4f9',
                            100: '#dde6f2',
                            200: '#c0d2e7',
                            300: '#94b5d6',
                            400: '#6293c1',
                            500: '#3d75ab',
                            600: '#2b5c90',
                            700: '#1b3b64',
                            800: '#0F2444',
                            900: '#0B182F',
                            950: '#060E1D',
                        },
                        pink: {
                            50: '#fff1f2',
                            100: '#ffe4e6',
                            200: '#fecdd3',
                            300: '#fda4af',
                            400: '#fb7185',
                            500: '#f43f5e',
                            600: '#e11d48',
                            700: '#be123c',
                            800: '#9f1239',
                            900: '#881337',
                        },
                        primary: {
                            50: '#fff1f2',
                            100: '#ffe4e6',
                            200: '#fecdd3',
                            500: '#f43f5e',
                            600: '#e11d48',
                            700: '#be123c',
                            800: '#9f1239',
                            900: '#881337',
                        }
                    }
                }
            }
        }
    </script>
    <!-- Lucide Icons -->
    <script src="https://unpkg.com/lucide@latest"></script>

    <script>
        if (localStorage.theme === 'dark' || (!('theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
            document.documentElement.classList.add('dark');
        } else {
            document.documentElement.classList.remove('dark');
        }
    </script>
    <style>
        @media print {
            .no-print { display: none !important; }
        }
        input[type="password"]::-ms-reveal,
        input[type="password"]::-ms-clear {
            display: none !important;
        }
    </style>
    @stack('styles')
</head>
<body class="bg-slate-100 text-slate-800 dark:bg-slate-950 dark:text-slate-100 min-h-screen flex font-sans antialiased transition-colors">

    @php
        $user = Auth::user();
        $pendaftar = $user ? $user->pendaftar : null;
        $activeGelombang = \App\Models\Gelombang::where('status_aktif', true)->first();
    @endphp

    <!-- Mobile Sidebar Backdrop -->
    <div id="sidebar-backdrop" class="fixed inset-0 bg-[#0B182F]/70 backdrop-blur-xs z-40 hidden md:hidden transition-opacity" onclick="toggleSidebar()"></div>

    <!-- Left Sidebar (Navy Theme Sesuai Identitas Resmi) -->
    <aside id="sidebar" class="fixed inset-y-0 left-0 z-50 w-72 bg-[#0B182F] text-slate-200 border-r border-[#13233f] flex flex-col shrink-0 no-print transition-transform duration-300 -translate-x-full md:translate-x-0 md:static shadow-xl">
        
        <!-- Brand Header (Logo & Nama Sekolah) -->
        <div class="h-18 md:h-20 flex items-center gap-3 px-5 border-b border-[#13233f] bg-[#060E1D]">
            <img src="{{ asset('images/logo.png') }}" alt="Logo SMK Islam Bina Khalifah Bangsa" class="w-10 h-10 object-contain shrink-0 drop-shadow">
            <div class="truncate">
                <span class="block text-xs font-black text-white tracking-wider leading-tight">SMK IB KHALIFAH</span>
                <span class="block text-[10px] font-bold text-[#f43f5e] uppercase tracking-wider">Panel Calon Siswa</span>
            </div>
            <!-- Close Button for Mobile -->
            <button onclick="toggleSidebar()" aria-label="Tutup Sidebar" class="md:hidden ml-auto p-1.5 rounded-lg text-slate-400 hover:text-white hover:bg-white/10">
                <i data-lucide="x" class="w-5 h-5"></i>
            </button>
        </div>

        <!-- Student Profile Card in Sidebar -->
        <div class="p-4 border-b border-[#13233f] bg-[#060E1D]/50">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-full bg-pink-500/20 border border-pink-500/40 text-pink-400 font-black text-sm flex items-center justify-center shrink-0">
                    {{ strtoupper(substr($user->nama ?? 'S', 0, 1)) }}
                </div>
                <div class="min-w-0 flex-1">
                    <h3 class="text-xs font-bold text-white truncate">{{ $user->nama ?? 'Calon Siswa' }}</h3>
                    <p class="text-[11px] text-slate-400 font-mono truncate">NISN: {{ $user->nisn ?? '-' }}</p>
                </div>
            </div>
            @if($pendaftar)
                <div class="mt-3 flex items-center justify-between text-[10px] px-2.5 py-1.5 rounded-lg bg-white/5 border border-white/10">
                    <span class="text-slate-400">No. Daftar:</span>
                    <span class="font-mono font-bold text-[#f43f5e]">{{ $pendaftar->no_pendaftaran }}</span>
                </div>
            @else
                <div class="mt-3 px-2 py-1 rounded-lg bg-amber-500/10 border border-amber-500/30 text-[10px] text-amber-400 font-medium text-center">
                    Belum Mengisi Formulir
                </div>
            @endif
        </div>

        <!-- Navigation Menu Vertikal -->
        <nav class="flex-1 overflow-y-auto p-3.5 space-y-1 text-xs font-semibold">
            
            <div class="text-[10px] uppercase text-slate-400 font-bold px-3 pt-2 pb-1">Alur Pendaftaran</div>
            
            <!-- 1. Dashboard -->
            <a href="{{ route('siswa.dashboard') }}" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl transition-colors {{ request()->routeIs('siswa.dashboard') ? 'bg-[#f43f5e] text-white font-bold shadow-md' : 'text-slate-300 hover:text-white hover:bg-white/10' }}">
                <i data-lucide="layout-dashboard" class="w-4 h-4"></i>
                <span>Dashboard Utama</span>
            </a>

            <!-- 2. Formulir Pendaftaran -->
            <a href="{{ route('siswa.formulir') }}" class="flex items-center justify-between px-3.5 py-2.5 rounded-xl transition-colors {{ request()->routeIs('siswa.formulir') ? 'bg-[#f43f5e] text-white font-bold shadow-md' : 'text-slate-300 hover:text-white hover:bg-white/10' }}">
                <div class="flex items-center gap-3">
                    <i data-lucide="file-text" class="w-4 h-4"></i>
                    <span>Formulir Biodata</span>
                </div>
                @if($pendaftar && $pendaftar->is_locked)
                    <span class="text-[9px] bg-slate-800 text-slate-400 px-1.5 py-0.5 rounded font-mono">Terkunci</span>
                @endif
            </a>

            <!-- 3. Pembayaran Formulir -->
            <a href="{{ route('siswa.pembayaran') }}" class="flex items-center justify-between px-3.5 py-2.5 rounded-xl transition-colors {{ request()->routeIs('siswa.pembayaran') ? 'bg-[#f43f5e] text-white font-bold shadow-md' : 'text-slate-300 hover:text-white hover:bg-white/10' }}">
                <div class="flex items-center gap-3">
                    <i data-lucide="credit-card" class="w-4 h-4"></i>
                    <span>Pembayaran PPDB</span>
                </div>
                @if($pendaftar && $pendaftar->status_pembayaran === 'Paid')
                    <span class="text-[9px] bg-emerald-500/20 text-emerald-400 px-2 py-0.5 rounded-full font-bold">Lunas</span>
                @endif
            </a>

            <!-- 4. Upload Berkas Digital -->
            <a href="{{ route('siswa.berkas') }}" class="flex items-center justify-between px-3.5 py-2.5 rounded-xl transition-colors {{ request()->routeIs('siswa.berkas') ? 'bg-[#f43f5e] text-white font-bold shadow-md' : 'text-slate-300 hover:text-white hover:bg-white/10' }}">
                <div class="flex items-center gap-3">
                    <i data-lucide="upload-cloud" class="w-4 h-4"></i>
                    <span>Upload Berkas</span>
                </div>
                @if($pendaftar && $pendaftar->berkas->whereIn('jenis_berkas', ['ijazah', 'kk', 'pasfoto', 'akta'])->count() >= 4)
                    <span class="text-[9px] bg-emerald-500/20 text-emerald-400 px-2 py-0.5 rounded-full font-bold">Lengkap</span>
                @elseif($pendaftar && $pendaftar->berkas->count() > 0)
                    <span class="text-[9px] bg-amber-500/20 text-amber-400 px-2 py-0.5 rounded-full font-bold">{{ $pendaftar->berkas->whereIn('jenis_berkas', ['ijazah', 'kk', 'pasfoto', 'akta'])->count() }}/4</span>
                @endif
            </a>

            <div class="text-[10px] uppercase text-slate-400 font-bold px-3 pt-4 pb-1">Dokumen & Cetak</div>

            @if($pendaftar)
                <a href="{{ route('siswa.cetak.formulir') }}" target="_blank" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-slate-300 hover:text-white hover:bg-white/10 transition-colors">
                    <i data-lucide="printer" class="w-4 h-4 text-pink-400"></i>
                    <span>Cetak Formulir (PDF)</span>
                </a>

                <a href="{{ route('siswa.jadwal') }}" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl transition-colors {{ request()->routeIs('siswa.jadwal') ? 'bg-[#f43f5e] text-white font-bold shadow-md' : 'text-slate-300 hover:text-white hover:bg-white/10' }}">
                    <i data-lucide="ticket" class="w-4 h-4 text-sky-400"></i>
                    <span>Jadwal & Kartu Ujian</span>
                </a>

                @if($pendaftar->status_pembayaran === 'Paid')
                <a href="{{ route('siswa.cetak.kwitansi') }}" target="_blank" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-slate-300 hover:text-white hover:bg-white/10 transition-colors">
                    <i data-lucide="receipt" class="w-4 h-4 text-amber-400"></i>
                    <span>Kwitansi Lunas (PDF)</span>
                </a>
                @endif

                @if($pendaftar->status_kelulusan === 'Diterima')
                <a href="{{ route('siswa.cetak.skl') }}" target="_blank" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-slate-300 hover:text-white hover:bg-white/10 transition-colors">
                    <i data-lucide="award" class="w-4 h-4 text-emerald-400"></i>
                    <span>Surat Kelulusan (SKL)</span>
                </a>
                @endif
            @else
                <div class="px-3.5 py-2 text-[11px] text-slate-500 italic">
                    Lengkapi formulir untuk mencetak dokumen.
                </div>
            @endif

            <div class="text-[10px] uppercase text-slate-400 font-bold px-3 pt-4 pb-1">Pasca Seleksi & Akun</div>

            <!-- 5. Daftar Ulang -->
            <a href="{{ route('siswa.daftar_ulang') }}" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl transition-colors {{ request()->routeIs('siswa.daftar_ulang') ? 'bg-[#f43f5e] text-white font-bold shadow-md' : 'text-slate-300 hover:text-white hover:bg-white/10' }}">
                <i data-lucide="check-circle-2" class="w-4 h-4"></i>
                <span>Daftar Ulang</span>
            </a>

            <!-- 6. Pengaturan Akun -->
            <a href="{{ route('siswa.pengaturan') }}" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl transition-colors {{ request()->routeIs('siswa.pengaturan') ? 'bg-[#f43f5e] text-white font-bold shadow-md' : 'text-slate-300 hover:text-white hover:bg-white/10' }}">
                <i data-lucide="settings" class="w-4 h-4"></i>
                <span>Pengaturan Akun</span>
            </a>

            <!-- 7. Riwayat Aktivitas Akun -->
            <a href="{{ route('siswa.riwayat_aktivitas') }}" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl transition-colors {{ request()->routeIs('siswa.riwayat_aktivitas') ? 'bg-[#f43f5e] text-white font-bold shadow-md' : 'text-slate-300 hover:text-white hover:bg-white/10' }}">
                <i data-lucide="history" class="w-4 h-4"></i>
                <span>Riwayat Aktivitas</span>
            </a>

            <!-- 8. Pusat Bantuan & FAQ -->
            <a href="{{ route('siswa.bantuan') }}" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl transition-colors {{ request()->routeIs('siswa.bantuan') ? 'bg-[#f43f5e] text-white font-bold shadow-md' : 'text-slate-300 hover:text-white hover:bg-white/10' }}">
                <i data-lucide="help-circle" class="w-4 h-4 text-pink-400"></i>
                <span>Pusat Bantuan & FAQ</span>
            </a>

        </nav>

        <!-- Sidebar Footer / Logout -->
        <div class="p-3 border-t border-[#13233f] bg-[#060E1D]">
            <form action="{{ route('logout') }}" method="POST">
                @csrf
                <button type="submit" class="w-full flex items-center justify-center gap-2 px-3 py-2.5 rounded-xl text-rose-400 hover:text-white hover:bg-rose-500/20 text-xs font-bold transition-all">
                    <i data-lucide="log-out" class="w-4 h-4"></i>
                    <span>Keluar Akun</span>
                </button>
            </form>
        </div>

    </aside>

    <!-- Main Content Area -->
    <div class="flex-1 flex flex-col min-w-0 overflow-x-hidden">
        
        <!-- Topbar -->
        <header class="h-16 md:h-20 bg-white dark:bg-slate-900 border-b border-slate-200 dark:border-slate-800 flex items-center justify-between px-4 sm:px-6 lg:px-8 no-print transition-colors sticky top-0 z-30 shadow-xs">
            
            <!-- Left Header: Mobile Toggle & Page Title -->
            <div class="flex items-center gap-3">
                <button onclick="toggleSidebar()" aria-label="Toggle Sidebar" class="md:hidden p-2 rounded-xl text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800">
                    <i data-lucide="menu" class="w-5 h-5"></i>
                </button>
                <div>
                    <h1 class="text-base md:text-lg font-bold text-[#0B182F] dark:text-white truncate">
                        @yield('title', 'Dashboard Calon Siswa')
                    </h1>
                    <p class="text-[11px] text-slate-500 dark:text-slate-400 hidden sm:block">
                        PPDB SMK Islam Bina Khalifah Bangsa Kota Metro
                    </p>
                </div>
            </div>

            <!-- Right Header: Gelombang Badge & Actions -->
            <div class="flex items-center gap-2 sm:gap-3">
                @if($activeGelombang)
                <div class="hidden lg:flex items-center gap-1.5 px-3 py-1.5 rounded-full bg-pink-50 dark:bg-pink-950/40 border border-pink-200 dark:border-pink-800 text-[11px] font-bold text-[#f43f5e]">
                    <span class="w-2 h-2 rounded-full bg-[#f43f5e] animate-pulse"></span>
                    <span>{{ $activeGelombang->nama_gelombang }} ({{ $activeGelombang->tahun_ajaran }})</span>
                </div>
                @endif

                <!-- Dark Mode Toggle -->
                <button id="theme-toggle" type="button" aria-label="Toggle Dark Mode" class="p-2 rounded-xl text-slate-500 hover:text-slate-700 dark:text-slate-400 dark:hover:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors">
                    <i id="theme-toggle-dark-icon" data-lucide="moon" class="hidden w-5 h-5"></i>
                    <i id="theme-toggle-light-icon" data-lucide="sun" class="hidden w-5 h-5"></i>
                </button>

                <!-- Visit Public Website -->
                <a href="{{ route('landing') }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full border border-slate-200 dark:border-slate-700 text-xs font-bold text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800 transition-colors">
                    <i data-lucide="globe" class="w-3.5 h-3.5 text-[#f43f5e]"></i>
                    <span class="hidden sm:inline">Website Utama</span>
                </a>
            </div>

        </header>

        <!-- Flash Messages (Alerts) -->
        <div class="w-full px-4 sm:px-6 lg:px-8 pt-4">
            @if(session('success'))
            <div class="p-4 mb-3 rounded-2xl bg-emerald-50 dark:bg-emerald-950/50 border border-emerald-200 dark:border-emerald-800 text-emerald-900 dark:text-emerald-200 text-xs flex items-center justify-between shadow-xs">
                <div class="flex items-center gap-2">
                    <i data-lucide="check-circle" class="w-4 h-4 text-emerald-600 shrink-0"></i>
                    <span>{{ session('success') }}</span>
                </div>
                <button onclick="this.parentElement.remove()" class="text-emerald-600 hover:text-emerald-800">&times;</button>
            </div>
            @endif

            @if(session('error'))
            <div class="p-4 mb-3 rounded-2xl bg-rose-50 dark:bg-rose-950/50 border border-rose-200 dark:border-rose-800 text-rose-900 dark:text-rose-200 text-xs flex items-center justify-between shadow-xs">
                <div class="flex items-center gap-2">
                    <i data-lucide="alert-circle" class="w-4 h-4 text-rose-600 shrink-0"></i>
                    <span>{{ session('error') }}</span>
                </div>
                <button onclick="this.parentElement.remove()" class="text-rose-600 hover:text-rose-800">&times;</button>
            </div>
            @endif

            @if(session('info'))
            <div class="p-4 mb-3 rounded-2xl bg-sky-50 dark:bg-sky-950/50 border border-sky-200 dark:border-sky-800 text-sky-900 dark:text-sky-200 text-xs flex items-center justify-between shadow-xs">
                <div class="flex items-center gap-2">
                    <i data-lucide="info" class="w-4 h-4 text-sky-600 shrink-0"></i>
                    <span>{{ session('info') }}</span>
                </div>
                <button onclick="this.parentElement.remove()" class="text-sky-600 hover:text-sky-800">&times;</button>
            </div>
            @endif

            @if ($errors->any())
            <div class="p-4 mb-3 rounded-2xl bg-rose-50 dark:bg-rose-950/50 border border-rose-200 dark:border-rose-800 text-rose-900 dark:text-rose-200 text-xs space-y-1 shadow-xs">
                <div class="font-bold flex items-center gap-2 text-rose-700">
                    <i data-lucide="alert-triangle" class="w-4 h-4"></i>
                    <span>Terdapat beberapa kesalahan pengisian:</span>
                </div>
                <ul class="list-disc list-inside space-y-0.5 pl-5">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
            @endif
        </div>

        <!-- Main Slot -->
        <main class="flex-1 p-4 sm:p-6 lg:p-8">
            @yield('content')
        </main>

        <!-- Footer -->
        <footer class="p-4 text-center text-[11px] text-slate-500 border-t border-slate-200 dark:border-slate-800 bg-white/60 dark:bg-slate-900/60 no-print">
            &copy; {{ date('Y') }} PPDB SMK Islam Bina Khalifah Bangsa Kota Metro. All rights reserved.
        </footer>

    </div>

    <!-- Script Drawer & Dark Mode -->
    <script>
        lucide.createIcons();

        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar');
            const backdrop = document.getElementById('sidebar-backdrop');
            if (sidebar.classList.contains('-translate-x-full')) {
                sidebar.classList.remove('-translate-x-full');
                backdrop.classList.remove('hidden');
            } else {
                sidebar.classList.add('-translate-x-full');
                backdrop.classList.add('hidden');
            }
        }

        // Dark Mode Logic
        const themeToggleDarkIcon = document.getElementById('theme-toggle-dark-icon');
        const themeToggleLightIcon = document.getElementById('theme-toggle-light-icon');
        const themeToggleBtn = document.getElementById('theme-toggle');

        function updateIcons() {
            if (document.documentElement.classList.contains('dark')) {
                themeToggleDarkIcon.classList.add('hidden');
                themeToggleLightIcon.classList.remove('hidden');
            } else {
                themeToggleLightIcon.classList.add('hidden');
                themeToggleDarkIcon.classList.remove('hidden');
            }
        }

        updateIcons();

        if (themeToggleBtn) {
            themeToggleBtn.addEventListener('click', function() {
                if (document.documentElement.classList.contains('dark')) {
                    document.documentElement.classList.remove('dark');
                    localStorage.setItem('theme', 'light');
                } else {
                    document.documentElement.classList.add('dark');
                    localStorage.setItem('theme', 'dark');
                }
                updateIcons();
            });
        }
    </script>
    @stack('scripts')
</body>
</html>
