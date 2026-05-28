<?php

namespace App\Sales\Application\Query;

final readonly class GetTodaySalesQuery
{
    public function __construct(
        public ?int $restaurantId = null,
    ) {}
}
