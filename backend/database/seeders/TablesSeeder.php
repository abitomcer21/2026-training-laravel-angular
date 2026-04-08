<?php

namespace Database\Seeders;

use App\Restaurants\Infrastructure\Persistence\Models\EloquentRestaurant;
use App\Tables\Infrastructure\Persistence\Models\EloquentTables;
use App\Zones\Infrastructure\Persistence\Models\EloquentZones;
use Illuminate\Database\Seeder;

class TablesSeeder extends Seeder
{
    private static array $prefijos = [
        'Terraza' => 'T',
        'Comedor' => 'S',
        'Barra'   => 'B',
    ];

    public function run(): void
    {
        if (EloquentTables::query()->exists()) {
            return;
        }

        $restaurants = EloquentRestaurant::query()->get();

        if ($restaurants->isEmpty()) {
            return;
        }

        foreach ($restaurants as $restaurant) {
            $zones = EloquentZones::where('restaurant_id', $restaurant->id)->get();

            foreach ($zones as $zone) {
                $prefijo = self::$prefijos[$zone->name] ?? 'M';

                for ($i = 1; $i <= 4; $i++) {
                    EloquentTables::factory()
                        ->forRestaurant($restaurant)
                        ->forZone($zone)
                        ->create(['name' => $prefijo . '-' . str_pad($i, 2, '0', STR_PAD_LEFT)]);
                }
            }
        }
    }
}
