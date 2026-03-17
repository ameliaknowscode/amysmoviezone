<?php

namespace Tests\Feature;

use App\Models\Movie;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MovieControllerTest extends TestCase
{
    use RefreshDatabase;

    // -------------------------------------------------------------------------
    // Authentication guard
    // -------------------------------------------------------------------------

    public function test_unauthenticated_user_is_redirected_from_index(): void
    {
        $this->get(route('admin.movies.index'))
            ->assertRedirect(route('login'));
    }

    public function test_unauthenticated_user_is_redirected_from_create(): void
    {
        $this->get(route('admin.movies.create'))
            ->assertRedirect(route('login'));
    }

    // -------------------------------------------------------------------------
    // Index
    // -------------------------------------------------------------------------

    public function test_index_returns_200_for_authenticated_user(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('admin.movies.index'))
            ->assertOk();
    }

    public function test_index_lists_movies(): void
    {
        $user   = User::factory()->create();
        $movies = Movie::factory()->count(3)->create();

        $response = $this->actingAs($user)
            ->get(route('admin.movies.index'));

        foreach ($movies as $movie) {
            $response->assertSee($movie->title);
        }
    }

    public function test_index_sorts_descending_by_default(): void
    {
        $user = User::factory()->create();
        Movie::factory()->create(['release_year' => 2000]);
        Movie::factory()->create(['release_year' => 2020]);
        Movie::factory()->create(['release_year' => 1990]);

        $this->actingAs($user)
            ->get(route('admin.movies.index'))
            ->assertViewHas('direction', 'desc');
    }

    public function test_index_sorts_ascending_when_requested(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('admin.movies.index', ['sort' => 'asc']))
            ->assertViewHas('direction', 'asc');
    }

    // -------------------------------------------------------------------------
    // Create
    // -------------------------------------------------------------------------

    public function test_create_returns_200_for_authenticated_user(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('admin.movies.create'))
            ->assertOk();
    }

    // -------------------------------------------------------------------------
    // Store
    // -------------------------------------------------------------------------

    public function test_store_creates_movie_and_redirects(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post(route('admin.movies.store'), [
                'title'        => 'The Matrix',
                'director'     => 'Wachowski Sisters',
                'release_year' => 1999,
            ])
            ->assertRedirect(route('admin.movies.index'))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('movies', [
            'title'        => 'The Matrix',
            'director'     => 'Wachowski Sisters',
            'release_year' => 1999,
        ]);
    }

    public function test_store_validates_required_title(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post(route('admin.movies.store'), [
                'director'     => 'Some Director',
                'release_year' => 2000,
            ])
            ->assertSessionHasErrors('title');
    }

    public function test_store_validates_required_director(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post(route('admin.movies.store'), [
                'title'        => 'Some Movie',
                'release_year' => 2000,
            ])
            ->assertSessionHasErrors('director');
    }

    public function test_store_validates_required_release_year(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post(route('admin.movies.store'), [
                'title'    => 'Some Movie',
                'director' => 'Some Director',
            ])
            ->assertSessionHasErrors('release_year');
    }

    public function test_store_rejects_release_year_before_1888(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post(route('admin.movies.store'), [
                'title'        => 'Ancient Film',
                'director'     => 'Someone',
                'release_year' => 1887,
            ])
            ->assertSessionHasErrors('release_year');
    }

    public function test_store_rejects_non_integer_release_year(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post(route('admin.movies.store'), [
                'title'        => 'Some Movie',
                'director'     => 'Someone',
                'release_year' => 'not-a-year',
            ])
            ->assertSessionHasErrors('release_year');
    }

    // -------------------------------------------------------------------------
    // Show (public — no auth required)
    // -------------------------------------------------------------------------

    public function test_show_returns_200_for_unauthenticated_user(): void
    {
        $movie = Movie::factory()->create();

        $this->get(route('movies.show', $movie))
            ->assertOk();
    }

    public function test_show_displays_movie_details(): void
    {
        $movie = Movie::factory()->create([
            'title'        => 'The Matrix',
            'director'     => 'Wachowski Sisters',
            'release_year' => 1999,
        ]);

        $this->get(route('movies.show', $movie))
            ->assertSee('The Matrix')
            ->assertSee('Wachowski Sisters')
            ->assertSee('1999');
    }

    public function test_show_returns_404_for_nonexistent_movie(): void
    {
        $this->get(route('movies.show', 999999))
            ->assertNotFound();
    }

    // -------------------------------------------------------------------------
    // Search (public — no auth required)
    // -------------------------------------------------------------------------

    public function test_search_returns_200_for_unauthenticated_user(): void
    {
        $this->get(route('search'))
            ->assertOk();
    }

    public function test_search_with_no_query_returns_empty_results(): void
    {
        Movie::factory()->count(3)->create();

        $this->get(route('search'))
            ->assertViewHas('movies', function ($movies) {
                return $movies->isEmpty();
            });
    }

    public function test_search_finds_movies_by_partial_title(): void
    {
        Movie::factory()->create(['title' => 'The Matrix']);
        Movie::factory()->create(['title' => 'Inception']);

        $this->get(route('search', ['q' => 'Matrix']))
            ->assertSee('The Matrix')
            ->assertDontSee('Inception');
    }

    public function test_search_is_case_insensitive(): void
    {
        Movie::factory()->create(['title' => 'The Matrix']);

        $this->get(route('search', ['q' => 'matrix']))
            ->assertSee('The Matrix');
    }

    public function test_search_returns_empty_for_unmatched_query(): void
    {
        Movie::factory()->create(['title' => 'The Matrix']);

        $this->get(route('search', ['q' => 'zzznomatch']))
            ->assertViewHas('movies', function ($movies) {
                return $movies->isEmpty();
            });
    }

    // -------------------------------------------------------------------------
    // Destroy
    // -------------------------------------------------------------------------

    public function test_destroy_deletes_movie_and_redirects(): void
    {
        $user  = User::factory()->create();
        $movie = Movie::factory()->create();

        $this->actingAs($user)
            ->delete(route('admin.movies.destroy', $movie))
            ->assertRedirect(route('admin.movies.index'))
            ->assertSessionHas('success');

        $this->assertDatabaseMissing('movies', ['id' => $movie->id]);
    }

    public function test_destroy_requires_authentication(): void
    {
        $movie = Movie::factory()->create();

        $this->delete(route('admin.movies.destroy', $movie))
            ->assertRedirect(route('login'));

        $this->assertDatabaseHas('movies', ['id' => $movie->id]);
    }
}
