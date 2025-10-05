<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Fortify\TwoFactorAuthenticatable;

class User extends Authenticatable implements MustVerifyEmail
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, TwoFactorAuthenticatable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'user_type',
        'is_admin',
        'email_verified_at',
        'profile_completed_at',
        'profile_completion_score',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'profile_completed_at' => 'datetime',
            'is_admin' => 'boolean',
        ];
    }

    /**
     * Get the creative profile associated with the user.
     */
    public function creativeProfile(): HasOne
    {
        return $this->hasOne(CreativeProfile::class);
    }

    /**
     * Get the opportunity owner profile associated with the user.
     */
    public function opportunityOwnerProfile(): HasOne
    {
        return $this->hasOne(OpportunityOwnerProfile::class);
    }

    /**
     * Get the jobs posted by the opportunity owner.
     */
    public function jobs(): HasMany
    {
        return $this->hasMany(Job::class);
    }

    /**
     * Applications submitted by the user as a creative.
     */
    public function applications(): HasMany
    {
        return $this->hasMany(Application::class);
    }

    /**
     * Applications received across the user's job postings.
     */
    public function receivedApplications(): HasManyThrough
    {
        return $this->hasManyThrough(Application::class, Job::class, 'user_id', 'job_id');
    }

    /**
     * Check if user is a creative.
     */
    public function isCreative(): bool
    {
        return $this->user_type === 'creative';
    }

    /**
     * Check if user is an opportunity owner.
     */
    public function isOpportunityOwner(): bool
    {
        return $this->user_type === 'opportunity_owner';
    }

    /**
     * Determine if the user has administrator privileges.
     */
    public function isAdmin(): bool
    {
        return (bool) $this->is_admin;
    }

    /**
     * Get the active profile based on user type.
     */
    public function getActiveProfile()
    {
        return $this->isCreative()
            ? $this->creativeProfile
            : $this->opportunityOwnerProfile;
    }

    /**
     * Update profile completion score.
     */
    public function updateProfileCompletionScore(): void
    {
        $profile = $this->getActiveProfile();

        if ($profile) {
            $score = $profile->getCompletionPercentage();
            $this->update([
                'profile_completion_score' => $score,
                'profile_completed_at' => $score >= 80 ? now() : null,
            ]);
        }
    }
}
