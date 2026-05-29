<?php

namespace App\Sales\Infrastructure\Persistence\Repositories;

use App\Sales\Domain\Entity\Sales;
use App\Sales\Domain\Entity\SalesLine;
use App\Sales\Domain\Interfaces\SalesRepositoryInterface;
use App\Sales\Infrastructure\Persistence\Models\EloquentSales;
use App\Sales\Infrastructure\Persistence\Models\EloquentSalesLine;
use App\User\Infrastructure\Persistence\Models\EloquentUser;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class EloquentSalesRepository implements SalesRepositoryInterface
{
    public function __construct(
        private EloquentSales $model,
        private EloquentSalesLine $salesLineModel,
    ) {}

    public function save(Sales $sales): void
    {
        $orderId = $this->getOrderIdByUuid($sales->orderId()->value());

        $salesModel = $this->model->newQuery()->updateOrCreate(
            ['uuid' => $sales->id()->value()],
            [
                'restaurant_id' => $sales->restaurantId(),
                'order_id' => $orderId,
                'user_id' => (int)$sales->userId(),
                'ticket_number' => $sales->ticketNumber()?->value(),
                'value_date' => $sales->valueDate()?->value(),
                'total' => $sales->total()?->cents(),
                'created_at' => $sales->createdAt()->value(),
                'updated_at' => $sales->updatedAt()->value(),
            ]
        );

        foreach ($sales->salesLines() as $salesLine) {
            $orderLineId = $this->getOrderLineIdByUuid($salesLine->orderLineId()->value());

            $userId = (int)$salesLine->userId();

            DB::table('sales_lines')->updateOrInsert(
                ['uuid' => $salesLine->id()->value()],
                [
                    'uuid' => $salesLine->id()->value(),
                    'restaurant_id' => $salesLine->restaurantId(),
                    'sale_id' => $salesModel->id,
                    'order_line_id' => $orderLineId,
                    'user_id' => $userId,
                    'quantity' => $salesLine->quantity(),
                    'price' => $salesLine->price()->cents(),
                    'tax_percentage' => (int)$salesLine->taxPercentage()->value(),
                    'created_at' => $salesLine->createdAt()->value(),
                    'updated_at' => $salesLine->updatedAt()->value(),
                ]
            );
        }
    }

    public function findById(string $id): ?Sales
    {
        $model = $this->model->newQuery()->where('uuid', $id)->first();

        if ($model === null) {
            return null;
        }

        return Sales::fromPersistence(
            $model->uuid,
            $model->restaurant_id,
            $this->getOrderUuidById($model->order_id),
            (string)$model->user_id,
            $model->ticket_number,
            $model->value_date?->format('Y-m-d H:i:s'),
            $model->total,
            $model->created_at->toDateTimeImmutable(),
            $model->updated_at->toDateTimeImmutable(),
        );
    }

    private function getOrderIdByUuid(string $orderUuid): int
    {
        $orderId = DB::table('orders')
            ->where('uuid', $orderUuid)
            ->value('id');

        if ($orderId) {
            return (int)$orderId;
        }

        throw new InvalidArgumentException(
            "Order with uuid '{$orderUuid}' not found"
        );
    }

    private function getOrderUuidById(int $orderId): string
    {
        $orderUuid = DB::table('orders')
            ->where('id', $orderId)
            ->value('uuid');

        if ($orderUuid) {
            return $orderUuid;
        }

        throw new InvalidArgumentException(
            "Order with id {$orderId} not found"
        );
    }

    private function getOrderLineIdByUuid(string $orderLineUuid): int
    {
        $orderLineId = DB::table('order_lines')
            ->where('uuid', $orderLineUuid)
            ->value('id');

        if ($orderLineId) {
            return (int)$orderLineId;
        }

        throw new InvalidArgumentException(
            "OrderLine with uuid '{$orderLineUuid}' not found"
        );
    }

    private function getUserIdByUuid(string $userUuid): int
    {
        $userId = EloquentUser::where('uuid', $userUuid)->value('id');

        if ($userId) {
            return (int)$userId;
        }

        throw new InvalidArgumentException(
            "User with uuid '{$userUuid}' not found"
        );
    }

    public function saveSalesLine(SalesLine $line): void
    {
        $orderLineId = $this->getOrderLineIdByUuid($line->orderLineId()->value());
        $userId = (int)$line->userId();
        $saleId = $this->model->where('uuid', $line->saleId()->value())->value('id');

        if ($saleId === null) {
            throw new InvalidArgumentException(
                "Sale with uuid '{$line->saleId()->value()}' not found"
            );
        }

        $this->salesLineModel->newQuery()->updateOrInsert(
            ['uuid' => $line->id()->value()],
            [
                'restaurant_id' => $line->restaurantId(),
                'sale_id' => $saleId,
                'order_line_id' => $orderLineId,
                'user_id' => $userId,
                'quantity' => $line->quantity(),
                'price' => $line->price()->cents(),
                'tax_percentage' => (int)$line->taxPercentage()->value(),
                'created_at' => $line->createdAt()->value(),
                'updated_at' => $line->updatedAt()->value(),
            ]
        );
    }

    public function findSalesLinesBySaleId(string $saleId): array
    {
        $sale = $this->model->newQuery()->where('uuid', $saleId)->first();

        if (!$sale) {
            return [];
        }

        $lines = DB::table('sales_lines')
            ->where('sale_id', $sale->id)
            ->get();

        return $lines->map(function ($line) {
            return SalesLine::fromPersistence(
                $line->uuid,
                $line->restaurant_id,
                $this->model->find($line->sale_id)->uuid,
                $this->getOrderLineUuidById($line->order_line_id),
                (string)$line->user_id,
                $line->quantity,
                $line->price,
                $line->tax_percentage,
                $line->created_at->toDateTimeImmutable(),
                $line->updated_at->toDateTimeImmutable(),
                $line->deleted_at?->toDateTimeImmutable(),
            );
        })->toArray();
    }

    public function findSalesLineById(string $id): ?SalesLine
    {
        $line = DB::table('sales_lines')
            ->where('uuid', $id)
            ->first();

        if (!$line) {
            return null;
        }

        return SalesLine::fromPersistence(
            $line->uuid,
            $line->restaurant_id,
            $this->model->find($line->sale_id)->uuid,
            $this->getOrderLineUuidById($line->order_line_id),
            (string)$line->user_id,
            $line->quantity,
            $line->price,
            $line->tax_percentage,
            new \DateTimeImmutable($line->created_at),
            new \DateTimeImmutable($line->updated_at),
            $line->deleted_at !== null ? new \DateTimeImmutable($line->deleted_at) : null,
        );
    }

    private function getOrderLineUuidById(int $orderLineId): string
    {
        $uuid = DB::table('order_lines')
            ->where('id', $orderLineId)
            ->value('uuid');

        if ($uuid) {
            return $uuid;
        }

        throw new InvalidArgumentException(
            "OrderLine with id {$orderLineId} not found"
        );
    }

    public function nextTicketNumber(): int
    {
        return (int)($this->model->newQuery()->max('ticket_number') ?? 0) + 1;
    }

    public function cancelSale(string $id): void
    {
        $this->model->newQuery()->where('uuid', $id)->delete();
    }

    public function cancelSalesLine(string $id): void
    {
        $this->salesLineModel->newQuery()->where('uuid', $id)->delete();
    }
    public function updateSaleTotal(string $saleId, int $totalCents): void
    {
        $this->model->newQuery()->where('uuid', $saleId)->update([
            'total'      => $totalCents,
            'updated_at' => now(),
        ]);
    }
    public function getTodaySales(string $date): array
    {
        $sales = DB::table('sales')
            ->join('orders', 'sales.order_id', '=', 'orders.id')
            ->join('users', 'sales.user_id', '=', 'users.id')
            ->whereDate('sales.created_at', $date)
            ->whereNull('sales.deleted_at')
            ->select(
                'sales.id as internal_id',
                'sales.uuid as id',
                'sales.ticket_number',
                'sales.total',
                'sales.user_id',
                'users.name as user_name',
                'sales.created_at'
            )
            ->orderBy('sales.created_at', 'desc')
            ->get()
            ->map(function ($sale) {
                $lines = DB::table('sales_lines')
                    ->join('order_lines', 'sales_lines.order_line_id', '=', 'order_lines.id')
                    ->join('products', 'order_lines.product_id', '=', 'products.id')
                    ->where('sales_lines.sale_id', $sale->internal_id)
                    ->whereNull('sales_lines.deleted_at')
                    ->select(
                        'sales_lines.uuid as id',
                        'products.name as product_name',
                        'sales_lines.quantity',
                        'sales_lines.price',
                        'sales_lines.tax_percentage',
                    )
                    ->get()
                    ->map(fn($line) => [
                        'id' => $line->id,
                        'product_name' => $line->product_name,
                        'quantity' => $line->quantity,
                        'price' => $line->price / 100,
                        'tax_percentage' => $line->tax_percentage,
                    ])
                    ->toArray();

                return [
                    'id' => $sale->id,
                    'ticket_number' => $sale->ticket_number,
                    'total' => $sale->total / 100,
                    'payment_method' => 'efectivo',
                    'user_id' => $sale->user_id,
                    'user_name' => $sale->user_name,
                    'created_at' => $sale->created_at,
                    'lines' => $lines,
                ];
            })
            ->toArray();

        return $sales;
    }
}
