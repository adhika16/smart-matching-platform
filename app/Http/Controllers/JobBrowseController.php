<?php

namespace App\Http\Controllers;

use App\Models\Application;
use App\Models\Job;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class JobBrowseController extends Controller
{
    /**
     * Display a paginated list of published jobs for creatives.
     */
    public function index(Request $request): Response
    {
        $filters = [
            'search' => $request->string('search')->toString(),
            'location' => $request->string('location')->toString(),
            'remote' => $request->boolean('remote'),
            'tag' => $request->string('tag')->toString(),
        ];

        $jobsQuery = Job::query()
            ->with(['owner.opportunityOwnerProfile'])
            ->where('status', Job::STATUS_PUBLISHED)
            ->orderByDesc('published_at');

        if ($filters['search']) {
            $jobsQuery->where(function ($query) use ($filters) {
                $query->where('title', 'like', '%'.$filters['search'].'%')
                    ->orWhere('summary', 'like', '%'.$filters['search'].'%')
                    ->orWhere('description', 'like', '%'.$filters['search'].'%');
            });
        }

        if ($filters['location']) {
            $jobsQuery->where('location', 'like', '%'.$filters['location'].'%');
        }

        if ($filters['remote']) {
            $jobsQuery->where('is_remote', true);
        }

        if ($filters['tag']) {
            $jobsQuery->whereJsonContains('tags', $filters['tag']);
        }

        $jobs = $jobsQuery
            ->paginate(10)
            ->withQueryString()
            ->through(fn (Job $job) => [
                'id' => $job->id,
                'slug' => $job->slug,
                'title' => $job->title,
                'summary' => $job->summary,
                'location' => $job->location,
                'is_remote' => $job->is_remote,
                'tags' => $job->tags,
                'published_at' => $job->published_at?->toIso8601String(),
                'company' => $job->owner?->opportunityOwnerProfile?->company_name,
            ]);

        return Inertia::render('creative/jobs/index', [
            'jobs' => $jobs,
            'filters' => $filters,
        ]);
    }

    /**
     * Display a single published job.
     */
    public function show(Request $request, Job $job): Response
    {
        abort_unless($job->status === Job::STATUS_PUBLISHED, 404);

        $job->loadMissing(['owner.opportunityOwnerProfile']);

        $hasApplied = Application::query()
            ->where('job_id', $job->id)
            ->where('user_id', $request->user()->id)
            ->exists();

        return Inertia::render('creative/jobs/show', [
            'job' => [
                'id' => $job->id,
                'slug' => $job->slug,
                'title' => $job->title,
                'summary' => $job->summary,
                'description' => $job->description,
                'location' => $job->location,
                'is_remote' => $job->is_remote,
                'tags' => $job->tags,
                'published_at' => $job->published_at?->toIso8601String(),
                'company' => [
                    'name' => $job->owner?->opportunityOwnerProfile?->company_name,
                    'industry' => $job->owner?->opportunityOwnerProfile?->industry,
                    'size' => $job->owner?->opportunityOwnerProfile?->company_size,
                    'website' => $job->owner?->opportunityOwnerProfile?->company_website,
                ],
            ],
            'hasApplied' => $hasApplied,
        ]);
    }
}
