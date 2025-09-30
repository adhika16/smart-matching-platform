<?php

namespace App\Http\Controllers;

use App\Models\Application;
use App\Models\Job;
use App\Services\Matching\ApplicationRankingService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class JobController extends Controller
{
    /**
     * Display a listing of the jobs for the authenticated opportunity owner.
     */
    public function index(Request $request): Response
    {
        $this->authorize('viewAny', Job::class);

        $jobs = $request->user()
            ->jobs()
            ->latest()
            ->paginate(perPage: 10)
            ->through(fn (Job $job) => [
                'id' => $job->id,
                'title' => $job->title,
                'status' => $job->status,
                'category' => $job->category,
                'skills' => $job->skills,
                'published_at' => $job->published_at?->toIso8601String(),
                'created_at' => $job->created_at?->toIso8601String(),
                'updated_at' => $job->updated_at?->toIso8601String(),
            ]);

        return Inertia::render('opportunity-owner/jobs/index', [
            'jobs' => $jobs,
        ]);
    }

    /**
     * Show the form for creating a new job.
     */
    public function create(): Response
    {
        $this->authorize('create', Job::class);

        return Inertia::render('opportunity-owner/jobs/create', [
            'compensationTypes' => $this->compensationTypes(),
            'taxonomy' => $this->taxonomy(),
        ]);
    }

    /**
     * Store a newly created job in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create', Job::class);

        $data = $this->validateJob($request);
        $status = $this->determineStatus($request, allowArchived: false);

        $job = $request->user()->jobs()->create([
            'title' => $data['title'],
            'slug' => Job::generateSlug($data['title']),
            'location' => $data['location'] ?? null,
            'is_remote' => $data['is_remote'] ?? false,
            'status' => $status,
            'compensation_type' => $data['compensation_type'] ?? null,
            'compensation_min' => $data['compensation_min'] ?? null,
            'compensation_max' => $data['compensation_max'] ?? null,
            'tags' => $data['tags'] ?? null,
            'skills' => $data['skills'] ?? null,
            'category' => $data['category'] ?? null,
            'summary' => $data['summary'] ?? null,
            'description' => $data['description'],
            'timeline_start' => $data['timeline_start'] ?? null,
            'timeline_end' => $data['timeline_end'] ?? null,
            'budget_min' => $data['budget_min'] ?? null,
            'budget_max' => $data['budget_max'] ?? null,
            'published_at' => $status === Job::STATUS_PUBLISHED ? Date::now() : null,
        ]);

        return redirect()
            ->route('opportunity-owner.jobs.edit', $job)
            ->with('success', $status === Job::STATUS_PUBLISHED ? 'Job published successfully.' : 'Job saved as draft.');
    }

    /**
     * Show the form for editing the specified job.
     */
    public function edit(Job $job, ApplicationRankingService $rankingService): Response
    {
        $this->authorize('update', $job);

        $rawApplications = $job->applications()
            ->with(['applicant', 'applicant.creativeProfile'])
            ->latest()
            ->get()
            ->filter(function ($application) {
                // Filter out applications where the user no longer exists
                return $application->applicant !== null;
            });

        // Apply smart ranking if there are applications
        $rankedApplications = collect();
        $hasSmartRanking = false;

        if ($rawApplications->isNotEmpty()) {
            try {
                $ranked = $rankingService->rankApplicationsForJob($job, $rawApplications);
                $hasSmartRanking = true;

                $rankedApplications = $ranked->map(function ($item) {
                    $application = $item['application'];
                    return [
                        'id' => $application->id,
                        'status' => $application->status,
                        'cover_letter' => $application->cover_letter,
                        'submitted_at' => $application->created_at?->toIso8601String(),
                        'applicant' => [
                            'id' => $application->applicant->id,
                            'name' => $application->applicant->name,
                            'email' => $application->applicant->email,
                        ],
                    'ai_match' => [
                        'score' => $item['score'],
                        'breakdown' => $item['breakdown'],
                    ],
                ];
            });
            } catch (\Exception $e) {
                // Log error but don't break the page - show applications without AI ranking
                Log::error('AI ranking service failed', [
                    'job_id' => $job->id,
                    'error' => $e->getMessage(),
                ]);

                // Fallback to showing applications without AI ranking
                $rankedApplications = $rawApplications->map(function ($application) {
                    return [
                        'id' => $application->id,
                        'status' => $application->status,
                        'cover_letter' => $application->cover_letter,
                        'submitted_at' => $application->created_at?->toIso8601String(),
                        'applicant' => [
                            'id' => $application->applicant->id,
                            'name' => $application->applicant->name,
                            'email' => $application->applicant->email,
                        ],
                        'ai_match' => [
                            'score' => 0,
                            'breakdown' => [
                                'profile_match' => 0,
                                'skills_match' => 0,
                                'experience_match' => 0,
                            ],
                        ],
                    ];
                });
                $hasSmartRanking = false;
            }
        }

        return Inertia::render('opportunity-owner/jobs/edit', [
            'job' => [
                'id' => $job->id,
                'title' => $job->title,
                'location' => $job->location,
                'is_remote' => $job->is_remote,
                'status' => $job->status,
                'compensation_type' => $job->compensation_type,
                'compensation_min' => $job->compensation_min,
                'compensation_max' => $job->compensation_max,
                'tags' => $job->tags,
                'skills' => $job->skills,
                'category' => $job->category,
                'summary' => $job->summary,
                'description' => $job->description,
                'published_at' => $job->published_at?->toIso8601String(),
                'timeline_start' => $job->timeline_start instanceof \Illuminate\Support\Carbon
                    ? $job->timeline_start->format('Y-m-d')
                    : $job->timeline_start,
                'timeline_end' => $job->timeline_end instanceof \Illuminate\Support\Carbon
                    ? $job->timeline_end->format('Y-m-d')
                    : $job->timeline_end,
                'budget_min' => $job->budget_min,
                'budget_max' => $job->budget_max,
            ],
            'compensationTypes' => $this->compensationTypes(),
            'taxonomy' => $this->taxonomy(),
            'applications' => $rankedApplications->toArray(),
            'hasSmartRanking' => $hasSmartRanking,
            'applicationStatuses' => [
                ['value' => Application::STATUS_PENDING, 'label' => 'Pending review'],
                ['value' => Application::STATUS_SHORTLISTED, 'label' => 'Shortlisted'],
                ['value' => Application::STATUS_REJECTED, 'label' => 'Rejected'],
            ],
        ]);
    }

    /**
     * Update the specified job in storage.
     */
    public function update(Request $request, Job $job): RedirectResponse
    {
        $this->authorize('update', $job);

        $data = $this->validateJob($request);
        $status = $this->determineStatus($request, allowArchived: true);

        $attributes = [
            'title' => $data['title'],
            'location' => $data['location'] ?? null,
            'is_remote' => $data['is_remote'] ?? false,
            'status' => $status ?? $job->status,
            'compensation_type' => $data['compensation_type'] ?? null,
            'compensation_min' => $data['compensation_min'] ?? null,
            'compensation_max' => $data['compensation_max'] ?? null,
            'tags' => $data['tags'] ?? null,
            'skills' => $data['skills'] ?? null,
            'category' => $data['category'] ?? null,
            'summary' => $data['summary'] ?? null,
            'description' => $data['description'],
            'timeline_start' => $data['timeline_start'] ?? null,
            'timeline_end' => $data['timeline_end'] ?? null,
            'budget_min' => $data['budget_min'] ?? null,
            'budget_max' => $data['budget_max'] ?? null,
        ];

        if (($status ?? $job->status) === Job::STATUS_PUBLISHED && ! $job->isPublished()) {
            $this->authorize('publish', $job);
            $attributes['published_at'] = Date::now();
        }

        if (($status ?? $job->status) !== Job::STATUS_PUBLISHED) {
            $attributes['published_at'] = null;
        }

        $job->update($attributes);

        return redirect()
            ->route('opportunity-owner.jobs.edit', $job)
            ->with('success', 'Job updated successfully.');
    }

    /**
     * Remove the specified job from storage.
     */
    public function destroy(Job $job): RedirectResponse
    {
        $this->authorize('delete', $job);

        $job->delete();

        return redirect()
            ->route('opportunity-owner.jobs.index')
            ->with('success', 'Job deleted successfully.');
    }

    /**
     * Publish the job.
     */
    public function publish(Job $job): RedirectResponse
    {
        $this->authorize('publish', $job);

        $job->update([
            'status' => Job::STATUS_PUBLISHED,
            'published_at' => Date::now(),
        ]);

        return redirect()
            ->route('opportunity-owner.jobs.index')
            ->with('success', 'Job published successfully.');
    }

    /**
     * Archive the job.
     */
    public function archive(Job $job): RedirectResponse
    {
        $this->authorize('archive', $job);

        $job->update([
            'status' => Job::STATUS_ARCHIVED,
            'published_at' => null,
        ]);

        return redirect()
            ->route('opportunity-owner.jobs.index')
            ->with('success', 'Job archived successfully.');
    }

    /**
     * Validate and normalize job data from the request.
     *
     * @return array<string, mixed>
     */
    protected function validateJob(Request $request): array
    {
        $taxonomy = $this->taxonomy();
        $skillValues = collect($taxonomy['skills'] ?? [])->pluck('value')->filter()->values()->all();
        $categoryValues = collect($taxonomy['categories'] ?? [])->pluck('value')->filter()->values()->all();

        $skillRule = count($skillValues) > 0 ? ['required', 'array', 'min:1'] : ['nullable', 'array'];
        $categoryRule = count($categoryValues) > 0
            ? ['required', Rule::in($categoryValues)]
            : ['nullable', 'string', 'max:100'];

        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'summary' => ['nullable', 'string', 'max:500'],
            'description' => ['required', 'string'],
            'location' => ['nullable', 'string', 'max:255'],
            'is_remote' => ['nullable', 'boolean'],
            'compensation_type' => ['nullable', 'in:hourly,project,salary'],
            'compensation_min' => ['nullable', 'numeric', 'min:0'],
            'compensation_max' => ['nullable', 'numeric', 'min:0'],
            'skills' => $skillRule,
            'skills.*' => count($skillValues) > 0
                ? ['string', Rule::in($skillValues)]
                : ['string', 'max:50'],
            'category' => $categoryRule,
            'timeline_start' => ['nullable', 'date'],
            'timeline_end' => ['nullable', 'date', 'after_or_equal:timeline_start'],
            'budget_min' => ['nullable', 'numeric', 'min:0'],
            'budget_max' => ['nullable', 'numeric', 'min:0'],
        ]);

        if (! empty($data['compensation_min']) && ! empty($data['compensation_max'])
            && $data['compensation_min'] > $data['compensation_max']) {
            throw ValidationException::withMessages([
                'compensation_max' => 'Maximum compensation must be greater than or equal to minimum compensation.',
            ]);
        }

        if (! empty($data['budget_min']) && ! empty($data['budget_max'])
            && $data['budget_min'] > $data['budget_max']) {
            throw ValidationException::withMessages([
                'budget_max' => 'Maximum budget must be greater than or equal to minimum budget.',
            ]);
        }

        $data['description'] = $this->sanitizeHtml($data['description']);
        $data['skills'] = collect($data['skills'] ?? [])->map(fn ($skill) => (string) $skill)->unique()->values()->all();
        $data['tags'] = $data['skills'];
        $data['is_remote'] = filter_var($request->input('is_remote', false), FILTER_VALIDATE_BOOLEAN);

        return $data;
    }

    /**
     * Determine desired status from request.
     */
    protected function determineStatus(Request $request, bool $allowArchived = false): string
    {
        $allowed = [Job::STATUS_DRAFT, Job::STATUS_PUBLISHED];

        if ($allowArchived) {
            $allowed[] = Job::STATUS_ARCHIVED;
        }

        $status = $request->input('status', Job::STATUS_DRAFT);

        return in_array($status, $allowed, true) ? $status : Job::STATUS_DRAFT;
    }

    /**
     * Normalize tags input into an array of strings.
     *
     * @return array<int, string>|null
     */
    protected function normalizeTags(mixed $tags): ?array
    {
        if (! $tags) {
            return null;
        }

        if (is_string($tags)) {
            $tags = Str::of($tags)
                ->explode(',')
                ->map(fn ($tag) => trim((string) $tag))
                ->filter()
                ->values()
                ->all();
        }

        if (is_array($tags)) {
            return collect($tags)
                ->map(fn ($tag) => trim((string) $tag))
                ->filter()
                ->values()
                ->all() ?: null;
        }

        return null;
    }

    /**
     * Sanitize rich-text content to a safe HTML subset.
     */
    protected function sanitizeHtml(string $html): string
    {
    $allowedTags = '<p><br><strong><em><u><ul><ol><li><a><blockquote><h2><h3><h4>';
        $cleaned = strip_tags($html, $allowedTags);

        // Remove potential javascript: URLs in anchors.
        $cleaned = preg_replace_callback('/<a\s+[^>]*href="([^"]*)"[^>]*>/i', function ($matches) {
            $href = $matches[1] ?? '';
            if (str_starts_with(strtolower($href), 'javascript:')) {
                return str_replace($matches[1], '#', $matches[0]);
            }

            return $matches[0];
        }, $cleaned ?? '');

        $cleaned = preg_replace('/on[a-zA-Z]+="[^"]*"/i', '', $cleaned ?? '');

        return $cleaned ?? '';
    }

    /**
     * Get structured taxonomy options for the job form.
     *
     * @return array{skills: array<int, array{value: string, label: string}>, categories: array<int, array{value: string, label: string}>}
     */
    protected function taxonomy(): array
    {
        return [
            'skills' => config('taxonomy.skills', []),
            'categories' => config('taxonomy.categories', []),
        ];
    }

    /**
     * Get available compensation types.
     *
     * @return array<int, array{value: string, label: string}>
     */
    protected function compensationTypes(): array
    {
        return [
            ['value' => 'hourly', 'label' => 'Hourly'],
            ['value' => 'project', 'label' => 'Per Project'],
            ['value' => 'salary', 'label' => 'Salary'],
        ];
    }
}
