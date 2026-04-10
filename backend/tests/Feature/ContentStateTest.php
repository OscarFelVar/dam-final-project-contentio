<?php

namespace Tests\Feature;

use App\Models\Clinic;
use App\Models\ContentItem;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class ContentStateTest extends TestCase
{
    use RefreshDatabase;

    private function setupAgencyItem(string $status = 'draft'): array
    {
        $agency = User::factory()->agency()->create();
        $clinic = Clinic::factory()->create([
            'user_id'       => $agency->id,
            'contact_email' => 'clinica@test.com',
        ]);
        $item = ContentItem::factory()->create([
            'clinic_id'  => $clinic->id,
            'created_by' => $agency->id,
            'status'     => $status,
        ]);
        return [$agency, $clinic, $item];
    }

    // ─── Transiciones válidas ─────────────────────────────────────────────────

    public function test_agency_can_send_draft_to_review(): void
    {
        [$agency, , $item] = $this->setupAgencyItem('draft');

        $response = $this->actingAs($agency)
            ->postJson("/api/content-items/{$item->id}/transition", [
                'status' => 'ready_for_review',
            ]);

        $response->assertStatus(200)
            ->assertJsonFragment(['status' => 'ready_for_review']);

        $this->assertDatabaseHas('state_histories', [
            'content_item_id' => $item->id,
            'from_status'     => 'draft',
            'to_status'       => 'ready_for_review',
        ]);
    }

    public function test_agency_can_resubmit_after_correction(): void
    {
        [$agency, , $item] = $this->setupAgencyItem('in_correction');

        $response = $this->actingAs($agency)
            ->postJson("/api/content-items/{$item->id}/transition", [
                'status' => 'ready_for_review',
            ]);

        $response->assertStatus(200)
            ->assertJsonFragment(['status' => 'ready_for_review']);
    }

    public function test_clinic_user_can_approve_item(): void
    {
        $clinicUser = User::factory()->clinic()->create();
        $clinic     = Clinic::factory()->create(['user_id' => $clinicUser->id]);
        $item       = ContentItem::factory()->readyForReview()->create([
            'clinic_id'  => $clinic->id,
            'created_by' => User::factory()->agency()->create()->id,
        ]);

        $response = $this->actingAs($clinicUser)
            ->postJson("/api/content-items/{$item->id}/transition", [
                'status' => 'approved',
            ]);

        $response->assertStatus(200)
            ->assertJsonFragment(['status' => 'approved']);
    }

    public function test_clinic_user_can_reject_item(): void
    {
        $clinicUser = User::factory()->clinic()->create();
        $clinic     = Clinic::factory()->create(['user_id' => $clinicUser->id]);
        $item       = ContentItem::factory()->readyForReview()->create([
            'clinic_id'  => $clinic->id,
            'created_by' => User::factory()->agency()->create()->id,
        ]);

        $response = $this->actingAs($clinicUser)
            ->postJson("/api/content-items/{$item->id}/transition", [
                'status' => 'rejected',
            ]);

        $response->assertStatus(200)
            ->assertJsonFragment(['status' => 'rejected']);
    }

    public function test_clinic_user_can_request_correction(): void
    {
        $clinicUser = User::factory()->clinic()->create();
        $clinic     = Clinic::factory()->create(['user_id' => $clinicUser->id]);
        $item       = ContentItem::factory()->readyForReview()->create([
            'clinic_id'  => $clinic->id,
            'created_by' => User::factory()->agency()->create()->id,
        ]);

        $response = $this->actingAs($clinicUser)
            ->postJson("/api/content-items/{$item->id}/transition", [
                'status' => 'in_correction',
            ]);

        $response->assertStatus(200)
            ->assertJsonFragment(['status' => 'in_correction']);
    }

    // ─── Transiciones inválidas ───────────────────────────────────────────────

    public function test_invalid_transition_returns_422(): void
    {
        [$agency, , $item] = $this->setupAgencyItem('draft');

        $response = $this->actingAs($agency)
            ->postJson("/api/content-items/{$item->id}/transition", [
                'status' => 'approved',  // draft → approved no permitido para agency
            ]);

        $response->assertStatus(422);
    }

    public function test_agency_cannot_approve_own_item(): void
    {
        [$agency, , $item] = $this->setupAgencyItem('ready_for_review');

        $response = $this->actingAs($agency)
            ->postJson("/api/content-items/{$item->id}/transition", [
                'status' => 'approved',
            ]);

        $response->assertStatus(422);
    }

    // ─── Historial de estados ─────────────────────────────────────────────────

    public function test_transition_is_recorded_in_history(): void
    {
        [$agency, , $item] = $this->setupAgencyItem('draft');

        $this->actingAs($agency)
            ->postJson("/api/content-items/{$item->id}/transition", [
                'status' => 'ready_for_review',
            ]);

        $this->assertDatabaseCount('state_histories', 1);
        $this->assertDatabaseHas('state_histories', [
            'content_item_id' => $item->id,
            'user_id'         => $agency->id,
            'from_status'     => 'draft',
            'to_status'       => 'ready_for_review',
        ]);
    }

    // ─── Notificaciones ───────────────────────────────────────────────────────

    public function test_email_is_sent_when_agency_sends_to_review(): void
    {
        Notification::fake();
        [$agency, , $item] = $this->setupAgencyItem('draft');

        $this->actingAs($agency)
            ->postJson("/api/content-items/{$item->id}/transition", [
                'status' => 'ready_for_review',
            ]);

        Notification::assertSentOnDemand(
            \App\Notifications\ContentStatusChanged::class
        );
    }

    public function test_email_is_sent_to_agency_when_clinic_approves(): void
    {
        Notification::fake();

        $agencyUser = User::factory()->agency()->create();
        $clinicUser = User::factory()->clinic()->create();
        $clinic     = Clinic::factory()->create(['user_id' => $clinicUser->id]);
        $item       = ContentItem::factory()->readyForReview()->create([
            'clinic_id'  => $clinic->id,
            'created_by' => $agencyUser->id,
        ]);

        $this->actingAs($clinicUser)
            ->postJson("/api/content-items/{$item->id}/transition", [
                'status' => 'approved',
            ]);

        Notification::assertSentTo(
            $agencyUser,
            \App\Notifications\ContentStatusChanged::class
        );
    }
}
