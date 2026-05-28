<?php

namespace App\User\Application\Query;

final readonly class GetAllUsersQuery
{
    public function __construct(
        public ?int $restaurantId,
    ) {}
}
