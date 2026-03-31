<?php

namespace App\Taxes\Application\GetAllTaxes;

use App\Taxes\Domain\Entity\Taxes;

final readonly class GetAllTaxesResponse
{

    public function __construct(
        public array $taxes,
        public int $total,
    ) {}


    public static function create(array $taxes): self
    {
        $taxesData = array_map(
            static fn(Taxes $tax): array => [
                'id' => $tax->id()->value(),
                'name' => $tax->name(),
                'percentage' => $tax->percentage()->value(),
                'restaurant_id' => $tax->restaurantId(),
                'created_at' => $tax->createdAt()->format(\DateTimeInterface::ATOM),
                'updated_at' => $tax->updatedAt()->format(\DateTimeInterface::ATOM),
            ],
            $taxes,
        );

        return new self(
            taxes: $taxesData,
            total: count($taxesData),
        );
    }


    public function toArray(): array
    {
        return [
            'taxes' => $this->taxes,
            'total' => $this->total,
        ];
    }
}
