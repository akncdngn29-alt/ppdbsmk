<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rekapitulasi Data Pendaftar PPDB - {{ $pengaturan->nama_sekolah }}</title>
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @media print {
            .no-print { display: none !important; }
            body { padding: 0; background: white; }
            @page {
                size: A4 landscape;
                margin: 12mm 10mm 15mm 10mm;
            }
        }
    </style>
</head>
<body class="bg-slate-100 text-slate-800 font-sans p-4 sm:p-8">

    <!-- Action Toolbar (No Print) -->
    <div class="max-w-6xl mx-auto mb-6 flex items-center justify-between no-print bg-white p-4 rounded-xl shadow-sm border border-slate-200">
        <div class="flex items-center gap-2">
            <a href="{{ route('admin.dashboard') }}" class="px-3 py-1.5 rounded-lg border border-slate-300 text-xs font-semibold text-slate-600 hover:bg-slate-50 transition-colors">
                &larr; Kembali ke Panel Admin
            </a>
            <span class="text-xs text-slate-400">|</span>
            <span class="text-xs font-semibold text-slate-700">Total Data: {{ count($pendaftar) }} Calon Siswa</span>
        </div>
        <button onclick="window.print()" class="px-4 py-2 rounded-xl bg-[#fa2f79] hover:bg-[#e11d48] text-white font-bold text-xs flex items-center gap-2 shadow-sm shadow-pink-500/20 transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
            Cetak Dokumen Resmi / Simpan PDF
        </button>
    </div>

    <!-- Printable Paper Area -->
    <div class="max-w-6xl mx-auto bg-white p-8 sm:p-12 shadow-md border border-slate-200 rounded-sm print:shadow-none print:border-none print:p-0">

        <!-- Kop Surat Lembaga -->
        <div class="border-b-4 border-double border-slate-900 pb-4 mb-6">
            <div class="flex items-center justify-between gap-4">
                <div class="w-20 h-20 rounded-full border-2 border-[#fa2f79] flex items-center justify-center font-bold text-[#fa2f79] text-2xl bg-pink-50 shrink-0">
                    SMK
                </div>
                <div class="text-center flex-1">
                    <h3 class="text-xs uppercase tracking-widest font-semibold text-slate-700">YAYASAN BINA KHALIFAH BANGSA</h3>
                    <h1 class="text-xl sm:text-2xl font-black uppercase text-slate-900 tracking-wide">{{ $pengaturan->nama_sekolah }}</h1>
                    <p class="text-xs font-semibold text-slate-700 mt-0.5">TERAKREDITASI | NPSN: {{ $pengaturan->npsn }} | Ijin Operasional: {{ $pengaturan->ijin_operasional }}</p>
                    <p class="text-[11px] text-slate-600 mt-1">{{ $pengaturan->alamat }}, {{ $pengaturan->kota }}, {{ $pengaturan->provinsi }}</p>
                    <p class="text-[10px] text-slate-500">Telp: {{ $pengaturan->no_telp }} | Email: {{ $pengaturan->email }} | Web: {{ $pengaturan->website }}</p>
                </div>
                <div class="w-20 h-20 rounded-xl border border-slate-200 flex items-center justify-center font-bold text-slate-400 text-xs shrink-0 text-center leading-tight">
                    PPDB<br>2026
                </div>
            </div>
        </div>

        <!-- Judul Laporan -->
        <div class="text-center mb-6">
            <h2 class="text-sm font-bold uppercase tracking-wider text-slate-900 underline">
                LAPORAN REKAPITULASI PENERIMAAN PESERTA DIDIK BARU (PPDB)
            </h2>
            <p class="text-xs text-slate-700 mt-1">
                Tahun Ajaran 2026/2027 
                @if($selectedJurusan)
                    &bull; Program Keahlian: <strong>{{ $selectedJurusan->nama_jurusan }} ({{ $selectedJurusan->kode_jurusan }})</strong>
                @else
                    &bull; Seluruh Program Keahlian
                @endif
            </p>
        </div>

        <!-- Tabel Rekap Data -->
        <div class="overflow-x-auto mb-6">
            <table class="w-full text-[11px] border-collapse border border-slate-300">
                <thead>
                    <tr class="bg-slate-100 text-slate-800">
                        <th class="border border-slate-300 px-2 py-2 text-center w-8">No</th>
                        <th class="border border-slate-300 px-2 py-2 text-center">No. Daftar</th>
                        <th class="border border-slate-300 px-2 py-2 text-center">NISN</th>
                        <th class="border border-slate-300 px-2 py-2 text-left">Nama Lengkap</th>
                        <th class="border border-slate-300 px-2 py-2 text-center">L/P</th>
                        <th class="border border-slate-300 px-2 py-2 text-left">Asal Sekolah</th>
                        <th class="border border-slate-300 px-2 py-2 text-left">Jurusan Pilihan</th>
                        <th class="border border-slate-300 px-2 py-2 text-center">Bayar</th>
                        <th class="border border-slate-300 px-2 py-2 text-center">Verifikasi</th>
                        <th class="border border-slate-300 px-2 py-2 text-center">Kelulusan</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($pendaftar as $index => $item)
                        <tr class="{{ $loop->iteration % 2 === 0 ? 'bg-slate-50/50' : 'bg-white' }}">
                            <td class="border border-slate-300 px-2 py-1.5 text-center">{{ $loop->iteration }}</td>
                            <td class="border border-slate-300 px-2 py-1.5 text-center font-mono font-semibold">{{ $item->no_pendaftaran }}</td>
                            <td class="border border-slate-300 px-2 py-1.5 text-center font-mono">{{ $item->nisn }}</td>
                            <td class="border border-slate-300 px-2 py-1.5 font-semibold text-slate-900">{{ $item->nama }}</td>
                            <td class="border border-slate-300 px-2 py-1.5 text-center">{{ $item->jenis_kelamin === 'Laki-laki' ? 'L' : 'P' }}</td>
                            <td class="border border-slate-300 px-2 py-1.5">{{ $item->asal_sekolah }}</td>
                            <td class="border border-slate-300 px-2 py-1.5">
                                <span class="font-semibold text-[#fa2f79]">{{ $item->jurusan1 ? $item->jurusan1->kode_jurusan : '-' }}</span>
                                @if($item->jurusan2)
                                    <span class="text-slate-400 text-[10px]"> / {{ $item->jurusan2->kode_jurusan }}</span>
                                @endif
                            </td>
                            <td class="border border-slate-300 px-2 py-1.5 text-center">
                                <span class="font-bold {{ $item->status_pembayaran === 'Paid' ? 'text-[#fa2f79]' : 'text-amber-700' }}">
                                    {{ $item->status_pembayaran }}
                                </span>
                            </td>
                            <td class="border border-slate-300 px-2 py-1.5 text-center">
                                {{ $item->status_verifikasi }}
                            </td>
                            <td class="border border-slate-300 px-2 py-1.5 text-center font-bold">
                                @if($item->status_kelulusan === 'Diterima')
                                    <span class="text-[#fa2f79]">DITERIMA</span>
                                @elseif($item->status_kelulusan === 'Cadangan')
                                    <span class="text-amber-700">CADANGAN</span>
                                @elseif($item->status_kelulusan === 'Tidak Lolos')
                                    <span class="text-rose-700">TIDAK LOLOS</span>
                                @else
                                    <span class="text-slate-400">PROSES</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10" class="border border-slate-300 px-4 py-8 text-center text-slate-400">
                                Tidak ada data pendaftar yang sesuai kriteria.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Tanda Tangan & Pengesahan -->
        <div class="grid grid-cols-2 gap-8 text-xs pt-4">
            <div>
                <p class="font-semibold text-slate-700">Catatan Panitia PPDB:</p>
                <ul class="list-disc list-inside text-[11px] text-slate-500 mt-1 space-y-0.5">
                    <li>Dokumen ini merupakan rekapitulasi data pendaftar sah per tanggal cetak.</li>
                    <li>Siswa berstatus "Diterima" berhak mencetak SKL dan melakukan daftar ulang.</li>
                </ul>
            </div>
            <div class="text-right">
                <p class="text-slate-700">{{ $pengaturan->kota }}, {{ \Carbon\Carbon::now()->translatedFormat('d F Y') }}</p>
                <p class="font-bold text-slate-900 mt-1">Kepala SMK Islam Bina Khalifah Bangsa,</p>
                
                <!-- Digital Stamp / Space for Signature -->
                <div class="h-20 flex items-center justify-end pr-8 my-1">
                    <div class="w-24 h-16 border border-dashed border-[#fa2f79] rounded-lg flex flex-col items-center justify-center text-[9px] text-[#fa2f79] uppercase font-bold transform -rotate-3">
                        <span>TERVERIFIKASI</span>
                        <span class="text-[7px] text-[#fa2f79] font-mono">PANITIA PPDB</span>
                    </div>
                </div>

                <p class="font-bold text-slate-900 underline">{{ $pengaturan->nama_kepsek }}</p>
                <p class="text-[11px] text-slate-600">NIP/NUPTK: {{ $pengaturan->nip_kepsek ?: '-' }}</p>
            </div>
        </div>

    </div>

</body>
</html>

