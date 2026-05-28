<?php

namespace App\Tax\Application\Query;

final readonly class GetAllTaxQuery
{
    public function __construct(
        public int $restaurantId,
    ) {}
}