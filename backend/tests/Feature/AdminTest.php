<?php

namespace Tests\Feature;

use App\Models\Clinic;
use App\Models\ContentItem;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminTest extends TestCase
{
    use RefreshDatabase;

    // ─── Stats ────────────────────────────────────────────────────────────────

    public function test_admin_can_view_stats(): void
    {
        $admin  = User::factory()->admin()->create();
        $agency = User::factory()->agency()->create();

        User::factory()->agency()->count(1)->create();
        User::factory()->clinic()->count(3)->create();
        Clinic::factory()->count(4)->create(['user_id' => $agency->id]);

        $response = $this->actingAs($admin)->getJson('/api/admin/stats');

        $response->assertStatus(200)
            ->assertJsonStructure(['agencies', 'clinics', 'clinic_users', 'content_items'])
            ->assertJsonFragment(['agencies' => 2, 'clinic_users' => 3, 'clinics' => 4]);
    }

    public function test_non_admin_cannot_access_stats(): void
    {
        $agency = User::factory()->agency()->create();

        $this->actingAs($agency)->getJson('/api/admin/stats')->assertStatus(403);
    }

    public function test_unauthenticated_user_cannot_access_stats(): void
    {
        $this->getJson('/api/admin/stats')->assertStatus(401);
    }

    // ─── Users Index ──────────────────────────────────────────────────────────

    public function test_admin_can_list_all_users(): void
    {
        $admin = User::factory()->admin()->create();
        User::factory()->agency()->count(2)->create();
        User::factory()->clinic()->count(3)->create();

        $response = $this->actingAs($admin)->getJson('/api/admin/users');

        // admin + 2 agency + 3 clinic = 6 total
        $response->assertStatus(200)->assertJsonCount(6);
    }

    // ─── Store User ───────────────────────────────────────────────────────────

    public function test_admin_can_create_agency_user(): void
    {
        $admin = User::factory()->admin()->create();

        $response = $this->actingAs($admin)->postJson('/api/admin/users', [
            'name'     => 'Nueva Agencia',
            'email'    => 'agencia@test.com',
            'password' => 'password123',
        ]);

        $response->assertStatus(201)
            ->assertJsonFragment(['email' => 'agencia@test.com', 'role' => 'agency']);

        $this->assertDatabaseHas('users', ['email' => 'agencia@test.com', 'role' => 'agency']);
    }

    public function test_create_user_validates_required_fields(): void
    {
        $admin = User::factory()->admin()->create();

        $response = $this->actingAs($admin)->postJson('/api/admin/users', []);

        $response->assertStatus(422)->assertJsonValidationErrors(['name', 'email', 'password']);
    }

    public function test_create_user_validates_unique_email(): void
    {
        $admin    = User::factory()->admin()->create();
        $existing = User::factory()->create(['email' => 'existente@test.com']);

        $response = $this->actingAs($admin)->postJson('/api/admin/users', [
            'name'     => 'Otro',
            'email'    => 'existente@test.com',
            'password' => 'password123',
        ]);

        $response->assertStatus(422)->assertJsonValidationErrors(['email']);
    }

    // ─── Destroy User ─────────────────────────────────────────────────────────

    public function test_admin_can_delete_user(): void
    {
        $admin  = User::factory()->admin()->create();
        $target = User::factory()->agency()->create();

        $response = $this->actingAs($admin)->deleteJson("/api/admin/users/{$target->id}");

        $response->assertStatus(204);
        $this->assertDatabaseMissing('users', ['id' => $target->id]);
    }

    public function test_admin_cannot_delete_own_account(): void
    {
        $admin = User::factory()->admin()->create();

        $response = $this->actingAs($admin)->deleteJson("/api/admin/users/{$admin->id}");

        $response->assertStatus(422);
        $this->assertDatabaseHas('users', ['id' => $admin->id]);
    }
}
