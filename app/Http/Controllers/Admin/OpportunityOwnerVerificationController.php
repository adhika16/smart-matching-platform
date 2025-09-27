<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\OpportunityOwnerProfile;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Date;
use Inertia\Inertia;
use Inertia\Response;

class OpportunityOwnerVerificationController extends Controller
{
    /**
     * Display a listing of opportunity owner profiles awaiting verification.
     */
    public function index(): Response
    {
        $pendingProfiles = OpportunityOwnerProfile::with('user')
            ->where('is_verified', false)
            ->orderByDesc('created_at')
            ->get()
            ->map(fn (OpportunityOwnerProfile $profile) => [
                'id' => $profile->id,
                'company_name' => $profile->company_name,
                'industry' => $profile->industry,
                'company_size' => $profile->company_size,
                'submitted_at' => $profile->created_at?->toIso8601String(),
                'user' => [
                    'id' => $profile->user->id,
                    'name' => $profile->user->name,
                    'email' => $profile->user->email,
                ],
            ]);

        $recentlyVerified = OpportunityOwnerProfile::with('user')
            ->where('is_verified', true)
            ->orderByDesc('verified_at')
            ->take(5)
            ->get()
            ->map(fn (OpportunityOwnerProfile $profile) => [
                'id' => $profile->id,
                'company_name' => $profile->company_name,
                'verified_at' => $profile->verified_at?->toIso8601String(),
                'user' => [
                    'name' => $profile->user->name,
                ],
            ]);

        return Inertia::render('admin/opportunity-owners/index', [
            'pendingProfiles' => $pendingProfiles,
            'recentlyVerified' => $recentlyVerified,
        ]);
    }

    /**
     * Approve the specified opportunity owner profile.
     */
    public function approve(OpportunityOwnerProfile $opportunityOwnerProfile): RedirectResponse
    {
        $opportunityOwnerProfile->update([
            'is_verified' => true,
            'verified_at' => Date::now(),
        ]);

        return back()->with('success', 'Opportunity owner approved successfully.');
    }

    /**
     * Reject the specified opportunity owner profile.
     */
    public function reject(OpportunityOwnerProfile $opportunityOwnerProfile): RedirectResponse
    {
        $opportunityOwnerProfile->update([
            'is_verified' => false,
            'verified_at' => null,
        ]);

        return back()->with('success', 'Opportunity owner marked as unverified.');
    }
}
