<?php

namespace Database\Factories;

use App\Models\Collection;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Collection>
 */
class CollectionFactory extends Factory
{
    protected $model = Collection::class;

    public function definition(): array
    {
        $name = fake()->unique()->sentence(3, false);

        return [
            'name'        => $name,
            'slug'        => Str::slug($name),
            'description' => null,
        ];
    }
}
