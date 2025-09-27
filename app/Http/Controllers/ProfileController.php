<?php

namespace App\Http\Controllers;

use App\Models\OpportunityOwnerProfile;
use App\Models\OpportunityOwnerVerificationLog;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Arr;
use Illuminate\Database\Eloquent\Model;
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
            'profile' => $this->serializeProfile($profile),
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
            'verification_documents' => 'nullable|array',
            'verification_documents.*' => 'file|mimes:pdf,jpg,jpeg,png,webp|max:10240',
            'verification_note' => 'nullable|string|max:500',
        ]);

        $uploadedDocuments = [];

        DB::transaction(function () use ($user, $validated, $request, &$uploadedDocuments): void {
            $profile = $user->opportunityOwnerProfile()->updateOrCreate(
                ['user_id' => $user->id],
                Arr::except($validated, ['verification_documents', 'verification_note'])
            );

            if ($request->hasFile('verification_documents')) {
                $existingDocuments = collect($profile->verification_documents ?? []);

                foreach ($request->file('verification_documents') as $file) {
                    if ($file === null) {
                        continue;
                    }

                    $path = $file->store('verification-documents/'.$user->id, 'public');

                    $document = [
                        'path' => $path,
                        'disk' => 'public',
                        'original_name' => $file->getClientOriginalName(),
                        'uploaded_at' => now()->toIso8601String(),
                    ];

                    $existingDocuments->push($document);
                    $uploadedDocuments[] = $document;
                }

                $profile->forceFill([
                    'verification_documents' => $existingDocuments->values()->all(),
                ])->save();
            }

            if ($uploadedDocuments !== []) {
                OpportunityOwnerVerificationLog::create([
                    'opportunity_owner_profile_id' => $profile->id,
                    'actor_id' => $user->id,
                    'actor_role' => 'opportunity_owner',
                    'action' => 'documents_uploaded',
                    'notes' => $validated['verification_note'] ?? null,
                    'metadata' => [
                        'documents' => array_map(
                            fn (array $document) => Arr::only($document, ['original_name', 'path']),
                            $uploadedDocuments
                        ),
                    ],
                ]);
            }

            $profile->refresh();
            $user->setRelation('opportunityOwnerProfile', $profile);
        });

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
            'profile' => $this->serializeProfile($profile),
            'userType' => $user->user_type,
            'completionScore' => $user->profile_completion_score,
        ]);
    }

    /**
     * @return array<string, mixed>|null
     */
    private function serializeProfile(?Model $profile): ?array
    {
        if ($profile === null) {
            return null;
        }

        if ($profile instanceof OpportunityOwnerProfile) {
            $documents = collect($profile->verification_documents ?? [])->map(function (array $document) {
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
            })->values();

            return array_merge($profile->toArray(), [
                'verification_documents' => $documents,
            ]);
        }

        return $profile->toArray();
    }
}
