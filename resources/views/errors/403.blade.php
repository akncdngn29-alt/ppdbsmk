<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>403 - Akses Ditolak | PPDB SMK Islam Bina Khalifah Bangsa</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/lucide@latest"></script>
    <style>body { font-family: 'Plus Jakarta Sans', sans-serif; }</style>
</head>
<body class="bg-slate-50 dark:bg-slate-950 text-slate-800 dark:text-slate-100 min-h-screen flex items-center justify-center p-4">
    <div class="max-w-md w-full text-center space-y-6">
        <div class="w-20 h-20 mx-auto rounded-3xl bg-amber-100 dark:bg-amber-950/60 text-amber-600 flex items-center justify-center shadow-lg">
            <i data-lucide="shield-alert" class="w-10 h-10"></i>
        </div>
        <div class="space-y-2">
            <span class="text-xs font-bold uppercase tracking-wider text-amber-600 dark:text-amber-400">Kode Status HTTP 403</span>
            <h1 class="text-2xl sm:text-3xl font-black tracking-tight text-slate-900 dark:text-white">Akses Ditolak</h1>
            <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400 leading-relaxed">
                {{ $exception->getMessage() ?: 'Anda tidak memiliki hak akses atau kewenangan yang diperlukan untuk membuka halaman ini.' }}
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

