<?php

namespace App\Sales\Domain\ValueObject;

class Quantity
{
    private int $value;

    private function __construct(int $value)
    {
        if ($value < 1) {
            throw new \InvalidArgumentException('Quantity must be at least 1.');
        }
        $this->value = $value;
    }

    public static function create(int $value): self
    {
        return new self($value);
    }

    public function quantity(): int
    {
        return $this->value;
    }

    public function value(): int
    {
        return $this->value;
    }
}
