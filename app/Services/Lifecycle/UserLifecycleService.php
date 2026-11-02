<?php

namespace App\Services\Lifecycle;

use App\Enums\JobApplicationStatus;
use App\Models\JobApplication;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
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

    /**
     * Hapus permanen akun pelamar beserta berkas fisik dari storage.
     */
    public function forceDeleteApplicant(User $user): bool
    {
        if ($user->isAdmin()) {
            return false;
        }

        return DB::transaction(function () use ($user) {
            // Hapus file fisik dokumen lamaran
            $applications = $user->jobApplications()->with('documents')->get();
            foreach ($applications as $application) {
                foreach ($application->documents as $doc) {
                    if ($doc->stored_path) {
                        if (Storage::disk('applicant_documents')->exists($doc->stored_path)) {
                            Storage::disk('applicant_documents')->delete($doc->stored_path);
                        } elseif (Storage::disk('local')->exists($doc->stored_path)) {
                            Storage::disk('local')->delete($doc->stored_path);
                        }
                    }
                }
            }

            // Hapus file foto profil jika ada
            if ($user->applicantProfile && $user->applicantProfile->photo_path) {
                if (Storage::disk('public')->exists($user->applicantProfile->photo_path)) {
                    Storage::disk('public')->delete($user->applicantProfile->photo_path);
                }
            }

            // Force delete relasi dan user
            $user->applicantProfile()?->delete();
            $user->jobApplications()?->delete();
            $user->forceDelete();

            Log::warning("User force deleted permanently along with physical files: ID {$user->id}");

            return true;
        });
    }
}
