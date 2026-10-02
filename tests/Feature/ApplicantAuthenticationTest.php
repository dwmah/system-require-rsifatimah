<?php

namespace Tests\Feature;

use App\Enums\JobVacancyStatus;
use App\Enums\RecruitmentPeriodStatus;
use App\Models\ApplicantProfile;
use App\Models\JobVacancy;
use App\Models\RecruitmentPeriod;
use App\Models\User;
use App\Support\PhoneNumberNormalizer;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\RateLimiter;
use Tests\TestCase;

class ApplicantAuthenticationTest extends TestCase
{
    use RefreshDatabase;

    protected function createActiveRecruitment(): JobVacancy
    {
        $period = RecruitmentPeriod::create([
            'name' => 'Rekrutmen SDM 2026',
            'code' => 'RSIF-2026',
            'registration_start_at' => now()->subDays(2),
            'registration_end_at' => now()->addDays(10),
            'selection_start_at' => now()->addDays(11),
            'selection_end_at' => now()->addDays(30),
            'status' => RecruitmentPeriodStatus::OPEN,
        ]);

        return JobVacancy::create([
            'recruitment_period_id' => $period->id,
            'title' => 'Perawat Medis',
            'code' => 'POS-2026-MED-01',
            'unit' => 'Pelayanan Medis',
            'employment_type' => 'PKWT',
            'quota' => 2,
            'location' => 'Banyuwangi',
            'open_at' => now()->subDays(2),
            'close_at' => now()->addDays(10),
            'status' => JobVacancyStatus::OPEN,
        ]);
    }

    public function test_app_timezone_is_asia_jakarta(): void
    {
        $this->assertEquals('Asia/Jakarta', config('app.timezone'));
    }

    public function test_phone_number_normalizer_canonicalizes_various_formats(): void
    {
        $this->assertEquals('6281234567890', PhoneNumberNormalizer::normalize('081234567890'));
        $this->assertEquals('6281234567890', PhoneNumberNormalizer::normalize('+6281234567890'));
        $this->assertEquals('6281234567890', PhoneNumberNormalizer::normalize('6281234567890'));
        $this->assertEquals('6281234567890', PhoneNumberNormalizer::normalize('0812-3456-7890'));
        $this->assertEquals('6281234567890', PhoneNumberNormalizer::normalize('+62 812 3456 7890'));
        $this->assertNull(PhoneNumberNormalizer::normalize(null));
        $this->assertNull(PhoneNumberNormalizer::normalize(''));
    }

    public function test_registration_page_accessible_when_recruitment_is_active(): void
    {
        $vacancy = $this->createActiveRecruitment();

        $response = $this->get(route('register', ['lowongan' => $vacancy->id]));

        $response->assertStatus(200);
        $response->assertSee('Registrasi Akun Pelamar');
        $response->assertSee('Saya menyetujui Syarat & Ketentuan');
        $response->assertSee('name="job_vacancy_id"', false);
    }

    public function test_registration_without_vacancy_redirects_to_vacancies_index(): void
    {
        $this->createActiveRecruitment();

        $response = $this->get(route('register'));

        $response->assertRedirect(route('vacancies.index'));
        $response->assertSessionHas('info');
    }

    public function test_registration_page_redirects_to_closed_when_no_active_recruitment(): void
    {
        $response = $this->get(route('register'));

        $response->assertRedirect(route('recruitment.closed'));
    }

    public function test_registration_post_is_protected_by_recruitment_open(): void
    {
        $response = $this->post(route('register.store'), [
            'name' => 'Budi Pelamar',
            'nik' => '3510123456780001',
            'phone' => '081234567890',
            'email' => 'budi@example.com',
            'password' => 'Rahasia123',
            'password_confirmation' => 'Rahasia123',
            'terms' => '1',
        ]);

        $response->assertRedirect(route('recruitment.closed'));
        $this->assertDatabaseMissing('users', ['email' => 'budi@example.com']);
    }

