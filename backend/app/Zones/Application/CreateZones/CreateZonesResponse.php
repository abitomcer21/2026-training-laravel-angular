<?php

namespace App\Zones\Application\CreateZones;

use App\Zones\Domain\Entity\Zones;

final readonly class CreateZonesResponse
{
    public function __construct(
        public string $id,
        public string $name,
        public string $createdAt,
        public string $updatedAt,
    ) {}

    public static function create(Zones $zones): self
    {
        return new self(
            id: $zones->id()->value(),
            name: $zones->name(),
            createdAt: $zones->createdAt()->format(\DateTimeInterface::ATOM),
            updatedAt: $zones->updatedAt()->format(\DateTimeInterface::ATOM),
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'created_at' => $this->createdAt,
            'updated_at' => $this->updatedAt,
        ];
    }
}
