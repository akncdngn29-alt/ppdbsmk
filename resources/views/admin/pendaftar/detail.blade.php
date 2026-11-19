@extends('layouts.admin')

@section('title', 'Detail Pendaftar: ' . $pendaftar->nama)
@section('header_title', 'Pemeriksaan Berkas: ' . $pendaftar->nama)

@section('content')
<div class="space-y-6">

    <!-- Back bar & Action Shortcuts -->
    <div class="flex items-center justify-between flex-wrap gap-4">
        <a href="{{ route('admin.pendaftar') }}" class="text-xs font-semibold text-emerald-600 dark:text-emerald-400 hover:underline flex items-center gap-1">
            <i data-lucide="arrow-left" class="w-3.5 h-3.5"></i> Kembali ke Daftar Pendaftar
        </a>

        <div class="flex items-center gap-2">
            <!-- Direct WhatsApp Chat Button -->
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

            <!-- Buka Kunci Formulir (SPEC Bab 426) -->
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

    <!-- Main Dossier Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        <!-- Col 1 & 2: Biodata & Dokumen -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Summary Profile Card -->
            <div class="p-6 rounded-3xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-sm space-y-4">
                <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-800 pb-4">
                    <div>
                        <span class="text-[11px] font-mono text-emerald-600 dark:text-emerald-400 font-bold block">{{ $pendaftar->no_pendaftaran }}</span>
                        <h2 class="text-xl font-extrabold text-slate-900 dark:text-white">{{ $pendaftar->nama }}</h2>
                        <p class="text-xs text-slate-500">NISN: {{ $pendaftar->nisn }} | Asal: {{ $pendaftar->asal_sekolah }}</p>
                    </div>
                    <div class="text-right">
                        <span class="text-[10px] text-slate-400 block uppercase">Pilihan 1</span>
                        <span class="font-bold text-emerald-600 dark:text-emerald-400 text-sm">{{ $pendaftar->jurusan1 ? $pendaftar->jurusan1->nama_jurusan : 'Belum dipilih' }}</span>
                        @if($pendaftar->jurusan2)
                        <span class="text-[11px] text-slate-400 block">Pil 2: {{ $pendaftar->jurusan2->nama_jurusan }}</span>
                        @endif
                    </div>
                </div>

                <!-- Biodata Details -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs">
                    <div>
                        <span class="text-slate-400 block text-[10px] uppercase">Tempat, Tanggal Lahir</span>
                        <span class="font-semibold text-slate-800 dark:text-slate-200">{{ $pendaftar->tempat_lahir }}, {{ $pendaftar->tanggal_lahir->translatedFormat('d F Y') }} ({{ $pendaftar->jenis_kelamin }})</span>
                    </div>
                    <div>
                        <span class="text-slate-400 block text-[10px] uppercase">Agama & Jalur</span>
                        <span class="font-semibold text-slate-800 dark:text-slate-200">{{ $pendaftar->agama }} | Jalur {{ $pendaftar->jalur_pendaftaran }}</span>
                    </div>
                    <div>
                        <span class="text-slate-400 block text-[10px] uppercase">Kontak Siswa</span>
                        <span class="font-semibold text-slate-800 dark:text-slate-200">{{ $pendaftar->hp_siswa }}</span>
                    </div>
                    <div>
                        <span class="text-slate-400 block text-[10px] uppercase">Ukuran Seragam / Wearpack</span>
                        <span class="font-semibold text-slate-800 dark:text-slate-200">Baju: {{ $pendaftar->ukuran_seragam }} | Wearpack: {{ $pendaftar->ukuran_wearpack }}</span>
                    </div>
                    <div class="sm:col-span-2">
                        <span class="text-slate-400 block text-[10px] uppercase">Alamat Siswa</span>
                        <span class="font-semibold text-slate-800 dark:text-slate-200">{{ $pendaftar->almt_siswa }}</span>
                    </div>
                    <div>
                        <span class="text-slate-400 block text-[10px] uppercase">Orang Tua (Ayah / Ibu)</span>
                        <span class="font-semibold text-slate-800 dark:text-slate-200">{{ $pendaftar->nama_ayah }} ({{ $pendaftar->kerja_ayah }}) & {{ $pendaftar->nama_ibu }} ({{ $pendaftar->kerja_ibu }})</span>
                    </div>
                    <div>
                        <span class="text-slate-400 block text-[10px] uppercase">No HP & Penghasilan Ortu</span>
                        <span class="font-semibold text-slate-800 dark:text-slate-200">{{ $pendaftar->hp_ortu }} | {{ $pendaftar->penghasilan_ortu }}</span>
                    </div>
                </div>

                <!-- Transfer to Choice 2 if Choice 1 full -->
                @if($pendaftar->jurusan2)
                <div class="pt-3 border-t border-slate-100 dark:border-slate-800 flex items-center justify-between">
                    <span class="text-xs text-slate-500">Kuota Jurusan 1 Penuh? Alihkan ke Pilihan 2:</span>
                    <form action="{{ route('admin.pendaftar.alihkan', $pendaftar->id_pendaftar) }}" method="POST" onsubmit="return confirm('Alihkan siswa ke Jurusan Pilihan 2?');">
                        @csrf
                        <button type="submit" class="px-3 py-1.5 rounded-lg border border-amber-300 dark:border-amber-700 text-amber-700 dark:text-amber-300 hover:bg-amber-50 text-xs font-bold transition-colors">
                            Alihkan ke {{ $pendaftar->jurusan2->kode_jurusan }} &rarr;
                        </button>
                    </form>
                </div>
                @endif
            </div>

            <!-- Uploaded Documents & Interactive Viewer -->
            <div class="p-6 rounded-3xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-sm space-y-4">
                <h3 class="text-base font-bold text-slate-900 dark:text-white">Dokumen Persyaratan yang Diunggah</h3>

                <div class="space-y-4">
                    @forelse($pendaftar->berkas as $b)
                    <div class="p-4 rounded-2xl bg-slate-50 dark:bg-slate-800/60 border border-slate-200 dark:border-slate-700 space-y-3">
                        <div class="flex items-center justify-between">
                            <span class="font-bold text-xs uppercase text-slate-800 dark:text-white">{{ $b->jenis_berkas }}</span>
                            <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold 
                                @if(in_array($b->status_berkas, ['Valid', 'Terverifikasi'])) bg-emerald-100 text-emerald-800 dark:bg-emerald-950 dark:text-emerald-300
                                @elseif(in_array($b->status_berkas, ['Tidak Valid', 'Perlu Perbaikan'])) bg-rose-100 text-rose-800 dark:bg-rose-950 dark:text-rose-300
                                @else bg-amber-100 text-amber-800 dark:bg-amber-950 dark:text-amber-300 @endif">
                                @if(in_array($b->status_berkas, ['Valid', 'Terverifikasi'])) Terverifikasi
                                @elseif(in_array($b->status_berkas, ['Tidak Valid', 'Perlu Perbaikan'])) Perlu Perbaikan
                                @else Menunggu Verifikasi @endif
                            </span>
                        </div>

                        <!-- Document Preview & Controls (Zoom & Rotate 90) -->
                        <div class="relative bg-slate-950 p-2 rounded-xl overflow-hidden flex flex-col items-center justify-center">
                            @php
                                $fileUrl = route('dokumen.berkas', $b->id_berkas);
                                $isImage = in_array(pathinfo($b->path_file, PATHINFO_EXTENSION), ['jpg', 'jpeg', 'png', 'webp']);
                            @endphp

                            @if($isImage)
                            <div class="overflow-auto max-h-64 w-full flex items-center justify-center">
                                <img id="doc-img-{{ $b->id_berkas }}" src="{{ $fileUrl }}" alt="{{ $b->jenis_berkas }}" class="max-h-60 object-contain transition-transform duration-200" data-deg="0">
                            </div>
                            <div class="mt-2 flex items-center gap-2 text-white text-xs">
                                <button type="button" onclick="rotateImage('doc-img-{{ $b->id_berkas }}')" class="px-2.5 py-1 rounded bg-slate-800 hover:bg-slate-700 flex items-center gap-1 text-[11px]">
                                    <i data-lucide="rotate-cw" class="w-3.5 h-3.5"></i> Putar 90°
                                </button>
                                <a href="{{ $fileUrl }}" target="_blank" class="px-2.5 py-1 rounded bg-slate-800 hover:bg-slate-700 flex items-center gap-1 text-[11px]">
                                    <i data-lucide="external-link" class="w-3.5 h-3.5"></i> Buka Layar Penuh
                                </a>
                            </div>
                            @else
                            <div class="py-6 text-center space-y-2 text-slate-300">
                                <i data-lucide="file-text" class="w-8 h-8 mx-auto text-emerald-400"></i>
                                <span class="text-xs block font-mono">{{ $b->nama_file }} (Dokumen PDF)</span>
                                <a href="{{ $fileUrl }}" target="_blank" class="px-3 py-1.5 rounded-lg bg-emerald-600 text-white text-xs font-bold inline-block">
                                    Buka Dokumen PDF
                                </a>
                            </div>
                            @endif
                        </div>

                        <!-- Single Berkas Verification Form -->
                        <form action="{{ route('admin.berkas.verifikasi', $b->id_berkas) }}" method="POST" class="flex items-center gap-2 text-xs pt-1">
                            @csrf
                            <select name="status_berkas" class="px-2.5 py-1.5 rounded-lg bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-xs font-semibold">
                                <option value="Valid" {{ in_array($b->status_berkas, ['Valid', 'Terverifikasi']) ? 'selected' : '' }}>Terverifikasi (Valid)</option>
                                <option value="Perlu Perbaikan" {{ in_array($b->status_berkas, ['Tidak Valid', 'Perlu Perbaikan']) ? 'selected' : '' }}>Perlu Perbaikan / Ditolak</option>
                                <option value="Menunggu" {{ $b->status_berkas === 'Menunggu' ? 'selected' : '' }}>Menunggu Verifikasi</option>
                            </select>
                            <input type="text" name="catatan" value="{{ $b->catatan }}" placeholder="Alasan penolakan / perbaikan (dapat dibaca siswa)..." class="flex-1 px-3 py-1.5 rounded-lg bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-xs">
                            <button type="submit" class="px-3 py-1.5 rounded-lg bg-slate-900 dark:bg-slate-700 text-white font-bold text-xs hover:bg-slate-800 transition-colors">
                                Simpan
                            </button>
                        </form>
                    </div>
                    @empty
                    <p class="text-xs text-slate-400 p-4 text-center">Calon siswa belum mengunggah dokumen persyaratan apa pun.</p>
                    @endforelse

                    @if($pendaftar->prestasi->isNotEmpty())
                    <div class="mt-4 pt-4 border-t border-slate-200 dark:border-slate-700 space-y-3">
                        <h4 class="text-xs font-bold text-slate-800 dark:text-white uppercase tracking-wider flex items-center gap-1.5">
                            <i data-lucide="award" class="w-4 h-4 text-amber-500"></i> Sertifikat Piagam Prestasi
                        </h4>
                        @foreach($pendaftar->prestasi as $pr)
                        <div class="p-3 rounded-xl bg-slate-100 dark:bg-slate-800 flex items-center justify-between text-xs">
                            <div>
                                <div class="font-bold text-slate-900 dark:text-white">{{ $pr->nama_prestasi }} ({{ $pr->peringkat }})</div>
                                <div class="text-[11px] text-slate-500">Kategori: {{ $pr->kategori }} &bull; Tingkat: {{ $pr->tingkat }} &bull; Skor: +{{ $pr->skor_bobot }} Poin</div>
                            </div>
                            <a href="{{ route('dokumen.prestasi', $pr->id_prestasi) }}" target="_blank" class="px-3 py-1.5 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs inline-flex items-center gap-1">
                                <i data-lucide="external-link" class="w-3.5 h-3.5"></i> Lihat Piagam
                            </a>
                        </div>
                        @endforeach
                    </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Col 3: Actions & Overall Verification -->
        <div class="space-y-6">

            <!-- Card Informasi Pembayaran & Voucher -->
            <div class="p-6 rounded-3xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-sm space-y-4">
                <div class="flex items-center justify-between">
                    <h3 class="text-base font-bold text-slate-900 dark:text-white flex items-center gap-2">
                        <i data-lucide="receipt" class="w-4 h-4 text-emerald-600"></i>
                        Status Pembayaran
                    </h3>
                    <span class="px-2.5 py-0.5 rounded-full text-[11px] font-bold {{ $pendaftar->status_pembayaran === 'Paid' ? 'bg-emerald-100 text-emerald-800 dark:bg-emerald-950 dark:text-emerald-300' : 'bg-amber-100 text-amber-800 dark:bg-amber-950 dark:text-amber-300' }}">
                        {{ $pendaftar->status_pembayaran === 'Paid' ? 'Lunas' : 'Belum Lunas' }}
                    </span>
                </div>

                @if($pendaftar->pembayaran)
                <div class="p-3.5 rounded-2xl bg-slate-50 dark:bg-slate-800/60 border border-slate-200 dark:border-slate-700 text-xs space-y-2">
                    <div class="flex items-center justify-between text-slate-500">
                        <span>Kode Transaksi:</span>
                        <strong class="font-mono text-slate-900 dark:text-white">{{ $pendaftar->pembayaran->kode_transaksi }}</strong>
                    </div>
                    <div class="flex items-center justify-between text-slate-500">
                        <span>Biaya Awal:</span>
                        <span class="font-mono">Rp {{ number_format($pendaftar->pembayaran->nominal, 0, ',', '.') }}</span>
                    </div>
                    @if($pendaftar->pembayaran->potongan > 0)
                    <div class="flex items-center justify-between text-emerald-600 font-semibold">
                        <span>Voucher ({{ $pendaftar->pembayaran->voucher ? $pendaftar->pembayaran->voucher->kode_voucher : 'Diskon' }}):</span>
                        <span class="font-mono">- Rp {{ number_format($pendaftar->pembayaran->potongan, 0, ',', '.') }}</span>
                    </div>
                    @endif
                    <div class="pt-1.5 border-t border-slate-200 dark:border-slate-700 flex items-center justify-between font-bold text-slate-900 dark:text-white">
                        <span>Total Tagihan:</span>
                        <span class="text-sm font-mono text-emerald-600 dark:text-emerald-400">Rp {{ number_format($pendaftar->pembayaran->total_bayar, 0, ',', '.') }}</span>
                    </div>
                </div>
                @else
                <p class="text-xs text-slate-400">Calon siswa belum membuat tagihan pembayaran.</p>
                @endif
            </div>

            <!-- Card Status & Buka Kunci Formulir (SPEC_PPDB.md Bab 426) -->
            <div class="p-6 rounded-3xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-sm space-y-4">
                <div class="flex items-center justify-between">
                    <h3 class="text-base font-bold text-slate-900 dark:text-white">Status Formulir Biodata</h3>
                    <span class="px-2.5 py-0.5 rounded-full text-[11px] font-bold {{ $pendaftar->is_locked ? 'bg-amber-100 text-amber-800 dark:bg-amber-950 dark:text-amber-300' : 'bg-emerald-100 text-emerald-800 dark:bg-emerald-950 dark:text-emerald-300' }}">
                        {{ $pendaftar->is_locked ? 'Terkunci (Final)' : 'Terbuka (Bisa Diedit)' }}
                    </span>
                </div>
                <p class="text-xs text-slate-500">
                    {{ $pendaftar->is_locked 
                        ? 'Formulir saat ini berstatus Read-Only karena telah difinalisasi oleh siswa. Buka kunci jika siswa mengajukan permohonan revisi data.' 
                        : 'Formulir berstatus terbuka. Calon siswa dapat memperbarui data diri, data orang tua, dan pilihan jurusan di dashboard siswa.' }}
                </p>

                @if($pendaftar->is_locked)
                <form action="{{ route('admin.pendaftar.unlock', $pendaftar->id_pendaftar) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin membuka kunci formulir biodata {{ $pendaftar->nama }}? Calon siswa akan dapat mengedit kembali biodatanya.');">
                    @csrf
                    <button type="submit" class="w-full py-2.5 rounded-xl bg-amber-500 hover:bg-amber-600 text-white font-bold text-xs shadow-md transition-all flex items-center justify-center gap-2">
                        <i data-lucide="unlock" class="w-4 h-4"></i> Buka Kunci Formulir
                    </button>
                </form>
                @else
                <div class="p-3 rounded-xl bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-800 text-emerald-800 dark:text-emerald-300 text-xs flex items-center gap-2">
                    <i data-lucide="check-circle" class="w-4 h-4 text-emerald-600 shrink-0"></i>
                    <span>Formulir dapat diedit oleh calon siswa.</span>
                </div>
                @endif
            </div>

            <!-- Overall Verification Form -->
            <div class="p-6 rounded-3xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-sm space-y-4">
                <h3 class="text-base font-bold text-slate-900 dark:text-white">Keputusan Verifikasi Berkas</h3>
                <p class="text-xs text-slate-500">Tentukan status keseluruhan berkas pendaftar ini.</p>

                <form action="{{ route('admin.pendaftar.verifikasi', $pendaftar->id_pendaftar) }}" method="POST" class="space-y-3">
                    @csrf
                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Status Verifikasi</label>
                        <select name="status_verifikasi" required class="w-full px-3 py-2.5 rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-xs font-bold">
                            <option value="Terverifikasi" {{ $pendaftar->status_verifikasi === 'Terverifikasi' ? 'selected' : '' }}>Terverifikasi (Lengkap & Sah)</option>
                            <option value="Ditolak" {{ $pendaftar->status_verifikasi === 'Ditolak' ? 'selected' : '' }}>Ditolak (Perlu Revisi Dokumen)</option>
                            <option value="Menunggu" {{ $pendaftar->status_verifikasi === 'Menunggu' ? 'selected' : '' }}>Menunggu Diperiksa</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Catatan Verifikasi (Tampil di Dashboard Siswa)</label>
                        <textarea name="catatan_verifikasi" rows="3" placeholder="Contoh: Foto KK terpotong, mohon unggah ulang lembar KK yang utuh." class="w-full px-3 py-2.5 rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-xs">{{ $pendaftar->catatan_verifikasi }}</textarea>
                    </div>

                    <button type="submit" class="w-full py-2.5 rounded-xl bg-[#fa2f79] hover:bg-[#e11d48] text-white font-bold text-xs shadow-md shadow-pink-500/25 transition-all">
                        Perbarui Status Verifikasi
                    </button>
                </form>
            </div>

            <!-- Printable Shortcuts -->
            <div class="p-6 rounded-3xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-sm space-y-3">
                <h3 class="text-xs font-bold text-slate-500 uppercase tracking-wider">Cetak Dokumen Pendaftar</h3>
                <div class="space-y-2">
                    <a href="{{ route('admin.cetak.formulir', $pendaftar->id_pendaftar) }}" target="_blank" class="w-full p-2.5 rounded-xl bg-slate-50 dark:bg-slate-800 text-slate-800 dark:text-slate-200 text-xs font-semibold hover:bg-slate-100 flex items-center justify-between">
                        <span>Lembar Formulir Pendaftaran</span>
                        <i data-lucide="external-link" class="w-4 h-4 text-slate-400"></i>
                    </a>
                    <a href="{{ route('admin.cetak.kartu_ujian', $pendaftar->id_pendaftar) }}" target="_blank" class="w-full p-2.5 rounded-xl bg-slate-50 dark:bg-slate-800 text-slate-800 dark:text-slate-200 text-xs font-semibold hover:bg-slate-100 flex items-center justify-between">
                        <span>Kartu Peserta Ujian</span>
                        <i data-lucide="external-link" class="w-4 h-4 text-slate-400"></i>
                    </a>
                    @if($pendaftar->terima)
                    <a href="{{ route('admin.cetak.skl', $pendaftar->id_pendaftar) }}" target="_blank" class="w-full p-2.5 rounded-xl bg-emerald-50 dark:bg-emerald-950/40 text-emerald-700 dark:text-emerald-300 text-xs font-bold hover:bg-emerald-100 flex items-center justify-between">
                        <span>Surat Keterangan Lulus (SKL)</span>
                        <i data-lucide="external-link" class="w-4 h-4 text-emerald-500"></i>
                    </a>
                    @endif
                </div>
            </div>

        </div>

    </div>

</div>
@endsection

@push('scripts')
<script>
    function rotateImage(imgId) {
        const img = document.getElementById(imgId);
        if (!img) return;
        let currentDeg = parseInt(img.getAttribute('data-deg') || '0', 10);
        currentDeg = (currentDeg + 90) % 360;
        img.style.transform = `rotate(${currentDeg}deg)`;
        img.setAttribute('data-deg', currentDeg);
    }
</script>
@endpush

