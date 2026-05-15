<?php

namespace App\Family\Application\Command;

final readonly class DeleteFamilyCommand
{
    public function __construct(
        public string $id,
        public int $restaurantId,
    ) {}
}