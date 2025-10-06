<?php

namespace App\Http\Controllers;

use App\Models\Job;
use App\Models\CreativeProfile;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class CreativeSearchPageController extends Controller
{
    public function index(Request $request): Response
    {
        $user = $request->user();

        if ($user->user_type !== 'opportunity_owner') {
            abort(403);
        }

        // Get user's jobs for context dropdown
        $jobs = Job::where('user_id', $user->id)
            ->where('status', 'published')
            ->select('id', 'title')
            ->orderBy('created_at', 'desc')
            ->get();

        return Inertia::render('opportunity-owner/creatives/index', [
            'jobs' => $jobs,
        ]);
    }

    public function show(Request $request, CreativeProfile $creative): Response
    {
        $user = $request->user();

        if ($user->user_type !== 'opportunity_owner') {
            abort(403);
        }

        // Load the creative profile with user relationship
        $creative->load('user');

        return Inertia::render('opportunity-owner/creatives/show', [
            'creative' => [
                'id' => $creative->id,
                'user_id' => $creative->user_id,
                'name' => $creative->user->name,
                'email' => $creative->user->email,
                'bio' => $creative->bio,
                'skills' => $creative->skills,
                'experience_level' => $creative->experience_level,
                'location' => $creative->location,
                'portfolio_url' => $creative->portfolio_url,
                'created_at' => $creative->created_at?->toISOString(),
                'updated_at' => $creative->updated_at?->toISOString(),
            ],
        ]);
    }
}
