<?php

namespace App\Policies;

use App\Models\Application;
use App\Models\Job;
use App\Models\User;

class ApplicationPolicy
{
    /**
     * Determine whether the creative can apply to a job.
     */
    public function create(User $user, Job $job): bool
    {
        if (! $user->isCreative()) {
            return false;
        }

        if ($job->status !== Job::STATUS_PUBLISHED) {
            return false;
        }

        return $job->user_id !== $user->id;
    }

    /**
     * Determine whether the opportunity owner can update an application.
     */
    public function update(User $user, Application $application): bool
    {
        if (! $user->isOpportunityOwner()) {
            return false;
        }

        $jobOwnerId = $application->job?->user_id ?? $application->job()->value('user_id');

        return $jobOwnerId === $user->id;
    }
}
