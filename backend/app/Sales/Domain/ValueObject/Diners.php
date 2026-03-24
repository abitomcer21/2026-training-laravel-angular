<?php

namespace App\Sales\Domain\ValueObject;

class Diners
{
    private int $quantity;

    private function __construct(int $quantity)
    {
        if ($quantity < 1) {
            throw new \InvalidArgumentException('Number of diners must be at least 1.');
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
}