    public function test_registration_creates_user_with_registration_number_profile_and_consent_timestamps(): void
    {
        $vacancy = $this->createActiveRecruitment();

        $response = $this->post(route('register.store'), [
            'name' => 'Ahmad Pelamar',
            'nik' => '3510123456780001',
            'phone' => '081234567890',
            'email' => 'ahmad@example.com',
            'password' => 'Rahasia123',
            'password_confirmation' => 'Rahasia123',
            'terms' => '1',
            'job_vacancy_id' => $vacancy->id,
        ]);

        $response->assertRedirect(route('portal.applications.index'));
        $this->assertAuthenticated();
        $this->assertDatabaseHas('job_applications', [
            'job_vacancy_id' => $vacancy->id,
            'status' => 'draft',
        ]);

        $user = User::where('email', 'ahmad@example.com')->first();
        $this->assertNotNull($user);
        $this->assertTrue($user->is_active);
        $this->assertEquals('6281234567890', $user->phone);
        $this->assertNotNull($user->terms_accepted_at);
        $this->assertNotNull($user->privacy_accepted_at);
        $this->assertTrue(Hash::check('Rahasia123', $user->password));

        $expectedYear = now()->format('Y');
        $expectedRegNo = sprintf('PLM-%s-%06d', $expectedYear, $user->id);
        $this->assertEquals($expectedRegNo, $user->registration_number);

        $profile = ApplicantProfile::where('user_id', $user->id)->first();
        $this->assertNotNull($profile);
        $this->assertEquals('3510123456780001', $profile->nik);
        $this->assertNull($profile->birth_place);
    }

    public function test_duplicate_nik_is_rejected(): void
    {
        $this->createActiveRecruitment();

        $user1 = User::create([
            'name' => 'User 1',
            'email' => 'user1@example.com',
            'phone' => '6281111111111',
            'password' => 'Secret123',
        ]);
        ApplicantProfile::create([
            'user_id' => $user1->id,
            'nik' => '3510999988887777',
        ]);

        $response = $this->post(route('register.store'), [
            'name' => 'User 2',
            'nik' => '3510999988887777',
            'phone' => '082222222222',
            'email' => 'user2@example.com',
            'password' => 'Secret123',
            'password_confirmation' => 'Secret123',
            'terms' => '1',
        ]);

        $response->assertSessionHasErrors(['nik']);
        $this->assertDatabaseMissing('users', ['email' => 'user2@example.com']);
    }

    public function test_duplicate_phone_is_rejected_even_with_different_input_format(): void
    {
        $this->createActiveRecruitment();

        User::create([
            'name' => 'User 1',
            'email' => 'user1@example.com',
            'phone' => '6281234567890',
            'password' => 'Secret123',
        ]);

        // Input using 08... should normalize to 6281234567890 and collide
        $response = $this->post(route('register.store'), [
            'name' => 'User 2',
            'nik' => '3510123456780002',
            'phone' => '081234567890',
            'email' => 'user2@example.com',
            'password' => 'Secret123',
            'password_confirmation' => 'Secret123',
            'terms' => '1',
        ]);

        $response->assertSessionHasErrors(['phone']);
        $this->assertDatabaseMissing('users', ['email' => 'user2@example.com']);
    }

    public function test_duplicate_email_is_rejected(): void
    {
        $this->createActiveRecruitment();

        User::create([
            'name' => 'User 1',
            'email' => 'user1@example.com',
            'phone' => '6281111111111',
            'password' => 'Secret123',
        ]);

        $response = $this->post(route('register.store'), [
            'name' => 'User Duplicate',
            'nik' => '3510123456780003',
            'phone' => '082222222222',
            'email' => 'user1@example.com',
            'password' => 'Secret123',
            'password_confirmation' => 'Secret123',
            'terms' => '1',
        ]);

        $response->assertSessionHasErrors(['email']);
    }

    public function test_login_succeeds_using_email(): void
    {
        $user = User::create([
            'name' => 'Siti Pelamar',
            'email' => 'siti@example.com',
            'phone' => '6281234567801',
            'password' => 'Password123',
            'is_active' => true,
        ]);

        $response = $this->post(route('login.store'), [
            'login' => 'siti@example.com',
            'password' => 'Password123',
        ]);

        $response->assertRedirect(route('portal.home'));
        $this->assertAuthenticatedAs($user);
    }

    public function test_login_fails_using_registration_number(): void
    {
        User::create([
            'name' => 'Bambang Pelamar',
            'email' => 'bambang@example.com',
            'phone' => '6281234567802',
            'registration_number' => 'PLM-2026-000042',
            'password' => 'Password123',
            'is_active' => true,
        ]);

        $response = $this->post(route('login.store'), [
            'login' => 'PLM-2026-000042',
            'password' => 'Password123',
        ]);

        $response->assertSessionHasErrors(['login']);
        $this->assertGuest();
    }

