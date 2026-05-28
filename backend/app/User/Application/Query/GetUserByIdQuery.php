<?php

namespace App\User\Application\Query;

final readonly class GetUserByIdQuery
{
    public function __construct(
        public string $id,
    ) {}
}
