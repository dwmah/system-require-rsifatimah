<?php

namespace Tests\Feature;

use App\Enums\JobVacancyStatus;
use App\Enums\RecruitmentPeriodStatus;
use App\Exceptions\Recruitment\InvalidDocumentRequirementException;
use App\Exceptions\Recruitment\InvalidRecruitmentPeriodException;
use App\Exceptions\Recruitment\InvalidSelectionStageException;
use App\Models\JobApplication;
use App\Models\JobVacancy;
use App\Models\RecruitmentPeriod;
use App\Models\User;
use App\Models\VacancyDocumentRequirement;
use App\Models\VacancySelectionStage;
use App\Services\Recruitment\JobApplicationService;
use App\Services\Recruitment\RecruitmentService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RecruitmentBusinessLogicTest extends TestCase
{
    use RefreshDatabase;

    private JobApplicationService $applicationService;
    private RecruitmentService $recruitmentService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->applicationService = new JobApplicationService();
        $this->recruitmentService = new RecruitmentService();
    }

    /**
     * 1. RecruitmentPeriod currentlyOpen mengembalikan periode yang aktif.
     */
    public function test_recruitment_period_currently_open_returns_active_period(): void
    {
        $activePeriod = RecruitmentPeriod::create([
            'name' => 'Periode Aktif 2026',
            'code' => 'ACTIVE-2026',
            'registration_start_at' => now()->subDays(2),
            'registration_end_at' => now()->addDays(10),
            'status' => RecruitmentPeriodStatus::OPEN,
        ]);

        $openPeriods = RecruitmentPeriod::currentlyOpen()->get();

        $this->assertTrue($openPeriods->contains('id', $activePeriod->id));
        $this->assertTrue($activePeriod->isCurrentlyOpen());
    }

    /**
     * 2. RecruitmentPeriod yang berstatus open tetapi tanggal sudah lewat tidak dianggap aktif.
     */
    public function test_recruitment_period_with_open_status_but_expired_date_is_not_active(): void
    {
        $expiredPeriod = RecruitmentPeriod::create([
            'name' => 'Periode Lampau',
            'code' => 'EXPIRED-2026',
            'registration_start_at' => now()->subDays(15),
            'registration_end_at' => now()->subDays(1),
            'status' => RecruitmentPeriodStatus::OPEN,
        ]);

        $openPeriods = RecruitmentPeriod::currentlyOpen()->get();

        $this->assertFalse($openPeriods->contains('id', $expiredPeriod->id));
        $this->assertFalse($expiredPeriod->isCurrentlyOpen());
    }

    /**
     * 3. JobVacancy open dengan tanggal valid dan periode aktif dianggap aktif.
     */
    public function test_job_vacancy_open_with_valid_dates_and_active_period_is_active(): void
    {
        $period = RecruitmentPeriod::create([
            'name' => 'Periode Utama',
            'code' => 'PER-01',
            'registration_start_at' => now()->subDays(3),
            'registration_end_at' => now()->addDays(10),
            'status' => RecruitmentPeriodStatus::OPEN,
        ]);

        $vacancy = JobVacancy::create([
            'recruitment_period_id' => $period->id,
            'code' => 'VAC-ACTIVE',
            'title' => 'Perawat IGD',
            'unit' => 'IGD',
            'employment_type' => 'PKWT',
            'quota' => 2,
            'location' => 'Banyuwangi',
            'open_at' => now()->subDays(2),
            'close_at' => now()->addDays(5),
            'status' => JobVacancyStatus::OPEN,
        ]);

        $openVacancies = JobVacancy::currentlyOpen()->get();

        $this->assertTrue($openVacancies->contains('id', $vacancy->id));
        $this->assertTrue($vacancy->isCurrentlyOpen());
        $this->assertTrue($this->recruitmentService->hasActiveRecruitment());
        $this->assertTrue(JobVacancy::hasActiveRecruitment());
    }

    /**
     * 4. JobVacancy open tetapi periode induk tidak aktif tidak dianggap aktif.
     */
    public function test_job_vacancy_open_but_period_is_inactive_is_not_considered_active(): void
    {
        // Periode sudah ditutup / kedaluwarsa
        $closedPeriod = RecruitmentPeriod::create([
            'name' => 'Periode Ditutup',
            'code' => 'PER-CLOSED',
            'registration_start_at' => now()->subDays(20),
            'registration_end_at' => now()->subDays(5),
            'status' => RecruitmentPeriodStatus::COMPLETED,
        ]);

        $vacancy = JobVacancy::create([
            'recruitment_period_id' => $closedPeriod->id,
            'code' => 'VAC-ORPHAN',
            'title' => 'Dokter Umum',
            'unit' => 'Pelayanan Medis',
            'employment_type' => 'PKWT',
            'quota' => 1,
            'location' => 'Banyuwangi',
            'open_at' => now()->subDays(2),
            'close_at' => now()->addDays(5),
            'status' => JobVacancyStatus::OPEN,
        ]);

        $openVacancies = JobVacancy::currentlyOpen()->get();

        $this->assertFalse($openVacancies->contains('id', $vacancy->id));
        $this->assertFalse($vacancy->isCurrentlyOpen());
    }

    /**
     * 5. JobApplication menolak mismatch:
     * recruitment_period_id != vacancy.recruitment_period_id.
     */
    public function test_job_application_rejects_recruitment_period_mismatch(): void
    {
        $period1 = RecruitmentPeriod::create([
            'name' => 'Periode 1',
            'code' => 'PER-P1',
            'registration_start_at' => now()->subDays(2),
            'registration_end_at' => now()->addDays(10),
            'status' => RecruitmentPeriodStatus::OPEN,
        ]);

        $period2 = RecruitmentPeriod::create([
            'name' => 'Periode 2',
            'code' => 'PER-P2',
            'registration_start_at' => now()->subDays(2),
            'registration_end_at' => now()->addDays(10),
            'status' => RecruitmentPeriodStatus::OPEN,
        ]);

        $vacancy = JobVacancy::create([
            'recruitment_period_id' => $period1->id,
            'code' => 'VAC-P1',
            'title' => 'Radiografer',
            'unit' => 'Radiologi',
            'employment_type' => 'PKWT',
            'quota' => 1,
            'location' => 'Banyuwangi',
            'open_at' => now()->subDays(2),
            'close_at' => now()->addDays(5),
            'status' => JobVacancyStatus::OPEN,
        ]);

        $user = User::create([
            'name' => 'Kandidat Uji',
            'email' => 'kandidat@example.com',
            'password' => bcrypt('password'),
        ]);

        $this->expectException(InvalidRecruitmentPeriodException::class);

        // Mismatch: mencoba mendaftar lowongan di period1 dengan period2_id
        $this->applicationService->createApplication(
            user: $user,
            vacancy: $vacancy,
            recruitmentPeriodId: $period2->id
        );
    }

    /**
     * 6. ApplicationDocument menolak requirement dari vacancy lain.
     */
    public function test_application_document_rejects_requirement_from_different_vacancy(): void
    {
        $period = RecruitmentPeriod::create([
            'name' => 'Periode Valid',
            'code' => 'PER-DOC',
            'registration_start_at' => now()->subDays(2),
            'registration_end_at' => now()->addDays(10),
            'status' => RecruitmentPeriodStatus::OPEN,
        ]);

        $vacancyA = JobVacancy::create([
            'recruitment_period_id' => $period->id,
            'code' => 'VAC-A',
            'title' => 'Lowongan A',
            'unit' => 'Unit A',
            'employment_type' => 'PKWT',
            'quota' => 1,
            'location' => 'Banyuwangi',
            'open_at' => now()->subDays(2),
            'close_at' => now()->addDays(5),
            'status' => JobVacancyStatus::OPEN,
        ]);

        $vacancyB = JobVacancy::create([
            'recruitment_period_id' => $period->id,
            'code' => 'VAC-B',
            'title' => 'Lowongan B',
            'unit' => 'Unit B',
            'employment_type' => 'PKWT',
            'quota' => 1,
            'location' => 'Banyuwangi',
            'open_at' => now()->subDays(2),
            'close_at' => now()->addDays(5),
            'status' => JobVacancyStatus::OPEN,
        ]);

        $reqFromVacancyB = VacancyDocumentRequirement::create([
            'job_vacancy_id' => $vacancyB->id,
            'name' => 'Sertifikat Khusus Lowongan B',
            'is_required' => true,
        ]);

        $user = User::create([
            'name' => 'Pelamar Lowongan A',
            'email' => 'pelamar.a@example.com',
            'password' => bcrypt('password'),
        ]);

        $application = $this->applicationService->createApplication(
            user: $user,
            vacancy: $vacancyA
        );

        $this->expectException(InvalidDocumentRequirementException::class);

        // Mismatch: dokumen requirement milik Vacancy B di-attach ke lamaran Vacancy A
        $this->applicationService->attachDocument(
            application: $application,
            requirement: $reqFromVacancyB,
            documentAttributes: [
                'original_name' => 'sertifikat.pdf',
                'stored_path' => 'documents/sertifikat.pdf',
                'mime_type' => 'application/pdf',
                'file_size' => 1024,
            ]
        );
    }

    /**
     * 7. ApplicationStageHistory menolak selection stage dari vacancy lain.
     */
    public function test_application_stage_history_rejects_stage_from_different_vacancy(): void
    {
        $period = RecruitmentPeriod::create([
            'name' => 'Periode Seleksi',
            'code' => 'PER-STAGE',
            'registration_start_at' => now()->subDays(2),
            'registration_end_at' => now()->addDays(10),
            'status' => RecruitmentPeriodStatus::OPEN,
        ]);

        $vacancyX = JobVacancy::create([
            'recruitment_period_id' => $period->id,
            'code' => 'VAC-X',
            'title' => 'Lowongan X',
            'unit' => 'Unit X',
            'employment_type' => 'PKWT',
            'quota' => 1,
            'location' => 'Banyuwangi',
            'open_at' => now()->subDays(2),
            'close_at' => now()->addDays(5),
            'status' => JobVacancyStatus::OPEN,
        ]);

        $vacancyY = JobVacancy::create([
            'recruitment_period_id' => $period->id,
            'code' => 'VAC-Y',
            'title' => 'Lowongan Y',
            'unit' => 'Unit Y',
            'employment_type' => 'PKWT',
            'quota' => 1,
            'location' => 'Banyuwangi',
            'open_at' => now()->subDays(2),
            'close_at' => now()->addDays(5),
            'status' => JobVacancyStatus::OPEN,
        ]);

        $stageFromVacancyY = VacancySelectionStage::create([
            'job_vacancy_id' => $vacancyY->id,
            'name' => 'Uji Praktek Khusus Vacancy Y',
            'sort_order' => 1,
        ]);

        $user = User::create([
            'name' => 'Pelamar Lowongan X',
            'email' => 'pelamar.x@example.com',
            'password' => bcrypt('password'),
        ]);

        $application = $this->applicationService->createApplication(
            user: $user,
            vacancy: $vacancyX
        );

        $this->expectException(InvalidSelectionStageException::class);

        // Mismatch: tahapan seleksi milik Vacancy Y dicatat pada lamaran Vacancy X
        $this->applicationService->recordStageHistory(
            application: $application,
            stage: $stageFromVacancyY,
            historyAttributes: [
                'status' => 'in_progress',
                'started_at' => now(),
            ]
        );
    }

    /**
     * 8. Kelulusan tahap akhir mematuhi batas kuota formasi.
     */
    public function test_update_stage_to_final_passed_respects_quota_limit(): void
    {
        $period = RecruitmentPeriod::create([
            'name' => 'Periode Kuota Test',
            'code' => 'PER-Q1',
            'registration_start_at' => now()->subDays(2),
            'registration_end_at' => now()->addDays(10),
            'status' => RecruitmentPeriodStatus::OPEN,
        ]);

        $vacancy = JobVacancy::create([
            'recruitment_period_id' => $period->id,
            'code' => 'VAC-Q1',
            'title' => 'Staff IT',
            'unit' => 'IT',
            'employment_type' => 'PKWT',
            'quota' => 1,
            'location' => 'Banyuwangi',
            'open_at' => now()->subDays(2),
            'close_at' => now()->addDays(5),
            'status' => JobVacancyStatus::OPEN,
        ]);

        $stageFinal = VacancySelectionStage::create([
            'job_vacancy_id' => $vacancy->id,
            'name' => 'Wawancara Akhir & Penetapan',
            'sort_order' => 1,
        ]);

        $user1 = User::create([
            'name' => 'Kandidat Pertama',
            'email' => 'kandidat1@example.com',
            'password' => bcrypt('password'),
        ]);

        $user2 = User::create([
            'name' => 'Kandidat Kedua',
            'email' => 'kandidat2@example.com',
            'password' => bcrypt('password'),
        ]);

        $app1 = $this->applicationService->createApplication(user: $user1, vacancy: $vacancy);
        $app2 = $this->applicationService->createApplication(user: $user2, vacancy: $vacancy);

        $verificationService = app(\App\Services\Recruitment\ApplicationVerificationService::class);

        // Kandidat 1 diloloskan pada tahapan akhir (diterima) -> sukses karena kuota 1
        $verificationService->updateSelectionStage($app1, $stageFinal, 'passed', 'Lolos diterima');
        $this->assertEquals(\App\Enums\JobApplicationStatus::ACCEPTED, $app1->fresh()->status);

        // Kandidat 2 mencoba diloloskan pada tahapan akhir -> gagal validasi karena kuota 1 sudah penuh
        $this->expectException(\Illuminate\Validation\ValidationException::class);
        $verificationService->updateSelectionStage($app2, $stageFinal, 'passed', 'Lolos diterima kedua');
    }
}
