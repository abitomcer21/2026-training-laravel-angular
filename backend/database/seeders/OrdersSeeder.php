<?php

namespace Database\Seeders;

use App\Order\Infrastructure\Persistence\Models\EloquentOrder;
use App\Order\Infrastructure\Persistence\Models\EloquentOrderLine;
use App\Products\Infrastructure\Persistence\Models\EloquentProduct;
use App\Restaurants\Infrastructure\Persistence\Models\EloquentRestaurant;
use App\Tables\Infrastructure\Persistence\Models\EloquentTables;
use App\Taxes\Infrastructure\Persistence\Models\EloquentTaxes;
use App\User\Infrastructure\Persistence\Models\EloquentUser;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class OrdersSeeder extends Seeder
{
    public function run(): void
    {
        if (EloquentOrder::query()->exists()) {
            return;
        }

        $restaurants = EloquentRestaurant::query()->get();

        if ($restaurants->isEmpty()) {
            return;
        }

        foreach ($restaurants as $restaurant) {
            $tables   = EloquentTables::query()->where('restaurant_id', $restaurant->id)->get();
            $products = EloquentProduct::query()->where('restaurant_id', $restaurant->id)->get();
            $taxes    = EloquentTaxes::query()->where('restaurant_id', $restaurant->id)->get()->keyBy('id');
            $users    = EloquentUser::query()
                ->where('restaurant_id', $restaurant->id)
                ->whereIn('role', ['waiter', 'chef'])
                ->get();

            if ($tables->isEmpty() || $products->isEmpty() || $users->isEmpty()) {
                continue;
            }

            for ($i = 0; $i < 10; $i++) {
                $status    = fake()->randomElement(['open', 'invoiced']);
                $openedBy  = $users->random();
                $closedBy  = $status === 'invoiced' ? $users->random() : null;

                $order = EloquentOrder::factory()
                    ->forRestaurant($restaurant)
                    ->forTable($tables->random())
                    ->forUser($openedBy)
                    ->create([
                        'status'             => $status,
                        'closed_by_user_id'  => $closedBy?->id,
                        'closed_at'          => $status === 'invoiced' ? now() : null,
                    ]);

                for ($line = 0; $line < random_int(2, 5); $line++) {
                    $product = $products->random();
                    $tax     = $taxes->get($product->tax_id);

                    EloquentOrderLine::create([
                        'uuid'           => (string) Str::uuid(),
                        'restaurant_id'  => $restaurant->id,
                        'order_id'       => $order->id,
                        'product_id'     => $product->id,
                        'user_id'        => $users->random()->id,
                        'quantity'       => random_int(1, 4),
                        'price'          => $product->price,
                        'tax_percentage' => $tax?->percentage ?? 21,
                    ]);
                }
            }
        }
    }
}
