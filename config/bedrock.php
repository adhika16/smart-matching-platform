<?php

return [
    'enabled' => (bool) env('BEDROCK_ENABLED', false),

    'region' => env('AWS_BEDROCK_REGION', env('AWS_DEFAULT_REGION', 'us-east-1')),

    'timeout' => (int) env('BEDROCK_TIMEOUT', 15),

    'embeddings' => [
        'model_id' => env('AWS_BEDROCK_EMBEDDING_MODEL', 'amazon.titan-embed-text-v1'),
    ],

    'content' => [
        'model_id' => env('AWS_BEDROCK_CONTENT_MODEL', 'anthropic.claude-3-sonnet-20240229-v1:0'),
        'max_tokens' => (int) env('BEDROCK_CONTENT_MAX_TOKENS', 800),
        'temperature' => (float) env('BEDROCK_CONTENT_TEMPERATURE', 0.3),
    ],
];
