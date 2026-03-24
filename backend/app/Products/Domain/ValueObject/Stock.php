<?php

namespace App\Products\Domain\ValueObject;

class Stock
{
    private int $quantity;

    private function __construct(int $quantity)
    {
        if ($quantity < 0) {
            throw new \InvalidArgumentException('Stock cannot be negative.');
        }
        $this->quantity = $quantity;
    }

    public static function create(int $quantity): self
    {
        return new self($quantity);
    }

    public function quantity(): int
    {
        return $this->quantity;
    }

    public function value(): int
    {
        return $this->quantity;
    }

    public function isAvailable(): bool
    {
        return $this->quantity > 0;
    }
}
