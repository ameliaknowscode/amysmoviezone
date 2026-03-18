<?php

namespace Tests\Feature;

use App\Models\Movie;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class MovieImportControllerTest extends TestCase
{
    use RefreshDatabase;

    // -----------------------------------------------------------------------
    // Access
    // -----------------------------------------------------------------------

    public function test_import_page_requires_authentication(): void
    {
        $this->get(route('admin.movies.import'))->assertRedirect('/login');
    }

    public function test_import_page_is_accessible_when_authenticated(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('admin.movies.import'))
            ->assertOk()
            ->assertSee('Import Movies from CSV');
    }

    // -----------------------------------------------------------------------
    // Valid imports
    // -----------------------------------------------------------------------

    public function test_valid_csv_imports_movies(): void
    {
        $user = User::factory()->create();

        $csv = "title,release_year\nThe Matrix,1999\nInception,2010\n";
        $file = UploadedFile::fake()->createWithContent('movies.csv', $csv);

        $response = $this->actingAs($user)
            ->post(route('admin.movies.import.store'), ['file' => $file]);

        $response->assertOk()->assertSee('2 movies imported successfully');

        $this->assertDatabaseHas('movies', ['title' => 'The Matrix', 'release_year' => 1999]);
        $this->assertDatabaseHas('movies', ['title' => 'Inception',   'release_year' => 2010]);
    }

    public function test_csv_columns_can_be_in_any_order(): void
    {
        $user = User::factory()->create();

        $csv  = "release_year,title\n2001,Mulholland Drive\n";
        $file = UploadedFile::fake()->createWithContent('movies.csv', $csv);

        $this->actingAs($user)
            ->post(route('admin.movies.import.store'), ['file' => $file])
            ->assertOk()
            ->assertSee('1 movie imported successfully');

        $this->assertDatabaseHas('movies', ['title' => 'Mulholland Drive', 'release_year' => 2001]);
    }

    public function test_slug_is_generated_for_imported_movies(): void
    {
        $user = User::factory()->create();

        $csv  = "title,release_year\nBlade Runner,1982\n";
        $file = UploadedFile::fake()->createWithContent('movies.csv', $csv);

        $this->actingAs($user)
            ->post(route('admin.movies.import.store'), ['file' => $file]);

        $this->assertDatabaseHas('movies', ['title' => 'Blade Runner', 'slug' => 'blade-runner']);
    }

    // -----------------------------------------------------------------------
    // Duplicate handling — skip mode (default)
    // -----------------------------------------------------------------------

    public function test_duplicate_movie_is_skipped_and_reported(): void
    {
        $user = User::factory()->create();
        Movie::factory()->create(['title' => 'The Matrix', 'release_year' => 1999]);

        $csv  = "title,release_year\nThe Matrix,1999\n";
        $file = UploadedFile::fake()->createWithContent('movies.csv', $csv);

        $response = $this->actingAs($user)
            ->post(route('admin.movies.import.store'), ['file' => $file]);

        $response->assertOk()->assertSee('already exists');
        $this->assertSame(1, Movie::where('title', 'The Matrix')->count());
    }

    public function test_duplicate_check_is_case_insensitive(): void
    {
        $user = User::factory()->create();
        Movie::factory()->create(['title' => 'the matrix', 'release_year' => 1999]);

        $csv  = "title,release_year\nThe Matrix,1999\n";
        $file = UploadedFile::fake()->createWithContent('movies.csv', $csv);

        $this->actingAs($user)
            ->post(route('admin.movies.import.store'), ['file' => $file]);

        $this->assertSame(1, Movie::whereRaw('LOWER(title) = ?', ['the matrix'])->count());
    }

    // -----------------------------------------------------------------------
    // Duplicate handling — update mode
    // -----------------------------------------------------------------------

    public function test_update_existing_updates_title_and_slug(): void
    {
        $user = User::factory()->create();
        Movie::factory()->create(['title' => 'the matrix', 'slug' => 'the-matrix', 'release_year' => 1999]);

        $csv  = "title,release_year\nThe Matrix,1999\n";
        $file = UploadedFile::fake()->createWithContent('movies.csv', $csv);

        $response = $this->actingAs($user)
            ->post(route('admin.movies.import.store'), ['file' => $file, 'update_existing' => '1']);

        $response->assertOk()->assertSee('1 movie updated successfully');

        $this->assertDatabaseHas('movies', ['title' => 'The Matrix', 'slug' => 'the-matrix', 'release_year' => 1999]);
        $this->assertSame(1, Movie::count());
    }

    public function test_update_existing_does_not_create_duplicate(): void
    {
        $user = User::factory()->create();
        Movie::factory()->create(['title' => 'Inception', 'release_year' => 2010]);

        $csv  = "title,release_year\nInception,2010\n";
        $file = UploadedFile::fake()->createWithContent('movies.csv', $csv);

        $this->actingAs($user)
            ->post(route('admin.movies.import.store'), ['file' => $file, 'update_existing' => '1']);

        $this->assertSame(1, Movie::where('title', 'Inception')->count());
    }

    public function test_without_update_flag_duplicate_is_still_reported(): void
    {
        $user = User::factory()->create();
        Movie::factory()->create(['title' => 'Inception', 'release_year' => 2010]);

        $csv  = "title,release_year\nInception,2010\n";
        $file = UploadedFile::fake()->createWithContent('movies.csv', $csv);

        $response = $this->actingAs($user)
            ->post(route('admin.movies.import.store'), ['file' => $file]);

        $response->assertOk()->assertSee('already exists');
    }

    // -----------------------------------------------------------------------
    // Row-level validation errors
    // -----------------------------------------------------------------------

    public function test_row_with_empty_title_is_skipped(): void
    {
        $user = User::factory()->create();

        $csv  = "title,release_year\n,2001\n";
        $file = UploadedFile::fake()->createWithContent('movies.csv', $csv);

        $response = $this->actingAs($user)
            ->post(route('admin.movies.import.store'), ['file' => $file]);

        $response->assertOk()->assertSee('Title is required');
        $this->assertSame(0, Movie::count());
    }

    public function test_row_with_invalid_year_is_skipped(): void
    {
        $user = User::factory()->create();

        $csv  = "title,release_year\nSome Film,not-a-year\n";
        $file = UploadedFile::fake()->createWithContent('movies.csv', $csv);

        $response = $this->actingAs($user)
            ->post(route('admin.movies.import.store'), ['file' => $file]);

        $response->assertOk()->assertSee('Release year must be a number');
        $this->assertSame(0, Movie::count());
    }

    public function test_row_with_year_too_early_is_skipped(): void
    {
        $user = User::factory()->create();

        $csv  = "title,release_year\nAncient Film,1800\n";
        $file = UploadedFile::fake()->createWithContent('movies.csv', $csv);

        $this->actingAs($user)
            ->post(route('admin.movies.import.store'), ['file' => $file])
            ->assertOk()
            ->assertSee('Release year must be a number');

        $this->assertSame(0, Movie::count());
    }

    public function test_valid_and_invalid_rows_are_handled_independently(): void
    {
        $user = User::factory()->create();

        $csv  = "title,release_year\nGood Movie,2000\n,1999\nAnother Good Movie,2005\n";
        $file = UploadedFile::fake()->createWithContent('movies.csv', $csv);

        $response = $this->actingAs($user)
            ->post(route('admin.movies.import.store'), ['file' => $file]);

        $response->assertOk()->assertSee('2 movies imported successfully');
        $this->assertDatabaseHas('movies', ['title' => 'Good Movie']);
        $this->assertDatabaseHas('movies', ['title' => 'Another Good Movie']);
        $this->assertSame(2, Movie::count());
    }

    // -----------------------------------------------------------------------
    // File-level validation errors
    // -----------------------------------------------------------------------

    public function test_missing_file_is_rejected(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post(route('admin.movies.import.store'), [])
            ->assertSessionHasErrors('file');
    }

    public function test_non_csv_file_is_rejected(): void
    {
        $user = User::factory()->create();

        $file = UploadedFile::fake()->create('movies.pdf', 100, 'application/pdf');

        $this->actingAs($user)
            ->post(route('admin.movies.import.store'), ['file' => $file])
            ->assertSessionHasErrors('file');
    }

    public function test_csv_missing_required_columns_is_rejected(): void
    {
        $user = User::factory()->create();

        $csv  = "name,year\nThe Matrix,1999\n";
        $file = UploadedFile::fake()->createWithContent('movies.csv', $csv);

        $this->actingAs($user)
            ->post(route('admin.movies.import.store'), ['file' => $file])
            ->assertSessionHasErrors('file');
    }

    public function test_empty_csv_reports_no_imports(): void
    {
        $user = User::factory()->create();

        $csv  = "title,release_year\n";
        $file = UploadedFile::fake()->createWithContent('movies.csv', $csv);

        $response = $this->actingAs($user)
            ->post(route('admin.movies.import.store'), ['file' => $file]);

        $response->assertOk()->assertSee('no data rows');
        $this->assertSame(0, Movie::count());
    }
}
