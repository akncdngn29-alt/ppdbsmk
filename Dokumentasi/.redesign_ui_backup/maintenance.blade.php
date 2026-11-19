<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistem Dalam Pemeliharaan - PPDB SMK IB Khalifah Bangsa</title>
    <link rel="icon" type="image/png" href="{{ asset('images/logo.png') }}">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/lucide@latest"></script>
    <style>body { font-family: 'Plus Jakarta Sans', sans-serif; }</style>
</head>
<body class="bg-[#0B182F] text-slate-100 min-h-screen flex items-center justify-center p-4 font-sans">
    <div class="max-w-md w-full text-center space-y-6 p-8 rounded-3xl bg-[#060E1D] border border-slate-800 shadow-2xl">
        <img src="{{ asset('images/logo.png') }}" alt="Logo SMK Islam Bina Khalifah Bangsa" class="w-16 h-16 mx-auto object-contain drop-shadow">
        <div class="w-16 h-16 rounded-2xl bg-amber-500/20 text-amber-400 flex items-center justify-center mx-auto border border-amber-500/30">
            <i data-lucide="wrench" class="w-8 h-8"></i>
        </div>
        <div class="space-y-2">
            <h1 class="text-2xl font-black text-white">Sistem Sedang Ditingkatkan</h1>
            <p class="text-xs text-slate-300 leading-relaxed">
                Website PPDB SMK Islam Bina Khalifah Bangsa sedang menjalani pemeliharaan terjadwal demi meningkatkan kehandalan dan keamanan data pendaftaran.
            </p>
        </div>
        <div class="p-4 rounded-2xl bg-white/5 border border-white/10 text-xs text-slate-300">
            Silakan kembali beberapa saat lagi atau hubungi panitia PPDB melalui WhatsApp: <strong class="text-[#f43f5e]">0821-7890-1234</strong>.
        </div>
        <div>
            <a href="{{ route('admin.login') }}" class="text-xs font-bold text-pink-400 hover:underline flex items-center justify-center gap-1">
                <span>Akses Khusus Staf Administrator &rarr;</span>
            </a>
        </div>
    </div>
    <script>lucide.createIcons();</script>
</body>
</html>
