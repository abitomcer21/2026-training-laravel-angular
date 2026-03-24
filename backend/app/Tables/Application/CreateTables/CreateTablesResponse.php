<?php

namespace App\Tables\Application\CreateTables;

use App\Tables\Domain\Entity\Tables;

final readonly class CreateTablesResponse
{
    public function __construct(
        public string $id,
        public string $zoneId,
        public string $name,
        public string $createdAt,
        public string $updatedAt,
    ) {}

    public static function create(Tables $tables): self
    {
        return new self(
            id: $tables->id()->value(),
            zoneId: $tables->zoneId()->value(),
            name: $tables->name(),
            createdAt: $tables->createdAt()->format(\DateTimeInterface::ATOM),
            updatedAt: $tables->updatedAt()->format(\DateTimeInterface::ATOM),
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'zone_id' => $this->zoneId,
            'name' => $this->name,
            'created_at' => $this->createdAt,
            'updated_at' => $this->updatedAt,
        ];
    }
}
