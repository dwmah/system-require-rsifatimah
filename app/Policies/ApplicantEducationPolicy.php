<?php

namespace App\Policies;

use App\Models\ApplicantEducation;
use App\Models\User;

class ApplicantEducationPolicy
{
    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, ApplicantEducation $education): bool
    {
        return $education->applicantProfile?->user_id === $user->id;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, ApplicantEducation $education): bool
    {
        return $education->applicantProfile?->user_id === $user->id;
    }
}
