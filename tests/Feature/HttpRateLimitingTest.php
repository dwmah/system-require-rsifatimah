<?php

namespace Tests\Feature;

use App\Enums\JobVacancyStatus;
use App\Enums\RecruitmentPeriodStatus;
use App\Models\JobVacancy;
use App\Models\RecruitmentPeriod;
use App\Models\User;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\RateLimiter;
use Tests\TestCase;

class HttpRateLimitingTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        RateLimiter::clear('registration-minute:192.168.10.1');
        RateLimiter::clear('registration-hour:192.168.10.1');
        RateLimiter::clear('registration-minute:192.168.10.2');
        RateLimiter::clear('registration-hour:192.168.10.2');
        RateLimiter::clear('registration-minute:192.168.10.3');
        RateLimiter::clear('registration-hour:192.168.10.3');

        RateLimiter::clear('password-reset-minute:192.168.20.1');
        RateLimiter::clear('password-reset-hour:192.168.20.1');
        RateLimiter::clear('password-reset-minute:192.168.20.2');
        RateLimiter::clear('password-reset-hour:192.168.20.2');
        RateLimiter::clear('password-reset-minute:192.168.20.3');
        RateLimiter::clear('password-reset-hour:192.168.20.3');
    }

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

    // ==========================================
    // REGISTRATION RATE LIMITING TESTS
    // ==========================================

    public function test_normal_registration_request_is_not_rate_limited(): void
    {
        $this->createActiveRecruitment();

        $response = $this->withServerVariables(['REMOTE_ADDR' => '192.168.10.1'])
            ->post(route('register.store'), [
                'name' => 'Pelamar Satu',
                'nik' => '3510123456780001',
                'phone' => '081234567891',
                'email' => 'pelamar1@example.com',
                'password' => 'Rahasia123',
                'password_confirmation' => 'Rahasia123',
                'terms' => '1',
            ]);

        $response->assertRedirect(route('portal.applications.index'));
        $this->assertDatabaseHas('users', ['email' => 'pelamar1@example.com']);
    }

    public function test_exceeding_registration_rate_limit_returns_429_too_many_requests(): void
    {
        $this->createActiveRecruitment();

        $ip = '192.168.10.2';

        // 5 requests allowed per minute
        for ($i = 1; $i <= 5; $i++) {
            $response = $this->withServerVariables(['REMOTE_ADDR' => $ip])
                ->post(route('register.store'), [
                    'name' => "Pelamar {$i}",
                    'nik' => sprintf('351012345678%04d', $i),
                    'phone' => sprintf('0812345678%02d', $i),
                    'email' => "pelamar{$i}@example.com",
                    'password' => 'Rahasia123',
                    'password_confirmation' => 'Rahasia123',
                    'terms' => '1',
                ]);

            $this->assertNotEquals(429, $response->getStatusCode(), "Request {$i} should not be 429");
        }

        // 6th request from the same IP must be throttled with HTTP 429
        $throttledResponse = $this->withServerVariables(['REMOTE_ADDR' => $ip])
            ->post(route('register.store'), [
                'name' => 'Pelamar Enam',
                'nik' => '3510123456780099',
                'phone' => '081234567899',
                'email' => 'pelamar6@example.com',
                'password' => 'Rahasia123',
                'password_confirmation' => 'Rahasia123',
                'terms' => '1',
            ]);

        $throttledResponse->assertStatus(429);
        $throttledResponse->assertSee('Terlalu banyak percobaan pendaftaran');
        $this->assertDatabaseMissing('users', ['email' => 'pelamar6@example.com']);
    }

    public function test_get_registration_page_is_not_blocked_when_post_limiter_is_exceeded(): void
    {
        $vacancy = $this->createActiveRecruitment();

        $ip = '192.168.10.2';

        // Hit limit on POST
        for ($i = 1; $i <= 6; $i++) {
            $this->withServerVariables(['REMOTE_ADDR' => $ip])
                ->post(route('register.store'), [
                    'name' => "Tester {$i}",
                    'email' => "tester{$i}@example.com",
                ]);
        }

        // GET /daftar must remain accessible (200 OK)
        $getResponse = $this->withServerVariables(['REMOTE_ADDR' => $ip])
            ->get(route('register', ['lowongan' => $vacancy->id]));

        $getResponse->assertStatus(200);
        $getResponse->assertSee('Registrasi Akun Pelamar');
    }

    public function test_registration_limiter_does_not_alter_normal_business_validation(): void
    {
        $this->createActiveRecruitment();

        $ip = '192.168.10.3';

        // Submit invalid data (missing NIK & password confirmation mismatch)
        $response = $this->withServerVariables(['REMOTE_ADDR' => $ip])
            ->post(route('register.store'), [
                'name' => 'Invalid User',
                'nik' => '123', // invalid length
                'phone' => '081234567890',
                'email' => 'invalid@example.com',
                'password' => 'Rahasia123',
                'password_confirmation' => 'Different123',
                'terms' => '1',
            ]);

        $response->assertStatus(302);
        $response->assertSessionHasErrors(['nik', 'password']);
    }

    // ==========================================
    // PASSWORD RESET RATE LIMITING TESTS
    // ==========================================

    public function test_normal_password_reset_request_is_processed(): void
    {
        Notification::fake();

        $user = User::create([
            'name' => 'Reset Target',
            'email' => 'resettarget@example.com',
            'phone' => '6281234567800',
            'password' => 'Password123',
            'is_active' => true,
        ]);

        $response = $this->withServerVariables(['REMOTE_ADDR' => '192.168.20.1'])
            ->post(route('password.email'), [
                'identifier' => 'resettarget@example.com',
            ]);

        $response->assertSessionHas('status');
        Notification::assertSentTo($user, ResetPassword::class);
    }

    public function test_exceeding_password_reset_rate_limit_returns_429(): void
    {
        Notification::fake();

        $ip = '192.168.20.2';

        // 5 requests allowed per minute from the same IP
        for ($i = 1; $i <= 5; $i++) {
            $response = $this->withServerVariables(['REMOTE_ADDR' => $ip])
                ->post(route('password.email'), [
                    'identifier' => "random{$i}@example.com",
                ]);

            $this->assertNotEquals(429, $response->getStatusCode(), "Attempt {$i} should not be 429");
        }

        // 6th request from the same IP must be throttled with HTTP 429
        $throttledResponse = $this->withServerVariables(['REMOTE_ADDR' => $ip])
            ->post(route('password.email'), [
                'identifier' => 'another@example.com',
            ]);

        $throttledResponse->assertStatus(429);
        $throttledResponse->assertSee('Terlalu banyak permintaan reset password');
    }

    public function test_get_forgot_password_page_is_not_blocked_by_post_limiter(): void
    {
        $ip = '192.168.20.2';

        // Hit limit on POST
        for ($i = 1; $i <= 6; $i++) {
            $this->withServerVariables(['REMOTE_ADDR' => $ip])
                ->post(route('password.email'), [
                    'identifier' => "spam{$i}@example.com",
                ]);
        }

        // GET /lupa-password must remain 200 OK
        $getResponse = $this->withServerVariables(['REMOTE_ADDR' => $ip])
            ->get(route('password.request'));

        $getResponse->assertStatus(200);
        $getResponse->assertSee('Lupa Password Akun');
    }

    public function test_password_reset_enumeration_safety_retains_generic_response(): void
    {
        Notification::fake();

        // 1. Existing user
        User::create([
            'name' => 'Existing Person',
            'email' => 'exists@example.com',
            'phone' => '6281234567801',
            'password' => 'Password123',
            'is_active' => true,
        ]);

        $responseExisting = $this->withServerVariables(['REMOTE_ADDR' => '192.168.20.3'])
            ->post(route('password.email'), [
                'identifier' => 'exists@example.com',
            ]);

        // 2. Non-existing user
        $responseNonExisting = $this->withServerVariables(['REMOTE_ADDR' => '192.168.20.3'])
            ->post(route('password.email'), [
                'identifier' => 'doesnotexist@example.com',
            ]);

        // Both must return identical generic response in session
        $responseExisting->assertSessionHas('status');
        $responseNonExisting->assertSessionHas('status');
        $this->assertEquals(
            session('status'),
            'Jika data tersebut terdaftar dan akun masih aktif, instruksi reset password akan dikirim ke email akun Anda.'
        );
    }

    public function test_password_broker_configuration_throttle_is_preserved_at_60_seconds(): void
    {
        $this->assertEquals(60, config('auth.passwords.users.throttle'));
    }

    // ==========================================
    // RECRUITMENT CLOSED UX TESTS
    // ==========================================

    public function test_recruitment_closed_page_shows_portal_cta_for_authenticated_applicant(): void
    {
        $user = User::create([
            'name' => 'Pelamar Login',
            'email' => 'loggedin@example.com',
            'phone' => '6281234567802',
            'password' => 'Password123',
            'is_active' => true,
        ]);

        $response = $this->actingAs($user)->get(route('recruitment.closed'));

        $response->assertStatus(200);
        $response->assertSee('Kembali ke Portal Saya');
        $response->assertSee(route('portal.home'));
    }

    public function test_recruitment_closed_page_shows_login_cta_for_guest(): void
    {
        $response = $this->get(route('recruitment.closed'));

        $response->assertStatus(200);
        $response->assertSee('Masuk ke Akun Pelamar');
        $response->assertSee(route('login'));
    }

    public function test_recruitment_closed_page_renders_flash_error_safely(): void
    {
        $response = $this->withSession(['error' => 'Periode pendaftaran formasi ini telah berakhir.'])
            ->get(route('recruitment.closed'));

        $response->assertStatus(200);
        $response->assertSee('Periode pendaftaran formasi ini telah berakhir.');
    }
}
