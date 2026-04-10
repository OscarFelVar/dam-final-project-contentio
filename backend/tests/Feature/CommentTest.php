<?php

namespace Tests\Feature;

use App\Models\Clinic;
use App\Models\Comment;
use App\Models\ContentItem;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CommentTest extends TestCase
{
    use RefreshDatabase;

    private function setupItem(): array
    {
        $agency = User::factory()->agency()->create();
        $clinic = Clinic::factory()->create(['user_id' => $agency->id]);
        $item   = ContentItem::factory()->create([
            'clinic_id'  => $clinic->id,
            'created_by' => $agency->id,
        ]);
        return [$agency, $item];
    }

    public function test_user_can_list_comments(): void
    {
        [$agency, $item] = $this->setupItem();
        Comment::factory()->count(3)->create([
            'content_item_id' => $item->id,
            'user_id'         => $agency->id,
        ]);

        $response = $this->actingAs($agency)
            ->getJson("/api/content-items/{$item->id}/comments");

        $response->assertStatus(200)->assertJsonCount(3);
    }

    public function test_user_can_create_comment(): void
    {
        [$agency, $item] = $this->setupItem();

        $response = $this->actingAs($agency)
            ->postJson("/api/content-items/{$item->id}/comments", [
                'body' => 'Este es mi comentario de prueba.',
            ]);

        $response->assertStatus(201)
            ->assertJsonFragment(['body' => 'Este es mi comentario de prueba.']);

        $this->assertDatabaseHas('comments', ['body' => 'Este es mi comentario de prueba.']);
    }

    public function test_comment_body_is_required(): void
    {
        [$agency, $item] = $this->setupItem();

        $response = $this->actingAs($agency)
            ->postJson("/api/content-items/{$item->id}/comments", []);

        $response->assertStatus(422)->assertJsonValidationErrors(['body']);
    }

    public function test_user_can_delete_own_comment(): void
    {
        [$agency, $item] = $this->setupItem();
        $comment = Comment::factory()->create([
            'content_item_id' => $item->id,
            'user_id'         => $agency->id,
        ]);

        $response = $this->actingAs($agency)
            ->deleteJson("/api/content-items/{$item->id}/comments/{$comment->id}");

        $response->assertStatus(204);
        $this->assertDatabaseMissing('comments', ['id' => $comment->id]);
    }

    public function test_user_cannot_delete_another_users_comment(): void
    {
        [$agency, $item] = $this->setupItem();
        $otherUser = User::factory()->create();
        $comment   = Comment::factory()->create([
            'content_item_id' => $item->id,
            'user_id'         => $otherUser->id,
        ]);

        $response = $this->actingAs($agency)
            ->deleteJson("/api/content-items/{$item->id}/comments/{$comment->id}");

        $response->assertStatus(403);
        $this->assertDatabaseHas('comments', ['id' => $comment->id]);
    }

    public function test_unauthenticated_user_cannot_access_comments(): void
    {
        $item = ContentItem::factory()->create();

        $this->getJson("/api/content-items/{$item->id}/comments")->assertStatus(401);
    }
}
