<?php

namespace Tests\Feature;

use App\Enums\ApplicationStageStatus;
use App\Enums\JobApplicationStatus;
use App\Enums\JobVacancyStatus;
use App\Enums\RecruitmentPeriodStatus;
use App\Models\ApplicantEducation;
use App\Models\ApplicantProfile;
use App\Models\ApplicationSnapshot;
use App\Models\ApplicationStageHistory;
use App\Models\JobApplication;
use App\Models\JobVacancy;
use App\Models\RecruitmentPeriod;
use App\Models\User;
use App\Models\VacancySelectionStage;
use App\Services\Recruitment\SelectionTrackingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class ApplicantSelectionTrackingTest extends TestCase
{
    use RefreshDatabase;

    protected function createApplicantUser(bool $completeProfile = true, array $userAttributes = []): User
    {
        static $sequence = 1;
        $seq = $sequence++;

        $user = User::create(array_merge([
            'name' => 'Pelamar Seleksi '.$seq,
            'email' => "pelamar.seleksi{$seq}@example.com",
            'phone' => sprintf('62812777%05d', $seq),
            'registration_number' => sprintf('PLM-2026-%06d', $seq),
            'password' => 'Password123',
            'is_active' => true,
        ], $userAttributes));

        if ($completeProfile) {
            $profile = ApplicantProfile::create([
                'user_id' => $user->id,
                'nik' => sprintf('3510554433%06d', $seq),
                'birth_place' => 'Banyuwangi',
                'birth_date' => '1997-04-12',
                'gender' => 'Perempuan',
                'religion' => 'Islam',
                'address_ktp' => 'Jl. Kepiting No. 5 Banyuwangi',
                'address_domicile' => 'Jl. Kepiting No. 5 Banyuwangi',
                'same_as_ktp' => true,
            ]);

            ApplicantEducation::create([
                'applicant_profile_id' => $profile->id,
                'education_level' => 'D3',
                'institution_name' => 'Poltekkes Kemenkes',
                'study_program' => 'Kebidanan',
                'accreditation' => 'A',
                'gpa' => '3.85',
                'graduation_year' => 2021,
            ]);
        }

        return $user->fresh(['applicantProfile', 'applicantProfile.educations']);
    }

    protected function createVacancyWithStages(array $stagesData = []): array
    {
        static $vacSeq = 1;
        $seq = $vacSeq++;

        $period = RecruitmentPeriod::create([
            'name' => 'Rekrutmen Tenaga Medis Periode '.$seq,
            'code' => 'PR-SEL-2026-'.sprintf('%03d', $seq),
            'status' => RecruitmentPeriodStatus::OPEN,
            'registration_start_at' => now()->subDays(10),
            'registration_end_at' => now()->addDays(10),
        ]);

        $vacancy = JobVacancy::create([
            'recruitment_period_id' => $period->id,
            'code' => 'BID-VK-'.sprintf('%03d', $seq),
            'title' => 'Bidan Ruang Bersalin '.$seq,
            'unit' => 'Kamar Bersalin',
            'employment_type' => 'PKWT',
            'location' => 'RSI Fatimah Banyuwangi',
            'quota' => 3,
            'status' => JobVacancyStatus::OPEN,
            'open_at' => now()->subDays(10),
            'close_at' => now()->addDays(10),
        ]);

        if (empty($stagesData)) {
            $stagesData = [
                [
                    'name' => 'Seleksi Administrasi',
                    'description' => 'Pemeriksaan berkas pendaftaran',
                    'sort_order' => 1,
                ],
                [
                    'name' => 'Ujian Tulis & CBT',
                    'description' => 'Tes kompetensi kebidanan',
                    'sort_order' => 2,
                ],
                [
                    'name' => 'Wawancara User & Medis',
                    'description' => 'Wawancara komite keperawatan',
                    'sort_order' => 3,
                ],
            ];
        }

        $stages = [];
        foreach ($stagesData as $s) {
            $stages[] = VacancySelectionStage::create(array_merge([
                'job_vacancy_id' => $vacancy->id,
            ], $s));
        }

        return [$period, $vacancy, $stages];
    }

    protected function createSubmittedApplicationWithSnapshot(User $user, JobVacancy $vacancy, array $stages): JobApplication
    {
        $application = JobApplication::create([
            'user_id' => $user->id,
            'recruitment_period_id' => $vacancy->recruitment_period_id,
            'job_vacancy_id' => $vacancy->id,
            'status' => JobApplicationStatus::SUBMITTED,
            'application_number' => sprintf('LAM-%s-%06d', $vacancy->recruitmentPeriod->code, $user->id),
            'submitted_at' => now()->subDays(2),
            'locked_at' => now()->subDays(2),
        ]);

        $snapshotStages = array_map(fn ($s) => [
            'id' => $s->id,
            'name' => $s->name,
            'description' => $s->description,
            'sort_order' => $s->sort_order,
            'start_at' => $s->start_at?->toIso8601String(),
            'end_at' => $s->end_at?->toIso8601String(),
        ], $stages);

        ApplicationSnapshot::create([
            'job_application_id' => $application->id,
            'recruitment_period_data' => ['name' => $vacancy->recruitmentPeriod->name],
            'personal_data' => ['name' => $user->name, 'nik' => '3510000000000001'],
            'educations' => [],
            'licenses' => [],
            'certifications' => [],
            'work_experiences' => [],
            'vacancy_data' => [
                'title' => $vacancy->title,
                'code' => $vacancy->code,
                'selection_stages' => $snapshotStages,
            ],
            'captured_at' => now()->subDays(2),
        ]);

        return $application->fresh(['snapshot', 'stageHistories']);
    }

    // =========================================================================
    // 1. PUBLICATION GATE & INFORMATION LEAK TESTS
    // =========================================================================

    public function test_unpublished_failed_stage_does_not_reveal_failure_to_applicant(): void
    {
        $user = $this->createApplicantUser(true);
        [$period, $vacancy, $stages] = $this->createVacancyWithStages();
        $application = $this->createSubmittedApplicationWithSnapshot($user, $vacancy, $stages);

        // Panitia menyimpan hasil evaluasi internal FAILED, tetapi published_at masih NULL
        ApplicationStageHistory::create([
            'job_application_id' => $application->id,
            'vacancy_selection_stage_id' => $stages[0]->id,
            'status' => ApplicationStageStatus::FAILED,
            'started_at' => now()->subDay(),
            'decided_at' => now(),
            'note' => 'CATATAN RAHASIA PANITIA: Nilai berkas tidak memenuhi syarat minimal akreditasi.',
            'published_at' => null,
            'applicant_message' => 'Anda tidak lolos berkas.',
        ]);

        $response = $this->actingAs($user)->get(route('portal.applications.tracking', $application));

        $response->assertOk();
        // TIDAK BOLEH membocorkan 'Tidak Lolos', 'CATATAN RAHASIA', atau applicant_message
        $response->assertDontSee('Tidak Lolos');
        $response->assertDontSee('CATATAN RAHASIA PANITIA');
        $response->assertDontSee('Anda tidak lolos berkas.');
        // Status publik harus tetap 'Menunggu'
        $response->assertSee('Menunggu');
    }

    public function test_unpublished_passed_stage_does_not_reveal_passed_status(): void
    {
        $user = $this->createApplicantUser(true);
        [$period, $vacancy, $stages] = $this->createVacancyWithStages();
        $application = $this->createSubmittedApplicationWithSnapshot($user, $vacancy, $stages);

        // Panitia menyimpan PASSED, tetapi published_at masih NULL
        ApplicationStageHistory::create([
            'job_application_id' => $application->id,
            'vacancy_selection_stage_id' => $stages[0]->id,
            'status' => ApplicationStageStatus::PASSED,
            'started_at' => now()->subDay(),
            'decided_at' => now(),
            'note' => 'Catatan internal: Kandidat terbaik.',
            'published_at' => null,
            'applicant_message' => 'Selamat Anda lolos ke tahap tes.',
        ]);

        $response = $this->actingAs($user)->get(route('portal.applications.tracking', $application));

        $response->assertOk();
        $response->assertDontSee('Lulus');
        $response->assertDontSee('Kandidat terbaik');
        $response->assertDontSee('Selamat Anda lolos ke tahap tes.');
        $response->assertSee('Menunggu');
    }

    public function test_future_published_at_is_treated_as_unpublished(): void
    {
        $user = $this->createApplicantUser(true);
        [$period, $vacancy, $stages] = $this->createVacancyWithStages();
        $application = $this->createSubmittedApplicationWithSnapshot($user, $vacancy, $stages);

        // published_at di masa depan (misal besok jam 10:00)
        ApplicationStageHistory::create([
            'job_application_id' => $application->id,
            'vacancy_selection_stage_id' => $stages[0]->id,
            'status' => ApplicationStageStatus::PASSED,
            'started_at' => now()->subDay(),
            'decided_at' => now(),
            'note' => 'Catatan panitia',
            'published_at' => now()->addDay(),
            'applicant_message' => 'Pesan masa depan',
        ]);

        $response = $this->actingAs($user)->get(route('portal.applications.tracking', $application));

        $response->assertOk();
        // Masa depan: TIDAK BOLEH menampilkan Lulus atau pesan
        $response->assertDontSee('Lulus');
        $response->assertDontSee('Pesan masa depan');
        $response->assertSee('Menunggu');
    }

    public function test_published_stage_reveals_status_and_applicant_message_but_never_internal_note(): void
    {
        $user = $this->createApplicantUser(true);
        [$period, $vacancy, $stages] = $this->createVacancyWithStages();
        $application = $this->createSubmittedApplicationWithSnapshot($user, $vacancy, $stages);

        // published_at sudah lewat (<= now())
        ApplicationStageHistory::create([
            'job_application_id' => $application->id,
            'vacancy_selection_stage_id' => $stages[0]->id,
            'status' => ApplicationStageStatus::PASSED,
            'started_at' => now()->subDays(2),
            'decided_at' => now()->subHour(),
            'note' => 'INTERNAL NOTE: Nilai berkas 95, rekomendasi kuat.',
            'published_at' => now()->subMinutes(10),
            'applicant_message' => 'Selamat, Anda dinyatakan lulus seleksi administrasi berkas.',
        ]);

        $response = $this->actingAs($user)->get(route('portal.applications.tracking', $application));

        $response->assertOk();
        // Lulus dan pesan publik HARUS tampil
        $response->assertSee('Lulus');
        $response->assertSee('Selamat, Anda dinyatakan lulus seleksi administrasi berkas.');
        // INTERNAL NOTE TIDAK BOLEH BOCOR SAMA SEKALI
        $response->assertDontSee('INTERNAL NOTE: Nilai berkas 95, rekomendasi kuat.');
        $response->assertDontSee('Nilai berkas 95');
    }

    public function test_subsequent_stages_after_published_failed_are_marked_not_continued(): void
    {
        $user = $this->createApplicantUser(true);
        [$period, $vacancy, $stages] = $this->createVacancyWithStages();
        $application = $this->createSubmittedApplicationWithSnapshot($user, $vacancy, $stages);

        // Stage 1 PUBLISHED FAILED
        ApplicationStageHistory::create([
            'job_application_id' => $application->id,
            'vacancy_selection_stage_id' => $stages[0]->id,
            'status' => ApplicationStageStatus::FAILED,
            'started_at' => now()->subDays(2),
            'decided_at' => now()->subDay(),
            'published_at' => now()->subHour(),
            'applicant_message' => 'Mohon maaf, Anda belum memenuhi kualifikasi berkas.',
        ]);

        $response = $this->actingAs($user)->get(route('portal.applications.tracking', $application));

        $response->assertOk();
        $response->assertSee('Tidak Lolos');
        // Stage 2 & 3 otomatis dipresentasikan sebagai 'Tidak Dilanjutkan'
        $response->assertSee('Tidak Dilanjutkan');
    }

    // =========================================================================
    // 2. FINAL RESULT VISIBILITY TESTS
    // =========================================================================

    public function test_accepted_application_without_published_final_result_shows_waiting_status(): void
    {
        $user = $this->createApplicantUser(true);
        [$period, $vacancy, $stages] = $this->createVacancyWithStages();
        $application = $this->createSubmittedApplicationWithSnapshot($user, $vacancy, $stages);

        // Database internal: ACCEPTED, tetapi final_result_published_at NULL
        $application->update([
            'status' => JobApplicationStatus::ACCEPTED,
            'accepted_at' => now(),
            'final_result_published_at' => null,
            'final_result_message' => 'Selamat Anda diterima bekerja.',
        ]);

        // 1. Check Tracking Page
        $trackingResponse = $this->actingAs($user)->get(route('portal.applications.tracking', $application));
        $trackingResponse->assertOk();
        $trackingResponse->assertSee('Menunggu Pengumuman Akhir');
        $trackingResponse->assertDontSee('Diterima');
        $trackingResponse->assertDontSee('Selamat Anda diterima bekerja.');

        // 2. Check Application Show Page
        $showResponse = $this->actingAs($user)->get(route('portal.applications.show', $application));
        $showResponse->assertOk();
        $showResponse->assertSee('Menunggu Pengumuman Akhir');
        $showResponse->assertDontSee('Diterima');

        // 3. Check Application Index Page
        $indexResponse = $this->actingAs($user)->get(route('portal.applications.index'));
        $indexResponse->assertOk();
        $indexResponse->assertSee('Menunggu Pengumuman Akhir');
        $indexResponse->assertDontSee('Diterima');

        // 4. Check Dashboard
        $dashResponse = $this->actingAs($user)->get(route('portal.home'));
        $dashResponse->assertOk();
        $dashResponse->assertSee('Menunggu Pengumuman Akhir');
        $dashResponse->assertDontSee('Diterima');
    }

    public function test_rejected_application_without_published_final_result_shows_waiting_status(): void
    {
        $user = $this->createApplicantUser(true);
        [$period, $vacancy, $stages] = $this->createVacancyWithStages();
        $application = $this->createSubmittedApplicationWithSnapshot($user, $vacancy, $stages);

        // Database internal: REJECTED, tetapi final_result_published_at NULL
        $application->update([
            'status' => JobApplicationStatus::REJECTED,
            'rejected_at' => now(),
            'final_result_published_at' => null,
            'final_result_message' => 'Anda gagal.',
        ]);

        $response = $this->actingAs($user)->get(route('portal.applications.tracking', $application));
        $response->assertOk();
        $response->assertSee('Menunggu Pengumuman Akhir');
        $response->assertDontSee('Anda gagal.');
    }

    public function test_future_final_result_published_at_remains_hidden(): void
    {
        $user = $this->createApplicantUser(true);
        [$period, $vacancy, $stages] = $this->createVacancyWithStages();
        $application = $this->createSubmittedApplicationWithSnapshot($user, $vacancy, $stages);

        // final_result_published_at besok
        $application->update([
            'status' => JobApplicationStatus::ACCEPTED,
            'accepted_at' => now(),
            'final_result_published_at' => now()->addDay(),
            'final_result_message' => 'Selamat bergabung.',
        ]);

        $response = $this->actingAs($user)->get(route('portal.applications.tracking', $application));
        $response->assertOk();
        $response->assertSee('Menunggu Pengumuman Akhir');
        $response->assertDontSee('Selamat bergabung.');
    }

    public function test_published_final_result_reveals_accepted_status_and_banner(): void
    {
        $user = $this->createApplicantUser(true);
        [$period, $vacancy, $stages] = $this->createVacancyWithStages();
        $application = $this->createSubmittedApplicationWithSnapshot($user, $vacancy, $stages);

        // final_result_published_at <= now()
        $application->update([
            'status' => JobApplicationStatus::ACCEPTED,
            'accepted_at' => now()->subDay(),
            'final_result_published_at' => now()->subHour(),
            'final_result_message' => 'Selamat, Anda dinyatakan diterima sebagai Bidan di RSI Fatimah Banyuwangi.',
        ]);

        $response = $this->actingAs($user)->get(route('portal.applications.tracking', $application));

        $response->assertOk();
        $response->assertSee('Diterima');
        $response->assertSee('Selamat, Anda dinyatakan diterima.');
        $response->assertSee('Selamat, Anda dinyatakan diterima sebagai Bidan di RSI Fatimah Banyuwangi.');
    }

    public function test_published_final_result_reveals_rejected_status_and_banner(): void
    {
        $user = $this->createApplicantUser(true);
        [$period, $vacancy, $stages] = $this->createVacancyWithStages();
        $application = $this->createSubmittedApplicationWithSnapshot($user, $vacancy, $stages);

        $application->update([
            'status' => JobApplicationStatus::REJECTED,
            'rejected_at' => now()->subDay(),
            'final_result_published_at' => now()->subHour(),
            'final_result_message' => 'Terima kasih atas partisipasi Anda dalam seleksi penerimaan karyawan.',
        ]);

        $response = $this->actingAs($user)->get(route('portal.applications.tracking', $application));

        $response->assertOk();
        $response->assertSee('Tidak Lolos');
        $response->assertSee('Terima kasih telah mengikuti proses rekrutmen.');
        $response->assertSee('Terima kasih atas partisipasi Anda dalam seleksi penerimaan karyawan.');
    }

    // =========================================================================
    // 3. HISTORICAL SNAPSHOT CONSISTENCY TESTS
    // =========================================================================

    public function test_tracking_uses_snapshot_stages_even_if_master_stages_are_modified_after_submission(): void
    {
        $user = $this->createApplicantUser(true);
        [$period, $vacancy, $stages] = $this->createVacancyWithStages([
            [
                'name' => 'Tahap Awal Berkas Asli',
                'description' => 'Deskripsi lama',
                'sort_order' => 1,
            ],
        ]);
        $application = $this->createSubmittedApplicationWithSnapshot($user, $vacancy, $stages);

        // Setelah pelamar submit, admin mengubah nama tahapan master lowongan
        $stages[0]->update([
            'name' => 'Nama Master Baru Berubah Total',
            'description' => 'Deskripsi master baru',
        ]);

        $response = $this->actingAs($user)->get(route('portal.applications.tracking', $application));

        $response->assertOk();
        // Tracking HARUS tetap membaca nama tahapan dari SNAPSHOT
        $response->assertSee('Tahap Awal Berkas Asli');
        $response->assertDontSee('Nama Master Baru Berubah Total');
    }

    // =========================================================================
    // 4. DASHBOARD & PRIMARY APPLICATION DETERMINISM TESTS
    // =========================================================================

    public function test_guest_cannot_access_portal_dashboard(): void
    {
        $response = $this->get(route('portal.home'));
        $response->assertRedirect(route('login'));
    }

    public function test_inactive_applicant_cannot_access_portal_dashboard(): void
    {
        $user = $this->createApplicantUser(true, ['is_active' => false]);
        $response = $this->actingAs($user)->get(route('portal.home'));
        $response->assertRedirect(route('login'));
    }

    public function test_dashboard_accessible_when_recruitment_is_closed(): void
    {
        $user = $this->createApplicantUser(true);
        // Periode sudah selesai
        $period = RecruitmentPeriod::create([
            'name' => 'Periode Selesai',
            'code' => 'PR-COMPLETED-01',
            'status' => RecruitmentPeriodStatus::COMPLETED,
            'registration_start_at' => now()->subDays(30),
            'registration_end_at' => now()->subDays(10),
        ]);

        $response = $this->actingAs($user)->get(route('portal.home'));
        $response->assertOk();
        $response->assertSee($user->name);
    }

    public function test_dashboard_selects_active_period_application_deterministically(): void
    {
        $user = $this->createApplicantUser(true);

        // Periode 1: Completed / Lama
        $periodClosed = RecruitmentPeriod::create([
            'name' => 'Periode Lama',
            'code' => 'PR-OLD-01',
            'status' => RecruitmentPeriodStatus::COMPLETED,
            'registration_start_at' => now()->subDays(60),
            'registration_end_at' => now()->subDays(30),
        ]);
        $vac1 = JobVacancy::create([
            'recruitment_period_id' => $periodClosed->id,
            'code' => 'VAC-OLD-01',
            'title' => 'Formasi Periode Lama',
            'unit' => 'Unit Lama',
            'employment_type' => 'PKWT',
            'location' => 'RSI Fatimah',
            'quota' => 2,
            'status' => JobVacancyStatus::CLOSED,
            'open_at' => now()->subDays(60),
            'close_at' => now()->subDays(30),
        ]);
        $app1 = JobApplication::create([
            'user_id' => $user->id,
            'recruitment_period_id' => $periodClosed->id,
            'job_vacancy_id' => $vac1->id,
            'status' => JobApplicationStatus::SUBMITTED,
            'application_number' => 'LAM-OLD-000001',
            'created_at' => now()->subDays(40),
        ]);

        // Periode 2: Sedang Seleksi (Active Selection)
        $periodSelection = RecruitmentPeriod::create([
            'name' => 'Periode Aktif Seleksi',
            'code' => 'PR-ACTIVE-02',
            'status' => RecruitmentPeriodStatus::SELECTION,
            'registration_start_at' => now()->subDays(10),
            'registration_end_at' => now()->subDays(1),
        ]);
        $vac2 = JobVacancy::create([
            'recruitment_period_id' => $periodSelection->id,
            'code' => 'VAC-ACT-02',
            'title' => 'Formasi Aktif Seleksi',
            'unit' => 'Unit Aktif',
            'employment_type' => 'PKWT',
            'location' => 'RSI Fatimah',
            'quota' => 2,
            'status' => JobVacancyStatus::CLOSED,
            'open_at' => now()->subDays(10),
            'close_at' => now()->subDays(1),
        ]);
        $app2 = JobApplication::create([
            'user_id' => $user->id,
            'recruitment_period_id' => $periodSelection->id,
            'job_vacancy_id' => $vac2->id,
            'status' => JobApplicationStatus::SUBMITTED,
            'application_number' => 'LAM-ACTIVE-000002',
            'created_at' => now()->subDays(5),
        ]);

        // Dashboard HARUS memilih lamaran pada periode aktif ($app2)
        $response = $this->actingAs($user)->get(route('portal.home'));

        $response->assertOk();
        $response->assertSee('Formasi Aktif Seleksi');
        $response->assertSee('LAM-ACTIVE-000002');
    }

    // =========================================================================
    // 5. AUTHORIZATION & READ-ONLY INTEGRITY TESTS
    // =========================================================================

    public function test_cross_user_cannot_view_tracking_page(): void
    {
        $user1 = $this->createApplicantUser(true);
        $user2 = $this->createApplicantUser(true);
        [$period, $vacancy, $stages] = $this->createVacancyWithStages();
        $app1 = $this->createSubmittedApplicationWithSnapshot($user1, $vacancy, $stages);

        // User 2 mencoba membuka tracking milik User 1
        $response = $this->actingAs($user2)->get(route('portal.applications.tracking', $app1));

        $response->assertForbidden();
    }

    public function test_draft_application_redirects_to_show_when_opening_tracking(): void
    {
        $user = $this->createApplicantUser(true);
        [$period, $vacancy, $stages] = $this->createVacancyWithStages();

        $draftApp = JobApplication::create([
            'user_id' => $user->id,
            'recruitment_period_id' => $vacancy->recruitment_period_id,
            'job_vacancy_id' => $vacancy->id,
            'status' => JobApplicationStatus::DRAFT,
        ]);

        $response = $this->actingAs($user)->get(route('portal.applications.tracking', $draftApp));

        $response->assertRedirect(route('portal.applications.show', $draftApp));
        $response->assertSessionHas('info');
    }

    public function test_tracking_and_dashboard_get_requests_are_strictly_read_only(): void
    {
        $user = $this->createApplicantUser(true);
        [$period, $vacancy, $stages] = $this->createVacancyWithStages();
        $application = $this->createSubmittedApplicationWithSnapshot($user, $vacancy, $stages);

        $history = ApplicationStageHistory::create([
            'job_application_id' => $application->id,
            'vacancy_selection_stage_id' => $stages[0]->id,
            'status' => ApplicationStageStatus::PASSED,
            'started_at' => now()->subDays(2),
            'decided_at' => now()->subDay(),
            'note' => 'Catatan internal panitia yang rahasia',
            'published_at' => now()->subHour(),
            'applicant_message' => 'Selamat, Anda dinyatakan lulus seleksi administrasi.',
        ]);

        $application->update([
            'final_result_published_at' => now()->addDays(5),
            'final_result_message' => 'Hasil akhir masa depan.',
        ]);

        $snapshot = $application->snapshot;

        // Catat state awal model secara komprehensif
        $savedApp = [
            'status' => $application->status,
            'application_number' => $application->application_number,
            'submitted_at' => $application->submitted_at?->toIso8601String(),
            'locked_at' => $application->locked_at?->toIso8601String(),
            'final_result_published_at' => $application->final_result_published_at?->toIso8601String(),
            'final_result_message' => $application->final_result_message,
            'updated_at' => $application->updated_at?->toIso8601String(),
        ];

        $savedHistory = [
            'status' => $history->status,
            'started_at' => $history->started_at?->toIso8601String(),
            'decided_at' => $history->decided_at?->toIso8601String(),
            'note' => $history->note,
            'published_at' => $history->published_at?->toIso8601String(),
            'applicant_message' => $history->applicant_message,
            'updated_at' => $history->updated_at?->toIso8601String(),
        ];

        $savedSnapshot = [
            'recruitment_period_data' => $snapshot->recruitment_period_data,
            'personal_data' => $snapshot->personal_data,
            'vacancy_data' => $snapshot->vacancy_data,
            'captured_at' => $snapshot->captured_at?->toIso8601String(),
            'updated_at' => $snapshot->updated_at?->toIso8601String(),
        ];

        $initialHistoriesCount = ApplicationStageHistory::count();
        $initialAppCount = JobApplication::count();
        $initialSnapshotCount = ApplicationSnapshot::count();

        // Buka seluruh applicant-facing GET routes
        $this->actingAs($user)->get(route('portal.home'))->assertOk();
        $this->actingAs($user)->get(route('portal.applications.tracking', $application))->assertOk();
        $this->actingAs($user)->get(route('portal.applications.show', $application))->assertOk();
        $this->actingAs($user)->get(route('portal.applications.index'))->assertOk();

        // Verifikasi count row tidak bertambah/berkurang
        $this->assertEquals($initialHistoriesCount, ApplicationStageHistory::count());
        $this->assertEquals($initialAppCount, JobApplication::count());
        $this->assertEquals($initialSnapshotCount, ApplicationSnapshot::count());

        // Refresh model dari DB dan assert seluruh atribut vital TIDAK termutasi
        $freshApp = $application->fresh();
        $this->assertEquals($savedApp['status'], $freshApp->status);
        $this->assertEquals($savedApp['application_number'], $freshApp->application_number);
        $this->assertEquals($savedApp['submitted_at'], $freshApp->submitted_at?->toIso8601String());
        $this->assertEquals($savedApp['locked_at'], $freshApp->locked_at?->toIso8601String());
        $this->assertEquals($savedApp['final_result_published_at'], $freshApp->final_result_published_at?->toIso8601String());
        $this->assertEquals($savedApp['final_result_message'], $freshApp->final_result_message);
        $this->assertEquals($savedApp['updated_at'], $freshApp->updated_at?->toIso8601String());

        $freshHistory = $history->fresh();
        $this->assertEquals($savedHistory['status'], $freshHistory->status);
        $this->assertEquals($savedHistory['started_at'], $freshHistory->started_at?->toIso8601String());
        $this->assertEquals($savedHistory['decided_at'], $freshHistory->decided_at?->toIso8601String());
        $this->assertEquals($savedHistory['note'], $freshHistory->note);
        $this->assertEquals($savedHistory['published_at'], $freshHistory->published_at?->toIso8601String());
        $this->assertEquals($savedHistory['applicant_message'], $freshHistory->applicant_message);
        $this->assertEquals($savedHistory['updated_at'], $freshHistory->updated_at?->toIso8601String());

        $freshSnapshot = $snapshot->fresh();
        $this->assertEquals($savedSnapshot['recruitment_period_data'], $freshSnapshot->recruitment_period_data);
        $this->assertEquals($savedSnapshot['personal_data'], $freshSnapshot->personal_data);
        $this->assertEquals($savedSnapshot['vacancy_data'], $freshSnapshot->vacancy_data);
        $this->assertEquals($savedSnapshot['captured_at'], $freshSnapshot->captured_at?->toIso8601String());
        $this->assertEquals($savedSnapshot['updated_at'], $freshSnapshot->updated_at?->toIso8601String());
    }

    // =========================================================================
    // 6. SERVICE OUTPUT WHITELIST (SAFE DTO) TESTS
    // =========================================================================

    public function test_service_output_never_contains_note_or_decided_at(): void
    {
        $user = $this->createApplicantUser(true);
        [$period, $vacancy, $stages] = $this->createVacancyWithStages();
        $application = $this->createSubmittedApplicationWithSnapshot($user, $vacancy, $stages);

        ApplicationStageHistory::create([
            'job_application_id' => $application->id,
            'vacancy_selection_stage_id' => $stages[0]->id,
            'status' => ApplicationStageStatus::PASSED,
            'started_at' => now()->subDay(),
            'decided_at' => now(),
            'note' => 'INTERNAL SECRET NOTE',
            'published_at' => now()->subHour(),
            'applicant_message' => 'Pesan untuk pelamar',
        ]);

        $service = app(SelectionTrackingService::class);
        $trackingResult = $service->tracking($application);

        $this->assertNotEmpty($trackingResult['stages']);
        $firstStage = $trackingResult['stages'][0];

        // Verifikasi ketat whitelist
        $this->assertArrayNotHasKey('note', $firstStage);
        $this->assertArrayNotHasKey('decided_at', $firstStage);
        $this->assertArrayNotHasKey('history', $firstStage);
        $this->assertArrayHasKey('public_state', $firstStage);
        $this->assertArrayHasKey('applicant_message', $firstStage);
        $this->assertEquals('Pesan untuk pelamar', $firstStage['applicant_message']);
    }

    // =========================================================================
    // 7. MICRO-AUDIT READ-ONLY & TAMPER SECURITY TESTS
    // =========================================================================

    public function test_applicant_cannot_tamper_internal_selection_fields_via_requests(): void
    {
        $user = $this->createApplicantUser(true);
        [$period, $vacancy, $stages] = $this->createVacancyWithStages();

        $application = JobApplication::create([
            'user_id' => $user->id,
            'recruitment_period_id' => $vacancy->recruitment_period_id,
            'job_vacancy_id' => $vacancy->id,
            'status' => JobApplicationStatus::DRAFT,
        ]);

        $tamperPayload = [
            'declaration' => '1',
            'status' => 'accepted',
            'published_at' => now()->toIso8601String(),
            'applicant_message' => 'Injected Message',
            'final_result_published_at' => now()->toIso8601String(),
            'final_result_message' => 'Injected Final Message',
            'note' => 'Injected Secret Note',
            'decided_at' => now()->toIso8601String(),
        ];

        // 1. Coba tamper via profile personal update
        $this->actingAs($user)->patch(route('profile.personal.update'), array_merge([
            'name' => 'Nama Baru',
            'nickname' => 'Budi',
            'birth_place' => 'Banyuwangi',
            'birth_date' => '1995-05-15',
            'gender' => 'Laki-laki',
        ], $tamperPayload));

        // 2. Coba tamper via profile contact update
        $this->actingAs($user)->patch(route('profile.contact.update'), array_merge([
            'email' => $user->email,
            'phone' => $user->phone,
            'address_ktp' => 'Alamat KTP Baru',
            'same_as_ktp' => true,
        ], $tamperPayload));

        // Pastikan application tidak termutasi oleh injected values
        $freshApp = $application->fresh();
        $this->assertEquals(JobApplicationStatus::DRAFT, $freshApp->status);
        $this->assertNull($freshApp->final_result_published_at);
        $this->assertNull($freshApp->final_result_message);
        $this->assertEquals(0, ApplicationStageHistory::count());
    }

    public function test_applicant_and_final_result_messages_are_escaped_in_blade(): void
    {
        $user = $this->createApplicantUser(true);
        [$period, $vacancy, $stages] = $this->createVacancyWithStages();
        $application = $this->createSubmittedApplicationWithSnapshot($user, $vacancy, $stages);

        $xssStagePayload = '<script>alert("stage-xss")</script>';
        $xssFinalPayload = '<script>alert("final-xss")</script>';
        $secretNote = 'CONFIDENTIAL_PANITIA_NOTE_DO_NOT_LEAK';

        ApplicationStageHistory::create([
            'job_application_id' => $application->id,
            'vacancy_selection_stage_id' => $stages[0]->id,
            'status' => ApplicationStageStatus::PASSED,
            'started_at' => now()->subDay(),
            'decided_at' => now()->subHour(),
            'note' => $secretNote,
            'published_at' => now()->subMinutes(10),
            'applicant_message' => $xssStagePayload,
        ]);

        $application->update([
            'status' => JobApplicationStatus::ACCEPTED,
            'final_result_published_at' => now()->subMinutes(10),
            'final_result_message' => $xssFinalPayload,
        ]);

        // Verifikasi pada halaman tracking
        $trackingResponse = $this->actingAs($user)->get(route('portal.applications.tracking', $application));
        $trackingResponse->assertOk();
        // Raw tag script TIDAK boleh muncul sebagai executable HTML
        $trackingResponse->assertDontSee($xssStagePayload, false);
        $trackingResponse->assertDontSee($xssFinalPayload, false);
        // HTML-escaped string aman HARUS muncul
        $trackingResponse->assertSee(e($xssStagePayload), false);
        $trackingResponse->assertSee(e($xssFinalPayload), false);
        // Note panitia TIDAK pernah tampil
        $trackingResponse->assertDontSee($secretNote);

        // Verifikasi pada dashboard home
        $homeResponse = $this->actingAs($user)->get(route('portal.home'));
        $homeResponse->assertOk();
        $homeResponse->assertDontSee($xssFinalPayload, false);
        $homeResponse->assertSee(e($xssFinalPayload), false);
        $homeResponse->assertDontSee($secretNote);
    }

    public function test_publication_boundary_exact_now_vs_future(): void
    {
        $user = $this->createApplicantUser(true);
        [$period, $vacancy, $stages] = $this->createVacancyWithStages();
        $application = $this->createSubmittedApplicationWithSnapshot($user, $vacancy, $stages);

        $frozenTime = Carbon::create(2026, 9, 12, 10, 0, 0, 'Asia/Jakarta');
        Carbon::setTestNow($frozenTime);

        try {
            $history = ApplicationStageHistory::create([
                'job_application_id' => $application->id,
                'vacancy_selection_stage_id' => $stages[0]->id,
                'status' => ApplicationStageStatus::PASSED,
                'started_at' => $frozenTime->copy()->subDay(),
                'decided_at' => $frozenTime->copy()->subHour(),
                'published_at' => $frozenTime->copy(), // Persis NOW
                'applicant_message' => 'Lulus tepat waktu',
            ]);

            $service = app(SelectionTrackingService::class);

            // 1. Boundary: published_at = now() -> DILULUSKAN (Published)
            $this->assertTrue($service->isStagePublished($history));
            $tracking = $service->tracking($application);
            $this->assertEquals('passed', $tracking['stages'][0]['public_state']);
            $this->assertEquals('Lulus', $tracking['stages'][0]['label']);

            // 2. Boundary: published_at = now() + 1 detik -> BELUM DIPUBLIKASI
            $history->update(['published_at' => $frozenTime->copy()->addSecond()]);
            $this->assertFalse($service->isStagePublished($history->fresh()));
            $tracking2 = $service->tracking($application);
            $this->assertEquals('waiting', $tracking2['stages'][0]['public_state']);
            $this->assertEquals('Menunggu', $tracking2['stages'][0]['label']);

            // 3. Boundary: final_result_published_at = now() -> PUBLISHED
            $application->update([
                'status' => JobApplicationStatus::ACCEPTED,
                'final_result_published_at' => $frozenTime->copy(), // Persis NOW
                'final_result_message' => 'Selamat diterima',
            ]);
            $this->assertTrue($service->isFinalResultPublished($application->fresh()));
            $this->assertNotNull($service->finalResult($application->fresh()));
            $this->assertEquals('Diterima', $service->publicApplicationStatus($application->fresh())['label']);

            // 4. Boundary: final_result_published_at = now() + 1 detik -> BELUM PUBLISHED
            $application->update([
                'final_result_published_at' => $frozenTime->copy()->addSecond(),
            ]);
            $this->assertFalse($service->isFinalResultPublished($application->fresh()));
            $this->assertNull($service->finalResult($application->fresh()));
            $this->assertEquals('Menunggu Pengumuman Akhir', $service->publicApplicationStatus($application->fresh())['label']);
        } finally {
            Carbon::setTestNow(); // Pastikan waktu di-reset
        }
    }

    public function test_submitted_application_without_snapshot_shows_integrity_warning_and_does_not_create_snapshot(): void
    {
        $user = $this->createApplicantUser(true);
        [$period, $vacancy, $stages] = $this->createVacancyWithStages();

        // Buat lamaran SUBMITTED tanpa snapshot
        $application = JobApplication::create([
            'user_id' => $user->id,
            'recruitment_period_id' => $vacancy->recruitment_period_id,
            'job_vacancy_id' => $vacancy->id,
            'status' => JobApplicationStatus::SUBMITTED,
            'application_number' => 'LAM-TEST-NOSNAPSHOT',
            'submitted_at' => now()->subDay(),
            'locked_at' => now()->subDay(),
        ]);

        $this->assertEquals(0, ApplicationSnapshot::where('job_application_id', $application->id)->count());

        // 1. Dashboard: Tidak crash dan tidak membuat snapshot
        $this->actingAs($user)->get(route('portal.home'))->assertOk();
        $this->assertEquals(0, ApplicationSnapshot::where('job_application_id', $application->id)->count());

        // 2. Index: Aman
        $this->actingAs($user)->get(route('portal.applications.index'))->assertOk();
        $this->assertEquals(0, ApplicationSnapshot::where('job_application_id', $application->id)->count());

        // 3. Show: Menampilkan warning, tidak fallback ke live profile, dan tidak membuat snapshot
        $showResponse = $this->actingAs($user)->get(route('portal.applications.show', $application));
        $showResponse->assertOk();
        $showResponse->assertSee('Peringatan Integritas Data');
        $showResponse->assertSee('salinan data historis resmi (Snapshot) tidak ditemukan pada sistem');
        $showResponse->assertDontSee('Data Pelamar Terhubung');
        $this->assertEquals(0, ApplicationSnapshot::where('job_application_id', $application->id)->count());

        // 4. Tracking: Menampilkan warning dan tidak membuat snapshot
        $trackingResponse = $this->actingAs($user)->get(route('portal.applications.tracking', $application));
        $trackingResponse->assertOk();
        $trackingResponse->assertSee('Data konfigurasi tahapan seleksi belum dapat dimuat untuk lamaran ini');
        $this->assertEquals(0, ApplicationSnapshot::where('job_application_id', $application->id)->count());
    }
}