    public function test_login_fails_using_phone_number(): void
    {
        User::create([
            'name' => 'Phone Pelamar',
            'email' => 'phone@example.com',
            'phone' => '6281234567809',
            'password' => 'Password123',
            'is_active' => true,
        ]);

        $response = $this->post(route('login.store'), [
            'email' => '6281234567809',
            'password' => 'Password123',
        ]);

        $response->assertSessionHasErrors(['email']);
        $this->assertGuest();
    }

    public function test_login_fails_with_incorrect_password(): void
    {
        User::create([
            'name' => 'Dewi Pelamar',
            'email' => 'dewi@example.com',
            'phone' => '6281234567803',
            'password' => 'CorrectPassword123',
            'is_active' => true,
        ]);

        $response = $this->post(route('login.store'), [
            'login' => 'dewi@example.com',
            'password' => 'WrongPassword',
        ]);

        $response->assertSessionHasErrors(['login']);
        $this->assertGuest();
    }

    public function test_inactive_account_cannot_login(): void
    {
        User::create([
            'name' => 'Nonaktif Pelamar',
            'email' => 'nonaktif@example.com',
            'phone' => '6281234567804',
            'password' => 'Password123',
            'is_active' => false,
        ]);

        $response = $this->post(route('login.store'), [
            'login' => 'nonaktif@example.com',
            'password' => 'Password123',
        ]);

        $response->assertSessionHasErrors(['login']);
        $this->assertGuest();
    }

    public function test_login_remains_available_when_public_recruitment_is_closed(): void
    {
        // No active vacancy exists
        $response = $this->get(route('login'));
        $response->assertStatus(200);
        $response->assertSee('Masuk Portal Pelamar');

        $user = User::create([
            'name' => 'Pelamar Lama',
            'email' => 'lama@example.com',
            'phone' => '6281234567805',
            'password' => 'Password123',
            'is_active' => true,
        ]);

        $postResponse = $this->post(route('login.store'), [
            'login' => 'lama@example.com',
            'password' => 'Password123',
        ]);

        $postResponse->assertRedirect(route('portal.home'));
        $this->assertAuthenticatedAs($user);
    }

    public function test_authenticated_active_user_can_access_portal_home(): void
    {
        $user = User::create([
            'name' => 'Rina Pelamar',
            'email' => 'rina@example.com',
            'phone' => '6281234567806',
            'registration_number' => 'PLM-2026-000099',
            'password' => 'Password123',
            'is_active' => true,
        ]);

        $response = $this->actingAs($user)->get(route('portal.home'));

        $response->assertStatus(200);
        $response->assertSee('Selamat Datang, Rina Pelamar');
        $response->assertSee('PLM-2026-000099');
        $response->assertSee('Keluar Akun');
    }

    public function test_portal_home_accessible_when_recruitment_is_closed(): void
    {
        // Ensure no active recruitment
        $this->assertEquals(0, JobVacancy::currentlyOpen()->count());

        $user = User::create([
            'name' => 'Tono Pelamar',
            'email' => 'tono@example.com',
            'phone' => '6281234567807',
            'password' => 'Password123',
            'is_active' => true,
        ]);

        $response = $this->actingAs($user)->get(route('portal.home'));
        $response->assertStatus(200);
        $response->assertSee('Selamat Datang, Tono Pelamar');
    }

    public function test_guest_cannot_access_portal_home(): void
    {
        $response = $this->get(route('portal.home'));

        $response->assertRedirect(route('login'));
    }

    public function test_inactive_authenticated_user_is_logged_out_by_middleware(): void
    {
        $user = User::create([
            'name' => 'User Nonaktif',
            'email' => 'deactive@example.com',
            'phone' => '6281234567808',
            'password' => 'Password123',
            'is_active' => false,
        ]);

        $response = $this->actingAs($user)->get(route('portal.home'));

        $response->assertRedirect(route('login'));
        $response->assertSessionHasErrors(['login']);
        $this->assertGuest();
    }

    public function test_logout_invalidates_authentication(): void
    {
        $user = User::create([
            'name' => 'Logout User',
            'email' => 'logout@example.com',
            'phone' => '6281234567809',
            'password' => 'Password123',
            'is_active' => true,
        ]);

        $response = $this->actingAs($user)->post(route('logout'));

        $response->assertRedirect(route('login'));
        $this->assertGuest();
    }

