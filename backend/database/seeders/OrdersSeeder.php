<?php

namespace Database\Seeders;

use App\Order\Infrastructure\Persistence\Models\EloquentOrder;
use App\Order\Infrastructure\Persistence\Models\EloquentOrderLine;
use App\Products\Infrastructure\Persistence\Models\EloquentProducts;
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

        $restaurant = EloquentRestaurant::query()->first();

        if ($restaurant === null) {
            return;
        }

        $tables = EloquentTables::query()->where('restaurant_id', $restaurant->id)->get();
        $products = EloquentProducts::query()->where('restaurant_id', $restaurant->id)->get();
        $taxes = EloquentTaxes::query()->where('restaurant_id', $restaurant->id)->get()->keyBy('id');
        $users = EloquentUser::query()
            ->where('restaurant_id', $restaurant->id)
            ->whereIn('role', ['waiter', 'chef'])
            ->get();

        if ($tables->isEmpty() || $products->isEmpty() || $users->isEmpty()) {
            return;
        }

        for ($i = 0; $i < 10; $i++) {
            $status = fake()->randomElement(['open', 'closed']);
            $openedBy = $users->random();
            $closedBy = $status === 'closed' ? $users->random() : null;

            $order = EloquentOrder::factory()
                ->forRestaurant($restaurant)
                ->forTable($tables->random())
                ->forUser($openedBy)
                ->create([
                    'status' => $status,
                    'closed_by_user_id' => $closedBy?->id,
                    'closed_at' => $status === 'closed' ? now() : null,
                ]);

            $lines = random_int(2, 5);

            for ($line = 0; $line < $lines; $line++) {
                $product = $products->random();
                $tax = $taxes->get($product->tax_id);
                $lineUser = $users->random();

                EloquentOrderLine::create([
                    'uuid' => (string) Str::uuid(),
                    'restaurant_id' => $restaurant->id,
                    'order_id' => $order->id,
                    'product_id' => $product->id,
                    'user_id' => $lineUser->id,
                    'quantity' => random_int(1, 4),
                    'price' => $product->price,
                    'tax_percentage' => $tax?->percentage ?? 21,
                ]);
            }
        }
    }
}
