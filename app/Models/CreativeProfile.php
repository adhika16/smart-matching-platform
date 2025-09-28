<?php

namespace App\Models;

use App\Jobs\SyncCreativeProfileEmbeddings;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CreativeProfile extends Model
{
    use HasFactory;

    protected static function booted(): void
    {
        static::saved(function (CreativeProfile $profile): void {
            SyncCreativeProfileEmbeddings::dispatch($profile->id, false)->afterCommit();
        });
    }

    protected $fillable = [
        'user_id',
        'bio',
        'skills',
        'portfolio_links',
        'location',
        'hourly_rate',
        'experience_level',
        'available_for_work',
    ];

    protected $casts = [
        'skills' => 'array',
        'portfolio_links' => 'array',
        'hourly_rate' => 'decimal:2',
        'available_for_work' => 'boolean',
    ];

    /**
     * Get the user that owns the creative profile.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Calculate profile completion percentage.
     */
    public function getCompletionPercentage(): int
    {
        $fields = [
            'bio' => $this->bio,
            'skills' => $this->skills,
            'portfolio_links' => $this->portfolio_links,
            'location' => $this->location,
            'hourly_rate' => $this->hourly_rate,
            'experience_level' => $this->experience_level,
        ];

        $completedFields = array_filter($fields, function ($value) {
            return !empty($value);
        });

        return (int) ((count($completedFields) / count($fields)) * 100);
    }

    /**
     * Check if profile is complete.
     */
    public function isComplete(): bool
    {
        return $this->getCompletionPercentage() >= 80;
    }
}
