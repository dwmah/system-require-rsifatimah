<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Models\RecruitmentPeriod;
use App\Services\Profile\ProfileCompletenessService;
use App\Services\Recruitment\RecruitmentService;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class PortalHomeController extends Controller
{
    /**
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
    }
}
