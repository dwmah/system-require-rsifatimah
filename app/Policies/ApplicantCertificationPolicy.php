<?php

namespace App\Policies;

use App\Models\ApplicantCertification;
use App\Models\User;

class ApplicantCertificationPolicy
{
    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, ApplicantCertification $certification): bool
    {
        return $certification->applicantProfile?->user_id === $user->id;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, ApplicantCertification $certification): bool
    {
        return $certification->applicantProfile?->user_id === $user->id;
    }
}
