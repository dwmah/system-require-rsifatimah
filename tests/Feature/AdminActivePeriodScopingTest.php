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

class AdminActivePeriodScopingTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_applications_and_dashboard_only_show_active_period_data_by_default(): void
    {
        $admin = User::factory()->create([
            'role' => User::ROLE_ADMIN,
            'is_active' => true,
        ]);

        // 1. Periode Lama (Sudah selesai)
        $oldPeriod = RecruitmentPeriod::create([
            'name' => 'Rekrutmen Gelombang 1 2026',
            'code' => 'PER-GEL-1',
            'registration_start_at' => now()->subMonths(2),
            'registration_end_at' => now()->subMonth(),
            'status' => RecruitmentPeriodStatus::COMPLETED,
        ]);

        $oldVacancy = JobVacancy::create([
            'recruitment_period_id' => $oldPeriod->id,
            'code' => 'VAC-OLD-01',
            'title' => 'Perawat IGD Gel 1',
            'unit' => 'Pelayanan Medis',
            'employment_type' => 'PKWT',
            'location' => 'RSI Fatimah',
            'quota' => 2,
            'status' => JobVacancyStatus::CLOSED,
            'open_at' => now()->subMonths(2),
            'close_at' => now()->subMonth(),
        ]);

        $oldApplicant = User::factory()->create(['name' => 'Pelamar Lama', 'role' => User::ROLE_APPLICANT]);
        $oldApplication = JobApplication::create([
            'user_id' => $oldApplicant->id,
            'job_vacancy_id' => $oldVacancy->id,
            'recruitment_period_id' => $oldPeriod->id,
            'application_number' => 'APP-OLD-999',
            'status' => JobApplicationStatus::ACCEPTED,
            'submitted_at' => now()->subMonths(2),
        ]);

        // 2. Periode Baru (Aktif / OPEN)
        $activePeriod = RecruitmentPeriod::create([
            'name' => 'Rekrutmen Gelombang 2 2026',
            'code' => 'PER-GEL-2',
            'registration_start_at' => now()->subDays(2),
            'registration_end_at' => now()->addDays(10),
            'status' => RecruitmentPeriodStatus::OPEN,
        ]);

        $activeVacancy = JobVacancy::create([
            'recruitment_period_id' => $activePeriod->id,
            'code' => 'VAC-NEW-01',
            'title' => 'Radiografer Gel 2',
            'unit' => 'Penunjang Medis',
            'employment_type' => 'PKWT',
            'location' => 'RSI Fatimah',
            'quota' => 1,
            'status' => JobVacancyStatus::OPEN,
            'open_at' => now()->subDays(2),
            'close_at' => now()->addDays(10),
        ]);

        $newApplicant = User::factory()->create(['name' => 'Pelamar Baru', 'role' => User::ROLE_APPLICANT]);
        $newApplication = JobApplication::create([
            'user_id' => $newApplicant->id,
            'job_vacancy_id' => $activeVacancy->id,
            'recruitment_period_id' => $activePeriod->id,
            'application_number' => 'APP-NEW-111',
            'status' => JobApplicationStatus::SUBMITTED,
            'submitted_at' => now()->subDay(),
        ]);

        // Request ke Admin Applications Index (Default: harus otomatis terfilter ke active period)
        $response = $this->actingAs($admin)->get(route('admin.applications.index'));
        $response->assertStatus(200);

        // Hanya pelamar dari periode aktif yang muncul
        $response->assertSee('APP-NEW-111');
        $response->assertSee('Pelamar Baru');
        $response->assertDontSee('APP-OLD-999');
        $response->assertDontSee('Pelamar Lama');

        // Request ke Admin Dashboard (Metrik lamaran masuk harus sesuai periode aktif)
        $dashResponse = $this->actingAs($admin)->get(route('admin.dashboard'));
        $dashResponse->assertStatus(200);
        $dashResponse->assertSee($activePeriod->name);
    }
}
