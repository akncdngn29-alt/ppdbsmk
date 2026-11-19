<!DOCTYPE html>
<html lang="id" class="scroll-smooth overflow-x-hidden">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'PPDB Online SMK Islam Bina Khalifah Bangsa Kota Metro T.A 2026/2027')</title>

    <!-- Google Fonts: Plus Jakarta Sans & Playfair Display -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800;900&family=Playfair+Display:ital,wght@1,600;1,700&display=swap" rel="stylesheet">

    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    screens: {
                        'xs': '480px',
                    },
                    fontFamily: {
                        sans: ['"Plus Jakarta Sans"', 'sans-serif'],
                        serif: ['"Playfair Display"', 'serif'],
                    },
                    colors: {
                        navy: {
                            950: '#060b1d',
                            900: '#0b1329',
                            850: '#0e1834',
                            800: '#142144',
                            700: '#1d2f5e',
                        },
                        brand: {
                            pink: '#fa2f79',
                            pinkDark: '#e11d48',
                            pinkLight: '#ffe4ec',
                            gold: '#f59e0b',
                        },
                        soft: {
                            bg: '#f8fafc',      /* Warna latar lembut, tidak silau/terlalu terang */
                            card: '#ffffff',
                            muted: '#f1f5f9',
                        }
                    }
                }
            }
        }
    </script>
    <!-- Lucide Icons -->
    <script src="https://unpkg.com/lucide@latest"></script>
    <style>
        html {
            overflow-x: hidden;
            max-width: 100%;
        }
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background-color: #060c1d; /* Warna navy dasar menyatu dengan hero & navbar, mencegah strip putih di mobile */
            color: #0f172a;
            overflow-x: hidden;
            max-width: 100%;
            width: 100%;
        }
        @media print {
            .no-print { display: none !important; }
        }
        /* Sembunyikan ikon mata bawaan peramban */
        input[type="password"]::-ms-reveal,
        input[type="password"]::-ms-clear {
            display: none !important;
        }
        .btn-pink {
            background-color: #fa2f79;
            color: #ffffff;
            transition: all 0.2s ease;
        }
        .btn-pink:hover {
            background-color: #e11d48;
            box-shadow: 0 6px 20px rgba(250, 47, 121, 0.4);
            transform: translateY(-1px);
        }
        .navy-card {
            background-color: #0b1329;
            border: 1px solid rgba(255, 255, 255, 0.08);
            color: #ffffff;
        }
        
        /* HEADER BAR KHAS DESAIN REFERENSI (NAVY DENGAN SUDUT MIRING PINK DI KANAN) */
        .card-header-badge {
            position: relative;
            background-color: #0b1329;
            color: #ffffff;
            padding: 12px 20px;
            border-top-left-radius: 1rem;
            border-top-right-radius: 1rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
            overflow: hidden;
        }
        .card-header-badge .pink-accent-slash {
            position: absolute;
            right: 0;
            top: 0;
            bottom: 0;
            width: 22px;
            background-color: #fa2f79;
            transform: skewX(-20deg) translateX(8px);
        }

        /* HERO GAYA KAMPUS RESMI: FOTO SEKOLAH & SISWA DENGAN GRADASI MENYATU */
        .hero-campus-um {
            position: relative;
            background-image: 
                linear-gradient(90deg, 
                    rgba(8, 14, 30, 0.94) 0%, 
                    rgba(8, 14, 30, 0.88) 32%, 
                    rgba(8, 14, 30, 0.40) 58%, 
                    rgba(8, 14, 30, 0.05) 100%),
                url('{{ asset("images/hero-siswa-jurusan.jpg") }}');
            background-size: cover;
            background-position: right center;
            background-repeat: no-repeat;
        }
        @media (max-width: 1024px) {
            .hero-campus-um {
                background-image: 
                    linear-gradient(180deg, 
                        rgba(8, 14, 30, 0.96) 0%, 
                        rgba(8, 14, 30, 0.88) 60%, 
                        rgba(8, 14, 30, 0.94) 100%),
                    url('{{ asset("images/hero-siswa-jurusan.jpg") }}');
                background-position: center;
            }
        }
    </style>
    @stack('styles')
