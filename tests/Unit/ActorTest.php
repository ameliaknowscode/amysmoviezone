<?php

namespace Tests\Unit;

use App\Models\Actor;
use App\Models\Movie;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ActorTest extends TestCase
{
    use RefreshDatabase;

    public function test_actor_can_be_created_with_factory(): void
    {
        $actor = Actor::factory()->create();

        $this->assertInstanceOf(Actor::class, $actor);
        $this->assertNotNull($actor->id);
    }

    public function test_actor_has_correct_fillable_fields(): void
    {
        $actor = new Actor();

        $this->assertEquals(['name', 'date_of_birth', 'nationality'], $actor->getFillable());
    }

    public function test_actor_stores_all_fields_correctly(): void
    {
        Actor::factory()->create([
            'name'          => 'Cate Blanchett',
            'date_of_birth' => '1969-05-14',
            'nationality'   => 'Australian',
        ]);

        $this->assertDatabaseHas('actors', [
            'name'          => 'Cate Blanchett',
            'date_of_birth' => '1969-05-14',
            'nationality'   => 'Australian',
        ]);
    }

    public function test_actor_optional_fields_can_be_null(): void
    {
        $actor = Actor::factory()->create([
            'date_of_birth' => null,
            'nationality'   => null,
        ]);

        $this->assertNull($actor->date_of_birth);
        $this->assertNull($actor->nationality);
    }

    public function test_actor_name_is_a_string(): void
    {
        $actor = Actor::factory()->create(['name' => 'Tom Hanks']);

        $this->assertIsString($actor->name);
    }

    public function test_actor_belongs_to_many_movies(): void
    {
        $actor  = Actor::factory()->create();
        $movie1 = Movie::factory()->create();
        $movie2 = Movie::factory()->create();
        $actor->movies()->attach([$movie1->id, $movie2->id]);

        $this->assertCount(2, $actor->movies);
        $this->assertInstanceOf(Movie::class, $actor->movies->first());
    }
}
