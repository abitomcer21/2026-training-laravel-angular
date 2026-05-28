<?php

namespace App\Order\Application\Response;

use App\Order\Domain\Entity\Order;

final readonly class CreateOrderResponse
{
    private function __construct(
        private string $id,
        private int $restaurantId,
        private string $tableId,
        private string $openedByUserId,
        private string $status,
        private int $diners,
        private string $createdAt,
        private string $updatedAt,
        private array $orderLines,
    ) {}

    public static function create(Order $order): self
    {
        $lines = array_map(static fn ($line): array => [
            'id'             => $line->id()->value(),
            'product_id'     => $line->productId(),
            'user_id'        => $line->userId(),
            'quantity'       => $line->quantity(),
            'price'          => $line->price()->cents(),
            'tax_percentage' => $line->taxPercentage()->value(),
            'created_at'     => $line->createdAt()->format(\DateTimeInterface::ATOM),
            'updated_at'     => $line->updatedAt()->format(\DateTimeInterface::ATOM),
        ], $order->orderLines());

        return new self(
            id:             $order->id()->value(),
            restaurantId:   $order->restaurantId(),
            tableId:        $order->tableId(),
            openedByUserId: $order->openedByUserId(),
            status:         $order->status()->value(),
            diners:         $order->diners(),
            createdAt:      $order->createdAt()->format(\DateTimeInterface::ATOM),
            updatedAt:      $order->updatedAt()->format(\DateTimeInterface::ATOM),
            orderLines:     $lines,
        );
    }

    public function toArray(): array
    {
        return [
            'id'                 => $this->id,
            'restaurant_id'      => $this->restaurantId,
            'table_id'           => $this->tableId,
            'opened_by_user_id'  => $this->openedByUserId,
            'status'             => $this->status,
            'diners'             => $this->diners,
            'created_at'         => $this->createdAt,
            'updated_at'         => $this->updatedAt,
            'order_lines'        => $this->orderLines,
        ];
    }
}
