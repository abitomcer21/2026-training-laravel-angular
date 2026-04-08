<?php

namespace Database\Seeders;

use App\Families\Infrastructure\Persistence\Models\EloquentFamilies;
use App\Products\Infrastructure\Persistence\Models\EloquentProduct;
use App\Restaurants\Infrastructure\Persistence\Models\EloquentRestaurant;
use App\Taxes\Infrastructure\Persistence\Models\EloquentTaxes;
use Database\Factories\ProductsFactory;
use Illuminate\Database\Seeder;

class ProductsSeeder extends Seeder
{
    public function run(): void
    {
        if (EloquentProduct::query()->exists()) {
            return;
        }

        $restaurants = EloquentRestaurant::query()->get();

        if ($restaurants->isEmpty()) {
            return;
        }

        foreach ($restaurants as $restaurant) {
            $tax = EloquentTaxes::query()->where('restaurant_id', $restaurant->id)->first();
            $familias = EloquentFamilies::where('restaurant_id', $restaurant->id)->pluck('id', 'name');

            if (!$tax || $familias->isEmpty()) {
                continue;
            }

            foreach (ProductsFactory::getCatalogo() as $item) {
                if (!isset($familias[$item['family']])) {
                    continue;
                }

                EloquentProduct::factory()
                    ->forRestaurant($restaurant)
                    ->forFamily($familias[$item['family']])
                    ->forTax($tax)
                    ->create([
                        'name'  => $item['name'],
                        'price' => $item['price'],
                    ]);
            }
        }
    }
}
