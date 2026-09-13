<?php

namespace Tests\Feature;

use App\Enums\JobVacancyStatus;
use App\Enums\RecruitmentPeriodStatus;
use App\Models\JobVacancy;
use App\Models\RecruitmentPeriod;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminVacancyManagementTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected RecruitmentPeriod $period;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create([
            'name' => 'Admin SDI RSI Fatimah',
            'email' => 'admin@rsifatimah.com',
            'role' => User::ROLE_ADMIN,
            'is_active' => true,
        ]);

        $this->period = RecruitmentPeriod::create([
            'name' => 'Rekrutmen RSI Fatimah 2026',
            'code' => 'RSIF-2026',
            'registration_start_at' => now()->subDays(5),
            'registration_end_at' => now()->addDays(30),
            'status' => RecruitmentPeriodStatus::OPEN,
        ]);
    }

    public function test_admin_can_view_vacancies_create_page_with_date_fields(): void
    {
        $response = $this->actingAs($this->admin)->get(route('admin.vacancies.create'));

        $response->assertStatus(200);
        $response->assertSee('Tanggal Mulai Pendaftaran');
        $response->assertSee('Batas Akhir Pendaftaran (Deadline)');
        $response->assertSee('name="open_at"', false);
        $response->assertSee('name="close_at"', false);
    }

    public function test_admin_can_create_vacancy_with_custom_open_and_close_dates(): void
    {
        $openDate = '2026-10-01';
        $closeDate = '2026-10-25';

        $response = $this->actingAs($this->admin)->post(route('admin.vacancies.store'), [
            'recruitment_period_id' => $this->period->id,
            'title' => 'Radiografer Madya',
            'department' => 'Instalasi Radiologi',
            'formation_type' => 'Tenaga Penunjang Medis',
            'quota' => 3,
            'open_at' => $openDate,
            'close_at' => $closeDate,
            'description' => 'Dibutuhkan radiografer dengan kualifikasi D3/D4 Radiologi.',
            'is_published' => 1,
        ]);

        $response->assertRedirect(route('admin.vacancies.index'));
        $this->assertDatabaseHas('job_vacancies', [
            'title' => 'Radiografer Madya',
            'unit' => 'Instalasi Radiologi',
            'employment_type' => 'Tenaga Penunjang Medis',
            'quota' => 3,
            'status' => JobVacancyStatus::OPEN->value,
        ]);

        $vacancy = JobVacancy::where('title', 'Radiografer Madya')->firstOrFail();
        $this->assertEquals('2026-10-01', $vacancy->open_at->format('Y-m-d'));
        $this->assertEquals('2026-10-25', $vacancy->close_at->format('Y-m-d'));
    }

    public function test_admin_can_edit_vacancy_and_update_dates(): void
    {
        $vacancy = JobVacancy::create([
            'recruitment_period_id' => $this->period->id,
            'code' => 'VAC-TEST-123',
            'title' => 'Dokter Umum',
            'unit' => 'Pelayanan Medis',
            'employment_type' => 'Tenaga Medis',
            'location' => 'RSI Fatimah Banyuwangi',
            'quota' => 2,
            'description' => 'Deskripsi dokter umum.',
            'open_at' => Carbon::parse('2026-09-01'),
            'close_at' => Carbon::parse('2026-09-30'),
            'status' => JobVacancyStatus::OPEN,
        ]);

        $editPage = $this->actingAs($this->admin)->get(route('admin.vacancies.edit', $vacancy));
        $editPage->assertStatus(200);
        $editPage->assertSee('2026-09-01');
        $editPage->assertSee('2026-09-30');

        $response = $this->actingAs($this->admin)->put(route('admin.vacancies.update', $vacancy), [
            'recruitment_period_id' => $this->period->id,
            'title' => 'Dokter Umum IGD',
            'department' => 'Instalasi Gawat Darurat',
            'formation_type' => 'Tenaga Medis',
            'quota' => 4,
            'open_at' => '2026-09-15',
            'close_at' => '2026-10-15',
            'description' => 'Deskripsi dokter umum IGD update.',
            'is_published' => 1,
        ]);

        $response->assertRedirect(route('admin.vacancies.show', $vacancy));
        $vacancy->refresh();

        $this->assertEquals('Dokter Umum IGD', $vacancy->title);
        $this->assertEquals('2026-09-15', $vacancy->open_at->format('Y-m-d'));
        $this->assertEquals('2026-10-15', $vacancy->close_at->format('Y-m-d'));
    }

    public function test_close_at_must_be_after_or_equal_to_open_at(): void
    {
        $response = $this->actingAs($this->admin)->post(route('admin.vacancies.store'), [
            'recruitment_period_id' => $this->period->id,
            'title' => 'Apoteker',
            'department' => 'Instalasi Farmasi',
            'formation_type' => 'Tenaga Kefarmasian',
            'quota' => 1,
            'open_at' => '2026-10-20',
            'close_at' => '2026-10-10', // Invalid: close_at is before open_at
            'description' => 'Dibutuhkan apoteker.',
        ]);

        $response->assertSessionHasErrors(['close_at']);
    }
}
