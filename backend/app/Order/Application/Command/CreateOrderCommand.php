<?php

namespace App\Order\Application\Command;

use App\Order\Domain\ValueObject\OrderStatus;

final readonly class CreateOrderCommand
{
    private function __construct(
        public int $restaurantId,
        public string $tableId,
        public string $openedByUserId,
        public ?string $closedByUserId,
        public OrderStatus $status,
        public int $diners,
        public array $orderLinesData,
    ) {}

    public static function create(
        int $restaurantId,
        string $tableId,
        string $openedByUserId,
        ?string $closedByUserId,
        string $status,
        int $diners,
        array $orderLinesData = [],
    ): self {
        return new self(
            restaurantId:    $restaurantId,
            tableId:         $tableId,
            openedByUserId:  $openedByUserId,
            closedByUserId:  $closedByUserId,
            status:          OrderStatus::create($status),
            diners:          $diners,
            orderLinesData:  $orderLinesData,
        );
    }
}
