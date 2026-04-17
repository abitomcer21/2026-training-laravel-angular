<?php

namespace App\Family\Application\GetAllFamily;

use App\Family\Domain\Entity\Family;

final readonly class GetAllFamilyResponse
{
    public function __construct(
        public array $family,
        public int $total,
    ) {}

    public static function create(array $family): self
    {
        $FamilyData = array_map(
            static fn (Family $family): array => [
                'id' => $family->id()->value(),
                'name' => $family->name()->value(),
                'active' => $family->status()->isActive(),
                'restaurant_id' => $family->restaurantId(),
                'created_at' => $family->createdAt()->format(\DateTimeInterface::ATOM),
                'updated_at' => $family->updatedAt()->format(\DateTimeInterface::ATOM),
            ],
            $family,
        );

        return new self(
            family: $FamilyData,
            total: count($FamilyData),
        );
    }

    public function toArray(): array
    {
        return [
            'Family' => $this->family,
            'total' => $this->total,
        ];
    }
}
