<?php

namespace App\Http\Controllers\Search;

use App\Http\Controllers\Controller;
use App\Http\Requests\CreativeSearchRequest;
use App\Models\CreativeProfile;
use App\Models\EmbeddingCache;
use App\Models\Job;
use App\Services\Embedding\EmbeddingVectorizer;
use App\Services\Pinecone\PineconeService;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Collection;
use Laravel\Scout\Builder as ScoutBuilder;

class CreativeSearchController extends Controller
{
    public function __invoke(
        CreativeSearchRequest $request,
        PineconeService $pinecone,
        EmbeddingVectorizer $vectorizer,
    ): JsonResponse {
        $user = $request->user();
        $query = trim($request->queryString());
        $filters = $request->filters();
        $resultLimit = $request->resultLimit();
        $jobId = $request->jobId();

        // Get job context if searching for specific job
        $jobContext = $jobId ? Job::find($jobId) : null;

        $keywordResults = $this->keywordCreativeSearch($query, $filters, $resultLimit);
        $queryVector = $this->buildQueryVector($vectorizer, $jobContext, $query);

        if ($queryVector === []) {
            return response()->json([
                'data' => $keywordResults->map(fn (CreativeProfile $profile) => $this->formatCreative($profile, 0.0, 1.0))->all(),
                'meta' => [
                    'source' => 'keyword-only',
                ],
            ]);
        }

        $semanticMatches = $this->semanticCreativeMatches($queryVector, $pinecone, $filters, $request->semanticLimit());

        $combined = $this->combineResults($keywordResults, $semanticMatches, $resultLimit, $filters);

        return response()->json([
            'data' => $combined,
            'meta' => [
                'source' => $pinecone->isEnabled() ? 'pinecone+scout' : 'cache+scout',
                'semantic_limit' => $request->semanticLimit(),
                'keyword_count' => $keywordResults->count(),
                'semantic_count' => count($semanticMatches),
                'job_context' => $jobContext ? $jobContext->title : null,
            ],
        ]);
    }

    private function keywordCreativeSearch(string $query, array $filters, int $limit): Collection
    {
        $limit = max(1, min($limit, 25));

        if ($query !== '') {
            try {
                /** @var ScoutBuilder $builder */
                $builder = CreativeProfile::search($query)
                    ->query(function (EloquentBuilder $eloquent) use ($filters): void {
                        $eloquent->whereHas('user', function (EloquentBuilder $userQuery): void {
                            $userQuery->where('user_type', 'creative');
                        });
                        $this->applyCreativeFilters($eloquent, $filters);
                    });

                return $builder->take($limit)->get();
            } catch (\Exception $e) {
                // Fallback to database search if Scout is not available
                \Log::warning('Scout search failed, falling back to database search: ' . $e->getMessage());

                $eloquent = CreativeProfile::query()
                    ->whereHas('user', function (EloquentBuilder $userQuery) use ($query): void {
                        $userQuery->where('user_type', 'creative')
                            ->where(function (EloquentBuilder $q) use ($query): void {
                                $q->where('name', 'like', "%{$query}%")
                                  ->orWhere('email', 'like', "%{$query}%");
                            });
                    })
                    ->orWhere(function (EloquentBuilder $profileQuery) use ($query): void {
                        $profileQuery->where('bio', 'like', "%{$query}%")
                                     ->orWhere('location', 'like', "%{$query}%")
                                     ->orWhereJsonContains('skills', $query);
                    });

                $this->applyCreativeFilters($eloquent, $filters);
                return $eloquent->take($limit)->get();
            }
        }

        $eloquent = CreativeProfile::query()->whereHas('user', function (EloquentBuilder $userQuery): void {
            $userQuery->where('user_type', 'creative');
        });
        $this->applyCreativeFilters($eloquent, $filters);

        return $eloquent->orderByDesc('updated_at')->limit($limit)->get();
    }

    /**
     * @param  array<string, mixed>  $filters
     */
    private function applyCreativeFilters(EloquentBuilder $query, array $filters): void
    {
        if (! empty($filters['skills']) && is_array($filters['skills'])) {
            $query->where(function (EloquentBuilder $builder) use ($filters): void {
                foreach ($filters['skills'] as $skill) {
                    $builder->orWhereJsonContains('skills', $skill);
                }
            });
        }

        if (! empty($filters['location'])) {
            $query->where('location', 'LIKE', '%'.$filters['location'].'%');
        }

        if (! empty($filters['experience_level'])) {
            $query->where('experience_level', $filters['experience_level']);
        }
    }

    private function buildQueryVector(
        EmbeddingVectorizer $vectorizer,
        ?Job $jobContext,
        string $query
    ): array {
        // Build search context from job and query
        $contextText = $query;

        if ($jobContext) {
            $contextText .= ' ' . $jobContext->title;
            if ($jobContext->summary) {
                $contextText .= ' ' . $jobContext->summary;
            }
            if ($jobContext->skills) {
                $contextText .= ' ' . implode(' ', $jobContext->skills);
            }
        }

        return $vectorizer->embed($contextText);
    }

    private function semanticCreativeMatches(
        array $queryVector,
        PineconeService $pinecone,
        array $filters,
        int $limit
    ): array {
        if ($pinecone->isEnabled()) {
            return $this->pineconeCreativeMatches($queryVector, $pinecone, $filters, $limit);
        }

        return $this->cachedCreativeMatches($queryVector, $filters, $limit);
    }

