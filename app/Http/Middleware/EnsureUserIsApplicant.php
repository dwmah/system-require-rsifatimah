<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsApplicant
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user) {
            return redirect()->route('login');
        }

        if ($user->isAdmin()) {
            return redirect()->route('admin.dashboard');
        }

        if (! $user->is_active) {
            auth()->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('login')->withErrors([
                'login' => 'Akun Anda telah dinonaktifkan (Arsip Pelamar Lolos / Selesai Seleksi). Silakan hubungi Tim SDI RSI Fatimah Banyuwangi untuk informasi lebih lanjut.',
                'email' => 'Akun Anda telah dinonaktifkan (Arsip Pelamar Lolos / Selesai Seleksi). Silakan hubungi Tim SDI RSI Fatimah Banyuwangi untuk informasi lebih lanjut.',
            ]);
        }

        return $next($request);
    }
}
