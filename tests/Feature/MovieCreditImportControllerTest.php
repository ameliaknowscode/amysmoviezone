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

class MovieCreditImportControllerTest extends TestCase
{
    use RefreshDatabase;

    // -----------------------------------------------------------------------
    // Access
    // -----------------------------------------------------------------------

    public function test_import_page_requires_authentication(): void
    {
        $movie = Movie::factory()->create();

        $this->get(route('admin.movies.credits.import', $movie))
            ->assertRedirect(route('login'));
    }

    public function test_import_page_is_accessible_when_authenticated(): void
    {
        $user  = User::factory()->admin()->create();
        $movie = Movie::factory()->create();

        $this->actingAs($user)
            ->get(route('admin.movies.credits.import', $movie))
            ->assertOk()
            ->assertSee($movie->title);
    }

    // -----------------------------------------------------------------------
    // Valid imports
    // -----------------------------------------------------------------------

    public function test_valid_csv_imports_credits(): void
    {
        $user   = User::factory()->admin()->create();
        $movie  = Movie::factory()->create();
        $person = Person::factory()->create(['name' => 'Keanu Reeves']);
        Type::firstOrCreate(['name' => 'Actor'], ['is_crew' => false]);

        $csv  = "person_name,type,character\nKeanu Reeves,Actor,Neo\n";
        $file = UploadedFile::fake()->createWithContent('credits.csv', $csv);

        $this->actingAs($user)
            ->post(route('admin.movies.credits.import.store', $movie), ['file' => $file])
            ->assertOk()
            ->assertSee('1 credit imported successfully');

        $this->assertDatabaseHas('credits', [
            'movie_id'  => $movie->id,
            'person_id' => $person->id,
            'character' => 'Neo',
        ]);
    }

    public function test_missing_person_is_created_automatically(): void
    {
        $user  = User::factory()->admin()->create();
        $movie = Movie::factory()->create();
        Type::firstOrCreate(['name' => 'Director'], ['is_crew' => true]);

        $csv  = "person_name,type,character\nChristopher Nolan,Director,\n";
        $file = UploadedFile::fake()->createWithContent('credits.csv', $csv);

        $this->actingAs($user)
            ->post(route('admin.movies.credits.import.store', $movie), ['file' => $file])
            ->assertOk()
            ->assertSee('1 credit imported successfully');

        $this->assertDatabaseHas('people', ['name' => 'Christopher Nolan']);
        $this->assertSame(1, Credit::where('movie_id', $movie->id)->count());
    }

    public function test_import_replaces_all_existing_credits(): void
    {
        $user      = User::factory()->admin()->create();
        $movie     = Movie::factory()->create();
        $oldPerson = Person::factory()->create();
        $newPerson = Person::factory()->create(['name' => 'New Person']);
        $type      = Type::firstOrCreate(['name' => 'Actor'], ['is_crew' => false]);

        Credit::create(['movie_id' => $movie->id, 'person_id' => $oldPerson->id, 'type_id' => $type->id]);

        $csv  = "person_name,type,character\nNew Person,Actor,Hero\n";
        $file = UploadedFile::fake()->createWithContent('credits.csv', $csv);

        $this->actingAs($user)
            ->post(route('admin.movies.credits.import.store', $movie), ['file' => $file])
            ->assertOk();

        $this->assertDatabaseMissing('credits', ['person_id' => $oldPerson->id]);
        $this->assertDatabaseHas('credits', ['movie_id' => $movie->id, 'person_id' => $newPerson->id]);
    }

    public function test_character_column_is_optional(): void
    {
        $user  = User::factory()->admin()->create();
        $movie = Movie::factory()->create();
        Person::factory()->create(['name' => 'Some Person']);
        Type::firstOrCreate(['name' => 'Actor'], ['is_crew' => false]);

        $csv  = "person_name,type\nSome Person,Actor\n";
        $file = UploadedFile::fake()->createWithContent('credits.csv', $csv);

        $this->actingAs($user)
            ->post(route('admin.movies.credits.import.store', $movie), ['file' => $file])
            ->assertOk()
            ->assertSee('1 credit imported successfully');

        $this->assertDatabaseHas('credits', ['movie_id' => $movie->id, 'character' => null]);
    }

    public function test_unknown_type_is_created_automatically(): void
    {
        $user  = User::factory()->admin()->create();
        $movie = Movie::factory()->create();

        $csv  = "person_name,type,character\nJane Smith,Choreographer,\n";
        $file = UploadedFile::fake()->createWithContent('credits.csv', $csv);

        $this->actingAs($user)
            ->post(route('admin.movies.credits.import.store', $movie), ['file' => $file])
            ->assertOk()
            ->assertSee('1 credit imported successfully');

        $this->assertDatabaseHas('types', ['name' => 'Choreographer']);
    }

