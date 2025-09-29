<?php

return [

    'driver' => env('SCOUT_DRIVER', 'meilisearch'),

    'prefix' => env('SCOUT_PREFIX', ''),

    'queue' => env('SCOUT_QUEUE', env('APP_ENV') === 'production'),

    'after_commit' => env('SCOUT_AFTER_COMMIT', true),

    'chunk' => [
        'searchable' => 500,
        'unsearchable' => 500,
    ],

    'soft_delete' => false,

    'identify' => env('SCOUT_IDENTIFY', false),

    'meilisearch' => [
        'host' => env('MEILISEARCH_HOST', 'http://127.0.0.1:7700'),
        'key' => env('MEILISEARCH_KEY', null),
        'index-settings' => [
            'job_postings' => [
                'filterableAttributes' => [
                    'status',
                    'location',
                    'is_remote',
                    'category',
                    'skills',
                    'published_at',
                ],
                'sortableAttributes' => [
                    'published_at',
                    'budget_min',
                    'budget_max',
                ],
            ],
            'creative_profiles' => [
                'filterableAttributes' => [
                    'skills',
                    'location',
                    'experience_level',
                    'hourly_rate',
                ],
                'sortableAttributes' => [
                    'hourly_rate',
                ],
            ],
        ],
    ],

];
