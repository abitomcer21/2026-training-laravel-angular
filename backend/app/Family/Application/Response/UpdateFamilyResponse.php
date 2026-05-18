<?php

namespace App\Family\Application\Response;

use App\Family\Domain\Entity\Family;

final readonly class UpdateFamilyResponse
{
    private function __construct(
        private string $id,
        private string $name,
        private bool $active,
        private int $restaurantId,
        private string $createdAt,
        private string $updatedAt,
    ) {}

    public static function create(Family $family): self
    {
        return new self(
            id: $family->id()->value(),
            name: $family->name()->value(),
            active: $family->status()->isActive(),
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
            'active' => $this->active,
            'restaurant_id' => $this->restaurantId,
            'created_at' => $this->createdAt,
            'updated_at' => $this->updatedAt,
        ];
    }
}
