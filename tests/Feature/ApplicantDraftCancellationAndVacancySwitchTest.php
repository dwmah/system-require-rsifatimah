<?php

namespace Tests\Feature;

use App\Enums\JobApplicationStatus;
use App\Enums\JobVacancyStatus;
use App\Enums\RecruitmentPeriodStatus;
use App\Models\ApplicantCertification;
use App\Models\ApplicantEducation;
use App\Models\ApplicantLicense;
use App\Models\ApplicantProfile;
use App\Models\ApplicantWorkExperience;
use App\Models\JobApplication;
use App\Models\JobVacancy;
use App\Models\RecruitmentPeriod;
use App\Models\User;
use App\Models\VacancyDocumentRequirement;
use App\Services\Recruitment\ApplicationDocumentService;
use App\Services\Recruitment\JobApplicationService;
use DomainException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ApplicantDraftCancellationAndVacancySwitchTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake(ApplicationDocumentService::DISK_NAME);
    }

    protected function createCompleteApplicantUser(array $userAttributes = []): User
    {
        static $sequence = 1;
        $seq = $sequence++;

        $user = User::create(array_merge([
            'name' => 'Pelamar Lengkap '.$seq,
            'email' => "pelamar{$seq}@rsifatimah.test",
            'phone' => sprintf('0812345%05d', $seq),
            'registration_number' => sprintf('PLM-2026-%06d', $seq),
            'password' => 'Password123!',
            'is_active' => true,
        ], $userAttributes));

        $profile = ApplicantProfile::create([
            'user_id' => $user->id,
            'nik' => sprintf('3510123456%06d', $seq),
            'gender' => 'female',
            'birth_place' => 'Banyuwangi',
            'birth_date' => '1998-07-20',
            'religion' => 'Islam',
            'address_ktp' => 'Jl. Ijen No. 10 Banyuwangi',
            'address_domicile' => 'Jl. Ijen No. 10 Banyuwangi',
            'same_as_ktp' => true,
            'phone' => $user->phone,
            'photo_path' => 'profile-photos/test.jpg',
        ]);

        ApplicantEducation::create([
            'applicant_profile_id' => $profile->id,
            'education_level' => 'S1',
            'institution_name' => 'Universitas Airlangga',
            'study_program' => 'Ilmu Keperawatan',
            'accreditation' => 'A',
            'gpa' => '3.80',
            'graduation_year' => 2021,
        ]);

        ApplicantLicense::create([
            'applicant_profile_id' => $profile->id,
            'license_type' => 'STR',
            'license_number' => sprintf('STR-12345%05d', $seq),
            'valid_until' => now()->addYears(3)->toDateString(),
            'is_permanent' => false,
        ]);

        ApplicantCertification::create([
            'applicant_profile_id' => $profile->id,
            'name' => 'Basic Trauma Cardiac Life Support (BTCLS)',
            'issuer' => 'Kemenkes RI',
            'issued_at' => now()->subYears(2)->toDateString(),
            'expires_at' => now()->addYears(2)->toDateString(),
        ]);

        ApplicantWorkExperience::create([
            'applicant_profile_id' => $profile->id,
            'company_name' => 'Klinik Rawat Inap Fatimah',
            'position' => 'Perawat Pelaksana',
            'start_date' => now()->subYears(2)->toDateString(),
            'end_date' => now()->subMonths(3)->toDateString(),
            'description' => 'Asuhan keperawatan pasien rawat inap.',
        ]);

        return $user->fresh([
            'applicantProfile',
            'applicantProfile.educations',
            'applicantProfile.licenses',
            'applicantProfile.certifications',
            'applicantProfile.workExperiences',
        ]);
    }

    protected function createRecruitmentPeriod(array $attributes = []): RecruitmentPeriod
    {
        static $pSeq = 1;
        $seq = $pSeq++;

        return RecruitmentPeriod::create(array_merge([
            'name' => 'Periode Rekrutmen 2026 #'.$seq,
            'code' => 'PR-2026-'.sprintf('%03d', $seq),
            'status' => RecruitmentPeriodStatus::OPEN,
            'registration_start_at' => now()->subDays(5),
            'registration_end_at' => now()->addDays(15),
        ], $attributes));
    }

    protected function createVacancy(RecruitmentPeriod $period, array $attributes = []): JobVacancy
    {
        static $vSeq = 1;
        $seq = $vSeq++;

        return JobVacancy::create(array_merge([
            'recruitment_period_id' => $period->id,
            'code' => 'POS-2026-'.sprintf('%03d', $seq),
            'title' => 'Formasi Lowongan '.$seq,
            'unit' => 'Instalasi Pelayanan',
            'employment_type' => 'PKWT',
            'quota' => 3,
            'location' => 'RSI Fatimah Banyuwangi',
            'status' => JobVacancyStatus::OPEN,
            'open_at' => now()->subDays(5),
            'close_at' => now()->addDays(15),
        ], $attributes));
    }

    // =========================================================================
    // TEST 1: Draft boleh ganti formasi & profil tetap ada & berkas terhapus
    // =========================================================================

    public function test_draft_application_can_switch_vacancy_safely(): void
    {
        $user = $this->createCompleteApplicantUser();
        $period = $this->createRecruitmentPeriod();
        $vacancy1 = $this->createVacancy($period, ['title' => 'Perawat IGD']);
        $vacancy2 = $this->createVacancy($period, ['title' => 'Perawat Rawat Inap']);

        // Requirement dokumen formasi 1
        $reqDoc1 = VacancyDocumentRequirement::create([
            'job_vacancy_id' => $vacancy1->id,
            'name' => 'Surat Lamaran IGD',
            'allowed_mimes' => 'application/pdf',
            'max_size_kb' => 2048,
            'is_required' => true,
        ]);

        // 1. Buat Draft di Lowongan 1
        $appService = app(JobApplicationService::class);
        $draft = $appService->startDraft($user, $vacancy1);
        $this->assertEquals($vacancy1->id, $draft->job_vacancy_id);
        $this->assertEquals(JobApplicationStatus::DRAFT, $draft->status);

        // 2. Upload Dokumen pada Lowongan 1
        $fakeFile = UploadedFile::fake()->create('surat_lamaran.pdf', 200, 'application/pdf');
        $docService = app(ApplicationDocumentService::class);
        $uploadedDoc = $docService->uploadOrReplace($draft, $reqDoc1, $fakeFile);

        $storedPath = $uploadedDoc->stored_path;
        $this->assertNotNull($storedPath);
        Storage::disk(ApplicationDocumentService::DISK_NAME)->assertExists($storedPath);
        $this->assertDatabaseHas('application_documents', ['id' => $uploadedDoc->id]);

        // 3. Pelamar Ganti Formasi ke Lowongan 2
        $response = $this->actingAs($user)->post(route('portal.applications.switch-vacancy', $draft), [
            'job_vacancy_id' => $vacancy2->id,
        ]);

        $response->assertSessionHas('success');
        $response->assertRedirect();

        // 4. Verifikasi Data Lamaran
        $newApplication = JobApplication::where('user_id', $user->id)->first();
        $this->assertNotNull($newApplication);
        $this->assertEquals($vacancy2->id, $newApplication->job_vacancy_id);
        $this->assertEquals(JobApplicationStatus::DRAFT, $newApplication->status);

        // Dokumen draft lama harus terhapus dari database
        $this->assertDatabaseMissing('application_documents', ['id' => $uploadedDoc->id]);
        $this->assertDatabaseMissing('application_documents', ['job_application_id' => $draft->id]);

        // File fisik di storage privat harus terhapus secara aman
        Storage::disk(ApplicationDocumentService::DISK_NAME)->assertMissing($storedPath);

        // 5. PROFIL PELAMAR TETAP LENGKAP DAN TIDAK BOLEH TERHAPUS
        $this->assertDatabaseHas('applicant_profiles', ['user_id' => $user->id]);
        $this->assertDatabaseHas('applicant_educations', ['applicant_profile_id' => $user->applicantProfile->id]);
        $this->assertDatabaseHas('applicant_licenses', ['applicant_profile_id' => $user->applicantProfile->id]);
        $this->assertDatabaseHas('applicant_certifications', ['applicant_profile_id' => $user->applicantProfile->id]);
        $this->assertDatabaseHas('applicant_work_experiences', ['applicant_profile_id' => $user->applicantProfile->id]);
    }

    // =========================================================================
    // TEST 2: Pelamar boleh membatalkan draft lamaran secara mandiri
    // =========================================================================

    public function test_applicant_cancelling_only_draft_deletes_application_and_user_account(): void
    {
        $user = $this->createCompleteApplicantUser();
        $period = $this->createRecruitmentPeriod();
        $vacancy = $this->createVacancy($period, ['title' => 'Apoteker']);

        $reqDoc = VacancyDocumentRequirement::create([
            'job_vacancy_id' => $vacancy->id,
            'name' => 'STRA',
            'allowed_mimes' => 'application/pdf',
            'max_size_kb' => 2048,
            'is_required' => true,
        ]);

        $appService = app(JobApplicationService::class);
        $draft = $appService->startDraft($user, $vacancy);

        $fakeFile = UploadedFile::fake()->create('stra.pdf', 300, 'application/pdf');
        $docService = app(ApplicationDocumentService::class);
        $uploadedDoc = $docService->uploadOrReplace($draft, $reqDoc, $fakeFile);
        $storedPath = $uploadedDoc->stored_path;

        Storage::disk(ApplicationDocumentService::DISK_NAME)->assertExists($storedPath);

        // Batalkan draft via endpoint cancel
        $response = $this->actingAs($user)->delete(route('portal.applications.cancel-draft', $draft));

        $response->assertRedirect(route('vacancies.index'));
        $response->assertSessionHas('success', 'Draft lamaran dan akun Anda telah berhasil dibatalkan.');

        // Sesi di-logout
        $this->assertGuest();

        // Lamaran draft terhapus
        $this->assertDatabaseMissing('job_applications', ['id' => $draft->id]);
        $this->assertEquals(0, JobApplication::where('user_id', $user->id)->count());

        // Dokumen draft terhapus dari database
        $this->assertDatabaseMissing('application_documents', ['id' => $uploadedDoc->id]);

        // File fisik di storage privat terhapus
        Storage::disk(ApplicationDocumentService::DISK_NAME)->assertMissing($storedPath);

        // Sesuai aturan bisnis Option A: pelamar tidak boleh memiliki akun jika tidak ada lamaran aktif
        // Akun user dan seluruh data profilnya dihapus dari database
        $this->assertDatabaseMissing('users', ['id' => $user->id]);
        $this->assertDatabaseMissing('applicant_profiles', ['user_id' => $user->id]);
    }

    // =========================================================================
    // TEST 3: Ganti formasi via konfirmasi pada pemilihan lowongan (store)
    // =========================================================================

    public function test_switching_formation_via_store_with_confirmation(): void
    {
        $user = $this->createCompleteApplicantUser();
        $period = $this->createRecruitmentPeriod();
        $vacancy1 = $this->createVacancy($period, ['title' => 'Nutrisionis']);
        $vacancy2 = $this->createVacancy($period, ['title' => 'Fisioterapis']);

        $appService = app(JobApplicationService::class);
        $draft1 = $appService->startDraft($user, $vacancy1);

        // Mencoba store vacancy 2 dengan confirm_switch=1
        $response = $this->actingAs($user)->post(route('portal.applications.store', $vacancy2), [
            'confirm_switch' => 1,
        ]);

        $response->assertSessionHas('success');
        $this->assertEquals(1, JobApplication::where('user_id', $user->id)->count());

        $currentApp = JobApplication::where('user_id', $user->id)->first();
        $this->assertEquals($vacancy2->id, $currentApp->job_vacancy_id);

        // Akun pelamar dan profil tetap utuh 100%
        $this->assertDatabaseHas('users', ['id' => $user->id]);
        $this->assertDatabaseHas('applicant_profiles', ['user_id' => $user->id]);
        $this->assertDatabaseHas('applicant_educations', ['applicant_profile_id' => $user->applicantProfile->id]);
    }

    // =========================================================================
    // TEST 4: Lamaran SUBMITTED tidak boleh ganti formasi atau dibatalkan
    // =========================================================================

    public function test_submitted_application_cannot_be_cancelled_or_switch_vacancy(): void
    {
        $user = $this->createCompleteApplicantUser();
        $period = $this->createRecruitmentPeriod();
        $vacancy1 = $this->createVacancy($period, ['title' => 'Radiografer']);
        $vacancy2 = $this->createVacancy($period, ['title' => 'Analis Kesehatan']);

        // Buat lamaran yang sudah FINAL SUBMIT
        $submittedApp = JobApplication::create([
            'user_id' => $user->id,
            'recruitment_period_id' => $period->id,
            'job_vacancy_id' => $vacancy1->id,
            'status' => JobApplicationStatus::SUBMITTED,
            'application_number' => 'LAM-PR-000001',
            'submitted_at' => now(),
            'locked_at' => now(),
        ]);

        // 1. Coba batalkan lamaran submitted via HTTP -> Harus 403 Forbidden
        $responseCancel = $this->actingAs($user)->delete(route('portal.applications.cancel-draft', $submittedApp));
        $responseCancel->assertStatus(403);

        // 2. Coba ganti formasi via HTTP -> Harus 403 Forbidden
        $responseSwitch = $this->actingAs($user)->post(route('portal.applications.switch-vacancy', $submittedApp), [
            'job_vacancy_id' => $vacancy2->id,
        ]);
        $responseSwitch->assertStatus(403);

        // 3. Verifikasi pada service layer langsung melempar DomainException
        $service = app(JobApplicationService::class);

        $this->expectException(DomainException::class);
        $service->cancelDraft($user, $submittedApp);
    }

    public function test_submitted_application_service_switch_throws_domain_exception(): void
    {
        $user = $this->createCompleteApplicantUser();
        $period = $this->createRecruitmentPeriod();
        $vacancy1 = $this->createVacancy($period);
        $vacancy2 = $this->createVacancy($period);

        $submittedApp = JobApplication::create([
            'user_id' => $user->id,
            'recruitment_period_id' => $period->id,
            'job_vacancy_id' => $vacancy1->id,
            'status' => JobApplicationStatus::SUBMITTED,
            'application_number' => 'LAM-PR-000002',
            'submitted_at' => now(),
            'locked_at' => now(),
        ]);

        $service = app(JobApplicationService::class);

        $this->expectException(DomainException::class);
        $service->switchVacancy($user, $submittedApp, $vacancy2);
    }

    // =========================================================================
    // TEST 5: User lain tidak bisa membatalkan draft orang lain (Otorisasi)
    // =========================================================================

    public function test_other_user_cannot_cancel_or_switch_someone_elses_draft(): void
    {
        $userA = $this->createCompleteApplicantUser(['email' => 'usera@example.test']);
        $userB = $this->createCompleteApplicantUser(['email' => 'userb@example.test']);
        $period = $this->createRecruitmentPeriod();
        $vacancy1 = $this->createVacancy($period);
        $vacancy2 = $this->createVacancy($period);

        $appService = app(JobApplicationService::class);
        $draftA = $appService->startDraft($userA, $vacancy1);

        // User B mencoba membatalkan draft User A -> Harus 403
        $responseCancel = $this->actingAs($userB)->delete(route('portal.applications.cancel-draft', $draftA));
        $responseCancel->assertStatus(403);

        // User B mencoba switch formasi draft User A -> Harus 403
        $responseSwitch = $this->actingAs($userB)->post(route('portal.applications.switch-vacancy', $draftA), [
            'job_vacancy_id' => $vacancy2->id,
        ]);
        $responseSwitch->assertStatus(403);

        // Draft User A harus tetap aman di database
        $this->assertDatabaseHas('job_applications', [
            'id' => $draftA->id,
            'user_id' => $userA->id,
            'job_vacancy_id' => $vacancy1->id,
        ]);
    }

    // =========================================================================
    // TEST 6: Pertahankan aturan 1 user maksimal 1 lamaran aktif per periode
    // =========================================================================

    public function test_max_one_application_per_user_per_period_is_retained_when_not_switching(): void
    {
        $user = $this->createCompleteApplicantUser();
        $period = $this->createRecruitmentPeriod();
        $vacancy1 = $this->createVacancy($period);
        $vacancy2 = $this->createVacancy($period);

        $appService = app(JobApplicationService::class);
        $draft1 = $appService->startDraft($user, $vacancy1);

        // Mencoba store vacancy2 tanpa konfirmasi switch
        $response = $this->actingAs($user)->post(route('portal.applications.store', $vacancy2));

        // Harus dialihkan ke draft yang sudah ada
        $response->assertRedirect(route('portal.applications.show', $draft1));
        $response->assertSessionHas('info', 'Anda sudah memiliki lamaran pada periode rekrutmen ini.');

        // Total lamaran tetap 1 pada vacancy 1
        $this->assertEquals(1, JobApplication::where('user_id', $user->id)->count());
        $this->assertEquals($vacancy1->id, JobApplication::where('user_id', $user->id)->first()->job_vacancy_id);
    }

    // =========================================================================
    // TEST 7: CTA Ganti Formasi & Batalkan Draft pada halaman Index dan Show
    // =========================================================================

    public function test_draft_application_card_renders_ganti_formasi_and_batalkan_draft_ctas(): void
    {
        $user = $this->createCompleteApplicantUser();
        $period = $this->createRecruitmentPeriod();
        $vacancy = $this->createVacancy($period);

        $appService = app(JobApplicationService::class);
        $draft = $appService->startDraft($user, $vacancy);

        // 1. Cek pada menu Lamaran Saya (index)
        $responseIndex = $this->actingAs($user)->get(route('portal.applications.index'));
        $responseIndex->assertOk();
        $responseIndex->assertSee('Ganti Formasi');
        $responseIndex->assertSee('Batalkan Draft');
        $responseIndex->assertSee(route('vacancies.index'));
        $responseIndex->assertSee(route('portal.applications.show', $draft));
        $responseIndex->assertSee(route('portal.applications.cancel-draft', $draft));

        // 2. Cek pada detail lamaran (show)
        $responseShow = $this->actingAs($user)->get(route('portal.applications.show', $draft));
        $responseShow->assertOk();
        $responseShow->assertSee('Ganti Formasi');
        $responseShow->assertSee('Batalkan Draft');
        $responseShow->assertSee(route('vacancies.index'));
        $responseShow->assertSee(route('portal.applications.cancel-draft', $draft));
    }

    // =========================================================================
    // TEST 8: Pop-up ganti formasi pada detail lowongan saat punya draft aktif
    // =========================================================================

    public function test_vacancy_detail_shows_popup_switch_modal_when_user_has_unsubmitted_draft_and_switches_successfully(): void
    {
        $user = $this->createCompleteApplicantUser();
        $period = $this->createRecruitmentPeriod();
        $vacancyOld = $this->createVacancy($period, ['title' => 'Formasi Lama IT']);
        $vacancyNew = $this->createVacancy($period, ['title' => 'Formasi Baru Perawat']);

        $appService = app(JobApplicationService::class);
        $oldDraft = $appService->startDraft($user, $vacancyOld);

        // Pelamar melihat detail lowongan baru (vacancyNew)
        $responseShow = $this->actingAs($user)->get(route('vacancies.show', $vacancyNew));
        $responseShow->assertOk();

        // Harus melihat tombol lamar dan modal konfirmasi ganti formasi
        $responseShow->assertSee('Lamar Posisi Ini Sekarang');
        $responseShow->assertSee('Ganti Formasi Lamaran?');
        $responseShow->assertSee('Lamaran Sebelumnya Akan Dihapus');
        $responseShow->assertSee('Formasi Lama IT');
        $responseShow->assertSee('Profil Anda Tetap Aman Tersimpan');
        $responseShow->assertSee('Ya, Ganti ke Formasi Ini');

        // Melakukan konfirmasi ganti formasi melalui form modal
        $responseSubmit = $this->actingAs($user)->post(route('portal.applications.store', $vacancyNew), [
            'job_vacancy_id' => $vacancyNew->id,
            'confirm_switch' => '1',
        ]);

        // Cek redirect ke show lamaran baru
        $newApplication = JobApplication::where('user_id', $user->id)
            ->where('job_vacancy_id', $vacancyNew->id)
            ->first();

        $this->assertNotNull($newApplication);
        $responseSubmit->assertRedirect(route('portal.applications.show', $newApplication));
        $responseSubmit->assertSessionHas('success');

        // Draft lama harus sudah terhapus
        $this->assertDatabaseMissing('job_applications', [
            'id' => $oldDraft->id,
        ]);

        // Profil user tetap utuh 100%
        $this->assertDatabaseHas('applicant_profiles', [
            'user_id' => $user->id,
        ]);
        $this->assertDatabaseHas('applicant_educations', [
            'applicant_profile_id' => $user->applicantProfile->id,
        ]);
    }
}
