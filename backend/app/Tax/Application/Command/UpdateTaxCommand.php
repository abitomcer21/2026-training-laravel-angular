<?php

namespace App\Tax\Application\Command;

use App\Tax\Domain\ValueObject\TaxName;
use App\Tax\Domain\ValueObject\TaxPercentage;
use App\Shared\Domain\ValueObject\Uuid;

final readonly class UpdateTaxCommand
{
    private function __construct(
        public Uuid $id,
        public ?TaxName $name,
        public ?TaxPercentage $percentage,
        public int $restaurantId,
    ) {}

    public static function create(string $id, ?string $name, ?int $percentage, int $restaurantId): self
    {
        return new self(
            id:           Uuid::create($id),
            name:         $name !== null ? TaxName::create($name) : null,
            percentage:   $percentage !== null ? TaxPercentage::create($percentage) : null,
            restaurantId: $restaurantId,
        );
    }
}