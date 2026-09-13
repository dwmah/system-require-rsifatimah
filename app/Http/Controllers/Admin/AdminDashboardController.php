<?php

namespace App\Http\Controllers\Admin;

use App\Enums\JobApplicationStatus;
use App\Http\Controllers\Controller;
use App\Models\JobApplication;
use App\Models\JobVacancy;
use App\Models\RecruitmentPeriod;
use App\Models\User;
use Illuminate\View\View;

class AdminDashboardController extends Controller
{
    /**
     * Tampilkan halaman Dashboard Utama Admin HRD/SDI.
     */
    public function index(): View
    {
        $activePeriod = RecruitmentPeriod::currentlyOpen()->first()
            ?? RecruitmentPeriod::where('status', \App\Enums\RecruitmentPeriodStatus::OPEN)->first()
            ?? RecruitmentPeriod::latest('registration_start_at')->first();

        // Statistik Lowongan
        $totalVacancies = JobVacancy::count();
        $activeVacancies = JobVacancy::where('status', \App\Enums\JobVacancyStatus::OPEN)->count();
        $closedVacancies = JobVacancy::where('status', '!=', \App\Enums\JobVacancyStatus::OPEN)->count();

        // Statistik Pelamar
        $totalApplicants = User::where('role', User::ROLE_APPLICANT)->count();
        $totalApplications = JobApplication::count();

        // Breakdown Status Lamaran
        $pendingVerification = JobApplication::where('status', JobApplicationStatus::SUBMITTED)->count();
        $passedAdministration = JobApplication::where('status', JobApplicationStatus::PASSED_ADMINISTRATION)->count();
        $inSelection = JobApplication::where('status', JobApplicationStatus::IN_SELECTION)->count();
        $acceptedCount = JobApplication::where('status', JobApplicationStatus::ACCEPTED)->count();
        $rejectedCount = JobApplication::where('status', JobApplicationStatus::REJECTED)->count();
        $draftCount = JobApplication::where('status', JobApplicationStatus::DRAFT)->count();

        // Lowongan dengan statistik pelamar
        $vacanciesWithStats = JobVacancy::withCount([
            'jobApplications as total_applicants',
            'jobApplications as pending_count' => function ($query) {
                $query->where('status', JobApplicationStatus::SUBMITTED);
            },
            'jobApplications as passed_count' => function ($query) {
                $query->whereIn('status', [
                    JobApplicationStatus::PASSED_ADMINISTRATION,
                    JobApplicationStatus::IN_SELECTION,
                    JobApplicationStatus::ACCEPTED,
                ]);
            },
        ])->latest()->take(6)->get();

        // Lamaran Terbaru yang Masuk
        $recentApplications = JobApplication::with(['user.applicantProfile', 'jobVacancy'])
            ->latest('submitted_at')
            ->take(8)
            ->get();

        return view('admin.dashboard', [
            'activePeriod' => $activePeriod,
            'totalVacancies' => $totalVacancies,
            'activeVacancies' => $activeVacancies,
            'closedVacancies' => $closedVacancies,
            'totalApplicants' => $totalApplicants,
            'totalApplications' => $totalApplications,
            'pendingVerification' => $pendingVerification,
            'passedAdministration' => $passedAdministration,
            'inSelection' => $inSelection,
            'acceptedCount' => $acceptedCount,
            'rejectedCount' => $rejectedCount,
            'draftCount' => $draftCount,
            'vacanciesWithStats' => $vacanciesWithStats,
            'recentApplications' => $recentApplications,
        ]);
    }
}
