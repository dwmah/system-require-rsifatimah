<?php

namespace Tests\Feature;

use App\Enums\JobVacancyStatus;
use App\Enums\RecruitmentPeriodStatus;
use App\Models\JobVacancy;
use App\Models\RecruitmentPeriod;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicVacancyAutoFilterTest extends TestCase
{
    use RefreshDatabase;

    protected RecruitmentPeriod $period;

    protected JobVacancy $vacancy1;

    protected JobVacancy $vacancy2;

    protected function setUp(): void
    {
        parent::setUp();

        $this->period = RecruitmentPeriod::create([
            'name' => 'Rekrutmen Tenaga Kesehatan 2026',
            'code' => 'RSIF-2026-TEST',
            'registration_start_at' => now()->subDays(5),
            'registration_end_at' => now()->addDays(20),
            'status' => RecruitmentPeriodStatus::OPEN,
            'is_active' => true,
        ]);

        $this->vacancy1 = JobVacancy::create([
            'recruitment_period_id' => $this->period->id,
            'code' => 'POS-IGD-01',
            'title' => 'Perawat IGD',
            'unit' => 'Instalasi Gawat Darurat',
            'employment_type' => 'PKWT',
            'quota' => 3,
            'status' => JobVacancyStatus::OPEN,
            'is_active' => true,
            'open_at' => now()->subDays(2),
            'close_at' => now()->addDays(10),
            'location' => 'RSI Fatimah Banyuwangi',
        ]);

        $this->vacancy2 = JobVacancy::create([
            'recruitment_period_id' => $this->period->id,
            'code' => 'POS-RAD-02',
            'title' => 'Radiografer',
            'unit' => 'Radiologi',
            'employment_type' => 'Tetap',
            'quota' => 2,
            'status' => JobVacancyStatus::OPEN,
            'is_active' => true,
            'open_at' => now()->subDays(2),
            'close_at' => now()->addDays(10),
            'location' => 'RSI Fatimah Banyuwangi',
        ]);
    }

    public function test_vacancies_page_does_not_contain_terapkan_filter_button(): void
    {
        $response = $this->get(route('vacancies.index'));

        $response->assertStatus(200);
        $response->assertDontSee('Terapkan Filter');
        $response->assertDontSee('Filter otomatis aktif');
        $response->assertDontSee('Reset Filter');
        $response->assertDontSee('Memperbarui formasi');
        $response->assertDontSee('id="filter-count"', false);
        $response->assertSee('id="search-input"', false);
        $response->assertSee('id="unit-select"', false);
        $response->assertSee('id="type-select"', false);
        $response->assertSee('id="edu-select"', false);
    }

    public function test_automatic_filtering_by_keyword(): void
    {
        $response = $this->get(route('vacancies.index', ['q' => 'Radiografer']));

        $response->assertStatus(200);
        $response->assertSee('Radiografer');
        $response->assertDontSee('Perawat IGD');
        $response->assertDontSee('Reset Filter');
    }

    public function test_automatic_filtering_by_unit(): void
    {
        $response = $this->get(route('vacancies.index', ['unit' => 'Instalasi Gawat Darurat']));

        $response->assertStatus(200);
        $response->assertSee('Perawat IGD');
        $response->assertDontSee('Radiografer');
        $response->assertDontSee('Reset Filter');
    }

    public function test_automatic_filtering_by_employment_type(): void
    {
        $response = $this->get(route('vacancies.index', ['employment_type' => 'Tetap']));

        $response->assertStatus(200);
        $response->assertSee('Radiografer');
        $response->assertDontSee('Perawat IGD');
    }
}
