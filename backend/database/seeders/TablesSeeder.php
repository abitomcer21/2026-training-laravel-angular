<?php

namespace Database\Seeders;

use App\Restaurants\Infrastructure\Persistence\Models\EloquentRestaurant;
use App\Tables\Infrastructure\Persistence\Models\EloquentTables;
use App\Zones\Infrastructure\Persistence\Models\EloquentZones;
use Illuminate\Database\Seeder;

class TablesSeeder extends Seeder
{
    public function run(): void
    {
        if (EloquentTables::query()->exists()) {
            return;
        }

        $restaurant = EloquentRestaurant::query()->first();

        if ($restaurant === null) {
            return;
        }

        $zones = EloquentZones::query()->where('restaurant_id', $restaurant->id)->get();

        foreach ($zones as $zone) {
            EloquentTables::factory(4)
                ->forRestaurant($restaurant)
                ->forZone($zone)
                ->create();
        }
    }
}
