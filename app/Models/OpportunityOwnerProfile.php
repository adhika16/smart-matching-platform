<?php

namespace App\Models;

use App\Models\OpportunityOwnerVerificationLog;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class OpportunityOwnerProfile extends Model
{
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $appends = [];

    protected $fillable = [
        'user_id',
        'company_name',
        'company_description',
        'company_website',
        'company_size',
        'industry',
        'is_verified',
        'verified_at',
        'verification_documents',
    ];

    protected $casts = [
        'is_verified' => 'boolean',
        'verified_at' => 'datetime',
        'verification_documents' => 'array',
    ];

    /**
     * Get the user that owns the opportunity owner profile.
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
            'company_name' => $this->company_name,
            'company_description' => $this->company_description,
            'company_website' => $this->company_website,
            'company_size' => $this->company_size,
            'industry' => $this->industry,
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

    /**
     * Mark the profile as verified.
     */
    public function markAsVerified(): void
    {
        $this->update([
            'is_verified' => true,
            'verified_at' => now(),
        ]);
    }

    /**
     * Verification audit logs associated with the profile.
     */
    public function verificationLogs(): HasMany
    {
        return $this->hasMany(OpportunityOwnerVerificationLog::class)->latest();
    }
}
