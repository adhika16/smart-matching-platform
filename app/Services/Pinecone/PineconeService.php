<?php

namespace App\Services\Pinecone;

use Illuminate\Support\Arr;
use Probots\Pinecone\Client as PineconeClient;
use Psr\Log\LoggerInterface;

class PineconeService
{
    public function __construct(
        private readonly PineconeClient $client,
        private readonly LoggerInterface $logger,
    ) {
    }

    public function isEnabled(): bool
    {
        return (bool) config('pinecone.enabled', false);
    }

    public function shouldSimulate(): bool
    {
        return (bool) config('pinecone.simulate', false);
    }

    public function indexName(): string
    {
        return (string) config('pinecone.index', 'creative-matching');
    }

    public function indexNamespace(): string
    {
        return (string) config('pinecone.namespace', 'default');
    }

    public function embedDimension(): int
    {
        return (int) config('pinecone.dimension', 1024);
    }

    /**
     * Create the Pinecone index if it does not exist.
     */
    public function createIndex(?array $overrides = null): bool
    {
        if ($this->shouldSimulate()) {
            $this->logger->info('Pinecone simulation: Skipping index creation.');

            return true;
        }

        if (! $this->isEnabled()) {
            return false;
        }

        $indexName = $this->indexName();
        $response = $this->client->control()->index($indexName)->describe()->json();

        if ($response['status']['ready']) {
            return true;
        }

        $options = array_merge([
            'dimension' => $this->embedDimension(),
            'metric' => config('pinecone.metric', 'cosine'),
            'pod_type' => config('pinecone.pod_type', 'p1.x1'),
        ], $overrides ?? []);

        try {
            $this->client->control()->index($indexName)->createPod(
                dimension: $options['dimension'],
                metric: $options['metric'],
                pod_type: $options['pod_type']
            );
        } catch (\Exception $e) {
            $this->logger->warning('Pinecone index creation failed.', [
                'message' => $e->getMessage(),
            ]);

            return false;
        }

        return true;
    }

    /**
     * @param  array<int, array{id: string, values: array<int, float>, metadata?: array<string, mixed>}>  $vectors
     */
    public function upsertVectors(array $vectors, ?string $namespace = null): bool
    {
        if (empty($vectors)) {
            return true;
        }

        if ($this->shouldSimulate() || ! $this->isEnabled()) {
            $this->logger->info('Pinecone simulation: Skipping vector upsert.', ['vector_count' => count($vectors)]);

            return true;
        }

        try {
            $this->client
                ->data()
                ->vectors()
                ->upsert($vectors);
        } catch (\Exception $e) {
            $this->logger->error('Pinecone vector upsert failed.', [
                'message' => $e->getMessage(),
            ]);

            return false;
        }

        return true;
    }

    /**
     * @param  array<string, mixed>  $options
     * @return array<string, mixed>
     */
    public function queryVectors(array $vector, array $options = []): array
    {
        if ($this->shouldSimulate() || ! $this->isEnabled()) {
            return [];
        }

        try {
            $response = $this->client
                ->data()
                ->vectors()
                ->query(
                    vector: $vector,
                    topK: Arr::get($options, 'topK', 10),
                    includeMetadata: Arr::get($options, 'includeMetadata', true),
                    includeValues: Arr::get($options, 'includeValues', false),
                    filter: Arr::get($options, 'filter', [])
                );

            return $response->json();
        } catch (\Exception $e) {
            $this->logger->error('Pinecone vector query failed.', [
                'message' => $e->getMessage(),
            ]);
        }

        return [];
    }

    /**
     * @param  array<int, string>  $ids
     */
    public function deleteVectors(array $ids, ?string $namespace = null): bool
    {
        if (empty($ids)) {
            return true;
        }

        if ($this->shouldSimulate() || ! $this->isEnabled()) {
            $this->logger->info('Pinecone simulation: Skipping vector deletion.', ['id_count' => count($ids)]);

            return true;
        }

        try {
            $this->client
                ->data()
                ->vectors()
                ->delete($ids);
        } catch (\Exception $e) {
            $this->logger->error('Pinecone vector deletion failed.', [
                'message' => $e->getMessage(),
            ]);

            return false;
        }

        return true;
    }
}
