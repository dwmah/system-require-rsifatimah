<?php

namespace Tests\Feature;

use App\Enums\ApplicationStageStatus;
use App\Enums\JobApplicationStatus;
use App\Enums\JobVacancyStatus;
use App\Enums\RecruitmentPeriodStatus;
use App\Models\ApplicantEducation;
use App\Models\ApplicantLicense;
use App\Models\ApplicantProfile;
use App\Models\ApplicantWorkExperience;
use App\Models\ApplicationDocument;
use App\Models\JobApplication;
use App\Models\JobVacancy;
use App\Models\RecruitmentPeriod;
use App\Models\User;
use App\Models\VacancyDocumentRequirement;
use App\Models\VacancyQualification;
use App\Models\VacancySelectionStage;
use App\Services\Recruitment\ApplicationDocumentService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AdminDocumentAutoApprovalAndBiodataTabTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected User $applicant;

    protected JobVacancy $vacancy;

    protected JobApplication $application;

    protected VacancyDocumentRequirement $reqKtp;

    protected VacancyDocumentRequirement $reqIjazah;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake(ApplicationDocumentService::DISK_NAME);

        // Admin
        $this->admin = User::create([
            'name' => 'Admin SDI',
            'email' => 'admin.sdi@rsifatimah.com',
            'password' => 'Password123',
            'role' => User::ROLE_ADMIN,
            'is_active' => true,
        ]);

        // Applicant
        $this->applicant = User::create([
            'name' => 'Dr. Budi Santoso',
            'email' => 'budi@example.com',
            'phone' => '081298765432',
            'registration_number' => 'REG-2026-00099',
            'password' => 'Password123',
            'role' => User::ROLE_APPLICANT,
            'is_active' => true,
        ]);

        $profile = ApplicantProfile::create([
            'user_id' => $this->applicant->id,
            'nik' => '3510123456780001',
            'birth_place' => 'Banyuwangi',
            'birth_date' => '1992-05-10',
            'gender' => 'male',
            'religion' => 'Islam',
            'address_ktp' => 'Jl. Jenderal Sudirman No. 45',
            'address_domicile' => 'Jl. Jenderal Sudirman No. 45',
            'same_as_ktp' => true,
        ]);

        ApplicantEducation::create([
            'applicant_profile_id' => $profile->id,
            'education_level' => 'Profesi Dokter',
            'institution_name' => 'Universitas Airlangga',
            'study_program' => 'Kedokteran Umum',
            'accreditation' => 'Unggul',
            'gpa' => '3.85',
            'graduation_year' => 2017,
        ]);

        ApplicantLicense::create([
            'applicant_profile_id' => $profile->id,
            'license_type' => 'STR Dokter Umum',
            'license_number' => '35.1.1.100.2.18.123456',
            'issued_at' => now()->subYears(2),
            'expires_at' => now()->addYears(3),
        ]);

        ApplicantWorkExperience::create([
            'applicant_profile_id' => $profile->id,
            'company_name' => 'Klinik Rawat Inap Fatimah Sehat',
            'position' => 'Dokter Jaga IGD',
            'start_date' => '2019-01-01',
            'end_date' => '2025-12-31',
            'description' => 'Menangani kegawatdaruratan medis pasien IGD.',
        ]);

        // Recruitment Period
        $period = RecruitmentPeriod::create([
            'name' => 'Rekrutmen Dokter RSI Fatimah',
            'code' => 'PER-DKT-2026',
            'status' => RecruitmentPeriodStatus::OPEN,
            'registration_start_at' => now()->subDays(2),
            'registration_end_at' => now()->addDays(15),
        ]);

        // Vacancy
        $this->vacancy = JobVacancy::create([
            'recruitment_period_id' => $period->id,
            'code' => 'VAC-DKT-01',
            'title' => 'Dokter Umum IGD',
            'unit' => 'Pelayanan Medis',
            'employment_type' => 'Full Time',
            'location' => 'RSI Fatimah Banyuwangi',
            'quota' => 2,
            'open_at' => now()->subDays(2),
            'close_at' => now()->addDays(15),
            'status' => JobVacancyStatus::OPEN,
        ]);

        VacancyQualification::create([
            'job_vacancy_id' => $this->vacancy->id,
            'label' => 'Pendidikan Formal',
            'value' => 'Profesi Dokter Umum (dr.)',
            'sort_order' => 1,
        ]);

        VacancyQualification::create([
            'job_vacancy_id' => $this->vacancy->id,
            'label' => 'STR Aktif',
            'value' => 'Memiliki STR Dokter Umum yang masih berlaku',
            'sort_order' => 2,
        ]);

        VacancySelectionStage::create([
            'job_vacancy_id' => $this->vacancy->id,
            'name' => 'Seleksi Administrasi',
            'sort_order' => 1,
        ]);

        VacancySelectionStage::create([
            'job_vacancy_id' => $this->vacancy->id,
            'name' => 'Ujian Tulis Kompetensi',
            'sort_order' => 2,
        ]);

        $this->reqKtp = VacancyDocumentRequirement::create([
            'job_vacancy_id' => $this->vacancy->id,
            'name' => 'KTP Asli',
            'is_required' => true,
            'sort_order' => 1,
        ]);

        $this->reqIjazah = VacancyDocumentRequirement::create([
            'job_vacancy_id' => $this->vacancy->id,
            'name' => 'Ijazah & Transkrip',
            'is_required' => true,
            'sort_order' => 2,
        ]);

        // Job Application
        $this->application = JobApplication::create([
            'user_id' => $this->applicant->id,
            'recruitment_period_id' => $period->id,
            'job_vacancy_id' => $this->vacancy->id,
            'application_number' => 'APP-2026-DKT-001',
            'status' => JobApplicationStatus::SUBMITTED,
            'submitted_at' => now(),
            'locked_at' => now(),
        ]);
    }

    public function test_application_detail_displays_biodata_tab_first_with_panel_and_tab_ordering(): void
    {
        $response = $this->actingAs($this->admin)->get(route('admin.applications.show', $this->application));

        $response->assertStatus(200);

        // Check Tab ordering: Biodata -> Berkas -> Tahapan
        $content = $response->getContent();
        $posBiodataTab = strpos($content, 'Biodata & Riwayat Pelamar');
        $posBerkasTab = strpos($content, 'Verifikasi Berkas Dokumen');
        $posTahapanTab = strpos($content, 'Tahapan Seleksi & Timeline');

        $this->assertNotFalse($posBiodataTab);
        $this->assertNotFalse($posBerkasTab);
        $this->assertNotFalse($posTahapanTab);
        $this->assertTrue($posBiodataTab < $posBerkasTab, 'Biodata tab should be before Berkas tab');
        $this->assertTrue($posBerkasTab < $posTahapanTab, 'Berkas tab should be before Tahapan tab');

        // Check activeTab default is profil/biodata
        $this->assertStringContainsString("activeTab: (new URLSearchParams(window.location.search).get('tab')) || 'profil'", $content);

        // Check Biodata verification panel is present
        $this->assertStringContainsString('Panel Verifikasi Kelayakan Biodata Pelamar', $content);
        $this->assertStringContainsString('NIK 16 Digit', $content);
        $this->assertStringContainsString('Profesi Dokter', $content);
        $this->assertStringContainsString('STR Aktif', $content);

        // Check manual verification decision panel on Berkas is removed
        $this->assertStringNotContainsString('Panel Keputusan Verifikasi Administrasi', $content);
    }

    public function test_marking_all_documents_valid_automatically_passes_administration(): void
    {
        // 1. Create 2 uploaded documents
        $docKtp = ApplicationDocument::create([
            'job_application_id' => $this->application->id,
            'vacancy_document_requirement_id' => $this->reqKtp->id,
            'stored_path' => 'documents/ktp.pdf',
            'original_name' => 'ktp_budi.pdf',
            'file_size' => 102400,
            'mime_type' => 'application/pdf',
            'verification_status' => 'pending',
        ]);

        $docIjazah = ApplicationDocument::create([
            'job_application_id' => $this->application->id,
            'vacancy_document_requirement_id' => $this->reqIjazah->id,
            'stored_path' => 'documents/ijazah.pdf',
            'original_name' => 'ijazah_budi.pdf',
            'file_size' => 204800,
            'mime_type' => 'application/pdf',
            'verification_status' => 'pending',
        ]);

        $this->assertEquals(JobApplicationStatus::SUBMITTED, $this->application->status);

        // 2. Admin verifies first document (KTP) as valid (redirects back with tab=berkas and anchor to doc row)
        $response1 = $this->actingAs($this->admin)->post(route('admin.applications.documents.verify', $docKtp), [
            'verification_status' => 'valid',
            'verification_note' => 'KTP jelas dan terbaca.',
        ]);

        $response1->assertRedirect(route('admin.applications.show', ['application' => $this->application, 'tab' => 'berkas']).'#doc-row-'.$docKtp->id);
        $docKtp->refresh();
        $this->assertEquals('valid', $docKtp->verification_status);
        $this->application->refresh();
        // Belum lolos karena ijazah masih pending
        $this->assertEquals(JobApplicationStatus::SUBMITTED, $this->application->status);

        // 3. Admin verifies second document as valid -> OTOMATIS LOLOS ADMINISTRASI BERKAS!
        $response2 = $this->actingAs($this->admin)->post(route('admin.applications.documents.verify', $docIjazah), [
            'verification_status' => 'valid',
            'verification_note' => 'Ijazah terlegalisir.',
        ]);

        $response2->assertRedirect(route('admin.applications.show', ['application' => $this->application, 'tab' => 'berkas']).'#doc-row-'.$docIjazah->id);
        $docIjazah->refresh();
        $this->assertEquals('valid', $docIjazah->verification_status);
        $this->application->refresh();

        $this->assertEquals(JobApplicationStatus::PASSED_ADMINISTRATION, $this->application->status);
        $this->assertNotNull($this->application->verified_at);
    }

    public function test_document_creation_starts_with_pending_status_and_displays_menunggu_review(): void
    {
        $doc = ApplicationDocument::create([
            'job_application_id' => $this->application->id,
            'vacancy_document_requirement_id' => $this->reqKtp->id,
            'stored_path' => 'documents/ktp.pdf',
            'original_name' => 'ktp_budi.pdf',
            'file_size' => 102400,
            'mime_type' => 'application/pdf',
        ]);

        $this->assertEquals('pending', $doc->verification_status);

        $response = $this->actingAs($this->admin)->get(route('admin.applications.show', $this->application));
        $response->assertStatus(200);
        $response->assertSee('Menunggu Review');
    }

    public function test_approving_biodata_passes_biodata_stage_without_modifying_documents(): void
    {
        // Setup vacancy with Stage 1: Pendaftaran Online & Review Portofolio, Stage 2: Seleksi Administrasi, Stage 3: Ujian Tulis
        $this->vacancy->selectionStages()->delete();

        $stage1 = VacancySelectionStage::create([
            'job_vacancy_id' => $this->vacancy->id,
            'name' => 'Pendaftaran Online & Review Portofolio',
            'sort_order' => 1,
        ]);

        $stage2 = VacancySelectionStage::create([
            'job_vacancy_id' => $this->vacancy->id,
            'name' => 'Seleksi Administrasi & Verifikasi STR',
            'sort_order' => 2,
        ]);

        $stage3 = VacancySelectionStage::create([
            'job_vacancy_id' => $this->vacancy->id,
            'name' => 'Ujian Tulis & Skill Test',
            'sort_order' => 3,
        ]);

        $this->application->update(['current_stage_id' => $stage1->id]);

        $doc = ApplicationDocument::create([
            'job_application_id' => $this->application->id,
            'vacancy_document_requirement_id' => $this->reqKtp->id,
            'stored_path' => 'documents/ktp.pdf',
            'original_name' => 'ktp_budi.pdf',
            'file_size' => 102400,
            'mime_type' => 'application/pdf',
            'verification_status' => 'pending',
        ]);

        // Admin approves biodata from the biodata panel (redirects back with tab=profil and #biodata-panel)
        $response = $this->actingAs($this->admin)->post(route('admin.applications.verify', $this->application), [
            'action' => 'approve',
            'admin_note' => 'Biodata sangat sesuai persyaratan.',
        ]);

        $response->assertRedirect(route('admin.applications.show', ['application' => $this->application, 'tab' => 'profil']).'#biodata-panel');
        $this->application->refresh();

        // Check Stage 1 history is PASSED
        $history1 = $this->application->stageHistories()->where('vacancy_selection_stage_id', $stage1->id)->first();
        $this->assertNotNull($history1);
        $this->assertEquals(ApplicationStageStatus::PASSED, $history1->status);
        $this->assertNotNull($history1->published_at);

        // Documents remain in pending state (verified separately)
        $doc->refresh();
        $this->assertEquals('pending', $doc->verification_status);

        // Application advances current stage to Stage 2 (Seleksi Administrasi / Berkas)
        $this->assertEquals($stage2->id, $this->application->current_stage_id);
    }

    public function test_updating_stage_to_in_progress_resets_application_and_documents_to_pending(): void
    {
        // 1. Setup stage and documents
        $stage = VacancySelectionStage::create([
            'job_vacancy_id' => $this->vacancy->id,
            'name' => 'Seleksi Administrasi',
            'sort_order' => 1,
        ]);

        $doc = ApplicationDocument::create([
            'job_application_id' => $this->application->id,
            'vacancy_document_requirement_id' => $this->reqKtp->id,
            'stored_path' => 'documents/ktp.pdf',
            'original_name' => 'ktp_budi.pdf',
            'file_size' => 102400,
            'mime_type' => 'application/pdf',
            'verification_status' => 'valid',
        ]);

        // Put application in passed state
        $this->application->update([
            'status' => JobApplicationStatus::PASSED_ADMINISTRATION,
            'verified_at' => now(),
            'verified_by' => $this->admin->id,
            'current_stage_id' => $stage->id,
        ]);

        $this->assertTrue($this->application->isPassedAdministration());

        // 2. Admin updates the stage to 'in_progress' (redirects back with tab=tahapan and #stage-item-)
        $response = $this->actingAs($this->admin)->post(route('admin.applications.update-stage', $this->application), [
            'stage_id' => $stage->id,
            'status' => 'in_progress',
            'stage_note' => 'Pemeriksaan ulang berkas dan biodata pelamar.',
        ]);

        $response->assertRedirect(route('admin.applications.show', ['application' => $this->application, 'tab' => 'tahapan']).'#stage-item-'.$stage->id);
        $this->application->refresh();

        // 3. Application returns to SUBMITTED status (so Tolak & Lolos buttons appear again in UI)
        $this->assertEquals(JobApplicationStatus::SUBMITTED, $this->application->status);
        $this->assertNull($this->application->verified_at);
        $this->assertFalse($this->application->isPassedAdministration());
        $this->assertFalse($this->application->isRejected());

        // 4. Documents are reset to 'pending' (Menunggu Review)
        $doc->refresh();
        $this->assertEquals('pending', $doc->verification_status);

        // 5. Admin page view shows the verification buttons again
        $showResponse = $this->actingAs($this->admin)->get(route('admin.applications.show', $this->application));
        $showResponse->assertStatus(200);
        $showResponse->assertSee('Tolak Biodata');
        $showResponse->assertSee('Lolos Verifikasi Biodata');
        $showResponse->assertSee('Menunggu Review');
    }

    public function test_application_detail_respects_tab_query_parameter(): void
    {
        // Request with tab=berkas
        $resBerkas = $this->actingAs($this->admin)->get(route('admin.applications.show', [
            'application' => $this->application,
            'tab' => 'berkas',
        ]));
        $resBerkas->assertStatus(200);
        $this->assertStringContainsString("|| 'berkas'", $resBerkas->getContent());

        // Request with tab=tahapan
        $resTahapan = $this->actingAs($this->admin)->get(route('admin.applications.show', [
            'application' => $this->application,
            'tab' => 'tahapan',
        ]));
        $resTahapan->assertStatus(200);
        $this->assertStringContainsString("|| 'tahapan'", $resTahapan->getContent());
    }

    public function test_admin_can_download_all_documents_as_zip(): void
    {
        $fakeFile1 = UploadedFile::fake()->create('ktp_custom.pdf', 200, 'application/pdf');
        $fakeFile2 = UploadedFile::fake()->create('ijazah_custom.pdf', 300, 'application/pdf');

        $path1 = $fakeFile1->store('documents', ApplicationDocumentService::DISK_NAME);
        $path2 = $fakeFile2->store('documents', ApplicationDocumentService::DISK_NAME);

        ApplicationDocument::create([
            'job_application_id' => $this->application->id,
            'vacancy_document_requirement_id' => $this->reqKtp->id,
            'original_name' => 'ktp_custom.pdf',
            'stored_path' => $path1,
            'file_size' => 200 * 1024,
            'mime_type' => 'application/pdf',
            'verification_status' => 'valid',
        ]);

        ApplicationDocument::create([
            'job_application_id' => $this->application->id,
            'vacancy_document_requirement_id' => $this->reqIjazah->id,
            'original_name' => 'ijazah_custom.pdf',
            'stored_path' => $path2,
            'file_size' => 300 * 1024,
            'mime_type' => 'application/pdf',
            'verification_status' => 'valid',
        ]);

        $response = $this->actingAs($this->admin)->get(route('admin.applications.documents.download-all', $this->application));
        $response->assertStatus(200);
        $this->assertStringContainsString('application/zip', $response->headers->get('content-type') ?? '');
    }

    public function test_admin_view_shows_preview_and_download_all_button_when_all_documents_are_valid(): void
    {
        $fakeFile1 = UploadedFile::fake()->create('ktp_custom.pdf', 200, 'application/pdf');
        $fakeFile2 = UploadedFile::fake()->create('ijazah_custom.pdf', 300, 'application/pdf');

        $path1 = $fakeFile1->store('documents', ApplicationDocumentService::DISK_NAME);
        $path2 = $fakeFile2->store('documents', ApplicationDocumentService::DISK_NAME);

        ApplicationDocument::create([
            'job_application_id' => $this->application->id,
            'vacancy_document_requirement_id' => $this->reqKtp->id,
            'original_name' => 'ktp_custom.pdf',
            'stored_path' => $path1,
            'file_size' => 200 * 1024,
            'mime_type' => 'application/pdf',
            'verification_status' => 'valid',
        ]);

        ApplicationDocument::create([
            'job_application_id' => $this->application->id,
            'vacancy_document_requirement_id' => $this->reqIjazah->id,
            'original_name' => 'ijazah_custom.pdf',
            'stored_path' => $path2,
            'file_size' => 300 * 1024,
            'mime_type' => 'application/pdf',
            'verification_status' => 'valid',
        ]);

        $response = $this->actingAs($this->admin)->get(route('admin.applications.show', [
            'application' => $this->application,
            'tab' => 'berkas',
        ]));

        $response->assertStatus(200);
        $response->assertSee('Seluruh Berkas Persyaratan Terverifikasi Valid');
        $response->assertSee('Preview &amp; Unduh Semua Berkas', false);
        $response->assertSee('Unduh Semua (.ZIP)');
    }
}
