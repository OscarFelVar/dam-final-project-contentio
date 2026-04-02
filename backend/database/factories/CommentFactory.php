<?php

namespace Database\Factories;

use App\Models\ContentItem;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class CommentFactory extends Factory
{
    public function definition(): array
    {
        return [
            'content_item_id' => ContentItem::factory(),
            'user_id'         => User::factory(),
            'body'            => fake()->paragraph(),
        ];
    }
}
