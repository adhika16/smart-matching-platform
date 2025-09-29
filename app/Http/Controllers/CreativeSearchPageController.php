<?php

namespace App\Http\Controllers;

use App\Models\Job;
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
        $jobs = Job::where('opportunity_owner_id', $user->opportunityOwnerProfile->id ?? null)
            ->where('status', 'published')
            ->select('id', 'title')
            ->orderBy('created_at', 'desc')
            ->get();

        return Inertia::render('opportunity-owner/creatives/index', [
            'jobs' => $jobs,
        ]);
    }
}
