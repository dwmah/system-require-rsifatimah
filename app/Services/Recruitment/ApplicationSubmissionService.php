<?php

namespace App\Services\Recruitment;

use App\Enums\JobApplicationStatus;
use App\Exceptions\Recruitment\ApplicationAlreadySnapshottedException;
use App\Exceptions\Recruitment\DocumentIntegrityException;
use App\Exceptions\Recruitment\InactiveVacancyException;
use App\Exceptions\Recruitment\IncompleteProfileException;
use App\Exceptions\Recruitment\InvalidRecruitmentPeriodException;
use App\Exceptions\Recruitment\MissingRequiredDocumentsException;
use App\Models\JobApplication;
use App\Models\User;
use App\Services\Profile\ProfileCompletenessService;
use DomainException;
use Illuminate\Support\Facades\DB;

class ApplicationSubmissionService
{
    public function __construct(
        protected ?ProfileCompletenessService $profileCompletenessService = null,
        protected ?ApplicationDocumentService $documentService = null,
        protected ?ApplicationSnapshotService $snapshotService = null
    ) {
        $this->profileCompletenessService = $profileCompletenessService ?? app(ProfileCompletenessService::class);
        $this->documentService = $documentService ?? app(ApplicationDocumentService::class);
        $this->snapshotService = $snapshotService ?? app(ApplicationSnapshotService::class);
    }

    /**
     * Memproses pengiriman resmi lamaran pekerjaan (Final Submit) secara atomik.
     *
     * Urutan Keamanan Wajib (Row Lock Transaction):
     * 1. Verifikasi kepemilikan user (defense-in-depth)
     * 2. Verifikasi status keaktifan user
     * 3. Idempotensi jika status sudah SUBMITTED
     * 4. Verifikasi status DRAFT
     * 5. Verifikasi locked_at === null
     * 6. Verifikasi masa pendaftaran lowongan masih buka (isCurrentlyOpen)
     * 7. Re-verifikasi profil dasar pelamar masih 100% lengkap
     * 8. Verifikasi invariant periode lowongan
     * 9. Validasi integritas fisik & kelengkapan dokumen wajib
     * 10. Generate nomor lamaran resmi & capture snapshot beku permanen
     * 11. Lock & update state lamaran ke SUBMITTED
     *
     * @throws IncompleteProfileException
     * @throws InactiveVacancyException
     * @throws MissingRequiredDocumentsException
     * @throws DocumentIntegrityException
     * @throws DomainException
     */
    public function submit(User $user, JobApplication $application): JobApplication
    {
        return DB::transaction(function () use ($user, $application) {
            // Query ulang dengan row-level exclusive lock untuk mencegah concurrent submit
            /** @var JobApplication $lockedApplication */
            $lockedApplication = JobApplication::where('id', $application->id)
                ->lockForUpdate()
                ->firstOrFail();

            // 1. Verifikasi Kepemilikan User
            if ($lockedApplication->user_id !== $user->id) {
                throw new DomainException('Otorisasi gagal: Lamaran bukan milik akun pengguna yang aktif.');
            }

            // 2. Verifikasi Keaktifan User
            if (! $user->is_active) {
                throw new DomainException('Akun pelamar dalam status tidak aktif.');
            }

            // 3. Idempotent Early Return (Hanya setelah kepemilikan diverifikasi)
            if ($lockedApplication->status === JobApplicationStatus::SUBMITTED) {
                // Verifikasi integritas state historis submitted (anti-corruption guard)
                if (
                    empty($lockedApplication->application_number) ||
                    $lockedApplication->submitted_at === null ||
                    $lockedApplication->locked_at === null ||
                    ! $lockedApplication->snapshot()->exists()
                ) {
                    throw new DocumentIntegrityException('Integritas data lamaran tidak valid: Data pengiriman historis rusak atau tidak lengkap.');
                }

                return $lockedApplication->fresh(['snapshot', 'documents', 'jobVacancy', 'recruitmentPeriod']);
            }

            // 4. Verifikasi Status DRAFT
            if ($lockedApplication->status !== JobApplicationStatus::DRAFT) {
                throw new DomainException('Lamaran tidak dalam status draft yang dapat diajukan.');
            }

            // 5. Verifikasi locked_at masih NULL
            if ($lockedApplication->locked_at !== null) {
                throw new DomainException('Lamaran telah dikunci dan tidak dapat diajukan ulang.');
            }

            // 6. Verifikasi Status Keterbukaan Lowongan
            $vacancy = $lockedApplication->jobVacancy;
            if (! $vacancy || ! $vacancy->isCurrentlyOpen()) {
                throw new InactiveVacancyException('Masa pengiriman lamaran untuk formasi ini telah berakhir.');
            }

            // 7. Re-check Kelengkapan Profil Dasar 100%
            if (! $this->profileCompletenessService->isComplete($user)) {
                throw new IncompleteProfileException('Profil dasar Anda tidak lagi lengkap 100%. Lengkapi profil sebelum mengirim lamaran.');
            }

            // 8. Verifikasi Invariant Periode Rekrutmen
            if ($lockedApplication->recruitment_period_id !== $vacancy->recruitment_period_id) {
                throw new InvalidRecruitmentPeriodException('Periode lamaran tidak konsisten dengan formasi lowongan.');
            }

            // 9. Validasi Kelengkapan Dokumen Wajib & Integritas File Fisik
            $this->documentService->validateDocumentsForSubmission($lockedApplication);

            // 10. Verifikasi Snapshot Belum Pernah Dibuat
            if ($lockedApplication->snapshot()->exists()) {
                throw new ApplicationAlreadySnapshottedException;
            }

            // 11. Tetapkan Timestamp Sinkron Identik
            $submittedAt = now();

            // 12. Generate Nomor Lamaran Resmi Format: LAM-{SAFE_PERIOD_CODE}-{ID_PADDED_6}
            $applicationNumber = $this->generateApplicationNumber($lockedApplication);

            // 13. Capture Snapshot Permanen
            $this->snapshotService->capture($lockedApplication, $submittedAt);

            // 14. Update State Akhir & Kunci Lamaran
            $lockedApplication->update([
                'application_number' => $applicationNumber,
                'status' => JobApplicationStatus::SUBMITTED,
                'submitted_at' => $submittedAt,
                'locked_at' => $submittedAt,
            ]);

            return $lockedApplication->fresh(['snapshot', 'documents', 'jobVacancy', 'recruitmentPeriod']);
        });
    }

    /**
     * Menghasilkan nomor pendaftaran resmi yang aman dan deterministik.
     * Format: LAM-{SAFE_PERIOD_CODE}-{APPLICATION_ID_PADDED_6}
     *
     * @throws DomainException
     */
    public function generateApplicationNumber(JobApplication $application): string
    {
        $rawCode = strtoupper(trim($application->recruitmentPeriod->code ?? ''));

        // Normalisasi separator ke '-' dan hanya mengizinkan karakter A-Z 0-9
        $normalized = preg_replace('/[^A-Z0-9]/', '-', $rawCode);
        $collapsed = preg_replace('/-+/', '-', $normalized);
        $safePeriodCode = trim($collapsed, '-');

        if ($safePeriodCode === '') {
            throw new DomainException('Kode periode rekrutmen tidak valid untuk pembuatan nomor lamaran resmi.');
        }

        return sprintf('LAM-%s-%06d', $safePeriodCode, $application->id);
    }
}
