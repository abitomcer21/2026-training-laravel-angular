<?php

namespace Database\Factories;

use App\Family\Infrastructure\Persistence\Models\EloquentFamily;
use App\Products\Infrastructure\Persistence\Models\EloquentProduct;
use App\Restaurants\Infrastructure\Persistence\Models\EloquentRestaurant;
use App\Tax\Infrastructure\Persistence\Models\EloquentTax;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class ProductsFactory extends Factory
{
    protected $model = EloquentProduct::class;

    public static function getCatalogo(): array
    {
        return [
            ['name' => 'Coca-Cola',      'price' => 250,  'Family' => 'Bebidas'],
            ['name' => 'Agua mineral',   'price' => 150,  'Family' => 'Bebidas'],
            ['name' => 'Cerveza',        'price' => 200,  'Family' => 'Cervezas'],
            ['name' => 'Vino tinto',     'price' => 350,  'Family' => 'Vinos'],
            ['name' => 'Café solo',      'price' => 150,  'Family' => 'Cafés'],
            ['name' => 'Cortado',        'price' => 160,  'Family' => 'Cafés'],
            ['name' => 'Croquetas',      'price' => 800,  'Family' => 'Entrantes'],
            ['name' => 'Jamón ibérico',  'price' => 1600, 'Family' => 'Entrantes'],
            ['name' => 'Solomillo',      'price' => 2200, 'Family' => 'Carnes'],
            ['name' => 'Chuletón',       'price' => 2800, 'Family' => 'Carnes'],
            ['name' => 'Lubina',         'price' => 1800, 'Family' => 'Pescados'],
            ['name' => 'Merluza',        'price' => 1600, 'Family' => 'Pescados'],
            ['name' => 'Tiramisú',       'price' => 600,  'Family' => 'Postres'],
            ['name' => 'Flan casero',    'price' => 500,  'Family' => 'Postres'],
        ];
    }

    public function definition(): array
    {
        return [
            'uuid' => (string) Str::uuid(),
            'restaurant_id' => EloquentRestaurant::factory(),
            'Family_id' => EloquentFamily::factory(),
            'tax_id' => EloquentTax::factory(),
            'image_src' => fake()->imageUrl(),
            'name' => fake()->word(),
            'price' => fake()->numberBetween(100, 3000),
            'stock' => fake()->numberBetween(10, 100),
            'active' => fake()->boolean(80),
        ];
    }

    public function forRestaurant(EloquentRestaurant|int $restaurant): static
    {
        return $this->state(fn (array $attributes) => [
            'restaurant_id' => $restaurant instanceof EloquentRestaurant ? $restaurant->id : $restaurant,
        ]);
    }

    public function forFamily(EloquentFamily|string $Family): static
    {
        return $this->state(fn (array $attributes) => [
            'Family_id' => $Family instanceof EloquentFamily ? $Family->uuid : $Family,
        ]);
    }

    public function forTax(EloquentTax|int $tax): static
    {
        return $this->state(fn (array $attributes) => [
            'tax_id' => $tax instanceof EloquentTax ? $tax->id : $tax,
        ]);
    }
}
