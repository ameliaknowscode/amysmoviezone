<?php

namespace Tests\Feature;

use App\Models\Genre;
use App\Models\Movie;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class GenreImportControllerTest extends TestCase
{
    use RefreshDatabase;

    // -----------------------------------------------------------------------
    // Access
    // -----------------------------------------------------------------------

    public function test_import_page_requires_authentication(): void
    {
        $this->get(route('admin.genres.import'))->assertRedirect('/login');
    }

    public function test_import_page_requires_admin(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('admin.genres.import'))
            ->assertForbidden();
    }

    public function test_import_page_is_accessible_to_admin(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)
            ->get(route('admin.genres.import'))
            ->assertOk()
            ->assertSee('Import Movie Genres from CSV');
    }

    // -----------------------------------------------------------------------
    // Valid imports
    // -----------------------------------------------------------------------

    public function test_valid_csv_assigns_genres_to_movies(): void
    {
        $admin = User::factory()->admin()->create();
        $movie = Movie::factory()->create(['title' => 'The Matrix', 'release_year' => 1999]);
        Genre::factory()->create(['name' => 'Action', 'slug' => 'action']);
        Genre::factory()->create(['name' => 'Science Fiction', 'slug' => 'science-fiction']);

        $csv  = "title,release_year,genres\nThe Matrix,1999,Action|Science Fiction\n";
        $file = UploadedFile::fake()->createWithContent('genres.csv', $csv);

        $this->actingAs($admin)
            ->post(route('admin.genres.import.store'), ['file' => $file])
            ->assertOk()
            ->assertSee('Genres updated for 1 movie');

        $this->assertCount(2, $movie->fresh()->genres);
    }

    public function test_single_genre_can_be_assigned(): void
    {
        $admin = User::factory()->admin()->create();
        $movie = Movie::factory()->create(['title' => 'Parasite', 'release_year' => 2019]);
        Genre::factory()->create(['name' => 'Drama', 'slug' => 'drama']);

        $csv  = "title,release_year,genres\nParasite,2019,Drama\n";
        $file = UploadedFile::fake()->createWithContent('genres.csv', $csv);

        $this->actingAs($admin)
            ->post(route('admin.genres.import.store'), ['file' => $file])
            ->assertOk();

        $this->assertCount(1, $movie->fresh()->genres);
    }

    public function test_genre_matching_is_case_insensitive(): void
    {
        $admin = User::factory()->admin()->create();
        $movie = Movie::factory()->create(['title' => 'Alien', 'release_year' => 1979]);
        Genre::factory()->create(['name' => 'Horror', 'slug' => 'horror']);

        $csv  = "title,release_year,genres\nAlien,1979,horror\n";
        $file = UploadedFile::fake()->createWithContent('genres.csv', $csv);

        $this->actingAs($admin)
            ->post(route('admin.genres.import.store'), ['file' => $file]);

        $this->assertCount(1, $movie->fresh()->genres);
        $this->assertEquals('Horror', $movie->fresh()->genres->first()->name);
    }

    public function test_unrecognised_genre_is_created_automatically(): void
    {
        $admin = User::factory()->admin()->create();
        $movie = Movie::factory()->create(['title' => 'Dune', 'release_year' => 2021]);

        $csv  = "title,release_year,genres\nDune,2021,Epic\n";
        $file = UploadedFile::fake()->createWithContent('genres.csv', $csv);

        $this->actingAs($admin)
            ->post(route('admin.genres.import.store'), ['file' => $file]);

        $this->assertDatabaseHas('genres', ['name' => 'Epic']);
        $this->assertCount(1, $movie->fresh()->genres);
    }

    public function test_import_syncs_genres_replacing_existing(): void
    {
        $admin       = User::factory()->admin()->create();
        $movie       = Movie::factory()->create(['title' => 'Heat', 'release_year' => 1995]);
        $oldGenre    = Genre::factory()->create(['name' => 'Romance', 'slug' => 'romance']);
        $newGenre    = Genre::factory()->create(['name' => 'Crime', 'slug' => 'crime']);
        $movie->genres()->attach($oldGenre);

        $csv  = "title,release_year,genres\nHeat,1995,Crime\n";
        $file = UploadedFile::fake()->createWithContent('genres.csv', $csv);

        $this->actingAs($admin)
            ->post(route('admin.genres.import.store'), ['file' => $file]);

        $genres = $movie->fresh()->genres->pluck('name');
        $this->assertContains('Crime', $genres);
        $this->assertNotContains('Romance', $genres);
    }

    public function test_movie_title_matching_is_case_insensitive(): void
    {
        $admin = User::factory()->admin()->create();
        $movie = Movie::factory()->create(['title' => 'blade runner', 'release_year' => 1982]);
        Genre::factory()->create(['name' => 'Science Fiction', 'slug' => 'science-fiction']);

        $csv  = "title,release_year,genres\nBlade Runner,1982,Science Fiction\n";
        $file = UploadedFile::fake()->createWithContent('genres.csv', $csv);

        $this->actingAs($admin)
            ->post(route('admin.genres.import.store'), ['file' => $file]);

        $this->assertCount(1, $movie->fresh()->genres);
    }

    public function test_csv_columns_can_be_in_any_order(): void
    {
        $admin = User::factory()->admin()->create();
        $movie = Movie::factory()->create(['title' => 'Jaws', 'release_year' => 1975]);
        Genre::factory()->create(['name' => 'Thriller', 'slug' => 'thriller']);

        $csv  = "genres,title,release_year\nThriller,Jaws,1975\n";
        $file = UploadedFile::fake()->createWithContent('genres.csv', $csv);

        $this->actingAs($admin)
            ->post(route('admin.genres.import.store'), ['file' => $file]);

        $this->assertCount(1, $movie->fresh()->genres);
    }

    public function test_multiple_movies_updated_in_one_import(): void
    {
        $admin  = User::factory()->admin()->create();
        $movie1 = Movie::factory()->create(['title' => 'Up', 'release_year' => 2009]);
        $movie2 = Movie::factory()->create(['title' => 'Soul', 'release_year' => 2020]);

        $csv  = "title,release_year,genres\nUp,2009,Animation\nSoul,2020,Animation|Drama\n";
        $file = UploadedFile::fake()->createWithContent('genres.csv', $csv);

        $this->actingAs($admin)
            ->post(route('admin.genres.import.store'), ['file' => $file])
            ->assertOk()
            ->assertSee('Genres updated for 2 movies');

        $this->assertCount(1, $movie1->fresh()->genres);
        $this->assertCount(2, $movie2->fresh()->genres);
    }

    // -----------------------------------------------------------------------
    // Row-level errors
    // -----------------------------------------------------------------------

    public function test_movie_not_found_is_skipped_and_reported(): void
    {
        $admin = User::factory()->admin()->create();

        $csv  = "title,release_year,genres\nNonexistent Film,2000,Drama\n";
        $file = UploadedFile::fake()->createWithContent('genres.csv', $csv);

        $this->actingAs($admin)
            ->post(route('admin.genres.import.store'), ['file' => $file])
            ->assertOk()
            ->assertSee('Movie not found');
    }

    public function test_row_with_empty_title_is_skipped(): void
    {
        $admin = User::factory()->admin()->create();

        $csv  = "title,release_year,genres\n,1999,Drama\n";
        $file = UploadedFile::fake()->createWithContent('genres.csv', $csv);

        $this->actingAs($admin)
            ->post(route('admin.genres.import.store'), ['file' => $file])
            ->assertOk()
            ->assertSee('Title is required');
    }

    public function test_row_with_invalid_year_is_skipped(): void
    {
        $admin = User::factory()->admin()->create();

        $csv  = "title,release_year,genres\nSome Film,not-a-year,Drama\n";
        $file = UploadedFile::fake()->createWithContent('genres.csv', $csv);

        $this->actingAs($admin)
            ->post(route('admin.genres.import.store'), ['file' => $file])
            ->assertOk()
            ->assertSee('Release year must be a number');
    }

    public function test_row_with_empty_genres_is_skipped(): void
    {
        $admin = User::factory()->admin()->create();
        Movie::factory()->create(['title' => 'Arrival', 'release_year' => 2016]);

        $csv  = "title,release_year,genres\nArrival,2016,\n";
        $file = UploadedFile::fake()->createWithContent('genres.csv', $csv);

        $this->actingAs($admin)
            ->post(route('admin.genres.import.store'), ['file' => $file])
            ->assertOk()
            ->assertSee('Genres column is empty');
    }

    public function test_valid_and_invalid_rows_handled_independently(): void
    {
        $admin = User::factory()->admin()->create();
        $movie = Movie::factory()->create(['title' => 'Whiplash', 'release_year' => 2014]);

        $csv  = "title,release_year,genres\nWhiplash,2014,Drama\nGhost Film,1900,Horror\n";
        $file = UploadedFile::fake()->createWithContent('genres.csv', $csv);

        $this->actingAs($admin)
            ->post(route('admin.genres.import.store'), ['file' => $file])
            ->assertOk()
            ->assertSee('Genres updated for 1 movie')
            ->assertSee('Movie not found');

        $this->assertCount(1, $movie->fresh()->genres);
    }

    // -----------------------------------------------------------------------
    // File-level validation
    // -----------------------------------------------------------------------

    public function test_missing_file_is_rejected(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)
            ->post(route('admin.genres.import.store'), [])
            ->assertSessionHasErrors('file');
    }

    public function test_non_csv_file_is_rejected(): void
    {
        $admin = User::factory()->admin()->create();
        $file  = UploadedFile::fake()->create('genres.pdf', 100, 'application/pdf');

        $this->actingAs($admin)
            ->post(route('admin.genres.import.store'), ['file' => $file])
            ->assertSessionHasErrors('file');
    }

    public function test_csv_missing_required_columns_is_rejected(): void
    {
        $admin = User::factory()->admin()->create();

        $csv  = "title,release_year\nThe Matrix,1999\n";
        $file = UploadedFile::fake()->createWithContent('genres.csv', $csv);

        $this->actingAs($admin)
            ->post(route('admin.genres.import.store'), ['file' => $file])
            ->assertSessionHasErrors('file');
    }

    public function test_empty_csv_reports_no_updates(): void
    {
        $admin = User::factory()->admin()->create();

        $csv  = "title,release_year,genres\n";
        $file = UploadedFile::fake()->createWithContent('genres.csv', $csv);

        $this->actingAs($admin)
            ->post(route('admin.genres.import.store'), ['file' => $file])
            ->assertOk()
            ->assertSee('no data rows');
    }
}
