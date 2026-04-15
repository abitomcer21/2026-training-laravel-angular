<?php
namespace Database\Factories;
use App\Family\Infrastructure\Persistence\Models\EloquentFamily;
use App\Restaurants\Infrastructure\Persistence\Models\EloquentRestaurant;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class FamilyFactory extends Factory
{
    protected $model = EloquentFamily::class;

    private static array $nombres = [
        'Entrantes', 'Carnes', 'Pescados', 'Postres', 'Bebidas', 'Vinos', 'Cervezas', 'Cafés',
    ];

    private static int $index = 0;

    public function definition(): array
    {
        return [
            'uuid'          => (string) Str::uuid(),
            'restaurant_id' => EloquentRestaurant::factory(),
            'name'          => self::$nombres[self::$index++ % count(self::$nombres)],
            'active'        => true,
        ];
    }

    public function forRestaurant(EloquentRestaurant|int $restaurant): static
    {
        return $this->state(fn (array $attributes) => [
            'restaurant_id' => $restaurant instanceof EloquentRestaurant ? $restaurant->id : $restaurant,
        ]);
    }
}