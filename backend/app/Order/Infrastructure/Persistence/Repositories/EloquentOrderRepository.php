<?php

namespace App\Order\Infrastructure\Persistence\Repositories;

use App\Order\Domain\Entity\Order;
use App\Order\Domain\Interfaces\OrderRepositoryInterface;
use App\Order\Infrastructure\Persistence\Models\EloquentOrder;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class EloquentOrderRepository implements OrderRepositoryInterface
{
    public function __construct(
        private EloquentOrder $model,
    ) {}

    public function save(Order $order): void
    {
        $tableId = $this->getTableIdByName(
            $order->restaurantId(),
            $order->tableId()
        );

        $orderModel = $this->model->newQuery()->updateOrCreate(
            ['uuid' => $order->id()->value()],
            [
                'restaurant_id' => $order->restaurantId(),
                'table_id' => $tableId,
                'opened_by_user_id' => $order->openedByUserId(),
                'closed_by_user_id' => $order->closedByUserId(),
                'status' => $order->status()->value(),
                'diners' => $order->diners(),
                'opened_at' => $order->openedAt()->value(),
                'closed_at' => $order->closedAt()?->value(),
                'created_at' => $order->createdAt()->value(),
                'updated_at' => $order->updatedAt()->value(),
            ]
        );

        foreach ($order->orderLines() as $orderLine) {
            DB::table('order_lines')->updateOrInsert(
                ['uuid' => $orderLine->id()->value()],
                [
                    'uuid' => $orderLine->id()->value(),
                    'restaurant_id' => $orderLine->restaurantId(),
                    'order_id' => $orderModel->id,
                    'product_id' => $this->getProductIdByUuid((string) $orderLine->productId()),
                    'user_id' => $orderLine->userId(),
                    'quantity' => $orderLine->quantity(),
                    'price' => $orderLine->price() * 100,
                    'tax_percentage' => $orderLine->taxPercentage(),
                    'created_at' => $orderLine->createdAt()->value(),
                    'updated_at' => $orderLine->updatedAt()->value(),
                ]
            );
        }
    }

    public function findById(string $id): ?Order
    {
        $model = $this->model->newQuery()->where('uuid', $id)->first();

        if ($model === null) {
            return null;
        }

        $tableName = $this->getTableNameById(
            $model->restaurant_id,
            $model->table_id
        );

        return Order::fromPersistence(
            $model->uuid,
            $model->restaurant_id,
            $tableName,
            $model->opened_by_user_id,
            $model->closed_by_user_id,
            $model->status,
            $model->diners,
            $model->opened_at->toDateTimeImmutable(),
            $model->closed_at?->toDateTimeImmutable(),
            $model->created_at->toDateTimeImmutable(),
            $model->updated_at->toDateTimeImmutable(),
            $model->deleted_at?->toDateTimeImmutable()
        );
    }

    private function getTableIdByName(int $restaurantId, string $tableUuid): int
    {
        $tableId = DB::table('tables')
            ->where('restaurant_id', $restaurantId)
            ->where('uuid', $tableUuid)
            ->value('id');

        if ($tableId) {
            return (int) $tableId;
        }

        throw new InvalidArgumentException(
            "Table with uuid '{$tableUuid}' not found for restaurant {$restaurantId}"
        );
    }

    private function getTableNameById(int $restaurantId, int $tableId): string
    {
        $tableName = DB::table('tables')
            ->where('restaurant_id', $restaurantId)
            ->where('id', $tableId)
            ->value('name');

        if ($tableName) {
            return $tableName;
        }

        throw new InvalidArgumentException(
            "Table ID {$tableId} not found for restaurant {$restaurantId}"
        );
    }

    private function getProductIdByUuid(string $productUuid): int
    {
        $productId = DB::table('products')
            ->where('uuid', $productUuid)
            ->value('id');

        if ($productId) {
            return (int) $productId;
        }

        throw new InvalidArgumentException(
            "Product with uuid '{$productUuid}' not found"
        );
    }
}
