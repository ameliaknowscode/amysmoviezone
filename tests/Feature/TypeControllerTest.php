<?php

namespace Tests\Feature;

use App\Models\Type;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TypeControllerTest extends TestCase
{
    use RefreshDatabase;

    // -------------------------------------------------------------------------
    // Authentication guard
    // -------------------------------------------------------------------------

    public function test_unauthenticated_user_is_redirected_from_index(): void
    {
        $this->get(route('admin.types.index'))
            ->assertRedirect(route('login'));
    }

    public function test_unauthenticated_user_is_redirected_from_create(): void
    {
        $this->get(route('admin.types.create'))
            ->assertRedirect(route('login'));
    }

    public function test_unauthenticated_user_is_redirected_from_edit(): void
    {
        $type = Type::factory()->create();

        $this->get(route('admin.types.edit', $type))
            ->assertRedirect(route('login'));
    }

    // -------------------------------------------------------------------------
    // Index
    // -------------------------------------------------------------------------

    public function test_index_returns_200_for_authenticated_user(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('admin.types.index'))
            ->assertOk();
    }

    public function test_index_lists_types(): void
    {
        $user  = User::factory()->create();
        $types = Type::factory()->count(3)->create();

        $response = $this->actingAs($user)
            ->get(route('admin.types.index'));

        foreach ($types as $type) {
            $response->assertSee($type->name);
        }
    }

    // -------------------------------------------------------------------------
    // Create
    // -------------------------------------------------------------------------

    public function test_create_returns_200_for_authenticated_user(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('admin.types.create'))
            ->assertOk();
    }

    // -------------------------------------------------------------------------
    // Store
    // -------------------------------------------------------------------------

    public function test_store_creates_non_crew_type_and_redirects(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post(route('admin.types.store'), [
                'name'    => 'Actor',
                'is_crew' => '0',
            ])
            ->assertRedirect(route('admin.types.index'))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('types', ['name' => 'Actor', 'is_crew' => false]);
    }

    public function test_store_creates_crew_type_and_redirects(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post(route('admin.types.store'), [
                'name'    => 'Director',
                'is_crew' => '1',
            ])
            ->assertRedirect(route('admin.types.index'))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('types', ['name' => 'Director', 'is_crew' => true]);
    }

    public function test_store_defaults_is_crew_to_true_when_omitted(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post(route('admin.types.store'), [
                'name' => 'Extra',
            ])
            ->assertRedirect(route('admin.types.index'));

        $this->assertDatabaseHas('types', ['name' => 'Extra', 'is_crew' => true]);
    }

    public function test_store_validates_required_name(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post(route('admin.types.store'), [])
            ->assertSessionHasErrors('name');
    }

    public function test_store_rejects_duplicate_name(): void
    {
        $user = User::factory()->create();
        Type::factory()->create(['name' => 'Director']);

        $this->actingAs($user)
            ->post(route('admin.types.store'), ['name' => 'Director'])
            ->assertSessionHasErrors('name');
    }

    public function test_store_requires_authentication(): void
    {
        $this->post(route('admin.types.store'), ['name' => 'Someone'])
            ->assertRedirect(route('login'));

        $this->assertDatabaseMissing('types', ['name' => 'Someone']);
    }

    // -------------------------------------------------------------------------
    // Edit
    // -------------------------------------------------------------------------

    public function test_edit_returns_200_for_authenticated_user(): void
    {
        $user = User::factory()->create();
        $type = Type::factory()->create();

        $this->actingAs($user)
            ->get(route('admin.types.edit', $type))
            ->assertOk();
    }

    public function test_edit_prepopulates_fields(): void
    {
        $user = User::factory()->create();
        $type = Type::factory()->create(['name' => 'Producer', 'is_crew' => true]);

        $this->actingAs($user)
            ->get(route('admin.types.edit', $type))
            ->assertSee('Producer');
    }

    // -------------------------------------------------------------------------
    // Update
    // -------------------------------------------------------------------------

    public function test_update_updates_type_and_redirects(): void
    {
        $user = User::factory()->create();
        $type = Type::factory()->create(['name' => 'Old Name', 'is_crew' => false]);

        $this->actingAs($user)
            ->patch(route('admin.types.update', $type), [
                'name'    => 'New Name',
                'is_crew' => '1',
            ])
            ->assertRedirect(route('admin.types.index'))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('types', ['id' => $type->id, 'name' => 'New Name', 'is_crew' => true]);
    }

    public function test_update_validates_required_name(): void
    {
        $user = User::factory()->create();
        $type = Type::factory()->create();

        $this->actingAs($user)
            ->patch(route('admin.types.update', $type), [])
            ->assertSessionHasErrors('name');
    }

    public function test_update_rejects_duplicate_name_for_different_type(): void
    {
        $user  = User::factory()->create();
        Type::factory()->create(['name' => 'Director']);
        $type = Type::factory()->create(['name' => 'Actor']);

        $this->actingAs($user)
            ->patch(route('admin.types.update', $type), ['name' => 'Director'])
            ->assertSessionHasErrors('name');
    }

    public function test_update_allows_keeping_same_name(): void
    {
        $user = User::factory()->create();
        $type = Type::factory()->create(['name' => 'Director']);

        $this->actingAs($user)
            ->patch(route('admin.types.update', $type), [
                'name'    => 'Director',
                'is_crew' => '1',
            ])
            ->assertRedirect(route('admin.types.index'));
    }

    public function test_update_requires_authentication(): void
    {
        $type = Type::factory()->create(['name' => 'Original']);

        $this->patch(route('admin.types.update', $type), ['name' => 'Changed'])
            ->assertRedirect(route('login'));

        $this->assertDatabaseHas('types', ['id' => $type->id, 'name' => 'Original']);
    }

    // -------------------------------------------------------------------------
    // Destroy
    // -------------------------------------------------------------------------

    public function test_destroy_deletes_type_and_redirects(): void
    {
        $user = User::factory()->create();
        $type = Type::factory()->create();

        $this->actingAs($user)
            ->delete(route('admin.types.destroy', $type))
            ->assertRedirect(route('admin.types.index'))
            ->assertSessionHas('success');

        $this->assertDatabaseMissing('types', ['id' => $type->id]);
    }

    public function test_destroy_requires_authentication(): void
    {
        $type = Type::factory()->create();

        $this->delete(route('admin.types.destroy', $type))
            ->assertRedirect(route('login'));

        $this->assertDatabaseHas('types', ['id' => $type->id]);
    }
}
