<?php

namespace App\Http\Controllers\Search;

use App\Http\Controllers\Controller;
use App\Http\Requests\SemanticSearchRequest;
use App\Jobs\SyncCreativeProfileEmbeddings;
use App\Jobs\SyncJobEmbeddings;
use App\Models\CreativeProfile;
use App\Models\EmbeddingCache;
use App\Models\Job;
use App\Services\Embedding\EmbeddingVectorizer;
use App\Services\Pinecone\PineconeService;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Laravel\Scout\Builder as ScoutBuilder;

class SemanticSearchController extends Controller
{
    public function __invoke(
        SemanticSearchRequest $request,
        PineconeService $pinecone,
        EmbeddingVectorizer $vectorizer,
    ): JsonResponse {
        $user = $request->user();
        $query = trim($request->queryString());
        $filters = $request->filters();
        $resultLimit = $request->resultLimit();

        $keywordResults = $this->keywordJobSearch($query, $filters, $resultLimit);
        $queryVector = $this->buildQueryVector($vectorizer, $user?->creativeProfile, $query);

        if ($queryVector === []) {
            return response()->json([
                'data' => $keywordResults->map(fn (Job $job) => $this->formatJob($job, 0.0, 1.0))->all(),
                'meta' => [
                    'source' => 'keyword-only',
                ],
            ]);
        }

        $semanticMatches = $this->semanticJobMatches($queryVector, $pinecone, $filters, $request->semanticLimit());

        $combined = $this->combineResults($keywordResults, $semanticMatches, $resultLimit, $filters);

        return response()->json([
            'data' => $combined,
            'meta' => [
                'source' => $pinecone->isEnabled() ? 'pinecone+scout' : 'cache+scout',
                'semantic_limit' => $request->semanticLimit(),
                'keyword_count' => $keywordResults->count(),
                'semantic_count' => count($semanticMatches),
            ],
        ]);
    }

    private function keywordJobSearch(string $query, array $filters, int $limit): Collection
    {
        $limit = max(1, min($limit, 25));

        if ($query !== '') {
            /** @var ScoutBuilder $builder */
            $builder = Job::search($query)
                ->query(function (EloquentBuilder $eloquent) use ($filters): void {
                    $eloquent->where('status', Job::STATUS_PUBLISHED);
                    $this->applyJobFilters($eloquent, $filters);
                });

            return $builder->take($limit)->get();
        }

        $eloquent = Job::query()->where('status', Job::STATUS_PUBLISHED);
        $this->applyJobFilters($eloquent, $filters);

        return $eloquent->orderByDesc('published_at')->limit($limit)->get();
    }

    /**
     * @param  array<string, mixed>  $filters
     */
    private function applyJobFilters(EloquentBuilder $query, array $filters): void
    {
        if (! empty($filters['category'])) {
            $query->where('category', $filters['category']);
        }

        if (! empty($filters['location'])) {
            $query->where('location', 'LIKE', '%'.$filters['location'].'%');
        }

        if (! empty($filters['remote'])) {
            $query->where('is_remote', true);
        }

        if (! empty($filters['skills']) && is_array($filters['skills'])) {
            $query->where(function (EloquentBuilder $builder) use ($filters): void {
                foreach ($filters['skills'] as $skill) {
                    $builder->orWhereJsonContains('skills', $skill);
                }
            });
        }
    }

    private function buildQueryVector(
        EmbeddingVectorizer $vectorizer,
        ?CreativeProfile $profile,
        string $userQuery,
    ): array {
        $vector = [];
        $userQuery = trim($userQuery);

        if ($userQuery !== '') {
            $vector = $vectorizer->embed($userQuery);
        }

        if ($profile) {
            $cached = EmbeddingCache::forEntity(CreativeProfile::class, $profile->id)->latest('generated_at')->first();

            if ($cached) {
                $profileVector = array_map(static fn ($value) => (float) $value, $cached->vector_data ?? []);

                if ($vector === []) {
                    $vector = $profileVector;
                } else {
                    $vector = $this->blendVectors($vector, $profileVector);
                }
            } else {
                SyncCreativeProfileEmbeddings::dispatch($profile->id, true)->afterCommit();
            }
        }

        return $vector;
    }

