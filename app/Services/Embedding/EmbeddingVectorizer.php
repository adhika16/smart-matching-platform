<?php

namespace App\Services\Embedding;

use App\Services\Bedrock\BedrockService;
use App\Services\Bedrock\Exceptions\BedrockDisabledException;
use App\Services\Bedrock\Exceptions\BedrockException;
use App\Services\Pinecone\PineconeService;
use Psr\Log\LoggerInterface;

class EmbeddingVectorizer
{
    public function __construct(
        private readonly BedrockService $bedrock,
        private readonly PineconeService $pinecone,
        private readonly LoggerInterface $logger,
    ) {
    }

    /**
     * Generate an embedding for the provided text using Bedrock when enabled,
     * falling back to a deterministic pseudo-vector when Bedrock is disabled.
     *
     * @return array<int, float>
     */
    public function embed(string $text, ?int $dimension = null): array
    {
        $dimension ??= $this->pinecone->embedDimension();

        if ($dimension <= 0) {
            $dimension = 1024;
        }

        $text = trim($text);

        if ($text === '') {
            return $this->fallbackEmbedding($text, $dimension);
        }

        try {
            if ($this->bedrock->isEnabled()) {
                $vector = $this->bedrock->generateEmbeddings($text);

                if (! empty($vector)) {
                    return $this->fitDimension($vector, $dimension);
                }
            }
        } catch (BedrockDisabledException|BedrockException $exception) {
            $this->logger->warning('Bedrock embedding generation failed; using fallback vector.', [
                'message' => $exception->getMessage(),
            ]);
        }

        return $this->fallbackEmbedding($text, $dimension);
    }

    /**
     * @return array<int, float>
     */
    private function fallbackEmbedding(string $text, int $dimension): array
    {
        $hashSeed = $text !== '' ? $text : 'semantic-empty';
        $vector = [];

        for ($i = 0; $i < $dimension; $i++) {
            $hash = hash('sha256', $hashSeed.'|'.$i);
            $chunk = substr($hash, 0, 16);
            $intValue = hexdec($chunk);
            $vector[] = $intValue / 0xffffffffffffffff;
        }

        return $this->normalize($vector);
    }

    /**
     * @param  array<int, float|int>  $vector
     * @return array<int, float>
     */
    private function fitDimension(array $vector, int $dimension): array
    {
        $vector = array_values(array_map(static fn ($value) => (float) $value, $vector));
        $count = count($vector);

        if ($count === $dimension) {
            return $this->normalize($vector);
        }

        if ($count > $dimension) {
            $vector = array_slice($vector, 0, $dimension);

            return $this->normalize($vector);
        }

        while (count($vector) < $dimension) {
            $vector[] = 0.0;
        }

        return $this->normalize($vector);
    }

    /**
     * @param  array<int, float>  $vector
     * @return array<int, float>
     */
    private function normalize(array $vector): array
    {
        $norm = 0.0;

        foreach ($vector as $value) {
            $norm += $value * $value;
        }

        $norm = sqrt($norm);

        if ($norm <= 0.0) {
            return $vector;
        }

        return array_map(static fn ($value) => $value / $norm, $vector);
    }
}
