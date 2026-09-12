<?php

namespace App\Services\Portal;

use App\Enums\JobApplicationStatus;
use App\Enums\RecruitmentPeriodStatus;
use App\Models\JobApplication;
use App\Models\User;
use App\Services\Profile\ProfileCompletenessService;
use App\Services\Recruitment\SelectionTrackingService;

class ApplicantDashboardService
{
    public function __construct(
        protected ?ProfileCompletenessService $completenessService = null,
        protected ?SelectionTrackingService $trackingService = null
    ) {
        $this->completenessService = $completenessService ?? app(ProfileCompletenessService::class);
        $this->trackingService = $trackingService ?? app(SelectionTrackingService::class);
    }

    /**
     * Memilih lamaran utama pelamar secara deterministik:
     * 1. Prioritas 1: Lamaran pada periode rekrutmen berstatus OPEN atau SELECTION
     *    (diurutkan berdasarkan created_at DESC, id DESC).
     * 2. Prioritas 2: Lamaran paling terbaru secara umum
     *    (diurutkan berdasarkan created_at DESC, id DESC).
     */
    public function getPrimaryApplication(User $user): ?JobApplication
    {
        // 1. Cari pada periode rekrutmen aktif (OPEN / SELECTION)
        $activePeriodApp = $user->jobApplications()
            ->whereHas('recruitmentPeriod', function ($q) {
                $q->whereIn('status', [
                    RecruitmentPeriodStatus::OPEN,
                    RecruitmentPeriodStatus::SELECTION,
                ]);
            })
            ->with(['recruitmentPeriod', 'jobVacancy', 'snapshot', 'stageHistories'])
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->first();

        if ($activePeriodApp) {
            return $activePeriodApp;
        }

        // 2. Fallback: Lamaran terbaru apapun status periodenya
        return $user->jobApplications()
            ->with(['recruitmentPeriod', 'jobVacancy', 'snapshot', 'stageHistories'])
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->first();
    }

    /**
     * Menghasilkan data agregat terpadu untuk halaman dashboard pelamar.
     *
     * @return array<string, mixed>
     */
    public function getDashboardData(User $user): array
    {
        $percentage = $this->completenessService->percentage($user);
        $isComplete = $this->completenessService->isComplete($user);
        $missingItems = $this->completenessService->missingItems($user);

        $primaryApp = $this->getPrimaryApplication($user);

        $publicStatus = null;
        $currentStage = null;
        $finalResult = null;
        $quickAction = [
            'label' => 'Lihat Lowongan Dibuka',
            'url' => route('vacancies.index'),
            'type' => 'vacancies',
        ];

        if ($primaryApp) {
            $publicStatus = $this->trackingService->publicApplicationStatus($primaryApp);

            if ($primaryApp->status === JobApplicationStatus::DRAFT) {
                $quickAction = [
                    'label' => 'Lanjutkan Lamaran',
                    'url' => route('portal.applications.show', $primaryApp),
                    'type' => 'draft',
                ];
            } else {
                $currentStage = $this->trackingService->currentStage($primaryApp);
                $finalResult = $this->trackingService->finalResult($primaryApp);
                $quickAction = [
                    'label' => 'Lihat Status Seleksi',
                    'url' => route('portal.applications.tracking', $primaryApp),
                    'type' => 'tracking',
                ];
            }
        }

        return [
            'user' => $user,
            'percentage' => $percentage,
            'isComplete' => $isComplete,
            'missingItems' => $missingItems,
            'primaryApplication' => $primaryApp,
            'publicStatus' => $publicStatus,
            'currentStage' => $currentStage,
            'finalResult' => $finalResult,
            'quickAction' => $quickAction,
        ];
    }
}
