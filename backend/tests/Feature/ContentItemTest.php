<?php

namespace Tests\Feature;

use App\Models\Clinic;
use App\Models\ContentItem;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ContentItemTest extends TestCase
{
    use RefreshDatabase;

    private function agencyWithClinic(): array
    {
        $agency = User::factory()->agency()->create();
        $clinic = Clinic::factory()->create(['user_id' => $agency->id]);
        return [$agency, $clinic];
    }

    // ─── Index ────────────────────────────────────────────────────────────────

    public function test_agency_can_list_own_content_items(): void
    {
        [$agency, $clinic] = $this->agencyWithClinic();
        ContentItem::factory()->count(3)->create([
            'clinic_id'  => $clinic->id,
            'created_by' => $agency->id,
        ]);

        $response = $this->actingAs($agency)->getJson('/api/content-items');

        $response->assertStatus(200)->assertJsonCount(3, 'data');
    }

    public function test_agency_does_not_see_other_agencies_items(): void
    {
        [$agency] = $this->agencyWithClinic();

        // Otro agency con sus propios items
        $otherClinic = Clinic::factory()->create();
        ContentItem::factory()->count(2)->create(['clinic_id' => $otherClinic->id]);

        $response = $this->actingAs($agency)->getJson('/api/content-items');

        $response->assertStatus(200)->assertJsonCount(0, 'data');
    }

    public function test_unauthenticated_user_cannot_list_items(): void
    {
        $this->getJson('/api/content-items')->assertStatus(401);
    }

    // ─── Store ────────────────────────────────────────────────────────────────

    public function test_agency_can_create_content_item(): void
    {
        [$agency, $clinic] = $this->agencyWithClinic();

        $response = $this->actingAs($agency)->postJson('/api/content-items', [
            'clinic_id' => $clinic->id,
            'title'     => 'Mi primer reel',
            'type'      => 'reel',
        ]);

        $response->assertStatus(201)
            ->assertJsonFragment(['title' => 'Mi primer reel', 'status' => 'draft']);

        $this->assertDatabaseHas('content_items', ['title' => 'Mi primer reel']);
    }

    public function test_agency_cannot_create_item_for_another_agencys_clinic(): void
    {
        $agency  = User::factory()->agency()->create();
        $otherClinic = Clinic::factory()->create();

        $response = $this->actingAs($agency)->postJson('/api/content-items', [
            'clinic_id' => $otherClinic->id,
            'title'     => 'Intento no autorizado',
            'type'      => 'post',
        ]);

        $response->assertStatus(403);
    }

    public function test_create_item_validates_required_fields(): void
    {
        $agency = User::factory()->agency()->create();

        $response = $this->actingAs($agency)->postJson('/api/content-items', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['clinic_id', 'title', 'type']);
    }

    public function test_create_item_validates_type_enum(): void
    {
        [$agency, $clinic] = $this->agencyWithClinic();

        $response = $this->actingAs($agency)->postJson('/api/content-items', [
            'clinic_id' => $clinic->id,
            'title'     => 'Test',
            'type'      => 'video_largo_no_valido',
        ]);

        $response->assertStatus(422)->assertJsonValidationErrors(['type']);
    }

    // ─── Show ─────────────────────────────────────────────────────────────────

    public function test_agency_can_view_own_content_item(): void
    {
        [$agency, $clinic] = $this->agencyWithClinic();
        $item = ContentItem::factory()->create([
            'clinic_id'  => $clinic->id,
            'created_by' => $agency->id,
        ]);

        $response = $this->actingAs($agency)->getJson("/api/content-items/{$item->id}");

        $response->assertStatus(200)->assertJsonFragment(['id' => $item->id]);
    }

    public function test_agency_cannot_view_another_agencys_item(): void
    {
        $agency      = User::factory()->agency()->create();
        $otherItem   = ContentItem::factory()->create();

        $response = $this->actingAs($agency)->getJson("/api/content-items/{$otherItem->id}");

        $response->assertStatus(403);
    }

    // ─── Update ───────────────────────────────────────────────────────────────

    public function test_agency_can_update_own_content_item(): void
    {
        [$agency, $clinic] = $this->agencyWithClinic();
        $item = ContentItem::factory()->create([
            'clinic_id'  => $clinic->id,
            'created_by' => $agency->id,
        ]);

        $response = $this->actingAs($agency)->putJson("/api/content-items/{$item->id}", [
            'title' => 'Titulo actualizado',
        ]);

        $response->assertStatus(200)->assertJsonFragment(['title' => 'Titulo actualizado']);
    }

    // ─── Destroy ──────────────────────────────────────────────────────────────

    public function test_agency_can_delete_draft_item(): void
    {
        [$agency, $clinic] = $this->agencyWithClinic();
        $item = ContentItem::factory()->draft()->create([
            'clinic_id'  => $clinic->id,
            'created_by' => $agency->id,
        ]);

        $response = $this->actingAs($agency)->deleteJson("/api/content-items/{$item->id}");

        $response->assertStatus(204);
        $this->assertDatabaseMissing('content_items', ['id' => $item->id]);
    }

    public function test_agency_cannot_delete_non_draft_item(): void
    {
        [$agency, $clinic] = $this->agencyWithClinic();
        $item = ContentItem::factory()->readyForReview()->create([
            'clinic_id'  => $clinic->id,
            'created_by' => $agency->id,
        ]);

        $response = $this->actingAs($agency)->deleteJson("/api/content-items/{$item->id}");

        $response->assertStatus(422);
        $this->assertDatabaseHas('content_items', ['id' => $item->id]);
    }
}
