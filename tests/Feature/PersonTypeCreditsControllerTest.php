<?php

namespace Tests\Feature;

use App\Models\Credit;
use App\Models\Movie;
use App\Models\Person;
use App\Models\Type;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class PersonTypeCreditsControllerTest extends TestCase
{
    use RefreshDatabase;

    private function url(Type $type, Person $person): string
    {
        return route('credits.by-type', [
            'typeSlug'   => Str::slug($type->name),
            'personSlug' => Str::slug($person->name),
        ]);
    }

    public function test_returns_200_for_valid_type_and_person_with_credits(): void
    {
        $person = Person::factory()->create(['name' => 'Keanu Reeves']);
        $type   = Type::firstOrCreate(['name' => 'Actor'], ['is_crew' => false]);
        $movie  = Movie::factory()->create(['title' => 'The Matrix', 'release_year' => 1999]);
        Credit::factory()->create([
            'person_id' => $person->id,
            'type_id'   => $type->id,
            'movie_id'  => $movie->id,
            'character' => 'Neo',
        ]);

        $this->get($this->url($type, $person))->assertOk();
    }

    public function test_displays_person_name_and_type(): void
    {
        $person = Person::factory()->create(['name' => 'Keanu Reeves']);
        $type   = Type::firstOrCreate(['name' => 'Actor'], ['is_crew' => false]);
        $movie  = Movie::factory()->create(['title' => 'The Matrix', 'release_year' => 1999]);
        Credit::factory()->create([
            'person_id' => $person->id,
            'type_id'   => $type->id,
            'movie_id'  => $movie->id,
        ]);

        $this->get($this->url($type, $person))
            ->assertSee('Keanu Reeves')
            ->assertSee('Actor');
    }

    public function test_lists_credits_with_movie_title_year_and_character(): void
    {
        $person = Person::factory()->create(['name' => 'Keanu Reeves']);
        $type   = Type::firstOrCreate(['name' => 'Actor'], ['is_crew' => false]);
        $movie  = Movie::factory()->create(['title' => 'The Matrix', 'release_year' => 1999]);
        Credit::factory()->create([
            'person_id' => $person->id,
            'type_id'   => $type->id,
            'movie_id'  => $movie->id,
            'character' => 'Neo',
        ]);

        $this->get($this->url($type, $person))
            ->assertSee('The Matrix')
            ->assertSee('1999')
            ->assertSee('Neo');
    }

    public function test_returns_404_for_unknown_type_slug(): void
    {
        $person = Person::factory()->create(['name' => 'Keanu Reeves']);

        $this->get('/no-such-type/' . Str::slug($person->name))
            ->assertNotFound();
    }

    public function test_returns_404_for_unknown_person_slug(): void
    {
        $type = Type::firstOrCreate(['name' => 'Actor'], ['is_crew' => false]);

        $this->get('/' . Str::slug($type->name) . '/nobody-here')
            ->assertNotFound();
    }

    public function test_returns_404_when_person_has_no_credits_of_that_type(): void
    {
        $person = Person::factory()->create(['name' => 'Keanu Reeves']);
        $type   = Type::firstOrCreate(['name' => 'Actor'], ['is_crew' => false]);
        // No credits created for this combination.

        $this->get($this->url($type, $person))
            ->assertNotFound();
    }

    public function test_only_shows_credits_for_the_requested_type(): void
    {
        $person      = Person::factory()->create(['name' => 'Keanu Reeves']);
        $actorType   = Type::firstOrCreate(['name' => 'Actor'],    ['is_crew' => false]);
        $directorType = Type::firstOrCreate(['name' => 'Director'], ['is_crew' => true]);
        $actorMovie   = Movie::factory()->create(['title' => 'The Matrix']);
        $directorMovie = Movie::factory()->create(['title' => 'Side Project']);

        Credit::factory()->create([
            'person_id' => $person->id,
            'type_id'   => $actorType->id,
            'movie_id'  => $actorMovie->id,
        ]);
        Credit::factory()->create([
            'person_id' => $person->id,
            'type_id'   => $directorType->id,
            'movie_id'  => $directorMovie->id,
        ]);

        $this->get($this->url($actorType, $person))
            ->assertSee('The Matrix')
            ->assertDontSee('Side Project');
    }

    public function test_is_accessible_without_authentication(): void
    {
        $person = Person::factory()->create(['name' => 'Keanu Reeves']);
        $type   = Type::firstOrCreate(['name' => 'Actor'], ['is_crew' => false]);
        $movie  = Movie::factory()->create();
        Credit::factory()->create([
            'person_id' => $person->id,
            'type_id'   => $type->id,
            'movie_id'  => $movie->id,
        ]);

        $this->get($this->url($type, $person))->assertOk();
    }
}
