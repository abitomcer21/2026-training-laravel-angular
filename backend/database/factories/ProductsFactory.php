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

    public static function getCatalogo(): array
    {
        return [
            ['name' => 'Coca-Cola',      'price' => 250,  'family' => 'Bebidas'],
            ['name' => 'Agua mineral',   'price' => 150,  'family' => 'Bebidas'],
            ['name' => 'Cerveza',        'price' => 200,  'family' => 'Cervezas'],
            ['name' => 'Vino tinto',     'price' => 350,  'family' => 'Vinos'],
            ['name' => 'Café solo',      'price' => 150,  'family' => 'Cafés'],
            ['name' => 'Cortado',        'price' => 160,  'family' => 'Cafés'],
            ['name' => 'Croquetas',      'price' => 800,  'family' => 'Entrantes'],
            ['name' => 'Jamón ibérico',  'price' => 1600, 'family' => 'Entrantes'],
            ['name' => 'Solomillo',      'price' => 2200, 'family' => 'Carnes'],
            ['name' => 'Chuletón',       'price' => 2800, 'family' => 'Carnes'],
            ['name' => 'Lubina',         'price' => 1800, 'family' => 'Pescados'],
            ['name' => 'Merluza',        'price' => 1600, 'family' => 'Pescados'],
            ['name' => 'Tiramisú',       'price' => 600,  'family' => 'Postres'],
            ['name' => 'Flan casero',    'price' => 500,  'family' => 'Postres'],
        ];
    }

    public function definition(): array
    {
        return [
            'uuid'          => (string) Str::uuid(),
            'restaurant_id' => EloquentRestaurant::factory(),
            'family_id'     => EloquentFamilies::factory(),
            'tax_id'        => EloquentTaxes::factory(),
            'image_src'     => fake()->imageUrl(),
            'name'          => fake()->word(),
            'price'         => fake()->numberBetween(100, 3000),
            'stock'         => fake()->numberBetween(10, 100),
            'active'        => fake()->boolean(80),
        ];
    }

    public function forRestaurant(EloquentRestaurant|int $restaurant): static
    {
        return $this->state(fn(array $attributes) => [
            'restaurant_id' => $restaurant instanceof EloquentRestaurant ? $restaurant->id : $restaurant,
        ]);
    }

    public function forFamily(EloquentFamilies|int $family): static
    {
        return $this->state(fn(array $attributes) => [
            'family_id' => $family instanceof EloquentFamilies ? $family->id : $family,
        ]);
    }

    public function forTax(EloquentTaxes|int $tax): static
    {
        return $this->state(fn(array $attributes) => [
            'tax_id' => $tax instanceof EloquentTaxes ? $tax->id : $tax,
        ]);
    }
}
