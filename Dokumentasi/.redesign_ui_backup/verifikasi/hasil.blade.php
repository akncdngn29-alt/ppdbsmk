<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verifikasi Dokumen — {{ $pengaturan->nama_sekolah }}</title>
    <link rel="icon" type="image/png" href="{{ asset('images/logo.png') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/lucide@latest"></script>
</head>
<body class="bg-slate-50 text-slate-800 font-sans min-h-screen flex items-center justify-center p-4">

    <div class="w-full max-w-md space-y-6">
        <!-- Header Sekolah dengan Logo Resmi -->
        <div class="text-center space-y-2">
            <img src="{{ asset('images/logo.png') }}" alt="Logo {{ $pengaturan->nama_sekolah }}" class="w-16 h-16 mx-auto object-contain drop-shadow">
            <h1 class="text-base sm:text-lg font-black uppercase text-[#0B182F] tracking-wide">{{ $pengaturan->nama_sekolah }}</h1>
            <p class="text-xs text-slate-500 font-medium">Sistem Validasi Dokumen Resmi PPDB Online</p>
        </div>

        <!-- Hasil Verifikasi -->
        @if($valid)
        <div class="bg-white border-2 border-emerald-500 rounded-3xl shadow-xl overflow-hidden">
            <div class="bg-[#0B182F] px-6 py-5 text-center text-white border-b-4 border-emerald-500">
                <div class="inline-flex items-center justify-center w-12 h-12 rounded-full bg-emerald-500/20 text-emerald-400 mb-2 border border-emerald-500/40">
                    <i data-lucide="check-circle-2" class="w-7 h-7"></i>
                </div>
                <h2 class="font-extrabold text-base uppercase tracking-wider text-white">Dokumen Terverifikasi Sah</h2>
                <p class="text-emerald-300 text-xs mt-0.5 font-medium">{{ $tipe }}</p>
            </div>
            <div class="p-6 space-y-3">
                <table class="w-full text-xs">
                    @foreach($data as $label => $value)
                    <tr class="border-b border-slate-100 last:border-b-0">
                        <td class="font-bold py-2 text-slate-500 capitalize w-36">{{ str_replace('_', ' ', $label) }}</td>
                        <td class="py-2 font-semibold text-[#0B182F]">{{ $value }}</td>
                    </tr>
                    @endforeach
                </table>
            </div>
            <div class="px-6 pb-6">
                <div class="p-3.5 bg-emerald-50 border border-emerald-200 rounded-2xl text-center">
                    <p class="text-xs text-emerald-900 font-bold flex items-center justify-center gap-1.5">
                        <i data-lucide="shield-check" class="w-4 h-4 text-emerald-600"></i>
                        <span>Dokumen ini resmi diterbitkan oleh {{ $pengaturan->nama_sekolah }}</span>
                    </p>
                    <p class="text-[10px] text-emerald-700 mt-1">
                        Diverifikasi pada {{ now()->translatedFormat('d F Y, H:i') }} WIB
                    </p>
                </div>
            </div>
        </div>
        @else
        <div class="bg-white border-2 border-rose-400 rounded-3xl shadow-xl overflow-hidden">
            <div class="bg-[#0B182F] px-6 py-5 text-center text-white border-b-4 border-rose-500">
                <div class="inline-flex items-center justify-center w-12 h-12 rounded-full bg-rose-500/20 text-rose-400 mb-2 border border-rose-500/40">
                    <i data-lucide="x-circle" class="w-7 h-7"></i>
                </div>
                <h2 class="font-extrabold text-base uppercase tracking-wider text-white">Verifikasi Tidak Ditemukan</h2>
                <p class="text-rose-300 text-xs mt-0.5 font-medium">{{ $tipe }}</p>
            </div>
            <div class="p-6">
                <div class="p-4 bg-rose-50 border border-rose-200 rounded-2xl text-center">
                    <p class="text-xs text-rose-900 font-semibold">{{ $pesan }}</p>
                    <p class="text-[11px] text-slate-500 mt-2 leading-relaxed">
                        Jika Anda yakin dokumen ini asli, silakan hubungi Panitia PPDB {{ $pengaturan->nama_sekolah }} untuk klarifikasi lebih lanjut.
                    </p>
                </div>
            </div>
        </div>
        @endif

        <!-- Footer -->
        <div class="text-center space-y-2">
            <a href="{{ route('landing') }}" class="inline-flex items-center gap-1.5 px-6 py-2.5 rounded-full bg-white hover:bg-slate-100 text-[#0B182F] border border-slate-300 font-bold text-xs shadow-xs transition-colors">
                <i data-lucide="arrow-left" class="w-3.5 h-3.5"></i>
                <span>Kembali ke Halaman Utama</span>
            </a>
            <p class="text-[11px] text-slate-500">
                {{ $pengaturan->nama_sekolah }} &bull; {{ $pengaturan->alamat }}
            </p>
        </div>
    </div>

    <script>
        lucide.createIcons();
    </script>
</body>
</html>
