<?php

namespace App\Taxes\Application\GetTaxesById;

use App\Taxes\Domain\Entity\Taxes;

final readonly class GetTaxesByIdResponse
{
    public function __construct(
        private string $id,
        private string $name,
        private int $percentage,
        public ?int $restaurantId,
        private string $createdAt,
        private string $updatedAt,
    ) {}

    public static function create(Taxes $taxes): self
    {
        return new self(
            id: $taxes->id()->value(),
            name: $taxes->name(),
            percentage: $taxes->percentage()->value(),
            restaurantId: $taxes->restaurantId(),
            createdAt: $taxes->createdAt()->format(\DateTimeInterface::ATOM),
            updatedAt: $taxes->updatedAt()->format(\DateTimeInterface::ATOM),
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