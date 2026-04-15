<?php

namespace App\Tax\Domain\ValueObject;

class TaxPercentage
{
    private int $percentage;

    private function __construct(int $percentage)
    {
        if ($percentage < 0 || $percentage > 100) {
            throw new \InvalidArgumentException('Tax percentage must be between 0 and 100.');
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
