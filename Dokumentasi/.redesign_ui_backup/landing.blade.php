@extends('layouts.app')

@section('title', 'PPDB Online SMK Islam Bina Khalifah Bangsa Kota Metro T.A 2026/2027')

@section('content')

<!-- 1. Hero Section (Sesuai Referensi Gambar 1) -->
<section class="relative overflow-hidden bg-white pt-8 pb-14 md:py-16">
    <!-- Subtle Pink / Navy wave decorations matching reference -->
    <div class="absolute top-0 right-0 w-1/2 h-full opacity-10 pointer-events-none bg-gradient-to-l from-pink-200 to-transparent"></div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative">
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-10 items-center">
            
            <!-- Left Text Content -->
            <div class="lg:col-span-7 space-y-6 text-center lg:text-left">
                <!-- Pill Badge -->
                <div class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full bg-pink-50 border border-pink-200 text-[#f43f5e] text-xs font-bold tracking-wide">
                    <i data-lucide="megaphone" class="w-4 h-4"></i>
                    <span>Penerimaan Peserta Didik Baru</span>
                </div>

                <!-- Main Heading -->
                <h1 class="text-3xl sm:text-4xl md:text-5xl lg:text-6xl font-black text-[#0B182F] tracking-tight leading-tight">
                    PPDB <span class="text-[#f43f5e]">2026/2027</span>
                </h1>

                <p class="text-lg sm:text-xl md:text-2xl font-bold text-slate-800 leading-snug">
                    Wujudkan Masa Depan Bersama<br class="hidden sm:inline">
                    <span class="text-[#0B182F]">SMK Islam Bina Khalifah Bangsa</span>
                </p>

                <!-- CTAs -->
                <div class="flex flex-col sm:flex-row items-center justify-center lg:justify-start gap-3.5 pt-2">
                    <a href="{{ route('register') }}" class="w-full sm:w-auto px-7 py-3.5 rounded-full bg-[#f43f5e] hover:bg-[#e11d48] text-white font-bold text-sm shadow-md hover:shadow-lg transition-all text-center flex items-center justify-center gap-2">
                        <i data-lucide="pen-tool" class="w-4 h-4"></i>
                        <span>Daftar Sekarang</span>
                        <i data-lucide="arrow-right" class="w-4 h-4"></i>
                    </a>
                    <a href="#alur" class="w-full sm:w-auto px-7 py-3.5 rounded-full bg-white hover:bg-slate-50 text-[#0B182F] border-2 border-[#0B182F] font-bold text-sm transition-all text-center flex items-center justify-center gap-2">
                        <i data-lucide="info" class="w-4 h-4"></i>
                        <span>Informasi PPDB</span>
                    </a>
                </div>
            </div>

            <!-- Right Image: Students & School Building (Image 1) -->
            <div class="lg:col-span-5 flex justify-center">
                <div class="relative w-full max-w-lg rounded-3xl overflow-hidden shadow-2xl border-4 border-white bg-pink-100">
                    <img src="{{ asset('images/hero-students.jpg') }}" alt="Siswa SMK Islam Bina Khalifah Bangsa" class="w-full h-auto object-cover object-center transform hover:scale-102 transition-transform duration-300">
                </div>
            </div>
        </div>

        <!-- Row Bawah Hero: Gelombang Countdown & Keunggulan (Sesuai Referensi Gambar 1) -->
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 mt-12">
            
            <!-- Gelombang Box (Image 1 Left) -->
            <div class="lg:col-span-6 bg-[#fff5f7] border border-pink-200 rounded-3xl p-6 sm:p-7 flex flex-col justify-between shadow-xs">
                <div>
                    <div class="flex items-center justify-between text-xs sm:text-sm font-semibold mb-5 flex-wrap gap-2">
                        <span class="text-[#f43f5e] font-bold flex items-center gap-2 text-sm sm:text-base">
                            <i data-lucide="clock" class="w-5 h-5"></i>
                            {{ $gelombangAktif ? $gelombangAktif->nama_gelombang : 'Gelombang 1 (Early Bird)' }}
                        </span>
                        <span class="text-slate-700">
                            Berakhir: <strong>{{ $gelombangAktif ? $gelombangAktif->tgl_tutup->translatedFormat('d F Y') : '02 Januari 2027' }}</strong>
                        </span>
                    </div>

                    <!-- Countdown Blocks -->
                    <div class="grid grid-cols-4 gap-2.5 sm:gap-3 text-center my-4" id="countdown-timer" data-target="{{ $gelombangAktif ? $gelombangAktif->tgl_tutup->format('Y-m-d 23:59:59') : '2027-01-02 23:59:59' }}">
                        <div class="bg-white rounded-2xl p-3 sm:p-4 shadow-sm border border-pink-100">
                            <div class="text-2xl sm:text-3xl font-black text-[#0B182F]" id="cd-days">51</div>
                            <div class="text-[11px] sm:text-xs font-bold text-slate-500 uppercase mt-1">HARI</div>
                        </div>
                        <div class="bg-white rounded-2xl p-3 sm:p-4 shadow-sm border border-pink-100">
                            <div class="text-2xl sm:text-3xl font-black text-[#0B182F]" id="cd-hours">00</div>
                            <div class="text-[11px] sm:text-xs font-bold text-slate-500 uppercase mt-1">JAM</div>
                        </div>
                        <div class="bg-white rounded-2xl p-3 sm:p-4 shadow-sm border border-pink-100">
                            <div class="text-2xl sm:text-3xl font-black text-[#0B182F]" id="cd-mins">40</div>
                            <div class="text-[11px] sm:text-xs font-bold text-slate-500 uppercase mt-1">MENIT</div>
                        </div>
                        <div class="bg-white rounded-2xl p-3 sm:p-4 shadow-sm border border-pink-100">
                            <div class="text-2xl sm:text-3xl font-black text-[#f43f5e]" id="cd-secs">50</div>
                            <div class="text-[11px] sm:text-xs font-bold text-[#f43f5e] uppercase mt-1">DETIK</div>
                        </div>
                    </div>

                    <div class="text-center my-3 text-sm font-semibold text-slate-800">
                        <span class="inline-flex items-center gap-1.5">
                            <span class="text-amber-500">🔥</span> Biaya Formulir: <strong class="text-[#f43f5e]">Rp {{ number_format($gelombangAktif ? $gelombangAktif->biaya_pendaftaran : 100000, 0, ',', '.') }}</strong>
                        </span>
                    </div>
                </div>

                <!-- Action Buttons inside Gelombang Card -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 pt-3">
                    <a href="{{ route('register') }}" class="w-full py-3 px-4 rounded-full bg-[#f43f5e] hover:bg-[#e11d48] text-white font-bold text-xs sm:text-sm text-center shadow-md flex items-center justify-center gap-2 transition-all">
                        <i data-lucide="user-plus" class="w-4 h-4"></i>
                        <span>Daftar Sekarang</span>
                        <i data-lucide="arrow-right" class="w-4 h-4"></i>
                    </a>
                    <a href="#cek-kelulusan" class="w-full py-3 px-4 rounded-full bg-white hover:bg-slate-50 text-[#0B182F] border-2 border-[#0B182F] font-bold text-xs sm:text-sm text-center flex items-center justify-center gap-2 transition-all">
                        <i data-lucide="search" class="w-4 h-4"></i>
                        <span>Cek Status Kelulusan</span>
                    </a>
                </div>
            </div>

            <!-- Keunggulan Card (Image 1 Right) -->
            <div class="lg:col-span-6 bg-[#0B182F] text-white rounded-3xl p-6 sm:p-7 shadow-xl flex flex-col justify-between">
                <div>
                    <h3 class="text-base sm:text-lg font-bold text-white mb-4 flex items-center gap-2">
                        <i data-lucide="star" class="w-5 h-5 text-[#f43f5e] fill-[#f43f5e]"></i>
                        <span>Keunggulan SMK IB Khalifah Bangsa</span>
                    </h3>

                    <!-- 3 White Rounded Pill Items (Image 1) -->
                    <div class="space-y-3">
                        <div class="flex items-center gap-3.5 p-3.5 rounded-2xl bg-white text-slate-800 shadow-sm">
                            <div class="w-10 h-10 rounded-xl bg-pink-50 text-[#f43f5e] flex items-center justify-center shrink-0">
                                <i data-lucide="shield-check" class="w-6 h-6"></i>
                            </div>
                            <div>
                                <h4 class="text-xs sm:text-sm font-bold text-[#0B182F]">Integrasi Kurikulum Pesantren & Industri</h4>
                                <p class="text-[11px] sm:text-xs text-slate-600">Pendidikan vokasi berlandaskan Al-Qur'an dengan kurikulum industri.</p>
                            </div>
                        </div>

                        <div class="flex items-center gap-3.5 p-3.5 rounded-2xl bg-white text-slate-800 shadow-sm">
                            <div class="w-10 h-10 rounded-xl bg-pink-50 text-[#f43f5e] flex items-center justify-center shrink-0">
                                <i data-lucide="laptop" class="w-6 h-6"></i>
                            </div>
                            <div>
                                <h4 class="text-xs sm:text-sm font-bold text-[#0B182F]">Lulusan Siap Kerja & Wirausaha</h4>
                                <p class="text-[11px] sm:text-xs text-slate-600">Lab modern dan pembelajaran berbasis industri.</p>
                            </div>
                        </div>

                        <div class="flex items-center gap-3.5 p-3.5 rounded-2xl bg-white text-slate-800 shadow-sm">
                            <div class="w-10 h-10 rounded-xl bg-pink-50 text-[#f43f5e] flex items-center justify-center shrink-0">
                                <i data-lucide="trophy" class="w-6 h-6"></i>
                            </div>
                            <div>
                                <h4 class="text-xs sm:text-sm font-bold text-[#0B182F]">Jalur Prestasi & Keringanan Biaya</h4>
                                <p class="text-[11px] sm:text-xs text-slate-600">Prestasi akademik dan non-akademik mendapatkan beasiswa.</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Bottom Quick Stats (Image 1) -->
                <div class="grid grid-cols-2 gap-4 mt-6 pt-5 border-t border-slate-700/80 items-center text-center">
                    <div class="flex items-center justify-center gap-3">
                        <i data-lucide="users" class="w-6 h-6 text-[#f43f5e]"></i>
                        <div class="text-left">
                            <div class="text-xl sm:text-2xl font-black text-white leading-tight">{{ $totalPendaftar ?: 4 }}</div>
                            <div class="text-[11px] text-slate-300">Total Pendaftar</div>
                        </div>
                    </div>
                    <div class="flex items-center justify-center gap-3 border-l border-slate-700/80">
                        <i data-lucide="book-open" class="w-6 h-6 text-[#f43f5e]"></i>
                        <div class="text-left">
                            <div class="text-xl sm:text-2xl font-black text-white leading-tight">4 Jurusan</div>
                            <div class="text-[11px] text-slate-300">Program Keahlian</div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</section>

<!-- 2. Profil Sekolah Section (Sesuai Referensi Gambar 2) -->
<section id="profil" class="py-14 md:py-20 bg-slate-50 border-t border-slate-200">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        
        <!-- Section Header -->
        <div class="text-center max-w-3xl mx-auto mb-12 space-y-2">
            <div class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full bg-pink-50 border border-pink-200 text-[#f43f5e] text-xs font-bold">
                <i data-lucide="landmark" class="w-4 h-4"></i>
                <span>Profil Sekolah</span>
            </div>
            <h2 class="text-2xl sm:text-3xl md:text-4xl font-extrabold text-[#0B182F]">
                SMK Islam <span class="text-[#f43f5e]">Bina Khalifah Bangsa</span>
            </h2>
            <p class="text-xs sm:text-sm md:text-base text-slate-600 font-medium">
                Berakhlak Mulia, Kompeten, dan Siap Menghadapi Masa Depan
            </p>
        </div>

        <!-- Grid Cards (Image 2) -->
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
            
            <!-- Tentang Sekolah Card (Image 2 Left) -->
            <div class="lg:col-span-7 bg-white rounded-3xl p-6 sm:p-7 border border-slate-200 shadow-sm flex flex-col justify-between">
                <div>
                    <!-- Card Tag Header -->
                    <div class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-[#0B182F] text-white text-xs font-bold mb-5 shadow-xs">
                        <i data-lucide="school" class="w-4 h-4"></i>
                        <span>Tentang Sekolah</span>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-12 gap-5 items-center">
                        <div class="sm:col-span-5 rounded-2xl overflow-hidden border border-slate-100 shadow-sm">
                            <img src="{{ asset('images/gedung-sekolah.jpg') }}" alt="Gedung SMK Islam Bina Khalifah Bangsa" class="w-full h-44 sm:h-52 object-cover">
                        </div>
                        <div class="sm:col-span-7 text-xs sm:text-sm text-slate-600 leading-relaxed space-y-2">
                            <p>
                                <strong>SMK Islam Bina Khalifah Bangsa</strong> adalah lembaga pendidikan menengah kejuruan yang berkomitmen mencetak generasi muda yang berakhlak mulia, berilmu pengetahuan, dan terampil di bidangnya.
                            </p>
                            <p>
                                Dengan dukungan tenaga pendidik profesional serta fasilitas modern, sekolah kami siap mempersiapkan siswa untuk melanjutkan pendidikan ke jenjang yang lebih tinggi maupun terjun ke dunia kerja.
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Identitas Sekolah Card (Image 2 Right) -->
            <div class="lg:col-span-5 bg-white rounded-3xl p-6 sm:p-7 border border-slate-200 shadow-sm">
                <!-- Card Tag Header -->
                <div class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-[#0B182F] text-white text-xs font-bold mb-5 shadow-xs">
                    <i data-lucide="star" class="w-4 h-4 text-amber-400"></i>
                    <span>Identitas Sekolah</span>
                </div>

                <div class="divide-y divide-slate-100 text-xs sm:text-sm">
                    <div class="py-2.5 flex items-center justify-between">
                        <span class="text-slate-500 flex items-center gap-2"><i data-lucide="school" class="w-4 h-4 text-[#f43f5e]"></i> Nama Sekolah</span>
                        <span class="font-bold text-[#0B182F] text-right">SMK Islam Bina Khalifah Bangsa</span>
                    </div>
                    <div class="py-2.5 flex items-start justify-between gap-4">
                        <span class="text-slate-500 flex items-center gap-2 shrink-0"><i data-lucide="map-pin" class="w-4 h-4 text-[#f43f5e]"></i> Alamat</span>
                        <span class="font-medium text-slate-800 text-right text-xs">Jl. Pattimura No. 24, Ganjar Asri, Kec. Metro Barat, Kota Metro, Lampung 34125</span>
                    </div>
                    <div class="py-2.5 flex items-center justify-between">
                        <span class="text-slate-500 flex items-center gap-2"><i data-lucide="hash" class="w-4 h-4 text-[#f43f5e]"></i> NPSN</span>
                        <span class="font-bold text-[#0B182F]">70012345</span>
                    </div>
                    <div class="py-2.5 flex items-center justify-between">
                        <span class="text-slate-500 flex items-center gap-2"><i data-lucide="check-circle" class="w-4 h-4 text-[#f43f5e]"></i> Status</span>
                        <span class="font-bold text-slate-800">Swasta</span>
                    </div>
                    <div class="py-2.5 flex items-center justify-between">
                        <span class="text-slate-500 flex items-center gap-2"><i data-lucide="award" class="w-4 h-4 text-[#f43f5e]"></i> Akreditasi</span>
                        <span class="font-black text-[#f43f5e]">A</span>
                    </div>
                    <div class="py-2.5 flex items-center justify-between">
                        <span class="text-slate-500 flex items-center gap-2"><i data-lucide="mail" class="w-4 h-4 text-[#f43f5e]"></i> Email</span>
                        <span class="font-medium text-slate-800">info@smkib.com</span>
                    </div>
                    <div class="py-2.5 flex items-center justify-between">
                        <span class="text-slate-500 flex items-center gap-2"><i data-lucide="phone" class="w-4 h-4 text-[#f43f5e]"></i> Telepon</span>
                        <span class="font-medium text-slate-800">0821-7890-1234 / 69773555</span>
                    </div>
                </div>
            </div>

            <!-- Visi Card (Image 2 Bottom Left) -->
            <div class="lg:col-span-5 bg-white rounded-3xl p-6 sm:p-7 border border-slate-200 shadow-sm flex flex-col justify-between">
                <div>
                    <div class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-[#0B182F] text-white text-xs font-bold mb-4 shadow-xs">
                        <i data-lucide="target" class="w-4 h-4"></i>
                        <span>Visi</span>
                    </div>
                    <div class="mt-2 p-5 rounded-2xl bg-[#fff5f7] border border-pink-100 flex items-start gap-3">
                        <span class="text-3xl font-serif text-[#f43f5e] leading-none">“</span>
                        <p class="text-xs sm:text-sm font-bold text-[#0B182F] italic leading-relaxed pt-1">
                            Menjadi Sekolah Menengah Kejuruan Islam yang Unggul, Berkarakter, Berdaya Saing, dan Berjiwa Islami.
                        </p>
                    </div>
                </div>
            </div>

            <!-- Misi Card (Image 2 Bottom Right) -->
            <div class="lg:col-span-7 bg-white rounded-3xl p-6 sm:p-7 border border-slate-200 shadow-sm">
                <div class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-[#0B182F] text-white text-xs font-bold mb-4 shadow-xs">
                    <i data-lucide="list-checks" class="w-4 h-4"></i>
                    <span>Misi</span>
                </div>
                <div class="space-y-2.5 text-xs sm:text-sm">
                    <div class="flex items-start gap-3">
                        <div class="w-5 h-5 rounded-full bg-[#f43f5e] text-white flex items-center justify-center shrink-0 mt-0.5">
                            <i data-lucide="check" class="w-3.5 h-3.5"></i>
                        </div>
                        <p class="text-slate-700">Menyelenggarakan pendidikan yang berkualitas dan berlandaskan nilai-nilai Islam.</p>
                    </div>
                    <div class="flex items-start gap-3">
                        <div class="w-5 h-5 rounded-full bg-[#f43f5e] text-white flex items-center justify-center shrink-0 mt-0.5">
                            <i data-lucide="check" class="w-3.5 h-3.5"></i>
                        </div>
                        <p class="text-slate-700">Membekali peserta didik dengan keterampilan sesuai kebutuhan dunia kerja.</p>
                    </div>
                    <div class="flex items-start gap-3">
                        <div class="w-5 h-5 rounded-full bg-[#f43f5e] text-white flex items-center justify-center shrink-0 mt-0.5">
                            <i data-lucide="check" class="w-3.5 h-3.5"></i>
                        </div>
                        <p class="text-slate-700">Menanamkan akhlak mulia dan kedisiplinan dalam kehidupan sehari-hari.</p>
                    </div>
                    <div class="flex items-start gap-3">
                        <div class="w-5 h-5 rounded-full bg-[#f43f5e] text-white flex items-center justify-center shrink-0 mt-0.5">
                            <i data-lucide="check" class="w-3.5 h-3.5"></i>
                        </div>
                        <p class="text-slate-700">Mengembangkan potensi siswa secara optimal melalui kegiatan akademik dan non-akademik.</p>
                    </div>
                </div>
            </div>

        </div>
    </div>
</section>

<!-- 3. Program Keahlian Section (Sesuai Referensi Gambar 3) -->
<section id="jurusan" class="py-14 md:py-20 bg-white border-t border-slate-200">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        
        <!-- Section Header -->
        <div class="text-center max-w-3xl mx-auto mb-12 space-y-2">
            <div class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full bg-pink-50 border border-pink-200 text-[#f43f5e] text-xs font-bold uppercase tracking-wider">
                <i data-lucide="briefcase" class="w-4 h-4"></i>
                <span>Kompetensi Keahlian Unggulan</span>
            </div>
            <h2 class="text-2xl sm:text-3xl md:text-4xl font-extrabold text-[#0B182F]">
                Pilihan 4 Program Keahlian SMK
            </h2>
            <p class="text-xs sm:text-sm md:text-base text-slate-600">
                Dirancang khusus untuk memenuhi kebutuhan dunia usaha dan dunia industri (DUDI) dengan lulusan 72 siswa per jurusan.
            </p>
        </div>

        <!-- 4 Dark Navy Cards (Image 3) -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            
            <!-- Jurusan 1: Farmasi -->
            <div class="bg-[#0B182F] text-white rounded-3xl p-6 border border-slate-800 shadow-xl flex flex-col justify-between hover:-translate-y-1 transition-all duration-200">
                <div class="space-y-4">
                    <div class="w-14 h-14 rounded-2xl bg-teal-500/20 text-teal-300 border border-teal-500/30 flex items-center justify-center shadow-md">
                        <i data-lucide="palette" class="w-7 h-7"></i>
                    </div>
                    <div>
                        <span class="text-xs font-bold text-teal-400 tracking-wider">KODE: FJR</span>
                        <h3 class="text-lg font-bold text-white mt-1 leading-snug">Farmasi Klinis dan Komunitas</h3>
                    </div>
                    <p class="text-xs text-slate-300 leading-relaxed">
                        Program keahlian farmasi yang mempersiapkan siswa menjadi tenaga profesional di bidang farmasi klinis dan komunitas.
                    </p>
                </div>
                <div class="mt-6 pt-4 border-t border-slate-800 flex items-center justify-between text-xs">
                    <span class="text-slate-400 flex items-center gap-1.5"><i data-lucide="users" class="w-4 h-4"></i> Kapasitas Kuota:</span>
                    <span class="bg-[#f43f5e] text-white font-bold px-3 py-1 rounded-full text-xs">72 Siswa</span>
                </div>
            </div>

            <!-- Jurusan 2: TKJ -->
            <div class="bg-[#0B182F] text-white rounded-3xl p-6 border border-slate-800 shadow-xl flex flex-col justify-between hover:-translate-y-1 transition-all duration-200">
                <div class="space-y-4">
                    <div class="w-14 h-14 rounded-2xl bg-sky-500/20 text-sky-300 border border-sky-500/30 flex items-center justify-center shadow-md">
                        <i data-lucide="monitor" class="w-7 h-7"></i>
                    </div>
                    <div>
                        <span class="text-xs font-bold text-sky-400 tracking-wider">KODE: TKI</span>
                        <h3 class="text-lg font-bold text-white mt-1 leading-snug">Teknik Komputer dan Jaringan</h3>
                    </div>
                    <p class="text-xs text-slate-300 leading-relaxed">
                        Program keahlian yang fokus pada teknologi informasi, jaringan komputer, dan pengelolaan sistem.
                    </p>
                </div>
                <div class="mt-6 pt-4 border-t border-slate-800 flex items-center justify-between text-xs">
                    <span class="text-slate-400 flex items-center gap-1.5"><i data-lucide="users" class="w-4 h-4"></i> Kapasitas Kuota:</span>
                    <span class="bg-[#f43f5e] text-white font-bold px-3 py-1 rounded-full text-xs">72 Siswa</span>
                </div>
            </div>

            <!-- Jurusan 3: TKR -->
            <div class="bg-[#0B182F] text-white rounded-3xl p-6 border border-slate-800 shadow-xl flex flex-col justify-between hover:-translate-y-1 transition-all duration-200">
                <div class="space-y-4">
                    <div class="w-14 h-14 rounded-2xl bg-amber-500/20 text-amber-300 border border-amber-500/30 flex items-center justify-center shadow-md">
                        <i data-lucide="settings" class="w-7 h-7"></i>
                    </div>
                    <div>
                        <span class="text-xs font-bold text-amber-400 tracking-wider">KODE: TKR</span>
                        <h3 class="text-lg font-bold text-white mt-1 leading-snug">Teknik Kendaraan Ringan</h3>
                    </div>
                    <p class="text-xs text-slate-300 leading-relaxed">
                        Program keahlian yang membekali siswa dengan keterampilan perawatan dan perbaikan kendaraan ringan.
                    </p>
                </div>
                <div class="mt-6 pt-4 border-t border-slate-800 flex items-center justify-between text-xs">
                    <span class="text-slate-400 flex items-center gap-1.5"><i data-lucide="users" class="w-4 h-4"></i> Kapasitas Kuota:</span>
                    <span class="bg-[#f43f5e] text-white font-bold px-3 py-1 rounded-full text-xs">72 Siswa</span>
                </div>
            </div>

            <!-- Jurusan 4: TBSM -->
            <div class="bg-[#0B182F] text-white rounded-3xl p-6 border border-slate-800 shadow-xl flex flex-col justify-between hover:-translate-y-1 transition-all duration-200">
                <div class="space-y-4">
                    <div class="w-14 h-14 rounded-2xl bg-rose-500/20 text-rose-300 border border-rose-500/30 flex items-center justify-center shadow-md">
                        <i data-lucide="zap" class="w-7 h-7"></i>
                    </div>
                    <div>
                        <span class="text-xs font-bold text-rose-400 tracking-wider">KODE: TBSM</span>
                        <h3 class="text-lg font-bold text-white mt-1 leading-snug">Teknik dan Bisnis Sepeda Motor</h3>
                    </div>
                    <p class="text-xs text-slate-300 leading-relaxed">
                        Program keahlian yang menyiapkan siswa di bidang teknik sepeda motor dan wirausaha otomotif.
                    </p>
                </div>
                <div class="mt-6 pt-4 border-t border-slate-800 flex items-center justify-between text-xs">
                    <span class="text-slate-400 flex items-center gap-1.5"><i data-lucide="users" class="w-4 h-4"></i> Kapasitas Kuota:</span>
                    <span class="bg-[#f43f5e] text-white font-bold px-3 py-1 rounded-full text-xs">72 Siswa</span>
                </div>
            </div>

        </div>
    </div>
</section>

<!-- 4. Ekstrakurikuler Section (Sesuai Referensi Gambar 4) -->
<section id="ekskul" class="py-14 md:py-20 bg-slate-50 border-t border-slate-200">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        
        <!-- Section Header -->
        <div class="text-center max-w-3xl mx-auto mb-12 space-y-2">
            <div class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full bg-pink-50 border border-pink-200 text-[#f43f5e] text-xs font-bold uppercase tracking-wider">
                <i data-lucide="users" class="w-4 h-4"></i>
                <span>Pengembangan Bakat & Karakter</span>
            </div>
            <h2 class="text-2xl sm:text-3xl md:text-4xl font-extrabold text-[#0B182F]">
                7 Ekstrakurikuler Unggulan
            </h2>
            <p class="text-xs sm:text-sm md:text-base text-slate-600">
                Menumbuhkan kecerdasan, integritas, kemandirian, dan kepemimpinan, serta kecintaan pada wawasan budaya dan lingkungan.
            </p>
        </div>

        <!-- 7 Dark Navy Cards Grid (Image 4) -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
            
            <!-- Ekskul 1 -->
            <div class="bg-[#0B182F] text-white rounded-2xl p-5 border border-slate-800 shadow-md flex items-start gap-4">
                <div class="w-12 h-12 rounded-xl bg-emerald-500 text-white flex items-center justify-center shrink-0 shadow-sm">
                    <i data-lucide="book-open" class="w-6 h-6"></i>
                </div>
                <div>
                    <h4 class="text-sm font-bold text-white mb-1">Tilawatil Al-Qur'an</h4>
                    <p class="text-xs text-slate-300 leading-relaxed">Meningkatkan kemampuan membaca dan memahami Al-Qur'an.</p>
                </div>
            </div>

            <!-- Ekskul 2 -->
            <div class="bg-[#0B182F] text-white rounded-2xl p-5 border border-slate-800 shadow-md flex items-start gap-4">
                <div class="w-12 h-12 rounded-xl bg-blue-500 text-white flex items-center justify-center shrink-0 shadow-sm">
                    <i data-lucide="activity" class="w-6 h-6"></i>
                </div>
                <div>
                    <h4 class="text-sm font-bold text-white mb-1">Tari & Tahfidzul-Qur'an</h4>
                    <p class="text-xs text-slate-300 leading-relaxed">Melatih keterampilan seni tari dan hafalan Al-Qur'an.</p>
                </div>
            </div>

            <!-- Ekskul 3 -->
            <div class="bg-[#0B182F] text-white rounded-2xl p-5 border border-slate-800 shadow-md flex items-start gap-4">
                <div class="w-12 h-12 rounded-xl bg-teal-500 text-white flex items-center justify-center shrink-0 shadow-sm">
                    <i data-lucide="tent" class="w-6 h-6"></i>
                </div>
                <div>
                    <h4 class="text-sm font-bold text-white mb-1">Pramuka (Gugus SMK IB)</h4>
                    <p class="text-xs text-slate-300 leading-relaxed">Menanamkan disiplin, kemandirian, dan semangat kerja sama.</p>
                </div>
            </div>

            <!-- Ekskul 4 -->
            <div class="bg-[#0B182F] text-white rounded-2xl p-5 border border-slate-800 shadow-md flex items-start gap-4">
                <div class="w-12 h-12 rounded-xl bg-purple-500 text-white flex items-center justify-center shrink-0 shadow-sm">
                    <i data-lucide="flag" class="w-6 h-6"></i>
                </div>
                <div>
                    <h4 class="text-sm font-bold text-white mb-1">Paskibra</h4>
                    <p class="text-xs text-slate-300 leading-relaxed">Melatih kedisiplinan, baris-berbaris dan jiwa nasionalisme.</p>
                </div>
            </div>

            <!-- Ekskul 5 -->
            <div class="bg-[#0B182F] text-white rounded-2xl p-5 border border-slate-800 shadow-md flex items-start gap-4">
                <div class="w-12 h-12 rounded-xl bg-cyan-600 text-white flex items-center justify-center shrink-0 shadow-sm">
                    <i data-lucide="shield" class="w-6 h-6"></i>
                </div>
                <div>
                    <h4 class="text-sm font-bold text-white mb-1">Karate</h4>
                    <p class="text-xs text-slate-300 leading-relaxed">Melatih fisik, mental, dan teknik bela diri.</p>
                </div>
            </div>

            <!-- Ekskul 6 -->
            <div class="bg-[#0B182F] text-white rounded-2xl p-5 border border-slate-800 shadow-md flex items-start gap-4">
                <div class="w-12 h-12 rounded-xl bg-sky-500 text-white flex items-center justify-center shrink-0 shadow-sm">
                    <i data-lucide="music" class="w-6 h-6"></i>
                </div>
                <div>
                    <h4 class="text-sm font-bold text-white mb-1">Gamelan Karawitan</h4>
                    <p class="text-xs text-slate-300 leading-relaxed">Melestarikan budaya daerah melalui seni gamelan.</p>
                </div>
            </div>

            <!-- Ekskul 7 -->
            <div class="bg-[#0B182F] text-white rounded-2xl p-5 border border-slate-800 shadow-md flex items-start gap-4 md:col-span-2 lg:col-span-1">
                <div class="w-12 h-12 rounded-xl bg-[#f43f5e] text-white flex items-center justify-center shrink-0 shadow-sm">
                    <i data-lucide="award" class="w-6 h-6"></i>
                </div>
                <div>
                    <h4 class="text-sm font-bold text-white mb-1">Gamelan Tolak Balak</h4>
                    <p class="text-xs text-slate-300 leading-relaxed">Melatih konsentrasi dan strategi dalam permainan tradisional adat.</p>
                </div>
            </div>

        </div>
    </div>
</section>

<!-- 5. Alur 7 Tahap PPDB Online Section (Sesuai Referensi Gambar 5) -->
<section id="alur" class="py-14 md:py-20 bg-white border-t border-slate-200">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        
        <!-- Section Header -->
        <div class="text-center max-w-3xl mx-auto mb-12 space-y-2">
            <div class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full bg-pink-50 border border-pink-200 text-[#f43f5e] text-xs font-bold uppercase tracking-wider">
                <i data-lucide="compass" class="w-4 h-4"></i>
                <span>Panduan Pendaftaran</span>
            </div>
            <h2 class="text-2xl sm:text-3xl md:text-4xl font-extrabold text-[#0B182F]">
                Alur 7 Tahap <span class="text-[#f43f5e]">PPDB Online</span>
            </h2>
            <p class="text-xs sm:text-sm md:text-base text-slate-600">
                Ikuti langkah-langkah berikut untuk melakukan pendaftaran secara online dengan mudah dan benar.
            </p>
        </div>

        <!-- 7 Step Cards with Arrows (Image 5) -->
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 lg:grid-cols-7 gap-4 relative">
            
            <!-- Step 1 -->
            <div class="bg-white rounded-3xl p-5 border border-slate-200/90 shadow-sm text-center relative flex flex-col items-center hover:border-pink-300 transition-colors">
                <div class="w-8 h-8 rounded-full bg-[#0B182F] text-white font-extrabold text-xs flex items-center justify-center mb-3">
                    1
                </div>
                <div class="w-14 h-14 rounded-full bg-pink-100/70 text-[#f43f5e] flex items-center justify-center mb-3 shadow-xs">
                    <i data-lucide="laptop" class="w-7 h-7"></i>
                </div>
                <h4 class="text-sm font-bold text-[#0B182F] mb-1.5">Daftar Akun</h4>
                <p class="text-[11px] text-slate-500 leading-relaxed">Buat akun baru menggunakan email aktif dan nomor HP.</p>
            </div>

            <!-- Step 2 -->
            <div class="bg-white rounded-3xl p-5 border border-slate-200/90 shadow-sm text-center relative flex flex-col items-center hover:border-pink-300 transition-colors">
                <div class="w-8 h-8 rounded-full bg-[#0B182F] text-white font-extrabold text-xs flex items-center justify-center mb-3">
                    2
                </div>
                <div class="w-14 h-14 rounded-full bg-pink-100/70 text-[#f43f5e] flex items-center justify-center mb-3 shadow-xs">
                    <i data-lucide="edit-3" class="w-7 h-7"></i>
                </div>
                <h4 class="text-sm font-bold text-[#0B182F] mb-1.5">Isi Formulir</h4>
                <p class="text-[11px] text-slate-500 leading-relaxed">Lengkapi data diri dan pilih jurusan yang diinginkan.</p>
            </div>

            <!-- Step 3 -->
            <div class="bg-white rounded-3xl p-5 border border-slate-200/90 shadow-sm text-center relative flex flex-col items-center hover:border-pink-300 transition-colors">
                <div class="w-8 h-8 rounded-full bg-[#0B182F] text-white font-extrabold text-xs flex items-center justify-center mb-3">
                    3
                </div>
                <div class="w-14 h-14 rounded-full bg-pink-100/70 text-[#f43f5e] flex items-center justify-center mb-3 shadow-xs">
                    <i data-lucide="upload-cloud" class="w-7 h-7"></i>
                </div>
                <h4 class="text-sm font-bold text-[#0B182F] mb-1.5">Upload Berkas</h4>
                <p class="text-[11px] text-slate-500 leading-relaxed">Unggah dokumen persyaratan sesuai ketentuan.</p>
            </div>

            <!-- Step 4 -->
            <div class="bg-white rounded-3xl p-5 border border-slate-200/90 shadow-sm text-center relative flex flex-col items-center hover:border-pink-300 transition-colors">
                <div class="w-8 h-8 rounded-full bg-[#0B182F] text-white font-extrabold text-xs flex items-center justify-center mb-3">
                    4
                </div>
                <div class="w-14 h-14 rounded-full bg-pink-100/70 text-[#f43f5e] flex items-center justify-center mb-3 shadow-xs">
                    <i data-lucide="clipboard-check" class="w-7 h-7"></i>
                </div>
                <h4 class="text-sm font-bold text-[#0B182F] mb-1.5">Verifikasi</h4>
                <p class="text-[11px] text-slate-500 leading-relaxed">Tunggu proses verifikasi data oleh panitia.</p>
            </div>

            <!-- Step 5 -->
            <div class="bg-white rounded-3xl p-5 border border-slate-200/90 shadow-sm text-center relative flex flex-col items-center hover:border-pink-300 transition-colors">
                <div class="w-8 h-8 rounded-full bg-[#0B182F] text-white font-extrabold text-xs flex items-center justify-center mb-3">
                    5
                </div>
                <div class="w-14 h-14 rounded-full bg-pink-100/70 text-[#f43f5e] flex items-center justify-center mb-3 shadow-xs">
                    <i data-lucide="credit-card" class="w-7 h-7"></i>
                </div>
                <h4 class="text-sm font-bold text-[#0B182F] mb-1.5">Pembayaran</h4>
                <p class="text-[11px] text-slate-500 leading-relaxed">Lakukan pembayaran sesuai metode yang telah tersedia.</p>
            </div>

            <!-- Step 6 -->
            <div class="bg-white rounded-3xl p-5 border border-slate-200/90 shadow-sm text-center relative flex flex-col items-center hover:border-pink-300 transition-colors">
                <div class="w-8 h-8 rounded-full bg-[#0B182F] text-white font-extrabold text-xs flex items-center justify-center mb-3">
                    6
                </div>
                <div class="w-14 h-14 rounded-full bg-pink-100/70 text-[#f43f5e] flex items-center justify-center mb-3 shadow-xs">
                    <i data-lucide="file-check-2" class="w-7 h-7"></i>
                </div>
                <h4 class="text-sm font-bold text-[#0B182F] mb-1.5">Tes Seleksi</h4>
                <p class="text-[11px] text-slate-500 leading-relaxed">Ikuti tes seleksi sesuai jadwal yang ditetapkan.</p>
            </div>

            <!-- Step 7 -->
            <div class="bg-white rounded-3xl p-5 border border-slate-200/90 shadow-sm text-center relative flex flex-col items-center hover:border-pink-300 transition-colors">
                <div class="w-8 h-8 rounded-full bg-[#0B182F] text-white font-extrabold text-xs flex items-center justify-center mb-3">
                    7
                </div>
                <div class="w-14 h-14 rounded-full bg-pink-100/70 text-[#f43f5e] flex items-center justify-center mb-3 shadow-xs">
                    <i data-lucide="award" class="w-7 h-7"></i>
                </div>
                <h4 class="text-sm font-bold text-[#0B182F] mb-1.5">Pengumuman</h4>
                <p class="text-[11px] text-slate-500 leading-relaxed">Cek hasil seleksi dan lakukan daftar ulang jika diterima.</p>
            </div>

        </div>
    </div>
</section>

<!-- 6. Cek Status Hasil Seleksi PPDB Section (Sesuai Referensi Gambar 6) -->
<section id="cek-kelulusan" class="py-14 md:py-20 bg-slate-50 border-t border-slate-200">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center space-y-6">
        
        <!-- Section Header -->
        <div class="space-y-2">
            <div class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full bg-pink-50 border border-pink-200 text-[#f43f5e] text-xs font-bold uppercase tracking-wider">
                <i data-lucide="trending-up" class="w-4 h-4"></i>
                <span>Pantau Hasil Seleksi</span>
            </div>
            <h2 class="text-2xl sm:text-3xl md:text-4xl font-extrabold text-[#0B182F]">
                Cek Status Hasil Seleksi <span class="text-[#f43f5e]">PPDB</span>
            </h2>
            <p class="text-xs sm:text-sm md:text-base text-slate-600 max-w-xl mx-auto">
                Masukkan kode pendaftaran atau NISN Anda untuk mengetahui hasil seleksi PPDB di SMK Islam Bina Khalifah Bangsa.
            </p>
        </div>

        <!-- Search Bar Card (Image 6) -->
        <div class="bg-white rounded-3xl p-6 sm:p-8 border border-slate-200 shadow-xl max-w-2xl mx-auto text-left">
            <form id="form-cek-kelulusan" class="space-y-4">
                @csrf
                <div class="flex flex-col sm:flex-row gap-3">
                    <div class="relative flex-1">
                        <i data-lucide="search" class="w-5 h-5 text-slate-400 absolute left-4 top-1/2 -translate-y-1/2"></i>
                        <input type="text" id="input-nisn" name="nisn" required placeholder="Contoh: 202611060001 atau NISN Anda" class="w-full pl-12 pr-4 py-3.5 rounded-2xl bg-slate-50 border border-slate-200 text-slate-800 placeholder-slate-400 text-sm focus:outline-none focus:border-[#f43f5e] focus:bg-white transition-all">
                    </div>
                    <button type="submit" id="btn-submit-cek" class="px-7 py-3.5 rounded-2xl bg-[#f43f5e] hover:bg-[#e11d48] text-white font-bold text-sm shadow-md flex items-center justify-center gap-2 shrink-0 transition-all">
                        <i data-lucide="search" class="w-4 h-4"></i>
                        <span>Cek Status</span>
                    </button>
                </div>
                
                <!-- Notice Info (Image 6) -->
                <div class="flex items-center gap-2 p-3 rounded-xl bg-sky-50 text-sky-800 text-xs">
                    <i data-lucide="info" class="w-4 h-4 text-sky-600 shrink-0"></i>
                    <span>Pastikan Anda memasukkan kode pendaftaran atau NISN dengan benar.</span>
                </div>
            </form>

            <!-- Result Container -->
            <div id="hasil-cek-box" class="hidden mt-6 pt-6 border-t border-slate-100">
                <div id="hasil-konten"></div>
            </div>
        </div>

    </div>
</section>

<!-- 7. FAQ Section (Sesuai Referensi Gambar 7) -->
<section id="faq" class="py-14 md:py-20 bg-white border-t border-slate-200">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        
        <!-- Section Header -->
        <div class="text-center mb-12 space-y-2">
            <div class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full bg-pink-50 border border-pink-200 text-[#f43f5e] text-xs font-bold uppercase tracking-wider">
                <i data-lucide="help-circle" class="w-4 h-4"></i>
                <span>Pertanyaan Umum</span>
            </div>
            <h2 class="text-2xl sm:text-3xl md:text-4xl font-extrabold text-[#0B182F]">
                Tanya Jawab (FAQ) <span class="text-[#f43f5e]">PPDB</span>
            </h2>
            <p class="text-xs sm:text-sm md:text-base text-slate-600">
                Jawaban atas pertanyaan yang sering ditanyakan seputar pendaftaran PPDB di SMK Islam Bina Khalifah Bangsa.
            </p>
        </div>

        <!-- 4 Navy FAQ Accordions (Image 7) -->
        <div class="space-y-4">
            
            <!-- FAQ 1 -->
            <details class="group bg-[#0B182F] text-white rounded-2xl p-5 border border-slate-800 shadow-md">
                <summary class="font-bold text-sm md:text-base cursor-pointer list-none flex items-center justify-between gap-4">
                    <span class="flex items-center gap-3.5">
                        <span class="w-8 h-8 rounded-full bg-[#f43f5e] text-white flex items-center justify-center shrink-0">
                            <i data-lucide="user" class="w-4 h-4"></i>
                        </span>
                        <span>Apakah pendaftaran PPDB ini dilakukan secara online?</span>
                    </span>
                    <i data-lucide="chevron-down" class="w-5 h-5 text-slate-400 group-open:rotate-180 transition-transform shrink-0"></i>
                </summary>
                <div class="mt-4 pt-4 border-t border-slate-800 text-xs md:text-sm text-slate-300 leading-relaxed pl-11">
                    Ya, seluruh alur mulai dari pembuatan akun, pengisian formulir, unggah berkas, hingga pengecekan kelulusan dilakukan secara online. Namun bagi orang tua atau calon siswa yang membutuhkan bantuan pendaftaran atau ingin membayar secara tunai, silakan hadir ke Loket TU di kampus sekolah kami.
                </div>
            </details>

            <!-- FAQ 2 -->
            <details class="group bg-[#0B182F] text-white rounded-2xl p-5 border border-slate-800 shadow-md">
                <summary class="font-bold text-sm md:text-base cursor-pointer list-none flex items-center justify-between gap-4">
                    <span class="flex items-center gap-3.5">
                        <span class="w-8 h-8 rounded-full bg-[#f43f5e] text-white flex items-center justify-center shrink-0">
                            <i data-lucide="calendar" class="w-4 h-4"></i>
                        </span>
                        <span>Bagaimana jika jurusan pilihan pertama saya sudah penuh?</span>
                    </span>
                    <i data-lucide="chevron-down" class="w-5 h-5 text-slate-400 group-open:rotate-180 transition-transform shrink-0"></i>
                </summary>
                <div class="mt-4 pt-4 border-t border-slate-800 text-xs md:text-sm text-slate-300 leading-relaxed pl-11">
                    Sistem kami memiliki mekanisme pengalihan kuota otomatis. Jika kuota 72 siswa pada jurusan pilihan 1 telah terpenuhi, berkas Anda otomatis dipertimbangkan untuk jurusan pilihan 2 yang telah Anda pilih saat mengisi formulir.
                </div>
            </details>

            <!-- FAQ 3 -->
            <details class="group bg-[#0B182F] text-white rounded-2xl p-5 border border-slate-800 shadow-md">
                <summary class="font-bold text-sm md:text-base cursor-pointer list-none flex items-center justify-between gap-4">
                    <span class="flex items-center gap-3.5">
                        <span class="w-8 h-8 rounded-full bg-[#f43f5e] text-white flex items-center justify-center shrink-0">
                            <i data-lucide="info" class="w-4 h-4"></i>
                        </span>
                        <span>Apa saja tes seleksi yang diadakan di SMK Islam Bina Khalifah Bangsa?</span>
                    </span>
                    <i data-lucide="chevron-down" class="w-5 h-5 text-slate-400 group-open:rotate-180 transition-transform shrink-0"></i>
                </summary>
                <div class="mt-4 pt-4 border-t border-slate-800 text-xs md:text-sm text-slate-300 leading-relaxed pl-11">
                    Tes seleksi mencakup Tes Wawancara Minat Bakat & Baca Al-Qur'an, serta Tes Buta Warna (khususnya untuk Farmasi, Komputer Jaringan, dan Otomotif guna menjamin keselamatan kerja saat praktikum di laboratorium dan bengkel).
                </div>
            </details>

            <!-- FAQ 4 -->
            <details class="group bg-[#0B182F] text-white rounded-2xl p-5 border border-slate-800 shadow-md">
                <summary class="font-bold text-sm md:text-base cursor-pointer list-none flex items-center justify-between gap-4">
                    <span class="flex items-center gap-3.5">
                        <span class="w-8 h-8 rounded-full bg-[#f43f5e] text-white flex items-center justify-center shrink-0">
                            <i data-lucide="award" class="w-4 h-4"></i>
                        </span>
                        <span>Apakah ada beasiswa khusus untuk Tahfiz Al-Qur'an?</span>
                    </span>
                    <i data-lucide="chevron-down" class="w-5 h-5 text-slate-400 group-open:rotate-180 transition-transform shrink-0"></i>
                </summary>
                <div class="mt-4 pt-4 border-t border-slate-800 text-xs md:text-sm text-slate-300 leading-relaxed pl-11">
                    Ya, kami menyediakan beasiswa penuh dan keringanan biaya bagi calon siswa yang memiliki hafalan Al-Qur'an minimal 3 Juz. Silakan pilih Jalur Prestasi dan unggah syahadah / sertifikat tahfiz Anda saat mengisi formulir pendaftaran.
                </div>
            </details>

        </div>
    </div>
</section>

<!-- 8. Lokasi Kampus Sekolah (Sesuai Referensi Gambar 8) -->
<section id="lokasi" class="py-14 md:py-20 bg-slate-50 border-t border-slate-200">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-center">
            
            <!-- Left Info (Image 8) -->
            <div class="lg:col-span-5 space-y-4">
                <div class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full bg-pink-50 border border-pink-200 text-[#f43f5e] text-xs font-bold uppercase tracking-wider">
                    <i data-lucide="map-pin" class="w-4 h-4"></i>
                    <span>Lokasi Kampus Sekolah</span>
                </div>
                
                <h2 class="text-2xl sm:text-3xl md:text-4xl font-extrabold text-[#0B182F] leading-tight">
                    Kunjungi SMK Islam<br>
                    <span class="text-[#f43f5e]">Bina Khalifah Bangsa</span>
                </h2>
                
                <p class="text-xs sm:text-sm text-slate-600 leading-relaxed">
                    Terletak strategis di Kota Metro, Lampung. Memiliki lingkungan belajar yang asri, sarana ibadah luas, dan fasilitas praktik berstandar untuk kenyamanan belajar siswa.
                </p>

                <div class="space-y-2.5 text-xs sm:text-sm text-slate-700 pt-2">
                    <p class="flex items-start gap-2.5">
                        <i data-lucide="map-pin" class="w-4 h-4 text-[#f43f5e] shrink-0 mt-0.5"></i>
                        <span>Jl. Pattimura No. 24, Ganjar Asri, Kec. Metro Barat, Kota Metro, Lampung 34125</span>
                    </p>
                    <p class="flex items-center gap-2.5">
                        <i data-lucide="clock" class="w-4 h-4 text-[#f43f5e] shrink-0"></i>
                        <span>Pelayanan loket PPDB Senin - Sabtu, Pukul 08.00 - 15.00 WIB</span>
                    </p>
                    <p class="flex items-center gap-2.5">
                        <i data-lucide="phone" class="w-4 h-4 text-[#f43f5e] shrink-0"></i>
                        <span>Call Center / WhatsApp: 0821-7890-1234</span>
                    </p>
                </div>

                <div class="pt-3">
                    <a href="https://www.google.com/maps/search/?api=1&query={{ urlencode('SMK Islam Bina Khalifah Bangsa Kota Metro Lampung') }}" target="_blank" rel="noopener noreferrer" class="inline-flex items-center gap-2 px-6 py-3 rounded-full bg-white hover:bg-slate-50 text-[#0B182F] border-2 border-[#0B182F] font-bold text-xs sm:text-sm shadow-sm hover:shadow transition-all">
                        <i data-lucide="navigation" class="w-4 h-4 text-[#f43f5e]"></i>
                        <span>Buka Petunjuk Rute Google Maps</span>
                        <i data-lucide="chevron-right" class="w-4 h-4"></i>
                    </a>
                </div>
            </div>

            <!-- Right Interactive Map Embed (Image 8) -->
            <div class="lg:col-span-7">
                <div class="relative rounded-3xl overflow-hidden border border-slate-200 shadow-2xl h-80 sm:h-96 bg-slate-100">
                    <iframe 
                        title="Peta Lokasi SMK Islam Bina Khalifah Bangsa"
                        class="w-full h-full border-0"
                        loading="lazy" 
                        allowfullscreen 
                        src="https://maps.google.com/maps?q={{ urlencode('SMK Islam Bina Khalifah Bangsa Kota Metro Lampung') }}&t=&z=16&ie=UTF8&iwloc=B&output=embed">
                    </iframe>
                    <!-- Campus Floating Pin Badge -->
                    <div class="absolute top-4 left-4 bg-white/95 backdrop-blur-md px-3.5 py-2 rounded-xl shadow-lg border border-slate-200 flex items-center gap-2.5 pointer-events-none">
                        <span class="relative flex h-2.5 w-2.5">
                            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-pink-400 opacity-75"></span>
                            <span class="relative inline-flex rounded-full h-2.5 w-2.5 bg-[#f43f5e]"></span>
                        </span>
                        <span class="text-xs font-bold text-[#0B182F]">SMK Islam Bina Khalifah Bangsa</span>
                    </div>
                </div>
            </div>

        </div>
    </div>
</section>

@endsection

@push('scripts')
<script>
    // Countdown Timer Logic
    const timerContainer = document.getElementById('countdown-timer');
    if (timerContainer) {
        const targetDate = new Date(timerContainer.getAttribute('data-target')).getTime();

        const updateTimer = () => {
            const now = new Date().getTime();
            const difference = targetDate - now;

            if (difference > 0) {
                const days = Math.floor(difference / (1000 * 60 * 60 * 24));
                const hours = Math.floor((difference % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
                const minutes = Math.floor((difference % (1000 * 60 * 60)) / (1000 * 60));
                const seconds = Math.floor((difference % (1000 * 60)) / 1000);

                const elDays = document.getElementById('cd-days');
                const elHours = document.getElementById('cd-hours');
                const elMins = document.getElementById('cd-mins');
                const elSecs = document.getElementById('cd-secs');

                if (elDays) elDays.innerText = String(days).padStart(2, '0');
                if (elHours) elHours.innerText = String(hours).padStart(2, '0');
                if (elMins) elMins.innerText = String(minutes).padStart(2, '0');
                if (elSecs) elSecs.innerText = String(seconds).padStart(2, '0');
            } else {
                document.getElementById('cd-days').innerText = '00';
                document.getElementById('cd-hours').innerText = '00';
                document.getElementById('cd-mins').innerText = '00';
                document.getElementById('cd-secs').innerText = '00';
            }
        };

        updateTimer();
        setInterval(updateTimer, 1000);
    }

    // Cek Kelulusan AJAX
    const formCek = document.getElementById('form-cek-kelulusan');
    const resultBox = document.getElementById('hasil-cek-box');
    const resultContent = document.getElementById('hasil-konten');
    const btnSubmit = document.getElementById('btn-submit-cek');

    if (formCek) {
        formCek.addEventListener('submit', async function(e) {
            e.preventDefault();
            const nisn = document.getElementById('input-nisn').value.trim();
            if (!nisn) return;

            btnSubmit.disabled = true;
            btnSubmit.innerHTML = `<i data-lucide="loader-2" class="w-4 h-4 animate-spin"></i><span>Memeriksa...</span>`;
            lucide.createIcons();

            try {
                const response = await fetch("{{ route('cek.kelulusan') }}", {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify({ nisn: nisn })
                });

                const res = await response.json();
                resultBox.classList.remove('hidden');

                if (res.success) {
                    const d = res.data;
                    let badgeClass = 'bg-amber-100 text-amber-800 border-amber-300';
                    let statusIcon = 'clock';
                    let statusMsg = 'Sedang Dalam Proses Seleksi / Penilaian.';

                    if (d.status_kelulusan === 'Diterima') {
                        badgeClass = 'bg-emerald-100 text-emerald-800 border-emerald-300';
                        statusIcon = 'check-circle-2';
                        statusMsg = `Selamat! Anda Diterima di Jurusan <strong>${d.jurusan_diterima}</strong>.`;
                    } else if (d.status_kelulusan === 'Cadangan') {
                        badgeClass = 'bg-sky-100 text-sky-800 border-sky-300';
                        statusIcon = 'help-circle';
                        statusMsg = 'Anda Masuk dalam Daftar Cadangan / Waiting List.';
                    } else if (d.status_kelulusan === 'Tidak Lolos') {
                        badgeClass = 'bg-rose-100 text-rose-800 border-rose-300';
                        statusIcon = 'x-circle';
                        statusMsg = 'Mohon Maaf, Anda Belum Lolos Seleksi Tahun Ini.';
                    }

                    resultContent.innerHTML = `
                        <div class="p-5 rounded-2xl bg-[#fff5f7] border border-pink-200 space-y-3">
                            <div class="flex items-center justify-between flex-wrap gap-2">
                                <span class="px-3 py-1 rounded-full border text-xs font-bold uppercase tracking-wider ${badgeClass} flex items-center gap-1.5">
                                    <i data-lucide="${statusIcon}" class="w-4 h-4"></i> ${d.status_kelulusan}
                                </span>
                                <span class="text-xs text-slate-500 font-mono font-bold">${d.no_pendaftaran}</span>
                            </div>
                            <div>
                                <h4 class="text-base font-bold text-[#0B182F]">${d.nama}</h4>
                                <p class="text-xs text-slate-600">NISN: ${d.nisn} | Asal Sekolah: ${d.asal_sekolah}</p>
                            </div>
                            <div class="p-3.5 rounded-xl bg-white border border-pink-100 text-xs text-slate-800">
                                ${statusMsg}
                                ${d.no_skl ? `<div class="mt-1.5 pt-1.5 border-t border-slate-100 text-[11px] font-bold text-[#f43f5e]">No. SKL: ${d.no_skl}</div>` : ''}
                            </div>
                            <div class="pt-1 text-center">
                                <a href="{{ route('login') }}" class="inline-flex items-center gap-1 text-xs font-bold text-[#f43f5e] hover:underline">
                                    Login ke Akun Siswa untuk Mengunduh Lembar SKL & Panduan Daftar Ulang &rarr;
                                </a>
                            </div>
                        </div>
                    `;
                } else {
                    resultContent.innerHTML = `
                        <div class="p-4 rounded-2xl bg-rose-50 border border-rose-200 text-rose-700 text-xs flex items-center gap-3">
                            <i data-lucide="alert-triangle" class="w-5 h-5 shrink-0 text-rose-500"></i>
                            <div>${res.message}</div>
                        </div>
                    `;
                }
            } catch (err) {
                resultContent.innerHTML = `
                    <div class="p-4 rounded-2xl bg-rose-50 border border-rose-200 text-rose-700 text-xs">
                        Terjadi kesalahan saat memeriksa data. Silakan periksa koneksi Anda dan coba lagi.
                    </div>
                `;
            } finally {
                btnSubmit.disabled = false;
                btnSubmit.innerHTML = `<i data-lucide="search" class="w-4 h-4"></i><span>Cek Status</span>`;
                lucide.createIcons();
            }
        });
    }
</script>
@endpush
