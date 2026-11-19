<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verifikasi Dokumen — {{ $pengaturan->nama_sekolah }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = { darkMode: 'class' }
    </script>
</head>
<body class="bg-slate-100 text-slate-900 font-sans min-h-screen flex items-center justify-center p-4">

    <div class="w-full max-w-md space-y-6">
        <!-- Header Sekolah -->
        <div class="text-center space-y-1">
            <h1 class="text-lg font-extrabold uppercase text-slate-800">{{ $pengaturan->nama_sekolah }}</h1>
            <p class="text-xs text-slate-500">Sistem Verifikasi Dokumen PPDB Online</p>
        </div>

        <!-- Hasil Verifikasi -->
        @if($valid)
        <div class="bg-white border-2 border-emerald-500 rounded-2xl shadow-lg overflow-hidden">
            <div class="bg-emerald-600 px-6 py-4 text-center">
                <div class="inline-flex items-center justify-center w-12 h-12 rounded-full bg-white/20 mb-2">
                    <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                    </svg>
                </div>
                <h2 class="text-white font-extrabold text-base uppercase tracking-wider">Dokumen Terverifikasi</h2>
                <p class="text-emerald-100 text-xs mt-1">{{ $tipe }}</p>
            </div>
            <div class="p-6 space-y-3">
                <table class="w-full text-xs">
                    @foreach($data as $label => $value)
                    <tr>
                        <td class="font-bold py-1.5 text-slate-600 capitalize w-40">{{ str_replace('_', ' ', $label) }}</td>
                        <td class="py-1.5 font-semibold text-slate-900">{{ $value }}</td>
                    </tr>
                    @endforeach
                </table>
            </div>
            <div class="px-6 pb-5">
                <div class="p-3 bg-emerald-50 border border-emerald-200 rounded-xl text-center">
                    <p class="text-[11px] text-emerald-800 font-semibold">
                        ✓ Dokumen ini diterbitkan secara resmi oleh {{ $pengaturan->nama_sekolah }}
                    </p>
                    <p class="text-[10px] text-emerald-600 mt-1">
                        Diverifikasi pada {{ now()->translatedFormat('d F Y, H:i') }} WIB
                    </p>
                </div>
            </div>
        </div>
        @else
        <div class="bg-white border-2 border-red-400 rounded-2xl shadow-lg overflow-hidden">
            <div class="bg-red-600 px-6 py-4 text-center">
                <div class="inline-flex items-center justify-center w-12 h-12 rounded-full bg-white/20 mb-2">
                    <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </div>
                <h2 class="text-white font-extrabold text-base uppercase tracking-wider">Verifikasi Gagal</h2>
                <p class="text-red-100 text-xs mt-1">{{ $tipe }}</p>
            </div>
            <div class="p-6">
                <div class="p-4 bg-red-50 border border-red-200 rounded-xl text-center">
                    <p class="text-xs text-red-800 font-semibold">{{ $pesan }}</p>
                    <p class="text-[10px] text-red-600 mt-2">
                        Jika Anda yakin dokumen ini asli, hubungi panitia PPDB {{ $pengaturan->nama_sekolah }} untuk klarifikasi.
                    </p>
                </div>
            </div>
        </div>
        @endif

        <!-- Footer -->
        <div class="text-center space-y-2">
            <a href="{{ route('landing') }}" class="inline-block px-5 py-2.5 rounded-xl bg-slate-200 text-slate-700 font-bold text-xs hover:bg-slate-300 transition-colors">
                &larr; Kembali ke Halaman Utama
            </a>
            <p class="text-[10px] text-slate-400">
                {{ $pengaturan->nama_sekolah }} &mdash; {{ $pengaturan->alamat }}, {{ $pengaturan->kota }}
            </p>
        </div>
    </div>

</body>
</html>

