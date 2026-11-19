@extends('layouts.admin')

@section('title', 'Pengaturan Lembaga & Payment Gateway')
@section('header_title', 'Pengaturan Sekolah & Payment Gateway')

@section('content')
<div class="space-y-6">

    <!-- Flash Messages -->
    @if(session('success'))
    <div class="p-4 rounded-2xl bg-emerald-50 dark:bg-emerald-950/60 border border-emerald-200 dark:border-emerald-800 text-emerald-800 dark:text-emerald-200 text-xs font-semibold flex items-center justify-between">
        <div class="flex items-center gap-2">
            <i data-lucide="check-circle-2" class="w-4 h-4 text-pink-600"></i>
            <span>{{ session('success') }}</span>
        </div>
        <button type="button" onclick="this.parentElement.remove()" class="text-pink-600 hover:text-emerald-800">&times;</button>
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
        $activeTab = in_array(request('tab'), ['biaya_pendaftaran', 'biaya_daftar_ulang', 'gateway', 'profil', 'voucher']) ? request('tab') : 'profil';
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

    <!-- Top Navigation Tabs (5 Tab Terpadu) -->
    <div class="flex items-center gap-2 p-1.5 bg-slate-200/70 dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 w-full overflow-x-auto">
        <button type="button" onclick="switchMainTab('profil')" id="tab-btn-profil"
                class="flex-1 flex items-center justify-center gap-2.5 px-4 py-2.5 rounded-xl font-bold text-xs transition-all whitespace-nowrap {{ $activeTab === 'profil' ? 'bg-pink-600 text-white shadow-md' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white' }}">
            <i data-lucide="building-2" class="w-4 h-4"></i>
            <div class="text-left">
                <span class="block leading-tight">Profil Lembaga</span>
                <span class="text-[10px] opacity-80 block font-normal">Identitas & Legalitas</span>
            </div>
        </button>

        <button type="button" onclick="switchMainTab('biaya_pendaftaran')" id="tab-btn-biaya_pendaftaran"
                class="flex-1 flex items-center justify-center gap-2.5 px-4 py-2.5 rounded-xl font-bold text-xs transition-all whitespace-nowrap {{ $activeTab === 'biaya_pendaftaran' ? 'bg-pink-600 text-white shadow-md' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white' }}">
            <i data-lucide="receipt" class="w-4 h-4"></i>
            <div class="text-left">
                <span class="block leading-tight">Biaya Pendaftaran</span>
                <span class="text-[10px] opacity-80 block font-normal">Formulir Gelombang</span>
            </div>
        </button>

        <button type="button" onclick="switchMainTab('biaya_daftar_ulang')" id="tab-btn-biaya_daftar_ulang"
                class="flex-1 flex items-center justify-center gap-2.5 px-4 py-2.5 rounded-xl font-bold text-xs transition-all whitespace-nowrap {{ $activeTab === 'biaya_daftar_ulang' ? 'bg-pink-600 text-white shadow-md' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white' }}">
            <i data-lucide="badge-check" class="w-4 h-4"></i>
            <div class="text-left">
                <span class="block leading-tight">Biaya Daftar Ulang</span>
                <span class="text-[10px] opacity-80 block font-normal">Siswa Diterima</span>
            </div>
        </button>

        <button type="button" onclick="switchMainTab('gateway')" id="tab-btn-gateway"
                class="flex-1 flex items-center justify-center gap-2.5 px-4 py-2.5 rounded-xl font-bold text-xs transition-all whitespace-nowrap {{ $activeTab === 'gateway' ? 'bg-pink-600 text-white shadow-md' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white' }}">
            <i data-lucide="credit-card" class="w-4 h-4"></i>
            <div class="text-left">
                <span class="block leading-tight">Payment Gateway</span>
                <span class="text-[10px] opacity-80 block font-normal">QRIS, VA & Sandbox/Live</span>
            </div>
        </button>

        <button type="button" onclick="switchMainTab('voucher')" id="tab-btn-voucher"
                class="flex-1 flex items-center justify-center gap-2.5 px-4 py-2.5 rounded-xl font-bold text-xs transition-all whitespace-nowrap {{ $activeTab === 'voucher' ? 'bg-pink-600 text-white shadow-md' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white' }}">
            <i data-lucide="ticket" class="w-4 h-4"></i>
            <div class="text-left">
                <span class="block leading-tight">Voucher Diskon</span>
                <span class="text-[10px] opacity-80 block font-normal">Beasiswa & Promo</span>
            </div>
        </button>
    </div>

    <!-- ======================================================== -->
    <!-- TAB 1: PROFIL SEKOLAH (IDENTITAS RESMI) -->
    <!-- ======================================================== -->
    <div id="tab-content-profil" class="{{ $activeTab === 'profil' ? '' : 'hidden' }} space-y-6">
        <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 p-6 shadow-sm">
            <div class="flex items-start gap-4">
                <div class="w-12 h-12 rounded-xl bg-emerald-100 text-pink-600 dark:bg-emerald-950/60 dark:text-emerald-400 flex items-center justify-center shrink-0">
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
                    <h3 class="text-xs font-bold uppercase tracking-wider text-pink-600 dark:text-pink-400 mb-4 pb-2 border-b border-slate-100 dark:border-slate-800 flex items-center gap-2">
                        <i data-lucide="school" class="w-4 h-4"></i> Data Pokok Lembaga
                    </h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Nama Lengkap Sekolah *</label>
                            <input type="text" name="nama_sekolah" required value="{{ old('nama_sekolah', $pengaturan->nama_sekolah) }}"
                                   class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-xs text-slate-800 dark:text-white focus:outline-none focus:ring-2 focus:ring-pink-500 font-semibold">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Nomor Pokok Sekolah Nasional (NPSN) *</label>
                            <input type="text" name="npsn" required value="{{ old('npsn', $pengaturan->npsn) }}"
                                   class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-xs text-slate-800 dark:text-white focus:outline-none focus:ring-2 focus:ring-pink-500 font-mono">
                        </div>
                        <div class="md:col-span-2">
                            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Ijin Operasional / SK Pendirian *</label>
                            <input type="text" name="ijin_operasional" required value="{{ old('ijin_operasional', $pengaturan->ijin_operasional) }}"
                                   class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-xs text-slate-800 dark:text-white focus:outline-none focus:ring-2 focus:ring-pink-500">
                        </div>
                    </div>
                </div>

                <div>
                    <h3 class="text-xs font-bold uppercase tracking-wider text-pink-600 dark:text-pink-400 mb-4 pb-2 border-b border-slate-100 dark:border-slate-800 flex items-center gap-2">
                        <i data-lucide="map-pin" class="w-4 h-4"></i> Lokasi & Saluran Kontak
                    </h3>
                    <div class="space-y-4">
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Alamat Lengkap *</label>
                            <textarea name="alamat" rows="2" required class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-xs text-slate-800 dark:text-white focus:outline-none focus:ring-2 focus:ring-pink-500">{{ old('alamat', $pengaturan->alamat) }}</textarea>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Kota / Kabupaten *</label>
                                <input type="text" name="kota" required value="{{ old('kota', $pengaturan->kota) }}"
                                       class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-xs text-slate-800 dark:text-white focus:outline-none focus:ring-2 focus:ring-pink-500">
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Provinsi *</label>
                                <input type="text" name="provinsi" required value="{{ old('provinsi', $pengaturan->provinsi) }}"
                                       class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-xs text-slate-800 dark:text-white focus:outline-none focus:ring-2 focus:ring-pink-500">
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div>
                                <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">No. Telepon / WhatsApp Resmi *</label>
                                <input type="text" name="no_telp" required value="{{ old('no_telp', $pengaturan->no_telp) }}"
                                       class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-xs text-slate-800 dark:text-white focus:outline-none focus:ring-2 focus:ring-pink-500 font-mono">
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Email Resmi *</label>
                                <input type="email" name="email" required value="{{ old('email', $pengaturan->email) }}"
                                       class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-xs text-slate-800 dark:text-white focus:outline-none focus:ring-2 focus:ring-pink-500">
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Website Sekolah *</label>
                                <input type="text" name="website" required value="{{ old('website', $pengaturan->website) }}"
                                       class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-xs text-slate-800 dark:text-white focus:outline-none focus:ring-2 focus:ring-pink-500">
                            </div>
                        </div>
                    </div>
                </div>

                <div>
                    <h3 class="text-xs font-bold uppercase tracking-wider text-pink-600 dark:text-pink-400 mb-4 pb-2 border-b border-slate-100 dark:border-slate-800 flex items-center gap-2">
                        <i data-lucide="award" class="w-4 h-4"></i> Kepala Sekolah & Pengesah Dokumen
                    </h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Nama Lengkap Kepala Sekolah (Gelar) *</label>
                            <input type="text" name="nama_kepsek" required value="{{ old('nama_kepsek', $pengaturan->nama_kepsek) }}"
                                   class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-xs text-slate-800 dark:text-white focus:outline-none focus:ring-2 focus:ring-pink-500 font-semibold">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">NIP / NUPTK (Opsional)</label>
                            <input type="text" name="nip_kepsek" value="{{ old('nip_kepsek', $pengaturan->nip_kepsek) }}" placeholder="Isi '-' jika tidak ada"
                                   class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-xs text-slate-800 dark:text-white focus:outline-none focus:ring-2 focus:ring-pink-500 font-mono">
                        </div>
                    </div>
                </div>

                <div class="pt-4 border-t border-slate-100 dark:border-slate-800 flex items-center justify-end">
                    <button type="submit" class="px-6 py-2.5 rounded-xl bg-pink-600 hover:bg-pink-700 text-white font-bold text-xs flex items-center gap-2 shadow-sm transition-colors">
                        <i data-lucide="save" class="w-4 h-4"></i> Simpan Perubahan Profil
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- ======================================================== -->
    <!-- TAB 2: BIAYA FORMULIR PENDAFTARAN PER GELOMBANG -->
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
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-700 dark:bg-emerald-950/60 dark:text-emerald-400 border border-emerald-300 dark:border-emerald-800">
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
                                               class="w-full pl-9 pr-3 py-2 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-xs font-bold text-slate-800 dark:text-white focus:outline-none focus:ring-2 focus:ring-pink-500 font-mono">
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

                <div class="pt-4 border-t border-slate-100 dark:border-slate-800 flex items-center justify-between">
                    <p class="text-[11px] text-slate-400">
                        * Ubah nominal di atas lalu klik tombol simpan untuk memperbarui biaya tagihan formulir.
                    </p>
                    <button type="submit" class="px-5 py-2.5 rounded-xl bg-pink-600 hover:bg-pink-700 text-white font-bold text-xs shadow-md transition-all flex items-center gap-2">
                        <i data-lucide="save" class="w-4 h-4"></i> Simpan Biaya Pendaftaran
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- ======================================================== -->
    <!-- TAB 3: BIAYA DAFTAR ULANG SISWA DITERIMA -->
    <!-- ======================================================== -->
    <div id="tab-content-biaya_daftar_ulang" class="{{ $activeTab === 'biaya_daftar_ulang' ? '' : 'hidden' }} space-y-6">
        <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 p-6 shadow-sm">
            <div class="flex items-start gap-4">
                <div class="w-12 h-12 rounded-xl bg-teal-100 text-teal-600 dark:bg-teal-950/60 dark:text-teal-400 flex items-center justify-center shrink-0">
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
                                   class="w-full pl-10 pr-4 py-2.5 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-xs font-bold text-slate-800 dark:text-white focus:outline-none focus:ring-2 focus:ring-pink-500 font-mono text-sm">
                        </div>
                        <span class="text-[11px] text-slate-400 mt-1 block">Nominal ini akan menjadi tagihan pembayaran online (Midtrans QRIS) maupun tunai di Loket TU.</span>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                            Rincian Komponen Biaya Daftar Ulang
                        </label>
                        <textarea name="keterangan_daftar_ulang" rows="4"
                                  placeholder="Contoh:&#10;1. Paket Seragam 3 Stel (Osis, Pramuka, Wearpack)&#10;2. Atribut Sekolah Lengkap (Topi, Dasi, Sabuk)&#10;3. Kegiatan Pra-MPLS & MPLS&#10;4. Asuransi Kecelakaan Siswa 1 Tahun"
                                  class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-xs text-slate-800 dark:text-white focus:outline-none focus:ring-2 focus:ring-pink-500 leading-relaxed font-sans">{{ old('keterangan_daftar_ulang', $pengaturan->keterangan_daftar_ulang) }}</textarea>
                        <span class="text-[11px] text-slate-400 mt-1 block">Rincian ini akan tampil transparan pada portal Calon Siswa di halaman Daftar Ulang.</span>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                            Petunjuk Pembayaran Offline (Loket TU)
                        </label>
                        <textarea name="instruksi_daftar_ulang" rows="3"
                                  placeholder="Contoh: Bagi calon siswa yang memilih pembayaran tunai, silakan datang langsung ke Loket TU Kampus SMK dengan membawa dokumen kelengkapan fisik dan lembar SKL."
                                  class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-xs text-slate-800 dark:text-white focus:outline-none focus:ring-2 focus:ring-pink-500 leading-relaxed font-sans">{{ old('instruksi_daftar_ulang', $pengaturan->instruksi_daftar_ulang) }}</textarea>
                    </div>
                </div>

                <div class="pt-4 border-t border-slate-100 dark:border-slate-800 flex items-center justify-end">
                    <button type="submit" class="px-5 py-2.5 rounded-xl bg-pink-600 hover:bg-pink-700 text-white font-bold text-xs shadow-md transition-all flex items-center gap-2">
                        <i data-lucide="save" class="w-4 h-4"></i> Simpan Biaya Daftar Ulang
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- ======================================================== -->
    <!-- TAB 4: PAYMENT GATEWAY (MIDTRANS) - SESUAI SCREENSHOT REFERENSI -->
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
                            Kelola integrasi pembayaran online QRIS, GoPay, ShopeePay, OVO, & Virtual Account Bank untuk PPDB SMK Islam Bina Khalifah Bangsa.
                        </p>
                    </div>
                </div>

                <!-- Badges Status -->
                <div class="flex items-center gap-2 shrink-0 flex-wrap">
                    @if($isGatewayActive)
                    <span class="px-3.5 py-1.5 rounded-full text-xs font-bold bg-emerald-950/80 border border-emerald-500/40 text-emerald-400 flex items-center gap-1.5 shadow-sm">
                        <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span> Gateway Aktif
                    </span>
                    @else
                    <span class="px-3.5 py-1.5 rounded-full text-xs font-bold bg-slate-800 border border-slate-700 text-slate-400 flex items-center gap-1.5">
                        <span class="w-2 h-2 rounded-full bg-slate-500"></span> Gateway Nonaktif
                    </span>
                    @endif

                    @if($isProd)
                    <span class="px-3.5 py-1.5 rounded-full text-xs font-bold bg-emerald-950/80 border border-emerald-500/40 text-emerald-300 flex items-center gap-1.5">
                        <i data-lucide="shield-check" class="w-3.5 h-3.5 text-emerald-400"></i> Mode Production (Live)
                    </span>
                    @else
                    <span class="px-3.5 py-1.5 rounded-full text-xs font-bold bg-amber-950/80 border border-amber-500/40 text-amber-300 flex items-center gap-1.5">
                        <i data-lucide="zap" class="w-3.5 h-3.5 text-amber-400"></i> Mode Sandbox (Testing)
                    </span>
                    @endif
                </div>
            </div>
        </div>

        <!-- 2 Columns Layout Sesuai Screenshot -->
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
            
            <!-- LEFT COLUMN: Form Konfigurasi Kunci API & Mode (col-span-7) -->
            <div class="lg:col-span-7 space-y-6">
                <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200 dark:border-slate-800 p-6 sm:p-7 shadow-sm">
                    <div class="flex items-center justify-between pb-4 mb-5 border-b border-slate-100 dark:border-slate-800">
                        <h3 class="text-xs font-extrabold uppercase tracking-wider text-slate-800 dark:text-white flex items-center gap-2">
                            <i data-lucide="settings" class="w-4 h-4 text-pink-600"></i> Konfigurasi Kunci API & Mode
                        </h3>
                        <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 flex items-center gap-1">
                            <i data-lucide="lock" class="w-3 h-3 text-emerald-500"></i> Tersimpan Aman di Database
                        </span>
                    </div>

                    <form id="form-midtrans" action="{{ route('admin.pengaturan.payment_gateway') }}" method="POST" class="space-y-5">
                        @csrf

                        <!-- Aktifkan Pembayaran Online / QRIS Switch -->
                        <div class="p-4 rounded-2xl bg-slate-50 dark:bg-slate-800/60 border border-slate-200 dark:border-slate-700 flex items-center justify-between gap-4">
                            <div>
                                <span class="text-xs font-bold text-slate-900 dark:text-white block">Aktifkan Pembayaran Online / QRIS</span>
                                <span class="text-[11px] text-slate-500 dark:text-slate-400 block mt-0.5">
                                    Jika diaktifkan, opsi QRIS / Online akan muncul dan dapat digunakan oleh calon siswa pada halaman pembayaran.
                                </span>
                            </div>
                            <label class="relative inline-flex items-center cursor-pointer shrink-0">
                                <input type="checkbox" name="midtrans_is_active" value="1" {{ $isGatewayActive ? 'checked' : '' }} class="sr-only peer">
                                <div class="w-11 h-6 bg-slate-300 peer-focus:outline-none rounded-full peer dark:bg-slate-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-emerald-600"></div>
                            </label>
                        </div>

                        <!-- Mode Lingkungan (Environment) -->
                        <div>
                            <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-2">Mode Lingkungan (Environment)</label>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                <!-- Sandbox -->
                                <label class="relative p-4 rounded-2xl border-2 cursor-pointer transition-all flex flex-col justify-between space-y-2 {{ !$isProd ? 'border-emerald-500 bg-emerald-50/40 dark:bg-emerald-950/20' : 'border-slate-200 dark:border-slate-700 hover:border-slate-300' }}">
                                    <div class="flex items-center justify-between">
                                        <div class="flex items-center gap-2">
                                            <input type="radio" name="midtrans_environment" value="sandbox" {{ !$isProd ? 'checked' : '' }} class="text-pink-600 focus:ring-pink-500">
                                            <span class="text-xs font-bold text-slate-900 dark:text-white">Sandbox (Testing)</span>
                                        </div>
                                        <span class="px-2 py-0.5 rounded-full text-[9px] font-extrabold bg-amber-100 text-amber-800 dark:bg-amber-950 dark:text-amber-300">
                                            Uji Coba
                                        </span>
                                    </div>
                                    <p class="text-[10px] text-slate-500 dark:text-slate-400 leading-relaxed">
                                        Gunakan untuk pengujian tanpa uang asli. Dapatkan kunci dari <span class="font-mono text-pink-600">dashboard.sandbox.midtrans.com</span>
                                    </p>
                                </label>

                                <!-- Production -->
                                <label class="relative p-4 rounded-2xl border-2 cursor-pointer transition-all flex flex-col justify-between space-y-2 {{ $isProd ? 'border-emerald-500 bg-emerald-50/40 dark:bg-emerald-950/20' : 'border-slate-200 dark:border-slate-700 hover:border-slate-300' }}">
                                    <div class="flex items-center justify-between">
                                        <div class="flex items-center gap-2">
                                            <input type="radio" name="midtrans_environment" value="production" {{ $isProd ? 'checked' : '' }} class="text-pink-600 focus:ring-pink-500">
                                            <span class="text-xs font-bold text-slate-900 dark:text-white">Production (Live)</span>
                                        </div>
                                        <span class="px-2 py-0.5 rounded-full text-[9px] font-extrabold bg-emerald-100 text-emerald-800 dark:bg-emerald-950 dark:text-emerald-300">
                                            Uang Asli
                                        </span>
                                    </div>
                                    <p class="text-[10px] text-slate-500 dark:text-slate-400 leading-relaxed">
                                        Gunakan untuk transaksi nyata / uang asli. Gunakan kunci resmi dari <span class="font-mono text-pink-600">dashboard.midtrans.com</span>
                                    </p>
                                </label>
                            </div>
                        </div>

                        <!-- Midtrans Server Key (Masked & Protected) -->
                        <div>
                            <div class="flex items-center justify-between mb-1">
                                <label for="midtrans_server_key" class="block text-xs font-bold text-slate-700 dark:text-slate-300">
                                    Midtrans Server Key *
                                </label>
                                <span class="text-[10px] text-slate-400">Rahasia &bull; Digunakan untuk verifikasi transaksi</span>
                            </div>
                            <div class="relative">
                                <input type="password" id="midtrans_server_key" name="midtrans_server_key"
                                       value="{{ $serverKeyMasked }}"
                                       placeholder="{{ $hasServerKey ? '••••••••••••••••••••••••••••••••••••••••' : 'SB-Mid-server-xxxxxxxxxxxx' }}"
                                       class="w-full pl-3.5 pr-10 py-2.5 rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 text-xs font-mono text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-pink-500">
                                <button type="button" onclick="toggleServerKeyVisibility(this)" class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600 dark:hover:text-slate-200">
                                    <i data-lucide="eye" class="w-4 h-4"></i>
                                </button>
                            </div>
                            @if($hasServerKey)
                            <span class="text-[10px] text-pink-600 dark:text-pink-400 mt-1 block">
                                &check; Server Key sudah tersimpan aman di database. Biarkan kolom ini bertanda titik jika tidak ingin mengganti.
                            </span>
                            @endif
                        </div>

                        <!-- Midtrans Client Key -->
                        <div>
                            <div class="flex items-center justify-between mb-1">
                                <label for="midtrans_client_key" class="block text-xs font-bold text-slate-700 dark:text-slate-300">
                                    Midtrans Client Key *
                                </label>
                                <span class="text-[10px] text-slate-400">Publik &bull; Dimuat pada form popup pembayaran Snap</span>
                            </div>
                            <input type="text" id="midtrans_client_key" name="midtrans_client_key" value="{{ old('midtrans_client_key', $clientKey) }}"
                                   placeholder="SB-Mid-client-xxxxxxxxxxxx"
                                   class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 text-xs font-mono text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-pink-500 font-medium">
                        </div>

                        <!-- Midtrans Merchant ID (Opsional) -->
                        <div>
                            <div class="flex items-center justify-between mb-1">
                                <label for="midtrans_merchant_id" class="block text-xs font-bold text-slate-700 dark:text-slate-300">
                                    Midtrans Merchant ID (Opsional)
                                </label>
                                <span class="text-[10px] text-slate-400">ID akun merchant Midtrans Anda</span>
                            </div>
                            <input type="text" id="midtrans_merchant_id" name="midtrans_merchant_id" value="{{ old('midtrans_merchant_id', $merchantId) }}"
                                   placeholder="Contoh: M012345678"
                                   class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 text-xs font-mono text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-pink-500 font-medium">
                        </div>

                        <!-- Box Uji Koneksi Midtrans -->
                        <div class="p-4 rounded-2xl bg-amber-50/70 dark:bg-amber-950/20 border border-amber-200 dark:border-amber-800/60 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                            <div class="flex items-start gap-2.5">
                                <i data-lucide="zap" class="w-4 h-4 text-amber-600 mt-0.5 shrink-0"></i>
                                <div>
                                    <span class="text-xs font-bold text-slate-900 dark:text-white block">Ingin memastikan Server Key dapat terhubung ke Midtrans?</span>
                                    <span class="text-[10px] text-slate-500 dark:text-slate-400 block">Klik tombol untuk memverifikasi kunci API Anda langsung ke server Midtrans.</span>
                                </div>
                            </div>
                            <button type="button" onclick="testMidtransConnection()" id="btn-test-connection" class="px-4 py-2 rounded-xl bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 hover:border-amber-500 text-slate-800 dark:text-slate-200 font-bold text-xs shrink-0 flex items-center justify-center gap-1.5 shadow-sm transition-all active:scale-95">
                                <i data-lucide="zap" class="w-3.5 h-3.5 text-amber-500"></i>
                                <span id="btn-test-text">Uji Koneksi Midtrans</span>
                            </button>
                        </div>

                        <!-- Feedback Box Uji Koneksi -->
                        <div id="test-connection-alert" class="hidden p-3.5 rounded-2xl text-xs flex items-center gap-2"></div>

                        <!-- Action Buttons Sesuai Screenshot -->
                        <div class="pt-4 border-t border-slate-100 dark:border-slate-800 flex items-center justify-between gap-3">
                            <a href="{{ route('admin.dashboard') }}" class="px-4 py-2.5 rounded-xl border border-slate-300 dark:border-slate-700 text-xs font-bold text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 flex items-center gap-1.5">
                                &larr; Kembali ke Dashboard
                            </a>

                            <button type="submit" class="px-6 py-2.5 rounded-xl bg-pink-600 hover:bg-pink-700 text-white font-bold text-xs flex items-center gap-2 shadow-md shadow-emerald-600/30 transition-all active:scale-95">
                                <i data-lucide="save" class="w-4 h-4"></i> Simpan Pengaturan
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- RIGHT COLUMN: URLs Integrasi, Panduan & Simulator (col-span-5) -->
            <div class="lg:col-span-5 space-y-5">
                
                <!-- Card 1: URL Integrasi Midtrans -->
                <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200 dark:border-slate-800 p-6 shadow-sm space-y-4">
                    <div class="flex items-center gap-2 pb-3 border-b border-slate-100 dark:border-slate-800">
                        <i data-lucide="link" class="w-4 h-4 text-pink-600"></i>
                        <h3 class="text-xs font-extrabold uppercase tracking-wider text-slate-800 dark:text-white">URL Integrasi Midtrans</h3>
                    </div>
                    <p class="text-[11px] text-slate-500 dark:text-slate-400 leading-relaxed">
                        Salin URL di bawah ini ke <span class="font-semibold text-slate-700 dark:text-slate-300">Midtrans Dashboard &rarr; Settings &rarr; Configuration</span> agar status pembayaran QRIS otomatis terupdate secara instan.
                    </p>

                    <!-- Webhook Notification URL -->
                    <div class="space-y-1">
                        <div class="flex items-center justify-between">
                            <span class="text-[10px] font-bold uppercase text-slate-600 dark:text-slate-300">PAYMENT NOTIFICATION URL (WEBHOOK)</span>
                            <span class="px-1.5 py-0.5 rounded text-[9px] font-extrabold bg-rose-100 text-rose-700 dark:bg-rose-950 dark:text-rose-300 uppercase">WAJIB</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <input type="text" readonly id="url-webhook" value="{{ $webhookUrl }}" class="flex-1 px-3 py-2 rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 font-mono text-[11px] text-slate-700 dark:text-slate-300 select-all">
                            <button type="button" onclick="copyToClipboard('url-webhook', this)" class="px-3 py-2 rounded-xl bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 text-[11px] font-bold shrink-0 transition-colors">
                                Salin
                            </button>
                        </div>
                    </div>

                    <!-- Finish Redirect URL -->
                    <div class="space-y-1">
                        <div class="flex items-center justify-between">
                            <span class="text-[10px] font-bold uppercase text-slate-600 dark:text-slate-300">FINISH REDIRECT URL</span>
                            <span class="px-1.5 py-0.5 rounded text-[9px] font-extrabold bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-400 uppercase">OPSIONAL</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <input type="text" readonly id="url-finish" value="{{ $finishUrl }}" class="flex-1 px-3 py-2 rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 font-mono text-[11px] text-slate-700 dark:text-slate-300 select-all">
                            <button type="button" onclick="copyToClipboard('url-finish', this)" class="px-3 py-2 rounded-xl bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 text-[11px] font-bold shrink-0 transition-colors">
                                Salin
                            </button>
                        </div>
                    </div>

                    <!-- Snap Script Auth / JS -->
                    <div class="space-y-1">
                        <div class="flex items-center justify-between">
                            <span class="text-[10px] font-bold uppercase text-slate-600 dark:text-slate-300">SNAP SCRIPT AUTH / JS</span>
                            <span class="px-1.5 py-0.5 rounded text-[9px] font-extrabold bg-sky-100 text-sky-700 dark:bg-sky-950 dark:text-sky-300 uppercase">SNAP JS v2</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <input type="text" readonly id="url-snap-js" value="{{ $snapJsUrl }}" class="flex-1 px-3 py-2 rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 font-mono text-[11px] text-slate-700 dark:text-slate-300 select-all">
                            <button type="button" onclick="copyToClipboard('url-snap-js', this)" class="px-3 py-2 rounded-xl bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 text-[11px] font-bold shrink-0 transition-colors">
                                Salin
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Card 2: Panduan Mendapatkan Kunci -->
                <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200 dark:border-slate-800 p-6 shadow-sm space-y-3">
                    <div class="flex items-center gap-2 pb-2 border-b border-slate-100 dark:border-slate-800">
                        <i data-lucide="lightbulb" class="w-4 h-4 text-amber-500"></i>
                        <h3 class="text-xs font-extrabold uppercase tracking-wider text-slate-800 dark:text-white">Panduan Mendapatkan Kunci</h3>
                    </div>
                    <ol class="list-decimal list-outside pl-4 space-y-2 text-[11px] text-slate-600 dark:text-slate-300 leading-relaxed">
                        <li>Buka dan login ke <a href="https://dashboard.sandbox.midtrans.com" target="_blank" class="text-pink-600 hover:underline font-mono">dashboard.sandbox.midtrans.com</a> (untuk Sandbox) atau <a href="https://dashboard.midtrans.com" target="_blank" class="text-pink-600 hover:underline font-mono">dashboard.midtrans.com</a> (untuk Production).</li>
                        <li>Masuk ke menu <strong>Settings &rarr; Access Keys</strong> pada sidebar kiri dashboard Midtrans Anda.</li>
                        <li>Salin <strong>Server Key</strong> & <strong>Client Key</strong> Anda, lalu tempelkan pada kolom di formulir sebelah kiri.</li>
                        <li>Klik tombol <strong>Uji Koneksi Midtrans</strong> untuk memverifikasi, lalu klik <strong>Simpan Pengaturan</strong>.</li>
                    </ol>
                </div>

                <!-- Card 3: Simulator Pembayaran Midtrans -->
                <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200 dark:border-slate-800 p-6 shadow-sm space-y-3">
                    <div class="flex items-center gap-2 pb-2 border-b border-slate-100 dark:border-slate-800">
                        <i data-lucide="tablet-smartphone" class="w-4 h-4 text-sky-500"></i>
                        <h3 class="text-xs font-extrabold uppercase tracking-wider text-slate-800 dark:text-white">Simulator Pembayaran Midtrans</h3>
                    </div>
                    <p class="text-[11px] text-slate-500 dark:text-slate-400 leading-relaxed">
                        Saat menggunakan mode Sandbox, Anda dapat mensimulasikan bayar QRIS, GoPay, dan bayar Virtual Account tanpa uang asli menggunakan simulator resmi.
                    </p>
                    <a href="https://simulator.sandbox.midtrans.com/qris/index" target="_blank" class="w-full py-2.5 px-4 rounded-xl border border-slate-300 dark:border-slate-700 hover:border-emerald-500 bg-slate-50 dark:bg-slate-800/80 hover:bg-slate-100 dark:hover:bg-slate-800 text-xs font-bold text-slate-800 dark:text-white flex items-center justify-center gap-2 transition-all">
                        <i data-lucide="external-link" class="w-4 h-4 text-emerald-500"></i>
                        <span>Buka Simulator QRIS Midtrans &nearr;</span>
                    </a>
                </div>

            </div>

        </div>

    </div>

    {{-- ======================================================== --}}
    {{-- TAB 5: VOUCHER DISKON & BEASISWA --}}
    {{-- ======================================================== --}}
    <div id="tab-content-voucher" class="{{ $activeTab === 'voucher' ? '' : 'hidden' }} space-y-6">

        <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 p-6 shadow-sm flex items-start gap-4">
            <div class="w-12 h-12 rounded-xl bg-purple-100 text-purple-600 dark:bg-purple-950/60 dark:text-purple-400 flex items-center justify-center shrink-0">
                <i data-lucide="ticket" class="w-6 h-6"></i>
            </div>
            <div>
                <h3 class="text-base font-extrabold text-slate-900 dark:text-white">Manajemen Voucher Diskon & Beasiswa</h3>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-1 leading-relaxed">
                    Kelola voucher diskon, beasiswa, dan kode promo pendaftaran siswa baru. Atur besaran potongan, batas kuota penggunaan, dan masa berlaku voucher.
                </p>
            </div>
        </div>

        {{-- Stats --}}
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
            <div class="p-5 rounded-3xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-sm flex items-center gap-4">
                <div class="w-12 h-12 rounded-2xl bg-emerald-50 dark:bg-emerald-950/50 text-pink-600 dark:text-pink-400 flex items-center justify-center shrink-0">
                    <i data-lucide="ticket" class="w-6 h-6"></i>
                </div>
                <div>
                    <span class="text-[11px] font-bold text-slate-500 uppercase tracking-wider block">Total Voucher</span>
                    <span class="text-xl font-extrabold text-slate-900 dark:text-white">{{ $voucherStats['total'] }}</span>
                </div>
            </div>
            <div class="p-5 rounded-3xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-sm flex items-center gap-4">
                <div class="w-12 h-12 rounded-2xl bg-blue-50 dark:bg-blue-950/50 text-blue-600 dark:text-blue-400 flex items-center justify-center shrink-0">
                    <i data-lucide="badge-percent" class="w-6 h-6"></i>
                </div>
                <div>
                    <span class="text-[11px] font-bold text-slate-500 uppercase tracking-wider block">Voucher Aktif</span>
                    <span class="text-xl font-extrabold text-blue-600 dark:text-blue-400">{{ $voucherStats['aktif'] }}</span>
                </div>
            </div>
            <div class="p-5 rounded-3xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-sm flex items-center gap-4">
                <div class="w-12 h-12 rounded-2xl bg-amber-50 dark:bg-amber-950/50 text-amber-600 dark:text-amber-400 flex items-center justify-center shrink-0">
                    <i data-lucide="users" class="w-6 h-6"></i>
                </div>
                <div>
                    <span class="text-[11px] font-bold text-slate-500 uppercase tracking-wider block">Total Terpakai</span>
                    <span class="text-xl font-extrabold text-amber-600 dark:text-amber-400">{{ $voucherStats['terpakai'] }} kali</span>
                </div>
            </div>
            <div class="p-5 rounded-3xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-sm flex items-center gap-4">
                <div class="w-12 h-12 rounded-2xl bg-purple-50 dark:bg-purple-950/50 text-purple-600 dark:text-purple-400 flex items-center justify-center shrink-0">
                    <i data-lucide="coins" class="w-6 h-6"></i>
                </div>
                <div>
                    <span class="text-[11px] font-bold text-slate-500 uppercase tracking-wider block">Total Potongan Diberikan</span>
                    <span class="text-lg font-extrabold text-purple-600 dark:text-purple-400 font-mono">Rp {{ number_format($voucherStats['total_potongan'], 0, ',', '.') }}</span>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

            {{-- Form Tambah Voucher --}}
            <div class="p-6 rounded-3xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-sm space-y-4">
                <div class="flex items-center gap-2 pb-3 border-b border-slate-100 dark:border-slate-800">
                    <div class="w-8 h-8 rounded-xl bg-emerald-100 dark:bg-emerald-950 text-pink-600 flex items-center justify-center">
                        <i data-lucide="plus-circle" class="w-4 h-4"></i>
                    </div>
                    <div>
                        <h3 class="text-sm font-bold text-slate-900 dark:text-white">Buat Voucher Baru</h3>
                        <p class="text-[11px] text-slate-400">Buat kode promo, diskon, atau beasiswa calon siswa</p>
                    </div>
                </div>

                <form action="{{ route('admin.master.voucher.simpan') }}" method="POST" class="space-y-3 text-xs">
                    @csrf
                    <div>
                        <label class="block font-bold text-slate-700 dark:text-slate-300 mb-1">Kode Voucher <span class="text-rose-500">*</span></label>
                        <input type="text" name="kode_voucher" value="{{ old('kode_voucher') }}" required placeholder="Contoh: TAHFIZ100, DISMP25"
                            class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-xs font-mono font-bold uppercase tracking-wider focus:outline-none focus:ring-2 focus:ring-pink-500 focus:outline-none">
                        <span class="text-[10px] text-slate-400">Kode unik huruf kapital & angka tanpa spasi.</span>
                    </div>

                    <div>
                        <label class="block font-bold text-slate-700 dark:text-slate-300 mb-1">Nama Promo / Keterangan Singkat</label>
                        <input type="text" name="nama_voucher" value="{{ old('nama_voucher') }}" placeholder="Contoh: Beasiswa Tahfiz 30 Juz / Diskon Gelombang 1"
                            class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-xs focus:outline-none focus:ring-2 focus:ring-pink-500 focus:outline-none">
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block font-bold text-slate-700 dark:text-slate-300 mb-1">Jenis Diskon <span class="text-rose-500">*</span></label>
                            <select name="tipe" id="create_tipe" onchange="toggleTipeInput('create')"
                                class="w-full px-3 py-2.5 rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-xs font-bold focus:outline-none focus:ring-2 focus:ring-pink-500 focus:outline-none">
                                <option value="nominal" {{ old('tipe') === 'nominal' ? 'selected' : '' }}>Nominal (Rp)</option>
                                <option value="persen" {{ old('tipe') === 'persen' ? 'selected' : '' }}>Persentase (%)</option>
                            </select>
                        </div>
                        <div>
                            <label class="block font-bold text-slate-700 dark:text-slate-300 mb-1">Besaran Potongan <span class="text-rose-500">*</span></label>
                            <input type="number" name="nilai_potongan" id="create_nilai_potongan" value="{{ old('nilai_potongan', 50000) }}" required min="1" step="any"
                                class="w-full px-3 py-2.5 rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-xs font-mono font-bold focus:outline-none focus:ring-2 focus:ring-pink-500 focus:outline-none">
                            <span id="create_nilai_hint" class="text-[10px] text-slate-400">Nominal rupiah dipotong langsung.</span>
                        </div>
                    </div>

                    <div>
                        <label class="block font-bold text-slate-700 dark:text-slate-300 mb-1">Batas Penggunaan (Kuota) <span class="text-rose-500">*</span></label>
                        <input type="number" name="kuota_voucher" value="{{ old('kuota_voucher', 50) }}" required min="1"
                            class="w-full px-3 py-2.5 rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-xs font-mono font-bold focus:outline-none focus:ring-2 focus:ring-pink-500 focus:outline-none">
                        <span class="text-[10px] text-slate-400">Batas maksimal berapa kali voucher ini dapat digunakan.</span>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block font-bold text-slate-700 dark:text-slate-300 mb-1">Berlaku Mulai</label>
                            <input type="date" name="berlaku_mulai" value="{{ old('berlaku_mulai', date('Y-m-d')) }}"
                                class="w-full px-3 py-2 rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-xs focus:outline-none focus:ring-2 focus:ring-pink-500 focus:outline-none">
                        </div>
                        <div>
                            <label class="block font-bold text-slate-700 dark:text-slate-300 mb-1">Berlaku Sampai</label>
                            <input type="date" name="berlaku_sampai" value="{{ old('berlaku_sampai') }}"
                                class="w-full px-3 py-2 rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-xs focus:outline-none focus:ring-2 focus:ring-pink-500 focus:outline-none">
                        </div>
                    </div>
                    <span class="text-[10px] text-slate-400 block -mt-1">Kosongkan 'Berlaku Sampai' jika berlaku tanpa batas waktu.</span>

                    <div>
                        <label class="block font-bold text-slate-700 dark:text-slate-300 mb-1">Deskripsi / Syarat Ketentuan Promo</label>
                        <textarea name="deskripsi" rows="2" placeholder="Informasi promo yang ditampilkan kepada calon siswa..."
                            class="w-full px-3 py-2 rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-xs focus:outline-none focus:ring-2 focus:ring-pink-500 focus:outline-none">{{ old('deskripsi') }}</textarea>
                    </div>

                    <div class="pt-2">
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="checkbox" name="status_aktif" value="1" checked class="rounded border-slate-300 text-pink-600 focus:ring-pink-500">
                            <span class="font-bold text-slate-800 dark:text-slate-200">Aktifkan Voucher Ini Sekarang</span>
                        </label>
                    </div>

                    <button type="submit" class="w-full py-2.5 rounded-xl bg-pink-600 hover:bg-pink-700 text-white font-bold text-xs shadow-md shadow-emerald-600/20 transition-all flex items-center justify-center gap-2">
                        <i data-lucide="check" class="w-4 h-4"></i> Simpan & Terbitkan Voucher
                    </button>
                </form>
            </div>

            {{-- Daftar Voucher --}}
            <div class="lg:col-span-2 p-6 rounded-3xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-sm space-y-4">
                <div class="flex items-center justify-between flex-wrap gap-2 pb-3 border-b border-slate-100 dark:border-slate-800">
                    <div>
                        <h3 class="text-base font-bold text-slate-900 dark:text-white flex items-center gap-2">
                            <i data-lucide="tags" class="w-5 h-5 text-pink-600"></i>
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
                                    <span class="font-mono font-extrabold text-pink-600 dark:text-pink-400 text-sm tracking-wide">{{ $v->kode_voucher }}</span>
                                    <div class="text-[11px] font-semibold text-slate-800 dark:text-slate-200 mt-0.5">{{ $v->nama_voucher ?: '-' }}</div>
                                    @if($v->deskripsi)
                                    <p class="text-[10px] text-slate-400 line-clamp-1 mt-0.5" title="{{ $v->deskripsi }}">{{ $v->deskripsi }}</p>
                                    @endif
                                </td>
                                <td class="p-3">
                                    <span class="px-2 py-0.5 rounded-md text-[10px] font-bold uppercase {{ $v->tipe === 'persen' ? 'bg-purple-100 text-purple-800 dark:bg-purple-950 dark:text-purple-300' : 'bg-blue-100 text-blue-800 dark:bg-blue-950 dark:text-blue-300' }}">
                                        {{ $v->tipe === 'persen' ? 'Persentase' : 'Nominal' }}
                                    </span>
                                    <div class="font-mono font-bold text-slate-900 dark:text-white mt-1">
                                        {{ $v->tipe === 'nominal' ? 'Rp ' . number_format($v->nilai_potongan, 0, ',', '.') : rtrim(rtrim(number_format((float)$v->nilai_potongan, 2, ',', '.'), '0'), ',') . '%' }}
                                    </div>
                                </td>
                                <td class="p-3">
                                    <div class="text-[11px] font-medium text-slate-700 dark:text-slate-300">
                                        @if($v->berlaku_mulai && $v->berlaku_sampai)
                                            <span>{{ $v->berlaku_mulai->format('d/m/Y') }} &ndash; {{ $v->berlaku_sampai->format('d/m/Y') }}</span>
                                        @elseif($v->berlaku_sampai)
                                            <span>s.d. {{ $v->berlaku_sampai->format('d/m/Y') }}</span>
                                        @elseif($v->berlaku_mulai)
                                            <span>Mulai {{ $v->berlaku_mulai->format('d/m/Y') }}</span>
                                        @else
                                            <span class="text-slate-400 italic">Selalu Berlaku</span>
                                        @endif
                                    </div>
                                    @if($isExpired)
                                        <span class="inline-block mt-0.5 px-2 py-0.5 rounded text-[10px] font-bold bg-rose-100 text-rose-700 dark:bg-rose-950 dark:text-rose-300">Kedaluwarsa</span>
                                    @elseif($isNotStarted)
                                        <span class="inline-block mt-0.5 px-2 py-0.5 rounded text-[10px] font-bold bg-amber-100 text-amber-700 dark:bg-amber-950 dark:text-amber-300">Belum Dimulai</span>
                                    @endif
                                </td>
                                <td class="p-3 min-w-[130px]">
                                    <div class="flex items-center justify-between text-[11px] font-mono mb-1">
                                        <span class="font-bold text-slate-800 dark:text-slate-200">{{ $terpakai }} / {{ $kuota }}</span>
                                        <span class="text-[10px] text-slate-400">({{ $v->sisaKuota() }} sisa)</span>
                                    </div>
                                    <div class="w-full bg-slate-100 dark:bg-slate-700 h-1.5 rounded-full overflow-hidden">
                                        <div class="h-full rounded-full {{ $persenPakai >= 100 ? 'bg-rose-500' : ($persenPakai > 70 ? 'bg-amber-500' : 'bg-emerald-500') }}" style="width: {{ $persenPakai }}%"></div>
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
                                        <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800 dark:bg-emerald-950 dark:text-emerald-300 flex items-center gap-1 w-fit">
                                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span> Aktif
                                        </span>
                                    @endif
                                </td>
                                <td class="p-3 text-right">
                                    <div class="flex items-center justify-end gap-1.5">
                                        <form action="{{ route('admin.master.voucher.toggle', $v->id_voucher) }}" method="POST"
                                            onsubmit="return confirm('Apakah Anda yakin ingin {{ $v->status_aktif ? 'menonaktifkan' : 'mengaktifkan' }} voucher {{ $v->kode_voucher }}?')">
                                            @csrf
                                            <button type="submit"
                                                title="{{ $v->status_aktif ? 'Klik untuk Menonaktifkan' : 'Klik untuk Mengaktifkan' }}"
                                                class="px-2.5 py-1.5 rounded-lg text-[11px] font-bold transition-all flex items-center gap-1 {{ $v->status_aktif ? 'bg-amber-50 hover:bg-amber-100 text-amber-700 dark:bg-amber-950/40 dark:text-amber-300 border border-amber-200 dark:border-amber-800' : 'bg-emerald-50 hover:bg-emerald-100 text-emerald-700 dark:bg-emerald-950/40 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800' }}">
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

