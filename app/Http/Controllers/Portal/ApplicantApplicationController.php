<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Models\JobApplication;
use App\Services\Lifecycle\UserLifecycleService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ApplicantApplicationController extends Controller
{
    public function __construct(
        protected UserLifecycleService $lifecycleService
    ) {}

    /**
     * Konfirmasi pelamar telah melihat pengumuman penolakan (Acknowledge) & akhiri akun sesuai lifecycle.
     */
    public function acknowledgeAnnouncement(Request $request, JobApplication $application): RedirectResponse
    {
        $user = Auth::user();

        if ($application->user_id !== $user->id) {
            abort(403);
        }

        if ($application->isRejected()) {
            if (is_null($application->announcement_seen_at)) {
                $application->update([
                    'announcement_seen_at' => now(),
                ]);
            }

            // Logout sesi terlebih dahulu
            Auth::guard('web')->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            // Jalankan cleanup data dan berkas fisik pelamar
            $this->lifecycleService->cleanupRejectedApplicant($user);

            return redirect()->route('login')->with('info', 'Terima kasih telah mengonfirmasi hasil pengumuman rekrutmen. Sesuai ketentuan RSI Fatimah Banyuwangi, data akun dan berkas lamaran Anda telah dibersihkan dari sistem kami.');
        }

        return back()->with('info', 'Terima kasih telah mengonfirmasi status hasil pengumuman.');
    }
}
