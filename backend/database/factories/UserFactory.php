<?php

namespace Database\Factories;

use App\User\Infrastructure\Persistence\Models\EloquentUser;
use App\Restaurants\Infrastructure\Persistence\Models\EloquentRestaurant;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UserFactory extends Factory
{
    protected $model = EloquentUser::class;

    protected static ?string $password;

    public function definition(): array
    {
        return [
            'uuid' => (string) Str::uuid(),
            'restaurant_id' => EloquentRestaurant::factory(),
            'role' => $this->faker->randomElement(['admin', 'waiter', 'chef']),
            'image_src' => $this->faker->imageUrl(),
            'name' => $this->faker->name(),
            'email' => $this->faker->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => static::$password ??= Hash::make('password'),
            'pin' => (string) $this->faker->unique()->numberBetween(1000, 9999),
            'remember_token' => Str::random(10),
        ];
    }

    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }

    public function admin(): static
    {
        return $this->state(fn (array $attributes) => [
            'role' => 'admin',
        ]);
    }

    public function waiter(): static
    {
        return $this->state(fn (array $attributes) => [
            'role' => 'waiter',
        ]);
    }

    public function chef(): static
    {
        return $this->state(fn (array $attributes) => [
            'role' => 'chef',
        ]);
    }

    public function forRestaurant(EloquentRestaurant|int $restaurant): static
    {
        return $this->state(fn (array $attributes) => [
            'restaurant_id' => $restaurant instanceof EloquentRestaurant ? $restaurant->id : $restaurant,
        ]);
    }
}