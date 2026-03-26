<?php

namespace App\Families\Application\UpdateFamily;

use App\Families\Domain\Entity\Family;

final readonly class UpdateFamilyResponse
{
    public function __construct(
        public string $id,
        public string $name,
        public bool $activo,
        public int $restaurantId,
        public string $createdAt,
        public string $updatedAt,
    ) {}

    public static function create(Family $family): self
    {
        return new self(
            id: $family->id()->value(),
            name: $family->name(),
            activo: $family->status()->isActive(),
            restaurantId: $family->restaurantId(),
            createdAt: $family->createdAt()->format(\DateTimeInterface::ATOM),
            updatedAt: $family->updatedAt()->format(\DateTimeInterface::ATOM),
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'activo' => $this->activo,
            'restaurant_id' => $this->restaurantId,
            'created_at' => $this->createdAt,
            'updated_at' => $this->updatedAt,
        ];
    }
}