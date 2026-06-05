<?php

namespace App\Tax\Application\Query;

final readonly class GetTaxByIdQuery
{
    public function __construct(
        public string $id,
        public int $restaurantId,
    ) {}
}   