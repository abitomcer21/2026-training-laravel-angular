<?php

namespace App\Zones\Application\GetAllZones;

use App\Zones\Domain\Entity\Zones;

final readonly class GetAllZonesResponse
{
    public function __construct(
        public array $zones,
        public int $total,
    ) {}

    public static function create(array $zones): self
    {
        $zonesData = array_map(
            static fn (Zones $zone): array => [
                'id' => $zone->id()->value(),
                'name' => $zone->name(),
                'restaurant_id' => $zone->restaurantId(),
                'created_at' => $zone->createdAt()->format(\DateTimeInterface::ATOM),
                'updated_at' => $zone->updatedAt()->format(\DateTimeInterface::ATOM),
            ],
            $zones,
        );

        return new self(
            zones: $zonesData,
            total: count($zonesData),
        );
    }

    public function toArray(): array
    {
        return [
            'zones' => $this->zones,
            'total' => $this->total,
        ];
    }
}
