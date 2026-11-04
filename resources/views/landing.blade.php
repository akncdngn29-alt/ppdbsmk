@extends('layouts.app')

@section('title', 'PPDB Online SMK Islam Bina Khalifah Bangsa Kota Metro T.A 2026/2027')

@section('content')

<!-- Hero Section -->
<section class="relative overflow-hidden bg-gradient-to-b from-emerald-900 via-slate-900 to-slate-900 text-white py-16 md:py-28">
    <!-- Background Islamic geometric / glow decoration -->
    <div class="absolute inset-0 opacity-10 bg-[radial-gradient(#10b981_1px,transparent_1px)] [background-size:16px_16px]"></div>
    <div class="absolute top-0 right-0 -mt-12 -mr-12 w-96 h-96 bg-emerald-500/20 rounded-full blur-3xl pointer-events-none"></div>

    <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-12 items-center">
            <!-- Left Text -->
            <div class="lg:col-span-7 space-y-6 text-center lg:text-left">
                <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-emerald-500/20 border border-emerald-500/30 text-emerald-300 text-xs font-bold tracking-wide uppercase">
                    <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                    Pendaftaran T.A 2026/2027 Telah Dibuka
                </div>

                <h1 class="text-3xl sm:text-4xl md:text-5xl lg:text-6xl font-extrabold tracking-tight leading-tight">
                    Wujudkan Generasi <span class="text-transparent bg-clip-text bg-gradient-to-r from-emerald-400 to-teal-300">Islami, Terampil,</span> & Siap Kerja.
                </h1>

                <p class="text-base sm:text-lg text-slate-300 max-w-2xl mx-auto lg:mx-0 font-normal leading-relaxed">
                    Selamat datang di Portal Resmi Penerimaan Peserta Didik Baru (PPDB) <strong class="text-white">SMK Islam Bina Khalifah Bangsa</strong> Kota Metro, Lampung. Pilihan terbaik pendidikan vokasi berlandaskan Al-Qur'an dan teknologi industri modern.
                </p>

                <!-- Gelombang Countdown Box -->
                @if($gelombangAktif)
                <div class="p-4 rounded-2xl bg-white/5 border border-white/10 backdrop-blur-md max-w-lg mx-auto lg:mx-0">
                    <div class="flex items-center justify-between text-xs text-slate-300 mb-2">
                        <span class="font-semibold text-emerald-400 flex items-center gap-1.5">
                            <i data-lucide="clock" class="w-4 h-4"></i> {{ $gelombangAktif->nama_gelombang }}
                        </span>
                        <span>Berakhir: <strong>{{ $gelombangAktif->tgl_tutup->translatedFormat('d F Y') }}</strong></span>
                    </div>
                    <div class="grid grid-cols-4 gap-2 text-center" id="countdown-timer" data-target="{{ $gelombangAktif->tgl_tutup->format('Y-m-d 23:59:59') }}">
                        <div class="bg-black/30 rounded-xl p-2 border border-white/5">
                            <div class="text-xl md:text-2xl font-extrabold text-white" id="cd-days">00</div>
                            <div class="text-[10px] text-slate-400 uppercase">Hari</div>
                        </div>
                        <div class="bg-black/30 rounded-xl p-2 border border-white/5">
                            <div class="text-xl md:text-2xl font-extrabold text-white" id="cd-hours">00</div>
                            <div class="text-[10px] text-slate-400 uppercase">Jam</div>
                        </div>
                        <div class="bg-black/30 rounded-xl p-2 border border-white/5">
                            <div class="text-xl md:text-2xl font-extrabold text-white" id="cd-mins">00</div>
                            <div class="text-[10px] text-slate-400 uppercase">Menit</div>
                        </div>
                        <div class="bg-black/30 rounded-xl p-2 border border-white/5">
                            <div class="text-xl md:text-2xl font-extrabold text-emerald-400" id="cd-secs">00</div>
                            <div class="text-[10px] text-slate-400 uppercase">Detik</div>
                        </div>
                    </div>
                    <div class="mt-2 text-[11px] text-slate-400 flex items-center justify-between">
                        <span>Biaya Formulir: <strong class="text-white">Rp {{ number_format($gelombangAktif->biaya_pendaftaran, 0, ',', '.') }}</strong></span>
                        <span class="text-emerald-400 font-medium">Tersedia Beasiswa Tahfiz</span>
                    </div>
                </div>
                @endif

                <!-- CTAs -->
                <div class="flex flex-col sm:flex-row items-center justify-center lg:justify-start gap-4 pt-2">
                    <a href="{{ route('register') }}" class="w-full sm:w-auto px-8 py-3.5 rounded-2xl bg-emerald-500 hover:bg-emerald-600 text-white font-bold text-sm sm:text-base shadow-lg shadow-emerald-500/30 hover:scale-105 transition-all text-center flex items-center justify-center gap-2">
                        <i data-lucide="user-plus" class="w-5 h-5"></i>
                        <span>Daftar Sekarang</span>
                    </a>
                    <a href="#cek-kelulusan" class="w-full sm:w-auto px-7 py-3.5 rounded-2xl bg-white/10 hover:bg-white/20 text-white border border-white/20 font-semibold text-sm sm:text-base backdrop-blur-md transition-all text-center flex items-center justify-center gap-2">
                        <i data-lucide="search" class="w-5 h-5 text-emerald-400"></i>
                        <span>Cek Status Kelulusan</span>
                    </a>
                </div>
            </div>

            <!-- Right Graphic / Highlights -->
            <div class="lg:col-span-5 space-y-4">
                <div class="relative bg-gradient-to-tr from-slate-800 to-slate-800/80 p-6 md:p-8 rounded-3xl border border-slate-700 shadow-2xl backdrop-blur-xl">
                    <h3 class="text-lg font-bold text-white mb-4 flex items-center gap-2">
                        <i data-lucide="sparkles" class="w-5 h-5 text-amber-400"></i> Keunggulan SMK IB Khalifah Bangsa
                    </h3>
                    <div class="space-y-3.5">
                        <div class="flex items-start gap-3 p-3 rounded-2xl bg-white/5 border border-white/5">
                            <div class="p-2 rounded-xl bg-emerald-500/20 text-emerald-400 shrink-0">
                                <i data-lucide="book-check" class="w-5 h-5"></i>
                            </div>
                            <div>
                                <h4 class="text-sm font-bold text-white">Integrasi Kurikulum Pesantren & Industri</h4>
                                <p class="text-xs text-slate-300">Pendidikan vokasi berstandar DU/DI diiringi hafalan Al-Qur'an (Tahfiz) dan kajian adab.</p>
                            </div>
                        </div>

                        <div class="flex items-start gap-3 p-3 rounded-2xl bg-white/5 border border-white/5">
                            <div class="p-2 rounded-xl bg-sky-500/20 text-sky-400 shrink-0">
                                <i data-lucide="briefcase" class="w-5 h-5"></i>
                            </div>
                            <div>
                                <h4 class="text-sm font-bold text-white">4 Jurusan Siap Kerja & Wirausaha</h4>
                                <p class="text-xs text-slate-300">Laboratorium Farmasi mandiri, Lab Jaringan Komputer, serta Bengkel Mobil & Motor standar pabrikan.</p>
                            </div>
                        </div>

                        <div class="flex items-start gap-3 p-3 rounded-2xl bg-white/5 border border-white/5">
                            <div class="p-2 rounded-xl bg-amber-500/20 text-amber-400 shrink-0">
                                <i data-lucide="award" class="w-5 h-5"></i>
                            </div>
                            <div>
                                <h4 class="text-sm font-bold text-white">Jalur Prestasi & Keringanan Biaya</h4>
                                <p class="text-xs text-slate-300">Apresiasi bagi calon siswa penghafal Al-Qur'an (Tahfiz), juara seni musik tradisional, dan atlet bela diri.</p>
                            </div>
                        </div>
                    </div>

                    <!-- Quick Stats -->
                    <div class="grid grid-cols-2 gap-3 mt-6 pt-6 border-t border-slate-700 text-center">
                        <div class="bg-black/20 p-3 rounded-2xl">
                            <div class="text-2xl font-black text-emerald-400">{{ $totalPendaftar }}</div>
                            <div class="text-[11px] text-slate-400 font-medium">Total Pendaftar</div>
                        </div>
                        <div class="bg-black/20 p-3 rounded-2xl">
                            <div class="text-2xl font-black text-teal-400">4 Jurusan</div>
                            <div class="text-[11px] text-slate-400 font-medium">Program Keahlian</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Program Keahlian Section -->
