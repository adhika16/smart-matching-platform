<?php

return [
    'enabled' => env('BEDROCK_ENABLED', env('AWS_BEDROCK_ENABLED', false)),
    'region' => env('AWS_BEDROCK_REGION', env('AWS_DEFAULT_REGION', 'us-east-1')),
    'retries' => env('AWS_BEDROCK_RETRIES', 3),
    'timeout' => env('AWS_BEDROCK_TIMEOUT', 15),

    'embeddings' => [
        'model_id' => env('AWS_BEDROCK_EMBEDDING_MODEL_ID', 'amazon.titan-embed-text-v2:0'),
    ],

    'content' => [
        'model_id' => env('AWS_BEDROCK_CONTENT_MODEL_ID', 'deepseek-llm-r1-distill-qwen-7b'),
        'max_tokens' => (int) env('AWS_BEDROCK_CONTENT_MAX_TOKENS', 800),
        'temperature' => (float) env('AWS_BEDROCK_CONTENT_TEMPERATURE', 0.3),
    ],
];

