<?php

namespace Tests\Feature;

use App\Enums\JobApplicationStatus;
use App\Enums\JobVacancyStatus;
use App\Enums\RecruitmentPeriodStatus;
use App\Models\JobApplication;
use App\Models\JobVacancy;
use App\Models\RecruitmentPeriod;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PeriodRecapLiveE2ETest extends TestCase
{
    use RefreshDatabase;

    public function test_period_lifecycle_from_open_to_recap(): void
    {
        // 1. Buat user Admin
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN, 'is_active' => true]);

        // 2. Buat Periode 1 (Aktif)
        $p1 = RecruitmentPeriod::create([
            'name' => 'Gelombang 1 RSI 2026',
            'code' => 'GEL-1',
            'status' => RecruitmentPeriodStatus::OPEN,
            'registration_start_at' => now()->subDays(5),
            'registration_end_at' => now()->addDays(5),
        ]);

        $v1 = JobVacancy::create([
            'recruitment_period_id' => $p1->id,
            'code' => 'VAC-GEL1',
            'title' => 'Perawat IGD',
            'unit' => 'IGD',
            'employment_type' => 'PKWT',
            'location' => 'RSI Fatimah',
            'quota' => 2,
            'status' => JobVacancyStatus::OPEN,
            'open_at' => now()->subDays(5),
            'close_at' => now()->addDays(5),
        ]);

        $u1 = User::factory()->create(['name' => 'Pelamar Sukses 1', 'role' => User::ROLE_APPLICANT]);
        $app1 = JobApplication::create([
            'user_id' => $u1->id,
            'job_vacancy_id' => $v1->id,
            'recruitment_period_id' => $p1->id,
            'application_number' => 'APP-GEL1-001',
            'status' => JobApplicationStatus::ACCEPTED,
            'submitted_at' => now()->subDays(3),
        ]);

        // Verifikasi pada saat P1 aktif:
        // Dashboard & lamaran masuk hanya menampilkan P1
        $resApp = $this->actingAs($admin)->get(route('admin.applications.index'));
        $resApp->assertStatus(200);
        $resApp->assertSee('APP-GEL1-001');
        $resApp->assertSee('Periode Aktif: Gelombang 1 RSI 2026');

        // 3. Periode 1 selesai/ditutup, dan dibuat Periode 2 (Aktif baru)
        $p1->update(['status' => RecruitmentPeriodStatus::COMPLETED]);

        $p2 = RecruitmentPeriod::create([
            'name' => 'Gelombang 2 RSI 2026',
            'code' => 'GEL-2',
            'status' => RecruitmentPeriodStatus::OPEN,
            'registration_start_at' => now(),
            'registration_end_at' => now()->addDays(14),
        ]);

        $v2 = JobVacancy::create([
            'recruitment_period_id' => $p2->id,
            'code' => 'VAC-GEL2',
            'title' => 'Radiografer',
            'unit' => 'Radiologi',
            'employment_type' => 'PKWT',
            'location' => 'RSI Fatimah',
            'quota' => 1,
            'status' => JobVacancyStatus::OPEN,
            'open_at' => now(),
            'close_at' => now()->addDays(14),
        ]);

        $u2 = User::factory()->create(['name' => 'Pelamar Baru P2', 'role' => User::ROLE_APPLICANT]);
        $app2 = JobApplication::create([
            'user_id' => $u2->id,
            'job_vacancy_id' => $v2->id,
            'recruitment_period_id' => $p2->id,
            'application_number' => 'APP-GEL2-001',
            'status' => JobApplicationStatus::SUBMITTED,
            'submitted_at' => now(),
        ]);

        // Verifikasi isolasi otomatis data operasional:
        // Lamaran masuk sekarang otomatis hanya menampilkan P2, data P1 tidak mencemari daftar aktif
        $resApp2 = $this->actingAs($admin)->get(route('admin.applications.index'));
        $resApp2->assertStatus(200);
        $resApp2->assertSee('Periode Aktif: Gelombang 2 RSI 2026');
        $resApp2->assertSee('APP-GEL2-001');
        $resApp2->assertDontSee('APP-GEL1-001');

        // 4. Akses modul rekap periode lampau (P1):
        // Di rekap index, kedua gelombang terdata
        $resRecapIndex = $this->actingAs($admin)->get(route('admin.recap.index'));
        $resRecapIndex->assertStatus(200);
        $resRecapIndex->assertSee('Gelombang 1 RSI 2026');
        $resRecapIndex->assertSee('Gelombang 2 RSI 2026');

        // Di detail rekap P1, data pelamar lama tetap tersimpan utuh
        $resRecapP1 = $this->actingAs($admin)->get(route('admin.recap.show', $p1));
        $resRecapP1->assertStatus(200);
        $resRecapP1->assertSee('Detail Rekap: Gelombang 1 RSI 2026');
        $resRecapP1->assertSee('APP-GEL1-001');
        $resRecapP1->assertSee('Pelamar Sukses 1');
        $resRecapP1->assertDontSee('APP-GEL2-001');

        // Ekspor Excel untuk P1 berfungsi normal
        $resExport = $this->actingAs($admin)->get(route('admin.recap.export', $p1));
        $resExport->assertStatus(200);
    }
}
