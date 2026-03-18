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

        $this->assertEquals(['name', 'slug', 'date_of_birth', 'nationality'], $person->getFillable());
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
}
