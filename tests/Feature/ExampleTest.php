<?php

namespace Tests\Feature;

use App\Enums\JobVacancyStatus;
use App\Enums\RecruitmentPeriodStatus;
use App\Models\JobVacancy;
use App\Models\RecruitmentPeriod;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Memastikan halaman utama aplikasi merespons dengan status 200 saat ada rekrutmen aktif.
     */
    public function test_the_application_returns_a_successful_response(): void
    {
        $period = RecruitmentPeriod::create([
            'name' => 'Periode Uji',
            'code' => 'TEST-01',
            'registration_start_at' => now()->subDay(),
            'registration_end_at' => now()->addDays(5),
            'status' => RecruitmentPeriodStatus::OPEN,
        ]);

        JobVacancy::create([
            'recruitment_period_id' => $period->id,
            'code' => 'VAC-TEST',
            'title' => 'Lowongan Uji',
            'unit' => 'Unit Uji',
            'employment_type' => 'PKWT',
            'quota' => 1,
            'location' => 'Banyuwangi',
            'open_at' => now()->subDay(),
            'close_at' => now()->addDays(5),
            'status' => JobVacancyStatus::OPEN,
        ]);

        $response = $this->get('/');

        $response->assertStatus(200);
    }
}
