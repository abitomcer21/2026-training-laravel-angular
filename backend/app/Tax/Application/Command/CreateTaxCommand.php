<?php

namespace App\Tax\Application\Command;

use App\Tax\Domain\ValueObject\TaxName;
use App\Tax\Domain\ValueObject\TaxPercentage;

final readonly class CreateTaxCommand
{
    private function __construct(
        public TaxName $name,
        public TaxPercentage $percentage,
        public int $restaurantId,
    ) {}

    public static function create(string $name, int $percentage, int $restaurantId): self
    {
        return new self(
            name:         TaxName::create($name),
            percentage:   TaxPercentage::create($percentage),
            restaurantId: $restaurantId,
        );
    }
}