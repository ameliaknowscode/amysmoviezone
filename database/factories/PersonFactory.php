<?php

namespace Database\Factories;

use App\Models\Person;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Person>
 */
class PersonFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name'          => fake()->name(),
            'date_of_birth' => fake()->dateTimeBetween('-80 years', '-18 years')->format('Y-m-d'),
            'nationality'   => fake()->country(),
        ];
    }
}
