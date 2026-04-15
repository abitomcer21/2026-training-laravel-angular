<?php

namespace App\Tax\Application\UpdateTax;
use App\Tax\Domain\Entity\Tax;

final readonly class UpdateTaxResponse
{
    public function __construct(
        private string $id,
        private string $name,
        private int $percentage,
        public int $restaurantId,
    ) {}

    public static function create(Tax $tax): self
    {
        return new self(
            id: $tax->id()->value(),
            name: $tax->name(),
            percentage: $tax->percentage()->value(),
            restaurantId: $tax->restaurantId(),
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'percentage' => $this->percentage,
            'restaurant_id' => $this->restaurantId,
        ];
    }
}