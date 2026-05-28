<?php

namespace App\Zones\Application\Query;

final readonly class GetAllZonesQuery
{
    public function __construct(
        public int $restaurantId,
    ) {}
}