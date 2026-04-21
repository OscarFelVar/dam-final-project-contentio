<?php

namespace Tests\Feature;

use App\Models\Annotation;
use App\Models\Clinic;
use App\Models\ContentItem;
use App\Models\MediaAsset;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AnnotationTest extends TestCase
{
    use RefreshDatabase;

    private function setupAsset(): array
    {
        $user  = User::factory()->agency()->create();
        $asset = MediaAsset::factory()->create();
        return [$user, $asset];
    }

    public function test_user_can_list_annotations(): void
    {
        [$user, $asset] = $this->setupAsset();
        Annotation::factory()->count(2)->create([
            'media_asset_id' => $asset->id,
            'user_id'        => $user->id,
        ]);

        $response = $this->actingAs($user)
            ->getJson("/api/media-assets/{$asset->id}/annotations");

        $response->assertStatus(200)->assertJsonCount(2);
    }

    public function test_user_can_create_annotation(): void
    {
        [$user, $asset] = $this->setupAsset();

        $response = $this->actingAs($user)
            ->postJson("/api/media-assets/{$asset->id}/annotations", [
                'x_position' => 45.5,
                'y_position' => 30.0,
                'comment'    => 'Ajustar el logo aqui',
            ]);

        $response->assertStatus(201)
            ->assertJsonFragment(['comment' => 'Ajustar el logo aqui', 'pin_number' => 1]);

        $this->assertDatabaseHas('annotations', ['comment' => 'Ajustar el logo aqui']);
    }

    public function test_pin_number_increments_automatically(): void
    {
        [$user, $asset] = $this->setupAsset();

        $this->actingAs($user)->postJson("/api/media-assets/{$asset->id}/annotations", [
            'x_position' => 10.0, 'y_position' => 10.0, 'comment' => 'Pin 1',
        ]);
        $response = $this->actingAs($user)->postJson("/api/media-assets/{$asset->id}/annotations", [
            'x_position' => 20.0, 'y_position' => 20.0, 'comment' => 'Pin 2',
        ]);

        $response->assertJsonFragment(['pin_number' => 2]);
    }

    public function test_annotation_validates_position_range(): void
    {
        [$user, $asset] = $this->setupAsset();

        $response = $this->actingAs($user)
            ->postJson("/api/media-assets/{$asset->id}/annotations", [
                'x_position' => 150.0,  // fuera de rango
                'y_position' => 30.0,
                'comment'    => 'Test',
            ]);

        $response->assertStatus(422)->assertJsonValidationErrors(['x_position']);
    }

    public function test_user_can_delete_own_annotation(): void
    {
        [$user, $asset] = $this->setupAsset();
        $annotation = Annotation::factory()->create([
            'media_asset_id' => $asset->id,
            'user_id'        => $user->id,
        ]);

        $response = $this->actingAs($user)
            ->deleteJson("/api/media-assets/{$asset->id}/annotations/{$annotation->id}");

        $response->assertStatus(204);
        $this->assertDatabaseMissing('annotations', ['id' => $annotation->id]);
    }

    public function test_user_cannot_delete_another_users_annotation(): void
    {
        [$user, $asset] = $this->setupAsset();
        $otherUser  = User::factory()->create();
        $annotation = Annotation::factory()->create([
            'media_asset_id' => $asset->id,
            'user_id'        => $otherUser->id,
        ]);

        $response = $this->actingAs($user)
            ->deleteJson("/api/media-assets/{$asset->id}/annotations/{$annotation->id}");

        $response->assertStatus(403);
        $this->assertDatabaseHas('annotations', ['id' => $annotation->id]);
    }

    public function test_unauthenticated_user_cannot_access_annotations(): void
    {
        $asset = MediaAsset::factory()->create();

        $this->getJson("/api/media-assets/{$asset->id}/annotations")->assertStatus(401);
    }
}
