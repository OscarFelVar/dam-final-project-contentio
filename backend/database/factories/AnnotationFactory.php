<?php

namespace Database\Factories;

use App\Models\MediaAsset;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class AnnotationFactory extends Factory
{
    public function definition(): array
    {
        return [
            'media_asset_id' => MediaAsset::factory(),
            'user_id'        => User::factory(),
            'pin_number'     => fake()->numberBetween(1, 20),
            'x_position'     => fake()->randomFloat(2, 0, 100),
            'y_position'     => fake()->randomFloat(2, 0, 100),
            'comment'        => fake()->sentence(),
        ];
    }
}
