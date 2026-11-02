<?php

namespace Tests\Feature;

use App\Enums\JobApplicationStatus;
use App\Enums\JobVacancyStatus;
use App\Enums\RecruitmentPeriodStatus;
use App\Models\ApplicantProfile;
use App\Models\JobApplication;
use App\Models\JobVacancy;
use App\Models\RecruitmentNotification;
use App\Models\RecruitmentPeriod;
use App\Models\User;
use App\Services\Notification\RecruitmentNotificationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ApplicantNotificationTest extends TestCase
{
    use RefreshDatabase;

    protected function createApplicantUser(array $userAttributes = []): User
    {
        static $sequence = 1;
        $seq = $sequence++;

        $user = User::create(array_merge([
            'name' => 'Pelamar Notifikasi '.$seq,
            'email' => "pelamar.notif{$seq}@example.com",
            'phone' => sprintf('62812345%05d', $seq),
            'registration_number' => sprintf('PLM-2026-%06d', $seq),
            'password' => 'Password123',
            'is_active' => true,
        ], $userAttributes));

        ApplicantProfile::create([
            'user_id' => $user->id,
            'nik' => sprintf('3510123456%06d', $seq),
            'same_as_ktp' => false,
        ]);

        return $user->fresh(['applicantProfile']);
    }

    public function test_guest_cannot_access_notifications(): void
    {
        $response = $this->get(route('portal.notifications.index'));
        $response->assertRedirect(route('login'));
    }

    public function test_applicant_can_view_notifications_list(): void
    {
        $user = $this->createApplicantUser();

        $notification = RecruitmentNotification::create([
            'user_id' => $user->id,
            'title' => 'Pembaruan Tahapan Seleksi',
            'message' => 'Tahapan seleksi Anda telah diperbarui.',
            'type' => 'info',
            'action_url' => route('portal.home'),
            'read_at' => null,
        ]);

        $response = $this->actingAs($user)->get(route('portal.notifications.index'));

        $response->assertOk();
        $response->assertSee('Pembaruan Tahapan Seleksi');
        $response->assertSee(route('portal.notifications.show', $notification));
        $response->assertSee('Lihat Detail');
        $response->assertSee('Tandai dibaca');
    }

    public function test_clicking_notification_detail_automatically_marks_it_as_read_and_redirects(): void
    {
        $user = $this->createApplicantUser();

        $notification = RecruitmentNotification::create([
            'user_id' => $user->id,
            'title' => 'Selamat! Anda Lolos Tahap Administrasi',
            'message' => 'Berkas lamaran Anda telah diverifikasi.',
            'type' => 'success',
            'action_url' => route('portal.home'),
            'read_at' => null,
        ]);

        $this->assertNull($notification->read_at);
        $this->assertFalse($notification->isRead());

        $response = $this->actingAs($user)->get(route('portal.notifications.show', $notification));

        $response->assertRedirect(route('portal.home'));

        $notification->refresh();
        $this->assertNotNull($notification->read_at);
        $this->assertTrue($notification->isRead());
    }

    public function test_clicking_notification_detail_when_already_read_still_redirects_safely(): void
    {
        $user = $this->createApplicantUser();
        $readTime = now()->subHour();

        $notification = RecruitmentNotification::create([
            'user_id' => $user->id,
            'title' => 'Notifikasi Lama',
            'message' => 'Pesan lama',
            'type' => 'info',
            'action_url' => route('portal.home'),
            'read_at' => $readTime,
        ]);

        $response = $this->actingAs($user)->get(route('portal.notifications.show', $notification));

        $response->assertRedirect(route('portal.home'));

        $notification->refresh();
        $this->assertEquals($readTime->toIso8601String(), $notification->read_at->toIso8601String());
    }

    public function test_applicant_cannot_view_or_mark_other_users_notification(): void
    {
        $user1 = $this->createApplicantUser();
        $user2 = $this->createApplicantUser();

        $notificationUser2 = RecruitmentNotification::create([
            'user_id' => $user2->id,
            'title' => 'Pesan Khusus User 2',
            'message' => 'Rahasia user 2',
            'type' => 'info',
            'action_url' => route('portal.home'),
            'read_at' => null,
        ]);

        $response = $this->actingAs($user1)->get(route('portal.notifications.show', $notificationUser2));
        $response->assertForbidden();

        $postResponse = $this->actingAs($user1)->post(route('portal.notifications.read', $notificationUser2));
        $postResponse->assertForbidden();

        $notificationUser2->refresh();
        $this->assertNull($notificationUser2->read_at);
    }

    public function test_applicant_can_manually_mark_single_notification_as_read(): void
    {
        $user = $this->createApplicantUser();

        $notification = RecruitmentNotification::create([
            'user_id' => $user->id,
            'title' => 'Info Berkas',
            'message' => 'Informasi berkas',
            'type' => 'info',
            'action_url' => null,
            'read_at' => null,
        ]);

        $response = $this->actingAs($user)->post(route('portal.notifications.read', $notification));

        $response->assertSessionHas('success');
        $notification->refresh();
        $this->assertNotNull($notification->read_at);
    }

    public function test_applicant_can_mark_all_notifications_as_read(): void
    {
        $user = $this->createApplicantUser();

        $notif1 = RecruitmentNotification::create([
            'user_id' => $user->id,
            'title' => 'Notif 1',
            'message' => 'Pesan 1',
            'type' => 'info',
            'read_at' => null,
        ]);

        $notif2 = RecruitmentNotification::create([
            'user_id' => $user->id,
            'title' => 'Notif 2',
            'message' => 'Pesan 2',
            'type' => 'info',
            'read_at' => null,
        ]);

        $response = $this->actingAs($user)->post(route('portal.notifications.read-all'));

        $response->assertSessionHas('success');

        $this->assertNotNull($notif1->fresh()->read_at);
        $this->assertNotNull($notif2->fresh()->read_at);
    }

    public function test_clicking_notification_detail_redirects_to_application_tracking_when_applicant_has_submitted_application(): void
    {
        $user = $this->createApplicantUser();

        $period = RecruitmentPeriod::create([
            'code' => 'PER-NOTIF-01',
            'name' => 'Periode Rekrutmen 2026',
            'status' => RecruitmentPeriodStatus::OPEN,
            'registration_start_at' => now()->subDays(5),
            'registration_end_at' => now()->addDays(10),
        ]);

        $vacancy = JobVacancy::create([
            'recruitment_period_id' => $period->id,
            'code' => 'VAC-NOTIF-01',
            'title' => 'Perawat',
            'unit' => 'Instalasi Gawat Darurat',
            'employment_type' => 'PKWT',
            'location' => 'RSI Fatimah Banyuwangi',
            'quota' => 5,
            'status' => JobVacancyStatus::OPEN,
            'open_at' => now()->subDays(5),
            'close_at' => now()->addDays(10),
        ]);

        $application = JobApplication::create([
            'user_id' => $user->id,
            'recruitment_period_id' => $period->id,
            'job_vacancy_id' => $vacancy->id,
            'status' => JobApplicationStatus::SUBMITTED,
            'application_number' => 'LAM-NOTIF-001',
            'submitted_at' => now(),
        ]);

        $notification = RecruitmentNotification::create([
            'user_id' => $user->id,
            'title' => 'Selamat! Anda Lolos Tahap Administrasi',
            'message' => 'Berkas lamaran Anda telah diverifikasi.',
            'type' => 'success',
            'action_url' => route('portal.home'),
            'read_at' => null,
        ]);

        $response = $this->actingAs($user)->get(route('portal.notifications.show', $notification));

        $response->assertRedirect(route('portal.applications.tracking', $application));
        $this->assertNotNull($notification->fresh()->read_at);
    }

    public function test_recruitment_notification_service_creates_action_url_pointing_to_application_tracking(): void
    {
        $user = $this->createApplicantUser();

        $period = RecruitmentPeriod::create([
            'code' => 'PER-NOTIF-02',
            'name' => 'Periode Rekrutmen 2026',
            'status' => RecruitmentPeriodStatus::OPEN,
            'registration_start_at' => now()->subDays(5),
            'registration_end_at' => now()->addDays(10),
        ]);

        $vacancy = JobVacancy::create([
            'recruitment_period_id' => $period->id,
            'code' => 'VAC-NOTIF-02',
            'title' => 'Perawat',
            'unit' => 'Instalasi Gawat Darurat',
            'employment_type' => 'PKWT',
            'location' => 'RSI Fatimah Banyuwangi',
            'quota' => 5,
            'status' => JobVacancyStatus::OPEN,
            'open_at' => now()->subDays(5),
            'close_at' => now()->addDays(10),
        ]);

        $application = JobApplication::create([
            'user_id' => $user->id,
            'recruitment_period_id' => $period->id,
            'job_vacancy_id' => $vacancy->id,
            'status' => JobApplicationStatus::SUBMITTED,
            'application_number' => 'LAM-NOTIF-002',
            'submitted_at' => now(),
        ]);

        $service = app(RecruitmentNotificationService::class);
        $service->notifyVerificationResult($application, true);

        $notification = RecruitmentNotification::where('user_id', $user->id)
            ->where('title', 'Selamat! Anda Lolos Tahap Administrasi')
            ->first();

        $this->assertNotNull($notification);
        $this->assertEquals(route('portal.applications.tracking', $application), $notification->action_url);
    }
}
