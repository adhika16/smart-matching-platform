<?php

use App\Models\Application;
use App\Models\Job;
use App\Models\OpportunityOwnerProfile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\post;
use function Pest\Laravel\patch;

uses(RefreshDatabase::class);

function createVerifiedOwner(): User
{
    $owner = User::factory()->opportunityOwner()->create();

    OpportunityOwnerProfile::factory()->for($owner)->create([
        'is_verified' => true,
        'verified_at' => now(),
    ]);

    return $owner;
}

test('creative user can browse published jobs', function () {
    $owner = createVerifiedOwner();
    $job = Job::factory()->for($owner)->published()->create([
        'published_at' => now()->subDay(),
    ]);

    $creative = User::factory()->creative()->create();

    actingAs($creative)
        ->get(route('creative.jobs.index'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('creative/jobs/index')
            ->where('jobs.data.0.title', $job->title)
        );
});

test('creative can apply to a job once', function () {
    $owner = createVerifiedOwner();
    $job = Job::factory()->for($owner)->published()->create([
        'published_at' => now(),
    ]);

    $creative = User::factory()->creative()->create();

    actingAs($creative)
        ->post(route('creative.jobs.apply', $job->slug), [
            'cover_letter' => 'I am excited to contribute.',
        ])
        ->assertRedirect(route('creative.jobs.show', $job->slug));

    assertDatabaseHas('applications', [
        'job_id' => $job->id,
        'user_id' => $creative->id,
        'status' => Application::STATUS_PENDING,
    ]);

    actingAs($creative)
        ->post(route('creative.jobs.apply', $job->slug), [
            'cover_letter' => 'Trying again',
        ])
        ->assertSessionHasErrors('cover_letter');
});

test('opportunity owner can update application status', function () {
    $owner = createVerifiedOwner();
    $job = Job::factory()->for($owner)->published()->create([
        'published_at' => now(),
    ]);

    $creative = User::factory()->creative()->create();

    $application = Application::factory()->for($job)->create([
        'user_id' => $creative->id,
        'status' => Application::STATUS_PENDING,
    ]);

    actingAs($owner)
        ->patch(route('opportunity-owner.jobs.applications.update', [$job->id, $application->id]), [
            'status' => Application::STATUS_SHORTLISTED,
        ])
        ->assertRedirect();

    $application->refresh();

    expect($application->status)->toBe(Application::STATUS_SHORTLISTED);
});

test('creative dashboard reflects application stats', function () {
    $owner = createVerifiedOwner();

    $publishedJob = Job::factory()->for($owner)->published()->create([
        'published_at' => now()->subDay(),
    ]);

    $secondJob = Job::factory()->for($owner)->published()->create([
        'published_at' => now()->subHours(12),
    ]);

    $creative = User::factory()->creative()->create();

    Application::factory()->for($publishedJob)->create([
        'user_id' => $creative->id,
        'status' => Application::STATUS_PENDING,
        'created_at' => now()->subDay(),
    ]);

    Application::factory()->for($secondJob)->shortlisted()->create([
        'user_id' => $creative->id,
        'created_at' => now()->subHours(6),
    ]);

    actingAs($creative)
        ->get(route('dashboard.creative'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('dashboard/creative')
            ->where('stats.activeApplications', 2)
            ->where('stats.shortlistedApplications', 1)
            ->has('recentApplications', 2)
            ->where('recentApplications.0.status', Application::STATUS_SHORTLISTED)
            ->where('recentApplications.1.status', Application::STATUS_PENDING)
        );
});

test('opportunity owner dashboard surfaces applicant insights', function () {
    $owner = createVerifiedOwner();

    $creativeA = User::factory()->creative()->create();
    $creativeB = User::factory()->creative()->create();

    $jobLive = Job::factory()->for($owner)->published()->create([
        'title' => 'Brand Identity Specialist',
        'slug' => 'brand-identity-specialist',
        'published_at' => now()->subDays(1),
        'updated_at' => now()->subMinutes(5),
    ]);

    $jobDraft = Job::factory()->for($owner)->create([
        'title' => 'UX Copywriter',
        'slug' => 'ux-copywriter',
        'updated_at' => now()->subMinutes(10),
    ]);

    Application::factory()->create([
        'job_id' => $jobLive->id,
        'user_id' => $creativeA->id,
        'status' => Application::STATUS_PENDING,
        'created_at' => now()->subMinutes(9),
    ]);

    Application::factory()->create([
        'job_id' => $jobLive->id,
        'user_id' => $creativeB->id,
        'status' => Application::STATUS_SHORTLISTED,
        'created_at' => now()->subMinutes(3),
    ]);

    Application::factory()->create([
        'job_id' => $jobDraft->id,
        'user_id' => $creativeA->id,
        'status' => Application::STATUS_PENDING,
        'created_at' => now()->subMinutes(2),
    ]);

    $jobLive->touch();

    actingAs($owner)
        ->get(route('dashboard.opportunity-owner'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('dashboard/opportunity-owner')
            ->where('applicationStats.total', 3)
            ->where('applicationStats.pending', 2)
            ->has('recentApplications', 3)
            ->where('recentApplications.0.job.title', 'UX Copywriter')
            ->where('recentApplications.0.status', Application::STATUS_PENDING)
            ->where('recentApplications.1.status', Application::STATUS_SHORTLISTED)
            ->where('recentApplications.2.status', Application::STATUS_PENDING)
            ->has('jobApplicationOverview', 2)
            ->where('jobApplicationOverview.0.title', 'Brand Identity Specialist')
            ->where('jobApplicationOverview.0.applications_count', 2)
            ->where('jobApplicationOverview.0.shortlisted_count', 1)
            ->where('jobApplicationOverview.1.title', 'UX Copywriter')
            ->where('jobApplicationOverview.1.pending_count', 1)
        );
});
