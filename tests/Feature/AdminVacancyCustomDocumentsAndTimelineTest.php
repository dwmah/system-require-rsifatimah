<?php

namespace Tests\Feature;

use App\Enums\JobApplicationStatus;
use App\Enums\JobVacancyStatus;
use App\Enums\RecruitmentPeriodStatus;
use App\Models\JobApplication;
use App\Models\JobVacancy;
use App\Models\RecruitmentPeriod;
use App\Models\User;
use App\Models\VacancyDocumentRequirement;
use App\Models\VacancySelectionStage;
use App\Services\Recruitment\ApplicationSnapshotService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminVacancyCustomDocumentsAndTimelineTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected User $applicant;
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

        $this->applicant = User::factory()->create([
            'name' => 'Pelamar Test',
            'email' => 'pelamar@gmail.com',
            'role' => User::ROLE_APPLICANT,
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

    public function test_admin_can_create_vacancies_with_distinct_custom_documents(): void
    {
        // Lowongan A: Perawat IGD (Medical)
        $responseA = $this->actingAs($this->admin)->post(route('admin.vacancies.store'), [
            'recruitment_period_id' => $this->period->id,
            'title' => 'Perawat IGD',
            'department' => 'Instalasi Gawat Darurat',
            'formation_type' => 'Tenaga Keperawatan',
            'quota' => 2,
            'open_at' => '2026-09-01',
            'close_at' => '2026-09-30',
            'description' => 'Lowongan Perawat IGD.',
            'is_published' => 1,
            'document_names' => [
                'Surat Lamaran Kerja',
                'Ijazah Ners Legalisir',
                'STR Perawat Aktif',
                'Sertifikat BTCLS / ACLS',
            ],
            'document_descriptions' => [
                'Ditujukan ke Direktur',
                'Scan ijazah asli',
                'Wajib masih aktif',
                'Pelatihan kegawatdaruratan',
            ],
            'document_mandatory' => [
                0 => '1',
                1 => '1',
                2 => '1',
                3 => '0',
            ],
        ]);

        $responseA->assertRedirect(route('admin.vacancies.index'));
        $vacancyA = JobVacancy::where('title', 'Perawat IGD')->firstOrFail();

        $this->assertCount(4, $vacancyA->documentRequirements);
        $this->assertDatabaseHas('vacancy_document_requirements', [
            'job_vacancy_id' => $vacancyA->id,
            'name' => 'STR Perawat Aktif',
            'is_required' => true,
        ]);
        $this->assertDatabaseHas('vacancy_document_requirements', [
            'job_vacancy_id' => $vacancyA->id,
            'name' => 'Sertifikat BTCLS / ACLS',
            'is_required' => false,
        ]);

        // Lowongan B: Programmer IT (Non-Medical)
        $responseB = $this->actingAs($this->admin)->post(route('admin.vacancies.store'), [
            'recruitment_period_id' => $this->period->id,
            'title' => 'Staff IT Programmer',
            'department' => 'SIMRS & IT',
            'formation_type' => 'Tenaga Non-Medis / Umum',
            'quota' => 1,
            'open_at' => '2026-09-01',
            'close_at' => '2026-09-30',
            'description' => 'Lowongan IT Programmer.',
            'is_published' => 1,
            'document_names' => [
                'Surat Lamaran Kerja',
                'Portofolio Proyek Web / Mobile',
                'Ijazah S1 Teknik Informatika',
                'Sertifikat Keahlian IT / AWS',
            ],
            'document_descriptions' => [
                'Ditujukan ke Direktur',
                'Lampiran link GitHub atau link deploy',
                'Scan ijazah S1',
                'Sertifikat IT opsional',
            ],
            'document_mandatory' => [
                0 => '1',
                1 => '1',
                2 => '1',
                3 => '0',
            ],
        ]);

        $responseB->assertRedirect(route('admin.vacancies.index'));
        $vacancyB = JobVacancy::where('title', 'Staff IT Programmer')->firstOrFail();

        $this->assertCount(4, $vacancyB->documentRequirements);
        $this->assertDatabaseHas('vacancy_document_requirements', [
            'job_vacancy_id' => $vacancyB->id,
            'name' => 'Portofolio Proyek Web / Mobile',
            'is_required' => true,
        ]);

        // Pastikan dokumen Lowongan A dan B berbeda
        $this->assertFalse(
            $vacancyB->documentRequirements->pluck('name')->contains('STR Perawat Aktif')
        );
        $this->assertFalse(
            $vacancyA->documentRequirements->pluck('name')->contains('Portofolio Proyek Web / Mobile')
        );
    }

    public function test_admin_can_update_vacancy_documents_and_stages_with_deadlines(): void
    {
        $vacancy = JobVacancy::create([
            'recruitment_period_id' => $this->period->id,
            'code' => 'VAC-TEST-UPDATE',
            'title' => 'Apoteker Farmasi',
            'unit' => 'Instalasi Farmasi',
            'employment_type' => 'Tenaga Kefarmasian',
            'location' => 'RSI Fatimah Banyuwangi',
            'quota' => 1,
            'description' => 'Deskripsi Apoteker.',
            'open_at' => Carbon::parse('2026-09-01'),
            'close_at' => Carbon::parse('2026-09-30'),
            'status' => JobVacancyStatus::OPEN,
        ]);

        $doc1 = VacancyDocumentRequirement::create([
            'job_vacancy_id' => $vacancy->id,
            'name' => 'Surat Lamaran',
            'is_required' => true,
            'sort_order' => 1,
        ]);

        $stage1 = VacancySelectionStage::create([
            'job_vacancy_id' => $vacancy->id,
            'name' => 'Pengisian & Verifikasi Berkas',
            'start_at' => Carbon::parse('2026-09-01'),
            'end_at' => Carbon::parse('2026-09-10'),
            'sort_order' => 1,
        ]);

        $response = $this->actingAs($this->admin)->put(route('admin.vacancies.update', $vacancy), [
            'recruitment_period_id' => $this->period->id,
            'title' => 'Apoteker Farmasi Senior',
            'department' => 'Instalasi Farmasi',
            'formation_type' => 'Tenaga Kefarmasian',
            'quota' => 2,
            'open_at' => '2026-09-01',
            'close_at' => '2026-09-30',
            'description' => 'Deskripsi Apoteker Senior.',
            'is_published' => 1,
            // Documents update
            'document_ids' => [$doc1->id, null],
            'document_names' => [
                'Surat Lamaran Kerja Resmi',
                'STRA (Surat Tanda Registrasi Apoteker) Aktif',
            ],
            'document_descriptions' => [
                'Format PDF',
                'Scan STRA asli',
            ],
            'document_mandatory' => [
                0 => '1',
                1 => '1',
            ],
            // Stages update
            'stage_ids' => [$stage1->id, null],
            'stage_names' => [
                'Pengisian & Verifikasi Berkas',
                'Ujian Kompetensi Kefarmasian',
            ],
            'stage_descriptions' => [
                'Pengecekan berkas',
                'Tes tertulis farmasi klinis',
            ],
            'stage_start_ats' => [
                '2026-09-01',
                '2026-09-15',
            ],
            'stage_end_ats' => [
                '2026-09-12',
                '2026-09-20',
            ],
        ]);

        $response->assertRedirect(route('admin.vacancies.show', $vacancy));

        $vacancy->refresh();
        $this->assertEquals('Apoteker Farmasi Senior', $vacancy->title);

        $doc1->refresh();
        $this->assertEquals('Surat Lamaran Kerja Resmi', $doc1->name);

        $this->assertDatabaseHas('vacancy_document_requirements', [
            'job_vacancy_id' => $vacancy->id,
            'name' => 'STRA (Surat Tanda Registrasi Apoteker) Aktif',
            'is_required' => true,
        ]);

        $stage1->refresh();
        $this->assertEquals('2026-09-12', $stage1->end_at->format('Y-m-d'));

        $this->assertDatabaseHas('vacancy_selection_stages', [
            'job_vacancy_id' => $vacancy->id,
            'name' => 'Ujian Kompetensi Kefarmasian',
            'start_at' => '2026-09-15 00:00:00',
            'end_at' => '2026-09-20 00:00:00',
        ]);
    }

    public function test_applicant_sees_deadline_reminder_on_document_upload_page(): void
    {
        $vacancy = JobVacancy::create([
            'recruitment_period_id' => $this->period->id,
            'code' => 'VAC-TEST-REMIND',
            'title' => 'Analis Laboratorium',
            'unit' => 'Instalasi Laboratorium',
            'employment_type' => 'Tenaga Penunjang Medis',
            'location' => 'RSI Fatimah Banyuwangi',
            'quota' => 1,
            'description' => 'Deskripsi Analis Laboratorium.',
            'open_at' => now()->subDays(2),
            'close_at' => now()->addDays(2), // 2 days left
            'status' => JobVacancyStatus::OPEN,
        ]);

        VacancyDocumentRequirement::create([
            'job_vacancy_id' => $vacancy->id,
            'name' => 'Ijazah D3 Analis Kesehatan',
            'is_required' => true,
            'sort_order' => 1,
        ]);

        $application = JobApplication::create([
            'user_id' => $this->applicant->id,
            'job_vacancy_id' => $vacancy->id,
            'recruitment_period_id' => $this->period->id,
            'application_number' => 'APP-2026-001',
            'status' => JobApplicationStatus::DRAFT,
        ]);

        $response = $this->actingAs($this->applicant)->get(route('portal.applications.documents.index', $application));

        $response->assertStatus(200);
        $response->assertSee('Batas Waktu Pengisian & Unggah Berkas:', false);
        $response->assertSee('Ijazah D3 Analis Kesehatan');
    }

    public function test_applicant_tracking_displays_stage_deadlines_and_reminders(): void
    {
        $vacancy = JobVacancy::create([
            'recruitment_period_id' => $this->period->id,
            'code' => 'VAC-TEST-TRACK',
            'title' => 'Dokter Umum',
            'unit' => 'Pelayanan Medis',
            'employment_type' => 'Tenaga Medis',
            'location' => 'RSI Fatimah Banyuwangi',
            'quota' => 1,
            'description' => 'Deskripsi Dokter.',
            'open_at' => now()->subDays(5),
            'close_at' => now()->addDays(20),
            'status' => JobVacancyStatus::OPEN,
        ]);

        $stage = VacancySelectionStage::create([
            'job_vacancy_id' => $vacancy->id,
            'name' => 'Seleksi Administrasi & Berkas',
            'description' => 'Verifikasi keabsahan dokumen',
            'start_at' => now()->subDays(1),
            'end_at' => now()->addDays(3),
            'sort_order' => 1,
        ]);

        $application = JobApplication::create([
            'user_id' => $this->applicant->id,
            'job_vacancy_id' => $vacancy->id,
            'recruitment_period_id' => $this->period->id,
            'application_number' => 'APP-2026-002',
            'status' => JobApplicationStatus::SUBMITTED,
            'submitted_at' => now()->subDay(),
        ]);

        // Capture immutable snapshot
        app(ApplicationSnapshotService::class)->capture($application);

        $response = $this->actingAs($this->applicant)->get(route('portal.applications.tracking', $application));

        $response->assertStatus(200);
        $response->assertSee('Alur & Tahapan Seleksi', false);
        $response->assertSee('Seleksi Administrasi &amp; Berkas', false);
        $response->assertSee('Pengingat Batas Waktu: Seleksi Administrasi &amp; Berkas', false);
    }
}
