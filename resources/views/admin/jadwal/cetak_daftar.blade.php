<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Peserta Tes - Ruang {{ $ruang->nama_ruang }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @media print {
            .no-print { display: none !important; }
            body { background: white !important; color: black !important; padding: 0 !important; }
            @page { size: A4 portrait; margin: 12mm; }
        }
    </style>
</head>
<body class="bg-slate-100 text-slate-900 font-sans p-4 md:p-8">

    <!-- Action Bar -->
    <div class="max-w-4xl mx-auto mb-6 flex items-center justify-between no-print">
        <a href="{{ route('admin.jadwal.penempatan', $jadwal->id_jadwal) }}" class="px-4 py-2 rounded-xl bg-slate-200 text-slate-700 font-bold text-xs hover:bg-slate-300">
            &larr; Kembali ke Penempatan
        </a>
        <button onclick="window.print()" class="px-6 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs shadow-md">
            Cetak Daftar Hadir (PDF)
        </button>
    </div>

    <!-- Printable Sheet -->
    <div class="max-w-4xl mx-auto bg-white p-8 border border-slate-300 rounded-xl shadow-lg text-xs space-y-4">
        
        <!-- Kop Surat Resmi -->
        <div class="border-b-2 border-black pb-3 text-center space-y-1">
            <h2 class="text-xs font-bold uppercase tracking-wider">PANITIA PENERIMAAN PESERTA DIDIK BARU (PPDB)</h2>
            <h1 class="text-lg font-black uppercase">{{ $pengaturan->nama_sekolah ?? 'SMK ISLAM BINA KHALIFAH BANGSA' }}</h1>
            <p class="text-[11px] text-slate-700">{{ $pengaturan->alamat ?? 'Kota Metro, Lampung' }} | NPSN: {{ $pengaturan->npsn ?? '10807575' }}</p>
        </div>

        <!-- Judul Berita Acara / Daftar Hadir -->
        <div class="text-center pt-1">
            <h3 class="text-sm font-black uppercase tracking-wider underline">DAFTAR HADIR PESERTA TES SELEKSI MASUK</h3>
            <p class="text-xs font-bold text-slate-700">{{ $jadwal->nama_jadwal }} ({{ $jadwal->jenis_seleksi }})</p>
        </div>

        <!-- Meta Information Grid -->
        <div class="grid grid-cols-2 gap-4 bg-slate-50 p-3 rounded-lg border border-slate-300 text-xs">
            <table class="w-full">
                <tr>
                    <td class="font-bold py-0.5 w-28">Hari, Tanggal</td>
                    <td>: {{ \Carbon\Carbon::parse($jadwal->tanggal)->isoFormat('dddd, D MMMM Y') }}</td>
                </tr>
                <tr>
                    <td class="font-bold py-0.5">Waktu Pelaksanaan</td>
                    <td>: Pukul {{ substr($jadwal->waktu_mulai, 0, 5) }} - {{ substr($jadwal->waktu_selesai, 0, 5) }} WIB</td>
                </tr>
                <tr>
                    <td class="font-bold py-0.5">Lokasi Seleksi</td>
                    <td>: {{ $jadwal->lokasi ?? 'Kampus Utama' }}</td>
                </tr>
            </table>

            <table class="w-full">
                <tr>
                    <td class="font-bold py-0.5 w-28">Ruang Seleksi</td>
                    <td class="font-bold">: {{ $ruang->nama_ruang }} ({{ $ruang->kode_ruang }})</td>
                </tr>
                <tr>
                    <td class="font-bold py-0.5">Gedung / Lantai</td>
                    <td>: {{ $ruang->gedung ?: '-' }}</td>
                </tr>
                <tr>
                    <td class="font-bold py-0.5">Jumlah Peserta</td>
                    <td class="font-bold">: {{ $penempatan->count() }} orang (Kapasitas: {{ $ruang->kapasitas }})</td>
                </tr>
            </table>
        </div>

        <!-- Tabel Peserta -->
        <table class="w-full border-collapse border border-black text-[11px] mt-4">
            <thead>
                <tr class="bg-slate-100 font-bold text-center">
                    <th class="border border-black py-2 px-1 w-8">No</th>
                    <th class="border border-black py-2 px-2 w-28">No. Peserta</th>
                    <th class="border border-black py-2 px-3 text-left">Nama Calon Siswa</th>
                    <th class="border border-black py-2 px-2 w-24">NISN</th>
                    <th class="border border-black py-2 px-2 text-left">Pilihan Jurusan</th>
                    <th class="border border-black py-2 px-2 w-28">Tanda Tangan</th>
                    <th class="border border-black py-2 px-2 w-20">Ket.</th>
                </tr>
            </thead>
            <tbody>
                @forelse($penempatan as $i => $item)
                <tr>
                    <td class="border border-black py-2 px-1 text-center font-mono">{{ $i + 1 }}</td>
                    <td class="border border-black py-2 px-2 text-center font-mono font-bold">{{ $item->pendaftar->no_pendaftaran ?? '-' }}</td>
                    <td class="border border-black py-2 px-3 font-bold uppercase">{{ $item->pendaftar->nama ?? '-' }}</td>
                    <td class="border border-black py-2 px-2 text-center font-mono">{{ $item->pendaftar->nisn ?? '-' }}</td>
                    <td class="border border-black py-2 px-2">
                        {{ $item->pendaftar && $item->pendaftar->jurusan1 ? $item->pendaftar->jurusan1->nama_jurusan : '-' }}
                    </td>
                    <td class="border border-black py-2 px-2 text-left text-[10px] text-slate-400">
                        {{ $i + 1 }}. ....................
                    </td>
                    <td class="border border-black py-2 px-2 text-center text-[10px]">
                        {{ $item->catatan ?: 'Hadir' }}
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="border border-black py-4 text-center italic text-slate-500">
                        Belum ada peserta yang ditempatkan di ruang ini.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>

        <!-- Signature Section -->
        <div class="pt-8 flex justify-between items-end text-xs">
            <div class="space-y-14 text-center">
                <div>
                    Pengawas Ruang Ujian,
                </div>
                <div class="font-bold underline">
                    ( .................................................. )
                </div>
            </div>

            <div class="space-y-14 text-center">
                <div>
                    Kota Metro, {{ \Carbon\Carbon::parse($jadwal->tanggal)->isoFormat('D MMMM Y') }}<br>
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

