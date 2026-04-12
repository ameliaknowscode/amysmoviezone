<?php

namespace Tests\Unit;

use App\Models\Credit;
use App\Models\Person;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PersonTest extends TestCase
{
    use RefreshDatabase;

    public function test_person_can_be_created_with_factory(): void
    {
        $person = Person::factory()->create();

        $this->assertInstanceOf(Person::class, $person);
        $this->assertNotNull($person->id);
    }

    public function test_person_has_correct_fillable_fields(): void
    {
        $person = new Person();

        $this->assertEquals(['name', 'slug', 'date_of_birth', 'date_of_death', 'nationality', 'bio', 'photo'], $person->getFillable());
    }

    public function test_person_stores_all_fields_correctly(): void
    {
        Person::factory()->create([
            'name'          => 'Jane Smith',
            'date_of_birth' => '1980-07-20',
            'nationality'   => 'Australian',
        ]);

        $this->assertDatabaseHas('people', [
            'name'          => 'Jane Smith',
            'date_of_birth' => '1980-07-20',
            'nationality'   => 'Australian',
        ]);
    }

    public function test_person_optional_fields_can_be_null(): void
    {
        $person = Person::factory()->create([
            'date_of_birth' => null,
            'nationality'   => null,
        ]);

        $this->assertNull($person->date_of_birth);
        $this->assertNull($person->nationality);
    }

    public function test_person_name_is_a_string(): void
    {
        $person = Person::factory()->create(['name' => 'Tom Jones']);

        $this->assertIsString($person->name);
    }

    public function test_person_has_many_credits(): void
    {
        $person = Person::factory()->create();
        Credit::factory()->count(2)->create(['person_id' => $person->id]);

        $person->refresh();
        $this->assertCount(2, $person->credits);
        $this->assertInstanceOf(Credit::class, $person->credits->first());
    }

    // -------------------------------------------------------------------------
    // Helper methods
    // -------------------------------------------------------------------------

    public function test_dominant_type_name_returns_most_frequent_credit_type(): void
    {
        $person       = Person::factory()->create();
        $movie1       = \App\Models\Movie::factory()->create();
        $movie2       = \App\Models\Movie::factory()->create();
        $movie3       = \App\Models\Movie::factory()->create();
        $actorType    = \App\Models\Type::factory()->create(['is_crew' => false]);
        $directorType = \App\Models\Type::factory()->create(['is_crew' => true]);

        // credits unique on (movie_id, person_id, type_id) — use different movies
        Credit::create(['person_id' => $person->id, 'movie_id' => $movie1->id, 'type_id' => $actorType->id]);
        Credit::create(['person_id' => $person->id, 'movie_id' => $movie2->id, 'type_id' => $actorType->id]);
        Credit::create(['person_id' => $person->id, 'movie_id' => $movie3->id, 'type_id' => $directorType->id]);

        $person->load(['credits.type']);

        $this->assertSame($actorType->name, $person->dominantTypeName());
    }

    public function test_dominant_type_name_returns_null_when_no_credits(): void
    {
        $person = Person::factory()->create();
        $person->load(['credits.type']);

        $this->assertNull($person->dominantTypeName());
    }

    public function test_dominant_type_url_returns_null_when_no_credits(): void
    {
        $person = Person::factory()->create();
        $person->load(['credits.type']);

        $this->assertNull($person->dominantTypeUrl());
    }

    public function test_dominant_type_url_returns_route_string_when_credits_exist(): void
    {
        $person = Person::factory()->create(['name' => 'Jane Doe']);
        $movie  = \App\Models\Movie::factory()->create();
        $type   = \App\Models\Type::factory()->create(['is_crew' => false]);

        Credit::create(['person_id' => $person->id, 'movie_id' => $movie->id, 'type_id' => $type->id]);

        $person->load(['credits.type']);

        $url = $person->dominantTypeUrl();
        $this->assertStringContainsString($person->slug, $url);
        $this->assertStringContainsString($type->slug, $url);
    }
}
