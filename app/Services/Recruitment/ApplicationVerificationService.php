<?php

namespace App\Services\Recruitment;

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
                ->where('stage_name', 'like', '%Administrasi%')
                ->first();

            if ($adminStage) {
                ApplicationStageHistory::updateOrCreate(
                    [
                        'job_application_id' => $application->id,
                        'vacancy_selection_stage_id' => $adminStage->id,
                    ],
                    [
                        'status' => 'passed',
                        'decided_at' => now(),
                        'note' => $adminNote ?? 'Berkas lengkap dan sesuai kualifikasi.',
                    ]
                );

                // Set tahapan berikutnya jika ada
                $nextStage = $application->jobVacancy->selectionStages()
                    ->where('sequence_order', '>', $adminStage->sequence_order)
                    ->orderBy('sequence_order', 'asc')
                    ->first();

                if ($nextStage) {
                    $application->update(['current_stage_id' => $nextStage->id]);
                    
                    ApplicationStageHistory::firstOrCreate(
                        [
                            'job_application_id' => $application->id,
                            'vacancy_selection_stage_id' => $nextStage->id,
                        ],
                        [
                            'status' => 'pending',
                            'started_at' => now(),
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
            ]);

            // Catat riwayat tahap seleksi administrasi ditolak
            $adminStage = $application->jobVacancy->selectionStages()
                ->where('stage_name', 'like', '%Administrasi%')
                ->first();

            if ($adminStage) {
                ApplicationStageHistory::updateOrCreate(
                    [
                        'job_application_id' => $application->id,
                        'vacancy_selection_stage_id' => $adminStage->id,
                    ],
                    [
                        'status' => 'rejected',
                        'decided_at' => now(),
                        'note' => $rejectionReason,
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
        string $status, // passed, rejected, pending, in_progress
        ?string $note = null
    ): JobApplication {
        return DB::transaction(function () use ($application, $stage, $status, $note) {
            ApplicationStageHistory::updateOrCreate(
                [
                    'job_application_id' => $application->id,
                    'vacancy_selection_stage_id' => $stage->id,
                ],
                [
                    'status' => $status,
                    'decided_at' => in_array($status, ['passed', 'rejected']) ? now() : null,
                    'note' => $note,
                ]
            );

            if ($status === 'passed') {
                // Cek apakah ada tahapan berikutnya
                $nextStage = $application->jobVacancy->selectionStages()
                    ->where('sequence_order', '>', $stage->sequence_order)
                    ->orderBy('sequence_order', 'asc')
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
                            'status' => 'pending',
                            'started_at' => now(),
                        ]
                    );

                    $this->notificationService->notifyStageUpdate($application, $nextStage->stage_name, $note);
                } else {
                    // Tahapan terakhir selesai -> Lolos Final / Diterima
                    $application->update([
                        'status' => JobApplicationStatus::ACCEPTED,
                        'accepted_at' => now(),
                    ]);

                    $this->notificationService->notifyStageUpdate(
                        $application,
                        'Pengumuman Akhir: Diterima sebagai Karyawan RSI Fatimah Banyuwangi',
                        $note ?? 'Selamat atas kelulusan akhir Anda.'
                    );
                }
            } elseif ($status === 'rejected') {
                $application->update([
                    'status' => JobApplicationStatus::REJECTED,
                    'rejected_at' => now(),
                    'rejection_reason' => $note ?? "Tidak lolos pada tahapan: {$stage->stage_name}",
                ]);

                $this->notificationService->notifyVerificationResult($application, false);
            }

            return $application->fresh();
        });
    }
}
