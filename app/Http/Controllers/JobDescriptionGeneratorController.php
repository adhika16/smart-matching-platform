<?php

namespace App\Http\Controllers;

use App\Models\Job;
use App\Services\Bedrock\BedrockService;
use App\Services\Bedrock\Exceptions\BedrockException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class JobDescriptionGeneratorController extends Controller
{
    public function __construct(private readonly BedrockService $bedrock)
    {
    }

    public function __invoke(Request $request): JsonResponse
    {
        $this->authorize('create', Job::class);

        if (! $this->bedrock->isEnabled()) {
            return response()->json([
                'message' => 'Bedrock integration is currently disabled.',
            ], 503);
        }

        $data = $request->validate([
            'title' => ['nullable', 'string', 'max:255'],
            'summary' => ['nullable', 'string', 'max:500'],
            'skills' => ['nullable', 'array'],
            'skills.*' => ['string', 'max:50'],
            'category' => ['nullable', 'string', 'max:100'],
            'timeline_start' => ['nullable', 'date'],
            'timeline_end' => ['nullable', 'date'],
            'budget_min' => ['nullable', 'numeric', 'min:0'],
            'budget_max' => ['nullable', 'numeric', 'min:0'],
        ]);

        $requirements = array_filter([
            'title' => $data['title'] ?? null,
            'summary' => $data['summary'] ?? null,
            'category' => $data['category'] ?? null,
            'skills' => $data['skills'] ?? null,
            'timeline_start' => $data['timeline_start'] ?? null,
            'timeline_end' => $data['timeline_end'] ?? null,
            'budget_min' => $data['budget_min'] ?? null,
            'budget_max' => $data['budget_max'] ?? null,
        ], fn ($value) => filled($value));

        try {
            $generated = $this->bedrock->generateProjectDescription($requirements);
        } catch (BedrockException $exception) {
            return response()->json([
                'message' => 'Unable to generate description at this time.',
                'error' => $exception->getMessage(),
            ], 422);
        }

        return response()->json([
            'description' => $generated,
        ]);
    }
}
