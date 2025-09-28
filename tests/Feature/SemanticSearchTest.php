<?php

use App\Console\Commands\SemanticStatusCommand;
use App\Jobs\SyncJobEmbeddings;
use App\Models\CreativeProfile;
use App\Models\Job;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\getJson;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    config()->set('bedrock.enabled', false);
    config()->set('pinecone.enabled', false);
    config()->set('pinecone.simulate', true);
    config()->set('queue.default', 'sync');
    config()->set('scout.driver', 'collection');
});

test('semantic search endpoint combines keyword and semantic scores', function (): void {
    $user = User::factory()->creative()->create();

    CreativeProfile::factory()->for($user)->create([
        'bio' => 'Designer specializing in product and UX.',
        'skills' => ['ui', 'ux', 'figma'],
        'portfolio_links' => ['https://example.com/portfolio'],
        'experience_level' => 'expert',
    ]);

    $jobA = Job::factory()->published()->create([
        'title' => 'Senior Product Designer',
        'summary' => 'Lead product design initiatives.',
        'description' => '<p>Looking for experienced designer proficient in Figma and UX research.</p>',
        'skills' => ['ux', 'figma'],
        'category' => 'design',
    ]);

    $jobB = Job::factory()->published()->create([
        'title' => 'Backend Engineer',
        'summary' => 'Build scalable APIs.',
        'description' => '<p>Ruby on Rails and PostgreSQL expertise required.</p>',
        'skills' => ['ruby', 'rails'],
        'category' => 'engineering',
    ]);

    SyncJobEmbeddings::dispatchSync($jobA->id, true);
    SyncJobEmbeddings::dispatchSync($jobB->id, true);

    actingAs($user)
        ->getJson('/api/search/personalized?q=product%20designer')
        ->assertOk()
        ->assertJsonStructure([
            'data' => [
                '*' => [
                    'id',
                    'title',
                    'scores' => [
                        'final',
                        'semantic',
                        'keyword',
                    ],
                ],
            ],
            'meta' => [
                'source',
                'semantic_limit',
                'keyword_count',
                'semantic_count',
            ],
        ])
        ->assertJson(fn ($json) => $json
            ->where('data.0.id', $jobA->id)
            ->where('meta.source', 'cache+scout'));
});

test('semantic status command reports health successfully', function (): void {
    $exitCode = Artisan::call(SemanticStatusCommand::class);

    expect($exitCode)->toBe(0);
    expect(Artisan::output())
        ->toContain('Semantic Search Status')
        ->toContain('Bedrock enabled');
});
