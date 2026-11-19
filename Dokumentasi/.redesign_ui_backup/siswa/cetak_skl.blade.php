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
        <a href="{{ route('admin.pendaftar.detail', $pendaftar->id_pendaftar) }}" class="px-4 py-2.5 rounded-xl bg-slate-200 text-slate-700 font-bold text-xs hover:bg-slate-300 transition-colors">
            &larr; Kembali ke Detail Pendaftar
        </a>
        @else
        <a href="{{ route('siswa.dashboard') }}" class="px-4 py-2.5 rounded-xl bg-slate-200 text-slate-700 font-bold text-xs hover:bg-slate-300 transition-colors">
            &larr; Kembali ke Dashboard
        </a>
        @endif
        <button onclick="window.print()" class="px-6 py-2.5 rounded-xl bg-rose-500 hover:bg-rose-600 text-white font-bold text-xs shadow-md shadow-rose-500/20 transition-all">
            Cetak SKL Resmi (PDF)
        </button>
    </div>

    <!-- Official SKL Document -->
    <div class="max-w-3xl mx-auto bg-white p-8 md:p-12 border border-slate-300 shadow-xl text-xs space-y-6">
        <!-- Kop Surat Resmi -->
        <div class="border-b-4 border-double border-black pb-4 text-center space-y-1 relative pl-20">
            <img src="{{ asset('images/logo.png') }}" alt="Logo SMK" class="w-16 h-16 object-contain absolute left-0 top-1/2 -translate-y-1/2">
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
        <div class="pt-6 grid grid-cols-2 items-end">
            <!-- QR Verification -->
            <div class="flex items-center gap-3">
                <div class="w-20 h-20 border border-black p-1 bg-white flex items-center justify-center">
                    <div id="qr-skl"></div>
                </div>
                <div class="text-[10px] text-slate-500">
                    Pindai QR Code untuk memverifikasi<br>keaslian surat keputusan kelulusan ini<br>secara online melalui portal sekolah.
                </div>
            </div>

            <!-- Headmaster Signature -->
            <div class="text-center space-y-16">
                <div>
                    {{ $pengaturan->kota }}, {{ $pendaftar->terima ? $pendaftar->terima->tgl_diterima->translatedFormat('d F Y') : date('d F Y') }}<br>
                    <strong>Kepala Sekolah,</strong>
                </div>
                <div>
                    <strong class="block text-sm uppercase underline">{{ $pengaturan->nama_kepsek }}</strong>
                    <span class="block text-[11px] text-slate-600">NIP. {{ $pengaturan->nip_kepsek ?: '197805122005011003' }}</span>
                </div>
            </div>
        </div>
    </div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        var qr = qrcode(0, 'M');
        qr.addData(@json(route('verifikasi.skl', $pendaftar->terima->verification_token ?? 'invalid')));
        qr.make();
        var el = document.getElementById('qr-skl');
        if (el) {
            el.innerHTML = qr.createImgTag(2, 2);
        }
    });
</script>

</body>
</html>

