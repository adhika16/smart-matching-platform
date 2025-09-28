<?php

namespace App\Jobs;

use App\Models\EmbeddingCache;
use App\Models\Job;
use App\Services\Embedding\EmbeddingVectorizer;
use App\Services\Pinecone\PineconeService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SyncJobEmbeddings implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public function __construct(
        public readonly int $jobId,
        public readonly bool $force = false,
    ) {
    }

    public function handle(PineconeService $pinecone, EmbeddingVectorizer $vectorizer): void
    {
        $job = Job::find($this->jobId);

        if (! $job) {
            return;
        }

        if (! $this->force && ! $job->shouldBeSearchable()) {
            $pinecone->deleteVectors([$this->vectorId($job->id)], namespace: $pinecone->indexNamespace());
            EmbeddingCache::forEntity(Job::class, $job->id)->delete();

            return;
        }

        $corpus = $this->buildCorpus($job);
        $vector = $vectorizer->embed($corpus);

        $modelVersion = config('bedrock.embeddings.model_id') ?? 'fallback';

        EmbeddingCache::updateOrCreate(
            [
                'entity_type' => Job::class,
                'entity_id' => $job->id,
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
                'id' => $this->vectorId($job->id),
                'values' => $vector,
                'metadata' => [
                    'entity_type' => 'job',
                    'job_id' => $job->id,
                    'status' => $job->status,
                    'category' => $job->category,
                    'skills' => $job->skills,
                    'title' => $job->title,
                ],
            ],
        ], namespace: $pinecone->indexNamespace());
    }

    private function buildCorpus(Job $job): string
    {
        $sections = [
            $job->title,
            $job->summary,
            strip_tags((string) $job->description),
            $job->category,
            implode(', ', $job->skills ?? []),
            implode(', ', $job->tags ?? []),
            $job->location,
        ];

        return trim(collect($sections)
            ->filter(fn ($value) => filled($value))
            ->implode('\n'));
    }

    private function vectorId(int $jobId): string
    {
        return 'job::'.$jobId;
    }
}
