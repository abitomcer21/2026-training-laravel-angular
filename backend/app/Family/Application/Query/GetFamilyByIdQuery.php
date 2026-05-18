<?php

namespace App\Family\Application\Query;

final readonly class GetFamilyByIdQuery
{
    public function __construct(
        public string $id,
        public int $restaurantId,
    ) {}
}