    // -----------------------------------------------------------------------
    // Pipe-separated types / actor-only characters
    // -----------------------------------------------------------------------

    public function test_actor_type_receives_character_non_actor_does_not(): void
    {
        $user  = User::factory()->admin()->create();
        $movie = Movie::factory()->create();
        Person::factory()->create(['name' => 'Keanu Reeves']);
        Type::firstOrCreate(['name' => 'Actor'],    ['is_crew' => false]);
        Type::firstOrCreate(['name' => 'Director'], ['is_crew' => true]);

        $csv  = "person_name,type,character\nKeanu Reeves,Actor|Director,Neo\n";
        $file = UploadedFile::fake()->createWithContent('credits.csv', $csv);

        $this->actingAs($user)
            ->post(route('admin.movies.credits.import.store', $movie), ['file' => $file])
            ->assertOk()
            ->assertSee('2 credits imported successfully');

        $this->assertDatabaseHas('credits', ['movie_id' => $movie->id, 'character' => 'Neo']);
        $this->assertDatabaseHas('credits', ['movie_id' => $movie->id, 'character' => null]);
    }

    public function test_character_value_ignored_when_type_is_not_actor(): void
    {
        $user  = User::factory()->admin()->create();
        $movie = Movie::factory()->create();
        Person::factory()->create(['name' => 'Some Director']);
        Type::firstOrCreate(['name' => 'Director'], ['is_crew' => true]);

        $csv  = "person_name,type,character\nSome Director,Director,Some Character\n";
        $file = UploadedFile::fake()->createWithContent('credits.csv', $csv);

        $this->actingAs($user)
            ->post(route('admin.movies.credits.import.store', $movie), ['file' => $file])
            ->assertOk()
            ->assertSee('1 credit imported successfully');

        $this->assertDatabaseHas('credits', ['movie_id' => $movie->id, 'character' => null]);
    }

    // -----------------------------------------------------------------------
    // Row-level errors
    // -----------------------------------------------------------------------

    public function test_row_with_empty_person_name_is_skipped(): void
    {
        $user  = User::factory()->admin()->create();
        $movie = Movie::factory()->create();

        $csv  = "person_name,type,character\n,Actor,Neo\n";
        $file = UploadedFile::fake()->createWithContent('credits.csv', $csv);

        $this->actingAs($user)
            ->post(route('admin.movies.credits.import.store', $movie), ['file' => $file])
            ->assertOk()
            ->assertSee('Person name is required');
    }

    public function test_existing_credits_are_not_wiped_when_all_rows_are_invalid(): void
    {
        $user   = User::factory()->admin()->create();
        $movie  = Movie::factory()->create();
        $person = Person::factory()->create();
        $type   = Type::firstOrCreate(['name' => 'Actor'], ['is_crew' => false]);

        Credit::create(['movie_id' => $movie->id, 'person_id' => $person->id, 'type_id' => $type->id]);

        // Every row is invalid (empty person name)
        $csv  = "person_name,type,character\n,Actor,Neo\n";
        $file = UploadedFile::fake()->createWithContent('credits.csv', $csv);

        $this->actingAs($user)
            ->post(route('admin.movies.credits.import.store', $movie), ['file' => $file])
            ->assertOk();

        $this->assertSame(1, Credit::where('movie_id', $movie->id)->count());
    }

    // -----------------------------------------------------------------------
    // File-level validation
    // -----------------------------------------------------------------------

    public function test_missing_file_is_rejected(): void
    {
        $user  = User::factory()->admin()->create();
        $movie = Movie::factory()->create();

        $this->actingAs($user)
            ->post(route('admin.movies.credits.import.store', $movie), [])
            ->assertSessionHasErrors('file');
    }

    public function test_non_csv_file_is_rejected(): void
    {
        $user  = User::factory()->admin()->create();
        $movie = Movie::factory()->create();
        $file  = UploadedFile::fake()->create('credits.pdf', 100, 'application/pdf');

        $this->actingAs($user)
            ->post(route('admin.movies.credits.import.store', $movie), ['file' => $file])
            ->assertSessionHasErrors('file');
    }

    public function test_csv_missing_required_columns_is_rejected(): void
    {
        $user  = User::factory()->admin()->create();
        $movie = Movie::factory()->create();

        $csv  = "name,role\nKeanu Reeves,Actor\n";
        $file = UploadedFile::fake()->createWithContent('credits.csv', $csv);

        $this->actingAs($user)
            ->post(route('admin.movies.credits.import.store', $movie), ['file' => $file])
            ->assertSessionHasErrors('file');
    }
}
