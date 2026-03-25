<?php

namespace Database\Factories;

use App\Order\Infrastructure\Persistence\Models\EloquentOrder;
use App\Restaurants\Infrastructure\Persistence\Models\EloquentRestaurant;
use App\Tables\Infrastructure\Persistence\Models\EloquentTables;
use App\User\Infrastructure\Persistence\Models\EloquentUser;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<EloquentOrder>
 */
class OrderFactory extends Factory
{
    protected $model = EloquentOrder::class;

    public function definition(): array
    {
        return [
            'uuid' => (string) Str::uuid(),
            'restaurant_id' => EloquentRestaurant::factory(),
            'table_id' => EloquentTables::factory(),
            'opened_by_user_id' => EloquentUser::factory(),
            'closed_by_user_id' => null,
            'status' => fake()->randomElement(['open', 'closed', 'cancelled']),
            'diners' => fake()->numberBetween(1, 10),
            'opened_at' => now(),
            'closed_at' => null,
        ];
    }

    public function forRestaurant(EloquentRestaurant|int $restaurant): static
    {
        return $this->state(fn (array $attributes) => [
            'restaurant_id' => $restaurant instanceof EloquentRestaurant ? $restaurant->id : $restaurant,
        ]);
    }

    public function forTable(EloquentTables|int $table): static
    {
        return $this->state(fn (array $attributes) => [
            'table_id' => $table instanceof EloquentTables ? $table->id : $table,
        ]);
    }

    public function forUser(EloquentUser|int $user): static
    {
        return $this->state(fn (array $attributes) => [
            'opened_by_user_id' => $user instanceof EloquentUser ? $user->id : $user,
        ]);
    }

    public function open(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'open',
            'closed_at' => null,
            'closed_by_user_id' => null,
        ]);
    }

    public function closed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'closed',
            'closed_at' => now(),
        ]);
    }
}
