<?php

namespace App\Http\Middleware;

use App\Services\Recruitment\RecruitmentService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsurePublicRecruitmentIsOpen
{
    public function __construct(
        protected RecruitmentService $recruitmentService
    ) {}

    /**
     * Handle an incoming request.
     *
     * Middleware ini hanya melindungi halaman portal publik (lowongan & panduan).
     * Jika tidak ada rekrutmen/lowongan yang aktif dibuka, dialihkan ke halaman info penutupan.
     * Tidak membatasi akses login/dashboard pelamar terdaftar di fase selanjutnya.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (! $this->recruitmentService->hasActiveRecruitment()) {
            return redirect()->route('recruitment.closed');
        }

        return $next($request);
    }
}
