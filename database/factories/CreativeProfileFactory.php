<?php

namespace Database\Factories;

use App\Models\CreativeProfile;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CreativeProfile>
 */
class CreativeProfileFactory extends Factory
{
    protected $model = CreativeProfile::class;

    public function definition(): array
    {
        $skills = collect(config('taxonomy.skills', []))->pluck('value')->all();
        $selectedSkills = empty($skills)
            ? [fake()->unique()->word()]
            : collect($skills)->shuffle()->take(fake()->numberBetween(2, 5))->values()->all();

        return [
            'user_id' => User::factory()->creative(),
            'bio' => fake()->paragraph(),
            'skills' => $selectedSkills,
            'portfolio_links' => [fake()->url()],
            'location' => fake()->city(),
            'hourly_rate' => fake()->numberBetween(30, 150),
            'experience_level' => fake()->randomElement(['beginner', 'intermediate', 'expert']),
            'available_for_work' => fake()->boolean(),
        ];
    }
}
