<?php

namespace App\Http\Controllers;

use App\Models\Application;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class CreativeDashboardController extends Controller
{
    /**
     * Show the creative dashboard.
     */
    public function index(): Response
    {
        $user = Auth::user();

        if (!$user->isCreative()) {
            abort(403, 'Access denied. Creative account required.');
        }

        $profile = $user->creativeProfile;

        $activeApplications = $user->applications()
            ->whereIn('status', [
                Application::STATUS_PENDING,
                Application::STATUS_SHORTLISTED,
            ])
            ->count();

        $shortlistedApplications = $user->applications()
            ->where('status', Application::STATUS_SHORTLISTED)
            ->count();

        $recentApplications = $user->applications()
            ->with(['job:id,slug,title'])
            ->latest()
            ->limit(5)
            ->get()
            ->map(fn (Application $application) => [
                'id' => $application->id,
                'status' => $application->status,
                'applied_at' => $application->created_at?->toIso8601String(),
                'job' => [
                    'title' => $application->job?->title,
                    'slug' => $application->job?->slug,
                ],
            ])
            ->all();

        return Inertia::render('dashboard/creative', [
            'user' => $user,
            'profile' => $profile,
            'completionScore' => $user->profile_completion_score,
            'profileComplete' => $user->profile_completed_at !== null,
            'stats' => [
                'activeApplications' => $activeApplications,
                'shortlistedApplications' => $shortlistedApplications,
            ],
            'recentApplications' => $recentApplications,
        ]);
    }
}
