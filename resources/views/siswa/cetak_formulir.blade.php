<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Formulir Pendaftaran - {{ $pendaftar->nama }} ({{ $pendaftar->no_pendaftaran }})</title>
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

    <div class="max-w-3xl mx-auto mb-6 flex items-center justify-between no-print">
        @if(!empty($adminMode))
        <a href="{{ route('admin.pendaftar.detail', $pendaftar->id_pendaftar) }}" class="px-4 py-2 rounded-xl bg-slate-200 text-slate-700 font-bold text-xs hover:bg-slate-300">
            &larr; Kembali ke Detail Pendaftar
        </a>
        @else
        <a href="{{ route('siswa.dashboard') }}" class="px-4 py-2 rounded-xl bg-slate-200 text-slate-700 font-bold text-xs hover:bg-slate-300">
            &larr; Kembali ke Dashboard
        </a>
        @endif
        <button onclick="window.print()" class="px-6 py-2.5 rounded-xl bg-[#fa2f79] hover:bg-[#e11d48] text-white font-bold text-xs shadow-md shadow-pink-500/25 flex items-center gap-2">
            <span>Cetak / Simpan PDF</span>
        </button>
    </div>

    <!-- Official Printable Sheet -->
    <div class="max-w-3xl mx-auto bg-white p-8 md:p-12 border border-slate-300 shadow-lg text-sm space-y-6">
        <!-- Kop Surat -->
        <div class="border-b-2 border-black pb-4 text-center space-y-1 relative">
            <h2 class="text-base font-extrabold uppercase tracking-wide">YAYASAN BINA KHALIFAH BANGSA</h2>
            <h1 class="text-xl font-black uppercase tracking-wider">{{ $pengaturan->nama_sekolah }}</h1>
            <p class="text-xs">NPSN: {{ $pengaturan->npsn }} | Izin Operasional: {{ $pengaturan->ijin_operasional }}</p>
            <p class="text-xs text-slate-700">{{ $pengaturan->alamat }}, {{ $pengaturan->kota }}, {{ $pengaturan->provinsi }} | Telp: {{ $pengaturan->no_telp }} | Email: {{ $pengaturan->email }}</p>
        </div>

        <!-- Form Title -->
        <div class="text-center space-y-1">
            <h3 class="text-base font-bold uppercase tracking-wider underline">CETAK FORMULIR PENDAFTARAN PENERIMAAN PESERTA DIDIK BARU</h3>
            <p class="text-xs font-semibold">TAHUN PELAJARAN {{ $pendaftar->gelombang ? $pendaftar->gelombang->tahun_ajaran : '2026/2027' }}</p>
            <div class="inline-block mt-1 px-3 py-1 bg-slate-100 border border-slate-300 font-mono font-bold text-xs">
                No. Registrasi: {{ $pendaftar->no_pendaftaran }}
            </div>
        </div>

        <!-- Data Table (Format Skripsi 2022) -->
        <table class="w-full border-collapse border border-black text-xs">
            <tbody>
                <tr>
                    <td class="border border-black p-2 font-bold w-1/3 bg-slate-50">NISN</td>
                    <td class="border border-black p-2 font-mono font-bold">{{ $pendaftar->nisn }}</td>
                </tr>
                <tr>
                    <td class="border border-black p-2 font-bold bg-slate-50">Nama Lengkap</td>
                    <td class="border border-black p-2 font-bold uppercase">{{ $pendaftar->nama }}</td>
                </tr>
                <tr>
                    <td class="border border-black p-2 font-bold bg-slate-50">Tempat, Tanggal Lahir</td>
                    <td class="border border-black p-2">{{ $pendaftar->tempat_lahir }}, {{ $pendaftar->tanggal_lahir->translatedFormat('d F Y') }}</td>
                </tr>
                <tr>
                    <td class="border border-black p-2 font-bold bg-slate-50">Jenis Kelamin</td>
                    <td class="border border-black p-2">{{ $pendaftar->jenis_kelamin }}</td>
                </tr>
                <tr>
                    <td class="border border-black p-2 font-bold bg-slate-50">Agama</td>
                    <td class="border border-black p-2">{{ $pendaftar->agama }}</td>
                </tr>
                <tr>
                    <td class="border border-black p-2 font-bold bg-slate-50">Pilihan Jurusan 1</td>
                    <td class="border border-black p-2 font-bold">{{ $pendaftar->jurusan1 ? $pendaftar->jurusan1->nama_jurusan . ' (' . $pendaftar->jurusan1->kode_jurusan . ')' : 'Belum dipilih' }}</td>
                </tr>
                <tr>
                    <td class="border border-black p-2 font-bold bg-slate-50">Pilihan Jurusan 2</td>
                    <td class="border border-black p-2">{{ $pendaftar->jurusan2 ? $pendaftar->jurusan2->nama_jurusan : '-' }}</td>
                </tr>
                <tr>
                    <td class="border border-black p-2 font-bold bg-slate-50">Jalur Pendaftaran</td>
                    <td class="border border-black p-2">{{ $pendaftar->jalur_pendaftaran }}</td>
                </tr>
                <tr>
                    <td class="border border-black p-2 font-bold bg-slate-50">Anak Ke- / Jumlah Saudara</td>
                    <td class="border border-black p-2">Anak ke-{{ $pendaftar->anak_ke }} dari {{ $pendaftar->jml_saudara }} bersaudara</td>
                </tr>
                <tr>
                    <td class="border border-black p-2 font-bold bg-slate-50">No. HP / WhatsApp Siswa</td>
                    <td class="border border-black p-2">{{ $pendaftar->hp_siswa }}</td>
                </tr>
                <tr>
                    <td class="border border-black p-2 font-bold bg-slate-50">Alamat Tempat Tinggal</td>
                    <td class="border border-black p-2">{{ $pendaftar->almt_siswa }}</td>
                </tr>
                <tr>
                    <td class="border border-black p-2 font-bold bg-slate-50">Berat Badan / Tinggi Badan</td>
