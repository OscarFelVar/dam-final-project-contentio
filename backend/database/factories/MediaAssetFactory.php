<?php

namespace Database\Factories;

use App\Models\ContentItem;
use Illuminate\Database\Eloquent\Factories\Factory;

class MediaAssetFactory extends Factory
{
    public function definition(): array
    {
        return [
            'content_item_id' => ContentItem::factory(),
            'file_path'       => 'media/1/' . fake()->uuid() . '.jpg',
            'file_name'       => fake()->word() . '.jpg',
            'mime_type'       => 'image/jpeg',
            'size'            => fake()->numberBetween(10000, 5000000),
        ];
    }
}
