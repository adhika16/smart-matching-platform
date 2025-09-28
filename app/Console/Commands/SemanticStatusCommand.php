<?php

namespace App\Console\Commands;

use App\Models\CreativeProfile;
use App\Models\EmbeddingCache;
use App\Models\Job;
use App\Services\Bedrock\BedrockService;
use App\Services\Pinecone\PineconeService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class SemanticStatusCommand extends Command
{
    protected $signature = 'semantic:status';

    protected $description = 'Display semantic search health, queue status, and embedding freshness.';

    public function __construct(
        private readonly BedrockService $bedrock,
        private readonly PineconeService $pinecone,
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        $this->line('Semantic Search Status');
        $this->line('=======================');

        $this->info(sprintf('Bedrock enabled: %s', $this->bedrock->isEnabled() ? 'yes' : 'no'));
        $this->info(sprintf('Pinecone enabled: %s', $this->pinecone->isEnabled() ? 'yes' : 'no'));
        $this->info(sprintf('Pinecone simulate mode: %s', $this->pinecone->shouldSimulate() ? 'yes' : 'no'));

        $queueConnection = config('queue.connections.'.config('queue.default'), []);
        $queueDriver = $queueConnection['driver'] ?? null;
        $pendingJobs = 0;
        $failedJobs = 0;

        if ($queueDriver === 'database') {
            $queueTable = $queueConnection['table'] ?? 'jobs';
            $failedTable = config('queue.failed.table', 'failed_jobs');

            $pendingJobs = DB::table($queueTable)->count();
            $failedJobs = DB::table($failedTable)->count();
        }

        $this->line('');
        $this->info('Queue metrics');
        $this->line(sprintf('Pending jobs: %d', $pendingJobs));
        $this->line(sprintf('Failed jobs: %d', $failedJobs));

        $latestJobEmbedding = EmbeddingCache::query()
            ->where('entity_type', Job::class)
            ->latest('generated_at')
            ->first();

        $latestProfileEmbedding = EmbeddingCache::query()
            ->where('entity_type', CreativeProfile::class)
            ->latest('generated_at')
            ->first();

        $this->line('');
        $this->info('Embedding freshness');
    $jobFreshness = optional($latestJobEmbedding?->generated_at)->diffForHumans() ?? 'never';
    $profileFreshness = optional($latestProfileEmbedding?->generated_at)->diffForHumans() ?? 'never';

    $this->line(sprintf('Latest job embedding: %s', $jobFreshness));
    $this->line(sprintf('Latest creative profile embedding: %s', $profileFreshness));

        if (! $this->pinecone->isEnabled()) {
            $this->warn('Pinecone is disabled. Semantic search will fall back to local embedding cache.');
        }

        if (! $this->bedrock->isEnabled()) {
            $this->warn('Bedrock is disabled. Using deterministic fallback vectors.');
        }

        return self::SUCCESS;
    }
}
