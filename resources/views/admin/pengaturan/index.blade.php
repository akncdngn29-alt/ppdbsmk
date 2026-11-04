@extends('layouts.admin')

@section('title', 'Pengaturan Profil Sekolah')
@section('header_title', 'Pengaturan Profil & Identitas Sekolah')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">

    <!-- Card Info Header -->
    <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 p-6 shadow-sm">
        <div class="flex items-start gap-4">
            <div class="w-12 h-12 rounded-xl bg-emerald-100 text-emerald-600 dark:bg-emerald-950/60 dark:text-emerald-400 flex items-center justify-center shrink-0">
                <i data-lucide="building-2" class="w-6 h-6"></i>
            </div>
            <div>
                <h2 class="text-base font-bold text-slate-800 dark:text-white">Identitas Lembaga & Kop Surat Resmi</h2>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-1 leading-relaxed">
                    Data di bawah ini digunakan secara otomatis pada seluruh dokumen cetak resmi PPDB seperti Formulir Pendaftaran, Kartu Peserta Ujian, Kwitansi Pembayaran, Surat Keterangan Lulus (SKL), dan Rekapitulasi Laporan.
                </p>
            </div>
        </div>
    </div>

    <!-- Form Pengaturan Sekolah -->
    <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 p-6 shadow-sm">
        <form action="{{ route('admin.pengaturan.simpan') }}" method="POST" class="space-y-6">
            @csrf

            <!-- Bagian 1: Data Pokok Sekolah -->
            <div>
                <h3 class="text-xs font-bold uppercase tracking-wider text-emerald-600 dark:text-emerald-400 mb-4 pb-2 border-b border-slate-100 dark:border-slate-800 flex items-center gap-2">
                    <i data-lucide="school" class="w-4 h-4"></i> Data Pokok Lembaga
                </h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Nama Lengkap Sekolah *</label>
                        <input type="text" name="nama_sekolah" required value="{{ old('nama_sekolah', $pengaturan->nama_sekolah) }}"
                               class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-xs text-slate-800 dark:text-white focus:outline-none focus:ring-2 focus:ring-emerald-500 font-semibold">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Nomor Pokok Sekolah Nasional (NPSN) *</label>
                        <input type="text" name="npsn" required value="{{ old('npsn', $pengaturan->npsn) }}"
                               class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-xs text-slate-800 dark:text-white focus:outline-none focus:ring-2 focus:ring-emerald-500 font-mono">
                    </div>
                    <div class="md:col-span-2">
                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Ijin Operasional / SK Pendirian *</label>
                        <input type="text" name="ijin_operasional" required value="{{ old('ijin_operasional', $pengaturan->ijin_operasional) }}"
                               class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-xs text-slate-800 dark:text-white focus:outline-none focus:ring-2 focus:ring-emerald-500">
                    </div>
                </div>
            </div>

            <!-- Bagian 2: Alamat & Kontak -->
            <div>
                <h3 class="text-xs font-bold uppercase tracking-wider text-emerald-600 dark:text-emerald-400 mb-4 pb-2 border-b border-slate-100 dark:border-slate-800 flex items-center gap-2">
                    <i data-lucide="map-pin" class="w-4 h-4"></i> Lokasi & Saluran Kontak
                </h3>
                <div class="space-y-4">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Alamat Lengkap *</label>
                        <textarea name="alamat" rows="2" required class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-xs text-slate-800 dark:text-white focus:outline-none focus:ring-2 focus:ring-emerald-500">{{ old('alamat', $pengaturan->alamat) }}</textarea>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Kota / Kabupaten *</label>
                            <input type="text" name="kota" required value="{{ old('kota', $pengaturan->kota) }}"
                                   class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-xs text-slate-800 dark:text-white focus:outline-none focus:ring-2 focus:ring-emerald-500">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Provinsi *</label>
                            <input type="text" name="provinsi" required value="{{ old('provinsi', $pengaturan->provinsi) }}"
                                   class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-xs text-slate-800 dark:text-white focus:outline-none focus:ring-2 focus:ring-emerald-500">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">No. Telepon / WhatsApp Resmi *</label>
                            <input type="text" name="no_telp" required value="{{ old('no_telp', $pengaturan->no_telp) }}"
                                   class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-xs text-slate-800 dark:text-white focus:outline-none focus:ring-2 focus:ring-emerald-500 font-mono">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Email Resmi *</label>
                            <input type="email" name="email" required value="{{ old('email', $pengaturan->email) }}"
                                   class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-xs text-slate-800 dark:text-white focus:outline-none focus:ring-2 focus:ring-emerald-500">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Website Sekolah *</label>
                            <input type="text" name="website" required value="{{ old('website', $pengaturan->website) }}"
                                   class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-xs text-slate-800 dark:text-white focus:outline-none focus:ring-2 focus:ring-emerald-500">
                        </div>
                    </div>
                </div>
            </div>

            <!-- Bagian 3: Pimpinan Sekolah & Penandatangan Dokumen -->
            <div>
                <h3 class="text-xs font-bold uppercase tracking-wider text-emerald-600 dark:text-emerald-400 mb-4 pb-2 border-b border-slate-100 dark:border-slate-800 flex items-center gap-2">
                    <i data-lucide="award" class="w-4 h-4"></i> Kepala Sekolah & Pengesah Dokumen
                </h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Nama Lengkap Kepala Sekolah (Gelar) *</label>
                        <input type="text" name="nama_kepsek" required value="{{ old('nama_kepsek', $pengaturan->nama_kepsek) }}"
                               class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-xs text-slate-800 dark:text-white focus:outline-none focus:ring-2 focus:ring-emerald-500 font-semibold">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">NIP / NUPTK (Opsional)</label>
                        <input type="text" name="nip_kepsek" value="{{ old('nip_kepsek', $pengaturan->nip_kepsek) }}" placeholder="Isi '-' jika tidak ada"
                               class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-xs text-slate-800 dark:text-white focus:outline-none focus:ring-2 focus:ring-emerald-500 font-mono">
                    </div>
                </div>
            </div>

            <!-- Bagian 4: Integrasi API Payment Gateway & WhatsApp -->
            <div>
                <h3 class="text-xs font-bold uppercase tracking-wider text-emerald-600 dark:text-emerald-400 mb-4 pb-2 border-b border-slate-100 dark:border-slate-800 flex items-center gap-2">
                    <i data-lucide="key" class="w-4 h-4"></i> Integrasi API Key (Midtrans & WhatsApp Gateway)
                </h3>
                <div class="space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Midtrans Server Key</label>
                            <input type="password" name="midtrans_server_key" value="{{ old('midtrans_server_key', $pengaturan->midtrans_server_key) }}" placeholder="SB-Mid-server-xxxxxxxxxxxx"
                                   class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-xs text-slate-800 dark:text-white focus:outline-none focus:ring-2 focus:ring-emerald-500 font-mono">
                            <span class="text-[10px] text-slate-400 mt-1 block">Dapatkan dari dashboard Midtrans (Settings &rarr; Access Keys).</span>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Midtrans Client Key</label>
                            <input type="text" name="midtrans_client_key" value="{{ old('midtrans_client_key', $pengaturan->midtrans_client_key) }}" placeholder="SB-Mid-client-xxxxxxxxxxxx"
                                   class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-xs text-slate-800 dark:text-white focus:outline-none focus:ring-2 focus:ring-emerald-500 font-mono">
                            <span class="text-[10px] text-slate-400 mt-1 block">Digunakan oleh frontend Snap JS untuk otentikasi transaksi.</span>
                        </div>
                    </div>

                    <div class="p-3.5 rounded-xl bg-slate-50 dark:bg-slate-800/60 border border-slate-200 dark:border-slate-700">
                        <label class="flex items-center gap-2.5 text-xs text-slate-700 dark:text-slate-300 cursor-pointer">
                            <input type="checkbox" name="midtrans_is_production" value="1" {{ old('midtrans_is_production', $pengaturan->midtrans_is_production) ? 'checked' : '' }} class="w-4 h-4 rounded text-emerald-600 focus:ring-emerald-500">
                            <div>
                                <span class="font-bold block">Mode Midtrans Production (Live Transaksi Nyata)</span>
                                <span class="text-[11px] text-slate-500 dark:text-slate-400">Biarkan tidak dicentang jika masih menggunakan mode Sandbox (Uji Coba).</span>
                            </div>
                        </label>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">WhatsApp Gateway API Token (Fonnte / Wablas)</label>
                        <input type="password" name="whatsapp_api_token" value="{{ old('whatsapp_api_token', $pengaturan->whatsapp_api_token) }}" placeholder="Contoh: oNn8Yxxxxxxxxxxxxxxxx"
                               class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-xs text-slate-800 dark:text-white focus:outline-none focus:ring-2 focus:ring-emerald-500 font-mono">
                        <span class="text-[10px] text-slate-400 mt-1 block">Token API untuk fitur broadcast pesan notifikasi verifikasi ke nomor WA siswa.</span>
                    </div>
                </div>
            </div>

            <!-- Tombol Simpan -->
            <div class="pt-4 border-t border-slate-100 dark:border-slate-800 flex items-center justify-end">
                <button type="submit" class="px-6 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs flex items-center gap-2 shadow-sm transition-colors">
                    <i data-lucide="save" class="w-4 h-4"></i> Simpan Perubahan Profil
                </button>
            </div>
        </form>
    </div>

</div>
@endsection

