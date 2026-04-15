<?php

namespace Database\Factories;

use App\Restaurants\Infrastructure\Persistence\Models\EloquentRestaurant;
use App\Tax\Infrastructure\Persistence\Models\EloquentTax;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class TaxFactory extends Factory
{
    protected $model = EloquentTax::class;

    public function definition(): array
    {
        return [
            'uuid' => (string) Str::uuid(),
            'restaurant_id' => EloquentRestaurant::factory(),
            'name'       => fake()->randomElement(['IVA Superreducido', 'IVA Reducido', 'IVA General']),
            'percentage' => fake()->randomElement([4, 10, 21]),
        ];
    }

    public function forRestaurant(EloquentRestaurant|int $restaurant): static
    {
        return $this->state(fn(array $attributes) => [
            'restaurant_id' => $restaurant instanceof EloquentRestaurant ? $restaurant->id : $restaurant,
        ]);
    }
}