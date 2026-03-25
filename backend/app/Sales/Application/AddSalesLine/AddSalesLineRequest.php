<?php

namespace App\Sales\Application\AddSalesLine;

class AddSalesLineRequest
{
    public function __construct(
        public readonly string $saleId,
        public readonly string $orderLineId,
        public readonly string $userId,
        public readonly int $quantity,
        public readonly int $price,
        public readonly int $taxPercentage,
    ) {}
}
