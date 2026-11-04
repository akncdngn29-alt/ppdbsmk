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
        // Don't block admin routes or login routes
        if ($request->is('admin*') || $request->is('login') || $request->is('up')) {
            return $next($request);
        }

        $pengaturan = PengaturanSekolah::getPengaturan();
        if ($pengaturan->mode_maintenance) {
            return response()->view('maintenance', compact('pengaturan'), 503);
        }

        return $next($request);
    }
}

