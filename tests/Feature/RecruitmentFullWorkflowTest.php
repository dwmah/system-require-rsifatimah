<?php

namespace Tests\Feature;

use App\Enums\JobApplicationStatus;
use App\Enums\JobVacancyStatus;
use App\Enums\RecruitmentPeriodStatus;
use App\Models\ApplicantProfile;
use App\Models\JobApplication;
use App\Models\JobVacancy;
use App\Models\RecruitmentPeriod;
use App\Models\User;
use App\Models\VacancyDocumentRequirement;
use App\Models\VacancySelectionStage;
use App\Services\Lifecycle\UserLifecycleService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class RecruitmentFullWorkflowTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected User $applicant;

    protected RecruitmentPeriod $period;

    protected JobVacancy $vacancy;

    protected VacancyDocumentRequirement $docReqSurat;

    protected VacancyDocumentRequirement $docReqIjazah;

    protected VacancyDocumentRequirement $docReqStr;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('local');

        // 1. Admin User
        $this->admin = User::factory()->create([
            'name' => 'Admin SDI RSI Fatimah',
            'email' => 'admin@rsifatimah.com',
            'role' => User::ROLE_ADMIN,
            'is_active' => true,
        ]);

        // 2. Applicant User
        $this->applicant = User::factory()->create([
            'name' => 'Ahmad Fauzi, S.Kep.',
            'email' => 'fauzi@example.com',
            'phone' => '08123456789',
            'role' => User::ROLE_APPLICANT,
            'is_active' => true,
        ]);

        ApplicantProfile::create([
            'user_id' => $this->applicant->id,
            'nik' => '3510123456780001',
            'nickname' => 'Fauzi',
            'gender' => 'male',
            'birth_place' => 'Banyuwangi',
            'birth_date' => '1995-05-12',
            'religion' => 'Islam',
            'address_ktp' => 'Jl. Basuki Rahmat No. 10',
            'address_domicile' => 'Jl. Basuki Rahmat No. 10',
            'phone' => '08123456789',
            'photo_path' => 'profile-photos/test.jpg',
        ]);

        $this->applicant->applicantProfile->educations()->create([
            'institution_name' => 'Universitas Airlangga',
            'education_level' => 'S1',
            'study_program' => 'Keperawatan',
            'graduation_year' => 2018,
            'gpa' => 3.75,
        ]);

        // 3. Active Recruitment Period
        $this->period = RecruitmentPeriod::create([
            'code' => 'PER-2026-01',
            'name' => 'Rekrutmen Tenaga Kesehatan 2026',
            'registration_start_at' => now()->subDays(5),
            'registration_end_at' => now()->addDays(20),
            'status' => RecruitmentPeriodStatus::OPEN,
        ]);

        // 4. Job Vacancy
        $this->vacancy = JobVacancy::create([
            'recruitment_period_id' => $this->period->id,
            'code' => 'VAC-PRW-001',
            'title' => 'Perawat Pelaksana IGD',
            'unit' => 'Instalasi Gawat Darurat',
            'employment_type' => 'Tenaga Keperawatan',
            'location' => 'RSI Fatimah Banyuwangi',
            'quota' => 5,
            'description' => 'Melaksanakan asuhan keperawatan gawat darurat sesuai standar akreditasi KARS.',
            'open_at' => now()->subDays(5),
            'close_at' => now()->addDays(20),
            'status' => JobVacancyStatus::OPEN,
        ]);

        // 5. Requirements
        $this->docReqSurat = VacancyDocumentRequirement::create([
            'job_vacancy_id' => $this->vacancy->id,
            'name' => 'Surat Lamaran Kerja',
            'is_required' => true,
            'allowed_mimes' => 'application/pdf,image/jpeg,image/png',
            'max_size_kb' => 5120,
            'sort_order' => 1,
        ]);

        $this->docReqIjazah = VacancyDocumentRequirement::create([
            'job_vacancy_id' => $this->vacancy->id,
            'name' => 'Ijazah Terakhir Legalisir',
            'is_required' => true,
            'allowed_mimes' => 'application/pdf,image/jpeg,image/png',
            'max_size_kb' => 5120,
            'sort_order' => 2,
        ]);

        $this->docReqStr = VacancyDocumentRequirement::create([
            'job_vacancy_id' => $this->vacancy->id,
            'name' => 'STR Aktif',
            'is_required' => true,
            'allowed_mimes' => 'application/pdf,image/jpeg,image/png',
            'max_size_kb' => 5120,
            'sort_order' => 3,
        ]);

        // 6. Stages
        VacancySelectionStage::create([
            'job_vacancy_id' => $this->vacancy->id,
            'name' => 'Seleksi Administrasi',
            'sort_order' => 1,
        ]);

        VacancySelectionStage::create([
            'job_vacancy_id' => $this->vacancy->id,
            'name' => 'Ujian Tulis',
            'sort_order' => 2,
        ]);
    }

    public function test_authorization_prevents_applicant_from_accessing_admin_dashboard(): void
    {
        $response = $this->actingAs($this->applicant)->get(route('admin.dashboard'));
        $response->assertStatus(403);
    }

    public function test_admin_can_access_admin_dashboard_and_vacancies(): void
    {
        $response = $this->actingAs($this->admin)->get(route('admin.dashboard'));
        $response->assertStatus(200);
        $response->assertSee('Dashboard Rekrutmen SDI');

        $responseJobs = $this->actingAs($this->admin)->get(route('admin.vacancies.index'));
        $responseJobs->assertStatus(200);
        $responseJobs->assertSee('Perawat Pelaksana IGD');
    }

    public function test_applicant_can_select_vacancy_and_upload_documents(): void
    {
        // 1. Pilih Formasi
        $responseApply = $this->actingAs($this->applicant)->post(route('portal.applications.store'), [
            'job_vacancy_id' => $this->vacancy->id,
        ]);

        $responseApply->assertRedirect(route('portal.applications.index'));
        $this->assertDatabaseHas('job_applications', [
            'user_id' => $this->applicant->id,
            'job_vacancy_id' => $this->vacancy->id,
            'status' => JobApplicationStatus::DRAFT->value,
        ]);

        $application = JobApplication::where('user_id', $this->applicant->id)->first();

        // 2. Upload Dokumen Wajib
        $fileSurat = UploadedFile::fake()->create('surat_lamaran.pdf', 500, 'application/pdf');
        $this->actingAs($this->applicant)->post(route('portal.applications.documents.store', $application), [
            'vacancy_document_requirement_id' => $this->docReqSurat->id,
            'document_file' => $fileSurat,
        ])->assertSessionHasNoErrors();

        $fileIjazah = UploadedFile::fake()->create('ijazah.pdf', 600, 'application/pdf');
        $this->actingAs($this->applicant)->post(route('portal.applications.documents.store', $application), [
            'vacancy_document_requirement_id' => $this->docReqIjazah->id,
            'document_file' => $fileIjazah,
        ])->assertSessionHasNoErrors();

        $fileStr = UploadedFile::fake()->create('str.pdf', 400, 'application/pdf');
        $this->actingAs($this->applicant)->post(route('portal.applications.documents.store', $application), [
            'vacancy_document_requirement_id' => $this->docReqStr->id,
            'document_file' => $fileStr,
        ])->assertSessionHasNoErrors();

        $this->assertEquals(3, $application->documents()->count());

        // 3. Final Submit
        $responseSubmit = $this->actingAs($this->applicant)->post(route('portal.applications.submit', $application), [
            'declaration' => true,
        ]);
        $responseSubmit->assertRedirect(route('portal.applications.show', $application));

        $application->refresh();
        $this->assertEquals(JobApplicationStatus::SUBMITTED, $application->status);
        $this->assertNotNull($application->submitted_at);
        $this->assertNotNull($application->locked_at);
    }

    public function test_admin_can_verify_and_approve_application(): void
    {
        // Setup submitted application
        $application = JobApplication::create([
            'user_id' => $this->applicant->id,
            'recruitment_period_id' => $this->period->id,
            'job_vacancy_id' => $this->vacancy->id,
            'application_number' => 'RSF-2026-001',
            'status' => JobApplicationStatus::SUBMITTED,
            'submitted_at' => now(),
            'locked_at' => now(),
        ]);

        $response = $this->actingAs($this->admin)->post(route('admin.applications.verify', $application), [
            'action' => 'approve',
            'admin_note' => 'Dokumen lengkap dan terverifikasi.',
        ]);

        $response->assertSessionHasNoErrors();
        $application->refresh();

        $this->assertEquals(JobApplicationStatus::PASSED_ADMINISTRATION, $application->status);
        $this->assertNotNull($application->verified_at);
        $this->assertEquals($this->admin->id, $application->verified_by);
    }

    public function test_admin_can_reject_application_with_reason_and_applicant_acknowledges(): void
    {
        $application = JobApplication::create([
            'user_id' => $this->applicant->id,
            'recruitment_period_id' => $this->period->id,
            'job_vacancy_id' => $this->vacancy->id,
            'application_number' => 'RSF-2026-002',
            'status' => JobApplicationStatus::SUBMITTED,
            'submitted_at' => now(),
            'locked_at' => now(),
        ]);

        // Reject by Admin
        $response = $this->actingAs($this->admin)->post(route('admin.applications.verify', $application), [
            'action' => 'reject',
            'rejection_reason' => 'STR telah kedaluwarsa dan ijazah belum dilegalisir asli.',
        ]);

        $response->assertSessionHasNoErrors();
        $application->refresh();

        $this->assertEquals(JobApplicationStatus::REJECTED, $application->status);
        $this->assertEquals('STR telah kedaluwarsa dan ijazah belum dilegalisir asli.', $application->rejection_reason);

        // Applicant sees and acknowledges rejection
        $response = $this->actingAs($this->applicant)->post(route('portal.applications.acknowledge', $application));
        $response->assertRedirect(route('login'));
        $this->assertGuest();
        $this->assertDatabaseMissing('users', ['id' => $this->applicant->id]);
    }

    public function test_user_lifecycle_service_handles_deactivation_and_cleanup(): void
    {
        $lifecycleService = app(UserLifecycleService::class);

        // 1. Deactivate passed candidate (data preserved)
        $lifecycleService->deactivatePassedApplicant($this->applicant);
        $this->applicant->refresh();
        $this->assertFalse($this->applicant->is_active);
        $this->assertNotNull($this->applicant->deactivated_at);

        // 2. Reactivate
        $lifecycleService->reactivateApplicant($this->applicant);
        $this->applicant->refresh();
        $this->assertTrue($this->applicant->is_active);

        // 3. Cleanup rejected candidate
        $lifecycleService->cleanupRejectedApplicant($this->applicant);
        $this->assertDatabaseMissing('users', ['id' => $this->applicant->id]);
    }

    public function test_admin_dashboard_can_be_accessed(): void
    {
        $response = $this->actingAs($this->admin)->get(route('admin.dashboard'));

        $response->assertStatus(200);
        $response->assertSee('Dashboard Rekrutmen SDI');
        $response->assertSee('Total Pelamar');
    }

    public function test_admin_timelines_page_can_be_accessed(): void
    {
        $response = $this->actingAs($this->admin)->get(route('admin.timelines.index'));

        $response->assertStatus(200);
        $response->assertSee('Timeline & Jadwal Seleksi', false);
    }
}
