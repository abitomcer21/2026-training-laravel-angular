<?php

namespace App\Tables\Application\Command;

use App\Tables\Domain\ValueObject\TableName;

final readonly class CreateTableCommand
{
    private function __construct(
        public TableName $name,
        public int $zoneId,
        public int $restaurantId,
    ) {}

    public static function create(string $name, int $zoneId, int $restaurantId): self
    {
        return new self(
            name:         TableName::create($name),
            zoneId:       $zoneId,
            restaurantId: $restaurantId,
        );
    }
}