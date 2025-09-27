<?php

namespace App\Services\Bedrock;

use App\Services\Bedrock\Exceptions\BedrockDisabledException;
use App\Services\Bedrock\Exceptions\BedrockException;
use Aws\BedrockRuntime\BedrockRuntimeClient;
use Aws\Exception\AwsException;
use Illuminate\Support\Arr;
use Psr\Log\LoggerInterface;
use Psr\Http\Message\StreamInterface;

class BedrockService
{
    /**
     * @param  array<string, mixed>  $config
     */
    public function __construct(
        private readonly array $config,
        private readonly ?BedrockRuntimeClient $client,
        private readonly LoggerInterface $logger,
    ) {
    }

    public function isEnabled(): bool
    {
        return (bool) ($this->config['enabled'] ?? false) && $this->client instanceof BedrockRuntimeClient;
    }

    /**
     * @return array<int, float>
     *
     * @throws BedrockException
     */
    public function generateEmbeddings(string $text): array
    {
        $this->guardEnabled();

        $payload = [
            'modelId' => Arr::get($this->config, 'embeddings.model_id'),
            'body' => json_encode([
                'inputText' => $text,
            ], JSON_THROW_ON_ERROR),
            'accept' => 'application/json',
            'contentType' => 'application/json',
        ];

        try {
            $result = $this->client->invokeModel($payload);
            $content = $this->getResponseBody($result->get('body'));
            $decoded = json_decode($content, true, 512, JSON_THROW_ON_ERROR);

            $embedding = Arr::get($decoded, 'embedding');

            return is_array($embedding) ? array_map(fn ($value) => (float) $value, $embedding) : [];
        } catch (AwsException|\JsonException $exception) {
            $this->logger->error('Bedrock embedding request failed.', [
                'exception' => $exception,
            ]);

            throw new BedrockException('Unable to generate embeddings via Bedrock.', 0, $exception);
        }
    }

    /**
     * @param  array<string, mixed>  $requirements
     *
     * @throws BedrockException
     */
    public function generateProjectDescription(array $requirements): string
    {
        $prompt = $this->buildProjectPrompt($requirements);

        return $this->generateTextResponse($prompt);
    }

    /**
     * @param  array<string, mixed>  $profileData
     *
     * @throws BedrockException
     */
    public function enhanceProfileMetadata(array $profileData): string
    {
        $prompt = $this->buildProfilePrompt($profileData);

        return $this->generateTextResponse($prompt);
    }

    /**
     * @throws BedrockDisabledException
     */
    private function guardEnabled(): void
    {
        if (! $this->isEnabled()) {
            throw new BedrockDisabledException('Bedrock integration is disabled. Update configuration to enable it.');
        }
    }

    /**
     * @throws BedrockException
     */
    private function generateTextResponse(string $prompt): string
    {
        $this->guardEnabled();

        $payload = [
            'modelId' => Arr::get($this->config, 'content.model_id'),
            'body' => json_encode([
                'anthropic_version' => 'bedrock-2023-05-31',
                'messages' => [
                    [
                        'role' => 'user',
                        'content' => [
                            [
                                'type' => 'text',
                                'text' => $prompt,
                            ],
                        ],
                    ],
                ],
                'max_tokens' => Arr::get($this->config, 'content.max_tokens', 800),
                'temperature' => Arr::get($this->config, 'content.temperature', 0.3),
            ], JSON_THROW_ON_ERROR),
            'accept' => 'application/json',
            'contentType' => 'application/json',
        ];

        try {
            $result = $this->client->invokeModel($payload);
            $content = $this->getResponseBody($result->get('body'));
            $decoded = json_decode($content, true, 512, JSON_THROW_ON_ERROR);
            $message = Arr::get($decoded, 'content.0.text');

            return is_string($message) ? trim($message) : '';
        } catch (AwsException|\JsonException $exception) {
            $this->logger->error('Bedrock content generation failed.', [
                'exception' => $exception,
            ]);

            throw new BedrockException('Unable to generate content via Bedrock.', 0, $exception);
        }
    }

    private function buildProjectPrompt(array $requirements): string
    {
        $lines = [
            'Generate a concise, compelling project description for a creative opportunity using the details provided below. '
            .'Highlight the most relevant information for potential applicants.',
            '',
        ];

        foreach ($requirements as $key => $value) {
            if (blank($value)) {
                continue;
            }

            $label = ucfirst(str_replace('_', ' ', (string) $key));
            $lines[] = sprintf('%s: %s', $label, is_array($value) ? implode(', ', $value) : $value);
        }

        $lines[] = '';
        $lines[] = 'Return only the improved project description text.';

        return implode(PHP_EOL, $lines);
    }

    private function buildProfilePrompt(array $profileData): string
    {
        $lines = [
            'Enhance this company profile for a creative opportunity marketplace. '
            .'Summarize the company, its strengths, and what creatives should know in 2-3 short paragraphs.',
            '',
        ];

        foreach ($profileData as $key => $value) {
            if (blank($value)) {
                continue;
            }

            $label = ucfirst(str_replace('_', ' ', (string) $key));
            $lines[] = sprintf('%s: %s', $label, is_array($value) ? implode(', ', $value) : $value);
        }

        $lines[] = '';
        $lines[] = 'Return only the enhanced profile summary text suitable for display to creatives.';

        return implode(PHP_EOL, $lines);
    }

    private function getResponseBody(mixed $body): string
    {
        if ($body instanceof StreamInterface) {
            return $body->getContents();
        }

        if (is_resource($body)) {
            return stream_get_contents($body) ?: '';
        }

        return (string) $body;
    }
}
