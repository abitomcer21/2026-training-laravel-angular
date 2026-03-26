<?php

namespace Database\Seeders;

use App\Order\Infrastructure\Persistence\Models\EloquentOrder;
use App\Order\Infrastructure\Persistence\Models\EloquentOrderLine;
use App\Sales\Infrastructure\Persistence\Models\EloquentSales;
use App\Sales\Infrastructure\Persistence\Models\EloquentSalesLine;
use App\User\Infrastructure\Persistence\Models\EloquentUser;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class SalesSeeder extends Seeder
{
    public function run(): void
    {
        if (EloquentSales::query()->exists()) {
            return;
        }

        $closedOrders = EloquentOrder::query()->where('status', 'closed')->get();

        if ($closedOrders->isEmpty()) {
            return;
        }

        $ticketNumber = 1000;

        foreach ($closedOrders as $order) {
            $user = EloquentUser::query()->find($order->closed_by_user_id ?? $order->opened_by_user_id);

            if ($user === null) {
                continue;
            }

            $orderLines = EloquentOrderLine::query()->where('order_id', $order->id)->get();

            if ($orderLines->isEmpty()) {
                continue;
            }

            $total = $orderLines->sum(fn (EloquentOrderLine $line) => $line->quantity * $line->price);

            $sale = EloquentSales::factory()
                ->forRestaurant($order->restaurant_id)
                ->forOrder($order)
                ->forUser($user)
                ->create([
                    'ticket_number' => $ticketNumber++,
                    'value_date' => now(),
                    'total' => $total,
                ]);

            foreach ($orderLines as $orderLine) {
                EloquentSalesLine::create([
                    'uuid' => (string) Str::uuid(),
                    'restaurant_id' => $order->restaurant_id,
                    'sale_id' => $sale->id,
                    'order_line_id' => $orderLine->id,
                    'user_id' => $orderLine->user_id,
                    'quantity' => $orderLine->quantity,
                    'price' => $orderLine->price,
                    'tax_percentage' => $orderLine->tax_percentage,
                ]);
            }
        }
    }
}
