<?php

namespace Tests\Feature;

use App\Enums\JobApplicationStatus;
use App\Enums\JobVacancyStatus;
use App\Enums\RecruitmentPeriodStatus;
use App\Exceptions\Recruitment\DocumentIntegrityException;
use App\Exceptions\Recruitment\MissingRequiredDocumentsException;
use App\Models\ApplicantEducation;
use App\Models\ApplicantProfile;
use App\Models\ApplicationDocument;
use App\Models\ApplicationSnapshot;
use App\Models\JobApplication;
use App\Models\JobVacancy;
use App\Models\RecruitmentPeriod;
use App\Models\User;
use App\Models\VacancyDocumentRequirement;
use App\Services\Recruitment\ApplicationDocumentService;
use App\Services\Recruitment\ApplicationSubmissionService;
use DomainException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ApplicationDocumentAndSubmissionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake(ApplicationDocumentService::DISK_NAME);
    }

    protected function createApplicantUser(bool $completeProfile = true, array $userAttributes = []): User
    {
        static $sequence = 1;
        $seq = $sequence++;

        $user = User::create(array_merge([
            'name' => 'Pelamar Dokumen ' . $seq,
            'email' => "pelamar.doc{$seq}@example.com",
            'phone' => sprintf('62812399%05d', $seq),
            'registration_number' => sprintf('PLM-2026-%06d', $seq),
            'password' => 'Password123',
            'is_active' => true,
        ], $userAttributes));

        if ($completeProfile) {
            $profile = ApplicantProfile::create([
                'user_id' => $user->id,
                'nik' => sprintf('3510998877%06d', $seq),
                'birth_place' => 'Banyuwangi',
                'birth_date' => '1995-08-17',
                'gender' => 'Laki-laki',
                'religion' => 'Islam',
                'address_ktp' => 'Jl. Ikan Hiu No. 12 Banyuwangi',
                'address_domicile' => 'Jl. Ikan Hiu No. 12 Banyuwangi',
                'same_as_ktp' => true,
            ]);

            ApplicantEducation::create([
                'applicant_profile_id' => $profile->id,
                'education_level' => 'S1',
                'institution_name' => 'Universitas Airlangga',
                'study_program' => 'Ilmu Keperawatan',
                'accreditation' => 'A',
                'gpa' => '3.80',
                'graduation_year' => 2020,
            ]);
        }

        return $user->fresh(['applicantProfile', 'applicantProfile.educations']);
    }

    protected function createVacancyWithRequirements(array $requirementsConfig = []): array
    {
        static $vacSeq = 1;
        $seq = $vacSeq++;

        $period = RecruitmentPeriod::create([
            'name' => 'Rekrutmen Medis RS Fatimah ' . $seq,
            'code' => 'PR-MED-2026-' . sprintf('%03d', $seq),
            'status' => RecruitmentPeriodStatus::OPEN,
            'registration_start_at' => now()->subDays(5),
            'registration_end_at' => now()->addDays(20),
        ]);

        $vacancy = JobVacancy::create([
            'recruitment_period_id' => $period->id,
            'code' => 'MED-IGD-' . sprintf('%03d', $seq),
            'title' => 'Perawat Gawat Darurat ' . $seq,
            'unit' => 'IGD',
            'employment_type' => 'PKWT',
            'location' => 'RSI Fatimah Banyuwangi',
            'quota' => 5,
            'status' => JobVacancyStatus::OPEN,
            'open_at' => now()->subDays(5),
            'close_at' => now()->addDays(20),
        ]);

        $requirements = [];
        if (empty($requirementsConfig)) {
            $requirementsConfig = [
                [
                    'name' => 'Surat Lamaran & CV',
                    'is_required' => true,
                    'allowed_mimes' => 'application/pdf',
                    'max_size_kb' => 2048,
                ],
                [
                    'name' => 'Ijazah & Transkrip Nilai',
                    'is_required' => true,
                    'allowed_mimes' => 'application/pdf',
                    'max_size_kb' => 3072,
                ],
                [
                    'name' => 'Sertifikat Pelatihan Tambahan',
                    'is_required' => false,
                    'allowed_mimes' => 'application/pdf,image/jpeg',
                    'max_size_kb' => 2048,
                ],
            ];
        }

        foreach ($requirementsConfig as $rc) {
            $requirements[] = VacancyDocumentRequirement::create(array_merge([
                'job_vacancy_id' => $vacancy->id,
                'description' => 'Petunjuk pengunggahan dokumen',
                'sort_order' => 1,
            ], $rc));
        }

        return [$period, $vacancy, $requirements];
    }

    protected function createDraftApplication(User $user, JobVacancy $vacancy): JobApplication
    {
        return JobApplication::create([
            'user_id' => $user->id,
            'recruitment_period_id' => $vacancy->recruitment_period_id,
            'job_vacancy_id' => $vacancy->id,
            'status' => JobApplicationStatus::DRAFT,
        ]);
    }

    // =========================================================================
    // 1. MIME CONFIGURATION & FAIL CLOSED TESTS
    // =========================================================================

    public function test_allowed_mimes_canonicalization_supports_both_mime_and_extension_tokens(): void
    {
        $service = app(ApplicationDocumentService::class);

        // Config dengan ekstensi token
        $canonical1 = $service->parseAllowedMimes('pdf, jpg, png');
        $this->assertEquals(['application/pdf', 'image/jpeg', 'image/png'], $canonical1);

        // Config dengan MIME token
        $canonical2 = $service->parseAllowedMimes('application/pdf, image/jpeg, image/png');
        $this->assertEquals(['application/pdf', 'image/jpeg', 'image/png'], $canonical2);

        // Config kombinasi dengan whitespace & duplicate
        $canonical3 = $service->parseAllowedMimes(' pdf, application/pdf , jpeg, image/jpeg ');
        $this->assertEquals(['application/pdf', 'image/jpeg'], $canonical3);
    }

    public function test_unknown_mime_token_fails_closed(): void
    {
        $service = app(ApplicationDocumentService::class);

        $this->expectException(DomainException::class);
        $this->expectExceptionMessage("Tipe berkas 'exe' tidak didukung oleh sistem rekrutmen.");

        $service->parseAllowedMimes('pdf, exe, png');
    }

    public function test_empty_or_null_mime_config_fails_closed(): void
    {
        $service = app(ApplicationDocumentService::class);

        $this->expectException(DomainException::class);
        $service->parseAllowedMimes('');
    }

    public function test_invalid_or_null_max_size_kb_fails_closed(): void
    {
        $service = app(ApplicationDocumentService::class);

        $this->expectException(DomainException::class);
        $service->validateMaxSizeBytes(0);
    }

    public function test_null_max_size_kb_fails_closed(): void
    {
        $service = app(ApplicationDocumentService::class);

        $this->expectException(DomainException::class);
        $service->validateMaxSizeBytes(null);
    }

    // =========================================================================
    // 2. DOCUMENT UPLOAD & REPLACEMENT TESTS
    // =========================================================================

    public function test_applicant_can_upload_document_to_draft_application(): void
    {
        $user = $this->createApplicantUser(true);
        [$period, $vacancy, $requirements] = $this->createVacancyWithRequirements();
        $application = $this->createDraftApplication($user, $vacancy);
        $req = $requirements[0];

        $fakePdf = UploadedFile::fake()->create('surat_lamaran.pdf', 500, 'application/pdf');

        $response = $this->actingAs($user)
            ->post(route('portal.applications.documents.store', [$application, $req]), [
                'file' => $fakePdf,
            ]);

        $response->assertRedirect(route('portal.applications.documents.index', $application));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('application_documents', [
            'job_application_id' => $application->id,
            'vacancy_document_requirement_id' => $req->id,
            'original_name' => 'surat_lamaran.pdf',
            'mime_type' => 'application/pdf',
            'verification_status' => 'pending',
            'verification_note' => null,
        ]);

        $document = ApplicationDocument::first();
        Storage::disk(ApplicationDocumentService::DISK_NAME)->assertExists($document->stored_path);
    }

    public function test_applicant_cannot_upload_document_for_requirement_belonging_to_another_vacancy(): void
    {
        $user = $this->createApplicantUser(true);
        [$period1, $vacancy1, $requirements1] = $this->createVacancyWithRequirements();
        [$period2, $vacancy2, $requirements2] = $this->createVacancyWithRequirements();

        $application = $this->createDraftApplication($user, $vacancy1);
        $otherReq = $requirements2[0];

        $fakePdf = UploadedFile::fake()->create('surat.pdf', 300, 'application/pdf');

        $response = $this->actingAs($user)
            ->post(route('portal.applications.documents.store', [$application, $otherReq]), [
                'file' => $fakePdf,
            ]);

        $response->assertNotFound();
    }

    public function test_replacing_document_stores_new_file_deletes_old_file_and_resets_verification_status(): void
    {
        $user = $this->createApplicantUser(true);
        [$period, $vacancy, $requirements] = $this->createVacancyWithRequirements();
        $application = $this->createDraftApplication($user, $vacancy);
        $req = $requirements[0];

        $disk = Storage::disk(ApplicationDocumentService::DISK_NAME);

        // Upload pertama
        $fileA = UploadedFile::fake()->create('berkas_awal.pdf', 400, 'application/pdf');
        $this->actingAs($user)
            ->post(route('portal.applications.documents.store', [$application, $req]), ['file' => $fileA]);

        $docA = ApplicationDocument::where('job_application_id', $application->id)->first();
        $oldPath = $docA->stored_path;
        $disk->assertExists($oldPath);

        // Simulasi bahwa dokumen lama sempat diverifikasi
        $docA->update([
            'verification_status' => 'verified',
            'verification_note' => 'Dokumen lengkap dan valid.',
        ]);

        // Upload kedua (Replace)
        $fileB = UploadedFile::fake()->create('berkas_revisi.pdf', 600, 'application/pdf');
        $this->actingAs($user)
            ->post(route('portal.applications.documents.store', [$application, $req]), ['file' => $fileB]);

        $docB = $docA->fresh();
        $newPath = $docB->stored_path;

        // Path baru tersimpan, path lama terhapus
        $this->assertNotEquals($oldPath, $newPath);
        $disk->assertExists($newPath);
        $disk->assertMissing($oldPath);

        // Metadata ter-update dan status verifikasi ter-reset
        $this->assertEquals('berkas_revisi.pdf', $docB->original_name);
        $this->assertEquals('pending', $docB->verification_status);
        $this->assertNull($docB->verification_note);
        $this->assertEquals(1, ApplicationDocument::count());
    }

    public function test_applicant_can_delete_document_when_draft(): void
    {
        $user = $this->createApplicantUser(true);
        [$period, $vacancy, $requirements] = $this->createVacancyWithRequirements();
        $application = $this->createDraftApplication($user, $vacancy);
        $req = $requirements[0];

        $disk = Storage::disk(ApplicationDocumentService::DISK_NAME);

        $file = UploadedFile::fake()->create('ijazah.pdf', 500, 'application/pdf');
        $this->actingAs($user)
            ->post(route('portal.applications.documents.store', [$application, $req]), ['file' => $file]);

        $document = ApplicationDocument::first();
        $storedPath = $document->stored_path;
        $disk->assertExists($storedPath);

        // Hapus
        $response = $this->actingAs($user)
            ->delete(route('portal.applications.documents.destroy', [$application, $document]));

        $response->assertRedirect(route('portal.applications.documents.index', $application));
        $this->assertDatabaseMissing('application_documents', ['id' => $document->id]);
        $disk->assertMissing($storedPath);
    }

    public function test_applicant_cannot_delete_document_belonging_to_another_application(): void
    {
        $user1 = $this->createApplicantUser(true);
        $user2 = $this->createApplicantUser(true);
        [$period, $vacancy, $requirements] = $this->createVacancyWithRequirements();

        $app1 = $this->createDraftApplication($user1, $vacancy);
        $app2 = $this->createDraftApplication($user2, $vacancy);
        $req = $requirements[0];

        $file = UploadedFile::fake()->create('doc.pdf', 300, 'application/pdf');
        $this->actingAs($user1)->post(route('portal.applications.documents.store', [$app1, $req]), ['file' => $file]);
        $doc1 = ApplicationDocument::first();

        // User2 mencoba delete dokumen User1
        $response = $this->actingAs($user2)
            ->delete(route('portal.applications.documents.destroy', [$app2, $doc1]));

        $response->assertNotFound();
    }

    // =========================================================================
    // 3. PRIVATE DOWNLOAD & SECURITY TESTS
    // =========================================================================

    public function test_applicant_can_download_own_document(): void
    {
        $user = $this->createApplicantUser(true);
        [$period, $vacancy, $requirements] = $this->createVacancyWithRequirements();
        $application = $this->createDraftApplication($user, $vacancy);
        $req = $requirements[0];

        $file = UploadedFile::fake()->create('dokumen_rahasia.pdf', 300, 'application/pdf');
        $this->actingAs($user)->post(route('portal.applications.documents.store', [$application, $req]), ['file' => $file]);
        $doc = ApplicationDocument::first();

        $response = $this->actingAs($user)
            ->get(route('portal.applications.documents.download', [$application, $doc]));

        $response->assertOk();
        $response->assertHeader('Content-Type', 'application/pdf');
        $response->assertHeader('Content-Disposition', 'attachment; filename="dokumen_rahasia.pdf"');
    }

    public function test_other_user_cannot_download_applicant_document(): void
    {
        $user1 = $this->createApplicantUser(true);
        $user2 = $this->createApplicantUser(true);
        [$period, $vacancy, $requirements] = $this->createVacancyWithRequirements();

        $app1 = $this->createDraftApplication($user1, $vacancy);
        $req = $requirements[0];

        $file = UploadedFile::fake()->create('dokumen1.pdf', 300, 'application/pdf');
        $this->actingAs($user1)->post(route('portal.applications.documents.store', [$app1, $req]), ['file' => $file]);
        $doc1 = ApplicationDocument::first();

        $response = $this->actingAs($user2)
            ->get(route('portal.applications.documents.download', [$app1, $doc1]));

        $response->assertForbidden();
    }

    // =========================================================================
    // 4. FINAL SUBMIT INTEGRITY & SECURITY ORDER TESTS
    // =========================================================================

    public function test_cross_user_submitted_application_cannot_use_idempotent_early_return(): void
    {
        $user1 = $this->createApplicantUser(true);
        $user2 = $this->createApplicantUser(true);
        [$period, $vacancy, $requirements] = $this->createVacancyWithRequirements();

        $app = JobApplication::create([
            'user_id' => $user1->id,
            'recruitment_period_id' => $vacancy->recruitment_period_id,
            'job_vacancy_id' => $vacancy->id,
            'status' => JobApplicationStatus::SUBMITTED,
            'application_number' => 'LAM-TEST-000001',
            'submitted_at' => now(),
            'locked_at' => now(),
        ]);

        $service = app(ApplicationSubmissionService::class);

        // User2 memanggil submit pada aplikasi milik User1 yang sudah SUBMITTED
        // Security rule: ownership check harus dievaluasi SEBELUM idempotent return
        $this->expectException(DomainException::class);
        $this->expectExceptionMessage('Otorisasi gagal: Lamaran bukan milik akun pengguna yang aktif.');

        $service->submit($user2, $app);
    }

    public function test_idempotent_return_works_for_actual_owner(): void
    {
        $user = $this->createApplicantUser(true);
        [$period, $vacancy, $requirements] = $this->createVacancyWithRequirements();

        $app = JobApplication::create([
            'user_id' => $user->id,
            'recruitment_period_id' => $vacancy->recruitment_period_id,
            'job_vacancy_id' => $vacancy->id,
            'status' => JobApplicationStatus::SUBMITTED,
            'application_number' => 'LAM-MED-000042',
            'submitted_at' => now(),
            'locked_at' => now(),
        ]);

        ApplicationSnapshot::create([
            'job_application_id' => $app->id,
            'recruitment_period_data' => ['name' => $period->name],
            'personal_data' => ['name' => $user->name],
            'educations' => [],
            'licenses' => [],
            'certifications' => [],
            'work_experiences' => [],
            'vacancy_data' => ['title' => $vacancy->title],
            'captured_at' => now(),
        ]);

        $service = app(ApplicationSubmissionService::class);
        $result = $service->submit($user, $app);

        $this->assertEquals($app->id, $result->id);
        $this->assertEquals('LAM-MED-000042', $result->application_number);
    }

    public function test_idempotent_submit_fails_integrity_check_if_snapshot_is_missing(): void
    {
        $user = $this->createApplicantUser(true);
        [$period, $vacancy, $requirements] = $this->createVacancyWithRequirements();

        // Aplikasi SUBMITTED tetapi snapshot HILANG/RUSAK
        $app = JobApplication::create([
            'user_id' => $user->id,
            'recruitment_period_id' => $vacancy->recruitment_period_id,
            'job_vacancy_id' => $vacancy->id,
            'status' => JobApplicationStatus::SUBMITTED,
            'application_number' => 'LAM-MED-000099',
            'submitted_at' => now(),
            'locked_at' => now(),
        ]);

        $service = app(ApplicationSubmissionService::class);

        $this->expectException(DocumentIntegrityException::class);
        $this->expectExceptionMessage('Integritas data lamaran tidak valid: Data pengiriman historis rusak atau tidak lengkap.');

        $service->submit($user, $app);
    }

    public function test_idempotent_submit_fails_integrity_check_if_application_number_is_missing(): void
    {
        $user = $this->createApplicantUser(true);
        [$period, $vacancy, $requirements] = $this->createVacancyWithRequirements();

        // Aplikasi SUBMITTED tetapi application_number kosong/null
        $app = JobApplication::create([
            'user_id' => $user->id,
            'recruitment_period_id' => $vacancy->recruitment_period_id,
            'job_vacancy_id' => $vacancy->id,
            'status' => JobApplicationStatus::SUBMITTED,
            'application_number' => null,
            'submitted_at' => now(),
            'locked_at' => now(),
        ]);

        ApplicationSnapshot::create([
            'job_application_id' => $app->id,
            'recruitment_period_data' => ['name' => $period->name],
            'personal_data' => ['name' => $user->name],
            'educations' => [],
            'licenses' => [],
            'certifications' => [],
            'work_experiences' => [],
            'vacancy_data' => ['title' => $vacancy->title],
            'captured_at' => now(),
        ]);

        $service = app(ApplicationSubmissionService::class);

        $this->expectException(DocumentIntegrityException::class);
        $this->expectExceptionMessage('Integritas data lamaran tidak valid: Data pengiriman historis rusak atau tidak lengkap.');

        $service->submit($user, $app);
    }

    public function test_final_submit_rejected_if_required_document_is_missing(): void
    {
        $user = $this->createApplicantUser(true);
        [$period, $vacancy, $requirements] = $this->createVacancyWithRequirements();
        $application = $this->createDraftApplication($user, $vacancy);

        // Hanya upload 1 dari 2 required requirements
        $file = UploadedFile::fake()->create('surat.pdf', 300, 'application/pdf');
        $this->actingAs($user)->post(route('portal.applications.documents.store', [$application, $requirements[0]]), ['file' => $file]);

        $service = app(ApplicationSubmissionService::class);

        $this->expectException(MissingRequiredDocumentsException::class);
        $service->submit($user, $application);
    }

    public function test_final_submit_rejected_if_db_document_row_exists_but_physical_file_is_missing(): void
    {
        $user = $this->createApplicantUser(true);
        [$period, $vacancy, $requirements] = $this->createVacancyWithRequirements();
        $application = $this->createDraftApplication($user, $vacancy);

        // Upload kedua required documents
        $file1 = UploadedFile::fake()->create('surat.pdf', 300, 'application/pdf');
        $file2 = UploadedFile::fake()->create('ijazah.pdf', 300, 'application/pdf');
        $this->actingAs($user)->post(route('portal.applications.documents.store', [$application, $requirements[0]]), ['file' => $file1]);
        $this->actingAs($user)->post(route('portal.applications.documents.store', [$application, $requirements[1]]), ['file' => $file2]);

        // Simulasikan file fisik berkas kedua hilang dari disk
        $doc2 = ApplicationDocument::where('vacancy_document_requirement_id', $requirements[1]->id)->first();
        Storage::disk(ApplicationDocumentService::DISK_NAME)->delete($doc2->stored_path);

        $service = app(ApplicationSubmissionService::class);

        $this->expectException(DocumentIntegrityException::class);
        $this->expectExceptionMessage("File fisik untuk dokumen '{$requirements[1]->name}' tidak ditemukan pada storage server.");

        $service->submit($user, $application);
    }

    public function test_final_submit_revalidates_and_rejects_if_requirement_max_size_changed_after_upload(): void
    {
        $user = $this->createApplicantUser(true);
        [$period, $vacancy, $requirements] = $this->createVacancyWithRequirements();
        $application = $this->createDraftApplication($user, $vacancy);

        // Upload 2.5 MB (awalnya diizinkan max 3 MB untuk req[1])
        $file1 = UploadedFile::fake()->create('surat.pdf', 1000, 'application/pdf');
        $file2 = UploadedFile::fake()->create('ijazah.pdf', 2500, 'application/pdf');
        $this->actingAs($user)->post(route('portal.applications.documents.store', [$application, $requirements[0]]), ['file' => $file1]);
        $this->actingAs($user)->post(route('portal.applications.documents.store', [$application, $requirements[1]]), ['file' => $file2]);

        // Admin mengubah ketentuan: max size diturunkan menjadi 1 MB
        $requirements[1]->update(['max_size_kb' => 1024]);

        $service = app(ApplicationSubmissionService::class);

        $this->expectException(DocumentIntegrityException::class);
        $this->expectExceptionMessage("Ukuran berkas '{$requirements[1]->name}' melebihi ketentuan ukuran terbaru lowongan.");

        $service->submit($user, $application);
    }

    public function test_final_submit_revalidates_and_rejects_if_requirement_mimes_changed_after_upload(): void
    {
        $user = $this->createApplicantUser(true);
        // Formasi dengan requirement mengizinkan JPG
        [$period, $vacancy, $requirements] = $this->createVacancyWithRequirements([
            [
                'name' => 'KTP',
                'is_required' => true,
                'allowed_mimes' => 'application/pdf,image/jpeg',
                'max_size_kb' => 2048,
            ],
        ]);
        $application = $this->createDraftApplication($user, $vacancy);

        // Pelamar upload file JPG
        $file = UploadedFile::fake()->create('ktp.jpg', 500, 'image/jpeg');
        $this->actingAs($user)->post(route('portal.applications.documents.store', [$application, $requirements[0]]), ['file' => $file]);

        // Admin mengubah ketentuan lowongan: hanya PDF yang diizinkan
        $requirements[0]->update(['allowed_mimes' => 'application/pdf']);

        $service = app(ApplicationSubmissionService::class);

        $this->expectException(DocumentIntegrityException::class);
        $this->expectExceptionMessage("Format berkas '{$requirements[0]->name}' (image/jpeg) sudah tidak lagi diizinkan");

        $service->submit($user, $application);
    }

    public function test_successful_final_submit_locks_application_generates_official_number_and_creates_snapshot(): void
    {
        $user = $this->createApplicantUser(true);
        [$period, $vacancy, $requirements] = $this->createVacancyWithRequirements([
            [
                'name' => 'Surat Lamaran',
                'is_required' => true,
                'allowed_mimes' => 'application/pdf',
                'max_size_kb' => 2048,
            ],
        ]);
        $application = $this->createDraftApplication($user, $vacancy);

        $file = UploadedFile::fake()->create('surat.pdf', 300, 'application/pdf');
        $this->actingAs($user)->post(route('portal.applications.documents.store', [$application, $requirements[0]]), ['file' => $file]);

        $response = $this->actingAs($user)->post(route('portal.applications.submit', $application), [
            'declaration' => 1,
        ]);

        $application->refresh();

        $response->assertRedirect(route('portal.applications.show', $application));
        $response->assertSessionHas('success');

        // Verifikasi state locked & submitted
        $this->assertEquals(JobApplicationStatus::SUBMITTED, $application->status);
        $this->assertNotNull($application->submitted_at);
        $this->assertNotNull($application->locked_at);
        $this->assertEquals($application->submitted_at->toDateTimeString(), $application->locked_at->toDateTimeString());

        // Verifikasi format nomor lamaran
        $this->assertMatchesRegularExpression('/^LAM-[A-Z0-9\-]+-\d{6}$/', $application->application_number);
        $this->assertStringContainsString(sprintf('%06d', $application->id), $application->application_number);

        // Verifikasi snapshot terbuat dengan captured_at identik
        $snapshot = $application->snapshot;
        $this->assertNotNull($snapshot);
        $this->assertEquals($application->submitted_at->toDateTimeString(), $snapshot->captured_at->toDateTimeString());
        $this->assertEquals($user->name, $snapshot->personal_data['name']);
        $this->assertEquals($vacancy->title, $snapshot->vacancy_data['title']);

        // Verifikasi bahwa pelamar tidak bisa lagi upload atau hapus berkas paska submit
        $newFile = UploadedFile::fake()->create('baru.pdf', 200, 'application/pdf');
        $uploadAfterSubmit = $this->actingAs($user)
            ->post(route('portal.applications.documents.store', [$application, $requirements[0]]), ['file' => $newFile]);
        $uploadAfterSubmit->assertForbidden();

        $doc = $application->documents->first();
        $deleteAfterSubmit = $this->actingAs($user)
            ->delete(route('portal.applications.documents.destroy', [$application, $doc]));
        $deleteAfterSubmit->assertForbidden();

        // Verifikasi pemilik tetap dapat mendownload berkasnya paska submit
        $downloadAfterSubmit = $this->actingAs($user)
            ->get(route('portal.applications.documents.download', [$application, $doc]));
        $downloadAfterSubmit->assertOk();
    }

    public function test_final_submit_rejected_without_declaration_and_application_remains_draft(): void
    {
        $user = $this->createApplicantUser(true);
        [$period, $vacancy, $requirements] = $this->createVacancyWithRequirements([
            [
                'name' => 'Surat Lamaran',
                'is_required' => true,
                'allowed_mimes' => 'application/pdf',
                'max_size_kb' => 2048,
            ],
        ]);
        $application = $this->createDraftApplication($user, $vacancy);

        $file = UploadedFile::fake()->create('surat.pdf', 300, 'application/pdf');
        $this->actingAs($user)->post(route('portal.applications.documents.store', [$application, $requirements[0]]), ['file' => $file]);

        // Submit TANPA checkbox declaration
        $response = $this->actingAs($user)->post(route('portal.applications.submit', $application), []);

        $response->assertSessionHasErrors(['declaration']);
        $this->assertEquals(JobApplicationStatus::DRAFT, $application->fresh()->status);
        $this->assertNull($application->fresh()->locked_at);
        $this->assertNull($application->fresh()->submitted_at);
        $this->assertNull($application->fresh()->application_number);
    }

    public function test_snapshot_contains_stable_master_ids_for_qualifications_requirements_and_stages(): void
    {
        $user = $this->createApplicantUser(true);
        [$period, $vacancy, $requirements] = $this->createVacancyWithRequirements([
            [
                'name' => 'Dokumen Syarat Wajib',
                'is_required' => true,
                'allowed_mimes' => 'application/pdf',
                'max_size_kb' => 2048,
            ],
        ]);

        $qualification = \App\Models\VacancyQualification::create([
            'job_vacancy_id' => $vacancy->id,
            'label' => 'Pendidikan Terakhir',
            'value' => 'S1 Keperawatan Ners',
            'sort_order' => 1,
        ]);

        $stage = \App\Models\VacancySelectionStage::create([
            'job_vacancy_id' => $vacancy->id,
            'name' => 'Ujian Tulis CBT',
            'description' => 'Tes kompetensi dasar komputer',
            'sort_order' => 1,
            'start_at' => now()->addDays(2),
            'end_at' => now()->addDays(3),
        ]);

        $application = $this->createDraftApplication($user, $vacancy);

        $file = UploadedFile::fake()->create('syarat.pdf', 300, 'application/pdf');
        $this->actingAs($user)->post(route('portal.applications.documents.store', [$application, $requirements[0]]), ['file' => $file]);

        $this->actingAs($user)->post(route('portal.applications.submit', $application), [
            'declaration' => 1,
        ]);

        $snapshot = $application->fresh()->snapshot;
        $this->assertNotNull($snapshot);

        // Verifikasi ID pada qualifications
        $this->assertNotEmpty($snapshot->vacancy_data['qualifications']);
        $this->assertEquals($qualification->id, $snapshot->vacancy_data['qualifications'][0]['id']);
        $this->assertEquals('Pendidikan Terakhir', $snapshot->vacancy_data['qualifications'][0]['label']);

        // Verifikasi ID pada document_requirements
        $this->assertNotEmpty($snapshot->vacancy_data['document_requirements']);
        $this->assertEquals($requirements[0]->id, $snapshot->vacancy_data['document_requirements'][0]['id']);
        $this->assertEquals('Dokumen Syarat Wajib', $snapshot->vacancy_data['document_requirements'][0]['name']);

        // Verifikasi ID pada selection_stages
        $this->assertNotEmpty($snapshot->vacancy_data['selection_stages']);
        $this->assertEquals($stage->id, $snapshot->vacancy_data['selection_stages'][0]['id']);
        $this->assertEquals('Ujian Tulis CBT', $snapshot->vacancy_data['selection_stages'][0]['name']);
    }

    public function test_download_returns_safe_404_if_db_row_exists_but_physical_file_is_missing(): void
    {
        $user = $this->createApplicantUser(true);
        [$period, $vacancy, $requirements] = $this->createVacancyWithRequirements();
        $application = $this->createDraftApplication($user, $vacancy);

        $file = UploadedFile::fake()->create('dokumen_hilang.pdf', 300, 'application/pdf');
        $this->actingAs($user)->post(route('portal.applications.documents.store', [$application, $requirements[0]]), ['file' => $file]);
        $doc = ApplicationDocument::first();

        // Hapus file fisik secara sengaja di storage backend
        Storage::disk(ApplicationDocumentService::DISK_NAME)->delete($doc->stored_path);

        $response = $this->actingAs($user)
            ->get(route('portal.applications.documents.download', [$application, $doc]));

        // Safe 404 response tanpa membocorkan filesystem exception atau server internal path
        $response->assertNotFound();
    }

    public function test_uploaded_file_is_isolated_to_applicant_documents_disk_and_not_exposed_to_public_disk(): void
    {
        Storage::fake('public');

        $user = $this->createApplicantUser(true);
        [$period, $vacancy, $requirements] = $this->createVacancyWithRequirements();
        $application = $this->createDraftApplication($user, $vacancy);

        $file = UploadedFile::fake()->create('ijazah_rahasia.pdf', 300, 'application/pdf');
        $this->actingAs($user)->post(route('portal.applications.documents.store', [$application, $requirements[0]]), ['file' => $file]);
        $doc = ApplicationDocument::first();

        // Verifikasi file ADA di private disk
        Storage::disk(ApplicationDocumentService::DISK_NAME)->assertExists($doc->stored_path);

        // Verifikasi file TIDAK ADA di public disk
        Storage::disk('public')->assertMissing($doc->stored_path);

        // Verifikasi path tidak diawali dengan public
        $this->assertStringStartsNotWith('public/', $doc->stored_path);
    }

    // =========================================================================
    // 5. HISTORICAL VIEW VS LIVE PROFILE TESTS
    // =========================================================================

    public function test_review_page_redirects_to_show_after_submission(): void
    {
        $user = $this->createApplicantUser(true);
        [$period, $vacancy, $requirements] = $this->createVacancyWithRequirements();
        $application = $this->createDraftApplication($user, $vacancy);

        // Upload berkas & submit
        $file1 = UploadedFile::fake()->create('surat.pdf', 300, 'application/pdf');
        $file2 = UploadedFile::fake()->create('ijazah.pdf', 300, 'application/pdf');
        $this->actingAs($user)->post(route('portal.applications.documents.store', [$application, $requirements[0]]), ['file' => $file1]);
        $this->actingAs($user)->post(route('portal.applications.documents.store', [$application, $requirements[1]]), ['file' => $file2]);

        $this->actingAs($user)->post(route('portal.applications.submit', $application), [
            'declaration' => 1,
        ]);

        // Akses review setelah submit
        $response = $this->actingAs($user)->get(route('portal.applications.review', $application));

        $response->assertRedirect(route('portal.applications.show', $application));
    }

    public function test_submitted_application_detail_uses_snapshot_data_even_if_live_profile_changes(): void
    {
        $user = $this->createApplicantUser(true);
        [$period, $vacancy, $requirements] = $this->createVacancyWithRequirements([
            [
                'name' => 'Dokumen 1',
                'is_required' => true,
                'allowed_mimes' => 'application/pdf',
                'max_size_kb' => 2048,
            ],
        ]);
        $application = $this->createDraftApplication($user, $vacancy);

        $file = UploadedFile::fake()->create('doc.pdf', 300, 'application/pdf');
        $this->actingAs($user)->post(route('portal.applications.documents.store', [$application, $requirements[0]]), ['file' => $file]);

        $this->actingAs($user)->post(route('portal.applications.submit', $application), [
            'declaration' => 1,
        ]);

        $originalName = $user->name;

        // User mengubah profil aktifnya setelah submit
        $user->update(['name' => 'Nama Baru Pelamar Setelah Submit']);
        $user->applicantProfile->update(['address_ktp' => 'Alamat Baru Berubah']);

        // Buka halaman detail lamaran
        $response = $this->actingAs($user)->get(route('portal.applications.show', $application));

        $response->assertOk();
        // Harus menampilkan data historis snapshot (nama lama)
        $response->assertSee($originalName);
        $response->assertSee('Data Lamaran Resmi yang Dikirim (Snapshot Terkunci)');
        $response->assertDontSee('Alamat Baru Berubah');
    }

    public function test_submitted_application_without_snapshot_displays_integrity_warning_not_live_profile(): void
    {
        $user = $this->createApplicantUser(true);
        [$period, $vacancy, $requirements] = $this->createVacancyWithRequirements();

        // Aplikasi SUBMITTED tetapi tanpa snapshot (data integrity problem)
        $application = JobApplication::create([
            'user_id' => $user->id,
            'recruitment_period_id' => $vacancy->recruitment_period_id,
            'job_vacancy_id' => $vacancy->id,
            'status' => JobApplicationStatus::SUBMITTED,
            'application_number' => 'LAM-CORRUPT-000001',
            'submitted_at' => now(),
            'locked_at' => now(),
        ]);

        $response = $this->actingAs($user)->get(route('portal.applications.show', $application));

        $response->assertOk();
        $response->assertSee('Peringatan Integritas Data');
        $response->assertDontSee('IMMUTABLE SNAPSHOT');
    }
}
