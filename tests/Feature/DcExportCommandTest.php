<?php

namespace Tests\Feature;

use App\Models\Credit;
use App\Models\Movie;
use App\Models\Person;
use App\Models\Type;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class DcExportCommandTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('local');
    }

    public function test_command_exits_successfully(): void
    {
        $this->artisan('dc:export')->assertExitCode(0);
    }

    public function test_command_writes_json_file_to_storage(): void
    {
        $this->artisan('dc:export');

        Storage::disk('local')->assertExists('dc-export.json');
    }

    public function test_command_respects_custom_output_filename(): void
    {
        $this->artisan('dc:export', ['--output' => 'custom.json']);

        Storage::disk('local')->assertExists('custom.json');
    }

    public function test_exported_json_has_required_top_level_keys(): void
    {
        $this->artisan('dc:export');

        $json = json_decode(Storage::disk('local')->get('dc-export.json'), true);

        $this->assertArrayHasKey('exported_at', $json);
        $this->assertArrayHasKey('counts', $json);
        $this->assertArrayHasKey('people', $json);
        $this->assertArrayHasKey('movies', $json);
        $this->assertArrayHasKey('types', $json);
        $this->assertArrayHasKey('credits', $json);
    }

    public function test_exported_people_have_correct_fields(): void
    {
        Person::factory()->create(['name' => 'Jane Doe', 'slug' => 'jane-doe', 'nationality' => 'British']);

        $this->artisan('dc:export');

        $json   = json_decode(Storage::disk('local')->get('dc-export.json'), true);
        $person = collect($json['people'])->firstWhere('slug', 'jane-doe');

        $this->assertNotNull($person);
        $this->assertSame('Jane Doe', $person['name']);
        $this->assertSame('British', $person['nationality']);
        $this->assertArrayHasKey('date_of_birth', $person);
        $this->assertArrayHasKey('date_of_death', $person);
    }

    public function test_exported_movies_have_correct_fields(): void
    {
        Movie::factory()->create(['title' => 'Inception', 'slug' => 'inception', 'release_year' => 2010]);

        $this->artisan('dc:export');

        $json  = json_decode(Storage::disk('local')->get('dc-export.json'), true);
        $movie = collect($json['movies'])->firstWhere('slug', 'inception');

        $this->assertNotNull($movie);
        $this->assertSame('Inception', $movie['title']);
        $this->assertSame(2010, $movie['release_year']);
        $this->assertArrayHasKey('poster', $movie);
    }

    public function test_exported_types_have_correct_fields_and_boolean_is_crew(): void
    {
        Type::factory()->create(['name' => 'Director', 'slug' => 'director', 'is_crew' => true]);

        $this->artisan('dc:export');

        $json = json_decode(Storage::disk('local')->get('dc-export.json'), true);
        $type = collect($json['types'])->firstWhere('slug', 'director');

        $this->assertNotNull($type);
        $this->assertSame('Director', $type['name']);
        $this->assertTrue($type['is_crew']);
    }

    public function test_exported_credits_use_slugs_not_ids(): void
    {
        // Provide name/title so factory afterCreating sets the correct slug
        $movie  = Movie::factory()->create(['title' => 'Alien']);   // slug → 'alien'
        $person = Person::factory()->create(['name' => 'Ridley Scott']); // slug → 'ridley-scott'
        $type   = Type::factory()->create(['name' => 'Director', 'slug' => 'director']);

        // Use Credit::create() directly to avoid CreditFactory spawning additional records
        Credit::create(['movie_id' => $movie->id, 'person_id' => $person->id, 'type_id' => $type->id]);

        $this->artisan('dc:export');

        $json   = json_decode(Storage::disk('local')->get('dc-export.json'), true);
        $credit = collect($json['credits'])->firstWhere('person_slug', 'ridley-scott');

        $this->assertNotNull($credit);
        $this->assertSame('alien', $credit['movie_slug']);
        $this->assertSame('director', $credit['type_slug']);
        $this->assertArrayNotHasKey('movie_id', $credit);
        $this->assertArrayNotHasKey('person_id', $credit);
    }

    public function test_counts_in_export_match_actual_records(): void
    {
        $people = Person::factory()->count(3)->create();
        $movies = Movie::factory()->count(2)->create();
        $type   = Type::factory()->create();

        // Create credits directly to avoid CreditFactory spawning additional people/movies/types
        Credit::create(['movie_id' => $movies[0]->id, 'person_id' => $people[0]->id, 'type_id' => $type->id]);
        Credit::create(['movie_id' => $movies[0]->id, 'person_id' => $people[1]->id, 'type_id' => $type->id]);
        Credit::create(['movie_id' => $movies[1]->id, 'person_id' => $people[0]->id, 'type_id' => $type->id]);
        Credit::create(['movie_id' => $movies[1]->id, 'person_id' => $people[2]->id, 'type_id' => $type->id]);

        $this->artisan('dc:export');

        $json = json_decode(Storage::disk('local')->get('dc-export.json'), true);

        // Assert the exported counts match what's actually in the DB
        $this->assertSame(Person::count(), $json['counts']['people']);
        $this->assertSame(Movie::count(), $json['counts']['movies']);
        $this->assertSame(Type::count(), $json['counts']['types']);
        $this->assertSame(Credit::count(), $json['counts']['credits']);
        // Assert the records we created are included
        $this->assertGreaterThanOrEqual(3, $json['counts']['people']);
        $this->assertGreaterThanOrEqual(2, $json['counts']['movies']);
        $this->assertGreaterThanOrEqual(4, $json['counts']['credits']);
    }
}
