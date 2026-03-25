<?php

namespace Database\Factories;

use App\Restaurants\Infraestructure\Persistence\Models\EloquentRestaurant;
use App\Sales\Infraestructure\Persistence\Models\EloquentSales;
use App\User\Infrastructure\Persistence\Models\EloquentUser;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<EloquentSales>
 */
class SalesFactory extends Factory
{
    protected $model = EloquentSales::class;

    public function definition(): array
    {
        return [
            'uuid' => (string) Str::uuid(),
            'restaurant_id' => EloquentRestaurant::factory(),
            'order_id' => null,
            'user_id' => EloquentUser::factory(),
            'ticket_number' => fake()->numberBetween(1000, 9999),
            'value_date' => now(),
            'total' => fake()->numberBetween(100, 10000),
        ];
    }

    public function forRestaurant(EloquentRestaurant|int $restaurant): static
    {
        return $this->state(fn (array $attributes) => [
            'restaurant_id' => $restaurant instanceof EloquentRestaurant ? $restaurant->id : $restaurant,
        ]);
    }

    public function forUser(EloquentUser|int $user): static
    {
        return $this->state(fn (array $attributes) => [
            'user_id' => $user instanceof EloquentUser ? $user->id : $user,
        ]);
    }
}

