<?php

namespace App\Http\Controllers;

use App\Models\Job;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Str;
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
            'summary' => $data['summary'] ?? null,
            'description' => $data['description'],
            'published_at' => $status === Job::STATUS_PUBLISHED ? Date::now() : null,
        ]);

        return redirect()
            ->route('opportunity-owner.jobs.edit', $job)
            ->with('success', $status === Job::STATUS_PUBLISHED ? 'Job published successfully.' : 'Job saved as draft.');
    }

    /**
     * Show the form for editing the specified job.
     */
    public function edit(Job $job): Response
    {
        $this->authorize('update', $job);

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
                'summary' => $job->summary,
                'description' => $job->description,
                'published_at' => $job->published_at?->toIso8601String(),
            ],
            'compensationTypes' => $this->compensationTypes(),
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
            'summary' => $data['summary'] ?? null,
            'description' => $data['description'],
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
        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'summary' => ['nullable', 'string', 'max:500'],
            'description' => ['required', 'string'],
            'location' => ['nullable', 'string', 'max:255'],
            'is_remote' => ['nullable', 'boolean'],
            'compensation_type' => ['nullable', 'in:hourly,project,salary'],
            'compensation_min' => ['nullable', 'numeric', 'min:0'],
            'compensation_max' => ['nullable', 'numeric', 'min:0'],
            'tags' => ['nullable'],
        ]);

        if (! empty($data['compensation_min']) && ! empty($data['compensation_max'])
            && $data['compensation_min'] > $data['compensation_max']) {
            throw ValidationException::withMessages([
                'compensation_max' => 'Maximum compensation must be greater than or equal to minimum compensation.',
            ]);
        }

        $data['tags'] = $this->normalizeTags($request->input('tags'));
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
