<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\PengaturanSekolah;

class CheckMaintenanceMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        // Jangan blokir rute administrasi (Admin TU, Kepala Sekolah), otentikasi admin/login, health check, atau admin yang sudah login
        if ($request->is('admin*') || $request->is('kepsek*') || $request->is('login') || $request->is('up') || auth('admin')->check()) {
            return $next($request);
        }

        $pengaturan = PengaturanSekolah::getPengaturan();
        if ($pengaturan->mode_maintenance) {
            return response()->view('maintenance', compact('pengaturan'), 503);
        }

        return $next($request);
    }
}