</div>
@endsection

{{-- Modal Edit Voucher --}}
<div id="editVoucherModal" class="fixed inset-0 z-50 bg-slate-950/75 backdrop-blur-sm hidden flex items-center justify-center p-4">
    <div class="bg-white dark:bg-slate-900 rounded-3xl max-w-lg w-full border border-slate-200 dark:border-slate-800 shadow-2xl overflow-hidden">
        <div class="px-6 py-4 bg-slate-900 text-white flex items-center justify-between border-b border-slate-800">
            <div class="flex items-center gap-2">
                <div class="w-8 h-8 rounded-xl bg-pink-600 text-white flex items-center justify-center">
                    <i data-lucide="edit" class="w-4 h-4"></i>
                </div>
                <div>
                    <h3 class="text-sm font-extrabold" id="editModalTitle">Edit Voucher Diskon</h3>
                    <span class="text-[10px] text-emerald-400 font-medium">Ubah parameter, masa berlaku, atau batas kuota voucher</span>
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
                    class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-xs font-mono font-bold uppercase tracking-wider focus:outline-none focus:ring-2 focus:ring-pink-500 focus:outline-none">
            </div>

            <div>
                <label class="block font-bold text-slate-700 dark:text-slate-300 mb-1">Nama Promo / Keterangan Singkat</label>
                <input type="text" id="edit_nama_voucher" name="nama_voucher"
                    class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-xs focus:outline-none focus:ring-2 focus:ring-pink-500 focus:outline-none">
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block font-bold text-slate-700 dark:text-slate-300 mb-1">Jenis Diskon <span class="text-rose-500">*</span></label>
                    <select name="tipe" id="edit_tipe" onchange="toggleTipeInput('edit')"
                        class="w-full px-3 py-2.5 rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-xs font-bold focus:outline-none focus:ring-2 focus:ring-pink-500 focus:outline-none">
                        <option value="nominal">Nominal Rupiah (Rp)</option>
                        <option value="persen">Persentase (%)</option>
                    </select>
                </div>
                <div>
                    <label class="block font-bold text-slate-700 dark:text-slate-300 mb-1">Besaran Potongan <span class="text-rose-500">*</span></label>
                    <input type="number" name="nilai_potongan" id="edit_nilai_potongan" required min="1" step="any"
                        class="w-full px-3 py-2.5 rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-xs font-mono font-bold focus:outline-none focus:ring-2 focus:ring-pink-500 focus:outline-none">
                    <span id="edit_nilai_hint" class="text-[10px] text-slate-400">Nominal rupiah.</span>
                </div>
            </div>

            <div>
                <label class="block font-bold text-slate-700 dark:text-slate-300 mb-1">Batas Penggunaan (Kuota) <span class="text-rose-500">*</span></label>
                <input type="number" id="edit_kuota_voucher" name="kuota_voucher" required min="1"
                    class="w-full px-3 py-2.5 rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-xs font-mono font-bold focus:outline-none focus:ring-2 focus:ring-pink-500 focus:outline-none">
                <span id="edit_kuota_hint" class="text-[10px] text-slate-400 block mt-0.5">Jumlah kuota batas penggunaan total.</span>
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block font-bold text-slate-700 dark:text-slate-300 mb-1">Masa Berlaku Mulai</label>
                    <input type="date" id="edit_berlaku_mulai" name="berlaku_mulai"
                        class="w-full px-3 py-2 rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-xs focus:outline-none focus:ring-2 focus:ring-pink-500 focus:outline-none">
                </div>
                <div>
                    <label class="block font-bold text-slate-700 dark:text-slate-300 mb-1">Masa Berlaku Sampai</label>
                    <input type="date" id="edit_berlaku_sampai" name="berlaku_sampai"
                        class="w-full px-3 py-2 rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-xs focus:outline-none focus:ring-2 focus:ring-pink-500 focus:outline-none">
                </div>
            </div>

            <div>
                <label class="block font-bold text-slate-700 dark:text-slate-300 mb-1">Deskripsi / Syarat Ketentuan Promo</label>
                <textarea id="edit_deskripsi" name="deskripsi" rows="2"
                    class="w-full px-3 py-2 rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-xs focus:outline-none focus:ring-2 focus:ring-pink-500 focus:outline-none"></textarea>
            </div>

            <div class="pt-2">
                <label class="flex items-center gap-2 cursor-pointer">
                    <input type="checkbox" id="edit_status_aktif" name="status_aktif" value="1" class="rounded border-slate-300 text-pink-600 focus:ring-pink-500">
                    <span class="font-bold text-slate-800 dark:text-slate-200">Voucher Aktif</span>
                </label>
            </div>

            <div class="pt-4 flex items-center justify-end gap-2 border-t border-slate-100 dark:border-slate-800">
                <button type="button" onclick="closeEditVoucherModal()"
                    class="px-4 py-2.5 rounded-xl border border-slate-300 dark:border-slate-700 text-slate-600 dark:text-slate-300 font-bold text-xs hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors">
                    Batal
                </button>
                <button type="submit"
                    class="px-5 py-2.5 rounded-xl bg-pink-600 hover:bg-pink-700 text-white font-bold text-xs shadow-md shadow-emerald-600/20 transition-all flex items-center gap-1.5">
                    <i data-lucide="save" class="w-4 h-4"></i> Simpan Perubahan
                </button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
    function switchMainTab(tab) {
        const tabs = ['profil', 'biaya_pendaftaran', 'biaya_daftar_ulang', 'gateway', 'voucher'];
        
        tabs.forEach(t => {
            const content = document.getElementById(`tab-content-${t}`);
            const btn = document.getElementById(`tab-btn-${t}`);
            
            if (content && btn) {
                if (t === tab) {
                    content.classList.remove('hidden');
                    btn.className = "flex-1 flex items-center justify-center gap-2.5 px-4 py-2.5 rounded-xl font-bold text-xs transition-all whitespace-nowrap bg-pink-600 text-white shadow-md";
                } else {
                    content.classList.add('hidden');
                    btn.className = "flex-1 flex items-center justify-center gap-2.5 px-4 py-2.5 rounded-xl font-bold text-xs transition-all whitespace-nowrap text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white";
                }
            }
        });

        const url = new URL(window.location);
        if (tab === 'profil') {
            url.searchParams.delete('tab');
        } else {
            url.searchParams.set('tab', tab);
        }
        window.history.replaceState({}, '', url);

        lucide.createIcons();
    }

    // ---- Voucher Functions ----
    function toggleTipeInput(prefix) {
        const tipe = document.getElementById(prefix + '_tipe').value;
        const hint = document.getElementById(prefix + '_nilai_hint');
        const input = document.getElementById(prefix + '_nilai_potongan');
        if (tipe === 'persen') {
            hint.innerText = "Masukkan nilai persentase diskon (1 - 100%).";
            input.setAttribute('max', '100');
            input.setAttribute('step', '1');
            if (parseFloat(input.value) > 100) input.value = 100;
        } else {
            hint.innerText = "Nominal rupiah potongan langsung.";
            input.removeAttribute('max');
            input.setAttribute('step', '1000');
        }
    }

    function openEditVoucherModal(voucher) {
        const form = document.getElementById('editVoucherForm');
        form.action = "{{ url('admin/master/voucher') }}/" + voucher.id_voucher;
        document.getElementById('editModalTitle').innerText = 'Edit Voucher: ' + voucher.kode_voucher;
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

    function toggleServerKeyVisibility(btn) {
        const input = document.getElementById('midtrans_server_key');
        if (input.type === 'password') {
            input.type = 'text';
            btn.innerHTML = `<i data-lucide="eye-off" class="w-4 h-4"></i>`;
        } else {
            input.type = 'password';
            btn.innerHTML = `<i data-lucide="eye" class="w-4 h-4"></i>`;
        }
        lucide.createIcons();
    }

    function copyToClipboard(elementId, btn) {
        const input = document.getElementById(elementId);
        input.select();
        input.setSelectionRange(0, 99999);
        navigator.clipboard.writeText(input.value);

        const originalText = btn.innerText;
        btn.innerText = "Tersalin!";
        btn.classList.add('bg-emerald-600', 'text-white');
        setTimeout(() => {
            btn.innerText = originalText;
            btn.classList.remove('bg-emerald-600', 'text-white');
        }, 1500);
    }

    async function testMidtransConnection() {
        const btn = document.getElementById('btn-test-connection');
        const btnText = document.getElementById('btn-test-text');
        const alertBox = document.getElementById('test-connection-alert');

        const serverKeyInp = document.getElementById('midtrans_server_key').value.trim();
        const envRadio = document.querySelector('input[name="midtrans_environment"]:checked');
        const environment = envRadio ? envRadio.value : 'sandbox';

        btn.disabled = true;
        btnText.innerText = "Memeriksa...";
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
                alertBox.className = "p-3.5 rounded-2xl bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-300 dark:border-emerald-800 text-emerald-800 dark:text-emerald-200 text-xs flex items-center gap-2";
                alertBox.innerHTML = `<i data-lucide="check-circle-2" class="w-4 h-4 text-pink-600 shrink-0"></i><span>${data.message}</span>`;
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
            lucide.createIcons();
        }
    }
</script>
@endpush
