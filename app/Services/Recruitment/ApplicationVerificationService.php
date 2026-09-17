<?php

namespace App\Services\Recruitment;

use App\Enums\ApplicationStageStatus;
use App\Enums\JobApplicationStatus;
use App\Models\ApplicationDocument;
use App\Models\ApplicationStageHistory;
use App\Models\JobApplication;
use App\Models\User;
use App\Models\VacancySelectionStage;
use App\Services\Notification\RecruitmentNotificationService;
use Illuminate\Support\Facades\DB;

class ApplicationVerificationService
{
    public function __construct(
        protected RecruitmentNotificationService $notificationService
    ) {
    }

    /**
     * Update status verifikasi dan catatan untuk satu item dokumen.
     */
    public function verifyDocument(ApplicationDocument $document, string $status, ?string $note = null): ApplicationDocument
    {
        $document->update([
            'verification_status' => $status,
            'verification_note' => $note,
        ]);

        return $document;
    }

    /**
     * Menyetujui (Approve) kelengkapan berkas administrasi pelamar.
     */
    public function approveAdministration(JobApplication $application, User $adminUser, ?string $adminNote = null): JobApplication
    {
        return DB::transaction(function () use ($application, $adminUser, $adminNote) {
            // Update status lamaran
            $application->update([
                'status' => JobApplicationStatus::PASSED_ADMINISTRATION,
                'accepted_at' => null, // accepted_at hanya untuk kelulusan seleksi final
                'rejected_at' => null,
                'rejection_reason' => null,
                'verified_at' => now(),
                'verified_by' => $adminUser->id,
            ]);

            // Tandai seluruh dokumen yang belum ditolak menjadi valid
            foreach ($application->documents as $doc) {
                if ($doc->verification_status !== 'invalid') {
                    $doc->update(['verification_status' => 'valid']);
                }
            }

            // Catat riwayat tahap seleksi administrasi
            $adminStage = $application->jobVacancy->selectionStages()
                ->where('name', 'like', '%Administrasi%')
                ->first();

            if ($adminStage) {
                ApplicationStageHistory::updateOrCreate(
                    [
                        'job_application_id' => $application->id,
                        'vacancy_selection_stage_id' => $adminStage->id,
                    ],
                    [
                        'status' => ApplicationStageStatus::PASSED,
                        'decided_at' => now(),
                        'note' => $adminNote ?? 'Berkas lengkap dan sesuai kualifikasi.',
                        'published_at' => now(),
                        'applicant_message' => $adminNote ?? 'Selamat, berkas administrasi Anda telah diverifikasi dan dinyatakan Lolos.',
                    ]
                );

                // Set tahapan berikutnya jika ada
                $nextStage = $application->jobVacancy->selectionStages()
                    ->where('sort_order', '>', $adminStage->sort_order)
                    ->orderBy('sort_order', 'asc')
                    ->first();

                if ($nextStage) {
                    $application->update(['current_stage_id' => $nextStage->id]);
                    
                    ApplicationStageHistory::firstOrCreate(
                        [
                            'job_application_id' => $application->id,
                            'vacancy_selection_stage_id' => $nextStage->id,
                        ],
                        [
                            'status' => ApplicationStageStatus::PENDING,
                            'started_at' => now(),
                            'published_at' => now(),
                        ]
                    );
                }
            }

            // Kirim notifikasi lolos
            $this->notificationService->notifyVerificationResult($application, true);

            return $application->fresh();
        });
    }

