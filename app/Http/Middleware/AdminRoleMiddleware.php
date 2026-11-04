<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminRoleMiddleware
{
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        if (!auth('admin')->check()) {
            return redirect()->route('admin.login')->with('error', 'Silakan login sebagai administrator terlebih dahulu.');
        }

        $admin = auth('admin')->user();
        if (!empty($roles) && !in_array($admin->role, $roles)) {
            abort(403, 'Akses ditolak. Anda tidak memiliki izin untuk halaman ini.');
        }

        return $next($request);
    }
}

