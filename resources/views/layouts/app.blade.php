<!DOCTYPE html>
<html lang="id" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'PPDB SMK Islam Bina Khalifah Bangsa') - Kota Metro</title>

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
                            50: '#ecfdf5',
                            100: '#d1fae5',
                            500: '#10b981',
                            600: '#059669',
                            700: '#047857',
                            800: '#065f46',
                            900: '#064e3b',
                        },
                        secondary: {
                            500: '#0284c7',
                            600: '#0369a1',
                        }
                    }
                }
            }
        }
    </script>
    <!-- Lucide Icons -->
    <script src="https://unpkg.com/lucide@latest"></script>

    <script>
        // Init Dark Mode from localStorage or system preference
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
        /* Sembunyikan ikon mata bawaan peramban (Microsoft Edge / IE) agar tidak muncul 2 mata */
        input[type="password"]::-ms-reveal,
        input[type="password"]::-ms-clear {
            display: none !important;
        }
    </style>
    @stack('styles')
</head>
<body class="bg-slate-50 text-slate-800 dark:bg-slate-900 dark:text-slate-100 min-h-screen flex flex-col font-sans antialiased transition-colors duration-200">

    <!-- Top Announcement Banner (If any) -->
    @php
        $topBanner = \App\Models\Pengumuman::getActiveBanner();
    @endphp
    @if($topBanner)
    <div class="bg-gradient-to-r from-emerald-600 via-teal-600 to-emerald-700 text-white text-xs md:text-sm py-2 px-4 shadow-sm no-print">
        <div class="max-w-7xl mx-auto flex items-center justify-between gap-2">
            <div class="flex items-center gap-2 truncate">
                <span class="bg-white/20 px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider">Pengumuman</span>
                <span class="truncate font-medium">{{ $topBanner->judul }}: {{ Str::limit($topBanner->isi_pengumuman, 80) }}</span>
            </div>
            <a href="{{ route('landing') }}#pengumuman" class="underline text-xs whitespace-nowrap hover:text-emerald-200">Lihat Detail &rarr;</a>
        </div>
    </div>
    @endif

    <!-- Navbar -->
    <header class="sticky top-0 z-40 bg-white/95 dark:bg-slate-900/95 backdrop-blur-md border-b border-slate-200 dark:border-slate-800 no-print transition-colors">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-16 md:h-20">
                <!-- Brand / Logo -->
                <a href="{{ route('landing') }}" class="flex items-center gap-3 group">
                    <div class="w-10 h-10 md:w-12 md:h-12 rounded-xl bg-gradient-to-tr from-emerald-600 to-teal-500 flex items-center justify-center text-white shadow-md shadow-emerald-500/20 group-hover:scale-105 transition-transform">
                        <i data-lucide="graduation-cap" class="w-6 h-6"></i>
                    </div>
                    <div>
                        <span class="block text-sm md:text-base font-extrabold text-slate-900 dark:text-white tracking-tight leading-tight">SMK IB KHALIFAH BANGSA</span>
                        <span class="block text-[11px] font-semibold text-emerald-600 dark:text-emerald-400">PPDB Online T.A 2026/2027</span>
                    </div>
                </a>

                <!-- Desktop Nav (Navigasi Publik Bersih) -->
                <nav class="hidden md:flex items-center gap-1 lg:gap-2 text-xs lg:text-sm font-semibold">
                    <a href="{{ route('landing') }}" class="px-3 py-2 text-slate-600 dark:text-slate-300 hover:text-emerald-600 dark:hover:text-emerald-400 transition-colors">Beranda</a>
                    <a href="{{ route('landing') }}#jurusan" class="px-3 py-2 text-slate-600 dark:text-slate-300 hover:text-emerald-600 dark:hover:text-emerald-400 transition-colors">Program Keahlian</a>
                    <a href="{{ route('landing') }}#ekskul" class="px-3 py-2 text-slate-600 dark:text-slate-300 hover:text-emerald-600 dark:hover:text-emerald-400 transition-colors">Ekstrakurikuler</a>
                    <a href="{{ route('landing') }}#alur" class="px-3 py-2 text-slate-600 dark:text-slate-300 hover:text-emerald-600 dark:hover:text-emerald-400 transition-colors">Alur PPDB</a>
                    <a href="{{ route('landing') }}#cek-kelulusan" class="px-3 py-2 text-slate-600 dark:text-slate-300 hover:text-emerald-600 dark:hover:text-emerald-400 transition-colors flex items-center gap-1.5">
                        <i data-lucide="search" class="w-4 h-4 text-emerald-500"></i>
                        <span>Cek Kelulusan</span>
                    </a>
                </nav>

                <!-- Actions & Dark Mode Toggle -->
                <div class="flex items-center gap-3">
                    <!-- Dark Mode Toggle Button -->
                    <button id="theme-toggle" type="button" aria-label="Toggle Dark Mode" class="p-2 rounded-xl text-slate-500 hover:text-slate-700 dark:text-slate-400 dark:hover:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors">
                        <i id="theme-toggle-dark-icon" data-lucide="moon" class="hidden w-5 h-5"></i>
                        <i id="theme-toggle-light-icon" data-lucide="sun" class="hidden w-5 h-5"></i>
                    </button>

                    @auth
                    <!-- User Dropdown (Logged in Siswa) -->
                    <div class="relative group">
                        <a href="{{ route('siswa.dashboard') }}" class="flex items-center gap-2 py-1.5 px-3 rounded-xl bg-emerald-50 dark:bg-emerald-950/50 border border-emerald-200 dark:border-emerald-800 text-emerald-700 dark:text-emerald-300 font-semibold text-xs md:text-sm hover:bg-emerald-100 dark:hover:bg-emerald-900/60 transition-colors">
                            <i data-lucide="user" class="w-4 h-4"></i>
                            <span class="max-w-[120px] truncate">{{ Auth::user()->nama }}</span>
                            <i data-lucide="chevron-down" class="w-3.5 h-3.5"></i>
                        </a>
                        <div class="absolute right-0 mt-2 w-48 bg-white dark:bg-slate-800 rounded-2xl shadow-xl border border-slate-100 dark:border-slate-700 py-2 hidden group-hover:block transition-all">
                            <a href="{{ route('siswa.dashboard') }}" class="flex items-center gap-2 px-4 py-2 text-xs md:text-sm text-slate-700 dark:text-slate-200 hover:bg-slate-50 dark:hover:bg-slate-700">
                                <i data-lucide="layout-dashboard" class="w-4 h-4"></i> Dashboard Siswa
                            </a>
                            <a href="{{ route('siswa.formulir') }}" class="flex items-center gap-2 px-4 py-2 text-xs md:text-sm text-slate-700 dark:text-slate-200 hover:bg-slate-50 dark:hover:bg-slate-700">
                                <i data-lucide="file-text" class="w-4 h-4"></i> Formulir Pendaftaran
                            </a>
                            <hr class="my-1 border-slate-100 dark:border-slate-700">
                            <form action="{{ route('logout') }}" method="POST">
                                @csrf
                                <button type="submit" class="w-full flex items-center gap-2 px-4 py-2 text-xs md:text-sm text-rose-600 hover:bg-rose-50 dark:hover:bg-rose-950/40 text-left">
                                    <i data-lucide="log-out" class="w-4 h-4"></i> Keluar
                                </button>
                            </form>
                        </div>
                    </div>
                    @else
                    <!-- Guest Buttons -->
                    <div class="hidden sm:flex items-center gap-2">
                        <a href="{{ route('login') }}" class="px-3.5 py-2 text-xs md:text-sm font-semibold text-slate-700 dark:text-slate-200 hover:text-emerald-600 dark:hover:text-emerald-400 transition-colors">
                            Masuk
                        </a>
                        <a href="{{ route('register') }}" class="px-4 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-semibold text-xs md:text-sm shadow-md shadow-emerald-600/20 hover:shadow-lg transition-all">
                            Daftar Sekarang
                        </a>
                    </div>
                    @endauth

                    <!-- Mobile Menu Button -->
                    <button id="mobile-menu-btn" class="md:hidden p-2 rounded-xl text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800">
                        <i data-lucide="menu" class="w-6 h-6"></i>
                    </button>
                </div>
            </div>
        </div>

        <!-- Mobile Drawer -->
        <div id="mobile-menu" class="hidden md:hidden border-t border-slate-100 dark:border-slate-800 bg-white dark:bg-slate-900 px-4 pt-3 pb-6 space-y-2">
            @auth
                <!-- Info Siswa Mobile -->
                <div class="p-3 rounded-xl bg-emerald-50 dark:bg-emerald-950/50 border border-emerald-200 dark:border-emerald-800 flex items-center justify-between">
                    <div>
                        <div class="text-xs font-bold text-slate-900 dark:text-white">{{ Auth::user()->nama }}</div>
                        <div class="text-[10px] text-slate-500 font-mono">NISN: {{ Auth::user()->nisn }}</div>
                    </div>
                    <a href="{{ route('siswa.dashboard') }}" class="px-3 py-1.5 rounded-lg bg-emerald-600 text-white font-bold text-xs shadow-sm">
                        Panel Siswa &rarr;
                    </a>
                </div>
                <hr class="border-slate-100 dark:border-slate-800">
                <a href="{{ route('landing') }}" class="block py-2 text-sm font-medium text-slate-700 dark:text-slate-200">Beranda</a>
                <a href="{{ route('landing') }}#jurusan" class="block py-2 text-sm font-medium text-slate-700 dark:text-slate-200">Program Keahlian</a>
                <a href="{{ route('landing') }}#ekskul" class="block py-2 text-sm font-medium text-slate-700 dark:text-slate-200">Ekstrakurikuler</a>
                <a href="{{ route('landing') }}#alur" class="block py-2 text-sm font-medium text-slate-700 dark:text-slate-200">Alur PPDB</a>
                <a href="{{ route('landing') }}#cek-kelulusan" class="block py-2 text-sm font-medium text-emerald-600 dark:text-emerald-400 font-semibold">Cek Kelulusan Cepat</a>
                <div class="pt-2 border-t border-slate-100 dark:border-slate-800">
                    <form action="{{ route('logout') }}" method="POST">
                        @csrf
                        <button type="submit" class="w-full flex items-center gap-2 px-3 py-2 rounded-xl text-sm font-bold text-rose-600 hover:bg-rose-50 dark:hover:bg-rose-950/40">
                            <i data-lucide="log-out" class="w-4 h-4"></i> Keluar
                        </button>
                    </form>
                </div>
            @else
                <a href="{{ route('landing') }}" class="block py-2 text-sm font-medium text-slate-700 dark:text-slate-200">Beranda</a>
                <a href="{{ route('landing') }}#jurusan" class="block py-2 text-sm font-medium text-slate-700 dark:text-slate-200">Program Keahlian</a>
                <a href="{{ route('landing') }}#ekskul" class="block py-2 text-sm font-medium text-slate-700 dark:text-slate-200">Ekstrakurikuler</a>
                <a href="{{ route('landing') }}#alur" class="block py-2 text-sm font-medium text-slate-700 dark:text-slate-200">Alur PPDB</a>
                <a href="{{ route('landing') }}#cek-kelulusan" class="block py-2 text-sm font-medium text-emerald-600 dark:text-emerald-400 font-semibold">Cek Kelulusan Cepat</a>
                <div class="pt-3 border-t border-slate-100 dark:border-slate-800 flex flex-col gap-2">
                    <a href="{{ route('login') }}" class="w-full py-2.5 text-center text-sm font-semibold rounded-xl border border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-200">Masuk Akun</a>
                    <a href="{{ route('register') }}" class="w-full py-2.5 text-center text-sm font-semibold rounded-xl bg-emerald-600 text-white">Daftar Akun Baru</a>
                </div>
            @endauth
        </div>
    </header>

    <!-- Flash Alerts -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 w-full mt-4 no-print">
        @if(session('success'))
        <div class="flex items-center gap-3 p-4 mb-4 text-emerald-800 bg-emerald-50 dark:bg-emerald-950/60 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800 rounded-2xl shadow-sm animate-fade-in">
            <i data-lucide="check-circle-2" class="w-5 h-5 shrink-0 text-emerald-600 dark:text-emerald-400"></i>
            <div class="text-sm font-medium">{{ session('success') }}</div>
        </div>
        @endif

        @if(session('error'))
        <div class="flex items-center gap-3 p-4 mb-4 text-rose-800 bg-rose-50 dark:bg-rose-950/60 dark:text-rose-300 border border-rose-200 dark:border-rose-800 rounded-2xl shadow-sm animate-fade-in">
            <i data-lucide="alert-circle" class="w-5 h-5 shrink-0 text-rose-600 dark:text-rose-400"></i>
            <div class="text-sm font-medium">{{ session('error') }}</div>
        </div>
        @endif

        @if(session('info'))
        <div class="flex items-center gap-3 p-4 mb-4 text-sky-800 bg-sky-50 dark:bg-sky-950/60 dark:text-sky-300 border border-sky-200 dark:border-sky-800 rounded-2xl shadow-sm animate-fade-in">
            <i data-lucide="info" class="w-5 h-5 shrink-0 text-sky-600 dark:text-sky-400"></i>
            <div class="text-sm font-medium">{{ session('info') }}</div>
        </div>
        @endif

        @if($errors->any())
        <div class="p-4 mb-4 text-rose-800 bg-rose-50 dark:bg-rose-950/60 dark:text-rose-300 border border-rose-200 dark:border-rose-800 rounded-2xl shadow-sm">
            <div class="flex items-center gap-2 mb-2 font-bold text-sm">
                <i data-lucide="alert-triangle" class="w-5 h-5 text-rose-600"></i> Mohon periksa kesalahan input berikut:
            </div>
            <ul class="list-disc list-inside text-xs space-y-1">
                @foreach($errors->all() as $err)
                <li>{{ $err }}</li>
                @endforeach
            </ul>
        </div>
        @endif
    </div>

    <!-- Main Content -->
    <main class="flex-grow">
        @yield('content')
    </main>

    <!-- Footer -->
    <footer class="bg-slate-900 text-slate-300 border-t border-slate-800 pt-12 pb-8 no-print transition-colors">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-8 mb-8">
                <!-- Col 1 -->
                <div class="space-y-4 md:col-span-2">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-emerald-600 flex items-center justify-center text-white shadow-md">
                            <i data-lucide="graduation-cap" class="w-6 h-6"></i>
                        </div>
                        <div>
                            <span class="block text-base font-extrabold text-white">SMK ISLAM BINA KHALIFAH BANGSA</span>
                            <span class="block text-xs text-emerald-400 font-medium">Kota Metro, Provinsi Lampung</span>
                        </div>
                    </div>
                    <p class="text-xs text-slate-400 leading-relaxed max-w-md">
                        Lembaga pendidikan kejuruan berlandaskan nilai-nilai Islam modern yang mencetak lulusan berakhlak mulia, kompeten, siap kerja, dan berdaya saing global di bidang Farmasi, Jaringan, dan Otomotif.
                    </p>
                    <div class="text-xs text-slate-400 space-y-1">
                        <div><strong class="text-slate-300">NPSN:</strong> 69773555 | <strong class="text-slate-300">Izin Operasional:</strong> 115/KPTS/D.3/03/2013</div>
                        <div><strong class="text-slate-300">Kepala Sekolah:</strong> Asliman Puja Kesuma, S.Pd</div>
                        <div><strong class="text-slate-300">Website:</strong> <a href="https://smkib.com" target="_blank" class="text-emerald-400 hover:underline">https://smkib.com</a></div>
                    </div>
                </div>

                <!-- Col 2: Jurusan -->
                <div>
                    <h4 class="text-sm font-bold text-white uppercase tracking-wider mb-3">Program Keahlian</h4>
                    <ul class="text-xs space-y-2 text-slate-400">
                        <li>1. Farmasi Klinis & Komunitas</li>
                        <li>2. Teknik Komputer & Jaringan (TKJ)</li>
                        <li>3. Teknik Kendaraan Ringan (TKR)</li>
                        <li>4. Teknik & Bisnis Sepeda Motor (TBSM)</li>
                    </ul>
                </div>

                <!-- Col 3: Kontak & Navigasi -->
                <div>
                    <h4 class="text-sm font-bold text-white uppercase tracking-wider mb-3">Kontak & Portal</h4>
                    <ul class="text-xs space-y-2 text-slate-400">
                        <li class="flex items-center gap-2"><i data-lucide="map-pin" class="w-4 h-4 text-emerald-400 shrink-0"></i> Jl. Pattimura No. 24, Metro Barat</li>
                        <li class="flex items-center gap-2"><i data-lucide="phone" class="w-4 h-4 text-emerald-400 shrink-0"></i> 0821-7890-1234</li>
                        <li class="flex items-center gap-2"><i data-lucide="mail" class="w-4 h-4 text-emerald-400 shrink-0"></i> info@smkib.com</li>
                        <li class="pt-2">
                            <a href="{{ route('admin.login') }}" class="inline-flex items-center gap-1.5 text-slate-400 hover:text-emerald-400 text-xs transition-colors">
                                <i data-lucide="lock" class="w-3.5 h-3.5"></i> Portal Staf TU & Kepala Sekolah &rarr;
                            </a>
                        </li>
                    </ul>
                </div>
            </div>

            <div class="border-t border-slate-800 pt-6 flex flex-col sm:flex-row items-center justify-between text-xs text-slate-500 gap-4">
                <p>&copy; {{ date('Y') }} SMK Islam Bina Khalifah Bangsa Kota Metro. Hak Cipta Dilindungi.</p>
                <p>Dikembangkan dengan Laravel 11, Tailwind CSS, & MySQL (Arsitektur 3NF).</p>
            </div>
        </div>
    </footer>

    <!-- Dark Mode Script & Mobile Menu Script -->
    <script>
        // Init Lucide Icons
        lucide.createIcons();

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

        // Mobile Menu Drawer Toggle
        const mobileMenuBtn = document.getElementById('mobile-menu-btn');
        const mobileMenu = document.getElementById('mobile-menu');
        if (mobileMenuBtn && mobileMenu) {
            mobileMenuBtn.addEventListener('click', function() {
                mobileMenu.classList.toggle('hidden');
            });
        }
    </script>
    @stack('scripts')
</body>
</html>

