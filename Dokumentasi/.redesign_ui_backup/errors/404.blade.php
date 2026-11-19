<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>404 - Halaman Tidak Ditemukan | PPDB SMK Islam Bina Khalifah Bangsa</title>
    <link rel="icon" type="image/png" href="{{ asset('images/logo.png') }}">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/lucide@latest"></script>
    <style>body { font-family: 'Plus Jakarta Sans', sans-serif; }</style>
</head>
<body class="bg-slate-50 text-slate-800 min-h-screen flex items-center justify-center p-4">
    <div class="max-w-md w-full text-center space-y-6 bg-white p-8 rounded-3xl border border-slate-200 shadow-xl">
        <img src="{{ asset('images/logo.png') }}" alt="Logo SMK Islam Bina Khalifah Bangsa" class="w-16 h-16 mx-auto object-contain drop-shadow">
        <div class="w-16 h-16 mx-auto rounded-2xl bg-pink-50 text-[#f43f5e] flex items-center justify-center border border-pink-200">
            <i data-lucide="file-question" class="w-8 h-8"></i>
        </div>
        <div class="space-y-2">
            <span class="text-xs font-bold uppercase tracking-wider text-[#f43f5e]">Kode Status HTTP 404</span>
            <h1 class="text-2xl font-black tracking-tight text-[#0B182F]">Halaman Tidak Ditemukan</h1>
            <p class="text-xs sm:text-sm text-slate-600 leading-relaxed">
                Tautan yang Anda tuju mungkin sudah dipindahkan, dihapus, atau alamat URL yang dimasukkan salah.
            </p>
        </div>
        <div class="pt-2 flex flex-col sm:flex-row gap-3 justify-center">
            <a href="{{ url()->previous() }}" class="px-5 py-2.5 rounded-full border border-slate-300 hover:bg-slate-50 font-bold text-xs text-slate-700 transition-colors flex items-center justify-center gap-2">
                <i data-lucide="arrow-left" class="w-4 h-4"></i> Kembali
            </a>
            <a href="{{ url('/') }}" class="px-5 py-2.5 rounded-full bg-[#f43f5e] hover:bg-[#e11d48] text-white font-bold text-xs transition-colors flex items-center justify-center gap-2 shadow-sm">
                <i data-lucide="home" class="w-4 h-4"></i> Beranda PPDB
            </a>
        </div>
    </div>
    <script>lucide.createIcons();</script>
</body>
</html>
