<?php

namespace Database\Factories;

use App\Models\OpportunityOwnerProfile;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\OpportunityOwnerProfile>
 */
class OpportunityOwnerProfileFactory extends Factory
{
    protected $model = OpportunityOwnerProfile::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory()->opportunityOwner(),
            'company_name' => fake()->company(),
            'company_description' => fake()->paragraph(),
            'company_website' => fake()->url(),
            'company_size' => fake()->randomElement(['1-10', '11-50', '51-200', '201-500', '500+']),
            'industry' => fake()->word(),
            'is_verified' => true,
            'verified_at' => now(),
        ];
    }

    /**
     * Indicate that the company is not verified.
     */
    public function unverified(): static
    {
        return $this->state(fn () => [
            'is_verified' => false,
            'verified_at' => null,
        ]);
    }
}
