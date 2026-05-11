<?php

namespace App\Order\Application\CreateOrder;

use App\Order\Domain\Entity\Order;

final readonly class CreateOrderResponse
{
    public function __construct(
        public string $id,
        public int $restaurantId,
        public string $tableId,
        public string $openedByUserId,
        public string $status,
        public int $diners,
        public string $createdAt,
        public string $updatedAt,
        public array $orderLines = [],
    ) {}

    public static function create(Order $order): self
    {
        $lines = array_map(function ($line) {
            return [
                'id' => $line->id()->value(),
                'product_id' => $line->productId(),
                'user_id' => $line->userId(),
                'quantity' => $line->quantity(),
                'price' => $line->price()->cents(),
                'tax_percentage' => $line->taxPercentage()->value(),
                'created_at' => $line->createdAt()->format(\DateTimeInterface::ATOM),
                'updated_at' => $line->updatedAt()->format(\DateTimeInterface::ATOM),
            ];
        }, $order->orderLines());

        return new self(
            id: $order->id()->value(),
            restaurantId: $order->restaurantId(),
            tableId: $order->tableId(),
            openedByUserId: $order->openedByUserId(),
            status: $order->status()->value(),
            diners: $order->diners(),
            createdAt: $order->createdAt()->format(\DateTimeInterface::ATOM),
            updatedAt: $order->updatedAt()->format(\DateTimeInterface::ATOM),
            orderLines: $lines,
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'restaurant_id' => $this->restaurantId,
            'table_id' => $this->tableId,
            'opened_by_user_id' => $this->openedByUserId,
            'status' => $this->status,
            'diners' => $this->diners,
            'created_at' => $this->createdAt,
            'updated_at' => $this->updatedAt,
            'order_lines' => $this->orderLines,
        ];
    }
}
