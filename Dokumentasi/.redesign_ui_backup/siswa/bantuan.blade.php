@extends('layouts.siswa')

@section('title', 'Pusat Bantuan & FAQ')
@section('page_title', 'Pusat Bantuan & FAQ')

@section('content')
<div class="space-y-6">

    <!-- Header Banner (Navy & Pink) -->
    <div class="bg-[#0B182F] rounded-3xl p-6 sm:p-8 text-white shadow-xl relative overflow-hidden border border-slate-800">
        <div class="relative z-10 max-w-2xl">
            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-pink-500/20 text-pink-300 border border-pink-400/30 mb-3">
                <i data-lucide="help-circle" class="w-3.5 h-3.5 text-[#f43f5e]"></i>
                Layanan Informasi Calon Siswa
            </span>
            <h1 class="text-xl sm:text-2xl font-black tracking-tight leading-tight text-white">
                Pusat Bantuan & Panduan PPDB
            </h1>
            <p class="text-xs sm:text-sm text-slate-300 mt-2 leading-relaxed">
                Temukan jawaban lengkap atas pertanyaan seputar pendaftaran, pengisian biodata, pembayaran biaya formulir, jadwal tes seleksi, hingga daftar ulang di SMK Islam Bina Khalifah Bangsa.
            </p>
        </div>
        <div class="absolute -right-10 -bottom-10 w-64 h-64 bg-white/5 rounded-full pointer-events-none"></div>
    </div>

    <!-- Kontak Helpdesk & Loket Pelayanan -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <!-- Loket TU -->
        <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200 dark:border-slate-800 p-5 shadow-xs">
            <div class="w-10 h-10 rounded-xl bg-pink-50 text-[#f43f5e] flex items-center justify-center mb-3">
                <i data-lucide="map-pin" class="w-5 h-5"></i>
            </div>
            <h3 class="text-xs font-bold text-[#0B182F] dark:text-white uppercase tracking-wider">Lokasi Kampus</h3>
            <p class="text-xs text-slate-600 dark:text-slate-300 mt-1 font-medium">
                {{ $pengaturan->alamat_sekolah ?? 'Jl. Pattimura No. 24, Ganjar Asri, Metro Barat, Kota Metro' }}
            </p>
            <p class="text-[11px] text-slate-400 mt-2">Ruang Tata Usaha (Gedung Utama Lt. 1)</p>
        </div>

        <!-- Jam Pelayanan -->
        <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200 dark:border-slate-800 p-5 shadow-xs">
            <div class="w-10 h-10 rounded-xl bg-pink-50 text-[#f43f5e] flex items-center justify-center mb-3">
                <i data-lucide="clock" class="w-5 h-5"></i>
            </div>
            <h3 class="text-xs font-bold text-[#0B182F] dark:text-white uppercase tracking-wider">Jam Pelayanan</h3>
            <p class="text-xs text-slate-600 dark:text-slate-300 mt-1 font-medium">
                Senin - Jum'at: 07.30 - 15.30 WIB<br>
                Sabtu: 08.00 - 12.00 WIB
            </p>
            <p class="text-[11px] text-slate-400 mt-2">Hari Minggu & Libur Nasional Tutup</p>
        </div>

        <!-- Kontak Panitia -->
        <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200 dark:border-slate-800 p-5 shadow-xs">
            <div class="w-10 h-10 rounded-xl bg-pink-50 text-[#f43f5e] flex items-center justify-center mb-3">
                <i data-lucide="phone-call" class="w-5 h-5"></i>
            </div>
            <h3 class="text-xs font-bold text-[#0B182F] dark:text-white uppercase tracking-wider">Kontak Panitia</h3>
            <p class="text-xs text-slate-600 dark:text-slate-300 mt-1 font-medium font-mono">
                WhatsApp: 0821-7890-1234<br>
                Email: {{ $pengaturan->email_sekolah ?? 'info@smkib.com' }}
            </p>
            <p class="text-[11px] text-slate-400 mt-2">NPSN: {{ $pengaturan->npsn ?? '70012345' }}</p>
        </div>
    </div>

    <!-- Alur Pendaftaran Langkah demi Langkah -->
    <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200 dark:border-slate-800 p-6 shadow-xs">
        <h2 class="text-sm font-bold text-[#0B182F] dark:text-white flex items-center gap-2 mb-4">
            <i data-lucide="git-merge" class="w-4 h-4 text-[#f43f5e]"></i>
            7 Tahapan Alur PPDB SMK Islam Bina Khalifah Bangsa
        </h2>
        
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            <div class="p-4 rounded-2xl bg-slate-50 dark:bg-slate-800/60 border border-slate-100 dark:border-slate-800">
                <span class="inline-block px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-[#0B182F] text-white mb-2">Tahap 1</span>
                <h4 class="text-xs font-bold text-[#0B182F] dark:text-white">Isi Formulir Biodata</h4>
                <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-1">
                    Lengkapi biodata diri, data orang tua, dan pilihan program keahlian. Anda dapat menyimpan sebagai draft sebelum melakukan finalisasi data.
                </p>
            </div>

            <div class="p-4 rounded-2xl bg-slate-50 dark:bg-slate-800/60 border border-slate-100 dark:border-slate-800">
                <span class="inline-block px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-[#0B182F] text-white mb-2">Tahap 2</span>
                <h4 class="text-xs font-bold text-[#0B182F] dark:text-white">Pembayaran Formulir</h4>
                <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-1">
                    Selesaikan biaya pendaftaran via online gateway Midtrans (QRIS, VA Bank) atau tunai langsung di kasir loket TU sekolah.
                </p>
            </div>

            <div class="p-4 rounded-2xl bg-slate-50 dark:bg-slate-800/60 border border-slate-100 dark:border-slate-800">
                <span class="inline-block px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-[#0B182F] text-white mb-2">Tahap 3</span>
                <h4 class="text-xs font-bold text-[#0B182F] dark:text-white">Upload Berkas Persyaratan</h4>
                <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-1">
                    Unggah pindaian KK, Akta Kelahiran, Ijazah/SKL, dan Pasfoto. Pastikan dokumen jelas dan dapat terbaca.
                </p>
            </div>

            <div class="p-4 rounded-2xl bg-slate-50 dark:bg-slate-800/60 border border-slate-100 dark:border-slate-800">
                <span class="inline-block px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-[#0B182F] text-white mb-2">Tahap 4</span>
                <h4 class="text-xs font-bold text-[#0B182F] dark:text-white">Verifikasi Dokumen oleh TU</h4>
                <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-1">
                    Petugas Tata Usaha memeriksa dan memvalidasi keabsahan seluruh dokumen yang telah diunggah.
                </p>
            </div>

            <div class="p-4 rounded-2xl bg-slate-50 dark:bg-slate-800/60 border border-slate-100 dark:border-slate-800">
                <span class="inline-block px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-[#0B182F] text-white mb-2">Tahap 5</span>
                <h4 class="text-xs font-bold text-[#0B182F] dark:text-white">Tes Seleksi & Wawancara</h4>
                <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-1">
                    Cetak Kartu Ujian dan pantau ruangan tes pada menu Jadwal. Ikuti tes akademik, wawancara minat, dan tes buta warna sesuai jadwal.
                </p>
            </div>

            <div class="p-4 rounded-2xl bg-slate-50 dark:bg-slate-800/60 border border-slate-100 dark:border-slate-800">
                <span class="inline-block px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-[#0B182F] text-white mb-2">Tahap 6</span>
                <h4 class="text-xs font-bold text-[#0B182F] dark:text-white">Pengumuman & SKL</h4>
                <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-1">
                    Kepala Sekolah mengesahkan hasil seleksi. Calon siswa yang lolos dapat langsung mengunduh Surat Keterangan Lulus (SKL) resmi ber-QR Code.
                </p>
            </div>

            <div class="p-4 rounded-2xl bg-slate-50 dark:bg-slate-800/60 border border-slate-100 dark:border-slate-800">
                <span class="inline-block px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-[#0B182F] text-white mb-2">Tahap 7</span>
                <h4 class="text-xs font-bold text-[#0B182F] dark:text-white">Konfirmasi Daftar Ulang</h4>
                <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-1">
                    Lakukan konfirmasi kesediaan masuk melalui portal dan serahkan berkas fisik ke loket TU untuk pengukuran seragam dan nomor induk siswa.
                </p>
            </div>
        </div>
    </div>

    <!-- Accordion FAQ Interaktif -->
    <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200 dark:border-slate-800 p-6 shadow-xs">
        <h2 class="text-sm font-bold text-[#0B182F] dark:text-white flex items-center gap-2 mb-4">
            <i data-lucide="message-square-text" class="w-4 h-4 text-[#f43f5e]"></i>
            Pertanyaan yang Sering Diajukan (FAQ)
        </h2>

        <div class="space-y-3">
            <!-- FAQ 1 -->
            <details class="group rounded-2xl border border-slate-200 dark:border-slate-800 overflow-hidden">
                <summary class="flex items-center justify-between p-4 cursor-pointer font-bold text-xs text-[#0B182F] dark:text-white group-open:bg-slate-50 dark:group-open:bg-slate-800/60 transition-colors">
                    <span>1. Bagaimana jika saya salah menginput biodata dan formulir sudah terkunci?</span>
                    <i data-lucide="chevron-down" class="w-4 h-4 text-slate-400 group-open:rotate-180 transition-transform"></i>
                </summary>
                <div class="p-4 text-xs text-slate-600 dark:text-slate-300 border-t border-slate-100 dark:border-slate-800 leading-relaxed bg-white dark:bg-slate-900">
                    Formulir yang telah difinalisasi dikunci secara otomatis demi integritas data seleksi. Apabila terdapat kesalahan penulisan nama, NISN, atau pilihan jurusan, silakan hubungi <strong>Petugas Tata Usaha (TU)</strong> di loket sekolah atau melalui menu kontak panitia. Petugas TU memiliki kewenangan resmi untuk membuka kembali kunci formulir (<em>Unlock Formulir</em>) tanpa membatalkan pembayaran Anda.
                </div>
            </details>

            <!-- FAQ 2 -->
            <details class="group rounded-2xl border border-slate-200 dark:border-slate-800 overflow-hidden">
                <summary class="flex items-center justify-between p-4 cursor-pointer font-bold text-xs text-[#0B182F] dark:text-white group-open:bg-slate-50 dark:group-open:bg-slate-800/60 transition-colors">
                    <span>2. Berapa lama proses verifikasi berkas oleh Petugas TU?</span>
                    <i data-lucide="chevron-down" class="w-4 h-4 text-slate-400 group-open:rotate-180 transition-transform"></i>
                </summary>
                <div class="p-4 text-xs text-slate-600 dark:text-slate-300 border-t border-slate-100 dark:border-slate-800 leading-relaxed bg-white dark:bg-slate-900">
                    Proses verifikasi berkas umumnya memakan waktu 1x24 jam kerja sejak berkas selesai diunggah. Pastikan hasil pindai/foto dokumen terbaca dengan jelas, tidak terpotong, dan berorientasi tegak lurus. Jika terdapat dokumen yang ditolak, periksa catatan revisi di dashboard akun Anda dan unggah ulang berkas yang dimaksud.
                </div>
            </details>

            <!-- FAQ 3 -->
            <details class="group rounded-2xl border border-slate-200 dark:border-slate-800 overflow-hidden">
                <summary class="flex items-center justify-between p-4 cursor-pointer font-bold text-xs text-[#0B182F] dark:text-white group-open:bg-slate-50 dark:group-open:bg-slate-800/60 transition-colors">
                    <span>3. Bagaimana cara menyelesaikan pembayaran secara tunai di loket?</span>
                    <i data-lucide="chevron-down" class="w-4 h-4 text-slate-400 group-open:rotate-180 transition-transform"></i>
                </summary>
                <div class="p-4 text-xs text-slate-600 dark:text-slate-300 border-t border-slate-100 dark:border-slate-800 leading-relaxed bg-white dark:bg-slate-900">
                    Pilih opsi <em>"Bayar Tunai di Loket TU"</em> pada menu Pembayaran PPDB. Cetak atau tunjukkan <strong>Nomor Pendaftaran</strong> Anda kepada staf kasir TU di kampus SMK Islam Bina Khalifah Bangsa. Staf TU akan memvalidasi pelunasan di sistem, dan status pembayaran Anda akan otomatis berubah menjadi <strong>LUNAS (Paid)</strong> beserta terbitnya kwitansi digital resmi.
                </div>
            </details>

            <!-- FAQ 4 -->
            <details class="group rounded-2xl border border-slate-200 dark:border-slate-800 overflow-hidden">
                <summary class="flex items-center justify-between p-4 cursor-pointer font-bold text-xs text-[#0B182F] dark:text-white group-open:bg-slate-50 dark:group-open:bg-slate-800/60 transition-colors">
                    <span>4. Apa saja yang wajib dibawa saat pelaksanaan tes seleksi masuk?</span>
                    <i data-lucide="chevron-down" class="w-4 h-4 text-slate-400 group-open:rotate-180 transition-transform"></i>
                </summary>
                <div class="p-4 text-xs text-slate-600 dark:text-slate-300 border-t border-slate-100 dark:border-slate-800 leading-relaxed bg-white dark:bg-slate-900">
                    Saat menghadiri tes seleksi masuk di kampus, peserta wajib membawa:
                    <ul class="list-disc list-inside mt-1.5 space-y-1 text-[11px] text-slate-500 dark:text-slate-400">
                        <li>Kartu Tanda Peserta Ujian (dicetak dari menu Jadwal dan Kartu Ujian).</li>
                        <li>Alat tulis (pensil 2B, pena hitam, penghapus).</li>
                        <li>Seragam sekolah asal (SMP/MTs) yang rapi dan sopan.</li>
                        <li>Bukti kwitansi lunas (bila diminta panitia).</li>
                    </ul>
                </div>
            </details>
        </div>
    </div>

</div>
@endsection
