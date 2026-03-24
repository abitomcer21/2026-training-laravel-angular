<?php

namespace App\Products\Domain\ValueObject;

class ProductStatus
{
    private bool $isActive;

    private function __construct(bool $isActive)
    {
        $this->isActive = $isActive;
    }

    public static function create(bool $isActive): self
    {
        return new self($isActive);
    }

    public static function active(): self
    {
        return new self(true);
    }

    public static function inactive(): self
    {
        return new self(false);
    }

    public function isActive(): bool
    {
        return $this->isActive;
    }

    public function value(): bool
    {
        return $this->isActive;
    }
}
