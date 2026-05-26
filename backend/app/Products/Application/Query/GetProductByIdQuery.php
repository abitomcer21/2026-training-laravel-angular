<?php

namespace App\Products\Application\Query;

final readonly class GetProductByIdQuery
{
    public function __construct(
        public string $id,
        public int $restaurantId,
    ) {}
}