<?php

namespace App\Family\Application\Response;

use App\Family\Domain\Entity\Family;

final readonly class GetAllFamilyResponse
{
    private function __construct(
        private array $families,
        private int $total,
    ) {}

    public static function create(array $families): self
    {
        $familyData = array_map(
            static fn (Family $family): array => [
                'id'=> $family->id()->value(),
                'name'=> $family->name()->value(),
                'active' => $family->active(),
                'restaurant_id' => $family->restaurantId(),
                'created_at'=> $family->createdAt()->format(\DateTimeInterface::ATOM),
                'updated_at'=> $family->updatedAt()->format(\DateTimeInterface::ATOM),
            ],
            $families,
        );

        return new self(
            families: $familyData,
            total: count($familyData),
        );
    }

    public function toArray(): array
    {
        return [
            'families' => $this->families,
            'total'=> $this->total,
        ];
    }
}