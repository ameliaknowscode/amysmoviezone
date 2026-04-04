<?php

namespace Tests\Unit;

use App\Models\Credit;
use App\Models\Movie;
use App\Models\Person;
use App\Models\Type;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CreditTest extends TestCase
{
    use RefreshDatabase;

    public function test_credit_can_be_created_with_factory(): void
    {
        $credit = Credit::factory()->create();

        $this->assertInstanceOf(Credit::class, $credit);
        $this->assertNotNull($credit->id);
    }

    public function test_credit_has_correct_fillable_fields(): void
    {
        $credit = new Credit();

        $this->assertEquals(['movie_id', 'person_id', 'type_id', 'character'], $credit->getFillable());
    }

    public function test_credit_belongs_to_movie(): void
    {
        $credit = Credit::factory()->create();

        $this->assertInstanceOf(Movie::class, $credit->movie);
    }

    public function test_credit_belongs_to_person(): void
    {
        $credit = Credit::factory()->create();

        $this->assertInstanceOf(Person::class, $credit->person);
    }

    public function test_credit_belongs_to_type(): void
    {
        $credit = Credit::factory()->create();

        $this->assertInstanceOf(Type::class, $credit->type);
    }

    // -------------------------------------------------------------------------
    // Helper methods
    // -------------------------------------------------------------------------

    public function test_by_type_url_returns_route_containing_type_and_person_slug(): void
    {
        $person = Person::factory()->create(['name' => 'Jane Doe']);
        $movie  = Movie::factory()->create();
        $type   = Type::factory()->create(['name' => 'Director', 'slug' => 'director']);

        // Use Credit::create() to avoid CreditFactory spawning an additional Type
        $credit = Credit::create([
            'movie_id'  => $movie->id,
            'person_id' => $person->id,
            'type_id'   => $type->id,
        ]);

        $credit->load(['type', 'person']);
        $url = $credit->byTypeUrl();

        $this->assertStringContainsString($type->slug, $url);
        $this->assertStringContainsString($person->slug, $url);
    }
}
