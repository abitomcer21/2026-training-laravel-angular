<?php

namespace App\User\Application\Query;

final readonly class GetMeQuery
{
    public function __construct(
        public string $uuid,
    ) {}
}
