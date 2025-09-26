<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
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

        return Inertia::render('dashboard/creative', [
            'user' => $user,
            'profile' => $profile,
            'completionScore' => $user->profile_completion_score,
            'profileComplete' => $user->profile_completed_at !== null,
        ]);
    }
}
