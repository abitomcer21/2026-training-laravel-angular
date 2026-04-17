<?php

namespace App\Zones\Application\GetZonesById;

use App\Zones\Domain\Entity\Zones;

final readonly class GetZonesByIdResponse
{
    public function __construct(
        public string $id,
        public string $name,
        public int $restaurantId,
        public string $createdAt,
        public string $updatedAt,

    ) {}

    public static function create(Zones $zones): self
    {
        return new self(
            id: $zones->id()->value(),
            name: $zones->name(),
            restaurantId: $zones->restaurantId(),
            createdAt: $zones->createdAt()->format(\DateTimeInterface::ATOM),
            updatedAt: $zones->updatedAt()->format(\DateTimeInterface::ATOM),
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