<section id="jurusan" class="py-16 md:py-24 bg-white dark:bg-slate-900 transition-colors">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center max-w-3xl mx-auto mb-16 space-y-3">
            <span class="text-emerald-600 dark:text-emerald-400 font-bold text-xs uppercase tracking-wider">Kompetensi Keahlian Unggulan</span>
            <h2 class="text-3xl md:text-4xl font-extrabold text-slate-900 dark:text-white">Pilihan 4 Program Keahlian SMK</h2>
            <p class="text-sm md:text-base text-slate-600 dark:text-slate-400">Dirancang khusus untuk memenuhi kebutuhan dunia usaha dan dunia industri (DU/DI) dengan kuota 72 siswa per jurusan.</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            @foreach($jurusan as $j)
            <div class="group relative rounded-3xl p-6 bg-slate-50 dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700/80 hover:border-emerald-500/50 hover:shadow-xl transition-all duration-300 flex flex-col justify-between">
                <div class="space-y-4">
                    <div class="w-14 h-14 rounded-2xl bg-gradient-to-tr 
                        @if($j->kode_jurusan === 'FAR') from-emerald-600 to-teal-400 
                        @elseif($j->kode_jurusan === 'TKJ') from-blue-600 to-cyan-400 
                        @elseif($j->kode_jurusan === 'TKR') from-amber-600 to-yellow-400 
                        @else from-rose-600 to-orange-400 @endif
                        flex items-center justify-center text-white shadow-lg shadow-black/10 group-hover:scale-110 transition-transform">
                        @if($j->kode_jurusan === 'FAR') <i data-lucide="pill" class="w-7 h-7"></i>
                        @elseif($j->kode_jurusan === 'TKJ') <i data-lucide="network" class="w-7 h-7"></i>
                        @elseif($j->kode_jurusan === 'TKR') <i data-lucide="car" class="w-7 h-7"></i>
                        @else <i data-lucide="wrench" class="w-7 h-7"></i> @endif
                    </div>
                    <div>
                        <div class="text-xs font-bold text-emerald-600 dark:text-emerald-400 uppercase tracking-wider mb-1">Kode: {{ $j->kode_jurusan }}</div>
                        <h3 class="text-lg font-bold text-slate-900 dark:text-white group-hover:text-emerald-600 dark:group-hover:text-emerald-400 transition-colors">{{ $j->nama_jurusan }}</h3>
                    </div>
                    <p class="text-xs text-slate-600 dark:text-slate-400 leading-relaxed">{{ $j->deskripsi }}</p>
                </div>

                <div class="mt-6 pt-4 border-t border-slate-200 dark:border-slate-700 flex items-center justify-between text-xs">
                    <span class="text-slate-500 dark:text-slate-400">Kapasitas Kuota:</span>
                    <span class="font-extrabold text-slate-900 dark:text-white px-2.5 py-1 bg-white dark:bg-slate-700 rounded-lg shadow-sm">{{ $j->kuota }} Siswa</span>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</section>

