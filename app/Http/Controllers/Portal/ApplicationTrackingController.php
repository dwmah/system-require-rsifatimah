<?php

namespace App\Http\Controllers\Portal;

use App\Enums\JobApplicationStatus;
use App\Http\Controllers\Controller;
use App\Models\ApplicationStageHistory;
use App\Models\JobApplication;
use App\Services\Recruitment\SelectionTrackingService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;

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

    /**
     * Download lampiran hasil tes pada tahapan seleksi tertentu oleh pelamar.
     */
    public function downloadStageAttachment(
        JobApplication $jobApplication,
        ApplicationStageHistory $history,
        SelectionTrackingService $trackingService
    ): StreamedResponse|BinaryFileResponse {
        Gate::authorize('view', $jobApplication);

        if ((int) $history->job_application_id !== (int) $jobApplication->id) {
            abort(404, 'Data riwayat tahapan tidak sesuai dengan lamaran.');
        }

        if (! $trackingService->isStagePublished($history)) {
            abort(403, 'Lampiran tahapan belum dipublikasikan.');
        }

        if (! $history->attachment_path || ! Storage::disk('local')->exists($history->attachment_path)) {
            abort(404, 'Berkas lampiran fisik tidak ditemukan di server.');
        }

        $filename = $history->attachment_original_name ?: basename($history->attachment_path);
        $mime = $history->attachment_mime ?: 'application/octet-stream';

        if (request()->boolean('preview')) {
            return Storage::disk('local')->response($history->attachment_path, $filename, [
                'Content-Type' => $mime,
                'Content-Disposition' => 'inline; filename="'.$filename.'"',
            ]);
        }

        return Storage::disk('local')->download($history->attachment_path, $filename, [
            'Content-Type' => $mime,
        ]);
    }
}
