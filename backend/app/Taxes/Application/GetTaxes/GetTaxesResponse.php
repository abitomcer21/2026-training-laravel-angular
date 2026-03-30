<?php

namespace App\Taxes\Application\GetTaxes;

use App\Taxes\Domain\Entity\Taxes;

final readonly class GetTaxesResponse
{
    public function __construct(
        private string $id,
        private string $name,
        private int $percentage,
        public ?int $restaurantId,
    ) {}

    public static function create(Taxes $taxes): self
    {
        return new self(
            id: $taxes->id()->value(),
            name: $taxes->name(),
            percentage: $taxes->percentage()->value(),
            restaurantId: $taxes->restaurantId(),
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