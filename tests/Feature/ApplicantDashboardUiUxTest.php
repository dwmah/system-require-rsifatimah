<?php

namespace Tests\Feature;

use App\Enums\ApplicationStageStatus;
use App\Enums\JobApplicationStatus;
use App\Enums\JobVacancyStatus;
use App\Enums\RecruitmentPeriodStatus;
use App\Models\ApplicantEducation;
use App\Models\ApplicantProfile;
use App\Models\ApplicationDocument;
use App\Models\ApplicationSnapshot;
use App\Models\ApplicationStageHistory;
use App\Models\JobApplication;
use App\Models\JobVacancy;
use App\Models\RecruitmentPeriod;
use App\Models\User;
use App\Models\VacancyDocumentRequirement;
use App\Models\VacancySelectionStage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ApplicantDashboardUiUxTest extends TestCase
{
    use RefreshDatabase;

    protected function createApplicantUser(bool $completeProfile = true, array $userAttributes = []): User
    {
        static $sequence = 1;
        $seq = $sequence++;

        $user = User::create(array_merge([
            'name' => 'Pelamar UIUX ' . $seq,
            'email' => "pelamar.uiux{$seq}@example.com",
            'phone' => sprintf('62813111%05d', $seq),
            'registration_number' => sprintf('PLM-2026-%06d', $seq),
            'password' => 'Password123',
            'is_active' => true,
        ], $userAttributes));

        if ($completeProfile) {
            $profile = ApplicantProfile::create([
                'user_id' => $user->id,
                'nik' => sprintf('3510223344%06d', $seq),
                'birth_place' => 'Banyuwangi',
                'birth_date' => '1996-05-20',
                'gender' => 'Laki-laki',
                'religion' => 'Islam',
                'address_ktp' => 'Jl. Basuki Rahmat No. 10 Banyuwangi',
                'address_domicile' => 'Jl. Basuki Rahmat No. 10 Banyuwangi',
                'same_as_ktp' => true,
                'photo_path' => 'profile-photos/test.jpg',
            ]);

            ApplicantEducation::create([
                'applicant_profile_id' => $profile->id,
                'education_level' => 'S1',
                'institution_name' => 'Universitas Airlangga',
                'study_program' => 'Keperawatan',
                'accreditation' => 'A',
                'gpa' => '3.75',
                'graduation_year' => 2021,
            ]);
        }

        return $user->fresh(['applicantProfile', 'applicantProfile.educations']);
    }

    protected function createVacancyWithStagesAndRequirements(array $stagesConfig = [], array $reqsConfig = []): array
    {
        static $vacSeq = 1;
        $seq = $vacSeq++;

        $period = RecruitmentPeriod::create([
            'name' => 'Rekrutmen Tenaga Medis RSI Fatimah ' . $seq,
            'code' => 'PR-UIUX-2026-' . sprintf('%03d', $seq),
            'status' => RecruitmentPeriodStatus::OPEN,
            'registration_start_at' => now()->subDays(5),
            'registration_end_at' => now()->addDays(15),
        ]);

        $vacancy = JobVacancy::create([
            'recruitment_period_id' => $period->id,
            'code' => 'NRS-IGD-' . sprintf('%03d', $seq),
            'title' => 'Perawat Gawat Darurat ' . $seq,
            'unit' => 'Instalasi Gawat Darurat',
            'employment_type' => 'PKWT',
            'location' => 'RSI Fatimah Banyuwangi',
            'quota' => 4,
            'status' => JobVacancyStatus::OPEN,
            'open_at' => now()->subDays(5),
            'close_at' => now()->addDays(15),
        ]);

        if (empty($stagesConfig)) {
            $stagesConfig = [
                ['name' => 'Seleksi Berkas Administrasi', 'sort_order' => 1, 'description' => 'Verifikasi keabsahan dokumen'],
                ['name' => 'Tes Potensi Akademik & CBT', 'sort_order' => 2, 'description' => 'Uji kompetensi dasar dan teknis medis'],
                ['name' => 'Wawancara Klinis & Direksi', 'sort_order' => 3, 'description' => 'Wawancara komite keperawatan & manajemen'],
            ];
        }

        $stages = [];
        foreach ($stagesConfig as $sc) {
            $stages[] = VacancySelectionStage::create(array_merge([
                'job_vacancy_id' => $vacancy->id,
            ], $sc));
        }

        if (empty($reqsConfig)) {
            $reqsConfig = [
                ['name' => 'Surat Lamaran & CV', 'is_required' => true, 'allowed_mimes' => 'application/pdf', 'max_size_kb' => 2048],
                ['name' => 'Ijazah & Transkrip S1', 'is_required' => true, 'allowed_mimes' => 'application/pdf', 'max_size_kb' => 3072],
                ['name' => 'Sertifikat BTCLS', 'is_required' => false, 'allowed_mimes' => 'application/pdf', 'max_size_kb' => 2048],
            ];
        }

        $requirements = [];
        foreach ($reqsConfig as $rc) {
            $requirements[] = VacancyDocumentRequirement::create(array_merge([
                'job_vacancy_id' => $vacancy->id,
                'description' => 'Unggah dokumen sesuai ketentuan',
                'sort_order' => 1,
            ], $rc));
        }

        return [$period, $vacancy, $stages, $requirements];
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
    // 1. PROFILE COMPLETENESS & CTA DISPLAY TESTS
    // =========================================================================

    public function test_profile_incomplete_displays_lengkapi_profil_cta(): void
    {
        // Pelamar belum melengkapi profil dasar (< 100%)
        $user = $this->createApplicantUser(false);

        $response = $this->actingAs($user)->get(route('portal.home'));

        $response->assertOk();
        $response->assertSee('Kelengkapan Profil Dasar');
        $response->assertSee('Lengkapi Profil Sekarang');
        $response->assertDontSee('Profil Anda Sudah Lengkap');
    }

    public function test_profile_complete_displays_lihat_edit_profil_cta(): void
    {
        // Pelamar sudah melengkapi seluruh profil dasar (>= 100%)
        $user = $this->createApplicantUser(true);

        $response = $this->actingAs($user)->get(route('portal.home'));

        $response->assertOk();
        $response->assertSee('Profil Anda Sudah Lengkap');
        $response->assertSee('100% Selesai');
        $response->assertSee('Lihat / Edit Profil');
        $response->assertDontSee('Lengkapi Profil Sekarang');
    }

    // =========================================================================
    // 2. DRAFT APPLICATION & DOCUMENT READINESS TESTS
    // =========================================================================

    public function test_draft_application_displays_lamaran_belum_lengkap_and_hides_unissued_number(): void
    {
        $user = $this->createApplicantUser(true);
        [$period, $vacancy, $stages, $requirements] = $this->createVacancyWithStagesAndRequirements();

        $application = JobApplication::create([
            'user_id' => $user->id,
            'recruitment_period_id' => $period->id,
            'job_vacancy_id' => $vacancy->id,
            'status' => JobApplicationStatus::DRAFT,
            'application_number' => null,
        ]);

        $response = $this->actingAs($user)->get(route('portal.home'));

        $response->assertOk();
        $response->assertSee('Draft');
        $response->assertSee('Status Lamaran Aktif');
        // Nomor lamaran yang belum diterbitkan harus disembunyikan saat draft
        $response->assertDontSee('Belum diterbitkan');
        $response->assertDontSee('Draft (Belum Diterbitkan)');
    }

    public function test_draft_application_incomplete_docs_shows_upload_cta(): void
    {
        $user = $this->createApplicantUser(true);
        [$period, $vacancy, $stages, $requirements] = $this->createVacancyWithStagesAndRequirements();

        // Lamaran draft tanpa dokumen sama sekali (masih kurang 2 dokumen wajib)
        $application = JobApplication::create([
            'user_id' => $user->id,
            'recruitment_period_id' => $period->id,
            'job_vacancy_id' => $vacancy->id,
            'status' => JobApplicationStatus::DRAFT,
        ]);

        $response = $this->actingAs($user)->get(route('portal.home'));

        $response->assertOk();
        $response->assertSee('dokumen yang belum dilengkapi sebelum lamaran dapat dikirim');
        $response->assertSee('Lengkapi Berkas');
        $response->assertSee(route('portal.applications.documents.index', $application));
        $response->assertDontSee('Review &amp; Kirim Lamaran', false);
    }

    public function test_draft_application_complete_docs_shows_review_cta(): void
    {
        $user = $this->createApplicantUser(true);
        [$period, $vacancy, $stages, $requirements] = $this->createVacancyWithStagesAndRequirements();

        $application = JobApplication::create([
            'user_id' => $user->id,
            'recruitment_period_id' => $period->id,
            'job_vacancy_id' => $vacancy->id,
            'status' => JobApplicationStatus::DRAFT,
        ]);

        // Unggah semua dokumen wajib (is_required = true)
        foreach ($requirements as $req) {
            if ($req->is_required) {
                ApplicationDocument::create([
                    'job_application_id' => $application->id,
                    'vacancy_document_requirement_id' => $req->id,
                    'original_name' => 'dokumen_' . $req->id . '.pdf',
                    'stored_path' => 'documents/test_' . $req->id . '.pdf',
                    'mime_type' => 'application/pdf',
                    'file_size' => 102400,
                    'verification_status' => 'pending',
                ]);
            }
        }

        $response = $this->actingAs($user)->get(route('portal.home'));

        $response->assertOk();
        $response->assertSee('Siap Dikirim');
        $response->assertSee('Semua dokumen persyaratan telah lengkap. Periksa kembali data Anda sebelum mengirim lamaran.');
        $response->assertSee('Review &amp; Kirim Lamaran', false);
        $response->assertSee(route('portal.applications.review', $application));
        $response->assertDontSee('Lengkapi Berkas');
    }

    // =========================================================================
    // 3. PUBLIC STATUS ANTI-LEAK TESTS
    // =========================================================================

    public function test_internal_accepted_with_unpublished_final_result_shows_waiting_status(): void
    {
        $user = $this->createApplicantUser(true);
        [$period, $vacancy, $stages, $requirements] = $this->createVacancyWithStagesAndRequirements();
        $application = $this->createSubmittedApplicationWithSnapshot($user, $vacancy, $stages);

        // Internal di-set ACCEPTED oleh panitia, tetapi final_result_published_at masih NULL
        $application->update([
            'status' => JobApplicationStatus::ACCEPTED,
            'final_result_published_at' => null,
            'final_result_message' => 'Selamat Anda Dinyatakan Diterima Resmi Menjadi Karyawan RSI Fatimah.',
        ]);

        $response = $this->actingAs($user)->get(route('portal.home'));

        $response->assertOk();
        // ANTI-LEAK: TIDAK BOLEH menampilkan 'Diterima' atau pesan hasil akhir sebelum dipublikasikan
        $response->assertDontSee('Selamat, Anda dinyatakan diterima.');
        $response->assertDontSee('Selamat Anda Dinyatakan Diterima Resmi Menjadi Karyawan RSI Fatimah.');
        // Status publik aman harus tetap 'Menunggu Pengumuman Akhir'
        $response->assertSee('Menunggu Pengumuman Akhir');
        $response->assertSee('Lihat Progres Seleksi');
    }

    public function test_internal_rejected_with_unpublished_final_result_shows_waiting_status(): void
    {
        $user = $this->createApplicantUser(true);
        [$period, $vacancy, $stages, $requirements] = $this->createVacancyWithStagesAndRequirements();
        $application = $this->createSubmittedApplicationWithSnapshot($user, $vacancy, $stages);

        // Internal di-set REJECTED oleh panitia, tetapi final_result_published_at masih NULL
        $application->update([
            'status' => JobApplicationStatus::REJECTED,
            'final_result_published_at' => null,
            'final_result_message' => 'Mohon Maaf, kualifikasi Anda belum memenuhi kualifikasi formasi.',
        ]);

        $response = $this->actingAs($user)->get(route('portal.home'));

        $response->assertOk();
        // ANTI-LEAK: TIDAK BOLEH menampilkan 'Tidak Lolos' atau pesan penolakan sebelum dipublikasikan
        $response->assertDontSee('Tidak Lolos');
        $response->assertDontSee('MOHON MAAF, ANDA BELUM LOLOS');
        $response->assertDontSee('Mohon Maaf, kualifikasi Anda belum memenuhi');
        // Status publik aman harus tetap 'Menunggu Pengumuman Akhir'
        $response->assertSee('Menunggu Pengumuman Akhir');
        $response->assertSee('Lihat Progres Seleksi');
    }

    // =========================================================================
    // 4. DYNAMIC TIMELINE & ISOLATION TESTS
    // =========================================================================

    public function test_submitted_timeline_uses_snapshot_selection_stages_even_if_vacancy_stages_change(): void
    {
        $user = $this->createApplicantUser(true);
        [$period, $vacancy, $stages, $requirements] = $this->createVacancyWithStagesAndRequirements([
            ['name' => 'Tahap Snapshot Khusus 1', 'sort_order' => 1, 'description' => 'Deskripsi Snapshot 1'],
            ['name' => 'Tahap Snapshot Khusus 2', 'sort_order' => 2, 'description' => 'Deskripsi Snapshot 2'],
            ['name' => 'Tahap Snapshot Khusus 3', 'sort_order' => 3, 'description' => 'Deskripsi Snapshot 3'],
        ]);

        $application = $this->createSubmittedApplicationWithSnapshot($user, $vacancy, $stages);

        // Ubah atau hapus tahapan live pada master vacancy
        VacancySelectionStage::where('job_vacancy_id', $vacancy->id)->delete();
        VacancySelectionStage::create([
            'job_vacancy_id' => $vacancy->id,
            'name' => 'Tahapan Baru yang Tidak Boleh Tampil di Lamaran Lama',
            'sort_order' => 1,
        ]);

        $response = $this->actingAs($user)->get(route('portal.home'));

        $response->assertOk();
        // Dashboard WAJIB menampilkan tahapan dari ApplicationSnapshot yang dibekukan
        $response->assertSee('Tahap Snapshot Khusus 1');
        $response->assertSee('Tahap Snapshot Khusus 2');
        $response->assertSee('Tahap Snapshot Khusus 3');
        $response->assertDontSee('Tahapan Baru yang Tidak Boleh Tampil di Lamaran Lama');
    }

    public function test_unpublished_selection_stage_and_internal_notes_do_not_leak(): void
    {
        $user = $this->createApplicantUser(true);
        [$period, $vacancy, $stages, $requirements] = $this->createVacancyWithStagesAndRequirements();
        $application = $this->createSubmittedApplicationWithSnapshot($user, $vacancy, $stages);

        // Evaluator mencatat evaluasi internal dengan status FAILED dan catatan rahasia
        ApplicationStageHistory::create([
            'job_application_id' => $application->id,
            'vacancy_selection_stage_id' => $stages[0]->id,
            'status' => ApplicationStageStatus::FAILED,
            'started_at' => now()->subDay(),
            'decided_at' => now(),
            'note' => 'CATATAN RAHASIA EVALUATOR: Peserta tidak memenuhi kualifikasi akreditasi.',
            'published_at' => null, // Belum dipublikasikan
            'applicant_message' => 'Pesan rahasia panitia sebelum publish.',
        ]);

        $response = $this->actingAs($user)->get(route('portal.home'));

        $response->assertOk();
        // Data internal tidak boleh bocor sama sekali ke view dashboard
        $response->assertDontSee('CATATAN RAHASIA EVALUATOR');
        $response->assertDontSee('Pesan rahasia panitia sebelum publish.');
        $response->assertDontSee('Tidak Lolos');
        // Label stage harus berstatus aman 'Menunggu'
        $response->assertSee('Menunggu');
    }

    public function test_dashboard_without_application_loads_cleanly(): void
    {
        $user = $this->createApplicantUser(true);

        // Buat periode rekrutmen aktif terbuka
        RecruitmentPeriod::create([
            'name' => 'Rekrutmen Terbuka Periode 1',
            'code' => 'PR-OPEN-001',
            'status' => RecruitmentPeriodStatus::OPEN,
            'registration_start_at' => now()->subDays(2),
            'registration_end_at' => now()->addDays(10),
        ]);

        $response = $this->actingAs($user)->get(route('portal.home'));

        $response->assertOk();
        $response->assertSee('Anda Belum Memilih Posisi Formasi');
        $response->assertSee('Pilih Formasi Lowongan');
        $response->assertSee('Informasi tahapan seleksi akan ditampilkan setelah Anda memilih formasi lowongan.');
    }

    // =========================================================================
    // 5. PURE READ ZERO SIDE-EFFECT VERIFICATION (DEEP ATTRIBUTE ASSERTION)
    // =========================================================================

    public function test_get_dashboard_is_pure_read_without_side_effects(): void
    {
        $user = $this->createApplicantUser(true);
        [$period, $vacancy, $stages, $requirements] = $this->createVacancyWithStagesAndRequirements();
        $application = $this->createSubmittedApplicationWithSnapshot($user, $vacancy, $stages);

        // Buat ApplicationDocument
        $document = ApplicationDocument::create([
            'job_application_id' => $application->id,
            'vacancy_document_requirement_id' => $requirements[0]->id,
            'original_name' => 'ijazah.pdf',
            'stored_path' => 'documents/ijazah_test.pdf',
            'mime_type' => 'application/pdf',
            'file_size' => 204800,
            'verification_status' => 'pending',
            'verification_note' => 'Catatan verifikator dokumen awal.',
        ]);

        // Buat ApplicationStageHistory
        $stageHistory = ApplicationStageHistory::create([
            'job_application_id' => $application->id,
            'vacancy_selection_stage_id' => $stages[0]->id,
            'status' => ApplicationStageStatus::IN_PROGRESS,
            'started_at' => now()->subDay(),
            'decided_at' => now()->subHours(2),
            'note' => 'Catatan evaluasi internal.',
            'published_at' => null,
            'applicant_message' => 'Pesan pengumuman tahap.',
        ]);

        $snapshot = $application->snapshot;

        // 1. Simpan State Relevan Sebelum GET /portal
        $beforeCounts = [
            'users' => User::count(),
            'applications' => JobApplication::count(),
            'documents' => ApplicationDocument::count(),
            'stage_histories' => ApplicationStageHistory::count(),
            'snapshots' => ApplicationSnapshot::count(),
        ];

        $beforeApp = [
            'status' => $application->status,
            'application_number' => $application->application_number,
            'submitted_at' => $application->submitted_at?->toIso8601String(),
            'locked_at' => $application->locked_at?->toIso8601String(),
            'accepted_at' => $application->accepted_at?->toIso8601String(),
            'rejected_at' => $application->rejected_at?->toIso8601String(),
            'hired_at' => $application->hired_at?->toIso8601String(),
            'final_result_published_at' => $application->final_result_published_at?->toIso8601String(),
            'final_result_message' => $application->final_result_message,
            'updated_at' => $application->updated_at?->toIso8601String(),
        ];

        $beforeHistory = [
            'status' => $stageHistory->status,
            'published_at' => $stageHistory->published_at?->toIso8601String(),
            'applicant_message' => $stageHistory->applicant_message,
            'note' => $stageHistory->note,
            'decided_at' => $stageHistory->decided_at?->toIso8601String(),
            'updated_at' => $stageHistory->updated_at?->toIso8601String(),
        ];

        $beforeSnapshot = [
            'payload' => $snapshot->toArray(),
            'captured_at' => $snapshot->captured_at?->toIso8601String(),
            'updated_at' => $snapshot->updated_at?->toIso8601String(),
        ];

        $beforeDocument = [
            'stored_path' => $document->stored_path,
            'verification_status' => $document->verification_status,
            'verification_note' => $document->verification_note,
            'updated_at' => $document->updated_at?->toIso8601String(),
        ];

        // 2. Eksekusi GET /portal
        $response = $this->actingAs($user)->get(route('portal.home'));
        $response->assertOk();

        // 3. Requery / Refresh Seluruh Model dari Database
        $application->refresh();
        $stageHistory->refresh();
        $snapshot->refresh();
        $document->refresh();

        // 4. Assert Record Count Identik (Tidak bertambah / berkurang)
        $this->assertSame($beforeCounts['users'], User::count(), 'User count must not change');
        $this->assertSame($beforeCounts['applications'], JobApplication::count(), 'JobApplication count must not change');
        $this->assertSame($beforeCounts['documents'], ApplicationDocument::count(), 'ApplicationDocument count must not change');
        $this->assertSame($beforeCounts['stage_histories'], ApplicationStageHistory::count(), 'ApplicationStageHistory count must not change');
        $this->assertSame($beforeCounts['snapshots'], ApplicationSnapshot::count(), 'ApplicationSnapshot count must not change');

        // 5. Assert Field JobApplication Identik Persis
        $this->assertSame($beforeApp['status'], $application->status, 'Application status mutated!');
        $this->assertSame($beforeApp['application_number'], $application->application_number, 'Application number mutated!');
        $this->assertSame($beforeApp['submitted_at'], $application->submitted_at?->toIso8601String(), 'Application submitted_at mutated!');
        $this->assertSame($beforeApp['locked_at'], $application->locked_at?->toIso8601String(), 'Application locked_at mutated!');
        $this->assertSame($beforeApp['accepted_at'], $application->accepted_at?->toIso8601String(), 'Application accepted_at mutated!');
        $this->assertSame($beforeApp['rejected_at'], $application->rejected_at?->toIso8601String(), 'Application rejected_at mutated!');
        $this->assertSame($beforeApp['hired_at'], $application->hired_at?->toIso8601String(), 'Application hired_at mutated!');
        $this->assertSame($beforeApp['final_result_published_at'], $application->final_result_published_at?->toIso8601String(), 'Application final_result_published_at mutated!');
        $this->assertSame($beforeApp['final_result_message'], $application->final_result_message, 'Application final_result_message mutated!');
        $this->assertSame($beforeApp['updated_at'], $application->updated_at?->toIso8601String(), 'Application updated_at mutated!');

        // 6. Assert Field ApplicationStageHistory Identik Persis
        $this->assertSame($beforeHistory['status'], $stageHistory->status, 'Stage history status mutated!');
        $this->assertSame($beforeHistory['published_at'], $stageHistory->published_at?->toIso8601String(), 'Stage history published_at mutated!');
        $this->assertSame($beforeHistory['applicant_message'], $stageHistory->applicant_message, 'Stage history applicant_message mutated!');
        $this->assertSame($beforeHistory['note'], $stageHistory->note, 'Stage history note mutated!');
        $this->assertSame($beforeHistory['decided_at'], $stageHistory->decided_at?->toIso8601String(), 'Stage history decided_at mutated!');
        $this->assertSame($beforeHistory['updated_at'], $stageHistory->updated_at?->toIso8601String(), 'Stage history updated_at mutated!');

        // 7. Assert Field ApplicationSnapshot Identik Persis
        $this->assertSame($beforeSnapshot['captured_at'], $snapshot->captured_at?->toIso8601String(), 'Snapshot captured_at mutated!');
        $this->assertSame($beforeSnapshot['updated_at'], $snapshot->updated_at?->toIso8601String(), 'Snapshot updated_at mutated!');
        $this->assertEquals($beforeSnapshot['payload']['vacancy_data'], $snapshot->vacancy_data, 'Snapshot vacancy_data mutated!');
        $this->assertEquals($beforeSnapshot['payload']['personal_data'], $snapshot->personal_data, 'Snapshot personal_data mutated!');

        // 8. Assert Field ApplicationDocument Identik Persis
        $this->assertSame($beforeDocument['stored_path'], $document->stored_path, 'Document stored_path mutated!');
        $this->assertSame($beforeDocument['verification_status'], $document->verification_status, 'Document verification_status mutated!');
        $this->assertSame($beforeDocument['verification_note'], $document->verification_note, 'Document verification_note mutated!');
        $this->assertSame($beforeDocument['updated_at'], $document->updated_at?->toIso8601String(), 'Document updated_at mutated!');
    }

    public function test_active_draft_application_card_renders_clean_simplified_layout(): void
    {
        $user = $this->createApplicantUser(true);
        $period = RecruitmentPeriod::create([
            'name' => 'Rekrutmen SDM RSI Fatimah 2026',
            'code' => 'PR-UIUX-2026-IT',
            'status' => RecruitmentPeriodStatus::OPEN,
            'registration_start_at' => now()->subDays(5),
            'registration_end_at' => now()->addDays(15),
        ]);

        $vacancy = JobVacancy::create([
            'recruitment_period_id' => $period->id,
            'title' => 'Staff IT',
            'code' => 'POS-2026-IT-01',
            'unit' => 'Teknologi Informasi',
            'employment_type' => 'PKWT',
            'quota' => 2,
            'location' => 'Banyuwangi',
            'status' => JobVacancyStatus::OPEN,
            'open_at' => now()->subDays(5),
            'close_at' => now()->addDays(15),
        ]);

        $req1 = VacancyDocumentRequirement::create([
            'job_vacancy_id' => $vacancy->id,
            'name' => 'KTP',
            'allowed_mimes' => 'application/pdf',
            'max_size_kb' => 2048,
            'is_required' => true,
        ]);
        $req2 = VacancyDocumentRequirement::create([
            'job_vacancy_id' => $vacancy->id,
            'name' => 'Ijazah & Transkrip',
            'allowed_mimes' => 'application/pdf',
            'max_size_kb' => 2048,
            'is_required' => true,
        ]);

        $application = JobApplication::create([
            'user_id' => $user->id,
            'recruitment_period_id' => $period->id,
            'job_vacancy_id' => $vacancy->id,
            'status' => JobApplicationStatus::DRAFT,
        ]);

        $response = $this->actingAs($user)->get(route('portal.home'));

        $response->assertOk();
        $response->assertSee('Status Lamaran Aktif');
        $response->assertSee('POS-2026-IT-01');
        $response->assertSee('Staff IT');
        $response->assertSee('Kelengkapan Berkas:');
        $response->assertSee('0 dari 2 dokumen');
        $response->assertSee('Lengkapi Berkas');
        $response->assertSee('Ingin Melamar Posisi Lain?');
        $response->assertSee('Batalkan Lamaran');
        $response->assertSeeText('Batalkan Lamaran & Hapus Akun?');
    }
}
