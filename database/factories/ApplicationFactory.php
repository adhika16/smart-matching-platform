<?php

namespace Database\Factories;

use App\Models\Application;
use App\Models\Job;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Application>
 */
class ApplicationFactory extends Factory
{
    protected $model = Application::class;

    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            'job_id' => Job::factory(),
            'user_id' => User::factory()->creative(),
            'status' => Application::STATUS_PENDING,
            'cover_letter' => fake()->paragraph(),
            'attachment_path' => null,
        ];
    }

    public function shortlisted(): static
    {
        return $this->state([
            'status' => Application::STATUS_SHORTLISTED,
        ]);
    }

    public function rejected(): static
    {
        return $this->state([
            'status' => Application::STATUS_REJECTED,
        ]);
    }
}
