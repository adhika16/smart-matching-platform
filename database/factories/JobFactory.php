<?php

namespace Database\Factories;

use App\Models\Job;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Job>
 */
class JobFactory extends Factory
{
    protected $model = Job::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $title = fake()->jobTitle();

        return [
            'user_id' => User::factory()->opportunityOwner(),
            'title' => $title,
            'slug' => Job::generateSlug($title),
            'location' => fake()->city(),
            'is_remote' => fake()->boolean(40),
            'status' => Job::STATUS_DRAFT,
            'compensation_type' => fake()->randomElement(['hourly', 'project', 'salary']) ?? null,
            'compensation_min' => fake()->numberBetween(30, 80),
            'compensation_max' => fake()->numberBetween(81, 150),
            'tags' => fake()->words(3),
            'summary' => fake()->sentence(12),
            'description' => fake()->paragraphs(3, true),
            'published_at' => null,
        ];
    }

    public function published(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => Job::STATUS_PUBLISHED,
            'published_at' => now(),
        ]);
    }

    public function archived(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => Job::STATUS_ARCHIVED,
            'published_at' => null,
        ]);
    }
}
