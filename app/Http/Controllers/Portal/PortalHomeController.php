<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Services\Portal\ApplicantDashboardService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PortalHomeController extends Controller
{
    /**
     * Menampilkan dashboard utama pelamar yang terotentikasi.
     */
    public function index(Request $request, ApplicantDashboardService $dashboardService): View
    {
        $dashboardData = $dashboardService->getDashboardData($request->user());

        return view('portal.home', $dashboardData);
    }
}
