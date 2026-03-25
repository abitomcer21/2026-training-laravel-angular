<?php

namespace Database\Factories;

use App\Restaurants\Infrastructure\Persistence\Models\EloquentRestaurant;
use App\Tables\Infrastructure\Persistence\Models\EloquentTables;
use App\Zones\Infrastructure\Persistence\Models\EloquentZones;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<EloquentTables>
 */
class TablesFactory extends Factory
{
    protected $model = EloquentTables::class;

    public function definition(): array
    {
        return [
            'uuid' => (string) Str::uuid(),
            'restaurant_id' => EloquentRestaurant::factory(),
            'zone_id' => EloquentZones::factory(),
            'name' => 'Table '.fake()->numberBetween(1, 50),
        ];
    }

    public function forZone(EloquentZones|int $zone): static
    {
        return $this->state(fn (array $attributes) => [
            'zone_id' => $zone instanceof EloquentZones ? $zone->id : $zone,
        ]);
    }

    public function forRestaurant(EloquentRestaurant|int $restaurant): static
    {
        return $this->state(fn (array $attributes) => [
            'restaurant_id' => $restaurant instanceof EloquentRestaurant ? $restaurant->id : $restaurant,
            'zone_id' => EloquentZones::factory()->forRestaurant($restaurant),
        ]);
    }
}
