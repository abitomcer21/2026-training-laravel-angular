<?php

namespace App\Tables\Application\CreateTable;

use App\Tables\Domain\Entity\Table;

final readonly class CreateTableResponse
{
    public function __construct(
        public string $id,
        public int $zoneId,
        public string $name,
        public int $restaurantId,
        public string $createdAt,
        public string $updatedAt,
    ) {}

    public static function create(Table $table): self
    {
        return new self(
            id: $table->id()->value(),
            zoneId: $table->zoneId(),
            name: $table->name(),
            restaurantId: $table->restaurantId(),
            createdAt: $table->createdAt()->format(\DateTimeInterface::ATOM),
            updatedAt: $table->updatedAt()->format(\DateTimeInterface::ATOM),
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'zone_id' => $this->zoneId,
            'name' => $this->name,
            'restaurant_id' => $this->restaurantId,
            'created_at' => $this->createdAt,
            'updated_at' => $this->updatedAt,
        ];
    }
}