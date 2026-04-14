<?php

namespace Tests\Feature;

use App\Models\Credit;
use App\Models\Genre;
use App\Models\Movie;
use App\Models\Person;
use App\Models\Rating;
use App\Models\Type;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class RecommendationsControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        // SQLite in-memory resets auto-increment IDs between tests, so two
        // tests can create users with the same ID and collide on the cache key.
        Cache::flush();
    }

    // -------------------------------------------------------------------------
    // Authentication guard
    // -------------------------------------------------------------------------

    public function test_unauthenticated_user_cannot_view_recommendations(): void
    {
        $this->get(route('recommendations'))
            ->assertRedirect(route('login'));
    }

    // -------------------------------------------------------------------------
    // Too few ratings
    // -------------------------------------------------------------------------

    public function test_user_with_no_ratings_sees_too_few_message(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('recommendations'))
            ->assertOk()
            ->assertSee('3'); // MIN_RATINGS_NEEDED shown in view
    }

    public function test_user_with_fewer_than_three_ratings_sees_too_few_message(): void
    {
        $user   = User::factory()->create();
        $movies = Movie::factory()->count(2)->create();

        foreach ($movies as $movie) {
            Rating::factory()->create(['user_id' => $user->id, 'movie_id' => $movie->id, 'stars' => 4]);
        }

        $this->actingAs($user)
            ->get(route('recommendations'))
            ->assertOk()
            ->assertSee('Rate at least');
    }

    // -------------------------------------------------------------------------
    // Collaborative bucket
    // -------------------------------------------------------------------------

    public function test_collaborative_bucket_excludes_already_rated_movies(): void
    {
        [$alice, $bob]   = User::factory()->count(2)->create();
        $alreadyRated    = Movie::factory()->count(3)->create();
        $notYetRated     = Movie::factory()->create(['title' => 'Hidden Gem']);

        foreach ($alreadyRated as $movie) {
            Rating::factory()->create(['user_id' => $alice->id, 'movie_id' => $movie->id, 'stars' => 4]);
        }

        Rating::factory()->create(['user_id' => $bob->id, 'movie_id' => $alreadyRated[0]->id, 'stars' => 5]);
        Rating::factory()->create(['user_id' => $bob->id, 'movie_id' => $alreadyRated[1]->id, 'stars' => 5]);
        Rating::factory()->create(['user_id' => $bob->id, 'movie_id' => $notYetRated->id,     'stars' => 5]);

        $response = $this->actingAs($alice)->get(route('recommendations'));

        $response->assertSee('Hidden Gem');

        foreach ($alreadyRated as $movie) {
            $response->assertDontSee($movie->title);
        }
    }

    public function test_collaborative_bucket_requires_minimum_overlap(): void
    {
        [$alice, $bob, $charlie] = User::factory()->count(3)->create();
        $sharedMovies            = Movie::factory()->count(3)->create();
        $bobExtra                = Movie::factory()->create(['title' => 'Bob Recommends']);
        $charlieExtra            = Movie::factory()->create(['title' => 'Charlie Unrelated']);

        foreach ($sharedMovies as $movie) {
            Rating::factory()->create(['user_id' => $alice->id, 'movie_id' => $movie->id, 'stars' => 4]);
        }

        // Bob shares 2 movies with Alice (qualifies)
        Rating::factory()->create(['user_id' => $bob->id, 'movie_id' => $sharedMovies[0]->id, 'stars' => 5]);
        Rating::factory()->create(['user_id' => $bob->id, 'movie_id' => $sharedMovies[1]->id, 'stars' => 5]);
        Rating::factory()->create(['user_id' => $bob->id, 'movie_id' => $bobExtra->id,         'stars' => 5]);

        // Charlie shares only 1 movie (below MIN_OVERLAP of 2 — should be ignored)
        Rating::factory()->create(['user_id' => $charlie->id, 'movie_id' => $sharedMovies[0]->id, 'stars' => 5]);
        Rating::factory()->create(['user_id' => $charlie->id, 'movie_id' => $charlieExtra->id,     'stars' => 5]);

        $response = $this->actingAs($alice)->get(route('recommendations'));

        $response->assertSee('Bob Recommends');
        $response->assertDontSee('Charlie Unrelated');
    }

    public function test_user_with_no_similar_users_sees_no_collaborative_section(): void
    {
        $alice  = User::factory()->create();
        $bob    = User::factory()->create();
        $movies = Movie::factory()->count(3)->create();

        foreach ($movies as $movie) {
            Rating::factory()->create(['user_id' => $alice->id, 'movie_id' => $movie->id, 'stars' => 3]);
        }

        $differentMovies = Movie::factory()->count(3)->create();
        foreach ($differentMovies as $movie) {
            Rating::factory()->create(['user_id' => $bob->id, 'movie_id' => $movie->id, 'stars' => 5]);
        }

        $this->actingAs($alice)
            ->get(route('recommendations'))
            ->assertOk()
            ->assertDontSee('Picked for you');
    }

    public function test_recommendations_page_loads_for_user_with_enough_ratings(): void
    {
        $alice  = User::factory()->create();
        $movies = Movie::factory()->count(3)->create();

        foreach ($movies as $movie) {
            Rating::factory()->create(['user_id' => $alice->id, 'movie_id' => $movie->id, 'stars' => 4]);
        }

        $this->actingAs($alice)
            ->get(route('recommendations'))
            ->assertOk();
    }

    // -------------------------------------------------------------------------
    // Genre bucket
    // -------------------------------------------------------------------------

    public function test_genre_bucket_shows_unrated_movies_in_top_genre(): void
    {
        $alice = User::factory()->create();
        $drama = Genre::factory()->create(['name' => 'Drama']);

        // Alice has rated 2 drama films (meets MIN_FILMS for taste profile)
        $ratedDramas = Movie::factory()->count(2)->create();
        foreach ($ratedDramas as $movie) {
            $movie->genres()->attach($drama);
            Rating::factory()->create(['user_id' => $alice->id, 'movie_id' => $movie->id, 'stars' => 5]);
        }

        // One extra rating to meet MIN_RATINGS_NEEDED = 3
        $otherMovie = Movie::factory()->create();
        Rating::factory()->create(['user_id' => $alice->id, 'movie_id' => $otherMovie->id, 'stars' => 4]);

        // A drama film Alice hasn't rated, with 2 community ratings averaging 4.0
        $recommended = Movie::factory()->create(['title' => 'Great Drama Film']);
        $recommended->genres()->attach($drama);

        $others = User::factory()->count(2)->create();
        foreach ($others as $other) {
            Rating::factory()->create(['user_id' => $other->id, 'movie_id' => $recommended->id, 'stars' => 4]);
        }

        $response = $this->actingAs($alice)->get(route('recommendations'));

        $response->assertSee('Because you love');
        $response->assertSee('Drama');
        $response->assertSee('Great Drama Film');
    }

    public function test_genre_bucket_excludes_already_rated_movies(): void
    {
        $alice = User::factory()->create();
        $drama = Genre::factory()->create(['name' => 'Drama']);

        // Alice rates 2 dramas + 1 other (meets both thresholds)
        $ratedDramas = Movie::factory()->count(2)->create();
        foreach ($ratedDramas as $movie) {
            $movie->genres()->attach($drama);
            Rating::factory()->create(['user_id' => $alice->id, 'movie_id' => $movie->id, 'stars' => 5]);
        }
        $extra = Movie::factory()->create();
        Rating::factory()->create(['user_id' => $alice->id, 'movie_id' => $extra->id, 'stars' => 4]);

        // Community also rates Alice's already-watched drama films highly
        $others = User::factory()->count(2)->create();
        foreach ($ratedDramas as $movie) {
            foreach ($others as $other) {
                Rating::factory()->create(['user_id' => $other->id, 'movie_id' => $movie->id, 'stars' => 5]);
            }
        }

        $response = $this->actingAs($alice)->get(route('recommendations'));

        // Already-rated films must not appear in the genre bucket
        foreach ($ratedDramas as $movie) {
            $response->assertDontSee($movie->title);
        }
    }

    // -------------------------------------------------------------------------
    // Director bucket
    // -------------------------------------------------------------------------

    public function test_director_bucket_shows_unrated_films_by_favourite_director(): void
    {
        $alice     = User::factory()->create();
        $director  = Person::factory()->create(['name' => 'Jane Auteur']);
        $dirType   = Type::firstOrCreate(['name' => 'Director', 'is_crew' => true]);

        // Alice rates 2 films by Jane Auteur (meets MIN_FILMS for taste profile)
        $ratedFilms = Movie::factory()->count(2)->create();
        foreach ($ratedFilms as $movie) {
            Credit::factory()->create(['movie_id' => $movie->id, 'person_id' => $director->id, 'type_id' => $dirType->id]);
            Rating::factory()->create(['user_id' => $alice->id, 'movie_id' => $movie->id, 'stars' => 5]);
        }

        // One extra rating to meet MIN_RATINGS_NEEDED = 3
        $other = Movie::factory()->create();
        Rating::factory()->create(['user_id' => $alice->id, 'movie_id' => $other->id, 'stars' => 4]);

        // Jane Auteur has an unrated film
        $unreleased = Movie::factory()->create(['title' => 'Auteur Masterpiece']);
        Credit::factory()->create(['movie_id' => $unreleased->id, 'person_id' => $director->id, 'type_id' => $dirType->id]);

        $response = $this->actingAs($alice)->get(route('recommendations'));

        $response->assertSee('More from');
        $response->assertSee('Jane Auteur');
        $response->assertSee('Auteur Masterpiece');
    }

    public function test_director_bucket_excludes_already_rated_films(): void
    {
        $alice    = User::factory()->create();
        $director = Person::factory()->create(['name' => 'Jane Auteur']);
        $dirType  = Type::firstOrCreate(['name' => 'Director', 'is_crew' => true]);

        $ratedFilms = Movie::factory()->count(3)->create();
        foreach ($ratedFilms as $movie) {
            Credit::factory()->create(['movie_id' => $movie->id, 'person_id' => $director->id, 'type_id' => $dirType->id]);
            Rating::factory()->create(['user_id' => $alice->id, 'movie_id' => $movie->id, 'stars' => 5]);
        }

        $response = $this->actingAs($alice)->get(route('recommendations'));

        foreach ($ratedFilms as $movie) {
            $response->assertDontSee($movie->title);
        }
    }

    // -------------------------------------------------------------------------
    // Taste profile panel
    // -------------------------------------------------------------------------

    public function test_taste_profile_shows_top_genres(): void
    {
        $alice = User::factory()->create();
        $drama = Genre::factory()->create(['name' => 'Drama']);

        $movies = Movie::factory()->count(3)->create();
        foreach ($movies as $movie) {
            $movie->genres()->attach($drama);
            Rating::factory()->create(['user_id' => $alice->id, 'movie_id' => $movie->id, 'stars' => 5]);
        }

        $this->actingAs($alice)
            ->get(route('recommendations'))
            ->assertOk()
            ->assertSee('Your taste profile')
            ->assertSee('Drama');
    }

    public function test_taste_profile_shows_favourite_directors(): void
    {
        $alice    = User::factory()->create();
        $director = Person::factory()->create(['name' => 'Jane Auteur']);
        $dirType  = Type::firstOrCreate(['name' => 'Director', 'is_crew' => true]);

        $movies = Movie::factory()->count(3)->create();
        foreach ($movies as $movie) {
            Credit::factory()->create(['movie_id' => $movie->id, 'person_id' => $director->id, 'type_id' => $dirType->id]);
            Rating::factory()->create(['user_id' => $alice->id, 'movie_id' => $movie->id, 'stars' => 5]);
        }

        $this->actingAs($alice)
            ->get(route('recommendations'))
            ->assertOk()
            ->assertSee('Your taste profile')
            ->assertSee('Jane Auteur');
    }
}
