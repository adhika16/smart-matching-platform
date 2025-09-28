<?php

namespace App\Jobs;

use App\Models\CreativeProfile;
use App\Models\EmbeddingCache;
use App\Services\Embedding\EmbeddingVectorizer;
use App\Services\Pinecone\PineconeService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SyncCreativeProfileEmbeddings implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public function __construct(
        public readonly int $profileId,
        public readonly bool $force = false,
    ) {
    }

    public function handle(PineconeService $pinecone, EmbeddingVectorizer $vectorizer): void
    {
        $profile = CreativeProfile::with('user')->find($this->profileId);

        if (! $profile) {
            return;
        }

        $corpus = $this->buildCorpus($profile);
        $vector = $vectorizer->embed($corpus);

        $modelVersion = config('bedrock.embeddings.model_id') ?? 'fallback';

        EmbeddingCache::updateOrCreate(
            [
                'entity_type' => CreativeProfile::class,
                'entity_id' => $profile->id,
                'model_version' => sprintf('bedrock:%s', $modelVersion),
            ],
            [
                'vector_data' => $vector,
                'dimension' => count($vector),
                'generated_at' => now(),
            ]
        );

        $pinecone->upsertVectors([
            [
                'id' => $this->vectorId($profile->id),
                'values' => $vector,
                'metadata' => [
                    'entity_type' => 'creative_profile',
                    'profile_id' => $profile->id,
                    'user_id' => $profile->user_id,
                    'skills' => $profile->skills,
                    'location' => $profile->location,
                    'experience_level' => $profile->experience_level,
                ],
            ],
        ], namespace: $pinecone->indexNamespace());
    }

    private function buildCorpus(CreativeProfile $profile): string
    {
        $sections = [
            $profile->user?->name,
            $profile->bio,
            implode(', ', $profile->skills ?? []),
            implode(', ', $profile->portfolio_links ?? []),
            $profile->experience_level,
            $profile->location,
        ];

        return trim(collect($sections)
            ->filter(fn ($value) => filled($value))
            ->implode('\n'));
    }

    private function vectorId(int $profileId): string
    {
        return 'creative_profile::'.$profileId;
    }
}
