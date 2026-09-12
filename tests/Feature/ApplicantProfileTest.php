<?php

namespace Tests\Feature;

use App\Models\ApplicantCertification;
use App\Models\ApplicantEducation;
use App\Models\ApplicantLicense;
use App\Models\ApplicantProfile;
use App\Models\ApplicantWorkExperience;
use App\Models\User;
use App\Services\Profile\ProfileCompletenessService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ApplicantProfileTest extends TestCase
{
    use RefreshDatabase;

    protected function createApplicantUser(array $userAttributes = [], array $profileAttributes = []): User
    {
        static $sequence = 1;
        $seq = $sequence++;

        $user = User::create(array_merge([
            'name' => 'Pelamar Tes ' . $seq,
            'email' => "pelamar.tes{$seq}@example.com",
            'phone' => sprintf('62812345%05d', $seq),
            'registration_number' => sprintf('PLM-2026-%06d', $seq),
            'password' => 'Password123',
            'is_active' => true,
        ], $userAttributes));

        ApplicantProfile::create(array_merge([
            'user_id' => $user->id,
            'nik' => sprintf('3510123456%06d', $seq),
            'same_as_ktp' => false,
        ], $profileAttributes));

        return $user->fresh(['applicantProfile']);
    }

    public function test_guest_cannot_access_profile(): void
    {
        $response = $this->get(route('profile.show'));

        $response->assertRedirect(route('login'));
    }

    public function test_inactive_applicant_cannot_access_profile(): void
    {
        $user = $this->createApplicantUser(['is_active' => false]);

        $response = $this->actingAs($user)->get(route('profile.show'));

        $response->assertRedirect(route('login'));
        $this->assertGuest();
    }

    public function test_active_applicant_can_access_profile(): void
    {
        $user = $this->createApplicantUser();

        $response = $this->actingAs($user)->get(route('profile.show'));

        $response->assertStatus(200);
        $response->assertSeeText('Profil Saya');
        $response->assertSeeText('Data Pribadi');
        $response->assertSeeText('Alamat & Kontak');
        $response->assertSeeText('Riwayat Pendidikan');
        $response->assertSee($user->applicantProfile->nik);
    }

    public function test_profile_accessible_when_recruitment_is_closed(): void
    {
        $user = $this->createApplicantUser();

        $response = $this->actingAs($user)->get(route('profile.show'));

        $response->assertStatus(200);
        $response->assertSee('Profil Saya');
    }

    public function test_nik_displayed_and_cannot_be_changed_via_personal_data_update(): void
    {
        $user = $this->createApplicantUser([], ['nik' => '3510123456780001']);

        // Send malicious payload attempting to change NIK
        $response = $this->actingAs($user)->patch(route('profile.personal.update'), [
            'name' => 'Nama Baru',
            'nik' => '3510999999999999', // should be ignored!
            'birth_place' => 'Banyuwangi',
            'birth_date' => '1998-05-20',
            'gender' => 'Laki-laki',
        ]);

        $response->assertRedirect(route('profile.show'));
        $response->assertSessionHas('success');

        $user->refresh();
        $this->assertEquals('Nama Baru', $user->name);
        $this->assertEquals('3510123456780001', $user->applicantProfile->nik); // NIK must remain unchanged
        $this->assertEquals('Banyuwangi', $user->applicantProfile->birth_place);
    }

    public function test_personal_data_update_succeeds_and_is_atomic(): void
    {
        $user = $this->createApplicantUser();

        $response = $this->actingAs($user)->patch(route('profile.personal.update'), [
            'name' => 'Fatimah Zahra',
            'birth_place' => 'Surabaya',
            'birth_date' => '1995-10-15',
            'gender' => 'Perempuan',
        ]);

        $response->assertRedirect(route('profile.show'));
        $response->assertSessionHas('success');

        $user->refresh();
        $this->assertEquals('Fatimah Zahra', $user->name);
        $this->assertEquals('Surabaya', $user->applicantProfile->birth_place);
        $this->assertEquals('1995-10-15', $user->applicantProfile->birth_date->format('Y-m-d'));
        $this->assertEquals('Perempuan', $user->applicantProfile->gender);
    }

    public function test_email_update_succeeds_and_ignores_current_user_id(): void
    {
        $user = $this->createApplicantUser(['email' => 'original@example.com']);

        // Keeping same email should not trigger unique validation error
        $response = $this->actingAs($user)->patch(route('profile.contact.update'), [
            'email' => 'original@example.com',
            'phone' => '081234567800',
            'address_ktp' => 'Jl. Veteran No. 1 Banyuwangi',
            'same_as_ktp' => '1',
        ]);

        $response->assertRedirect(route('profile.show'));
        $response->assertSessionHas('success');

        // Updating to new email
        $response2 = $this->actingAs($user)->patch(route('profile.contact.update'), [
            'email' => 'new.email@example.com',
            'phone' => '081234567800',
            'address_ktp' => 'Jl. Veteran No. 1 Banyuwangi',
            'same_as_ktp' => '1',
        ]);

        $response2->assertRedirect(route('profile.show'));
        $this->assertEquals('new.email@example.com', $user->fresh()->email);
        $this->assertEquals($user->registration_number, $user->fresh()->registration_number); // registration number remains unchanged
    }

    public function test_phone_update_uses_canonical_normalization_and_rejects_duplicate(): void
    {
        $otherUser = $this->createApplicantUser(['email' => 'other@example.com', 'phone' => '6289999999999'], ['nik' => '3510888888888888']);
        $user = $this->createApplicantUser();

        // Updating phone with 08... format should canonicalize to 628...
        $response = $this->actingAs($user)->patch(route('profile.contact.update'), [
            'email' => $user->email,
            'phone' => '081298765432',
            'address_ktp' => 'Jl. Sudirman Banyuwangi',
            'same_as_ktp' => '1',
        ]);

        $response->assertRedirect(route('profile.show'));
        $user->refresh();
        $this->assertEquals('6281298765432', $user->phone);

        // Attempting to update to other user's phone should be rejected
        $responseDuplicate = $this->actingAs($user)->patch(route('profile.contact.update'), [
            'email' => $user->email,
            'phone' => '089999999999', // normalizes to 6289999999999
            'address_ktp' => 'Jl. Sudirman Banyuwangi',
            'same_as_ktp' => '1',
        ]);

        $responseDuplicate->assertSessionHasErrors(['phone']);
    }

    public function test_same_as_ktp_true_mirrors_address_domicile_server_side(): void
    {
        $user = $this->createApplicantUser();

        $response = $this->actingAs($user)->patch(route('profile.contact.update'), [
            'email' => $user->email,
            'phone' => '081234567800',
            'address_ktp' => 'Jl. Basuki Rahmat No. 10 Banyuwangi',
            'same_as_ktp' => '1',
            'address_domicile' => 'Should Be Overridden by KTP',
        ]);

        $response->assertRedirect(route('profile.show'));
        $user->refresh();
        $this->assertTrue($user->applicantProfile->same_as_ktp);
        $this->assertEquals('Jl. Basuki Rahmat No. 10 Banyuwangi', $user->applicantProfile->address_ktp);
        $this->assertEquals('Jl. Basuki Rahmat No. 10 Banyuwangi', $user->applicantProfile->address_domicile);
    }

    public function test_same_as_ktp_false_requires_address_domicile(): void
    {
        $user = $this->createApplicantUser();

        $response = $this->actingAs($user)->patch(route('profile.contact.update'), [
            'email' => $user->email,
            'phone' => '081234567800',
            'address_ktp' => 'Jl. Basuki Rahmat No. 10 Banyuwangi',
            'same_as_ktp' => '0',
            'address_domicile' => '',
        ]);

        $response->assertSessionHasErrors(['address_domicile']);
    }

    public function test_education_crud_and_future_graduation_year_rejection(): void
    {
        $user = $this->createApplicantUser();

        // 1. Future graduation year rejection
        $responseFuture = $this->actingAs($user)->post(route('profile.educations.store'), [
            'education_level' => 'S1',
            'institution_name' => 'Universitas Indonesia',
            'study_program' => 'Ilmu Keperawatan',
            'graduation_year' => now()->year + 5,
        ]);
        $responseFuture->assertSessionHasErrors(['graduation_year']);

        // 2. Valid create
        $responseCreate = $this->actingAs($user)->post(route('profile.educations.store'), [
            'education_level' => 'D3',
            'institution_name' => 'Poltekkes Kemenkes',
            'study_program' => 'Keperawatan',
            'accreditation' => 'A',
            'gpa' => '3.65',
            'graduation_year' => 2021,
        ]);
        $responseCreate->assertRedirect(route('profile.show'));
        $this->assertDatabaseHas('applicant_educations', [
            'applicant_profile_id' => $user->applicantProfile->id,
            'study_program' => 'Keperawatan',
        ]);

        $education = ApplicantEducation::where('applicant_profile_id', $user->applicantProfile->id)->first();

        // 3. Valid update
        $responseUpdate = $this->actingAs($user)->patch(route('profile.educations.update', $education), [
            'education_level' => 'D3',
            'institution_name' => 'Poltekkes Kemenkes Surabaya',
            'study_program' => 'Keperawatan',
            'gpa' => '3.80',
        ]);
        $responseUpdate->assertRedirect(route('profile.show'));
        $this->assertEquals('Poltekkes Kemenkes Surabaya', $education->fresh()->institution_name);

        // 4. Valid delete
        $responseDelete = $this->actingAs($user)->delete(route('profile.educations.destroy', $education));
        $responseDelete->assertRedirect(route('profile.show'));
        $this->assertDatabaseMissing('applicant_educations', ['id' => $education->id]);
    }

    public function test_cross_user_education_access_is_forbidden(): void
    {
        $userA = $this->createApplicantUser(['email' => 'userA@example.com'], ['nik' => '3510000000000001']);
        $userB = $this->createApplicantUser(['email' => 'userB@example.com', 'phone' => '6281234567809'], ['nik' => '3510000000000002']);

        $educationB = ApplicantEducation::create([
            'applicant_profile_id' => $userB->applicantProfile->id,
            'education_level' => 'S1',
            'institution_name' => 'Universitas B',
            'study_program' => 'Farmasi',
        ]);

        // User A attempts to edit User B's education
        $responseEdit = $this->actingAs($userA)->patch(route('profile.educations.update', $educationB), [
            'education_level' => 'S1',
            'institution_name' => 'Hacked University',
            'study_program' => 'Farmasi',
        ]);
        $responseEdit->assertStatus(403);

        // User A attempts to delete User B's education
        $responseDelete = $this->actingAs($userA)->delete(route('profile.educations.destroy', $educationB));
        $responseDelete->assertStatus(403);
        $this->assertDatabaseHas('applicant_educations', ['id' => $educationB->id]);
    }

    public function test_child_create_does_not_accept_tampered_applicant_profile_id(): void
    {
        $userA = $this->createApplicantUser(['email' => 'userA@example.com'], ['nik' => '3510000000000011']);
        $userB = $this->createApplicantUser(['email' => 'userB@example.com', 'phone' => '6281234567819'], ['nik' => '3510000000000012']);

        // User A sends applicant_profile_id of User B
        $response = $this->actingAs($userA)->post(route('profile.educations.store'), [
            'applicant_profile_id' => $userB->applicantProfile->id, // Tampered ID!
            'education_level' => 'S1',
            'institution_name' => 'Universitas A',
            'study_program' => 'Kedokteran',
        ]);

        $response->assertRedirect(route('profile.show'));

        // The record MUST belong to userA's profile, NOT userB's profile!
        $this->assertDatabaseHas('applicant_educations', [
            'applicant_profile_id' => $userA->applicantProfile->id,
            'study_program' => 'Kedokteran',
        ]);
        $this->assertDatabaseMissing('applicant_educations', [
            'applicant_profile_id' => $userB->applicantProfile->id,
            'study_program' => 'Kedokteran',
        ]);
    }

    public function test_license_crud_and_cross_user_protection(): void
    {
        $user = $this->createApplicantUser();
        $otherUser = $this->createApplicantUser(['email' => 'other2@example.com', 'phone' => '6281234567829'], ['nik' => '3510000000000022']);

        // Date consistency check: expires_at without issued_at
        $resDateInvalid = $this->actingAs($user)->post(route('profile.licenses.store'), [
            'license_type' => 'STR Perawat',
            'license_number' => '1234567890',
            'expires_at' => '2028-12-31',
        ]);
        $resDateInvalid->assertSessionHasErrors(['issued_at']);

        // Valid create
        $resCreate = $this->actingAs($user)->post(route('profile.licenses.store'), [
            'license_type' => 'STR Perawat',
            'license_number' => 'STR-2023-9988',
            'issued_at' => '2023-01-01',
            'expires_at' => '2028-01-01',
        ]);
        $resCreate->assertRedirect(route('profile.show'));

        $license = ApplicantLicense::where('applicant_profile_id', $user->applicantProfile->id)->first();
        $this->assertNotNull($license);

        // Cross-user update forbidden
        $resCrossUpdate = $this->actingAs($otherUser)->patch(route('profile.licenses.update', $license), [
            'license_type' => 'Hacked',
            'license_number' => 'Hacked',
        ]);
        $resCrossUpdate->assertStatus(403);

        // Cross-user delete forbidden
        $resCrossDelete = $this->actingAs($otherUser)->delete(route('profile.licenses.destroy', $license));
        $resCrossDelete->assertStatus(403);

        // Owner delete succeeds
        $resOwnerDelete = $this->actingAs($user)->delete(route('profile.licenses.destroy', $license));
        $resOwnerDelete->assertRedirect(route('profile.show'));
        $this->assertDatabaseMissing('applicant_licenses', ['id' => $license->id]);
    }

    public function test_certification_crud_and_cross_user_protection(): void
    {
        $user = $this->createApplicantUser();
        $otherUser = $this->createApplicantUser(['email' => 'other3@example.com', 'phone' => '6281234567839'], ['nik' => '3510000000000033']);

        $resCreate = $this->actingAs($user)->post(route('profile.certifications.store'), [
            'name' => 'BTCLS',
            'issuer' => 'PPNI',
            'certificate_number' => 'CERT-998877',
            'issued_at' => '2024-01-10',
            'expires_at' => '2027-01-10',
        ]);
        $resCreate->assertRedirect(route('profile.show'));

        $cert = ApplicantCertification::where('applicant_profile_id', $user->applicantProfile->id)->first();
        $this->assertNotNull($cert);

        // Cross-user access forbidden
        $this->actingAs($otherUser)->patch(route('profile.certifications.update', $cert), ['name' => 'Hacked'])->assertStatus(403);
        $this->actingAs($otherUser)->delete(route('profile.certifications.destroy', $cert))->assertStatus(403);

        // Owner delete succeeds
        $this->actingAs($user)->delete(route('profile.certifications.destroy', $cert))->assertRedirect(route('profile.show'));
        $this->assertDatabaseMissing('applicant_certifications', ['id' => $cert->id]);
    }

    public function test_work_experience_crud_and_date_consistency(): void
    {
        $user = $this->createApplicantUser();
        $otherUser = $this->createApplicantUser(['email' => 'other4@example.com', 'phone' => '6281234567849'], ['nik' => '3510000000000044']);

        // End date before start date rejected
        $resInvalid = $this->actingAs($user)->post(route('profile.work-experiences.store'), [
            'company_name' => 'RS Lama',
            'position' => 'Perawat',
            'start_date' => '2024-05-01',
            'end_date' => '2023-05-01',
        ]);
        $resInvalid->assertSessionHasErrors(['end_date']);

        // Valid create with ongoing job (end_date null)
        $resCreate = $this->actingAs($user)->post(route('profile.work-experiences.store'), [
            'company_name' => 'RS Citra Medika',
            'position' => 'Staff Keperawatan',
            'start_date' => '2023-01-01',
            'end_date' => null,
            'description' => 'Melakukan asuhan keperawatan rawat inap.',
        ]);
        $resCreate->assertRedirect(route('profile.show'));

        $work = ApplicantWorkExperience::where('applicant_profile_id', $user->applicantProfile->id)->first();
        $this->assertNotNull($work);

        // Cross-user access forbidden
        $this->actingAs($otherUser)->patch(route('profile.work-experiences.update', $work), [
            'company_name' => 'Hacked',
            'position' => 'Staff Keperawatan',
        ])->assertStatus(403);
        $this->actingAs($otherUser)->delete(route('profile.work-experiences.destroy', $work))->assertStatus(403);

        // Owner delete succeeds
        $this->actingAs($user)->delete(route('profile.work-experiences.destroy', $work))->assertRedirect(route('profile.show'));
        $this->assertDatabaseMissing('applicant_work_experiences', ['id' => $work->id]);
    }

    public function test_profile_completeness_service_evaluates_correctly(): void
    {
        $service = new ProfileCompletenessService();

        // 1. Fresh user right after registration:
        // Has name, email, phone, NIK (4 criteria = 40%)
        $user = $this->createApplicantUser();
        $this->assertEquals(40, $service->percentage($user));
        $this->assertFalse($service->isComplete($user));
        $this->assertCount(6, $service->missingItems($user));

        // 2. Fill personal data (+3 criteria: birth_place, birth_date, gender -> 70%)
        $user->applicantProfile->update([
            'birth_place' => 'Banyuwangi',
            'birth_date' => '1996-04-12',
            'gender' => 'Laki-laki',
        ]);
        $this->assertEquals(70, $service->percentage($user->fresh()));

        // 3. Fill address (+2 criteria: address_ktp, address_domicile -> 90%)
        $user->applicantProfile->update([
            'address_ktp' => 'Jl. Ahmad Yani No. 5',
            'address_domicile' => 'Jl. Ahmad Yani No. 5',
            'same_as_ktp' => true,
        ]);
        $this->assertEquals(90, $service->percentage($user->fresh()));

        // 4. Add education (+1 criterion: min 1 education -> 100%)
        $edu = ApplicantEducation::create([
            'applicant_profile_id' => $user->applicantProfile->id,
            'education_level' => 'S1',
            'institution_name' => 'Universitas Jember',
            'study_program' => 'Keperawatan',
        ]);
        $this->assertEquals(100, $service->percentage($user->fresh()));
        $this->assertTrue($service->isComplete($user->fresh()));
        $this->assertEmpty($service->missingItems($user->fresh()));

        // 5. Adding License, Certification, Work Experience does NOT change completeness percentage (still 100%)
        ApplicantLicense::create([
            'applicant_profile_id' => $user->applicantProfile->id,
            'license_type' => 'STR Perawat',
            'license_number' => 'STR-12345',
        ]);
        ApplicantCertification::create([
            'applicant_profile_id' => $user->applicantProfile->id,
            'name' => 'BLS',
        ]);
        ApplicantWorkExperience::create([
            'applicant_profile_id' => $user->applicantProfile->id,
            'company_name' => 'Klinik Sehat',
            'position' => 'Perawat',
        ]);
        $this->assertEquals(100, $service->percentage($user->fresh()));

        // 6. Deleting the only education drops completeness back to 90%
        $edu->delete();
        $this->assertEquals(90, $service->percentage($user->fresh()));
        $this->assertFalse($service->isComplete($user->fresh()));
    }

    public function test_profile_completeness_service_is_safe_without_applicant_profile(): void
    {
        $service = new ProfileCompletenessService();

        // User without applicantProfile record
        $user = User::create([
            'name' => 'Orphan User',
            'email' => 'orphan@example.com',
            'phone' => '6281234567891',
            'password' => 'Password123',
        ]);

        $details = $service->details($user);
        $this->assertTrue($details['name']);
        $this->assertTrue($details['email']);
        $this->assertTrue($details['phone']);
        $this->assertFalse($details['nik']);
        $this->assertFalse($details['birth_place']);
        $this->assertEquals(30, $service->percentage($user));
        $this->assertFalse($service->isComplete($user));
    }

    public function test_portal_home_displays_completeness_percentage_and_cta(): void
    {
        $user = $this->createApplicantUser();

        $response = $this->actingAs($user)->get(route('portal.home'));

        $response->assertStatus(200);
        $response->assertSee('Kelengkapan Profil Dasar');
        $response->assertSee('40% Selesai');
        $response->assertSee('Lengkapi Profil Sekarang');
    }
}
