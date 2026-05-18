<?php

namespace App\Sales\Application\GetTodaySales;

use App\Sales\Domain\Interfaces\SalesRepositoryInterface;
use Illuminate\Support\Facades\DB;

class GetTodaySalesHandler
{
    public function __construct(
        private SalesRepositoryInterface $salesRepository
    ) {}

    public function __invoke(GetTodaySalesQuery $query): array
    {
        $today = date('Y-m-d');
        
        $sales = DB::table('sales')
            ->join('orders', 'sales.order_id', '=', 'orders.id')
            ->join('users', 'sales.user_id', '=', 'users.id')
            ->whereDate('sales.created_at', $today)
            ->select(
                'sales.uuid as id',
                'sales.ticket_number',
                'sales.total',
                'sales.payment_method',
                'sales.user_id',
                'users.name as user_name',
                'sales.created_at'
            )
            ->orderBy('sales.created_at', 'desc')
            ->get()
            ->map(function ($sale) {
                return [
                    'id' => $sale->id,
                    'ticket_number' => $sale->ticket_number,
                    'total' => $sale->total / 100, // Convertir de céntimos a euros
                    'payment_method' => $sale->payment_method ?? 'efectivo',
                    'user_id' => $sale->user_id,
                    'user_name' => $sale->user_name,
                    'created_at' => $sale->created_at
                ];
            })
            ->toArray();

        return [
            'data' => $sales,
            'message' => 'Ventas del día obtenidas correctamente'
        ];
    }
}