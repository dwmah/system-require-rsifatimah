<?php

namespace App\Policies;

use App\Models\JobApplication;
use App\Models\User;

class JobApplicationPolicy
{
    /**
     * Determine whether the user can view the job application.
     */
    public function view(User $user, JobApplication $application): bool
    {
        return $application->user_id === $user->id;
    }
}
