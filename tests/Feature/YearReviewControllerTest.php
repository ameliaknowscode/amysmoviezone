<?php

namespace Tests\Feature;

use App\Models\Credit;
use App\Models\Genre;
use App\Models\Movie;
use App\Models\Person;
use App\Models\Rating;
use App\Models\Review;
use App\Models\Type;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class YearReviewControllerTest extends TestCase
{
    use RefreshDatabase;

    private function makeMovie(array $attrs = []): Movie
    {
        return Movie::factory()->create($attrs);
    }

    /** Create a review (= watch) for a user. */
    private function watch(User $user, Movie $movie, string $watchedAt, array $extras = []): Review
    {
        return Review::factory()->create(array_merge([
            'user_id'    => $user->id,
            'movie_id'   => $movie->id,
            'watched_at' => $watchedAt,
        ], $extras));
    }

    private function directorType(): Type
    {
        return Type::firstOrCreate(['name' => 'Director'], ['slug' => 'director', 'is_crew' => true]);
    }

    private function attachDirector(Movie $movie, Person $person): void
    {
        Credit::factory()->create([
            'movie_id'  => $movie->id,
            'person_id' => $person->id,
            'type_id'   => $this->directorType()->id,
        ]);
    }

    // -------------------------------------------------------------------------
    // Authentication
    // -------------------------------------------------------------------------

    public function test_unauthenticated_user_is_redirected_to_login(): void
    {
        $this->get(route('year-review.show', 2025))->assertRedirect(route('login'));
        $this->get(route('year-review.index'))->assertRedirect(route('login'));
    }

    // -------------------------------------------------------------------------
    // Index: redirect to latest year
    // -------------------------------------------------------------------------

    public function test_index_redirects_to_latest_year_with_reviews(): void
    {
        $user  = User::factory()->create();
        $movie = $this->makeMovie();

        $this->watch($user, $movie, '2024-06-01');
        $this->watch($user, $this->makeMovie(), '2026-03-01');

        $this->actingAs($user)
            ->get(route('year-review.index'))
            ->assertRedirect(route('year-review.show', 2026));
    }

    public function test_index_redirects_to_current_year_when_user_has_no_reviews(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('year-review.index'))
            ->assertRedirect(route('year-review.show', now()->year));
    }

    // -------------------------------------------------------------------------
    // Empty state
    // -------------------------------------------------------------------------

    public function test_year_with_no_films_shows_empty_state(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('year-review.show', 2025))
            ->assertOk()
            ->assertSee('No films logged for 2025', false);
    }

    // -------------------------------------------------------------------------
    // Overview: totals
    // -------------------------------------------------------------------------

    public function test_total_watched_counts_only_the_target_year(): void
    {
        $user = User::factory()->create();

        $this->watch($user, $this->makeMovie(), '2025-04-10');
        $this->watch($user, $this->makeMovie(), '2025-09-21');
        $this->watch($user, $this->makeMovie(), '2024-12-31'); // out of year
        $this->watch($user, $this->makeMovie(), '2026-01-01'); // out of year

        $response = $this->actingAs($user)->get(route('year-review.show', 2025))->assertOk();

        $data = $response->viewData('totalWatched');
        $this->assertSame(2, $data);
    }

    public function test_total_minutes_sums_only_target_year_runtimes(): void
    {
        $user = User::factory()->create();

        $this->watch($user, $this->makeMovie(['runtime' => 90]),  '2025-01-15');
        $this->watch($user, $this->makeMovie(['runtime' => 120]), '2025-07-10');
        $this->watch($user, $this->makeMovie(['runtime' => 200]), '2024-06-01'); // out of year
        $this->watch($user, $this->makeMovie(['runtime' => null]), '2025-08-01'); // null runtime ignored

        $response = $this->actingAs($user)->get(route('year-review.show', 2025))->assertOk();

        $this->assertSame(210, (int) $response->viewData('totalMinutes'));
    }

    public function test_avg_rating_uses_only_target_year_films(): void
    {
        $user = User::factory()->create();

        $movieA = $this->makeMovie();
        $movieB = $this->makeMovie();
        $movieC = $this->makeMovie();

        $this->watch($user, $movieA, '2025-04-10');
        $this->watch($user, $movieB, '2025-08-08');
        $this->watch($user, $movieC, '2024-04-10'); // out of year

        Rating::factory()->create(['user_id' => $user->id, 'movie_id' => $movieA->id, 'stars' => 4]);
        Rating::factory()->create(['user_id' => $user->id, 'movie_id' => $movieB->id, 'stars' => 5]);
        Rating::factory()->create(['user_id' => $user->id, 'movie_id' => $movieC->id, 'stars' => 1]);

        $response = $this->actingAs($user)->get(route('year-review.show', 2025))->assertOk();

        // (4 + 5) / 2 = 4.5 — the 2024 film must be excluded
        $this->assertEquals(4.5, (float) $response->viewData('avgRating'));
    }

    public function test_total_rewatches_counts_is_rewatch_flag_within_year(): void
    {
        $user = User::factory()->create();

        $this->watch($user, $this->makeMovie(), '2025-01-10', ['is_rewatch' => true]);
        $this->watch($user, $this->makeMovie(), '2025-02-10', ['is_rewatch' => true]);
        $this->watch($user, $this->makeMovie(), '2025-03-10', ['is_rewatch' => false]);
        $this->watch($user, $this->makeMovie(), '2024-04-10', ['is_rewatch' => true]); // out of year

        $response = $this->actingAs($user)->get(route('year-review.show', 2025))->assertOk();

        $this->assertSame(2, $response->viewData('totalRewatches'));
    }

    // -------------------------------------------------------------------------
    // Highest-rated film
    // -------------------------------------------------------------------------

    public function test_highest_rated_picks_top_starred_film_in_year(): void
    {
        $user = User::factory()->create();

        $low  = $this->makeMovie(['title' => 'Low Rated']);
        $high = $this->makeMovie(['title' => 'High Rated']);

        $this->watch($user, $low,  '2025-04-10');
        $this->watch($user, $high, '2025-04-11');

        Rating::factory()->create(['user_id' => $user->id, 'movie_id' => $low->id,  'stars' => 2]);
        Rating::factory()->create(['user_id' => $user->id, 'movie_id' => $high->id, 'stars' => 5]);

        $response = $this->actingAs($user)->get(route('year-review.show', 2025))->assertOk();

        $highest = $response->viewData('highestRated');
        $this->assertSame('High Rated', $highest->title);
    }

    // -------------------------------------------------------------------------
    // Month-by-month
    // -------------------------------------------------------------------------

    public function test_by_month_buckets_watches_correctly(): void
    {
        $user = User::factory()->create();

        $this->watch($user, $this->makeMovie(), '2025-01-15'); // Jan
        $this->watch($user, $this->makeMovie(), '2025-01-20'); // Jan
        $this->watch($user, $this->makeMovie(), '2025-07-04'); // Jul

        $response = $this->actingAs($user)->get(route('year-review.show', 2025))->assertOk();

        $byMonth = $response->viewData('byMonth')->keyBy('month');
        $this->assertSame(2, $byMonth[1]->count);
        $this->assertSame(1, $byMonth[7]->count);
        $this->assertSame(0, $byMonth[3]->count);
    }

    // -------------------------------------------------------------------------
    // Top genres / directors / decade / rating distribution
    // -------------------------------------------------------------------------

    public function test_top_genres_only_count_target_year_films(): void
    {
        $user  = User::factory()->create();
        $genre = Genre::factory()->create(['name' => 'Western']);

        $thisYear = $this->makeMovie();
        $thisYear->genres()->attach($genre);
        $this->watch($user, $thisYear, '2025-04-01');

        $otherYear = $this->makeMovie();
        $otherYear->genres()->attach($genre);
        $this->watch($user, $otherYear, '2024-01-01');

        $response = $this->actingAs($user)->get(route('year-review.show', 2025))->assertOk();

        $byGenre = $response->viewData('byGenre');
        $this->assertCount(1, $byGenre);
        $this->assertSame('Western', $byGenre->first()->name);
        $this->assertSame(1, (int) $byGenre->first()->count);
    }

    public function test_by_decade_groups_by_release_year(): void
    {
        $user = User::factory()->create();

        $this->watch($user, $this->makeMovie(['release_year' => 1994]), '2025-01-01');
        $this->watch($user, $this->makeMovie(['release_year' => 1998]), '2025-02-01');
        $this->watch($user, $this->makeMovie(['release_year' => 2007]), '2025-03-01');

        $response = $this->actingAs($user)->get(route('year-review.show', 2025))->assertOk();

        $byDecade = $response->viewData('byDecade')->keyBy('decade');
        $this->assertSame(2, $byDecade[1990]->count);
        $this->assertSame(1, $byDecade[2000]->count);
    }

    public function test_rating_distribution_counts_distinct_films_per_star(): void
    {
        $user = User::factory()->create();

        $a = $this->makeMovie();
        $b = $this->makeMovie();
        $c = $this->makeMovie();

        $this->watch($user, $a, '2025-01-01');
        $this->watch($user, $b, '2025-02-01');
        $this->watch($user, $c, '2025-03-01');

        Rating::factory()->create(['user_id' => $user->id, 'movie_id' => $a->id, 'stars' => 4]);
        Rating::factory()->create(['user_id' => $user->id, 'movie_id' => $b->id, 'stars' => 4]);
        Rating::factory()->create(['user_id' => $user->id, 'movie_id' => $c->id, 'stars' => 5]);

        $response = $this->actingAs($user)->get(route('year-review.show', 2025))->assertOk();

        $dist = $response->viewData('ratingDist');
        $this->assertSame(2, (int) $dist['4']->count);
        $this->assertSame(1, (int) $dist['5']->count);
    }

    // -------------------------------------------------------------------------
    // Year picker
    // -------------------------------------------------------------------------

    public function test_available_years_are_distinct_and_sorted_ascending(): void
    {
        $user = User::factory()->create();

        $this->watch($user, $this->makeMovie(), '2024-01-01');
        $this->watch($user, $this->makeMovie(), '2024-12-31'); // dup year
        $this->watch($user, $this->makeMovie(), '2026-05-05');
        $this->watch($user, $this->makeMovie(), '2025-07-04');

        $response = $this->actingAs($user)->get(route('year-review.show', 2025))->assertOk();

        $this->assertSame([2024, 2025, 2026], $response->viewData('availableYears')->all());
    }

    // -------------------------------------------------------------------------
    // User isolation
    // -------------------------------------------------------------------------

    public function test_other_users_reviews_do_not_leak(): void
    {
        $alice = User::factory()->create();
        $bob   = User::factory()->create();

        $this->watch($alice, $this->makeMovie(), '2025-04-10');
        $this->watch($alice, $this->makeMovie(), '2025-04-11');

        $response = $this->actingAs($bob)->get(route('year-review.show', 2025))->assertOk();

        $this->assertSame(0, $response->viewData('totalWatched'));
    }

    // -------------------------------------------------------------------------
    // Directors Discovered
    // -------------------------------------------------------------------------

    public function test_directors_discovered_excludes_directors_seen_in_prior_year(): void
    {
        $user      = User::factory()->create();
        $known     = Person::factory()->create(['name' => 'Known Director']);
        $newcomer  = Person::factory()->create(['name' => 'New Director']);

        $oldFilm = $this->makeMovie();
        $this->attachDirector($oldFilm, $known);

        $thisYearByKnown = $this->makeMovie();
        $this->attachDirector($thisYearByKnown, $known);

        $thisYearByNewcomer = $this->makeMovie();
        $this->attachDirector($thisYearByNewcomer, $newcomer);

        $this->watch($user, $oldFilm, '2024-04-10');           // saw "Known" pre-2025
        $this->watch($user, $thisYearByKnown, '2025-02-10');   // re-encounter — not discovered
        $this->watch($user, $thisYearByNewcomer, '2025-03-10');

        $response = $this->actingAs($user)->get(route('year-review.show', 2025))->assertOk();

        $discovered = $response->viewData('directorsDiscovered');
        $this->assertCount(1, $discovered);
        $this->assertSame('New Director', $discovered->first()->name);
    }

    public function test_directors_discovered_lists_co_directors_separately(): void
    {
        $user = User::factory()->create();
        $coA  = Person::factory()->create(['name' => 'Co A']);
        $coB  = Person::factory()->create(['name' => 'Co B']);

        $movie = $this->makeMovie(['title' => 'Coen Together']);
        $this->attachDirector($movie, $coA);
        $this->attachDirector($movie, $coB);

        $this->watch($user, $movie, '2025-05-05');

        $response = $this->actingAs($user)->get(route('year-review.show', 2025))->assertOk();

        $discovered = $response->viewData('directorsDiscovered');
        $names = $discovered->pluck('name')->all();
        sort($names);
        $this->assertSame(['Co A', 'Co B'], $names);

        // Both should reference the same intro film
        foreach ($discovered as $d) {
            $this->assertSame('Coen Together', $d->first_film->title);
        }
    }

    public function test_directors_discovered_is_chronological_by_first_watched_at(): void
    {
        $user      = User::factory()->create();
        $earlyDir  = Person::factory()->create(['name' => 'Early Discovery']);
        $laterDir  = Person::factory()->create(['name' => 'Later Discovery']);

        $earlyFilm = $this->makeMovie();
        $laterFilm = $this->makeMovie();
        $this->attachDirector($earlyFilm, $earlyDir);
        $this->attachDirector($laterFilm, $laterDir);

        $this->watch($user, $laterFilm, '2025-09-01');
        $this->watch($user, $earlyFilm, '2025-02-01');

        $response = $this->actingAs($user)->get(route('year-review.show', 2025))->assertOk();

        $names = $response->viewData('directorsDiscovered')->pluck('name')->all();
        $this->assertSame(['Early Discovery', 'Later Discovery'], $names);
    }

    public function test_directors_discovered_attaches_first_watched_film_per_director(): void
    {
        $user = User::factory()->create();
        $dir  = Person::factory()->create(['name' => 'New To Me']);

        $intro  = $this->makeMovie(['title' => 'Intro Film']);
        $second = $this->makeMovie(['title' => 'Second Film']);
        $this->attachDirector($intro,  $dir);
        $this->attachDirector($second, $dir);

        // intro watched FIRST chronologically
        $this->watch($user, $second, '2025-05-15');
        $this->watch($user, $intro,  '2025-02-01');

        $response = $this->actingAs($user)->get(route('year-review.show', 2025))->assertOk();

        $discovered = $response->viewData('directorsDiscovered')->first();
        $this->assertSame('Intro Film', $discovered->first_film->title);
        $this->assertSame(2, (int) $discovered->count);
    }

    public function test_directors_discovered_count_uses_distinct_movies_not_review_rows(): void
    {
        // If a user rewatches a film in the same year, the join produces extra
        // rows. COUNT(DISTINCT movie_id) ensures the count reflects unique films
        // by that director, not raw review rows.
        $user = User::factory()->create();
        $dir  = Person::factory()->create(['name' => 'Watched Twice']);

        $movie = $this->makeMovie(['title' => 'Same Film']);
        $this->attachDirector($movie, $dir);

        $this->watch($user, $movie, '2025-02-10', ['is_rewatch' => false]);
        $this->watch($user, $movie, '2025-08-10', ['is_rewatch' => true]); // rewatch same year

        $response = $this->actingAs($user)->get(route('year-review.show', 2025))->assertOk();

        $this->assertSame(1, (int) $response->viewData('directorsDiscovered')->first()->count);
    }

    public function test_directors_discovered_card_hidden_when_empty(): void
    {
        $user = User::factory()->create();
        $dir  = Person::factory()->create(['name' => 'Familiar']);

        $oldFilm  = $this->makeMovie();
        $thisFilm = $this->makeMovie();
        $this->attachDirector($oldFilm,  $dir);
        $this->attachDirector($thisFilm, $dir);

        $this->watch($user, $oldFilm,  '2024-04-10');
        $this->watch($user, $thisFilm, '2025-04-10');

        $this->actingAs($user)
            ->get(route('year-review.show', 2025))
            ->assertOk()
            ->assertDontSee('Directors Discovered');
    }

    public function test_directors_discovered_card_renders_when_present(): void
    {
        $user = User::factory()->create();
        $dir  = Person::factory()->create(['name' => 'Brand New']);

        $movie = $this->makeMovie(['title' => 'Their First Film']);
        $this->attachDirector($movie, $dir);
        $this->watch($user, $movie, '2025-04-10');

        $this->actingAs($user)
            ->get(route('year-review.show', 2025))
            ->assertOk()
            ->assertSee('Directors Discovered')
            ->assertSee('Brand New')
            ->assertSee('Their First Film');
    }

    public function test_directors_discovered_isolates_per_user(): void
    {
        $alice = User::factory()->create();
        $bob   = User::factory()->create();
        $dir   = Person::factory()->create(['name' => 'Shared Director']);

        $oldFilm  = $this->makeMovie();
        $thisFilm = $this->makeMovie();
        $this->attachDirector($oldFilm,  $dir);
        $this->attachDirector($thisFilm, $dir);

        // Alice saw this director in 2024 → not discovered for her in 2025
        $this->watch($alice, $oldFilm,  '2024-04-10');
        $this->watch($alice, $thisFilm, '2025-04-10');

        // Bob has never seen this director before → discovered for him in 2025
        $this->watch($bob, $thisFilm, '2025-04-10');

        $aliceDiscovered = $this->actingAs($alice)
            ->get(route('year-review.show', 2025))->assertOk()
            ->viewData('directorsDiscovered');
        $bobDiscovered = $this->actingAs($bob)
            ->get(route('year-review.show', 2025))->assertOk()
            ->viewData('directorsDiscovered');

        $this->assertCount(0, $aliceDiscovered);
        $this->assertCount(1, $bobDiscovered);
    }
}
