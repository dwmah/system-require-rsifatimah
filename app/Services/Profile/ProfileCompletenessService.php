<?php

namespace App\Services\Profile;

use App\Models\User;

class ProfileCompletenessService
{
    /**
     * Labels for the 10 basic profile criteria.
     *
     * @var array<string, string>
     */
    protected const CRITERIA_LABELS = [
        'name' => 'Nama Lengkap',
        'nik' => 'Nomor Induk Kependudukan (NIK)',
        'email' => 'Alamat Email',
        'phone' => 'Nomor WhatsApp / HP',
        'birth_place' => 'Tempat Lahir',
        'birth_date' => 'Tanggal Lahir',
        'gender' => 'Jenis Kelamin',
        'address_ktp' => 'Alamat Sesuai KTP',
        'address_domicile' => 'Alamat Domisili',
        'education' => 'Minimal 1 Riwayat Pendidikan',
    ];

    /**
     * Evaluate the completeness status of each criterion for the user.
     *
     * @param User $user
     * @return array<string, bool>
     */
    public function details(User $user): array
    {
        // Load relationship if not already loaded to avoid duplicate queries
        $user->loadMissing(['applicantProfile.educations']);

        $profile = $user->applicantProfile;

        return [
            'name' => ! empty(trim((string) ($user->name ?? ''))),
            'nik' => ! empty(trim((string) ($profile?->nik ?? ''))),
            'email' => ! empty(trim((string) ($user->email ?? ''))),
            'phone' => ! empty(trim((string) ($user->phone ?? ''))),
            'birth_place' => ! empty(trim((string) ($profile?->birth_place ?? ''))),
            'birth_date' => ! empty($profile?->birth_date),
            'gender' => ! empty(trim((string) ($profile?->gender ?? ''))),
            'address_ktp' => ! empty(trim((string) ($profile?->address_ktp ?? ''))),
            'address_domicile' => ! empty(trim((string) ($profile?->address_domicile ?? ''))),
            'education' => ($profile?->educations?->count() ?? 0) >= 1,
        ];
    }

    /**
     * Calculate the basic profile completeness percentage (0-100%).
     *
     * @param User $user
     * @return int
     */
    public function percentage(User $user): int
    {
        $details = $this->details($user);
        $total = count($details);
        $completed = count(array_filter($details));

        if ($total === 0) {
            return 0;
        }

        return (int) round(($completed / $total) * 100);
    }

    /**
     * Determine whether the basic profile is 100% complete.
     *
     * @param User $user
     * @return bool
     */
    public function isComplete(User $user): bool
    {
        return $this->percentage($user) === 100;
    }

    /**
     * Return list of missing criteria labels.
     *
     * @param User $user
     * @return array<string>
     */
    public function missingItems(User $user): array
    {
        $details = $this->details($user);
        $missing = [];

        foreach ($details as $key => $isDone) {
            if (! $isDone && isset(self::CRITERIA_LABELS[$key])) {
                $missing[] = self::CRITERIA_LABELS[$key];
            }
        }

        return $missing;
    }
}
