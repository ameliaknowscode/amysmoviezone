<?php

namespace Tests\Feature;

use App\Models\Credit;
use App\Models\Movie;
use App\Models\Person;
use App\Models\Type;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class PersonCreditImportControllerTest extends TestCase
{
    use RefreshDatabase;

    // -----------------------------------------------------------------------
    // Access
    // -----------------------------------------------------------------------

    public function test_import_page_requires_authentication(): void
    {
        $person = Person::factory()->create();

        $this->get(route('admin.people.credits.import', $person))
            ->assertRedirect('/login');
    }

    public function test_import_page_is_accessible_when_authenticated(): void
    {
        $user   = User::factory()->create();
        $person = Person::factory()->create(['name' => 'Cate Blanchett']);

        $this->actingAs($user)
            ->get(route('admin.people.credits.import', $person))
            ->assertOk()
            ->assertSee('Cate Blanchett');
    }

    // -----------------------------------------------------------------------
    // Valid imports
    // -----------------------------------------------------------------------

    public function test_valid_csv_imports_credits(): void
    {
        $user   = User::factory()->create();
        $person = Person::factory()->create();
        $movie  = Movie::factory()->create(['title' => 'The Matrix', 'release_year' => 1999]);
        Type::firstOrCreate(['name' => 'Actor'], ['is_crew' => false]);

        $csv  = "movie_title,release_year,type,character\nThe Matrix,1999,Actor,Neo\n";
        $file = UploadedFile::fake()->createWithContent('credits.csv', $csv);

        $response = $this->actingAs($user)
            ->post(route('admin.people.credits.import.store', $person), ['file' => $file]);

        $response->assertOk()->assertSee('1 credit imported successfully');

        $this->assertDatabaseHas('credits', [
            'person_id' => $person->id,
            'movie_id'  => $movie->id,
            'character' => 'Neo',
        ]);
    }

    public function test_missing_movie_is_created_automatically(): void
    {
        $user   = User::factory()->create();
        $person = Person::factory()->create();
        Type::firstOrCreate(['name' => 'Actor'], ['is_crew' => false]);

        $csv  = "movie_title,release_year,type,character\nBrand New Film,2024,Actor,\n";
        $file = UploadedFile::fake()->createWithContent('credits.csv', $csv);

        $this->actingAs($user)
            ->post(route('admin.people.credits.import.store', $person), ['file' => $file]);

        $this->assertDatabaseHas('movies', ['title' => 'Brand New Film', 'release_year' => 2024]);
        $this->assertSame(1, Credit::where('person_id', $person->id)->count());
    }

    public function test_import_replaces_all_existing_credits(): void
    {
        $user        = User::factory()->create();
        $person      = Person::factory()->create();
        $oldMovie    = Movie::factory()->create();
        $newMovie    = Movie::factory()->create(['title' => 'New Movie', 'release_year' => 2020]);
        $type        = Type::firstOrCreate(['name' => 'Actor'], ['is_crew' => false]);

        Credit::factory()->create([
            'person_id' => $person->id,
            'movie_id'  => $oldMovie->id,
            'type_id'   => $type->id,
        ]);

        $csv  = "movie_title,release_year,type,character\nNew Movie,2020,Actor,\n";
        $file = UploadedFile::fake()->createWithContent('credits.csv', $csv);

        $this->actingAs($user)
            ->post(route('admin.people.credits.import.store', $person), ['file' => $file]);

        $this->assertDatabaseMissing('credits', ['person_id' => $person->id, 'movie_id' => $oldMovie->id]);
        $this->assertDatabaseHas('credits',    ['person_id' => $person->id, 'movie_id' => $newMovie->id]);
    }

    public function test_character_column_is_optional(): void
    {
        $user   = User::factory()->create();
        $person = Person::factory()->create();
        Movie::factory()->create(['title' => 'Alien', 'release_year' => 1979]);
        Type::firstOrCreate(['name' => 'Actor'], ['is_crew' => false]);

        // CSV without character column
        $csv  = "movie_title,release_year,type\nAlien,1979,Actor\n";
        $file = UploadedFile::fake()->createWithContent('credits.csv', $csv);

        $this->actingAs($user)
            ->post(route('admin.people.credits.import.store', $person), ['file' => $file])
            ->assertOk()
            ->assertSee('1 credit imported successfully');
    }

    // -----------------------------------------------------------------------
    // Row-level errors
    // -----------------------------------------------------------------------

    public function test_row_with_unknown_type_is_created_automatically(): void
    {
        $user   = User::factory()->create();
        $person = Person::factory()->create();

        $csv  = "movie_title,release_year,type,character\nSome Film,2000,NewType,\n";
        $file = UploadedFile::fake()->createWithContent('credits.csv', $csv);

        $this->actingAs($user)
            ->post(route('admin.people.credits.import.store', $person), ['file' => $file])
            ->assertOk()->assertSee('1 credit imported successfully');

        $this->assertDatabaseHas('types', ['name' => 'NewType']);
        $this->assertSame(1, Credit::where('person_id', $person->id)->count());
    }

    public function test_row_with_empty_movie_title_is_skipped(): void
    {
        $user   = User::factory()->create();
        $person = Person::factory()->create();
        Type::firstOrCreate(['name' => 'Actor'], ['is_crew' => false]);

        $csv  = "movie_title,release_year,type,character\n,2000,Actor,\n";
        $file = UploadedFile::fake()->createWithContent('credits.csv', $csv);

        $response = $this->actingAs($user)
            ->post(route('admin.people.credits.import.store', $person), ['file' => $file]);

        $response->assertOk()->assertSee('Movie title is required');
        $this->assertSame(0, Credit::where('person_id', $person->id)->count());
    }

    public function test_row_with_invalid_year_is_skipped(): void
    {
        $user   = User::factory()->create();
        $person = Person::factory()->create();
        Type::firstOrCreate(['name' => 'Actor'], ['is_crew' => false]);

        $csv  = "movie_title,release_year,type,character\nSome Film,not-a-year,Actor,\n";
        $file = UploadedFile::fake()->createWithContent('credits.csv', $csv);

        $response = $this->actingAs($user)
            ->post(route('admin.people.credits.import.store', $person), ['file' => $file]);

        $response->assertOk()->assertSee('Release year must be a number');
        $this->assertSame(0, Credit::where('person_id', $person->id)->count());
    }

    public function test_existing_credits_are_not_wiped_when_all_rows_are_invalid(): void
    {
        $user   = User::factory()->create();
        $person = Person::factory()->create();
        $movie  = Movie::factory()->create();
        $type   = Type::firstOrCreate(['name' => 'Actor'], ['is_crew' => false]);

        Credit::factory()->create([
            'person_id' => $person->id,
            'movie_id'  => $movie->id,
            'type_id'   => $type->id,
        ]);

        // All rows invalid — existing credits should be preserved
        $csv  = "movie_title,release_year,type,character\n,2000,Actor,\n";
        $file = UploadedFile::fake()->createWithContent('credits.csv', $csv);

        $this->actingAs($user)
            ->post(route('admin.people.credits.import.store', $person), ['file' => $file]);

        $this->assertSame(1, Credit::where('person_id', $person->id)->count());
    }

    // -----------------------------------------------------------------------
    // Pipe-separated multi-credit rows
    // -----------------------------------------------------------------------

    public function test_actor_type_receives_character_non_actor_does_not(): void
    {
        $user   = User::factory()->create();
        $person = Person::factory()->create();
        Movie::factory()->create(['title' => 'The Matrix', 'release_year' => 1999]);
        Type::firstOrCreate(['name' => 'Actor'],    ['is_crew' => false]);
        Type::firstOrCreate(['name' => 'Director'], ['is_crew' => true]);

        $csv  = "movie_title,release_year,type,character\nThe Matrix,1999,Actor|Director,Neo\n";
        $file = UploadedFile::fake()->createWithContent('credits.csv', $csv);

        $this->actingAs($user)
            ->post(route('admin.people.credits.import.store', $person), ['file' => $file])
            ->assertOk()->assertSee('2 credits imported successfully');

        $this->assertDatabaseHas('credits', ['person_id' => $person->id, 'character' => 'Neo']);
        $this->assertDatabaseHas('credits', ['person_id' => $person->id, 'character' => null]);
    }

    public function test_multiple_rows_each_with_actor_and_director_credits(): void
    {
        $user   = User::factory()->create();
        $person = Person::factory()->create();
        Movie::factory()->create(['title' => 'Adaptation',  'release_year' => 2002]);
        Movie::factory()->create(['title' => 'Being John Malkovich', 'release_year' => 1999]);
        Type::firstOrCreate(['name' => 'Actor'],    ['is_crew' => false]);
        Type::firstOrCreate(['name' => 'Director'], ['is_crew' => true]);

        // Each row: one Actor credit (with character) + one Director credit (no character)
        $csv  = "movie_title,release_year,type,character\n"
              . "Adaptation,2002,Actor|Director,Charlie\n"
              . "Being John Malkovich,1999,Actor|Director,Craig\n";
        $file = UploadedFile::fake()->createWithContent('credits.csv', $csv);

        $this->actingAs($user)
            ->post(route('admin.people.credits.import.store', $person), ['file' => $file])
            ->assertOk()->assertSee('4 credits imported successfully');

        $this->assertDatabaseHas('credits', ['person_id' => $person->id, 'character' => 'Charlie']);
        $this->assertDatabaseHas('credits', ['person_id' => $person->id, 'character' => 'Craig']);
        $this->assertSame(2, Credit::where('person_id', $person->id)->whereNull('character')->count());
    }

    public function test_character_value_ignored_when_type_is_not_actor(): void
    {
        $user   = User::factory()->create();
        $person = Person::factory()->create();
        Movie::factory()->create(['title' => 'Some Film', 'release_year' => 2000]);
        Type::firstOrCreate(['name' => 'Director'], ['is_crew' => true]);

        // Character provided but no Actor type — should be stored as null
        $csv  = "movie_title,release_year,type,character\nSome Film,2000,Director,Some Character\n";
        $file = UploadedFile::fake()->createWithContent('credits.csv', $csv);

        $this->actingAs($user)
            ->post(route('admin.people.credits.import.store', $person), ['file' => $file])
            ->assertOk()->assertSee('1 credit imported successfully');

        $this->assertDatabaseHas('credits', ['person_id' => $person->id, 'character' => null]);
    }

    public function test_pipe_separated_row_creates_unknown_type_automatically(): void
    {
        $user   = User::factory()->create();
        $person = Person::factory()->create();
        Movie::factory()->create(['title' => 'The Matrix', 'release_year' => 1999]);
        Type::firstOrCreate(['name' => 'Actor'], ['is_crew' => false]);

        $csv  = "movie_title,release_year,type,character\nThe Matrix,1999,Actor|NewType,Neo\n";
        $file = UploadedFile::fake()->createWithContent('credits.csv', $csv);

        $this->actingAs($user)
            ->post(route('admin.people.credits.import.store', $person), ['file' => $file])
            ->assertOk()->assertSee('2 credits imported successfully');

        $this->assertDatabaseHas('types', ['name' => 'NewType']);
        $this->assertSame(2, Credit::where('person_id', $person->id)->count());
    }

    // -----------------------------------------------------------------------
    // File-level validation
    // -----------------------------------------------------------------------

    public function test_missing_file_is_rejected(): void
    {
        $user   = User::factory()->create();
        $person = Person::factory()->create();

        $this->actingAs($user)
            ->post(route('admin.people.credits.import.store', $person), [])
            ->assertSessionHasErrors('file');
    }

    public function test_non_csv_file_is_rejected(): void
    {
        $user   = User::factory()->create();
        $person = Person::factory()->create();

        $file = UploadedFile::fake()->create('credits.pdf', 100, 'application/pdf');

        $this->actingAs($user)
            ->post(route('admin.people.credits.import.store', $person), ['file' => $file])
            ->assertSessionHasErrors('file');
    }

    public function test_csv_missing_required_columns_is_rejected(): void
    {
        $user   = User::factory()->create();
        $person = Person::factory()->create();

        $csv  = "title,year\nThe Matrix,1999\n";
        $file = UploadedFile::fake()->createWithContent('credits.csv', $csv);

        $this->actingAs($user)
            ->post(route('admin.people.credits.import.store', $person), ['file' => $file])
            ->assertSessionHasErrors('file');
    }
}
