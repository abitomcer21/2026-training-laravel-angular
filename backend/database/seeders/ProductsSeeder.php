<?php

namespace Database\Seeders;

use App\Family\Infrastructure\Persistence\Models\EloquentFamily;
use App\Products\Infrastructure\Persistence\Models\EloquentProduct;
use App\Restaurants\Infrastructure\Persistence\Models\EloquentRestaurant;
use App\Tax\Infrastructure\Persistence\Models\EloquentTax;
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
            $tax = EloquentTax::query()->where('restaurant_id', $restaurant->id)->first();
            $familias = EloquentFamily::where('restaurant_id', $restaurant->id)->pluck('id', 'name');

            if (!$tax || $familias->isEmpty()) {
                continue;
            }

            foreach (ProductsFactory::getCatalogo() as $item) {
                if (!isset($familias[$item['Family']])) {
                    continue;
                }

                EloquentProduct::factory()
                    ->forRestaurant($restaurant)
                    ->forFamily($familias[$item['Family']])
                    ->forTax($tax)
                    ->create([
                        'name'  => $item['name'],
                        'price' => $item['price'],
                    ]);
            }
        }
    }
}
