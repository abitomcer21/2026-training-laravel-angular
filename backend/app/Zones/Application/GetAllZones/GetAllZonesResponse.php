<?php

namespace App\Zones\Application\GetAllZones;

use App\Zones\Domain\Entity\Zones;

final readonly class GetAllZonesResponse
{
    public function __construct(
        public array $zones,
        public int $total,
    ) {}

    public static function create(array $zonesData): self
    {
        $zones = array_map(
            static fn (array $item): array => [
                'database_id' => $item['database_id'],
                'id' => $item['zone']->id()->value(),
                'name' => $item['zone']->name(),
                'restaurant_id' => $item['zone']->restaurantId(),
                'created_at' => $item['zone']->createdAt()->format(\DateTimeInterface::ATOM),
                'updated_at' => $item['zone']->updatedAt()->format(\DateTimeInterface::ATOM),
            ],
            $zonesData,
        );

        return new self(
            zones: $zones,
            total: count($zones),
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
