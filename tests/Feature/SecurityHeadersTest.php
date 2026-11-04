<?php

namespace Tests\Feature;

use App\Enums\JobVacancyStatus;
use App\Enums\RecruitmentPeriodStatus;
use App\Models\JobVacancy;
use App\Models\RecruitmentGuide;
use App\Models\RecruitmentPeriod;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SecurityHeadersTest extends TestCase
{
    use RefreshDatabase;

    public function test_responses_contain_security_headers(): void
    {
        $response = $this->get(route('recruitment.closed'));

        $response->assertHeader('X-Frame-Options', 'SAMEORIGIN');
        $response->assertHeader('X-Content-Type-Options', 'nosniff');
        $response->assertHeader('X-XSS-Protection', '1; mode=block');
        $response->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin');
        $response->assertHeader('Permissions-Policy', 'camera=(), microphone=(), geolocation=()');
    }

    public function test_requirements_guide_escapes_content_against_xss(): void
    {
        $period = RecruitmentPeriod::create([
            'name' => 'Periode Test',
            'code' => 'PER-TEST',
            'registration_start_at' => now()->subDay(),
            'registration_end_at' => now()->addDay(),
            'status' => RecruitmentPeriodStatus::OPEN,
        ]);

        JobVacancy::create([
            'recruitment_period_id' => $period->id,
            'title' => 'Formasi Test',
            'code' => 'VAC-TEST-SEC',
            'unit' => 'Pelayanan Medis',
            'employment_type' => 'Tenaga Medis',
            'location' => 'RSI Fatimah Banyuwangi',
            'quota' => 1,
            'status' => JobVacancyStatus::OPEN,
            'open_at' => now()->subDay(),
            'close_at' => now()->addDay(),
        ]);

        $guide = RecruitmentGuide::where('slug', 'persyaratan-pendaftaran')->first();
        if ($guide) {
            $guide->update([
                'content' => [
                    [
                        'title' => 'Syarat XSS',
                        'description' => "<script>alert('xss')</script>\nBaris kedua",
                    ],
                ],
                'is_active' => true,
            ]);
        } else {
            RecruitmentGuide::create([
                'type' => 'requirements',
                'slug' => 'persyaratan-pendaftaran',
                'title' => 'Persyaratan Test',
                'content' => [
                    [
                        'title' => 'Syarat XSS',
                        'description' => "<script>alert('xss')</script>\nBaris kedua",
                    ],
                ],
                'is_active' => true,
            ]);
        }

        $response = $this->get(route('guides.requirements'));

        $response->assertStatus(200);
        $response->assertDontSee('<script>', false);
        $response->assertSee('&lt;script&gt;', false);
    }
}
