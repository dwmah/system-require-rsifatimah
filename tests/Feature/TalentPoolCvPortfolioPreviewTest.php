<?php

namespace Tests\Feature;

use App\Enums\JobApplicationStatus;
use App\Enums\JobVacancyStatus;
use App\Enums\RecruitmentPeriodStatus;
use App\Models\ApplicationDocument;
use App\Models\JobApplication;
use App\Models\JobVacancy;
use App\Models\RecruitmentPeriod;
use App\Models\User;
use App\Models\VacancyDocumentRequirement;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class TalentPoolCvPortfolioPreviewTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_preview_cv_and_portfolio_of_soft_deleted_applicant(): void
    {
        Storage::fake('applicant_documents');

        $admin = User::factory()->create([
            'role' => User::ROLE_ADMIN,
            'is_active' => true,
        ]);

        $applicant = User::factory()->create([
            'role' => User::ROLE_APPLICANT,
            'is_active' => false,
        ]);

        $period = RecruitmentPeriod::create([
            'name' => 'Periode 2026',
            'code' => 'PER-2026',
            'registration_start_at' => now()->subDays(10),
            'registration_end_at' => now()->subDays(2),
            'status' => RecruitmentPeriodStatus::COMPLETED,
        ]);

        $vacancy = JobVacancy::create([
            'recruitment_period_id' => $period->id,
            'code' => 'VAC-TEST-TP',
            'title' => 'Staf IT',
            'unit' => 'IT',
            'employment_type' => 'PKWT',
            'location' => 'RSI Fatimah',
            'quota' => 1,
            'status' => JobVacancyStatus::CLOSED,
            'open_at' => now()->subDays(10),
            'close_at' => now()->subDays(2),
        ]);

        $cvReq = VacancyDocumentRequirement::create([
            'job_vacancy_id' => $vacancy->id,
            'name' => 'Curriculum Vitae (CV)',
            'is_required' => true,
            'allowed_mimes' => 'application/pdf',
            'max_size_kb' => 5120,
            'sort_order' => 1,
        ]);

        $portoReq = VacancyDocumentRequirement::create([
            'job_vacancy_id' => $vacancy->id,
            'name' => 'Portofolio Karya',
            'is_required' => true,
            'allowed_mimes' => 'application/pdf',
            'max_size_kb' => 5120,
            'sort_order' => 2,
        ]);

        $application = JobApplication::create([
            'user_id' => $applicant->id,
            'job_vacancy_id' => $vacancy->id,
            'recruitment_period_id' => $period->id,
            'application_number' => 'APP-TP-001',
            'status' => JobApplicationStatus::REJECTED,
            'submitted_at' => now()->subDays(5),
        ]);

        Storage::disk('applicant_documents')->put('cv_sample.pdf', 'dummy cv content');
        Storage::disk('applicant_documents')->put('porto_sample.pdf', 'dummy porto content');

        $cvDoc = ApplicationDocument::create([
            'job_application_id' => $application->id,
            'vacancy_document_requirement_id' => $cvReq->id,
            'original_name' => 'cv_pelamar.pdf',
            'stored_path' => 'cv_sample.pdf',
            'mime_type' => 'application/pdf',
            'file_size' => 1024,
        ]);

        $portoDoc = ApplicationDocument::create([
            'job_application_id' => $application->id,
            'vacancy_document_requirement_id' => $portoReq->id,
            'original_name' => 'portofolio_pelamar.pdf',
            'stored_path' => 'porto_sample.pdf',
            'mime_type' => 'application/pdf',
            'file_size' => 2048,
        ]);

        // Soft delete pelamar (masuk talent pool)
        $applicant->delete();
        $this->assertTrue($applicant->trashed());

        // Admin melakukan preview CV
        $responseCv = $this->actingAs($admin)->get(route('documents.preview', $cvDoc));
        $responseCv->assertStatus(200);

        // Admin melakukan preview Portofolio
        $responsePorto = $this->actingAs($admin)->get(route('documents.preview', $portoDoc));
        $responsePorto->assertStatus(200);
    }
}
