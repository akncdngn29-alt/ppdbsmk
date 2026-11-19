@extends('layouts.admin')

@php
    $isKepsek = auth('admin')->check() && auth('admin')->user()->role === 'kepala_sekolah';
    $pageTitle = $isKepsek ? 'Pengaturan Kebijakan PPDB' : 'Pengaturan Lembaga & Pengumuman';
@endphp

@section('title', $pageTitle . ' - SMK IB')
@section('header_title', $pageTitle)

@section('content')
<div class="space-y-6">

    <!-- Flash Messages -->
    @if(session('success'))
    <div class="p-4 rounded-2xl bg-pink-50 dark:bg-pink-950/60 border border-pink-200 dark:border-pink-800 text-pink-800 dark:text-pink-200 text-xs font-semibold flex items-center justify-between">
        <div class="flex items-center gap-2">
            <i data-lucide="check-circle-2" class="w-4 h-4 text-[#fa2f79]"></i>
            <span>{{ session('success') }}</span>
        </div>
        <button type="button" onclick="this.parentElement.remove()" class="text-[#fa2f79] hover:text-pink-800">&times;</button>
    </div>
    @endif

    @if(session('error'))
    <div class="p-4 rounded-2xl bg-rose-50 dark:bg-rose-950/60 border border-rose-200 dark:border-rose-800 text-rose-800 dark:text-rose-200 text-xs font-semibold flex items-center justify-between">
        <div class="flex items-center gap-2">
            <i data-lucide="alert-circle" class="w-4 h-4 text-rose-600"></i>
            <span>{{ session('error') }}</span>
        </div>
        <button type="button" onclick="this.parentElement.remove()" class="text-rose-600 hover:text-rose-800">&times;</button>
    </div>
    @endif

    @if($errors->any())
    <div class="p-4 rounded-2xl bg-rose-50 dark:bg-rose-950/60 border border-rose-200 dark:border-rose-800 text-rose-800 dark:text-rose-200 text-xs space-y-1">
        @foreach($errors->all() as $err)
        <div class="flex items-center gap-2">
            <i data-lucide="alert-circle" class="w-4 h-4 text-rose-600"></i>
            <span>{{ $err }}</span>
        </div>
        @endforeach
    </div>
    @endif

    @php
        $kepsekTabs = ['gateway', 'voucher', 'gelombang', 'biaya_pendaftaran', 'biaya_daftar_ulang'];
        $tuTabs = ['profil', 'pengumuman'];

        if ($isKepsek) {
            $allowedTabs = $kepsekTabs;
            $defaultTab = 'gateway';
        } else {
            $allowedTabs = $tuTabs;
            $defaultTab = 'profil';
        }

        $reqTab = request('tab');
        if (request()->routeIs('admin.master.voucher*')) {
            $reqTab = 'voucher';
        }
        $activeTab = in_array($reqTab, $allowedTabs) ? $reqTab : $defaultTab;

        $isGatewayActive = (bool) ($pengaturan->midtrans_is_active ?? true);
        $isProd = (bool) ($pengaturan->midtrans_is_production ?? false);
        $hasServerKey = !empty($pengaturan->midtrans_server_key);
        $serverKeyMasked = $hasServerKey ? '••••••••••••••••••••••••••••••••••••••••' : '';
        $clientKey = $pengaturan->midtrans_client_key ?? '';
        $merchantId = $pengaturan->midtrans_merchant_id ?? '';
        $webhookUrl = url('api/midtrans/notification');
        $finishUrl = route('siswa.pembayaran');
        $snapJsUrl = $isProd ? 'https://app.midtrans.com/snap/snap.js' : 'https://app.sandbox.midtrans.com/snap/snap.js';
    @endphp

    <!-- Top Navigation Tabs -->
    <div class="flex items-center gap-2 p-1.5 bg-slate-200/70 dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 w-full overflow-x-auto">
        @if($isKepsek)
            {{-- TAB 1: PAYMENT GATEWAY --}}
            <button type="button" onclick="switchMainTab('gateway')" id="tab-btn-gateway"
                    class="flex-1 flex items-center justify-center gap-2.5 px-4 py-2.5 rounded-xl font-bold text-xs transition-all whitespace-nowrap {{ $activeTab === 'gateway' ? 'bg-[#fa2f79] text-white shadow-md shadow-pink-500/25' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white' }}">
                <i data-lucide="credit-card" class="w-4 h-4"></i>
                <div class="text-left">
                    <span class="block leading-tight">Payment Gateway</span>
                    <span class="text-[10px] opacity-80 block font-normal">QRIS, VA & Midtrans</span>
                </div>
            </button>

            {{-- TAB 2: VOUCHER & BEASISWA --}}
            <button type="button" onclick="switchMainTab('voucher')" id="tab-btn-voucher"
                    class="flex-1 flex items-center justify-center gap-2.5 px-4 py-2.5 rounded-xl font-bold text-xs transition-all whitespace-nowrap {{ $activeTab === 'voucher' ? 'bg-[#fa2f79] text-white shadow-md shadow-pink-500/25' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white' }}">
                <i data-lucide="ticket" class="w-4 h-4"></i>
                <div class="text-left">
                    <span class="block leading-tight">Voucher & Beasiswa</span>
                    <span class="text-[10px] opacity-80 block font-normal">Diskon & Promo</span>
                </div>
            </button>

            {{-- TAB 3: GELOMBANG PENDAFTARAN --}}
            <button type="button" onclick="switchMainTab('gelombang')" id="tab-btn-gelombang"
                    class="flex-1 flex items-center justify-center gap-2.5 px-4 py-2.5 rounded-xl font-bold text-xs transition-all whitespace-nowrap {{ $activeTab === 'gelombang' ? 'bg-[#fa2f79] text-white shadow-md shadow-pink-500/25' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white' }}">
                <i data-lucide="calendar" class="w-4 h-4"></i>
                <div class="text-left">
                    <span class="block leading-tight">Gelombang Pendaftaran</span>
                    <span class="text-[10px] opacity-80 block font-normal">Jadwal & Kuota Masuk</span>
                </div>
            </button>

            {{-- TAB 4: BIAYA PENDAFTARAN --}}
            <button type="button" onclick="switchMainTab('biaya_pendaftaran')" id="tab-btn-biaya_pendaftaran"
                    class="flex-1 flex items-center justify-center gap-2.5 px-4 py-2.5 rounded-xl font-bold text-xs transition-all whitespace-nowrap {{ $activeTab === 'biaya_pendaftaran' ? 'bg-[#fa2f79] text-white shadow-md shadow-pink-500/25' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white' }}">
                <i data-lucide="receipt" class="w-4 h-4"></i>
                <div class="text-left">
                    <span class="block leading-tight">Biaya Pendaftaran</span>
                    <span class="text-[10px] opacity-80 block font-normal">Formulir Gelombang</span>
                </div>
            </button>

            {{-- TAB 5: BIAYA DAFTAR ULANG --}}
            <button type="button" onclick="switchMainTab('biaya_daftar_ulang')" id="tab-btn-biaya_daftar_ulang"
                    class="flex-1 flex items-center justify-center gap-2.5 px-4 py-2.5 rounded-xl font-bold text-xs transition-all whitespace-nowrap {{ $activeTab === 'biaya_daftar_ulang' ? 'bg-[#fa2f79] text-white shadow-md shadow-pink-500/25' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white' }}">
                <i data-lucide="badge-check" class="w-4 h-4"></i>
                <div class="text-left">
                    <span class="block leading-tight">Biaya Daftar Ulang</span>
                    <span class="text-[10px] opacity-80 block font-normal">Siswa Diterima</span>
                </div>
            </button>
        @else
            {{-- TAB 1: PROFIL LEMBAGA (TU) --}}
            <button type="button" onclick="switchMainTab('profil')" id="tab-btn-profil"
                    class="flex-1 flex items-center justify-center gap-2.5 px-4 py-2.5 rounded-xl font-bold text-xs transition-all whitespace-nowrap {{ $activeTab === 'profil' ? 'bg-[#fa2f79] text-white shadow-md shadow-pink-500/25' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white' }}">
                <i data-lucide="building-2" class="w-4 h-4"></i>
                <div class="text-left">
                    <span class="block leading-tight">Profil Lembaga</span>
                    <span class="text-[10px] opacity-80 block font-normal">Identitas & Legalitas Sekolah</span>
                </div>
            </button>

            {{-- TAB 2: PENGUMUMAN & BANNER (TU) --}}
            <button type="button" onclick="switchMainTab('pengumuman')" id="tab-btn-pengumuman"
                    class="flex-1 flex items-center justify-center gap-2.5 px-4 py-2.5 rounded-xl font-bold text-xs transition-all whitespace-nowrap {{ $activeTab === 'pengumuman' ? 'bg-[#fa2f79] text-white shadow-md shadow-pink-500/25' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white' }}">
                <i data-lucide="megaphone" class="w-4 h-4"></i>
                <div class="text-left">
                    <span class="block leading-tight">Banner Pengumuman</span>
                    <span class="text-[10px] opacity-80 block font-normal">Informasi & Maintenance Mode</span>
                </div>
            </button>
        @endif
    </div>

    @if(!$isKepsek)
    <!-- ======================================================== -->
    <!-- TAB 1 TU: PROFIL SEKOLAH (IDENTITAS RESMI)               -->
    <!-- ======================================================== -->
    <div id="tab-content-profil" class="{{ $activeTab === 'profil' ? '' : 'hidden' }} space-y-6">
        <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 p-6 shadow-sm">
            <div class="flex items-start gap-4">
                <div class="w-12 h-12 rounded-xl bg-pink-100 text-[#fa2f79] dark:bg-pink-950/60 dark:text-pink-400 flex items-center justify-center shrink-0">
                    <i data-lucide="building-2" class="w-6 h-6"></i>
                </div>
                <div>
                    <h2 class="text-base font-bold text-slate-800 dark:text-white">Identitas Lembaga & Kop Surat Resmi</h2>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-1 leading-relaxed">
                        Data ini digunakan otomatis pada seluruh dokumen cetak resmi PPDB seperti Formulir Pendaftaran, Kartu Ujian, Kwitansi Pembayaran, Surat Keterangan Lulus (SKL), dan Rekapitulasi Laporan.
                    </p>
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 p-6 shadow-sm">
            <form action="{{ route('admin.pengaturan.simpan') }}" method="POST" class="space-y-6">
                @csrf
                <div>
                    <h3 class="text-xs font-bold uppercase tracking-wider text-[#fa2f79] dark:text-pink-400 mb-4 pb-2 border-b border-slate-100 dark:border-slate-800 flex items-center gap-2">
                        <i data-lucide="school" class="w-4 h-4"></i> Data Pokok Lembaga
                    </h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Nama Lengkap Sekolah *</label>
                            <input type="text" name="nama_sekolah" required value="{{ old('nama_sekolah', $pengaturan->nama_sekolah) }}"
                                   class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-xs text-slate-800 dark:text-white focus:outline-none focus:ring-2 focus:ring-[#fa2f79] font-semibold">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Nomor Pokok Sekolah Nasional (NPSN) *</label>
                            <input type="text" name="npsn" required value="{{ old('npsn', $pengaturan->npsn) }}"
                                   class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-xs text-slate-800 dark:text-white focus:outline-none focus:ring-2 focus:ring-[#fa2f79] font-mono">
                        </div>
                        <div class="md:col-span-2">
                            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Ijin Operasional / SK Pendirian *</label>
                            <input type="text" name="ijin_operasional" required value="{{ old('ijin_operasional', $pengaturan->ijin_operasional) }}"
                                   class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-xs text-slate-800 dark:text-white focus:outline-none focus:ring-2 focus:ring-[#fa2f79]">
                        </div>
                    </div>
                </div>

                <div>
                    <h3 class="text-xs font-bold uppercase tracking-wider text-[#fa2f79] dark:text-pink-400 mb-4 pb-2 border-b border-slate-100 dark:border-slate-800 flex items-center gap-2">
                        <i data-lucide="map-pin" class="w-4 h-4"></i> Lokasi & Saluran Kontak
                    </h3>
                    <div class="space-y-4">
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Alamat Lengkap *</label>
                            <textarea name="alamat" rows="2" required class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-xs text-slate-800 dark:text-white focus:outline-none focus:ring-2 focus:ring-[#fa2f79]">{{ old('alamat', $pengaturan->alamat) }}</textarea>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Kota / Kabupaten *</label>
                                <input type="text" name="kota" required value="{{ old('kota', $pengaturan->kota) }}"
                                       class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-xs text-slate-800 dark:text-white focus:outline-none focus:ring-2 focus:ring-[#fa2f79]">
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Provinsi *</label>
                                <input type="text" name="provinsi" required value="{{ old('provinsi', $pengaturan->provinsi) }}"
                                       class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-xs text-slate-800 dark:text-white focus:outline-none focus:ring-2 focus:ring-[#fa2f79]">
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div>
                                <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">No. Telepon / WhatsApp Resmi *</label>
                                <input type="text" name="no_telp" required value="{{ old('no_telp', $pengaturan->no_telp) }}"
                                       class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-xs text-slate-800 dark:text-white focus:outline-none focus:ring-2 focus:ring-[#fa2f79] font-mono">
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Email Resmi *</label>
                                <input type="email" name="email" required value="{{ old('email', $pengaturan->email) }}"
                                       class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-xs text-slate-800 dark:text-white focus:outline-none focus:ring-2 focus:ring-[#fa2f79]">
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Website Sekolah *</label>
                                <input type="text" name="website" required value="{{ old('website', $pengaturan->website) }}"
                                       class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-xs text-slate-800 dark:text-white focus:outline-none focus:ring-2 focus:ring-[#fa2f79]">
                            </div>
                        </div>
                    </div>
                </div>

                <div>
                    <h3 class="text-xs font-bold uppercase tracking-wider text-[#fa2f79] dark:text-pink-400 mb-4 pb-2 border-b border-slate-100 dark:border-slate-800 flex items-center gap-2">
                        <i data-lucide="award" class="w-4 h-4"></i> Kepala Sekolah & Pengesah Dokumen
                    </h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Nama Lengkap Kepala Sekolah (Gelar) *</label>
                            <input type="text" name="nama_kepsek" required value="{{ old('nama_kepsek', $pengaturan->nama_kepsek) }}"
                                   class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-xs text-slate-800 dark:text-white focus:outline-none focus:ring-2 focus:ring-[#fa2f79] font-semibold">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">NIP / NUPTK (Opsional)</label>
                            <input type="text" name="nip_kepsek" value="{{ old('nip_kepsek', $pengaturan->nip_kepsek) }}" placeholder="Isi '-' jika tidak ada"
                                   class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-xs text-slate-800 dark:text-white focus:outline-none focus:ring-2 focus:ring-[#fa2f79] font-mono">
                        </div>
                    </div>
                </div>

                <div class="pt-4 border-t border-slate-100 dark:border-slate-800 flex items-center justify-end">
                    <button type="submit" class="px-6 py-2.5 rounded-xl bg-[#fa2f79] hover:bg-[#e11d48] text-white font-bold text-xs flex items-center gap-2 shadow-md shadow-pink-500/25 transition-colors">
                        <i data-lucide="save" class="w-4 h-4"></i> Simpan Perubahan Profil
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- ======================================================== -->
    <!-- TAB 2 TU: PENGUMUMAN & MODE PEMELIHARAAN                 -->
    <!-- ======================================================== -->
    <div id="tab-content-pengumuman" class="{{ $activeTab === 'pengumuman' ? '' : 'hidden' }} space-y-6">
        
        <!-- Mode Pemeliharaan (Maintenance Mode) Toggle Card -->
        <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 p-6 shadow-sm">
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div class="flex items-start gap-4">
                    <div class="w-12 h-12 rounded-xl {{ $pengaturan->mode_maintenance ? 'bg-rose-100 text-rose-600 dark:bg-rose-950/60 dark:text-rose-400' : 'bg-pink-100 text-[#fa2f79] dark:bg-pink-950/60 dark:text-pink-400' }} flex items-center justify-center shrink-0">
                        <i data-lucide="{{ $pengaturan->mode_maintenance ? 'alert-triangle' : 'shield-check' }}" class="w-6 h-6"></i>
                    </div>
                    <div>
                        <div class="flex items-center gap-2">
                            <h2 class="text-base font-bold text-slate-800 dark:text-white">Status Mode Pemeliharaan (Maintenance)</h2>
                            <span class="px-2.5 py-0.5 rounded-full text-xs font-bold {{ $pengaturan->mode_maintenance ? 'bg-rose-500 text-white animate-pulse' : 'bg-[#fa2f79] text-white' }}">
                                {{ $pengaturan->mode_maintenance ? 'AKTIF (Situs Dikunci)' : 'TIDAK AKTIF (Situs Online)' }}
                            </span>
                        </div>
                        <p class="text-xs text-slate-500 dark:text-slate-400 mt-1 max-w-2xl leading-relaxed">
                            Jika mode pemeliharaan diaktifkan, seluruh calon siswa dan pengunjung publik akan otomatis dialihkan ke halaman pemeliharaan khusus, kecuali Petugas TU dan Kepala Sekolah yang dapat mengakses panel admin.
                        </p>
                    </div>
                </div>

                <form action="{{ route('admin.maintenance.toggle') }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin mengubah status mode pemeliharaan?');" class="shrink-0">
                    @csrf
                    <button type="submit" class="px-5 py-2.5 rounded-xl font-bold text-xs flex items-center gap-2 transition-all shadow-sm {{ $pengaturan->mode_maintenance ? 'bg-[#fa2f79] hover:bg-[#e11d48] text-white' : 'bg-rose-600 hover:bg-rose-700 text-white' }}">
                        <i data-lucide="power" class="w-4 h-4"></i>
                        {{ $pengaturan->mode_maintenance ? 'Nonaktifkan Mode Pemeliharaan' : 'Aktifkan Mode Pemeliharaan' }}
                    </button>
                </form>
            </div>
        </div>

        <!-- Layout Grid: Form Tambah & Daftar Pengumuman -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Form Buat Pengumuman Baru -->
            <div class="lg:col-span-1">
                <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 p-6 shadow-sm sticky top-6 space-y-4">
                    <div class="flex items-center gap-2 pb-3 border-b border-slate-100 dark:border-slate-800">
                        <i data-lucide="megaphone" class="w-5 h-5 text-[#fa2f79]"></i>
                        <h3 class="text-sm font-bold text-slate-800 dark:text-white">Buat Pengumuman Baru</h3>
                    </div>

                    <form action="{{ route('admin.pengumuman.simpan') }}" method="POST" class="space-y-4">
                        @csrf
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Judul Pengumuman *</label>
                            <input type="text" name="judul" required placeholder="Contoh: Jadwal Tes Wawancara Gelombang 1"
                                   class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-xs text-slate-800 dark:text-white focus:outline-none focus:ring-2 focus:ring-pink-400">
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Kategori / Tipe *</label>
                            <select name="tipe" required class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-xs text-slate-800 dark:text-white focus:outline-none focus:ring-2 focus:ring-pink-400">
                                <option value="info">Informasi (Biru)</option>
                                <option value="penting">Penting (Merah)</option>
                                <option value="peringatan">Peringatan (Kuning)</option>
                                <option value="maintenance">Maintenance (Ungu)</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Isi / Konten Pengumuman *</label>
                            <textarea name="isi_pengumuman" required rows="4" placeholder="Tuliskan isi pengumuman secara rinci di sini..."
                                      class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-xs text-slate-800 dark:text-white focus:outline-none focus:ring-2 focus:ring-pink-400 resize-none"></textarea>
                        </div>

                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Tanggal Mulai *</label>
                                <input type="date" name="tgl_mulai" required value="{{ date('Y-m-d') }}"
                                       class="w-full px-3 py-2 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-xs text-slate-800 dark:text-white focus:outline-none focus:ring-2 focus:ring-pink-400">
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Tanggal Selesai *</label>
                                <input type="date" name="tgl_selesai" required value="{{ date('Y-m-d', strtotime('+30 days')) }}"
                                       class="w-full px-3 py-2 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-xs text-slate-800 dark:text-white focus:outline-none focus:ring-2 focus:ring-pink-400">
                            </div>
                        </div>

                        <div class="space-y-2 pt-1">
                            <label class="flex items-center gap-2 text-xs text-slate-700 dark:text-slate-300 cursor-pointer">
                                <input type="checkbox" name="tampil_di_banner" value="1" checked class="w-4 h-4 rounded text-[#fa2f79] focus:ring-[#fa2f79]">
                                <span>Tampilkan sebagai Banner di Beranda</span>
                            </label>
                            <label class="flex items-center gap-2 text-xs text-slate-700 dark:text-slate-300 cursor-pointer">
                                <input type="checkbox" name="status_aktif" value="1" checked class="w-4 h-4 rounded text-[#fa2f79] focus:ring-[#fa2f79]">
                                <span>Status Langsung Aktif</span>
                            </label>
                        </div>

                        <button type="submit" class="w-full py-2.5 rounded-xl bg-[#fa2f79] hover:bg-[#e11d48] text-white font-bold text-xs flex items-center justify-center gap-2 shadow-md shadow-pink-500/20 transition-colors mt-2">
                            <i data-lucide="send" class="w-4 h-4"></i> Publikasikan Pengumuman
                        </button>
                    </form>
                </div>
            </div>

            <!-- Daftar Pengumuman Aktif & Arsip -->
            <div class="lg:col-span-2">
                <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 p-6 shadow-sm space-y-4">
                    <div class="flex items-center justify-between pb-3 border-b border-slate-100 dark:border-slate-800">
                        <div class="flex items-center gap-2">
                            <i data-lucide="list" class="w-5 h-5 text-[#fa2f79]"></i>
                            <h3 class="text-sm font-bold text-slate-800 dark:text-white">Daftar Pengumuman ({{ count($pengumuman ?? []) }})</h3>
                        </div>
                    </div>

                    @if(empty($pengumuman) || $pengumuman->isEmpty())
                        <div class="py-12 text-center text-slate-400">
                            <i data-lucide="inbox" class="w-12 h-12 mx-auto stroke-1 mb-2"></i>
                            <p class="text-xs">Belum ada pengumuman yang diterbitkan.</p>
                        </div>
                    @else
                        <div class="space-y-4">
                            @foreach($pengumuman as $item)
                                <div class="p-4 rounded-xl border border-slate-200 dark:border-slate-800 hover:border-slate-300 dark:hover:border-slate-700 transition-colors">
                                    <div class="flex items-start justify-between gap-3 mb-2">
                                        <div class="flex items-center gap-2 flex-wrap">
                                            @if($item->tipe === 'penting')
                                                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-rose-100 text-rose-700 dark:bg-rose-950 dark:text-rose-400">PENTING</span>
                                            @elseif($item->tipe === 'peringatan')
                                                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-amber-100 text-amber-700 dark:bg-amber-950 dark:text-amber-400">PERINGATAN</span>
                                            @elseif($item->tipe === 'maintenance')
                                                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-purple-100 text-purple-700 dark:bg-purple-950 dark:text-purple-400">MAINTENANCE</span>
                                            @else
                                                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-sky-100 text-sky-700 dark:bg-sky-950 dark:text-sky-400">INFORMASI</span>
                                            @endif

                                            @if($item->status_aktif)
                                                <span class="px-2 py-0.5 rounded text-[10px] font-semibold bg-pink-100 text-pink-700 dark:bg-pink-950 dark:text-pink-400">Aktif</span>
                                            @else
                                                <span class="px-2 py-0.5 rounded text-[10px] font-semibold bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-400">Nonaktif</span>
                                            @endif

                                            @if($item->tampil_di_banner)
                                                <span class="px-2 py-0.5 rounded text-[10px] font-semibold bg-indigo-50 text-indigo-600 dark:bg-indigo-950/60 dark:text-indigo-400">Banner On</span>
                                            @endif
                                        </div>

                                        <span class="text-[10px] text-slate-400 shrink-0">
                                            {{ $item->tgl_mulai ? $item->tgl_mulai->format('d/m/Y') : '-' }} s/d {{ $item->tgl_selesai ? $item->tgl_selesai->format('d/m/Y') : '-' }}
                                        </span>
                                    </div>

                                    <h4 class="text-xs font-bold text-slate-900 dark:text-white mb-1.5">{{ $item->judul }}</h4>
                                    <p class="text-xs text-slate-600 dark:text-slate-300 leading-relaxed whitespace-pre-line mb-3">{{ $item->isi_pengumuman }}</p>

                                    {{-- Tombol Aksi --}}
                                    <div class="flex items-center gap-2 pt-2 border-t border-slate-100 dark:border-slate-800">
                                        {{-- Edit --}}
                                        <button type="button"
                                            onclick="bukaModalEditPengumuman({{ json_encode($item) }})"
                                            class="flex items-center gap-1 px-2.5 py-1.5 rounded-lg bg-pink-50 hover:bg-pink-100 dark:bg-pink-950/40 dark:hover:bg-pink-900/60 text-[#fa2f79] text-[10px] font-bold transition-colors">
                                            <i data-lucide="edit-3" class="w-3.5 h-3.5"></i> Edit
                                        </button>

                                        {{-- Toggle Aktif/Nonaktif --}}
                                        <form action="{{ route('admin.pengumuman.toggle', $item->id_pengumuman) }}" method="POST">
                                            @csrf
                                            <button type="submit"
                                                class="flex items-center gap-1 px-2.5 py-1.5 rounded-lg text-[10px] font-bold transition-colors {{ $item->status_aktif ? 'bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-600 dark:text-slate-300' : 'bg-green-50 hover:bg-green-100 dark:bg-green-950/40 text-green-700 dark:text-green-400' }}">
                                                <i data-lucide="{{ $item->status_aktif ? 'eye-off' : 'eye' }}" class="w-3.5 h-3.5"></i>
                                                {{ $item->status_aktif ? 'Nonaktifkan' : 'Aktifkan' }}
                                            </button>
                                        </form>

                                        {{-- Hapus --}}
                                        <form action="{{ route('admin.pengumuman.hapus', $item->id_pengumuman) }}" method="POST"
                                            onsubmit="return confirm('Hapus pengumuman \'{{ addslashes($item->judul) }}\'?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit"
                                                class="flex items-center gap-1 px-2.5 py-1.5 rounded-lg bg-rose-50 hover:bg-rose-100 dark:bg-rose-950/40 dark:hover:bg-rose-900/60 text-rose-600 dark:text-rose-400 text-[10px] font-bold transition-colors">
                                                <i data-lucide="trash-2" class="w-3.5 h-3.5"></i> Hapus
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        </div>

    </div>
    @endif

    @if($isKepsek)
    <!-- ======================================================== -->
    <!-- TAB 1 KEPSEK: PAYMENT GATEWAY (MIDTRANS)                 -->
    <!-- ======================================================== -->
    <div id="tab-content-gateway" class="{{ $activeTab === 'gateway' ? '' : 'hidden' }} space-y-6">
        
        <!-- Header Card Payment Gateway -->
        <div class="bg-[#121c2d] rounded-2xl border border-slate-800 p-6 shadow-xl text-white">
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div class="flex items-start gap-4">
                    <div class="w-12 h-12 rounded-2xl bg-indigo-600/30 border border-indigo-500/40 text-indigo-400 flex items-center justify-center shrink-0">
                        <i data-lucide="credit-card" class="w-6 h-6"></i>
                    </div>
                    <div>
                        <h2 class="text-lg font-black text-white tracking-tight">Pengaturan Payment Gateway (Midtrans)</h2>
                        <p class="text-xs text-slate-400 mt-1">
                            Kelola integrasi pembayaran online QRIS, GoPay, ShopeePay, OVO, & Virtual Account Bank untuk PPDB SMK IB.
                        </p>
                    </div>
                </div>

                <div class="flex items-center gap-2 self-start md:self-center">
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold {{ $isGatewayActive ? 'bg-pink-500/20 text-pink-300 border border-pink-500/40' : 'bg-slate-800 text-slate-400 border border-slate-700' }}">
                        <span class="w-2 h-2 rounded-full {{ $isGatewayActive ? 'bg-pink-400 animate-pulse' : 'bg-slate-500' }}"></span>
                        {{ $isGatewayActive ? 'Gateway Aktif' : 'Gateway Nonaktif' }}
                    </span>
                    <span class="px-2.5 py-1 rounded-full text-[11px] font-mono font-bold uppercase tracking-wider {{ $isProd ? 'bg-rose-500/20 text-rose-300 border border-rose-500/40' : 'bg-amber-500/20 text-amber-300 border border-amber-500/40' }}">
                        {{ $isProd ? 'Production / Live' : 'Sandbox (Uji Coba)' }}
                    </span>
                </div>
            </div>
        </div>

        <!-- Form Konfigurasi Midtrans -->
        <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 p-6 shadow-sm">
            <form action="{{ route('admin.pengaturan.payment_gateway') }}" method="POST" class="space-y-6">
                @csrf

                <!-- Lingkungan Transaksi (Sandbox vs Production) -->
                <div>
                    <h3 class="text-xs font-bold uppercase tracking-wider text-[#fa2f79] dark:text-pink-400 mb-3 flex items-center gap-2">
                        <i data-lucide="server" class="w-4 h-4"></i> Lingkungan Operasional (Environment)
                    </h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <label class="relative flex items-start gap-3 p-4 rounded-xl border border-slate-200 dark:border-slate-800 hover:border-pink-300 dark:hover:border-pink-800 cursor-pointer transition-all bg-slate-50/50 dark:bg-slate-800/40">
                            <input type="radio" name="midtrans_environment" value="sandbox" {{ !$isProd ? 'checked' : '' }}
                                   class="mt-1 text-[#fa2f79] focus:ring-[#fa2f79]">
                            <div>
                                <span class="block text-xs font-bold text-slate-800 dark:text-white">Mode Sandbox (Uji Coba Gratis)</span>
                                <span class="text-[11px] text-slate-500 dark:text-slate-400 block mt-0.5">
                                    Simulasi pembayaran tanpa uang sungguhan menggunakan Midtrans Simulator QRIS & VA.
                                </span>
                            </div>
                        </label>

                        <label class="relative flex items-start gap-3 p-4 rounded-xl border border-slate-200 dark:border-slate-800 hover:border-pink-300 dark:hover:border-pink-800 cursor-pointer transition-all bg-slate-50/50 dark:bg-slate-800/40">
                            <input type="radio" name="midtrans_environment" value="production" {{ $isProd ? 'checked' : '' }}
                                   class="mt-1 text-[#fa2f79] focus:ring-[#fa2f79]">
                            <div>
                                <span class="block text-xs font-bold text-slate-800 dark:text-white">Mode Production (Live / Uang Nyata)</span>
                                <span class="text-[11px] text-slate-500 dark:text-slate-400 block mt-0.5">
                                    Transaksi riil langsung terhubung ke rekening sekolah (Pastikan akun Midtrans telah diverifikasi legalitas).
                                </span>
                            </div>
                        </label>
                    </div>
                </div>

                <!-- Toggle Aktif/Nonaktif Gateway -->
                <div>
                    <label class="flex items-center gap-3 p-3.5 rounded-xl border border-slate-200 dark:border-slate-800 bg-slate-50/40 dark:bg-slate-800/30 cursor-pointer">
                        <input type="checkbox" name="midtrans_is_active" value="1" {{ $isGatewayActive ? 'checked' : '' }}
                               class="w-4 h-4 rounded text-[#fa2f79] focus:ring-[#fa2f79]">
                        <div>
                            <span class="block text-xs font-bold text-slate-800 dark:text-white">Aktifkan Metode Pembayaran Online (Midtrans)</span>
                            <span class="text-[11px] text-slate-400 block">Jika dimatikan, calon siswa hanya dapat memilih metode Bayar Tunai di Loket TU.</span>
                        </div>
                    </label>
                </div>

                <!-- Kunci Kredensial API -->
                <div>
                    <h3 class="text-xs font-bold uppercase tracking-wider text-[#fa2f79] dark:text-pink-400 mb-3 flex items-center gap-2">
                        <i data-lucide="key" class="w-4 h-4"></i> Kredensial API Midtrans (Access Keys)
                    </h3>
                    <div class="space-y-4">
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                                Client Key (Kunci Publik) *
                            </label>
                            <input type="text" name="midtrans_client_key" required value="{{ old('midtrans_client_key', $clientKey) }}"
                                   placeholder="SB-Mid-client-..."
                                   class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-xs text-slate-800 dark:text-white focus:outline-none focus:ring-2 focus:ring-[#fa2f79] font-mono">
                            <span class="text-[11px] text-slate-400 mt-1 block">Didapatkan dari Midtrans Dashboard &rsaquo; Settings &rsaquo; Access Keys.</span>
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                                Server Key (Kunci Rahasia) *
                            </label>
                            <div class="relative">
                                <input type="password" id="midtrans_server_key" name="midtrans_server_key" required
                                       value="{{ old('midtrans_server_key', $pengaturan->midtrans_server_key) }}"
                                       placeholder="SB-Mid-server-..."
                                       class="w-full pl-3.5 pr-10 py-2.5 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-xs text-slate-800 dark:text-white focus:outline-none focus:ring-2 focus:ring-[#fa2f79] font-mono">
                                <button type="button" onclick="toggleServerKeyVisibility(this)" class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600 dark:hover:text-slate-200">
                                    <i data-lucide="eye" class="w-4 h-4"></i>
                                </button>
                            </div>
                            <span class="text-[11px] text-slate-400 mt-1 block">Kunci ini tersimpan terenkripsi dan diverifikasi otomatis pada saat menerima webhook pembayaran.</span>
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                                Merchant ID (Opsional)
                            </label>
                            <input type="text" name="midtrans_merchant_id" value="{{ old('midtrans_merchant_id', $merchantId) }}"
                                   placeholder="Contoh: G123456789"
                                   class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-xs text-slate-800 dark:text-white focus:outline-none focus:ring-2 focus:ring-[#fa2f79] font-mono">
                        </div>
                    </div>
                </div>

                <!-- Informasi Endpoint Webhook & Callback -->
                <div>
                    <h3 class="text-xs font-bold uppercase tracking-wider text-[#fa2f79] dark:text-pink-400 mb-3 flex items-center gap-2">
                        <i data-lucide="network" class="w-4 h-4"></i> Konfigurasi Webhook Notifikasi
                    </h3>
                    <div class="p-4 rounded-xl bg-slate-50 dark:bg-slate-800/60 border border-slate-200 dark:border-slate-700/60 space-y-3">
                        <div>
                            <span class="block text-[11px] font-bold text-slate-500 dark:text-slate-400">Payment Notification URL (Webhook)</span>
                            <div class="flex items-center gap-2 mt-1">
                                <input type="text" readonly id="webhook-url-input" value="{{ $webhookUrl }}"
                                       class="flex-1 px-3 py-1.5 rounded-lg bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-xs font-mono text-slate-700 dark:text-slate-300">
                                <button type="button" onclick="copyToClipboard('webhook-url-input', this)" class="px-3 py-1.5 rounded-lg bg-slate-200 dark:bg-slate-700 hover:bg-pink-100 text-xs font-bold text-slate-700 dark:text-slate-200 transition-colors shrink-0">
                                    Salin
                                </button>
                            </div>
                            <span class="text-[10px] text-slate-400 mt-1 block">Daftarkan URL ini pada Midtrans Dashboard &rsaquo; Settings &rsaquo; Configuration &rsaquo; Payment Notification URL.</span>
                        </div>
                    </div>
                </div>

                <!-- Tombol Aksi Uji Koneksi & Simpan -->
                <div class="pt-4 border-t border-slate-100 dark:border-slate-800 flex flex-col sm:flex-row items-center justify-between gap-3">
                    <button type="button" onclick="testMidtransConnection()" id="btn-test-connection"
                            class="px-4 py-2.5 rounded-xl border border-slate-300 dark:border-slate-700 hover:bg-slate-100 dark:hover:bg-slate-800 text-slate-700 dark:text-slate-200 font-bold text-xs flex items-center gap-2 transition-all">
                        <i data-lucide="plug-zap" class="w-4 h-4 text-indigo-500"></i>
                        <span id="btn-test-text">Uji Koneksi Midtrans</span>
                    </button>

                    <button type="submit" class="px-6 py-2.5 rounded-xl bg-[#fa2f79] hover:bg-[#e11d48] text-white font-bold text-xs shadow-md shadow-pink-500/25 transition-all flex items-center gap-2">
                        <i data-lucide="save" class="w-4 h-4"></i> Simpan Pengaturan Gateway
                    </button>
                </div>

                <!-- Box Hasil Uji Koneksi -->
                <div id="test-connection-alert" class="hidden"></div>
            </form>
        </div>
    </div>

    <!-- ======================================================== -->
    <!-- TAB 2 KEPSEK: VOUCHER & BEASISWA                         -->
    <!-- ======================================================== -->
    <div id="tab-content-voucher" class="{{ $activeTab === 'voucher' ? '' : 'hidden' }} space-y-6">
        
        <!-- Header & KPI Voucher -->
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 p-4 shadow-sm">
                <span class="text-[11px] font-bold text-slate-400 block">Total Voucher</span>
                <span class="text-xl font-black text-slate-800 dark:text-white mt-1 block">{{ $voucherStats['total'] ?? count($vouchers) }}</span>
            </div>
            <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 p-4 shadow-sm">
                <span class="text-[11px] font-bold text-pink-500 block">Voucher Aktif</span>
                <span class="text-xl font-black text-[#fa2f79] dark:text-pink-400 mt-1 block">{{ $voucherStats['aktif'] ?? 0 }}</span>
            </div>
            <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 p-4 shadow-sm">
                <span class="text-[11px] font-bold text-slate-400 block">Total Klaim Terpakai</span>
                <span class="text-xl font-black text-slate-800 dark:text-white mt-1 block">{{ $voucherStats['terpakai'] ?? 0 }}x</span>
            </div>
            <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 p-4 shadow-sm">
                <span class="text-[11px] font-bold text-purple-500 block">Total Diskon Diberikan</span>
                <span class="text-base font-black text-purple-600 dark:text-purple-400 mt-1 block font-mono">Rp {{ number_format($voucherStats['total_potongan'] ?? 0, 0, ',', '.') }}</span>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            {{-- Form Tambah Voucher --}}
            <div class="p-6 rounded-3xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-sm space-y-4">
                <div class="flex items-center gap-2 pb-3 border-b border-slate-100 dark:border-slate-800">
                    <i data-lucide="plus-circle" class="w-5 h-5 text-[#fa2f79]"></i>
                    <h3 class="text-base font-bold text-slate-900 dark:text-white">Buat Voucher Baru</h3>
                </div>

                <form action="{{ route('admin.master.voucher.simpan') }}" method="POST" class="space-y-3 text-xs">
                    @csrf
                    <div>
                        <label class="block font-bold text-slate-700 dark:text-slate-300 mb-1">Kode Voucher <span class="text-rose-500">*</span></label>
                        <input type="text" name="kode_voucher" required value="{{ old('kode_voucher') }}" placeholder="Cth: KHALIFAH2026, YATIM100"
                            class="w-full px-3 py-2 rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-xs font-mono font-bold uppercase tracking-wider focus:outline-none focus:ring-2 focus:ring-[#fa2f79]">
                        <span class="text-[10px] text-slate-400 mt-0.5 block">Huruf kapital & angka tanpa spasi.</span>
                    </div>

                    <div>
                        <label class="block font-bold text-slate-700 dark:text-slate-300 mb-1">Nama Promo / Keterangan Singkat</label>
                        <input type="text" name="nama_voucher" value="{{ old('nama_voucher') }}" placeholder="Cth: Diskon Beasiswa Prestasi Gelombang 1"
                            class="w-full px-3 py-2 rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-xs focus:outline-none focus:ring-2 focus:ring-[#fa2f79]">
                    </div>

                    <div class="grid grid-cols-2 gap-2">
                        <div>
                            <label class="block font-bold text-slate-700 dark:text-slate-300 mb-1">Jenis Diskon <span class="text-rose-500">*</span></label>
                            <select name="tipe" id="create_tipe" onchange="toggleTipeInput('create')"
                                class="w-full px-3 py-2 rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-xs font-bold focus:outline-none focus:ring-2 focus:ring-[#fa2f79]">
                                <option value="nominal" {{ old('tipe') === 'nominal' ? 'selected' : '' }}>Nominal (Rp)</option>
                                <option value="persen" {{ old('tipe') === 'persen' ? 'selected' : '' }}>Persentase (%)</option>
                            </select>
                        </div>
                        <div>
                            <label class="block font-bold text-slate-700 dark:text-slate-300 mb-1">Besaran <span class="text-rose-500">*</span></label>
                            <input type="number" name="nilai_potongan" id="create_nilai_potongan" required min="1" step="any"
                                value="{{ old('nilai_potongan', 25000) }}"
                                class="w-full px-3 py-2 rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-xs font-bold font-mono focus:outline-none focus:ring-2 focus:ring-[#fa2f79]">
                        </div>
                    </div>

                    <div>
                        <label class="block font-bold text-slate-700 dark:text-slate-300 mb-1">Batas Kuota Penggunaan <span class="text-rose-500">*</span></label>
                        <input type="number" name="kuota_voucher" required min="1" value="{{ old('kuota_voucher', 50) }}"
                            class="w-full px-3 py-2 rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-xs font-bold font-mono focus:outline-none focus:ring-2 focus:ring-[#fa2f79]">
                    </div>

                    <div class="grid grid-cols-2 gap-2">
                        <div>
                            <label class="block font-bold text-slate-700 dark:text-slate-300 mb-1">Mulai Berlaku</label>
                            <input type="date" name="berlaku_mulai" value="{{ old('berlaku_mulai', date('Y-m-d')) }}"
                                class="w-full px-3 py-2 rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-xs focus:outline-none focus:ring-2 focus:ring-[#fa2f79]">
                        </div>
                        <div>
                            <label class="block font-bold text-slate-700 dark:text-slate-300 mb-1">Berlaku Sampai</label>
                            <input type="date" name="berlaku_sampai" value="{{ old('berlaku_sampai') }}"
                                class="w-full px-3 py-2 rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-xs focus:outline-none focus:ring-2 focus:ring-[#fa2f79]">
                        </div>
                    </div>

                    <div>
                        <label class="block font-bold text-slate-700 dark:text-slate-300 mb-1">Syarat & Ketentuan</label>
                        <textarea name="deskripsi" rows="2" placeholder="Informasi promo untuk calon siswa..."
                            class="w-full px-3 py-2 rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-xs focus:outline-none focus:ring-2 focus:ring-[#fa2f79]">{{ old('deskripsi') }}</textarea>
                    </div>

                    <button type="submit" class="w-full py-2.5 rounded-xl bg-[#fa2f79] hover:bg-[#e11d48] text-white font-bold text-xs shadow-md shadow-pink-500/25 transition-all flex items-center justify-center gap-2">
                        <i data-lucide="check" class="w-4 h-4"></i> Simpan & Terbitkan Voucher
                    </button>
                </form>
            </div>

            {{-- Daftar Voucher --}}
            <div class="lg:col-span-2 p-6 rounded-3xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-sm space-y-4">
                <div class="flex items-center justify-between pb-3 border-b border-slate-100 dark:border-slate-800">
                    <div>
                        <h3 class="text-base font-bold text-slate-900 dark:text-white flex items-center gap-2">
                            <i data-lucide="tags" class="w-5 h-5 text-[#fa2f79]"></i>
                            Daftar Voucher & Ketentuan Promo
                        </h3>
                        <p class="text-xs text-slate-400">Kelola status aktif, edit masa berlaku, dan pantau batas penggunaan voucher</p>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs">
                        <thead class="bg-slate-50 dark:bg-slate-800/60 text-slate-500 uppercase font-bold text-[10px] tracking-wider">
                            <tr>
                                <th class="p-3">Kode & Nama</th>
                                <th class="p-3">Jenis Potongan</th>
                                <th class="p-3">Masa Berlaku</th>
                                <th class="p-3">Penggunaan / Batas</th>
                                <th class="p-3">Status</th>
                                <th class="p-3 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                            @forelse($vouchers as $v)
                            @php
                                $terpakai   = (int)$v->kuota_terpakai;
                                $kuota      = (int)$v->kuota_voucher;
                                $persenPakai = $kuota > 0 ? min(100, round(($terpakai / $kuota) * 100)) : 0;
                                $isExpired   = $v->isExpired();
                                $isNotStarted = $v->isNotStartedYet();
                                $isFull      = $terpakai >= $kuota;
                            @endphp
                            <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-800/40 transition-colors">
                                <td class="p-3">
                                    <span class="font-mono font-extrabold text-[#fa2f79] dark:text-pink-400 text-sm tracking-wide">{{ $v->kode_voucher }}</span>
                                    <div class="text-[11px] font-semibold text-slate-800 dark:text-slate-200 mt-0.5">{{ $v->nama_voucher ?: '-' }}</div>
                                </td>
                                <td class="p-3">
                                    <span class="px-2 py-0.5 rounded-md text-[10px] font-bold uppercase {{ $v->tipe === 'persen' ? 'bg-purple-100 text-purple-800 dark:bg-purple-950 dark:text-purple-300' : 'bg-blue-100 text-blue-800 dark:bg-blue-950 dark:text-blue-300' }}">
                                        {{ $v->tipe === 'persen' ? 'Persentase' : 'Nominal' }}
                                    </span>
                                    <div class="font-mono font-bold text-slate-900 dark:text-white mt-1">
                                        {{ $v->tipe === 'nominal' ? 'Rp ' . number_format($v->nilai_potongan, 0, ',', '.') : rtrim(rtrim(number_format((float)$v->nilai_potongan, 2, ',', '.'), '0'), ',') . '%' }}
                                    </div>
                                </td>
                                <td class="p-3 text-[11px]">
                                    @if($v->berlaku_mulai && $v->berlaku_sampai)
                                        <span>{{ $v->berlaku_mulai->format('d/m/Y') }} &ndash; {{ $v->berlaku_sampai->format('d/m/Y') }}</span>
                                    @elseif($v->berlaku_sampai)
                                        <span>s.d. {{ $v->berlaku_sampai->format('d/m/Y') }}</span>
                                    @elseif($v->berlaku_mulai)
                                        <span>Mulai {{ $v->berlaku_mulai->format('d/m/Y') }}</span>
                                    @else
                                        <span class="text-slate-400 italic">Selalu Berlaku</span>
                                    @endif
                                </td>
                                <td class="p-3 min-w-[130px]">
                                    <div class="flex items-center justify-between text-[11px] font-mono mb-1">
                                        <span class="font-bold text-slate-800 dark:text-slate-200">{{ $terpakai }} / {{ $kuota }}</span>
                                        <span class="text-[10px] text-slate-400">({{ $v->sisaKuota() }} sisa)</span>
                                    </div>
                                    <div class="w-full bg-slate-100 dark:bg-slate-700 h-1.5 rounded-full overflow-hidden">
                                        <div class="h-full rounded-full {{ $persenPakai >= 100 ? 'bg-rose-500' : ($persenPakai > 70 ? 'bg-amber-500' : 'bg-[#fa2f79]') }}" style="width: {{ $persenPakai }}%"></div>
                                    </div>
                                </td>
                                <td class="p-3">
                                    @if(!$v->status_aktif)
                                        <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-400">Nonaktif</span>
                                    @elseif($isExpired)
                                        <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-rose-100 text-rose-800 dark:bg-rose-950 dark:text-rose-300">Kedaluwarsa</span>
                                    @elseif($isFull)
                                        <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-amber-100 text-amber-800 dark:bg-amber-950 dark:text-amber-300">Kuota Habis</span>
                                    @else
                                        <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-pink-100 text-pink-800 dark:bg-pink-950 dark:text-pink-300 flex items-center gap-1 w-fit">
                                            <span class="w-1.5 h-1.5 rounded-full bg-pink-500 animate-pulse"></span> Aktif
                                        </span>
                                    @endif
                                </td>
                                <td class="p-3 text-right">
                                    <div class="flex items-center justify-end gap-1.5">
                                        <form action="{{ route('admin.master.voucher.toggle', $v->id_voucher) }}" method="POST">
                                            @csrf
                                            <button type="submit"
                                                class="px-2.5 py-1.5 rounded-lg text-[11px] font-bold transition-all flex items-center gap-1 {{ $v->status_aktif ? 'bg-amber-50 hover:bg-amber-100 text-amber-700 dark:bg-amber-950/40 dark:text-amber-300 border border-amber-200 dark:border-amber-800' : 'bg-pink-50 hover:bg-pink-100 text-[#fa2f79] dark:bg-pink-950/40 dark:text-pink-300 border border-pink-200 dark:border-pink-800' }}">
                                                <i data-lucide="{{ $v->status_aktif ? 'power-off' : 'power' }}" class="w-3.5 h-3.5"></i>
                                                <span>{{ $v->status_aktif ? 'Nonaktifkan' : 'Aktifkan' }}</span>
                                            </button>
                                        </form>
                                        <button type="button" onclick="openEditVoucherModal({{ json_encode($v) }})" title="Edit Voucher"
                                            class="px-2.5 py-1.5 rounded-lg bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 font-bold text-[11px] transition-colors flex items-center gap-1">
                                            <i data-lucide="edit-3" class="w-3.5 h-3.5"></i>
                                            <span>Edit</span>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="p-8 text-center text-slate-400">
                                    <i data-lucide="inbox" class="w-8 h-8 mx-auto mb-2 text-slate-300"></i>
                                    Belum ada voucher diskon. Gunakan form di samping untuk membuat voucher baru.
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </div>

    <!-- ======================================================== -->
    <!-- TAB 3 KEPSEK: GELOMBANG PENDAFTARAN (CRUD LENGKAP)       -->
    <!-- ======================================================== -->
    <div id="tab-content-gelombang" class="{{ $activeTab === 'gelombang' ? '' : 'hidden' }} space-y-6">
        
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Form Tambah Gelombang Baru -->
            <div class="p-6 rounded-3xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-sm space-y-4">
                <div class="flex items-center gap-2 pb-3 border-b border-slate-100 dark:border-slate-800">
                    <i data-lucide="calendar-plus" class="w-5 h-5 text-[#fa2f79]"></i>
                    <h3 class="text-base font-bold text-slate-900 dark:text-white">Tambah Gelombang Baru</h3>
                </div>

                <form action="{{ route('admin.master.gelombang.simpan') }}" method="POST" class="space-y-3 text-xs">
                    @csrf
                    <div>
                        <label class="block font-bold text-slate-700 dark:text-slate-300 mb-1">Nama Gelombang *</label>
                        <input type="text" name="nama_gelombang" required placeholder="Contoh: Gelombang 3 (Susulan)"
                               class="w-full px-3 py-2 rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-xs focus:outline-none focus:ring-2 focus:ring-[#fa2f79]">
                    </div>

                    <div>
                        <label class="block font-bold text-slate-700 dark:text-slate-300 mb-1">Tahun Ajaran *</label>
                        <input type="text" name="tahun_ajaran" required value="2026/2027"
                               class="w-full px-3 py-2 rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-xs focus:outline-none focus:ring-2 focus:ring-[#fa2f79]">
                    </div>

                    <div class="grid grid-cols-2 gap-2">
                        <div>
                            <label class="block font-bold text-slate-700 dark:text-slate-300 mb-1">Tgl Mulai *</label>
                            <input type="date" name="tgl_buka" required value="{{ date('Y-m-d') }}"
                                   class="w-full px-2 py-2 rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-xs focus:outline-none focus:ring-2 focus:ring-[#fa2f79]">
                        </div>
                        <div>
                            <label class="block font-bold text-slate-700 dark:text-slate-300 mb-1">Tgl Selesai *</label>
                            <input type="date" name="tgl_tutup" required value="{{ date('Y-m-d', strtotime('+30 days')) }}"
                                   class="w-full px-2 py-2 rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-xs focus:outline-none focus:ring-2 focus:ring-[#fa2f79]">
                        </div>
                    </div>

                    <div>
                        <label class="block font-bold text-slate-700 dark:text-slate-300 mb-1">Tarif Biaya Formulir (Rp) *</label>
                        <input type="number" name="biaya_pendaftaran" required min="0" step="5000" value="100000"
                               class="w-full px-3 py-2 rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-xs font-bold font-mono focus:outline-none focus:ring-2 focus:ring-[#fa2f79]">
                    </div>

                    <div>
                        <label class="block font-bold text-slate-700 dark:text-slate-300 mb-1">Status Langsung Aktif?</label>
                        <select name="status_aktif" class="w-full px-3 py-2 rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-xs focus:outline-none focus:ring-2 focus:ring-[#fa2f79]">
                            <option value="1">Aktif Sekarang</option>
                            <option value="0">Tidak Aktif (Jadwal Nanti)</option>
                        </select>
                    </div>

                    <button type="submit" class="w-full py-2.5 rounded-xl bg-[#fa2f79] hover:bg-[#e11d48] text-white font-bold text-xs shadow-md shadow-pink-500/25 transition-colors flex items-center justify-center gap-2 mt-2">
                        <i data-lucide="check" class="w-4 h-4"></i> Simpan Gelombang Baru
                    </button>
                </form>
            </div>

            <!-- List Gelombang Pendaftaran -->
            <div class="lg:col-span-2 p-6 rounded-3xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-sm space-y-4">
                <div class="flex items-center justify-between pb-3 border-b border-slate-100 dark:border-slate-800">
                    <div>
                        <h3 class="text-base font-bold text-slate-900 dark:text-white flex items-center gap-2">
                            <i data-lucide="calendar" class="w-5 h-5 text-[#fa2f79]"></i>
                            Daftar Gelombang Pendaftaran PPDB
                        </h3>
                        <p class="text-xs text-slate-400">Kelola jadwal buka/tutup, edit biaya pendaftaran, dan ubah status aktif gelombang</p>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs">
                        <thead class="bg-slate-50 dark:bg-slate-800 text-slate-500 uppercase font-bold text-[10px]">
                            <tr>
                                <th class="p-3">Gelombang</th>
                                <th class="p-3">Rentang Jadwal</th>
                                <th class="p-3">Biaya Formulir</th>
                                <th class="p-3">Pendaftar</th>
                                <th class="p-3">Status</th>
                                <th class="p-3 text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                            @forelse($gelombangs as $g)
                            <tr>
                                <td class="p-3">
                                    <strong class="text-slate-900 dark:text-white block">{{ $g->nama_gelombang }}</strong>
                                    <span class="text-[10px] text-slate-400">T.A {{ $g->tahun_ajaran }}</span>
                                </td>
                                <td class="p-3 text-slate-600 dark:text-slate-300">
                                    {{ $g->tgl_buka ? \Carbon\Carbon::parse($g->tgl_buka)->format('d/m/Y') : '-' }} s/d {{ $g->tgl_tutup ? \Carbon\Carbon::parse($g->tgl_tutup)->format('d/m/Y') : '-' }}
                                </td>
                                <td class="p-3 font-mono font-bold text-slate-900 dark:text-white">
                                    Rp {{ number_format($g->biaya_pendaftaran, 0, ',', '.') }}
                                </td>
                                <td class="p-3 font-bold">{{ $g->pendaftar_count ?? 0 }} Siswa</td>
                                <td class="p-3">
                                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold 
                                        {{ $g->status_aktif ? 'bg-pink-100 text-pink-800 dark:bg-pink-950 dark:text-pink-300' : 'bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-400' }}">
                                        {{ $g->status_aktif ? 'AKTIF' : 'NONAKTIF' }}
                                    </span>
                                </td>
                                <td class="p-3">
                                    <div class="flex items-center justify-center gap-1.5">
                                        <!-- Tombol Edit -->
                                        <button type="button"
                                            onclick="bukaModalEditGelombang({{ json_encode($g) }})"
                                            class="flex items-center gap-1 px-2.5 py-1.5 rounded-lg bg-pink-50 hover:bg-pink-100 dark:bg-pink-950/40 dark:hover:bg-pink-900/60 text-[#fa2f79] text-[10px] font-bold transition-colors"
                                            title="Edit Gelombang">
                                            <i data-lucide="edit-3" class="w-3.5 h-3.5"></i>
                                            Edit
                                        </button>

                                        <!-- Tombol Toggle ON/OFF -->
                                        <form action="{{ route('admin.master.gelombang.toggle', $g->id_gelombang) }}" method="POST"
                                            onsubmit="return confirm('Ubah status gelombang \'{{ addslashes($g->nama_gelombang) }}\' menjadi {{ $g->status_aktif ? 'NONAKTIF' : 'AKTIF' }}?')">
                                            @csrf
                                            <button type="submit"
                                                class="flex items-center gap-1 px-2.5 py-1.5 rounded-lg text-[10px] font-bold transition-colors {{ $g->status_aktif ? 'bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-600 dark:text-slate-300' : 'bg-green-50 hover:bg-green-100 dark:bg-green-950/40 dark:hover:bg-green-900/60 text-green-700 dark:text-green-400' }}"
                                                title="{{ $g->status_aktif ? 'Nonaktifkan' : 'Aktifkan' }}">
                                                <i data-lucide="{{ $g->status_aktif ? 'toggle-right' : 'toggle-left' }}" class="w-3.5 h-3.5"></i>
                                                {{ $g->status_aktif ? 'Nonaktifkan' : 'Aktifkan' }}
                                            </button>
                                        </form>

                                        <!-- Tombol Hapus (hanya jika tidak ada pendaftar) -->
                                        @if(($g->pendaftar_count ?? 0) === 0)
                                        <form action="{{ route('admin.master.gelombang.hapus', $g->id_gelombang) }}" method="POST"
                                            onsubmit="return confirm('PERINGATAN: Hapus gelombang \'{{ addslashes($g->nama_gelombang) }}\'? Tindakan ini tidak dapat dibatalkan.')">
                                            @csrf
                                            <button type="submit"
                                                class="flex items-center gap-1 px-2.5 py-1.5 rounded-lg bg-rose-50 hover:bg-rose-100 dark:bg-rose-950/40 dark:hover:bg-rose-900/60 text-rose-600 dark:text-rose-400 text-[10px] font-bold transition-colors"
                                                title="Hapus Gelombang">
                                                <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
                                                Hapus
                                            </button>
                                        </form>
                                        @else
                                        <span class="flex items-center gap-1 px-2.5 py-1.5 rounded-lg bg-slate-50 dark:bg-slate-800 text-slate-400 text-[10px] font-bold cursor-not-allowed" title="Tidak bisa dihapus karena memiliki data pendaftar">
                                            <i data-lucide="lock" class="w-3.5 h-3.5"></i>
                                            Terkunci
                                        </span>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="p-8 text-center text-slate-400">
                                    <i data-lucide="inbox" class="w-8 h-8 mx-auto mb-2 text-slate-300"></i>
                                    Belum ada gelombang pendaftaran yang dibuat.
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </div>

    <!-- ======================================================== -->
    <!-- TAB 4 KEPSEK: BIAYA FORMULIR PENDAFTARAN PER GELOMBANG   -->
    <!-- ======================================================== -->
    <div id="tab-content-biaya_pendaftaran" class="{{ $activeTab === 'biaya_pendaftaran' ? '' : 'hidden' }} space-y-6">
        <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 p-6 shadow-sm">
            <div class="flex items-start gap-4">
                <div class="w-12 h-12 rounded-xl bg-amber-100 text-amber-600 dark:bg-amber-950/60 dark:text-amber-400 flex items-center justify-center shrink-0">
                    <i data-lucide="receipt" class="w-6 h-6"></i>
                </div>
                <div>
                    <h2 class="text-base font-bold text-slate-800 dark:text-white">Pengaturan Biaya Formulir Pendaftaran</h2>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-1 leading-relaxed">
                        Atur nominal biaya pendaftaran formulir PPDB untuk masing-masing gelombang masuk. Siswa yang mendaftar pada gelombang tertentu akan ditagihkan biaya sesuai nominal yang tersimpan di sini (melalui Midtrans QRIS atau Loket TU).
                    </p>
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 p-6 shadow-sm">
            <form action="{{ route('admin.pengaturan.biaya_pendaftaran') }}" method="POST" class="space-y-6">
                @csrf
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="border-b border-slate-200 dark:border-slate-800 text-[11px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">
                                <th class="py-3 px-4">Gelombang</th>
                                <th class="py-3 px-4">Tahun Ajaran</th>
                                <th class="py-3 px-4">Periode Tanggal</th>
                                <th class="py-3 px-4">Status Aktif</th>
                                <th class="py-3 px-4 w-56">Biaya Formulir (Rp) *</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-800 text-xs">
                            @forelse($gelombangs ?? [] as $gel)
                            <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/50 transition-colors">
                                <td class="py-3.5 px-4 font-bold text-slate-800 dark:text-white">
                                    {{ $gel->nama_gelombang }}
                                </td>
                                <td class="py-3.5 px-4 text-slate-600 dark:text-slate-300 font-mono">
                                    {{ $gel->tahun_ajaran }}
                                </td>
                                <td class="py-3.5 px-4 text-slate-500 dark:text-slate-400 text-[11px]">
                                    {{ \Carbon\Carbon::parse($gel->tgl_buka)->translatedFormat('d M Y') }} s/d {{ \Carbon\Carbon::parse($gel->tgl_tutup)->translatedFormat('d M Y') }}
                                </td>
                                <td class="py-3.5 px-4">
                                    @if($gel->status_aktif)
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[10px] font-bold bg-pink-100 text-pink-700 dark:bg-pink-950/60 dark:text-pink-400 border border-pink-300 dark:border-pink-800">
                                        <i data-lucide="check" class="w-3 h-3"></i> Sedang Aktif
                                    </span>
                                    @else
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[10px] font-semibold bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-400">
                                        Tidak Aktif
                                    </span>
                                    @endif
                                </td>
                                <td class="py-3.5 px-4">
                                    <div class="relative">
                                        <span class="absolute left-3 top-1/2 -translate-y-1/2 text-xs font-bold text-slate-400">Rp</span>
                                        <input type="number" step="1000" min="0" required
                                               name="gelombang[{{ $gel->id_gelombang }}][biaya_pendaftaran]"
                                               value="{{ old('gelombang.'.$gel->id_gelombang.'.biaya_pendaftaran', (int)$gel->biaya_pendaftaran) }}"
                                               class="w-full pl-9 pr-3 py-2 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-xs font-bold text-slate-800 dark:text-white focus:outline-none focus:ring-2 focus:ring-[#fa2f79] font-mono">
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="py-6 text-center text-slate-400 text-xs">
                                    Belum ada data gelombang pendaftaran.
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="pt-4 border-t border-slate-100 dark:border-slate-800 flex flex-col sm:flex-row items-center justify-between gap-3">
                    <p class="text-[11px] text-slate-400">
                        * Ubah nominal di atas lalu klik tombol simpan untuk memperbarui biaya tagihan formulir.
                    </p>
                    <button type="submit" class="px-5 py-2.5 rounded-xl bg-[#fa2f79] hover:bg-[#e11d48] text-white font-bold text-xs shadow-md shadow-pink-500/25 transition-all flex items-center gap-2">
                        <i data-lucide="save" class="w-4 h-4"></i> Simpan Biaya Pendaftaran
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- ======================================================== -->
    <!-- TAB 5 KEPSEK: BIAYA DAFTAR ULANG SISWA DITERIMA          -->
    <!-- ======================================================== -->
    <div id="tab-content-biaya_daftar_ulang" class="{{ $activeTab === 'biaya_daftar_ulang' ? '' : 'hidden' }} space-y-6">
        <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 p-6 shadow-sm">
            <div class="flex items-start gap-4">
                <div class="w-12 h-12 rounded-xl bg-pink-100 text-[#fa2f79] dark:bg-pink-950/60 dark:text-pink-400 flex items-center justify-center shrink-0">
                    <i data-lucide="badge-check" class="w-6 h-6"></i>
                </div>
                <div>
                    <h2 class="text-base font-bold text-slate-800 dark:text-white">Pengaturan Biaya Daftar Ulang (Siswa Diterima)</h2>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-1 leading-relaxed">
                        Atur nominal biaya daftar ulang dan rincian komponen (seragam, atribut, orientasi MPLS, dll) yang akan dibayarkan oleh calon siswa baru yang telah dinyatakan Diterima dalam seleksi PPDB.
                    </p>
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 p-6 shadow-sm">
            <form action="{{ route('admin.pengaturan.biaya_daftar_ulang') }}" method="POST" class="space-y-6">
                @csrf
                <div class="space-y-4">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                            Nominal Total Biaya Daftar Ulang (Rp) *
                        </label>
                        <div class="relative max-w-md">
                            <span class="absolute left-3.5 top-1/2 -translate-y-1/2 text-xs font-bold text-slate-400">Rp</span>
                            <input type="number" step="1000" min="0" required name="biaya_daftar_ulang"
                                   value="{{ old('biaya_daftar_ulang', (int)($pengaturan->biaya_daftar_ulang ?? 1500000)) }}"
                                   class="w-full pl-10 pr-4 py-2.5 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-xs font-bold text-slate-800 dark:text-white focus:outline-none focus:ring-2 focus:ring-[#fa2f79] font-mono text-sm">
                        </div>
                        <span class="text-[11px] text-slate-400 mt-1 block">Nominal ini akan menjadi tagihan pembayaran online (Midtrans QRIS) maupun tunai di Loket TU.</span>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                            Rincian Komponen Biaya Daftar Ulang
                        </label>
                        <textarea name="keterangan_daftar_ulang" rows="4"
                                  placeholder="Contoh:&#10;1. Paket Seragam 3 Stel (Osis, Pramuka, Wearpack)&#10;2. Atribut Sekolah Lengkap (Topi, Dasi, Sabuk)&#10;3. Kegiatan Pra-MPLS & MPLS&#10;4. Asuransi Kecelakaan Siswa 1 Tahun"
                                  class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-xs text-slate-800 dark:text-white focus:outline-none focus:ring-2 focus:ring-[#fa2f79] leading-relaxed font-sans">{{ old('keterangan_daftar_ulang', $pengaturan->keterangan_daftar_ulang) }}</textarea>
                        <span class="text-[11px] text-slate-400 mt-1 block">Rincian ini akan tampil transparan pada portal Calon Siswa di halaman Daftar Ulang.</span>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                            Petunjuk Pembayaran Offline (Loket TU)
                        </label>
                        <textarea name="instruksi_daftar_ulang" rows="3"
                                  placeholder="Contoh: Bagi calon siswa yang memilih pembayaran tunai, silakan datang langsung ke Loket TU Kampus SMK dengan membawa dokumen kelengkapan fisik dan lembar SKL."
                                  class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-xs text-slate-800 dark:text-white focus:outline-none focus:ring-2 focus:ring-[#fa2f79] leading-relaxed font-sans">{{ old('instruksi_daftar_ulang', $pengaturan->instruksi_daftar_ulang) }}</textarea>
                    </div>
                </div>

                <div class="pt-4 border-t border-slate-100 dark:border-slate-800 flex items-center justify-end">
                    <button type="submit" class="px-5 py-2.5 rounded-xl bg-[#fa2f79] hover:bg-[#e11d48] text-white font-bold text-xs shadow-md shadow-pink-500/25 transition-all flex items-center gap-2">
                        <i data-lucide="save" class="w-4 h-4"></i> Simpan Biaya Daftar Ulang
                    </button>
                </div>
            </form>
        </div>
    </div>
    @endif

</div>

{{-- ======================================================== --}}
{{-- MODALS SECTION                                           --}}
{{-- ======================================================== --}}

@if($isKepsek)
{{-- MODAL EDIT VOUCHER --}}
<div id="editVoucherModal" class="fixed inset-0 z-50 bg-slate-950/75 backdrop-blur-sm hidden flex items-center justify-center p-4">
    <div class="bg-white dark:bg-slate-900 rounded-3xl max-w-lg w-full border border-slate-200 dark:border-slate-800 shadow-2xl overflow-hidden">
        <div class="px-6 py-4 bg-slate-900 text-white flex items-center justify-between border-b border-slate-800">
            <div class="flex items-center gap-2">
                <div class="w-8 h-8 rounded-xl bg-[#fa2f79] text-white flex items-center justify-center">
                    <i data-lucide="edit" class="w-4 h-4"></i>
                </div>
                <div>
                    <h3 class="text-sm font-extrabold" id="editModalTitle">Edit Voucher Diskon</h3>
                    <span class="text-[10px] text-pink-300 font-medium">Ubah parameter, masa berlaku, atau batas kuota voucher</span>
                </div>
            </div>
            <button type="button" onclick="closeEditVoucherModal()" class="text-slate-400 hover:text-white transition-colors">
                <i data-lucide="x" class="w-5 h-5"></i>
            </button>
        </div>

        <form id="editVoucherForm" method="POST" action="" class="p-6 space-y-4 text-xs">
            @csrf
            @method('PUT')

            <div>
                <label class="block font-bold text-slate-700 dark:text-slate-300 mb-1">Kode Voucher <span class="text-rose-500">*</span></label>
                <input type="text" id="edit_kode_voucher" name="kode_voucher" required
                    class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-xs font-mono font-bold uppercase tracking-wider focus:outline-none focus:ring-2 focus:ring-[#fa2f79]">
            </div>

            <div>
                <label class="block font-bold text-slate-700 dark:text-slate-300 mb-1">Nama Promo / Keterangan Singkat</label>
                <input type="text" id="edit_nama_voucher" name="nama_voucher"
                    class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-xs focus:outline-none focus:ring-2 focus:ring-[#fa2f79]">
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block font-bold text-slate-700 dark:text-slate-300 mb-1">Jenis Diskon <span class="text-rose-500">*</span></label>
                    <select name="tipe" id="edit_tipe" onchange="toggleTipeInput('edit')"
                        class="w-full px-3 py-2.5 rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-xs font-bold focus:outline-none focus:ring-2 focus:ring-[#fa2f79]">
                        <option value="nominal">Nominal Rupiah (Rp)</option>
                        <option value="persen">Persentase (%)</option>
                    </select>
                </div>
                <div>
                    <label class="block font-bold text-slate-700 dark:text-slate-300 mb-1">Besaran Potongan <span class="text-rose-500">*</span></label>
                    <input type="number" name="nilai_potongan" id="edit_nilai_potongan" required min="1" step="any"
                        class="w-full px-3 py-2.5 rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-xs font-bold font-mono focus:outline-none focus:ring-2 focus:ring-[#fa2f79]">
                </div>
            </div>

            <div>
                <label class="block font-bold text-slate-700 dark:text-slate-300 mb-1">Batas Kuota Penggunaan <span class="text-rose-500">*</span></label>
                <input type="number" name="kuota_voucher" id="edit_kuota_voucher" required min="1"
                    class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-xs font-bold font-mono focus:outline-none focus:ring-2 focus:ring-[#fa2f79]">
                <span id="edit_kuota_hint" class="text-[10px] text-slate-400 mt-1 block"></span>
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block font-bold text-slate-700 dark:text-slate-300 mb-1">Mulai Berlaku</label>
                    <input type="date" name="berlaku_mulai" id="edit_berlaku_mulai"
                        class="w-full px-3 py-2 rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-xs focus:outline-none focus:ring-2 focus:ring-[#fa2f79]">
                </div>
                <div>
                    <label class="block font-bold text-slate-700 dark:text-slate-300 mb-1">Berlaku Sampai</label>
                    <input type="date" name="berlaku_sampai" id="edit_berlaku_sampai"
                        class="w-full px-3 py-2 rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-xs focus:outline-none focus:ring-2 focus:ring-[#fa2f79]">
                </div>
            </div>

            <div>
                <label class="block font-bold text-slate-700 dark:text-slate-300 mb-1">Syarat & Ketentuan</label>
                <textarea name="deskripsi" id="edit_deskripsi" rows="2"
                    class="w-full px-3 py-2.5 rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-xs focus:outline-none focus:ring-2 focus:ring-[#fa2f79]"></textarea>
            </div>

            <div class="pt-2">
                <label class="flex items-center gap-2 cursor-pointer">
                    <input type="checkbox" name="status_aktif" id="edit_status_aktif" value="1" class="rounded border-slate-300 text-[#fa2f79] focus:ring-[#fa2f79]">
                    <span class="font-bold text-slate-800 dark:text-slate-200">Aktifkan Voucher Ini</span>
                </label>
            </div>

            <div class="pt-4 border-t border-slate-100 dark:border-slate-800 flex items-center justify-end gap-2">
                <button type="button" onclick="closeEditVoucherModal()" class="px-4 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 font-bold transition-colors">
                    Batal
                </button>
                <button type="submit" class="px-5 py-2 rounded-xl bg-[#fa2f79] hover:bg-[#e11d48] text-white font-bold shadow-md shadow-pink-500/25 transition-all">
                    Simpan Perubahan
                </button>
            </div>
        </form>
    </div>
</div>

{{-- MODAL EDIT GELOMBANG --}}
<div id="modalEditGelombang" class="fixed inset-0 z-50 bg-slate-950/75 backdrop-blur-sm hidden flex items-center justify-center p-4">
    <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200 dark:border-slate-800 p-6 max-w-lg w-full shadow-xl">
        <div class="flex items-center justify-between pb-4 border-b border-slate-100 dark:border-slate-800 mb-4">
            <h3 class="text-sm font-bold text-slate-800 dark:text-white flex items-center gap-2">
                <i data-lucide="edit-3" class="w-4 h-4 text-[#fa2f79]"></i> Edit Data Gelombang
            </h3>
            <button onclick="tutupModalEditGelombang()" class="p-1 rounded-lg text-slate-400 hover:text-white hover:bg-slate-800">
                <i data-lucide="x" class="w-4 h-4"></i>
            </button>
        </div>

        <form id="formEditGelombang" method="POST" class="space-y-3 text-xs">
            @csrf
            @method('PUT')

            <div>
                <label class="block font-bold text-slate-700 dark:text-slate-300 mb-1">Nama Gelombang</label>
                <input type="text" id="edit_nama_gelombang" name="nama_gelombang" required class="w-full px-3 py-2 rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-xs">
            </div>

            <div>
                <label class="block font-bold text-slate-700 dark:text-slate-300 mb-1">Tahun Ajaran</label>
                <input type="text" id="edit_tahun_ajaran" name="tahun_ajaran" required class="w-full px-3 py-2 rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-xs">
            </div>

            <div class="grid grid-cols-2 gap-2">
                <div>
                    <label class="block font-bold text-slate-700 dark:text-slate-300 mb-1">Tgl Mulai</label>
                    <input type="date" id="edit_tgl_buka" name="tgl_buka" required class="w-full px-2 py-2 rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-xs">
                </div>
                <div>
                    <label class="block font-bold text-slate-700 dark:text-slate-300 mb-1">Tgl Selesai</label>
                    <input type="date" id="edit_tgl_tutup" name="tgl_tutup" required class="w-full px-2 py-2 rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-xs">
                </div>
            </div>

            <div>
                <label class="block font-bold text-slate-700 dark:text-slate-300 mb-1">Tarif Biaya Formulir (Rp)</label>
                <input type="number" id="edit_biaya_pendaftaran" name="biaya_pendaftaran" required min="0" step="5000" class="w-full px-3 py-2 rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-xs font-bold font-mono">
            </div>

            <div>
                <label class="block font-bold text-slate-700 dark:text-slate-300 mb-1">Status</label>
                <select id="edit_status_aktif" name="status_aktif" class="w-full px-3 py-2 rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-xs">
                    <option value="1">Aktif</option>
                    <option value="0">Tidak Aktif</option>
                </select>
            </div>

            <div class="pt-3 flex gap-2">
                <button type="submit" class="flex-1 py-2.5 rounded-xl bg-[#fa2f79] hover:bg-[#e11d48] text-white font-bold text-xs shadow-md shadow-pink-500/25 transition-colors">
                    Simpan Perubahan
                </button>
                <button type="button" onclick="tutupModalEditGelombang()" class="px-4 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-600 dark:text-slate-300 font-bold text-xs transition-colors">
                    Batal
                </button>
            </div>
        </form>
    </div>
</div>
@endif

@if(!$isKepsek)
{{-- MODAL EDIT PENGUMUMAN (TU) --}}
<div id="modalEditPengumuman" class="fixed inset-0 z-50 bg-slate-950/75 backdrop-blur-sm hidden flex items-center justify-center p-4">
    <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200 dark:border-slate-800 p-6 max-w-lg w-full shadow-xl max-h-[90vh] overflow-y-auto">
        <div class="flex items-center justify-between pb-4 border-b border-slate-100 dark:border-slate-800 mb-4">
            <h3 class="text-sm font-bold text-slate-800 dark:text-white flex items-center gap-2">
                <i data-lucide="edit-3" class="w-4 h-4 text-[#fa2f79]"></i> Edit Pengumuman
            </h3>
            <button onclick="tutupModalEditPengumuman()" class="p-1 rounded-lg text-slate-400 hover:text-white hover:bg-slate-800">
                <i data-lucide="x" class="w-4 h-4"></i>
            </button>
        </div>

        <form id="formEditPengumuman" method="POST" class="space-y-3 text-xs">
            @csrf
            @method('PUT')

            <div>
                <label class="block font-bold text-slate-700 dark:text-slate-300 mb-1">Judul Pengumuman *</label>
                <input type="text" id="edit_judul" name="judul" required
                    class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-xs text-slate-800 dark:text-white focus:outline-none focus:ring-2 focus:ring-pink-400">
            </div>

            <div>
                <label class="block font-bold text-slate-700 dark:text-slate-300 mb-1">Kategori / Tipe *</label>
                <select id="edit_tipe" name="tipe" required
                    class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-xs text-slate-800 dark:text-white focus:outline-none focus:ring-2 focus:ring-pink-400">
                    <option value="info">Informasi (Biru)</option>
                    <option value="penting">Penting (Merah)</option>
                    <option value="peringatan">Peringatan (Kuning)</option>
                    <option value="maintenance">Maintenance (Ungu)</option>
                </select>
            </div>

            <div>
                <label class="block font-bold text-slate-700 dark:text-slate-300 mb-1">Isi / Konten Pengumuman *</label>
                <textarea id="edit_isi_pengumuman" name="isi_pengumuman" required rows="4"
                    class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-xs text-slate-800 dark:text-white focus:outline-none focus:ring-2 focus:ring-pink-400 resize-none"></textarea>
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block font-bold text-slate-700 dark:text-slate-300 mb-1">Tanggal Mulai *</label>
                    <input type="date" id="edit_tgl_mulai" name="tgl_mulai" required
                        class="w-full px-3 py-2 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-xs text-slate-800 dark:text-white focus:outline-none focus:ring-2 focus:ring-pink-400">
                </div>
                <div>
                    <label class="block font-bold text-slate-700 dark:text-slate-300 mb-1">Tanggal Selesai *</label>
                    <input type="date" id="edit_tgl_selesai" name="tgl_selesai" required
                        class="w-full px-3 py-2 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-xs text-slate-800 dark:text-white focus:outline-none focus:ring-2 focus:ring-pink-400">
                </div>
            </div>

            <div class="flex items-center gap-6 pt-1">
                <label class="flex items-center gap-2 text-xs text-slate-700 dark:text-slate-300 cursor-pointer">
                    <input type="checkbox" id="edit_tampil_di_banner" name="tampil_di_banner" value="1" class="w-4 h-4 rounded text-[#fa2f79] focus:ring-[#fa2f79]">
                    <span>Tampil di Banner</span>
                </label>
                <label class="flex items-center gap-2 text-xs text-slate-700 dark:text-slate-300 cursor-pointer">
                    <input type="checkbox" id="edit_status_aktif" name="status_aktif" value="1" class="w-4 h-4 rounded text-[#fa2f79] focus:ring-[#fa2f79]">
                    <span>Status Aktif</span>
                </label>
            </div>

            <div class="pt-3 flex gap-2">
                <button type="submit" class="flex-1 py-2.5 rounded-xl bg-[#fa2f79] hover:bg-[#e11d48] text-white font-bold text-xs shadow-md shadow-pink-500/25 transition-colors flex items-center justify-center gap-2">
                    <i data-lucide="save" class="w-4 h-4"></i> Simpan Perubahan
                </button>
                <button type="button" onclick="tutupModalEditPengumuman()" class="px-4 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-600 dark:text-slate-300 font-bold text-xs transition-colors">
                    Batal
                </button>
            </div>
        </form>
    </div>
</div>
@endif

@endsection

@push('scripts')
<script>
    // 1. Switch Main Tab
    function switchMainTab(tab) {
        const isKepsek = @json($isKepsek);
        const tabs = isKepsek 
            ? ['gateway', 'voucher', 'gelombang', 'biaya_pendaftaran', 'biaya_daftar_ulang']
            : ['profil', 'pengumuman'];

        tabs.forEach(t => {
            const content = document.getElementById('tab-content-' + t);
            const btn = document.getElementById('tab-btn-' + t);
            if (content) {
                if (t === tab) {
                    content.classList.remove('hidden');
                } else {
                    content.classList.add('hidden');
                }
            }
            if (btn) {
                if (t === tab) {
                    btn.className = "flex-1 flex items-center justify-center gap-2.5 px-4 py-2.5 rounded-xl font-bold text-xs transition-all whitespace-nowrap bg-[#fa2f79] text-white shadow-md shadow-pink-500/25";
                } else {
                    btn.className = "flex-1 flex items-center justify-center gap-2.5 px-4 py-2.5 rounded-xl font-bold text-xs transition-all whitespace-nowrap text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white";
                }
            }
        });

        const newUrl = new URL(window.location);
        newUrl.searchParams.set('tab', tab);
        window.history.replaceState({}, '', newUrl);

        if (window.lucide) {
            lucide.createIcons();
        }
    }

    // 2. Voucher Functions
    function toggleTipeInput(prefix) {
        const tipe = document.getElementById(prefix + '_tipe').value;
        const input = document.getElementById(prefix + '_nilai_potongan');
        if (tipe === 'persen') {
            input.setAttribute('max', '100');
            input.setAttribute('placeholder', 'Contoh: 15 (artinya 15%)');
        } else {
            input.removeAttribute('max');
            input.setAttribute('placeholder', 'Contoh: 25000');
        }
    }

    function openEditVoucherModal(voucher) {
        document.getElementById('editVoucherForm').action = "{{ url('admin/master/voucher') }}/" + voucher.id_voucher;
        document.getElementById('edit_kode_voucher').value = voucher.kode_voucher;
        document.getElementById('edit_nama_voucher').value = voucher.nama_voucher || '';
        document.getElementById('edit_tipe').value = voucher.tipe;
        document.getElementById('edit_nilai_potongan').value = parseFloat(voucher.nilai_potongan);
        document.getElementById('edit_kuota_voucher').value = voucher.kuota_voucher;
        document.getElementById('edit_kuota_voucher').setAttribute('min', Math.max(1, voucher.kuota_terpakai || 0));
        document.getElementById('edit_kuota_hint').innerText = "Sudah terpakai " + (voucher.kuota_terpakai || 0) + " kali. Kuota baru minimal " + Math.max(1, voucher.kuota_terpakai || 0) + ".";
        document.getElementById('edit_berlaku_mulai').value = voucher.berlaku_mulai ? voucher.berlaku_mulai.substring(0, 10) : '';
        document.getElementById('edit_berlaku_sampai').value = voucher.berlaku_sampai ? voucher.berlaku_sampai.substring(0, 10) : '';
        document.getElementById('edit_deskripsi').value = voucher.deskripsi || '';
        document.getElementById('edit_status_aktif').checked = Boolean(voucher.status_aktif);
        toggleTipeInput('edit');
        document.getElementById('editVoucherModal').classList.remove('hidden');
        if (window.lucide) lucide.createIcons();
    }

    function closeEditVoucherModal() {
        document.getElementById('editVoucherModal').classList.add('hidden');
    }

    // 3. Gelombang Functions
    function bukaModalEditGelombang(g) {
        const form = document.getElementById('formEditGelombang');
        form.action = "{{ url('admin/master/gelombang') }}/" + g.id_gelombang;

        document.getElementById('edit_nama_gelombang').value    = g.nama_gelombang || '';
        document.getElementById('edit_tahun_ajaran').value      = g.tahun_ajaran || '';
        document.getElementById('edit_tgl_buka').value          = g.tgl_buka ? g.tgl_buka.substring(0, 10) : '';
        document.getElementById('edit_tgl_tutup').value         = g.tgl_tutup ? g.tgl_tutup.substring(0, 10) : '';
        document.getElementById('edit_biaya_pendaftaran').value = g.biaya_pendaftaran ? parseInt(g.biaya_pendaftaran) : '';
        document.getElementById('edit_status_aktif').value      = g.status_aktif ? '1' : '0';

        document.getElementById('modalEditGelombang').classList.remove('hidden');
        if (window.lucide) lucide.createIcons();
    }

    function tutupModalEditGelombang() {
        document.getElementById('modalEditGelombang').classList.add('hidden');
    }

    // 4. Pengumuman Functions
    function bukaModalEditPengumuman(item) {
        const form = document.getElementById('formEditPengumuman');
        form.action = "{{ url('admin/pengumuman') }}/" + item.id_pengumuman;

        document.getElementById('edit_judul').value          = item.judul || '';
        document.getElementById('edit_tipe').value           = item.tipe || 'info';
        document.getElementById('edit_isi_pengumuman').value = item.isi_pengumuman || '';
        document.getElementById('edit_tgl_mulai').value      = item.tgl_mulai ? item.tgl_mulai.substring(0, 10) : '';
        document.getElementById('edit_tgl_selesai').value    = item.tgl_selesai ? item.tgl_selesai.substring(0, 10) : '';
        document.getElementById('edit_tampil_di_banner').checked = Boolean(item.tampil_di_banner);
        document.getElementById('edit_status_aktif').checked     = Boolean(item.status_aktif);

        document.getElementById('modalEditPengumuman').classList.remove('hidden');
        if (window.lucide) lucide.createIcons();
    }

    function tutupModalEditPengumuman() {
        document.getElementById('modalEditPengumuman').classList.add('hidden');
    }

    // 5. Payment Gateway Helpers
    function toggleServerKeyVisibility(btn) {
        const input = document.getElementById('midtrans_server_key');
        if (!input) return;
        if (input.type === 'password') {
            input.type = 'text';
            btn.innerHTML = `<i data-lucide="eye-off" class="w-4 h-4"></i>`;
        } else {
            input.type = 'password';
            btn.innerHTML = `<i data-lucide="eye" class="w-4 h-4"></i>`;
        }
        if (window.lucide) lucide.createIcons();
    }

    function copyToClipboard(elementId, btn) {
        const input = document.getElementById(elementId);
        if (!input) return;
        input.select();
        input.setSelectionRange(0, 99999);
        navigator.clipboard.writeText(input.value);

        const originalText = btn.innerText;
        btn.innerText = "Tersalin!";
        btn.classList.add('bg-[#fa2f79]', 'text-white');
        setTimeout(() => {
            btn.innerText = originalText;
            btn.classList.remove('bg-[#fa2f79]', 'text-white');
        }, 1500);
    }

    async function testMidtransConnection() {
        const btn = document.getElementById('btn-test-connection');
        const btnText = document.getElementById('btn-test-text');
        const alertBox = document.getElementById('test-connection-alert');
        if (!btn || !alertBox) return;

        const serverKeyInp = document.getElementById('midtrans_server_key').value.trim();
        const envRadio = document.querySelector('input[name="midtrans_environment"]:checked');
        const environment = envRadio ? envRadio.value : 'sandbox';

        btn.disabled = true;
        btnText.innerText = "Memeriksa..";
        alertBox.className = "hidden";

        try {
            const res = await fetch("{{ route('admin.pengaturan.test_midtrans') }}", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({
                    server_key: serverKeyInp,
                    environment: environment
                })
            });

            const data = await res.json();
            alertBox.classList.remove('hidden');

            if (data.success) {
                alertBox.className = "p-3.5 rounded-2xl bg-pink-50 dark:bg-pink-950/40 border border-pink-300 dark:border-pink-800 text-pink-800 dark:text-pink-200 text-xs flex items-center gap-2";
                alertBox.innerHTML = `<i data-lucide="check-circle-2" class="w-4 h-4 text-[#fa2f79] shrink-0"></i><span>${data.message}</span>`;
            } else {
                alertBox.className = "p-3.5 rounded-2xl bg-rose-50 dark:bg-rose-950/40 border border-rose-300 dark:border-rose-800 text-rose-800 dark:text-rose-200 text-xs flex items-center gap-2";
                alertBox.innerHTML = `<i data-lucide="alert-circle" class="w-4 h-4 text-rose-600 shrink-0"></i><span>${data.message}</span>`;
            }
        } catch (err) {
            alertBox.classList.remove('hidden');
            alertBox.className = "p-3.5 rounded-2xl bg-rose-50 dark:bg-rose-950/40 border border-rose-300 dark:border-rose-800 text-rose-800 dark:text-rose-200 text-xs flex items-center gap-2";
            alertBox.innerHTML = `<i data-lucide="alert-circle" class="w-4 h-4 text-rose-600 shrink-0"></i><span>Gagal menghubungi server aplikasi untuk verifikasi.</span>`;
        } finally {
            btn.disabled = false;
            btnText.innerText = "Uji Koneksi Midtrans";
            if (window.lucide) lucide.createIcons();
        }
    }

    // Keyboard navigation (Escape to close modals)
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeEditVoucherModal();
            tutupModalEditGelombang();
            tutupModalEditPengumuman();
        }
    });
</script>
@endpush
