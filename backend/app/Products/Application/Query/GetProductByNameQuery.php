<?php

namespace App\Products\Application\Query;

final readonly class GetProductByNameQuery
{
    public function __construct(
        public string $name,
        public int $restaurantId,
    ) {}
}