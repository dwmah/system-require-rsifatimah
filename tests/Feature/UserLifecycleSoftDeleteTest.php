<?php

namespace Tests\Feature;

use App\Enums\JobApplicationStatus;
use App\Enums\JobVacancyStatus;
use App\Enums\RecruitmentPeriodStatus;
use App\Models\ApplicantProfile;
use App\Models\ApplicationDocument;
use App\Models\JobApplication;
use App\Models\JobVacancy;
use App\Models\RecruitmentPeriod;
use App\Models\User;
use App\Models\VacancyDocumentRequirement;
use App\Services\Lifecycle\UserLifecycleService;
use App\Services\Recruitment\ApplicationDocumentService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class UserLifecycleSoftDeleteTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected User $rejectedUser;

    protected JobVacancy $vacancy;

    protected JobApplication $rejectedApplication;

    protected VacancyDocumentRequirement $requirement;

    protected ApplicationDocument $document;

    protected string $docPath;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake(ApplicationDocumentService::DISK_NAME);
        Storage::fake('public');

        $this->admin = User::create([
            'name' => 'Admin SDI',
            'email' => 'admin.sdi@rsifatimah.com',
            'password' => 'Password123',
            'role' => User::ROLE_ADMIN,
            'is_active' => true,
        ]);

        $period = RecruitmentPeriod::create([
            'code' => 'GEL-1-2026',
            'name' => 'Gelombang 1 2026',
            'registration_start_at' => now()->subDays(10),
            'registration_end_at' => now()->addDays(20),
            'status' => RecruitmentPeriodStatus::OPEN,
            'is_active' => true,
        ]);

        $this->vacancy = JobVacancy::create([
            'recruitment_period_id' => $period->id,
            'code' => 'VAC-MED-001',
            'title' => 'Dokter Umum',
            'unit' => 'Pelayanan Medis',
            'employment_type' => 'Full Time',
            'location' => 'Banyuwangi',
            'quota' => 2,
            'open_at' => now()->subDays(10),
            'close_at' => now()->addDays(20),
            'status' => JobVacancyStatus::OPEN,
        ]);

        $this->requirement = VacancyDocumentRequirement::create([
            'job_vacancy_id' => $this->vacancy->id,
            'document_type' => 'cv',
            'name' => 'Curriculum Vitae',
            'description' => 'CV lengkap dan terbaru',
            'is_required' => true,
            'max_size_kb' => 2048,
            'allowed_extensions' => ['pdf'],
        ]);

        $this->rejectedUser = User::create([
            'name' => 'Kandidat Tidak Lolos',
            'email' => 'kandidat.gagal@example.com',
            'phone' => '08987654321',
            'registration_number' => 'REG-2026-999999',
            'password' => 'Password123',
            'role' => User::ROLE_APPLICANT,
            'is_active' => true,
        ]);

        ApplicantProfile::create([
            'user_id' => $this->rejectedUser->id,
            'nik' => '3510099999990001',
            'birth_place' => 'Banyuwangi',
            'birth_date' => '1998-08-17',
            'gender' => 'Laki-laki',
            'address_ktp' => 'Jl. RS Fatimah No. 1',
            'address_domicile' => 'Jl. RS Fatimah No. 1',
            'same_as_ktp' => true,
        ]);

        $this->rejectedApplication = JobApplication::create([
            'user_id' => $this->rejectedUser->id,
            'job_vacancy_id' => $this->vacancy->id,
            'recruitment_period_id' => $period->id,
            'application_number' => 'APP-2026-99999',
            'status' => JobApplicationStatus::REJECTED,
            'rejected_at' => now(),
            'rejection_reason' => 'Kualifikasi belum sesuai kriteria formasi saat ini.',
            'announcement_seen_at' => now()->subHour(),
        ]);

        $fakePdf = UploadedFile::fake()->create('cv_kandidat.pdf', 300, 'application/pdf');
        $this->docPath = $fakePdf->store('applications/'.$this->rejectedApplication->id, ApplicationDocumentService::DISK_NAME);

        $this->document = ApplicationDocument::create([
            'job_application_id' => $this->rejectedApplication->id,
            'vacancy_document_requirement_id' => $this->requirement->id,
            'original_name' => 'cv_kandidat.pdf',
            'stored_path' => $this->docPath,
            'file_size' => 300 * 1024,
            'mime_type' => 'application/pdf',
        ]);
    }

    /**
     * Verifikasi bahwa cleanup menjalankan soft delete dan file berkas fisik pelamar TIDAK dihapus.
     */
    public function test_cleanup_performs_soft_delete_and_preserves_physical_files(): void
    {
        $service = app(UserLifecycleService::class);

        // Pastikan file dokumen ada sebelum cleanup
        Storage::disk(ApplicationDocumentService::DISK_NAME)->assertExists($this->docPath);

        // Eksekusi cleanup
        $result = $service->cleanupRejectedApplicant($this->rejectedUser);

        $this->assertTrue($result);

        // 1. User mengalami soft delete
        $this->rejectedUser->refresh();
        $this->assertTrue($this->rejectedUser->trashed());
        $this->assertNotNull($this->rejectedUser->deleted_at);
        $this->assertSoftDeleted('users', ['id' => $this->rejectedUser->id]);

        // 2. Berkas fisik CV/Portofolio pelamar TIDAK DIHAPUS dari storage
        Storage::disk(ApplicationDocumentService::DISK_NAME)->assertExists($this->docPath);

        // 3. Record ApplicationDocument dan JobApplication di database tetap tersimpan
        $this->assertDatabaseHas('application_documents', ['id' => $this->document->id]);
        $this->assertDatabaseHas('job_applications', ['id' => $this->rejectedApplication->id]);
        $this->assertDatabaseHas('applicant_profiles', ['user_id' => $this->rejectedUser->id]);
    }

    /**
     * Verifikasi admin dapat melakukan cleanup via HTTP route dan diarahkan kembali dengan sukses.
     */
    public function test_admin_can_trigger_soft_delete_cleanup_via_route(): void
    {
        Storage::disk(ApplicationDocumentService::DISK_NAME)->assertExists($this->docPath);

        $response = $this->actingAs($this->admin)
            ->delete(route('admin.lifecycle.cleanup', $this->rejectedUser));

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->rejectedUser->refresh();
        $this->assertTrue($this->rejectedUser->trashed());

        // File berkas fisik tetap tersimpan
        Storage::disk(ApplicationDocumentService::DISK_NAME)->assertExists($this->docPath);
    }

    /**
     * Verifikasi admin tetap dapat mengakses riwayat pelamar dan berkas dokumennya di Admin Lifecycle & Detail Lamaran.
     */
    public function test_admin_can_view_soft_deleted_applicant_in_lifecycle_and_view_documents(): void
    {
        $service = app(UserLifecycleService::class);
        $service->cleanupRejectedApplicant($this->rejectedUser);

        $this->assertTrue($this->rejectedUser->fresh()->trashed());

        // 1. Admin melihat tab pelamar tidak lolos di Lifecycle
        $lifecycleResponse = $this->actingAs($this->admin)
            ->get(route('admin.lifecycle.index', ['tab' => 'tidak_lolos']));

        $lifecycleResponse->assertOk();
        $lifecycleResponse->assertSee($this->rejectedUser->name);
        $lifecycleResponse->assertSee('Akun Diarsipkan / Soft Deleted');
        $lifecycleResponse->assertSee(route('admin.applications.show', $this->rejectedApplication));

        // 2. Admin membuka halaman detail lamaran pelamar yang akunnya di-soft-delete
        $detailResponse = $this->actingAs($this->admin)
            ->get(route('admin.applications.show', $this->rejectedApplication));

        $detailResponse->assertOk();
        $detailResponse->assertSee($this->rejectedUser->name);
        $detailResponse->assertSee('cv_kandidat.pdf');
    }

    /**
     * Verifikasi batch cleanup massal mengarsipkan akun terkonfirmasi tanpa menghapus berkas fisik.
     */
    public function test_batch_cleanup_all_seen_soft_deletes_confirmed_applicants_and_keeps_files(): void
    {
        Storage::disk(ApplicationDocumentService::DISK_NAME)->assertExists($this->docPath);

        $response = $this->actingAs($this->admin)
            ->post(route('admin.lifecycle.cleanup-all-seen'));

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->rejectedUser->refresh();
        $this->assertTrue($this->rejectedUser->trashed());

        // Berkas dokumen fisik tetap dipertahankan
        Storage::disk(ApplicationDocumentService::DISK_NAME)->assertExists($this->docPath);
    }

    /**
     * Verifikasi method talent pool di UserLifecycleService mengembalikan kandidat soft deleted.
     */
    public function test_talent_pool_query_and_is_archived_helper(): void
    {
        $service = app(UserLifecycleService::class);

        $this->assertFalse($service->isApplicantArchived($this->rejectedUser));

        $service->cleanupRejectedApplicant($this->rejectedUser);

        $this->assertTrue($service->isApplicantArchived($this->rejectedUser->fresh()));

        $talentPoolResults = $service->getArchivedTalentPoolQuery()->get();
        $this->assertTrue($talentPoolResults->contains('id', $this->rejectedApplication->id));

        $archivedApp = $talentPoolResults->firstWhere('id', $this->rejectedApplication->id);
        $this->assertNotNull($archivedApp->user);
        $this->assertTrue($archivedApp->user->trashed());
    }
}
