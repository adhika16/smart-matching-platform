<?php

namespace App\Http\Controllers;

use App\Models\CreativeProfile;
use App\Models\OpportunityOwnerProfile;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class ProfileController extends Controller
{
    /**
     * Show the profile setup page.
     */
    public function setup(): Response
    {
        $user = Auth::user();
        $profile = $user->getActiveProfile();

        return Inertia::render('profile/setup', [
            'user' => $user,
            'profile' => $profile,
            'userType' => $user->user_type,
            'completionScore' => $user->profile_completion_score,
        ]);
    }

    /**
     * Update creative profile.
     */
    public function updateCreative(Request $request): RedirectResponse
    {
        $user = Auth::user();

        if (!$user->isCreative()) {
            abort(403, 'Unauthorized access.');
        }

        $validated = $request->validate([
            'bio' => 'nullable|string|max:1000',
            'skills' => 'nullable|array',
            'skills.*' => 'string|max:50',
            'portfolio_links' => 'nullable|array',
            'portfolio_links.*' => 'url',
            'location' => 'nullable|string|max:100',
            'hourly_rate' => 'nullable|numeric|min:0',
            'experience_level' => 'nullable|in:beginner,intermediate,expert',
            'available_for_work' => 'nullable|boolean',
        ]);

        $validated['available_for_work'] = $request->boolean('available_for_work');

        $user->creativeProfile()->updateOrCreate(
            ['user_id' => $user->id],
            $validated
        );

        $user->updateProfileCompletionScore();

        return redirect()->back()->with('success', 'Profile updated successfully!');
    }

    /**
     * Update opportunity owner profile.
     */
    public function updateOpportunityOwner(Request $request): RedirectResponse
    {
        $user = Auth::user();

        if (!$user->isOpportunityOwner()) {
            abort(403, 'Unauthorized access.');
        }

        $validated = $request->validate([
            'company_name' => 'required|string|max:255',
            'company_description' => 'nullable|string|max:1000',
            'company_website' => 'nullable|url',
            'company_size' => 'nullable|string|max:50',
            'industry' => 'nullable|string|max:100',
        ]);

        $user->opportunityOwnerProfile()->updateOrCreate(
            ['user_id' => $user->id],
            $validated
        );

        $user->updateProfileCompletionScore();

        return redirect()->back()->with('success', 'Profile updated successfully!');
    }

    /**
     * Show the user's profile.
     */
    public function show(): Response
    {
        $user = Auth::user();
        $profile = $user->getActiveProfile();

        return Inertia::render('profile/show', [
            'user' => $user,
            'profile' => $profile,
            'userType' => $user->user_type,
            'completionScore' => $user->profile_completion_score,
        ]);
    }
}
