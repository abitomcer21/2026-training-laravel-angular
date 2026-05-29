<?php

namespace App\Sales\Application\Command;

final readonly class CancelSalesLineCommand
{
    private function __construct(
        public string $salesLineId,
    ) {}

    public static function create(string $salesLineId): self
    {
        return new self(salesLineId: $salesLineId);
    }
}
