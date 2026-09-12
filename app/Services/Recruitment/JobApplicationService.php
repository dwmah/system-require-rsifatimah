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
}
