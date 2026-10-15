<?php

namespace Tests\Feature;

use App\Enums\ApplicationStageStatus;
use App\Enums\JobApplicationStatus;
use App\Enums\JobVacancyStatus;
use App\Enums\RecruitmentPeriodStatus;
use App\Models\JobApplication;
use App\Models\JobVacancy;
use App\Models\RecruitmentPeriod;
use App\Models\User;
use App\Models\VacancySelectionStage;
use App\Services\Recruitment\ApplicationVerificationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminVacancyQuotaUnlimitedTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected RecruitmentPeriod $period;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create([
            'role' => 'admin',
            'is_active' => true,
        ]);

        $this->period = RecruitmentPeriod::create([
            'name' => 'Rekrutmen Medis 2026',
            'code' => 'RSIF-TEST-2026',
            'registration_start_at' => now()->subMonth(),
            'registration_end_at' => now()->addMonth(),
            'status' => RecruitmentPeriodStatus::OPEN,
        ]);
    }

    public function test_admin_create_page_defaults_to_disabled_quota(): void
    {
        $response = $this->actingAs($this->admin)->get(route('admin.vacancies.create'));

        $response->assertStatus(200);
        $response->assertSee('name="has_quota"', false);
        $response->assertSee('name="quota"', false);
        $response->assertSee('Unlimited (Default)');
    }

    public function test_admin_can_create_vacancy_without_quota_default_disabled(): void
    {
        $response = $this->actingAs($this->admin)->post(route('admin.vacancies.store'), [
            'recruitment_period_id' => $this->period->id,
            'title' => 'Staf Rekam Medis',
            'department' => 'Rekam Medis',
            'formation_type' => 'Tenaga Penunjang Medis',
            'has_quota' => 0,
            'quota' => null,
            'description' => 'Dibutuhkan staf rekam medis tanpa batasan kuota kaku.',
            'is_published' => 1,
        ]);

        $response->assertRedirect(route('admin.vacancies.index'));

        $vacancy = JobVacancy::where('title', 'Staf Rekam Medis')->firstOrFail();
        $this->assertNull($vacancy->quota);
        $this->assertFalse($vacancy->hasQuotaLimit());
        $this->assertEquals('Tidak Dibatasi', $vacancy->quota_label);
    }

    public function test_admin_can_create_vacancy_with_enabled_quota(): void
    {
        $response = $this->actingAs($this->admin)->post(route('admin.vacancies.store'), [
            'recruitment_period_id' => $this->period->id,
            'title' => 'Perawat ICU',
            'department' => 'Keperawatan',
            'formation_type' => 'Tenaga Keperawatan',
            'has_quota' => 1,
            'quota' => 5,
            'description' => 'Dibutuhkan perawat ICU sebanyak 5 orang.',
            'is_published' => 1,
        ]);

        $response->assertRedirect(route('admin.vacancies.index'));

        $vacancy = JobVacancy::where('title', 'Perawat ICU')->firstOrFail();
        $this->assertEquals(5, $vacancy->quota);
        $this->assertTrue($vacancy->hasQuotaLimit());
        $this->assertEquals('5 Orang', $vacancy->quota_label);
    }

    public function test_admin_can_toggle_existing_vacancy_quota_from_limited_to_unlimited(): void
    {
        $vacancy = JobVacancy::create([
            'recruitment_period_id' => $this->period->id,
            'code' => 'VAC-TEST-LIMITED',
            'title' => 'Bidan Pelaksana',
            'unit' => 'Kebidanan',
            'employment_type' => 'Tenaga Kebidanan',
            'location' => 'RSI Fatimah Banyuwangi',
            'quota' => 3,
            'description' => 'Deskripsi bidan.',
            'open_at' => now(),
            'close_at' => now()->addMonth(),
            'status' => JobVacancyStatus::OPEN,
        ]);

        $this->assertTrue($vacancy->hasQuotaLimit());

        $response = $this->actingAs($this->admin)->put(route('admin.vacancies.update', $vacancy), [
            'recruitment_period_id' => $this->period->id,
            'title' => 'Bidan Pelaksana',
            'department' => 'Kebidanan',
            'formation_type' => 'Tenaga Kebidanan',
            'has_quota' => 0,
            'quota' => null,
            'description' => 'Deskripsi bidan diperbarui ke unlimited.',
            'is_published' => 1,
        ]);

        $response->assertRedirect(route('admin.vacancies.show', $vacancy));
        $vacancy->refresh();

        $this->assertNull($vacancy->quota);
        $this->assertFalse($vacancy->hasQuotaLimit());
        $this->assertEquals('Tidak Dibatasi', $vacancy->quota_label);
    }

    public function test_unlimited_vacancy_allows_accepting_candidates_without_quota_restriction(): void
    {
        $vacancy = JobVacancy::create([
            'recruitment_period_id' => $this->period->id,
            'code' => 'VAC-TEST-UNLIMITED',
            'title' => 'Fisioterapis',
            'unit' => 'Rehabilitasi Medik',
            'employment_type' => 'Tenaga Penunjang Medis',
            'location' => 'RSI Fatimah Banyuwangi',
            'quota' => null,
            'description' => 'Deskripsi fisioterapis.',
            'open_at' => now(),
            'close_at' => now()->addMonth(),
            'status' => JobVacancyStatus::OPEN,
        ]);

        $finalStage = VacancySelectionStage::create([
            'job_vacancy_id' => $vacancy->id,
            'name' => 'Wawancara Akhir',
            'sort_order' => 1,
        ]);

        // Buat 3 aplikasi dan nyatakan diterima secara berurutan
        $service = app(ApplicationVerificationService::class);

        for ($i = 1; $i <= 3; $i++) {
            $user = User::factory()->create();
            $application = JobApplication::create([
                'user_id' => $user->id,
                'job_vacancy_id' => $vacancy->id,
                'recruitment_period_id' => $this->period->id,
                'status' => JobApplicationStatus::IN_SELECTION,
            ]);

            $service->updateSelectionStage(
                $application,
                $finalStage,
                ApplicationStageStatus::PASSED->value,
                'Kandidat diterima.'
            );

            $application->refresh();
            $this->assertEquals(JobApplicationStatus::ACCEPTED, $application->status);
        }

        $acceptedTotal = JobApplication::where('job_vacancy_id', $vacancy->id)
            ->where('status', JobApplicationStatus::ACCEPTED)
            ->count();

        $this->assertEquals(3, $acceptedTotal);
    }
}
