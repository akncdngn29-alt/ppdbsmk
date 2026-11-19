<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kartu Peserta Tes Seleksi - {{ $pendaftar->nama }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/qrcode-generator@1.4.4/qrcode.min.js"></script>
    <style>
        @media print {
            .no-print { display: none !important; }
            body { background: white !important; color: black !important; padding: 0 !important; }
            @page { size: A4 portrait; margin: 15mm; }
        }
    </style>
</head>
<body class="bg-slate-100 text-slate-900 font-sans p-4 md:p-8">

    <div class="max-w-2xl mx-auto mb-6 flex items-center justify-between no-print">
        @if(!empty($adminMode))
        <a href="{{ route('admin.pendaftar.detail', $pendaftar->id_pendaftar) }}" class="px-4 py-2.5 rounded-xl bg-slate-200 text-slate-700 font-bold text-xs hover:bg-slate-300 transition-colors">
            &larr; Kembali ke Detail Pendaftar
        </a>
        @else
        <a href="{{ route('siswa.dashboard') }}" class="px-4 py-2.5 rounded-xl bg-slate-200 text-slate-700 font-bold text-xs hover:bg-slate-300 transition-colors">
            &larr; Kembali ke Dashboard
        </a>
        @endif
        <button onclick="window.print()" class="px-6 py-2.5 rounded-xl bg-rose-500 hover:bg-rose-600 text-white font-bold text-xs shadow-md shadow-rose-500/20 transition-all">
            Cetak Kartu Ujian (PDF)
        </button>
    </div>

    <!-- Printable Card Container -->
    <div class="max-w-2xl mx-auto bg-white p-6 md:p-8 border-2 border-black rounded-2xl shadow-xl text-xs space-y-5">
        <!-- Kop Surat -->
        <div class="border-b-2 border-black pb-3 text-center space-y-1 relative pl-16">
            <img src="{{ asset('images/logo.png') }}" alt="Logo SMK" class="w-14 h-14 object-contain absolute left-0 top-1/2 -translate-y-1/2">
            <h2 class="text-xs font-bold uppercase tracking-wider">PANITIA PENERIMAAN PESERTA DIDIK BARU (PPDB)</h2>
            <h1 class="text-base font-extrabold uppercase">{{ $pengaturan->nama_sekolah }}</h1>
            <p class="text-[11px] text-slate-700">{{ $pengaturan->alamat }}, {{ $pengaturan->kota }} | NPSN: {{ $pengaturan->npsn }}</p>
        </div>

        <div class="text-center">
            <h3 class="text-sm font-black uppercase tracking-wider underline">KARTU TANDA PESERTA TES SELEKSI MASUK</h3>
            <p class="text-[11px] font-semibold">TAHUN AJARAN {{ $pendaftar->gelombang ? $pendaftar->gelombang->tahun_ajaran : '2026/2027' }}</p>
        </div>

        <!-- Details Grid -->
        <div class="grid grid-cols-12 gap-4 items-center pt-2">
            <!-- Left Info -->
            <div class="col-span-8 space-y-2">
                <table class="w-full text-xs">
                    <tr>
                        <td class="font-bold py-1 w-36">No. Peserta Ujian</td>
                        <td class="font-mono font-bold text-sm">: {{ $pendaftar->no_pendaftaran }}</td>
                    </tr>
                    <tr>
                        <td class="font-bold py-1">Nama Calon Siswa</td>
                        <td class="font-bold uppercase">: {{ $pendaftar->nama }}</td>
                    </tr>
                    <tr>
                        <td class="font-bold py-1">NISN</td>
                        <td class="font-mono">: {{ $pendaftar->nisn }}</td>
                    </tr>
                    <tr>
                        <td class="font-bold py-1">Pilihan Jurusan 1</td>
                        <td class="font-bold">: {{ $pendaftar->jurusan1 ? $pendaftar->jurusan1->nama_jurusan : '-' }}</td>
                    </tr>
                    <tr>
                        <td class="font-bold py-1">Pilihan Jurusan 2</td>
                        <td>: {{ $pendaftar->jurusan2 ? $pendaftar->jurusan2->nama_jurusan : '-' }}</td>
                    </tr>
                    <tr>
                        <td class="font-bold py-1">Asal Sekolah</td>
                        <td>: {{ $pendaftar->asal_sekolah }}</td>
                    </tr>
                    <tr>
                        <td class="font-bold py-1">Jalur Pendaftaran</td>
                        <td>: {{ $pendaftar->jalur_pendaftaran }}</td>
                    </tr>
                    <tr>
                        <td class="font-bold py-1">Status Biaya</td>
                        <td class="font-bold text-emerald-700">: LUNAS (TERVERIFIKASI)</td>
                    </tr>
                </table>
            </div>

            <!-- Right Photo & QR -->
            <div class="col-span-4 flex flex-col items-center justify-center space-y-2">
                <div class="w-28 h-36 border-2 border-dashed border-slate-400 bg-slate-50 flex items-center justify-center text-center p-2 rounded-lg">
                    @php
                        $pasfoto = $pendaftar->berkas->where('jenis_berkas', 'pasfoto')->first();
                    @endphp
                    @if($pasfoto)
                    <img src="{{ route('dokumen.berkas', $pasfoto->id_berkas) }}" alt="Pas Foto" class="w-full h-full object-cover rounded">
                    @else
                    <span class="text-[10px] text-slate-400 font-bold uppercase">Pas Foto 3x4 Resmi</span>
                    @endif
                </div>
                <div class="w-20 h-20 border border-black p-1 bg-white text-center flex flex-col items-center justify-center">
                    <!-- QR Code Verifikasi Kartu Ujian -->
                    <div id="qr-kartu-ujian"></div>
                </div>
            </div>
        </div>

        <!-- Schedule & Rules -->
        <div class="p-3 bg-slate-50 border border-black rounded-lg space-y-1.5 text-[11px]">
            <strong class="block text-xs uppercase underline">Jadwal & Lokasi Tes Seleksi Masuk:</strong>
            @if(!empty($penempatan) && $penempatan->jadwal)
                <p>1. <strong>Materi Ujian:</strong> {{ $penempatan->jadwal->nama_jadwal }} ({{ $penempatan->jadwal->jenis_seleksi }})</p>
                <p>2. <strong>Waktu Pelaksanaan:</strong> {{ \Carbon\Carbon::parse($penempatan->jadwal->tanggal)->isoFormat('dddd, D MMMM Y') }} | Pukul {{ substr($penempatan->jadwal->waktu_mulai, 0, 5) }} - {{ substr($penempatan->jadwal->waktu_selesai, 0, 5) }} WIB</p>
                <p>3. <strong>Ruang & Tempat:</strong> {{ $penempatan->ruang ? $penempatan->ruang->nama_ruang . ' (' . $penempatan->ruang->kode_ruang . ')' : 'Belum Ditentukan' }}{{ $penempatan->ruang && $penempatan->ruang->gedung ? ', Gedung ' . $penempatan->ruang->gedung : '' }} | {{ $penempatan->jadwal->lokasi ?? 'Kampus SMK Islam Bina Khalifah Bangsa' }}</p>
                <p>4. <strong>Tata Tertib:</strong> Wajib membawa cetakan Kartu Ujian ini, memakai seragam SMP asal rapi & bersepatu, serta hadir 15 menit sebelum tes dimulai. {{ $penempatan->jadwal->keterangan ? '(' . $penempatan->jadwal->keterangan . ')' : '' }}</p>
            @else
                <p>1. <strong>Materi Ujian:</strong> Tes Wawancara Minat Bakat, Tes Baca Al-Qur'an (Tahfiz), dan Tes Buta Warna.</p>
                <p>2. <strong>Waktu & Tempat:</strong> Gedung Kampus SMK Islam Bina Khalifah Bangsa, Ruang Laboratorium 1.</p>
                <p>3. <strong>Tata Tertib:</strong> Wajib membawa cetakan Kartu Ujian ini, memakai seragam SMP asal rapi & bersepatu, serta hadir 15 menit sebelum tes dimulai.</p>
            @endif
        </div>

        <!-- Signature -->
        <div class="pt-2 flex justify-between items-end text-center">
            <div class="space-y-12">
                <div>Tanda Tangan Calon Siswa,</div>
                <div class="font-bold">( {{ strtoupper($pendaftar->nama) }} )</div>
            </div>

            <div class="space-y-12">
                <div>
                    Kota Metro, {{ date('d F Y') }}<br>
                    <strong>Ketua Panitia PPDB,</strong>
                </div>
                <div class="font-bold underline">
                    Budi Santoso, S.Kom
                </div>
            </div>
        </div>
    </div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        var qr = qrcode(0, 'M');
        qr.addData(@json(route('verifikasi.kartu_ujian', $pendaftar->verification_token ?? 'invalid')));
        qr.make();
        var el = document.getElementById('qr-kartu-ujian');
        if (el) {
            el.innerHTML = qr.createImgTag(2, 2);
        }
    });
</script>

</body>
</html>