    /**
     * Menolak (Reject) berkas lamaran dengan catatan alasan penolakan wajib.
     */
    public function rejectAdministration(JobApplication $application, User $adminUser, string $rejectionReason): JobApplication
    {
        return DB::transaction(function () use ($application, $adminUser, $rejectionReason) {
            $application->update([
                'status' => JobApplicationStatus::REJECTED,
                'rejected_at' => now(),
                'rejection_reason' => $rejectionReason,
                'verified_at' => now(),
                'verified_by' => $adminUser->id,
                'final_result_published_at' => now(),
                'final_result_message' => $rejectionReason,
            ]);

            // Catat riwayat tahap seleksi administrasi ditolak
            $adminStage = $application->jobVacancy->selectionStages()
                ->where('name', 'like', '%Administrasi%')
                ->first();

            if ($adminStage) {
                ApplicationStageHistory::updateOrCreate(
                    [
                        'job_application_id' => $application->id,
                        'vacancy_selection_stage_id' => $adminStage->id,
                    ],
                    [
                        'status' => ApplicationStageStatus::FAILED,
                        'decided_at' => now(),
                        'note' => $rejectionReason,
                        'published_at' => now(),
                        'applicant_message' => $rejectionReason,
                    ]
                );
            }

            // Kirim notifikasi tidak lolos
            $this->notificationService->notifyVerificationResult($application, false);

            return $application->fresh();
        });
    }

    /**
     * Memperbarui status tahapan seleksi lanjutan (Tes Tulis, Interview, MCU, Final).
     */
    public function updateSelectionStage(
        JobApplication $application,
        VacancySelectionStage $stage,
        string $status, // passed, rejected/failed, pending, in_progress
        ?string $note = null
    ): JobApplication {
        $stageStatus = match ($status) {
            'passed' => ApplicationStageStatus::PASSED,
            'rejected', 'failed' => ApplicationStageStatus::FAILED,
            'in_progress' => ApplicationStageStatus::IN_PROGRESS,
            default => ApplicationStageStatus::PENDING,
        };

        return DB::transaction(function () use ($application, $stage, $stageStatus, $note) {
            ApplicationStageHistory::updateOrCreate(
                [
                    'job_application_id' => $application->id,
                    'vacancy_selection_stage_id' => $stage->id,
                ],
                [
                    'status' => $stageStatus,
                    'decided_at' => in_array($stageStatus, [ApplicationStageStatus::PASSED, ApplicationStageStatus::FAILED]) ? now() : null,
                    'note' => $note,
                    'published_at' => now(),
                    'applicant_message' => $note,
                ]
            );

            if ($stageStatus === ApplicationStageStatus::PASSED) {
                // Cek apakah ada tahapan berikutnya
                $nextStage = $application->jobVacancy->selectionStages()
                    ->where('sort_order', '>', $stage->sort_order)
                    ->orderBy('sort_order', 'asc')
                    ->first();

                if ($nextStage) {
                    $application->update([
                        'current_stage_id' => $nextStage->id,
                        'status' => JobApplicationStatus::IN_SELECTION,
                    ]);

                    ApplicationStageHistory::firstOrCreate(
                        [
                            'job_application_id' => $application->id,
                            'vacancy_selection_stage_id' => $nextStage->id,
                        ],
                        [
                            'status' => ApplicationStageStatus::PENDING,
                            'started_at' => now(),
                        ]
                    );

                    $this->notificationService->notifyStageUpdate($application, $nextStage->stage_name, $note);
                } else {
                    // Tahapan terakhir selesai -> Lolos Final / Diterima
                    $application->update([
                        'status' => JobApplicationStatus::ACCEPTED,
                        'accepted_at' => now(),
                        'final_result_published_at' => now(),
                        'final_result_message' => $note ?? 'Selamat, Anda dinyatakan diterima sebagai karyawan RSI Fatimah Banyuwangi.',
                    ]);

                    $this->notificationService->notifyStageUpdate(
                        $application,
                        'Pengumuman Akhir: Diterima sebagai Karyawan RSI Fatimah Banyuwangi',
                        $note ?? 'Selamat atas kelulusan akhir Anda.'
                    );
                }
            } elseif ($stageStatus === ApplicationStageStatus::FAILED) {
                $application->update([
                    'status' => JobApplicationStatus::REJECTED,
                    'rejected_at' => now(),
                    'rejection_reason' => $note ?? "Tidak lolos pada tahapan: {$stage->stage_name}",
                    'final_result_published_at' => now(),
                    'final_result_message' => $note ?? "Tidak lolos pada tahapan: {$stage->stage_name}",
                ]);

                $this->notificationService->notifyVerificationResult($application, false);
            }

            return $application->fresh();
        });
    }
}
