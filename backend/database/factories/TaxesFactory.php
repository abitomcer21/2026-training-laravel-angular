<?php

namespace Database\Factories;

use App\Restaurants\Infraestructure\Persistence\Models\EloquentRestaurant;
use App\Taxes\Infraestructure\Persistence\Models\EloquentTaxes;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<EloquentTaxes>
 */
class TaxesFactory extends Factory
{
    protected $model = EloquentTaxes::class;

    public function definition(): array
    {
        return [
            'uuid' => (string) Str::uuid(),
            'restaurant_id' => EloquentRestaurant::factory(),
            'name' => fake()->word(),
            'percentage' => fake()->randomElement([0, 4, 10, 21]),
        ];
    }

    public function forRestaurant(EloquentRestaurant|int $restaurant): static
    {
        return $this->state(fn (array $attributes) => [
            'restaurant_id' => $restaurant instanceof EloquentRestaurant ? $restaurant->id : $restaurant,
        ]);
    }
}

