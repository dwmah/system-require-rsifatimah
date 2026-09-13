<?php

namespace App\Http\Controllers\Admin;

use App\Enums\JobApplicationStatus;
use App\Http\Controllers\Controller;
use App\Models\JobApplication;
use App\Models\User;
use App\Services\Lifecycle\UserLifecycleService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminLifecycleController extends Controller
{
    public function __construct(
        protected UserLifecycleService $lifecycleService
    ) {
    }

    /**
     * Tampilkan halaman pengelolaan siklus akun pengguna pasca-seleksi.
     */
    public function index(): View
    {
        // 1. Pelamar Lolos / Calon Karyawan Baru (Arsip Data Kepegawaian)
        $passedApplications = JobApplication::whereIn('status', [
            JobApplicationStatus::PASSED_ADMINISTRATION,
            JobApplicationStatus::IN_SELECTION,
            JobApplicationStatus::ACCEPTED,
        ])
        ->with(['user.applicantProfile', 'jobVacancy', 'documents'])
        ->latest('accepted_at')
        ->paginate(10, ['*'], 'passed_page');

        // 2. Pelamar Tidak Lolos (Antrean Pembersihan Data)
        $rejectedApplications = JobApplication::where('status', JobApplicationStatus::REJECTED)
            ->with(['user.applicantProfile', 'jobVacancy', 'documents'])
            ->latest('rejected_at')
            ->paginate(10, ['*'], 'rejected_page');

        $seenCount = JobApplication::where('status', JobApplicationStatus::REJECTED)
            ->whereNotNull('announcement_seen_at')
            ->count();

        return view('admin.lifecycle.index', [
            'passedApplications' => $passedApplications,
            'rejectedApplications' => $rejectedApplications,
            'seenCount' => $seenCount,
        ]);
    }

    /**
     * Nonaktifkan akun pelamar yang lolos (Arsip data tetap tersimpan).
     */
    public function deactivate(User $user): RedirectResponse
    {
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
     * Hapus data dan berkas fisik pelamar yang tidak lolos secara permanen.
     */
    public function cleanup(User $user): RedirectResponse
    {
        $userName = $user->name;
        $this->lifecycleService->cleanupRejectedApplicant($user);

        return back()->with('success', "Data dan berkas pelamar {$userName} berhasil dibersihkan dari server.");
    }

    /**
     * Bersihkan sekaligus seluruh pelamar tidak lolos yang telah mengonfirmasi melihat pengumuman.
     */
    public function cleanupAllSeen(): RedirectResponse
    {
        $count = $this->lifecycleService->cleanupAllSeenRejectedApplicants();

        return back()->with('success', "Pembersihan massal berhasil: {$count} data & berkas pelamar tidak lolos telah dibersihkan.");
    }
}
