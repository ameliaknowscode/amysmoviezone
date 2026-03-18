<?php

namespace Tests\Feature;

use App\Models\Credit;
use App\Models\Movie;
use App\Models\Person;
use App\Models\Type;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SearchControllerTest extends TestCase
{
    use RefreshDatabase;

    // =========================================================================
    // search
    // =========================================================================

    public function test_search_page_loads_without_query(): void
    {
        $this->get(route('search'))->assertOk();
    }

    public function test_search_is_accessible_without_authentication(): void
    {
        $this->get(route('search', ['q' => 'anything']))->assertOk();
    }

    public function test_search_returns_movie_matching_title(): void
    {
        Movie::factory()->create(['title' => 'The Godfather', 'release_year' => 1972]);

        $this->get(route('search', ['q' => 'Godfather']))
            ->assertSee('The Godfather');
    }

    public function test_search_does_not_return_movie_not_matching_title(): void
    {
        Movie::factory()->create(['title' => 'Casablanca', 'release_year' => 1942]);

        $this->get(route('search', ['q' => 'Godfather']))
            ->assertDontSee('Casablanca');
    }

    public function test_search_returns_person_matching_name(): void
    {
        Person::factory()->create(['name' => 'Meryl Streep']);

        $this->get(route('search', ['q' => 'Meryl']))
            ->assertSee('Meryl Streep');
    }

    public function test_search_does_not_return_person_not_matching_name(): void
    {
        Person::factory()->create(['name' => 'Tom Hanks']);

        $this->get(route('search', ['q' => 'Meryl']))
            ->assertDontSee('Tom Hanks');
    }

    public function test_search_with_empty_query_shows_no_results(): void
    {
        Movie::factory()->create(['title' => 'Inception']);
        Person::factory()->create(['name' => 'Leonardo DiCaprio']);

        $response = $this->get(route('search', ['q' => '']));

        $response->assertDontSee('Inception');
        $response->assertDontSee('Leonardo DiCaprio');
    }

    // =========================================================================
    // directorSearch
    // =========================================================================

    public function test_director_search_page_loads_without_params(): void
    {
        $this->get(route('director-connections'))->assertOk();
    }

    public function test_director_search_is_accessible_without_authentication(): void
    {
        $this->get(route('director-connections'))->assertOk();
    }

    public function test_director_search_lists_directors_in_dropdowns(): void
    {
        $directorType = Type::firstOrCreate(['name' => 'Director'], ['is_crew' => true]);
        $director = Person::factory()->create(['name' => 'Stanley Kubrick']);
        $movie = Movie::factory()->create();
        Credit::factory()->create([
            'person_id' => $director->id,
            'type_id'   => $directorType->id,
            'movie_id'  => $movie->id,
        ]);

        $this->get(route('director-connections'))
            ->assertSee('Stanley Kubrick');
    }

    public function test_director_search_does_not_list_non_directors_in_dropdowns(): void
    {
        $actorType = Type::firstOrCreate(['name' => 'Actor'], ['is_crew' => false]);
        $actor = Person::factory()->create(['name' => 'Jack Nicholson']);
        $movie = Movie::factory()->create();
        Credit::factory()->create([
            'person_id' => $actor->id,
            'type_id'   => $actorType->id,
            'movie_id'  => $movie->id,
        ]);

        $this->get(route('director-connections'))
            ->assertDontSee('Jack Nicholson');
    }

    public function test_director_search_returns_actors_for_one_director(): void
    {
        $directorType = Type::firstOrCreate(['name' => 'Director'], ['is_crew' => true]);
        $actorType    = Type::firstOrCreate(['name' => 'Actor'],    ['is_crew' => false]);

        $director = Person::factory()->create(['name' => 'Stanley Kubrick']);
        $actor    = Person::factory()->create(['name' => 'Jack Nicholson']);
        $movie    = Movie::factory()->create(['title' => 'The Shining']);

        Credit::factory()->create(['person_id' => $director->id, 'type_id' => $directorType->id, 'movie_id' => $movie->id]);
        Credit::factory()->create(['person_id' => $actor->id,    'type_id' => $actorType->id,    'movie_id' => $movie->id]);

        $this->get(route('director-connections', ['directors' => [$director->id]]))
            ->assertSee('Jack Nicholson');
    }

    public function test_director_search_returns_only_actors_in_all_directors_films(): void
    {
        $directorType = Type::firstOrCreate(['name' => 'Director'], ['is_crew' => true]);
        $actorType    = Type::firstOrCreate(['name' => 'Actor'],    ['is_crew' => false]);

        $director1   = Person::factory()->create(['name' => 'Stanley Kubrick']);
        $director2   = Person::factory()->create(['name' => 'Francis Coppola']);
        $sharedActor = Person::factory()->create(['name' => 'Robert Duvall']);
        $onlyD1Actor = Person::factory()->create(['name' => 'Jack Nicholson']);
        $movie1      = Movie::factory()->create();
        $movie2      = Movie::factory()->create();

        Credit::factory()->create(['person_id' => $director1->id,   'type_id' => $directorType->id, 'movie_id' => $movie1->id]);
        Credit::factory()->create(['person_id' => $director2->id,   'type_id' => $directorType->id, 'movie_id' => $movie2->id]);
        // Shared actor appears in both directors' films.
        Credit::factory()->create(['person_id' => $sharedActor->id, 'type_id' => $actorType->id,    'movie_id' => $movie1->id]);
        Credit::factory()->create(['person_id' => $sharedActor->id, 'type_id' => $actorType->id,    'movie_id' => $movie2->id]);
        // This actor only worked with director 1 — should be excluded.
        Credit::factory()->create(['person_id' => $onlyD1Actor->id, 'type_id' => $actorType->id,    'movie_id' => $movie1->id]);

        $response = $this->get(route('director-connections', [
            'directors' => [$director1->id, $director2->id],
        ]));

        $response->assertSee('Robert Duvall');
        $response->assertDontSee('Jack Nicholson');
    }

    public function test_director_search_does_not_return_actors_from_unrelated_films(): void
    {
        $directorType = Type::firstOrCreate(['name' => 'Director'], ['is_crew' => true]);
        $actorType    = Type::firstOrCreate(['name' => 'Actor'],    ['is_crew' => false]);

        $director      = Person::factory()->create(['name' => 'Stanley Kubrick']);
        $actor         = Person::factory()->create(['name' => 'Jack Nicholson']);
        $unrelatedActor = Person::factory()->create(['name' => 'Tom Hanks']);
        $movie         = Movie::factory()->create();
        $unrelatedMovie = Movie::factory()->create();

        Credit::factory()->create(['person_id' => $director->id,      'type_id' => $directorType->id, 'movie_id' => $movie->id]);
        Credit::factory()->create(['person_id' => $actor->id,          'type_id' => $actorType->id,    'movie_id' => $movie->id]);
        Credit::factory()->create(['person_id' => $unrelatedActor->id, 'type_id' => $actorType->id,    'movie_id' => $unrelatedMovie->id]);

        $this->get(route('director-connections', ['directors' => [$director->id]]))
            ->assertSee('Jack Nicholson')
            ->assertDontSee('Tom Hanks');
    }

    public function test_director_search_does_not_include_directors_as_actors(): void
    {
        $directorType = Type::firstOrCreate(['name' => 'Director'], ['is_crew' => true]);

        $director = Person::factory()->create(['name' => 'Stanley Kubrick']);
        $movie    = Movie::factory()->create();
        Credit::factory()->create(['person_id' => $director->id, 'type_id' => $directorType->id, 'movie_id' => $movie->id]);

        // Director has no actor credit — results should be empty.
        $this->get(route('director-connections', ['directors' => [$director->id]]))
            ->assertSee('No actors found for the selected director(s).');
    }

    public function test_director_search_actor_in_both_directors_films_appears_once(): void
    {
        $directorType = Type::firstOrCreate(['name' => 'Director'], ['is_crew' => true]);
        $actorType    = Type::firstOrCreate(['name' => 'Actor'],    ['is_crew' => false]);

        $director1    = Person::factory()->create(['name' => 'Stanley Kubrick']);
        $director2    = Person::factory()->create(['name' => 'Francis Coppola']);
        $sharedActor  = Person::factory()->create(['name' => 'Robert Duvall']);
        $movie1       = Movie::factory()->create();
        $movie2       = Movie::factory()->create();

        Credit::factory()->create(['person_id' => $director1->id,   'type_id' => $directorType->id, 'movie_id' => $movie1->id]);
        Credit::factory()->create(['person_id' => $director2->id,   'type_id' => $directorType->id, 'movie_id' => $movie2->id]);
        Credit::factory()->create(['person_id' => $sharedActor->id, 'type_id' => $actorType->id,    'movie_id' => $movie1->id]);
        Credit::factory()->create(['person_id' => $sharedActor->id, 'type_id' => $actorType->id,    'movie_id' => $movie2->id]);

        $response = $this->get(route('director-connections', [
            'directors' => [$director1->id, $director2->id],
        ]));

        $response->assertOk();
        $this->assertSame(1, substr_count($response->getContent(), 'Robert Duvall'));
    }

    public function test_director_search_with_no_selection_shows_no_results(): void
    {
        $actorType = Type::firstOrCreate(['name' => 'Actor'], ['is_crew' => false]);
        $actor = Person::factory()->create(['name' => 'Jack Nicholson']);
        $movie = Movie::factory()->create();
        Credit::factory()->create(['person_id' => $actor->id, 'type_id' => $actorType->id, 'movie_id' => $movie->id]);

        $this->get(route('director-connections'))
            ->assertDontSee('Jack Nicholson');
    }

    public function test_director_search_returns_actors_common_to_three_directors(): void
    {
        $directorType = Type::firstOrCreate(['name' => 'Director'], ['is_crew' => true]);
        $actorType    = Type::firstOrCreate(['name' => 'Actor'],    ['is_crew' => false]);

        $director1   = Person::factory()->create(['name' => 'Stanley Kubrick']);
        $director2   = Person::factory()->create(['name' => 'Francis Coppola']);
        $director3   = Person::factory()->create(['name' => 'Martin Scorsese']);
        $sharedActor = Person::factory()->create(['name' => 'Robert De Niro']);
        $onlyD1Actor = Person::factory()->create(['name' => 'Jack Nicholson']);
        $movie1      = Movie::factory()->create();
        $movie2      = Movie::factory()->create();
        $movie3      = Movie::factory()->create();

        Credit::factory()->create(['person_id' => $director1->id,   'type_id' => $directorType->id, 'movie_id' => $movie1->id]);
        Credit::factory()->create(['person_id' => $director2->id,   'type_id' => $directorType->id, 'movie_id' => $movie2->id]);
        Credit::factory()->create(['person_id' => $director3->id,   'type_id' => $directorType->id, 'movie_id' => $movie3->id]);
        // Shared actor appears in all three directors' films.
        Credit::factory()->create(['person_id' => $sharedActor->id, 'type_id' => $actorType->id,    'movie_id' => $movie1->id]);
        Credit::factory()->create(['person_id' => $sharedActor->id, 'type_id' => $actorType->id,    'movie_id' => $movie2->id]);
        Credit::factory()->create(['person_id' => $sharedActor->id, 'type_id' => $actorType->id,    'movie_id' => $movie3->id]);
        // This actor only worked with director 1 — should be excluded.
        Credit::factory()->create(['person_id' => $onlyD1Actor->id, 'type_id' => $actorType->id,    'movie_id' => $movie1->id]);

        $response = $this->get(route('director-connections', [
            'directors' => [$director1->id, $director2->id, $director3->id],
        ]));

        $response->assertSee('Robert De Niro');
        $response->assertDontSee('Jack Nicholson');
    }
}
