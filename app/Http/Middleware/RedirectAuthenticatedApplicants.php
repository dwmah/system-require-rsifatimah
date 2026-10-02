<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RedirectAuthenticatedApplicants
{
    /**
     * Handle an incoming request.
     *
     * Pelamar yang sudah login/masuk tidak diperkenankan mengakses halaman katalog
     * lowongan publik atau halaman pengumuman penutupan, dan otomatis dialihkan ke Portal Pelamar.
     * Admin dialihkan ke Dashboard Admin saat mengakses halaman awal ('/') atau pengumuman penutupan.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user) {
            if ($user->isApplicant()) {
                return redirect()->route('portal.home');
            }

            if ($user->isAdmin() && ($request->is('/') || $request->is('rekrutmen-ditutup'))) {
                return redirect()->route('admin.dashboard');
            }
        }

        return $next($request);
    }
}
