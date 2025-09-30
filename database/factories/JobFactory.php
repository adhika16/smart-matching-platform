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
        $skills = collect(config('taxonomy.skills', []))->pluck('value')->all();
        $categories = collect(config('taxonomy.categories', []))->pluck('value')->all();

        $selectedSkills = empty($skills)
            ? [fake()->unique()->word()]
            : collect($skills)->shuffle()->take(fake()->numberBetween(2, 4))->values()->all();

        $category = empty($categories)
            ? null
            : collect($categories)->random();

        return [
            'user_id' => User::factory()->opportunityOwner(),
            'title' => $title,
            'slug' => Job::generateSlug($title),
            'location' => fake()->city(),
            'is_remote' => fake()->boolean(40),
            'status' => Job::STATUS_DRAFT,
            'compensation_type' => fake()->randomElement(['project', 'hourly', 'salary']),
            'tags' => $selectedSkills,
            'category' => $category,
            'skills' => $selectedSkills,
            'summary' => fake()->sentence(12),
            'description' => fake()->paragraphs(3, true),
            'published_at' => null,
            'timeline_start' => now()->addDays(fake()->numberBetween(7, 21))->toDateString(),
            'timeline_end' => now()->addDays(fake()->numberBetween(30, 90))->toDateString(),
            'budget_min' => fake()->numberBetween(5000, 15000),
            'budget_max' => fake()->numberBetween(15001, 45000),
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
