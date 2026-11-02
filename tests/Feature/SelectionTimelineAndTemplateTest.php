<?php

namespace Tests\Feature;

use App\Enums\JobApplicationStatus;
use App\Enums\JobVacancyStatus;
use App\Enums\RecruitmentPeriodStatus;
use App\Models\ApplicantEducation;
use App\Models\ApplicantProfile;
use App\Models\JobApplication;
use App\Models\JobVacancy;
use App\Models\RecruitmentPeriod;
use App\Models\SelectionStageTemplate;
use App\Models\User;
use App\Models\VacancyDocumentRequirement;
use App\Models\VacancySelectionStage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class SelectionTimelineAndTemplateTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected User $applicant;

    protected RecruitmentPeriod $openPeriod;

    protected RecruitmentPeriod $draftPeriod;

    protected JobVacancy $vacancy;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('applicant_documents');

        $this->admin = User::factory()->create([
            'role' => 'admin',
            'is_active' => true,
        ]);

        $this->applicant = User::factory()->create([
            'name' => 'Dokter Pelamar',
            'email' => 'pelamar@fatimah.test',
            'phone' => '081234567890',
            'role' => 'applicant',
            'is_active' => true,
        ]);

        $profile = ApplicantProfile::create([
            'user_id' => $this->applicant->id,
            'nik' => '3510000000000001',
            'nickname' => 'Dokter',
            'birth_place' => 'Banyuwangi',
            'birth_date' => '1995-05-15',
            'gender' => 'Laki-laki',
            'religion' => 'Islam',
            'phone' => '081234567890',
            'address_ktp' => 'Banyuwangi',
            'address_domicile' => 'Banyuwangi',
            'same_as_ktp' => true,
            'photo_path' => 'profile-photos/test.jpg',
        ]);

        ApplicantEducation::create([
            'applicant_profile_id' => $profile->id,
            'education_level' => 'S1',
            'institution_name' => 'Universitas Airlangga',
            'study_program' => 'Kedokteran',
            'accreditation' => 'A',
            'gpa' => '3.80',
            'graduation_year' => 2020,
        ]);

        $this->openPeriod = RecruitmentPeriod::create([
            'name' => 'Periode Rekrutmen Terbuka',
            'code' => 'PER-OPEN-2026',
            'registration_start_at' => now()->subDays(2),
            'registration_end_at' => now()->addDays(10),
            'status' => RecruitmentPeriodStatus::OPEN,
        ]);

        $this->draftPeriod = RecruitmentPeriod::create([
            'name' => 'Periode Rekrutmen Ditutup',
            'code' => 'PER-DRAFT-2026',
            'registration_start_at' => now()->addDays(5),
            'registration_end_at' => now()->addDays(20),
            'status' => RecruitmentPeriodStatus::DRAFT,
        ]);

        $this->vacancy = JobVacancy::create([
            'recruitment_period_id' => $this->openPeriod->id,
            'code' => 'VAC-DOK-01',
            'title' => 'Dokter Umum Baru',
            'unit' => 'Pelayanan Medis',
            'employment_type' => 'Tenaga Medis',
            'location' => 'RSI Fatimah Banyuwangi',
            'description' => 'Lowongan dokter umum',
            'open_at' => now()->subDays(2),
            'close_at' => now()->addDays(10),
            'status' => JobVacancyStatus::OPEN,
        ]);
    }

    public function test_admin_can_toggle_recruitment_period_status(): void
    {
        $this->actingAs($this->admin);

        // Tutup open period
        $response = $this->post(route('admin.timelines.toggle-status', $this->openPeriod), [
            'status' => 'completed',
        ]);

        $response->assertSessionHas('success');
        $this->assertEquals(RecruitmentPeriodStatus::COMPLETED, $this->openPeriod->fresh()->status);

        // Buka draft period -> harus menjadi OPEN dan periode lain tertutup
        $response2 = $this->post(route('admin.timelines.toggle-status', $this->draftPeriod), [
            'status' => 'open',
        ]);

        $response2->assertSessionHas('success');
        $this->assertEquals(RecruitmentPeriodStatus::OPEN, $this->draftPeriod->fresh()->status);
        $this->assertEquals(RecruitmentPeriodStatus::COMPLETED, $this->openPeriod->fresh()->status);
    }

    public function test_admin_can_create_and_edit_selection_stage_template(): void
    {
        $this->actingAs($this->admin);

        $stagesData = [
            [
                'sequence_order' => 1,
                'name' => 'Pendaftaran Peserta Rekrutmen Dan Seleksi Administrasi',
                'description' => 'Tahap berkas',
                'start_date' => now()->format('Y-m-d'),
                'end_date' => now()->addDays(5)->format('Y-m-d'),
                'announcement_date' => now()->addDays(6)->format('Y-m-d'),
            ],
            [
                'sequence_order' => 2,
                'name' => 'Seleksi test tulis, test skill, Test Wawancara',
                'description' => 'Tahap tes kompetensi',
                'start_date' => now()->addDays(7)->format('Y-m-d'),
                'end_date' => now()->addDays(10)->format('Y-m-d'),
                'announcement_date' => now()->addDays(11)->format('Y-m-d'),
            ],
        ];

        // Store
        $response = $this->post(route('admin.timelines.templates.store'), [
            'name' => 'Template Dokter Umum',
            'is_default' => 1,
            'stages' => $stagesData,
        ]);

        $response->assertSessionHas('success');
        $template = SelectionStageTemplate::where('name', 'Template Dokter Umum')->first();
        $this->assertNotNull($template);
        $this->assertTrue($template->is_default);
        $this->assertCount(2, $template->stages_data);

        // Update
        $stagesData[0]['name'] = 'Pendaftaran & Seleksi Administrasi Updated';
        $updateResponse = $this->put(route('admin.timelines.templates.update', $template), [
            'name' => 'Template Dokter Umum Updated',
            'is_default' => 1,
            'stages' => $stagesData,
        ]);

        $updateResponse->assertSessionHas('success');
        $this->assertEquals('Template Dokter Umum Updated', $template->fresh()->name);
        $this->assertEquals('Pendaftaran & Seleksi Administrasi Updated', $template->fresh()->stages_data[0]['name']);
    }

    public function test_admin_can_apply_selection_stage_template_to_vacancies(): void
    {
        $this->actingAs($this->admin);

        $template = SelectionStageTemplate::create([
            'name' => 'Template 4 Tahap Standar',
            'is_default' => true,
            'stages_data' => [
                [
                    'sequence_order' => 1,
                    'name' => 'Pendaftaran Peserta Rekrutmen Dan Seleksi Administrasi',
                    'description' => 'Seleksi Administrasi',
                    'start_date' => '2026-11-01',
                    'end_date' => '2026-11-05',
                ],
                [
                    'sequence_order' => 2,
                    'name' => 'Seleksi test tulis, test skill, Test Wawancara',
                    'description' => 'Tes Bidang',
                    'start_date' => '2026-11-06',
                    'end_date' => '2026-11-10',
                ],
                [
                    'sequence_order' => 3,
                    'name' => 'Seleksi test psikologi dan test kesehatan',
                    'description' => 'Psikotes & MCU',
                    'start_date' => '2026-11-11',
                    'end_date' => '2026-11-15',
                ],
                [
                    'sequence_order' => 4,
                    'name' => 'Masa Orientasi Magang Dokter Umum Baru (atau Karyawan Baru)',
                    'description' => 'Orientasi Kerja',
                    'start_date' => '2026-11-16',
                    'end_date' => '2026-11-20',
                ],
            ],
        ]);

        $response = $this->post(route('admin.timelines.templates.apply-to-vacancies', $template), [
            'vacancy_ids' => [$this->vacancy->id],
        ]);

        $response->assertSessionHas('success');

        $stages = $this->vacancy->fresh()->selectionStages()->orderBy('sort_order')->get();
        $this->assertCount(4, $stages);
        $this->assertEquals('Pendaftaran Peserta Rekrutmen Dan Seleksi Administrasi', $stages[0]->name);
        $this->assertEquals('Seleksi test tulis, test skill, Test Wawancara', $stages[1]->name);
        $this->assertEquals('Seleksi test psikologi dan test kesehatan', $stages[2]->name);
        $this->assertEquals('Masa Orientasi Magang Dokter Umum Baru (atau Karyawan Baru)', $stages[3]->name);
    }

    public function test_user_cannot_upload_document_after_submission_deadline_ended(): void
    {
        $requirement = VacancyDocumentRequirement::create([
            'job_vacancy_id' => $this->vacancy->id,
            'name' => 'Surat Lamaran',
            'description' => 'PDF Lamaran',
            'is_required' => true,
            'allowed_mimes' => 'application/pdf',
            'max_size_kb' => 2048,
            'sort_order' => 1,
        ]);

        // Stage 1 end_at sudah lewat (kemarin)
        VacancySelectionStage::create([
            'job_vacancy_id' => $this->vacancy->id,
            'name' => 'Pendaftaran & Seleksi Administrasi',
            'sort_order' => 1,
            'start_at' => now()->subDays(5),
            'end_at' => now()->subDays(1),
        ]);

        $application = JobApplication::create([
            'user_id' => $this->applicant->id,
            'job_vacancy_id' => $this->vacancy->id,
            'recruitment_period_id' => $this->openPeriod->id,
            'status' => JobApplicationStatus::DRAFT,
        ]);

        $this->actingAs($this->applicant);

        // User akses halaman documents -> melihat indikator penutupan berkas
        $pageResponse = $this->get(route('portal.applications.documents.index', $application));
        $pageResponse->assertStatus(200);
        $pageResponse->assertSee('Batas pengumpulan berakhir');
        $pageResponse->assertSee('Batas Pengumpulan Berkas Telah Berakhir');

        // User mencoba upload berkas -> ditolak
        $file = UploadedFile::fake()->create('lamaran.pdf', 500, 'application/pdf');

        $uploadResponse = $this->post(route('portal.applications.documents.store', [$application, $requirement]), [
            'file' => $file,
        ]);

        $uploadResponse->assertSessionHas('error', 'Batas waktu pengumpulan berkas telah berakhir.');
        $this->assertDatabaseMissing('application_documents', [
            'job_application_id' => $application->id,
            'vacancy_document_requirement_id' => $requirement->id,
        ]);
    }
}
