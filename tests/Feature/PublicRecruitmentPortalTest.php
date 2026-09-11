<?php

namespace Tests\Feature;

use App\Enums\JobVacancyStatus;
use App\Enums\RecruitmentPeriodStatus;
use App\Models\JobVacancy;
use App\Models\RecruitmentPeriod;
use App\Models\VacancyDocumentRequirement;
use App\Models\VacancyQualification;
use App\Models\VacancySelectionStage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicRecruitmentPortalTest extends TestCase
{
    use RefreshDatabase;

    private RecruitmentPeriod $period;

    protected function setUp(): void
    {
        parent::setUp();

        $this->period = RecruitmentPeriod::create([
            'name' => 'Rekrutmen RSI Fatimah 2026',
            'code' => 'RSIF-2026',
            'registration_start_at' => now()->subDays(5),
            'registration_end_at' => now()->addDays(20),
            'status' => RecruitmentPeriodStatus::OPEN,
        ]);
    }

    private function createActiveVacancy(array $attributes = []): JobVacancy
    {
        return JobVacancy::create(array_merge([
            'recruitment_period_id' => $this->period->id,
            'code' => 'POS-' . uniqid(),
            'title' => 'Perawat IGD',
            'unit' => 'Instalasi Gawat Darurat',
            'employment_type' => 'PKWT',
            'quota' => 2,
            'location' => 'RSI Fatimah Banyuwangi',
            'open_at' => now()->subDays(2),
            'close_at' => now()->addDays(10),
            'status' => JobVacancyStatus::OPEN,
        ], $attributes));
    }

    /**
     * 1. Public recruitment index return 200 ketika ada active vacancy.
     */
    public function test_public_recruitment_index_returns_200_when_there_is_active_vacancy(): void
    {
        $this->createActiveVacancy();

        $responseRoot = $this->get('/');
        $responseRoot->assertStatus(200);
        $responseRoot->assertSee('Lowongan Pekerjaan');

        $responseIndex = $this->get('/lowongan');
        $responseIndex->assertStatus(200);
        $responseIndex->assertSee('Lowongan Pekerjaan');
    }

    /**
     * 2. Ketika tidak ada active recruitment, public recruitment route redirect ke recruitment.closed.
     */
    public function test_public_recruitment_redirects_to_closed_when_no_active_recruitment(): void
    {
        // Tutup periode rekrutmen sehingga tidak ada rekrutmen aktif
        $this->period->update([
            'status' => RecruitmentPeriodStatus::COMPLETED,
        ]);

        $this->get('/')->assertRedirect(route('recruitment.closed'));
        $this->get('/lowongan')->assertRedirect(route('recruitment.closed'));
        $this->get('/panduan/alur-pendaftaran')->assertRedirect(route('recruitment.closed'));
        $this->get('/panduan/persyaratan')->assertRedirect(route('recruitment.closed'));
    }

    /**
     * 3. Closed page return 200.
     */
    public function test_closed_page_returns_200(): void
    {
        $response = $this->get('/rekrutmen-ditutup');

        $response->assertStatus(200);
        $response->assertSee('Rekrutmen Sedang Ditutup');
        $response->assertSee('RSI Fatimah Banyuwangi');
    }

    /**
     * 4. Index hanya menampilkan currently-open vacancy.
     */
    public function test_index_only_displays_currently_open_vacancies(): void
    {
        $openVacancy = $this->createActiveVacancy([
            'title' => 'Lowongan Yang Terbuka',
            'code' => 'OPEN-01',
        ]);

        $draftVacancy = $this->createActiveVacancy([
            'title' => 'Lowongan Masih Draft',
            'code' => 'DRAFT-01',
            'status' => JobVacancyStatus::DRAFT,
        ]);

        $closedVacancy = $this->createActiveVacancy([
            'title' => 'Lowongan Telah Ditutup',
            'code' => 'CLOSED-01',
            'status' => JobVacancyStatus::CLOSED,
        ]);

        $response = $this->get('/lowongan');

        $response->assertStatus(200);
        $response->assertSee('Lowongan Yang Terbuka');
        $response->assertDontSee('Lowongan Masih Draft');
        $response->assertDontSee('Lowongan Telah Ditutup');
    }

    /**
     * 5. Search berdasarkan title bekerja.
     */
    public function test_search_by_title_works(): void
    {
        $this->createActiveVacancy(['title' => 'Dokter Spesialis Bedah', 'code' => 'DOK-01']);
        $this->createActiveVacancy(['title' => 'Radiografer Senior', 'code' => 'RAD-01']);

        $response = $this->get('/lowongan?q=Bedah');

        $response->assertStatus(200);
        $response->assertSee('Dokter Spesialis Bedah');
        $response->assertDontSee('Radiografer Senior');
    }

    /**
     * 6. Filter unit bekerja.
     */
    public function test_filter_unit_works(): void
    {
        $this->createActiveVacancy(['unit' => 'Instalasi Radiologi', 'title' => 'Petugas X-Ray']);
        $this->createActiveVacancy(['unit' => 'Instalasi Farmasi', 'title' => 'Staff Apotek']);

        $response = $this->get('/lowongan?unit=' . urlencode('Instalasi Radiologi'));

        $response->assertStatus(200);
        $response->assertSee('Petugas X-Ray');
        $response->assertDontSee('Staff Apotek');
    }

    /**
     * 7. Detail vacancy dapat dibuka menggunakan vacancy code.
     */
    public function test_detail_vacancy_can_be_opened_using_vacancy_code(): void
    {
        $vacancy = $this->createActiveVacancy([
            'code' => 'POS-2026-IGD-01',
            'title' => 'Perawat Gawat Darurat',
        ]);

        $response = $this->get('/lowongan/POS-2026-IGD-01');

        $response->assertStatus(200);
        $response->assertSee('POS-2026-IGD-01');
        $response->assertSee('Perawat Gawat Darurat');
    }

    /**
     * 8. Detail hanya menampilkan qualification/document/stage milik vacancy yang benar.
     */
    public function test_detail_only_displays_qualification_document_and_stage_belonging_to_correct_vacancy(): void
    {
        $vacancy1 = $this->createActiveVacancy(['code' => 'POS-V1', 'title' => 'Formasi Satu']);
        $vacancy2 = $this->createActiveVacancy(['code' => 'POS-V2', 'title' => 'Formasi Dua']);

        VacancyQualification::create([
            'job_vacancy_id' => $vacancy1->id,
            'label' => 'Keahlian V1',
            'value' => 'Sertifikat Khusus Posisi Satu',
            'sort_order' => 1,
        ]);

        VacancyDocumentRequirement::create([
            'job_vacancy_id' => $vacancy1->id,
            'name' => 'Dokumen Khusus V1',
            'is_required' => true,
            'sort_order' => 1,
        ]);

        VacancySelectionStage::create([
            'job_vacancy_id' => $vacancy1->id,
            'name' => 'Ujian Tertulis V1',
            'sort_order' => 1,
        ]);

        VacancyQualification::create([
            'job_vacancy_id' => $vacancy2->id,
            'label' => 'Keahlian V2',
            'value' => 'Hanya Untuk Posisi Dua',
            'sort_order' => 1,
        ]);

        VacancyDocumentRequirement::create([
            'job_vacancy_id' => $vacancy2->id,
            'name' => 'Dokumen Khusus V2',
            'is_required' => true,
            'sort_order' => 1,
        ]);

        VacancySelectionStage::create([
            'job_vacancy_id' => $vacancy2->id,
            'name' => 'Tahapan Khusus V2',
            'sort_order' => 1,
        ]);

        $response = $this->get('/lowongan/POS-V1');

        $response->assertStatus(200);
        $response->assertSee('Keahlian V1');
        $response->assertSee('Dokumen Khusus V1');
        $response->assertSee('Ujian Tertulis V1');

        $response->assertDontSee('Hanya Untuk Posisi Dua');
        $response->assertDontSee('Dokumen Khusus V2');
        $response->assertDontSee('Tahapan Khusus V2');
    }

    /**
     * 9. Inactive/closed vacancy tidak dapat diakses sebagai public vacancy (404).
     */
    public function test_inactive_or_closed_vacancy_cannot_be_accessed_as_public_vacancy(): void
    {
        // Pastikan ada 1 active vacancy agar portal tidak redirect ke closed
        $this->createActiveVacancy(['code' => 'ACTIVE-PORTAL']);

        $closedVacancy = $this->createActiveVacancy([
            'code' => 'POS-CLOSED-99',
            'status' => JobVacancyStatus::CLOSED,
        ]);

        $response = $this->get('/lowongan/POS-CLOSED-99');

        $response->assertStatus(404);
    }

    /**
     * 10. Flow guide return 200 ketika recruitment aktif.
     */
    public function test_flow_guide_returns_200_when_recruitment_is_active(): void
    {
        $this->createActiveVacancy();

        $response = $this->get('/panduan/alur-pendaftaran');

        $response->assertStatus(200);
        $response->assertSee('Alur Pendaftaran');
        $response->assertSee('Registrasi Akun Pelamar');
    }

    /**
     * 11. Requirements guide return 200 ketika recruitment aktif.
     */
    public function test_requirements_guide_returns_200_when_recruitment_is_active(): void
    {
        $this->createActiveVacancy();

        $response = $this->get('/panduan/persyaratan');

        $response->assertStatus(200);
        $response->assertSee('Persyaratan');
        $response->assertSee('Ketentuan Akun');
    }
}
