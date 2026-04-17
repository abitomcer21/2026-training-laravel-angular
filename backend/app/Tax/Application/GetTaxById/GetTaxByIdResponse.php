<?php

namespace App\Tax\Application\GetTaxById;

use App\Tax\Domain\Entity\Tax;

final readonly class GetTaxByIdResponse
{
    public function __construct(
        private string $id,
        private string $name,
        private int $percentage,
        public int $restaurantId,
        private string $createdAt,
        private string $updatedAt,
    ) {}

    public static function create(Tax $tax): self
    {
        return new self(
            id: $tax->id()->value(),
            name: $tax->name(),
            percentage: $tax->percentage()->value(),
            restaurantId: $tax->restaurantId(),
            createdAt: $tax->createdAt()->format(\DateTimeInterface::ATOM),
            updatedAt: $tax->updatedAt()->format(\DateTimeInterface::ATOM),
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'percentage' => $this->percentage,
            'restaurant_id' => $this->restaurantId,
            'created_at' => $this->createdAt,
            'updated_at' => $this->updatedAt,
        ];
    }
}
