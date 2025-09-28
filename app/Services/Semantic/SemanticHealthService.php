<?php

namespace App\Services\Semantic;

use App\Models\CreativeProfile;
use App\Models\EmbeddingCache;
use App\Models\Job;
use App\Services\Bedrock\BedrockService;
use App\Services\Pinecone\PineconeService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class SemanticHealthService
{
    public function __construct(
        private readonly BedrockService $bedrock,
        private readonly PineconeService $pinecone,
    ) {
    }

    /**
     * Provide an at-a-glance snapshot of the semantic search pipeline.
     *
     * @return array<string, mixed>
     */
    public function snapshot(): array
    {
        $queueDefault = config('queue.default');
        $queueConnection = config(sprintf('queue.connections.%s', $queueDefault), []);
        $queueDriver = $queueConnection['driver'] ?? null;

        $queueTable = $queueConnection['table'] ?? 'jobs';
        $failedTable = config('queue.failed.table', 'failed_jobs');

        $queueTablesAvailable = $queueDriver === 'database'
            && Schema::hasTable($queueTable)
            && Schema::hasTable($failedTable);

        $pendingJobs = $queueTablesAvailable ? DB::table($queueTable)->count() : null;
        $failedJobs = $queueTablesAvailable ? DB::table($failedTable)->count() : null;

        $latestJobEmbedding = EmbeddingCache::query()
            ->where('entity_type', Job::class)
            ->latest('generated_at')
            ->first();

        $latestProfileEmbedding = EmbeddingCache::query()
            ->where('entity_type', CreativeProfile::class)
            ->latest('generated_at')
            ->first();

        $totalEmbeddings = EmbeddingCache::count();

        $recommendations = [];

        if (! $this->bedrock->isEnabled()) {
            $recommendations[] = 'Enable AWS Bedrock to generate fresh embeddings instead of fallback vectors.';
        }

        if (! $this->pinecone->isEnabled()) {
            $recommendations[] = 'Connect Pinecone or enable simulation mode only for local development.';
        } elseif ($this->pinecone->shouldSimulate()) {
            $recommendations[] = 'Pinecone is running in simulation mode; disable simulation in production environments.';
        }

        if (! $queueTablesAvailable) {
            $recommendations[] = 'Queue metrics unavailable; ensure the database queue tables exist or configure a supported queue driver.';
        }

        if ($totalEmbeddings === 0) {
            $recommendations[] = 'Run "php artisan semantic:rebuild" to seed embeddings for jobs and creative profiles.';
        }

        return [
            'bedrock' => [
                'enabled' => $this->bedrock->isEnabled(),
            ],
            'pinecone' => [
                'enabled' => $this->pinecone->isEnabled(),
                'simulate' => $this->pinecone->shouldSimulate(),
            ],
            'queue' => [
                'driver' => $queueDriver,
                'connection' => $queueDefault,
                'supports_counts' => $queueTablesAvailable,
                'pending_jobs' => $pendingJobs,
                'failed_jobs' => $failedJobs,
            ],
            'embeddings' => [
                'latest_job_generated_at' => optional($latestJobEmbedding?->generated_at)->toIso8601String(),
                'latest_profile_generated_at' => optional($latestProfileEmbedding?->generated_at)->toIso8601String(),
                'total_cached_records' => $totalEmbeddings,
            ],
            'recommendations' => $recommendations,
        ];
    }
}
