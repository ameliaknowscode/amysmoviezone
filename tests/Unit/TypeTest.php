<?php

namespace Tests\Unit;

use App\Models\Type;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TypeTest extends TestCase
{
    use RefreshDatabase;

    public function test_type_can_be_created_with_factory(): void
    {
        $type = Type::factory()->create();

        $this->assertInstanceOf(Type::class, $type);
        $this->assertNotNull($type->id);
    }

    public function test_type_has_correct_fillable_fields(): void
    {
        $type = new Type();

        $this->assertEquals(['name', 'is_crew'], $type->getFillable());
    }

    public function test_type_stores_all_fields_correctly(): void
    {
        Type::factory()->create([
            'name'    => 'Director',
            'is_crew' => true,
        ]);

        $this->assertDatabaseHas('types', [
            'name'    => 'Director',
            'is_crew' => true,
        ]);
    }

    public function test_is_crew_is_cast_to_boolean(): void
    {
        $crew    = Type::factory()->create(['is_crew' => true]);
        $nonCrew = Type::factory()->create(['is_crew' => false]);

        $this->assertIsBool($crew->is_crew);
        $this->assertTrue($crew->is_crew);
        $this->assertFalse($nonCrew->is_crew);
    }

    public function test_is_crew_defaults_to_true(): void
    {
        $type = Type::create(['name' => 'Extra']);

        $this->assertTrue($type->is_crew);
    }
}
