<?php

namespace Database\Factories;

use App\Models\Clinic;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class ContentItemFactory extends Factory
{
    public function definition(): array
    {
        return [
            'clinic_id'   => Clinic::factory(),
            'created_by'  => User::factory()->agency(),
            'title'       => fake()->sentence(4),
            'type'        => fake()->randomElement(['reel', 'post', 'story']),
            'status'      => 'draft',
            'deadline_at' => null,
        ];
    }

    public function draft(): static
    {
        return $this->state(fn () => ['status' => 'draft']);
    }

    public function readyForReview(): static
    {
        return $this->state(fn () => ['status' => 'ready_for_review']);
    }

    public function approved(): static
    {
        return $this->state(fn () => ['status' => 'approved']);
    }
}
