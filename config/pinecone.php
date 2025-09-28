<?php

return [
    'enabled' => env('PINECONE_ENABLED', false),
    'api_key' => env('PINECONE_API_KEY'),
    'project_id' => env('PINECONE_PROJECT_ID'),
    'environment' => env('PINECONE_ENVIRONMENT'),

    'index' => [
        'name' => env('PINECONE_INDEX', 'creative-matching'),
        'dimension' => (int) env('PINECONE_DIMENSION', 1536),
        'metric' => env('PINECONE_METRIC', 'cosine'),
        'namespace' => env('PINECONE_NAMESPACE', 'default'),
        'host' => env('PINECONE_INDEX_HOST'),
        'pod_type' => env('PINECONE_POD_TYPE'),
    ],

    'timeout' => (int) env('PINECONE_HTTP_TIMEOUT', 10),
    'simulate' => env('PINECONE_SIMULATE', false),
];
