<?php

namespace Tests\Feature;

use App\Models\Actor;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ActorControllerTest extends TestCase
{
    use RefreshDatabase;

    // -------------------------------------------------------------------------
    // Authentication guard
    // -------------------------------------------------------------------------

    public function test_unauthenticated_user_is_redirected_from_index(): void
    {
        $this->get(route('admin.actors.index'))
            ->assertRedirect(route('login'));
    }

    public function test_unauthenticated_user_is_redirected_from_create(): void
    {
        $this->get(route('admin.actors.create'))
            ->assertRedirect(route('login'));
    }

    // -------------------------------------------------------------------------
    // Index
    // -------------------------------------------------------------------------

    public function test_index_returns_200_for_authenticated_user(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('admin.actors.index'))
            ->assertOk();
    }

    public function test_index_lists_actors(): void
    {
        $user   = User::factory()->create();
        $actors = Actor::factory()->count(3)->create();

        $response = $this->actingAs($user)
            ->get(route('admin.actors.index'));

        foreach ($actors as $actor) {
            $response->assertSee($actor->name);
        }
    }

    // -------------------------------------------------------------------------
    // Create
    // -------------------------------------------------------------------------

    public function test_create_returns_200_for_authenticated_user(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('admin.actors.create'))
            ->assertOk();
    }

    // -------------------------------------------------------------------------
    // Store
    // -------------------------------------------------------------------------

    public function test_store_creates_actor_with_all_fields_and_redirects(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post(route('admin.actors.store'), [
                'name'          => 'Meryl Streep',
                'date_of_birth' => '1949-06-22',
                'nationality'   => 'American',
            ])
            ->assertRedirect(route('admin.actors.index'))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('actors', [
            'name'          => 'Meryl Streep',
            'date_of_birth' => '1949-06-22',
            'nationality'   => 'American',
        ]);
    }

    public function test_store_creates_actor_with_only_required_name(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post(route('admin.actors.store'), [
                'name' => 'Keanu Reeves',
            ])
            ->assertRedirect(route('admin.actors.index'))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('actors', ['name' => 'Keanu Reeves']);
    }

    public function test_store_validates_required_name(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post(route('admin.actors.store'), [
                'nationality' => 'British',
            ])
            ->assertSessionHasErrors('name');
    }

    public function test_store_rejects_future_date_of_birth(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post(route('admin.actors.store'), [
                'name'          => 'Future Person',
                'date_of_birth' => now()->addYear()->format('Y-m-d'),
            ])
            ->assertSessionHasErrors('date_of_birth');
    }

    public function test_store_rejects_invalid_date_of_birth(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post(route('admin.actors.store'), [
                'name'          => 'Some Actor',
                'date_of_birth' => 'not-a-date',
            ])
            ->assertSessionHasErrors('date_of_birth');
    }

    public function test_store_requires_authentication(): void
    {
        $this->post(route('admin.actors.store'), ['name' => 'Someone'])
            ->assertRedirect(route('login'));

        $this->assertDatabaseMissing('actors', ['name' => 'Someone']);
    }

    // -------------------------------------------------------------------------
    // Destroy
    // -------------------------------------------------------------------------

    public function test_destroy_deletes_actor_and_redirects(): void
    {
        $user  = User::factory()->create();
        $actor = Actor::factory()->create();

        $this->actingAs($user)
            ->delete(route('admin.actors.destroy', $actor))
            ->assertRedirect(route('admin.actors.index'))
            ->assertSessionHas('success');

        $this->assertDatabaseMissing('actors', ['id' => $actor->id]);
    }

    public function test_destroy_requires_authentication(): void
    {
        $actor = Actor::factory()->create();

        $this->delete(route('admin.actors.destroy', $actor))
            ->assertRedirect(route('login'));

        $this->assertDatabaseHas('actors', ['id' => $actor->id]);
    }
}
