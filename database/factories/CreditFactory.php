<?php

namespace Database\Factories;

use App\Models\Credit;
use App\Models\Movie;
use App\Models\Person;
use App\Models\Type;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Credit>
 */
class CreditFactory extends Factory
{
    public function definition(): array
    {
        return [
            'movie_id'  => Movie::factory(),
            'person_id' => Person::factory(),
            'type_id'   => Type::factory(),
            'character' => fake()->optional()->name(),
        ];
    }
}
