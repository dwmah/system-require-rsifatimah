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
     * Jika seluruh berkas persyaratan telah berstatus valid, tahap berkas / administrasi otomatis lolos.
     */
    public function verifyDocument(ApplicationDocument $document, string $status, ?string $note = null, ?User $adminUser = null): ApplicationDocument
    {
        $document->update([
            'verification_status' => $status,
            'verification_note' => $note,
        ]);

        if ($status === 'valid') {
            $this->checkAndAutoApproveIfAllDocumentsValid($document->jobApplication, $adminUser);
        }

        return $document;
    }

    /**
     * Periksa apakah seluruh berkas persyaratan telah berstatus valid.
     * Jika ya, otomatis loloskan tahapan verifikasi berkas / seleksi administrasi.
     */
    public function checkAndAutoApproveIfAllDocumentsValid(JobApplication $application, ?User $adminUser = null): bool
    {
        if (!$application || $application->isRejected() || $application->isPassedAdministration()) {
            return false;
        }

        $requirements = $application->jobVacancy->documentRequirements;
        if ($requirements->isEmpty()) {
            return false;
        }

        $mandatoryReqs = $requirements->where('is_required', true);
        $reqsToCheck = $mandatoryReqs->isNotEmpty() ? $mandatoryReqs : $requirements;

        foreach ($reqsToCheck as $req) {
            $doc = $application->documents()->where('vacancy_document_requirement_id', $req->id)->first();
            if (!$doc || $doc->verification_status !== 'valid') {
                return false;
            }
        }

        // Pastikan tidak ada dokumen terunggah yang berstatus invalid atau pending
        if ($application->documents()->where('verification_status', 'invalid')->exists()) {
            return false;
        }
        if ($application->documents()->where('verification_status', 'pending')->exists()) {
            return false;
        }

        $admin = $adminUser ?? \Illuminate\Support\Facades\Auth::user() ?? User::where('role', User::ROLE_ADMIN)->first();

        // Temukan tahapan seleksi administrasi / berkas
        $adminStage = $application->jobVacancy->selectionStages()
            ->where(function ($q) {
                $q->where('name', 'like', '%Administrasi%')
                  ->orWhere('name', 'like', '%Berkas%')
                  ->orWhere('name', 'like', '%Dokumen%');
            })
            ->orderBy('sort_order', 'asc')
            ->first() ?? $application->jobVacancy->selectionStages()->orderBy('sort_order', 'asc')->first();

        if ($adminStage) {
            ApplicationStageHistory::updateOrCreate(
                [
                    'job_application_id' => $application->id,
                    'vacancy_selection_stage_id' => $adminStage->id,
                ],
                [
                    'status' => ApplicationStageStatus::PASSED,
                    'decided_at' => now(),
                    'note' => 'Seluruh berkas persyaratan telah diverifikasi valid.',
                    'published_at' => now(),
                    'applicant_message' => 'Selamat, seluruh berkas persyaratan Anda telah terverifikasi Valid dan Lolos Seleksi Berkas/Administrasi.',
                ]
            );

            // Update status lamaran
            $application->update([
                'status' => JobApplicationStatus::PASSED_ADMINISTRATION,
                'verified_at' => now(),
                'verified_by' => $admin?->id,
            ]);

            // Set tahapan berikutnya jika ada
            $nextStage = $application->jobVacancy->selectionStages()
                ->where('sort_order', '>', $adminStage->sort_order)
                ->orderBy('sort_order', 'asc')
                ->first();

            if ($nextStage) {
                $application->update(['current_stage_id' => $nextStage->id]);

                ApplicationStageHistory::updateOrCreate(
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

            // Kirim notifikasi lolos berkas administrasi
            $this->notificationService->notifyVerificationResult($application, true);
            return true;
        }

        return false;
    }

    /**
     * Menyetujui (Approve) verifikasi biodata & portofolio pelamar dari panel biodata.
     */
    public function approveBiodata(JobApplication $application, User $adminUser, ?string $adminNote = null): JobApplication
    {
        return DB::transaction(function () use ($application, $adminUser, $adminNote) {
            // Temukan tahapan pendaftaran online / biodata / portofolio
            $biodataStage = $application->jobVacancy->selectionStages()
                ->where(function ($q) {
                    $q->where('name', 'like', '%Pendaftaran%')
                      ->orWhere('name', 'like', '%Biodata%')
                      ->orWhere('name', 'like', '%Portofolio%');
                })
                ->orderBy('sort_order', 'asc')
                ->first() ?? $application->jobVacancy->selectionStages()->orderBy('sort_order', 'asc')->first();

            if ($biodataStage) {
                ApplicationStageHistory::updateOrCreate(
                    [
                        'job_application_id' => $application->id,
                        'vacancy_selection_stage_id' => $biodataStage->id,
                    ],
                    [
                        'status' => ApplicationStageStatus::PASSED,
                        'decided_at' => now(),
                        'note' => $adminNote ?? 'Biodata dan riwayat pelamar telah diverifikasi sesuai.',
                        'published_at' => now(),
                        'applicant_message' => $adminNote ?? 'Selamat, biodata dan data pendaftaran Anda telah diverifikasi dan dinyatakan Lolos.',
                    ]
                );

                // Jika tahapan saat ini adalah tahap biodata, alihkan ke tahap berikutnya (misal berkas)
                $nextStage = $application->jobVacancy->selectionStages()
                    ->where('sort_order', '>', $biodataStage->sort_order)
                    ->orderBy('sort_order', 'asc')
                    ->first();

                if ($nextStage && $application->current_stage_id === $biodataStage->id) {
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

                $this->notificationService->notifyStageUpdate($application, $biodataStage->stage_name, $adminNote);
            }

            return $application->fresh();
        });
    }

    /**
     * Menolak (Reject) biodata pelamar.
     */
    public function rejectBiodata(JobApplication $application, User $adminUser, string $rejectionReason): JobApplication
    {
        return $this->rejectAdministration($application, $adminUser, $rejectionReason);
    }

    /**
     * Menyetujui (Approve) kelengkapan administrasi/biodata pelamar secara keseluruhan.
     */
    public function approveAdministration(JobApplication $application, User $adminUser, ?string $adminNote = null): JobApplication
    {
        return DB::transaction(function () use ($application, $adminUser, $adminNote) {
            // Update status lamaran
            $application->update([
                'status' => JobApplicationStatus::PASSED_ADMINISTRATION,
                'accepted_at' => null,
                'rejected_at' => null,
                'rejection_reason' => null,
                'verified_at' => now(),
                'verified_by' => $adminUser->id,
            ]);

            // Temukan tahapan seleksi administrasi / verifikasi berkas dokumen
            $adminStage = $application->jobVacancy->selectionStages()
                ->where(function ($q) {
                    $q->where('name', 'like', '%Administrasi%')
                      ->orWhere('name', 'like', '%Berkas%')
                      ->orWhere('name', 'like', '%Dokumen%');
                })
                ->orderBy('sort_order', 'asc')
                ->first() ?? $application->jobVacancy->selectionStages()->orderBy('sort_order', 'asc')->first();

            if ($adminStage) {
                // Loloskan semua tahapan pendaftaran online, review berkas/portofolio, hingga seleksi administrasi (sort_order <= adminStage)
                $priorAndAdminStages = $application->jobVacancy->selectionStages()
                    ->where('sort_order', '<=', $adminStage->sort_order)
                    ->orderBy('sort_order', 'asc')
                    ->get();

                foreach ($priorAndAdminStages as $stg) {
                    $defaultMsg = $stg->id === $adminStage->id
                        ? ($adminNote ?? 'Selamat, berkas administrasi Anda telah diverifikasi dan dinyatakan Lolos.')
                        : 'Pendaftaran online dan kelengkapan berkas/portofolio telah diverifikasi dan dinyatakan Lolos.';

                    ApplicationStageHistory::updateOrCreate(
                        [
                            'job_application_id' => $application->id,
                            'vacancy_selection_stage_id' => $stg->id,
                        ],
                        [
                            'status' => ApplicationStageStatus::PASSED,
                            'decided_at' => now(),
                            'note' => $adminNote ?? 'Lolos verifikasi.',
                            'published_at' => now(),
                            'applicant_message' => $defaultMsg,
                        ]
                    );
                }

                // Set tahapan berikutnya jika ada
                $nextStage = $application->jobVacancy->selectionStages()
                    ->where('sort_order', '>', $adminStage->sort_order)
                    ->orderBy('sort_order', 'asc')
                    ->first();

                if ($nextStage) {
                    $application->update(['current_stage_id' => $nextStage->id]);
                    
                    ApplicationStageHistory::updateOrCreate(
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
            } elseif ($stageStatus === ApplicationStageStatus::IN_PROGRESS || $stageStatus === ApplicationStageStatus::PENDING) {
                // Kembalikan status lamaran agar tombol verifikasi muncul kembali di panel biodata
                $application->update([
                    'status' => JobApplicationStatus::SUBMITTED,
                    'verified_at' => null,
                    'verified_by' => null,
                    'accepted_at' => null,
                    'rejected_at' => null,
                    'rejection_reason' => null,
                    'final_result_published_at' => null,
                    'final_result_message' => null,
                    'current_stage_id' => $stage->id,
                ]);

                // Reset seluruh dokumen menjadi 'pending' (Menunggu Review)
                foreach ($application->documents as $doc) {
                    $doc->update([
                        'verification_status' => 'pending',
                        'verification_note' => null,
                    ]);
                }

                // Reset tahapan-tahapan setelahnya agar tidak menggantung
                $subsequentStages = $application->jobVacancy->selectionStages()
                    ->where('sort_order', '>', $stage->sort_order)
                    ->get();

                foreach ($subsequentStages as $subStage) {
                    ApplicationStageHistory::where('job_application_id', $application->id)
                        ->where('vacancy_selection_stage_id', $subStage->id)
                        ->update([
                            'status' => ApplicationStageStatus::PENDING,
                            'decided_at' => null,
                            'published_at' => null,
                            'applicant_message' => null,
                            'note' => null,
                        ]);
                }
            }

            return $application->fresh();
        });
    }
}
