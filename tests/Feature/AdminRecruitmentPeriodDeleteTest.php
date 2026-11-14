<?php

namespace Tests\Feature;

use App\Enums\ApplicationStageStatus;
use App\Enums\JobApplicationStatus;
use App\Enums\JobVacancyStatus;
use App\Enums\RecruitmentPeriodStatus;
use App\Models\ApplicationDocument;
use App\Models\ApplicationStageHistory;
use App\Models\JobApplication;
use App\Models\JobVacancy;
use App\Models\RecruitmentPeriod;
use App\Models\User;
use App\Models\VacancyDocumentRequirement;
use App\Models\VacancySelectionStage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AdminRecruitmentPeriodDeleteTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_safely_delete_completed_period_and_its_physical_files(): void
    {
        Storage::fake('applicant_documents');

        $admin = User::factory()->create([
            'role' => User::ROLE_ADMIN,
            'is_active' => true,
        ]);

        $period = RecruitmentPeriod::create([
            'name' => 'Rekrutmen Gelombang Lama',
            'code' => 'PER-OLD-DEL',
            'registration_start_at' => now()->subMonths(3),
            'registration_end_at' => now()->subMonths(2),
            'status' => RecruitmentPeriodStatus::COMPLETED,
        ]);

        $vacancy = JobVacancy::create([
            'recruitment_period_id' => $period->id,
            'code' => 'VAC-DEL-01',
            'title' => 'Staf Arsip',
            'unit' => 'Rekam Medis',
            'employment_type' => 'PKWT',
            'location' => 'RSI Fatimah',
            'quota' => 1,
            'status' => JobVacancyStatus::CLOSED,
            'open_at' => now()->subMonths(3),
            'close_at' => now()->subMonths(2),
        ]);

        $stage = VacancySelectionStage::create([
            'job_vacancy_id' => $vacancy->id,
            'name' => 'Seleksi Berkas',
            'stage_order' => 1,
            'start_date' => now()->subMonths(3),
            'end_date' => now()->subMonths(2),
        ]);

        $docReq = VacancyDocumentRequirement::create([
            'job_vacancy_id' => $vacancy->id,
            'name' => 'Curriculum Vitae',
            'is_required' => true,
            'allowed_mimes' => 'application/pdf',
            'max_size_kb' => 2048,
        ]);

        $applicant = User::factory()->create(['role' => User::ROLE_APPLICANT]);

        $application = JobApplication::create([
            'user_id' => $applicant->id,
            'job_vacancy_id' => $vacancy->id,
            'recruitment_period_id' => $period->id,
            'application_number' => 'APP-DEL-999',
            'status' => JobApplicationStatus::REJECTED,
            'current_stage_id' => $stage->id,
            'submitted_at' => now()->subMonths(3),
        ]);

        ApplicationStageHistory::create([
            'job_application_id' => $application->id,
            'vacancy_selection_stage_id' => $stage->id,
            'status' => ApplicationStageStatus::FAILED,
        ]);

        // Siapkan file dokumen fisik di storage disk
        $cvPath = 'documents/cv_test_period_del.pdf';
        Storage::disk('applicant_documents')->put($cvPath, 'fake pdf content');
        $this->assertTrue(Storage::disk('applicant_documents')->exists($cvPath));

        $doc = ApplicationDocument::create([
            'job_application_id' => $application->id,
            'vacancy_document_requirement_id' => $docReq->id,
            'original_name' => 'cv_pelamar.pdf',
            'stored_path' => $cvPath,
            'mime_type' => 'application/pdf',
            'file_size' => 1024,
        ]);

        // Admin melakukan penghapusan rekapan periode
        $response = $this->actingAs($admin)->delete(route('admin.recap.destroy', $period));

        $response->assertRedirect(route('admin.recap.index'));
        $response->assertSessionHas('success');

        // Pastikan record periode dan seluruh anak datanya terhapus di database
        $this->assertDatabaseMissing('recruitment_periods', ['id' => $period->id]);
        $this->assertDatabaseMissing('job_vacancies', ['id' => $vacancy->id]);
        $this->assertDatabaseMissing('vacancy_selection_stages', ['id' => $stage->id]);
        $this->assertDatabaseMissing('vacancy_document_requirements', ['id' => $docReq->id]);
        $this->assertDatabaseMissing('job_applications', ['id' => $application->id]);
        $this->assertDatabaseMissing('application_stage_histories', ['job_application_id' => $application->id]);
        $this->assertDatabaseMissing('application_documents', ['id' => $doc->id]);

        // Pastikan file fisik di disk storage benar-benar terhapus
        $this->assertFalse(Storage::disk('applicant_documents')->exists($cvPath));
    }

    public function test_admin_cannot_delete_active_open_recruitment_period(): void
    {
        $admin = User::factory()->create([
            'role' => User::ROLE_ADMIN,
            'is_active' => true,
        ]);

        $activePeriod = RecruitmentPeriod::create([
            'name' => 'Rekrutmen Sedang Berjalan',
            'code' => 'PER-ACT-DEL',
            'registration_start_at' => now()->subDay(),
            'registration_end_at' => now()->addDays(7),
            'status' => RecruitmentPeriodStatus::OPEN,
        ]);

        $response = $this->actingAs($admin)->delete(route('admin.recap.destroy', $activePeriod));

        $response->assertSessionHas('error');
        $this->assertDatabaseHas('recruitment_periods', ['id' => $activePeriod->id]);
    }

    public function test_applicant_cannot_delete_recruitment_period(): void
    {
        $applicant = User::factory()->create([
            'role' => User::ROLE_APPLICANT,
            'is_active' => true,
        ]);

        $period = RecruitmentPeriod::create([
            'name' => 'Rekrutmen Lama',
            'code' => 'PER-LMA-DEL',
            'registration_start_at' => now()->subMonths(5),
            'registration_end_at' => now()->subMonths(4),
            'status' => RecruitmentPeriodStatus::COMPLETED,
        ]);

        $response = $this->actingAs($applicant)->delete(route('admin.recap.destroy', $period));
        $response->assertStatus(403);
        $this->assertDatabaseHas('recruitment_periods', ['id' => $period->id]);
    }
}
