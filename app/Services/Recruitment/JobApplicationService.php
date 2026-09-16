<?php

namespace App\Services\Recruitment;

use App\Enums\JobApplicationStatus;
use App\Exceptions\Recruitment\DuplicateApplicationException;
use App\Exceptions\Recruitment\InactiveVacancyException;
use App\Exceptions\Recruitment\IncompleteProfileException;
use App\Exceptions\Recruitment\InvalidDocumentRequirementException;
use App\Exceptions\Recruitment\InvalidRecruitmentPeriodException;
use App\Exceptions\Recruitment\InvalidSelectionStageException;
use App\Models\ApplicationDocument;
use App\Models\ApplicationStageHistory;
use App\Models\JobApplication;
use App\Models\JobVacancy;
use App\Models\RecruitmentPeriod;
use App\Models\User;
use App\Models\VacancyDocumentRequirement;
use App\Models\VacancySelectionStage;
use App\Services\Profile\ProfileCompletenessService;
use DomainException;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class JobApplicationService
{
    public function __construct(
        protected ?ProfileCompletenessService $completenessService = null
    ) {
        $this->completenessService = $completenessService ?? app(ProfileCompletenessService::class);
    }

    /**
     * Memulai draft lamaran baru untuk pelamar pada lowongan tertentu.
     *
     * Invariants:
     * - User akun harus aktif
     * - Profil dasar pelamar harus 100% lengkap
     * - Lowongan pekerjaan harus currently open
     * - Periode rekrutmen konsisten
     * - Belum ada lamaran lain oleh user pada periode yang sama (1 akun = 1 formasi/periode)
     * - Proteksi konkurensi atomic via DB transaction & deteksi presisi duplicate constraint
     *
     * @throws IncompleteProfileException
     * @throws InactiveVacancyException
     * @throws DuplicateApplicationException
     * @throws InvalidRecruitmentPeriodException
     * @throws DomainException
     */
    public function startDraft(User $user, JobVacancy $vacancy): JobApplication
    {
        if (! $user->is_active) {
            throw new DomainException('Akun pelamar tidak aktif.');
        }

        if (! $this->completenessService->isComplete($user)) {
            throw new IncompleteProfileException();
        }

        if (! $vacancy->isCurrentlyOpen()) {
            throw new InactiveVacancyException();
        }

        $this->validatePeriodMatch($vacancy->recruitment_period_id, $vacancy);

        return DB::transaction(function () use ($user, $vacancy) {
            $existing = JobApplication::where('user_id', $user->id)
                ->where('recruitment_period_id', $vacancy->recruitment_period_id)
                ->lockForUpdate()
                ->first();

            if ($existing) {
                throw new DuplicateApplicationException();
            }

            try {
                return JobApplication::create([
                    'user_id' => $user->id,
                    'recruitment_period_id' => $vacancy->recruitment_period_id,
                    'job_vacancy_id' => $vacancy->id,
                    'status' => JobApplicationStatus::DRAFT,
                    'application_number' => null,
                    'submitted_at' => null,
                    'locked_at' => null,
                    'accepted_at' => null,
                    'rejected_at' => null,
                    'hired_at' => null,
                ]);
            } catch (QueryException $e) {
                $isDuplicate = $e->getCode() == 23000
                    || (isset($e->errorInfo[1]) && $e->errorInfo[1] == 1062)
                    || str_contains($e->getMessage(), 'Duplicate entry');

                if ($isDuplicate) {
                    throw new DuplicateApplicationException(
                        'Anda sudah memiliki lamaran pada periode rekrutmen ini.',
                        422,
                        $e
                    );
                }

                throw $e;
            }
        });
    }
    /**
     * Memvalidasi invariant:
     * recruitment_period_id pada JobApplication HARUS sama dengan
     * recruitment_period_id milik JobVacancy yang dilamar.
     *
     * @throws InvalidRecruitmentPeriodException
     */
    public function validatePeriodMatch(int|RecruitmentPeriod $period, int|JobVacancy $vacancy): void
    {
        $periodId = $period instanceof RecruitmentPeriod ? $period->id : $period;
        $vacancyPeriodId = $vacancy instanceof JobVacancy ? $vacancy->recruitment_period_id : JobVacancy::findOrFail($vacancy)->recruitment_period_id;

        if ($periodId !== $vacancyPeriodId) {
            throw new InvalidRecruitmentPeriodException(
                sprintf(
                    'Periode lamaran (ID: %d) tidak sesuai dengan periode lowongan pekerjaan (ID: %d).',
                    $periodId,
                    $vacancyPeriodId
                )
            );
        }
    }

    /**
     * Membuat lamaran pekerjaan baru dengan jaminan konsistensi integritas periode.
     *
     * @throws InvalidRecruitmentPeriodException
     */
    public function createApplication(
        User $user,
        JobVacancy $vacancy,
        ?int $recruitmentPeriodId = null,
        array $attributes = []
    ): JobApplication {
        $targetPeriodId = $recruitmentPeriodId ?? $vacancy->recruitment_period_id;

        $this->validatePeriodMatch($targetPeriodId, $vacancy);

        return JobApplication::create(array_merge($attributes, [
            'user_id' => $user->id,
            'job_vacancy_id' => $vacancy->id,
            'recruitment_period_id' => $targetPeriodId,
        ]));
    }

    /**
     * Memvalidasi invariant:
     * ApplicationDocument hanya boleh menggunakan VacancyDocumentRequirement
     * yang merupakan bagian dari JobVacancy yang sedang dilamar.
     *
     * @throws InvalidDocumentRequirementException
     */
    public function validateDocumentRequirement(
        JobApplication $application,
        VacancyDocumentRequirement $requirement
    ): void {
        if ($requirement->job_vacancy_id !== $application->job_vacancy_id) {
            throw new InvalidDocumentRequirementException(
                sprintf(
                    'Persyaratan dokumen "%s" (Lowongan ID: %d) tidak valid untuk lamaran lowongan ID: %d.',
                    $requirement->name,
                    $requirement->job_vacancy_id,
                    $application->job_vacancy_id
                )
            );
        }
    }

    /**
     * Menyimpan/memperbarui berkas lamaran dengan validasi invariant dokumen.
     *
     * @throws InvalidDocumentRequirementException
     */
    public function attachDocument(
        JobApplication $application,
        VacancyDocumentRequirement $requirement,
        array $documentAttributes
    ): ApplicationDocument {
        $this->validateDocumentRequirement($application, $requirement);

        return ApplicationDocument::updateOrCreate(
            [
                'job_application_id' => $application->id,
                'vacancy_document_requirement_id' => $requirement->id,
            ],
            $documentAttributes
        );
    }

    /**
     * Memvalidasi invariant:
     * ApplicationStageHistory hanya boleh menggunakan VacancySelectionStage
     * yang merupakan bagian dari JobVacancy yang sedang dilamar.
     *
     * @throws InvalidSelectionStageException
     */
    public function validateSelectionStage(
        JobApplication $application,
        VacancySelectionStage $stage
    ): void {
        if ($stage->job_vacancy_id !== $application->job_vacancy_id) {
            throw new InvalidSelectionStageException(
                sprintf(
                    'Tahapan seleksi "%s" (Lowongan ID: %d) tidak valid untuk lamaran lowongan ID: %d.',
                    $stage->name,
                    $stage->job_vacancy_id,
                    $application->job_vacancy_id
                )
            );
        }
    }

    /**
     * Mencatat riwayat tahapan seleksi lamaran dengan validasi invariant tahapan.
     *
     * @throws InvalidSelectionStageException
     */
    public function recordStageHistory(
        JobApplication $application,
        VacancySelectionStage $stage,
        array $historyAttributes
    ): ApplicationStageHistory {
        $this->validateSelectionStage($application, $stage);

        return ApplicationStageHistory::updateOrCreate(
            [
                'job_application_id' => $application->id,
                'vacancy_selection_stage_id' => $stage->id,
            ],
            $historyAttributes
        );
    }

    /**
     * Menghapus record draft lamaran beserta seluruh berkas dokumen fisik dan riwayat terkait.
     */
    public function deleteApplicationAndFiles(JobApplication $application): void
    {
        // Hapus berkas fisik dan record dokumen lamaran draft
        $documents = $application->documents()->get();
        $disk = Storage::disk(ApplicationDocumentService::DISK_NAME);

        foreach ($documents as $document) {
            if ($document->stored_path && $disk->exists($document->stored_path)) {
                $disk->delete($document->stored_path);
            }
            $document->delete();
        }

        // Bersihkan direktori aplikasi jika ada
        $disk->deleteDirectory((string) $application->id);

        // Hapus stage histories jika ada
        $application->stageHistories()->delete();

        // Hapus record JobApplication draft itu sendiri
        $application->delete();
    }

    /**
     * Membatalkan draft lamaran pekerjaan yang sedang aktif.
     * Sesuai aturan bisnis: pelamar tidak boleh memiliki akun jika tidak ada lamaran yang diajukan.
     * Oleh karena itu, jika pelamar tidak memiliki lamaran lain, pembatalan draft akan sekaligus
     * menghapus akun pengguna (User) beserta seluruh data profil dan berkas fisiknya.
     *
     * Invariants:
     * - Hanya lamaran dengan status DRAFT yang belum di-submit dan belum dikunci yang boleh dibatalkan
     * - Dilakukan dalam database transaction dengan lockForUpdate untuk mencegah race condition
     *
     * @throws DomainException
     */
    public function cancelDraft(User $user, JobApplication $application): void
    {
        DB::transaction(function () use ($user, $application) {
            /** @var JobApplication $lockedApplication */
            $lockedApplication = JobApplication::where('id', $application->id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($lockedApplication->user_id !== $user->id) {
                throw new DomainException('Otorisasi gagal: Lamaran bukan milik akun pengguna yang aktif.');
            }

            if ($lockedApplication->status !== JobApplicationStatus::DRAFT || $lockedApplication->locked_at !== null || $lockedApplication->submitted_at !== null) {
                throw new DomainException('Lamaran yang telah diajukan atau dikunci tidak dapat dibatalkan.');
            }

            // Hapus berkas fisik dan record lamaran draft
            $this->deleteApplicationAndFiles($lockedApplication);

            // Sesuai aturan bisnis: pelamar tanpa lamaran tidak boleh memiliki akun.
            // Jika pengguna tidak lagi memiliki lamaran pekerjaan apapun,
            // hapus akun pengguna (User) beserta foto profilnya.
            if ($user->jobApplications()->count() === 0) {
                if ($user->applicantProfile && $user->applicantProfile->photo_path) {
                    Storage::disk('public')->delete($user->applicantProfile->photo_path);
                }
                $user->delete();
            }
        });
    }

    /**
     * Mengganti formasi lowongan pada draft lamaran pekerjaan yang belum difinalisasi.
     *
     * Invariants:
     * - Hanya draft lamaran yang belum difinalisasi/dikunci yang dapat diganti formulasinya
     * - Draft lama dibatalkan beserta berkas dokumennya secara atomik
     * - Profil dan seluruh riwayat pelamar tetap tersimpan (akun TIDAK dihapus)
     * - Formasi baru harus currently open dan memenuhi syarat
     * - Dibuat draft baru untuk lowongan tujuan
     *
     * @throws IncompleteProfileException
     * @throws InactiveVacancyException
     * @throws DuplicateApplicationException
     * @throws DomainException
     */
    public function switchVacancy(User $user, JobApplication $application, JobVacancy $newVacancy): JobApplication
    {
        return DB::transaction(function () use ($user, $application, $newVacancy) {
            /** @var JobApplication $lockedApplication */
            $lockedApplication = JobApplication::where('id', $application->id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($lockedApplication->user_id !== $user->id) {
                throw new DomainException('Otorisasi gagal: Lamaran bukan milik akun pengguna yang aktif.');
            }

            if ($lockedApplication->status !== JobApplicationStatus::DRAFT || $lockedApplication->locked_at !== null || $lockedApplication->submitted_at !== null) {
                throw new DomainException('Lamaran yang telah diajukan atau dikunci tidak dapat diganti formulasinya.');
            }

            if ($lockedApplication->job_vacancy_id === $newVacancy->id) {
                return $lockedApplication;
            }

            // 1. Bersihkan draft lama dan seluruh dokumen fisik beserta record-nya (akun pelamar tetap utuh)
            $this->deleteApplicationAndFiles($lockedApplication);

            // 2. Buat draft baru untuk formasi baru
            return $this->startDraft($user, $newVacancy);
        });
    }
}
