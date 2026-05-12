<?php

namespace App\Shared\Domain\ValueObject;

use App\Shared\Domain\Exceptions\InvalidTaxPercentageException;

class TaxPercentage
{
    private int $percentage;

    private function __construct(int $percentage)
    {
        if ($percentage < 0 || $percentage > 100) {
            throw new InvalidTaxPercentageException($percentage);
        }
        $this->percentage = $percentage;
    }

    public static function create(int $percentage): self
    {
        return new self($percentage);
    }

    public function percentage(): int
    {
        return $this->percentage;
    }

    public function value(): int
    {
        return $this->percentage;
    }

    public function asDecimal(): float
    {
        return $this->percentage / 100;
    }
}