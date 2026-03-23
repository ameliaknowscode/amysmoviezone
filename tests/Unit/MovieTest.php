<?php

namespace Tests\Unit;

use App\Models\Credit;
use App\Models\Movie;
use App\Models\Type;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MovieTest extends TestCase
{
    use RefreshDatabase;

    public function test_movie_can_be_created_with_factory(): void
    {
        $movie = Movie::factory()->create();

        $this->assertInstanceOf(Movie::class, $movie);
        $this->assertNotNull($movie->id);
    }

    public function test_movie_has_correct_fillable_fields(): void
    {
        $movie = new Movie();

        $this->assertEquals(['title', 'slug', 'release_year', 'poster'], $movie->getFillable());
    }

    public function test_movie_stores_all_fields_correctly(): void
    {
        Movie::factory()->create([
            'title'        => 'Inception',
            'release_year' => 2010,
        ]);

        $this->assertDatabaseHas('movies', [
            'title'        => 'Inception',
            'release_year' => 2010,
        ]);
    }

    public function test_movie_release_year_is_an_integer(): void
    {
        $movie = Movie::factory()->create(['release_year' => 2005]);

        $this->assertIsInt($movie->release_year);
    }

    public function test_movie_has_many_credits(): void
    {
        $movie = Movie::factory()->create();
        Credit::factory()->count(3)->create(['movie_id' => $movie->id]);

        $movie->refresh();
        $this->assertCount(3, $movie->credits);
        $this->assertInstanceOf(Credit::class, $movie->credits->first());
    }

    // -------------------------------------------------------------------------
    // Helper methods
    // -------------------------------------------------------------------------

    public function test_poster_url_returns_null_when_no_poster(): void
    {
        $movie = Movie::factory()->create(['poster' => null]);

        $this->assertNull($movie->posterUrl());
    }

    public function test_public_url_returns_route_using_slug(): void
    {
        $movie = Movie::factory()->create(['title' => 'The Matrix']);

        $this->assertSame(route('movies.public', $movie->slug), $movie->publicUrl());
    }

    public function test_get_cast_returns_only_non_crew_credits(): void
    {
        $movie     = Movie::factory()->create();
        $actorType = Type::factory()->create(['is_crew' => false]);
        $crewType  = Type::factory()->create(['is_crew' => true]);

        $castCredit = Credit::factory()->create(['movie_id' => $movie->id, 'type_id' => $actorType->id]);
        Credit::factory()->create(['movie_id' => $movie->id, 'type_id' => $crewType->id]);

        $movie->load(['credits.type', 'credits.person']);
        $cast = $movie->getCast();

        $this->assertCount(1, $cast);
        $this->assertTrue($cast->first()->is($castCredit));
    }

    public function test_get_crew_returns_crew_credits_grouped_by_type_name(): void
    {
        $movie        = Movie::factory()->create();
        $directorType = Type::factory()->create(['name' => 'Director', 'slug' => 'director', 'is_crew' => true]);
        $actorType    = Type::factory()->create(['is_crew' => false]);

        Credit::factory()->create(['movie_id' => $movie->id, 'type_id' => $directorType->id]);
        Credit::factory()->create(['movie_id' => $movie->id, 'type_id' => $actorType->id]);

        $movie->load(['credits.type', 'credits.person']);
        $crew = $movie->getCrew();

        $this->assertArrayHasKey('Director', $crew->toArray());
        $this->assertArrayNotHasKey($actorType->name, $crew->toArray());
    }

    public function test_get_cast_returns_empty_collection_when_no_cast(): void
    {
        $movie = Movie::factory()->create();
        $movie->load(['credits.type', 'credits.person']);

        $this->assertCount(0, $movie->getCast());
    }

    public function test_get_crew_returns_empty_collection_when_no_crew(): void
    {
        $movie = Movie::factory()->create();
        $movie->load(['credits.type', 'credits.person']);

        $this->assertCount(0, $movie->getCrew());
    }
}
