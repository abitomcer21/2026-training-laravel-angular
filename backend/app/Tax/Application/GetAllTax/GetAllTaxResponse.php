<?php

namespace App\Tax\Application\GetAllTax;

use App\Tax\Domain\Entity\Tax;

final readonly class GetAllTaxResponse
{
    public function __construct(
        public array $tax,
        public int $total,
    ) {}

    public static function create(array $tax): self
    {
        $taxData = array_map(
            static fn (Tax $tax): array => [
                'id' => $tax->id()->value(),
                'name' => $tax->name(),
                'percentage' => $tax->percentage()->value(),
                'restaurant_id' => $tax->restaurantId(),
                'created_at' => $tax->createdAt()->format(\DateTimeInterface::ATOM),
                'updated_at' => $tax->updatedAt()->format(\DateTimeInterface::ATOM),
            ],
            $tax,
        );

        return new self(
            tax: $taxData,
            total: count($taxData),
        );
    }

    public function toArray(): array
    {
        return [
            'tax' => $this->tax,
            'total' => $this->total,
        ];
    }
}
