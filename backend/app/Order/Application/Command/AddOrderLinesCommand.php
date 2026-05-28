<?php

namespace App\Order\Application\Command;

final readonly class AddOrderLinesCommand
{
    private function __construct(
        public string $orderId,
        public array $orderLinesData,
    ) {}

    public static function create(string $orderId, array $orderLinesData): self
    {
        return new self(
            orderId:        $orderId,
            orderLinesData: $orderLinesData,
        );
    }
}
