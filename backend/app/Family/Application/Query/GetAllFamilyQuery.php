<?php

namespace App\Family\Application\Query;

final readonly class GetAllFamilyQuery
{
    public function __construct(
        public int $restaurantId,
    ) {}
}