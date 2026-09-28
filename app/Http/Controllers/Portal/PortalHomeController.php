<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Services\Portal\ApplicantDashboardService;
use App\Services\Recruitment\RecruitmentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class PortalHomeController extends Controller
{
    /**
     * Menampilkan dashboard utama pelamar yang terotentikasi.
     */
    public function index(
        Request $request,
        ApplicantDashboardService $dashboardService,
        RecruitmentService $recruitmentService
    ): View {
        $user = $request->user() ?? Auth::user();
        $dashboardData = $dashboardService->getDashboardData($user);
        $activePeriod = $recruitmentService->getActivePeriod();
        $unreadNotifications = $user->recruitmentNotifications()->whereNull('read_at')->take(3)->get();

        return view('portal.home', array_merge($dashboardData, [
            'application' => $dashboardData['primaryApplication'],
            'activePeriod' => $activePeriod,
            'unreadNotifications' => $unreadNotifications,
        ]));
    }
}
