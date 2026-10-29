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
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class StageResultUploadTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected User $applicant;

    protected JobVacancy $vacancy;

    protected JobApplication $application;

    protected VacancySelectionStage $stage1;

    protected VacancySelectionStage $stage2;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('local');

        $this->admin = User::factory()->create([
            'role' => User::ROLE_ADMIN,
            'is_active' => true,
        ]);

        $this->applicant = User::create([
            'name' => 'Kandidat Ujian',
            'email' => 'kandidat@example.com',
            'phone' => '6281234567890',
            'registration_number' => 'REG-TEST-001',
            'password' => 'Password123',
            'role' => User::ROLE_APPLICANT,
            'is_active' => true,
        ]);

        $profile = ApplicantProfile::create([
            'user_id' => $this->applicant->id,
            'nik' => '3510000000000001',
            'birth_place' => 'Banyuwangi',
            'birth_date' => '1998-05-15',
            'gender' => 'Laki-laki',
            'religion' => 'Islam',
            'address_ktp' => 'Banyuwangi',
            'address_domicile' => 'Banyuwangi',
            'same_as_ktp' => true,
        ]);

        ApplicantEducation::create([
            'applicant_profile_id' => $profile->id,
            'education_level' => 'S1',
            'institution_name' => 'Universitas Airlangga',
            'study_program' => 'Ilmu Keperawatan',
            'accreditation' => 'A',
            'gpa' => '3.80',
            'graduation_year' => 2022,
        ]);

        $period = RecruitmentPeriod::create([
            'name' => 'Periode Rekrutmen 2026',
            'code' => 'PER-2026-01',
            'status' => RecruitmentPeriodStatus::OPEN,
            'registration_start_at' => now()->subDays(5),
            'registration_end_at' => now()->addDays(20),
        ]);

        $this->vacancy = JobVacancy::create([
            'recruitment_period_id' => $period->id,
            'code' => 'PER-001',
            'title' => 'Perawat Rawat Inap',
            'unit' => 'Rawat Inap',
            'employment_type' => 'PKWT',
            'location' => 'RSI Fatimah',
            'quota' => 5,
            'status' => JobVacancyStatus::OPEN,
            'open_at' => now()->subDays(5),
            'close_at' => now()->addDays(20),
        ]);

        $this->stage1 = VacancySelectionStage::create([
            'job_vacancy_id' => $this->vacancy->id,
            'name' => 'Tes Tulis Kompetensi',
            'sort_order' => 1,
        ]);

        $this->stage2 = VacancySelectionStage::create([
            'job_vacancy_id' => $this->vacancy->id,
            'name' => 'Wawancara User',
            'sort_order' => 2,
        ]);

        $this->application = JobApplication::create([
            'user_id' => $this->applicant->id,
            'recruitment_period_id' => $period->id,
            'job_vacancy_id' => $this->vacancy->id,
            'current_stage_id' => $this->stage1->id,
            'status' => JobApplicationStatus::IN_SELECTION,
            'application_number' => 'APP-2026-0001',
            'submitted_at' => now()->subDays(2),
            'locked_at' => now()->subDays(2),
        ]);

        ApplicationSnapshot::create([
            'job_application_id' => $this->application->id,
            'recruitment_period_data' => ['name' => $period->name],
            'personal_data' => ['name' => $this->applicant->name],
            'educations' => [],
            'licenses' => [],
            'certifications' => [],
            'work_experiences' => [],
            'vacancy_data' => [
                'title' => $this->vacancy->title,
                'code' => $this->vacancy->code,
                'selection_stages' => [
                    [
                        'id' => $this->stage1->id,
                        'name' => $this->stage1->stage_name,
                        'sort_order' => 1,
                    ],
                    [
                        'id' => $this->stage2->id,
                        'name' => $this->stage2->stage_name,
                        'sort_order' => 2,
                    ],
                ],
            ],
            'captured_at' => now()->subDays(2),
        ]);
    }

    public function test_admin_can_update_stage_with_pdf_attachment(): void
    {
        $file = UploadedFile::fake()->create('hasil_ujian_tulis.pdf', 500, 'application/pdf');

        $response = $this->actingAs($this->admin)->post(
            route('admin.applications.update-stage', $this->application),
            [
                'stage_id' => $this->stage1->id,
                'status' => 'passed',
                'stage_note' => 'Selamat, Anda lolos ujian tulis dengan nilai 88.',
                'stage_attachment' => $file,
            ]
        );

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $history = ApplicationStageHistory::where('job_application_id', $this->application->id)
            ->where('vacancy_selection_stage_id', $this->stage1->id)
            ->first();

        $this->assertNotNull($history);
        $this->assertEquals(ApplicationStageStatus::PASSED, $history->status);
        $this->assertNotNull($history->attachment_path);
        $this->assertEquals('hasil_ujian_tulis.pdf', $history->attachment_original_name);
        $this->assertEquals('application/pdf', $history->attachment_mime);

        Storage::disk('local')->assertExists($history->attachment_path);

        // Verifikasi admin dapat mendownload file attachment
        $downloadResponse = $this->actingAs($this->admin)->get(
            route('admin.applications.stages.download-attachment', [
                'application' => $this->application,
                'history' => $history,
            ])
        );

        $downloadResponse->assertOk();
        $this->assertStringContainsString('hasil_ujian_tulis.pdf', (string) $downloadResponse->headers->get('Content-Disposition'));

        // Verifikasi tampilan admin show memuat nama attachment
        $showResponse = $this->actingAs($this->admin)->get(route('admin.applications.show', $this->application));
        $showResponse->assertOk();
        $showResponse->assertSee('hasil_ujian_tulis.pdf');
    }

    public function test_admin_can_update_stage_with_jpg_image_attachment(): void
    {
        $file = UploadedFile::fake()->image('hasil_tes_mcu.jpg', 600, 600);

        $response = $this->actingAs($this->admin)->post(
            route('admin.applications.update-stage', $this->application),
            [
                'stage_id' => $this->stage2->id,
                'status' => 'in_progress',
                'stage_note' => 'Lampiran jadwal wawancara terlampir.',
                'stage_attachment' => $file,
            ]
        );

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $history = ApplicationStageHistory::where('job_application_id', $this->application->id)
            ->where('vacancy_selection_stage_id', $this->stage2->id)
            ->first();

        $this->assertNotNull($history);
        $this->assertEquals(ApplicationStageStatus::IN_PROGRESS, $history->status);
        $this->assertNotNull($history->attachment_path);
        $this->assertEquals('hasil_tes_mcu.jpg', $history->attachment_original_name);
        $this->assertStringContainsString('image/', $history->attachment_mime);

        Storage::disk('local')->assertExists($history->attachment_path);
    }

    public function test_applicant_can_see_and_download_attachment_when_published(): void
    {
        $file = UploadedFile::fake()->create('pengumuman_lolos_tahap1.pdf', 300, 'application/pdf');

        $this->actingAs($this->admin)->post(
            route('admin.applications.update-stage', $this->application),
            [
                'stage_id' => $this->stage1->id,
                'status' => 'passed',
                'stage_note' => 'Hasil seleksi tahap 1 terlampir.',
                'stage_attachment' => $file,
            ]
        );

        $history = ApplicationStageHistory::where('job_application_id', $this->application->id)
            ->where('vacancy_selection_stage_id', $this->stage1->id)
            ->first();

        // 1. Pelamar membuka halaman tracking
        $trackingResponse = $this->actingAs($this->applicant)->get(
            route('portal.applications.tracking', $this->application)
        );

        $trackingResponse->assertOk();
        $trackingResponse->assertSee('Lihat / Unduh Lampiran Hasil Tes (PDF/Gambar)');
        $trackingResponse->assertSee('pengumuman_lolos_tahap1.pdf');

        // 2. Pelamar mengunduh berkas lampiran
        $downloadResponse = $this->actingAs($this->applicant)->get(
            route('portal.applications.stages.download-attachment', [
                'jobApplication' => $this->application,
                'history' => $history,
            ])
        );

        $downloadResponse->assertOk();
        $this->assertStringContainsString('pengumuman_lolos_tahap1.pdf', (string) $downloadResponse->headers->get('Content-Disposition'));
    }

    public function test_applicant_cannot_see_or_download_attachment_when_unpublished(): void
    {
        $file = UploadedFile::fake()->create('draft_lampiran_rahasia.pdf', 300, 'application/pdf');

        $this->actingAs($this->admin)->post(
            route('admin.applications.update-stage', $this->application),
            [
                'stage_id' => $this->stage1->id,
                'status' => 'passed',
                'stage_note' => 'Catatan internal panitia.',
                'stage_attachment' => $file,
            ]
        );

        $history = ApplicationStageHistory::where('job_application_id', $this->application->id)
            ->where('vacancy_selection_stage_id', $this->stage1->id)
            ->first();

        // Buat tahapan belum dipublikasikan (published_at = NULL atau di masa depan)
        $history->update(['published_at' => now()->addDays(2)]);

        // 1. Di halaman tracking, tombol unduh dan nama lampiran TIDAK BOLEH tampil
        $trackingResponse = $this->actingAs($this->applicant)->get(
            route('portal.applications.tracking', $this->application)
        );

        $trackingResponse->assertOk();
        $trackingResponse->assertDontSee('draft_lampiran_rahasia.pdf');
        $trackingResponse->assertDontSee('Lihat / Unduh Lampiran Hasil Tes (PDF/Gambar)');

        // 2. Akses download langsung dicegah (403 Forbidden)
        $downloadResponse = $this->actingAs($this->applicant)->get(
            route('portal.applications.stages.download-attachment', [
                'jobApplication' => $this->application,
                'history' => $history,
            ])
        );

        $downloadResponse->assertForbidden();
    }

    public function test_other_applicant_cannot_download_stage_attachment(): void
    {
        $file = UploadedFile::fake()->create('hasil_peserta_a.pdf', 300, 'application/pdf');

        $this->actingAs($this->admin)->post(
            route('admin.applications.update-stage', $this->application),
            [
                'stage_id' => $this->stage1->id,
                'status' => 'passed',
                'stage_attachment' => $file,
            ]
        );

        $history = ApplicationStageHistory::where('job_application_id', $this->application->id)
            ->where('vacancy_selection_stage_id', $this->stage1->id)
            ->first();

        // Buat user pelamar lain
        $otherApplicant = User::create([
            'name' => 'Pelamar Lain',
            'email' => 'other@example.com',
            'phone' => '628999999999',
            'registration_number' => 'REG-TEST-999',
            'password' => 'Password123',
            'role' => User::ROLE_APPLICANT,
            'is_active' => true,
        ]);

        $response = $this->actingAs($otherApplicant)->get(
            route('portal.applications.stages.download-attachment', [
                'jobApplication' => $this->application,
                'history' => $history,
            ])
        );

        $response->assertForbidden();
    }

    public function test_disallowed_file_types_are_rejected_by_validation(): void
    {
        // 1. Ekstensi .exe tidak diizinkan
        $exeFile = UploadedFile::fake()->create('malicious.exe', 100, 'application/x-msdownload');

        $response = $this->actingAs($this->admin)->post(
            route('admin.applications.update-stage', $this->application),
            [
                'stage_id' => $this->stage1->id,
                'status' => 'passed',
                'stage_attachment' => $exeFile,
            ]
        );

        $response->assertSessionHasErrors('stage_attachment');

        // 2. Ekstensi .docx tidak diizinkan (hanya PDF, JPG, PNG)
        $docxFile = UploadedFile::fake()->create('dokumen.docx', 100, 'application/vnd.openxmlformats-officedocument.wordprocessingml.document');

        $response2 = $this->actingAs($this->admin)->post(
            route('admin.applications.update-stage', $this->application),
            [
                'stage_id' => $this->stage1->id,
                'status' => 'passed',
                'stage_attachment' => $docxFile,
            ]
        );

        $response2->assertSessionHasErrors('stage_attachment');

        // 3. File melebihi batas 10 MB (10240 KB)
        $largeFile = UploadedFile::fake()->create('oversize.pdf', 11000, 'application/pdf');

        $response3 = $this->actingAs($this->admin)->post(
            route('admin.applications.update-stage', $this->application),
            [
                'stage_id' => $this->stage1->id,
                'status' => 'passed',
                'stage_attachment' => $largeFile,
            ]
        );

        $response3->assertSessionHasErrors('stage_attachment');
    }
}