<!-- Ekstrakurikuler Section -->
<section id="ekskul" class="py-16 md:py-24 bg-slate-50 dark:bg-slate-950 transition-colors border-y border-slate-200 dark:border-slate-800">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center max-w-3xl mx-auto mb-16 space-y-3">
            <span class="text-emerald-600 dark:text-emerald-400 font-bold text-xs uppercase tracking-wider">Pengembangan Bakat & Karakter</span>
            <h2 class="text-3xl md:text-4xl font-extrabold text-slate-900 dark:text-white">7 Ekstrakurikuler Unggulan</h2>
            <p class="text-sm md:text-base text-slate-600 dark:text-slate-400">Menumbuhkan kecerdasan spiritual, kedisiplinan fisik, kepemimpinan, dan kecintaan pada warisan budaya daerah Lampung.</p>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach($ekskul as $e)
            <div class="p-6 rounded-3xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-sm hover:shadow-md transition-shadow flex items-start gap-4">
                <div class="w-12 h-12 rounded-2xl bg-emerald-50 dark:bg-emerald-950 text-emerald-600 dark:text-emerald-400 flex items-center justify-center shrink-0">
                    <i data-lucide="{{ $e['icon'] }}" class="w-6 h-6"></i>
                </div>
                <div>
                    <h4 class="text-base font-bold text-slate-900 dark:text-white mb-1">{{ $e['nama'] }}</h4>
                    <p class="text-xs text-slate-600 dark:text-slate-400 leading-relaxed">{{ $e['desc'] }}</p>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</section>

