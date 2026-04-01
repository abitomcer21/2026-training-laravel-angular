<?php

namespace App\Zones\Application\CreateZones;

use App\Zones\Domain\Entity\Zones;

final readonly class CreateZonesResponse
{
    public function __construct(
        public string $id,
        public string $name,
        public int $restaurantId,
        public string $createdAt,
        public string $updatedAt,
    ) {}

    public static function create(Zones $zone): self
    {
        return new self(
            id: $zone->id()->value(),
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
            'name' => $this->name,
            'restaurant_id' => $this->restaurantId,
            'created_at' => $this->createdAt,
            'updated_at' => $this->updatedAt,
        ];
    }
}
