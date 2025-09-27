<?php

namespace App\Http\Controllers;

use App\Models\Application;
use App\Models\Job;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class ApplicationController extends Controller
{
    /**
     * Store a new job application from a creative.
     */
    public function store(Request $request, Job $job): RedirectResponse
    {
        $this->authorize('create', [Application::class, $job]);

        $user = $request->user();

        $existing = $job->applications()->where('user_id', $user->id)->exists();

        if ($existing) {
            throw ValidationException::withMessages([
                'cover_letter' => 'You have already applied to this job.',
            ]);
        }

        $data = $request->validate([
            'cover_letter' => ['nullable', 'string', 'max:5000'],
        ]);

        $job->applications()->create([
            'user_id' => $user->id,
            'status' => Application::STATUS_PENDING,
            'cover_letter' => $data['cover_letter'] ?? null,
        ]);

        return redirect()
            ->route('creative.jobs.show', $job->slug)
            ->with('success', 'Application submitted successfully.');
    }

    /**
     * Update an application status by the opportunity owner.
     */
    public function update(Request $request, Job $job, Application $application): RedirectResponse
    {
    $application->loadMissing('job');

    abort_if($application->job_id !== $job->id, 404);

    $this->authorize('update', $application);

        $data = $request->validate([
            'status' => ['required', 'in:'.implode(',', [
                Application::STATUS_PENDING,
                Application::STATUS_SHORTLISTED,
                Application::STATUS_REJECTED,
            ])],
        ]);

        $application->update([
            'status' => $data['status'],
        ]);

        return back()->with('success', 'Application status updated.');
    }
}
