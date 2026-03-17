<?php

namespace Tests\Feature;

use App\Models\Actor;
use App\Models\Credit;
use App\Models\Movie;
use App\Models\Person;
use App\Models\Type;
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

    public function test_unauthenticated_user_is_redirected_from_edit(): void
    {
        $movie = Movie::factory()->create();

        $this->get(route('admin.movies.edit', $movie))
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
    // Edit
    // -------------------------------------------------------------------------

    public function test_edit_returns_200_for_authenticated_user(): void
    {
        $user  = User::factory()->create();
        $movie = Movie::factory()->create();

        $this->actingAs($user)
            ->get(route('admin.movies.edit', $movie))
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

    public function test_store_syncs_credits(): void
    {
        $user          = User::factory()->create();
        $person1       = Person::factory()->create();
        $person2       = Person::factory()->create();
        $actorType     = Type::firstOrCreate(['name' => 'Actor'],    ['is_crew' => false]);
        $directorType  = Type::firstOrCreate(['name' => 'Director'], ['is_crew' => true]);

        $this->actingAs($user)
            ->post(route('admin.movies.store'), [
                'title'        => 'Credits Movie',
                'director'     => 'Some Director',
                'release_year' => 2000,
                'credits'      => [
                    ['person_id' => $person1->id, 'type_id' => $actorType->id,    'character' => 'Hero'],
                    ['person_id' => $person2->id, 'type_id' => $directorType->id, 'character' => ''],
                ],
            ]);

        $movie = Movie::where('title', 'Credits Movie')->firstOrFail();
        $this->assertCount(2, $movie->credits);

        $this->assertDatabaseHas('credits', [
            'movie_id'  => $movie->id,
            'person_id' => $person1->id,
            'type_id'   => $actorType->id,
            'character' => 'Hero',
        ]);
        $this->assertDatabaseHas('credits', [
            'movie_id'  => $movie->id,
            'person_id' => $person2->id,
            'type_id'   => $directorType->id,
        ]);
    }

    // -------------------------------------------------------------------------
    // Update
    // -------------------------------------------------------------------------

    public function test_update_updates_movie_and_redirects(): void
    {
        $user  = User::factory()->create();
        $movie = Movie::factory()->create(['title' => 'Old Title']);

        $this->actingAs($user)
            ->patch(route('admin.movies.update', $movie), [
                'title'        => 'New Title',
                'director'     => 'New Director',
                'release_year' => 2005,
            ])
            ->assertRedirect(route('admin.movies.index'))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('movies', ['id' => $movie->id, 'title' => 'New Title']);
    }

    public function test_update_validates_required_title(): void
    {
        $user  = User::factory()->create();
        $movie = Movie::factory()->create();

        $this->actingAs($user)
            ->patch(route('admin.movies.update', $movie), [
                'director'     => 'Some Director',
                'release_year' => 2000,
            ])
            ->assertSessionHasErrors('title');
    }

    public function test_update_requires_authentication(): void
    {
        $movie = Movie::factory()->create(['title' => 'Original']);

        $this->patch(route('admin.movies.update', $movie), [
            'title'        => 'Changed',
            'director'     => 'Someone',
            'release_year' => 2000,
        ])->assertRedirect(route('login'));

        $this->assertDatabaseHas('movies', ['id' => $movie->id, 'title' => 'Original']);
    }

    public function test_update_syncs_credits(): void
    {
        $user      = User::factory()->create();
        $movie     = Movie::factory()->create();
        $person1   = Person::factory()->create();
        $person2   = Person::factory()->create();
        $actorType = Type::firstOrCreate(['name' => 'Actor'], ['is_crew' => false]);

        // Pre-existing credit for person1
        Credit::factory()->create([
            'movie_id'  => $movie->id,
            'person_id' => $person1->id,
            'type_id'   => $actorType->id,
        ]);

        $this->actingAs($user)
            ->patch(route('admin.movies.update', $movie), [
                'title'        => $movie->title,
                'director'     => $movie->director,
                'release_year' => $movie->release_year,
                'credits'      => [
                    ['person_id' => $person2->id, 'type_id' => $actorType->id, 'character' => 'Protagonist'],
                ],
            ]);

        $movie->refresh();
        $this->assertCount(1, $movie->credits);
        $this->assertDatabaseHas('credits', [
            'movie_id'  => $movie->id,
            'person_id' => $person2->id,
            'type_id'   => $actorType->id,
            'character' => 'Protagonist',
        ]);
        $this->assertDatabaseMissing('credits', [
            'movie_id'  => $movie->id,
            'person_id' => $person1->id,
        ]);
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
            'release_year' => 1999,
        ]);

        $this->get(route('movies.show', $movie))
            ->assertSee('The Matrix')
            ->assertSee('1999');
    }

    public function test_show_returns_404_for_nonexistent_movie(): void
    {
        $this->get(route('movies.show', 999999))
            ->assertNotFound();
    }

    public function test_show_displays_cast_section(): void
    {
        $movie  = Movie::factory()->create();
        $person = Person::factory()->create(['name' => 'Keanu Reeves']);
        $type   = Type::firstOrCreate(['name' => 'Actor'], ['is_crew' => false]);
        Credit::factory()->create([
            'movie_id'  => $movie->id,
            'person_id' => $person->id,
            'type_id'   => $type->id,
            'character' => 'Neo',
        ]);

        $this->get(route('movies.show', $movie))
            ->assertSee('Keanu Reeves')
            ->assertSee('Neo');
    }

    public function test_show_displays_crew_section(): void
    {
        $movie  = Movie::factory()->create();
        $person = Person::factory()->create(['name' => 'Christopher Nolan']);
        $type   = Type::factory()->create(['name' => 'Director', 'is_crew' => true]);
        Credit::factory()->create([
            'movie_id'  => $movie->id,
            'person_id' => $person->id,
            'type_id'   => $type->id,
        ]);

        $this->get(route('movies.show', $movie))
            ->assertSee('Christopher Nolan')
            ->assertSee('Director');
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

    public function test_search_finds_people_by_name(): void
    {
        Person::factory()->create(['name' => 'Meryl Streep']);
        Person::factory()->create(['name' => 'Tom Hanks']);

        $this->get(route('search', ['q' => 'Meryl']))
            ->assertSee('Meryl Streep')
            ->assertDontSee('Tom Hanks');
    }

    public function test_search_with_no_query_returns_empty_people(): void
    {
        Person::factory()->count(2)->create();

        $this->get(route('search'))
            ->assertViewHas('people', function ($people) {
                return $people->isEmpty();
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
