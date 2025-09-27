<?php

use App\Models\OpportunityOwnerProfile;
use App\Models\OpportunityOwnerVerificationLog;
use App\Models\User;
use Illuminate\Support\Facades\Date;
use function Pest\Laravel\actingAs;
use function Pest\Laravel\post;

it('records an audit log when an admin approves an opportunity owner', function () {
    Date::setTestNow(now());

    /** @var User $admin */
    $admin = User::factory()->admin()->create();

    /** @var User $owner */
    $owner = User::factory()->opportunityOwner()->create();

    /** @var OpportunityOwnerProfile $profile */
    $profile = $owner->opportunityOwnerProfile()->create([
        'company_name' => 'CoLab Labs',
    ]);

    actingAs($admin);

    post(route('admin.opportunity-owners.approve', $profile), [
        'notes' => 'All documentation validated.',
    ])->assertSessionHas('success');

    $profile->refresh();

    expect($profile->is_verified)->toBeTrue();
    expect($profile->verified_at?->toDateTimeString())->toBe(Date::now()->toDateTimeString());

    $log = OpportunityOwnerVerificationLog::where('opportunity_owner_profile_id', $profile->id)
        ->latest()
        ->first();

    expect($log)
        ->not->toBeNull()
        ->and($log->action)
        ->toBe('approved')
        ->and($log->actor_id)
        ->toBe($admin->id)
        ->and($log->notes)
        ->toBe('All documentation validated.');
});
