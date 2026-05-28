<?php

namespace App\User\Application\Query;

final readonly class GetUserByEmailQuery
{
    public function __construct(
        public string $email,
    ) {}
}
