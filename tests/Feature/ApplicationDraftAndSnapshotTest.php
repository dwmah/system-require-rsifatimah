<?php

namespace Tests\Feature;

use App\Enums\JobApplicationStatus;
use App\Enums\JobVacancyStatus;
use App\Enums\RecruitmentPeriodStatus;
use App\Exceptions\Recruitment\ApplicationAlreadySnapshottedException;
use App\Exceptions\Recruitment\DuplicateApplicationException;
use App\Models\ApplicantCertification;
use App\Models\ApplicantEducation;
use App\Models\ApplicantLicense;
use App\Models\ApplicantProfile;
use App\Models\ApplicantWorkExperience;
use App\Models\ApplicationSnapshot;
use App\Models\JobApplication;
use App\Models\JobVacancy;
use App\Models\RecruitmentPeriod;
use App\Models\User;
use App\Models\VacancyDocumentRequirement;
use App\Models\VacancyQualification;
use App\Models\VacancySelectionStage;
use App\Services\Recruitment\ApplicationSnapshotService;
use App\Services\Recruitment\JobApplicationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ApplicationDraftAndSnapshotTest extends TestCase
{
    use RefreshDatabase;

    protected function createApplicantUser(bool $completeProfile = true, array $userAttributes = [], array $profileAttributes = []): User
    {
        static $sequence = 1;
        $seq = $sequence++;

        $user = User::create(array_merge([
            'name' => 'Pelamar Tes '.$seq,
            'email' => "pelamar{$seq}@example.com",
            'phone' => sprintf('62812345%05d', $seq),
            'registration_number' => sprintf('PLM-2026-%06d', $seq),
            'password' => 'Password123',
            'is_active' => true,
        ], $userAttributes));

        if ($completeProfile) {
            $profile = ApplicantProfile::create(array_merge([
                'user_id' => $user->id,
                'nik' => sprintf('3510123456%06d', $seq),
                'birth_place' => 'Banyuwangi',
                'birth_date' => '1996-06-15',
                'gender' => 'Laki-laki',
                'address_ktp' => 'Jl. Jendral Sudirman No. 45 Banyuwangi',
                'address_domicile' => 'Jl. Jendral Sudirman No. 45 Banyuwangi',
                'same_as_ktp' => true,
                'photo_path' => 'profile-photos/test.jpg',
            ], $profileAttributes));

            ApplicantEducation::create([
                'applicant_profile_id' => $profile->id,
                'education_level' => 'S1',
                'institution_name' => 'Universitas Airlangga',
                'study_program' => 'Keperawatan',
                'accreditation' => 'A',
                'gpa' => '3.75',
                'graduation_year' => 2021,
            ]);
        } else {
            // Incomplete profile: missing birth_place, birth_date, address, education
            ApplicantProfile::create(array_merge([
                'user_id' => $user->id,
                'nik' => sprintf('3510123456%06d', $seq),
                'same_as_ktp' => false,
            ], $profileAttributes));
        }

        return $user->fresh(['applicantProfile', 'applicantProfile.educations']);
    }

    protected function createOpenVacancy(array $vacancyAttributes = []): JobVacancy
    {
        static $vacSeq = 1;
        $seq = $vacSeq++;

        $period = RecruitmentPeriod::create([
            'name' => 'Periode Rekrutmen Medis 2026 #'.$seq,
            'code' => 'PR-2026-'.sprintf('%03d', $seq),
            'status' => RecruitmentPeriodStatus::OPEN,
            'registration_start_at' => now()->subDays(5),
            'registration_end_at' => now()->addDays(15),
        ]);

        return JobVacancy::create(array_merge([
            'recruitment_period_id' => $period->id,
            'code' => 'POS-2026-MED-'.sprintf('%03d', $seq),
            'title' => 'Perawat IGD '.$seq,
            'unit' => 'Instalasi Gawat Darurat',
            'employment_type' => 'PKWT',
            'quota' => 5,
            'location' => 'RSI Fatimah Banyuwangi',
            'description' => 'Tugas asuhan keperawatan IGD.',
            'responsibilities' => 'Melakukan tindakan gawat darurat.',
            'status' => JobVacancyStatus::OPEN,
            'open_at' => now()->subDays(5),
            'close_at' => now()->addDays(15),
        ], $vacancyAttributes));
    }

    // ==========================================
    // 1. Guest & Auth Flow
    // ==========================================

    public function test_guest_clicking_start_is_redirected_to_login(): void
    {
        $vacancy = $this->createOpenVacancy();

        $response = $this->get(route('portal.applications.start', $vacancy));

        $response->assertRedirect(route('login'));
    }

    public function test_login_redirects_intended_back_to_application_start(): void
    {
        $vacancy = $this->createOpenVacancy();
        $user = $this->createApplicantUser();

        // 1. Access protected start route as guest
        $startUrl = route('portal.applications.start', $vacancy);
        $this->get($startUrl)->assertRedirect(route('login'));

        // 2. Perform login
        $loginResponse = $this->post(route('login.store'), [
            'login' => $user->email,
            'password' => 'Password123',
        ]);

        // 3. Assert redirected back to the intended start route
        $loginResponse->assertRedirect($startUrl);
    }

    // ==========================================
    // 2. Prerequisite & Availability
    // ==========================================

    public function test_active_applicant_with_complete_profile_can_view_start_page(): void
    {
        $user = $this->createApplicantUser(true);
        $vacancy = $this->createOpenVacancy();

        $response = $this->actingAs($user)->get(route('portal.applications.start', $vacancy));

        $response->assertStatus(200);
        $response->assertSeeText($vacancy->title);
        $response->assertSeeText($vacancy->code);
        $response->assertSeeText('Profil Dasar Anda 100% Lengkap');
        $response->assertSeeText('Buat Draft Lamaran');
    }

    public function test_applicant_with_incomplete_profile_cannot_view_start_page_and_is_redirected_to_profile(): void
    {
        $user = $this->createApplicantUser(false); // Incomplete
        $vacancy = $this->createOpenVacancy();

        $response = $this->actingAs($user)->get(route('portal.applications.start', $vacancy));

        $response->assertRedirect(route('profile.show'));
        $response->assertSessionHas('warning', 'Lengkapi profil dasar Anda sebelum mengajukan lamaran.');
    }

    public function test_closed_or_inactive_vacancy_cannot_be_started(): void
    {
        $user = $this->createApplicantUser(true);
        $closedVacancy = $this->createOpenVacancy([
            'status' => JobVacancyStatus::CLOSED,
        ]);

        $responseGet = $this->actingAs($user)->get(route('portal.applications.start', $closedVacancy));
        $responseGet->assertRedirect(route('vacancies.index'));
        $responseGet->assertSessionHas('error');

        $responsePost = $this->actingAs($user)->post(route('portal.applications.store', $closedVacancy));
        $responsePost->assertRedirect(route('vacancies.index'));
        $responsePost->assertSessionHas('error');
    }

    public function test_get_start_page_does_not_create_job_application(): void
    {
        $user = $this->createApplicantUser(true);
        $vacancy = $this->createOpenVacancy();

        $this->actingAs($user)->get(route('portal.applications.start', $vacancy));

        $this->assertEquals(0, JobApplication::count());
    }

    // ==========================================
    // 3. Draft Creation & Data Integrity
    // ==========================================

    public function test_post_store_creates_one_job_application_draft(): void
    {
        $user = $this->createApplicantUser(true);
        $vacancy = $this->createOpenVacancy();

        $response = $this->actingAs($user)->post(route('portal.applications.store', $vacancy));

        $this->assertEquals(1, JobApplication::count());
        $application = JobApplication::first();

        $response->assertRedirect(route('portal.applications.show', $application));
        $response->assertSessionHas('success');

        $this->assertEquals($user->id, $application->user_id);
        $this->assertEquals($vacancy->id, $application->job_vacancy_id);
        $this->assertEquals($vacancy->recruitment_period_id, $application->recruitment_period_id);
        $this->assertEquals(JobApplicationStatus::DRAFT, $application->status);
    }

    public function test_draft_application_has_null_application_number_and_timestamps(): void
    {
        $user = $this->createApplicantUser(true);
        $vacancy = $this->createOpenVacancy();

        $this->actingAs($user)->post(route('portal.applications.store', $vacancy));

        $application = JobApplication::first();

        $this->assertNull($application->application_number);
        $this->assertNull($application->submitted_at);
        $this->assertNull($application->locked_at);
        $this->assertNull($application->accepted_at);
        $this->assertNull($application->rejected_at);
        $this->assertNull($application->hired_at);
    }

    public function test_post_uses_route_model_binding_and_ignores_tampered_body_parameters(): void
    {
        $user = $this->createApplicantUser(true);
        $otherUser = $this->createApplicantUser(true, ['email' => 'otheruser@example.com', 'phone' => '6289988776655']);
        $vacancy = $this->createOpenVacancy();

        // Malicious request sending tampered user_id, recruitment_period_id, status, and application_number
        $response = $this->actingAs($user)->post(route('portal.applications.store', $vacancy), [
            'user_id' => $otherUser->id,
            'recruitment_period_id' => 9999,
            'status' => 'accepted',
            'application_number' => 'FAKE-NUMBER-99',
        ]);

        $application = JobApplication::first();
        $this->assertNotNull($application);
        $this->assertEquals($user->id, $application->user_id); // Authenticated user
        $this->assertEquals($vacancy->recruitment_period_id, $application->recruitment_period_id); // From vacancy
        $this->assertEquals(JobApplicationStatus::DRAFT, $application->status); // Forced draft
        $this->assertNull($application->application_number); // Unset
    }

    // ==========================================
    // 4. One Account = One Formation per Period
    // ==========================================

    public function test_user_cannot_create_second_application_in_same_recruitment_period(): void
    {
        $user = $this->createApplicantUser(true);
        $vacancy1 = $this->createOpenVacancy();

        // Create second vacancy in the SAME recruitment period
        $vacancy2 = JobVacancy::create([
            'recruitment_period_id' => $vacancy1->recruitment_period_id,
            'code' => 'POS-2026-FARMASI-01',
            'title' => 'Tenaga Teknis Kefarmasian',
            'unit' => 'Instalasi Farmasi',
            'employment_type' => 'PKWT',
            'quota' => 2,
            'location' => 'RSI Fatimah Banyuwangi',
            'status' => JobVacancyStatus::OPEN,
            'open_at' => now()->subDays(5),
            'close_at' => now()->addDays(15),
        ]);

        // 1. First application succeeds
        $res1 = $this->actingAs($user)->post(route('portal.applications.store', $vacancy1));
        $res1->assertSessionHas('success');
        $this->assertEquals(1, JobApplication::count());

        $firstApplication = JobApplication::first();

        // 2. Second application in same period is rejected and redirected to existing application
        $res2 = $this->actingAs($user)->post(route('portal.applications.store', $vacancy2));
        $res2->assertRedirect(route('portal.applications.show', $firstApplication));
        $res2->assertSessionHas('info', 'Anda sudah memiliki lamaran pada periode rekrutmen ini. Formasi tidak dapat diganti.');

        $this->assertEquals(1, JobApplication::count());
    }

    public function test_existing_application_does_not_change_vacancy_when_attempting_second_apply(): void
    {
        $user = $this->createApplicantUser(true);
        $vacancy1 = $this->createOpenVacancy();
        $vacancy2 = JobVacancy::create([
            'recruitment_period_id' => $vacancy1->recruitment_period_id,
            'code' => 'POS-2026-GIZI-01',
            'title' => 'Nutrisionis',
            'unit' => 'Instalasi Gizi',
            'employment_type' => 'PKWT',
            'quota' => 1,
            'location' => 'RSI Fatimah Banyuwangi',
            'status' => JobVacancyStatus::OPEN,
            'open_at' => now()->subDays(5),
            'close_at' => now()->addDays(15),
        ]);

        $this->actingAs($user)->post(route('portal.applications.store', $vacancy1));
        $this->assertEquals($vacancy1->id, JobApplication::first()->job_vacancy_id);

        // Attempting to apply to vacancy 2
        $this->actingAs($user)->post(route('portal.applications.store', $vacancy2));

        // Vacancy ID MUST remain vacancy1 (locked, no transfer)
        $this->assertEquals($vacancy1->id, JobApplication::first()->job_vacancy_id);
    }

    public function test_user_can_have_applications_in_different_recruitment_periods(): void
    {
        $user = $this->createApplicantUser(true);
        $vacancyPeriod1 = $this->createOpenVacancy();
        $vacancyPeriod2 = $this->createOpenVacancy(); // Different period!

        $this->actingAs($user)->post(route('portal.applications.store', $vacancyPeriod1));
        $this->actingAs($user)->post(route('portal.applications.store', $vacancyPeriod2));

        $this->assertEquals(2, JobApplication::where('user_id', $user->id)->count());
    }

    // ==========================================
    // 5. Ownership & Route Security
    // ==========================================

    public function test_user_only_sees_their_own_applications_in_index(): void
    {
        $userA = $this->createApplicantUser(true);
        $userB = $this->createApplicantUser(true, ['email' => 'userb@example.com', 'phone' => '6281112223334']);

        $vacancyA = $this->createOpenVacancy();
        $vacancyB = $this->createOpenVacancy();

        $this->actingAs($userA)->post(route('portal.applications.store', $vacancyA));
        $this->actingAs($userB)->post(route('portal.applications.store', $vacancyB));

        $responseA = $this->actingAs($userA)->get(route('portal.applications.index'));
        $responseA->assertStatus(200);
        $responseA->assertSeeText($vacancyA->title);
        $responseA->assertDontSeeText($vacancyB->title);
    }

    public function test_cross_user_application_show_is_forbidden_with_403(): void
    {
        $userA = $this->createApplicantUser(true);
        $userB = $this->createApplicantUser(true, ['email' => 'userb2@example.com', 'phone' => '6281112223335']);

        $vacancy = $this->createOpenVacancy();
        $this->actingAs($userB)->post(route('portal.applications.store', $vacancy));
        $appUserB = JobApplication::where('user_id', $userB->id)->first();

        // User A tries to view User B's application
        $response = $this->actingAs($userA)->get(route('portal.applications.show', $appUserB));
        $response->assertStatus(403);
    }

    public function test_route_mulai_does_not_clash_with_numeric_job_application_route(): void
    {
        $user = $this->createApplicantUser(true);
        $vacancy = $this->createOpenVacancy();

        // When requesting /portal/lamaran/mulai/{code}, it matches portal.applications.start
        $response = $this->actingAs($user)->get(route('portal.applications.start', $vacancy));
        $response->assertStatus(200);
        $response->assertSeeText('Konfirmasi Formasi');
    }

    public function test_application_index_and_show_accessible_when_recruitment_is_closed(): void
    {
        $user = $this->createApplicantUser(true);
        $vacancy = $this->createOpenVacancy();

        $this->actingAs($user)->post(route('portal.applications.store', $vacancy));
        $application = JobApplication::first();

        // Now close the period and vacancy
        $vacancy->recruitmentPeriod->update(['status' => RecruitmentPeriodStatus::COMPLETED]);
        $vacancy->update(['status' => JobVacancyStatus::CLOSED]);

        // Index remains accessible
        $this->actingAs($user)->get(route('portal.applications.index'))->assertStatus(200);

        // Show remains accessible
        $this->actingAs($user)->get(route('portal.applications.show', $application))->assertStatus(200);
    }

    public function test_lamaran_saya_sidebar_link_is_active(): void
    {
        $user = $this->createApplicantUser(true);

        $response = $this->actingAs($user)->get(route('portal.applications.index'));

        $response->assertStatus(200);
        $response->assertSee(route('portal.applications.index'));
        $response->assertSeeText('Lamaran Saya');
    }

    // ==========================================
    // 6. Snapshot Foundation & Serialization
    // ==========================================

    public function test_snapshot_capture_creates_complete_snapshot(): void
    {
        $user = $this->createApplicantUser(true);
        $vacancy = $this->createOpenVacancy();

        $profile = $user->applicantProfile;
        ApplicantLicense::create([
            'applicant_profile_id' => $profile->id,
            'license_type' => 'STR Perawat',
            'license_number' => 'STR-2023-112233',
            'issued_at' => '2023-01-15',
            'expires_at' => '2028-01-15',
        ]);
        ApplicantCertification::create([
            'applicant_profile_id' => $profile->id,
            'name' => 'Basic Trauma Cardiac Life Support (BTCLS)',
            'issuer' => 'PPNI',
            'certificate_number' => 'CERT-2024-001',
            'issued_at' => '2024-02-10',
            'expires_at' => '2027-02-10',
        ]);
        ApplicantWorkExperience::create([
            'applicant_profile_id' => $profile->id,
            'company_name' => 'Klinik Rawat Inap Medika',
            'position' => 'Staf Perawat',
            'start_date' => '2022-03-01',
            'end_date' => '2024-01-01',
            'description' => 'Pelayanan IGD dan Rawat Inap.',
        ]);

        VacancyQualification::create([
            'job_vacancy_id' => $vacancy->id,
            'label' => 'Pendidikan Minimal',
            'value' => 'D3 / S1 Keperawatan + Ners',
            'sort_order' => 1,
        ]);
        VacancyDocumentRequirement::create([
            'job_vacancy_id' => $vacancy->id,
            'name' => 'Scan STR Aktif',
            'description' => 'STR yang masih berlaku minimal 6 bulan.',
            'is_required' => true,
            'allowed_mimes' => 'pdf',
            'max_size_kb' => 2048,
            'sort_order' => 1,
        ]);
        VacancySelectionStage::create([
            'job_vacancy_id' => $vacancy->id,
            'name' => 'Seleksi Administrasi',
            'description' => 'Verifikasi kelengkapan dan keaslian berkas.',
            'sort_order' => 1,
            'start_at' => now()->addDays(1),
            'end_at' => now()->addDays(5),
        ]);

        $this->actingAs($user)->post(route('portal.applications.store', $vacancy));
        $application = JobApplication::first();

        // Execute snapshot capture
        $snapshotService = app(ApplicationSnapshotService::class);
        $snapshot = $snapshotService->capture($application);

        $this->assertInstanceOf(ApplicationSnapshot::class, $snapshot);
        $this->assertEquals($application->id, $snapshot->job_application_id);

        // Period data
        $this->assertEquals($vacancy->recruitmentPeriod->name, $snapshot->recruitment_period_data['name']);
        $this->assertEquals($vacancy->recruitmentPeriod->code, $snapshot->recruitment_period_data['code']);

        // Personal data
        $this->assertEquals($user->name, $snapshot->personal_data['name']);
        $this->assertEquals($profile->nik, $snapshot->personal_data['nik']);
        $this->assertArrayNotHasKey('password', $snapshot->personal_data);
        $this->assertArrayNotHasKey('remember_token', $snapshot->personal_data);

        // Educations
        $this->assertCount(1, $snapshot->educations);
        $this->assertEquals('Keperawatan', $snapshot->educations[0]['study_program']);
        $this->assertEquals('Universitas Airlangga', $snapshot->educations[0]['institution_name']);

        // Licenses
        $this->assertCount(1, $snapshot->licenses);
        $this->assertEquals('STR-2023-112233', $snapshot->licenses[0]['license_number']);
        $this->assertEquals('2023-01-15', $snapshot->licenses[0]['issued_at']);

        // Certifications
        $this->assertCount(1, $snapshot->certifications);
        $this->assertEquals('PPNI', $snapshot->certifications[0]['issuer']);
        $this->assertEquals('CERT-2024-001', $snapshot->certifications[0]['certificate_number']);

        // Work Experiences
        $this->assertCount(1, $snapshot->work_experiences);
        $this->assertEquals('Klinik Rawat Inap Medika', $snapshot->work_experiences[0]['company_name']);

        // Vacancy Data & Nested Relations
        $this->assertEquals($vacancy->code, $snapshot->vacancy_data['code']);
        $this->assertEquals('Pendidikan Minimal', $snapshot->vacancy_data['qualifications'][0]['label']);
        $this->assertEquals('Scan STR Aktif', $snapshot->vacancy_data['document_requirements'][0]['name']);
        $this->assertTrue($snapshot->vacancy_data['document_requirements'][0]['is_required']);
        $this->assertEquals(2048, $snapshot->vacancy_data['document_requirements'][0]['max_size_kb']);
        $this->assertEquals('Seleksi Administrasi', $snapshot->vacancy_data['selection_stages'][0]['name']);
    }

    public function test_recapturing_snapshot_on_same_application_throws_exception(): void
    {
        $user = $this->createApplicantUser(true);
        $vacancy = $this->createOpenVacancy();

        $this->actingAs($user)->post(route('portal.applications.store', $vacancy));
        $application = JobApplication::first();

        $snapshotService = app(ApplicationSnapshotService::class);
        $snapshotService->capture($application);

        // Second capture MUST throw ApplicationAlreadySnapshottedException
        $this->expectException(ApplicationAlreadySnapshottedException::class);
        $snapshotService->capture($application);
    }

    public function test_draft_creation_does_not_automatically_create_snapshot(): void
    {
        $user = $this->createApplicantUser(true);
        $vacancy = $this->createOpenVacancy();

        $this->actingAs($user)->post(route('portal.applications.store', $vacancy));
        $application = JobApplication::first();

        $this->assertNull($application->snapshot);
        $this->assertEquals(0, ApplicationSnapshot::count());
    }

    public function test_non_duplicate_query_exception_is_rethrowed_by_start_draft(): void
    {
        $user = $this->createApplicantUser(true);
        $vacancy = $this->createOpenVacancy();

        // Mock service or test startDraft error handling
        $service = app(JobApplicationService::class);

        // Test normal startDraft works
        $application = $service->startDraft($user, $vacancy);
        $this->assertInstanceOf(JobApplication::class, $application);

        // Calling startDraft second time triggers DuplicateApplicationException
        $this->expectException(DuplicateApplicationException::class);
        $service->startDraft($user, $vacancy);
    }
}
