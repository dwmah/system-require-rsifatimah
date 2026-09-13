<?php

namespace App\Services\Notification;

use App\Mail\ApplicationSubmittedMail;
use App\Mail\ApplicationVerificationResultMail;
use App\Mail\SelectionStageUpdatedMail;
use App\Models\JobApplication;
use App\Models\RecruitmentNotification;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class RecruitmentNotificationService
{
    /**
     * Kirim notifikasi dual-channel saat lamaran disubmit.
     */
    public function notifyApplicationSubmitted(JobApplication $application): void
    {
        $user = $application->user;

        // 1. In-App Notification
        RecruitmentNotification::create([
            'user_id' => $user->id,
            'title' => 'Lamaran Berhasil Dikirim',
            'message' => "Lamaran Anda untuk formasi {$application->jobVacancy->title} berhasil dikirim dan sedang menunggu verifikasi berkas oleh Tim SDI.",
            'type' => 'info',
            'action_url' => route('portal.home'),
        ]);

        // 2. Email Notification
        try {
            if ($user->email) {
                Mail::to($user->email)->send(new ApplicationSubmittedMail($application));
            }
        } catch (\Throwable $e) {
            Log::error("Failed to send ApplicationSubmittedMail: {$e->getMessage()}");
        }
    }

    /**
     * Kirim notifikasi dual-channel untuk hasil verifikasi berkas administrasi.
     */
    public function notifyVerificationResult(JobApplication $application, bool $isPassed): void
    {
        $user = $application->user;

        // 1. In-App Notification
        if ($isPassed) {
            RecruitmentNotification::create([
                'user_id' => $user->id,
                'title' => 'Selamat! Anda Lolos Tahap Administrasi',
                'message' => "Berkas lamaran Anda untuk formasi {$application->jobVacancy->title} telah diverifikasi dan dinyatakan LENGKAP. Silakan cek jadwal tahapan seleksi berikutnya.",
                'type' => 'success',
                'action_url' => route('portal.home'),
            ]);
        } else {
            RecruitmentNotification::create([
                'user_id' => $user->id,
                'title' => 'Hasil Verifikasi Berkas Administrasi',
                'message' => "Berkas lamaran Anda untuk formasi {$application->jobVacancy->title} belum memenuhi syarat: " . ($application->rejection_reason ?? 'Berkas tidak lengkap/tidak sesuai.'),
                'type' => 'danger',
                'action_url' => route('portal.home'),
            ]);
        }

        // 2. Email Notification
        try {
            if ($user->email) {
                Mail::to($user->email)->send(new ApplicationVerificationResultMail($application, $isPassed));
            }
        } catch (\Throwable $e) {
            Log::error("Failed to send ApplicationVerificationResultMail: {$e->getMessage()}");
        }
    }

    /**
     * Kirim notifikasi update tahapan seleksi (Tes Tulis, Interview, MCU, Final).
     */
    public function notifyStageUpdate(JobApplication $application, string $stageName, ?string $note = null): void
    {
        $user = $application->user;

        RecruitmentNotification::create([
            'user_id' => $user->id,
            'title' => "Pembaruan Tahapan: {$stageName}",
            'message' => "Tahapan seleksi Anda telah diperbarui ke: {$stageName}. " . ($note ? "Catatan: {$note}" : ''),
            'type' => 'info',
            'action_url' => route('portal.home'),
        ]);

        try {
            if ($user->email) {
                Mail::to($user->email)->send(new SelectionStageUpdatedMail($application, $stageName, $note));
            }
        } catch (\Throwable $e) {
            Log::error("Failed to send SelectionStageUpdatedMail: {$e->getMessage()}");
        }
    }
}
