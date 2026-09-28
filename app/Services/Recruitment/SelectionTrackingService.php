<?php

namespace App\Services\Recruitment;

use App\Enums\ApplicationStageStatus;
use App\Enums\JobApplicationStatus;
use App\Models\ApplicationStageHistory;
use App\Models\JobApplication;
use Carbon\Carbon;

class SelectionTrackingService
{
    /**
     * Memeriksa apakah sebuah riwayat tahapan seleksi telah resmi dipublikasikan
     * berdasarkan aturan time-aware (published_at != null dan published_at <= now()).
     */
    public function isStagePublished(?ApplicationStageHistory $history): bool
    {
        if ($history === null || $history->published_at === null) {
            return false;
        }

        return $history->published_at->isPast() || $history->published_at->isCurrentSecond();
    }

    /**
     * Memeriksa apakah pengumuman hasil akhir lamaran telah resmi dipublikasikan
     * berdasarkan aturan time-aware (final_result_published_at != null dan final_result_published_at <= now()).
     */
    public function isFinalResultPublished(JobApplication $application): bool
    {
        if ($application->final_result_published_at === null) {
            return false;
        }

        return $application->final_result_published_at->isPast() || $application->final_result_published_at->isCurrentSecond();
    }

    /**
     * Menghasilkan status lamaran publik yang aman (anti-leak) bagi pelamar.
     * Tidak membocorkan status ACCEPTED/REJECTED sebelum final_result_published_at <= now().
     *
     * @return array{key: string, label: string, badge_class: string}
     */
    public function publicApplicationStatus(JobApplication $application): array
    {
        return match ($application->status) {
            JobApplicationStatus::DRAFT => [
                'key' => 'draft',
                'label' => 'Draft Lamaran',
                'badge_class' => 'bg-amber-50 text-amber-700 border-amber-200',
            ],
            JobApplicationStatus::SUBMITTED => [
                'key' => 'submitted',
                'label' => 'Lamaran Terkirim',
                'badge_class' => 'bg-blue-50 text-blue-700 border-blue-200',
            ],
            JobApplicationStatus::IN_PROCESS, JobApplicationStatus::IN_SELECTION, JobApplicationStatus::PASSED_ADMINISTRATION => [
                'key' => 'in_process',
                'label' => 'Proses Seleksi',
                'badge_class' => 'bg-indigo-50 text-indigo-700 border-indigo-200',
            ],
            JobApplicationStatus::ACCEPTED => $this->isFinalResultPublished($application)
                ? [
                    'key' => 'accepted',
                    'label' => 'Diterima',
                    'badge_class' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                ]
                : [
                    'key' => 'waiting_final_result',
                    'label' => 'Menunggu Pengumuman Akhir',
                    'badge_class' => 'bg-sky-50 text-sky-700 border-sky-200',
                ],
            JobApplicationStatus::REJECTED => $this->isFinalResultPublished($application)
                ? [
                    'key' => 'rejected',
                    'label' => 'Tidak Lolos',
                    'badge_class' => 'bg-rose-50 text-rose-700 border-rose-200',
                ]
                : [
                    'key' => 'waiting_final_result',
                    'label' => 'Menunggu Pengumuman Akhir',
                    'badge_class' => 'bg-sky-50 text-sky-700 border-sky-200',
                ],
        };
    }

    /**
     * Menghasilkan data pengumuman hasil akhir resmi jika sudah dipublikasikan.
     *
     * @return array{status: string, title: string, published_at: \Illuminate\Support\Carbon, message: ?string}|null
     */
    public function finalResult(JobApplication $application): ?array
    {
        // Hanya untuk status ACCEPTED atau REJECTED
        if (! in_array($application->status, [JobApplicationStatus::ACCEPTED, JobApplicationStatus::REJECTED], true)) {
            return null;
        }

        // Hanya jika final_result_published_at <= now()
        if (! $this->isFinalResultPublished($application)) {
            return null;
        }

        if ($application->status === JobApplicationStatus::ACCEPTED) {
            return [
                'status' => 'accepted',
                'title' => 'Selamat, Anda dinyatakan diterima.',
                'published_at' => $application->final_result_published_at ?? now(),
                'message' => $application->final_result_message,
            ];
        }

        return [
            'status' => 'rejected',
            'title' => 'Terima kasih telah mengikuti proses rekrutmen. Anda belum berhasil pada proses rekrutmen ini.',
            'published_at' => $application->final_result_published_at ?? $application->rejected_at ?? $application->verified_at ?? now(),
            'message' => $application->rejection_reason ?? $application->final_result_message ?? 'Terima kasih telah mengikuti proses rekrutmen. Anda belum berhasil pada tahapan seleksi ini.',
        ];
    }

