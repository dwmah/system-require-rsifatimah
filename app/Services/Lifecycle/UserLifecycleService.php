<?php

namespace App\Services\Lifecycle;

use App\Enums\JobApplicationStatus;
use App\Models\JobApplication;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class UserLifecycleService
{
    /**
     * Nonaktifkan akun pelamar yang lolos (Arsip Data Kepegawaian Baru).
     * Seluruh biodata dan berkas dokumen tetap dipertahankan.
     */
    public function deactivatePassedApplicant(User $user): bool
    {
        if (!$user->isApplicant()) {
            return false;
        }

        return $user->update([
            'is_active' => false,
            'deactivated_at' => now(),
        ]);
    }

    /**
     * Aktifkan kembali akun yang dinonaktifkan jika diperlukan oleh Admin.
     */
    public function reactivateApplicant(User $user): bool
    {
        return $user->update([
            'is_active' => true,
            'deactivated_at' => null,
        ]);
    }

    /**
     * Hapus permanen data dan berkas fisik pelamar yang tidak lolos seleksi
     * setelah pelamar melihat pengumuman (announcement_seen_at) atau dipicu oleh admin.
     */
    public function cleanupRejectedApplicant(User $user): bool
    {
        if ($user->isAdmin()) {
            return false;
        }

        return DB::transaction(function () use ($user) {
            // 1. Hapus seluruh berkas dokumen fisik dari storage privat
            foreach ($user->jobApplications as $application) {
                foreach ($application->documents as $document) {
                    if ($document->stored_path && Storage::exists($document->stored_path)) {
                        Storage::delete($document->stored_path);
                    }
                }
            }

            // 2. Hapus profile photo jika ada
            if ($user->applicantProfile && $user->applicantProfile->photo_path) {
                if (Storage::disk('public')->exists($user->applicantProfile->photo_path)) {
                    Storage::disk('public')->delete($user->applicantProfile->photo_path);
                } elseif (Storage::exists($user->applicantProfile->photo_path)) {
                    Storage::delete($user->applicantProfile->photo_path);
                }
            }

            // 3. Hapus record user (cascade delete akan menghapus profile, educations, applications, dll.)
            $user->delete();

            Log::info("User lifecycle cleanup executed successfully for user ID: {$user->id}");

            return true;
        });
    }

    /**
     * Batch cleanup untuk seluruh pelamar tidak lolos yang telah mengonfirmasi melihat pengumuman.
     */
    public function cleanupAllSeenRejectedApplicants(): int
    {
        $rejectedApplications = JobApplication::where('status', JobApplicationStatus::REJECTED)
            ->whereNotNull('announcement_seen_at')
            ->with(['user', 'documents'])
            ->get();

        $count = 0;
        foreach ($rejectedApplications as $application) {
            if ($application->user) {
                $this->cleanupRejectedApplicant($application->user);
                $count++;
            }
        }

        return $count;
    }
}
