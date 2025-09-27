<?php

use App\Models\OpportunityOwnerVerificationLog;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use function Pest\Laravel\actingAs;
use function Pest\Laravel\patch;

it('allows opportunity owners to upload verification evidence and records an audit log', function () {
    Storage::fake('public');

    /** @var User $user */
    $user = User::factory()->opportunityOwner()->create();

    $user->opportunityOwnerProfile()->create([
        'company_name' => 'Acme Co.',
    ]);

    actingAs($user);

    $file = UploadedFile::fake()->create('business-license.pdf', 150, 'application/pdf');

    patch(route('profile.update.opportunity-owner'), [
        'company_name' => 'Acme Co.',
        'company_description' => 'We help brands ship delightful product experiences.',
        'company_website' => 'https://acme.test',
        'company_size' => '11-50',
        'industry' => 'Technology',
        'verification_documents' => [$file],
        'verification_note' => 'Business license attached for review.',
    ])->assertSessionHas('success');

    $profile = $user->opportunityOwnerProfile()->firstOrFail();
    $profile->refresh();

    expect($profile->verification_documents)
        ->toBeArray()
        ->and($profile->verification_documents)
        ->toHaveCount(1);

    $document = $profile->verification_documents[0];

    expect(Storage::disk('public')->exists($document['path']))->toBeTrue();

    $log = OpportunityOwnerVerificationLog::where('opportunity_owner_profile_id', $profile->id)
        ->where('action', 'documents_uploaded')
        ->latest()
        ->first();

    expect($log)
        ->not->toBeNull()
        ->and($log->notes)
        ->toBe('Business license attached for review.')
        ->and($log->actor_id)
        ->toBe($user->id);
});
