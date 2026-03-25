<?php

namespace Database\Factories;

use App\Families\Infraestructure\Persistence\Models\EloquentFamilies;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<EloquentFamilies>
 */
class FamilyFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'uuid' => (string) Str::uuid(),
            'restaurant_id' => fake()->numberBetween(1, 10),
            'name' => fake()->word(),
            'activo' => fake()->boolean(),
        ];
    }
}