<!-- Alur Pendaftaran Section -->
<section id="alur" class="py-16 md:py-24 bg-white dark:bg-slate-900 transition-colors">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center max-w-3xl mx-auto mb-16 space-y-3">
            <span class="text-emerald-600 dark:text-emerald-400 font-bold text-xs uppercase tracking-wider">Langkah Mudah Mendaftar</span>
            <h2 class="text-3xl md:text-4xl font-extrabold text-slate-900 dark:text-white">Alur 6 Tahap PPDB Online</h2>
            <p class="text-sm md:text-base text-slate-600 dark:text-slate-400">Seluruh proses transparan, terintegrasi, dan dapat dipantau dari smartphone Anda kapan saja.</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-6 gap-6 relative">
            @php
                $steps = [
                    ['no' => '1', 'title' => 'Buat Akun', 'desc' => 'Daftar dengan NISN 10 digit, Username, dan Email aktif.'],
                    ['no' => '2', 'title' => 'Isi Formulir', 'desc' => 'Lengkapi biodata diri, pilihan jurusan 1 & 2, ukuran seragam & asal sekolah.'],
                    ['no' => '3', 'title' => 'Pembayaran', 'desc' => 'Bayar via QRIS/VA Midtrans atau bayar tunai di Loket TU.'],
                    ['no' => '4', 'title' => 'Upload Berkas', 'desc' => 'Unggah scan Ijazah, KK, Akta, Pas Foto, dan Sertifikat Prestasi.'],
                    ['no' => '5', 'title' => 'Tes & Seleksi', 'desc' => 'Cetak Kartu Ujian dan ikuti tes wawancara & tes buta warna.'],
                    ['no' => '6', 'title' => 'Daftar Ulang', 'desc' => 'Unduh SKL resmi Kepala Sekolah dan konfirmasi masuk.'],
                ];
            @endphp

            @foreach($steps as $s)
            <div class="p-5 rounded-2xl bg-slate-50 dark:bg-slate-800/60 border border-slate-200 dark:border-slate-700/60 text-center relative group hover:border-emerald-500 transition-all">
                <div class="w-10 h-10 rounded-full bg-emerald-600 text-white font-extrabold flex items-center justify-center mx-auto mb-3 text-sm shadow-md shadow-emerald-600/30">
                    {{ $s['no'] }}
                </div>
                <h4 class="text-sm font-bold text-slate-900 dark:text-white mb-1.5">{{ $s['title'] }}</h4>
                <p class="text-[11px] text-slate-500 dark:text-slate-400 leading-normal">{{ $s['desc'] }}</p>
            </div>
            @endforeach
        </div>
    </div>
</section>

<!-- Cek Status Kelulusan Cepat Section (Widget Publik Tanpa Login) -->
<section id="cek-kelulusan" class="py-16 md:py-24 bg-gradient-to-br from-emerald-900 via-slate-900 to-slate-950 text-white">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center space-y-8">
        <div class="space-y-3">
            <span class="text-emerald-400 font-bold text-xs uppercase tracking-wider">Layanan Publik Cepat</span>
            <h2 class="text-3xl md:text-4xl font-extrabold">Cek Status Hasil Seleksi PPDB</h2>
            <p class="text-sm md:text-base text-slate-300 max-w-xl mx-auto">Masukkan 10 digit NISN atau Nomor Pendaftaran Anda untuk melihat hasil penetapan kelulusan resmi tanpa perlu login ke sistem.</p>
        </div>

        <!-- Form Lookup -->
        <div class="p-6 md:p-8 rounded-3xl bg-white/10 border border-white/20 backdrop-blur-xl shadow-2xl max-w-2xl mx-auto">
            <form id="form-cek-kelulusan" class="space-y-4">
                @csrf
                <div class="flex flex-col sm:flex-row gap-3">
                    <div class="relative flex-1">
                        <i data-lucide="search" class="w-5 h-5 text-slate-400 absolute left-4 top-1/2 -translate-y-1/2"></i>
                        <input type="text" id="input-nisn" name="nisn" required placeholder="Contoh: 0078912345 atau PPDB-2026-TKJ-001" class="w-full pl-12 pr-4 py-3.5 rounded-2xl bg-slate-900/80 border border-slate-700 text-white placeholder-slate-400 text-sm focus:outline-none focus:border-emerald-500">
                    </div>
                    <button type="submit" id="btn-submit-cek" class="px-6 py-3.5 rounded-2xl bg-emerald-500 hover:bg-emerald-600 text-white font-bold text-sm shadow-lg shadow-emerald-500/30 transition-all flex items-center justify-center gap-2 shrink-0">
                        <i data-lucide="search-check" class="w-4 h-4"></i>
                        <span>Cek Status</span>
                    </button>
                </div>
                <p class="text-[11px] text-slate-400 text-left">Gunakan nomor NISN Anda yang terdaftar pada saat registrasi awal.</p>
            </form>

            <!-- Result Box (Hidden initially) -->
            <div id="hasil-cek-box" class="hidden mt-6 pt-6 border-t border-white/15 text-left animate-fade-in">
                <div id="hasil-konten"></div>
            </div>
        </div>
    </div>
