@extends('layouts.app')

@section('title', 'PPDB Online SMK Islam Bina Khalifah Bangsa Kota Metro T.A 2026/2027')

@section('content')

    <!-- ========================================================================= -->
    <!-- HERO SECTION (PERSIS GAMBAR REFERENSI: TAMPILAN BERANDA UTAMA)            -->
    <!-- ========================================================================= -->
    <section class="hero-campus-um pt-8 md:pt-12 pb-14 md:pb-20 text-white border-b border-navy-800">
        <div class="max-w-[1600px] mx-auto px-4 sm:px-8 lg:px-12 xl:px-16">
            <div class="max-w-2xl lg:max-w-2xl xl:max-w-3xl space-y-5 text-left">
                
                <!-- Headline Persis Gambar Referensi (4 Baris Utuh, '& Siap' Tidak Terpotong) -->
                <h1 class="text-3xl sm:text-4xl md:text-5xl lg:text-[46px] xl:text-[52px] font-black tracking-tight leading-[1.12]">
                    <span class="text-white">PPDB </span><span class="text-[#fa2f79]">2026/2027</span><br>
                    <span class="text-white">Wujudkan Generasi</span><br>
                    <span class="text-[#fa2f79]">Islam, Terampilan,</span> <span class="text-white whitespace-nowrap">& Siap</span><br>
                    <span class="text-white">Kerja.</span>
                </h1>

                <!-- Deskripsi Sambutan (3 Baris Proporsional) -->
                <p class="text-xs sm:text-sm md:text-base text-slate-200 font-normal leading-relaxed max-w-xl">
                    Selamat datang di Portal Resmi Penerimaan Peserta Didik Baru (PPDB) SMK Islam Bina Khalifah Bangsa Kota Metro, Lampung. Pilihan terbaik pendidikan vokasi berlandaskan Al-Qur'an dan teknologi industri modern.
                </p>

                <!-- Gelombang Countdown Box (Persis Desain Floating Glass Card Referensi) -->
                <div class="p-4 sm:p-5 rounded-2xl bg-[#161c2d]/75 border border-white/10 backdrop-blur-md max-w-lg shadow-2xl space-y-3">
                    <div class="flex items-center justify-between text-xs sm:text-sm">
                        <span class="font-medium text-[#fa2f79] flex items-center gap-1.5">
                            <i data-lucide="clock" class="w-4 h-4 text-[#fa2f79]"></i>
                            <span>{{ $gelombangAktif->nama_gelombang ?? 'Gelombang 1 (Early Bird)' }}</span>
                        </span>
                        <span class="text-slate-200 text-xs sm:text-sm">Berakhir : <strong class="text-white font-semibold">{{ $gelombangAktif ? $gelombangAktif->tgl_tutup->translatedFormat('d F Y') : '02 januari 2027' }}</strong></span>
                    </div>

                    <div class="grid grid-cols-4 gap-2 sm:gap-3 text-center" id="countdown-timer" data-target="{{ $gelombangAktif ? $gelombangAktif->tgl_tutup->format('Y-m-d 23:59:59') : '2027-01-02 23:59:59' }}">
                        <div class="bg-[#0c1222]/85 rounded-xl py-3 px-2 border border-white/5 shadow-inner">
                            <div class="text-2xl sm:text-3xl font-black text-white leading-tight" id="cd-days">50</div>
                            <div class="text-[10px] sm:text-[11px] font-bold text-white uppercase tracking-wider mt-1">HARI</div>
                        </div>
                        <div class="bg-[#0c1222]/85 rounded-xl py-3 px-2 border border-white/5 shadow-inner">
                            <div class="text-2xl sm:text-3xl font-black text-white leading-tight" id="cd-hours">4</div>
                            <div class="text-[10px] sm:text-[11px] font-bold text-white uppercase tracking-wider mt-1">JAM</div>
                        </div>
                        <div class="bg-[#0c1222]/85 rounded-xl py-3 px-2 border border-white/5 shadow-inner">
                            <div class="text-2xl sm:text-3xl font-black text-white leading-tight" id="cd-mins">11</div>
                            <div class="text-[10px] sm:text-[11px] font-bold text-white uppercase tracking-wider mt-1">MENIT</div>
                        </div>
                        <div class="bg-[#0c1222]/85 rounded-xl py-3 px-2 border border-white/5 shadow-inner">
                            <div class="text-2xl sm:text-3xl font-black text-white leading-tight" id="cd-secs">11</div>
                            <div class="text-[10px] sm:text-[11px] font-bold text-white uppercase tracking-wider mt-1">DETIK</div>
                        </div>
                    </div>

                    <div class="text-xs sm:text-sm text-slate-200 font-normal pt-0.5">
                        Biaya Formulir : <strong class="text-white font-bold">Rp {{ $gelombangAktif ? number_format($gelombangAktif->biaya_pendaftaran, 0, ',', '.') : '100.000' }}</strong>
                    </div>
                </div>

                <!-- CTAs (Tombol Aksi: Solid Pink & Translucent Outline Sesuai Gambar) -->
                <div class="flex flex-wrap items-center gap-3 sm:gap-4 pt-1">
                    <a href="{{ route('register') }}" class="px-7 py-3.5 rounded-2xl bg-[#fa2f79] hover:bg-[#e11d48] text-white font-bold text-sm sm:text-base shadow-lg shadow-pink-500/25 transition-all text-center flex items-center justify-center gap-2">
                        <i data-lucide="user-plus" class="w-5 h-5"></i>
                        <span>Daftar Sekarang</span>
                    </a>
                    <a href="#cek-kelulusan" class="px-6 py-3.5 rounded-2xl bg-[#161c2d]/75 hover:bg-white/10 text-white font-semibold text-sm sm:text-base border border-white/20 transition-all text-center flex items-center justify-center gap-2 backdrop-blur-md">
                        <i data-lucide="search" class="w-5 h-5 text-white"></i>
                        <span>Cek Status Kelulusan</span>
                    </a>
                </div>

            </div>
        </div>
    </section>

    <!-- ========================================================================= -->
    <!-- PROFIL SEKOLAH SECTION (PERSIS GAMBAR REFERENSI DENGAN FOTO & GRADASI)    -->
    <!-- ========================================================================= -->
    <section id="profil" class="py-16 md:py-20 text-white border-b border-navy-800" style="background-image: linear-gradient(90deg, rgba(8, 12, 28, 0.94) 0%, rgba(22, 14, 40, 0.88) 32%, rgba(120, 20, 80, 0.78) 68%, rgba(230, 25, 125, 0.72) 100%), url('{{ asset('images/bg-profil-sekolah.jpg') }}'); background-size: cover; background-position: center center; background-repeat: no-repeat;">
        <div class="max-w-[1600px] mx-auto px-4 sm:px-8 lg:px-12 xl:px-16">
            
            <!-- Section Header (Pill Putih + Judul Dwiwarna SMK Islam Pink) -->
            <div class="text-center max-w-3xl mx-auto mb-12 space-y-2">
                <div class="inline-block bg-[#f8bbd0] text-slate-900 px-7 py-2 rounded-full font-bold text-sm sm:text-base shadow-lg mb-2">
                    Profil Sekolah
                </div>
                <h2 class="text-3xl sm:text-4xl md:text-5xl font-black text-white tracking-tight">
                    SMK Islam <span class="text-[#fa2f79]">Bina Khalifah Bangsa</span>
                </h2>
                <p class="text-xs sm:text-sm md:text-base text-slate-200 font-normal">
                    Berakhlak Mulia, Kompeten, dan Siap Menghadapi Masa Depan.
                </p>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 lg:gap-8 items-start">
                
                <!-- Kolom Kiri: 3 Kartu Mandiri (Tentang Sekolah, Visi Sekolah, Misi Sekolah) -->
                <div class="lg:col-span-6 space-y-6">
                    
                    <!-- Kartu 1: Tentang Sekolah -->
                    <div class="bg-white rounded-2xl overflow-hidden shadow-2xl border border-white/20">
                        <div class="card-header-badge !py-3 !px-5">
                            <span class="font-black text-base sm:text-lg text-white">Tentang Sekolah</span>
                            <div class="pink-accent-slash"></div>
                        </div>
                        <div class="p-5 sm:p-6">
                            <div class="flex flex-col sm:flex-row gap-5 items-center sm:items-start">
                                <div class="w-full sm:w-48 shrink-0 text-center">
                                    <div class="rounded-xl overflow-hidden border border-slate-200 shadow-sm bg-slate-100">
                                        <img src="{{ asset('images/gedung-pink-campus.jpg') }}" alt="Gedung sekolah" class="w-full h-32 object-cover object-center">
                                    </div>
                                    <div class="mt-2 text-sm font-extrabold text-slate-900">Gedung sekolah</div>
                                </div>
                                <div class="text-xs sm:text-[13px] text-slate-800 leading-relaxed space-y-2.5">
                                    <p>
                                        <strong class="font-black text-slate-900">SMK Islam Bina Khalifah Bangsa</strong> adalah lembaga pendidikan menengah kejuruan yang berkomitmen mencetak generasi muda berakhlak mulia, berilmu pengetahuan, dan terampil di bidangnya.
                                    </p>
                                    <p>
                                        Dengan dukungan tenaga pendidik profesional serta fasilitas modern, kami mempersiapkan siswa untuk siap kerja maupun melanjutkan kuliah.
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Kartu 2: Visi Sekolah -->
                    <div class="bg-white rounded-2xl overflow-hidden shadow-2xl border border-white/20">
                        <div class="card-header-badge !py-3 !px-5">
                            <span class="font-black text-base sm:text-lg text-white">Visi Sekolah</span>
                            <div class="pink-accent-slash"></div>
                        </div>
                        <div class="p-5 sm:p-6 text-xs sm:text-sm text-slate-800 font-normal leading-relaxed">
                            "Menjadi Sekolah Menengah Kejuruan Islam yang Unggul, Berkarakter, Berdaya Saing, dan Berjiwa Islami."
                        </div>
                    </div>

                    <!-- Kartu 3: Tentang Sekolah (Misi) -->
                    <div class="bg-white rounded-2xl overflow-hidden shadow-2xl border border-white/20">
                        <div class="card-header-badge !py-3 !px-5">
                            <span class="font-black text-base sm:text-lg text-white">Tentang Sekolah</span>
                            <div class="pink-accent-slash"></div>
                        </div>
                        <div class="p-5 sm:p-6 text-xs sm:text-[13px] text-slate-800">
                            <ul class="space-y-2 list-disc list-inside leading-relaxed">
                                <li>Menyelenggarakan pendidikan berkualitas berlandaskan nilai Islam.</li>
                                <li>Membekali peserta didik dengan keterampilan siap kerja.</li>
                                <li>Menanamkan akhlak mulia dan kedisiplinan dalam kehidupan sehari-hari.</li>
                            </ul>
                        </div>
                    </div>

                </div>

                <!-- Kolom Kanan: 2 Kartu Mandiri (Identitas Sekolah & Keunggulan) -->
                <div class="lg:col-span-6 space-y-6">
                    
                    <!-- Kartu 1: Identitas Sekolah (Teks Tebal & Besar Persis Gambar) -->
                    <div class="bg-white rounded-2xl overflow-hidden shadow-2xl border border-white/20">
                        <div class="card-header-badge !py-3 !px-5">
                            <span class="font-black text-base sm:text-lg text-white">Identitas Sekolah</span>
                            <div class="pink-accent-slash"></div>
                        </div>
                        <div class="p-6 space-y-3.5 text-sm sm:text-base">
                            <div class="flex items-start">
                                <span class="w-36 sm:w-44 font-black text-slate-900 shrink-0">Nama Sekolah</span>
                                <span class="font-black text-slate-900 mr-2">:</span>
                                <span class="font-normal text-slate-900">SMK Islam Bina Khalifah Bangsa</span>
                            </div>
                            <div class="flex items-start">
                                <span class="w-36 sm:w-44 font-black text-slate-900 shrink-0">NPSN Resmi</span>
                                <span class="font-black text-slate-900 mr-2">:</span>
                                <span class="font-normal text-slate-900">70012345 / 69773555</span>
                            </div>
                            <div class="flex items-start">
                                <span class="w-36 sm:w-44 font-black text-slate-900 shrink-0">Status</span>
                                <span class="font-black text-slate-900 mr-2">:</span>
                                <span class="font-normal text-slate-900">Swasta</span>
                            </div>
                            <div class="flex items-start">
                                <span class="w-36 sm:w-44 font-black text-slate-900 shrink-0">Akreditasi</span>
                                <span class="font-black text-slate-900 mr-2">:</span>
                                <span class="font-normal text-slate-900">Terakreditasi</span>
                            </div>
                            <div class="flex items-start">
                                <span class="w-36 sm:w-44 font-black text-slate-900 shrink-0">Email</span>
                                <span class="font-black text-slate-900 mr-2">:</span>
                                <span class="font-normal text-slate-900">info@smkib.com</span>
                            </div>
                            <div class="flex items-start">
                                <span class="w-36 sm:w-44 font-black text-slate-900 shrink-0">Alamat</span>
                                <span class="font-black text-slate-900 mr-2">:</span>
                                <span class="font-normal text-slate-900 leading-snug">Jl. Pattimura No. 24 / Sukarno Hatta, Ganjar Asri, Metro Barat</span>
                            </div>
                        </div>
                    </div>

                    <!-- Kartu 2: Keunggulan (Persis Gambar) -->
                    <div class="bg-white rounded-2xl overflow-hidden shadow-2xl border border-white/20">
                        <div class="card-header-badge !py-3 !px-5">
                            <span class="font-black text-base sm:text-lg text-white">Keunggulan</span>
                            <div class="pink-accent-slash"></div>
                        </div>
                        <div class="p-6">
                            <ul class="space-y-4 text-xs sm:text-sm text-slate-800">
                                <li class="flex items-start gap-2.5">
                                    <span class="text-slate-900 font-bold text-base leading-none mt-0.5">•</span>
                                    <div>
                                        <strong class="font-black text-slate-900 block text-xs sm:text-sm">Integrasi Kurikulum Pesantren & Industri</strong>
                                        <span class="text-slate-600 text-xs leading-relaxed block mt-0.5">Pendidikan vokasi berstandar DU/DI diiringi hafalan Al-Qur'an (Tahfiz) dan kajian adab.</span>
                                    </div>
                                </li>
                                <li class="flex items-start gap-2.5">
                                    <span class="text-slate-900 font-bold text-base leading-none mt-0.5">•</span>
                                    <div>
                                        <strong class="font-black text-slate-900 block text-xs sm:text-sm">4 Jurusan Siap Kerja & Wirausaha</strong>
                                        <span class="text-slate-600 text-xs leading-relaxed block mt-0.5">Laboratorium Farmasi mandiri, Lab Jaringan Komputer, serta Bengkel Mobil & Motor standar pabrikan.</span>
                                    </div>
                                </li>
                                <li class="flex items-start gap-2.5">
                                    <span class="text-slate-900 font-bold text-base leading-none mt-0.5">•</span>
                                    <div>
                                        <strong class="font-black text-slate-900 block text-xs sm:text-sm">Jalur Prestasi & Keringanan</strong>
                                        <span class="text-slate-600 text-xs leading-relaxed block mt-0.5">BiayaApresiasi bagi calon siswa penghafal Al-Qur'an (Tahfiz), juara seni musik tradisional, dan atlet bela diri.</span>
                                    </div>
                                </li>
                            </ul>
                        </div>
                    </div>

                </div>

            </div>
        </div>
    </section>

    <!-- ========================================================================= -->
    <!-- PROGRAM KEAHLIAN SECTION (PERSIS GAMBAR REFERENSI DENGAN FOTO & KARTU PUTIH) -->
    <!-- ========================================================================= -->
    <section id="jurusan" class="py-16 md:py-20 text-white border-b border-navy-800" style="background-image: linear-gradient(90deg, rgba(8, 12, 28, 0.94) 0%, rgba(22, 14, 40, 0.88) 32%, rgba(120, 20, 80, 0.78) 68%, rgba(230, 25, 125, 0.72) 100%), url('{{ asset('images/bg-profil-sekolah.jpg') }}'); background-size: cover; background-position: center center; background-repeat: no-repeat;">
        <div class="max-w-[1600px] mx-auto px-4 sm:px-8 lg:px-12 xl:px-16">
            
            <!-- Section Header (Pill Pink Lembut + Judul Dwiwarna) -->
            <div class="text-center max-w-3xl mx-auto mb-12 space-y-2">
                <div class="inline-block bg-[#f8bbd0] text-slate-900 px-7 py-2 rounded-full font-bold text-sm sm:text-base shadow-lg mb-2">
                    Kompetensi Keahlian Unggulan
                </div>
                <h2 class="text-3xl sm:text-4xl md:text-5xl font-black text-white tracking-tight">
                    Pilihan <span class="text-[#fa2f79]">4 Program Keahlian SMK</span>
                </h2>
                <p class="text-xs sm:text-sm md:text-base text-slate-200 font-normal">
                    Dirancang khusus untuk memenuhi kebutuhan dunia usaha dan dunia industri (DUDI)
                </p>
            </div>

            <!-- Grid 4 Kartu Jurusan Putih Elegan -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 items-stretch">
                
                <!-- Jurusan 1: Farmasi -->
                <div class="bg-white rounded-2xl overflow-hidden shadow-2xl border border-white/20 flex flex-col justify-between">
                    <div class="card-header-badge !py-3 !px-5">
                        <span class="font-black text-base sm:text-lg text-white">KODE: FJR</span>
                        <div class="pink-accent-slash"></div>
                    </div>
                    <div class="p-6 flex flex-col justify-between flex-grow">
                        <div class="space-y-3">
                            <h3 class="text-base sm:text-lg font-black text-slate-900 leading-snug">Farmasi Klinis & Komunitas</h3>
                            <p class="text-xs sm:text-[13px] text-slate-700 leading-relaxed">
                                Mempersiapkan siswa menjadi tenaga profesional di bidang farmasi klinis dan apotek obat.
                            </p>
                        </div>
                        <div class="pt-5 border-t border-slate-100 flex items-center justify-between mt-6">
                            <span class="font-bold text-slate-900 text-xs sm:text-sm">Kuota:</span>
                            <span class="px-3.5 py-1 rounded-full bg-[#fa2f79] text-white font-black text-xs sm:text-sm shadow-md">72 Siswa</span>
                        </div>
                    </div>
                </div>

                <!-- Jurusan 2: TKJ -->
                <div class="bg-white rounded-2xl overflow-hidden shadow-2xl border border-white/20 flex flex-col justify-between">
                    <div class="card-header-badge !py-3 !px-5">
                        <span class="font-black text-base sm:text-lg text-white">KODE: TKJ</span>
                        <div class="pink-accent-slash"></div>
                    </div>
                    <div class="p-6 flex flex-col justify-between flex-grow">
                        <div class="space-y-3">
                            <h3 class="text-base sm:text-lg font-black text-slate-900 leading-snug">Teknik Komputer & Jaringan</h3>
                            <p class="text-xs sm:text-[13px] text-slate-700 leading-relaxed">
                                Fokus pada teknologi informasi, jaringan komputer, server, dan pengelolaan sistem IT.
                            </p>
                        </div>
                        <div class="pt-5 border-t border-slate-100 flex items-center justify-between mt-6">
                            <span class="font-bold text-slate-900 text-xs sm:text-sm">Kuota:</span>
                            <span class="px-3.5 py-1 rounded-full bg-[#fa2f79] text-white font-black text-xs sm:text-sm shadow-md">72 Siswa</span>
                        </div>
                    </div>
                </div>

                <!-- Jurusan 3: TKR -->
                <div class="bg-white rounded-2xl overflow-hidden shadow-2xl border border-white/20 flex flex-col justify-between">
                    <div class="card-header-badge !py-3 !px-5">
                        <span class="font-black text-base sm:text-lg text-white">KODE: TKR</span>
                        <div class="pink-accent-slash"></div>
                    </div>
                    <div class="p-6 flex flex-col justify-between flex-grow">
                        <div class="space-y-3">
                            <h3 class="text-base sm:text-lg font-black text-slate-900 leading-snug">Teknik Kendaraan Ringan</h3>
                            <p class="text-xs sm:text-[13px] text-slate-700 leading-relaxed">
                                Membekali siswa dengan keterampilan perawatan dan perbaikan kendaraan ringan mobil modern.
                            </p>
                        </div>
                        <div class="pt-5 border-t border-slate-100 flex items-center justify-between mt-6">
                            <span class="font-bold text-slate-900 text-xs sm:text-sm">Kuota:</span>
                            <span class="px-3.5 py-1 rounded-full bg-[#fa2f79] text-white font-black text-xs sm:text-sm shadow-md">72 Siswa</span>
                        </div>
                    </div>
                </div>

                <!-- Jurusan 4: TBSM -->
                <div class="bg-white rounded-2xl overflow-hidden shadow-2xl border border-white/20 flex flex-col justify-between">
                    <div class="card-header-badge !py-3 !px-5">
                        <span class="font-black text-base sm:text-lg text-white">KODE: TBSM</span>
                        <div class="pink-accent-slash"></div>
                    </div>
                    <div class="p-6 flex flex-col justify-between flex-grow">
                        <div class="space-y-3">
                            <h3 class="text-base sm:text-lg font-black text-slate-900 leading-snug">Teknik Bisnis Sepeda Motor</h3>
                            <p class="text-xs sm:text-[13px] text-slate-700 leading-relaxed">
                                Menyiapkan siswa di bidang teknik sepeda motor standar pabrik dan wirausaha bengkel otomotif.
                            </p>
                        </div>
                        <div class="pt-5 border-t border-slate-100 flex items-center justify-between mt-6">
                            <span class="font-bold text-slate-900 text-xs sm:text-sm">Kuota:</span>
                            <span class="px-3.5 py-1 rounded-full bg-[#fa2f79] text-white font-black text-xs sm:text-sm shadow-md">72 Siswa</span>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </section>

    <!-- ========================================================================= -->
    <!-- EKSTRAKURIKULER SECTION (PERSIS GAMBAR REFERENSI DENGAN FOTO & KARTU TRANSPARAN) -->
    <!-- ========================================================================= -->
    <section id="ekskul" class="py-16 md:py-20 text-white border-b border-navy-800" style="background-image: linear-gradient(90deg, rgba(8, 12, 28, 0.94) 0%, rgba(22, 14, 40, 0.88) 32%, rgba(120, 20, 80, 0.78) 68%, rgba(230, 25, 125, 0.72) 100%), url('{{ asset('images/bg-profil-sekolah.jpg') }}'); background-size: cover; background-position: center center; background-repeat: no-repeat;">
        <div class="max-w-[1600px] mx-auto px-4 sm:px-8 lg:px-12 xl:px-16">
            
            <!-- Section Header (Pill Pink Lembut + Judul Dwiwarna) -->
            <div class="text-center max-w-3xl mx-auto mb-12 space-y-2">
                <div class="inline-block bg-[#f8bbd0] text-slate-900 px-7 py-2 rounded-full font-bold text-sm sm:text-base shadow-lg mb-2">
                    Pengembangan Bakat & Karakter
                </div>
                <h2 class="text-3xl sm:text-4xl md:text-5xl font-black text-white tracking-tight">
                    6 Ekstrakurikuler <span class="text-[#fa2f79]">Unggulan</span>
                </h2>
                <p class="text-xs sm:text-sm md:text-base text-slate-200 font-normal max-w-2xl mx-auto leading-relaxed">
                    Menumbuhkan kecerdasan,ampilitu,kemandirian,dan kepemimpinan, serta kecintaan pada wawasan budaya dan lingkungan.
                </p>
            </div>

            <!-- Grid 6 Kartu Ekskul Elegan Transparan Tanpa Ikon -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                
                <!-- Ekskul 1 -->
                <div class="p-6 rounded-2xl bg-[#0d1630]/85 backdrop-blur-md border border-white/10 shadow-xl transition-all hover:border-[#fa2f79]/50 flex flex-col justify-center">
                    <h3 class="text-base sm:text-lg font-black text-white mb-2 leading-snug">Tilawatil Al-Qur'an</h3>
                    <p class="text-xs sm:text-sm text-slate-200 leading-relaxed">
                        Meningkatkan kemampuan membaca tajwid Al-Qur'an.
                    </p>
                </div>

                <!-- Ekskul 2 -->
                <div class="p-6 rounded-2xl bg-[#0d1630]/85 backdrop-blur-md border border-white/10 shadow-xl transition-all hover:border-[#fa2f79]/50 flex flex-col justify-center">
                    <h3 class="text-base sm:text-lg font-black text-white mb-2 leading-snug">Tahfidzul Qur'an</h3>
                    <p class="text-xs sm:text-sm text-slate-200 leading-relaxed">
                        Program unggulan hafalan dan pemahaman ayat.
                    </p>
                </div>

                <!-- Ekskul 3 -->
                <div class="p-6 rounded-2xl bg-[#0d1630]/85 backdrop-blur-md border border-white/10 shadow-xl transition-all hover:border-[#fa2f79]/50 flex flex-col justify-center">
                    <h3 class="text-base sm:text-lg font-black text-white mb-2 leading-snug">Pramuka (Gugus SMK IB)</h3>
                    <p class="text-xs sm:text-sm text-slate-200 leading-relaxed">
                        Menanamkan disiplin, kemandirian, dan kerja sama.
                    </p>
                </div>

                <!-- Ekskul 4 -->
                <div class="p-6 rounded-2xl bg-[#0d1630]/85 backdrop-blur-md border border-white/10 shadow-xl transition-all hover:border-[#fa2f79]/50 flex flex-col justify-center">
                    <h3 class="text-base sm:text-lg font-black text-white mb-2 leading-snug">Paskibra</h3>
                    <p class="text-xs sm:text-sm text-slate-200 leading-relaxed">
                        Kedisiplinan, baris-berbaris, dan jiwa nasionalisme.
                    </p>
                </div>

                <!-- Ekskul 5 -->
                <div class="p-6 rounded-2xl bg-[#0d1630]/85 backdrop-blur-md border border-white/10 shadow-xl transition-all hover:border-[#fa2f79]/50 flex flex-col justify-center">
                    <h3 class="text-base sm:text-lg font-black text-white mb-2 leading-snug">Karate</h3>
                    <p class="text-xs sm:text-sm text-slate-200 leading-relaxed">
                        Melatih fisik, mental, dan teknik bela diri.
                    </p>
                </div>

                <!-- Ekskul 6 -->
                <div class="p-6 rounded-2xl bg-[#0d1630]/85 backdrop-blur-md border border-white/10 shadow-xl transition-all hover:border-[#fa2f79]/50 flex flex-col justify-center">
                    <h3 class="text-base sm:text-lg font-black text-white mb-2 leading-snug">Gamelan Karawitan</h3>
                    <p class="text-xs sm:text-sm text-slate-200 leading-relaxed">
                        Melestarikan budaya daerah melalui seni gamelan.
                    </p>
                </div>

            </div>
        </div>
    </section>

    <!-- ========================================================================= -->
    <!-- ALUR 6 TAHAP PPDB SECTION (PERSIS GAMBAR REFERENSI DENGAN FOTO & CONTAINER GLASS) -->
    <!-- ========================================================================= -->
    <section id="alur" class="py-16 md:py-20 text-white border-b border-navy-800" style="background-image: linear-gradient(90deg, rgba(8, 12, 28, 0.94) 0%, rgba(22, 14, 40, 0.88) 32%, rgba(120, 20, 80, 0.78) 68%, rgba(230, 25, 125, 0.72) 100%), url('{{ asset('images/bg-profil-sekolah.jpg') }}'); background-size: cover; background-position: center center; background-repeat: no-repeat;">
        <div class="max-w-[1600px] mx-auto px-4 sm:px-8 lg:px-12 xl:px-16">
            
            <!-- Section Header (Pill Pink Lembut + Judul Dwiwarna) -->
            <div class="text-center max-w-3xl mx-auto mb-12 space-y-2">
                <div class="inline-block bg-[#f8bbd0] text-slate-900 px-7 py-2 rounded-full font-bold text-sm sm:text-base shadow-lg mb-2">
                    Panduan Pendaftaran
                </div>
                <h2 class="text-3xl sm:text-4xl md:text-5xl font-black text-white tracking-tight">
                    Alur 6 Tahap <span class="text-[#fa2f79]">PPDB Online</span>
                </h2>
                <p class="text-xs sm:text-sm md:text-base text-slate-200 font-normal max-w-2xl mx-auto leading-relaxed">
                    Ikuti langkah-langkah berikut untuk melakukan pendaftaran secara online dengan mudah dan benar.
                </p>
            </div>

            <!-- Wadah Besar Navy Glass Container Memayungi 6 Kartu Putih -->
            <div class="p-6 sm:p-8 rounded-3xl bg-[#0c1838]/80 backdrop-blur-md border border-white/10 shadow-2xl">
                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4 sm:gap-5 text-center">
                    
                    <!-- Tahap 1 -->
                    <div class="p-5 sm:p-6 rounded-2xl bg-white text-center shadow-lg border border-slate-100 flex flex-col justify-start h-full">
                        <div class="w-12 h-12 rounded-full bg-[#fa2f79] text-white font-black text-lg flex items-center justify-center mx-auto mb-4 shadow-md">
                            1
                        </div>
                        <h4 class="text-sm sm:text-base font-black text-slate-900 mb-2 leading-snug">Buat Akun</h4>
                        <p class="text-xs text-slate-600 leading-relaxed font-normal">Daftar dengan NISN 10 digit, Username, dan Email aktif.</p>
                    </div>

                    <!-- Tahap 2 -->
                    <div class="p-5 sm:p-6 rounded-2xl bg-white text-center shadow-lg border border-slate-100 flex flex-col justify-start h-full">
                        <div class="w-12 h-12 rounded-full bg-[#fa2f79] text-white font-black text-lg flex items-center justify-center mx-auto mb-4 shadow-md">
                            2
                        </div>
                        <h4 class="text-sm sm:text-base font-black text-slate-900 mb-2 leading-snug">Isi Formulir</h4>
                        <p class="text-xs text-slate-600 leading-relaxed font-normal">Lengkapi biodata diri, pilihan jurusan 1 & 2, ukuran seragam & asal sekolah.</p>
                    </div>

                    <!-- Tahap 3 -->
                    <div class="p-5 sm:p-6 rounded-2xl bg-white text-center shadow-lg border border-slate-100 flex flex-col justify-start h-full">
                        <div class="w-12 h-12 rounded-full bg-[#fa2f79] text-white font-black text-lg flex items-center justify-center mx-auto mb-4 shadow-md">
                            3
                        </div>
                        <h4 class="text-sm sm:text-base font-black text-slate-900 mb-2 leading-snug">Pembayaran</h4>
                        <p class="text-xs text-slate-600 leading-relaxed font-normal">Bayar via QRIS/VA Midtrans atau bayar tunai di Loket TU.</p>
                    </div>

                    <!-- Tahap 4 -->
                    <div class="p-5 sm:p-6 rounded-2xl bg-white text-center shadow-lg border border-slate-100 flex flex-col justify-start h-full">
                        <div class="w-12 h-12 rounded-full bg-[#fa2f79] text-white font-black text-lg flex items-center justify-center mx-auto mb-4 shadow-md">
                            4
                        </div>
                        <h4 class="text-sm sm:text-base font-black text-slate-900 mb-2 leading-snug">Upload Berkas</h4>
                        <p class="text-xs text-slate-600 leading-relaxed font-normal">Unggah scan Ijazah, KK, Akta, Pas Foto, dan Sertifikat Prestasi.</p>
                    </div>

                    <!-- Tahap 5 -->
                    <div class="p-5 sm:p-6 rounded-2xl bg-white text-center shadow-lg border border-slate-100 flex flex-col justify-start h-full">
                        <div class="w-12 h-12 rounded-full bg-[#fa2f79] text-white font-black text-lg flex items-center justify-center mx-auto mb-4 shadow-md">
                            5
                        </div>
                        <h4 class="text-sm sm:text-base font-black text-slate-900 mb-2 leading-snug">Tes & Seleksi</h4>
                        <p class="text-xs text-slate-600 leading-relaxed font-normal">Cetak Kartu Ujian dan ikuti tes wawancara & tes buta warna.</p>
                    </div>

                    <!-- Tahap 6 -->
                    <div class="p-5 sm:p-6 rounded-2xl bg-white text-center shadow-lg border border-slate-100 flex flex-col justify-start h-full">
                        <div class="w-12 h-12 rounded-full bg-[#fa2f79] text-white font-black text-lg flex items-center justify-center mx-auto mb-4 shadow-md">
                            6
                        </div>
                        <h4 class="text-sm sm:text-base font-black text-slate-900 mb-2 leading-snug">Daftar Ulang</h4>
                        <p class="text-xs text-slate-600 leading-relaxed font-normal">Unduh SKL resmi Kepala Sekolah dan konfirmasi masuk.</p>
                    </div>

                </div>
            </div>
        </div>
    </section>

    <!-- ========================================================================= -->
    <!-- CEK KELULUSAN SECTION (PERSIS GAMBAR REFERENSI DENGAN FOTO & CONTAINER GLASS) -->
    <!-- ========================================================================= -->
    <section id="cek-kelulusan" class="py-16 md:py-20 text-white border-b border-navy-800" style="background-image: linear-gradient(90deg, rgba(8, 12, 28, 0.94) 0%, rgba(22, 14, 40, 0.88) 32%, rgba(120, 20, 80, 0.78) 68%, rgba(230, 25, 125, 0.72) 100%), url('{{ asset('images/bg-profil-sekolah.jpg') }}'); background-size: cover; background-position: center center; background-repeat: no-repeat;">
        <div class="max-w-[1600px] mx-auto px-4 sm:px-8 lg:px-12 xl:px-16">
            
            <!-- Section Header (Pill Pink Lembut + Judul Dwiwarna) -->
            <div class="text-center max-w-5xl mx-auto mb-10 space-y-2">
                <div class="inline-block bg-[#f8bbd0] text-slate-900 px-7 py-2 rounded-full font-bold text-sm sm:text-base shadow-lg mb-2">
                    Pantau Hasil Seleksi
                </div>
                <h2 class="text-2xl sm:text-3xl md:text-4xl lg:text-[42px] xl:text-5xl font-black text-white tracking-tight leading-tight">
                    Cek Status Hasil Seleksi <span class="whitespace-nowrap text-[#fa2f79]">PPDB Online</span>
                </h2>
                <p class="text-xs sm:text-sm md:text-base text-slate-200 font-normal max-w-2xl mx-auto leading-relaxed">
                    Masukan kode pendaftaran Anda unttuk mengetahui hasil seleksi PPDB di SMK Islam Bina Khalifah Bangsa
                </p>
            </div>

            <!-- Wadah Besar Navy Glass Container Memayungi Search Form -->
            <div class="max-w-4xl mx-auto p-6 sm:p-10 rounded-3xl bg-[#0c1838]/85 backdrop-blur-md border border-white/10 shadow-2xl">
                <form id="form-cek-kelulusan" class="w-full">
                    <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3">
                        
                        <!-- Kolom Input Kapsul Putih dengan Ikon Kaca Pembesar di Kiri -->
                        <div class="relative flex-grow flex items-center bg-white rounded-full px-5 py-1.5 shadow-md border border-slate-100">
                            <i data-lucide="search" class="w-6 h-6 text-slate-800 shrink-0 mr-3"></i>
                            <input type="text" id="input-nisn" name="nisn" required placeholder="Contoh; 202611060001 atau NISN Anda" class="w-full py-2.5 bg-transparent text-slate-900 text-sm sm:text-base font-semibold focus:outline-none placeholder:text-slate-500">
                        </div>

                        <!-- Tombol Cek Status Pink -->
                        <button type="submit" id="btn-submit-cek" class="px-8 py-3.5 rounded-full bg-[#fa2f79] hover:bg-[#e11d48] text-white font-bold text-sm sm:text-base flex items-center justify-center gap-2 shrink-0 shadow-lg transition-all">
                            <i data-lucide="search" class="w-5 h-5"></i>
                            <span>Cek Status</span>
                        </button>
                    </div>

                    <!-- Pill Catatan Petunjuk di Bawah Input -->
                    <div class="mt-4 flex justify-start">
                        <div class="inline-flex items-center gap-2 bg-white/95 text-sky-800 text-xs sm:text-sm font-semibold px-4 py-1.5 rounded-full shadow-sm">
                            <i data-lucide="info" class="w-4 h-4 text-sky-600 shrink-0"></i>
                            <span>Pastikan Anda memasukkan kode pendaftaran dengan benar.</span>
                        </div>
                    </div>
                </form>

                <!-- Result Box -->
                <div id="hasil-cek-box" class="hidden mt-6 text-left w-full">
                    <div id="hasil-konten"></div>
                </div>
            </div>

        </div>
    </section>

    <!-- ========================================================================= -->
    <!-- TANYA JAWAB (FAQ) SECTION (PERSIS GAMBAR REFERENSI DENGAN FOTO & BAR ARROW) -->
    <!-- ========================================================================= -->
    <section id="faq" class="py-16 md:py-20 text-white border-b border-navy-800" style="background-image: linear-gradient(90deg, rgba(8, 12, 28, 0.94) 0%, rgba(22, 14, 40, 0.88) 32%, rgba(120, 20, 80, 0.78) 68%, rgba(230, 25, 125, 0.72) 100%), url('{{ asset('images/bg-profil-sekolah.jpg') }}'); background-size: cover; background-position: center center; background-repeat: no-repeat;">
        <div class="max-w-[1600px] mx-auto px-4 sm:px-8 lg:px-12 xl:px-16">
            
            <!-- Section Header (Pill Pink Lembut + Judul Dwiwarna) -->
            <div class="text-center max-w-4xl mx-auto mb-10 space-y-2">
                <div class="inline-block bg-[#f8bbd0] text-slate-900 px-7 py-2 rounded-full font-black text-sm sm:text-base tracking-wider shadow-lg mb-2">
                    PERTANYAAN UMUM
                </div>
                <h2 class="text-3xl sm:text-4xl md:text-5xl font-black text-white tracking-tight leading-tight">
                    Tanya Jawab (FAQ) <span class="whitespace-nowrap text-[#fa2f79]">PPDB Online</span>
                </h2>
                <p class="text-xs sm:text-sm md:text-base text-slate-200 font-normal max-w-2xl mx-auto leading-relaxed">
                    Jawaban atas pertanyaan yang sering di tanyakan seputar pendaftaran PPDB di SMK Bina Khalifah Bangsa
                </p>
            </div>

            <!-- List Bar FAQ Lebar Navy Elegan dengan Panah Arrow Down Putih -->
            <div class="max-w-4xl mx-auto space-y-4">
                
                <!-- FAQ 1 -->
                <div class="px-6 py-4 rounded-2xl bg-[#0c1838]/90 backdrop-blur-md border border-white/10 shadow-lg flex items-center justify-between cursor-pointer hover:border-[#fa2f79]/50 transition-all">
                    <span class="text-xs sm:text-sm md:text-base font-bold text-white">Apakah pendaftran PPDB ini dilakukan secara online?</span>
                    <i data-lucide="arrow-down" class="w-5 h-5 text-white shrink-0 ml-4 stroke-[2.5]"></i>
                </div>

                <!-- FAQ 2 -->
                <div class="px-6 py-4 rounded-2xl bg-[#0c1838]/90 backdrop-blur-md border border-white/10 shadow-lg flex items-center justify-between cursor-pointer hover:border-[#fa2f79]/50 transition-all">
                    <span class="text-xs sm:text-sm md:text-base font-bold text-white">Bagaimana jika jurusan pilihan pertama saya penuh?</span>
                    <i data-lucide="arrow-down" class="w-5 h-5 text-white shrink-0 ml-4 stroke-[2.5]"></i>
                </div>

                <!-- FAQ 3 -->
                <div class="px-6 py-4 rounded-2xl bg-[#0c1838]/90 backdrop-blur-md border border-white/10 shadow-lg flex items-center justify-between cursor-pointer hover:border-[#fa2f79]/50 transition-all">
                    <span class="text-xs sm:text-sm md:text-base font-bold text-white">Apa saja tes seleksi yang diadakan di SMK Islam Bina Khalifah Bangsa?</span>
                    <i data-lucide="arrow-down" class="w-5 h-5 text-white shrink-0 ml-4 stroke-[2.5]"></i>
                </div>

                <!-- FAQ 4 -->
                <div class="px-6 py-4 rounded-2xl bg-[#0c1838]/90 backdrop-blur-md border border-white/10 shadow-lg flex items-center justify-between cursor-pointer hover:border-[#fa2f79]/50 transition-all">
                    <span class="text-xs sm:text-sm md:text-base font-bold text-white">Apakah ada beasiswa khusus untuk Tahfiz-Qur'an?</span>
                    <i data-lucide="arrow-down" class="w-5 h-5 text-white shrink-0 ml-4 stroke-[2.5]"></i>
                </div>

            </div>

        </div>
    </section>

    <!-- ========================================================================= -->
    <!-- LOKASI SEKOLAH (PERSIS GAMBAR REFERENSI DENGAN GRADIENT & GOOGLE MAPS)    -->
    <!-- ========================================================================= -->
    <section class="py-16 md:py-20 text-white border-b border-navy-800" style="background: linear-gradient(135deg, #100b26 0%, #290c37 32%, #6e1452 70%, #9e1460 100%);">
        <div class="max-w-[1600px] mx-auto px-4 sm:px-8 lg:px-12 xl:px-16">
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 lg:gap-12 items-center">
                
                <!-- Kolom Kiri: Info & Kontak -->
                <div class="lg:col-span-6 space-y-5">
                    
                    <!-- Badge Pill Soft Pink -->
                    <div class="inline-flex items-center gap-2 bg-[#f8bbd0] text-slate-900 px-6 py-2 rounded-full font-black text-xs sm:text-sm tracking-wider shadow-lg">
                        <i data-lucide="map-pin" class="w-4 h-4 text-slate-900 shrink-0"></i>
                        <span>LOKASI SEKOLAH</span>
                    </div>

                    <!-- Judul Dwiwarna -->
                    <h2 class="text-3xl sm:text-4xl md:text-5xl font-black text-white tracking-tight leading-tight">
                        Kunjungi SMK Islam<br>
                        <span class="text-[#fa2f79]">Bina Khalifah Bangsa</span>
                    </h2>

                    <!-- Paragraf Deskripsi -->
                    <p class="text-xs sm:text-sm text-slate-200 leading-relaxed max-w-xl">
                        Terletak strategis di Kota Metro, Lampung. Memiliki lingkungan belajar yang asri, sarana ibadah luas, dan fasilitas praktik bersetandar untuk kenyamanan belajar siswa
                    </p>

                    <!-- Daftar Kontak dengan Lingkaran Pink Solid -->
                    <div class="space-y-3.5 text-xs sm:text-sm text-white pt-2">
                        <div class="flex items-center gap-3">
                            <div class="w-7 h-7 rounded-full bg-[#fa2f79] text-white flex items-center justify-center shrink-0 shadow-sm">
                                <i data-lucide="map-pin" class="w-3.5 h-3.5"></i>
                            </div>
                            <span class="font-normal text-slate-100">Jl. Pattimura No.24, Ganjar Asri, Kec. Metro Barat,Kota Metro Lampung 34125</span>
                        </div>
                        <div class="flex items-center gap-3">
                            <div class="w-7 h-7 rounded-full bg-[#fa2f79] text-white flex items-center justify-center shrink-0 shadow-sm">
                                <i data-lucide="clock" class="w-3.5 h-3.5"></i>
                            </div>
                            <span class="font-normal text-slate-100">Pelayanan loket PPDB Senin-Sabtu 08.00-15.00 WIB</span>
                        </div>
                        <div class="flex items-center gap-3">
                            <div class="w-7 h-7 rounded-full bg-[#fa2f79] text-white flex items-center justify-center shrink-0 shadow-sm">
                                <i data-lucide="phone" class="w-3.5 h-3.5"></i>
                            </div>
                            <span class="font-normal text-slate-100">CALL Center / WhatsApp; 0821-7890-1234</span>
                        </div>
                    </div>

                    <!-- Tombol Kapsul Putih Buka Google Maps -->
                    <div class="pt-2">
                        <a href="https://maps.google.com/?q=SMK+IB+KHALIFAH+BANGSA+METRO" target="_blank" rel="noopener noreferrer" class="inline-flex items-center gap-3 px-6 py-3 rounded-full bg-white text-slate-900 font-bold text-xs sm:text-sm shadow-xl hover:shadow-2xl hover:bg-slate-50 transition-all">
                            <i data-lucide="navigation" class="w-4 h-4 text-[#fa2f79] fill-[#fa2f79]"></i>
                            <span>Buka Google Maps</span>
                            <span class="text-slate-400 font-bold text-base">&gt;</span>
                        </a>
                    </div>

                </div>

                <!-- Kolom Kanan: Peta Google Maps Asli (Menampilkan Pin & Nama Resmi Bawaan Google) -->
                <div class="lg:col-span-6 rounded-2xl overflow-hidden shadow-2xl border-4 border-white/20 h-[340px] sm:h-[380px] bg-slate-900">
                    <iframe src="https://maps.google.com/maps?q=SMK+IB+KHALIFAH+BANGSA+METRO&amp;t=m&amp;z=17&amp;ie=UTF8&amp;iwloc=B&amp;output=embed" width="100%" height="100%" style="border:0;" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
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
        const targetAttr = timerContainer.getAttribute('data-target');
        const targetDate = new Date(targetAttr.replace(/-/g, '/')).getTime();

        const updateTimer = () => {
            const now = new Date().getTime();
            const difference = targetDate - now;

            if (difference > 0) {
                const days = Math.floor(difference / (1000 * 60 * 60 * 24));
                const hours = Math.floor((difference % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
                const minutes = Math.floor((difference % (1000 * 60 * 60)) / (1000 * 60));
                const seconds = Math.floor((difference % (1000 * 60)) / 1000);

                const elD = document.getElementById('cd-days');
                const elH = document.getElementById('cd-hours');
                const elM = document.getElementById('cd-mins');
                const elS = document.getElementById('cd-secs');

                if (elD) elD.innerText = String(days).padStart(2, '0');
                if (elH) elH.innerText = String(hours).padStart(2, '0');
                if (elM) elM.innerText = String(minutes).padStart(2, '0');
                if (elS) elS.innerText = String(seconds).padStart(2, '0');
            }
        };

        updateTimer();
        setInterval(updateTimer, 1000);
    }

    // Cek Kelulusan AJAX Logic
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
            btnSubmit.innerHTML = `<i data-lucide="loader-2" class="w-4 h-4 animate-spin"></i> Memeriksa...`;
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
                    let statusMsg = 'Sedang Dalam Proses Seleksi / Penilaian.';

                    if (d.status_kelulusan === 'Diterima') {
                        badgeClass = 'bg-emerald-100 text-emerald-800 border-emerald-300';
                        statusMsg = `Selamat! Anda Diterima di Jurusan <strong>${d.jurusan_diterima}</strong>.`;
                    } else if (d.status_kelulusan === 'Cadangan') {
                        badgeClass = 'bg-sky-100 text-sky-800 border-sky-300';
                        statusMsg = 'Anda Masuk dalam Daftar Cadangan / Waiting List.';
                    } else if (d.status_kelulusan === 'Tidak Lolos') {
                        badgeClass = 'bg-rose-100 text-rose-800 border-rose-300';
                        statusMsg = 'Mohon Maaf, Anda Belum Lolos Seleksi Tahun Ini.';
                    }

                    resultContent.innerHTML = `
                        <div class="p-5 rounded-2xl bg-white border border-slate-200 shadow-sm space-y-3">
                            <div class="flex items-center justify-between flex-wrap gap-2">
                                <span class="px-3 py-1 rounded-full border text-xs font-bold uppercase tracking-wider ${badgeClass}">
                                    ${d.status_kelulusan}
                                </span>
                                <span class="text-xs text-slate-500 font-mono">${d.no_pendaftaran}</span>
                            </div>
                            <div>
                                <h4 class="text-base font-bold text-navy-950">${d.nama}</h4>
                                <p class="text-xs text-slate-600">NISN: ${d.nisn} | Asal Sekolah: ${d.asal_sekolah}</p>
                            </div>
                            <div class="p-3 rounded-xl bg-slate-50 border border-slate-200 text-xs text-slate-700">
                                ${statusMsg}
                                ${d.no_skl ? `<div class="mt-1.5 pt-1.5 border-t border-slate-200 text-[11px] text-emerald-700 font-medium">No. SKL: ${d.no_skl}</div>` : ''}
                            </div>
                            <div class="pt-1 text-center">
                                <a href="{{ route('login') }}" class="text-xs text-brand-pink hover:underline font-semibold">Login ke Akun Siswa untuk Mengunduh Lembar SKL & Panduan Daftar Ulang &rarr;</a>
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
                        Terjadi kesalahan saat memeriksa data. Silakan coba lagi.
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
