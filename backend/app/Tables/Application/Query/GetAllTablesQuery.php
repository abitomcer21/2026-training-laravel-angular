<?php

namespace App\Tables\Application\Query;

final readonly class GetAllTablesQuery
{
    public function __construct(
        public int $restaurantId,
    ) {}
}