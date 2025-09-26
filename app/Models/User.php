<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Fortify\TwoFactorAuthenticatable;

class User extends Authenticatable
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
        ];
    }

    /**
     * Get the creative profile associated with the user.
     */
    public function creativeProfile()
    {
        return $this->hasOne(CreativeProfile::class);
    }

    /**
     * Get the opportunity owner profile associated with the user.
     */
    public function opportunityOwnerProfile()
    {
        return $this->hasOne(OpportunityOwnerProfile::class);
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
