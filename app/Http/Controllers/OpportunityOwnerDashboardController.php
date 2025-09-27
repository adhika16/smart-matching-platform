<?php

namespace App\Http\Controllers;

use App\Models\Application;
use App\Models\Job;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class OpportunityOwnerDashboardController extends Controller
{
    /**
     * Show the opportunity owner dashboard.
     */
    public function index(): Response
    {
        $user = Auth::user();

        if (!$user->isOpportunityOwner()) {
            abort(403, 'Access denied. Opportunity owner account required.');
        }

        $profile = $user->opportunityOwnerProfile;

        $jobCounts = $user->jobs()
            ->selectRaw('status, count(*) as aggregate')
            ->groupBy('status')
            ->pluck('aggregate', 'status');

        $totalApplications = $user->receivedApplications()->count();
        $pendingApplications = $user->receivedApplications()
            ->where('applications.status', Application::STATUS_PENDING)
            ->count();

        $recentApplications = $user->receivedApplications()
            ->with([
                'applicant:id,name,email',
                'job:id,title,slug,status',
            ])
            ->latest()
            ->limit(5)
            ->get()
            ->map(fn (Application $application) => [
                'id' => $application->id,
                'status' => $application->status,
                'submitted_at' => $application->created_at?->toIso8601String(),
                'applicant' => [
                    'name' => $application->applicant?->name,
                    'email' => $application->applicant?->email,
                ],
                'job' => [
                    'id' => $application->job?->id,
                    'title' => $application->job?->title,
                    'slug' => $application->job?->slug,
                    'status' => $application->job?->status,
                ],
            ])
            ->all();

        $jobApplicationOverview = $user->jobs()
            ->withCount([
                'applications as applications_count',
                'applications as pending_applications_count' => fn ($query) => $query
                    ->where('status', Application::STATUS_PENDING),
                'applications as shortlisted_applications_count' => fn ($query) => $query
                    ->where('status', Application::STATUS_SHORTLISTED),
            ])
            ->orderByDesc('updated_at')
            ->limit(6)
            ->get(['id', 'title', 'slug', 'status', 'published_at'])
            ->map(fn (Job $job) => [
                'id' => $job->id,
                'title' => $job->title,
                'slug' => $job->slug,
                'status' => $job->status,
                'published_at' => $job->published_at?->toIso8601String(),
                'applications_count' => $job->applications_count,
                'pending_count' => $job->pending_applications_count,
                'shortlisted_count' => $job->shortlisted_applications_count,
            ])
            ->all();

        return Inertia::render('dashboard/opportunity-owner', [
            'user' => $user,
            'profile' => $profile,
            'completionScore' => $user->profile_completion_score,
            'profileComplete' => $user->profile_completed_at !== null,
            'isVerified' => $profile?->is_verified ?? false,
            'jobStats' => [
                'published' => (int) ($jobCounts[Job::STATUS_PUBLISHED] ?? 0),
                'draft' => (int) ($jobCounts[Job::STATUS_DRAFT] ?? 0),
                'archived' => (int) ($jobCounts[Job::STATUS_ARCHIVED] ?? 0),
            ],
            'applicationStats' => [
                'total' => $totalApplications,
                'pending' => $pendingApplications,
            ],
            'recentApplications' => $recentApplications,
            'jobApplicationOverview' => $jobApplicationOverview,
        ]);
    }
}
