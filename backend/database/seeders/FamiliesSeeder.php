<?php

namespace Database\Seeders;

use App\Families\Infrastructure\Persistence\Models\EloquentFamilies;
use App\Restaurants\Infrastructure\Persistence\Models\EloquentRestaurant;
use Illuminate\Database\Seeder;

class FamiliesSeeder extends Seeder
{
    public function run(): void
    {
        if (EloquentFamilies::query()->exists()) {
            return;
        }

        $restaurant = EloquentRestaurant::query()->first();

        if ($restaurant === null) {
            return;
        }

        EloquentFamilies::factory(6)
            ->forRestaurant($restaurant)
            ->create();
    }
}
