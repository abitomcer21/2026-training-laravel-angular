<?php

namespace Database\Factories;

use App\Families\Infraestructure\Persistence\Models\EloquentFamilies;
use App\Restaurants\Infraestructure\Persistence\Models\EloquentRestaurant;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<EloquentFamilies>
 */
class FamilyFactory extends Factory
{
    protected $model = EloquentFamilies::class;

    public function definition(): array
    {
        return [
            'uuid' => (string) Str::uuid(),
            'restaurant_id' => EloquentRestaurant::factory(),
            'name' => fake()->word(),
            'activo' => fake()->boolean(),
        ];
    }

    public function forRestaurant(EloquentRestaurant|int $restaurant): static
    {
        return $this->state(fn (array $attributes) => [
            'restaurant_id' => $restaurant instanceof EloquentRestaurant ? $restaurant->id : $restaurant,
        ]);
    }
}
