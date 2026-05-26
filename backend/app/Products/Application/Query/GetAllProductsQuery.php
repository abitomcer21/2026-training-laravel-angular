<?php

namespace App\Products\Application\Query;

final readonly class GetAllProductsQuery
{
    public function __construct(
        public int $restaurantId,
    ) {}
}