</section>

<!-- FAQ Section -->
<section class="py-16 md:py-24 bg-white dark:bg-slate-900 transition-colors">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-16 space-y-3">
            <span class="text-emerald-600 dark:text-emerald-400 font-bold text-xs uppercase tracking-wider">Pertanyaan Umum</span>
            <h2 class="text-3xl md:text-4xl font-extrabold text-slate-900 dark:text-white">Tanya Jawab (FAQ) PPDB</h2>
            <p class="text-sm md:text-base text-slate-600 dark:text-slate-400">Jawaban seputar pendaftaran, syarat berkas, dan sistem seleksi di SMK Islam Bina Khalifah Bangsa.</p>
        </div>

        <div class="space-y-4">
            <details class="group p-5 rounded-2xl bg-slate-50 dark:bg-slate-800/60 border border-slate-200 dark:border-slate-700/60">
                <summary class="font-bold text-sm md:text-base text-slate-900 dark:text-white cursor-pointer list-none flex items-center justify-between">
                    <span>Apakah pendaftaran PPDB ini dilakukan secara full online?</span>
                    <i data-lucide="chevron-down" class="w-5 h-5 text-slate-400 group-open:rotate-180 transition-transform"></i>
                </summary>
                <p class="mt-3 text-xs md:text-sm text-slate-600 dark:text-slate-400 leading-relaxed">
                    Ya, pembuatan akun, pengisian formulir, unggah berkas, dan pengecekan kelulusan 100% online. Namun bagi calon siswa dan orang tua yang ingin membayar tunai atau membutuhkan bantuan pendaftaran, dapat hadir langsung ke Loket TU di sekolah.
                </p>
            </details>

            <details class="group p-5 rounded-2xl bg-slate-50 dark:bg-slate-800/60 border border-slate-200 dark:border-slate-700/60">
                <summary class="font-bold text-sm md:text-base text-slate-900 dark:text-white cursor-pointer list-none flex items-center justify-between">
                    <span>Bagaimana jika jurusan pilihan pertama saya sudah penuh?</span>
                    <i data-lucide="chevron-down" class="w-5 h-5 text-slate-400 group-open:rotate-180 transition-transform"></i>
                </summary>
                <p class="mt-3 text-xs md:text-sm text-slate-600 dark:text-slate-400 leading-relaxed">
                    Sistem kami memiliki fitur limpahan kuota cerdas. Jika kuota 72 siswa pada Pilihan 1 telah terpenuhi, panitia secara otomatis akan mempertimbangkan Anda untuk diterima di Jurusan Pilihan 2 yang telah Anda tentukan.
                </p>
            </details>

            <details class="group p-5 rounded-2xl bg-slate-50 dark:bg-slate-800/60 border border-slate-200 dark:border-slate-700/60">
                <summary class="font-bold text-sm md:text-base text-slate-900 dark:text-white cursor-pointer list-none flex items-center justify-between">
                    <span>Apa saja tes seleksi yang diadakan di SMK Islam Bina Khalifah Bangsa?</span>
                    <i data-lucide="chevron-down" class="w-5 h-5 text-slate-400 group-open:rotate-180 transition-transform"></i>
                </summary>
                <p class="mt-3 text-xs md:text-sm text-slate-600 dark:text-slate-400 leading-relaxed">
                    Tes seleksi mencakup Tes Wawancara Minat Bakat & Keagamaan, serta Tes Buta Warna (khususnya untuk Farmasi, TKJ, TKR, dan TBSM guna menjamin keselamatan kerja praktik).
                </p>
            </details>

            <details class="group p-5 rounded-2xl bg-slate-50 dark:bg-slate-800/60 border border-slate-200 dark:border-slate-700/60">
                <summary class="font-bold text-sm md:text-base text-slate-900 dark:text-white cursor-pointer list-none flex items-center justify-between">
                    <span>Apakah ada beasiswa khusus untuk Tahfiz Al-Qur'an?</span>
                    <i data-lucide="chevron-down" class="w-5 h-5 text-slate-400 group-open:rotate-180 transition-transform"></i>
                </summary>
                <p class="mt-3 text-xs md:text-sm text-slate-600 dark:text-slate-400 leading-relaxed">
                    Tentu ada! Calon siswa dengan hafalan minimal 3 Juz berhak mendapatkan potongan biaya pendaftaran dengan memasukkan kode voucher <strong>TAHFIZJUARA</strong> saat checkout dan mengunggah sertifikat/syahadah tahfiz pada formulir Jalur Prestasi.
                </p>
            </details>
        </div>
    </div>
