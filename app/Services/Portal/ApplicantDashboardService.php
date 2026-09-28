<?php

namespace App\Services\Portal;

use App\Enums\ApplicationStageStatus;
use App\Enums\JobApplicationStatus;
use App\Enums\RecruitmentPeriodStatus;
use App\Models\JobApplication;
use App\Models\User;
use App\Services\Profile\ProfileCompletenessService;
use App\Services\Recruitment\ApplicationDocumentService;
use App\Services\Recruitment\SelectionTrackingService;

class ApplicantDashboardService
{
    public function __construct(
        protected ?ProfileCompletenessService $completenessService = null,
        protected ?SelectionTrackingService $trackingService = null,
        protected ?ApplicationDocumentService $documentService = null
    ) {
        $this->completenessService = $completenessService ?? app(ProfileCompletenessService::class);
        $this->trackingService = $trackingService ?? app(SelectionTrackingService::class);
        $this->documentService = $documentService ?? app(ApplicationDocumentService::class);
    }

    /**
     * Memilih lamaran utama pelamar secara deterministik dengan eager loading anti-N+1:
     * 1. Prioritas 1: Lamaran pada periode rekrutmen berstatus OPEN atau SELECTION
     *    (diurutkan berdasarkan created_at DESC, id DESC).
     * 2. Prioritas 2: Lamaran paling terbaru secara umum
     *    (diurutkan berdasarkan created_at DESC, id DESC).
     */
    public function getPrimaryApplication(User $user): ?JobApplication
    {
        $eagerRelations = [
            'recruitmentPeriod',
            'jobVacancy.documentRequirements',
            'jobVacancy.selectionStages',
            'documents',
            'snapshot',
            'stageHistories',
        ];

        // 1. Cari pada periode rekrutmen aktif (OPEN / SELECTION)
        $activePeriodApp = $user->jobApplications()
            ->whereHas('recruitmentPeriod', function ($q) {
                $q->whereIn('status', [
                    RecruitmentPeriodStatus::OPEN,
                    RecruitmentPeriodStatus::SELECTION,
                ]);
            })
            ->with($eagerRelations)
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->first();

        if ($activePeriodApp) {
            return $activePeriodApp;
        }

        // 2. Fallback: Lamaran terbaru apapun status periodenya
        return $user->jobApplications()
            ->with($eagerRelations)
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->first();
    }

