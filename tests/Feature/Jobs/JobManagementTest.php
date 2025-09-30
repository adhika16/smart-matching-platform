<?php

use App\Models\Job;
use App\Models\OpportunityOwnerProfile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\assertDatabaseMissing;
use function Pest\Laravel\get;
use function Pest\Laravel\patch;
use function Pest\Laravel\post;

uses(RefreshDatabase::class);

function createVerifiedOpportunityOwner(): User
{
    $user = User::factory()->opportunityOwner()->create();

    OpportunityOwnerProfile::factory()->for($user)->create([
        'is_verified' => true,
        'verified_at' => now(),
    ]);

    return $user;
}

test('verified opportunity owner can create and publish a job', function () {
    $user = createVerifiedOpportunityOwner();

    actingAs($user)
        ->post(route('opportunity-owner.jobs.store'), [
            'title' => 'Senior Product Designer',
            'summary' => 'Help us design beautiful experiences.',
            'description' => 'We are seeking a designer with 5+ years of experience.',
            'location' => 'Remote',
            'is_remote' => true,
            'compensation_type' => 'salary',
            'budget_min' => 80000,
            'budget_max' => 120000,
            'tags' => 'design,product,ux',
            'status' => 'published',
        ])
        ->assertRedirect();

    $job = Job::first();

    expect($job)
        ->not->toBeNull()
        ->status->toBe(Job::STATUS_PUBLISHED)
        ->published_at->not->toBeNull();
});

test('unverified opportunity owner cannot create a job', function () {
    $user = User::factory()->opportunityOwner()->create();

    OpportunityOwnerProfile::factory()->for($user)->unverified()->create();

    actingAs($user)
        ->post(route('opportunity-owner.jobs.store'), [
            'title' => 'Junior Copywriter',
            'description' => 'Entry-level writing role.',
        ])
        ->assertForbidden();

    assertDatabaseMissing('job_postings', ['title' => 'Junior Copywriter']);
});

test('creative users cannot access job creation', function () {
    $user = User::factory()->creative()->create();

    actingAs($user)
        ->get(route('opportunity-owner.jobs.create'))
        ->assertForbidden();
});

test('opportunity owner can publish and archive job via dedicated endpoints', function () {
    $user = createVerifiedOpportunityOwner();
    $job = Job::factory()->for($user)->create([
        'status' => Job::STATUS_DRAFT,
    ]);

    actingAs($user)
        ->patch(route('opportunity-owner.jobs.publish', $job))
        ->assertRedirect();

    $job->refresh();
    expect($job->status)->toBe(Job::STATUS_PUBLISHED);

    actingAs($user)
        ->patch(route('opportunity-owner.jobs.archive', $job))
        ->assertRedirect();

    $job->refresh();
    expect($job->status)->toBe(Job::STATUS_ARCHIVED);
});

test('users cannot modify jobs they do not own', function () {
    $owner = createVerifiedOpportunityOwner();
    $job = Job::factory()->for($owner)->create();

    $otherUser = createVerifiedOpportunityOwner();

    actingAs($otherUser)
        ->patch(route('opportunity-owner.jobs.publish', $job))
        ->assertForbidden();

    $job->refresh();
    expect($job->status)->toBe(Job::STATUS_DRAFT);
});
