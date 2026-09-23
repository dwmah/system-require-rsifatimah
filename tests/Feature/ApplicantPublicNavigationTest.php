<?php

namespace Tests\Feature;

use App\Enums\JobVacancyStatus;
use App\Enums\RecruitmentPeriodStatus;
use App\Models\JobVacancy;
use App\Models\RecruitmentPeriod;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ApplicantPublicNavigationTest extends TestCase
{
    use RefreshDatabase;

    protected User $applicant;

    protected RecruitmentPeriod $period;

    protected JobVacancy $vacancy;

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

        $this->vacancy = JobVacancy::create([
            'recruitment_period_id' => $this->period->id,
            'code' => 'POS-2026-IGD-01',
            'title' => 'Perawat Instalasi Gawat Darurat (IGD)',
            'unit' => 'Instalasi Gawat Darurat',
            'employment_type' => 'PKWT',
            'quota' => 5,
            'status' => JobVacancyStatus::OPEN,
            'is_active' => true,
            'open_at' => now()->subDays(3),
            'close_at' => now()->addDays(15),
            'location' => 'RSI Fatimah Banyuwangi',
        ]);

        $this->applicant = User::factory()->create([
            'name' => 'Dawam Ahmadi',
            'email' => 'ahmadidawam48@gmail.com',
            'role' => User::ROLE_APPLICANT,
            'is_active' => true,
        ]);
    }

    public function test_authenticated_applicant_sees_applicant_sidebar_on_vacancies_page(): void
    {
        $response = $this->actingAs($this->applicant)->get(route('vacancies.index'));

        $response->assertStatus(200);
        $response->assertSee('Portal Pelamar');
        $response->assertSee('Beranda Portal');
        $response->assertSee('Lamaran Saya');
        $response->assertSee('Profil Saya');
        $response->assertSee('Notifikasi');
        $response->assertSee('Lowongan Pekerjaan');
        $response->assertSee('Panduan & Info', false);
        $response->assertSee('Pusat Informasi SDM');
    }

    public function test_authenticated_applicant_sees_applicant_sidebar_on_vacancy_detail_page(): void
    {
        $response = $this->actingAs($this->applicant)->get(route('vacancies.show', $this->vacancy));

        $response->assertStatus(200);
        $response->assertSee('Portal Pelamar');
        $response->assertSee('Beranda Portal');
        $response->assertSee('Lamaran Saya');
        $response->assertSee('Profil Saya');
        $response->assertSee('Pusat Informasi SDM');
    }

    public function test_authenticated_applicant_sees_applicant_sidebar_on_guides_page(): void
    {
        $response = $this->actingAs($this->applicant)->get(route('guides.flow'));

        $response->assertStatus(200);
        $response->assertSee('Portal Pelamar');
        $response->assertSee('Beranda Portal');
        $response->assertSee('Lamaran Saya');
        $response->assertSee('Profil Saya');
        $response->assertSee('Pusat Informasi SDM');
    }

    public function test_guest_sees_public_sidebar_on_vacancies_page(): void
    {
        $response = $this->get(route('vacancies.index'));

        $response->assertStatus(200);
        $response->assertDontSee('Portal Pelamar');
        $response->assertDontSee('Pelamar SDM Aktif');
        $response->assertSee('Pusat Informasi SDM');
        $response->assertSee('Masuk');
    }

    public function test_guest_sees_already_have_application_continue_link_on_vacancy_detail_page(): void
    {
        $response = $this->get(route('vacancies.show', $this->vacancy));

        $response->assertStatus(200);
        $response->assertSee('Sudah punya lamaran?');
        $response->assertSee('Lanjutkan');
        $response->assertDontSee('Sudah punya akun?');
    }
}
