<?php

namespace App\Http\Controllers\Admin;

use App\Enums\JobApplicationStatus;
use App\Enums\JobVacancyStatus;
use App\Enums\RecruitmentPeriodStatus;
use App\Http\Controllers\Controller;
use App\Models\JobApplication;
use App\Models\JobVacancy;
use App\Models\RecruitmentPeriod;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminDashboardController extends Controller
{
    /**
     * Tampilkan halaman Dashboard Utama Admin HRD/SDI.
     */
    public function index(Request $request): View
    {
        $activePeriod = RecruitmentPeriod::where('status', RecruitmentPeriodStatus::OPEN)->first()
            ?? RecruitmentPeriod::currentlyOpen()->first()
            ?? RecruitmentPeriod::latest('registration_start_at')->first();

        // Scope query ke periode aktif bila tersedia
        $vacancyBaseQuery = JobVacancy::query();
        $applicationBaseQuery = JobApplication::query();

        if ($activePeriod) {
            $vacancyBaseQuery->where('recruitment_period_id', $activePeriod->id);
            $applicationBaseQuery->where('recruitment_period_id', $activePeriod->id);
        }

        // Statistik Lowongan
        $totalVacancies = (clone $vacancyBaseQuery)->count();
        $activeVacancies = (clone $vacancyBaseQuery)->where('status', JobVacancyStatus::OPEN)->count();
        $closedVacancies = (clone $vacancyBaseQuery)->where('status', '!=', JobVacancyStatus::OPEN)->count();

        // Statistik Pelamar
        $totalApplicants = (clone $applicationBaseQuery)->distinct('user_id')->count('user_id');
        $totalApplications = (clone $applicationBaseQuery)->count();

        // Breakdown Status Lamaran
        $pendingVerification = (clone $applicationBaseQuery)->where('status', JobApplicationStatus::SUBMITTED)->count();
        $passedAdministration = (clone $applicationBaseQuery)->where('status', JobApplicationStatus::PASSED_ADMINISTRATION)->count();
        $inSelection = (clone $applicationBaseQuery)->where('status', JobApplicationStatus::IN_SELECTION)->count();
        $acceptedCount = (clone $applicationBaseQuery)->where('status', JobApplicationStatus::ACCEPTED)->count();
        $rejectedCount = (clone $applicationBaseQuery)->where('status', JobApplicationStatus::REJECTED)->count();
        $draftCount = (clone $applicationBaseQuery)->where('status', JobApplicationStatus::DRAFT)->count();

        // Lowongan dengan statistik pelamar
        $vacanciesWithStats = (clone $vacancyBaseQuery)->withCount([
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

        // Filter Formasi jika dipilih
        $selectedVacancyId = $request->query('vacancy_id');
        $selectedVacancy = null;
        if ($selectedVacancyId) {
            $selectedVacancy = JobVacancy::find($selectedVacancyId);
            if (! $selectedVacancy) {
                $selectedVacancyId = null;
            }
        }

        // Lamaran Terbaru yang Masuk (difilter jika formasi dipilih)
        $recentApplicationsQuery = (clone $applicationBaseQuery)->with(['user.applicantProfile', 'jobVacancy'])
            ->latest('submitted_at');

        if ($selectedVacancyId) {
            $recentApplicationsQuery->where('job_vacancy_id', $selectedVacancyId);
        }

        $recentApplications = $recentApplicationsQuery
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
            'selectedVacancyId' => $selectedVacancyId,
            'selectedVacancy' => $selectedVacancy,
        ]);
    }
}
