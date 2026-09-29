<?php

namespace Tests\Feature;

use App\Enums\JobApplicationStatus;
use App\Enums\JobVacancyStatus;
use App\Enums\RecruitmentPeriodStatus;
use App\Models\ApplicantEducation;
use App\Models\ApplicantProfile;
use App\Models\JobApplication;
use App\Models\JobVacancy;
use App\Models\RecruitmentPeriod;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminLifecycleExcelExportTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected User $acceptedUser;

    protected JobVacancy $vacancy;

    protected JobApplication $acceptedApplication;

    protected function setUp(): void
    {
        parent::setUp();

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
            'code' => 'VAC-IT-001',
            'title' => 'Staf IT Programmer',
            'unit' => 'Sistem Informasi & EDP',
            'employment_type' => 'Full Time',
            'location' => 'Banyuwangi',
            'quota' => 2,
            'open_at' => now()->subDays(10),
            'close_at' => now()->addDays(20),
            'status' => JobVacancyStatus::OPEN,
        ]);

        $this->acceptedUser = User::create([
            'name' => 'Hirzi Rofif Irsyadhi',
            'email' => 'hirzi@rsifatimah.com',
            'phone' => '081234567890',
            'registration_number' => 'PLM-2026-000003',
            'password' => 'Password123',
            'role' => User::ROLE_APPLICANT,
            'is_active' => true,
        ]);

        $profile = ApplicantProfile::create([
            'user_id' => $this->acceptedUser->id,
            'nik' => '3510012345670001',
            'birth_place' => 'Banyuwangi',
            'birth_date' => '2001-05-15',
            'gender' => 'Laki-laki',
            'address_ktp' => 'Jl. Jember No. 12, Banyuwangi',
            'address_domicile' => 'Jl. Jember No. 12, Banyuwangi',
            'same_as_ktp' => true,
        ]);

        ApplicantEducation::create([
            'applicant_profile_id' => $profile->id,
            'education_level' => 'D4',
            'institution_name' => 'Politeknik Negeri Jember',
            'study_program' => 'Teknik Informatika',
            'graduation_year' => 2024,
            'gpa' => 3.85,
            'accreditation' => 'A',
        ]);

        $this->acceptedApplication = JobApplication::create([
            'user_id' => $this->acceptedUser->id,
            'job_vacancy_id' => $this->vacancy->id,
            'recruitment_period_id' => $period->id,
            'application_number' => 'APP-2026-00001',
            'status' => JobApplicationStatus::ACCEPTED,
            'accepted_at' => now(),
            'submitted_at' => now()->subDays(5),
        ]);
    }

    public function test_admin_can_see_export_excel_button_in_lifecycle_page(): void
    {
        $response = $this->actingAs($this->admin)->get(route('admin.lifecycle.index', ['tab' => 'lolos']));

        $response->assertStatus(200);
        $response->assertSee('Unduh Biodata Diterima (.xlsx)');
        $response->assertSee(route('admin.lifecycle.export-accepted-biodata'));
    }

    public function test_admin_can_download_accepted_applicants_biodata_excel(): void
    {
        $response = $this->actingAs($this->admin)->get(route('admin.lifecycle.export-accepted-biodata'));

        $response->assertStatus(200);
        $this->assertStringContainsString('application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', $response->headers->get('content-type') ?? '');
        $this->assertStringContainsString('.xlsx', $response->headers->get('content-disposition') ?? '');
    }

    public function test_non_admin_cannot_download_accepted_biodata(): void
    {
        $response = $this->actingAs($this->acceptedUser)->get(route('admin.lifecycle.export-accepted-biodata'));

        $response->assertStatus(403);
    }
}
