@extends('layouts.admin')

@section('title', 'Detail Pendaftar: ' . $pendaftar->nama)
@section('header_title', 'Pemeriksaan Berkas: ' . $pendaftar->nama)

@section('content')
<div class="space-y-6 max-w-5xl mx-auto">

    <!-- Top Action Bar -->
    <div class="flex items-center justify-between flex-wrap gap-4">
        <a href="{{ route('admin.pendaftar') }}" class="text-xs font-semibold text-[#fa2f79] dark:text-pink-400 hover:underline flex items-center gap-1">
            <i data-lucide="arrow-left" class="w-3.5 h-3.5"></i> Kembali ke Daftar Pendaftar
        </a>

        <div class="flex items-center gap-2">
            @php
                $cleanPhone = preg_replace('/[^0-9]/', '', $pendaftar->hp_siswa);
                if (str_starts_with($cleanPhone, '0')) {
                    $cleanPhone = '62' . substr($cleanPhone, 1);
                }
                $waMessage = urlencode("Assalamu'alaikum Wr. Wb. Yth. Calon Siswa {$pendaftar->nama} (No. Reg: {$pendaftar->no_pendaftaran}) di SMK Islam Bina Khalifah Bangsa Kota Metro...");
            @endphp
            <a href="https://wa.me/{{ $cleanPhone }}?text={{ $waMessage }}" target="_blank" class="px-3.5 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs shadow-sm flex items-center gap-2">
                <i data-lucide="message-circle" class="w-4 h-4"></i> Chat WhatsApp Siswa
            </a>

            <!-- Buka Kunci Formulir -->
            @if($pendaftar->is_locked)
            <form action="{{ route('admin.pendaftar.unlock', $pendaftar->id_pendaftar) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin membuka kunci formulir biodata {{ $pendaftar->nama }}? Calon siswa akan dapat mengedit kembali biodatanya.');" class="inline">
                @csrf
                <button type="submit" class="px-3.5 py-2 rounded-xl bg-amber-500 hover:bg-amber-600 text-white font-bold text-xs shadow-sm flex items-center gap-1.5 transition-all">
                    <i data-lucide="unlock" class="w-4 h-4"></i> Buka Kunci Formulir
                </button>
            </form>
            @endif

            <!-- If cash payment pending -->
            @if($pendaftar->status_pembayaran !== 'Paid')
            <form action="{{ route('admin.pendaftar.bayar_tunai', $pendaftar->id_pendaftar) }}" method="POST" onsubmit="return confirm('Konfirmasi bahwa siswa telah membayar tunai di loket TU?');">
                @csrf
                <button type="submit" class="px-3.5 py-2 rounded-xl bg-amber-500 hover:bg-amber-600 text-white font-bold text-xs shadow-sm flex items-center gap-1.5">
                    <i data-lucide="check-circle" class="w-4 h-4"></i> Verifikasi Lunas Tunai
                </button>
            </form>
            @endif
        </div>
    </div>

    <!-- 1. KOTAK INFORMASI BIODATA & STATUS PENDAFTAR (Jejer ke bawah) -->
    <div class="p-6 rounded-3xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-sm space-y-4">
        <!-- Header Profil -->
        <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-800 pb-4 flex-wrap gap-3">
            <div>
                <span class="text-[11px] font-mono text-[#fa2f79] dark:text-pink-400 font-bold block">{{ $pendaftar->no_pendaftaran }}</span>
                <h2 class="text-xl font-extrabold text-slate-900 dark:text-white">{{ $pendaftar->nama }}</h2>
                <p class="text-xs text-slate-500">NISN: {{ $pendaftar->nisn }} | Asal Sekolah: {{ $pendaftar->asal_sekolah }}</p>
            </div>
            <div class="flex items-center gap-2 flex-wrap">
                <!-- Status Pembayaran Badge -->
                <div class="px-3 py-1.5 rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-xs flex items-center gap-2">
                    <span class="text-slate-400 text-[11px]">Pembayaran:</span>
                    <strong class="{{ $pendaftar->status_pembayaran === 'Paid' ? 'text-pink-600 dark:text-pink-400' : 'text-amber-600 dark:text-amber-400' }}">
                        {{ $pendaftar->status_pembayaran === 'Paid' ? 'LUNAS (Rp ' . number_format($pendaftar->pembayaran->total_bayar ?? 0, 0, ',', '.') . ')' : 'BELUM LUNAS' }}
                    </strong>
                </div>

                <!-- Status Formulir Badge -->
                <div class="px-3 py-1.5 rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-xs flex items-center gap-2">
                    <span class="text-slate-400 text-[11px]">Biodata:</span>
                    <strong class="{{ $pendaftar->is_locked ? 'text-slate-700 dark:text-slate-200' : 'text-pink-600 dark:text-pink-400' }}">
                        {{ $pendaftar->is_locked ? 'Terkunci (Final)' : 'Bisa Diedit' }}
                    </strong>
                </div>
            </div>
        </div>

        <!-- Biodata Detail Grid -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 text-xs">
            <div>
                <span class="text-slate-400 block text-[10px] uppercase font-bold">Jurusan Pilihan</span>
                <span class="font-bold text-[#fa2f79] dark:text-pink-400">{{ $pendaftar->jurusan1 ? $pendaftar->jurusan1->nama_jurusan : '-' }}</span>
                @if($pendaftar->jurusan2)
                <span class="text-[11px] text-slate-500 block">Pil 2: {{ $pendaftar->jurusan2->nama_jurusan }}</span>
                @endif
            </div>
            <div>
                <span class="text-slate-400 block text-[10px] uppercase font-bold">Tempat, Tanggal Lahir</span>
                <span class="font-semibold text-slate-800 dark:text-slate-200">{{ $pendaftar->tempat_lahir }}, {{ $pendaftar->tanggal_lahir->translatedFormat('d F Y') }} ({{ $pendaftar->jenis_kelamin }})</span>
            </div>
            <div>
                <span class="text-slate-400 block text-[10px] uppercase font-bold">Kontak Siswa & Ortu</span>
                <span class="font-semibold text-slate-800 dark:text-slate-200">{{ $pendaftar->hp_siswa }} / {{ $pendaftar->hp_ortu }}</span>
            </div>
            <div>
                <span class="text-slate-400 block text-[10px] uppercase font-bold">Cetak Dokumen</span>
                <div class="flex items-center gap-1.5 pt-0.5">
                    <a href="{{ route('admin.cetak.formulir', $pendaftar->id_pendaftar) }}" target="_blank" class="px-2 py-1 rounded-lg bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 text-[10px] font-bold">Formulir ↗</a>
                    <a href="{{ route('admin.cetak.kartu_ujian', $pendaftar->id_pendaftar) }}" target="_blank" class="px-2 py-1 rounded-lg bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 text-[10px] font-bold">Kartu Ujian ↗</a>
                    @if($pendaftar->terima)
                    <a href="{{ route('admin.cetak.skl', $pendaftar->id_pendaftar) }}" target="_blank" class="px-2 py-1 rounded-lg bg-pink-100 text-[#fa2f79] text-[10px] font-bold">SKL ↗</a>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- 2. KOTAK DOKUMEN PERSYARATAN YANG DIUNGGAH (FORMAT RAPI JEJER KE BAWAH) -->
    <div class="p-6 sm:p-8 rounded-3xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-sm space-y-6">
        <div>
            <h3 class="text-lg font-black text-slate-900 dark:text-white tracking-tight">Dokumen Persyaratan yang Diunggah</h3>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                Klik tombol file untuk melihat berkas, pilih status dokumen, lalu klik tombol perbarui verifikasi di bawah.
            </p>
        </div>

        <form action="{{ route('admin.pendaftar.verifikasi', $pendaftar->id_pendaftar) }}" method="POST" class="space-y-6">
            @csrf

            @php
                $daftarWajib = [
                    ['no' => '1', 'key' => 'ijazah', 'label' => 'Ijazah / Surat Keterangan Lulus (SKL)'],
                    ['no' => '2', 'key' => 'pasfoto', 'label' => 'Pasfoto Resmi'],
                    ['no' => '3', 'key' => 'kk', 'label' => 'Kartu Keluarga (KK)'],
                    ['no' => '4', 'key' => 'akta', 'label' => 'Akta Kelahiran'],
                ];
            @endphp

            <div class="divide-y divide-slate-100 dark:divide-slate-800/80">
                @foreach($daftarWajib as $dw)
                @php
                    $b = $pendaftar->berkas->where('jenis_berkas', $dw['key'])->first();
                @endphp
                <div class="py-5 first:pt-0 last:pb-0 space-y-3">
                    <div class="flex items-center justify-between flex-wrap gap-2">
                        <div class="flex items-center gap-2">
                            <span class="w-6 h-6 rounded-full bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 font-bold text-xs flex items-center justify-center shrink-0">
                                {{ $dw['no'] }}
                            </span>
                            <span class="font-bold text-sm text-slate-900 dark:text-white uppercase tracking-wide">
                                {{ $dw['label'] }}
                            </span>
                        </div>

                        <!-- Badge Status Saat Ini -->
                        @if($b)
                            <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold 
                                @if(in_array($b->status_berkas, ['Valid', 'Terverifikasi'])) bg-pink-100 text-[#be123c] dark:bg-pink-950 dark:text-pink-300
                                @elseif(in_array($b->status_berkas, ['Tidak Valid', 'Perlu Perbaikan'])) bg-rose-100 text-rose-800 dark:bg-rose-950 dark:text-rose-300
                                @else bg-amber-100 text-amber-800 dark:bg-amber-950 dark:text-amber-300 @endif">
                                @if(in_array($b->status_berkas, ['Valid', 'Terverifikasi'])) Terverifikasi (Valid)
                                @elseif(in_array($b->status_berkas, ['Tidak Valid', 'Perlu Perbaikan'])) Perlu Perbaikan
                                @else Menunggu Verifikasi @endif
                            </span>
                        @else
                            <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-slate-100 text-slate-500 dark:bg-slate-800 dark:text-slate-400">
                                Belum Diunggah
                            </span>
                        @endif
                    </div>

                    @if($b)
                    <div class="bg-slate-50/80 dark:bg-slate-800/40 p-4 rounded-2xl border border-slate-100 dark:border-slate-800 space-y-3">
                        <!-- File (Bisa Dibuka Langsung) -->
                        <div>
                            <a href="{{ route('dokumen.berkas', $b->id_berkas) }}" target="_blank" 
                               class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-xs font-bold text-[#fa2f79] dark:text-pink-400 hover:bg-pink-50 dark:hover:bg-pink-950/40 shadow-sm transition-all max-w-full truncate">
                                <i data-lucide="file-text" class="w-4 h-4 shrink-0 text-[#fa2f79]"></i>
                                <span class="truncate">Buka File: {{ $b->nama_file }}</span>
                                <i data-lucide="external-link" class="w-3.5 h-3.5 shrink-0 text-slate-400"></i>
                            </a>
                        </div>

                        <!-- Status (Pilih) - Diletakkan di bawah file -->
                        <div class="flex items-center gap-3 pt-0.5">
                            <span class="text-xs font-bold text-slate-700 dark:text-slate-300 shrink-0">Status:</span>
                            <div class="max-w-xs w-full">
                                <select name="berkas[{{ $b->id_berkas }}][status_berkas]" class="w-full px-3 py-1.5 rounded-xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-xs font-bold text-slate-800 dark:text-slate-200 focus:outline-none focus:ring-2 focus:ring-[#fa2f79]">
                                    <option value="Valid" {{ in_array($b->status_berkas, ['Valid', 'Terverifikasi']) || $b->status_berkas === 'Menunggu' ? 'selected' : '' }}>✓ Valid</option>
                                    <option value="Perlu Perbaikan" {{ in_array($b->status_berkas, ['Tidak Valid', 'Perlu Perbaikan']) ? 'selected' : '' }}>✗ Ditolak (Perlu Perbaikan)</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    @else
                    <div class="p-3.5 rounded-2xl bg-slate-50 dark:bg-slate-800/40 border border-slate-100 dark:border-slate-800 text-xs text-slate-400 italic">
                        Calon siswa belum mengunggah dokumen ini.
                    </div>
                    @endif
                </div>
                @endforeach
            </div>

            <!-- Jika Siswa Mengunggah Piagam Prestasi Tambahan -->
            @if($pendaftar->prestasi->isNotEmpty())
            <div class="pt-4 border-t border-slate-200 dark:border-slate-800 space-y-3">
                <span class="text-xs font-bold text-slate-800 dark:text-white uppercase tracking-wider flex items-center gap-1.5">
                    <i data-lucide="award" class="w-4 h-4 text-amber-500"></i> Sertifikat Piagam Prestasi Tambahan
                </span>
                @foreach($pendaftar->prestasi as $pr)
                <div class="p-3.5 rounded-2xl bg-slate-50 dark:bg-slate-800/50 border border-slate-200 dark:border-slate-700 flex items-center justify-between text-xs flex-wrap gap-2">
                    <div>
                        <strong class="text-slate-900 dark:text-white">{{ $pr->nama_prestasi }} ({{ $pr->peringkat }})</strong>
                        <p class="text-[11px] text-slate-500">{{ $pr->kategori }} &bull; Tingkat: {{ $pr->tingkat }} &bull; Skor: +{{ $pr->skor_bobot }} Poin</p>
                    </div>
                    <a href="{{ route('dokumen.prestasi', $pr->id_prestasi) }}" target="_blank" class="px-3.5 py-1.5 rounded-xl bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-[#fa2f79] dark:text-pink-400 font-bold text-xs inline-flex items-center gap-1.5 hover:bg-pink-50">
                        <i data-lucide="external-link" class="w-3.5 h-3.5"></i> Buka Piagam
                    </a>
                </div>
                @endforeach
            </div>
            @endif

            <!-- Bagian Bawah: Catatan Verifikasi & Tombol Perbarui Status Verifikasi -->
            <div class="pt-6 border-t border-slate-200 dark:border-slate-800 space-y-4">
                <div>
                    <label class="block text-xs font-bold text-slate-800 dark:text-white uppercase mb-1.5">
                        Catatan Verifikasi (Tampil di Dashboard Siswa)
                    </label>
                    <textarea name="catatan_verifikasi" rows="3" placeholder="Tuliskan catatan verifikasi ke siswa jika ada dokumen yang perlu diperbaiki..." 
                              class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-xs text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-[#fa2f79]">{{ $pendaftar->catatan_verifikasi }}</textarea>
                    <span class="text-[11px] text-slate-500 dark:text-slate-400 mt-1 block">
                        * Otomatis: Jika ada minimal 1 berkas berstatus "Ditolak", status verifikasi pendaftar otomatis menjadi <strong>Ditolak</strong>. Jika semua berkas valid, status otomatis menjadi <strong>Terverifikasi</strong>.
                    </span>
                </div>

                <div class="flex items-center justify-end gap-3 pt-2">
                    <button type="submit" class="w-full sm:w-auto px-8 py-3 rounded-2xl bg-[#fa2f79] hover:bg-[#e11d48] text-white font-extrabold text-xs shadow-lg shadow-pink-500/25 transition-all flex items-center justify-center gap-2 active:scale-95">
                        <i data-lucide="check-circle" class="w-4 h-4"></i>
                        <span>Perbarui Status Verifikasi</span>
                    </button>
                </div>
            </div>
        </form>
    </div>

</div>
@endsection
