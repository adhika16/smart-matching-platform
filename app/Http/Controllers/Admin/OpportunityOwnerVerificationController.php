<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\OpportunityOwnerProfile;
use App\Models\OpportunityOwnerVerificationLog;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class OpportunityOwnerVerificationController extends Controller
{
    /**
     * Display a listing of opportunity owner profiles awaiting verification.
     */
    public function index(): Response
    {
        $pendingProfiles = OpportunityOwnerProfile::with([
            'user',
            'verificationLogs' => fn ($query) => $query
                ->with('actor:id,name')
                ->latest()
                ->limit(5),
        ])
            ->where('is_verified', false)
            ->orderByDesc('created_at')
            ->get()
            ->map(fn (OpportunityOwnerProfile $profile) => [
                'id' => $profile->id,
                'company_name' => $profile->company_name,
                'industry' => $profile->industry,
                'company_size' => $profile->company_size,
                'submitted_at' => $profile->created_at?->toIso8601String(),
                'verification_documents' => collect($profile->verification_documents ?? [])->map(function (array $document) {
                    $disk = $document['disk'] ?? 'public';
                    $path = $document['path'] ?? null;
                    $url = null;

                    if ($path) {
                        $filesystem = Storage::disk($disk);
                        $url = method_exists($filesystem, 'url') ? $filesystem->url($path) : null;
                    }

                    return [
                        'original_name' => $document['original_name'] ?? ($path ? basename($path) : 'document'),
                        'uploaded_at' => $document['uploaded_at'] ?? null,
                        'url' => $url,
                    ];
                })->values()->all(),
                'logs' => $profile->verificationLogs->map(fn (OpportunityOwnerVerificationLog $log) => [
                    'id' => $log->id,
                    'action' => $log->action,
                    'actor_role' => $log->actor_role,
                    'actor_name' => $log->actor?->name,
                    'notes' => $log->notes,
                    'created_at' => $log->created_at?->toIso8601String(),
                ])->values()->all(),
                'user' => [
                    'id' => $profile->user->id,
                    'name' => $profile->user->name,
                    'email' => $profile->user->email,
                ],
            ]);

        $recentlyVerified = OpportunityOwnerProfile::with([
            'user',
            'verificationLogs' => fn ($query) => $query
                ->with('actor:id,name')
                ->latest()
                ->limit(1),
        ])
            ->where('is_verified', true)
            ->orderByDesc('verified_at')
            ->take(5)
            ->get()
            ->map(fn (OpportunityOwnerProfile $profile) => [
                'id' => $profile->id,
                'company_name' => $profile->company_name,
                'verified_at' => $profile->verified_at?->toIso8601String(),
                'last_action' => $profile->verificationLogs->first()?->only(['action', 'notes']) ?? null,
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
    public function approve(Request $request, OpportunityOwnerProfile $opportunityOwnerProfile): RedirectResponse
    {
        $data = $request->validate([
            'notes' => 'nullable|string|max:1000',
        ]);

        $opportunityOwnerProfile->update([
            'is_verified' => true,
            'verified_at' => Date::now(),
        ]);

        OpportunityOwnerVerificationLog::create([
            'opportunity_owner_profile_id' => $opportunityOwnerProfile->id,
            'actor_id' => $request->user()->id,
            'actor_role' => 'admin',
            'action' => 'approved',
            'notes' => $data['notes'] ?? null,
        ]);

        return back()->with('success', 'Opportunity owner approved successfully.');
    }

    /**
     * Reject the specified opportunity owner profile.
     */
    public function reject(Request $request, OpportunityOwnerProfile $opportunityOwnerProfile): RedirectResponse
    {
        $data = $request->validate([
            'notes' => 'nullable|string|max:1000',
        ]);

        $opportunityOwnerProfile->update([
            'is_verified' => false,
            'verified_at' => null,
        ]);

        OpportunityOwnerVerificationLog::create([
            'opportunity_owner_profile_id' => $opportunityOwnerProfile->id,
            'actor_id' => $request->user()->id,
            'actor_role' => 'admin',
            'action' => 'rejected',
            'notes' => $data['notes'] ?? null,
        ]);

        return back()->with('success', 'Opportunity owner marked as unverified.');
    }
}
