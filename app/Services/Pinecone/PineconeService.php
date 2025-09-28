<?php

namespace App\Services\Pinecone;

use Illuminate\Http\Client\Factory as HttpFactory;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Psr\Log\LoggerInterface;

class PineconeService
{
    /**
     * @param  array<string, mixed>  $config
     */
    public function __construct(
        private readonly array $config,
        private readonly HttpFactory $http,
        private readonly LoggerInterface $logger,
    ) {
    }

    public function isEnabled(): bool
    {
        return (bool) ($this->config['enabled'] ?? false)
            && filled($this->config['api_key'] ?? null)
            && filled(Arr::get($this->config, 'index.host'))
            && ! $this->shouldSimulate();
    }

    public function shouldSimulate(): bool
    {
        return (bool) ($this->config['simulate'] ?? false);
    }

    public function indexName(): string
    {
        return (string) Arr::get($this->config, 'index.name', 'creative-matching');
    }

    public function indexNamespace(): string
    {
        return (string) Arr::get($this->config, 'index.namespace', 'default');
    }

    public function embedDimension(): int
    {
        return (int) Arr::get($this->config, 'index.dimension', 1536);
    }

    /**
     * Create the Pinecone index if it does not exist.
     */
    public function createIndex(?array $overrides = null): bool
    {
        if ($this->shouldSimulate()) {
            return true;
        }

        if (! $this->isEnabled()) {
            return false;
        }

        $payload = array_merge(
            [
                'name' => $this->indexName(),
                'dimension' => $this->embedDimension(),
                'metric' => Arr::get($this->config, 'index.metric', 'cosine'),
            ],
            $overrides ?? []
        );

        if ($podType = Arr::get($this->config, 'index.pod_type')) {
            $payload['pod_type'] = $podType;
        }

        /** @var Response $response */
        $response = $this->http->withHeaders($this->headers())
            ->withOptions(['timeout' => $this->timeout()])
            ->post($this->controllerEndpoint().'/databases', $payload);

        if ($response->successful() || $response->status() === 409) {
            return true;
        }

        $this->logger->warning('Pinecone index creation failed.', [
            'payload' => $payload,
            'status' => $response->status(),
            'body' => $response->json(),
        ]);

        return false;
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
            $this->logger->info('Skipping Pinecone upsert (simulation or disabled).', [
                'count' => count($vectors),
            ]);

            return true;
        }

        $endpoint = $this->indexEndpoint('/vectors/upsert');

        if ($endpoint === null) {
            return false;
        }

        $payload = [
            'vectors' => $vectors,
            'namespace' => $namespace ?? $this->indexNamespace(),
        ];

        $response = $this->http->withHeaders($this->headers())
            ->withOptions(['timeout' => $this->timeout()])
            ->post($endpoint, $payload);

        if (! $response->successful()) {
            $this->logger->error('Failed to upsert vectors to Pinecone.', [
                'status' => $response->status(),
                'body' => $response->json(),
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

        $endpoint = $this->indexEndpoint('/query');

        if ($endpoint === null) {
            return [];
        }

        $payload = array_merge([
            'vector' => $vector,
            'topK' => Arr::get($options, 'topK', 10),
            'namespace' => Arr::get($options, 'namespace', $this->indexNamespace()),
            'includeMetadata' => Arr::get($options, 'includeMetadata', true),
            'includeValues' => Arr::get($options, 'includeValues', false),
        ], Arr::except($options, ['topK', 'namespace', 'includeMetadata', 'includeValues']));

        $response = $this->http->withHeaders($this->headers())
            ->withOptions(['timeout' => $this->timeout()])
            ->post($endpoint, $payload);

        if (! $response->successful()) {
            $this->logger->error('Pinecone query failed.', [
                'status' => $response->status(),
                'body' => $response->json(),
            ]);

            return [];
        }

        return $response->json() ?? [];
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
            $this->logger->info('Skipping Pinecone delete (simulation or disabled).', [
                'count' => count($ids),
            ]);

            return true;
        }

        $endpoint = $this->indexEndpoint('/vectors/delete');

        if ($endpoint === null) {
            return false;
        }

        $payload = [
            'ids' => $ids,
            'namespace' => $namespace ?? $this->indexNamespace(),
        ];

        $response = $this->http->withHeaders($this->headers())
            ->withOptions(['timeout' => $this->timeout()])
            ->post($endpoint, $payload);

        if (! $response->successful()) {
            $this->logger->error('Failed to delete vectors from Pinecone.', [
                'status' => $response->status(),
                'body' => $response->json(),
            ]);

            return false;
        }

        return true;
    }

    private function headers(): array
    {
        return [
            'Api-Key' => (string) ($this->config['api_key'] ?? ''),
            'Content-Type' => 'application/json',
        ];
    }

    private function timeout(): int
    {
        return (int) ($this->config['timeout'] ?? 10);
    }

    private function indexEndpoint(string $path = ''): ?string
    {
        $host = Arr::get($this->config, 'index.host');

        if (blank($host)) {
            return null;
        }

        if (! Str::startsWith($host, ['http://', 'https://'])) {
            $host = 'https://'.$host;
        }

        return rtrim($host, '/').$path;
    }

    private function controllerEndpoint(): string
    {
        $environment = Arr::get($this->config, 'environment');

        return sprintf('https://controller.%s.pinecone.io', $environment ?: 'us-east1-gcp');
    }
}
