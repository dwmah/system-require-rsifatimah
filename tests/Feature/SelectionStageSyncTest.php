<?php

namespace Tests\Feature;

use App\Enums\JobApplicationStatus;
use App\Enums\JobVacancyStatus;
use App\Enums\RecruitmentPeriodStatus;
use App\Models\ApplicantProfile;
use App\Models\ApplicationSnapshot;
use App\Models\JobApplication;
use App\Models\JobVacancy;
use App\Models\RecruitmentPeriod;
use App\Models\User;
use App\Models\VacancySelectionStage;
use App\Services\Recruitment\SelectionTrackingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SelectionStageSyncTest extends TestCase
{
    use RefreshDatabase;

    public function test_newly_added_vacancy_stage_syncs_and_appears_in_applicant_tracking_timeline(): void
    {
        // 1. Buat pelamar
        $user = User::factory()->create([
            'email' => 'applicant_sync@example.com',
            'is_active' => true,
        ]);
        ApplicantProfile::create([
            'user_id' => $user->id,
            'nik' => '3510000000000001',
            'gender' => 'Laki-laki',
            'birth_place' => 'Banyuwangi',
            'birth_date' => '1995-01-01',
            'phone' => '081234567890',
            'address_ktp' => 'Jl. Banyuwangi No. 1',
            'is_profile_completed' => true,
        ]);

        // 2. Buat lowongan dengan tahapan 1 & 2
        $period = RecruitmentPeriod::create([
            'name' => 'Rekrutmen Medis 2026',
            'code' => 'PR-MED-2026',
            'status' => RecruitmentPeriodStatus::OPEN,
            'registration_start_at' => now()->subDays(5),
            'registration_end_at' => now()->addDays(10),
        ]);

        $vacancy = JobVacancy::create([
            'recruitment_period_id' => $period->id,
            'code' => 'DOK-001',
            'title' => 'Dokter Umum Rawat Inap',
            'unit' => 'Pelayanan Medis',
            'employment_type' => 'PKWT',
            'location' => 'RSI Fatimah Banyuwangi',
            'quota' => 2,
            'status' => JobVacancyStatus::OPEN,
            'open_at' => now()->subDays(5),
            'close_at' => now()->addDays(10),
        ]);

        $stage1 = VacancySelectionStage::create([
            'job_vacancy_id' => $vacancy->id,
            'name' => 'Seleksi Administrasi & Berkas',
            'description' => 'Verifikasi keabsahan dokumen',
            'sort_order' => 1,
            'start_at' => now()->subDays(2),
            'end_at' => now()->addDays(1),
        ]);

        $stage2 = VacancySelectionStage::create([
            'job_vacancy_id' => $vacancy->id,
            'name' => 'Ujian Tulis & CBT',
            'description' => 'Tes kompetensi klinis',
            'sort_order' => 2,
            'start_at' => now()->addDays(2),
            'end_at' => now()->addDays(4),
        ]);

        // 3. User submit lamaran -> Snapshot dibuat dengan 2 tahapan awal saja
        $application = JobApplication::create([
            'user_id' => $user->id,
            'recruitment_period_id' => $period->id,
            'job_vacancy_id' => $vacancy->id,
            'status' => JobApplicationStatus::SUBMITTED,
            'application_number' => 'LAM-MED-000001',
            'submitted_at' => now()->subDay(),
            'locked_at' => now()->subDay(),
        ]);

        ApplicationSnapshot::create([
            'job_application_id' => $application->id,
            'recruitment_period_data' => ['name' => $period->name],
            'personal_data' => ['name' => $user->name, 'nik' => '3510000000000001'],
            'educations' => [],
            'licenses' => [],
            'certifications' => [],
            'work_experiences' => [],
            'vacancy_data' => [
                'title' => $vacancy->title,
                'code' => $vacancy->code,
                'selection_stages' => [
                    [
                        'id' => $stage1->id,
                        'name' => $stage1->name,
                        'description' => $stage1->description,
                        'sort_order' => 1,
                        'start_at' => $stage1->start_at->toIso8601String(),
                        'end_at' => $stage1->end_at->toIso8601String(),
                    ],
                    [
                        'id' => $stage2->id,
                        'name' => $stage2->name,
                        'description' => $stage2->description,
                        'sort_order' => 2,
                        'start_at' => $stage2->start_at->toIso8601String(),
                        'end_at' => $stage2->end_at->toIso8601String(),
                    ],
                ],
            ],
            'captured_at' => now()->subDay(),
        ]);

        // 4. Admin kemudian menambahkan tahapan ke-3 pada JobVacancy SETELAH lamaran disubmit
        $stage3 = VacancySelectionStage::create([
            'job_vacancy_id' => $vacancy->id,
            'name' => 'Wawancara Direksi & Komite Medis',
            'description' => 'Wawancara panel manajemen',
            'sort_order' => 3,
            'start_at' => now()->addDays(5),
            'end_at' => now()->addDays(7),
        ]);

        // 5. Verifikasi via SelectionTrackingService::tracking()
        $trackingService = app(SelectionTrackingService::class);
        $trackingData = $trackingService->tracking($application);

        $this->assertTrue($trackingData['has_snapshot']);
        $this->assertCount(3, $trackingData['stages'], 'Tahapan ke-3 harus muncul di output tracking pelamar');

        $stageIds = array_column($trackingData['stages'], 'id');
        $this->assertContains($stage3->id, $stageIds, 'ID tahapan ke-3 harus ada di tracking');

        $stage3Output = collect($trackingData['stages'])->firstWhere('id', $stage3->id);
        $this->assertNotNull($stage3Output);
        $this->assertEquals('Wawancara Direksi & Komite Medis', $stage3Output['name']);
        $this->assertEquals(3, $stage3Output['sort_order']);
        $this->assertEquals('waiting', $stage3Output['public_state']);
        $this->assertEquals('Menunggu', $stage3Output['label']);
        $this->assertNull($stage3Output['applicant_message']);
        $this->assertNull($stage3Output['published_at']);

        // 6. Verifikasi via HTTP route tracking portal pelamar
        $response = $this->actingAs($user)->get(route('portal.applications.tracking', $application));
        $response->assertOk();
        $response->assertSee('Seleksi Administrasi & Berkas');
        $response->assertSee('Ujian Tulis & CBT');
        $response->assertSee('Wawancara Direksi & Komite Medis');
    }
}