    /**
     * @param  array<int, float>  $queryVector
     * @param  array<string, mixed>  $filters
     * @return array<int, array{job_id:int, score:float}>
     */
    private function semanticJobMatches(array $queryVector, PineconeService $pinecone, array $filters, int $limit): array
    {
        $limit = max(1, min($limit, 50));

        if ($pinecone->isEnabled()) {
            $pineconeFilter = $this->buildPineconeJobFilter($filters);

            $queryOptions = [
                'topK' => $limit,
                'filter' => $pineconeFilter,
            ];

            $response = $pinecone->queryVectors($queryVector, $queryOptions);

            $matches = Arr::get($response, 'matches', []);

            return collect($matches)
                ->map(function (array $match): ?array {
                    $id = (string) ($match['id'] ?? '');

                    if (! str_starts_with($id, 'job::')) {
                        return null;
                    }

                    $jobId = (int) substr($id, 5);

                    if ($jobId <= 0) {
                        return null;
                    }

                    return [
                        'job_id' => $jobId,
                        'score' => $this->clampScore((float) ($match['score'] ?? 0)),
                    ];
                })
                ->filter()
                ->values()
                ->take($limit)
                ->all();
        }

        return $this->localSemanticMatches($queryVector, $filters, $limit);
    }

    private function buildPineconeJobFilter(array $filters): array
    {
        $pineconeFilter = [
            ['entity_type' => ['$eq' => 'job']],
            ['status' => ['$eq' => 'published']],
        ];

        if (! empty($filters['category'])) {
            $pineconeFilter[] = ['category' => ['$eq' => $filters['category']]];
        }

        if (! empty($filters['remote'])) {
            $pineconeFilter[] = ['is_remote' => ['$eq' => true]];
        }

        if (! empty($filters['skills']) && is_array($filters['skills'])) {
            // Assuming 'skills' in Pinecone metadata is an array of strings
            $pineconeFilter[] = ['skills' => ['$in' => $filters['skills']]];
        }

        // Note: Location filtering is complex for Pinecone as it doesn't support LIKE operations
        // For now, we'll handle location filtering in the local semantic matches fallback
        // You could implement geo-based filtering if location coordinates are stored in metadata

        return count($pineconeFilter) > 1 ? ['$and' => $pineconeFilter] : $pineconeFilter[0];
    }

    /**
     * @param  array<string, mixed>  $filters
     * @return array<int, array{job_id:int, score:float}>
     */
    private function localSemanticMatches(array $queryVector, array $filters, int $limit): array
    {
        $candidateQuery = Job::query()
            ->where('status', Job::STATUS_PUBLISHED)
            ->orderByDesc('published_at')
            ->limit($limit * 3);

        $this->applyJobFilters($candidateQuery, $filters);

        $jobIds = $candidateQuery->pluck('id');

        if ($jobIds->isEmpty()) {
            return [];
        }

        $caches = EmbeddingCache::query()
            ->where('entity_type', Job::class)
            ->whereIn('entity_id', $jobIds)
            ->get()
            ->keyBy('entity_id');

        $matches = [];

        foreach ($jobIds as $jobId) {
            $cache = $caches->get($jobId);

            if (! $cache) {
                SyncJobEmbeddings::dispatch($jobId, true)->afterCommit();
                continue;
            }

            $candidateVector = array_map(static fn ($value) => (float) $value, $cache->vector_data ?? []);
            $score = $this->cosineSimilarity($queryVector, $candidateVector);

            if ($score <= 0) {
                continue;
            }

            $matches[] = [
                'job_id' => (int) $jobId,
                'score' => $this->clampScore($score),
            ];
        }

        usort($matches, fn (array $a, array $b) => $b['score'] <=> $a['score']);

        return array_slice($matches, 0, $limit);
    }

