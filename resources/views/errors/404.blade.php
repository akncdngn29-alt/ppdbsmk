<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>404 - Halaman Tidak Ditemukan | PPDB SMK Islam Bina Khalifah Bangsa</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/lucide@latest"></script>
    <style>body { font-family: 'Plus Jakarta Sans', sans-serif; }</style>
</head>
<body class="bg-slate-50 dark:bg-slate-950 text-slate-800 dark:text-slate-100 min-h-screen flex items-center justify-center p-4">
    <div class="max-w-md w-full text-center space-y-6">
        <div class="w-20 h-20 mx-auto rounded-3xl bg-emerald-100 dark:bg-emerald-950/60 text-emerald-600 flex items-center justify-center shadow-lg">
            <i data-lucide="file-question" class="w-10 h-10"></i>
        </div>
        <div class="space-y-2">
            <span class="text-xs font-bold uppercase tracking-wider text-emerald-600 dark:text-emerald-400">Kode Status HTTP 404</span>
            <h1 class="text-2xl sm:text-3xl font-black tracking-tight text-slate-900 dark:text-white">Halaman Tidak Ditemukan</h1>
            <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400 leading-relaxed">
                Tautan yang Anda tuju mungkin sudah dipindahkan, dihapus, atau alamat URL yang dimasukkan salah.
            </p>
        </div>
        <div class="pt-2 flex flex-col sm:flex-row gap-3 justify-center">
            <a href="{{ url()->previous() }}" class="px-5 py-2.5 rounded-xl border border-slate-200 dark:border-slate-800 hover:bg-slate-100 dark:hover:bg-slate-900 font-bold text-xs transition-colors flex items-center justify-center gap-2">
                <i data-lucide="arrow-left" class="w-4 h-4"></i> Kembali
            </a>
            <a href="{{ url('/') }}" class="px-5 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs transition-colors flex items-center justify-center gap-2 shadow-sm">
                <i data-lucide="home" class="w-4 h-4"></i> Beranda PPDB
            </a>
        </div>
    </div>
    <script>lucide.createIcons();</script>
</body>
</html>

