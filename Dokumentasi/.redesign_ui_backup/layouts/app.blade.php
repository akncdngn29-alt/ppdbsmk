<!DOCTYPE html>
<html lang="id" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'PPDB Online SMK Islam Bina Khalifah Bangsa') - Kota Metro</title>

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
        input[type="password"]::-ms-reveal,
        input[type="password"]::-ms-clear {
            display: none !important;
        }
    </style>
    @stack('styles')
</head>
<body class="bg-white text-slate-800 dark:bg-slate-950 dark:text-slate-100 min-h-screen flex flex-col font-sans antialiased transition-colors duration-200">

    <!-- Top Announcement Ticker (Sesuai Referensi Gambar 1-8) -->
    @php
        $topBanner = \App\Models\Pengumuman::getActiveBanner();
    @endphp
    <div class="bg-white text-slate-800 text-xs py-2 px-4 border-b border-slate-200 shadow-xs no-print">
        <div class="max-w-7xl mx-auto flex items-center justify-between gap-3">
            <div class="flex items-center gap-2.5 truncate">
                <span class="inline-flex items-center gap-1.5 bg-[#f43f5e] text-white px-3 py-0.5 rounded-full text-[11px] font-bold uppercase tracking-wider shrink-0 shadow-xs">
                    <i data-lucide="megaphone" class="w-3.5 h-3.5"></i> PENGUMUMAN
                </span>
                <span class="text-slate-300 font-light">|</span>
                <span class="truncate text-xs font-medium text-slate-700">
                    @if($topBanner)
                        <strong>{{ $topBanner->judul }}:</strong> {{ Str::limit($topBanner->isi_pengumuman, 90) }}
                    @else
                        Penerimaan Peserta Didik Baru (PPDB) T.A 2026/2027 Resmi Dibuka! Selamat datang di Sistem Informasi PPDB Online SMK Islam Bina Khalifah Bangsa Kota Metro...
                    @endif
                </span>
            </div>
            <a href="{{ route('landing') }}#alur" class="text-xs font-semibold text-slate-800 hover:text-[#f43f5e] whitespace-nowrap flex items-center gap-1 shrink-0 transition-colors">
                Lihat Detail <i data-lucide="arrow-right" class="w-3.5 h-3.5"></i>
            </a>
        </div>
    </div>

    <!-- Main Navbar (Navy Dominant Sesuai Referensi Gambar 1-8) -->
    <header class="sticky top-0 z-40 bg-[#0B182F] text-white border-b border-[#13233f] shadow-md no-print transition-colors">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-18 md:h-20">
                <!-- Brand / Logo -->
                <a href="{{ route('landing') }}" class="flex items-center gap-3 group">
                    <img src="{{ asset('images/logo.png') }}" alt="Logo SMK Islam Bina Khalifah Bangsa" class="w-11 h-11 md:w-12 md:h-12 object-contain shrink-0 group-hover:scale-105 transition-transform drop-shadow">
                    <div>
                        <span class="block text-xs md:text-sm font-extrabold text-white tracking-wider leading-tight">SMK ISLAM</span>
                        <span class="block text-xs md:text-sm font-extrabold text-white tracking-wider leading-tight">BINA KHALIFAH BANGSA</span>
                        <span class="block text-[10px] md:text-[11px] font-medium text-slate-300">Kota Metro, Lampung</span>
                    </div>
                </a>

                <!-- Desktop Nav (Sesuai Referensi) -->
                <nav class="hidden lg:flex items-center gap-1 xl:gap-2 text-xs font-semibold">
                    <div class="h-6 w-px bg-slate-700 mx-2"></div>

                    <a href="{{ route('landing') }}" class="px-3.5 py-1.5 rounded-full {{ request()->routeIs('landing') ? 'bg-[#f43f5e] text-white shadow-sm' : 'text-slate-200 hover:text-white hover:bg-white/10' }} flex items-center gap-1.5 transition-colors">
                        <i data-lucide="home" class="w-4 h-4"></i>
                        <span>Beranda</span>
                    </a>

                    <a href="{{ route('landing') }}#profil" class="px-3 py-1.5 rounded-full text-slate-200 hover:text-white hover:bg-white/10 flex items-center gap-1 transition-colors">
                        <i data-lucide="landmark" class="w-4 h-4"></i>
                        <span>Profil Sekolah</span>
                        <i data-lucide="chevron-down" class="w-3 h-3 text-slate-400"></i>
                    </a>

                    <a href="{{ route('landing') }}#jurusan" class="px-3 py-1.5 rounded-full text-slate-200 hover:text-white hover:bg-white/10 flex items-center gap-1 transition-colors">
                        <i data-lucide="briefcase" class="w-4 h-4"></i>
                        <span>Program Keahlian</span>
                        <i data-lucide="chevron-down" class="w-3 h-3 text-slate-400"></i>
                    </a>

                    <a href="{{ route('landing') }}#ekskul" class="px-3 py-1.5 rounded-full text-slate-200 hover:text-white hover:bg-white/10 flex items-center gap-1 transition-colors">
                        <i data-lucide="users" class="w-4 h-4"></i>
                        <span>Ekstrakurikuler</span>
                        <i data-lucide="chevron-down" class="w-3 h-3 text-slate-400"></i>
                    </a>

                    <a href="{{ route('landing') }}#alur" class="px-3 py-1.5 rounded-full text-slate-200 hover:text-white hover:bg-white/10 flex items-center gap-1 transition-colors">
                        <i data-lucide="info" class="w-4 h-4"></i>
                        <span>Informasi PPDB</span>
                        <i data-lucide="chevron-down" class="w-3 h-3 text-slate-400"></i>
                    </a>

                    <a href="{{ route('landing') }}#cek-kelulusan" class="px-3 py-1.5 rounded-full text-slate-200 hover:text-white hover:bg-white/10 flex items-center gap-1.5 transition-colors">
                        <i data-lucide="search" class="w-4 h-4 text-pink-400"></i>
                        <span>Cek Kelulusan</span>
                    </a>
                </nav>

                <!-- Actions: Masuk / Daftar Sekarang / User Profile -->
                <div class="flex items-center gap-2.5">
                    <div class="hidden lg:block h-6 w-px bg-slate-700 mx-1"></div>

                    @auth
                    <!-- User Dropdown (Logged in Siswa) -->
                    <div class="relative group">
                        <a href="{{ route('siswa.dashboard') }}" class="flex items-center gap-2 py-1.5 px-3 rounded-full bg-white/10 hover:bg-white/20 border border-white/15 text-white font-semibold text-xs md:text-sm transition-colors">
                            <i data-lucide="user" class="w-4 h-4 text-pink-400"></i>
                            <span class="max-w-[120px] truncate">{{ Auth::user()->nama }}</span>
                            <i data-lucide="chevron-down" class="w-3.5 h-3.5 text-slate-400"></i>
                        </a>
                        <div class="absolute right-0 mt-2 w-52 bg-[#0B182F] text-slate-200 rounded-2xl shadow-2xl border border-slate-700 py-2 hidden group-hover:block transition-all z-50">
                            <div class="px-4 py-2 border-b border-slate-800">
                                <p class="text-xs font-bold text-white truncate">{{ Auth::user()->nama }}</p>
                                <p class="text-[10px] text-pink-400 font-mono">NISN: {{ Auth::user()->nisn }}</p>
                            </div>
                            <a href="{{ route('siswa.dashboard') }}" class="flex items-center gap-2 px-4 py-2 text-xs text-slate-200 hover:bg-white/10 hover:text-white">
                                <i data-lucide="layout-dashboard" class="w-4 h-4 text-pink-400"></i> Dashboard Siswa
                            </a>
                            <a href="{{ route('siswa.formulir') }}" class="flex items-center gap-2 px-4 py-2 text-xs text-slate-200 hover:bg-white/10 hover:text-white">
                                <i data-lucide="file-text" class="w-4 h-4 text-pink-400"></i> Formulir Biodata
                            </a>
                            <a href="{{ route('siswa.pembayaran') }}" class="flex items-center gap-2 px-4 py-2 text-xs text-slate-200 hover:bg-white/10 hover:text-white">
                                <i data-lucide="credit-card" class="w-4 h-4 text-pink-400"></i> Status Pembayaran
                            </a>
                            <hr class="my-1 border-slate-800">
                            <form action="{{ route('logout') }}" method="POST">
                                @csrf
                                <button type="submit" class="w-full flex items-center gap-2 px-4 py-2 text-xs text-rose-400 hover:bg-rose-500/10 text-left">
                                    <i data-lucide="log-out" class="w-4 h-4"></i> Keluar
                                </button>
                            </form>
                        </div>
                    </div>
                    @else
                    <!-- Guest Buttons Sesuai Referensi Gambar 1 -->
                    <div class="hidden sm:flex items-center gap-2">
                        <a href="{{ route('login') }}" class="px-3.5 py-2 text-xs md:text-sm font-semibold text-slate-200 hover:text-white flex items-center gap-1.5 transition-colors">
                            <i data-lucide="user" class="w-4 h-4"></i>
                            <span>Masuk</span>
                        </a>
                        <a href="{{ route('register') }}" class="px-4.5 py-2 rounded-full bg-[#f43f5e] hover:bg-[#e11d48] text-white font-bold text-xs md:text-sm shadow-md hover:shadow-lg transition-all flex items-center gap-1.5">
                            <span>Daftar Sekarang</span>
                            <i data-lucide="arrow-right" class="w-4 h-4"></i>
                        </a>
                    </div>
                    @endauth

                    <!-- Mobile Menu Button -->
                    <button id="mobile-menu-btn" aria-label="Toggle Mobile Menu" class="lg:hidden p-2 rounded-xl text-slate-200 hover:bg-white/10">
                        <i data-lucide="menu" class="w-6 h-6"></i>
                    </button>
                </div>
            </div>
        </div>

        <!-- Mobile Drawer -->
        <div id="mobile-menu" class="hidden lg:hidden border-t border-slate-800 bg-[#0B182F] px-4 pt-3 pb-6 space-y-2">
            @auth
                <div class="p-3 rounded-xl bg-white/5 border border-white/10 flex items-center justify-between mb-3">
                    <div>
                        <div class="text-xs font-bold text-white">{{ Auth::user()->nama }}</div>
                        <div class="text-[10px] text-pink-400 font-mono">NISN: {{ Auth::user()->nisn }}</div>
                    </div>
                    <a href="{{ route('siswa.dashboard') }}" class="px-3 py-1.5 rounded-full bg-[#f43f5e] text-white font-bold text-xs shadow-sm">
                        Panel Siswa &rarr;
                    </a>
                </div>
            @endauth
            <a href="{{ route('landing') }}" class="block py-2 text-sm font-semibold text-white">Beranda</a>
            <a href="{{ route('landing') }}#profil" class="block py-2 text-sm font-medium text-slate-300 hover:text-white">Profil Sekolah</a>
            <a href="{{ route('landing') }}#jurusan" class="block py-2 text-sm font-medium text-slate-300 hover:text-white">Program Keahlian</a>
            <a href="{{ route('landing') }}#ekskul" class="block py-2 text-sm font-medium text-slate-300 hover:text-white">Ekstrakurikuler</a>
            <a href="{{ route('landing') }}#alur" class="block py-2 text-sm font-medium text-slate-300 hover:text-white">Alur 7 Tahap PPDB</a>
            <a href="{{ route('landing') }}#cek-kelulusan" class="block py-2 text-sm font-semibold text-pink-400 flex items-center gap-1.5">
                <i data-lucide="search" class="w-4 h-4"></i> Cek Status Kelulusan
            </a>
            <a href="{{ route('landing') }}#faq" class="block py-2 text-sm font-medium text-slate-300 hover:text-white">FAQ</a>
            <a href="{{ route('landing') }}#lokasi" class="block py-2 text-sm font-medium text-slate-300 hover:text-white">Lokasi & Kontak</a>

            @guest
            <div class="pt-3 border-t border-slate-800 flex flex-col gap-2">
                <a href="{{ route('login') }}" class="w-full py-2.5 text-center text-sm font-semibold rounded-full border border-slate-700 text-white hover:bg-white/10">Masuk Akun</a>
                <a href="{{ route('register') }}" class="w-full py-2.5 text-center text-sm font-bold rounded-full bg-[#f43f5e] text-white shadow-md">Daftar Sekarang &rarr;</a>
            </div>
            @else
            <div class="pt-2 border-t border-slate-800">
                <form action="{{ route('logout') }}" method="POST">
                    @csrf
                    <button type="submit" class="w-full flex items-center gap-2 px-3 py-2 rounded-xl text-sm font-bold text-rose-400 hover:bg-rose-500/10">
                        <i data-lucide="log-out" class="w-4 h-4"></i> Keluar
                    </button>
                </form>
            </div>
            @endguest
        </div>
    </header>

    <!-- Flash Alerts -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 w-full mt-4 no-print">
        @if(session('success'))
        <div class="flex items-center gap-3 p-4 mb-4 text-emerald-900 bg-emerald-50 dark:bg-emerald-950/60 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800 rounded-2xl shadow-xs animate-fade-in">
            <i data-lucide="check-circle-2" class="w-5 h-5 shrink-0 text-emerald-600"></i>
            <div class="text-sm font-medium">{{ session('success') }}</div>
        </div>
        @endif

        @if(session('error'))
        <div class="flex items-center gap-3 p-4 mb-4 text-rose-900 bg-rose-50 dark:bg-rose-950/60 dark:text-rose-300 border border-rose-200 dark:border-rose-800 rounded-2xl shadow-xs animate-fade-in">
            <i data-lucide="alert-circle" class="w-5 h-5 shrink-0 text-rose-600"></i>
            <div class="text-sm font-medium">{{ session('error') }}</div>
        </div>
        @endif

        @if(session('info'))
        <div class="flex items-center gap-3 p-4 mb-4 text-sky-900 bg-sky-50 dark:bg-sky-950/60 dark:text-sky-300 border border-sky-200 dark:border-sky-800 rounded-2xl shadow-xs animate-fade-in">
            <i data-lucide="info" class="w-5 h-5 shrink-0 text-sky-600"></i>
            <div class="text-sm font-medium">{{ session('info') }}</div>
        </div>
        @endif

        @if($errors->any())
        <div class="p-4 mb-4 text-rose-900 bg-rose-50 dark:bg-rose-950/60 dark:text-rose-300 border border-rose-200 dark:border-rose-800 rounded-2xl shadow-xs">
            <div class="flex items-center gap-2 mb-2 font-bold text-sm text-rose-700">
                <i data-lucide="alert-triangle" class="w-5 h-5"></i> Mohon periksa kesalahan input berikut:
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

    <!-- Footer (Sesuai Referensi Gambar 8) -->
    <footer class="bg-[#0B182F] text-slate-300 border-t border-[#13233f] pt-12 pb-6 no-print transition-colors">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-12 gap-8 mb-10">
                <!-- Col 1: Brand & Desc (Image 8) -->
                <div class="md:col-span-5 space-y-4">
                    <div class="flex items-center gap-3">
                        <img src="{{ asset('images/logo.png') }}" alt="Logo SMK Islam Bina Khalifah Bangsa" class="w-12 h-12 object-contain shrink-0 drop-shadow">
                        <div>
                            <span class="block text-sm md:text-base font-extrabold text-white tracking-wide">SMK ISLAM</span>
                            <span class="block text-sm md:text-base font-extrabold text-white tracking-wide">BINA KHALIFAH BANGSA</span>
                            <span class="block text-xs text-slate-400">Kota Metro, Lampung</span>
                        </div>
                    </div>
                    <p class="text-xs text-slate-400 leading-relaxed max-w-sm">
                        Lembaga pendidikan berkualitas dan berakhlak mulia, siap mencetak generasi unggul di era digital.
                    </p>
                    <!-- Social Media Links (Pink Circles Sesuai Referensi Gambar 8) -->
                    <div class="flex items-center gap-2.5 pt-1">
                        <a href="https://youtube.com" target="_blank" rel="noopener noreferrer" aria-label="YouTube SMK IB" class="w-8 h-8 rounded-full bg-[#f43f5e] hover:bg-[#e11d48] text-white flex items-center justify-center transition-transform hover:scale-110 shadow-sm">
                            <i data-lucide="youtube" class="w-4 h-4"></i>
                        </a>
                        <a href="https://instagram.com" target="_blank" rel="noopener noreferrer" aria-label="Instagram SMK IB" class="w-8 h-8 rounded-full bg-[#f43f5e] hover:bg-[#e11d48] text-white flex items-center justify-center transition-transform hover:scale-110 shadow-sm">
                            <i data-lucide="instagram" class="w-4 h-4"></i>
                        </a>
                        <a href="https://facebook.com" target="_blank" rel="noopener noreferrer" aria-label="Facebook SMK IB" class="w-8 h-8 rounded-full bg-[#f43f5e] hover:bg-[#e11d48] text-white flex items-center justify-center transition-transform hover:scale-110 shadow-sm">
                            <i data-lucide="facebook" class="w-4 h-4"></i>
                        </a>
                        <a href="https://tiktok.com" target="_blank" rel="noopener noreferrer" aria-label="TikTok SMK IB" class="w-8 h-8 rounded-full bg-[#f43f5e] hover:bg-[#e11d48] text-white flex items-center justify-center transition-transform hover:scale-110 shadow-sm">
                            <i data-lucide="video" class="w-4 h-4"></i>
                        </a>
                    </div>
                </div>

                <!-- Col 2: Program Keahlian (Image 8) -->
                <div class="md:col-span-3 space-y-3">
                    <h4 class="text-xs md:text-sm font-extrabold text-white uppercase tracking-wider">PROGRAM KEAHLIAN</h4>
                    <ul class="text-xs space-y-2 text-slate-400 font-medium">
                        <li class="hover:text-white transition-colors">1. Farmasi Klinis & Komunitas</li>
                        <li class="hover:text-white transition-colors">2. Teknik Komputer & Jaringan (TKJ)</li>
                        <li class="hover:text-white transition-colors">3. Teknik Kendaraan Ringan (TKR)</li>
                        <li class="hover:text-white transition-colors">4. Teknik & Bisnis Sepeda Motor (TBSM)</li>
                    </ul>
                </div>

                <!-- Col 3: Kontak & Portal (Image 8) -->
                <div class="md:col-span-4 space-y-3">
                    <h4 class="text-xs md:text-sm font-extrabold text-white uppercase tracking-wider">KONTAK & PORTAL</h4>
                    <ul class="text-xs space-y-2.5 text-slate-400">
                        <li class="flex items-start gap-2.5">
                            <i data-lucide="map-pin" class="w-4 h-4 text-[#f43f5e] shrink-0 mt-0.5"></i>
                            <span>Jl. Pattimura No. 24, Metro Barat, Kota Metro, Lampung 34125</span>
                        </li>
                        <li class="flex items-center gap-2.5">
                            <i data-lucide="phone" class="w-4 h-4 text-[#f43f5e] shrink-0"></i>
                            <span>0821-7890-1234</span>
                        </li>
                        <li class="flex items-center gap-2.5">
                            <i data-lucide="mail" class="w-4 h-4 text-[#f43f5e] shrink-0"></i>
                            <span>info@smkib.com</span>
                        </li>
                        <li class="flex items-center gap-2.5">
                            <i data-lucide="clock" class="w-4 h-4 text-[#f43f5e] shrink-0"></i>
                            <span>Portal: Senin - Sabtu, 08.00 - 15.00 WIB</span>
                        </li>
                        <li class="pt-1.5">
                            <a href="{{ route('admin.login') }}" class="inline-flex items-center gap-1.5 text-slate-400 hover:text-[#f43f5e] text-xs font-semibold transition-colors">
                                <i data-lucide="shield-check" class="w-3.5 h-3.5"></i> Portal Staf TU & Kepala Sekolah &rarr;
                            </a>
                        </li>
                    </ul>
                </div>
            </div>

            <!-- Bottom Line (Image 8) -->
            <div class="border-t border-slate-800/80 pt-6 flex flex-col sm:flex-row items-center justify-between text-xs text-slate-400 gap-3">
                <p>&copy; 2026 SMK Islam Bina Khalifah Bangsa Kota Metro. Hak Cipta Dilindungi.</p>
                <div class="flex items-center gap-4 text-slate-400">
                    <a href="#profil" class="hover:text-white transition-colors">Disclaimer</a>
                    <span>|</span>
                    <a href="#profil" class="hover:text-white transition-colors">Kebijakan Privasi</a>
                    <span>|</span>
                    <a href="#alur" class="hover:text-white transition-colors">Sitemap</a>
                </div>
            </div>
        </div>
    </footer>

    <!-- Scripts -->
    <script>
        lucide.createIcons();

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
