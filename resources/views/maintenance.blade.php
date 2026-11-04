<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistem Dalam Pemeliharaan - PPDB SMK IB Khalifah Bangsa</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/lucide@latest"></script>
</head>
<body class="bg-slate-900 text-slate-100 min-h-screen flex items-center justify-center p-4 font-sans">
    <div class="max-w-md w-full text-center space-y-6 p-8 rounded-3xl bg-slate-800 border border-slate-700 shadow-2xl">
        <div class="w-16 h-16 rounded-2xl bg-amber-500/20 text-amber-400 flex items-center justify-center mx-auto border border-amber-500/30">
            <i data-lucide="wrench" class="w-8 h-8 animate-bounce"></i>
        </div>
        <div class="space-y-2">
            <h1 class="text-2xl font-extrabold text-white">Sistem Sedang Ditingkatkan</h1>
            <p class="text-xs text-slate-300 leading-relaxed">
                Website PPDB SMK Islam Bina Khalifah Bangsa sedang menjalani pemeliharaan terjadwal demi meningkatkan kehandalan dan keamanan data pendaftaran.
            </p>
        </div>
        <div class="p-4 rounded-2xl bg-black/30 border border-white/5 text-xs text-slate-400">
            Silakan kembali beberapa saat lagi atau hubungi panitia PPDB melalui WhatsApp: <strong class="text-white">0821-7890-1234</strong>.
        </div>
        <div>
            <a href="{{ route('admin.login') }}" class="text-xs text-emerald-400 hover:underline">
                Akses Khusus Staf Administrator &rarr;
            </a>
        </div>
    </div>
    <script>lucide.createIcons();</script>
</body>
</html>

