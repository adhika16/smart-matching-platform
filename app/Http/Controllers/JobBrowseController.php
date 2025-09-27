<?php

namespace App\Http\Controllers;

use App\Models\Application;
use App\Models\Job;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
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
            'category' => $request->string('category')->toString(),
            'skill' => $request->string('skill')->toString(),
        ];

        $searchTerm = $filters['search'];

        $query = null;

        if ($searchTerm !== '') {
            $query = Job::search($searchTerm)
                ->where('status', Job::STATUS_PUBLISHED);

            if ($filters['category']) {
                $query->where('category', $filters['category']);
            }

            if ($filters['skill']) {
                $query->where('skills', $filters['skill']);
            }

            if ($filters['remote']) {
                $query->where('is_remote', true);
            }

            if ($filters['location']) {
                $query->where('location', $filters['location']);
            }

            if ($filters['tag']) {
                $query->where('tags', $filters['tag']);
            }

            $jobsPaginator = $query
                ->query(fn ($eloquent) => $eloquent->with(['owner.opportunityOwnerProfile']))
                ->paginate(perPage: 10, pageName: 'page', page: $request->integer('page', 1));
        } else {
            $jobsQuery = Job::query()
                ->with(['owner.opportunityOwnerProfile'])
                ->where('status', Job::STATUS_PUBLISHED)
                ->orderByDesc('published_at');

            if ($filters['location']) {
                $jobsQuery->where('location', 'like', '%'.$filters['location'].'%');
            }

            if ($filters['remote']) {
                $jobsQuery->where('is_remote', true);
            }

            if ($filters['tag']) {
                $jobsQuery->whereJsonContains('tags', $filters['tag']);
            }

            if ($filters['category']) {
                $jobsQuery->where('category', $filters['category']);
            }

            if ($filters['skill']) {
                $jobsQuery->whereJsonContains('skills', $filters['skill']);
            }

            $jobsPaginator = $jobsQuery->paginate(10)
                ->withQueryString();
        }

        $jobs = $jobsPaginator
            ->withQueryString()
            ->through(fn (Job $job) => [
                'id' => $job->id,
                'slug' => $job->slug,
                'title' => $job->title,
                'summary' => $job->summary,
                'location' => $job->location,
                'is_remote' => $job->is_remote,
                'tags' => $job->tags,
                'skills' => $job->skills,
                'category' => $job->category,
                'published_at' => $job->published_at?->toIso8601String(),
                'budget_min' => $job->budget_min,
                'budget_max' => $job->budget_max,
                'timeline_start' => $job->timeline_start instanceof Carbon ? $job->timeline_start->format('Y-m-d') : $job->timeline_start,
                'timeline_end' => $job->timeline_end instanceof Carbon ? $job->timeline_end->format('Y-m-d') : $job->timeline_end,
                'company' => $job->owner?->opportunityOwnerProfile?->company_name,
            ]);

        return Inertia::render('creative/jobs/index', [
            'jobs' => $jobs,
            'filters' => $filters,
            'taxonomy' => config('taxonomy'),
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
                'skills' => $job->skills,
                'category' => $job->category,
                'published_at' => $job->published_at?->toIso8601String(),
                'timeline_start' => $job->timeline_start instanceof Carbon ? $job->timeline_start->format('Y-m-d') : $job->timeline_start,
                'timeline_end' => $job->timeline_end instanceof Carbon ? $job->timeline_end->format('Y-m-d') : $job->timeline_end,
                'budget_min' => $job->budget_min,
                'budget_max' => $job->budget_max,
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
