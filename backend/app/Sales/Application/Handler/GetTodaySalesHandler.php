<?php

namespace App\Sales\Application\Handler;

use App\Sales\Application\Query\GetTodaySalesQuery;
use App\Sales\Application\Response\GetTodaySalesResponse;
use App\Sales\Domain\Interfaces\SalesRepositoryInterface;
use Illuminate\Support\Facades\DB;

class GetTodaySalesHandler
{
    public function __construct(
        private SalesRepositoryInterface $salesRepository,
    ) {}

    public function __invoke(GetTodaySalesQuery $query): GetTodaySalesResponse
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
                'sales.user_id',
                'users.name as user_name',
                'sales.created_at',
            )
            ->orderBy('sales.created_at', 'desc')
            ->get()
            ->map(static fn ($sale): array => [
                'id'            => $sale->id,
                'ticket_number' => $sale->ticket_number,
                'total'         => $sale->total / 100,
                'user_id'       => $sale->user_id,
                'user_name'     => $sale->user_name,
                'created_at'    => $sale->created_at,
            ])
            ->toArray();

        return GetTodaySalesResponse::create($sales);
    }
}

