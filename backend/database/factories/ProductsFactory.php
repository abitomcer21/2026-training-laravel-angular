<?php

namespace Database\Factories;

use App\Families\Infrastructure\Persistence\Models\EloquentFamilies;
use App\Products\Infrastructure\Persistence\Models\EloquentProduct;
use App\Restaurants\Infrastructure\Persistence\Models\EloquentRestaurant;
use App\Taxes\Infrastructure\Persistence\Models\EloquentTaxes;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class ProductsFactory extends Factory
{
    protected $model = EloquentProduct::class;

    public function definition(): array
    {
        return [
            'uuid' => (string) Str::uuid(),
            'restaurant_id' => EloquentRestaurant::factory(),
            'family_id' => EloquentFamilies::factory(),
            'tax_id' => EloquentTaxes::factory(),
            'image_src' => fake()->imageUrl(),
            'name' => fake()->word(),
            'price' => fake()->numberBetween(100, 1000),
            'stock' => fake()->numberBetween(0, 100),
            'active' => fake()->boolean(),
        ];
    }

    public function forRestaurant(EloquentRestaurant|int $restaurant): static
    {
        return $this->state(fn (array $attributes) => [
            'restaurant_id' => $restaurant instanceof EloquentRestaurant ? $restaurant->id : $restaurant,
        ]);
    }

    public function forFamily(EloquentFamilies|int $family): static
    {
        return $this->state(fn (array $attributes) => [
            'family_id' => $family instanceof EloquentFamilies ? $family->id : $family,
        ]);
    }

    public function forTax(EloquentTaxes|int $tax): static
    {
        return $this->state(fn (array $attributes) => [
            'tax_id' => $tax instanceof EloquentTaxes ? $tax->id : $tax,
        ]);
    }
}
