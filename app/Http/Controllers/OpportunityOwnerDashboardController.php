<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
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

        return Inertia::render('dashboard/opportunity-owner', [
            'user' => $user,
            'profile' => $profile,
            'completionScore' => $user->profile_completion_score,
            'profileComplete' => $user->profile_completed_at !== null,
            'isVerified' => $profile?->is_verified ?? false,
        ]);
    }
}
