<?php

namespace Tests\Feature;

use App\Models\Movie;
use App\Models\MovieList;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MovieListControllerTest extends TestCase
{
    use RefreshDatabase;

    // -------------------------------------------------------------------------
    // Authentication guard
    // -------------------------------------------------------------------------

    public function test_unauthenticated_user_cannot_view_lists(): void
    {
        $this->get(route('lists.index'))->assertRedirect(route('login'));
    }

    public function test_unauthenticated_user_cannot_view_create_form(): void
    {
        $this->get(route('lists.create'))->assertRedirect(route('login'));
    }

    public function test_unauthenticated_user_cannot_store_list(): void
    {
        $this->post(route('lists.store'), ['name' => 'My List'])->assertRedirect(route('login'));
    }

    public function test_unauthenticated_user_cannot_edit_list(): void
    {
        $list = MovieList::factory()->create();
        $this->get(route('lists.edit', $list))->assertRedirect(route('login'));
    }

    public function test_unauthenticated_user_cannot_update_list(): void
    {
        $list = MovieList::factory()->create();
        $this->put(route('lists.update', $list), ['name' => 'Updated'])->assertRedirect(route('login'));
    }

    public function test_unauthenticated_user_cannot_delete_list(): void
    {
        $list = MovieList::factory()->create();
        $this->delete(route('lists.destroy', $list))->assertRedirect(route('login'));
    }

    // -------------------------------------------------------------------------
    // Index
    // -------------------------------------------------------------------------

    public function test_index_returns_200_for_authenticated_user(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->get(route('lists.index'))->assertOk();
    }

    public function test_index_shows_only_the_users_own_lists(): void
    {
        $user  = User::factory()->create();
        $other = User::factory()->create();
        MovieList::factory()->create(['user_id' => $user->id, 'name' => 'My Favourites']);
        MovieList::factory()->create(['user_id' => $other->id, 'name' => 'Their Favourites']);

        $this->actingAs($user)
            ->get(route('lists.index'))
            ->assertSee('My Favourites')
            ->assertDontSee('Their Favourites');
    }

    // -------------------------------------------------------------------------
    // Create
    // -------------------------------------------------------------------------

    public function test_create_returns_200(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->get(route('lists.create'))->assertOk();
    }

    // -------------------------------------------------------------------------
    // Store
    // -------------------------------------------------------------------------

    public function test_user_can_create_a_list(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post(route('lists.store'), [
                'name'        => 'Sci-Fi Classics',
                'description' => 'The best sci-fi ever',
                'is_public'   => '1',
                'is_ranked'   => '0',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('movie_lists', [
            'user_id'     => $user->id,
            'name'        => 'Sci-Fi Classics',
            'description' => 'The best sci-fi ever',
            'is_public'   => true,
            'is_ranked'   => false,
        ]);
    }

    public function test_store_requires_name(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post(route('lists.store'), ['name' => ''])
            ->assertSessionHasErrors('name');
    }

    public function test_store_name_cannot_exceed_100_characters(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post(route('lists.store'), ['name' => str_repeat('a', 101)])
            ->assertSessionHasErrors('name');
    }

    public function test_store_description_cannot_exceed_1000_characters(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post(route('lists.store'), [
                'name'        => 'Valid Name',
                'description' => str_repeat('a', 1001),
            ])
            ->assertSessionHasErrors('description');
    }

    public function test_store_redirects_to_show_on_success(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->post(route('lists.store'), ['name' => 'New List']);

        $list = MovieList::where('user_id', $user->id)->first();
        $response->assertRedirect(route('lists.show', $list));
    }

    // -------------------------------------------------------------------------
    // Show
    // -------------------------------------------------------------------------

    public function test_authenticated_user_can_view_a_public_list(): void
    {
        $owner = User::factory()->create();
        $other = User::factory()->create();
        $list  = MovieList::factory()->create(['user_id' => $owner->id, 'is_public' => true]);

        $this->actingAs($other)->get(route('lists.show', $list))->assertOk();
    }

    public function test_owner_can_view_their_private_list(): void
    {
        $owner = User::factory()->create();
        $list  = MovieList::factory()->create(['user_id' => $owner->id, 'is_public' => false]);

        $this->actingAs($owner)->get(route('lists.show', $list))->assertOk();
    }

    public function test_other_user_cannot_view_private_list(): void
    {
        $owner = User::factory()->create();
        $other = User::factory()->create();
        $list  = MovieList::factory()->create(['user_id' => $owner->id, 'is_public' => false]);

        $this->actingAs($other)->get(route('lists.show', $list))->assertForbidden();
    }

    public function test_guest_is_redirected_to_login_when_viewing_list(): void
    {
        $owner = User::factory()->create();
        $list  = MovieList::factory()->create(['user_id' => $owner->id, 'is_public' => true]);

        $this->get(route('lists.show', $list))->assertRedirect(route('login'));
    }

    // -------------------------------------------------------------------------
    // Edit
    // -------------------------------------------------------------------------

    public function test_owner_can_view_edit_form(): void
    {
        $user = User::factory()->create();
        $list = MovieList::factory()->create(['user_id' => $user->id]);

        $this->actingAs($user)->get(route('lists.edit', $list))->assertOk();
    }

    public function test_non_owner_cannot_view_edit_form(): void
    {
        $owner = User::factory()->create();
        $other = User::factory()->create();
        $list  = MovieList::factory()->create(['user_id' => $owner->id]);

        $this->actingAs($other)->get(route('lists.edit', $list))->assertForbidden();
    }

    // -------------------------------------------------------------------------
    // Update
    // -------------------------------------------------------------------------

    public function test_owner_can_update_their_list(): void
    {
        $user = User::factory()->create();
        $list = MovieList::factory()->create(['user_id' => $user->id, 'name' => 'Old Name']);

        $this->actingAs($user)
            ->put(route('lists.update', $list), [
                'name'      => 'New Name',
                'is_public' => '1',
                'is_ranked' => '1',
            ])
            ->assertRedirect(route('lists.show', $list));

        $this->assertDatabaseHas('movie_lists', [
            'id'        => $list->id,
            'name'      => 'New Name',
            'is_ranked' => true,
        ]);
    }

    public function test_non_owner_cannot_update_a_list(): void
    {
        $owner = User::factory()->create();
        $other = User::factory()->create();
        $list  = MovieList::factory()->create(['user_id' => $owner->id, 'name' => 'Original']);

        $this->actingAs($other)
            ->put(route('lists.update', $list), ['name' => 'Hijacked'])
            ->assertForbidden();

        $this->assertDatabaseHas('movie_lists', ['id' => $list->id, 'name' => 'Original']);
    }

    public function test_update_requires_name(): void
    {
        $user = User::factory()->create();
        $list = MovieList::factory()->create(['user_id' => $user->id]);

        $this->actingAs($user)
            ->put(route('lists.update', $list), ['name' => ''])
            ->assertSessionHasErrors('name');
    }

    // -------------------------------------------------------------------------
    // Destroy
    // -------------------------------------------------------------------------

    public function test_owner_can_delete_their_list(): void
    {
        $user = User::factory()->create();
        $list = MovieList::factory()->create(['user_id' => $user->id]);

        $this->actingAs($user)
            ->delete(route('lists.destroy', $list))
            ->assertRedirect(route('lists.index'));

        $this->assertDatabaseMissing('movie_lists', ['id' => $list->id]);
    }

    public function test_non_owner_cannot_delete_a_list(): void
    {
        $owner = User::factory()->create();
        $other = User::factory()->create();
        $list  = MovieList::factory()->create(['user_id' => $owner->id]);

        $this->actingAs($other)->delete(route('lists.destroy', $list))->assertForbidden();

        $this->assertDatabaseHas('movie_lists', ['id' => $list->id]);
    }
}
