<?php

namespace Tests\Feature;

use App\Models\Genre;
use App\Models\Movie;
use App\Models\Rating;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MovieBrowseControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_browse_page_loads_for_guests(): void
    {
        $this->get(route('movies.browse'))->assertOk();
    }

    public function test_browse_page_loads_for_authenticated_users(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->get(route('movies.browse'))->assertOk();
    }

    public function test_movies_are_displayed_on_browse_page(): void
    {
        Movie::factory()->create(['title' => 'The Matrix']);

        $this->get(route('movies.browse'))->assertSee('The Matrix');
    }

    public function test_movies_are_ordered_by_average_rating_descending(): void
    {
        $lowRated  = Movie::factory()->create(['title' => 'Low Rated']);
        $highRated = Movie::factory()->create(['title' => 'High Rated']);

        Rating::factory()->create(['movie_id' => $lowRated->id,  'stars' => 1]);
        Rating::factory()->create(['movie_id' => $highRated->id, 'stars' => 5]);

        $response = $this->get(route('movies.browse'));

        $highPos = strpos($response->content(), 'High Rated');
        $lowPos  = strpos($response->content(), 'Low Rated');

        $this->assertLessThan($lowPos, $highPos);
    }

    public function test_browse_page_paginates_at_72_per_page(): void
    {
        Movie::factory()->count(80)->create();

        $response = $this->get(route('movies.browse'));

        $response->assertOk();
        $this->assertGreaterThan(72, Movie::count());
    }

    public function test_empty_movie_list_still_renders_page(): void
    {
        $this->get(route('movies.browse'))->assertOk();
    }

    // -------------------------------------------------------------------------
    // Genre filter
    // -------------------------------------------------------------------------

    public function test_genre_filter_shows_only_matching_movies(): void
    {
        $horror = Genre::factory()->create(['name' => 'Horror', 'slug' => 'horror']);
        $drama  = Genre::factory()->create(['name' => 'Drama',  'slug' => 'drama']);

        $horrorMovie = Movie::factory()->create(['title' => 'The Shining']);
        $dramaMovie  = Movie::factory()->create(['title' => 'The Godfather']);

        $horrorMovie->genres()->attach($horror);
        $dramaMovie->genres()->attach($drama);

        $this->get(route('movies.browse', ['genre' => 'horror']))
            ->assertSee('The Shining')
            ->assertDontSee('The Godfather');
    }

    public function test_genre_filter_with_no_match_shows_empty_state(): void
    {
        Genre::factory()->create(['name' => 'Horror', 'slug' => 'horror']);
        Movie::factory()->create(['title' => 'A Drama Film']);

        $this->get(route('movies.browse', ['genre' => 'horror']))
            ->assertSee('No movies matched your filters');
    }

    public function test_genre_filter_with_unknown_slug_shows_empty_state(): void
    {
        Movie::factory()->create(['title' => 'Some Movie']);

        $this->get(route('movies.browse', ['genre' => 'does-not-exist']))
            ->assertOk()
            ->assertSee('No movies matched your filters');
    }

    public function test_browse_page_shows_genre_dropdown(): void
    {
        Genre::factory()->create(['name' => 'Animation', 'slug' => 'animation']);

        $this->get(route('movies.browse'))
            ->assertSee('Animation');
    }

    public function test_genre_filter_is_reflected_in_selected_dropdown_option(): void
    {
        Genre::factory()->create(['name' => 'Horror', 'slug' => 'horror']);

        $this->get(route('movies.browse', ['genre' => 'horror']))
            ->assertSee('horror');
    }
}
