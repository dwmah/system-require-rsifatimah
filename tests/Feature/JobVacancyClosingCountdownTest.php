<?php

namespace Tests\Feature;

use App\Enums\JobVacancyStatus;
use App\Enums\RecruitmentPeriodStatus;
use App\Models\JobVacancy;
use App\Models\RecruitmentPeriod;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class JobVacancyClosingCountdownTest extends TestCase
{
    use RefreshDatabase;

    private RecruitmentPeriod $period;

    protected function setUp(): void
    {
        parent::setUp();

        $this->period = RecruitmentPeriod::create([
            'name' => 'Periode Rekrutmen Medis 2026',
            'code' => 'TEST-2026',
            'registration_start_at' => now()->subDays(5),
            'registration_end_at' => now()->addDays(20),
            'status' => RecruitmentPeriodStatus::OPEN,
        ]);
    }

    public function test_is_closing_soon_returns_correct_boolean(): void
    {
        $now = Carbon::parse('2026-09-24 10:00:00');

        $vacancyClosingSoon = new JobVacancy([
            'close_at' => Carbon::parse('2026-09-25 18:00:00'), // 32 jam kemudian (<= 72 jam)
        ]);

        $vacancyNotClosingSoon = new JobVacancy([
            'close_at' => Carbon::parse('2026-09-30 10:00:00'), // 6 hari kemudian (> 72 jam)
        ]);

        $vacancyAlreadyClosed = new JobVacancy([
            'close_at' => Carbon::parse('2026-09-24 09:00:00'), // 1 jam yang lalu
        ]);

        $this->assertTrue($vacancyClosingSoon->isClosingSoon($now));
        $this->assertFalse($vacancyNotClosingSoon->isClosingSoon($now));
        $this->assertFalse($vacancyAlreadyClosed->isClosingSoon($now));
    }

    public function test_get_closing_remaining_text_formats_days_and_hours_cleanly(): void
    {
        $now = Carbon::parse('2026-09-24 10:00:00');

        // Kasus 1: Sisa 1 hari 8 jam (seperti 1.34339... hari)
        $vacancy1 = new JobVacancy(['close_at' => Carbon::parse('2026-09-25 18:00:00')]);
        $this->assertSame('1 hari 8 jam', $vacancy1->getClosingRemainingText($now));

        // Kasus 2: Sisa tepat 2 hari
        $vacancy2 = new JobVacancy(['close_at' => Carbon::parse('2026-09-26 10:00:00')]);
        $this->assertSame('2 hari', $vacancy2->getClosingRemainingText($now));

        // Kasus 3: Sisa 5 jam 30 menit
        $vacancy3 = new JobVacancy(['close_at' => Carbon::parse('2026-09-24 15:30:00')]);
        $this->assertSame('5 jam 30 menit', $vacancy3->getClosingRemainingText($now));

        // Kasus 4: Sisa kurang dari 1 jam (misal 45 menit)
        $vacancy4 = new JobVacancy(['close_at' => Carbon::parse('2026-09-24 10:45:00')]);
        $this->assertSame('45 menit', $vacancy4->getClosingRemainingText($now));

        // Kasus 5: Sudah lewat / kedaluwarsa
        $vacancy5 = new JobVacancy(['close_at' => Carbon::parse('2026-09-24 09:00:00')]);
        $this->assertNull($vacancy5->getClosingRemainingText($now));
    }

    public function test_vacancy_card_component_renders_formatted_remaining_time(): void
    {
        Carbon::setTestNow('2026-09-24 10:00:00');

        // Lowongan yang ditutup dalam 32 jam (1 hari 8 jam)
        $vacancy = JobVacancy::create([
            'recruitment_period_id' => $this->period->id,
            'code' => 'VAC-TEST-01',
            'title' => 'Perawat Gawat Darurat',
            'unit' => 'Instalasi Gawat Darurat',
            'employment_type' => 'PKWT',
            'quota' => 3,
            'location' => 'RSI Fatimah Banyuwangi',
            'open_at' => now()->subDays(5),
            'close_at' => now()->addHours(32),
            'status' => JobVacancyStatus::OPEN,
        ]);

        $rendered = $this->blade('<x-vacancy.card :vacancy="$vacancy" />', ['vacancy' => $vacancy]);

        $this->assertStringContainsString('Segera Ditutup (1 hari 8 jam)', $rendered);
        $this->assertStringNotContainsString('1.34', $rendered);

        Carbon::setTestNow();
    }

    public function test_vacancy_show_detail_renders_formatted_remaining_time(): void
    {
        Carbon::setTestNow('2026-09-24 10:00:00');

        $vacancy = JobVacancy::create([
            'recruitment_period_id' => $this->period->id,
            'code' => 'VAC-TEST-02',
            'title' => 'Dokter Spesialis Bedah',
            'unit' => 'Instalasi Bedah Sentral',
            'employment_type' => 'Tetap',
            'quota' => 1,
            'location' => 'RSI Fatimah Banyuwangi',
            'open_at' => now()->subDays(5),
            'close_at' => now()->addHours(32),
            'status' => JobVacancyStatus::OPEN,
        ]);

        $response = $this->get(route('vacancies.show', $vacancy));

        $response->assertStatus(200);
        $response->assertSee('Segera Ditutup (Sisa 1 hari 8 jam)');
        $response->assertDontSee('1.34');

        Carbon::setTestNow();
    }
}
