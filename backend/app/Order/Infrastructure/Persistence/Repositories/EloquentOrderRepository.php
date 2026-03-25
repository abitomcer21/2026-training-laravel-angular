<?php

namespace App\Order\Infrastructure\Persistence\Repositories;

use App\Order\Domain\Entity\Order;
use App\Order\Domain\Interfaces\OrderRepositoryInterface;
use App\Order\Infrastructure\Persistence\Models\EloquentOrder;

class EloquentOrderRepository implements OrderRepositoryInterface
{
    public function __construct(
        private EloquentOrder $model,
    ) {}

    public function save(Order $order): void
    {
        $this->model->newQuery()->updateOrCreate(
            ['uuid' => $order->id()->value()],
            [
                'restaurant_id' => $order->restaurantId(),
                'table_id' => $order->tableId(),
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
    }

    public function findById(string $id): ?Order
    {
        $model = $this->model->newQuery()->where('uuid', $id)->first();

        if ($model === null) {
            return null;
        }

        return Order::fromPersistence(
            $model->uuid,
            $model->restaurant_id,
            $model->table_id,
            $model->opened_by_user_id,
            $model->closed_by_user_id,
            $model->status,
            $model->diners,
            $model->opened_at->toDateTimeImmutable(),
            $model->closed_at?->toDateTimeImmutable(),
            $model->created_at->toDateTimeImmutable(),
            $model->updated_at->toDateTimeImmutable(),
            $model->deleted_at?->toDateTimeImmutable(),
        );
    }
}
