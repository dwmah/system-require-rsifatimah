<?php

namespace Tests\Feature;

use App\Enums\RecruitmentPeriodStatus;
use App\Models\JobVacancy;
use App\Models\RecruitmentPeriod;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DefaultVacancyDocumentsTest extends TestCase
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
            'name' => 'Periode 2026',
            'code' => 'PER-2026',
            'status' => RecruitmentPeriodStatus::OPEN,
            'registration_start_at' => now()->subDays(2),
            'registration_end_at' => now()->addDays(20),
        ]);
    }

    public function test_creating_vacancy_without_custom_docs_includes_cv_and_portofolio_by_default(): void
    {
        $response = $this->actingAs($this->admin)->post(route('admin.vacancies.store'), [
            'recruitment_period_id' => $this->period->id,
            'title' => 'Staf Administrasi',
            'department' => 'Umum',
            'formation_type' => 'PKWT',
            'has_quota' => 1,
            'quota' => 3,
            'description' => 'Deskripsi pekerjaan staf administrasi',
            'is_published' => 1,
            'open_at' => now()->subDay()->format('Y-m-d'),
            'close_at' => now()->addDays(15)->format('Y-m-d'),
            'qualifications' => ['Lulusan D3/S1', 'Bisa komputer'],
        ]);

        $response->assertRedirect();

        $vacancy = JobVacancy::where('title', 'Staf Administrasi')->first();
        $this->assertNotNull($vacancy);

        $docNames = $vacancy->documentRequirements->pluck('name')->toArray();

        // CV dan Portofolio harus ada di dalam default dokumen
        $hasCv = false;
        $hasPortofolio = false;

        foreach ($docNames as $name) {
            if (str_contains(strtolower($name), 'cv') || str_contains(strtolower($name), 'curriculum vitae')) {
                $hasCv = true;
            }
            if (str_contains(strtolower($name), 'portofolio')) {
                $hasPortofolio = true;
            }
        }

        $this->assertTrue($hasCv, 'Default documents must include Curriculum Vitae (CV)');
        $this->assertTrue($hasPortofolio, 'Default documents must include Portofolio');
    }
}
