<?php

namespace App\Sales\Application\GetTodaySales;

class GetTodaySalesQuery
{
    public function __construct(
        public readonly ?string $restaurantId = null
    ) {}
}