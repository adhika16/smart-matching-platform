<?php

namespace App\Services\Matching;

use App\Models\Application;
use App\Models\CreativeProfile;
use App\Models\Job;
use App\Services\Embedding\EmbeddingVectorizer;
use App\Services\Pinecone\PineconeService;
use Illuminate\Support\Collection;

class ApplicationRankingService
{
    public function __construct(
        private readonly EmbeddingVectorizer $vectorizer,
        private readonly PineconeService $pinecone,
    ) {
    }

    /**
     * Rank applications for a job using AI matching
     */
    public function rankApplicationsForJob(Job $job, Collection $applications): Collection
    {
        if ($applications->isEmpty()) {
            return $applications;
        }

        // Generate embedding for the job
        $jobVector = $this->generateJobEmbedding($job);

        if (empty($jobVector)) {
            return $applications;
        }

        // Score each application
        $scoredApplications = $applications->map(function (Application $application) use ($jobVector) {
            // Check if the application has a valid user and creative profile
            if (!$application->user) {
                return [
                    'application' => $application,
                    'score' => 0.0,
                    'breakdown' => [
                        'profile_match' => 0.0,
                        'skills_match' => 0.0,
                        'experience_match' => 0.0,
                    ],
                ];
            }

            $creative = $application->user->creativeProfile;

            if (!$creative) {
                return [
                    'application' => $application,
                    'score' => 0.0,
                    'breakdown' => [
                        'profile_match' => 0.0,
                        'skills_match' => 0.0,
                        'experience_match' => 0.0,
                    ],
                ];
            }

            $score = $this->calculateApplicationScore($jobVector, $creative, $application);

            return [
                'application' => $application,
                'score' => $score['total'],
                'breakdown' => $score['breakdown'],
            ];
        });

        // Sort by total score descending
        return $scoredApplications->sortByDesc('score');
    }

    private function generateJobEmbedding(Job $job): array
    {
        $jobText = $job->title;

        if ($job->summary) {
            $jobText .= ' ' . $job->summary;
        }

        if ($job->description) {
            $jobText .= ' ' . $job->description;
        }

        if ($job->skills) {
            $jobText .= ' ' . implode(' ', $job->skills);
        }

        return $this->vectorizer->embed($jobText);
    }

    private function calculateApplicationScore(array $jobVector, CreativeProfile $creative, Application $application): array
    {
        $profileScore = $this->calculateProfileMatch($jobVector, $creative);
        $skillsScore = $this->calculateSkillsMatch($creative, $application);
        $experienceScore = $this->calculateExperienceMatch($creative);

        // Weighted combination
        $totalScore = ($profileScore * 0.5) + ($skillsScore * 0.3) + ($experienceScore * 0.2);

        return [
            'total' => $totalScore,
            'breakdown' => [
                'profile_match' => $profileScore,
                'skills_match' => $skillsScore,
                'experience_match' => $experienceScore,
            ],
        ];
    }

    private function calculateProfileMatch(array $jobVector, CreativeProfile $creative): float
    {
        // Generate creative profile embedding
        $creativeText = $creative->bio ?? '';

        if ($creative->skills) {
            $creativeText .= ' ' . implode(' ', $creative->skills);
        }

        if (empty(trim($creativeText))) {
            return 0.0;
        }

        $creativeVector = $this->vectorizer->embed($creativeText);

        if (empty($creativeVector) || empty($jobVector)) {
            return 0.0;
        }

        return $this->cosineSimilarity($jobVector, $creativeVector);
    }

    private function calculateSkillsMatch(CreativeProfile $creative, Application $application): float
    {
        $job = $application->job;

        if (!$job->skills || !$creative->skills) {
            return 0.0;
        }

        $jobSkills = collect($job->skills);
        $creativeSkills = collect($creative->skills);

        // Calculate overlap
        $matchingSkills = $jobSkills->intersect($creativeSkills);

        if ($jobSkills->isEmpty()) {
            return 0.0;
        }

        return $matchingSkills->count() / $jobSkills->count();
    }

    private function calculateExperienceMatch(CreativeProfile $creative): float
    {
        // Base score based on experience level
        $experienceScores = [
            'entry' => 0.6,
            'mid' => 0.8,
            'senior' => 1.0,
            'lead' => 1.0,
        ];

        return $experienceScores[$creative->experience_level] ?? 0.5;
    }

    private function cosineSimilarity(array $vectorA, array $vectorB): float
    {
        if (count($vectorA) !== count($vectorB)) {
            return 0.0;
        }

        $dotProduct = 0.0;
        $magnitudeA = 0.0;
        $magnitudeB = 0.0;

        for ($i = 0; $i < count($vectorA); $i++) {
            $dotProduct += $vectorA[$i] * $vectorB[$i];
            $magnitudeA += $vectorA[$i] ** 2;
            $magnitudeB += $vectorB[$i] ** 2;
        }

        $magnitude = sqrt($magnitudeA) * sqrt($magnitudeB);

        return $magnitude > 0 ? $dotProduct / $magnitude : 0.0;
    }
}
