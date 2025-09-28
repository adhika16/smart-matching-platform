<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property string $entity_type
 * @property int $entity_id
 * @property array $vector_data
 * @property string|null $model_version
 * @property int $dimension
 * @property \Illuminate\Support\Carbon|null $generated_at
 */
class EmbeddingCache extends Model
{
    use HasFactory;

    protected $table = 'embeddings_cache';

    protected $guarded = [];

    protected $casts = [
        'vector_data' => 'array',
        'generated_at' => 'datetime',
    ];

    public static function forEntity(string $entityType, int $entityId): Builder
    {
        return static::query()->where([
            'entity_type' => $entityType,
            'entity_id' => $entityId,
        ]);
    }
}
