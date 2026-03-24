<?php

namespace App\Sales\Domain\ValueObject;

class Total
{
    private int $cents;

    private function __construct(int $cents)
    {
        if ($cents < 0) {
            throw new \InvalidArgumentException('Total cannot be negative.');
        }
        $this->cents = $cents;
    }

    public static function create(int $cents): self
    {
        return new self($cents);
    }

    public static function zero(): self
    {
        return new self(0);
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
