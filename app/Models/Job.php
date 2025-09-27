<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Job extends Model
{
    use HasFactory;

    protected $table = 'job_postings';

    protected $fillable = [
        'user_id',
        'title',
        'slug',
        'location',
        'is_remote',
        'status',
        'compensation_type',
        'compensation_min',
        'compensation_max',
        'tags',
        'summary',
        'description',
        'published_at',
    ];

    protected $casts = [
        'is_remote' => 'boolean',
        'tags' => 'array',
        'compensation_min' => 'decimal:2',
        'compensation_max' => 'decimal:2',
        'published_at' => 'datetime',
    ];

    public const STATUS_DRAFT = 'draft';
    public const STATUS_PUBLISHED = 'published';
    public const STATUS_ARCHIVED = 'archived';

    /**
     * Get the opportunity owner who created the job.
     */
    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Maintain compatibility with factory helpers expecting a user relationship.
     */
    public function user(): BelongsTo
    {
        return $this->owner();
    }

    /**
     * Applications submitted to this job.
     */
    public function applications(): HasMany
    {
        return $this->hasMany(Application::class);
    }

    /**
     * Determine if the job is published.
     */
    public function isPublished(): bool
    {
        return $this->status === self::STATUS_PUBLISHED;
    }

    /**
     * Generate a unique slug for the job based on title.
     */
    public static function generateSlug(string $title): string
    {
        return Str::slug($title) . '-' . Str::random(6);
    }
}
