<?php

namespace Database\Factories;

use App\Restaurants\Infraestructure\Persistence\Models\EloquentRestaurant;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends Factory<EloquentRestaurant>
 */
class RestaurantFactory extends Factory
{
    protected $model = EloquentRestaurant::class;

    /**
     * The current password being used by the factory.
     */
    protected static ?string $password;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'uuid' => (string) Str::uuid(),
            'name' => fake()->company(),
            'legal_name' => fake()->company().' SL',
            'tax_id' => strtoupper(fake()->bothify('??########')),
            'email' => fake()->unique()->companyEmail(),
            'password' => static::$password ??= Hash::make('password'),
        ];
    }
}
