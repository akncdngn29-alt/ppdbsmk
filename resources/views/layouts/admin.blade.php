<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Admin Panel') - PPDB SMK IB Khalifah Bangsa</title>

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
                        primary: {
                            50: '#fff1f2',
                            100: '#ffe4e6',
                            500: '#fa2f79',
                            600: '#e11d48',
                            700: '#be123c',
                        }
                    }
                }
            }
        }
    </script>
    <!-- Lucide Icons & Chart.js -->
    <script src="https://unpkg.com/lucide@latest"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

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
        /* Sembunyikan ikon mata bawaan peramban (Microsoft Edge / IE) */
        input[type="password"]::-ms-reveal,
        input[type="password"]::-ms-clear {
            display: none !important;
        }
    </style>
    @stack('styles')
</head>
<body class="bg-slate-100 text-slate-800 dark:bg-slate-950 dark:text-slate-100 min-h-screen flex font-sans antialiased transition-colors">

    @php
        $admin = auth('admin')->user();
        $isTU = $admin && $admin->role === 'tu';
        $isKepsek = $admin && $admin->role === 'kepala_sekolah';
    @endphp

    <!-- Mobile Sidebar Backdrop -->
    <div id="admin-sidebar-backdrop" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-40 hidden md:hidden transition-opacity" onclick="toggleAdminSidebar()"></div>

    <!-- Sidebar Desktop & Mobile Offcanvas -->
    <aside id="sidebar" class="fixed inset-y-0 left-0 z-50 w-64 bg-slate-900 text-slate-200 border-r border-slate-800 flex flex-col shrink-0 no-print transition-transform duration-300 -translate-x-full md:translate-x-0 md:static">
        <!-- Brand -->
        <div class="h-16 md:h-20 flex items-center gap-3 px-5 border-b border-slate-800 bg-slate-950/40">
            <img src="{{ asset('images/logo-sekolah.png') }}" alt="Logo SMK IB Khalifah Bangsa" class="w-9 h-9 md:w-10 md:h-10 object-contain shrink-0">
            <div class="truncate">
                <span class="block text-sm font-bold text-white tracking-tight leading-tight">SMK IB KHALIFAH</span>
                <span class="block text-[10px] font-semibold text-[#fa2f79] uppercase tracking-wider">
                    {{ $isKepsek ? 'Panel Kepala Sekolah' : 'Panel Petugas TU' }}
                </span>
            </div>
            <button onclick="toggleAdminSidebar()" class="md:hidden ml-auto p-1.5 rounded-lg text-slate-400 hover:text-white hover:bg-slate-800">
                <i data-lucide="x" class="w-5 h-5"></i>
            </button>
        </div>

        <!-- Navigation Menu -->
        <nav class="flex-1 overflow-y-auto p-4 space-y-1 text-xs font-semibold">
            @if($isTU)
                <!-- TU Menus -->
                <div class="text-[10px] uppercase text-slate-400 font-bold px-3 pt-2 pb-1">Utama</div>
                <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-slate-300 hover:text-white hover:bg-slate-800/80 transition-colors {{ request()->routeIs('admin.dashboard') ? 'bg-[#fa2f79] text-white font-bold shadow-md shadow-pink-500/25' : '' }}">
                    <i data-lucide="layout-dashboard" class="w-4 h-4"></i> Dashboard TU
                </a>
                <a href="{{ route('admin.pendaftar') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-slate-300 hover:text-white hover:bg-slate-800/80 transition-colors {{ request()->routeIs('admin.pendaftar*') ? 'bg-[#fa2f79] text-white font-bold shadow-md shadow-pink-500/25' : '' }}">
                    <i data-lucide="users" class="w-4 h-4"></i> Data Pendaftar
                </a>
                <a href="{{ route('admin.seleksi') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-slate-300 hover:text-white hover:bg-slate-800/80 transition-colors {{ request()->routeIs('admin.seleksi*') ? 'bg-[#fa2f79] text-white font-bold shadow-md shadow-pink-500/25' : '' }}">
                    <i data-lucide="clipboard-check" class="w-4 h-4"></i> Seleksi & Penilaian
                </a>

                <div class="text-[10px] uppercase text-slate-400 font-bold px-3 pt-4 pb-1">Master Data</div>
                <a href="{{ route('admin.master.jurusan') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-slate-300 hover:text-white hover:bg-slate-800/80 transition-colors {{ request()->routeIs('admin.master.jurusan*') ? 'bg-[#fa2f79] text-white font-bold shadow-md shadow-pink-500/25' : '' }}">
                    <i data-lucide="book-open" class="w-4 h-4"></i> Master Jurusan & Kuota
                </a>
                <a href="{{ route('admin.master.gelombang') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-slate-300 hover:text-white hover:bg-slate-800/80 transition-colors {{ request()->routeIs('admin.master.gelombang*') ? 'bg-[#fa2f79] text-white font-bold shadow-md shadow-pink-500/25' : '' }}">
                    <i data-lucide="calendar" class="w-4 h-4"></i> Gelombang Pendaftaran
                </a>

                <div class="text-[10px] uppercase text-slate-400 font-bold px-3 pt-4 pb-1">Laporan & Ekspor</div>
                <a href="{{ route('admin.ekspor.excel') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-slate-300 hover:text-white hover:bg-slate-800/80 transition-colors">
                    <i data-lucide="file-spreadsheet" class="w-4 h-4 text-pink-400"></i> Ekspor Excel (.csv)
                </a>
                <a href="{{ route('admin.ekspor.dapodik') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-slate-300 hover:text-white hover:bg-slate-800/80 transition-colors">
                    <i data-lucide="download" class="w-4 h-4 text-sky-400"></i> Ekspor Format Dapodik
                </a>
                <a href="{{ route('admin.laporan.rekap_pdf') }}" target="_blank" class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-slate-300 hover:text-white hover:bg-slate-800/80 transition-colors">
                    <i data-lucide="printer" class="w-4 h-4 text-[#fa2f79]"></i> Cetak Rekap PDF Resmi
                </a>

                <div class="text-[10px] uppercase text-slate-400 font-bold px-3 pt-4 pb-1">Pengaturan & Informasi</div>
                <a href="{{ route('admin.pengaturan') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-slate-300 hover:text-white hover:bg-slate-800/80 transition-colors {{ request()->routeIs('admin.pengaturan*') || request()->routeIs('admin.pengumuman*') ? 'bg-[#fa2f79] text-white font-bold shadow-md shadow-pink-500/25' : '' }}">
                    <i data-lucide="settings" class="w-4 h-4"></i> Pengaturan Sekolah
                </a>
            @else
                <!-- Kepala Sekolah Menus -->
                <div class="text-[10px] uppercase text-slate-400 font-bold px-3 pt-2 pb-1">Eksekutif</div>
                <a href="{{ route('kepsek.dashboard') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-slate-300 hover:text-white hover:bg-slate-800/80 transition-colors {{ request()->routeIs('kepsek.dashboard') ? 'bg-[#fa2f79] text-white font-bold shadow-md shadow-pink-500/25' : '' }}">
                    <i data-lucide="bar-chart-3" class="w-4 h-4"></i> Statistik Eksekutif
                </a>
                <a href="{{ route('kepsek.kelulusan') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-slate-300 hover:text-white hover:bg-slate-800/80 transition-colors {{ request()->routeIs('kepsek.kelulusan*') ? 'bg-[#fa2f79] text-white font-bold shadow-md shadow-pink-500/25' : '' }}">
                    <i data-lucide="award" class="w-4 h-4"></i> Kelulusan & Pengesahan SKL
                </a>
                <a href="{{ route('kepsek.keuangan') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-slate-300 hover:text-white hover:bg-slate-800/80 transition-colors {{ request()->routeIs('kepsek.keuangan*') ? 'bg-[#fa2f79] text-white font-bold shadow-md shadow-pink-500/25' : '' }}">
                    <i data-lucide="wallet" class="w-4 h-4 text-pink-400"></i> Rekapitulasi Keuangan
                </a>
                <a href="{{ route('kepsek.laporan') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-slate-300 hover:text-white hover:bg-slate-800/80 transition-colors {{ request()->routeIs('kepsek.laporan*') ? 'bg-[#fa2f79] text-white font-bold shadow-md shadow-pink-500/25' : '' }}">
                    <i data-lucide="file-text" class="w-4 h-4"></i> Laporan Resmi Sekolah
                </a>
                <a href="{{ route('kepsek.audit_log') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-slate-300 hover:text-white hover:bg-slate-800/80 transition-colors {{ request()->routeIs('kepsek.audit_log*') ? 'bg-[#fa2f79] text-white font-bold shadow-md shadow-pink-500/25' : '' }}">
                    <i data-lucide="activity" class="w-4 h-4"></i> Audit Trail (Log Aktivitas)
                </a>

                <div class="text-[10px] uppercase text-slate-400 font-bold px-3 pt-4 pb-1">Kebijakan & Keamanan</div>
                <a href="{{ route('admin.pengaturan', ['tab' => 'gateway']) }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-slate-300 hover:text-white hover:bg-slate-800/80 transition-colors {{ request()->routeIs('admin.pengaturan*') ? 'bg-[#fa2f79] text-white font-bold shadow-md shadow-pink-500/25' : '' }}">
                    <i data-lucide="sliders" class="w-4 h-4 text-pink-400"></i> Pengaturan Kebijakan PPDB
                </a>
                <a href="{{ route('admin.staf.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-slate-300 hover:text-white hover:bg-slate-800/80 transition-colors {{ request()->routeIs('admin.staf*') ? 'bg-[#fa2f79] text-white font-bold shadow-md shadow-pink-500/25' : '' }}">
                    <i data-lucide="user-cog" class="w-4 h-4 text-purple-400"></i> Manajemen Akun Staf
                </a>
                <a href="{{ route('admin.backup.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-slate-300 hover:text-white hover:bg-slate-800/80 transition-colors {{ request()->routeIs('admin.backup*') ? 'bg-[#fa2f79] text-white font-bold shadow-md shadow-pink-500/25' : '' }}">
                    <i data-lucide="database" class="w-4 h-4 text-pink-400"></i> Backup & Restore Basis Data
                </a>
            @endif
        </nav>

        <!-- User Profile & Logout -->
        <div class="p-4 border-t border-slate-800 bg-slate-950/60 flex items-center justify-between">
            <div class="truncate">
                <span class="block text-xs font-bold text-white truncate">{{ $admin->nama_admin }}</span>
                <span class="block text-[10px] text-slate-400 truncate">{{ $admin->email }}</span>
            </div>
            <form action="{{ route('admin.logout') }}" method="POST">
                @csrf
                <button type="submit" title="Keluar" class="p-2 rounded-xl text-rose-400 hover:text-white hover:bg-rose-900/40 transition-colors">
                    <i data-lucide="log-out" class="w-4 h-4"></i>
                </button>
            </form>
        </div>
    </aside>

    <!-- Main Content Wrapper -->
    <div class="flex-1 flex flex-col min-w-0 overflow-x-hidden">
        <!-- Top Navbar -->
        <header class="h-16 md:h-20 bg-white dark:bg-slate-900 border-b border-slate-200 dark:border-slate-800 flex items-center justify-between px-4 sm:px-6 lg:px-8 no-print transition-colors">
            <div class="flex items-center gap-3">
                <button id="sidebar-toggle-btn" onclick="toggleAdminSidebar()" class="md:hidden p-2 rounded-xl text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800">
                    <i data-lucide="menu" class="w-5 h-5"></i>
                </button>
                <h1 class="text-base md:text-lg font-bold text-slate-800 dark:text-white truncate">
                    @yield('header_title', 'Dashboard')
                </h1>
            </div>

            <div class="flex items-center gap-3">
                <!-- Dark Mode Toggle -->
                <button id="theme-toggle" type="button" aria-label="Toggle Dark Mode" class="p-2 rounded-xl text-slate-500 hover:text-slate-700 dark:text-slate-400 dark:hover:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors">
                    <i id="theme-toggle-dark-icon" data-lucide="moon" class="hidden w-5 h-5"></i>
                    <i id="theme-toggle-light-icon" data-lucide="sun" class="hidden w-5 h-5"></i>
                </button>

                <!-- Visit Public Website -->
                <a href="{{ route('landing') }}" target="_blank" class="hidden sm:inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl border border-slate-200 dark:border-slate-700 text-xs font-semibold text-slate-600 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800 transition-colors">
                    <i data-lucide="external-link" class="w-3.5 h-3.5"></i> Lihat Website
                </a>
            </div>
        </header>

        <!-- Flash Messages -->
        <div class="px-4 sm:px-6 lg:px-8 pt-4 no-print">
            @if(session('success'))
            <div class="flex items-center gap-3 p-4 mb-4 text-pink-900 bg-pink-50 dark:bg-pink-950/60 dark:text-pink-200 border border-pink-200 dark:border-pink-800 rounded-2xl shadow-sm">
                <i data-lucide="check-circle-2" class="w-5 h-5 shrink-0 text-[#fa2f79] dark:text-pink-400"></i>
                <div class="text-sm font-medium">{{ session('success') }}</div>
            </div>
            @endif

            @if(session('error'))
            <div class="flex items-center gap-3 p-4 mb-4 text-rose-800 bg-rose-50 dark:bg-rose-950/60 dark:text-rose-300 border border-rose-200 dark:border-rose-800 rounded-2xl shadow-sm">
                <i data-lucide="alert-circle" class="w-5 h-5 shrink-0 text-rose-600 dark:text-rose-400"></i>
                <div class="text-sm font-medium">{{ session('error') }}</div>
            </div>
            @endif
        </div>

        <!-- Content Area -->
        <main class="flex-1 p-4 sm:p-6 lg:p-8">
            @yield('content')
        </main>
    </div>

    <!-- Scripts -->
    <script>
        lucide.createIcons();

        // Admin Mobile Sidebar Toggle
        function toggleAdminSidebar() {
            const sidebar = document.getElementById('sidebar');
            const backdrop = document.getElementById('admin-sidebar-backdrop');
            if (sidebar.classList.contains('-translate-x-full')) {
                sidebar.classList.remove('-translate-x-full');
                backdrop.classList.remove('hidden');
            } else {
                sidebar.classList.add('-translate-x-full');
                backdrop.classList.add('hidden');
            }
        }

        // Dark Mode Logic
        const themeToggleBtn = document.getElementById('theme-toggle');
        const darkIcon = document.getElementById('theme-toggle-dark-icon');
        const lightIcon = document.getElementById('theme-toggle-light-icon');

        function updateIcons() {
            if (document.documentElement.classList.contains('dark')) {
                lightIcon.classList.remove('hidden');
                darkIcon.classList.add('hidden');
            } else {
                darkIcon.classList.remove('hidden');
                lightIcon.classList.add('hidden');
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

