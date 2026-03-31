<?php

namespace App\Families\Application\GetAllFamily;

use App\Families\Domain\Entity\Family;

final readonly class GetAllFamilyResponse
{
    public function __construct(
        public array $families,
        public int $total,
    ) {}

    public static function create(array $families): self
    {
        $familiesData = array_map(
            static fn (Family $family): array => [
                'id' => $family->id()->value(),
                'name' => $family->name(),
                'restaurant_id' => $family->restaurantId(),
                'created_at' => $family->createdAt()->format(\DateTimeInterface::ATOM),
                'updated_at' => $family->updatedAt()->format(\DateTimeInterface::ATOM),
            ],
            $families,
        );

        return new self(
            families: $familiesData,
            total: count($familiesData),
        );
    }

    public function toArray(): array
    {
        return [
            'families' => $this->families,
            'total' => $this->total,
        ];
    }
}