    private function pineconeCreativeMatches(
        array $queryVector,
        PineconeService $pinecone,
        array $filters,
        int $limit
    ): array {
        $pineconeFilter = $this->buildPineconeFilter($filters);

        $queryOptions = [
            'topK' => $limit,
            'includeValues' => false,
            'includeMetadata' => true,
            'filter' => $pineconeFilter,
        ];

        // Query Pinecone for creative profiles
        $response = $pinecone->queryVectors($queryVector, $queryOptions);

        $matches = [];
        foreach ($response['matches'] ?? [] as $match) {
            $profileId = (int) $match['id'];
            $profile = CreativeProfile::find($profileId);

            if ($profile) {
                $matches[] = [
                    'profile' => $profile,
                    'score' => $match['score'],
                ];
            }
        }

        return $matches;
    }

    private function buildPineconeFilter(array $filters): array
    {
        $pineconeFilter = [
            ['entity_type' => ['$eq' => 'creative_profile']],
        ];

        if (! empty($filters['skills']) && is_array($filters['skills'])) {
            $pineconeFilter[] = ['skills' => ['$in' => $filters['skills']]];
        }

        if (! empty($filters['experience_level'])) {
            $pineconeFilter[] = ['experience_level' => ['$eq' => $filters['experience_level']]];
        }

        // Note: Pinecone doesn't support 'LIKE' or full-text search on metadata.
        // We can't directly translate the location 'LIKE' filter.
        // If location metadata is stored precisely, we could use an '$eq' filter.
        // For this implementation, we'll omit location from the Pinecone filter
        // and rely on the broader semantic match, with keyword search handling location.

        return count($pineconeFilter) > 1 ? ['$and' => $pineconeFilter] : $pineconeFilter[0];
    }

    private function cachedCreativeMatches(
        array $queryVector,
        array $filters,
        int $limit
    ): array {
        $embeddings = EmbeddingCache::where('embeddable_type', CreativeProfile::class)
            ->with('embeddable')
            ->get();

        $matches = [];
        foreach ($embeddings as $embedding) {
            if (! $embedding->embeddable) {
                continue;
            }

            $profile = $embedding->embeddable;
            if (! $this->matchesFilters($profile, $filters)) {
                continue;
            }

            $similarity = $this->cosineSimilarity($queryVector, $embedding->vector);
            $matches[] = [
                'profile' => $profile,
                'score' => $similarity,
            ];
        }

        // Sort by similarity score
        usort($matches, fn ($a, $b) => $b['score'] <=> $a['score']);

        return array_slice($matches, 0, $limit);
    }

    private function matchesFilters(CreativeProfile $profile, array $filters): bool
    {
        if (! empty($filters['skills']) && is_array($filters['skills'])) {
            $profileSkills = $profile->skills ?? [];
            $hasMatchingSkill = false;
            foreach ($filters['skills'] as $skill) {
                if (in_array($skill, $profileSkills)) {
                    $hasMatchingSkill = true;
                    break;
                }
            }
            if (! $hasMatchingSkill) {
                return false;
            }
        }

        if (! empty($filters['location']) && $profile->location) {
            if (stripos($profile->location, $filters['location']) === false) {
                return false;
            }
        }

        if (! empty($filters['experience_level']) && $profile->experience_level !== $filters['experience_level']) {
            return false;
        }

        return true;
    }

    private function combineResults(
        Collection $keywordResults,
        array $semanticMatches,
        int $limit,
        array $filters
    ): array {
        $combined = collect();
        $seen = collect();

        // Add semantic matches first (higher priority)
        foreach ($semanticMatches as $match) {
            $profile = $match['profile'];
            $semanticScore = $match['score'];

            if ($seen->has($profile->id)) {
                continue;
            }

            $combined->push($this->formatCreative($profile, $semanticScore, 0.0));
            $seen->put($profile->id, true);
        }

        // Add keyword matches that weren't already included
        foreach ($keywordResults as $profile) {
            if ($seen->has($profile->id)) {
                continue;
            }

            $combined->push($this->formatCreative($profile, 0.0, 1.0));
            $seen->put($profile->id, true);
        }

        // Apply final scoring (semantic 70%, keyword 30%)
        $scored = $combined->map(function (array $item): array {
            $item['scores']['final'] = ($item['scores']['semantic'] * 0.7) + ($item['scores']['keyword'] * 0.3);
            return $item;
        });

        return $scored->sortByDesc('scores.final')->take($limit)->values()->all();
    }

    private function formatCreative(CreativeProfile $profile, float $semanticScore, float $keywordScore): array
    {
        return [
            'id' => $profile->id,
            'user_id' => $profile->user_id,
            'name' => $profile->user->name,
            'email' => $profile->user->email,
            'bio' => $profile->bio,
            'skills' => $profile->skills,
            'experience_level' => $profile->experience_level,
            'location' => $profile->location,
            'portfolio_url' => $profile->portfolio_url,
            'created_at' => $profile->created_at?->toISOString(),
            'updated_at' => $profile->updated_at?->toISOString(),
            'scores' => [
                'semantic' => $semanticScore,
                'keyword' => $keywordScore,
                'final' => ($semanticScore * 0.7) + ($keywordScore * 0.3),
            ],
        ];
    }

    private function cosineSimilarity(array $vectorA, array $vectorB): float
    {
        if (count($vectorA) !== count($vectorB)) {
            return 0.0;
        }

        $dotProduct = 0.0;
        $magnitudeA = 0.0;
        $magnitudeB = 0.0;

        for ($i = 0; $i < count($vectorA); $i++) {
            $dotProduct += $vectorA[$i] * $vectorB[$i];
            $magnitudeA += $vectorA[$i] ** 2;
            $magnitudeB += $vectorB[$i] ** 2;
        }

        $magnitude = sqrt($magnitudeA) * sqrt($magnitudeB);

        return $magnitude > 0 ? $dotProduct / $magnitude : 0.0;
    }
}