    /**
     * Menghasilkan data agregat terpadu untuk halaman dashboard pelamar.
     * Operasi murni read-only tanpa mutasi database (zero side-effect).
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
        $isDocumentsComplete = false;
        $documentProgress = [
            'total' => 0,
            'completed' => 0,
            'missing' => 0,
            'percentage' => 100,
        ];
        $timelineStages = [];
        $quickAction = [
            'label' => 'Lihat Lowongan Dibuka',
            'url' => route('vacancies.index'),
            'type' => 'vacancies',
        ];

        if ($primaryApp) {
            $publicStatus = $this->trackingService->publicApplicationStatus($primaryApp);

            // Perhitungan progres kelengkapan dokumen persyaratan secara dinamis
            $vacancyRequirements = $primaryApp->jobVacancy?->documentRequirements ?? collect();
            $requiredRequirements = $vacancyRequirements->where('is_required', true);
            $targetRequirements = $requiredRequirements->isNotEmpty() ? $requiredRequirements : $vacancyRequirements;

            $totalRequirements = $targetRequirements->count();
            $uploadedReqIds = $primaryApp->documents
                ? $primaryApp->documents->pluck('vacancy_document_requirement_id')->unique()->all()
                : [];

            $completedRequirements = $targetRequirements
                ->filter(fn ($req) => in_array($req->id, $uploadedReqIds, true))
                ->count();

            $missingRequirementsCount = max(0, $totalRequirements - $completedRequirements);
            $documentPercentage = $totalRequirements > 0
                ? (int) round(($completedRequirements / $totalRequirements) * 100)
                : 100;

            $documentProgress = [
                'total' => $totalRequirements,
                'completed' => $completedRequirements,
                'missing' => $missingRequirementsCount,
                'percentage' => $documentPercentage,
            ];

            if ($primaryApp->status === JobApplicationStatus::DRAFT) {
                // Reuse existing domain service validation using is_required
                $isDocumentsComplete = $this->documentService->hasAllRequiredDocuments($primaryApp);

                if (! $isComplete) {
                    $quickAction = [
                        'label' => 'Lengkapi Profil Anda',
                        'url' => route('profile.show'),
                        'type' => 'profile_incomplete',
                    ];
                } else {
                    $quickAction = [
                        'label' => 'Lanjutkan Lamaran',
                        'url' => $isDocumentsComplete
                            ? route('portal.applications.review', $primaryApp)
                            : route('portal.applications.documents.index', $primaryApp),
                        'type' => $isDocumentsComplete ? 'draft_complete' : 'draft_incomplete',
                    ];
                }

                // Pure read-only structural stages using already eager-loaded collection (zero extra queries)
                $stagesCollection = $primaryApp->jobVacancy?->selectionStages
                    ? $primaryApp->jobVacancy->selectionStages->sortBy('sort_order')->values()
                    : collect();

                $timelineStages = $stagesCollection->map(function ($stage, $index) {
                    return [
                        'id' => (int) $stage->id,
                        'name' => (string) ($stage->name ?? 'Tahapan Seleksi'),
                        'description' => $stage->description,
                        'sort_order' => (int) ($stage->sort_order ?? ($index + 1)),
                        'public_state' => 'waiting',
                        'label' => 'Belum Dimulai',
                        'badge_class' => 'bg-slate-100 text-slate-600 border-slate-200',
                        'applicant_message' => null,
                        'published_at' => null,
                        'start_at' => $stage->start_at,
                        'end_at' => $stage->end_at,
                        'is_current' => false,
                    ];
                })->all();
            } else {
                // Submitted+ timeline strictly from SelectionTrackingService (ApplicationSnapshot source-of-truth)
                $trackingData = $this->trackingService->tracking($primaryApp);
                $timelineStages = $trackingData['stages'] ?? [];
                $currentStage = $this->trackingService->currentStage($primaryApp);
                $finalResult = $this->trackingService->finalResult($primaryApp);

                // Fallback timeline stages from live jobVacancy if snapshot is missing / empty
                if (empty($timelineStages) && $primaryApp->jobVacancy?->selectionStages) {
                    $stagesCollection = $primaryApp->jobVacancy->selectionStages->sortBy('sort_order')->values();
                    $histories = $primaryApp->stageHistories ? $primaryApp->stageHistories->keyBy('vacancy_selection_stage_id') : collect();

                    $timelineStages = $stagesCollection->map(function ($stage, $index) use ($histories, $primaryApp) {
                        $history = $histories->get($stage->id);
                        $isPublished = $history && $history->published_at && ($history->published_at->isPast() || $history->published_at->isCurrentSecond());

                        $publicState = 'waiting';
                        $publicLabel = 'Belum Dimulai';
                        $badgeClass = 'bg-slate-100 text-slate-600 border-slate-200';
                        $applicantMessage = null;
                        $publishedAt = null;

                        if ($isPublished && $history->status) {
                            $stVal = $history->status instanceof ApplicationStageStatus ? $history->status->value : (string) $history->status;
                            match ($stVal) {
                                'passed' => [
                                    $publicState = 'passed',
                                    $publicLabel = 'Lulus',
                                    $badgeClass = 'bg-emerald-50 text-emerald-700 border-emerald-200',
                                ],
                                'failed', 'rejected' => [
                                    $publicState = 'failed',
                                    $publicLabel = 'Tidak Lolos',
                                    $badgeClass = 'bg-red-50 text-red-700 border-red-200',
                                ],
                                'in_progress' => [
                                    $publicState = 'in_progress',
                                    $publicLabel = 'Sedang Berlangsung',
                                    $badgeClass = 'bg-blue-50 text-blue-700 border-blue-200',
                                ],
                                default => null,
                            };
                            $applicantMessage = $history->applicant_message;
                            $publishedAt = $history->published_at;
                        }

                        return [
                            'id' => (int) $stage->id,
                            'name' => (string) ($stage->name ?? $stage->stage_name ?? 'Tahapan Seleksi'),
                            'description' => $stage->description,
                            'sort_order' => (int) ($stage->sort_order ?? ($index + 1)),
                            'public_state' => $publicState,
                            'label' => $publicLabel,
                            'badge_class' => $badgeClass,
                            'applicant_message' => $applicantMessage,
                            'published_at' => $publishedAt,
                            'start_at' => $stage->start_at,
                            'end_at' => $stage->end_at,
                            'is_current' => ($primaryApp->current_stage_id === $stage->id),
                        ];
                    })->all();
                }

                if ($finalResult !== null) {
                    $quickAction = [
                        'label' => 'Lihat Hasil Seleksi',
                        'url' => route('portal.applications.tracking', $primaryApp),
                        'type' => 'final_result',
                    ];
                } elseif ($primaryApp->status === JobApplicationStatus::SUBMITTED) {
                    $quickAction = [
                        'label' => 'Lihat Detail Lamaran',
                        'url' => route('portal.applications.show', $primaryApp),
                        'type' => 'submitted',
                    ];
                } else {
                    $quickAction = [
                        'label' => 'Lihat Progres Seleksi',
                        'url' => route('portal.applications.tracking', $primaryApp),
                        'type' => 'tracking',
                    ];
                }
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
            'isDocumentsComplete' => $isDocumentsComplete,
            'documentProgress' => $documentProgress,
            'timelineStages' => $timelineStages,
        ];
    }
}
