<?php

namespace App\Http\Controllers\Admin;

use App\Enums\JobApplicationStatus;
use App\Http\Controllers\Controller;
use App\Models\JobApplication;
use App\Models\JobVacancy;
use App\Models\User;
use App\Services\Export\BiodataExcelExportService;
use App\Services\Lifecycle\UserLifecycleService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class AdminLifecycleController extends Controller
{
    public function __construct(
        protected UserLifecycleService $lifecycleService
    ) {}

    /**
     * Tampilkan halaman pengelolaan siklus akun pengguna pasca-seleksi dengan filter interaktif.
     */
    public function index(Request $request): View
    {
        $vacancies = JobVacancy::latest()->get(['id', 'title']);

        // Helper filter closure
        $applyFilters = function ($query) use ($request) {
            if ($request->filled('search')) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->whereHas('user', function ($uq) use ($search) {
                        $uq->where('name', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%")
                            ->orWhere('phone', 'like', "%{$search}%")
                            ->orWhere('registration_number', 'like', "%{$search}%");
                    })->orWhere('application_number', 'like', "%{$search}%");
                });
            }

            if ($request->filled('vacancy_id')) {
                $query->where('job_vacancy_id', $request->vacancy_id);
            }

            if ($request->filled('status')) {
                $query->where('status', $request->status);
            }

            if ($request->filled('account_status')) {
                if ($request->account_status === 'active') {
                    $query->whereHas('user', fn ($uq) => $uq->where('is_active', true)->whereNull('deleted_at'));
                } elseif ($request->account_status === 'inactive') {
                    $query->whereHas('user', fn ($uq) => $uq->where('is_active', false)->whereNull('deleted_at'));
                } elseif ($request->account_status === 'archived') {
                    $query->whereHas('user', fn ($uq) => $uq->onlyTrashed());
                }
            }
        };

        // 1. Pelamar Lolos / Calon Karyawan Baru (Arsip Data Kepegawaian)
        $passedQuery = JobApplication::whereIn('status', [
            JobApplicationStatus::PASSED_ADMINISTRATION,
            JobApplicationStatus::IN_SELECTION,
            JobApplicationStatus::ACCEPTED,
        ])
            ->with(['user' => fn ($q) => $q->withTrashed()->with('applicantProfile'), 'jobVacancy', 'documents']);

        $applyFilters($passedQuery);
        $passedApplications = $passedQuery->latest('accepted_at')
            ->paginate(10, ['*'], 'passed_page')
            ->withQueryString();

        // 2. Pelamar Tidak Lolos (Antrean Pembersihan & Pengarsipan Talent Pool)
        $rejectedQuery = JobApplication::where('status', JobApplicationStatus::REJECTED)
            ->with(['user' => fn ($q) => $q->withTrashed()->with('applicantProfile'), 'jobVacancy', 'documents']);

        $applyFilters($rejectedQuery);
        $rejectedApplications = $rejectedQuery->latest('rejected_at')
            ->paginate(10, ['*'], 'rejected_page')
            ->withQueryString();

        $seenCount = JobApplication::where('status', JobApplicationStatus::REJECTED)
            ->whereNotNull('announcement_seen_at')
            ->whereHas('user', fn ($q) => $q->whereNull('deleted_at'))
            ->count();

        $acceptedCount = JobApplication::where('status', JobApplicationStatus::ACCEPTED)->count();

        return view('admin.lifecycle.index', [
            'passedApplications' => $passedApplications,
            'rejectedApplications' => $rejectedApplications,
            'seenCount' => $seenCount,
            'acceptedCount' => $acceptedCount,
            'vacancies' => $vacancies,
        ]);
    }

    /**
     * Unduh seluruh data biodata pelamar yang diterima dalam format Excel (.xlsx).
     */
    public function exportAcceptedBiodata(Request $request, BiodataExcelExportService $exportService): BinaryFileResponse|RedirectResponse
    {
        $vacancyId = $request->filled('vacancy_id') ? (int) $request->vacancy_id : null;

        $hasAccepted = JobApplication::where('status', JobApplicationStatus::ACCEPTED)
            ->when($vacancyId, fn ($q) => $q->where('job_vacancy_id', $vacancyId))
            ->exists();

        if (! $hasAccepted) {
            return back()->with('error', 'Belum ada pelamar dengan status Diterima untuk diekspor ke Excel.');
        }

        return $exportService->exportAcceptedApplicants($vacancyId);
    }

    /**
     * Nonaktifkan akun pelamar yang diterima (Arsip data tetap tersimpan).
     */
    public function deactivate(User $user): RedirectResponse
    {
        $hasAccepted = $user->jobApplications()
            ->where('status', JobApplicationStatus::ACCEPTED)
            ->exists();

        if (! $hasAccepted) {
            return back()->with('error', 'Hanya akun pelamar dengan status Diterima yang dapat dinonaktifkan.');
        }

        $this->lifecycleService->deactivatePassedApplicant($user);

        return back()->with('success', "Akun pelamar {$user->name} berhasil dinonaktifkan. Seluruh berkas dan riwayat data tetap tersimpan sebagai arsip kepegawaian.");
    }

    /**
     * Aktifkan kembali akun pelamar jika diperlukan.
     */
    public function reactivate(User $user): RedirectResponse
    {
        $this->lifecycleService->reactivateApplicant($user);

        return back()->with('success', "Akun pelamar {$user->name} berhasil diaktifkan kembali.");
    }

    /**
     * Arsipkan akun pelamar yang tidak lolos secara soft delete (berkas fisik CV/Portofolio tetap tersimpan).
     */
    public function cleanup(User $user): RedirectResponse
    {
        $userName = $user->name;
        $this->lifecycleService->cleanupRejectedApplicant($user);

        return back()->with('success', "Akun pelamar {$userName} berhasil diarsipkan (soft delete). Berkas dokumen CV & portofolio tetap tersimpan.");
    }

    /**
     * Arsipkan sekaligus seluruh pelamar tidak lolos yang telah mengonfirmasi melihat pengumuman (soft delete).
     */
    public function cleanupAllSeen(): RedirectResponse
    {
        $count = $this->lifecycleService->cleanupAllSeenRejectedApplicants();

        return back()->with('success', "Pengarsipan massal berhasil: {$count} akun pelamar tidak lolos telah diarsipkan (soft delete). Berkas dokumen tetap dipertahankan.");
    }
}
