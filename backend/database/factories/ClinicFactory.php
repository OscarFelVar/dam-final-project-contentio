<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class ClinicFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name'          => fake()->company(),
            'description'   => fake()->sentence(),
            'contact_email' => fake()->unique()->safeEmail(),
            'user_id'       => User::factory()->agency(),
        ];
    }
}