    /**
     * @param  \Illuminate\Support\Collection<int, Job>  $keywordResults
     * @param  array<int, array{job_id:int, score:float}>  $semanticMatches
     * @return array<int, array<string, mixed>>
     */
    private function combineResults(Collection $keywordResults, array $semanticMatches, int $limit, array $filters): array
    {
        $semanticWeight = 0.65;
        $keywordWeight = 0.35;

        $combined = [];
        $keywordCount = max($keywordResults->count(), 1);

        foreach ($keywordResults->values() as $rank => $job) {
            $combined[$job->id] = [
                'job' => $job,
                'keyword_score' => 1 - ($rank / $keywordCount),
                'semantic_score' => 0.0,
                'keyword_rank' => $rank + 1,
            ];
        }

        foreach ($semanticMatches as $match) {
            $jobId = $match['job_id'];

            $combined[$jobId] ??= [
                'job' => null,
                'keyword_score' => 0.0,
                'semantic_score' => 0.0,
                'keyword_rank' => null,
            ];

            $combined[$jobId]['semantic_score'] = max($combined[$jobId]['semantic_score'], $match['score']);
        }

        $jobIds = collect($combined)
            ->filter(fn (array $entry) => $entry['job'] === null)
            ->keys()
            ->map(fn ($id) => (int) $id)
            ->all();

        if ($jobIds !== []) {
            $additionalJobs = Job::query()
                ->whereIn('id', $jobIds)
                ->where('status', Job::STATUS_PUBLISHED)
                ->get()
                ->keyBy('id');

            foreach ($jobIds as $jobId) {
                if ($additionalJobs->has($jobId)) {
                    $combined[$jobId]['job'] = $additionalJobs->get($jobId);
                }
            }
        }

        $results = collect($combined)
            ->filter(fn (array $entry) => $entry['job'] instanceof Job)
            ->map(function (array $entry) use ($semanticWeight, $keywordWeight) {
                $semanticScore = $entry['semantic_score'];
                $keywordScore = $entry['keyword_score'];
                $finalScore = ($semanticScore * $semanticWeight) + ($keywordScore * $keywordWeight);

                return [
                    'job' => $entry['job'],
                    'final_score' => $finalScore,
                    'semantic_score' => $semanticScore,
                    'keyword_score' => $keywordScore,
                    'keyword_rank' => $entry['keyword_rank'],
                ];
            })
            ->sortByDesc('final_score')
            ->take($limit)
            ->values()
            ->map(fn (array $entry) => $this->formatJob($entry['job'], $entry['semantic_score'], $entry['keyword_score'], $entry['final_score'], $entry['keyword_rank']))
            ->all();

        return $results;
    }

    private function formatJob(Job $job, float $semanticScore, float $keywordScore, ?float $finalScore = null, ?int $keywordRank = null): array
    {
        return [
            'id' => $job->id,
            'title' => $job->title,
            'slug' => $job->slug,
            'summary' => $job->summary,
            'location' => $job->location,
            'is_remote' => (bool) $job->is_remote,
            'category' => $job->category,
            'skills' => $job->skills,
            'published_at' => optional($job->published_at)->toIso8601String(),
            'budget_min' => $job->budget_min,
            'budget_max' => $job->budget_max,
            'scores' => [
                'final' => $finalScore ?? $semanticScore,
                'semantic' => $semanticScore,
                'keyword' => $keywordScore,
            ],
            'explanation' => [
                'keyword_rank' => $keywordRank,
            ],
        ];
    }

    /**
     * @param  array<int, float>  $vectorA
     * @param  array<int, float>  $vectorB
     * @return array<int, float>
     */
    private function blendVectors(array $vectorA, array $vectorB): array
    {
        $length = max(count($vectorA), count($vectorB));

        if ($length === 0) {
            return [];
        }

        $blended = [];

        for ($i = 0; $i < $length; $i++) {
            $valueA = $vectorA[$i] ?? 0.0;
            $valueB = $vectorB[$i] ?? 0.0;
            $blended[] = ($valueA + $valueB) / 2;
        }

        return $blended;
    }

    /**
     * @param  array<int, float>  $vectorA
     * @param  array<int, float>  $vectorB
     */
    private function cosineSimilarity(array $vectorA, array $vectorB): float
    {
        $length = min(count($vectorA), count($vectorB));

        if ($length === 0) {
            return 0.0;
        }

        $dot = 0.0;
        $normA = 0.0;
        $normB = 0.0;

        for ($i = 0; $i < $length; $i++) {
            $a = $vectorA[$i];
            $b = $vectorB[$i];
            $dot += $a * $b;
            $normA += $a * $a;
            $normB += $b * $b;
        }

        if ($normA <= 0 || $normB <= 0) {
            return 0.0;
        }

        return $dot / (sqrt($normA) * sqrt($normB));
    }

    private function clampScore(float $score): float
    {
        if ($score < 0) {
            return 0.0;
        }

        if ($score > 1) {
            return 1.0;
        }

        return round($score, 6);
    }
}
