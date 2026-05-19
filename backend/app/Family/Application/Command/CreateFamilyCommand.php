<?php

namespace App\Family\Application\Command;

use App\Family\Domain\ValueObject\FamilyName;

final readonly class CreateFamilyCommand
{
    private function __construct(
        public FamilyName $name,
        public bool $active,
        public int $restaurantId,
    ) {}

    public static function create(string $name, bool $active, int $restaurantId): self
    {
        return new self(
            name:         FamilyName::create($name),
            active:       $active,
            restaurantId: $restaurantId,
        );
    }
}