</section>

<!-- Lokasi & Peta Section -->
<section class="py-16 bg-slate-50 dark:bg-slate-950 transition-colors border-t border-slate-200 dark:border-slate-800">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 items-center">
            <div class="space-y-4">
                <span class="text-emerald-600 dark:text-emerald-400 font-bold text-xs uppercase tracking-wider">Lokasi Kampus Sekolah</span>
                <h2 class="text-2xl md:text-3xl font-extrabold text-slate-900 dark:text-white">Kunjungi {{ $pengaturan->nama_sekolah }}</h2>
                <p class="text-sm text-slate-600 dark:text-slate-400 leading-relaxed">
                    Terletak strategis di Kota Metro, Lampung. Memiliki lingkungan belajar yang asri, sarana ibadah luas, dan fasilitas praktik bengkel serta laboratorium kefarmasian yang lengkap.
                </p>
                <div class="space-y-2 text-xs text-slate-700 dark:text-slate-300">
                    <p class="flex items-center gap-2"><i data-lucide="map-pin" class="w-4 h-4 text-emerald-500 shrink-0"></i> {{ $pengaturan->alamat }}</p>
                    <p class="flex items-center gap-2"><i data-lucide="clock" class="w-4 h-4 text-emerald-500 shrink-0"></i> Pelayanan Loket TU PPDB: Senin - Sabtu, Pukul 08.00 - 15.00 WIB</p>
                    <p class="flex items-center gap-2"><i data-lucide="phone" class="w-4 h-4 text-emerald-500 shrink-0"></i> Call Center / WhatsApp: {{ $pengaturan->no_telp }}</p>
                </div>
                <div class="pt-2">
                    <a href="https://www.google.com/maps/search/?api=1&query={{ urlencode($pengaturan->nama_sekolah . ' ' . $pengaturan->alamat) }}" target="_blank" class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-slate-900 dark:bg-white text-white dark:text-slate-900 font-bold text-xs shadow-md hover:opacity-90 transition-opacity">
                        <i data-lucide="navigation" class="w-4 h-4 text-emerald-500"></i> Buka Petunjuk Rute Google Maps
                    </a>
                </div>
            </div>

            <!-- Maps Embed Frame dengan Pin Lokasi Spesifik -->
            <div class="relative rounded-3xl overflow-hidden border border-slate-200 dark:border-slate-800 shadow-xl h-72 lg:h-96 bg-slate-200 dark:bg-slate-800">
                <iframe 
                    title="Peta Lokasi {{ $pengaturan->nama_sekolah }}"
                    class="w-full h-full border-0"
                    loading="lazy" 
                    allowfullscreen 
                    src="https://maps.google.com/maps?q={{ urlencode($pengaturan->nama_sekolah . ', ' . $pengaturan->alamat) }}&t=&z=17&ie=UTF8&iwloc=B&output=embed">
                </iframe>
                <!-- Floating Pin Badge Indikator Kampus -->
                <div class="absolute top-4 left-4 bg-white/95 dark:bg-slate-900/95 backdrop-blur-sm px-3.5 py-2 rounded-xl shadow-lg border border-slate-200 dark:border-slate-700 flex items-center gap-2.5 pointer-events-none">
                    <span class="relative flex h-2.5 w-2.5">
                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                        <span class="relative inline-flex rounded-full h-2.5 w-2.5 bg-emerald-600"></span>
                    </span>
                    <span class="text-xs font-bold text-slate-800 dark:text-white">{{ $pengaturan->nama_sekolah }}</span>
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

                document.getElementById('cd-days').innerText = String(days).padStart(2, '0');
                document.getElementById('cd-hours').innerText = String(hours).padStart(2, '0');
                document.getElementById('cd-mins').innerText = String(minutes).padStart(2, '0');
                document.getElementById('cd-secs').innerText = String(seconds).padStart(2, '0');
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
                    let badgeClass = 'bg-amber-500/20 text-amber-300 border-amber-500/40';
                    let statusIcon = 'clock';
                    let statusMsg = 'Sedang Dalam Proses Seleksi / Penilaian.';

                    if (d.status_kelulusan === 'Diterima') {
                        badgeClass = 'bg-emerald-500/20 text-emerald-300 border-emerald-500/40';
                        statusIcon = 'check-circle-2';
                        statusMsg = `Selamat! Anda Diterima di Jurusan <strong>${d.jurusan_diterima}</strong>.`;
                    } else if (d.status_kelulusan === 'Cadangan') {
                        badgeClass = 'bg-sky-500/20 text-sky-300 border-sky-500/40';
                        statusIcon = 'help-circle';
                        statusMsg = 'Anda Masuk dalam Daftar Cadangan / Waiting List.';
                    } else if (d.status_kelulusan === 'Tidak Lolos') {
                        badgeClass = 'bg-rose-500/20 text-rose-300 border-rose-500/40';
                        statusIcon = 'x-circle';
                        statusMsg = 'Mohon Maaf, Anda Belum Lolos Seleksi Tahun Ini.';
                    }

                    resultContent.innerHTML = `
                        <div class="p-4 rounded-2xl bg-white/5 border border-white/10 space-y-3">
                            <div class="flex items-center justify-between flex-wrap gap-2">
                                <span class="px-3 py-1 rounded-full border text-xs font-bold uppercase tracking-wider ${badgeClass} flex items-center gap-1.5">
                                    <i data-lucide="${statusIcon}" class="w-4 h-4"></i> ${d.status_kelulusan}
                                </span>
                                <span class="text-xs text-slate-400 font-mono">${d.no_pendaftaran}</span>
                            </div>
                            <div>
                                <h4 class="text-base font-bold text-white">${d.nama}</h4>
                                <p class="text-xs text-slate-300">NISN: ${d.nisn} | Asal Sekolah: ${d.asal_sekolah}</p>
                            </div>
                            <div class="p-3 rounded-xl bg-black/30 border border-white/5 text-xs text-slate-200">
                                ${statusMsg}
                                ${d.no_skl ? `<div class="mt-1.5 pt-1.5 border-t border-white/10 text-[11px] text-emerald-400">No. SKL: ${d.no_skl}</div>` : ''}
                            </div>
                            <div class="pt-1 text-center">
                                <a href="{{ route('login') }}" class="text-xs text-emerald-400 hover:underline">Login ke Akun Siswa untuk Mengunduh Lembar SKL & Panduan Daftar Ulang &rarr;</a>
                            </div>
                        </div>
                    `;
                } else {
                    resultContent.innerHTML = `
                        <div class="p-4 rounded-2xl bg-rose-500/10 border border-rose-500/20 text-rose-300 text-xs flex items-center gap-3">
                            <i data-lucide="alert-triangle" class="w-5 h-5 shrink-0 text-rose-400"></i>
                            <div>${res.message}</div>
                        </div>
                    `;
                }
            } catch (err) {
                resultContent.innerHTML = `
                    <div class="p-4 rounded-2xl bg-rose-500/10 border border-rose-500/20 text-rose-300 text-xs">
                        Terjadi kesalahan saat memeriksa data. Silakan coba lagi.
                    </div>
                `;
            } finally {
                btnSubmit.disabled = false;
                btnSubmit.innerHTML = `<i data-lucide="search-check" class="w-4 h-4"></i><span>Cek Status</span>`;
                lucide.createIcons();
            }
        });
    }
</script>
@endpush

