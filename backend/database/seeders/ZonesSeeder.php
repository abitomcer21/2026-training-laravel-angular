<?php

namespace Database\Seeders;

use App\Restaurants\Infrastructure\Persistence\Models\EloquentRestaurant;
use App\Zones\Infrastructure\Persistence\Models\EloquentZones;
use Illuminate\Database\Seeder;

class ZonesSeeder extends Seeder
{
    public function run(): void
    {
        if (EloquentZones::query()->exists()) {
            return;
        }

        $restaurant = EloquentRestaurant::query()->first();

        if ($restaurant === null) {
            return;
        }

        $zoneNames = ['Terraza', 'Comedor', 'Barra'];

        foreach ($zoneNames as $zoneName) {
            EloquentZones::factory()
                ->forRestaurant($restaurant)
                ->create([
                    'name' => $zoneName,
                ]);
        }
    }
}
