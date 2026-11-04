<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kartu Peserta Tes Seleksi - {{ $pendaftar->nama }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
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
        <a href="{{ route('siswa.dashboard') }}" class="px-4 py-2 rounded-xl bg-slate-200 text-slate-700 font-bold text-xs hover:bg-slate-300">
            &larr; Kembali ke Dashboard
        </a>
        <button onclick="window.print()" class="px-6 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs shadow-md">
            Cetak Kartu Ujian (PDF)
        </button>
    </div>

    <!-- Printable Card Container -->
    <div class="max-w-2xl mx-auto bg-white p-6 md:p-8 border-2 border-black rounded-2xl shadow-xl text-xs space-y-5">
        <!-- Kop Surat -->
        <div class="border-b-2 border-black pb-3 text-center space-y-1">
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
                        <td class="font-bold">: {{ $pendaftar->jurusan1->nama_jurusan }}</td>
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
                    <img src="{{ asset('storage/' . $pasfoto->path_file) }}" alt="Pas Foto" class="w-full h-full object-cover rounded">
                    @else
                    <span class="text-[10px] text-slate-400 font-bold uppercase">Pas Foto 3x4 Resmi</span>
                    @endif
                </div>
                <div class="w-20 h-20 border border-black p-1 bg-white text-center flex flex-col items-center justify-center">
                    <!-- QR Simulation -->
                    <div class="w-16 h-16 bg-slate-900 p-1 flex items-center justify-center text-white text-[8px] font-mono leading-none break-all text-center">
                        QR VALID<br>{{ substr($pendaftar->no_pendaftaran, -7) }}
                    </div>
                </div>
            </div>
        </div>

        <!-- Schedule & Rules -->
        <div class="p-3 bg-slate-50 border border-black rounded-lg space-y-1.5 text-[11px]">
            <strong class="block text-xs uppercase underline">Jadwal & Lokasi Tes Seleksi Masuk:</strong>
            <p>1. <strong>Materi Ujian:</strong> Tes Wawancara Minat Bakat, Tes Baca Al-Qur'an (Tahfiz), dan Tes Buta Warna.</p>
            <p>2. <strong>Waktu & Tempat:</strong> Gedung Kampus SMK Islam Bina Khalifah Bangsa, Ruang Laboratorium 1.</p>
            <p>3. <strong>Tata Tertib:</strong> Wajib membawa cetakan Kartu Ujian ini, memakai seragam SMP asal rapi & bersepatu, serta hadir 15 menit sebelum tes dimulai.</p>
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

</body>
</html>

