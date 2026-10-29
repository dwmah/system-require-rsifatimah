<?php

namespace App\Services\Lifecycle;

use App\Enums\JobApplicationStatus;
use App\Models\JobApplication;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class UserLifecycleService
{
    /**
     * Nonaktifkan akun pelamar yang lolos (Arsip Data Kepegawaian Baru).
     * Seluruh biodata dan berkas dokumen tetap dipertahankan.
     */
    public function deactivatePassedApplicant(User $user): bool
    {
        if (! $user->isApplicant()) {
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
     * Lakukan pengarsipan (soft delete) pada akun pelamar yang tidak lolos seleksi.
     * Berkas fisik dokumen CV, portofolio, dan foto tetap dipertahankan untuk talent pool.
     */
    public function cleanupRejectedApplicant(User $user): bool
    {
        if ($user->isAdmin()) {
            return false;
        }

        return DB::transaction(function () use ($user) {
            // Berkas fisik CV/Portofolio dan dokumen TIDAK dihapus (dipertahankan untuk talent pool).
            // Lakukan soft delete pada user (mengisi deleted_at).
            $user->delete();

            Log::info("User lifecycle soft delete executed successfully for user ID: {$user->id}");

            return true;
        });
    }

    /**
     * Batch pengarsipan (soft delete) untuk seluruh pelamar tidak lolos yang telah mengonfirmasi melihat pengumuman.
     */
    public function cleanupAllSeenRejectedApplicants(): int
    {
        $rejectedApplications = JobApplication::where('status', JobApplicationStatus::REJECTED)
            ->whereNotNull('announcement_seen_at')
            ->whereHas('user', fn ($q) => $q->whereNull('deleted_at'))
            ->with(['user', 'documents'])
            ->get();

        $count = 0;
        foreach ($rejectedApplications as $application) {
            if ($application->user && ! $application->user->trashed()) {
                $this->cleanupRejectedApplicant($application->user);
                $count++;
            }
        }

        return $count;
    }

    /**
     * Cek apakah akun pelamar telah diarsipkan (soft delete).
     */
    public function isApplicantArchived(User $user): bool
    {
        return $user->trashed();
    }

    /**
     * Dapatkan query pelamar tidak lolos yang telah diarsipkan (soft deleted) sebagai talent pool.
     */
    public function getArchivedTalentPoolQuery(): Builder
    {
        return JobApplication::where('status', JobApplicationStatus::REJECTED)
            ->whereHas('user', fn ($q) => $q->onlyTrashed())
            ->with(['user' => fn ($q) => $q->withTrashed(), 'jobVacancy', 'documents']);
    }
}
