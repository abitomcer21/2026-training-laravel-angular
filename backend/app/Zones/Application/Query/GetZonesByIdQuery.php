<?php

namespace App\Zones\Application\Query;

final readonly class GetZonesByIdQuery
{
    public function __construct(
        public string $id,
        public int $restaurantId,
    ) {}
}