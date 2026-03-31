<?php

namespace App\Products\Domain\ValueObject;

class ProductPrice
{
    private int $cents;

    private function __construct(int $cents)
    {
        if ($cents < 0) {
            throw new \InvalidArgumentException('Price cannot be negative.');
        }
        $this->cents = $cents;
    }

    public static function create(int $cents): self
    {
        return new self($cents);
    }

    public static function fromEuros(float $euros): self
    {
        return new self((int) round($euros * 100));
    }

    public function cents(): int
    {
        return $this->cents;
    }

    public function value(): int
    {
        return $this->cents;
    }

    public function euros(): float
    {
        return $this->cents / 100;
    }
}
