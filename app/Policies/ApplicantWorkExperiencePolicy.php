<?php

namespace App\Policies;

use App\Models\ApplicantWorkExperience;
use App\Models\User;

class ApplicantWorkExperiencePolicy
{
    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, ApplicantWorkExperience $workExperience): bool
    {
        return $workExperience->applicantProfile?->user_id === $user->id;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, ApplicantWorkExperience $workExperience): bool
    {
        return $workExperience->applicantProfile?->user_id === $user->id;
    }
}
