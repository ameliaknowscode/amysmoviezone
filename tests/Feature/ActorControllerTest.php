<?php

namespace Tests\Feature;

use App\Models\Actor;
use App\Models\Movie;
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

    public function test_unauthenticated_user_is_redirected_from_edit(): void
    {
        $actor = Actor::factory()->create();

        $this->get(route('admin.actors.edit', $actor))
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
    // Show (public — no auth required)
    // -------------------------------------------------------------------------

    public function test_show_returns_200_for_unauthenticated_user(): void
    {
        $actor = Actor::factory()->create();

        $this->get(route('actors.show', $actor))
            ->assertOk();
    }

    public function test_show_displays_actor_details(): void
    {
        $actor = Actor::factory()->create([
            'name'        => 'Cate Blanchett',
            'nationality' => 'Australian',
        ]);

        $this->get(route('actors.show', $actor))
            ->assertSee('Cate Blanchett')
            ->assertSee('Australian');
    }

    public function test_show_displays_filmography(): void
    {
        $actor = Actor::factory()->create(['name' => 'Cate Blanchett']);
        $movie = Movie::factory()->create(['title' => 'The Aviator']);
        $actor->movies()->attach($movie);

        $this->get(route('actors.show', $actor))
            ->assertSee('The Aviator');
    }

    public function test_show_returns_404_for_nonexistent_actor(): void
    {
        $this->get(route('actors.show', 999999))
            ->assertNotFound();
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

    public function test_store_syncs_movies(): void
    {
        $user   = User::factory()->create();
        $movie1 = Movie::factory()->create();
        $movie2 = Movie::factory()->create();

        $this->actingAs($user)
            ->post(route('admin.actors.store'), [
                'name'      => 'Film Actor',
                'movie_ids' => [$movie1->id, $movie2->id],
            ]);

        $actor = Actor::where('name', 'Film Actor')->firstOrFail();
        $this->assertCount(2, $actor->movies);
        $this->assertTrue($actor->movies->contains($movie1));
        $this->assertTrue($actor->movies->contains($movie2));
    }

    // -------------------------------------------------------------------------
    // Edit
    // -------------------------------------------------------------------------

    public function test_edit_returns_200_for_authenticated_user(): void
    {
        $user  = User::factory()->create();
        $actor = Actor::factory()->create();

        $this->actingAs($user)
            ->get(route('admin.actors.edit', $actor))
            ->assertOk();
    }

    // -------------------------------------------------------------------------
    // Update
    // -------------------------------------------------------------------------

    public function test_update_updates_actor_and_redirects(): void
    {
        $user  = User::factory()->create();
        $actor = Actor::factory()->create(['name' => 'Old Name']);

        $this->actingAs($user)
            ->patch(route('admin.actors.update', $actor), [
                'name'        => 'New Name',
                'nationality' => 'British',
            ])
            ->assertRedirect(route('admin.actors.index'))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('actors', ['id' => $actor->id, 'name' => 'New Name']);
    }

    public function test_update_validates_required_name(): void
    {
        $user  = User::factory()->create();
        $actor = Actor::factory()->create();

        $this->actingAs($user)
            ->patch(route('admin.actors.update', $actor), [
                'nationality' => 'British',
            ])
            ->assertSessionHasErrors('name');
    }

    public function test_update_rejects_future_date_of_birth(): void
    {
        $user  = User::factory()->create();
        $actor = Actor::factory()->create();

        $this->actingAs($user)
            ->patch(route('admin.actors.update', $actor), [
                'name'          => $actor->name,
                'date_of_birth' => now()->addYear()->format('Y-m-d'),
            ])
            ->assertSessionHasErrors('date_of_birth');
    }

    public function test_update_requires_authentication(): void
    {
        $actor = Actor::factory()->create(['name' => 'Original']);

        $this->patch(route('admin.actors.update', $actor), ['name' => 'Changed'])
            ->assertRedirect(route('login'));

        $this->assertDatabaseHas('actors', ['id' => $actor->id, 'name' => 'Original']);
    }

    public function test_update_syncs_movies(): void
    {
        $user   = User::factory()->create();
        $actor  = Actor::factory()->create();
        $movie1 = Movie::factory()->create();
        $movie2 = Movie::factory()->create();
        $actor->movies()->sync([$movie1->id]);

        $this->actingAs($user)
            ->patch(route('admin.actors.update', $actor), [
                'name'      => $actor->name,
                'movie_ids' => [$movie2->id],
            ]);

        $actor->refresh();
        $this->assertCount(1, $actor->movies);
        $this->assertTrue($actor->movies->contains($movie2));
        $this->assertFalse($actor->movies->contains($movie1));
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
