<?php

namespace App\Tables\Application\Command;

use App\Tables\Domain\ValueObject\TableName;
use App\Shared\Domain\ValueObject\Uuid;

final readonly class UpdateTableCommand
{
    private function __construct(
        public Uuid $id,
        public ?TableName $name,
        public int $restaurantId,
    ) {}

    public static function create(string $id, ?string $name, int $restaurantId): self
    {
        return new self(
            id:           Uuid::create($id),
            name:         $name !== null ? TableName::create($name) : null,
            restaurantId: $restaurantId,
        );
    }
}