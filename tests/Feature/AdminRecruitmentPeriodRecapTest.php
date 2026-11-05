<?php

namespace Tests\Feature;

use App\Enums\JobApplicationStatus;
use App\Enums\JobVacancyStatus;
use App\Enums\RecruitmentPeriodStatus;
use App\Models\JobApplication;
use App\Models\JobVacancy;
use App\Models\RecruitmentPeriod;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminRecruitmentPeriodRecapTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_access_recap_index_and_view_all_periods_summary(): void
    {
        $admin = User::factory()->create([
            'role' => User::ROLE_ADMIN,
            'is_active' => true,
        ]);

        $pastPeriod = RecruitmentPeriod::create([
            'name' => 'Rekrutmen Batch 1 2025',
            'code' => 'PER-2025-1',
            'registration_start_at' => now()->subYear(),
            'registration_end_at' => now()->subYear()->addMonth(),
            'status' => RecruitmentPeriodStatus::COMPLETED,
        ]);

        $activePeriod = RecruitmentPeriod::create([
            'name' => 'Rekrutmen Batch 1 2026',
            'code' => 'PER-2026-1',
            'registration_start_at' => now()->subDays(5),
            'registration_end_at' => now()->addDays(15),
            'status' => RecruitmentPeriodStatus::OPEN,
        ]);

        $vacancyPast = JobVacancy::create([
            'recruitment_period_id' => $pastPeriod->id,
            'code' => 'VAC-PST-1',
            'title' => 'Perawat 2025',
            'unit' => 'Keperawatan',
            'employment_type' => 'PKWT',
            'location' => 'RSI Fatimah',
            'quota' => 2,
            'status' => JobVacancyStatus::CLOSED,
            'open_at' => now()->subYear(),
            'close_at' => now()->subYear()->addMonth(),
        ]);

        $applicant = User::factory()->create(['role' => User::ROLE_APPLICANT]);
        JobApplication::create([
            'user_id' => $applicant->id,
            'job_vacancy_id' => $vacancyPast->id,
            'recruitment_period_id' => $pastPeriod->id,
            'application_number' => 'APP-PST-001',
            'status' => JobApplicationStatus::ACCEPTED,
            'submitted_at' => now()->subYear(),
        ]);

        $response = $this->actingAs($admin)->get(route('admin.recap.index'));
        $response->assertStatus(200);
        $response->assertSee('Rekap Data Rekrutmen per Periode');
        $response->assertSee('Rekrutmen Batch 1 2025');
        $response->assertSee('Rekrutmen Batch 1 2026');
    }

    public function test_admin_can_view_recap_details_for_specific_period(): void
    {
        $admin = User::factory()->create([
            'role' => User::ROLE_ADMIN,
            'is_active' => true,
        ]);

        $pastPeriod = RecruitmentPeriod::create([
            'name' => 'Rekrutmen Batch Khusus',
            'code' => 'PER-SPEC',
            'registration_start_at' => now()->subMonths(6),
            'registration_end_at' => now()->subMonths(5),
            'status' => RecruitmentPeriodStatus::COMPLETED,
        ]);

        $vacancy = JobVacancy::create([
            'recruitment_period_id' => $pastPeriod->id,
            'code' => 'VAC-SPEC-1',
            'title' => 'Apoteker',
            'unit' => 'Farmasi',
            'employment_type' => 'PKWT',
            'location' => 'RSI Fatimah',
            'quota' => 1,
            'status' => JobVacancyStatus::CLOSED,
            'open_at' => now()->subMonths(6),
            'close_at' => now()->subMonths(5),
        ]);

        $applicant = User::factory()->create([
            'name' => 'Calon Apoteker Handal',
            'role' => User::ROLE_APPLICANT,
        ]);

        JobApplication::create([
            'user_id' => $applicant->id,
            'job_vacancy_id' => $vacancy->id,
            'recruitment_period_id' => $pastPeriod->id,
            'application_number' => 'APP-SPEC-777',
            'status' => JobApplicationStatus::ACCEPTED,
            'submitted_at' => now()->subMonths(6),
        ]);

        $response = $this->actingAs($admin)->get(route('admin.recap.show', $pastPeriod));
        $response->assertStatus(200);
        $response->assertSee('Detail Rekap: Rekrutmen Batch Khusus');
        $response->assertSee('Calon Apoteker Handal');
        $response->assertSee('APP-SPEC-777');
    }

    public function test_admin_can_export_recap_data_for_period(): void
    {
        $admin = User::factory()->create([
            'role' => User::ROLE_ADMIN,
            'is_active' => true,
        ]);

        $pastPeriod = RecruitmentPeriod::create([
            'name' => 'Rekrutmen Periode Ekspor',
            'code' => 'PER-EXP',
            'registration_start_at' => now()->subMonths(4),
            'registration_end_at' => now()->subMonths(3),
            'status' => RecruitmentPeriodStatus::COMPLETED,
        ]);

        $response = $this->actingAs($admin)->get(route('admin.recap.export', $pastPeriod));
        $response->assertStatus(200);
        $this->assertTrue(
            str_contains($response->headers->get('content-disposition', ''), 'attachment') ||
            str_contains($response->headers->get('content-type', ''), 'spreadsheetml')
        );
    }
}
