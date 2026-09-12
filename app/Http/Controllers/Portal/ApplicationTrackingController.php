<?php

namespace App\Http\Controllers\Portal;

use App\Enums\JobApplicationStatus;
use App\Http\Controllers\Controller;
use App\Models\JobApplication;
use App\Services\Recruitment\SelectionTrackingService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class ApplicationTrackingController extends Controller
{
    /**
     * Menampilkan halaman tracking proses seleksi untuk lamaran pekerjaan tertentu.
     * Bersifat READ-ONLY bagi pelamar dan dilindungi publication gate.
     */
    public function show(
        JobApplication $jobApplication,
        SelectionTrackingService $trackingService
    ): View|RedirectResponse {
        // 1. Otorisasi kepemilikan lamaran (Cross-user -> 403 Forbidden)
        Gate::authorize('view', $jobApplication);

        // 2. Proteksi Status Draft: Alihkan ke detail jika belum dikirim resmi
        if ($jobApplication->status === JobApplicationStatus::DRAFT) {
            return redirect()->route('portal.applications.show', $jobApplication)
                ->with('info', 'Kirim lamaran terlebih dahulu untuk melihat proses seleksi.');
        }

        // 3. Eager load relasi historis yang diperlukan
        $jobApplication->loadMissing([
            'recruitmentPeriod',
            'snapshot',
            'stageHistories',
        ]);

        // 4. Kalkulasi data publik yang aman dan time-aware
        $trackingData = $trackingService->tracking($jobApplication);
        $publicStatus = $trackingService->publicApplicationStatus($jobApplication);
        $finalResult = $trackingService->finalResult($jobApplication);

        return view('portal.applications.tracking', [
            'jobApplication' => $jobApplication,
            'trackingData' => $trackingData,
            'publicStatus' => $publicStatus,
            'finalResult' => $finalResult,
        ]);
    }
}
