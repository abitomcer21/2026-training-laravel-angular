<?php

namespace App\Sales\Application\Command;

final readonly class CancelSaleCommand
{
    private function __construct(
        public string $saleId,
    ) {}

    public static function create(string $saleId): self
    {
        return new self(saleId: $saleId);
    }
}
