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
use App\Models\User;
use App\Models\VacancyDocumentRequirement;
use App\Services\Recruitment\ApplicationDocumentService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class VacancyVideoUploadRequirementTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected User $applicant;

    protected RecruitmentPeriod $period;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake(ApplicationDocumentService::DISK_NAME);

        $this->admin = User::create([
            'name' => 'Admin HRD Test',
            'email' => 'admin.hrd.video@rsifatimah.com',
            'phone' => '081234567890',
            'password' => 'Password123!',
            'role' => 'admin',
            'is_active' => true,
        ]);

        $this->applicant = User::create([
            'name' => 'Pelamar Video Test',
            'email' => 'pelamar.video@example.com',
            'phone' => '089876543210',
            'password' => 'Password123!',
            'role' => 'applicant',
            'is_active' => true,
        ]);

        $profile = ApplicantProfile::create([
            'user_id' => $this->applicant->id,
            'nik' => '3510998877665544',
            'birth_place' => 'Banyuwangi',
            'birth_date' => '1998-05-10',
            'gender' => 'Laki-laki',
            'religion' => 'Islam',
            'address_ktp' => 'Jl. Test No. 1 Banyuwangi',
            'address_domicile' => 'Jl. Test No. 1 Banyuwangi',
            'same_as_ktp' => true,
            'photo_path' => 'profile-photos/test.jpg',
        ]);

        ApplicantEducation::create([
            'applicant_profile_id' => $profile->id,
            'education_level' => 'S1',
            'institution_name' => 'Universitas Brawijaya',
            'study_program' => 'Ilmu Komunikasi',
            'accreditation' => 'A',
            'gpa' => '3.75',
            'graduation_year' => 2021,
        ]);

        $this->period = RecruitmentPeriod::create([
            'name' => 'Rekrutmen Humas Video 2026',
            'code' => 'RSIF-VIDEO-2026',
            'registration_start_at' => now()->subDays(2),
            'registration_end_at' => now()->addDays(20),
            'status' => RecruitmentPeriodStatus::OPEN,
        ]);
    }

    public function test_admin_can_create_vacancy_with_video_mp4_requirement(): void
    {
        $response = $this->actingAs($this->admin)->post(route('admin.vacancies.store'), [
            'recruitment_period_id' => $this->period->id,
            'title' => 'Staf Media Kreatif & Videografer',
            'department' => 'Humas & Promkes',
            'formation_type' => 'Tenaga Non-Medis / Umum',
            'quota' => 1,
            'open_at' => now()->format('Y-m-d'),
            'close_at' => now()->addDays(14)->format('Y-m-d'),
            'description' => 'Membuat video promosi dan konten profil rumah sakit.',
            'is_published' => 1,
            'document_names' => [
                'Video Perkenalan & Portofolio Karya',
            ],
            'document_descriptions' => [
                'Unggah video perkenalan diri format MP4 durasi maks 2 menit',
            ],
            'document_mandatory' => [
                0 => '1',
            ],
            'document_mimes' => [
                0 => 'video/mp4',
            ],
            'document_max_sizes' => [
                0 => 20480,
            ],
        ]);

        $response->assertRedirect(route('admin.vacancies.index'));

        $vacancy = JobVacancy::where('title', 'Staf Media Kreatif & Videografer')->firstOrFail();
        $this->assertCount(1, $vacancy->documentRequirements);

        $req = $vacancy->documentRequirements->first();
        $this->assertEquals('Video Perkenalan & Portofolio Karya', $req->name);
        $this->assertEquals('video/mp4', $req->allowed_mimes);
        $this->assertEquals(20480, $req->max_size_kb);
        $this->assertTrue((bool) $req->is_required);
    }

    public function test_applicant_can_upload_mp4_video_and_metadata_saved_correctly(): void
    {
        $vacancy = JobVacancy::create([
            'recruitment_period_id' => $this->period->id,
            'code' => 'VAC-VIDEO-001',
            'title' => 'Content Creator',
            'unit' => 'Humas',
            'employment_type' => 'Kontrak',
            'location' => 'RSI Fatimah',
            'quota' => 1,
            'description' => 'Dibutuhkan segera videografer.',
            'open_at' => now()->subDay(),
            'close_at' => now()->addDays(10),
            'status' => JobVacancyStatus::OPEN,
        ]);

        $requirement = VacancyDocumentRequirement::create([
            'job_vacancy_id' => $vacancy->id,
            'name' => 'Video Profil Diri',
            'description' => 'Video format .mp4 maks 20MB',
            'is_required' => true,
            'allowed_mimes' => 'video/mp4',
            'max_size_kb' => 20480,
            'sort_order' => 1,
        ]);

        $application = JobApplication::create([
            'job_vacancy_id' => $vacancy->id,
            'recruitment_period_id' => $this->period->id,
            'user_id' => $this->applicant->id,
            'application_number' => 'APP-VIDEO-2026-0001',
            'status' => JobApplicationStatus::DRAFT,
        ]);

        $fakeVideo = UploadedFile::fake()->create('intro.mp4', 1024, 'video/mp4');

        $response = $this->actingAs($this->applicant)->post(
            route('portal.applications.documents.store', [$application, $requirement]),
            [
                'file' => $fakeVideo,
            ]
        );

        $response->assertRedirect(route('portal.applications.documents.index', $application));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('application_documents', [
            'job_application_id' => $application->id,
            'vacancy_document_requirement_id' => $requirement->id,
            'original_name' => 'intro.mp4',
            'mime_type' => 'video/mp4',
        ]);

        $doc = $application->documents()->firstOrFail();
        $this->assertStringEndsWith('.mp4', $doc->stored_path);
        Storage::disk(ApplicationDocumentService::DISK_NAME)->assertExists($doc->stored_path);
    }

    public function test_applicant_uploading_non_mp4_file_is_rejected_with_validation_error(): void
    {
        $vacancy = JobVacancy::create([
            'recruitment_period_id' => $this->period->id,
            'code' => 'VAC-VIDEO-002',
            'title' => 'Video Editor',
            'unit' => 'Humas',
            'employment_type' => 'Kontrak',
            'location' => 'RSI Fatimah',
            'quota' => 1,
            'description' => 'Editor video YouTube.',
            'open_at' => now()->subDay(),
            'close_at' => now()->addDays(10),
            'status' => JobVacancyStatus::OPEN,
        ]);

        $requirement = VacancyDocumentRequirement::create([
            'job_vacancy_id' => $vacancy->id,
            'name' => 'Showreel Video',
            'description' => 'Wajib MP4',
            'is_required' => true,
            'allowed_mimes' => 'video/mp4',
            'max_size_kb' => 20480,
            'sort_order' => 1,
        ]);

        $application = JobApplication::create([
            'job_vacancy_id' => $vacancy->id,
            'recruitment_period_id' => $this->period->id,
            'user_id' => $this->applicant->id,
            'application_number' => 'APP-VIDEO-2026-0002',
            'status' => JobApplicationStatus::DRAFT,
        ]);

        // Upload PDF when only video/mp4 is allowed
        $fakePdf = UploadedFile::fake()->create('dokumen.pdf', 500, 'application/pdf');

        $response = $this->actingAs($this->applicant)->from(route('portal.applications.documents.index', $application))->post(
            route('portal.applications.documents.store', [$application, $requirement]),
            [
                'file' => $fakePdf,
            ]
        );

        $response->assertRedirect(route('portal.applications.documents.index', $application));
        $response->assertSessionHasErrors('file');

        $this->assertDatabaseMissing('application_documents', [
            'job_application_id' => $application->id,
            'vacancy_document_requirement_id' => $requirement->id,
        ]);
    }

    public function test_admin_and_applicant_can_access_video_preview_and_download(): void
    {
        $vacancy = JobVacancy::create([
            'recruitment_period_id' => $this->period->id,
            'code' => 'VAC-VIDEO-003',
            'title' => 'Videografer Medis',
            'unit' => 'Humas',
            'employment_type' => 'Tetap',
            'location' => 'RSI Fatimah',
            'quota' => 1,
            'description' => 'Dokumentasi kegiatan medis rumah sakit.',
            'open_at' => now()->subDay(),
            'close_at' => now()->addDays(10),
            'status' => JobVacancyStatus::OPEN,
        ]);

        $requirement = VacancyDocumentRequirement::create([
            'job_vacancy_id' => $vacancy->id,
            'name' => 'Video Demonstrasi Skill',
            'description' => 'Format MP4',
            'is_required' => true,
            'allowed_mimes' => 'video/mp4',
            'max_size_kb' => 20480,
            'sort_order' => 1,
        ]);

        $application = JobApplication::create([
            'job_vacancy_id' => $vacancy->id,
            'recruitment_period_id' => $this->period->id,
            'user_id' => $this->applicant->id,
            'application_number' => 'APP-VIDEO-2026-0003',
            'status' => JobApplicationStatus::DRAFT,
        ]);

        $fakeVideo = UploadedFile::fake()->create('portfolio.mp4', 2048, 'video/mp4');

        $this->actingAs($this->applicant)->post(
            route('portal.applications.documents.store', [$application, $requirement]),
            [
                'file' => $fakeVideo,
            ]
        );

        $document = $application->documents()->firstOrFail();

        // 1. Applicant can preview & download
        $appPreview = $this->actingAs($this->applicant)->get(route('documents.preview', $document));
        $appPreview->assertOk();
        $this->assertEquals('video/mp4', $appPreview->headers->get('Content-Type'));

        $appDownload = $this->actingAs($this->applicant)->get(route('portal.applications.documents.download', [$application, $document]));
        $appDownload->assertOk();
        $this->assertEquals('video/mp4', $appDownload->headers->get('Content-Type'));

        // 2. Admin can preview & download
        $adminPreview = $this->actingAs($this->admin)->get(route('documents.preview', $document));
        $adminPreview->assertOk();
        $this->assertEquals('video/mp4', $adminPreview->headers->get('Content-Type'));

        $adminDownload = $this->actingAs($this->admin)->get(route('documents.download', $document));
        $adminDownload->assertOk();
        $this->assertEquals('video/mp4', $adminDownload->headers->get('Content-Type'));

        // 3. Admin show page renders video preview tag correctly
        $adminView = $this->actingAs($this->admin)->get(route('admin.applications.show', $application));
        $adminView->assertOk();
        $adminView->assertSee('<video controls', false);
        $adminView->assertSee('type="video/mp4"', false);
        $adminView->assertSee('Download Video', false);

        // 4. Portal documents page displays MP4 Video badge
        $portalView = $this->actingAs($this->applicant)->get(route('portal.applications.documents.index', $application));
        $portalView->assertOk();
        $portalView->assertSee('MP4 Video');
    }
}
