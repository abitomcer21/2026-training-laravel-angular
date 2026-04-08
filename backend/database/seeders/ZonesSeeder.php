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

        $restaurants = EloquentRestaurant::query()->get();

        if ($restaurants->isEmpty()) {
            return;
        }

        foreach ($restaurants as $restaurant) {
            foreach (['Terraza', 'Sala', 'Barra'] as $zoneName) {
                EloquentZones::factory()
                    ->forRestaurant($restaurant)
                    ->create(['name' => $zoneName]);
            }
        }
    }
}
