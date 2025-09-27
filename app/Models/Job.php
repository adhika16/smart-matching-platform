<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;
use Laravel\Scout\Searchable;

class Job extends Model
{
    use HasFactory;
    use Searchable;

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
        'category',
        'skills',
        'summary',
        'description',
        'published_at',
        'timeline_start',
        'timeline_end',
        'budget_min',
        'budget_max',
    ];

    protected $casts = [
        'is_remote' => 'boolean',
        'tags' => 'array',
        'skills' => 'array',
        'compensation_min' => 'decimal:2',
        'compensation_max' => 'decimal:2',
        'published_at' => 'datetime',
        'timeline_start' => 'date',
        'timeline_end' => 'date',
        'budget_min' => 'decimal:2',
        'budget_max' => 'decimal:2',
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

    /**
     * Determine if the job should be searchable.
     */
    public function shouldBeSearchable(): bool
    {
        return $this->status === self::STATUS_PUBLISHED;
    }

    /**
     * Convert the model instance to an array for search indexing.
     */
    public function toSearchableArray(): array
    {
        $this->loadMissing(['owner.opportunityOwnerProfile']);

        return [
            'id' => $this->id,
            'title' => $this->title,
            'status' => $this->status,
            'summary' => $this->summary,
            'description' => strip_tags($this->description),
            'location' => $this->location,
            'is_remote' => $this->is_remote,
            'tags' => $this->tags,
            'skills' => $this->skills,
            'category' => $this->category,
            'budget_min' => $this->budget_min,
            'budget_max' => $this->budget_max,
            'timeline_start' => optional($this->timeline_start)?->toDateString(),
            'timeline_end' => optional($this->timeline_end)?->toDateString(),
            'published_at' => optional($this->published_at)?->toDateTimeString(),
            'company' => $this->owner?->opportunityOwnerProfile?->company_name,
        ];
    }
}
