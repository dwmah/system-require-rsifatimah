<?php

namespace App\Services\Auth;

use App\Models\ApplicantProfile;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class ApplicantRegistrationService
{
    /**
     * Register a new applicant user and profile atomically.
     *
     * @param array<string, mixed> $data
     * @return User
     */
    public function register(array $data): User
    {
        return DB::transaction(function () use ($data) {
            // 1. Create User
            $user = User::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'phone' => $data['phone'],
                'password' => $data['password'],
                'is_active' => true,
                'terms_accepted_at' => now(),
                'privacy_accepted_at' => now(),
            ]);

            // 2. Generate Registration Number using actual year and padded user id
            $year = now()->format('Y');
            $paddedId = str_pad((string) $user->id, 6, '0', STR_PAD_LEFT);
            $registrationNumber = "PLM-{$year}-{$paddedId}";

            // 3. Update User with Registration Number
            $user->registration_number = $registrationNumber;
            $user->save();

            // 4. Create Applicant Profile with NIK
            ApplicantProfile::create([
                'user_id' => $user->id,
                'nik' => $data['nik'],
                'same_as_ktp' => false,
            ]);

            return $user->fresh(['applicantProfile']);
        });
    }
}
