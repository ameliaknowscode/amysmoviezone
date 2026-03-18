<?php

namespace Tests\Unit;

use App\Models\Actor;
use App\Models\Credit;
use App\Models\Movie;
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

        $this->assertEquals(['title', 'slug', 'director', 'release_year'], $movie->getFillable());
    }

    public function test_movie_stores_all_fields_correctly(): void
    {
        Movie::factory()->create([
            'title'        => 'Inception',
            'director'     => 'Christopher Nolan',
            'release_year' => 2010,
        ]);

        $this->assertDatabaseHas('movies', [
            'title'        => 'Inception',
            'director'     => 'Christopher Nolan',
            'release_year' => 2010,
        ]);
    }

    public function test_movie_release_year_is_an_integer(): void
    {
        $movie = Movie::factory()->create(['release_year' => 2005]);

        $this->assertIsInt($movie->release_year);
    }

    public function test_movie_belongs_to_many_actors(): void
    {
        $movie  = Movie::factory()->create();
        $actor1 = Actor::factory()->create();
        $actor2 = Actor::factory()->create();
        $movie->actors()->attach([$actor1->id, $actor2->id]);

        $this->assertCount(2, $movie->actors);
        $this->assertInstanceOf(Actor::class, $movie->actors->first());
    }

    public function test_movie_has_many_credits(): void
    {
        $movie = Movie::factory()->create();
        Credit::factory()->count(3)->create(['movie_id' => $movie->id]);

        $movie->refresh();
        $this->assertCount(3, $movie->credits);
        $this->assertInstanceOf(Credit::class, $movie->credits->first());
    }
}
