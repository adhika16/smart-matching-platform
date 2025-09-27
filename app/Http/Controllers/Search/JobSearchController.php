<?php

namespace App\Http\Controllers\Search;

use App\Http\Controllers\Controller;
use App\Models\Job;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class JobSearchController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'q' => ['nullable', 'string', 'max:255'],
            'category' => ['nullable', 'string', 'max:100'],
            'skill' => ['nullable', 'string', 'max:50'],
            'location' => ['nullable', 'string', 'max:255'],
            'remote' => ['nullable', 'boolean'],
            'limit' => ['nullable', 'integer', 'min:1', 'max:50'],
        ]);

        $limit = $validated['limit'] ?? 10;
        $searchTerm = trim($validated['q'] ?? '');

        if ($searchTerm === '') {
            $query = Job::query()->where('status', Job::STATUS_PUBLISHED)->latest('published_at');
        } else {
            $query = Job::search($searchTerm)
                ->query(fn ($builder) => $builder->where('status', Job::STATUS_PUBLISHED));
        }

        if (! empty($validated['category'])) {
            $query = $query instanceof \Laravel\Scout\Builder
                ? $query->where('category', $validated['category'])
                : $query->where('category', $validated['category']);
        }

        if (! empty($validated['skill'])) {
            $skillFilter = $validated['skill'];
            $query = $query instanceof \Laravel\Scout\Builder
                ? $query->where('skills', $skillFilter)
                : $query->whereJsonContains('skills', $skillFilter);
        }

        if (! empty($validated['location'])) {
            $location = $validated['location'];
            $query = $query instanceof \Laravel\Scout\Builder
                ? $query->where('location', $location)
                : $query->where('location', 'like', "%{$location}%");
        }

        if (array_key_exists('remote', $validated)) {
            $isRemote = filter_var($validated['remote'], FILTER_VALIDATE_BOOLEAN);
            $query = $query instanceof \Laravel\Scout\Builder
                ? $query->where('is_remote', $isRemote)
                : $query->where('is_remote', $isRemote);
        }

        $results = $query instanceof \Laravel\Scout\Builder
            ? $query->take($limit)->get()
            : $query->limit($limit)->get();

        $formatted = $results->map(fn (Job $job) => [
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
        ]);

        return response()->json([
            'data' => $formatted,
        ]);
    }
}