</head>
<body class="min-h-screen flex flex-col antialiased overflow-x-hidden w-full">

    <!-- Top Announcement Bar (Sesuai Referensi: Latar Putih, Badge Pink, Tombol Hitam) -->
    <div class="bg-white text-slate-900 text-xs py-2 px-3 sm:px-4 border-b border-slate-200 no-print overflow-hidden w-full">
        <div class="max-w-[1600px] mx-auto px-1 sm:px-4 lg:px-12 xl:px-16 flex items-center justify-between gap-2 sm:gap-3 min-w-0">
            <div class="flex items-center gap-2 min-w-0 flex-1 overflow-hidden">
                <span class="bg-[#eb3f7c] text-white px-2 sm:px-2.5 py-0.5 rounded-full text-[9px] sm:text-[10px] font-extrabold uppercase tracking-wide shrink-0">PENGUMUMAN</span>
                <span class="truncate text-slate-900 font-bold text-[11px] sm:text-xs md:text-sm">Penerimaan Peserta Didik Baru (PPDB) T.A. 2026/2027 Resmi Dibuka! Kuota Terbatas.</span>
            </div>
            <a href="{{ route('landing') }}#alur" class="bg-black hover:bg-slate-800 text-white text-[10px] font-bold px-2 sm:px-2.5 py-1 rounded flex items-center gap-1 shrink-0 transition-all">
                <span class="hidden xs:inline">Lihat </span><span>Alur</span>
                <i data-lucide="arrow-right" class="w-3 h-3"></i>
            </a>
        </div>
    </div>

    <!-- Header / Navbar Resmi (Sesuai Referensi Mockup) -->
    <header class="sticky top-0 z-50 bg-[#060c1d] text-white shadow-md no-print w-full overflow-hidden">
        <div class="max-w-[1600px] mx-auto px-3 sm:px-6 lg:px-12 xl:px-16">
            <div class="flex items-center justify-between h-16 sm:h-20 gap-2 sm:gap-4">
                
                <!-- Brand & Logo Transparan Sekolah -->
                <a href="{{ route('landing') }}" class="flex items-center gap-2 sm:gap-3 min-w-0 py-2">
                    <img src="{{ asset('images/logo-sekolah-transparan.png') }}" alt="Logo SMK Islam Bina Khalifah Bangsa" class="w-9 h-9 sm:w-11 sm:h-11 object-contain shrink-0 drop-shadow-sm">
                    <div class="leading-tight min-w-0">
                        <span class="block text-[10px] sm:text-[11px] font-bold text-slate-300 tracking-wider uppercase truncate">SMK ISLAM</span>
                        <span class="block text-xs sm:text-base font-extrabold text-white tracking-tight truncate">BINA KHALIFAH BANGSA</span>
                        <span class="block text-[9px] sm:text-[11px] text-slate-400 font-medium truncate">Kota Metro, Lampung</span>
                    </div>
                </a>

                <!-- Navigasi Menu (Desktop) -->
                <nav id="main-nav" class="hidden lg:flex items-center gap-1 xl:gap-2 text-sm font-medium">
                    <a href="{{ route('landing') }}" id="nav-beranda" class="nav-item {{ request()->routeIs('landing') ? 'px-4 xl:px-5 py-2 rounded-full bg-brand-pink text-white font-bold text-xs sm:text-sm shadow-md shadow-brand-pink/20 transition-all' : 'px-2.5 xl:px-3 py-2 text-slate-200 hover:text-white rounded-lg transition-colors text-xs sm:text-sm font-medium' }}">
                        Beranda
                    </a>
                    <a href="{{ route('landing') }}#profil" id="nav-profil" class="nav-item px-2.5 xl:px-3 py-2 text-slate-200 hover:text-white rounded-lg transition-colors text-xs sm:text-sm font-medium">
                        Profil Sekolah
                    </a>
                    <a href="{{ route('landing') }}#jurusan" id="nav-jurusan" class="nav-item px-2.5 xl:px-3 py-2 text-slate-200 hover:text-white rounded-lg transition-colors text-xs sm:text-sm font-medium">
                        Program Keahlian
                    </a>
                    <a href="{{ route('landing') }}#ekskul" id="nav-ekskul" class="nav-item px-2.5 xl:px-3 py-2 text-slate-200 hover:text-white rounded-lg transition-colors text-xs sm:text-sm font-medium">
                        Ekstrakurikuler
                    </a>
                    <a href="{{ route('landing') }}#alur" id="nav-alur" class="nav-item px-2.5 xl:px-3 py-2 text-slate-200 hover:text-white rounded-lg transition-colors text-xs sm:text-sm font-medium">
                        Informasi PPDB
                    </a>
                    <a href="{{ route('landing') }}#cek-kelulusan" id="nav-cek-kelulusan" class="nav-item px-2.5 xl:px-3 py-2 text-slate-200 hover:text-white rounded-lg transition-colors text-xs sm:text-sm font-medium">
                        Cek Kelulusan
                    </a>
                </nav>

                <!-- Tombol Auth & Hamburger -->
                <div class="flex items-center gap-1.5 sm:gap-2.5 shrink-0">
                    @auth
                    <!-- User Dropdown (Logged in Siswa) -->
                    <div class="relative group hidden sm:block">
                        <a href="{{ route('siswa.dashboard') }}" class="flex items-center gap-2 py-1.5 sm:py-2 px-3 sm:px-3.5 rounded-full bg-slate-800/80 border border-slate-700 text-white font-semibold text-xs md:text-sm hover:bg-slate-700 transition-colors whitespace-nowrap">
                            <i data-lucide="user" class="w-4 h-4 text-emerald-400"></i>
                            <span class="max-w-[100px] md:max-w-[120px] truncate">{{ Auth::user()->nama }}</span>
                            <i data-lucide="chevron-down" class="w-3.5 h-3.5 text-slate-400"></i>
                        </a>
                        <div class="absolute right-0 mt-2 w-48 bg-navy-900 rounded-2xl shadow-2xl border border-slate-700 py-2 hidden group-hover:block transition-all z-50">
                            <a href="{{ route('siswa.dashboard') }}" class="flex items-center gap-2 px-4 py-2 text-xs md:text-sm text-slate-200 hover:text-white hover:bg-white/10">
                                <i data-lucide="layout-dashboard" class="w-4 h-4 text-emerald-400"></i> Dashboard Siswa
                            </a>
                            <a href="{{ route('siswa.formulir') }}" class="flex items-center gap-2 px-4 py-2 text-xs md:text-sm text-slate-200 hover:text-white hover:bg-white/10">
                                <i data-lucide="file-text" class="w-4 h-4 text-sky-400"></i> Formulir Pendaftaran
                            </a>
                            <hr class="my-1 border-slate-800">
                            <form action="{{ route('logout') }}" method="POST">
                                @csrf
                                <button type="submit" class="w-full flex items-center gap-2 px-4 py-2 text-xs md:text-sm text-rose-400 hover:bg-rose-950/40 text-left">
                                    <i data-lucide="log-out" class="w-4 h-4"></i> Keluar
                                </button>
                            </form>
                        </div>
                    </div>
                    @else
                    <a href="{{ route('login') }}" class="px-2.5 sm:px-4 py-1.5 sm:py-2 rounded-xl bg-[#fa2f79] hover:bg-[#e11d48] text-white text-xs sm:text-sm font-bold transition-all flex items-center gap-1.5 sm:gap-2 shadow-md shadow-pink-500/20">
                        <span class="w-4 h-4 sm:w-5 sm:h-5 rounded-md bg-[#060c1d] flex items-center justify-center shrink-0">
                            <i data-lucide="user" class="w-3 h-3 sm:w-3.5 sm:h-3.5 text-white"></i>
                        </span>
                        <span>Login</span>
                    </a>
                    <a href="{{ route('register') }}" class="hidden md:inline-flex px-5 py-2.5 rounded-full btn-pink text-xs sm:text-sm font-bold items-center gap-1.5 shadow-md shadow-pink-500/25">
                        <span>Daftar Sekarang</span>
                        <i data-lucide="arrow-right" class="w-4 h-4"></i>
                    </a>
                    @endauth

                    <!-- Mobile Menu Button -->
                    <button id="mobile-menu-btn" aria-label="Menu" class="lg:hidden p-1.5 sm:p-2 rounded-xl text-slate-300 hover:text-white hover:bg-slate-800 transition-colors">
                        <i data-lucide="menu" class="w-5 h-5 sm:w-6 sm:h-6"></i>
                    </button>
                </div>

            </div>
        </div>

        <!-- Mobile Drawer -->
        <div id="mobile-menu" class="hidden lg:hidden border-t border-navy-800 bg-navy-950 px-4 pt-3 pb-6 space-y-2">
            @auth
                <div class="p-3 rounded-xl bg-navy-900 border border-slate-700 flex items-center justify-between">
                    <div>
                        <div class="text-xs font-bold text-white">{{ Auth::user()->nama }}</div>
                        <div class="text-[10px] text-slate-400 font-mono">NISN: {{ Auth::user()->nisn }}</div>
                    </div>
                    <a href="{{ route('siswa.dashboard') }}" class="px-3 py-1.5 rounded-lg btn-pink font-bold text-xs shadow-sm">
                        Panel Siswa &rarr;
                    </a>
                </div>
                <hr class="border-navy-800">
                <a href="{{ route('landing') }}" class="block py-2 text-sm font-medium text-white hover:text-brand-pink">Beranda</a>
                <a href="{{ route('landing') }}#profil" class="block py-2 text-sm font-medium text-slate-300 hover:text-white">Profil Sekolah</a>
                <a href="{{ route('landing') }}#jurusan" class="block py-2 text-sm font-medium text-slate-300 hover:text-white">Program Keahlian</a>
                <a href="{{ route('landing') }}#ekskul" class="block py-2 text-sm font-medium text-slate-300 hover:text-white">Ekstrakurikuler</a>
                <a href="{{ route('landing') }}#alur" class="block py-2 text-sm font-medium text-slate-300 hover:text-white">Informasi / Alur PPDB</a>
                <a href="{{ route('landing') }}#cek-kelulusan" class="block py-2 text-sm font-medium text-brand-pink font-semibold">Cek Kelulusan</a>
                <div class="pt-2 border-t border-navy-800">
                    <form action="{{ route('logout') }}" method="POST">
                        @csrf
                        <button type="submit" class="w-full flex items-center gap-2 px-3 py-2 rounded-xl text-sm font-bold text-rose-400 hover:bg-rose-950/40">
                            <i data-lucide="log-out" class="w-4 h-4"></i> Keluar
                        </button>
                    </form>
                </div>
            @else
                <a href="{{ route('landing') }}" class="block py-2 text-sm font-medium {{ request()->routeIs('landing') ? 'text-brand-pink font-bold' : 'text-slate-300 hover:text-white' }}">Beranda</a>
                <a href="{{ route('landing') }}#profil" class="block py-2 text-sm font-medium text-slate-300 hover:text-white">Profil Sekolah</a>
                <a href="{{ route('landing') }}#jurusan" class="block py-2 text-sm font-medium text-slate-300 hover:text-white">Program Keahlian</a>
                <a href="{{ route('landing') }}#ekskul" class="block py-2 text-sm font-medium text-slate-300 hover:text-white">Ekstrakurikuler</a>
                <a href="{{ route('landing') }}#alur" class="block py-2 text-sm font-medium text-slate-300 hover:text-white">Informasi / Alur PPDB</a>
                <a href="{{ route('landing') }}#cek-kelulusan" class="block py-2 text-sm font-medium text-brand-pink font-semibold">Cek Kelulusan</a>
                <div class="pt-3 border-t border-navy-800 flex flex-col gap-2">
                    <a href="{{ route('login') }}" class="w-full py-2.5 text-center text-sm font-semibold rounded-xl {{ request()->routeIs('login') ? 'bg-[#fa2f79] text-white font-bold shadow-md' : 'border border-slate-700 text-white hover:bg-slate-800' }} transition-colors">Masuk Akun</a>
                    <a href="{{ route('register') }}" class="w-full py-2.5 text-center text-sm font-semibold rounded-xl {{ request()->routeIs('register') ? 'bg-[#fa2f79] text-white font-bold ring-2 ring-pink-400 shadow-md' : 'btn-pink text-white shadow-md' }}">Daftar Akun Baru</a>
                </div>
            @endauth
        </div>
    </header>

    <!-- Flash Alerts (Hanya Tampil Jika Ada Pesan Alert, Mencegah Celah Garis Putih) -->
    @if(session('success') || session('error') || session('info') || $errors->any())
    <div class="max-w-[1600px] mx-auto px-4 sm:px-8 lg:px-12 xl:px-16 w-full mt-4 no-print">
        @if(session('success'))
        <div class="flex items-center gap-3 p-4 mb-4 text-emerald-800 bg-emerald-50 border border-emerald-200 rounded-2xl shadow-sm">
            <i data-lucide="check-circle-2" class="w-5 h-5 shrink-0 text-emerald-600"></i>
            <div class="text-sm font-medium">{{ session('success') }}</div>
        </div>
        @endif

        @if(session('error'))
        <div class="flex items-center gap-3 p-4 mb-4 text-rose-800 bg-rose-50 border border-rose-200 rounded-2xl shadow-sm">
            <i data-lucide="alert-circle" class="w-5 h-5 shrink-0 text-rose-600"></i>
            <div class="text-sm font-medium">{{ session('error') }}</div>
        </div>
        @endif

        @if(session('info'))
        <div class="flex items-center gap-3 p-4 mb-4 text-sky-800 bg-sky-50 border border-sky-200 rounded-2xl shadow-sm">
            <i data-lucide="info" class="w-5 h-5 shrink-0 text-sky-600"></i>
            <div class="text-sm font-medium">{{ session('info') }}</div>
        </div>
        @endif

        @if($errors->any())
        <div class="p-4 mb-4 text-rose-800 bg-rose-50 border border-rose-200 rounded-2xl shadow-sm">
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
    @endif

    <!-- Main Content -->
    <main class="flex-grow">
        @yield('content')
    </main>

    <!-- Footer Resmi (Deep Navy & Aksen Pink) -->
    <footer class="bg-navy-950 text-white py-12 border-t border-navy-800 no-print">
        <div class="max-w-[1600px] mx-auto px-4 sm:px-8 lg:px-12 xl:px-16">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8 pb-8 border-b border-navy-800">
                
                <!-- Kolom Kiri: Identitas Sekolah & Ikon Sosmed -->
                <div class="space-y-3.5">
                    <div class="flex items-center gap-3.5">
                        <img src="{{ asset('images/logo-sekolah-transparan.png') }}" alt="Logo SMK" class="w-14 h-14 sm:w-16 sm:h-16 object-contain shrink-0 drop-shadow-md">
                        <div>
                            <span class="block text-xs font-bold text-slate-300 uppercase tracking-wider">SMK ISLAM</span>
                            <span class="block text-sm sm:text-base font-extrabold text-white tracking-tight">BINA KHALIFAH BANGSA</span>
                            <span class="block text-xs text-slate-400">Kota Metro, Lampung</span>
                        </div>
                    </div>
                    <p class="text-xs text-slate-300 leading-relaxed max-w-sm">
                        Lembaga pendidikan berkualitas dan berakhlak mulia, siap mencetak generasi unggul di era digital
                    </p>
                    
                    <!-- Ikon Sosial Media (YouTube, Instagram, Facebook, TikTok dengan Logo Asli SVG) -->
                    <div class="flex items-center gap-2 pt-1">
                        <!-- YouTube -->
                        <a href="https://youtube.com" target="_blank" rel="noopener noreferrer" aria-label="YouTube" class="w-7 h-7 rounded-full bg-[#ff0000] text-white flex items-center justify-center hover:scale-110 transition-transform shadow-sm" title="YouTube">
                            <svg class="w-3.5 h-3.5 fill-white" viewBox="0 0 24 24">
                                <path d="M23.498 6.186a3.016 3.016 0 0 0-2.122-2.136C19.505 3.545 12 3.545 12 3.545s-7.505 0-9.377.505A3.017 3.017 0 0 0 .502 6.186C0 8.07 0 12 0 12s0 3.93.502 5.814a3.016 3.016 0 0 0 2.122 2.136c1.871.505 9.376.505 9.376.505s7.505 0 9.377-.505a3.015 3.015 0 0 0 2.122-2.136C24 15.93 24 12 24 12s0-3.93-.502-5.814zM9.545 15.568V8.432L15.818 12l-6.273 3.568z"/>
                            </svg>
                        </a>
                        <!-- Instagram -->
                        <a href="https://instagram.com" target="_blank" rel="noopener noreferrer" aria-label="Instagram" class="w-7 h-7 rounded-full bg-gradient-to-tr from-[#f09433] via-[#dc2743] to-[#bc1888] text-white flex items-center justify-center hover:scale-110 transition-transform shadow-sm" title="Instagram">
                            <svg class="w-3.5 h-3.5 fill-white" viewBox="0 0 24 24">
                                <path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z"/>
                            </svg>
                        </a>
                        <!-- Facebook -->
                        <a href="https://facebook.com" target="_blank" rel="noopener noreferrer" aria-label="Facebook" class="w-7 h-7 rounded-full bg-[#1877f2] text-white flex items-center justify-center hover:scale-110 transition-transform shadow-sm" title="Facebook">
                            <svg class="w-3.5 h-3.5 fill-white" viewBox="0 0 24 24">
                                <path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/>
                            </svg>
                        </a>
                        <!-- TikTok -->
                        <a href="https://tiktok.com" target="_blank" rel="noopener noreferrer" aria-label="TikTok" class="w-7 h-7 rounded-full bg-[#000000] border border-slate-700 text-white flex items-center justify-center hover:scale-110 transition-transform shadow-sm" title="TikTok">
                            <svg class="w-3.5 h-3.5 fill-white" viewBox="0 0 24 24">
                                <path d="M12.525.02c1.31-.02 2.61-.01 3.91-.02.08 1.53.63 3.09 1.75 4.17 1.12 1.11 2.7 1.62 4.24 1.79v4.03c-1.44-.05-2.89-.35-4.2-.97-.57-.26-1.1-.59-1.62-.93-.01 2.92.01 5.84-.02 8.75-.08 1.4-.54 2.79-1.35 3.94-1.31 1.92-3.58 3.17-5.91 3.21-1.43.08-2.86-.31-4.08-1.03-2.02-1.19-3.44-3.37-3.65-5.71-.02-.5-.03-1-.01-1.49.18-1.9 1.12-3.72 2.58-4.96 1.66-1.44 3.98-2.13 6.15-1.72.02 1.48-.04 2.96-.04 4.44-.99-.32-2.15-.23-3.02.37-.63.41-1.11 1.04-1.36 1.75-.21.51-.24 1.07-.14 1.61.24 1.64 1.82 3.02 3.5 2.87 1.12-.01 2.19-.66 2.77-1.61.19-.33.4-.67.41-1.06.1-1.79.06-3.57.07-5.36.01-4.03-.01-8.05.02-12.07z"/>
                            </svg>
                        </a>
                    </div>

                    <div class="pt-1">
                        <a href="{{ route('admin.login') }}" class="inline-flex items-center gap-1.5 text-slate-500 hover:text-brand-pink text-[11px] transition-colors">
                            <i data-lucide="lock" class="w-3 h-3"></i> Portal Staf TU & Kepala Sekolah &rarr;
                        </a>
                    </div>
                </div>

                <!-- Kolom Tengah: Program Keahlian -->
                <div>
                    <h4 class="text-sm font-bold text-white mb-3">Program Keahlian</h4>
                    <ul class="space-y-1.5 text-xs text-slate-300">
                        <li>1. Farmasi Klinis & Komunitas</li>
                        <li>2. Teknik Komputer & Jaringan (TKJ)</li>
                        <li>3. Teknik Kendaraan Ringan (TKR)</li>
                        <li>4. Teknik & Bisnis Sepeda Motor (TBSM)</li>
                    </ul>
                </div>

                <!-- Kolom Kanan: Kontak & Portal -->
                <div>
                    <h4 class="text-sm font-bold text-white mb-3 uppercase">KONTAK & PORTAL</h4>
                    <div class="space-y-2 text-xs text-slate-300">
                        <div class="flex items-center gap-2.5">
                            <i data-lucide="map-pin" class="w-4 h-4 text-slate-400 shrink-0"></i>
                            <span>Jl. Pattimura No. 24, ,Metro Barat</span>
                        </div>
                        <div class="flex items-center gap-2.5">
                            <i data-lucide="phone" class="w-4 h-4 text-slate-400 shrink-0"></i>
                            <span>0821-7890-1234</span>
                        </div>
                        <div class="flex items-center gap-2.5">
                            <i data-lucide="mail" class="w-4 h-4 text-slate-400 shrink-0"></i>
                            <span>info@smkib.com</span>
                        </div>
                        <div class="flex items-center gap-2.5">
                            <i data-lucide="clock" class="w-4 h-4 text-slate-400 shrink-0"></i>
                            <span>senin-Sabtu, 08.00-15.00 WIB</span>
                        </div>
                    </div>
                </div>

            </div>

            <div class="pt-6 flex flex-col sm:flex-row items-center justify-between text-xs text-slate-500 gap-2">
                <span>&copy; 2026 SMK Islam Bina Khalifah Bangsa Kota Metro. Hak Cipta Dilindungi.</span>
                <div class="flex items-center gap-4">
                    <a href="#" class="hover:text-slate-400">Disclaimer</a>
                    <a href="#" class="hover:text-slate-400">Kebijakan Privasi</a>
                    <a href="#" class="hover:text-slate-400">Sitemap</a>
                </div>
            </div>
        </div>
    </footer>

    <!-- Init Lucide Icons, Mobile Menu Toggle & Nav Scroll Spy -->
    <script>
        lucide.createIcons();

        const mobileMenuBtn = document.getElementById('mobile-menu-btn');
        const mobileMenu = document.getElementById('mobile-menu');
        if (mobileMenuBtn && mobileMenu) {
            mobileMenuBtn.addEventListener('click', function() {
                mobileMenu.classList.toggle('hidden');
            });
        }

        @if(request()->routeIs('landing'))
        // Nav Scroll Spy to Highlight Active Pill (Khusus Halaman Beranda)
        const navItems = {
            'profil': document.getElementById('nav-profil'),
            'jurusan': document.getElementById('nav-jurusan'),
            'ekskul': document.getElementById('nav-ekskul'),
            'alur': document.getElementById('nav-alur'),
            'cek-kelulusan': document.getElementById('nav-cek-kelulusan'),
        };
        const navBeranda = document.getElementById('nav-beranda');

        function setActiveNav(activeEl) {
            document.querySelectorAll('.nav-item').forEach(el => {
                el.className = 'nav-item px-2.5 xl:px-3 py-2 text-slate-200 hover:text-white rounded-lg transition-colors text-xs sm:text-sm font-medium';
            });
            if (activeEl) {
                activeEl.className = 'nav-item px-4 xl:px-5 py-2 rounded-full bg-brand-pink text-white font-bold text-xs sm:text-sm shadow-md shadow-brand-pink/20 transition-all';
            }
        }

        window.addEventListener('scroll', () => {
            const scrollPos = window.scrollY + 180;
            const profilSec = document.getElementById('profil');
            
            // Jika masih di area atas (Hero / Beranda)
            if (profilSec && window.scrollY < (profilSec.offsetTop - 200)) {
                setActiveNav(navBeranda);
                return;
            }

            let current = null;
            ['profil', 'jurusan', 'ekskul', 'alur', 'cek-kelulusan'].forEach(id => {
                const sec = document.getElementById(id);
                if (sec) {
                    const top = sec.offsetTop;
                    const bottom = top + sec.offsetHeight;
                    if (scrollPos >= top && scrollPos < bottom) {
                        current = id;
                    }
                }
            });

            if (current && navItems[current]) {
                setActiveNav(navItems[current]);
            } else {
                // Ketika sudah melewati cek kelulusan (misal di FAQ, Maps, Footer), hilangkan kotak pink aktif
                setActiveNav(null);
            }
        }, { passive: true });
        @endif
    </script>
    @stack('scripts')
</body>
</html>
