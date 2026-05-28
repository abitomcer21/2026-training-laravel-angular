<?php

namespace App\Sales\Application\Command;

final readonly class CreateSaleCommand
{
    private function __construct(
        public string $orderId,
        public string $userId,
    ) {}

    public static function create(string $orderId, string $userId): self
    {
        return new self(
            orderId: $orderId,
            userId:  $userId,
        );
    }
}
