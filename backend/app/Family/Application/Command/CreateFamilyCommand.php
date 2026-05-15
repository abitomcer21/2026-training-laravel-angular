<?php

namespace App\Family\Application\Command;

final readonly class CreateFamilyCommand
{
    public function __construct(
        public string $name,
        public bool $active,
        public int $restaurantId,
    ) {}
}