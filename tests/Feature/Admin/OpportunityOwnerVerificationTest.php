<?php

use App\Models\OpportunityOwnerProfile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\get;
use function Pest\Laravel\post;

uses(RefreshDatabase::class);

test('admin can approve an opportunity owner profile', function () {
    $admin = User::factory()->admin()->create();
    $profile = OpportunityOwnerProfile::factory()->unverified()->create();

    actingAs($admin)
        ->post(route('admin.opportunity-owners.approve', $profile))
        ->assertSessionHasNoErrors()
        ->assertRedirect();

    $profile->refresh();

    expect($profile->is_verified)->toBeTrue();
    expect($profile->verified_at)->not->toBeNull();

    assertDatabaseHas('opportunity_owner_profiles', [
        'id' => $profile->id,
        'is_verified' => true,
    ]);
});

test('admin can mark an opportunity owner as unverified', function () {
    $admin = User::factory()->admin()->create();
    $profile = OpportunityOwnerProfile::factory()->create();

    actingAs($admin)
        ->post(route('admin.opportunity-owners.reject', $profile))
        ->assertRedirect();

    $profile->refresh();

    expect($profile->is_verified)->toBeFalse();
    expect($profile->verified_at)->toBeNull();
});

test('non-admin users cannot access opportunity owner admin routes', function () {
    $user = User::factory()->create();

    actingAs($user)
        ->get(route('admin.opportunity-owners.index'))
        ->assertForbidden();

    $profile = OpportunityOwnerProfile::factory()->unverified()->create();

    actingAs($user)
        ->post(route('admin.opportunity-owners.approve', $profile))
        ->assertForbidden();
});

test('admin dashboard redirect sends user to admin verification page', function () {
    $admin = User::factory()->admin()->create();

    actingAs($admin)
        ->get('/dashboard')
        ->assertRedirect(route('admin.opportunity-owners.index'));
});
