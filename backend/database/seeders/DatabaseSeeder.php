<?php

namespace Database\Seeders;

use App\Families\Infrastructure\Persistence\Models\EloquentFamilies;
use App\Order\Infrastructure\Persistence\Models\EloquentOrder;
use App\Order\Infrastructure\Persistence\Models\EloquentOrderLine;
use App\Products\Infrastructure\Persistence\Models\EloquentProducts;
use App\Restaurants\Infrastructure\Persistence\Models\EloquentRestaurant;
use App\Sales\Infrastructure\Persistence\Models\EloquentSales;
use App\Sales\Infrastructure\Persistence\Models\EloquentSalesLine;
use App\Tables\Infrastructure\Persistence\Models\EloquentTables;
use App\Taxes\Infrastructure\Persistence\Models\EloquentTaxes;
use App\User\Infrastructure\Persistence\Models\EloquentUser;
use App\Zones\Infrastructure\Persistence\Models\EloquentZones;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        $restaurant = EloquentRestaurant::factory()->create();

        $users = collect();
        $users = $users->merge(EloquentUser::factory(1)->admin()->forRestaurant($restaurant)->create());
        $users = $users->merge(EloquentUser::factory(3)->waiter()->forRestaurant($restaurant)->create());
        $users = $users->merge(EloquentUser::factory(2)->chef()->forRestaurant($restaurant)->create());

        $waiters = $users->where('role', 'waiter');

        $families = EloquentFamilies::factory(5)->forRestaurant($restaurant)->create();

        $taxes = EloquentTaxes::factory(3)->forRestaurant($restaurant)->create();

        $zones = EloquentZones::factory(3)->forRestaurant($restaurant)->create();

        $tables = collect();
        foreach ($zones as $zone) {
            $tables = $tables->merge(
                EloquentTables::factory(4)
                    ->forRestaurant($restaurant)
                    ->forZone($zone)
                    ->create()
            );
        }

        $products = collect();
        for ($i = 0; $i < 20; $i++) {
            $products->push(
                EloquentProducts::factory()
                    ->forRestaurant($restaurant)
                    ->forFamily($families->random())
                    ->forTax($taxes->random())
                    ->create()
            );
        }

        for ($i = 0; $i < 10; $i++) {
            $order = EloquentOrder::factory()
                ->forRestaurant($restaurant)
                ->forTable($tables->random())
                ->forUser($waiters->random())
                ->create();

            $sale = EloquentSales::factory()
                ->forRestaurant($restaurant)
                ->forOrder($order)
                ->forUser($waiters->random())
                ->create();

            $lines = random_int(2, 5);

            for ($line = 0; $line < $lines; $line++) {
                $product = $products->random();
                $lineUser = $waiters->random();
                $taxPercentage = $taxes->random()->percentage;
                $quantity = random_int(1, 4);
                $price = $product->price;

                $orderLine = EloquentOrderLine::create([
                    'uuid' => (string) Str::uuid(),
                    'restaurant_id' => $restaurant->id,
                    'order_id' => $order->id,
                    'product_id' => $product->id,
                    'user_id' => $lineUser->id,
                    'quantity' => $quantity,
                    'price' => $price,
                    'tax_percentage' => $taxPercentage,
                ]);

                EloquentSalesLine::create([
                    'uuid' => (string) Str::uuid(),
                    'restaurant_id' => $restaurant->id,
                    'sale_id' => $sale->id,
                    'order_line_id' => $orderLine->id,
                    'user_id' => $lineUser->id,
                    'quantity' => $quantity,
                    'price' => $price,
                    'tax_percentage' => $taxPercentage,
                ]);
            }
        }
    }
}