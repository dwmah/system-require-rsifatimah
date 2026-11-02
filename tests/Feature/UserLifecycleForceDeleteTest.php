<?php

namespace Tests\Feature;

use App\Enums\JobApplicationStatus;
use App\Enums\JobVacancyStatus;
use App\Enums\RecruitmentPeriodStatus;
use App\Models\ApplicantProfile;
use App\Models\ApplicationDocument;
use App\Models\JobApplication;
use App\Models\JobVacancy;
use App\Models\RecruitmentPeriod;
use App\Models\User;
use App\Models\VacancyDocumentRequirement;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class UserLifecycleForceDeleteTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected User $applicant;

    protected JobApplication $application;

    protected string $docPath;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('applicant_documents');
        Storage::fake('local');

        $this->admin = User::factory()->create([
            'role' => 'admin',
            'is_active' => true,
        ]);

        $this->applicant = User::factory()->create([
            'role' => 'pelamar',
            'is_active' => true,
        ]);

        $profile = ApplicantProfile::create([
            'user_id' => $this->applicant->id,
            'nik' => '3510112233445566',
            'gender' => 'L',
            'birth_place' => 'Banyuwangi',
            'birth_date' => '1998-05-15',
            'phone' => '081234567890',
            'address' => 'Jl. RS Fatimah No. 1',
        ]);

        $period = RecruitmentPeriod::create([
            'name' => 'Periode 2026',
            'code' => 'PER-2026',
            'status' => RecruitmentPeriodStatus::OPEN,
            'registration_start_at' => now()->subDays(5),
            'registration_end_at' => now()->addDays(20),
        ]);

        $vacancy = JobVacancy::create([
            'recruitment_period_id' => $period->id,
            'code' => 'VAC-01',
            'title' => 'Staf IT',
            'unit' => 'IT',
            'employment_type' => 'PKWT',
            'location' => 'RSI Fatimah',
            'quota' => 2,
            'status' => JobVacancyStatus::OPEN,
            'open_at' => now()->subDays(5),
            'close_at' => now()->addDays(20),
        ]);

        $docReq = VacancyDocumentRequirement::create([
            'job_vacancy_id' => $vacancy->id,
            'name' => 'Curriculum Vitae (CV) & Portofolio',
            'is_required' => true,
            'allowed_mimes' => 'application/pdf',
            'max_size_kb' => 5120,
            'sort_order' => 1,
        ]);

        $this->application = JobApplication::create([
            'user_id' => $this->applicant->id,
            'job_vacancy_id' => $vacancy->id,
            'recruitment_period_id' => $period->id,
            'application_number' => 'APP-2026-0001',
            'status' => JobApplicationStatus::REJECTED,
            'rejected_at' => now()->subDay(),
            'rejection_reason' => 'Kualifikasi belum sesuai kriteria',
            'announcement_seen_at' => now()->subHours(5),
        ]);

        $this->docPath = "documents/{$this->application->id}/cv_portofolio.pdf";
        Storage::disk('applicant_documents')->put($this->docPath, 'dummy cv content');

        ApplicationDocument::create([
            'job_application_id' => $this->application->id,
            'vacancy_document_requirement_id' => $docReq->id,
            'original_name' => 'cv_portofolio.pdf',
            'stored_path' => $this->docPath,
            'mime_type' => 'application/pdf',
            'file_size' => 1024,
            'verification_status' => 'pending',
        ]);
    }

    public function test_admin_can_force_delete_soft_deleted_applicant(): void
    {
        // 1. Soft delete applicant first
        $this->applicant->delete();
        $this->assertSoftDeleted('users', ['id' => $this->applicant->id]);
        Storage::disk('applicant_documents')->assertExists($this->docPath);

        // 2. Admin performs force delete
        $response = $this->actingAs($this->admin)->delete(
            route('admin.lifecycle.force-delete', $this->applicant->id)
        );

        $response->assertRedirect();
        $response->assertSessionHas('success');

        // Verify permanently deleted from database
        $this->assertDatabaseMissing('users', ['id' => $this->applicant->id]);
        $this->assertDatabaseMissing('job_applications', ['id' => $this->application->id]);

        // Verify physical file was removed from storage
        Storage::disk('applicant_documents')->assertMissing($this->docPath);
    }
}
