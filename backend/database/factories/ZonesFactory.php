<?php

namespace Database\Factories;

use App\Restaurants\Infrastructure\Persistence\Models\EloquentRestaurant;
use App\Zones\Infrastructure\Persistence\Models\EloquentZones;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<EloquentZones>
 */
class ZonesFactory extends Factory
{
    protected $model = EloquentZones::class;

    public function definition(): array
    {
        return [
            'uuid' => (string) Str::uuid(),
            'restaurant_id' => EloquentRestaurant::factory(),
            'name' => 'Zone '.fake()->numberBetween(1, 20),
        ];
    }

    public function forRestaurant(EloquentRestaurant|int $restaurant): static
    {
        return $this->state(fn (array $attributes) => [
            'restaurant_id' => $restaurant instanceof EloquentRestaurant ? $restaurant->id : $restaurant,
        ]);
    }
}
