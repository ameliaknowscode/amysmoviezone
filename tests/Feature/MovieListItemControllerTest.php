<?php

namespace Tests\Feature;

use App\Models\Movie;
use App\Models\MovieList;
use App\Models\MovieListItem;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MovieListItemControllerTest extends TestCase
{
    use RefreshDatabase;

    // -------------------------------------------------------------------------
    // Store
    // -------------------------------------------------------------------------

    public function test_owner_can_add_a_movie_to_their_list(): void
    {
        $user  = User::factory()->create();
        $list  = MovieList::factory()->create(['user_id' => $user->id]);
        $movie = Movie::factory()->create();

        $this->actingAs($user)
            ->post(route('lists.movies.store', $list), ['movie_id' => $movie->id])
            ->assertRedirect();

        $this->assertDatabaseHas('movie_list_items', [
            'movie_list_id' => $list->id,
            'movie_id'      => $movie->id,
        ]);
    }

    public function test_non_owner_cannot_add_movie_to_list(): void
    {
        $owner = User::factory()->create();
        $other = User::factory()->create();
        $list  = MovieList::factory()->create(['user_id' => $owner->id]);
        $movie = Movie::factory()->create();

        $this->actingAs($other)
            ->post(route('lists.movies.store', $list), ['movie_id' => $movie->id])
            ->assertForbidden();

        $this->assertDatabaseCount('movie_list_items', 0);
    }

    public function test_adding_a_movie_assigns_next_position(): void
    {
        $user  = User::factory()->create();
        $list  = MovieList::factory()->create(['user_id' => $user->id]);
        $movie1 = Movie::factory()->create();
        $movie2 = Movie::factory()->create();

        $this->actingAs($user)->post(route('lists.movies.store', $list), ['movie_id' => $movie1->id]);
        $this->actingAs($user)->post(route('lists.movies.store', $list), ['movie_id' => $movie2->id]);

        $this->assertDatabaseHas('movie_list_items', ['movie_id' => $movie1->id, 'position' => 1]);
        $this->assertDatabaseHas('movie_list_items', ['movie_id' => $movie2->id, 'position' => 2]);
    }

    public function test_adding_duplicate_movie_does_not_create_second_entry(): void
    {
        $user  = User::factory()->create();
        $list  = MovieList::factory()->create(['user_id' => $user->id]);
        $movie = Movie::factory()->create();

        $this->actingAs($user)->post(route('lists.movies.store', $list), ['movie_id' => $movie->id]);
        $this->actingAs($user)->post(route('lists.movies.store', $list), ['movie_id' => $movie->id]);

        $this->assertDatabaseCount('movie_list_items', 1);
    }

    public function test_store_returns_json_when_expecting_json(): void
    {
        $user  = User::factory()->create();
        $list  = MovieList::factory()->create(['user_id' => $user->id]);
        $movie = Movie::factory()->create();

        $this->actingAs($user)
            ->postJson(route('lists.movies.store', $list), ['movie_id' => $movie->id])
            ->assertJson(['in_list' => true]);
    }

    public function test_store_returns_in_list_true_for_duplicate_via_json(): void
    {
        $user  = User::factory()->create();
        $list  = MovieList::factory()->create(['user_id' => $user->id]);
        $movie = Movie::factory()->create();
        MovieListItem::create(['movie_list_id' => $list->id, 'movie_id' => $movie->id, 'position' => 1]);

        $this->actingAs($user)
            ->postJson(route('lists.movies.store', $list), ['movie_id' => $movie->id])
            ->assertJson(['in_list' => true]);
    }

    public function test_store_validates_movie_id_exists(): void
    {
        $user = User::factory()->create();
        $list = MovieList::factory()->create(['user_id' => $user->id]);

        $this->actingAs($user)
            ->post(route('lists.movies.store', $list), ['movie_id' => 99999])
            ->assertSessionHasErrors('movie_id');
    }

    public function test_unauthenticated_user_cannot_add_movie(): void
    {
        $list  = MovieList::factory()->create();
        $movie = Movie::factory()->create();

        $this->post(route('lists.movies.store', $list), ['movie_id' => $movie->id])
            ->assertRedirect(route('login'));
    }

    // -------------------------------------------------------------------------
    // Destroy
    // -------------------------------------------------------------------------

    public function test_owner_can_remove_a_movie_from_their_list(): void
    {
        $user  = User::factory()->create();
        $list  = MovieList::factory()->create(['user_id' => $user->id]);
        $movie = Movie::factory()->create();
        MovieListItem::create(['movie_list_id' => $list->id, 'movie_id' => $movie->id, 'position' => 1]);

        $this->actingAs($user)
            ->delete(route('lists.movies.destroy', [$list, $movie]))
            ->assertRedirect();

        $this->assertDatabaseMissing('movie_list_items', [
            'movie_list_id' => $list->id,
            'movie_id'      => $movie->id,
        ]);
    }

    public function test_non_owner_cannot_remove_movie_from_list(): void
    {
        $owner = User::factory()->create();
        $other = User::factory()->create();
        $list  = MovieList::factory()->create(['user_id' => $owner->id]);
        $movie = Movie::factory()->create();
        MovieListItem::create(['movie_list_id' => $list->id, 'movie_id' => $movie->id, 'position' => 1]);

        $this->actingAs($other)
            ->delete(route('lists.movies.destroy', [$list, $movie]))
            ->assertForbidden();

        $this->assertDatabaseCount('movie_list_items', 1);
    }

    public function test_destroy_returns_json_when_expecting_json(): void
    {
        $user  = User::factory()->create();
        $list  = MovieList::factory()->create(['user_id' => $user->id]);
        $movie = Movie::factory()->create();
        MovieListItem::create(['movie_list_id' => $list->id, 'movie_id' => $movie->id, 'position' => 1]);

        $this->actingAs($user)
            ->deleteJson(route('lists.movies.destroy', [$list, $movie]))
            ->assertJson(['in_list' => false]);
    }

    public function test_unauthenticated_user_cannot_remove_movie(): void
    {
        $list  = MovieList::factory()->create();
        $movie = Movie::factory()->create();

        $this->delete(route('lists.movies.destroy', [$list, $movie]))
            ->assertRedirect(route('login'));
    }

    // -------------------------------------------------------------------------
    // Reorder
    // -------------------------------------------------------------------------

    public function test_owner_can_reorder_list_items(): void
    {
        $user  = User::factory()->create();
        $list  = MovieList::factory()->create(['user_id' => $user->id]);
        $movie1 = Movie::factory()->create();
        $movie2 = Movie::factory()->create();
        $item1 = MovieListItem::create(['movie_list_id' => $list->id, 'movie_id' => $movie1->id, 'position' => 1]);
        $item2 = MovieListItem::create(['movie_list_id' => $list->id, 'movie_id' => $movie2->id, 'position' => 2]);

        $this->actingAs($user)
            ->postJson(route('lists.movies.reorder', $list), ['order' => [$item2->id, $item1->id]])
            ->assertJson(['ok' => true]);

        $this->assertDatabaseHas('movie_list_items', ['id' => $item2->id, 'position' => 1]);
        $this->assertDatabaseHas('movie_list_items', ['id' => $item1->id, 'position' => 2]);
    }

    public function test_non_owner_cannot_reorder_list(): void
    {
        $owner = User::factory()->create();
        $other = User::factory()->create();
        $list  = MovieList::factory()->create(['user_id' => $owner->id]);
        $movie = Movie::factory()->create();
        $item  = MovieListItem::create(['movie_list_id' => $list->id, 'movie_id' => $movie->id, 'position' => 1]);

        $this->actingAs($other)
            ->postJson(route('lists.movies.reorder', $list), ['order' => [$item->id]])
            ->assertForbidden();
    }

    public function test_reorder_validates_order_is_required(): void
    {
        $user = User::factory()->create();
        $list = MovieList::factory()->create(['user_id' => $user->id]);

        $this->actingAs($user)
            ->postJson(route('lists.movies.reorder', $list), [])
            ->assertJsonValidationErrors('order');
    }
}