    /**
     * Membangun daftar timeline tahapan seleksi publik dari ApplicationSnapshot secara aman.
     *
     * Aturan Ketat Whitelist Output:
     * - Hanya field publik yang disertakan.
     * - Field 'note' (catatan internal panitia) dan 'decided_at' internal TIDAK PERNAH dikirim.
     * - Jika unpublished, status publik tetap 'Belum Diumumkan'.
     * - Jika ada tahapan sebelumnya yang published FAILED, tahapan berikutnya tanpa status published
     *   dipresentasikan sebagai 'Tidak Dilanjutkan'.
     *
     * @return array{has_snapshot: bool, is_draft: bool, stages: list<array{id: int, name: string, description: ?string, sort_order: int, public_state: string, label: string, badge_class: string, applicant_message: ?string, published_at: ?\Illuminate\Support\Carbon, is_current: bool}>}
     */
    public function tracking(JobApplication $application): array
    {
        if ($application->status === JobApplicationStatus::DRAFT) {
            return [
                'has_snapshot' => false,
                'is_draft' => true,
                'stages' => [],
            ];
        }

        $snapshot = $application->snapshot;
        if (! $snapshot || empty($snapshot->vacancy_data['selection_stages'])) {
            return [
                'has_snapshot' => false,
                'is_draft' => false,
                'stages' => [],
            ];
        }

        $stages = $snapshot->vacancy_data['selection_stages'];

        usort($stages, fn ($a, $b) => ($a['sort_order'] ?? 0) <=> ($b['sort_order'] ?? 0));

        $application->load('stageHistories');
        $histories = $application->stageHistories->keyBy('vacancy_selection_stage_id');

        $stagesOutput = [];
        $hasFailedBefore = false;
        $currentStageIdentified = false;

        $liveStages = $application->jobVacancy?->selectionStages?->keyBy('id') ?? collect();

        foreach ($stages as $stage) {
            $stageId = (int) ($stage['id'] ?? 0);
            $liveStage = $liveStages->get($stageId);
            $history = $histories->get($stageId);
            $isPublished = $this->isStagePublished($history);

            if ($hasFailedBefore && ! $isPublished) {
                // Subsequent stage after a published FAILED stage
                $publicState = 'not_continued';
                $publicLabel = 'Tidak Dilanjutkan';
                $badgeClass = 'bg-slate-100 text-slate-500 border-slate-200';
                $applicantMessage = null;
                $publishedAt = null;
                $isCurrent = false;
            } elseif (! $isPublished) {
                // History belum ada atau published_at masih NULL / masa depan
                $publicState = 'waiting';
                $publicLabel = 'Menunggu';
                $badgeClass = 'bg-slate-100 text-slate-600 border-slate-200';
                $applicantMessage = null;
                $publishedAt = null;
                $isCurrent = false;
            } else {
                // History valid dan sudah dipublikasikan (published_at <= now())
                $statusEnum = $history->status instanceof ApplicationStageStatus
                    ? $history->status
                    : ApplicationStageStatus::tryFrom((string) $history->status);

                match ($statusEnum) {
                    ApplicationStageStatus::PENDING => [
                        $publicState = 'pending',
                        $publicLabel = 'Belum Dimulai',
                        $badgeClass = 'bg-amber-50 text-amber-700 border-amber-200',
                    ],
                    ApplicationStageStatus::IN_PROGRESS => [
                        $publicState = 'in_progress',
                        $publicLabel = 'Sedang Berlangsung',
                        $badgeClass = 'bg-blue-50 text-blue-700 border-blue-200',
                    ],
                    ApplicationStageStatus::PASSED => [
                        $publicState = 'passed',
                        $publicLabel = 'Lulus',
                        $badgeClass = 'bg-emerald-50 text-emerald-700 border-emerald-200',
                    ],
                    ApplicationStageStatus::FAILED => [
                        $publicState = 'failed',
                        $publicLabel = 'Tidak Lolos',
                        $badgeClass = 'bg-red-50 text-red-700 border-red-200',
                        $hasFailedBefore = true,
                    ],
                    default => [
                        $publicState = 'waiting',
                        $publicLabel = 'Menunggu',
                        $badgeClass = 'bg-slate-100 text-slate-600 border-slate-200',
                    ],
                };

                $applicantMessage = $history->applicant_message;
                $publishedAt = $history->published_at;
                $isCurrent = false;
            }

            $stageName = (string) ($stage['name'] ?? ($liveStage?->name ?? 'Tahapan Seleksi'));
            $stageDescription = $stage['description'] ?? ($liveStage?->description ?? null);
            $startAt = ($liveStage && $liveStage->start_at) ? $liveStage->start_at : (! empty($stage['start_at']) ? Carbon::parse($stage['start_at']) : null);
            $endAt = ($liveStage && $liveStage->end_at) ? $liveStage->end_at : (! empty($stage['end_at']) ? Carbon::parse($stage['end_at']) : null);
            $daysRemaining = $endAt ? (int) ceil(now()->diffInDays($endAt->endOfDay(), false)) : null;
            $isDeadlineApproaching = $endAt && $daysRemaining !== null && $daysRemaining >= 0 && $daysRemaining <= 3;
            $isPastDeadline = $endAt ? $endAt->endOfDay()->isPast() : false;

            $stageNameLower = strtolower($stageName);
            $hasActionKeyword = str_contains($stageNameLower, 'tes')
                || str_contains($stageNameLower, 'ujian')
                || str_contains($stageNameLower, 'wawancara')
                || str_contains($stageNameLower, 'interview')
                || str_contains($stageNameLower, 'konfirmasi')
                || str_contains($stageNameLower, 'mcu')
                || str_contains($stageNameLower, 'kesehatan')
                || str_contains($stageNameLower, 'formulir');

            $requiresAction = ($publicState === 'in_progress' && $hasActionKeyword)
                || (! empty($applicantMessage) && in_array($publicState, ['in_progress', 'pending'], true));

            $stagesOutput[] = [
                'id' => $stageId,
                'name' => $stageName,
                'description' => $stageDescription,
                'sort_order' => (int) ($stage['sort_order'] ?? 1),
                'public_state' => $publicState,
                'label' => $publicLabel,
                'badge_class' => $badgeClass,
                'applicant_message' => $applicantMessage,
                'published_at' => $publishedAt,
                'start_at' => $startAt,
                'end_at' => $endAt,
                'days_remaining' => $daysRemaining,
                'is_deadline_approaching' => $isDeadlineApproaching,
                'is_past_deadline' => $isPastDeadline,
                'requires_action' => $requiresAction,
                'is_current' => false,
            ];
        }

        // Tentukan is_current secara publik: tahapan pertama yang sedang berlangsung,
        // atau jika tidak ada, tahapan pertama yang belum dimulai / menunggu.
        foreach ($stagesOutput as &$st) {
            if ($st['public_state'] === 'in_progress') {
                $st['is_current'] = true;
                $currentStageIdentified = true;
                break;
            }
        }
        unset($st);

        if (! $currentStageIdentified) {
            foreach ($stagesOutput as &$st) {
                if (in_array($st['public_state'], ['pending', 'waiting'], true)) {
                    $st['is_current'] = true;
                    break;
                }
            }
            unset($st);
        }

        return [
            'has_snapshot' => true,
            'is_draft' => false,
            'stages' => $stagesOutput,
        ];
    }

    /**
     * Mengembalikan tahapan yang sedang aktif dihadapi oleh pelamar secara publik.
     *
     * @return array{id: int, name: string, description: ?string, sort_order: int, public_state: string, label: string, badge_class: string, applicant_message: ?string, published_at: ?\Illuminate\Support\Carbon, is_current: bool}|null
     */
    public function currentStage(JobApplication $application): ?array
    {
        $trackingData = $this->tracking($application);
        foreach ($trackingData['stages'] as $stage) {
            if (! empty($stage['is_current'])) {
                return $stage;
            }
        }

        return null;
    }
}
