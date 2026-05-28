<?php

namespace App\Zones\Application\Response;

use App\Zones\Domain\Entity\Zones;

final readonly class UpdateZonesResponse
{
    private function __construct(
        private string $id,
        private string $name,
        private int $restaurantId,
        private string $createdAt,
        private string $updatedAt,
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
