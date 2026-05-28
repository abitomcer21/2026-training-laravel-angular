<?php

namespace App\Tables\Application\Query;

final readonly class GetTableByIdQuery
{
    public function __construct(
        public string $id,
        public int $restaurantId,
    ) {}
}