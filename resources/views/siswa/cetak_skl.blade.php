<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Surat Keterangan Lulus (SKL) - {{ $pendaftar->nama }}</title>
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
        <button onclick="window.print()" class="px-6 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs shadow-md">
            Cetak SKL Resmi (PDF)
        </button>
    </div>

    <!-- Official SKL Document -->
    <div class="max-w-3xl mx-auto bg-white p-8 md:p-12 border border-slate-300 shadow-xl text-xs space-y-6">
        <!-- Kop Surat Resmi -->
        <div class="border-b-4 border-double border-black pb-4 text-center space-y-1">
            <h2 class="text-sm font-extrabold uppercase tracking-wide">MAJELIS PENDIDIKAN DASAR DAN MENENGAH</h2>
            <h1 class="text-xl font-black uppercase tracking-wider text-slate-900">{{ $pengaturan->nama_sekolah }}</h1>
            <p class="text-xs">NPSN: {{ $pengaturan->npsn }} | Status: Terakreditasi | Ijin Operasional: {{ $pengaturan->ijin_operasional }}</p>
            <p class="text-xs text-slate-700">{{ $pengaturan->alamat }}, {{ $pengaturan->kota }}, {{ $pengaturan->provinsi }} | Website: {{ $pengaturan->website }}</p>
        </div>

        <!-- Judul Surat -->
        <div class="text-center space-y-1">
            <h3 class="text-base font-extrabold uppercase tracking-wider underline">SURAT KETERANGAN LULUS SELEKSI PPDB</h3>
            <p class="text-xs font-mono font-bold">Nomor: {{ $pendaftar->terima ? $pendaftar->terima->no_surat_kelulusan : '421.5/SKL-PPDB/2026/0001' }}</p>
        </div>

        <p class="text-justify leading-relaxed">
            Kepala SMK Islam Bina Khalifah Bangsa Kota Metro, Provinsi Lampung, menerangkan bahwa berdasarkan hasil seleksi administrasi, verifikasi berkas, dan uji kompetensi peminatan pada Penerimaan Peserta Didik Baru (PPDB) Tahun Ajaran 2026/2027:
        </p>

        <!-- Data Siswa Table -->
        <div class="p-4 bg-slate-50 border border-black rounded-lg space-y-1.5">
            <table class="w-full text-xs">
                <tr>
                    <td class="w-48 font-bold py-1">Nama Lengkap</td>
                    <td class="font-bold uppercase text-sm">: {{ $pendaftar->nama }}</td>
                </tr>
                <tr>
                    <td class="font-bold py-1">NISN</td>
                    <td class="font-mono font-bold">: {{ $pendaftar->nisn }}</td>
                </tr>
                <tr>
                    <td class="font-bold py-1">Nomor Registrasi PPDB</td>
                    <td class="font-mono">: {{ $pendaftar->no_pendaftaran }}</td>
                </tr>
                <tr>
                    <td class="font-bold py-1">Tempat, Tanggal Lahir</td>
                    <td>: {{ $pendaftar->tempat_lahir }}, {{ $pendaftar->tanggal_lahir->translatedFormat('d F Y') }}</td>
                </tr>
                <tr>
                    <td class="font-bold py-1">Asal Sekolah</td>
                    <td>: {{ $pendaftar->asal_sekolah }}</td>
                </tr>
                <tr>
                    <td class="font-bold py-1">Nama Orang Tua / Wali</td>
                    <td>: {{ $pendaftar->nama_ayah }} / {{ $pendaftar->nama_ibu }}</td>
                </tr>
            </table>
        </div>

        <!-- Decision Box -->
        <div class="text-center p-4 bg-emerald-50 border-2 border-emerald-700 rounded-xl space-y-1">
            <span class="text-xs font-bold uppercase tracking-wider text-emerald-900 block">Dinyatakan:</span>
            <div class="text-2xl font-black text-emerald-800 tracking-wider">LULUS / DITERIMA</div>
            <div class="text-xs font-bold text-slate-800 pt-1">
                Sebagai Peserta Didik Baru pada Program Keahlian:
                <span class="block text-base text-emerald-900 font-extrabold mt-0.5">
                    {{ $pendaftar->terima ? $pendaftar->terima->jurusan->nama_jurusan : ($pendaftar->jurusan1 ? $pendaftar->jurusan1->nama_jurusan : '-') }}
                </span>
            </div>
        </div>

        <p class="text-justify leading-relaxed">
            Demikian surat keterangan ini diberikan kepada yang bersangkutan untuk dipergunakan sebagaimana mestinya dan sebagai syarat wajib dalam pelaksanaan <strong>Daftar Ulang</strong> serta penyerahan kelengkapan berkas fisik ke panitia PPDB sekolah.
        </p>

        <!-- Signatures & Official Stamp -->
