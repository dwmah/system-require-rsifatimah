<?php

namespace App\Policies;

use App\Enums\JobApplicationStatus;
use App\Models\ApplicationDocument;
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

    /**
     * Determine whether the user can update the job application (upload/replace docs).
     */
    public function update(User $user, JobApplication $application): bool
    {
        return $application->user_id === $user->id
            && $application->status === JobApplicationStatus::DRAFT
            && $application->locked_at === null;
    }

    /**
     * Determine whether the user can delete documents from the job application.
     */
    public function delete(User $user, JobApplication $application): bool
    {
        return $this->update($user, $application);
    }

    /**
     * Determine whether the user can download a document belonging to this application.
     */
    public function downloadDocument(User $user, JobApplication $application, ApplicationDocument $document): bool
    {
        return $application->user_id === $user->id
            && $document->job_application_id === $application->id;
    }
}