    public function test_forgot_password_sends_reset_link_for_active_user_email(): void
    {
        Notification::fake();

        $user = User::create([
            'name' => 'Active User',
            'email' => 'active@example.com',
            'phone' => '6281234567810',
            'password' => 'Password123',
            'is_active' => true,
        ]);

        $response = $this->post(route('password.email'), [
            'identifier' => 'active@example.com',
        ]);

        $response->assertSessionHas('status');
        Notification::assertSentTo($user, ResetPassword::class);
    }

    public function test_forgot_password_rejects_phone_number_and_requires_email(): void
    {
        Notification::fake();

        User::create([
            'name' => 'Phone User',
            'email' => 'phoneuser@example.com',
            'phone' => '6281234567899',
            'password' => 'Password123',
            'is_active' => true,
        ]);

        // Input using phone should now be rejected as invalid email
        $response = $this->post(route('password.email'), [
            'email' => '081234567899',
        ]);

        $response->assertSessionHasErrors(['email']);
        Notification::assertNothingSent();
    }

    public function test_forgot_password_gives_generic_response_for_unregistered_identifier(): void
    {
        Notification::fake();

        $response = $this->post(route('password.email'), [
            'identifier' => 'unregistered@example.com',
        ]);

        $response->assertSessionHas('status');
        $this->assertStringContainsString(
            'Jika data tersebut terdaftar dan akun masih aktif',
            session('status')
        );
        Notification::assertNothingSent();
    }

    public function test_forgot_password_does_not_send_to_inactive_user(): void
    {
        Notification::fake();

        User::create([
            'name' => 'Inactive User',
            'email' => 'inactive_reset@example.com',
            'phone' => '6281234567888',
            'password' => 'Password123',
            'is_active' => false,
        ]);

        $response = $this->post(route('password.email'), [
            'identifier' => 'inactive_reset@example.com',
        ]);

        $response->assertSessionHas('status');
        Notification::assertNothingSent();
    }

    public function test_reset_password_page_requires_token_parameter(): void
    {
        $response = $this->get('/reset-password/sample-token?email=user@example.com');
        $response->assertStatus(200);
        $response->assertSee('Perbarui Password Akun');
        $response->assertSee('sample-token');
    }

    public function test_reset_password_succeeds_with_valid_token_and_active_user(): void
    {
        $user = User::create([
            'name' => 'Reset Target',
            'email' => 'target@example.com',
            'phone' => '6281234567877',
            'password' => 'OldPassword123',
            'is_active' => true,
        ]);

        $token = Password::broker()->createToken($user);

        $response = $this->post(route('password.update'), [
            'token' => $token,
            'email' => 'target@example.com',
            'password' => 'NewPassword123',
            'password_confirmation' => 'NewPassword123',
        ]);

        $response->assertRedirect(route('login'));
        $response->assertSessionHas('status', 'Password berhasil diperbarui. Silakan masuk kembali.');

        $user->refresh();
        $this->assertTrue(Hash::check('NewPassword123', $user->password));
    }

    public function test_reset_password_fails_for_inactive_account(): void
    {
        $user = User::create([
            'name' => 'Inactive Reset',
            'email' => 'inactive_target@example.com',
            'phone' => '6281234567866',
            'password' => 'OldPassword123',
            'is_active' => false,
        ]);

        $token = Password::broker()->createToken($user);

        $response = $this->post(route('password.update'), [
            'token' => $token,
            'email' => 'inactive_target@example.com',
            'password' => 'NewPassword123',
            'password_confirmation' => 'NewPassword123',
        ]);

        $response->assertSessionHasErrors(['email']);
        $user->refresh();
        $this->assertTrue(Hash::check('OldPassword123', $user->password));
    }

    public function test_repeated_failed_logins_are_throttled_by_rate_limiter(): void
    {
        RateLimiter::clear(hash('sha256', 'throttle@example.com|127.0.0.1'));

        User::create([
            'name' => 'Throttle User',
            'email' => 'throttle@example.com',
            'phone' => '6281234567855',
            'password' => 'ValidPassword123',
            'is_active' => true,
        ]);

        // Attempt 5 wrong logins
        for ($i = 0; $i < 5; $i++) {
            $response = $this->post(route('login.store'), [
                'login' => 'throttle@example.com',
                'password' => 'WrongPassword',
            ]);
            $response->assertSessionHasErrors(['login']);
        }

        // 6th attempt should be locked out
        $response6 = $this->post(route('login.store'), [
            'login' => 'throttle@example.com',
            'password' => 'ValidPassword123', // even with correct password
        ]);

        $response6->assertSessionHasErrors(['login']);
        $this->assertGuest();
    }
}
