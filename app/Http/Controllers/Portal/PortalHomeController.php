<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
<<<<<<< HEAD
use App\Models\RecruitmentPeriod;
use App\Services\Profile\ProfileCompletenessService;
use App\Services\Recruitment\RecruitmentService;
use Illuminate\Support\Facades\Auth;
=======
use App\Services\Portal\ApplicantDashboardService;
use Illuminate\Http\Request;
>>>>>>> 00c3161e062b5b07902ac4a8b7a5537efbf9b88d
use Illuminate\View\View;

class PortalHomeController extends Controller
{
    /**
<<<<<<< HEAD
     * Display the authenticated applicant portal dashboard home.
     */
    public function index(
        ProfileCompletenessService $completenessService,
        RecruitmentService $recruitmentService
    ): View {
        $user = Auth::user();
        $activePeriod = $recruitmentService->getActivePeriod();

        $application = $user->jobApplications()
            ->with([
                'jobVacancy.documentRequirements',
                'jobVacancy.selectionStages',
                'documents',
                'stageHistories.selectionStage',
                'recruitmentPeriod',
                'currentStage',
            ])
            ->latest()
            ->first();

        $unreadNotifications = $user->recruitmentNotifications()->whereNull('read_at')->take(3)->get();

        return view('portal.home', [
            'user' => $user,
            'application' => $application,
            'activePeriod' => $activePeriod,
            'percentage' => $completenessService->percentage($user),
            'isComplete' => $completenessService->isComplete($user),
            'missingItems' => $completenessService->missingItems($user),
            'unreadNotifications' => $unreadNotifications,
        ]);
=======
     * Menampilkan dashboard utama pelamar yang terotentikasi.
     */
    public function index(Request $request, ApplicantDashboardService $dashboardService): View
    {
        $dashboardData = $dashboardService->getDashboardData($request->user());

        return view('portal.home', $dashboardData);
>>>>>>> 00c3161e062b5b07902ac4a8b7a5537efbf9b88d
    }
}
