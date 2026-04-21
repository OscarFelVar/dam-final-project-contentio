<?php

namespace Tests\Feature;

use App\Models\Clinic;
use App\Models\ContentItem;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ClinicTest extends TestCase
{
    use RefreshDatabase;

    // ─── Index ────────────────────────────────────────────────────────────────

    public function test_agency_can_list_own_clinics(): void
    {
        $agency = User::factory()->agency()->create();
        Clinic::factory()->count(3)->create(['user_id' => $agency->id]);

        $response = $this->actingAs($agency)->getJson('/api/clinics');

        $response->assertStatus(200)->assertJsonCount(3);
    }

    public function test_unauthenticated_user_cannot_list_clinics(): void
    {
        $this->getJson('/api/clinics')->assertStatus(401);
    }

    // ─── Store ────────────────────────────────────────────────────────────────

    public function test_agency_can_create_clinic(): void
    {
        $agency = User::factory()->agency()->create();

        $response = $this->actingAs($agency)->postJson('/api/clinics', [
            'name'          => 'Clínica Test',
            'contact_email' => 'test@clinica.com',
        ]);

        $response->assertStatus(201)->assertJsonFragment(['name' => 'Clínica Test']);
        $this->assertDatabaseHas('clinics', ['name' => 'Clínica Test']);
    }

    public function test_create_clinic_validates_required_name(): void
    {
        $agency = User::factory()->agency()->create();

        $response = $this->actingAs($agency)->postJson('/api/clinics', []);

        $response->assertStatus(422)->assertJsonValidationErrors(['name']);
    }

    public function test_create_clinic_validates_contact_email_format(): void
    {
        $agency = User::factory()->agency()->create();

        $response = $this->actingAs($agency)->postJson('/api/clinics', [
            'name'          => 'Clínica Test',
            'contact_email' => 'no-es-un-email',
        ]);

        $response->assertStatus(422)->assertJsonValidationErrors(['contact_email']);
    }

    // ─── Update ───────────────────────────────────────────────────────────────

    public function test_agency_can_update_own_clinic(): void
    {
        $agency = User::factory()->agency()->create();
        $clinic = Clinic::factory()->create(['user_id' => $agency->id]);

        $response = $this->actingAs($agency)->putJson("/api/clinics/{$clinic->id}", [
            'name' => 'Nombre actualizado',
        ]);

        $response->assertStatus(200)->assertJsonFragment(['name' => 'Nombre actualizado']);
    }

    public function test_agency_cannot_update_another_agencys_clinic(): void
    {
        $agency      = User::factory()->agency()->create();
        $otherClinic = Clinic::factory()->create();

        $response = $this->actingAs($agency)->putJson("/api/clinics/{$otherClinic->id}", [
            'name' => 'Intento no autorizado',
        ]);

        $response->assertStatus(403);
    }

    // ─── Destroy ──────────────────────────────────────────────────────────────

    public function test_agency_can_delete_empty_clinic(): void
    {
        $agency = User::factory()->agency()->create();
        $clinic = Clinic::factory()->create(['user_id' => $agency->id]);

        $response = $this->actingAs($agency)->deleteJson("/api/clinics/{$clinic->id}");

        $response->assertStatus(204);
        $this->assertDatabaseMissing('clinics', ['id' => $clinic->id]);
    }

    public function test_agency_cannot_delete_clinic_with_content(): void
    {
        $agency = User::factory()->agency()->create();
        $clinic = Clinic::factory()->create(['user_id' => $agency->id]);
        ContentItem::factory()->create([
            'clinic_id'  => $clinic->id,
            'created_by' => $agency->id,
        ]);

        $response = $this->actingAs($agency)->deleteJson("/api/clinics/{$clinic->id}");

        $response->assertStatus(422);
        $this->assertDatabaseHas('clinics', ['id' => $clinic->id]);
    }

    public function test_agency_cannot_delete_another_agencys_clinic(): void
    {
        $agency      = User::factory()->agency()->create();
        $otherClinic = Clinic::factory()->create();

        $response = $this->actingAs($agency)->deleteJson("/api/clinics/{$otherClinic->id}");

        $response->assertStatus(403);
    }

    // ─── Clinic Users ─────────────────────────────────────────────────────────

    public function test_agency_can_list_clinic_users(): void
    {
        $agency     = User::factory()->agency()->create();
        $clinic     = Clinic::factory()->create(['user_id' => $agency->id]);
        $clinicUser = User::factory()->clinic()->create();
        $clinic->clinicUsers()->attach($clinicUser->id);

        $response = $this->actingAs($agency)->getJson("/api/clinics/{$clinic->id}/users");

        $response->assertStatus(200)->assertJsonCount(1);
    }

    public function test_agency_can_create_clinic_user(): void
    {
        $agency = User::factory()->agency()->create();
        $clinic = Clinic::factory()->create(['user_id' => $agency->id]);

        $response = $this->actingAs($agency)->postJson("/api/clinics/{$clinic->id}/users", [
            'name'     => 'Nuevo Usuario',
            'email'    => 'nuevo@clinica.com',
            'password' => 'password123',
        ]);

        $response->assertStatus(201)->assertJsonFragment(['email' => 'nuevo@clinica.com']);
        $this->assertDatabaseHas('users', ['email' => 'nuevo@clinica.com', 'role' => 'clinic']);
    }

    public function test_agency_can_remove_clinic_user(): void
    {
        $agency     = User::factory()->agency()->create();
        $clinic     = Clinic::factory()->create(['user_id' => $agency->id]);
        $clinicUser = User::factory()->clinic()->create();
        $clinic->clinicUsers()->attach($clinicUser->id);

        $response = $this->actingAs($agency)
            ->deleteJson("/api/clinics/{$clinic->id}/users/{$clinicUser->id}");

        $response->assertStatus(204);
        $this->assertDatabaseMissing('clinic_user', [
            'clinic_id' => $clinic->id,
            'user_id'   => $clinicUser->id,
        ]);
    }

    public function test_agency_cannot_access_another_agencys_clinic_users(): void
    {
        $agency      = User::factory()->agency()->create();
        $otherClinic = Clinic::factory()->create();

        $response = $this->actingAs($agency)->getJson("/api/clinics/{$otherClinic->id}/users");

        $response->assertStatus(403);
    }
}
