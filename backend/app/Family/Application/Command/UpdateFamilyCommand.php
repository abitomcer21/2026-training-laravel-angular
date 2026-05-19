<?php

namespace App\Family\Application\Command;

use App\Family\Domain\ValueObject\FamilyName;
use App\Shared\Domain\ValueObject\Uuid;

final readonly class UpdateFamilyCommand
{
    private function __construct(
        public Uuid $id,
        public ?FamilyName $name,
        public ?bool $active,
    ) {}

    public static function create(string $id, ?string $name, ?bool $active): self
    {
        return new self(
            id:     Uuid::create($id),
            name:   $name !== null ? FamilyName::create($name) : null,
            active: $active,
        );
    }
}