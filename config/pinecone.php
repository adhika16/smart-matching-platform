<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Pinecone Configuration
    |--------------------------------------------------------------------------
    |
    | This configuration manages the connection to the Pinecone vector database.
    | The settings here are used by the Pinecone PHP client to interact with
    | your indexes for semantic search capabilities.
    |
    | - `enabled`: Master switch to enable or disable Pinecone integration.
    | - `simulate`: If true, Pinecone operations are logged but not executed.
    | - `api_key`: Your Pinecone API key.
    | - `environment`: The environment where your Pinecone index is hosted.
    | - `index`: The name of the default index to use for vector operations.
    | - `namespace`: The default namespace within the index.
    |
    */

    'enabled' => env('PINECONE_ENABLED', true),
    'simulate' => env('PINECONE_SIMULATE', false),

    'api_key' => env('PINECONE_API_KEY'),
    'environment' => env('PINECONE_ENVIRONMENT'),
    'index_host' => env('PINECONE_INDEX_HOST'),

    'index' => env('PINECONE_INDEX', 'creative-matching'),
    'namespace' => env('PINECONE_NAMESPACE', 'default'),

    'dimension' => (int) env('PINECONE_DIMENSION', 1024),
    'metric' => env('PINECONE_METRIC', 'cosine'),
    'pod_type' => env('PINECONE_POD_TYPE', 'p1.x1'),
];
