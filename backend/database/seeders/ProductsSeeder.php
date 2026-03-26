<?php

namespace Database\Seeders;

use App\Families\Infrastructure\Persistence\Models\EloquentFamilies;
use App\Products\Infrastructure\Persistence\Models\EloquentProducts;
use App\Restaurants\Infrastructure\Persistence\Models\EloquentRestaurant;
use App\Taxes\Infrastructure\Persistence\Models\EloquentTaxes;
use Illuminate\Database\Seeder;

class ProductsSeeder extends Seeder
{
    public function run(): void
    {
        if (EloquentProducts::query()->exists()) {
            return;
        }

        $restaurant = EloquentRestaurant::query()->first();

        if ($restaurant === null) {
            return;
        }

        $families = EloquentFamilies::query()->where('restaurant_id', $restaurant->id)->get();
        $taxes = EloquentTaxes::query()->where('restaurant_id', $restaurant->id)->get();

        if ($families->isEmpty() || $taxes->isEmpty()) {
            return;
        }

        for ($i = 0; $i < 20; $i++) {
            EloquentProducts::factory()
                ->forRestaurant($restaurant)
                ->forFamily($families->random())
                ->forTax($taxes->random())
                ->create();
        }
    }
}
