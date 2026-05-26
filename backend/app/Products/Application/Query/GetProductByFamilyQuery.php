<?php

namespace App\Products\Application\Query;

final readonly class GetProductByFamilyQuery
{
    public function __construct(
        public string $familyId,
        public int $restaurantId,
    ) {}
}