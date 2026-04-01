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
        public ?string $closedByUserId,
        public string $status,
        public int $diners,
        public string $openedAt,
        public ?string $closedAt,
        public string $createdAt,
        public string $updatedAt,
    ) {}

    public static function create(Order $order): self
    {
        return new self(
            id: $order->id()->value(),
            restaurantId: $order->restaurantId(),
            tableId: $order->tableId(),
            openedByUserId: $order->openedByUserId(),
            closedByUserId: $order->closedByUserId(),
            status: $order->status()->value(),
            diners: $order->diners(),
            openedAt: $order->openedAt()->format(\DateTimeInterface::ATOM),
            closedAt: $order->closedAt()?->format(\DateTimeInterface::ATOM),
            createdAt: $order->createdAt()->format(\DateTimeInterface::ATOM),
            updatedAt: $order->updatedAt()->format(\DateTimeInterface::ATOM),
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'restaurant_id' => $this->restaurantId,
            'table_id' => $this->tableId,
            'opened_by_user_id' => $this->openedByUserId,
            'closed_by_user_id' => $this->closedByUserId,
            'status' => $this->status,
            'diners' => $this->diners,
            'opened_at' => $this->openedAt,
            'closed_at' => $this->closedAt,
            'created_at' => $this->createdAt,
            'updated_at' => $this->updatedAt,
        ];
    }
}
