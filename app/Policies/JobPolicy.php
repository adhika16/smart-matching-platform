<?php

namespace App\Policies;

use App\Models\Job;
use App\Models\User;

class JobPolicy
{
    /**
     * Determine whether the user can view any jobs.
     */
    public function viewAny(?User $user): bool
    {
        if (! $user) {
            return false;
        }

        return $user->isOpportunityOwner() && $user->opportunityOwnerProfile?->is_verified;
    }

    /**
     * Determine whether the user can view the job.
     */
    public function view(?User $user, Job $job): bool
    {
        if ($user && $user->id === $job->user_id) {
            return true;
        }

        return $job->status === Job::STATUS_PUBLISHED;
    }

    /**
     * Determine whether the user can create jobs.
     */
    public function create(User $user): bool
    {
        return $user->isOpportunityOwner() && $user->opportunityOwnerProfile?->is_verified;
    }

    /**
     * Determine whether the user can update the job.
     */
    public function update(User $user, Job $job): bool
    {
        return $this->ownsJob($user, $job) && $this->isVerifiedOpportunityOwner($user);
    }

    /**
     * Determine whether the user can delete the job.
     */
    public function delete(User $user, Job $job): bool
    {
        return $this->ownsJob($user, $job) && $this->isVerifiedOpportunityOwner($user);
    }

    /**
     * Determine whether the user can publish the job.
     */
    public function publish(User $user, Job $job): bool
    {
        return $this->ownsJob($user, $job) && $this->isVerifiedOpportunityOwner($user);
    }

    /**
     * Determine whether the user can archive the job.
     */
    public function archive(User $user, Job $job): bool
    {
        return $this->ownsJob($user, $job) && $this->isVerifiedOpportunityOwner($user);
    }

    protected function ownsJob(User $user, Job $job): bool
    {
        return $user->id === $job->user_id;
    }

    protected function isVerifiedOpportunityOwner(User $user): bool
    {
        return $user->isOpportunityOwner() && $user->opportunityOwnerProfile?->is_verified;
    }
}
