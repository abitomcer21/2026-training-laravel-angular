<?php

namespace App\Zones\Application\CreateZones;

use App\Zones\Domain\Entity\Zones;

final readonly class CreateZonesResponse
{
    public function __construct(
        public string $id,
        public ?int $databaseId,
        public string $name,
        public int $restaurantId,
        public string $createdAt,
        public string $updatedAt,
    ) {}

    public static function create(Zones $zone, ?int $databaseId = null): self
    {
        return new self(
            id: $zone->id()->value(),
            databaseId: $databaseId,
            name: $zone->name(),
            restaurantId: $zone->restaurantId(),
            createdAt: $zone->createdAt()->format(\DateTimeInterface::ATOM),
            updatedAt: $zone->updatedAt()->format(\DateTimeInterface::ATOM),
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'database_id' => $this->databaseId,
            'name' => $this->name,
            'restaurant_id' => $this->restaurantId,
            'created_at' => $this->createdAt,
            'updated_at' => $this->updatedAt,
        ];
    }
}
