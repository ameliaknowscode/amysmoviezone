<?php

namespace Tests\Unit;

use App\Actions\SyncCredits;
use App\Models\Credit;
use App\Models\Movie;
use App\Models\Person;
use App\Models\Type;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SyncCreditsTest extends TestCase
{
    use RefreshDatabase;

    // -------------------------------------------------------------------------
    // Movie as owner
    // -------------------------------------------------------------------------

    public function test_syncing_credits_for_a_movie_inserts_rows(): void
    {
        $movie  = Movie::factory()->create();
        $person = Person::factory()->create();
        $type   = Type::factory()->create();

        SyncCredits::for($movie, [
            ['person_id' => $person->id, 'type_id' => $type->id, 'character' => 'Hero'],
        ]);

        $this->assertDatabaseHas('credits', [
            'movie_id'  => $movie->id,
            'person_id' => $person->id,
            'type_id'   => $type->id,
            'character' => 'Hero',
        ]);
    }

    public function test_syncing_movie_credits_deletes_existing_credits_first(): void
    {
        $movie   = Movie::factory()->create();
        $person1 = Person::factory()->create();
        $person2 = Person::factory()->create();
        $type    = Type::factory()->create();

        Credit::factory()->create([
            'movie_id'  => $movie->id,
            'person_id' => $person1->id,
            'type_id'   => $type->id,
        ]);

        SyncCredits::for($movie, [
            ['person_id' => $person2->id, 'type_id' => $type->id],
        ]);

        $this->assertDatabaseMissing('credits', ['person_id' => $person1->id]);
        $this->assertDatabaseHas('credits', ['person_id' => $person2->id]);
    }

    public function test_syncing_movie_credits_with_empty_rows_clears_all_credits(): void
    {
        $movie  = Movie::factory()->create();
        $person = Person::factory()->create();
        $type   = Type::factory()->create();
        Credit::factory()->create(['movie_id' => $movie->id, 'person_id' => $person->id, 'type_id' => $type->id]);

        SyncCredits::for($movie, []);

        $this->assertDatabaseCount('credits', 0);
    }

    public function test_rows_missing_person_id_are_skipped(): void
    {
        $movie = Movie::factory()->create();
        $type  = Type::factory()->create();

        SyncCredits::for($movie, [
            ['type_id' => $type->id, 'character' => 'Nobody'],
        ]);

        $this->assertDatabaseCount('credits', 0);
    }

    public function test_rows_missing_type_id_are_skipped(): void
    {
        $movie  = Movie::factory()->create();
        $person = Person::factory()->create();

        SyncCredits::for($movie, [
            ['person_id' => $person->id],
        ]);

        $this->assertDatabaseCount('credits', 0);
    }

    public function test_character_is_optional(): void
    {
        $movie  = Movie::factory()->create();
        $person = Person::factory()->create();
        $type   = Type::factory()->create();

        SyncCredits::for($movie, [
            ['person_id' => $person->id, 'type_id' => $type->id],
        ]);

        $this->assertDatabaseHas('credits', [
            'movie_id'  => $movie->id,
            'character' => null,
        ]);
    }

    // -------------------------------------------------------------------------
    // Person as owner
    // -------------------------------------------------------------------------

    public function test_syncing_credits_for_a_person_inserts_rows(): void
    {
        $person = Person::factory()->create();
        $movie  = Movie::factory()->create();
        $type   = Type::factory()->create();

        SyncCredits::for($person, [
            ['movie_id' => $movie->id, 'type_id' => $type->id, 'character' => 'Villain'],
        ]);

        $this->assertDatabaseHas('credits', [
            'person_id' => $person->id,
            'movie_id'  => $movie->id,
            'type_id'   => $type->id,
            'character' => 'Villain',
        ]);
    }

    public function test_syncing_person_credits_deletes_existing_credits_first(): void
    {
        $person = Person::factory()->create();
        $movie1 = Movie::factory()->create();
        $movie2 = Movie::factory()->create();
        $type   = Type::factory()->create();

        Credit::factory()->create([
            'person_id' => $person->id,
            'movie_id'  => $movie1->id,
            'type_id'   => $type->id,
        ]);

        SyncCredits::for($person, [
            ['movie_id' => $movie2->id, 'type_id' => $type->id],
        ]);

        $this->assertDatabaseMissing('credits', ['movie_id' => $movie1->id]);
        $this->assertDatabaseHas('credits', ['movie_id' => $movie2->id]);
    }
}
