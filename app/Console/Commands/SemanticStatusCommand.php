<?php

namespace App\Console\Commands;

use App\Services\Semantic\SemanticHealthService;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

class SemanticStatusCommand extends Command
{
    protected $signature = 'semantic:status';

    protected $description = 'Display semantic search health, queue status, and embedding freshness.';

    public function __construct(
        private readonly SemanticHealthService $healthService,
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        $this->line('Semantic Search Status');
        $this->line('=======================');

        $snapshot = $this->healthService->snapshot();

        $this->info(sprintf('Bedrock enabled: %s', $snapshot['bedrock']['enabled'] ? 'yes' : 'no'));
        $this->info(sprintf('Pinecone enabled: %s', $snapshot['pinecone']['enabled'] ? 'yes' : 'no'));
        $this->info(sprintf('Pinecone simulate mode: %s', $snapshot['pinecone']['simulate'] ? 'yes' : 'no'));

        $this->line('');
        $this->info('Queue metrics');

        $pending = $snapshot['queue']['supports_counts']
            ? (string) ($snapshot['queue']['pending_jobs'] ?? 0)
            : 'n/a';

        $failed = $snapshot['queue']['supports_counts']
            ? (string) ($snapshot['queue']['failed_jobs'] ?? 0)
            : 'n/a';

        $this->line(sprintf('Queue driver: %s', $snapshot['queue']['driver'] ?? 'unknown'));
        $this->line(sprintf('Pending jobs: %s', $pending));
        $this->line(sprintf('Failed jobs: %s', $failed));

        $this->line('');
        $this->info('Embedding freshness');

        $jobFreshness = $this->formatRelativeTime($snapshot['embeddings']['latest_job_generated_at']);
        $creativeFreshness = $this->formatRelativeTime($snapshot['embeddings']['latest_profile_generated_at']);

        $this->line(sprintf('Latest job embedding: %s', $jobFreshness));
        $this->line(sprintf('Latest creative profile embedding: %s', $creativeFreshness));
        $this->line(sprintf('Cached embeddings: %d', $snapshot['embeddings']['total_cached_records']));

        if (! $snapshot['pinecone']['enabled']) {
            $this->warn('Pinecone is disabled. Semantic search will fall back to local embedding cache.');
        }

        if (! $snapshot['bedrock']['enabled']) {
            $this->warn('Bedrock is disabled. Using deterministic fallback vectors.');
        }

        foreach ($snapshot['recommendations'] as $recommendation) {
            $this->line(sprintf('- %s', $recommendation));
        }

        return self::SUCCESS;
    }

    private function formatRelativeTime(?string $timestamp): string
    {
        if ($timestamp === null) {
            return 'never';
        }

        return Carbon::parse($timestamp)->diffForHumans();
    }
}
