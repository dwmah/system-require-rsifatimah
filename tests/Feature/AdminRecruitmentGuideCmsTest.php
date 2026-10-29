<?php

namespace Tests\Feature;

use App\Enums\JobVacancyStatus;
use App\Enums\RecruitmentGuideType;
use App\Enums\RecruitmentPeriodStatus;
use App\Models\JobVacancy;
use App\Models\RecruitmentGuide;
use App\Models\RecruitmentPeriod;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminRecruitmentGuideCmsTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected User $applicant;

    protected RecruitmentPeriod $period;

    protected JobVacancy $vacancy;

    protected RecruitmentGuide $flowGuide;

    protected RecruitmentGuide $requirementsGuide;

    protected function setUp(): void
    {
        parent::setUp();

        $this->period = RecruitmentPeriod::create([
            'name' => 'Rekrutmen Tenaga Kesehatan 2026',
            'code' => 'RSIF-2026-TEST',
            'registration_start_at' => now()->subDays(5),
            'registration_end_at' => now()->addDays(20),
            'status' => RecruitmentPeriodStatus::OPEN,
            'is_active' => true,
        ]);

        $this->vacancy = JobVacancy::create([
            'recruitment_period_id' => $this->period->id,
            'code' => 'POS-2026-TEST-01',
            'title' => 'Staf Rekrutmen',
            'unit' => 'SDI',
            'employment_type' => 'PKWT',
            'quota' => 1,
            'location' => 'RSI Fatimah Banyuwangi',
            'open_at' => now()->subDays(2),
            'close_at' => now()->addDays(10),
            'status' => JobVacancyStatus::OPEN,
            'is_active' => true,
        ]);

        $this->admin = User::factory()->create([
            'name' => 'Admin SDI Fatimah',
            'email' => 'admin@rsifatimah.com',
            'role' => User::ROLE_ADMIN,
            'is_active' => true,
        ]);

        $this->applicant = User::factory()->create([
            'name' => 'Pelamar RSI',
            'email' => 'pelamar@gmail.com',
            'role' => User::ROLE_APPLICANT,
            'is_active' => true,
        ]);

        $this->flowGuide = RecruitmentGuide::where('slug', 'alur-pendaftaran')->first()
            ?? RecruitmentGuide::create([
                'type' => RecruitmentGuideType::FLOW->value,
                'title' => 'Alur Pendaftaran Rekrutmen SDM',
                'slug' => 'alur-pendaftaran',
                'subtitle' => 'Ikuti tahapan resmi pendaftaran calon pegawai RSI Fatimah Banyuwangi.',
                'sort_order' => 1,
                'is_active' => true,
                'content' => [
                    [
                        'step' => 1,
                        'title' => 'Registrasi Akun Baru',
                        'description' => 'Membuat akun pelamar dengan email aktif.',
                    ],
                ],
            ]);

        $this->requirementsGuide = RecruitmentGuide::where('slug', 'persyaratan-pendaftaran')->first()
            ?? RecruitmentGuide::create([
                'type' => RecruitmentGuideType::REQUIREMENTS->value,
                'title' => 'Persyaratan & Ketentuan Pendaftaran',
                'slug' => 'persyaratan-pendaftaran',
                'subtitle' => 'Panduan kualifikasi umum dan dokumen.',
                'sort_order' => 2,
                'is_active' => true,
                'content' => [
                    [
                        'step' => 1,
                        'title' => 'Persyaratan Berkas Wajib',
                        'description' => 'Ijazah dan transkrip asli berformat PDF.',
                    ],
                ],
            ]);
    }

    public function test_guest_cannot_access_admin_guides_page(): void
    {
        $response = $this->get(route('admin.guides.index'));

        $response->assertRedirect(route('login'));
    }

    public function test_non_admin_user_cannot_access_admin_guides_page(): void
    {
        $response = $this->actingAs($this->applicant)->get(route('admin.guides.index'));

        $response->assertStatus(403);
    }

    public function test_admin_can_view_guides_index_page(): void
    {
        $response = $this->actingAs($this->admin)->get(route('admin.guides.index'));

        $response->assertStatus(200);
        $response->assertSee('Kelola Konten Panduan (CMS)');
        $response->assertSee('Kelola Panduan');
        $response->assertSee('Alur Pendaftaran Rekrutmen SDM');
        $response->assertSee('Persyaratan &amp; Ketentuan Pendaftaran', false);
        $response->assertSee('Tambah Bagian Panduan');
    }

    public function test_admin_can_view_guide_edit_page(): void
    {
        $response = $this->actingAs($this->admin)->get(route('admin.guides.edit', $this->flowGuide));

        $response->assertStatus(200);
        $response->assertSee('Edit Panduan: '.$this->flowGuide->title);
        $response->assertSee('value="'.e($this->flowGuide->title).'"', false);
        $response->assertSee('Simpan Perubahan Panduan');
    }

    public function test_admin_can_update_flow_guide_content(): void
    {
        $updatedData = [
            'title' => 'Alur Pendaftaran SDM Terkini 2026',
            'type' => 'flow',
            'subtitle' => 'Tahapan seleksi berbasis sistem terbaru RSI Fatimah.',
            'sort_order' => 1,
            'is_active' => true,
            'items' => [
                [
                    'step' => 1,
                    'title' => 'Langkah 1: Registrasi Akun Online',
                    'description' => 'Pelamar mendaftar menggunakan NIK valid dan email aktif.',
                ],
                [
                    'step' => 2,
                    'title' => 'Langkah 2: Verifikasi Berkas & STR',
                    'description' => 'Tim SDI memvalidasi berkas dan STR pelamar secara online.',
                ],
            ],
        ];

        $response = $this->actingAs($this->admin)->put(
            route('admin.guides.update', $this->flowGuide),
            $updatedData
        );

        $response->assertRedirect(route('admin.guides.index'));
        $response->assertSessionHas('success');

        $this->flowGuide->refresh();
        $this->assertSame('Alur Pendaftaran SDM Terkini 2026', $this->flowGuide->title);
        $this->assertSame('Tahapan seleksi berbasis sistem terbaru RSI Fatimah.', $this->flowGuide->subtitle);
        $this->assertCount(2, $this->flowGuide->content);
        $this->assertSame('Langkah 1: Registrasi Akun Online', $this->flowGuide->content[0]['title']);
    }

    public function test_admin_can_update_requirements_guide_content(): void
    {
        $updatedData = [
            'title' => 'Ketentuan & Dokumen Wajib 2026',
            'type' => 'requirements',
            'subtitle' => 'Syarat berkas digital calon tenaga medis dan umum.',
            'sort_order' => 2,
            'is_active' => true,
            'items' => [
                [
                    'step' => 1,
                    'title' => '1. Kualifikasi Utama Pelamar',
                    'description' => "WNI berakhlak mulia.\nSehat jasmani dan rohani.",
                ],
                [
                    'step' => 2,
                    'title' => '2. Dokumen Format PDF',
                    'description' => 'Maksimal ukuran file dokumen adalah 2 MB per berkas.',
                ],
            ],
        ];

        $response = $this->actingAs($this->admin)->put(
            route('admin.guides.update', $this->requirementsGuide),
            $updatedData
        );

        $response->assertRedirect(route('admin.guides.index'));
        $response->assertSessionHas('success');

        $this->requirementsGuide->refresh();
        $this->assertSame('Ketentuan & Dokumen Wajib 2026', $this->requirementsGuide->title);
        $this->assertCount(2, $this->requirementsGuide->content);
    }

    public function test_updated_flow_guide_appears_on_public_flow_page(): void
    {
        $this->flowGuide->update([
            'title' => 'Alur Pendaftaran Terupdate RSI Fatimah',
            'subtitle' => 'Petunjuk resmi langkah seleksi periode saat ini.',
            'content' => [
                [
                    'step' => 1,
                    'title' => 'Pembuatan Akun Portal Terpadu',
                    'description' => 'Calon pelamar membuat akun melalui portal rekrutmen terpadu.',
                ],
            ],
        ]);

        $response = $this->get(route('guides.flow'));

        $response->assertStatus(200);
        $response->assertSee('Alur Pendaftaran Terupdate RSI Fatimah');
        $response->assertSee('Petunjuk resmi langkah seleksi periode saat ini.');
        $response->assertSee('Pembuatan Akun Portal Terpadu');
        $response->assertSee('Calon pelamar membuat akun melalui portal rekrutmen terpadu.');
    }

    public function test_updated_requirements_guide_appears_on_public_requirements_page(): void
    {
        $this->requirementsGuide->update([
            'title' => 'Persyaratan Khusus Pelamar 2026',
            'subtitle' => 'Kriteria dan format dokumen yang wajib dipenuhi pelamar.',
            'content' => [
                [
                    'step' => 1,
                    'title' => 'Persyaratan Khusus Dokter dan Perawat',
                    'description' => 'STR wajib masih aktif minimal 6 bulan ke depan.',
                ],
            ],
        ]);

        $response = $this->get(route('guides.requirements'));

        $response->assertStatus(200);
        $response->assertSee('Persyaratan Khusus Pelamar 2026');
        $response->assertSee('Kriteria dan format dokumen yang wajib dipenuhi pelamar.');
        $response->assertSee('Persyaratan Khusus Dokter dan Perawat');
        $response->assertSee('STR wajib masih aktif minimal 6 bulan ke depan.');
    }

    public function test_admin_can_toggle_active_status_of_guide(): void
    {
        $this->assertTrue($this->flowGuide->is_active);

        $response = $this->actingAs($this->admin)->post(
            route('admin.guides.toggle-active', $this->flowGuide)
        );

        $response->assertRedirect();
        $this->flowGuide->refresh();
        $this->assertFalse($this->flowGuide->is_active);

        // Toggle back to active
        $this->actingAs($this->admin)->post(
            route('admin.guides.toggle-active', $this->flowGuide)
        );

        $this->flowGuide->refresh();
        $this->assertTrue($this->flowGuide->is_active);
    }

    public function test_admin_can_store_new_guide(): void
    {
        $response = $this->actingAs($this->admin)->post(route('admin.guides.store'), [
            'title' => 'Panduan Wawancara Direksi',
            'type' => 'flow',
            'subtitle' => 'Tata tertib dan persiapan ujian wawancara tatap muka.',
            'sort_order' => 3,
            'is_active' => true,
        ]);

        $response->assertRedirect(route('admin.guides.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('recruitment_guides', [
            'title' => 'Panduan Wawancara Direksi',
            'slug' => 'panduan-wawancara-direksi',
            'type' => 'flow',
        ]);
    }
}
